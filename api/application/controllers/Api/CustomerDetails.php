<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class CustomerDetails extends REST_Controller {

    // public $white_listed_ips = array("208.109.63.229");

    public function __construct() {
        parent::__construct();
        $this->load->model('Lead_Model', 'Lead');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }


    public function getLoan_post() {
        $input_data = file_get_contents("php://input");
        //return $this->response($input_data, REST_Controller::HTTP_OK);
        if ($input_data) {
            $jsonInput = json_decode($input_data, true);
            $post = $this->security->xss_clean($jsonInput);
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        // print_r($post); die;
        $query = $this->db->select('lead_id, first_name, mobile, email, loan_amount,loan_no')->where('pancard', $post['panNumber'])->where('status', 'DISBURSED')->where('stage', 'S14')->from('leads')->order_by('lead_id', 'DESC')->limit(1)->get();
        $result = $query->row();
        if (isset($result) && !empty($result->lead_id)) {
            $res = array('Status' => 1, 'Message' => 'Record found successfully.');
            $data = $this->Lead->getLoanRepaymentDetails($result->lead_id);
            $res['data'] = $data;
            return json_encode($this->response($res, REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => 2, 'Message' => 'No PAN found.'], REST_Controller::HTTP_OK));
        }
    }

    public function SendOtp_post_old() {

        $input_data = file_get_contents("php://input");
        //return $this->response($input_data, REST_Controller::HTTP_OK);
        if ($input_data) {
            $jsonInput = json_decode($input_data, true);
            $post = $this->security->xss_clean($jsonInput);
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $queryLead = $this->db->select('lead_id, first_name, mobile')->where('pancard', $post['pancard'])->where('status', 'DISBURSED')->where('stage', 'S14')->from('leads')->order_by('lead_id', 'DESC')->limit(1)->get();
        $resultLead = $queryLead->row();

        $Panlead = $resultLead->lead_id;
        $mobileLead = $resultLead->mobile;
        $firstname_Lead = $resultLead->first_name;
        $last_four_mob = substr($mobileLead, -4);

        $currentDate = date("Y-m-d");

        $queryOtp = $this->db->select('lot_lead_id,lot_mobile_no')->where('lot_mobile_no', $mobileLead)->where("DATE_FORMAT(lot_otp_trigger_time, '%Y-%m-%d') =", $currentDate)->where('lot_lead_id', $Panlead)->from('leads_otp_trans')->get();
        $resultotp = $queryOtp->row();

        if ($queryOtp->num_rows() > 10) {
            $res = array('Status' => 2, 'Message' => 'You can attempt max 10 time.');
            return json_encode($this->response($res, REST_Controller::HTTP_OK));
        } else {
            if (isset($Panlead) && ($Panlead > 0)) {
                $otp = rand(1000, 9999);
                $insertDataOTP = array(
                    'lot_lead_id' => $Panlead,
                    'lot_mobile_no' => $mobileLead,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 2,
                    'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
                );

                $this->db->insert('leads_otp_trans', $insertDataOTP);
                $this->db->insert_id();
                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();
                $sms_input_data = array();
                $sms_input_data['mobile'] = $mobileLead;
                $sms_input_data['name'] = $firstname_Lead;
                $sms_input_data['otp'] = $otp;
                $CommonComponent->payday_sms_api(1, $Panlead, $sms_input_data);


                return json_encode($this->response(['Status' => 1, 'Message' => 'Otp send on your register mobile no. #######' . $last_four_mob], REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 2, 'Message' => 'Enter Wrong Otp.'], REST_Controller::HTTP_OK));
            }
        }
    }

    public function SendOtp_post() {
        // Get input data
        $input_data = file_get_contents("php://input");
        $post = $input_data ? json_decode($this->security->xss_clean($input_data), true) : $this->security->xss_clean($_POST);

        try {
            if (empty($post['pancard'])) {
                throw new Exception('Pancard is required.', REST_Controller::HTTP_BAD_REQUEST);
            }

            // Fetch lead details
            $query = "SELECT LD.lead_id, LD.first_name, LD.mobile
                      FROM leads LD
                      WHERE LD.lead_status_id IN(14,19)
                      AND LD.pancard = ?";
            $lead = $this->db->query($query, [$post['pancard']])->row();

            if (!$lead) {
                throw new Exception('No lead found.', REST_Controller::HTTP_NOT_FOUND);
            }

            $lead_id = $lead->lead_id;
            $mobileLead = $lead->mobile;
            $firstname_Lead = $lead->first_name;
            $last_four_mob = substr($mobileLead, -4);

            // Check OTP attempts for the current day
            $currentDate = date("Y-m-d");
            $otpAttemptsQuery = "SELECT COUNT(*) AS counts
                                 FROM leads_otp_trans
                                 WHERE DATE_FORMAT(lot_otp_trigger_time, '%Y-%m-%d') = ?
                                 AND lot_mobile_no = ?
                                 AND lot_lead_id = ?";
            $otpAttempts = $this->db->query($otpAttemptsQuery, [$currentDate, $mobileLead, $lead_id])->row();

            if ($otpAttempts->counts >= 10) {
                throw new Exception('You can attempt max 10 times.', REST_Controller::HTTP_OK);
            }

            // Generate and insert OTP
            $otp = rand(1000, 9999);
            $insertDataOTP = [
                'lot_lead_id' => $lead_id,
                'lot_mobile_no' => $mobileLead,
                'lot_mobile_otp' => $otp,
                'lot_mobile_otp_type' => 2,
                'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
            ];

            if (!$this->db->insert('leads_otp_trans', $insertDataOTP)) {
                throw new Exception('Failed to save OTP.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Send OTP via SMS
            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $sms_input_data = [
                'mobile' => $mobileLead,
                'name' => $firstname_Lead,
                'otp' => $otp,
            ];

            if (!$CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data)) {
                throw new Exception('Failed to send OTP.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->response(
                ['Status' => 1, 'Message' => 'Otp sent to your registered mobile number #######' . $last_four_mob],
                REST_Controller::HTTP_OK
            );
        } catch (Exception $e) {
            // Handle errors
            return $this->response(
                ['Status' => 2, 'Message' => $e->getMessage()],
                $e->getCode() ?: REST_Controller::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyOtp_post() {
        // Get input data
        $input_data = file_get_contents("php://input");
        $post = $input_data ? json_decode($this->security->xss_clean($input_data), true) : $this->security->xss_clean($_POST);

        try {
            if (empty($post['panNumber']) || empty($post['otp'])) {
                throw new Exception('Pan number and OTP are required.', REST_Controller::HTTP_BAD_REQUEST);
            }

            // Fetch lead details
            $resultQuery = "SELECT LD.lead_id, LD.first_name, LD.mobile
                      FROM leads LD
                      WHERE LD.lead_status_id IN(14,19)
                      AND LD.pancard = ?";
            $resultLead = $this->db->query($resultQuery, $post['panNumber'])->row();

            if (empty($resultLead->lead_id)) {
                return $this->response(['Status' => 2, 'Message' => 'No data found.'], REST_Controller::HTTP_OK);
            }

            $mobileLead = $resultLead->mobile;
            $email = $resultLead->email;
            $last_four_mob = substr($mobileLead, -4);
            $lead_id = $resultLead->lead_id;

            // Verify OTP
            $currentDate = date("Y-m-d");
            $queryOtp = $this->db->select('*')
                ->where('lot_mobile_no', $mobileLead)
                ->where('lot_lead_id', $lead_id)
                ->where("DATE_FORMAT(lot_otp_trigger_time, '%Y-%m-%d') =", $currentDate)
                ->from('leads_otp_trans')
                ->order_by('lot_id', 'DESC')
                ->limit(1)
                ->get();
            $resultOtp = $queryOtp->row();

            if (empty($resultOtp->lot_lead_id) || $resultOtp->lot_otp_verify_flag == 1) {
                return $this->response(['Status' => 2, 'Message' => 'OTP is not valid, please resend OTP.'], REST_Controller::HTTP_OK);
            }

            if ($resultOtp->lot_mobile_otp != $post['otp']) {
                return $this->response(['Status' => 2, 'Message' => 'Entered wrong OTP.'], REST_Controller::HTTP_OK);
            }

            // Mark OTP as verified
            $this->db->where('lot_id', $resultOtp->lot_id)->update('leads_otp_trans', ['lot_otp_verify_flag' => 1]);

            // Fetch loan repayment details after OTP verification
            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $repayment_data = $CommonComponent->get_loan_repayment_details($resultLead->lead_id)['repayment_data'];

            // Create RazorPay Order ID
            $order_id = $this->createRazorPayOrderID($resultLead->lead_id, $repayment_data);

            if ($order_id["Status"] != 1) {
                return $this->response(['Status' => 1, 'Message' => 'Order ID not created.'], REST_Controller::HTTP_OK);
            }

            // Mask mobile and email for response
            $repayment_data['mobile'] = str_repeat('*', strlen($mobileLead) - 4) . $last_four_mob;
            $email_parts = explode('@', $email);
            $repayment_data['email'] = substr($email_parts[0], 0, 1) . str_repeat('*', strlen($email_parts[0]) - 1) . '@' . $email_parts[1];

            // Send success response with order ID and repayment details
            $response = [
                'Status' => 1,
                'Message' => 'OTP verified on your registered mobile no. ######' . $last_four_mob,
                'order_id' => $order_id["order_id"],
                'repayment_data' => $repayment_data
            ];

            return $this->response($response, REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            // Handle errors
            return $this->response(
                ['Status' => 2, 'Message' => $e->getMessage()],
                $e->getCode() ?: REST_Controller::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyOtp_post_old() {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $jsonInput = json_decode($input_data, true);
            $post = $this->security->xss_clean($jsonInput);
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        // Query the lead details
        $queryLead = $this->db->select('lead_id, first_name, otp, mobile, email, loan_amount, loan_no')
            ->where('pancard', $post['panNumber'])
            ->where('status', 'DISBURSED')
            ->where('stage', 'S14')
            ->from('leads')
            ->order_by('lead_id', 'DESC')
            ->limit(1)
            ->get();
        $resultLead = $queryLead->row();

        if (!$resultLead) {
            return json_encode($this->response(['Status' => 2, 'Message' => 'No Data found.'], REST_Controller::HTTP_OK));
        }

        $mobileLead = $resultLead->mobile;
        $email = $resultLead->email;
        $last_four_mob = substr($mobileLead, -4);
        $Panlead = $resultLead->lead_id;

        // Verify OTP
        $currentDate = date("Y-m-d");
        $queryOtp = $this->db->select('*')
            ->where('lot_mobile_otp', $post['otp'])
            ->where('lot_lead_id', $Panlead)
            ->where("DATE_FORMAT(lot_otp_trigger_time, '%Y-%m-%d') =", $currentDate)
            ->from('leads_otp_trans')
            ->order_by('lot_lead_id', 'DESC')
            ->limit(1)
            ->get();
        $resultOtp = $queryOtp->row();

        if (!$resultOtp || $resultOtp->lot_otp_verify_flag == 1) {
            return json_encode($this->response(['Status' => 2, 'Message' => 'Otp is not valid, please resend OTP.'], REST_Controller::HTTP_OK));
        }

        if ($resultOtp->lot_mobile_otp != $post['otp']) {
            return json_encode($this->response(['Status' => 2, 'Message' => 'Entered wrong OTP.'], REST_Controller::HTTP_OK));
        }

        // Fetch loan repayment details after OTP verification
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $repayment_data = $CommonComponent->get_loan_repayment_details($resultLead->lead_id)['repayment_data'];

        // Create RazorPay Order ID
        $order_id = $this->createRazorPayOrderID($resultLead->lead_id, $repayment_data);

        if ($order_id["Status"] != 1) {
            return json_encode($this->response(['Status' => 1, 'Message' => 'Order Id not created'], REST_Controller::HTTP_OK));
        }

        // // Masking mobile: show only last 4 digits
        // $masked_mobile = substr($resultLead->mobile, -4);
        // $repayment_data['mobile'] = str_repeat('*', strlen($resultLead->mobile) - 4) . $masked_mobile;

        // // Masking email: show only first letter and full domain
        // $exploded_email = explode('@', $resultLead->email);
        // $masked_email = substr($exploded_email[0], 0, 1) . str_repeat('*', strlen($exploded_email[0]) - 1) . '@' . $exploded_email[1];
        // $repayment_data['email'] = $masked_email;

        $repayment_data['mobile'] = 9999999999;
        $repayment_data['email'] = "info@tejasloan.com";

        // Send success response with order ID and repayment details
        $response = [
            'Status' => 1,
            'Message' => 'Otp verified on your registered mobile no. ######' . $last_four_mob,
            'order_id' => $order_id["order_id"],
            'repayment_data' => $repayment_data
        ];

        return json_encode($this->response($response, REST_Controller::HTTP_OK));
    }

    function createRazorPayOrderID($lead_id, $repayment_data) {
        $curl = curl_init();
        $return_data = [];

        // Calculate amount in paise and prepare payload for RazorPay API request
        $rp_amount = round(($repayment_data['total_due_amount'] * 100), 2);
        $loan_no = $repayment_data['loan_no'];

        $payload = json_encode([
            "amount" => $rp_amount,
            "currency" => "INR",
            "receipt" => $loan_no . "-" . date("YmdHis"),
            "notes" => [
                "orderid" => $loan_no,
                "lead_id" => $lead_id
            ],
            "partial_payment" => true,
            // "first_payment_min_amount" => 10000
        ]);

        $key_id = "rzp_live_gSedwg0IRWdr5a";
        $key_secret = "5gDjxpdq7DhwrM7MA4W8Eqg2";

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $key_id . ':' . $key_secret,
            CURLOPT_POSTFIELDS => $payload
        ]);

        // Execute cURL request and handle response
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            // Handle error
            $return_data['Status'] = 0;
            $return_data['error_message'] = curl_error($curl);
        } else {
            $decoded_response = json_decode($response, true);
            $return_data['Status'] = 1;
            $return_data['order_id'] = $decoded_response['id'] ?? null;
            $return_data['rzp_amount'] = $rp_amount;
        }

        // Close cURL session
        curl_close($curl);

        return $return_data;
    }

    function verifyRazorPayCheckPaymentStatus_post() {

        // Replace with your Razorpay Key and Secret
        // $apiKey = 'rzp_live_3XXwpvgLtdYIh3';
        // $apiSecret = 'hwsGRXVuJ5BUjT3KdNaSYc4T';

        $apiKey = 'rzp_live_gSedwg0IRWdr5a';
        $apiSecret = '5gDjxpdq7DhwrM7MA4W8Eqg2';

        header('Content-Type: application/json');

        // Get the JSON body from the request (Sent by Razorpay or frontend)
        $inputData = json_decode(file_get_contents("php://input"), true);

        try {
            // Check if all required fields are present
            if (
                !isset($inputData['razorpay_payment_id']) ||
                !isset($inputData['razorpay_order_id']) ||
                !isset($inputData['razorpay_signature'])
            ) {
                throw new Exception('Missing required parameters.');
            }

            $razorpayPaymentId = $inputData['razorpay_payment_id'];
            $razorpayOrderId = $inputData['razorpay_order_id'];
            $razorpaySignature = $inputData['razorpay_signature'];

            // Step 1: Create the string to hash by concatenating order_id and payment_id
            $generatedSignature = $razorpayOrderId . '|' . $razorpayPaymentId;

            // Step 2: Calculate HMAC SHA256 of the generatedSignature using Razorpay Secret as key
            $calculatedSignature = hash_hmac('sha256', $generatedSignature, $apiSecret);

            // Step 3: Compare the calculated signature with the one provided by Razorpay
            if (!hash_equals($calculatedSignature, $razorpaySignature)) {
                throw new Exception('Payment verification failed using signature.');
            }

            // If signature verification is successful, we can also check the payment status from Razorpay API
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.razorpay.com/v1/payments/' . $razorpayPaymentId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERPWD => $apiKey . ':' . $apiSecret,
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // Convert the response to JSON format
            $paymentData = json_decode($response, true);

            // Check if the payment is successful
            if ($paymentData['status'] == 'captured') {
                $this->insertRecoveryResponse($paymentData);
                // Return success response
                echo json_encode([
                    "status" => "success",
                    "message" => "Payment captured successfully.",
                    "data" => $paymentData,
                    "txnId" => $paymentData['acquirer_data']['rrn']
                ]);
                exit;
            } else {
                // Payment status is not captured, return failure message
                echo json_encode([
                    "status" => "failure",
                    "message" => "Payment not successful, status: " . $paymentData['status'],
                    "txnId" => $paymentData['acquirer_data']['rrn']
                ]);
                exit;
            }
        } catch (Exception $e) {
            // Log the error (optional, to debug in production environments)
            error_log($e->getMessage());

            // Return failure response with error message
            echo json_encode([
                "status" => "failure",
                "message" => $e->getMessage()
            ]);
            exit;
        }
    }

    function insertRecoveryResponse($data) {

        // Check if data is provided
        if (!empty($data)) {

            // Decode the JSON data
            $decoded_data = $data;

            if ($decoded_data['amount'] > 0 &&  isset($decoded_data['notes']) && isset($decoded_data['notes'])) {

                $actual_amount = ($decoded_data['amount'] - ($decoded_data['fee'])) / 100;
                // $actual_amount = number_format($actual_amount / 100, 2, '.', '');
                $lead_id = (int)$decoded_data['notes']['lead_id'];
                $bank_rrn = $decoded_data['acquirer_data']['rrn'];
                $bank_transaction_id = $decoded_data['acquirer_data']['bank_transaction_id'];

                if ($lead_id > 0) {

                    $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no FROM leads WHERE lead_active = 1 AND lead_id = ?";

                    $query_data = $this->db->query($query, array($lead_id))->row_array();

                    if ($query_data) {

                        $recoveryData = array(
                            'company_id' => $query_data['company_id'],
                            'lead_id' => $query_data['lead_id'],
                            'customer_id' => $query_data['customer_id'],
                            'loan_no' => $query_data['loan_no'],
                            'payment_mode' => 'Razorpay',
                            'payment_mode_id' => 13,
                            'received_amount' => $actual_amount,
                            'refrence_no' => (!empty($bank_rrn) ? $bank_rrn : $bank_transaction_id),
                            'date_of_recived' => date("Y-m-d"),
                            'repayment_type' => 19,
                            'remarks' => 'Payment Received through Razor Pay',
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                            'payment_verification' => 0,
                        );

                        // Insert the recovery data into the collection table
                        $this->db->insert('collection', $recoveryData);
                    } else {
                        // Log an error or handle case where lead_id does not exist
                        log_message('error', "Lead not found for lead_id: {$lead_id}");
                    }
                } else {
                    // Invalid lead_id
                    echo json_encode([
                        "status" => 0,
                        "message"  => "Invalid lead_id: {$lead_id}"
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => 0,
                    "message"  => "Json Parse failed."
                ]);
                exit;
            }
        } else {
            // Handle case where $data is empty
            echo json_encode([
                "status" => 0,
                "message"  => 'Empty data received'
            ]);
            exit;
        }
    }
}
