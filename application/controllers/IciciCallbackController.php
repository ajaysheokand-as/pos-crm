<?php
defined('BASEPATH') or exit('No direct script access allowed');

class IciciCallbackController extends CI_Controller {
    public function deposit_callback() {

        $encResponse = file_get_contents('php://input');

        $api_status_id = 0;
        $api_errors = "";
        $response_array = array();
        $api_response_datetime = date('Y-m-d H:i:s');
        $apiResponseData = "";

        try {

            $fp = fopen(COMP_PATH . '/includes/icici-key/serv.txt', 'r');
            $priv_key = fread($fp, 8192);
            fclose($fp);

            $res = openssl_get_privatekey($priv_key, "");
            openssl_private_decrypt(base64_decode($encResponse), $newsource, $res);
            $apiResponseData = json_decode($newsource, true);

            if (empty($apiResponseData)) {
                throw new Exception("Empty response received.");
            }

            $merchantTranId = !empty($apiResponseData['merchantTranId']) ? $apiResponseData['merchantTranId'] : null;
            $amount = !empty($apiResponseData['PayerAmount']) ? $apiResponseData['PayerAmount'] : 0;
            $transactionNo = !empty($apiResponseData['BankRRN']) ? $apiResponseData['BankRRN'] : "";

            $logData = $this->db->query("SELECT * FROM api_upi_logs WHERE au_transaction_id='$merchantTranId' AND au_active=1 AND au_deleted=0 ORDER BY au_id DESC LIMIT 1")->row_array();

            if (empty($logData)) {
                throw new Exception("No log data found.");
            }

            $lead_id = !empty($logData['au_lead_id']) ? $logData['au_lead_id'] : 0;
            $method_id = !empty($logData['au_method_id']) ? $logData['au_method_id'] : 0;
            $user_id = !empty($logData['au_user_id']) ? $logData['au_user_id'] : 0;

            if (empty($lead_id)) {
                throw new Exception("Lead id not found.");
            }

            require_once(COMPONENT_PATH . "CommonComponent.php");
            $CommonComponent = new CommonComponent();
            $responseData = $CommonComponent->call_check_upi_api($lead_id, array("payment_mode_id" => $method_id));

            if ($responseData['status'] != 1) {
                throw new Exception($responseData['errors']);
            }

            $api_status_id = 1;
            $return_message = "Response saved successfully";
        } catch (Exception $ex) {
            $api_status_id = 2;
            $api_errors = $ex->getMessage();
            $return_message = $api_errors;
        }

        $insert_array = array();
        $insert_array['acu_method_id'] = 1;
        $insert_array['acu_lead_id'] = $lead_id;
        $insert_array['acu_transaction_id'] = $merchantTranId;
        $insert_array['acu_response'] = json_encode($apiResponseData);
        $insert_array['acu_encrypt_response'] = $encResponse;
        $insert_array['acu_status_id'] = $api_status_id;
        $insert_array['acu_errors'] = $api_errors;
        $insert_array['acu_response_datetime'] = $api_response_datetime;
        $insert_array['acu_requested_amount'] = $amount;

        $this->db->insert('api_callback_upi', $insert_array);

        if ($api_status_id == 1) {

            // Prepare data to insert into collection table
            $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no, email FROM leads WHERE lead_active = 1 AND lead_id = ?";
            $query_data = $this->db->query($query, [$lead_id])->row_array();

            $dataToInsert = array(
                'lead_id' => $lead_id,
                'customer_id' => $query_data['customer_id'],
                'loan_no' => $query_data['loan_no'],
                'payment_mode' => 'ICICI UPI',
                'payment_mode_id' => 3,
                'received_amount' => $amount,
                'refrence_no' => $transactionNo,
                'date_of_recived' => date("Y-m-d"),
                'repayment_type' => 19,
                'company_account_no' => '071805004842',
                'docs' => '',
                'discount' => 0,
                'recovery_status' => 0,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'remarks' => 'Payment Received through ICICI UPI',
                'collection_executive_user_id' => $user_id > 0 ? $user_id : NULL,
                'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                'payment_verification' => 0
            );

            // Insert the data into the collection table
            $this->db->insert('collection', $dataToInsert);

            $this->sendSuccessResponse("er9.alam@gmail.com", $dataToInsert['received_amount'], $dataToInsert['refrence_no']);
            // $this->sendSuccessResponse($query_data['email'], $amount, $transactionNo);
        }

        $response_array['Status'] = $api_status_id;
        $response_array['Message'] = $return_message;

        echo json_encode($response_array);
    }

    private function sendSuccessResponse($email = "", $amount = 0, $reference = "") {

        require_once(COMPONENT_PATH . "CommonComponent.php");

        if (empty($email) || empty($amount) || empty($reference)) {
            return false;
        }

        $amount = number_format($amount, 2);
        $email_subject = "Payment Received | " . $amount;

        $email_message = '<!DOCTYPE html>
                            <html lang="en">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Payment Received</title>
                            </head>
                            <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; color: #333;">
                                <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e5e7eb;">
                                    <div style="text-align: center; padding: 20px; background-color: #333;">
                                        <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="Company Logo" style="width: 120px; margin-bottom: 15px;">
                                    </div>
                                    <div style="background-color: #4CAF50; color: #ffffff; text-align: center; padding: 15px; font-size: 22px; font-weight: bold;">
                                        Payment Received
                                    </div>
                                    <div style="padding: 20px 25px; font-size: 16px; line-height: 1.6;">
                                        <p>Dear Customer,</p>
                                        <p>Thank you for your payment! We are pleased to inform you that we have received your payment successfully.</p>
                                        <p><strong>Reference Number:</strong> ' . $reference . '</p>
                                        <p><strong>Amount Paid:</strong> ₹' . $amount . '</p>
                                        <p>Thank you for your payment. Our team will review it and get in touch with you within 24 hours. If you have any questions or need assistance in the meantime, please don’t hesitate to contact us.</p>
                                        <p>Warm regards,</p>
                                        <p><strong>' . BRAND_NAME . '</strong></p>
                                    </div>
                                    <div style="background-color: #f9fafb; text-align: center; padding: 15px; font-size: 14px; color: #666; border-top: 1px solid #e5e7eb;">
                                        <p>&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                        <p>
                                            <a href="' . WEBSITE_URL . '" target="_blank" style="color: #4CAF50; text-decoration: none;">Visit our website</a> |
                                            <a href="' . WEBSITE_URL . "contact" . '" target="_blank" style="color: #4CAF50; text-decoration: none;">Contact Support</a>
                                        </p>
                                        <div style="text-align: center; margin: 20px 0;">
                                            <p style="font-size: 14px; color: #777; margin: 10px 0;">Follow us on:</p>
                                           <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . FACEBOOK_ICON . '" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . TWITTER_ICON . '" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . LINKEDIN_ICON . '" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . INSTAGRAM_ICON . '" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . YOUTUBE_ICON . '" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </body>
                            </html>';

        common_send_email($email, $email_subject, $email_message, "", "", "info@salarywalle.com", "", "", "", "");
    }
}
