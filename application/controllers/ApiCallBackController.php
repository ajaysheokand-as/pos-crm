<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ApiCallBackController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Calcutta");
        define("timestamp", date('Y-m-d H:i:s'));
        $this->load->model('Integration/Integration_Model', 'IntegrationModel');
    }

    public function callback_novel_bank_analysis() {
        $return_array = array("status" => 0, "errors" => "");

        if (!empty($_POST['status']) && in_array(strtolower($_POST['status']), array("processed", "downloaded"))) {

            if (!empty($_POST['docId'])) {
                $docId = intval($_POST['docId']);

                $this->load->helper('integration/payday_bank_analysis_call_api_helper');

                $bank_return_array = payday_bank_analysis_api_call("BANK_STATEMENT_DOWNLOAD", 0, $docId);

                if ($bank_return_array['status'] == 1) {
                    $return_array['status'] = 1;
                } else {
                    $return_array['errors'] = $bank_return_array["error_msg"];
                }
            } else {
                $return_array['errors'] = "Return document id not found.";
            }
        } else {
            $return_array['errors'] = "Return status does not proceed.";
        }

        return json_encode($return_array);
    }

    public function eSignSanctionLetterRequest() {

        $return_status = 0;
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_GET['docId'])) {
            $esign_data = $this->IntegrationModel->geteSignDetailsByDocId($_GET['docId']);

            if ($esign_data['status'] == 1) {
                $lead_id = $esign_data['esign_data']['esign_lead_id'];
            }
        }

        if (!empty($_GET['lead_id']) || !empty($_GET['refstr'])) {

            if (!empty($_GET['lead_id'])) {
                $lead_id = $_GET['lead_id'];
            } else if (!empty($_GET['refstr'])) {
                $enc_lead_id = $_GET['refstr'];
                $lead_id = $this->encrypt->decode($enc_lead_id);
            }

            $request_array = array("refstr" => $this->encrypt->encode($lead_id));

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if ($applicationDetails['lead_status_id'] == 12) {

                        $lead_followup_insert_array = [
                            'lead_id' => $lead_id,
                            'customer_id' => $applicationDetails['customer_id'],
                            'user_id' => $user_id,
                            'status' => $applicationDetails['status'],
                            'stage' => $applicationDetails['stage'],
                            'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                            'remarks' => "Sanction letter eSign request initiated",
                            'created_on' => date("Y-m-d H:i:s")
                        ];

                        $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                        require_once(COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $esign_return = $CommonComponent->call_esign_api($lead_id, $request_array);

                        $message = '<p style="text-align : center;">eSign Process...</p>';

                        if ($esign_return['status'] == 1) {
                            $message .= '<br/><br/><p style="text-align : center;">Please keep below points in mind : </p>';
                            $message .= '<br/><br/><p style="text-align : center;">1. Please wait, you will be redirect to NSDL for eSign.</p>';
                            $message .= '<br/><p style="text-align : center;">2. If you are not able to redirect to NSDL portal, Please connect with Sanction Executive.</p>';
                            $message .= '<br/><p style="text-align : center;">3. Only three times request is allowed.</p>';
                            $message .= '<br/><p style="text-align : center;">4. When you do the successfully eSigned on NSDL, You will be redirect to our portal again in 10 second.</p>';
                            $message .= '<br/><br/><p style="text-align : center;">Please <a href="' . $esign_return['nsdl_url'] . '">click here</a> if you are not able to redirect to NSDL portal.</p>';

                            $message .= '<script type="text/javascript">';
                            $message .= 'window.location = "' . $esign_return['nsdl_url'] . '"';
                            $message .= '</script>';
                        } else {
                            $esign_error = $esign_return['errors'];
                            $message .= '<p style="text-align : center;">Message : ' . $esign_error . '</p>';
                        }
                    } else {
                        $message = "Application has been already accepted and move to next step.";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        echo $message;
    }

    public function eSignConsentForm() {

        if (!empty($_GET['refstr'])) {

            $refstr = $_GET['refstr'];
            $lead_id = intval($this->encrypt->decode($refstr));
            $appDataReturnArr = $this->IntegrationModel->geteSignCamDetails($lead_id);

            if ($appDataReturnArr['status'] == 1) {
                $data['refstr'] = $refstr;
                $data['cam_data'] = $appDataReturnArr['cam_data'];
                $this->load->view('CAM/esign-consent-form', $data);
            } else {
                echo 'invlaid consent tried..';
            }
        } else {
            echo 'invlaid consent tried.';
        }
    }

    public function eSignSanctionLetterResponse() {
        $return_status = 0;
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_GET['docId'])) {
            $esign_data = $this->IntegrationModel->geteSignDetailsByDocId($_GET['docId']);

            if ($esign_data['status'] == 1) {
                $lead_id = $esign_data['esign_data']['esign_lead_id'];
            }
        }

        if (!empty($_GET['lead_id']) || !empty($_GET['refstr']) || !empty($lead_id)) {

            $enc_lead_id = "";

            if (!empty($_GET['lead_id'])) {
                $lead_id = intval($_GET['lead_id']);
            } else if (!empty($_GET['refstr'])) {
                $enc_lead_id = $this->encrypt->decode($_GET['refstr']);
                $lead_id = intval($enc_lead_id);
            }

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if ($applicationDetails['lead_status_id'] == 12) {

                        require_once(COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        // $esign_download_return = $CommonComponent->download_esign_document_api($lead_id);

                        $getEsignRequestLogs = $this->db->select('esign_response, esign_provider')->from('api_esign_logs')->where(['esign_lead_id' => $lead_id, 'esign_api_status_id' => 1, 'esign_active' => 1])->get()->result_array();

                        if (empty($getEsignRequestLogs)) {
                            $message = "eSign Request not found.";
                        }

                        foreach ($getEsignRequestLogs as $key => $value) {

                            if (!empty($value['esign_provider']) && $value['esign_provider'] == 1) {
                                $getEsignRequestData = json_decode($value['esign_response'], true);

                                $request_array = array(
                                    "contractId" => $getEsignRequestData['contractId'],
                                    "provider" => 1
                                );

                                $esign_download_return = $CommonComponent->download_esign_document_api($lead_id, $request_array);
                                if ($esign_download_return['status'] == 5) {
                                    break;
                                }
                            }

                            if (!empty($value['esign_provider']) && $value['esign_provider'] == 2) {
                                $request_array = array(
                                    "provider" => 2
                                );

                                $esign_download_return = $CommonComponent->download_esign_document_api($lead_id, $request_array);
                                if ($esign_download_return['status'] == 5) {
                                    break;
                                }
                            }
                        }

                        if ($esign_download_return['status'] == 5) {

                            // if (!empty($esign_download_return['esigned_file_url'])) {

                            //     $file_basename = basename($esign_download_return['esigned_file_url']);

                            //     file_put_contents(TEMP_UPLOAD_PATH . $file_basename, file_get_contents($esign_download_return['esigned_file_url']));

                            //     $tmp_file_ext = pathinfo(TEMP_UPLOAD_PATH . $file_basename, PATHINFO_EXTENSION);

                            //     $upload_esign = uploadDocument(TEMP_UPLOAD_PATH . $file_basename, $lead_id, 2, $tmp_file_ext);

                            //     if ($upload_esign['status'] == 1) {
                            //         $this->IntegrationModel->update('credit_analysis_memo', ['lead_id' => $lead_id], ['cam_sanction_letter_esgin_file_name' => $upload_esign['file_name'], 'cam_sanction_letter_esgin_on' => date("Y-m-d H:i:s")]);
                            //         unlink(TEMP_UPLOAD_PATH . $file_basename);
                            //     } else {
                            //         $message = "eSign File not uploaded.";
                            //     }
                            // } else {
                            //     $message = "eSign File not generated.";
                            // }

                            $loanDataReturnArr = $this->IntegrationModel->getLeadLoanDetails($lead_id);

                            if ($loanDataReturnArr['status'] === 1) {

                                $loanDetails = $loanDataReturnArr['loan_data'];
                                $email = $applicationDetails['email'];
                                $loan_id = $loanDetails['loan_id'];

                                if (empty($loanDetails['loanAgreementResponse'])) {

                                    $status = 'DISBURSAL-NEW';
                                    $stage = 'S20';
                                    $lead_status_id = 25;

                                    $dataLoan = [
                                        "status" => $status,
                                        "loan_status_id" => $lead_status_id,
                                        "loanAgreementResponse" => 1,
                                        "mail" => $email,
                                        "agrementUserIP" => $_SERVER['REMOTE_ADDR'],
                                        "agrementResponseDate" => date("Y-m-d H:i:s"),
                                    ];

                                    $conditions = ['loan_id' => $loan_id];

                                    $result = $this->db->where($conditions)->update('loan', $dataLoan);

                                    if ($result) {

                                        $dataLeads = [
                                            'status' => $status,
                                            'stage' => $stage,
                                            'lead_status_id' => $lead_status_id,
                                            'updated_on' => date("Y-m-d H:i:s")
                                        ];

                                        $conditions = ['lead_id' => $lead_id];

                                        $result = $this->db->where($conditions)->update('leads', $dataLeads);
                                        if ($result) {

                                            $lead_followup_insert_array = [
                                                'lead_id' => $lead_id,
                                                'customer_id' => $applicationDetails['customer_id'],
                                                'user_id' => $user_id,
                                                'status' => $status,
                                                'stage' => $stage,
                                                'lead_followup_status_id' => $lead_status_id,
                                                'remarks' => "Sanction letter acceptance given by customer",
                                                'created_on' => date("Y-m-d H:i:s")
                                            ];

                                            $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                                            $return_status = 1;
                                            $message = 'You have successfully eSigned the Sanction Letter. We will get back to you soon.';
                                        } else {
                                            $message = "Unable to update lead details of application.";
                                        }
                                    } else {
                                        $message = "Unable to update loan details of application.";
                                    }
                                } else {
                                    $message = "Application has been already accepted and move to next step.";
                                }
                            } else {
                                $message = "Unable to find loan details of application.";
                            }
                        } else {
                            $message = $esign_download_return['errors'];
                        }
                    } else if ($applicationDetails['lead_status_id'] == 25) {
                        $return_status = 1;
                        $message = 'You have successfully eSigned the Sanction Letter. We will get back to you soon..';
                    } else {
                        $message = "Application has been move to next step..";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        if ($return_status == 1) {
            $message = $this->thank_you_html($message);
        } else {
            $message = $this->error_page_html($message);
        }
        echo $message;
        exit;
    }

    public function loanAgreementLetterResponse() {
        $return_status = 0;
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_GET['lead_id']) || !empty($_GET['refstr'])) {
            $enc_lead_id = "";
            if (!empty($_GET['lead_id'])) {
                $enc_lead_id = $_GET['lead_id'];
            } else if (!empty($_GET['refstr'])) {
                $enc_lead_id = $_GET['refstr'];
            }

            $lead_id = intval($this->encrypt->decode($enc_lead_id));

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];
                    //                    echo "<pre>";
                    //                    print_r($applicationDetails);die;

                    $email = $applicationDetails['email'];

                    if ($applicationDetails['lead_status_id'] == 12) {

                        $loanDataReturnArr = $this->IntegrationModel->getLeadLoanDetails($lead_id);

                        if ($loanDataReturnArr['status'] === 1) {

                            $loanDetails = $loanDataReturnArr['loan_data'];

                            $loan_id = $loanDetails['loan_id'];

                            if (empty($loanDetails['loanAgreementResponse'])) {

                                $status = 'DISBURSAL-NEW';
                                $stage = 'S20';
                                $lead_status_id = 25;

                                $dataLoan = [
                                    "status" => $status,
                                    "loan_status_id" => $lead_status_id,
                                    "loanAgreementResponse" => 1,
                                    "mail" => $email,
                                    "agrementUserIP" => $_SERVER['REMOTE_ADDR'],
                                    "agrementResponseDate" => date("Y-m-d H:i:s"),
                                ];

                                $conditions = ['loan_id' => $loan_id];

                                $result = $this->db->where($conditions)->update('loan', $dataLoan);

                                if ($result) {

                                    $dataLeads = [
                                        'status' => $status,
                                        'stage' => $stage,
                                        'lead_status_id' => $lead_status_id,
                                        'updated_on' => date("Y-m-d H:i:s")
                                    ];

                                    $conditions = ['lead_id' => $lead_id];

                                    $result = $this->db->where($conditions)->update('leads', $dataLeads);
                                    if ($result) {

                                        $lead_followup_insert_array = [
                                            'lead_id' => $lead_id,
                                            'customer_id' => $applicationDetails['customer_id'],
                                            'user_id' => $user_id,
                                            'status' => $status,
                                            'stage' => $stage,
                                            'lead_followup_status_id' => $lead_status_id,
                                            'remarks' => "Sanction letter acceptance given by user.",
                                            'created_on' => date("Y-m-d H:i:s")
                                        ];

                                        $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                                        $return_status = 1;
                                        $message = '<p style="text-align : center;"><img src="' . WEBSITE_URL . '"public/front/images/thumb.PNG" style=" width: 400px; height: 200px;" alt="thumb"></p>
                            <p style="text-align : center;">Thanks For Your Response.</p>';
                                    } else {
                                        $message = "Unable to update lead details of application.";
                                    }
                                } else {
                                    $message = "Unable to update loan details of application.";
                                }
                            } else {
                                $message = "Application has been already accepted and move to next step.";
                            }
                        } else {
                            $message = "Unable to find loan details of application.";
                        }
                    } else {
                        $message = "Application has been move to next step";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        echo $message;
    }

    public function digilockerRequest() {
        $message = "";
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_GET['lead_id']) || !empty($_GET['refstr'])) {

            $enc_lead_id = "";

            if (!empty($_GET['lead_id'])) {
                $lead_id = $_GET['lead_id'];
                $enc_lead_id = $this->encrypt->encode($lead_id);
            } else if (!empty($_GET['refstr'])) {
                $enc_lead_id = $_GET['refstr'];
                $lead_id = $this->encrypt->decode($enc_lead_id);
            }

            // if (!empty($_GET['redirect_flag'])) {
            //     $request_array['redirect_url'] = WEBSITE_URL . 'verify-digilocker-ekyc?refstr=' . $enc_lead_id;
            //     if ($_GET['redirect_flag'] == 3) {
            //         $request_array['redirect_url'] = WEBSITE_URL . 'verify-ekyc?refstr=' . $enc_lead_id;
            //     }
            // }
            // echo $request_array['redirect_url'];
            // exit;

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if (in_array($applicationDetails['lead_status_id'], array(1, 4, 5, 6, 11, 42, 41)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) { // APPLICATION-NEW, APPLICATION-INPROCESS and APPLICATION-HOLD,APPLICATION-SEND-BACK
                        $lead_followup_insert_array = [
                            'lead_id' => $lead_id,
                            'customer_id' => $applicationDetails['customer_id'],
                            'user_id' => $user_id,
                            'status' => $applicationDetails['status'],
                            'stage' => $applicationDetails['stage'],
                            'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                            'remarks' => "Digilocker request initiated",
                            'created_on' => date("Y-m-d H:i:s")
                        ];

                        $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                        require_once(COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $aadhaar_return = $CommonComponent->call_aadhaar_verification_request_api($lead_id, $request_array);

                        if ($aadhaar_return['status'] == 1) {
                            $message = $this->ekyc_GET_html($aadhaar_return['digilocker_url']);
                        } else {
                            $message = '<p style="text-align : center;">Aadhaar Verification Process...</p>';
                            $aadhaar_error = $aadhaar_return['errors'];
                            $message .= '<p style="text-align : center;">Message : ' . $aadhaar_error . '</p>';
                        }
                    } else {
                        $message = "Application has been move to next step.";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        echo $message;
    }

    public function digilockerResponse() {

        $return_status = 0;
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;
        $redirect_url = NULL;

        if (!empty($_GET['lead_id']) || !empty($_GET['refstr'])) {

            $enc_lead_id = "";

            if (!empty($_GET['lead_id'])) {
                $lead_id = $_GET['lead_id'];
            } else if (!empty($_GET['refstr'])) {
                $enc_lead_id = $_GET['refstr'];
                $lead_id = $enc_lead_id;
            }

            // if (!empty($_GET['redirect_flag']) && $_GET['redirect_flag'] == 3) {
            //     $redirect_url = WEBSITE_URL . 'loan-application';
            // }

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if (in_array($applicationDetails['lead_status_id'], array(1, 4, 5, 6, 11, 42, 41)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) {
                        if ($_GET['status'] == "success") {

                            require_once(COMPONENT_PATH . 'CommonComponent.php');

                            $CommonComponent = new CommonComponent();

                            $aadhaar_return = $CommonComponent->call_aadhaar_verification_response_api($lead_id);

                            if ($aadhaar_return['status'] == 1) {
                                $lead_followup_insert_array = [
                                    'lead_id' => $lead_id,
                                    'customer_id' => $applicationDetails['customer_id'],
                                    'user_id' => $user_id,
                                    'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                                    'remarks' => "Digilocker acceptance given by customer",
                                    'created_on' => date("Y-m-d H:i:s")
                                ];

                                $result = $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                                if ($result) {
                                    $return_status = 1;
                                    $message = 'You have successfully done the E-KYC.';
                                } else {
                                    $message = "Unable to update lead details of application.";
                                }
                            } else {
                                $message = $aadhaar_return['errors'];
                            }
                        } else {
                            $lead_followup_insert_array = [
                                'lead_id' => $lead_id,
                                'customer_id' => $applicationDetails['customer_id'],
                                'user_id' => $user_id,
                                'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                                'remarks' => "Digilocker verification failed.",
                                'created_on' => date("Y-m-d H:i:s")
                            ];

                            $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);
                            $message = "Digilocker verification failed. Please contact the sanction executive.";
                        }
                    } else {
                        $message = "Application has been move to next step..";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        if ($return_status == 1) {
            $message = $this->thank_you_html($message, $redirect_url);
        } else {
            $message = $this->error_page_html($message, $redirect_url);
        }
        echo $message;
        exit;
    }

    private function thank_you_html($tag_line, $redirect_url = null) {

        if (empty($redirect_url)) {
            $return_button = '';
        } else {
            $return_button = '';
        }

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Thank You</title>
                        <link rel="stylesheet" href="' . WEBSITE_URL . 'public/css/bootstrap.min.css?v=1.9"/>
                    </head>

                    <body style="background: #00455E">
                    <style>
                    .ekyc_thnk {position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;width: 600px;height: 310px;padding: 20px;border:
                        solid 1px #fa6900;background: #fff;border-radius: 20px;box-shadow: 0 0 13px #fa6900;text-align: center;}
                    .we-cant {font-size: 20px;font-weight: normal;color: #525252;line-height: 25px;}
                    .error {color: #525252;font-weight: bold; font-size: 27px;margin: 30px 0px 21px 0px;}.back-to-home-page {background: #fa6900;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}.back-to-home-page:hover {background: #fa6900;
                        color: #fff;text-decoration: blink;}.error-page-marging {margin-top: 80px;text-align: center;}.follow-us {
                        font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}

                    @media all and (max-width: 320px),(max-width: 375px),(max-width: 384px),(max-width: 414px),(max-device-width: 450px),(max-device-width: 480px),(max-device-width: 540px) {
                        .ekyc_thnk {
                            position: relative;
                            top: 0;
                            bottom: 0;
                            left: 0;
                            right: 0;
                            margin: 50% auto;
                            width: 100%;
                            height: auto;
                            padding: 25px 20px;
                            border: solid 1px #fa6900;
                            background: #fff;
                            border-radius: 20px;
                            box-shadow: 0 0 13px #fa6900;
                            text-align: center;
                            float: left;
                        }
                        .ekyc_thnk p>img{
                            width: 65% !important;
                            margin-top: 5%;
                        }
                        .oops {font-size: 63px;
                            padding: 3% 0; font-weight: 900;color: #fa6900;margin: 0px;}
                            .we-cant { font-size: 27px;
                                font-weight: bold;
                                color: #00334b;
                                line-height: 47px;}
                                .error {    color: #00334b;
                                    font-weight: bold;
                                    font-size: 27px;
                                    margin: 10px 0px 20px 0px;
                                    width: 100%;
                                    float: left;}
                        .back-to-home-page {    background: #fa6900;
                            color: #fff;
                            padding: 20px;
                            border-radius: 3px;
                            font-weight: 500;
                            font-size: 26px !important;
                            margin: 6% 0 0 0;
                            position: relative;
                            text-transform: uppercase;
                            border-radius: 22px;
                            width: 100%;
                            float: left;}
                        .error-page-marging {margin-top: 0px;}
                    }
                    </style>
                     <div class="container">
                        <div class="ekyc_thnk">
                        <p>
                            <img
                            src="' . LMS_BRAND_LOGO . '"
                            alt="thanks"
                            style="border-bottom: dotted 1px #b31c43; padding-bottom: 10px;width: 200px;"/>
                        </p>
                        <div class="oops">Thank You!</div>
                        <div class="we-cant">' . $tag_line . '</div><br/><br/>
                        ' . $return_button . '
                        </div>
                        </div>
                    </body>
                    </html>';

        return $html;
    }

    private function error_page_html($tag_line, $redirect_url = null) {

        if (empty($redirect_url)) {
            $return_button = '<a href="' . WEBSITE_URL . '" class="back-to-home-page">Back to Home Page</a>';
        } else {
            $return_button = '<a href="' . $redirect_url . '" class="back-to-home-page">Please continue the journey</a>';
        }

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Thank You</title>

                        <link rel="stylesheet" href="' . WEBSITE_URL . 'public/css/bootstrap.min.css?v=1.9"/>

                    </head>

                    <body style="background: #fa6900">
                        <style>
                        .ekyc_thnk {position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;width: 600px;height: 310px;padding: 20px;border:
                            solid 1px #fa6900;background: #fff;border-radius: 20px;box-shadow: 0 0 13px #fa6900;text-align: center;}
                            .oops {font-size: 40px;font-weight: bold;color: #fa6900;}
                        .we-cant {font-size: 17px;font-weight: normal;color: #525252;line-height: 25px;}
                        .error {color: #525252;font-weight: bold;font-size: 17px;margin: 30px 0px 21px 0px;}.back-to-home-page {background: #fa6900;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}.back-to-home-page:hover {background: #fa6900;
                            color: #fff;text-decoration: blink;}.error-page-marging {margin-top: 80px;text-align: center;}.follow-us {
                            font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}

                        @media all and (max-width: 320px),(max-width: 375px),(max-width: 384px),(max-width: 414px),(max-device-width: 450px),(max-device-width: 480px),(max-device-width: 540px) {
                            .ekyc_thnk {
                                position: relative;
                                top: 0;
                                bottom: 0;
                                left: 0;
                                right: 0;
                                margin: 50% auto;
                                width: 100%;
                                height: auto;
                                padding: 25px 20px;
                                border: solid 1px #fa6900;
                                background: #fff;
                                border-radius: 20px;
                                box-shadow: 0 0 13px #fa6900;
                                text-align: center;
                                float: left;
                            }
                            .ekyc_thnk p>img{
                                width: 65% !important;
                                margin-top: 5%;
                            }
                            .oops {font-size: 63px;
                                padding: 3% 0; font-weight: 900;color: #fa6900;margin: 0px;}
                                .we-cant {    font-size: 27px;
                                    font-weight: bold;
                                    color: #00334b;
                                    line-height: 47px;}
                            .error {    color: #00334b;
                                font-weight: bold;
                                font-size: 27px;
                                margin: 10px 0px 20px 0px;
                                width: 100%;
                                float: left;}
                            .back-to-home-page {    background: #d42452;
                                color: #fff;
                                padding: 20px;
                                border-radius: 3px;
                                font-weight: 500;
                                font-size: 26px !important;
                                margin: 6% 0 0 0;
                                position: relative;
                                text-transform: uppercase;
                                border-radius: 22px;
                                width: 100%;
                                float: left;}
                            .error-page-marging {margin-top: 0px;}
                        }
                        </style>

                        <div class="ekyc_thnk">
                        <p>
                            <img
                            src="' . LMS_BRAND_LOGO . '"
                            alt="thanks"
                            style="border-bottom: dotted 1px #b31c43; padding-bottom: 10px;width: 200px;"/>
                        </p>
                        <div class="oops">Oops!!! Error Occurred.</div>
                        <div class="we-cant">' . $tag_line . '</div><br/><br/>
                        ' . $return_button . '
                        </div>
                    </body>
                    </html>';

        return $html;
    }

    private function ekyc_GET_html($url) {

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>DIGITAL EKYC</title>
                        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
                    </head>
                    <body>
                        <style type="text/css">
                            .div {position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;width:600px;height:380px;padding: 20px;border: solid 1px #d1dee6;background: #e2f2fc;border-radius: 3px;box-shadow: 0 0 13px #d1dee6;text-align:center;}
                            .oops{font-size:40px;font-weight: bold;color: #fa6900;}
                            .we-cant{font-size: 20px;font-weight: normal;color: #00334b;line-height: 25px;}
                            .error{color: #00334b;font-weight: bold;font-size: 17px;margin: 30px 0px 21px 0px;}
                            .back-to-home-page{background: #fa6900;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}
                            .back-to-home-page:hover{background:#fa6900;color: #fff;text-decoration:blink}
                            .error-page-marging{margin-top:80px;text-align:center;}
                            .follow-us{font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}

                            @media all and (max-width:320px), (max-width:375px), (max-width:384px), (max-width:414px), (max-device-width:450px), (max-device-width:480px), (max-device-width:540px) {
                                .oops{font-size:30px;font-weight: 900;color: #fa6900;margin:0px;}
                                .we-cant{font-size:8px;font-weight: bold;color: #00334b;line-height:13px;}
                                .error{color: #00334b;font-weight: bold;font-size: 9px;margin: 10px 0px 20px 0px;}
                                .back-to-home-page{background: #fa6900;color: #fff;padding: 11px 11px;border-radius: 3px;font-weight: bold;font-size: 9px !important;}
                                .error-page-marging{margin-top:0px;}
                            }
                        </style>
                        <div class="div">';

        $message .= '<h3 style="text-align : center;">AADHAAR E-KYC PROCESS</h3>';
        $message .= '<p style="text-align : left;">Please keep the below points in mind : </p>';
        $message .= '<p style="text-align : left;">1. Please wait, you will be redirect to DigiLocker for Aadhaar Verification.</p>';
        $message .= '<p style="text-align : left;">2. If you are not able to redirect to DigiLocker Portal, Please connect with Sanction Executive.</p>';
        $message .= '<p style="text-align : left;">3. Only three times request is allowed.</p>';
        $message .= '<p style="text-align : left;">4. When you do the successfully Aadhaar Verification on DigiLocker, You will be redirect to our portal again in 10 second.</p>';
        $message .= '<p style="text-align : center;">Please <a href="' . $url . '">click here</a> if you are not able to redirect to DigiLocker Portal.</p>';
        $message .= '</div>';
        $message .= '<script type="text/javascript">';
        $message .= 'window.location = "' . $url . '"';
        $message .= '</script>';
        $message .= '</body>';
        $message .= '</html>';
        return $message;
    }

    public function completeESign() {
        $return_status = 0;
        $getEsignRequestData = array();
        $esign_download_return = array();
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_GET['lead_id']) || !empty($user_id)) {

            $lead_id = intval($_GET['lead_id']);

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if ($applicationDetails['lead_status_id'] == 12) {

                        require_once(COMPONENT_PATH . 'CommonComponent.php');
                        $CommonComponent = new CommonComponent();

                        $getEsignRequestLogs = $this->db->select('esign_response, esign_provider')->from('api_esign_logs')->where(['esign_lead_id' => $lead_id, 'esign_api_status_id' => 1, 'esign_active' => 1])->get()->result_array();

                        if (empty($getEsignRequestLogs)) {
                            $message = "eSign Request not found.";
                        }

                        foreach ($getEsignRequestLogs as $key => $value) {

                            if (!empty($value['esign_provider']) && $value['esign_provider'] == 1) {
                                $getEsignRequestData = json_decode($value['esign_response'], true);

                                $request_array = array(
                                    "contractId" => $getEsignRequestData['contractId'],
                                    "provider" => 1
                                );

                                $esign_download_return = $CommonComponent->download_esign_document_api($lead_id, $request_array);
                                if ($esign_download_return['status'] == 5) {
                                    break;
                                }
                            }

                            if (!empty($value['esign_provider']) && $value['esign_provider'] == 2) {
                                $request_array = array(
                                    "provider" => 2
                                );

                                $esign_download_return = $CommonComponent->download_esign_document_api($lead_id, $request_array);
                                if ($esign_download_return['status'] == 5) {
                                    break;
                                }
                            }
                        }

                        if ($esign_download_return['status'] == 5) {

                            $loanDataReturnArr = $this->IntegrationModel->getLeadLoanDetails($lead_id);

                            if ($loanDataReturnArr['status'] === 1) {

                                $loanDetails = $loanDataReturnArr['loan_data'];
                                $email = $applicationDetails['email'];
                                $loan_id = $loanDetails['loan_id'];

                                if (empty($loanDetails['loanAgreementResponse'])) {

                                    $status = 'DISBURSAL-NEW';
                                    $stage = 'S20';
                                    $lead_status_id = 25;

                                    $dataLoan = [
                                        "status" => $status,
                                        "loan_status_id" => $lead_status_id,
                                        "loanAgreementResponse" => 1,
                                        "mail" => $email,
                                        "agrementUserIP" => $_SERVER['REMOTE_ADDR'],
                                        "agrementResponseDate" => date("Y-m-d H:i:s"),
                                    ];

                                    $conditions = ['loan_id' => $loan_id];

                                    $result = $this->db->where($conditions)->update('loan', $dataLoan);

                                    if ($result) {

                                        $dataLeads = [
                                            'status' => $status,
                                            'stage' => $stage,
                                            'lead_status_id' => $lead_status_id,
                                            'updated_on' => date("Y-m-d H:i:s")
                                        ];

                                        $conditions = ['lead_id' => $lead_id];

                                        $result = $this->db->where($conditions)->update('leads', $dataLeads);
                                        if ($result) {

                                            $lead_followup_insert_array = [
                                                'lead_id' => $lead_id,
                                                'customer_id' => $applicationDetails['customer_id'],
                                                'user_id' => $user_id,
                                                'status' => $status,
                                                'stage' => $stage,
                                                'lead_followup_status_id' => $lead_status_id,
                                                'remarks' => "Sanction letter acceptance given by customer",
                                                'created_on' => date("Y-m-d H:i:s")
                                            ];

                                            $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                                            $return_status = 1;
                                            $message = 'You have successfully eSigned the Sanction Letter. We will get back to you soon.';
                                        } else {
                                            $message = "Unable to update lead details of application.";
                                        }
                                    } else {
                                        $message = "Unable to update loan details of application.";
                                    }
                                } else {
                                    $message = "Application has been already accepted and move to next step.";
                                }
                            } else {
                                $message = "Unable to find loan details of application.";
                            }
                        } else {
                            $message = $esign_download_return['errors'];
                        }
                    } else if ($applicationDetails['lead_status_id'] == 25) {
                        $return_status = 1;
                        $message = 'You have successfully eSigned the Sanction Letter. We will get back to you soon..';
                    } else {
                        $message = "Application has been move to next step..";
                    }
                } else {
                    $message = "Application does not exist.";
                }
            } else {
                $message = "Unable to decript application reference";
            }
        } else {
            $message = "Missing application reference.";
        }

        if ($return_status == 1) {
            $message = $this->thank_you_html($message);
        } else {
            $message = $this->error_page_html($message);
        }
        echo $message;
        exit;
    }

    public function digitapView() {
        $lead_id = $this->input->get('refstr', TRUE);
        $lead_id = !empty($lead_id) ? $this->encrypt->decode($lead_id) : NULL;

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (empty($lead_id)) {
            $this->session->set_flashdata('err', 'Lead ID is missing.');
            echo $this->digitapErrorHtml('Application Number Missing. Please try again.');
            return;
        }

        $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

        if (isset($appDataReturnArr['status']) && $appDataReturnArr['status'] === 1) {
            $applicationDetails = $appDataReturnArr['app_data'];
        } else {
            $this->session->set_flashdata('err', 'Lead not found.');
            echo $this->digitapErrorHtml();
            return;
        }

        // $lead_followup_insert_array = [
        //     'lead_id' => $lead_id,
        //     'customer_id' => $applicationDetails['customer_id'],
        //     'user_id' => $user_id,
        //     'lead_followup_status_id' => $applicationDetails['lead_status_id'],
        //     'remarks' => "Digilocker initiated by customer",
        //     'created_on' => date("Y-m-d H:i:s")
        // ];

        // $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

        if (in_array($applicationDetails['lead_status_id'], array(1, 4, 5, 6, 11, 42, 41)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) {
            echo $this->digitapRequestHtml($applicationDetails);
            return;
        } else {
            echo $this->digitapErrorHtml();
            return;
        }
    }

    public function verifyEkycDigitap() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(['err' => 'Invalid request method.']);
            return;
        }

        $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
        $this->form_validation->set_rules('aadhaar_no', 'Aadhaar Number', 'required|trim|numeric|exact_length[12]');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['err' => validation_errors()]);
            return;
        }

        $lead_id = $this->input->post('lead_id', TRUE);
        $aadhaar_no = $this->input->post('aadhaar_no', TRUE);

        try {

            if (empty($lead_id) || empty($aadhaar_no)) {
                echo $this->digitapErrorHtml("Aadhaar number are required.");
                return;
            }

            $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;
            $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

            if (isset($appDataReturnArr['status']) && $appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];
            } else {
                $this->session->set_flashdata('err', 'Lead not found.');
                echo $this->digitapErrorHtml();
                return;
            }

            $lead_followup_insert_array = [
                'lead_id' => $lead_id,
                'customer_id' => $applicationDetails['customer_id'],
                'user_id' => $user_id,
                'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                'remarks' => "Digilocker Requested by customer: Aadhaar No: "  . substr($aadhaar_no, -4),
                'created_on' => date("Y-m-d H:i:s")
            ];

            $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $response = $CommonComponent->call_digitap_ekyc_api_call('DIGITAP_EKYC_CREATE_OTP', $lead_id, [
                'aadhaar_no' => $aadhaar_no
            ]);

            if (isset($response['status']) && $response['status'] == 1) {
                echo json_encode(['success' => 'OTP sent to your Aadhaar-registered mobile.']);
            } else {
                $errorMessage = $response['message'] ?? 'Error processing eKYC!';
                echo json_encode(['err' => $errorMessage]);
            }
        } catch (Exception $e) {
            echo json_encode(['err' => 'Internal server error: ' . $e->getMessage()]);
        }
    }

    public function ekyc_otp_verify() {
        $lead_id = $this->input->post('lead_id', TRUE);
        $otp = $this->input->post('otp', TRUE);

        if (empty($lead_id) || empty($otp)) {
            echo json_encode(['err' => 'Lead ID and OTP are required.']);
            return;
        }

        try {
            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $response = $CommonComponent->call_digitap_ekyc_api_call('DIGITAP_EKYC_SUCCESS', $lead_id, ['otp' => $otp]);

            if (isset($response['status']) && $response['status'] == 1) {

                $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;
                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if (isset($appDataReturnArr['status']) && $appDataReturnArr['status'] === 1) {
                    $applicationDetails = $appDataReturnArr['app_data'];
                } else {
                    $this->session->set_flashdata('err', 'Lead not found.');
                    echo $this->digitapErrorHtml();
                    return;
                }

                $lead_followup_insert_array = [
                    'lead_id' => $lead_id,
                    'customer_id' => $applicationDetails['customer_id'],
                    'user_id' => $user_id,
                    'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                    'remarks' => "Digilocker acceptance given by customer",
                    'created_on' => date("Y-m-d H:i:s")
                ];

                $this->IntegrationModel->insert('lead_followup', $lead_followup_insert_array);

                echo json_encode(['success' => 'OTP verified successfully.']);
            } else {
                // echo $this->digitapErrorHtml($response['message'] ?? 'Error verifying OTP!');
                echo json_encode(['err' => $response['message'] ?? 'Error verifying OTP!']);
                return;
            }
        } catch (Exception $e) {
            echo json_encode(['err' => 'Internal server error: ' . $e->getMessage()]);
        }
    }

    public function digitapDirectUrl() {

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if ($user_id == NULL) {
            echo $this->digitapErrorHtml('Unauthorized access.');
            return;
        }

        if (!empty($_GET['lead_id'])  && $user_id != NULL) {

            try {
                $lead_id = $_GET['lead_id'];
                $otp = 1234;

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if (isset($appDataReturnArr['status']) && $appDataReturnArr['status'] === 1) {
                    $applicationDetails = $appDataReturnArr['app_data'];
                } else {
                    $this->session->set_flashdata('err', 'Lead not found.');
                    echo $this->digitapErrorHtml();
                    return;
                }

                if (in_array($applicationDetails['lead_status_id'], array(1, 4, 5, 6, 11, 42, 41)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) {
                    require_once(COMPONENT_PATH . 'CommonComponent.php');
                    $CommonComponent = new CommonComponent();

                    $response = $CommonComponent->call_digitap_ekyc_api_call('DIGITAP_EKYC_SUCCESS', $lead_id, ['otp' => $otp]);

                    if (isset($response['status']) && $response['status'] == 1) {
                        echo json_encode(['success' => 'Successfully Completed.', 'message' => $response['message']]);
                    } else {
                        $errorMessage = $response['message'] ?? 'Error verifying OTP!';
                        echo json_encode(['err' => $errorMessage]);
                    }
                } else {
                    $errorMessage = $response['message'] ?? 'Error verifying OTP!';
                    echo $this->digitapErrorHtml($errorMessage);
                    return;
                }
            } catch (Exception $e) {
                echo json_encode(['err' => 'Internal server error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['err' => 'Lead ID are required.']);
        }
    }

    private function digitapRequestHtml($data) {
        $csrf = [
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        ];

        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Aadhaar Verification</title>
            <link rel="stylesheet preload" href="<?= base_url('public'); ?>/css/style.css">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #fa6900;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .container {
                    background-color: #fff;
                    border-radius: 20px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                    width: 506px;
                    max-width: 90%;
                    margin: auto;
                    padding: 30px;
                    text-align: center;
                }

                .container h1 {
                    font-size: 24px;
                    font-weight: bold;
                    color: #fa6900;
                    margin-bottom: 15px;
                }

                .container p {
                    font-size: 16px;
                    color: #333;
                    margin-bottom: 25px;
                }

                .container input {
                    background-color: #eee;
                    border: none;
                    margin: 10px 0;
                    padding: 10px 15px;
                    font-size: 14px;
                    border-radius: 8px;
                    width: 80%;
                    outline: none;
                }

                .container button,
                .container a {
                    background-color: #fa6900;
                    color: #fff;
                    font-size: 14px;
                    padding: 10px 30px;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    text-transform: uppercase;
                    cursor: pointer;
                    margin-top: 15px;
                    transition: background-color 0.3s ease;
                    text-decoration: none;
                    display: inline-block;
                }

                .container button:hover,
                .container a:hover {
                    background-color: #fa6900;
                }

                .thank-you-container {
                    display: none;
                }

                .tick-icon-container {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    background-color: #28a745;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 15px auto;
                }

                .tick-icon {
                    font-size: 50px;
                    color: #fff;
                }

                .err {
                    color: red !important;
                    font-weight: bold;
                }
            </style>
        </head>

        <body>
            <!-- Aadhaar Form -->
            <div class="container" id="aadhaarForm">
                <div class="logo_container">
                    <a href="<?= WEBSITE_URL; ?>" target="_blank">
                        <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                        <img src="hhttps://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="logo" style="width: 50%; background:#fff;">
                    </a>
                </div>
                <h1>Fill Aadhaar Number</h1>
                <p>Please enter your Aadhaar number to proceed.</p>
                <p id="err_msg" class="err"></p>
                <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                <input type="hidden" name="lead_id" id="lead_id" value="<?= $data['lead_id'] ?>">
                <input type="text" name="aadhaar_no" id="aadhaar_no" placeholder="Enter Aadhaar Number" title="Aadhaar Number" required style="text-align: center;" onpaste="return false;">
                <button type="button" id="request_otp">Get OTP</button>
            </div>

            <!-- OTP Form -->
            <div class="container" id="otpForm" style="display: none;">
                <div class="logo_container">
                    <a href="<?= WEBSITE_URL; ?>" target="_blank">
                        <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                        <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="logo" style="width: 50%; background:#fff;">
                    </a>
                </div>
                <p id="seccess_msg" style="color: green; font-weight: bold;"></p>
                <p id="err_otp_msg" style="color: red; font-weight: bold;"></p>
                <h1>Enter OTP</h1>
                <p>Please enter the OTP sent to your Aadhaar-registered mobile.</p>
                <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                <input type="hidden" name="lead_id" value="<?= $data['lead_id'] ?>">
                <input type="text" name="otp" id="otp" placeholder="Enter OTP" title="OTP" required style="text-align: center;">
                <button type="button" id="submit_otp">Submit OTP</button>
            </div>

            <!-- Thank You Section -->
            <div class="container thank-you-container" id="thankYouSection">
                <div class="tick-icon-container">
                    <div class="tick-icon">✔</div>
                </div>
                <div class="logo_container">
                    <a href="<?= WEBSITE_URL; ?>" target="_blank">
                        <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                        <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="logo" style="width: 50%; background:#fff">
                    </a>
                </div>
                <h1>Thank You!</h1>
                <p>Your Aadhaar verification was successful. You can now proceed with the next steps.</p>
                <a href="<?= WEBSITE_URL; ?>" target="_blank">Go to Homepage</a>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <script>
                $('#request_otp').prop('disabled', true).css('cursor', 'not-allowed').css('background-color', '#ccc');
                $("#aadhaar_no").on("input", function() {
                    var value = $(this).val().replace(/\D/g, '');
                    if (value.length > 12) {
                        value = value.slice(0, 12);
                    }
                    $(this).val(value);

                    if (value.length === 12) {
                        $('#request_otp').prop('disabled', false).removeAttr('style');
                    } else {
                        $('#request_otp').prop('disabled', true).css('cursor', 'not-allowed').css('background-color', '#ccc');
                    }
                });

                $(document).ready(function() {
                    $('#request_otp').prop('disabled', true);
                });

                $("#otp").on("input", function() {
                    var value = $(this).val().replace(/\D/g, '');
                    if (value.length > 6) {
                        value = value.slice(0, 6);
                    }
                    $(this).val(value);
                });

                $(document).ready(function() {
                    const csrf_token_name = "<?= $this->security->get_csrf_token_name(); ?>";
                    const csrf_token_value = "<?= $this->security->get_csrf_hash(); ?>";

                    $('#request_otp').click(function() {
                        const aadhaar_no = $('#aadhaar_no').val();
                        const lead_id = $('#lead_id').val();

                        if (!aadhaar_no) {
                            alert("Please enter your Aadhaar number.");
                            return;
                        }

                        $.ajax({
                            url: "<?= base_url('verifyEkycDigitap') ?>",
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                lead_id: lead_id,
                                aadhaar_no: aadhaar_no,
                                [csrf_token_name]: csrf_token_value
                            },
                            beforeSend: function() {
                                $('#request_otp').prop('disabled', true).text('Processing...');
                            },
                            success: function(response) {
                                if (response.err) {
                                    $('#seccess_msg').hide();
                                    $('#err_msg').html(response.err).addClass('err');
                                } else {
                                    $('#aadhaarForm').hide();
                                    $('#otpForm').show();
                                    $('#seccess_msg').text(response.success);
                                }
                            },
                            complete: function() {
                                $('#request_otp').prop('disabled', false).text('Get OTP');
                            }
                        });
                    });

                    $('#submit_otp').click(function() {
                        const otp = $('#otp').val();
                        const lead_id = $('#lead_id').val();

                        if (!otp) {
                            alert("Please enter the OTP.");
                            return;
                        }

                        $.ajax({
                            url: "<?= base_url('ekyc_otp_verify') ?>",
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                lead_id: lead_id,
                                otp: otp,
                                [csrf_token_name]: csrf_token_value
                            },
                            beforeSend: function() {
                                $('#submit_otp').prop('disabled', true).text('Verifying...');
                            },
                            success: function(response) {
                                if (response.err) {
                                    $('#seccess_msg').hide();
                                    $('#err_otp_msg').text(response.err);
                                } else {
                                    $('#otpForm').hide();
                                    $('#thankYouSection').show();
                                }
                            },
                            complete: function() {
                                $('#submit_otp').prop('disabled', false).text('Submit OTP');
                            }
                        });
                    });
                });
            </script>
        </body>

        </html>
    <?php
        return ob_get_clean();
    }

    private function digitapErrorHtml($content = null) {
        ob_start();
    ?>

        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Error</title>
            <link rel="stylesheet preload" href="<?= base_url('public'); ?>/css/style.css">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #fa6900;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .container {
                    background-color: #fff;
                    border-radius: 20px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                    width: 506px;
                    max-width: 90%;
                    margin: auto;
                    padding: 30px;
                    text-align: center;
                }

                .container h1 {
                    font-size: 24px;
                    font-weight: bold;
                    color: #d9534f;
                    margin-bottom: 15px;
                }

                .container p {
                    font-size: 16px;
                    color: #333;
                    margin-bottom: 25px;
                }

                .container a {
                    background-color: #fa6900;
                    color: #fff;
                    font-size: 14px;
                    padding: 10px 30px;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    text-transform: uppercase;
                    cursor: pointer;
                    margin-top: 15px;
                    text-decoration: none;
                    display: inline-block;
                    transition: background-color 0.3s ease;
                }

                .container a:hover {
                    background-color: #fa6900;
                }

                .error-icon-container {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    background-color: #d9534f;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 15px auto;
                }

                .error-icon {
                    font-size: 50px;
                    color: #fff;
                }
            </style>
        </head>

        <body>
            <div class="container">

                <div class="logo_container">
                    <a href="<?= WEBSITE_URL; ?>" target="_blank">
                        <!-- <img src="https://sot-website.s3.ap-south-1.amazonaws.com/upload/company_logo.png" alt="logo" style="width: 50%; margin-bottom: 20px;"> -->
                        <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="logo" style="width: 50%; background:#fff">
                    </a>
                </div>
                <div class="error-icon-container">
                    <div class="error-icon">✖</div>
                </div>
                <h1>Error Occurred</h1>
                <?php if (!empty($content)) : ?>
                    <p style="color:red !important; font-size:bold;"><?= $content; ?></p>
                    <!-- <p>Sorry, something went wrong. Please try again or contact support.</p> -->
                <?php endif; ?>
                <a href="<?= WEBSITE_URL; ?>" target="_blank">Go to Homepage</a>
            </div>
        </body>

        </html>

<?php
        return ob_get_clean();
    }
}
