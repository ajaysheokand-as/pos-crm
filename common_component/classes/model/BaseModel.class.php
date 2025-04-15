<?php

class BaseModel {

    public $path = null;
    public $context = null;

    public function __construct() {

        $this->path = COMP_PATH;
    }

    protected function connectDatabase() {
        require_once($this->path . "/classes/model/DatabaseConn.class.php");

        $this->context = new DatabaseConn();
    }

    public function insertApplicationLog($lead_id, $lead_status_id, $remark) {

        if (empty($lead_id) || empty($lead_status_id) || empty($remark)) {
            return null;
        }

        $user_id = 0;

        if (isset($_SESSION['isUserSession']['user_id']) && !empty($_SESSION['isUserSession']['user_id'])) {
            $user_id = $_SESSION['isUserSession']['user_id'];
        }

        $insert_log_array = array();
        $insert_log_array['lead_id'] = $lead_id;
        $insert_log_array['user_id'] = $user_id;
        $insert_log_array['lead_followup_status_id'] = $lead_status_id;
        $insert_log_array['remarks'] = addslashes($remark);
        $insert_log_array['created_on'] = date("Y-m-d H:i:s");

        return $this->context->insert('lead_followup', $insert_log_array);
    }

    public function insertTable($table, $insert_array) {

        if (empty($table) || empty($insert_array)) {
            return null;
        }

        return $this->context->insert($table, $insert_array);
    }

    public function updateTable($table, $update_array, $where) {

        if (empty($table) || empty($update_array) || empty($where)) {
            return null;
        }

        return $this->context->update($table, $update_array, $where);
    }

    public function updateLeadTable($lead_id, $update_array) {
        if (empty($lead_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('leads', $update_array, " lead_id=$lead_id");
    }

    public function updateLeadCustomerTable($lead_id, $update_array) {

        if (empty($lead_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('lead_customer', $update_array, " customer_lead_id=$lead_id");
    }

    public function updateCAMTable($lead_id, $update_array) {
        if (empty($lead_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('credit_analysis_memo', $update_array, " lead_id=$lead_id");
    }

    public function updateDocsTable($lead_id, $doc_id, $update_array) {

        if (empty($lead_id) || empty($doc_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('docs', $update_array, " lead_id=$lead_id AND docs_id=$doc_id ");
    }

    public function updateLoanTable($lead_id, $update_array) {

        if (empty($lead_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('loan', $update_array, " lead_id=$lead_id");
    }

    public function __destruct() {
        if (is_object($this->context)) {
            $this->context->close();
        }
    }

}

?>
