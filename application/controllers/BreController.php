<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BreController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Bre_Model', 'BRE');
        $this->load->model('Docs_Model', 'Docs');
        $login = new IsLogin();
        $login->index();
    }

    public function error_page() {
        $this->load->view('errors/index');
    }

    public function gernerateBreResult() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }


        $lead_id = intval($this->encrypt->decode($_POST['enc_lead_id']));

        if (empty($lead_id)) {
            $json['err'] = "Missing Lead Id.";
            echo json_encode($json);
            return false;
        }


        $conditions['LD.lead_id'] = $lead_id;

        $leadData = $this->Tasks->getLeadDetails($conditions);

        $leadDetails = $leadData->row_array();

        if (empty($leadDetails)) {
            $json['err'] = "Missing Lead Details.";
            echo json_encode($json);
            return false;
        }

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        // OCR and Black List Check
        if (!empty($lead_id)) { // && empty($leadDetails['lead_screener_assign_user_id'])
            // print_r($leadDetails);
            // die;
            // if (agent != "CR1") {
            //     $json['err'] = "You are not authrized to take this action.[U01]";
            //     echo json_encode($json);
            //     return false;
            // }

            $pan_query = $this->db->query("SELECT * FROM `api_poi_verification_logs` WHERE poi_veri_lead_id=" . $lead_id . " AND poi_veri_api_status_id=1 ORDER BY `poi_veri_id` DESC LIMIT 1");
            $pan_Data = $pan_query->row_array();
            $pan_Details = json_decode($pan_Data['poi_veri_response'], true);


            // $update_data_lead_customer = array();

            if (empty($leadDetails)) {
                $json['err'] = "Lead details does not exist.[L01]";
                echo json_encode($json);
                return false;
            }

            if (empty($leadDetails['pancard'])) {
                $json['err'] = "PAN is not available. Please check pan no.[S03]";
                echo json_encode($json);
                return false;
            }

            // if (strtoupper($leadDetails['first_name']) != strtoupper($pan_Details['result']['firstName'])) {
            //     $json['err'] = "First name does not matched with PAN Card[S03]";
            //     echo json_encode($json);
            //     return false;
            // }

            $isBlackListed = $this->Tasks->checkBlackListedCustomer($lead_id);

            if ($isBlackListed['status'] == 1) {
                $json['err'] = $isBlackListed['error_msg'];
                echo json_encode($json);
                return false;
            }

            if (empty($leadDetails['aadhar_no'])) {
                $json['err'] = "Aadhaar last 4 digit is not available. Please check aadhaar no.[S04]";
                echo json_encode($json);
                return false;
            }

            // $pancard = $leadDetails['pancard'];
            // $aadhar = $leadDetails['aadhar_no'];
            // $lead_data_source_id = $leadDetails['lead_data_source_id'];

            $conditions = ['lead_id' => $lead_id];
            $remark = '';

            //production
            if (ENVIRONMENT == 'production') {

                $docs_data = $CommonComponent->check_customer_mandatory_documents($lead_id);

                if (empty($docs_data['status'])) {
                    $json['err'] = $docs_data['error'];
                    echo json_encode($json);
                    return false;
                }

                $pan_validate_status = 0;
                $pan_ocr_status = 0;
                $aadhaar_ocr_status = 0;

                if (!empty($leadDetails['email']) && $leadDetails['email_verified_status'] != "YES") {
                    $email_return = $CommonComponent->call_email_verification_api($lead_id);

                    if ($email_return['status'] == 1 && $email_return['email_validate_status'] == 1) {
                        $lead_remark .= "<br/>Email Verified";
                    }
                } else if (!empty($leadDetails['email']) && $leadDetails['email_verified_status'] == "YES") {
                    $lead_remark .= "<br/>Email Verified";
                }

                // $pan_veri_return = $CommonComponent->call_pan_verification_api($lead_id);


                // if ($pan_veri_return['status'] == 1) {

                //     if ($pan_veri_return['pan_valid_status'] == 2) {

                //         $pan_validate_status = 2;
                //         $lead_remark .= "<br/>PAN Verified";
                //     } else {
                //         $json['err'] = "Customer Name does not matched with PAN Detail. Please check the application log.";
                //         echo json_encode($json);
                //         return false;
                //     }
                // } else {

                //     $json['err'] = trim($pan_veri_return['errors']);
                //     echo json_encode($json);
                //     return false;
                // }
            } else if ($leadDetails['pancard_verified_status'] == 2) {
                $pan_validate_status = 2;
                $lead_remark .= "<br/>PAN Verified";
            }

            $panDocsDetails = $this->Docs->getLeadDocumentWithTypeDetails($lead_id, 4);

            if (($leadDetails['user_type'] != "REPEAT" || $panDocsDetails['status'] == 1) && $leadDetails['pancard_ocr_verified_status'] != 1) {
                // require_once(COMPONENT_PATH . 'CommonComponent.php');

                // $CommonComponent = new CommonComponent();

                $pan_ocr_return = $CommonComponent->call_pan_ocr_api($lead_id);

                if ($pan_ocr_return['status'] == 1) {

                    if ($pan_ocr_return['pan_valid_status'] == 1) {
                        $pan_ocr_status = 1;
                        $lead_remark .= "<br/>PAN OCR Verified";
                    } else {
                        $pan_ocr_status = 1;
                        $json['err'] = "Customer PAN does not matched with PAN OCR Detail. Please check the application log.";
                        echo json_encode($json);
                        return false;
                    }
                } else {
                    $pan_ocr_status = 1;
                    $json['err'] = trim($pan_ocr_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else {
                $pan_ocr_status = 1;
            }

            if ($pan_validate_status != 1 && $pan_ocr_status != 1) {
                $json['err'] = "Something went wrong. Please contact to IT Team.";
                echo json_encode($json);
                return false;
            }


            $aadhaarDocsDetails = $this->Docs->getLeadDocumentWithTypeDetails($lead_id, "1,2");

            if (($leadDetails['user_type'] != "REPEAT" || $aadhaarDocsDetails['status'] == 1) && $leadDetails['aadhaar_ocr_verified_status'] != 1) {


                $aadhaar_ocr_return = $CommonComponent->call_aadhaar_ocr_api($lead_id);

                if ($aadhaar_ocr_return['status'] == 1) {

                    if ($aadhaar_ocr_return['aadhaar_valid_status'] == 1) {
                        $aadhaar_ocr_status = 1;
                        $lead_remark .= "<br/>Aadhaar OCR Verified";
                    } else {
                        $json['err'] = "Customer Aadhaar does not matched with Aadhaar OCR Detail. Please check the application log.";
                        echo json_encode($json);
                        return false;
                    }
                } else {
                    $json['err'] = trim($aadhaar_ocr_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else {
                $aadhaar_ocr_status = 1;
            }

            if ($aadhaar_ocr_status != 1) {
                $json['err'] = "Something went wrong. Please contact to IT Team.";
                echo json_encode($json);
                return false;
            }

            if ($leadDetails['user_type'] == "REPEAT") {
                $update_lead_customer = array();

                $update_lead_customer['pancard_ocr_verified_status'] = 1;
                $update_lead_customer['pancard_ocr_verified_on'] = date("Y-m-d H:i:s");

                $update_lead_customer['aadhaar_ocr_verified_status'] = 1;
                $update_lead_customer['aadhaar_ocr_verified_on'] = date("Y-m-d H:i:s");

                $update_lead_customer['customer_digital_ekyc_flag'] = 1;
                $update_lead_customer['customer_digital_ekyc_done_on'] = date("Y-m-d H:i:s");
                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_lead_customer);
            }
        }

        // OCR and Black List Check

        $return_bre_response = $CommonComponent->call_bre_rule_engine($lead_id);

        $json['msg'] = "BRE Run successfully";
        $json['bre_response'] = $return_bre_response;
        echo json_encode($json);
        return;
    }

    public function getBreRuleResult() {
        $json = array();
        $lead_id = intval($this->encrypt->decode($_POST['enc_lead_id']));
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        } else if (empty($lead_id)) {
            $json['err'] = "Missing Lead Id.";
            echo json_encode($json);
            return false;
        } else {

            $breRuleResult = $this->BRE->getBreAllRuleResult($lead_id);

            $data = array();
            $data['master_bre_category'] = $this->BRE->getMasterBreCategory();
            $data['bre_rule_result'] = $breRuleResult['bre_rule_result'];

            if (empty($breRuleResult['bre_rule_result'])) {
                $json['rule_result_flag'] = 1;
                $json['rule_result_html'] = "<p>No Rule Result Found</p>";
            } else {
                $json['rule_result_flag'] = 1;
                $json['rule_result_html'] = $this->load->view('Bre/bre_rule_result', $data, TRUE);
            }

            echo json_encode($json);
        }
    }

    public function saveBreManualDecision() {
        $json = array();

        $lead_id = intval($this->encrypt->decode($_POST['enc_lead_id']));

        $trans_rule_id = !empty($_POST['trans_rule_id']) ? $_POST['trans_rule_id'] : "";
        $deviation_decision = !empty($_POST['deviation_decision']) ? intval($_POST['deviation_decision']) : "";
        $deviation_remark = !empty($_POST['deviation_remark']) ? addslashes(trim($_POST['deviation_remark'])) : "";

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        } else if (empty($lead_id)) {
            $json['err'] = "Missing Lead Id.";
            echo json_encode($json);
            return false;
        } else if (empty($trans_rule_id)) {
            $json['err'] = "Missing Rule Id.";
            echo json_encode($json);
            return false;
        } else if (empty($deviation_decision)) {
            $json['err'] = "Missing Deviation Decision";
            echo json_encode($json);
            return false;
        } else if (empty($deviation_remark)) {
            $json['err'] = "Missing Deviation Decision Remarks";
            echo json_encode($json);
            return false;
        } else if (!empty($deviation_remark) && strlen($deviation_remark) > 500) {
            $json['err'] = "Deviation Decision Remarks should be less then 500 chars.";
            echo json_encode($json);
            return false;
        } else {

            $breRuleResult = $this->BRE->getBreAllRuleResult($lead_id, $trans_rule_id);

            if (empty($breRuleResult['bre_rule_result'])) {
                $json['err'] = "Rule id details does not exist.";
                echo json_encode($json);
                return false;
            }

            $flag = $this->BRE->update("lead_bre_rule_result", ["lbrr_id" => $trans_rule_id], ["lbrr_rule_manual_decision_id" => $deviation_decision, "lbrr_rule_manual_decision_remarks" => $deviation_remark]);

            if ($flag) {
                $json['rule_result_flag'] = 1;
            } else {
                $json['err'] = "Some error occurred  during rule descision update.";
            }


            echo json_encode($json);
        }
    }

    public function breEditApplication() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        $lead_id = intval($this->encrypt->decode($_POST['enc_lead_id']));

        if (empty($lead_id)) {
            $json['errSession'] = "Invalid Lead Id";
            echo json_encode($json);
            return false;
        }

        $table = "lead_customer";

        $select = "customer_bre_run_flag";

        $conditions['customer_lead_id'] = $lead_id;

        $get_customer_bre_run_flag = $this->BRE->select($conditions, $select, $table);

        $customer_bre_run_flag = $get_customer_bre_run_flag->row_array();

        if ($customer_bre_run_flag['customer_bre_run_flag'] == 1) {
            $update['customer_bre_run_flag'] = 0;
            $update['customer_bre_run_datetime'] = date("Y-m-d H:i:s");
            $this->BRE->update($table, $conditions, $update);
            $json['msg'] = 1;
        }
        echo json_encode($json);
    }

    public function __destruct() {
        $this->db->close();
    }
}
