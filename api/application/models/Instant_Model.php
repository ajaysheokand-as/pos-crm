<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Instant_Model extends CI_Model {

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
        $result_array = array("status" => 0, "data" => array());

        $conditions['cif_pancard'] = $pancard;

        $this->db->select('*');
        $this->db->from('cif_customer');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
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
        $this->db->join('loan L', 'L.lead_id=LD.lead_id', 'LEFT');

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
        // $conditions['CB.account_status_id'] = 1;

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

        $this->db->select('CAM.*, LD.status as status_name');
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
                                                                    <td width="24%" align="center" valign="top"><span style="background: #fe6801;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/light_icon.png" width="30" height="30" alt="Inr" /></span></td>
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
                                                                    <td align="center" valign="top"><span style="background: #fe6801;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/appoint-sechdule/image/inr_icon.png" width="30" height="30" alt="Inr" /></span></td>
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

    public function updateApplicationJourneyStage($lead_id, $lead_journey_type_id, $lead_journey_stage_id) {
        return $this->update(['lead_id' => $lead_id], "leads", ['lead_journey_type_id' => $lead_journey_type_id, 'lead_journey_stage_id' => $lead_journey_stage_id, 'updated_on' => date("Y-m-d H:i:s")]);
    }

    public function getLeadDetails($lead_id) {
        $result_array = array('status' => 0);

        if (empty($lead_id)) {
            return $result_array;
        }

        $conditions = array();
        $conditions['LD.lead_id'] = $lead_id;
        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;

        $this->db->select('LD.*');
        $this->db->from('leads LD');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $result_array['lead_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function check_validationToken($token) {
        $result_array = array("status" => 0);
        $currentdate = date('Y-m-d H:i:s');

        $select = 'MLT.mlt_id, MLT.mlt_valid_datetime, MLT.mlt_token_valid_time';

        $conditions['MLT.mlt_token'] = $token;
        $conditions['MLT.mlt_active'] = 1;
        $conditions['MLT.mlt_deleted'] = 0;
        $conditions['mlt_valid_datetime >='] = $currentdate;

        $this->db->select($select);
        $this->db->from('mobileapp_login_trans MLT');
        $this->db->where($conditions);
        $query = $this->db->order_by('MLT.mlt_id', 'DESC')->limit(1)->get();

        if (!empty($query->num_rows())) {
            $token_status = $query->row_array();

            $mlt_valid_datetime = date("Y-m-d H:i:s");
            $mlt_token_valid_time = $token_status['mlt_token_valid_time'];
            $minutes_to_add = 1440;

            $time = new DateTime($mlt_valid_datetime);
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $mlt_token_valid_timenew = $minutes_to_add;
            $newexptime = $time->format('Y-m-d H:i:s');

            $conditions_update['mlt_id'] = $token_status['mlt_id'];

            $update_mobileapp_login_trans['mlt_valid_datetime'] = $newexptime;
            $update_mobileapp_login_trans['mlt_token_valid_time'] = $mlt_token_valid_timenew;
            $update_mobileapp_login_trans['mlt_updated_on'] = date('Y-m-d H:i:s');

            $res = $this->db->where($conditions_update)->update('mobileapp_login_trans', $update_mobileapp_login_trans);
            if (!empty($res)) {
                $result_array['status'] = 1;
            }
        }
        return $result_array;
    }

    public function insertMobileApplicationLog($source_id, $lead_id = 0, $insert_data = array()) {
        return null;
        if (empty($insert_data)) {
            return null;
        }

        $insert_log_array = array();
        $insert_log_array = $insert_data;
        $insert_log_array['mapp_src_id'] = $source_id;
        //        $insert_log_array['mapp_data_source_str'] = (!empty($insert_data['mapp_data_source_str']) ? $insert_data['mapp_data_source_str'] : NULL);
        $insert_log_array['mapp_browser_info'] = $_SERVER['HTTP_USER_AGENT'];
        $insert_log_array['mapp_action_name'] = ($insert_data['mapp_action_name'] ? $insert_data['mapp_action_name'] : NULL);
        $insert_log_array['mapp_lead_id'] = $lead_id;
        //        $insert_log_array['mapp_mobile'] = ($insert_data['mobile'] ? $insert_data['mobile'] : NULL);
        //        $insert_log_array['mapp_customer_id'] = ($insert_data['customer_id'] ? $insert_data['customer_id'] : NULL);
        $insert_log_array['mapp_request_datetime'] = date('Y-m-d H:i:s');
        $this->db->insert('api_mobile_app_logs', $insert_log_array);
        return $this->db->insert_id();
    }

    public function updateMobileApplicationLog($log_id, $lead_id, $update_data = array()) {
        return null;
        if (empty($update_data) || empty($log_id)) {
            return null;
        }

        $table = "api_mobile_app_logs";

        $conditions = array("mapp_log_id" => $log_id);

        $query = "SELECT * FROM $table WHERE mapp_log_id = $log_id";

        $get_log_details = $this->db->query($query);

        if ($get_log_details->num_rows() > 0) {
            $update_data['mapp_lead_id'] = (!empty($lead_id) ? $lead_id : NULL);
            $update_data['mapp_response_datetime'] = date('Y-m-d H:i:s');
            return $this->db->where($conditions)->update($table, $update_data);
        } else {
            return false;
        }
    }

    public function insertMobileAppLoginTrans($lead_id = 0, $insert_data = array()) {

        if (empty($insert_data)) {
            return null;
        }

        $insert_data['mlt_token_valid_time'] = 30;
        $insert_data['mlt_app_version'] = 1;
        $insert_data['mlt_created_on'] = date('Y-m-d H:i:s');

        $this->db->insert('mobileapp_login_trans', $insert_data);
        return $this->db->insert_id();
    }

    public function insertProfileFollowupLog($profile_id, $profile_status_id, $remark) {

        if (empty($profile_id) || empty($profile_status_id) || empty($remark)) {
            return null;
        }

        $user_id = 0;

        $insert_log_array = array();
        $insert_log_array['profile_followup_profile_id'] = $profile_id;
        $insert_log_array['profile_followup_user_id'] = $user_id;
        $insert_log_array['profile_followup_status_id'] = $profile_status_id;
        $insert_log_array['profile_followup_remarks'] = addslashes($remark);
        $insert_log_array['profile_followup_created_on'] = date("Y-m-d H:i:s");

        return $this->db->insert('customer_profile_followup', $insert_log_array);
    }

    public function get_otp_trans_logs($mobile, $otp = "") {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM leads_otp_trans WHERE lot_mobile_no='$mobile' ORDER BY lot_id DESC LIMIT 1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['otp_trans_logs'] = $data;
        }

        return $result_array;
    }

    public function get_customer_profile_details($search_by, $search_type = 0) {

        $result_array = array("status" => 0);

        if ($search_type == 0) {
            $conditions["cp_id"] = $search_by;
        } else if ($search_type == 1) {
            $conditions["cp_mobile"] = $search_by;
        } else if ($search_type == 2) {
            $conditions["cp_pancard"] = $search_by;
        }

        $conditions['cp_active'] = 1;
        $conditions['cp_deleted'] = 0;

        $tempDetails = $this->db->select('*')->where($conditions)->from('customer_profile')->order_by('cp_id DESC')->limit(1)->get();

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['customer_profile_details'] = $data;
        }

        return $result_array;
    }

    public function updateCustomerProfile($cust_profile_id, $update_data = array()) {

        if (empty($update_data) || empty($cust_profile_id)) {
            return null;
        }

        $conditions = array("cp_id" => $cust_profile_id);

        $query = "SELECT * FROM customer_profile WHERE cp_id = $cust_profile_id";

        $get_log_details = $this->db->query($query);

        if ($get_log_details->num_rows() > 0) {
            return $this->db->where($conditions)->update("customer_profile", $update_data);
        } else {
            return false;
        }
    }

    public function getCityStateByPincode($pincode) {

        $result_array = array('status' => 0);

        if (empty($pincode)) {
            return null;
        }

        $query = "SELECT MP.m_pincode_value as pincode, MC.m_city_id as city_id, MC.m_city_name as city_name, MC.m_city_branch_id as branch_id, MC.m_city_is_sourcing, MS.m_state_id as state_id, MS.m_state_name as state_name";
        $query .= " FROM master_pincode MP";
        $query .= " INNER JOIN master_city MC ON (MC.m_city_id=MP.m_pincode_city_id)";
        $query .= " INNER JOIN master_state MS ON (MS.m_state_id=MC.m_city_state_id)";
        $query .= " WHERE MP.m_pincode_value=$pincode";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['get_city_state_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function getMasterResidence($id) {
        if (empty($id)) {
            return null;
        }
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_residence_type WHERE m_residence_type_id=$id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['master_residence_details'] = $data;
        }

        return $result_array;
    }

    public function updateLeads($lead_id, $update_data = array()) {

        if (empty($update_data) || empty($lead_id)) {
            return null;
        }
        $result_array = array("status" => 0);

        $table = "leads";

        $conditions = array("lead_id" => $lead_id);

        $query = "SELECT * FROM $table WHERE lead_id = $lead_id";

        $get_log_details = $this->db->query($query);

        if ($get_log_details->num_rows() > 0) {
            return $this->db->where($conditions)->update($table, $update_data);
        } else {
            return false;
        }

        return $result_array;
    }

    public function updateLeadCustomer($lead_id, $update_data = array()) {

        if (empty($update_data) || empty($lead_id)) {
            return null;
        }

        $result_array = array("status" => 0);

        $table = "lead_customer";

        $conditions = array("customer_lead_id" => $lead_id);

        $query = "SELECT * FROM $table WHERE customer_lead_id = $lead_id";

        $get_log_details = $this->db->query($query);

        if ($get_log_details->num_rows() > 0) {
            return $this->db->where($conditions)->update($table, $update_data);
        } else {
            return false;
        }

        return $result_array;
    }

    public function updateCustomerEmployment($lead_id, $update_data = array()) {

        if (empty($update_data) || empty($lead_id)) {
            return null;
        }

        $result_array = array("status" => 0);

        $table = "customer_employment";

        $conditions = array("lead_id" => $lead_id);

        $query = "SELECT * FROM $table WHERE lead_id = $lead_id";

        $get_log_details = $this->db->query($query);

        if ($get_log_details->num_rows() > 0) {
            return $this->db->where($conditions)->update($table, $update_data);
        } else {
            return false;
        }

        return $result_array;
    }

    public function getMasterDocument($id) {
        if (empty($id)) {
            return null;
        }
        $result_array = array("status" => 0);

        $query = "SELECT * FROM docs_master WHERE id=$id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['master_document_details'] = $data;
        }

        return $result_array;
    }

    public function get_bank_details($ifsc_code, $search_type = 0) {
        if (empty($ifsc_code)) {
            return null;
        }
        $result_array = array("status" => 0);

        if ($search_type == 0) {
            $query = "SELECT * FROM tbl_bank_details WHERE bank_ifsc='$ifsc_code'";
        } else {
            $query = "SELECT bank_ifsc FROM tbl_bank_details WHERE bank_ifsc like '%$ifsc_code%'";
        }

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {

            if ($search_type == 0) {
                $data = $tempDetails->row_array();
            } else {
                $data = $tempDetails->result_array();
            }

            $result_array['status'] = 1;
            $result_array['bank_account_details'] = $data;
        }

        return $result_array;
    }

    public function getMasterSalaryMode() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_salary_mode WHERE m_salary_mode_active=1 AND m_salary_mode_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_salary_mode'] = $data;
        }

        return $result_array;
    }

    public function getMasterMaritalStatus() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_marital_status WHERE m_marital_status_active=1 AND m_marital_status_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_marital_status'] = $data;
        }

        return $result_array;
    }

    public function getMasterBankType() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_bank_type WHERE m_bank_type_active=1 AND m_bank_type_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_bank_type'] = $data;
        }

        return $result_array;
    }

    public function getMasterLoanPurpose() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_enduse WHERE enduse_active=1 AND enduse_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_loan_purpose'] = $data;
        }

        return $result_array;
    }

    public function getMasterResidenceType() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_residence_type WHERE m_residence_type_active=1 AND m_residence_type_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_residence_type'] = $data;
        }

        return $result_array;
    }

    public function getMasterCompanyType() {
        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_company_type WHERE m_company_type_active=1 AND m_company_type_deleted=0";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->result_array();
            $result_array['status'] = 1;
            $result_array['master_company_type'] = $data;
        }

        return $result_array;
    }

    public function getMasterJourneyStage($stage_name, $type_id = 1) {
        $result_array = array("status" => 0);

        if (empty($stage_name)) {
            return null;
        }

        $query = "SELECT * FROM master_journey_stage WHERE m_journey_code='$stage_name' AND m_journey_type_id=$type_id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['master_journey_stage'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function selectprofilefollowup($conditions = null, $data = null, $table = 'customer_profile_followup', $order_by = null, $limit = null) {
        return $this->db->select($data)->where($conditions)->from($table)->order_by($order_by)->limit($limit)->get();
    }

    public function getSalaryModeById($id) {
        $result_array = array("status" => 0);

        if (empty($id)) {
            return null;
        }

        $query = "SELECT * FROM master_salary_mode WHERE m_salary_mode_id=$id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['master_salary_mode_details'] = $data;
        }

        return $result_array;
    }

    public function getMaritalStatusById($id) {
        $result_array = array("status" => 0);

        if (empty($id)) {
            return null;
        }

        $query = "SELECT * FROM master_marital_status WHERE m_marital_status_id=$id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['master_marital_status_details'] = $data;
        }

        return $result_array;
    }

    public function getBankTypeById($id) {
        $result_array = array("status" => 0);

        if (empty($id)) {
            return null;
        }

        $query = "SELECT * FROM master_bank_type WHERE m_bank_type_id=$id";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['master_bank_type_details'] = $data;
        }

        return $result_array;
    }

    public function getDigilockerDocuments($search_id, $search_by = 1) {

        $result_array = array('status' => 0);

        if (empty($search_id) || empty($search_by)) {
            return null;
        }

        if ($search_by == 1) {
            $search = "lead_id = " . $search_id;
        } else if ($search_by == 2) {
            $search = "pancard = '" . $search_id . "'";
        }

        $query = "SELECT * FROM docs WHERE $search AND docs_master_id in (20,21) AND docs_active=1 AND docs_deleted=0 ORDER BY created_on DESC LIMIT 2";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['get_digilocker_documents'] = $tempDetails->result_array();
        }

        return $result_array;
    }

    public function get_active_loan($pancard) {
        $result_array = array('status' => 0);

        if (empty($pancard)) {
            return null;
        }

        $query = 'SELECT count(lead_id) AS active_loan_count,product_id FROM leads WHERE lead_status_id in (14,19) AND pancard="' . $pancard . '"';

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['get_active_loan'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_active_cases($pancard) {
        $result_array = array('status' => 0);

        if (empty($pancard)) {
            return null;
        }

        $query = 'SELECT count(lead_id) AS active_cases FROM leads WHERE lead_status_id in (2,3,4,5,6,10,11,12,13,25,30,35,37,43,44,45,46) AND pancard="' . $pancard . '"';

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['get_active_cases'] = $tempDetails->result_array();
        }

        return $result_array;
    }

    public function select_data_by_filter($conditions = null, $data = null, $table = null, $order_by = null, $limit = null) {
        return $this->db->select($data)->where($conditions)->from($table)->order_by($order_by)->limit($limit)->get();
    }

    public function get_docs_by_master_doc_id($doc_id, $pancard) {
        $result_array = array('status' => 0);

        if (empty($doc_id)) {
            return null;
        }

        $query = "SELECT D.docs_master_id as master_id,D.file as file_name FROM docs D WHERE D.docs_master_id=$doc_id AND D.pancard='" . $pancard . "' AND D.docs_active=1 AND D.docs_deleted=0 ORDER BY D.docs_id DESC LIMIT 1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['doc_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function get_lead_docs_by_master_doc_id($docs_master_id, $lead_id) {
        $result_array = array('status' => 0);

        if (empty($docs_master_id) || empty($lead_id)) {
            return $result_array;
        }

        $query = "SELECT D.docs_master_id as master_id,D.file as file_name FROM docs D WHERE D.docs_master_id=$docs_master_id AND D.lead_id=$lead_id AND D.docs_active=1 AND D.docs_deleted=0 ORDER BY D.docs_id DESC LIMIT 1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['doc_details'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function updateJourneyEvents($id, $column_id, $event_type_id = 1, $journey_type_id = 1, $update_array = array()) { // Event type 1=>Profile Journey, 2=>Lead Journey // Journey Type 1=>Web, 2=>App
        $result = null;

        $update_data = array();

        if (empty($id) || empty($column_id)) {
            return null;
        }

        if ($event_type_id == 1) {
            $conditions['pje_profile_id'] = $id;
            $table = 'profile_journey_events';
            $prefix = 'pje_';
        } else if ($event_type_id == 2) {
            $conditions['lje_lead_id'] = $id;
            $table = 'lead_journey_events';
            $prefix = 'lje_';
        }

        $update_data = $update_array;

        $get_journey_details = $this->selectdata(['m_journey_id' => $column_id], 'm_journey_code', 'master_journey_stage');

        $get_journey_details = $get_journey_details->row_array();

        $get_event_name = $get_journey_details['m_journey_code'];

        $update_data[$prefix . $get_event_name] = 1;

        $update_data[$prefix . 'journey_type_id'] = $journey_type_id;

        $tempDetails = $this->db->select('*')->where($conditions)->from($table)->get();

        if ($tempDetails->num_rows() > 0) {
            if ($event_type_id == 1) {
                $update_data['pje_updated_at'] = date("Y-m-d H:i:s");
            } else if ($event_type_id == 2) {
                $update_data['lje_updated_at'] = date("Y-m-d H:i:s");
            }
            $result = $this->db->where($conditions)->update($table, $update_data);
        } else {
            if ($event_type_id == 1) {
                $update_data['pje_profile_id'] = $id;
                $update_data['pje_created_at'] = date("Y-m-d H:i:s");
            } else if ($event_type_id == 2) {
                $update_data['lje_lead_id'] = $id;
                $update_data['lje_created_at'] = date("Y-m-d H:i:s");
            }
            $this->db->insert($table, $update_data);
        }

        return $result;
    }

    public function selectJourneyEvents($id, $column_id, $event_type_id = 1) {

        if (empty($id)) {
            return null;
        }

        if ($event_type_id == 1) {
            $conditions['pje_profile_id'] = $id;
            $table = 'profile_journey_events';
            $prefix = "pje_";
        } else if ($event_type_id == 2) {
            $conditions['lje_lead_id'] = $id;
            $table = 'lead_journey_events';
            $prefix = "lje_";
        }

        $get_journey_details = $this->selectdata(['m_journey_id' => $column_id], 'm_journey_code', 'master_journey_stage');

        $get_journey_details = $get_journey_details->row_array();

        $get_event_name = $get_journey_details['m_journey_code'];

        $select = $prefix . $get_event_name;

        $tempDetails = $this->db->select($select)->where($conditions)->from($table)->get();

        $tempDetails = $tempDetails->row_array();

        if ($tempDetails[$select] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function resetJourneyEvents($id, $event_type_id = 1, $update_array = array()) { // Event type 1=>Profile Journey, 2=>Lead Journey
        $result = null;

        $update_data = array();

        if (empty($id)) {
            return null;
        }

        if ($event_type_id == 1) {
            $conditions['pje_profile_id'] = $id;
            $table = 'profile_journey_events';
            $pre = 'pje_';
        } else if ($event_type_id == 2) {
            $conditions['lje_lead_id'] = $id;
            $table = 'lead_journey_events';
            $pre = 'lje_';
        }

        $update_data = $update_array;

        $update_data[$pre . 'login'] = 0;
        $update_data[$pre . 'otp_verify'] = 0;
        $update_data[$pre . 'resend_otp'] = 0;
        $update_data[$pre . 'residence_pincode'] = 0;
        $update_data[$pre . 'pancard_verification'] = 0;
        $update_data[$pre . 'income_details'] = 0;
        $update_data[$pre . 'personal_details'] = 0;
        $update_data[$pre . 'residence_details'] = 0;
        $update_data[$pre . 'promocode'] = 0;
        $update_data[$pre . 'selfie_upload'] = 0;
        $update_data[$pre . 'registration_successful'] = 0;
        //        $update_data[$pre . 'eligibility_failed'] = 0;
        //        $update_data[$pre . 'reject'] = 0;
        $update_data[$pre . 'thank_you'] = 0;

        $tempDetails = $this->db->select('*')->where($conditions)->from($table)->get();

        if ($tempDetails->num_rows() > 0) {
            if ($event_type_id == 1) {
                $update_data['pje_lead_id'] = NULL;
                $update_data['pje_updated_at'] = date("Y-m-d H:i:s");
            } else if ($event_type_id == 2) {
                $update_data['lje_updated_at'] = date("Y-m-d H:i:s");
            }
            $result = $this->db->where($conditions)->update($table, $update_data);
        }

        return $result;
    }

    public function getMobileUTMTags($platform_id = 0, $ip_address = "") {
        $result_array = array("status" => 0, "campaign_data" => "");

        if (empty($platform_id) || empty($ip_address)) {
            return null;
        }

        $query = "SELECT amcl_utm_source, amcl_utm_medium,amcl_utm_campaign FROM app_mobile_campaign_lending_logs WHERE amcl_platform_id=$platform_id AND amcl_ip='$ip_address' AND amcl_created_date='" . date("Y-m-d") . "' ORDER BY amcl_log_id DESC LIMIT 1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['campaign_data'] = $tempDetails->row_array();
        }

        return $result_array;
    }

    public function getUserIdByUsername($user_name) {
        $result_array = array("status" => 0);

        $conditions['U.user_active'] = 1;
        $conditions['U.user_dialer_id'] = $user_name;
        $conditions['U.user_deleted'] = 0;

        $this->db->select('U.user_id, U.name');
        $this->db->from('users U');
        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $data_array = array();
            $result_array['status'] = 1;

            $result = $tempDetails->row_array();

            $data_array['user_id'] = $result['user_id'];
            $data_array['name'] = $result['name'];

            $result_array['data'] = $data_array;
        }

        return $result_array;
    }

    public function insertRepeatCustomerBankingDetails($pancard, $lead_id) {
        if (empty($pancard) || empty($lead_id)) {
            return array();
        }
        $sql_customer_banking = "SELECT CB.* FROM leads LD";
        $sql_customer_banking .= " INNER JOIN customer_banking CB ON (CB.lead_id = LD.lead_id)";
        $sql_customer_banking .= " WHERE LD.pancard= '" . $pancard . "' AND CB.account_status_id = 1 AND LD.lead_status_id IN (14, 16, 17, 19)";
        $sql_customer_banking .= " AND CB.customer_banking_active=1 AND CB.customer_banking_deleted=0";
        $sql_customer_banking .= " ORDER BY CB.id DESC";
        $sql_customer_banking .= " LIMIT 0,1";

        $query_customer_banking = $this->db->query($sql_customer_banking);

        if ($query_customer_banking->num_rows() > 0) {

            $repeatCustomerBanking = $query_customer_banking->row_array();
            $insert_customer_banking_data = array();
            $insert_customer_banking_data['customer_id'] = $repeatCustomerBanking['customer_id'];
            $insert_customer_banking_data['lead_id'] = $lead_id;
            $insert_customer_banking_data['bank_name'] = $repeatCustomerBanking['bank_name'];
            $insert_customer_banking_data['ifsc_code'] = $repeatCustomerBanking['ifsc_code'];
            $insert_customer_banking_data['branch'] = $repeatCustomerBanking['branch'];
            $insert_customer_banking_data['beneficiary_name'] = $repeatCustomerBanking['beneficiary_name'];
            $insert_customer_banking_data['account'] = $repeatCustomerBanking['account'];
            $insert_customer_banking_data['confirm_account'] = $repeatCustomerBanking['confirm_account'];
            $insert_customer_banking_data['account_type'] = $repeatCustomerBanking['account_type'];
            $insert_customer_banking_data['account_status'] = NULL;
            $insert_customer_banking_data['account_status_id'] = NULL;
            $insert_customer_banking_data['remark'] = "Repeat case banking - " . $repeatCustomerBanking['remark'];
            $insert_customer_banking_data['created_on'] = date("Y-m-d H:i:s");

            $this->insert("customer_banking", $insert_customer_banking_data);
        }
    }

    public function insertRepeatCustomerReferenceDetails($pancard, $lead_id) {

        if (empty($pancard) || empty($lead_id)) {
            return array();
        }

        $sql_customer_references = "SELECT LCR.*";
        $sql_customer_references .= " FROM leads LD";
        $sql_customer_references .= " INNER JOIN lead_customer_references LCR ON (LCR.lcr_lead_id = LD.lead_id)";
        $sql_customer_references .= " WHERE LD.pancard= '" . $pancard . "' AND LD.lead_status_id IN (14, 16, 17, 19)";
        $sql_customer_references .= " ORDER BY LCR.lcr_id DESC";
        $sql_customer_references .= " LIMIT 0, 2";

        $query_customer_references = $this->db->query($sql_customer_references);

        if ($query_customer_references->num_rows() > 0) {

            $repeatCustomerReferences = $query_customer_references->result_array();

            foreach ($repeatCustomerReferences as $row) {

                $insert_customer_references_data = array();
                $insert_customer_references_data['lcr_lead_id'] = $lead_id;
                $insert_customer_references_data['lcr_name'] = $row['lcr_name'];
                $insert_customer_references_data['lcr_relationType'] = $row['lcr_relationType'];
                $insert_customer_references_data['lcr_mobile'] = $row['lcr_mobile'];
                $insert_customer_references_data['lcr_created_on'] = date("Y-m-d H:i:s");
                $this->insert('lead_customer_references', $insert_customer_references_data);
            }
        }
    }

    public function get_loan_history($pancard) {
        if (empty($pancard)) {
            return null;
        }

        $result_array = array('status' => 0, 'data' => '');

        $select_data = "SELECT L.lead_id, L.loan_no,L.product_id ,CAM.loan_recommended,CAM.roi,CAM.repayment_amount,CAM.repayment_date,CAM.tenure,CAM.disbursal_date,CAM.panel_roi,L.loan_total_payable_amount,LD.lead_status_id,LD.status,L.loan_total_received_amount,L.loan_total_outstanding_amount";

        $select_data .= " FROM leads LD INNER JOIN loan L ON (L.lead_id=LD.lead_id)";

        $select_data .= " INNER JOIN credit_analysis_memo CAM ON (CAM.lead_id=LD.lead_id)";

        $select_data .= " WHERE LD.lead_status_id IN (14,16,17,18,19) AND LD.pancard='" . $pancard . "'";

        $select_data .= " ORDER BY LD.lead_id DESC";

        $tempDetails = $this->db->query($select_data);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['data'] = $tempDetails->result_array();
        }

        return $result_array;
    }

    public function getUserDetails($user_id, $role_type_id = 0) {

        $result_array = array("status" => 0);

        if (empty($user_id)) {
            return null;
        }

        $query = "SELECT U.user_id, U.name, U.email, U.mobile FROM users U";
        $query .= " INNER JOIN user_roles UR ON (UR.user_role_user_id=U.user_id)";
        $query .= " WHERE UR.user_role_user_id=$user_id";
        $query .= " AND U.user_status_id=1 AND U.user_active=1";
        $query .= " AND UR.user_role_active=1";

        if (!empty($role_type_id)) {
            $query .= " AND UR.user_role_type_id=$role_type_id";
        }

        $query .= " ORDER BY U.user_id DESC";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $data = $tempDetails->row_array();
            $result_array['status'] = 1;
            $result_array['user_data'] = $data;
        }

        return $result_array;
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

    public function getBlogList($limit, $start, $orderUrl) {
        $this->db->select('*');
        $this->db->from("website_blog");
        $this->db->limit($limit, $start);
        $this->db->where('wb_publish_status', 1);
        $this->db->where('wb_active', 1);
        $this->db->where('wb_deleted', 0);
        if ($orderUrl != '' && !is_numeric($orderUrl)) {
            $order = $orderUrl;
        } else if ($orderUrl == '' && is_numeric($orderUrl)) {
            $order = 'asc';
        } else {
            $order = 'desc';
        }
        $return = $this->db->order_by('wb_publish_date', $order)->get()->result_array();
        return $return;
    }

    public function getBlogDetails($slug) {
        $blogqry = "SELECT  wb_title, wb_slug, wb_short_description, wb_long_description, wb_thumb_image_url, wb_banner_image_url, wb_category_id, wb_seo_title, wb_seo_keyword, wb_seo_description, wb_publish_status, wb_publish_date FROM  website_blog WHERE wb_publish_status = 1 AND wb_active = 1 AND wb_slug = '$slug'";

        $response = $this->db->query($blogqry);

        if ($response->num_rows() > 0) {
            return $response->result_array();
        } else {
            return false;
        }
    }

    public function getBlogListHome() {
        $blogqry = "SELECT  wb_title, wb_slug, wb_short_description, wb_long_description, wb_thumb_image_url, wb_banner_image_url, wb_category_id, wb_seo_title, wb_seo_keyword, wb_seo_description, wb_publish_status, DATE_FORMAT(wb_publish_date,'%d-%M-%Y') as wb_publish_date";
        $blogqry .= " FROM  website_blog WHERE wb_publish_status = 1 AND wb_active = 1  ORDER BY wb_publish_date DESC  LIMIT 3";

        $response = $this->db->query($blogqry);

        if ($response->num_rows() > 0) {
            return $response->result_array();
        } else {
            return false;
        }
    }

    public function getBlogCount() {
        $this->db->select("wb_id");
        $this->db->where('wb_publish_status', 1);
        $this->db->where('wb_active', 1);
        $this->db->where('wb_deleted', 0);
        $response = $this->db->from('website_blog')->get()->num_rows();
        return $response;
    }

    public function getPaymentGateway($company_id, $service_id) {
        $return_array = array('status' => 0);

        $conditions = array(
            'mpg_company_id' => $company_id,
            'mpg_gateway_mode_id' => $service_id,
            'mpg_active' => 1,
            'mpg_deleted' => 0
        );

        $response = $this->db->select("*")
            ->from('master_payment_gateway')
            ->where($conditions)
            ->get()->row_array();

        if (!empty($response)) {
            $return_array['status'] = 1;
            $return_array['data'] = $response;
        }

        return $return_array;
    }

    public function getLoanCount($pancard) {
        $return_array = array('status' => 0);

        $query = "SELECT COUNT(pancard) as total_disbursed_cases FROM leads WHERE lead_status_id IN (16,17,18,19) AND pancard='" . $pancard . "'";

        $response = $this->db->query($query);

        if ($response->num_rows > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $response->row_array();
        }

        return $return_array;
    }

    /*---------loan emi scheduler-----------*/

    public function emiScheduler($loan_account_no) {
        $return_array = array('status' => 0);
        $curr_date = date('Y-m-d');
        $query = "select les_id as id,les_lead_id as lead_id,les_loan_no as loan_account_no,les_emi_installment_no as emi_no,les_emi_due_date as emi_date,les_emi_amount as emi_amount,les_emi_principal_amount as principal_amount,les_emi_interest_amount as interest_amount,Case when les_payment_status_id=0 then 'PENDING' WHEN les_payment_status_id=1 THEN 'PAID' WHEN les_payment_status_id=2 THEN 'PENDING' end as emi_status ,les_emi_bounce_charges as bounce_charge,les_emi_penalty_amount as penalty_amount,les_emi_penalty_dpd as emi_penalty_dpd from loan_emi_collection_schedule where les_loan_no='" . $loan_account_no . "'";

        $response = $this->db->query($query);

        if ($response->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $response->result_array();
        }

        return $return_array;
    }

    public function newsList($limit, $start = null, $conditions = array()) {
        $this->db->select('*');
        $this->db->from("website_news");
        $this->db->distinct();
        $this->db->limit($limit, $start);
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('wn_active', 1);
        $this->db->where('wn_deleted', 0);
        $return = $this->db->order_by('wn_id', 'desc')->get()->result_array();
        // $this->db->last_query();

        return $return;
    }

    public function newsListCount($conditions) {
        $this->db->select("wn_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('wn_active', 1);
        $this->db->where('wn_deleted', 0);
        return $this->db->from('website_news')->get()->num_rows();
    }

    public function calculateMedian($date) {
        $salary_amount = explode("-", $date);

        $dt = explode("-", $date);
        $d = 0;
        $daysGay = 0;
        if ($dt[0] >= $dt[1]) {
            $d = $dt[0];
            $daysGay = $dt[0] - $dt[1];
        } else if ($dt[1] >= $dt[2]) {
            $d = $dt[1];
            $daysGay = $dt[1] - $dt[2];
        } else if ($dt[2] >= $dt[0]) {
            $d = $dt[2];
            $daysGay = $dt[2] - $dt[0];
        }
        $next_month = date('m') + 1;
        $next_year = date('Y');
        if ($next_month > 12) {
            $next_month = $next_month - 12;
            $next_year = $next_year + 1;
        }
        $next_pay_date = $d . '-' . $next_month . '-' . $next_year;

        $salary_on_time = "LOW";
        if ($daysGay > 5) {
            $salary_on_time = "LOW";
        } else if ($daysGay > 3 && $daysGay < 5) {
            $salary_on_time = "MEDIUM";
        } else if ($daysGay <= 2) {
            $salary_on_time = "HIGH";
        }

        $data['salary_on_time'] = $salary_on_time;
        $data['next_pay_date'] = $next_pay_date;
        return $data;
    }

    public function averageSalary($arr) {
        $arr = explode("-", $arr);
        // echo "<pre>"; print_r($arr); exit;
        $salary1 = ($arr[0] != "") ? $arr[0] : 0;
        $salary2 = ($arr[1] != "") ? $arr[1] : 0;
        $salary3 = ($arr[2] != "") ? $arr[2] : 0;

        $average_salary = $salary1;
        if (!empty($salary1) && empty($salary2) && empty($salary3)) {
            $count = 1;
        } else if (!empty($salary1) && !empty($salary2) && empty($salary3)) {
            $count = 2;
        } else if (!empty($salary1) && !empty($salary2) && !empty($salary3)) {
            $count = 3;
        } else {
            $count = 1;
        }

        if ($salary1 >= $salary2 && $salary1 >= $salary3) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        } else if ($salary2 >= $salary1 && $salary2 >= $salary3) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        } else if ($salary3 >= $salary1 && $salary3 >= $salary2) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        }
        $data['average_salary'] = round($average_salary);
        $data['salary_variance'] = $this->salaryVariance($arr);
        return $data;
    }

    public function salaryVariance($salary) {
        sort($salary);
        $x = 0;
        $y = 0;
        if ($salary[0] != '' && $salary[1] != '' && $salary[2] != '') {
            if ($salary[0] > $salary[1]) {
                $x = ($salary[0] - $salary[1]) / $salary[0];
            } else if ($salary[1] >= $salary[0]) {
                $x = ($salary[1] - $salary[0]) / $salary[1];
            }
            if ($salary[1] >= $salary[2]) {
                $y = ($salary[1] - $salary[2]) / $salary[1];
            } else if ($salary[2] >= $salary[1]) {
                $y = ($salary[2] - $salary[1]) / $salary[2];
            } else if ($salary[0] >= $salary[2]) {
                $x = ($salary[0] - $salary[2]) / $salary[0];
            }

            $variance = "-";
            $sVariance = ($x + $y) / 2;
            if ($sVariance <= 5) {
                // $variance = "LOW";
                $variance = "HIGH";
            } else if ($sVariance > 5 && $sVariance <= 9) {
                $variance = "MEDIUM";
            } else if ($sVariance <= 10) {
                // $variance = "HIGH";
                $variance = "LOW";
            }
        } else {
            $variance = "HIGH";
        }
        return $variance;
    }

    public function calculation_ntc($dob, $cibil, $presentServiceTenure) {
        $today = date('Y-m-d');
        $diff = abs(strtotime($today) - strtotime($dob));
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        // $data['borrower_age'] = $years .' Y, '. $months .' M, '. $days .' days';
        $data['borrower_age'] = $years . 'Y, ' . $months . 'M';

        if ($presentServiceTenure > 12) {
            $pst = "HIGH";
        } else if ($presentServiceTenure > 6 && $presentServiceTenure <= 12) {
            $pst = "MEDIUM";
        } else if ($presentServiceTenure <= 6) {
            $pst = "LOW";
        }

        $data['job_stability'] = $pst;
        $ntc = "YES";
        if ($cibil >= 5) {
            $ntc = "NO";
        }
        $data['ntc'] = $ntc;
        $data['cibil'] = (($cibil != NULL) ? $cibil : '');
        return $data;
    }

    public function calcAmount($input) {
        $product_id = !empty($input['product_id']) ? $input['product_id'] : 1;
        $loan_recommended = $input['loan_recommended'];
        $obligations = $input['obligations'];
        $monthly_salary = $input['monthly_salary'];
        $eligible_foir_percentage = $input['eligible_foir_percentage'];
        $roi = ($input['roi'] ? $input['roi'] : 1);
        $processing_fee_percent = ($input['processing_fee_percent']) ? ($input['processing_fee_percent']) : 0;
        $fintech_fee_percent = $input['fintech_fee_percent'];
        $tenure = $input['tenure'] ? $input['tenure'] : 0;
        $annualInterestRate = 486.67;
        $pre_emi_interest = 0;
        $repayment_amount = 0;

        $disbursal_date = $input['disbursal_date'];

        if ($product_id == 1) {
            $repayment_date = $input['repayment_date'];
            $d1 = strtotime($disbursal_date);
            $d2 = strtotime($repayment_date);
            if (!empty($d2)) {
                $datediff = $d2 - $d1;
                $tenure = round($datediff / (60 * 60 * 24));
            }
        } else if ($product_id == 2) {
            $annualInterestRate = $roi * 12;
            $first_emi_start_day = $input['first_emi_start_day'];
            if ($first_emi_start_day < 10) {
                $first_emi_start_day = "0" . $first_emi_start_day;
            }
            $temp_first_emi_start_date = !empty($first_emi_start_day) ? date("Y-m-" . $first_emi_start_day) : '';
            if ($temp_first_emi_start_date > date("Y-m-d")) {
                $first_emi_start_date = $temp_first_emi_start_date;
            } else {
                $date = date("Y-m-" . $first_emi_start_day);
                $first_emi_start_date = date("Y-m-" . $first_emi_start_day, strtotime($date . "+1 month"));
            }
            $first_emi_due_date = date("Y-m-d", strtotime("+1 month", strtotime($first_emi_start_date)));
            $d1 = strtotime($disbursal_date);
            $d2 = strtotime($first_emi_start_date);
            if (!empty($d2) && !empty($d1)) {
                $datediff = $d2 - $d1;
                $pre_emi_tenure = round($datediff / (60 * 60 * 24));
            }
        }

        $fintech_fee = round(($loan_recommended * $fintech_fee_percent) / 100);
        $fintech_fee_gst = round(($fintech_fee * 18) / 100);
        $fintech_fee_with_gst = $fintech_fee + $fintech_fee_gst;

        $fintech_fee = 0;
        $fintech_fee_gst = 0;
        $fintech_fee_with_gst = 0;

        if ($product_id == 2) {
            $daily_interest_rate = ($annualInterestRate / 100) / 365;
            $pre_emi_interest = round(($loan_recommended * $daily_interest_rate * $pre_emi_tenure), 2);

            $monthly_interest_rate = ($annualInterestRate / 100) / 12;
            $emi = $loan_recommended * $monthly_interest_rate * ((1 + $monthly_interest_rate) ** $tenure) / (((1 + $monthly_interest_rate) ** $tenure) - 1);

            for ($month = 1; $month <= $tenure; $month++) {
                $repayment_amount += round($emi, 2);
            }
        } else {
            $repayment_amount = ($loan_recommended + ($loan_recommended * $roi * $tenure) / 100);
        }

        $admin_fee = round(($loan_recommended * $processing_fee_percent) / 100);
        $adminFeeWithoutGst = round(($admin_fee / 1.18));
        $gst = $admin_fee - $adminFeeWithoutGst;
        $total_admin_fee = round($admin_fee - $gst);
        // $gst = round(($admin_fee * 18) / 100);
        // $total_admin_fee = round($admin_fee + $gst);

        $data['roi'] = $roi;
        $data['tenure'] = $tenure;
        $data['repayment_amount'] = round($repayment_amount);
        // $data['admin_fee'] = $total_admin_fee;
        $data['admin_fee'] = $admin_fee;
        $data['adminFeeWithGST'] = $gst;
        $data['adminFeeGST'] = $gst;
        $data['total_admin_fee'] = $total_admin_fee;
        // $data['total_admin_fee'] = $admin_fee;
        $data['final_foir_percentage'] = number_format((($loan_recommended + $obligations) / $monthly_salary) * 100, 2);
        $data['foir_enhanced_by'] = number_format($data['final_foir_percentage'] - $eligible_foir_percentage, 2);
        $data['pre_emi_tenure'] = $pre_emi_tenure;
        $data['pre_emi_interest'] = round($pre_emi_interest);
        $data['fintech_fee'] = $fintech_fee;
        $data['fintech_fee_gst'] = $fintech_fee_gst;
        $data['fintech_fee_with_gst'] = $fintech_fee_with_gst;
        $data['first_emi_due_date'] = $first_emi_due_date;
        $data['first_emi_start_date'] = $first_emi_start_date;
        $data['net_disbursal_amount'] = round($loan_recommended - $total_admin_fee - $fintech_fee_with_gst - $pre_emi_interest);

        return $data;
    }

    public function get_last_residence_proof($pancard) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($pancard)) {
            return null;
        }

        $today_date = date("Y-m-d");

        $query = "SELECT COUNT(docs_id) as residence_proof_docs_count FROM docs D  INNER JOIN
        (SELECT MAX(L.lead_id),L.pancard FROM leads L WHERE L.lead_status_id=16 GROUP BY L.pancard) as LD
        ON (D.pancard=LD.pancard)
        WHERE LD.pancard='$pancard' AND D.docs_type='PRESENT_ADDRESS_PROOF'";

        $doc_details = $this->db->query($query);

        if ($doc_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["data"] = $doc_details->row_array();
        }

        return $return_array;
    }

    public function getMasterMarketingVendor($username) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($username)) {
            return null;
        }

        $query = "SELECT * FROM master_marketing_vendor WHERE mmv_api_username = '" . $username . "' AND mmv_active = 1";

        $vendor_details = $this->db->query($query);

        if ($vendor_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["data"] = $vendor_details->row_array();
        }

        return $return_array;
    }

    public function checkMarketingCompanyLeadId($lead_id) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($lead_id)) {
            return null;
        }

        $query = "SELECT lead_marketing_company_lead_id FROM leads WHERE lead_marketing_company_lead_id = '" . $lead_id . "'";

        $marketing_company_lead_id = $this->db->query($query);

        if ($marketing_company_lead_id->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["data"] = $marketing_company_lead_id->row_array();
        }

        return $return_array;
    }

    public function checkDedupeExternal($mobile) {
        $cp_query = "SELECT cp_id FROM customer_profile WHERE cp_mobile = " . $mobile;
        $cp_id = $this->db->query($cp_query);

        $lead_query = "SELECT lead_id FROM leads WHERE mobile = " . $mobile;
        $lead_id = $this->db->query($lead_query);

        $cif_query = "SELECT cif_id FROM cif_customer WHERE cif_mobile = " . $mobile;
        $cif_id = $this->db->query($cif_query);

        if ($cp_id->num_rows() > 0 || $lead_id->num_rows() > 0 || $cif_id->num_rows() > 0) {
            return 1;
        }

        return 0;
    }

    public function get_lead_list($pancard) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($pancard)) {
            return $return_array;
        }

        $query = "SELECT
                    LD.lead_id,
                    LD.application_no,
                    LD.loan_no,
                    LD.loan_amount,
                    LD.monthly_salary_amount,
                    LD.pincode,
                    LD.status,
                    LD.lead_entry_date,
                    LD.tenure,
                    LD.lead_status_id,
                    CAM.loan_recommended,
                    CAM.tenure as cam_tenure
                FROM
                    leads LD
                    LEFT JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)
                WHERE
                    pancard = '$pancard'
                ORDER BY
                    LD.lead_id DESC
                LIMIT
                    5";
        // $query = "SELECT lead_id, application_no, loan_no, loan_amount, monthly_salary_amount, pincode, status, lead_entry_date, tenure,lead_status_id FROM leads ";
        // $query .= " WHERE pancard='$pancard' ORDER BY lead_id DESC LIMIT 5";

        $doc_details = $this->db->query($query);

        if ($doc_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["leads_data"] = $doc_details->result_array();
        }

        return $return_array;
    }

    public function getJourneyEvents($lead_id) {

        $return_array = array('status' => 0, 'data' => '');

        if (empty($lead_id)) {
            return $return_array;
        }

        $query = "SELECT * FROM lead_journey_events WHERE lje_lead_id=$lead_id";

        $doc_details = $this->db->query($query);

        if ($doc_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["event_data"] = $doc_details->row_array();
        }

        return $return_array;
    }

    public function getProfileEvents($profile_event_id) {

        $return_array = array('status' => 0, 'data' => '');

        if (empty($profile_event_id)) {
            return $return_array;
        }

        $query = "SELECT * FROM profile_journey_events WHERE pje_profile_id=$profile_event_id";

        $doc_details = $this->db->query($query);

        if ($doc_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["event_data"] = $doc_details->row_array();
        }

        return $return_array;
    }

    public function getCollectionHistory($lead_id) {

        $return_array = array('status' => 0, 'data' => '');

        if (empty($lead_id)) {
            return $return_array;
        }

        $query = "SELECT MS.status_name, C.id, C.discount, if(C.payment_verification = 0, 'Pending', IF(C.payment_verification = 1, 'Verified', 'Reject')) as payment_verification, C.lead_id, C.loan_no, C.payment_mode, C.received_amount, C.date_of_recived, C.repayment_type  ";
        $query .= "FROM collection C INNER JOIN master_status MS ON(C.repayment_type=MS.status_id) WHERE collection_active=1 AND collection_deleted=0 AND lead_id=$lead_id ORDER BY id DESC";

        $collection_details = $this->db->query($query);

        if ($collection_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["collection_history"] = $collection_details->result_array();
        }

        return $return_array;
    }

    public function getExcutiveDetails($user_id) {

        $return_array = array('status' => 0, 'data' => array());

        if (empty($user_id)) {
            return $return_array;
        }

        $query = "SELECT  name, email, mobile ";
        $query .= "FROM users WHERE user_id=$user_id";

        $users_details = $this->db->query($query);

        if ($users_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["data"] = $users_details->row_array();
        }

        return $return_array;
    }

    public function get_lastest_cibil_file($pancard) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($pancard)) {
            return $return_array;
        }

        $query = "SELECT lead_id, cibil_id, cibilScore FROM tbl_cibil WHERE cibil_pancard='$pancard' ORDER BY cibil_id DESC LIMIT 1";

        $cibil_details = $this->db->query($query);

        if ($cibil_details->num_rows() > 0) {
            $data = $cibil_details->row_array();
            $return_array["status"] = 1;
            $return_array["cibil_data"] = $data;
        }

        return $return_array;
    }

    public function getLastLeadDetails($pancard) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($pancard)) {
            return $return_array;
        }

        $query = "SELECT lead_id, lead_status_id, lead_entry_date FROM leads WHERE pancard='$pancard' ORDER BY lead_id DESC LIMIT 1";

        $lead_details = $this->db->query($query);

        if ($lead_details->num_rows() > 0) {
            $return_array["status"] = 1;
            $return_array["data"] = $lead_details->row_array();
        }

        return $return_array;
    }

    public function lastCollectionHistory($pancard) {
        $return_array = array('status' => 0, 'data' => '');

        if (empty($pancard)) {
            return $return_array;
        }

        $query = "SELECT
                        MS.status_name,
                        C.id,
                        C.discount,
                        if (
                                C.payment_verification = 0,
                                'Pending',
                                IF (C.payment_verification = 1, 'Verified', 'Reject')
                        ) as payment_verification,
                        C.lead_id,
                        C.loan_no,
                        C.payment_mode,
                        C.received_amount,
                        C.date_of_recived,
                        C.repayment_type
                FROM
                        collection C
                        INNER JOIN leads LD ON (C.lead_id = LD.lead_id)
                        INNER JOIN master_status MS ON (C.repayment_type = MS.status_id)
                WHERE
                        C.collection_active = 1
                        AND C.collection_deleted = 0
                        AND LD.pancard = '$pancard'
                ORDER BY
                        C.id DESC
                LIMIT
                        5";

        $collection_details = $this->db->query($query);

        if (!empty($collection_details)) {
            $return_array["status"] = 1;
            $return_array["collection_history"] = $collection_details->result_array();
        }

        return $return_array;
    }
}
