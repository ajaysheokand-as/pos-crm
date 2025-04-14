<?php

defined('BASEPATH') OR exit('No direct script access allowed');

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

        if (!empty($_REQUEST['refstr'])) {
            if (!isset($_REQUEST['consent']) || empty($_REQUEST['consent'])) {
                $refstr = $_REQUEST['refstr'];
                redirect('sanction-esign-consent?refstr=' . $refstr);
            }
        }
        $return_status = 0;
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL;

        if (!empty($_REQUEST['refstr'])) {

            $enc_lead_id = $_REQUEST['refstr'];

            $lead_id = intval($this->encrypt->decode($enc_lead_id));

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

                        require_once (COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $esign_return = $CommonComponent->call_esign_api($lead_id);

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

        if (!empty($_REQUEST['refstr'])) {

            $refstr = $_REQUEST['refstr'];
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

        if (!empty($_REQUEST['lead_id']) || !empty($_REQUEST['refstr'])) {

            $enc_lead_id = "";

            if (!empty($_REQUEST['lead_id'])) {
                $lead_id = intval($_REQUEST['lead_id']);
            } else if (!empty($_REQUEST['refstr'])) {
                $enc_lead_id = $_REQUEST['refstr'];
                $lead_id = intval($enc_lead_id);
            }

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if ($applicationDetails['lead_status_id'] == 12) {

                        require_once (COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $esign_download_return = $CommonComponent->download_esign_document_api($lead_id);

                        if ($esign_download_return['status'] == 5) {

                            if (!empty($esign_download_return['esigned_file_url'])) {
                                //$this->load->helper('commonfun');

                                $file_basename = basename($esign_download_return['esigned_file_url']);

                                file_put_contents(UPLOAD_PATH . $file_basename, file_get_contents($esign_download_return['esigned_file_url']));

                                $tmp_file_ext = pathinfo(UPLOAD_PATH . $file_basename, PATHINFO_EXTENSION);

                                $upload_esign = uploadDocument(UPLOAD_PATH . $file_basename, $lead_id, 2, $tmp_file_ext);
                                
                                if ($upload_esign['status'] == 1) {
                                    $this->IntegrationModel->update('credit_analysis_memo', ['lead_id' => $lead_id], ['cam_sanction_letter_esgin_file_name' => $upload_esign['file_name'], 'cam_sanction_letter_esgin_on' => date("Y-m-d H:i:s")]);
                                    unlink(UPLOAD_PATH . $file_basename);
                                } else {
                                    $message = "eSign File not uploaded.";
                                }
                            } else {
                                $message = "eSign File not generated.";
                            }

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

        if (!empty($_REQUEST['lead_id']) || !empty($_REQUEST['refstr'])) {
            $enc_lead_id = "";
            if (!empty($_REQUEST['lead_id'])) {
                $enc_lead_id = $_REQUEST['lead_id'];
            } else if (!empty($_REQUEST['refstr'])) {
                $enc_lead_id = $_REQUEST['refstr'];
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
                                        $message = '<p style="text-align : center;"><img src="' . WEBSITE_URL . '"public/front/images/thumb.PNG" style=" width: 400px; height: 300px;" alt="thumb"></p>
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

        if (!empty($_REQUEST['lead_id']) || !empty($_REQUEST['refstr'])) {

            $enc_lead_id = "";

            if (!empty($_REQUEST['lead_id'])) {
                $lead_id = intval($this->encrypt->decode($_REQUEST['lead_id']));
            } else if (!empty($_REQUEST['refstr'])) {
                $enc_lead_id = $_REQUEST['refstr'];
                $lead_id = intval($this->encrypt->decode($enc_lead_id));
            }

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if (in_array($applicationDetails['lead_status_id'], array(1, 4, 5, 6, 11)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) {// APPLICATION-NEW, APPLICATION-INPROCESS and APPLICATION-HOLD,APPLICATION-SEND-BACK
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

                        require_once (COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $aadhaar_return = $CommonComponent->call_aadhaar_verification_request_api($lead_id);

                        if ($aadhaar_return['status'] == 1) {
                            $message = $this->ekyc_request_html($aadhaar_return['digilocker_url']);
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

        if (!empty($_REQUEST['lead_id']) || !empty($_REQUEST['refstr'])) {

            $enc_lead_id = "";

            if (!empty($_REQUEST['lead_id'])) {
                $lead_id = intval($_REQUEST['lead_id']);
            } else if (!empty($_REQUEST['refstr'])) {
                $enc_lead_id = $_REQUEST['refstr'];
                $lead_id = intval($enc_lead_id);
            }

            if (!empty($lead_id)) {

                $appDataReturnArr = $this->IntegrationModel->getLeadDetails($lead_id);

                if ($appDataReturnArr['status'] === 1) {

                    $applicationDetails = $appDataReturnArr['app_data'];

                    if (in_array($applicationDetails['lead_status_id'], array(4, 5, 6, 11)) || $applicationDetails['customer_digital_ekyc_flag'] == 2) {// APPLICATION-NEW, APPLICATION-INPROCESS and APPLICATION-HOLD, APPLICATION-SEND-BACK
                        if ($_REQUEST['status'] == "success") {


                            require_once (COMPONENT_PATH . 'CommonComponent.php');

                            $CommonComponent = new CommonComponent();

                            $aadhaar_return = $CommonComponent->call_aadhaar_verification_response_api($lead_id);

                            if ($aadhaar_return['status'] == 1) {

                                if (!empty($aadhaar_return['aadhaar_photo'])) {

                                    $generated_file_name = "DIGILOCKER_PHOTO_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";

                                    $file_write_flag = file_put_contents(UPLOAD_PATH . $generated_file_name, file_get_contents($aadhaar_return['aadhaar_photo']));

                                    if ($file_write_flag) {

                                        $tmp_file_ext = pathinfo(UPLOAD_PATH . $generated_file_name, PATHINFO_EXTENSION);

                                        $upload_file = uploadDocument(UPLOAD_PATH . $generated_file_name, $lead_id, 2, $tmp_file_ext);

                                        if ($upload_file['status'] == 1) {

                                            $document_insert_array = array();
                                            $document_insert_array["lead_id"] = $lead_id;
                                            $document_insert_array["customer_id"] = NULL;
                                            $document_insert_array["pancard"] = $applicationDetails['pancard'];
                                            $document_insert_array["mobile"] = $applicationDetails['mobile'];
                                            $document_insert_array["file"] = $upload_file['file_name'];
                                            $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                                            $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                                            $document_insert_array["upload_by"] = $user_id;
                                            $document_insert_array["docs_master_id"] = 19;
                                            $document_insert_array["docs_type"] = "DIGILOCKER";
                                            $document_insert_array["sub_docs_type"] = "AADHAAR PHOTO";

                                            $this->IntegrationModel->insert("docs", $document_insert_array);

                                            unlink(UPLOAD_PATH . $generated_file_name);
                                        }
                                    }
                                }
                                if (!empty($aadhaar_return['digilocker_files'])) {
                                    if(!empty($aadhaar_return['fetch_document_array'])){
                                        $fetch_document_array = $aadhaar_return['fetch_document_array'];
                                    }
                                    foreach ($aadhaar_return['digilocker_files'] as $document_array) {

                                        if (!empty($fetch_document_array[$document_array['doctype']])) {

                                            if (!empty($document_array["id"]) && $document_array["id"] == $fetch_document_array[$document_array['doctype']] && !empty($document_array["file"]['pdf'])) {

                                                $generated_file_name = "DIGILOCKER_" . $document_array['doctype'] . "_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".pdf";

                                                $file_write_flag = file_put_contents(UPLOAD_PATH . $generated_file_name, file_get_contents($document_array["file"]['pdf']));

                                                if ($file_write_flag) {

                                                    $tmp_file_ext = pathinfo(UPLOAD_PATH . $generated_file_name, PATHINFO_EXTENSION);

                                                    $upload_file = uploadDocument(UPLOAD_PATH . $generated_file_name, $lead_id, 2, $tmp_file_ext);

                                                    if($upload_file['status'] == 1) {

                                                    $document_insert_array = array();
                                                    $document_insert_array["lead_id"] = $lead_id;
                                                    $document_insert_array["customer_id"] = NULL;
                                                    $document_insert_array["pancard"] = $applicationDetails['pancard'];
                                                    $document_insert_array["mobile"] = $applicationDetails['mobile'];
                                                    $document_insert_array["file"] = $upload_file['file_name'];
                                                    $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                                                    $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                                                    $document_insert_array["upload_by"] = $user_id;

                                                    if ($document_array['doctype'] == "ADHAR") {
                                                        $document_insert_array["docs_master_id"] = 20;
                                                        $document_insert_array["docs_type"] = "DIGILOCKER";
                                                        $document_insert_array["sub_docs_type"] = "AADHAAR CARD";
                                                    } else if ($document_array['doctype'] == "PANCR") {
                                                        $document_insert_array["docs_master_id"] = 21;
                                                        $document_insert_array["docs_type"] = "DIGILOCKER";
                                                        $document_insert_array["sub_docs_type"] = "PAN CARD";
                                                    } else if ($document_array['doctype'] == "DRVLC") {
                                                        $document_insert_array["docs_master_id"] = 23;
                                                        $document_insert_array["docs_type"] = "DIGILOCKER";
                                                        $document_insert_array["sub_docs_type"] = "DRIVING LICENCE";
                                                    }

                                                    $this->IntegrationModel->insert("docs", $document_insert_array);
                                                    unlink(UPLOAD_PATH . $generated_file_name);
                                                }
                                                }
                                            }
                                        }
                                    }
                                }

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
            $message = $this->thank_you_html($message);
        } else {
            $message = $this->error_page_html($message);
        }
        echo $message;
        exit;
    }

    private function thank_you_html($tag_line) {

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Thank You</title>
                        <link
                        rel="stylesheet"
                        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>
                    </head>
                    
                    <body style="background: #d42452">
                    <style>
                    .div {position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;width: 600px;height: 380px;padding: 20px;border: solid 1px #d42452;background: #fff;border-radius: 20px;box-shadow: 0 0 13px #a71037;text-align: center;}.oops {font-size: 40px;font-weight: bold;color: #d42452;}
                    .we-cant {font-size: 20px;font-weight: normal;color: #525252;line-height: 25px;}
                    .error {color: #525252;font-weight: bold;font-size: 17px;margin: 30px 0px 21px 0px;}.back-to-home-page {background: #d42452;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}.back-to-home-page:hover {background: #9f1b3d;
                        color: #fff;text-decoration: blink;}.error-page-marging {margin-top: 80px;text-align: center;}.follow-us {
                        font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}
                    
                    @media all and (max-width: 320px),(max-width: 375px),(max-width: 384px),(max-width: 414px),(max-device-width: 450px),(max-device-width: 480px),(max-device-width: 540px) {
                        .oops {font-size: 30px;font-weight: 900;color: #0068a5;margin: 0px;}.we-cant {
                        font-size: 8px;font-weight: bold;color: #00334b;line-height: 13px;}.error {
                        color: #00334b;font-weight: bold;font-size: 9px;margin: 10px 0px 20px 0px;}.back-to-home-page {
                        background: #0068a5;color: #fff;padding: 11px 11px;border-radius: 3px;font-weight: bold;font-size: 9px !important;}.error-page-marging {margin-top: 0px;}
                    }
                    </style>
                    
                        <div class="div">
                        <p>
                            <img
                            src="' . LMS_BRAND_LOGO . '"
                            alt="thanks"
                            style="border-bottom: dotted 1px #b31c43; padding-bottom: 10px"/>
                        </p>
                        <div class="oops">Thank You!</div>
                        <div class="we-cant">' . $tag_line . '</div>
                        <div class="error">If you have any issues <a href="' . WEBSITE_URL . 'contact-us"
                            style="text-decoration: underline; color: #000">contact us.</a>
                        </div>
                        <a href="' . WEBSITE_URL . '" class="back-to-home-page">Back to Home Page</a>
                        </div>
                    </body>
                    </html>';

        return $html;
    }

    private function error_page_html($tag_line) {

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Thank You</title>
                        <link
                        rel="stylesheet"
                        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>
                    </head>
                    
                    <body style="background: #d42452">
                        <style>
                        .div {position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;width: 600px;height: 380px;padding: 20px;border: solid 1px #d42452;background: #fff;border-radius: 20px;box-shadow: 0 0 13px #a71037;text-align: center;}.oops {font-size: 40px;font-weight: bold;color: #d42452;}
                        .we-cant {font-size: 17px;font-weight: normal;color: #525252;line-height: 25px;}
                        .error {color: #525252;font-weight: bold;font-size: 17px;margin: 30px 0px 21px 0px;}.back-to-home-page {background: #d42452;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}.back-to-home-page:hover {background: #9f1b3d;
                            color: #fff;text-decoration: blink;}.error-page-marging {margin-top: 80px;text-align: center;}.follow-us {
                            font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}
                        
                        @media all and (max-width: 320px),(max-width: 375px),(max-width: 384px),(max-width: 414px),(max-device-width: 450px),(max-device-width: 480px),(max-device-width: 540px) {
                            .oops {font-size: 30px;font-weight: 900;color: #0068a5;margin: 0px;}.we-cant {
                            font-size: 17px;font-weight: bold;color: #00334b;line-height: 13px;}.error {
                            color: #00334b;font-weight: bold;font-size: 9px;margin: 10px 0px 20px 0px;}.back-to-home-page {
                            background: #0068a5;color: #fff;padding: 11px 11px;border-radius: 3px;font-weight: bold;font-size: 9px !important;}.error-page-marging {margin-top: 0px;}
                        }
                        </style>
                    
                        <div class="div">
                        <p>
                            <img
                            src="' . LMS_BRAND_LOGO . '"
                            alt="thanks"
                            style="border-bottom: dotted 1px #b31c43; padding-bottom: 10px"/>
                        </p>
                        <div class="oops">Oops!!! Error Occurred.</div>
                        <div class="we-cant">' . $tag_line . '</div>
                        <div class="error">If you have any issues <a href="' . WEBSITE_URL . 'contact-us"
                            style="text-decoration: underline; color: #000">contact us.</a>
                        </div>
                        <a href="' . WEBSITE_URL . '" class="back-to-home-page">Back to Home Page</a>
                        </div>
                    </body>
                    </html>';

        return $html;
    }

    private function ekyc_request_html($url) {

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
                            .oops{font-size:40px;font-weight: bold;color: #0068a5;}
                            .we-cant{font-size: 20px;font-weight: normal;color: #00334b;line-height: 25px;}
                            .error{color: #00334b;font-weight: bold;font-size: 17px;margin: 30px 0px 21px 0px;}
                            .back-to-home-page{background: #0068a5;color: #fff;padding: 15px 20px;border-radius: 3px;font-weight: bold;}
                            .back-to-home-page:hover{background:#0068a5;color: #fff;text-decoration:blink}
                            .error-page-marging{margin-top:80px;text-align:center;}
                            .follow-us{font-weight: bold;color: #0363a3;margin-top: 32px;line-height: 38px;}

                            @media all and (max-width:320px), (max-width:375px), (max-width:384px), (max-width:414px), (max-device-width:450px), (max-device-width:480px), (max-device-width:540px) {
                                .oops{font-size:30px;font-weight: 900;color: #0068a5;margin:0px;}
                                .we-cant{font-size:8px;font-weight: bold;color: #00334b;line-height:13px;}
                                .error{color: #00334b;font-weight: bold;font-size: 9px;margin: 10px 0px 20px 0px;}
                                .back-to-home-page{background: #0068a5;color: #fff;padding: 11px 11px;border-radius: 3px;font-weight: bold;font-size: 9px !important;}
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

}

?>
