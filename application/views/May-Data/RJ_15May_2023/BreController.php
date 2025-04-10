<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class BreController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Bre_Model', 'BRE');
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


        $lead_id = $this->encrypt->decode($_POST['enc_lead_id']);

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


        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $return_bre_response = $CommonComponent->call_bre_rule_engine($lead_id);

        $json['msg'] = "BRE Run successfully";
        $json['bre_response'] = $return_bre_response;
        echo json_encode($json);
        return;
    }

    public function getBreRuleResult() {
        $json = array();
        $lead_id = $this->encrypt->decode($_POST['enc_lead_id']);
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

        $lead_id = $this->encrypt->decode($_POST['enc_lead_id']);

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

        $lead_id = $this->encrypt->decode($_POST['enc_lead_id']);

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
