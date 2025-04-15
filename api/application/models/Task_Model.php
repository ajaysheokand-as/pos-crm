<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task_Model extends CI_Model {

    function __construct() {

        parent::__construct();

        define("ip", $this->input->ip_address());

        date_default_timezone_set('Asia/Kolkata');

        define("todayDate", date('Y-m-d'));

        define("tableLeads", "leads");

        define("currentDate", date('Y-m-d'));

        define("created_at", date('Y-m-d H:i:s'));

        define("updated_at", date('Y-m-d H:i:s'));

        define("server", $_SERVER['SERVER_NAME']);

        define("localhost", "public/images/");

        define("live", base_url() . "upload/");

        /////////// define role ///////////////////////////////////////



        define('screener', "SANCTION QUICKCALLER");

        define('creditManager', "Sanction & Telecaller");

        define('headCreditManager', "Sanction Head");

        define('admin', "Client Admin");

        define('teamDisbursal', "Disbursal");

        define('teamClosure', "Account and MIS");

        define('teamCollection', "Collection");
    }

    private $table = 'leads';

    public function selectdata($conditions = null, $data = null, $table = null) {

        return $this->db->select($data)->where($conditions)->from($table)->get();
    }

    public function insert($table = null, $data = null) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($conditions = null, $table = null, $data = null) {
        return $this->db->where($conditions)->update($table, $data);
    }

    public function generateApplicationNo($lead_id) {
        $application_no = 0;
        if (!empty($lead_id)) {
            $conditions2 = ['lead_id' => $lead_id, 'application_no !=' => ''];
            $leadsDetails = $this->selectdata($conditions2, 'lead_id, application_no', 'leads');
            if ($leadsDetails->num_rows() > 0) {
                $leadApp_no = $leadsDetails->row_array();
                $application_no = $leadApp_no['application_no'];
            } else {
                $conditions2 = ['P.company_id' => company_id, 'P.product_id' => product_id];
                $fetch2 = 'P.product_id, P.product_name, P.product_code';
                $sql2 = $this->selectdata($conditions2, $fetch2, 'tbl_product P');
                $product = $sql2->row();

                $conditions3 = ['LD.lead_id' => $lead_id];
                $sql3 = $this->db->select("LD.city_id, CT.m_city_code")->where($conditions3)->from($this->table . ' LD')->join('master_city CT', 'LD.city_id = CT.m_city_id', 'left')->get();
                $query = $sql3->row();
                $leadsCount = $this->db->select('lead_id')->where('application_no !=', '')->from('leads')->get()->num_rows();
                $num1 = preg_replace('/[^0-9]/', '', $leadsCount) + $this->count;
                $zerocounts = '';
                for ($i = strlen('000000000'); $i > strlen($num1); $i--) {
                    $zerocounts = $zerocounts . '0';
                }
                $number = $zerocounts . '' . $num1;
                $application_no = 'AP' . $product->product_code . '' . $query->m_city_code . '' . $number; // NFPDNRL000000063
            }
        }
        return $application_no;
    }

    public function gettotalleadsCount($table) {

        $sql = "Select count(lead_id) as total from $table where application_no != null and application_no !=''";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {

                return $row['total'];
            }
        } else {

            return "0";
        }
    }

    public function getProductCode($product_id) {

        $sql = "SELECT product_code FROM tbl_product WHERE product_id='$product_id'";

        $data1 = $this->db->query($sql);

        return $data1->result_array();
    }

    public function getAllDataFromPincode($pincode) {

        $sql = "select mc.m_city_id as city_id,mc.m_city_name as city_name,ms.m_state_id as state_id,ms.m_state_name as state_name from master_pincode mp inner join master_city mc on mp.m_pincode_city_id=mc.m_city_id inner join master_state ms on mc.m_city_state_id=ms.m_state_id  where mp.m_pincode_value='$pincode'";

        $data1 = $this->db->query($sql);

        return $data1->result_array();
    }

    public function CheckUserStatus($lead_id) {

        $sql = "SELECT status FROM leads WHERE lead_id='$product_id'";

        $data1 = $this->db->query($sql);

        return $data1->result_array();
    }

    public function generateReferenceCode($lead_id, $first_name, $last_name, $mobile) {

        $code_mix = array($lead_id[rand(0, strlen($lead_id) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $mobile[rand(0, strlen($mobile) - 1)], $mobile[rand(0, strlen($mobile) - 1)]);

        shuffle($code_mix);

        $referenceID = "#SALARY";

        foreach ($code_mix as $each) {

            $referenceID .= $each;
        }

        $referenceID = str_replace(" ", "X", $referenceID);

        $referenceID = strtoupper($referenceID);

        return $referenceID;
    }

    //get single data from db



    public function getcustId($table, $column, $id, $getval) {

        $id = strtoupper($id);

        //'master_state','m_state_id',$sql['cif_residence_state_id'],'m_state_name')
        //echo  "============= SELECT $getval from $table where $column='$id' ";

        $query = $this->db->query("SELECT $getval from $table where $column='$id'  ");

        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {

                //echo "<pre>";print_r($row);

                return $row[$getval];
            }
        } else {

            return "0";
        }
    }

    public function sendOTPForUserRegistrationVerification($data) {

        $mobile = $data['mobile'];

        $otp = $data['otp'];

        $name = !empty($data['name']) ? $data['name'] : "User";

        $message = "Dear Mr/Ms $name,\nYour mobile verification\nOTP is: " . $otp . ".\nPlease don't share it with anyone - LW (Naman Finlease)";

        $username = urlencode("namanfinl");

        $password = urlencode("ASX1@#SD");

        $type = 0;

        $dlr = 1;

        $destination = $mobile;

        $source = "LWAPLY";

        $message = urlencode($message);

        $entityid = 1201159134511282286;

        $tempid = 1207161976462053311;

        $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";

        $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));

        $output = curl_exec($ch);

        curl_close($ch);
    }

    public function sendOTPAppliedSuccessfully($data) {
        $title = $data['title'];
        $name = $data['name'];
        $mobile = $data['mobile'];
        $message = "Dear " . $title . " " . $name . ",\nYour loan application is\nsuccessfully submitted.\nWe will get back to you soon.\n- Loanwalle (Naman Finlease)";
        $username = urlencode("namanfinl");
        $password = urlencode("ASX1@#SD");
        $type = 0;
        $dlr = 1;
        $destination = $mobile;
        $source = "LWALLE";
        $message = urlencode($message);
        $entityid = 1201159134511282286;
        $tempid = 1207161976525243363;
        $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
        $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));

        $output = curl_exec($ch);
        curl_close($ch);
    }

    public function sendOTPAppliedSuccessfully1($data) {
        $title = $data['title'];
        $name = $data['name'];
        $mobile = $data['mobile'];
        $message = "Dear " . $title . " " . $name . ",\nYour loan application is\nsuccessfully submitted.\nWe will get back to you soon.\n- Loanwalle (Naman Finlease)";
        $username = urlencode("namanfinl");
        $password = urlencode("ASX1@#SD");
        $type = 0;
        $dlr = 1;
        $destination = $mobile;
        $source = "LWALLE";
        $message = urlencode($message);
        $entityid = 1201159134511282286;
        $tempid = 1207161976525243363;
        $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
        $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));

        $output = curl_exec($ch);
        curl_close($ch);
    }

    public function common_parse_full_name($full_name = "") {
        $first_name = $middle_name = $last_name = "";
        if (!empty($full_name)) {
            $full_name = preg_replace("!\s+!", " ", $full_name);

            $name_array = explode(" ", $full_name);

            $first_name = $name_array[0];

            for ($i = 1; $i < (count($name_array) - 1); $i++) {
                $middle_name .= " " . $name_array[$i];
            }

            $middle_name = trim($middle_name);
            $last_name = (count($name_array) != 1 && isset($name_array[count($name_array) - 1])) ? $name_array[count($name_array) - 1] : "";
        }
        return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
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

        return $this->db->insert('lead_followup', $insert_log_array);
    }

    public function get_lead_details($lead_id) {
        $result_array = array("status" => 0);

        $conditions['LD.lead_id'] = $lead_id;

        $this->db->select('LD.*, LC.first_name, LC.middle_name, LC.sur_name, L.loan_noc_letter_sent_datetime');
        $this->db->from('leads LD');
        $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id");
        $this->db->join('loan L', "L.lead_id = LD.lead_id");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        } else {
            return [];
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $row = $tempDetails->row_array();
            $result_array['status'] = 1;

            $data['lead_id'] = $this->encrypt->encode($row['lead_id']);
            $data['full_name'] = trim($row['first_name'] . " " . $row['middle_name'] . " " . $row['sur_name']);
            $data['mobile'] = $row['mobile'];
            $data['email'] = $row['email'];
            $data['status'] = $row['status'];
            $data['lead_status_id'] = $row['lead_status_id'];
            $data['feedback_link_sent_on'] = $row['loan_noc_letter_sent_datetime'];
            $data['pancard'] = $row['pancard'];
            $data['pancard'] = $row['user_type'];
            $result_array['data']['lead_details'] = $data;
        }

        $master_feedback_questions = $this->get_master_feedback_questions();
        $result_array['data']['master_feedback_questions'] = $master_feedback_questions['data']['master_feedback_questions'];

        $master_feedback_answers = $this->get_master_feedback_answers();
        $result_array['data']['master_feedback_answers'] = $master_feedback_answers['data']['master_feedback_answers'];

        $customer_feedback = $this->get_customer_feedback_main($lead_id);
        $result_array['data']['customer_feedback'] = $customer_feedback['data']['customer_feedback'];

        return $result_array;
    }

    public function get_cif_customer_details($pancard) {
        $result_array = array("status" => 0);

        $conditions['cif_pancard'] = $pancard;

        $this->db->select('*');
        $this->db->from('cif_customer');

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        } else {
            return [];
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data = $tempDetails->row_array();
            $data['status'] = 1;
            $result_array['data']['cif_customer_details'] = $data;
        }

        return $result_array;
    }

    public function get_master_feedback_questions() {
        $result_array = array("status" => 0);

        $conditions['MFQ.mfq_active'] = 1;
        $conditions['MFQ.mfq_deleted'] = 0;

        $this->db->select('MFQ.mfq_id, MFQ.mfq_question');
        $this->db->from('master_feedback_questions MFQ');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data_array = array();
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $row) {
                $data['question_id'] = $row['mfq_id'];
                $data['question'] = $row['mfq_question'];

                $data_array[] = $data;
            }

            $result_array['data']['master_feedback_questions'] = $data_array;
        }

        return $result_array;
    }

    public function get_master_feedback_answers() {
        $result_array = array("status" => 0);

        $conditions['MFA.mfa_active'] = 1;
        $conditions['MFA.mfa_deleted'] = 0;

        $this->db->select('MFA.mfa_id, MFA.mfa_answer, MFA.mfa_icons');
        $this->db->from('master_feedback_answers MFA');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data_array = array();
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $row) {
                $data['answer_id'] = $row['mfa_id'];
                $data['answer'] = $row['mfa_answer'];
                $data['icons'] = $row['mfa_icons'];

                $data_array[] = $data;
            }

            $result_array['data']['master_feedback_answers'] = $data_array;
        }

        return $result_array;
    }

    public function get_customer_feedback_main($lead_id) {
        $result_array = array("status" => 0);

        $conditions['CFM.cfm_active'] = 1;
        $conditions['CFM.cfm_deleted'] = 0;
        $conditions['CFM.cfm_lead_id'] = $lead_id;

        $this->db->select('CFM.cfm_id, CFM.cfm_lead_id, CFM.cfm_created_on');
        $this->db->from('customer_feedback_main CFM');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $row = $tempDetails->row_array();

            $data['cfm_id'] = $row['cfm_id'];
            $data['lead_id'] = $row['cfm_lead_id'];
            $data['created_on'] = $row['cfm_created_on'];

            $result_array['data']['customer_feedback'] = $data;
        }

        return $result_array;
    }

    public function get_repeat_customer_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('LD.*, LC.first_name, LC.middle_name, LC.sur_name, L.loan_closure_date');
        $this->db->from('leads LD');
        $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id AND LC.customer_active = 1 AND LC.customer_deleted = 0");
        $this->db->join('loan L', 'L.lead_id=LD.lead_id');

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['lead_details'] = $tempDetails->row_array();
        }

        $lead_customer = $this->get_lead_customer_details($lead_id);
        $result_array['data']['lead_customer_details'] = $lead_customer['data']['lead_customer_details'];

        $customer_employment = $this->get_customer_employment_details($lead_id);
        $result_array['data']['customer_employment_details'] = $customer_employment['data']['customer_employment_details'];

        $customer_banking = $this->get_customer_banking_details($lead_id);
        $result_array['data']['customer_banking_details'] = $customer_banking['data']['customer_banking_details'];

        $customer_reference = $this->get_customer_reference_details($lead_id);
        $result_array['data']['customer_reference_details'] = $customer_reference['data']['customer_reference_details'];

        $cam = $this->get_cam_details($lead_id);
        $result_array['data']['cam_details'] = $cam['data']['cam_details'];

        return $result_array;
    }

    public function get_lead_customer_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('LC.*');
        $this->db->from('leads LD');
        $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id AND LC.customer_active = 1 AND LC.customer_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['lead_customer_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_customer_employment_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('CE.*');
        $this->db->from('leads LD');
        $this->db->join('customer_employment CE', "CE.lead_id = LD.lead_id AND CE.emp_active = 1 AND CE.emp_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['customer_employment_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_customer_banking_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;
        $conditions['CB.account_status_id'] = 1;

        $this->db->select('CB.*');
        $this->db->from('leads LD');
        $this->db->join('customer_banking CB', "CB.lead_id = LD.lead_id AND CB.customer_banking_active = 1 AND CB.customer_banking_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['customer_banking_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_cam_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('CAM.*');
        $this->db->from('leads LD');
        $this->db->join('credit_analysis_memo CAM', "CAM.lead_id = LD.lead_id AND CAM.cam_active = 1 AND CAM.cam_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['cam_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_customer_reference_details($lead_id) {
        $result_array = array('status' => 0);

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('LCR.*');
        $this->db->from('leads LD');
        $this->db->join('lead_customer_references LCR', "LCR.lcr_lead_id = LD.lead_id AND LCR.lcr_active = 1 AND LCR.lcr_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['customer_reference_details'] = $tempDetails->result_array();
        }

        return $result_array;
    }

    public function email_appointment_schedule_with_link($lead_id, $email, $name) {

        if (!empty($lead_id)) {
            $encry_id = $this->encrypt->encode($lead_id);

            $email_header = 'Hi ' . $name . ', Schedule Your Appointment | ' . BRAND_NAME;
            $link = WEBSITE_URL . '/appointment-schedule/' . $encry_id;

            $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">

                                <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                    <title>Schedule Your Appointment</title>
                                </head>

                                <body>
                                    <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; border:solid 1px #ddd; font-size:14px; line-height:22px;">
                                        <tr>
                                            <td><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/appoint-sechdule.png" alt="header" width="650" height="474" border="0" usemap="#Map" /></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">
                                                <table width="95%" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td width="59%" align="left">
                                                            <p style="margin:0px 0px 5px 0px; font-size:19px;"><strong>Dear ' . $name . ',</strong></p>
                                                        </td>
                                                        <td width="41%" align="left">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td rowspan="2" align="left" valign="top">
                                                            <p> Thank you for showing interest in ' . BRAND_NAME . '.</p>
                                                            <p>Need a callback from us?</p>
                                                            <p>Schedule an appointment using the below-mentioned link.</p>
                                                            <p><a href="' . $link . '" style="background: #1d61ac;border-radius: 50px;padding: 11px 15px;color: #fff;text-decoration: blink;font-weight: bold;">Schedule Appointment</a></p>
                                                            <p>We will reach out to you at the scheduled date & time, in the meanwhile please keep your documents (KYC, employment) prepared for disbursal within no time.</p>
                                                            <p> <b>Regards</b>,<br/> Team ' . BRAND_NAME . '<br/>
                                                            </p>
                                                        </td>
                                                        <td align="left" valign="top">
                                                            <table width="100%%" border="0" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <td width="24%" align="center" valign="top"><span style="background: #fb6703;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/light_icon.png" width="30" height="30" alt="Inr" /></span></td>
                                                                    <td width="5%">&nbsp;</td>
                                                                    <td width="71%" valign="top">
                                                                        <p style="margin:0px 0px 5px 0px;"><strong>Application Verification</strong></p>
                                                                        <p style="margin:0px; font-size:13px; line-height:17px;">Digital eKyc using Aadhaar. Document Verification.</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" valign="top">&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" valign="top"><span style="background: #ed1651;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/setting_icon.png" width="30" height="30" alt="Inr" /></span></td>
                                                                    <td>&nbsp;</td>
                                                                    <td valign="top">
                                                                        <p style="margin:0px 0px 5px 0px;"><strong>Digital E-Sign</strong></p>
                                                                        <p style="margin:0px; font-size:13px; line-height:17px;">You need to digitally sign our terms &amp; conditions.</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" valign="top">&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" valign="top"><span style="background: #fb6703;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/inr_icon.png" width="30" height="30" alt="Inr" /></span></td>
                                                                    <td>&nbsp;</td>
                                                                    <td valign="top">
                                                                        <p style="margin:0px 0px 5px 0px;"><strong>Get Loan</strong></p>
                                                                        <p style="margin:0px; font-size:13px; line-height:17px;">Once we have verified all the details. The loan amount will be credited to your account.</p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" valign="top">
                                                            <p style="margin:0px 0px 5px 0px;">&nbsp;</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/contact-details.png" alt="footer" width="577" height="76" border="0" usemap="#Map2" /></td>
                                        </tr>
                                        <tr>
                                            <td align="center">&nbsp;</td>
                                        </tr>
                                    </table>
                                    <map name="Map" id="Map">
                                        <area shape="rect" coords="6,7,225,61" href="' . WEBSITE_URL . '" target="_blank" />
                                    </map>

                                    <map name="Map2" id="Map2">
                                        <area shape="rect" coords="16,7,180,29" href="tel:' . REGISTED_MOBILE . '" />
                                        <area shape="rect" coords="187,9,367,32" href="' . WEBSITE_URL . '" target="_blank" />
                                        <area shape="rect" coords="376,9,559,31" href="mailto:' . INFO_EMAIL . '" />
                                        <area shape="rect" coords="82,52,170,72" href="' . APPLE_STORE_LINK . '" target="_blank" />
                                        <area shape="rect" coords="407,50,496,74" href="' . ANDROID_STORE_LINK . '" target="_blank" />
                                        <area shape="circle" coords="228,63,12" href="' . LINKEDIN_LINK . '" target="_blank" />
                                        <area shape="circle" coords="259,63,13" href="' . INSTAGRAM_LINK . '" target="_blank" />
                                        <area shape="circle" coords="288,64,13" href="' . FACEBOOK_LINK . '" target="_blank" />
                                        <area shape="circle" coords="318,62,12" href="' . TWITTER_LINK . '" target="_blank" />
                                        <area shape="circle" coords="348,64,13" href="' . YOUTUBE_LINK . '" target="_blank" />
                                    </map>
                                </body>

                            </html>';

            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();
            $CommonComponent->call_sent_email($email, $email_header, $email_message);
        }
    }

    public function getLoanApplicationDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            $sql = "LD.lead_id, LD.source , LD.status,LD.loan_no, LC.email, LD.mobile, LC.alternate_mobile, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) name,";
            $sql .= " CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard,";
            $sql .= " CAM.loan_recommended, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.roi,  MB.m_branch_name branch,";
            $sql .= " L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_outstanding_amount, L.loan_penalty_outstanding_amount, L.loan_recovery_status_id, L.loan_total_outstanding_amount";

            $this->db->select($sql);
            $this->db->from('leads LD');
            $this->db->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER');
            $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'INNER');
            $this->db->join('loan L', 'LD.lead_id = L.lead_id', 'INNER');
            $this->db->join('master_state MS', 'LD.state_id = MS.m_state_id', 'LEFT');
            $this->db->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT');
            $this->db->join('master_branch MB', 'LD.lead_branch_id = MB.m_branch_id', 'LEFT');
            $this->db->where(['LD.lead_id' => $lead_id]);
            $tempDetails = $this->db->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function get_master_collection_followup_status($status_id = "", $status_name = "") {

        $result_array = array("status" => 0);

        $conditions = array();
        $conditions['m_followup_status_active'] = 1;
        $conditions['m_followup_status_deleted'] = 0;

        if (!empty($status_id)) {
            $conditions['m_followup_status_id'] = $status_id;
        }

        if (!empty($status_name)) {
            $conditions['m_followup_status_name'] = stripslashes($status_name);
        }

        $this->db->select('m_followup_status_id, m_followup_status_name');
        $this->db->from('master_followup_status');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data_array = array();

            foreach ($tempDetails->result_array() as $row) {
                $data_array[$row['m_followup_status_id']] = $row['m_followup_status_name'];
            }

            $result_array['status'] = 1;
            $result_array['data'] = $data_array;
        }

        return $result_array;
    }

    public function get_master_sanction_followup_status($status_id = "", $status_name = "") {

        $result_array = array("status" => 0);

        $conditions = array();
        $conditions['m_sf_status_active'] = 1;
        $conditions['m_sf_status_deleted'] = 0;

        if (!empty($status_id)) {
            $conditions['m_sf_status_id'] = $status_id;
        }

        if (!empty($status_name)) {
            $conditions['m_sf_status_name'] = stripslashes($status_name);
        }

        $this->db->select('m_sf_status_id, m_sf_status_name');
        $this->db->from('master_sanction_followup_status');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data_array = array();

            foreach ($tempDetails->result_array() as $row) {
                $data_array[$row['m_sf_status_id']] = $row['m_sf_status_name'];
            }

            $result_array['status'] = 1;
            $result_array['data'] = $data_array;
        }

        return $result_array;
    }

    public function insertCollectionCallFollowup($lead_id = "", $call_status_id = "", $call_remarks = "", $call_user_id = "", $call_created_on = "", $runo_call_log_id = "", $runo_call_mobile = "") {

        if (empty($lead_id) || empty($call_status_id) || empty($call_user_id)) {
            return false;
        }

        $collection_followup_array = array();
        $collection_followup_array['lcf_lead_id'] = $lead_id;
        $collection_followup_array['lcf_type_id'] = 1;
        $collection_followup_array['lcf_status_id'] = $call_status_id;
        $collection_followup_array['lcf_remarks'] = addslashes($call_remarks);
        $collection_followup_array['lcf_user_id'] = $call_user_id;
        $collection_followup_array['lcf_runo_call_log_id'] = $runo_call_log_id;
        $collection_followup_array['lcf_runo_call_mobile'] = $runo_call_mobile;
        $collection_followup_array['lcf_created_on'] = !empty($call_created_on) ? $call_created_on : date("Y-m-d H:i:s");

        return $this->insert('loan_collection_followup', $collection_followup_array);
    }

    public function insertSanctionCallFollowup($lead_id = "", $call_status_id = "", $call_remarks = "", $call_user_id = "", $call_created_on = "", $runo_call_log_id = "", $runo_call_mobile = "") {

        if (empty($lead_id) || empty($call_status_id) || empty($call_user_id)) {
            return false;
        }

        $sanction_followup_array = array();
        $sanction_followup_array['lsf_lead_id'] = $lead_id;
        $sanction_followup_array['lsf_type_id'] = 1;
        $sanction_followup_array['lsf_status_id'] = $call_status_id;
        $sanction_followup_array['lsf_remarks'] = addslashes($call_remarks);
        $sanction_followup_array['lsf_user_id'] = $call_user_id;
        $sanction_followup_array['lsf_runo_call_log_id'] = $runo_call_log_id;
        $sanction_followup_array['lsf_runo_call_mobile'] = $runo_call_mobile;
        $sanction_followup_array['lsf_created_on'] = !empty($call_created_on) ? $call_created_on : date("Y-m-d H:i:s");

        return $this->insert('lead_sanction_followups', $sanction_followup_array);
    }

    public function get_instant_lead_last_assignment() {

        $return_array['status'] = 0;
        $return_array['data'] = '';

        $sql = "SELECT lead_credithead_assign_user_id FROM leads WHERE lead_data_source_id=4 AND";
        $sql .= " lead_credithead_assign_user_id > 0 AND lead_credithead_assign_datetime is not NULL AND lead_status_id = 10";
        $sql .= " ORDER BY lead_credithead_assign_datetime DESC LIMIT 1";

        $last_assign = $this->db->query($sql);

        if ($last_assign->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $last_assign->row_array();
        }

        return $return_array;
    }

    public function getAffIliateDetails() {

        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_marketing_channel MMC";
        $query .= " WHERE MMC.mmc_affiliate_flag=1 AND MMC.mmc_affiliate_mmp_pid_name IS NOT NULL";
        $query .= " AND MMC.mmc_active=1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['affiliate_data'] = $tempDetails->result_array();
        }

        return $result_array;
    }
}
