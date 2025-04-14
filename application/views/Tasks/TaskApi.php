<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class TaskApi extends REST_Controller {

    public $white_listed_ips = array("13.126.63.92");

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }

    /* User Login Api */

    public function UserLogin_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }


        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("full_name", "Name", "required|trim|min_length[3]|max_length[50]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("source", "Lead Source", "required|trim");
            $this->form_validation->set_rules("pancard", "Pancard", "required|trim|exact_length[10]|alpha_numeric");
            $this->form_validation->set_rules("coordinates", "coordinates", "trim");
            $this->form_validation->set_rules("ip", "IP", "trim");
            $this->form_validation->set_rules("city_id", "CITY ID", "required|trim|numeric");
            $this->form_validation->set_rules("income_type", "Income Type", "required|trim|numeric");
            $this->form_validation->set_rules("purposeofloan", "Purpose of Loan", "required|trim|numeric");
            $this->form_validation->set_rules("monthly_salary", "Monthly Salary", "required|trim|numeric|min_length[5]|max_length[7]");
            $this->form_validation->set_rules("loan_amount", "Required Loan Amount", "required|trim|numeric|min_length[4]|max_length[6]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
//                error_reporting(E_ALL);
//                ini_set('display_errors', 1);

                require_once (COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $full_name = strtoupper(strval($post['full_name']));

                $temp_name_array = $this->Tasks->common_parse_full_name($full_name);

                $first_name = !empty($temp_name_array['first_name']) ? strtoupper(strval($temp_name_array['first_name'])) : "";
                $middle_name = !empty($temp_name_array['middle_name']) ? strtoupper(strval($temp_name_array['middle_name'])) : "";
                $last_name = !empty($temp_name_array['last_name']) ? strtoupper(strval($temp_name_array['last_name'])) : "";

                $mobile = !empty($post['mobile']) ? intval($post['mobile']) : "";
                $email = !empty($post['email']) ? strtoupper(strval($post['email'])) : "";

                $city_id = !empty($post['city_id']) ? intval($post['city_id']) : "";
                $pancard = !empty($post['pancard']) ? strval($post['pancard']) : "";
                $income_type = !empty($post['income_type']) ? strval($post['income_type']) : "";
                $purposeofloan = !empty($post['purposeofloan']) ? strval($post['purposeofloan']) : "";
                $loan_amount = !empty($post['loan_amount']) ? doubleval($post['loan_amount']) : "";
                $monthly_salary = !empty($post['monthly_salary']) ? doubleval($post['monthly_salary']) : "";
                $ipAddress = !empty($post['ip']) ? strval($post['ip']) : "";
                $utm_source = !empty($post['utm_source']) ? strval($post['utm_source']) : "";
                $utm_campaign = !empty($post['utm_campaign']) ? strval($post['utm_campaign']) : "";
                $coordinates = !empty($post['coordinates']) ? strval($post['coordinates']) : "";
//                echo "Shubham2";
                $dedupeRequestArray = array('mobile' => $mobile, 'pancard' => $pancard, 'email' => $email);
//                echo "Shubham3";
                $dedupeReturnArray = $CommonComponent->check_customer_dedupe($dedupeRequestArray);
//                print_r($dedupeReturnArray);
//                echo "Shubham4";
                if (!empty($dedupeReturnArray['status']) && $dedupeReturnArray['status'] == 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "You have already applied for the day. Please try again tomorrow."], REST_Controller::HTTP_OK));
                }

                $fetch = 'm_city_state_id';

                $query = $this->Tasks->selectdata(['m_city_id' => $city_id], $fetch, 'master_city');
                if ($query->num_rows() > 0) {
                    $sql = $query->row();
                    $city_state_id = $sql->m_city_state_id;
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => "City is out of range."], REST_Controller::HTTP_OK));
                }



                $purposeofloanname = '';

                $query = $this->Tasks->selectdata(['enduse_id' => $purposeofloan], 'enduse_name', 'master_enduse');

                if ($query->num_rows() > 0) {
                    $sql = $query->row();
                    $purposeofloanname = $sql->enduse_name;
                }


                $otp = rand(1000, 9999);

                if ($mobile == "9953931000") {//Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") {//Hardcoded otp testing... donot remove
                    $otp = 1989;
                } else if ($mobile == "9369815048") {//Hardcoded otp testing... donot remove
                    $otp = 1906;
                }
                $otp = 1111;

                $lead_status_id = 1;

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'email' => $email,
                    'pancard' => $pancard,
                    'otp' => $otp,
                    'user_type' => 'NEW',
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => strval($post['source']),
                    'ip' => $ipAddress,
                    'status' => "LEAD-NEW",
                    'stage' => "S1",
                    'lead_status_id' => $lead_status_id,
                    'qde_consent' => strval($post['checkbox']),
                    'term_and_condition' => "YES",
                    'lead_data_source_id' => strval($post['lead_data_source_id']),
                    'coordinates' => $coordinates,
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'loan_amount' => $loan_amount,
                    'tenure' => 30,
                    'purpose' => $purposeofloanname
                );

                $InsertLeads = $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                if (!$lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Some error occurred due to data set. Please try again."], REST_Controller::HTTP_OK));
                }

                $insertLeadsCustomer = array(
                    'customer_lead_id' => $lead_id,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'sur_name' => $last_name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'pancard' => $pancard,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'created_date' => created_on
                );

                $InsertLeadCustomer = $this->db->insert('lead_customer', $insertLeadsCustomer);

                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "New lead applied");

                if (!empty($pancard)) {

                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();

                    $empquery = $empquery->row();

                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                    $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();

                    if ($cif_query->num_rows() > 0) {

                        $cif_result = $cif_query->row();

                        $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;

                        if ($isdisbursedcheck > 0) {
                            $user_type = "REPEAT";
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "REPEAT CUSTOMER");
                        } else {
                            $user_type = "NEW";
                        }

                        $gender = "MALE";

                        if ($cif_result->cif_gender == 2) {
                            $gender = "FEMALE";
                        }

                        $update_data_lead_customer = [
                            'middle_name' => !empty($middle_name) ? $middle_name : $cif_result->cif_middle_name,
                            'sur_name' => !empty($last_name) ? $last_name : $cif_result->cif_sur_name,
                            'gender' => $gender,
                            'dob' => $cif_result->cif_dob,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $cif_result->cif_office_email,
                            'alternate_mobile' => $cif_result->cif_alternate_mobile,
                            'current_house' => $cif_result->cif_residence_address_1,
                            'current_locality' => $cif_result->cif_residence_address_2,
                            'current_landmark' => $cif_result->cif_residence_landmark,
                            'current_residence_type' => $cif_result->cif_residence_type,
                            'cr_residence_pincode' => $cif_result->cif_residence_pincode,
                            'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                            'current_residence_since' => $cif_result->cif_residence_since,
                            'aa_same_as_current_address' => $cif_result->cif_aadhaar_same_as_residence,
                            'aa_current_house' => $cif_result->cif_aadhaar_address_1,
                            'aa_current_locality' => $cif_result->cif_aadhaar_address_2,
                            'aa_current_landmark' => $cif_result->cif_aadhaar_landmark,
                            'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                            'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                            'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                            'aadhar_no' => $cif_result->cif_aadhaar_no,
                            'updated_at' => created_on
                        ];

                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $cif_result->cif_office_email,
                            'pincode' => $cif_result->cif_residence_pincode,
                            'user_type' => $user_type,
                            'updated_on' => created_on
                        ];

                        $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'customer_id' => $cif_result->cif_number,
                            'employer_name' => $cif_result->cif_company_name,
                            'emp_pincode' => $cif_result->cif_office_pincode,
                            'emp_house' => $cif_result->cif_office_address_1,
                            'emp_street' => $cif_result->cif_office_address_2,
                            'emp_landmark' => $cif_result->cif_office_address_landmark,
                            'emp_residence_since' => $cif_result->cif_office_working_since,
                            'emp_shopNo' => $cif_result->cif_office_address_1,
                            'emp_designation' => $cif_result->cif_office_designation,
                            'emp_department' => $cif_result->cif_office_department,
                            'emp_employer_type' => $cif_result->cif_company_type_id,
                            'emp_website' => $cif_result->cif_company_website,
                            'emp_email' => $cif_result->cif_office_email,
                            'state_id' => $cif_result->cif_office_state_id,
                            'city_id' => $cif_result->cif_office_city_id,
                            'monthly_income' => $monthly_salary,
                            'income_type' => $income_type
                        ];
                    } else {
                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'monthly_income' => $monthly_salary,
                            'income_type' => $income_type
                        ];
                    }

                    if (!empty($emp_id)) {
                        $insert_customer_employement['updated_on'] = created_on;
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = created_on;
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }
                }




                $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                if ($return_eligibility_array['status'] == 2) {
                    return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                }


                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 1,
                    'lot_otp_trigger_time' => created_on,
                );

                $InsertOTP = $this->db->insert('leads_otp_trans', $insertDataOTP);
                $lead_otp_id = $this->db->insert_id();

                $sms_input_data = array();
                $sms_input_data['mobile'] = $mobile;
                $sms_input_data['name'] = $full_name;
                $sms_input_data['otp'] = $otp;

                $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");

                if (!empty($lead_id) && !empty($lead_otp_id)) {
//                        $this->Tasks->email_appointment_schedule_with_link($lead_id, $email, $first_name);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'User Contact Details Added Successfully.', 'mobile' => $mobile, 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Record'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* User Resend API */

    public function ResendOTP_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|exact_length[10]|numeric");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $mobile = intval($post['mobile']);
                $lead_id = intval($post['lead_id']);

                $otp = rand(1000, 9999);
                /*
                if ($mobile == "9953931000") {//Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") {//Hardcoded otp testing... donot remove
                    $otp = 1989;
                }
                */
                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $query = $this->db->select('lot_lead_id')->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->get();
                $result = $query->row();
                $existing_lead_id = $result->lot_lead_id;

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 1,
                    'lot_otp_trigger_time' => created_on,
                );

                $query = $this->db->select('lot_lead_id')->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->get();
                if ($query->num_rows() > 3) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'You can not resend otp more than 3 times.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $InsertOTP = $this->db->insert('leads_otp_trans', $insertDataOTP);
                $update_lead = $this->db->set('otp', $otp)->where('lead_id', $lead_id)->update('leads');

                if ($InsertOTP && $update_lead) {
                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = "Customer";
                    $sms_input_data['otp'] = $otp;

                    require_once (COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                    json_encode($this->response(['Status' => 1, 'Message' => 'OTP resend successfully', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed to resend OTP.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* User Verified API */

    public function VerifyAppliedCustomerOTP_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $mobile = intval($post['mobile']);
                $lead_id = intval($this->encrypt->decode($post['lead_id']));
                $otp = strval($post['otp']);

                $query = $this->db->select('lead_id,first_name,mobile, email, lead_status_id,city_id, state_id,loan_amount')->where('lead_id', $lead_id)->from('leads')->get();
                $query_cust = $this->db->select('pancard,first_name,middle_name,sur_name,gender,dob,email,alternate_email,alternate_mobile')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $empquery = $this->db->select('id,monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();

                $result = $query->row();
                $result_cust = $query_cust->row();
                $empquery = $empquery->row();

                $existing_lead_id = $result->lead_id;
                $lead_status_id = $result->lead_status_id;
                $loan_amount = $result->loan_amount;
                $monthly_salary = $empquery->monthly_income;

                $first_name = $result_cust->first_name;
                $middle_name = $result_cust->middle_name;
                $last_name = $result_cust->sur_name;
                $gender = $result_cust->gender;
                $email = $result_cust->email;
                $alternate_email = $result_cust->alternate_email;
                $alternate_mobile = $result_cust->alternate_mobile;
                $pancard = $result_cust->pancard;
                $dob = $result_cust->dob;

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }


                if ($lead_status_id > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $last_row = $this->db->select('lot_id,lot_mobile_otp')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();
                $lastotp = $last_row->lot_mobile_otp;
                $lot_id = $last_row->lot_id;

                if ($lastotp != $otp) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'OTP verification failed. Please try again.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $update_lead_otp_trans_data = [
                    'lot_otp_verify_time' => date("Y-m-d H:i:s"),
                    'lot_otp_verify_flag' => 1,
                ];

                $this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);

                $update_data_leads['lead_is_mobile_verified'] = 1;

                $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                $update_data_lead_customer = [
                    'mobile_verified_status' => "YES",
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                $Customer_data = [
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'sur_name' => $last_name,
                    'gender' => $gender,
                    'dob' => !empty($dob) ? date("d-m-Y", strtotime($dob)) : "",
                    'pancard' => $pancard,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'mobile' => $mobile,
                    'alternate_mobile' => $alternate_mobile,
                    'loan_amount' => $loan_amount,
                    'monthly_salary' => $monthly_salary
                ];

                $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id, docs_sub_type', 'docs_master');
                $tempDetails = $query->result_array();

                $docs_master = array();

                foreach ($tempDetails as $document_data) {
                    $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                }

                return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'Customer_data' => $Customer_data, 'Lead_id' => $lead_id, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getPersonalDetails_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = intval($this->encrypt->decode($post['lead_id']));

                $query = $this->db->select('lead_id, first_name, mobile, email, lead_status_id, city_id, state_id, loan_amount')->where('lead_id', $lead_id)->from('leads')->get();
                $query_cust = $this->db->select('pancard, first_name, middle_name, sur_name, gender, dob, email, alternate_email, alternate_mobile')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $empquery = $this->db->select('id, monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();

                $result = $query->row();
                $result_cust = $query_cust->row();
                $empquery = $empquery->row();

                $existing_lead_id = $result->lead_id;
                $lead_status_id = $result->lead_status_id;
                $mobile = $result->mobile;
                $loan_amount = $result->loan_amount;
                $monthly_salary = $empquery->monthly_income;

                $first_name = $result_cust->first_name;
                $middle_name = $result_cust->middle_name;
                $last_name = $result_cust->sur_name;
                $gender = $result_cust->gender;
                $email = $result_cust->email;
                $alternate_email = $result_cust->alternate_email;
                $alternate_mobile = $result_cust->alternate_mobile;
                $pancard = $result_cust->pancard;
                $dob = $result_cust->dob;

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }


                if ($lead_status_id > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $Customer_data = [
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'sur_name' => $last_name,
                    'gender' => $gender,
                    'dob' => !empty($dob) ? date("d-m-Y", strtotime($dob)) : "",
                    'pancard' => $pancard,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'mobile' => $mobile,
                    'alternate_mobile' => $alternate_mobile,
                    'loan_amount' => $loan_amount,
                    'monthly_salary' => $monthly_salary
                ];

                $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id, docs_sub_type', 'docs_master');
                $tempDetails = $query->result_array();

                $docs_master = array();

                foreach ($tempDetails as $document_data) {
                    $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                }

                return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'Customer_data' => $Customer_data, 'lead_id' => $lead_id, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Personal Details Save Api */

    public function savePersonalDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("first_name", "First Name", "required|trim|min_length[1]|max_length[30]");
            $this->form_validation->set_rules("middle_name", "Middle Name", "trim|min_length[1]|max_length[30]");
            $this->form_validation->set_rules("sur_name", "Sur Name", "trim|min_length[1]|max_length[30]");
            $this->form_validation->set_rules("gender", "Gender", "required|trim");
            $this->form_validation->set_rules("dob", "Date Of Birth", "required|trim");
            $this->form_validation->set_rules("pancard", "Pancard", "required|trim|exact_length[10]|alpha_numeric");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("alternate_mobile", "Alternate Mobile No", "trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("email_office", "Office Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = intval($this->encrypt->decode($post['lead_id']));
                $pancard = strval(strtoupper($post['pancard']));
                $mobile = intval($post['mobile']);
                $middle_name = strval(strtoupper($post['middle_name']));
                $sur_name = strval(strtoupper($post['sur_name']));
                $gender = strval($post['gender']);
                $dob = $post['dob'];
                $alternate_mobile = intval($post['alternate_mobile']);
                $email_personal = strval($post['email_personal']);
                $email_office = strval($post['email_office']);

                $query = $this->db->select('lead_id,lead_status_id,lead_is_mobile_verified,city_id, state_id,customer_id,loan_amount')->where('lead_id', $lead_id)->from('leads')->get();
                $result = $query->row();
                $existing_lead_id = $result->lead_id;
                $lead_is_mobile_verified = $result->lead_is_mobile_verified;
                $city_id = $result->city_id;
                $state_id = $result->state_id;
                $customer_id = $result->customer_id;
                $loan_amount = intval($result->loan_amount);

                $empquery = $this->db->select('id,monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;
                $monthly_income = $empquery->monthly_income;

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_is_mobile_verified != 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application OTP not verified.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_status_id > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $dob = date('Y-m-d', strtotime($dob));
                $existing_customer_flag = false;

                require_once (COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                if (empty($customer_id) && false) {

                    $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();

                    if ($cif_query->num_rows() > 0) {
                        $cif_result = $cif_query->row();
                        $existing_customer_flag = true;
                        $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;
                        $customer_id = $cif_result->cif_number;

                        if ($isdisbursedcheck > 0) {
                            $user_type = "REPEAT";
                        } else {
                            $user_type = "NEW";
                        }

                        $update_data_lead_customer = [
                            'middle_name' => $middle_name,
                            'sur_name' => $sur_name,
                            'gender' => $gender,
                            'dob' => $dob,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $email_office,
                            'alternate_mobile' => $alternate_mobile,
                            'current_house' => $cif_result->cif_residence_address_1,
                            'current_locality' => $cif_result->cif_residence_address_2,
                            'current_landmark' => $cif_result->cif_residence_landmark,
                            'current_residence_type' => $cif_result->cif_residence_type,
                            'cr_residence_pincode' => $cif_result->cif_residence_pincode,
                            'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                            'current_residence_since' => $cif_result->cif_residence_since,
                            'aa_same_as_current_address' => $cif_result->cif_aadhaar_same_as_residence,
                            'aa_current_house' => $cif_result->cif_aadhaar_address_1,
                            'aa_current_locality' => $cif_result->cif_aadhaar_address_2,
                            'aa_current_landmark' => $cif_result->cif_aadhaar_landmark,
                            'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                            'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                            'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                            'aadhar_no' => $cif_result->cif_aadhaar_no,
                            'updated_at' => created_on
                        ];

                        $update_cust_leads = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $email_office,
                            'pincode' => $cif_result->cif_residence_pincode,
                            'user_type' => $user_type,
                            'updated_on' => created_on
                        ];

                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'customer_id' => $cif_result->cif_number,
                            'employer_name' => $cif_result->cif_company_name,
                            'emp_pincode' => $cif_result->cif_office_pincode,
                            'emp_house' => $cif_result->cif_office_address_1,
                            'emp_street' => $cif_result->cif_office_address_2,
                            'emp_landmark' => $cif_result->cif_office_address_landmark,
                            'emp_residence_since' => $cif_result->cif_office_working_since,
                            'emp_shopNo' => $cif_result->cif_office_address_1,
                            'emp_designation' => $cif_result->cif_office_designation,
                            'emp_department' => $cif_result->cif_office_department,
                            'emp_employer_type' => $cif_result->cif_company_type_id,
                            'emp_website' => $cif_result->cif_company_website,
                            'emp_email' => $cif_result->cif_office_email,
                            'city_id' => $cif_result->cif_office_city_id,
                            'state_id' => $cif_result->cif_office_state_id,
                            'updated_on' => created_on,
                        ];

                        if (!empty($emp_id)) {
                            $insert_customer_employement['updated_on'] = created_on;
                            $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                        } else {
                            $insert_customer_employement['created_on'] = created_on;
                            $this->db->insert('customer_employment', $insert_customer_employement);
                        }

                        $update_leads = $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                        $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                        if ($return_eligibility_array['status'] == 2) {
                            return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                        }

                        if ($update_leads == true && $update_cust_leads == true) {
                            return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id], REST_Controller::HTTP_OK));
                        } else {
                            return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                        }
                    }
                }

                if ($existing_customer_flag == false) {


                    $dataCustomer = array(
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'dob' => $dob,
//                        'pancard' => $pancard,
                        'mobile' => $mobile,
                        'alternate_mobile' => $alternate_mobile,
                        'email' => $email_personal,
                        'alternate_email' => $email_office,
                        'updated_at' => created_on,
                    );

                    $dataLeads = array(
//                        'pancard' => $pancard,
                        'mobile' => $mobile,
                        'email' => $email_personal,
                        'alternate_email' => $email_office,
                        'updated_on' => created_on,
                    );

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'customer_id' => $customer_id,
                        'emp_email' => $email_office,
                        'updated_on' => created_on,
                    ];

                    if (!empty($emp_id)) {
                        $insert_customer_employement['updated_on'] = created_on;
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = created_on;
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }

                    $res_lead = $this->db->where('lead_id', $lead_id)->update('leads', $dataLeads);

                    $res_customer = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $dataCustomer);

                    $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                    if ($return_eligibility_array['status'] == 2) {
                        return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                    }

                    if ($res_lead == true && $res_customer == true) {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated..', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id, 'loan_amount' => $loan_amount, 'monthly_salary' => $monthly_income], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                    }
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* saveApplicationDetails API */

    public function saveApplicationDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("obligations", "Exisitng EMI", "trim|numeric|is_natural");
            $this->form_validation->set_rules("state_id", "State", "required|trim|numeric");
            $this->form_validation->set_rules("city_id", "City", "required|trim");
            $this->form_validation->set_rules("pincode", "Pincode", "required|trim|numeric|exact_length[6]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = intval($this->encrypt->decode($post['lead_id']));
                $input_state_id = intval($post['state_id']);
                $input_city_id = intval($post['city_id']);
                $pincode = strval($post['pincode']);
                $loan_amount = doubleval($post['loan_amount']);
                $monthly_income = doubleval($post['monthly_income']);
                $obligations = doubleval($post['obligations']);

                $query = $this->db->select('lead_id,mobile,email,lead_reference_no,lead_status_id,lead_is_mobile_verified,city_id, state_id,customer_id')->where('lead_id', $lead_id)->from('leads')->get();
                $result = $query->row();
                $existing_lead_id = $result->lead_id;
                $lead_is_mobile_verified = $result->lead_is_mobile_verified;
                $city_id = $result->city_id;
                $state_id = $result->state_id;
                $customer_id = $result->customer_id;
                $mobile = $result->mobile;
                $email = $result->email;
                $lead_status_id = $result->lead_status_id;
                $lead_reference_no = $result->lead_reference_no;

                $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_is_mobile_verified != 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application OTP not verified.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_status_id > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been move to next step.'], REST_Controller::HTTP_OK));
                }



                $conditions = ['customer_lead_id' => $lead_id];
                $fetch = 'first_name, sur_name, mobile, gender,pancard';
                $query = $this->Tasks->selectdata($conditions, $fetch, 'lead_customer');
                $sql = $query->row();
                $first_name = $sql->first_name;
                $last_name = $sql->sur_name;
                $mobile = $sql->mobile;
                $gender = $sql->gender;
                $pancard = $sql->pancard;

                $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id, docs_sub_type', 'docs_master');
                $tempDetails = $query->result_array();

                $docs_master = array();

                foreach ($tempDetails as $document_data) {
                    $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                }

                if (empty($lead_reference_no)) {

                    $ReferenceCode = $this->Tasks->generateReferenceCode($lead_id, $first_name, $last_name, $mobile);
                    $dataleads = array(
                        'lead_id' => $lead_id,
                        'lead_reference_no' => $ReferenceCode,
                        'loan_amount' => doubleval($post['loan_amount']),
                        'term_and_condition' => "YES",
                        'obligations' => (!empty($post['obligations']) ? doubleval($post['obligations']) : 0),
                        'state_id' => $input_state_id,
                        'city_id' => $input_city_id,
                        'pincode' => ($post['pincode'] ? strval($post['pincode']) : ''),
                    );

                    $datacustomer = array(
                        'customer_lead_id' => $lead_id,
                        'state_id' => $input_state_id,
                        'city_id' => $input_city_id,
                        'cr_residence_pincode' => ($post['pincode'] ? strval($post['pincode']) : ''),
                    );

                    $update_customer = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $datacustomer);

                    $update_lead = $this->db->where('lead_id', $lead_id)->update('leads', $dataleads);

                    if ($update_lead && $update_customer) {

                        $insert_customer_employement = array(
                            'lead_id' => $lead_id,
                            'monthly_income' => doubleval($post['monthly_income']),
                            'created_on' => created_on,
                        );

                        if (!empty($emp_id)) {
                            $insert_customer_employement['updated_on'] = created_on;
                            $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                        } else {
                            $insert_customer_employement['created_on'] = created_on;
                            $this->db->insert('customer_employment', $insert_customer_employement);
                        }


                        $dataSMS = [
                            'title' => ($gender == "MALE") ? "Mr." : "Ms.",
                            'name' => $first_name,
                            'mobile' => $mobile,
                        ];

                        require_once (COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                        if ($return_eligibility_array['status'] == 2) {
                            return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                        }

                        $sms_input_data = array();
                        $sms_input_data['mobile'] = $mobile;
                        $sms_input_data['name'] = (($gender == "MALE") ? "Mr. " : "Ms. ") . $first_name;
                        $sms_input_data['refrence_no'] = $ReferenceCode;

                        $CommonComponent->payday_sms_api(2, $lead_id, $sms_input_data);

                        $CommonComponent->sent_lead_thank_you_email($lead_id, $email, $first_name, $ReferenceCode);

                        return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been submitted successfully.', 'reference_no' => $ReferenceCode, 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been submitted successfully.', 'reference_no' => $lead_reference_no, 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
                }
            }
        } else {
            $result_data = array('status' => 0, 'message' => 'Request Failed, Try Again.');
            echo json_encode($result_data);
            exit;
        }
    }

    /* Upload Customer Documents */

    public function saveCustomerDocument_post() {
        $lead_id = 0;
        $apiStatusId = 0;
        $mobile = '';
        $apiStatusMessage = "";
        $ReferenceCode = "";
        $docs_master = [];

        $input_data = file_get_contents("php://input");
        
        try {

            $email_message = "Step 1";

            if ($input_data) {
                $post = $this->security->xss_clean(json_decode($input_data, true));
            } else {
                $post = $this->security->xss_clean($_POST);
            }

            $email_message .= "<br/>Step 2";

            if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
                throw new Exception("UnAuthorized Access.");
            }

            $email_message .= "<br/>Step 3";

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $email_message .= "<br/>Step 4";
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
                $this->form_validation->set_rules("docs_type", "Docs Type", "required|trim");
                $this->form_validation->set_rules("password", "Password", "trim");
                //$this->form_validation->set_rules("file", "Document", "required|trim");
                $this->form_validation->set_rules("ext", "Extension", "required|trim");
                $email_message .= "<br/>Step 5";
                if ($this->form_validation->run() == FALSE) {
                    $email_message .= "<br/>Step 6";
                    throw new Exception(validation_errors());
                } else {
                    $email_message .= "<br/>Step 7";
                    $lead_id       = intval($this->encrypt->decode($post['lead_id']));
                    $document_id   = strval($post['docs_type']);
                    $password      = strval($post['password']);
                    $ReferenceCode = strval($post['refrence_no']);
                    $ip            = strval($post['ip']);
                    $ext           = strval($post['ext']);
                    //$file          = htmlspecialchars($post['file']);
                    $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id, docs_sub_type', 'docs_master');
                    $tempDetails = $query->result_array();

                    $docs_master = array();

                    foreach ($tempDetails as $document_data) {
                        $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                    }

                    $query = $this->db->select('lead_id,customer_id,lead_status_id,lead_reference_no, pancard, mobile,lead_is_mobile_verified')->where('lead_id', $lead_id)->from('leads')->get();
                    $email_message .= "<br/>Step 8";
                    if ($query->num_rows() > 0) {

                        $email_message .= "<br/>Step 9";

                        $result = $query->row();

                        $existing_lead_id = $result->lead_id;
                        $pancard = $result->pancard;
                        $ReferenceCode = $result->lead_reference_no;
                        $lead_status_id = $result->lead_status_id;
                        $customer_id = $result->customer_id;
                        $mobile = $result->mobile;
                        $lead_is_mobile_verified = $result->lead_is_mobile_verified;

                        if ($existing_lead_id != $lead_id) {
                            $email_message .= "<br/>Step 10";
                            throw new Exception('Invalid access for the application.');
                        }

                        if ($lead_is_mobile_verified != 1) {
                            $email_message .= "<br/>Step 11";
                            throw new Exception('Application OTP not verified.');
                        }
                        
                        if ($lead_status_id > 1) {
                            $email_message .= "<br/>Step 12";
                            throw new Exception('Your application has been move to next step.');
                        }

                        if ($ext != 'pdf' && in_array($document_id, array(6, 7, 13))) {
                            throw new Exception('Only pdf file allowed.');
                        }

                        if (!in_array($ext, array('jpg', 'jpeg', 'png')) && in_array($document_id, array(18))) {
                            throw new Exception('Only jpg, jpeg, png file allowed.');
                        }

                        $query = $this->db->select('id,docs_type,docs_sub_type')->where('id', $document_id)->from('docs_master')->get();

                        if ($query->num_rows() == 0) {
                            $email_message .= "<br/>Step 13";
                            throw new Exception('Document type is out of range.');
                        } else {
                            $email_message .= "<br/>Step 14";
                            $documentMaster = $query->row();
                            $document_type_id = $documentMaster->id;
                            $docs_type = $documentMaster->docs_type;
                            $docs_sub_type = $documentMaster->docs_sub_type;
                        }                    
                        /*
                        $image_name = $lead_id . "_" . $document_type_id . "_" . date("YmdHis") . "_" . rand(1000, 9999);
                        $email_message .= "<br/>Step 15";
                        $imgUrl = $image_name . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $imgUrl;
                        $flag = file_put_contents($image_upload_dir, base64_decode($post['file']));
                        
                        $email_message .= "<br/>Step 16";
                        
                        $image_size = filesize($image_upload_dir);
                        $image_size_kb = number_format($image_size / 1024 / 1024);
                        if ($image_size_kb > 2) {
                            $email_message .= "<br/>Step 17";
                            throw new Exception('Maximum upload size can be upto 2 mb.');
                        }
                        $email_message .= "<br/>Step 18";

                        */
                        

                        //if ($flag) {   
                            $upload_return = uploadDocument(base64_decode($post['file']),$lead_id,1,$ext);
                            if($upload_return['status'] == 1) 
                            {
                               $imgUrl = $upload_return['file_name'];
                            }
                            else{
                               $email_message .= "<br/>Step 15";
                               throw new Exception('Please upload the document!'); 
                            }
                            
                            $insert_document_data = [
                                "lead_id" => $lead_id,
                                "pancard" => $pancard,
                                "mobile" => $mobile,
                                "docs_type" => $docs_type,
                                "sub_docs_type" => $docs_sub_type,
                                "file" => $imgUrl,
                                "docs_master_id" => $document_type_id,
                                "ip" => $ip,
                                "created_on" => date("Y-m-d H:i:s")
                            ];                            
                            if (!empty($customer_id)) {
                                $insert_document_data['customer_id'] = $customer_id;
                            }
                            if (!empty($password)) {
                                $insert_document_data['pwd'] = $password;
                            }

                            $result = $this->db->insert('docs', $insert_document_data);

                            $docsId = $this->db->insert_id();
                            $email_message .= "<br/>Step 16";
                            if (!empty($docsId)) {
                                $email_message .= "<br/>Step 17";
                                $apiStatusId = 1;
                                $apiStatusMessage = "Documents uploaded Successfully. You can upload more documents";
                            } else {
                                $email_message .= "<br/>Step 18";
                                throw new Exception('Unable to upload docs. You can contact to customer care.');
                            }
                        /*} else {
                            $email_message .= "<br/>Step 22";
                            throw new Exception('Failed to save Docs. Try Again');
                        }*/
                        $email_message .= "<br/>Step 19";
                    }
                    $email_message .= "<br/>Step 20";
                }
                $email_message .= "<br/>Step 21";
            } else {
                $email_message .= "<br/>Step 22";
                throw new Exception('Request Method Post Failed.');
            }
        } catch (Exception $e) {
            $apiStatusId = 0;
            $apiStatusMessage = $e->getMessage();
        }

        $email_message .= "<br/>Step 23";
        //return json_encode($this->response(['Status' =>0, 'Message' => $insert_document_data], REST_Controller::HTTP_OK));
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Mobile' => $mobile, 'lead_id' => $lead_id, 'refrence_no' => $ReferenceCode, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
    }

    public function getUploadedDocs_post() {
        $input_data = file_get_contents("php://input");

        $status = 0;

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Invalid Access", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $lead_id = intval($this->encrypt->decode($post['lead_id']));

                $this->db->select("lead_id,lead_reference_no");
                $this->db->from('leads');
                $this->db->where("lead_id", $lead_id);
                $sql = $this->db->get();

                if (!empty($sql->num_rows())) {
                    $leadDetails = $sql->row_array();
                    $lead_id = $leadDetails['lead_id'];
                    $lead_reference_no = $leadDetails['lead_reference_no'];
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application reference is missing.'], REST_Controller::HTTP_OK));
                }

                require_once (COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $docs_data = $CommonComponent->check_customer_mandatory_documents($lead_id);

                if ($docs_data['status'] == 1) {
                    $status = 1;
                    $Message = "All document avialbe to process application.";
                } else {
                    $Message = $docs_data['error'];
                }

                return json_encode($this->response(['Status' => $status, 'Message' => $Message, 'reference_no' => $lead_reference_no, 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Customer Enuiry Api */

    public function SaveContactEnquiry_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("name", "Name", "required|trim");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("city", "City", "trim");
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $city_name = strval($post['city']);
                $getCityId = $this->Tasks->getcustId('master_city', 'm_city_name', $city_name, 'm_city_id');

                $DataContactEnquiry = array(
                    'cust_enquiry_name' => strval(strtoupper($post['name'])),
                    'cust_enquiry_mobile' => strval($post['mobile']),
                    'cust_enquiry_email' => strval(strtoupper($post['email'])),
                    'cust_enquiry_loan_amount' => doubleval($post['loan_amount']),
                    'cust_enquiry_city_name' => strval(strtoupper($post['city'])),
                    'cust_enquiry_city_id' => $getCityId,
                    "cust_enquiry_data_source_id" => 1,
                    "cust_enquiry_type_id" => 1,
                    "cust_enquiry_ip_address" => strval($post['ip']),
                    "cust_enquiry_geo_coordinates" => strval($post['coordinates']),
                    "cust_enquiry_created_datetime" => created_on
                );

                $res = $this->db->insert('customer_enquiry', $DataContactEnquiry);
                if ($res == true) {
                    return json_encode($this->response(['message' => 'Contact Enquiry Save Successfully.', 'Status' => 1], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Save Enquiry.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Customer Enuiry Api */

    public function SaveContactUsEnquiry_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("name", "Name", "required|trim");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("message", "Message", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $DataContactEnquiry = array(
                    'cust_enquiry_name' => strval(strtoupper($post['name'])),
                    'cust_enquiry_mobile' => intval($post['mobile']),
                    'cust_enquiry_email' => strval(strtoupper($post['email'])),
                    'cust_enquiry_remarks' => strval(strtoupper($post['message'])),
                    "cust_enquiry_data_source_id" => 1,
                    "cust_enquiry_type_id" => 2,
                    "cust_enquiry_ip_address" => strval($post['ip']),
                    "cust_enquiry_geo_coordinates" => strval($post['coordinates']),
                    "cust_enquiry_created_datetime" => created_on
                );

                $res = $this->db->insert('customer_enquiry', $DataContactEnquiry);
                if ($res == true) {
                    return json_encode($this->response(['message' => 'Contact Enquiry Save Successfully.', 'Status' => 1], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Save Enquiry.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Subscription Api */

    public function Subscription_post() {

        $message = '';
        $status = 0;
        $encrypted_id = '';

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $email = strtoupper(strval($post['email']));
                $select = "SELECT es_id, es_email, es_email_created_on FROM email_subscribe WHERE es_active=1 AND es_email='$email'";
                $result = $this->db->query($select)->row();

//                    echo json_encode($result);
//                    exit;

                if (empty($result)) {
                    $DataContactEnquiry = array(
                        "es_email" => $email,
                        "es_email_verify_datetime" => 1,
                        "es_email_data_source_id" => 1,
                        "es_email_created_on" => created_on
                    );
                    $res = $this->db->insert('email_subscribe', $DataContactEnquiry);
                    $lead_id = $this->db->insert_id();
                    $message = 'Email subscription link send successfully to your email.';
                    $status = 1;

                    $encrypted_id = $this->encrypt->encode($lead_id);
                    $subject = "Bharat Loan - Confirm your email on Bharat Loan";
                    $maillink = 'https://www.bharatloan.com/subscribe-email-verify' . "/" . $encrypted_id;

                    $html = '<!DOCTYPE html>
                                    <html xmlns="http://www.w3.org/1999/xhtml">
                                    <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                    <title>Untitled Document</title>
                                    </head>
                                    <body>
                                    <table width="600" border="0" align="center" style="font-family:Arial, Helvetica, sans-serif;border:solid 1px #ddd;padding:10px;background: #f9f9f9;">
                                    <tr>
                                    <td width="975" align="center"><img src="https://www.bharatloan.com/public/images/brand_logo.png" style="width:150px;"></td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;"><table width="618" border="0" style="text-align:center;padding:20px;background: #fff;">
                                    <tr>
                                    <td width="auto" align="center"><img src="https://www.bharatloan.com/public/emailimages/verification-email.png" style="width:auto;"></td>
                                    </tr>                                    
                                    <tr>
                                    <td width="612" style="font-size:16px;"><h1>Thank you for joining Bharat Loan!</h1></td>
                                    </tr>
                                    <tr>
                                    <td width="612" style="font-size:16px;">Please confirm your email address by clicking the button below.</td>
                                    </tr>
                                    <tr>
                                    <td >&nbsp;</td>
                                    </tr>
                                    <tr>
                                    <td><a href="' . $maillink . '" style="background: #e7305a;padding: 9px 20px;color: #fff;text-decoration: blink;border-radius: 3px;">Verify Email</a></td>
                                    </tr>
                                    </table></td>
                                    </tr>
                                    <tr>
                                    <td align="center">&nbsp;</td>
                                    </tr>
                                    <tr>
                                    <td align="center">Follow Us On</td>
                                    </tr>
                                    <tr>
                                    <td align="center">
                                    <a href="https://www.facebook.com/BharatLoan-105632195732824" target="_blank">
                                    <img src="https://www.bharatloan.com/public/image/bharatloan-facebook.png" class="socil-t" alt="bharatloan-facebook" style="width:30px;"></a>
                                    <a href="https://twitter.com/bharatloans" target="_blank"><img src="https://www.bharatloan.com/public/image/bharatloan-twitter.png" class="socil-t" alt="bharatloan-twitter" style="width:30px;"></a>
                                    <a href="https://www.linkedin.com/company/bharatloan" target="_blank"><img src="https://www.bharatloan.com/public/image/bharatloan-linkdin.png" class="socil-t" alt="bharatloan-linkdin" style="width:30px;"></a>
                                    <a href="https://www.instagram.com/bharatloan_india" target="_blank"><img src="https://www.bharatloan.com/public/image/bharatloan-instagram.png" class="socil-t" alt="bharatloan-instagram" style="width:30px;"></a>
                                    <a href="https://www.youtube.com/channel/UCUwrJB1IMvDiMctHHRKDLxw" target="_blank"><img src="https://www.bharatloan.com/public/image/bharatloan-youtube.png" class="socil-t" alt="bharatloan-youtube" style="width:30px;"></a>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="center">For Latest Updates &amp; Offers</td>
                                    </tr>
                                    </table>
                                    </body>
                                    </html>';

                    require_once(COMPONENT_PATH . 'CommonComponent.php');
                    $CommonComponent = new CommonComponent();

                    $return_array = $CommonComponent->call_sent_email($email, $subject, $html);
                } else {
                    $lead_id = $result->es_id;
                    $message = 'You have already subscribed to our services.';
                    $status = 2;
                }

                if (!empty($lead_id)) {
                    $result_data = array('message' => $message, 'Status' => $status, 'EncryptedId' => $encrypted_id);
                    echo json_encode($result_data);
                    exit;
                } else {
                    echo json_encode($this->response(['Status' => $status, 'Message' => 'Unable to send email.'], REST_Controller::HTTP_OK));
                    exit;
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Subscription Verify Api */

    public function SubscriptionVerify_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        $id = intval($post['id']);
        $decrypted_lead_id = $this->encrypt->decode($id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("id", "ID", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $DataContactEnquiry = array(
                    "es_email_verify" => 1,
                    "es_email_verify_datetime" => created_on
                );

                $query = $this->db->select('es_email_verify')->where('es_id', $decrypted_lead_id)->from('email_subscribe')->get();
                $result = $query->row();
                $check_existing_verify = $result->es_email_verify;

                if ($check_existing_verify == 1) {
                    $result_data = array('message' => 'This Email ID is Already Verified', 'Status' => 0);
                    echo json_encode($result_data);
                    exit;
                }

                $this->db->where('es_id', $decrypted_lead_id);
                $update = $this->db->update('email_subscribe', $DataContactEnquiry);

                if ($update == true) {
                    $result_data = array('message' => 'Email Verification Successfully.', 'Status' => 1);
                    echo json_encode($result_data);
                    exit;
                } else {
                    echo json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Verifiy email.'], REST_Controller::HTTP_OK));
                    exit;
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /*     * *************
     * Lending Pages for all website other than
     */

    public function lendingLeadSave_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("first_name", "Name", "required|trim");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("gender", "Gender", "required|trim");
            $this->form_validation->set_rules("dob", "Date Of Birth", "required|trim");
            $this->form_validation->set_rules("pan", "Pan card", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("alternate_mobile", "Alternate Mobile No", "trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("obligations", "Obligations", "trim|numeric|is_natural");
            $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("email_office", "Office Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("source", "Lead Source", "trim");
            $this->form_validation->set_rules("coordinates", "coordinates", "trim");
            $this->form_validation->set_rules("ip", "IP", "trim");
            $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric");
            $this->form_validation->set_rules("state_id", "State", "required|trim");
            $this->form_validation->set_rules("city", "City", "required|trim");
            $this->form_validation->set_rules("pin", "Pincode", "required|trim|numeric|min_length[6]|max_length[6]");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $full_name = strtoupper(strval($post['first_name']));

                $parse_name = $this->Tasks->common_parse_full_name($full_name);

                $first_name = $parse_name['first_name'];
                $middle_name = $parse_name['middle_name'];
                $last_name = $parse_name['last_name'];

                $mobile = intval($post['mobile']);
                $email = strtoupper(strval($post['email_personal']));
                $pancard = strtoupper(strval($post['pan']));
                // echo $pancard; exit;
                $dob = date('Y-m-d', strtotime($post['dob']));

                $otp = rand(1000, 9999);
                // $otp = 1234;

                $insertDataLeads = array(
                    'company_id' => intval($post['company_id']),
                    'product_id' => 1,
                    'user_type' => 'NEW',
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'otp' => $otp,
                    'alternate_email' => strval($post['email_office']),
                    'pancard' => $pancard,
                    'loan_amount' => doubleval($post['loan_amount']),
                    'obligations' => ($post['obligations'] ? doubleval($post['obligations']) : ''),
                    'state_id' => intval($post['state_id']),
                    'city' => strval($post['city']),
                    'pincode' => ($post['pin'] ? strval($post['pin']) : ''),
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => strval($post['source']),
                    'ip' => strval($post['ip']),
                    'lead_status_id' => 1,
                    'loan_amount' => doubleval($post['loan_amount']),
                    'qde_consent' => strval($post['checkbox']),
                    // 'lead_data_source_id'   => $post['lead_data_source_id'],
                    'coordinates' => ($post['coordinates']) ? strval($post['coordinates']) : "",
                    'utm_source' => ($post['utm_source'] ? strval($post['utm_source']) : ''),
                    'utm_campaign' => ($post['utm_campaign'] ? strval($post['utm_campaign']) : ''),
                );

                $InsertLeads = $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                $insertLeadsCustomer = array(
                    'customer_lead_id' => $lead_id,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'sur_name' => $last_name,
                    'dob' => $dob,
                    'gender' => strtoupper(strval($post['gender'])),
                    'pancard' => $pancard,
                    'mobile' => intval($post['mobile']),
                    'alternate_mobile' => intval($post['alternate_mobile']),
                    'email' => $email,
                    'alternate_email' => strval($post['email_office']),
                    'state_id' => intval($post['state_id']),
                    'current_city' => strval($post['city']),
                    'cr_residence_pincode' => ($post['pin'] ? strval($post['pin']) : ''),
                    'created_date' => created_on
                );

                $customer_emp = array(
                    'lead_id' => $lead_id,
                    'company_id' => intval($post['company_id']),
                    'product_id' => 1,
                    'monthly_income' => doubleval($post['monthly_income']),
                    'created_on' => created_on,
                );
                $insert_cust_emp = $this->db->insert('customer_employment', $customer_emp);
                $InsertLeadCustomer = $this->db->insert('lead_customer', $insertLeadsCustomer);

                $getRefNum = $this->generateReferencenumber($lead_id);
                $update_lead = $this->db->set('lead_reference_no', $getRefNum)->where('lead_id', $lead_id)->update('leads');

                $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();
                if ($cif_query->num_rows() > 0) {
                    $cif_result = $cif_query->row();

                    $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;
                    if ($isdisbursedcheck > 0) {
                        $user_type = "REPEAT";
                    } else {
                        $user_type = "NEW";
                    }

                    $update_data_lead_customer = [
                        // 'middle_name'                   => $cif_result->cif_middle_name,
                        // 'sur_name'                      => $cif_result->cif_sur_name,
                        // 'gender'                        => $cif_result->cif_gender,
                        // 'dob'                           => $cif_result->cif_dob,
                        // 'pancard'                       => $cif_result->cif_pancard,
                        // 'alternate_email'               => $cif_result->cif_office_email,
                        // 'alternate_mobile'              => $cif_result->cif_alternate_mobile,
                        'current_house' => $cif_result->cif_residence_address_1,
                        'current_locality' => $cif_result->cif_residence_address_2,
                        'current_landmark' => $cif_result->cif_residence_landmark,
                        'current_residence_type' => $cif_result->cif_residence_type,
                        // 'cr_residence_pincode'          => $cif_result->cif_residence_pincode,
                        'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                        'current_residence_since' => $cif_result->cif_residence_since,
                        'aa_same_as_current_address' => $cif_result->cif_aadhaar_same_as_residence,
                        'aa_current_house' => $cif_result->cif_aadhaar_address_1,
                        'aa_current_locality' => $cif_result->cif_aadhaar_address_2,
                        'aa_current_landmark' => $cif_result->cif_aadhaar_landmark,
                        'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                        'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                        'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                        'aadhar_no' => $cif_result->cif_aadhaar_no,
                        'updated_at' => created_on
                    ];

                    $insert_data_leads = [
                        'customer_id' => $cif_result->cif_number,
                        // 'pancard'           => $cif_result->cif_pancard,
                        // 'alternate_email'   => $cif_result->cif_office_email,
                        // 'pincode'           => $cif_result->cif_residence_pincode,
                        'user_type' => $user_type,
                        'updated_on' => created_on
                    ];

                    $update_customer_employement = [
                        'lead_id' => $lead_id,
                        'customer_id' => $cif_result->cif_number,
                        'employer_name' => $cif_result->cif_company_name,
                        'emp_pincode' => $cif_result->cif_office_pincode,
                        'emp_house' => $cif_result->cif_office_address_1,
                        'emp_street' => $cif_result->cif_office_address_2,
                        'emp_landmark' => $cif_result->cif_office_address_landmark,
                        'emp_residence_since' => $cif_result->cif_office_working_since,
                        'emp_shopNo' => $cif_result->cif_office_address_1,
                        'emp_designation' => $cif_result->cif_office_designation,
                        'emp_department' => $cif_result->cif_office_department,
                        'emp_employer_type' => $cif_result->cif_company_type_id,
                        'emp_website' => $cif_result->cif_company_website,
                        'emp_email' => $cif_result->cif_office_email,
                        'updated_on' => created_on,
                    ];

                    $update_cust_emp = $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);
                    $update_leads = $this->db->where('lead_id', $lead_id)->update('leads', $insert_data_leads);
                    $update_cust_leads = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);
                }


                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 1,
                    'lot_otp_trigger_time' => created_on,
                );

                $InsertOTP = $this->db->insert('leads_otp_trans', $insertDataOTP);
                $lead_otp_id = $this->db->insert_id();

                $sms_input_data = array();
                $sms_input_data['mobile'] = $mobile;
                $sms_input_data['name'] = $full_name;
                $sms_input_data['otp'] = $otp;

                require_once (COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                if (isset($lead_id) && isset($lead_otp_id)) {
                    json_encode($this->response(['Status' => 1, 'Message' => 'User Contact Details Added Successfully.', 'mobile' => $mobile, 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Record'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function generateReferencenumber($lead_id) {
        $conditions = ['customer_lead_id' => $lead_id];
        $fetch = 'first_name, sur_name, mobile, gender';
        $query = $this->Tasks->selectdata($conditions, $fetch, 'lead_customer');
        $sql = $query->row();
        $first_name = $sql->first_name;
        $last_name = $sql->sur_name;
        $mobile = $sql->mobile;

        return $ReferenceCode = $this->Tasks->generateReferenceCode($lead_id, $first_name, $last_name, $mobile);
    }

    public function PreApprovedEmailApplication_post() {
        $response_array = array('status' => 0);

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
            return json_encode($this->response(['status' => 0, 'error' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
        $last_inserted_id = 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("app_token", "App Token", "required|trim");
            $this->form_validation->set_rules("utm_source", "UTM source", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['status' => 0, 'error' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                try {

                    $insert_lead_data = array();
                    $insert_lead_customer_data = array();
                    $insert_customer_employment_data = array();
                    $insert_customer_banking_data = array();
                    $insert_cam_data = array();

                    $ip_address = strval($post['ip']);

                    $lead_id = $this->encrypt->decode($post['app_token']);

                    if (empty($lead_id)) {
                        throw new Exception("Invalid Lead ID.");
                    }

                    $lead_details = $this->Tasks->get_repeat_customer_details($lead_id);

                    if (empty($lead_details['status'])) {
                        throw new Exception("Application details not found.");
                    }

                    $lead_data = $lead_details['data']['lead_details'];

                    $lead_customer_data = $lead_details['data']['lead_customer_details'];
                    $customer_employment_data = $lead_details['data']['customer_employment_details'];
                    $customer_banking_data = $lead_details['data']['customer_banking_details'];
                    $customer_reference_data = $lead_details['data']['customer_reference_details'];
                    $cam_data = $lead_details['data']['cam_details'];

                    if (empty($lead_data)) {
                        throw new Exception("Customer details not found.");
                    }

                    $token_date = $lead_data['loan_closure_date'];

                    $expire_date = date('Y-m-d', strtotime('+5 days', strtotime($token_date)));

                    if (strtotime(date('Y-m-d')) > strtotime($expire_date)) {
                        throw new Exception("URL has been expired.");
                    }

                    require_once (COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $request_array = array();
                    $request_array['mobile'] = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";
                    $request_array['pancard'] = !empty($lead_data['pancard']) ? $lead_data['pancard'] : "";
                    $request_array['email'] = !empty($lead_data['email']) ? $lead_data['email'] : "";

                    $dedupeDetails = $CommonComponent->check_customer_dedupe($request_array);

                    if ($dedupeDetails['status'] == 1) {
                        throw new ErrorException($dedupeDetails['message']);
                    }

                    if (empty($customer_employment_data)) {
                        throw new Exception("Customer Employment details not found.");
                    }

                    if (empty($lead_customer_data)) {
                        throw new Exception("Customer details not found.");
                    }

                    if (empty($customer_banking_data)) {
                        throw new Exception("Customer Banking details not found.");
                    }

                    if (empty($customer_reference_data)) {
                        throw new Exception("Lead Customer Reference details not found.");
                    }

                    if (empty($cam_data)) {
                        throw new Exception("Credit details not found.");
                    }

                    $lead_columns = array(
                        "customer_id",
                        "company_id",
                        "product_id",
                        "purpose",
                        "first_name",
                        "mobile",
                        "lead_is_mobile_verified",
                        "pancard",
                        "email",
                        "otp",
                        "alternate_email",
                        "loan_amount",
                        "tenure",
                        "cibil",
                        "check_cibil_status",
                        "obligations",
                        "promocode",
                        "source",
                        "lead_branch_id",
                        "state_id",
                        "city_id",
                        "pincode",
                        "term_and_condition",
                        "coordinates",
                        "remark",
                        "lead_data_source_id",
                        "application_status",
                        "qde_consent"
                    );

                    foreach ($lead_columns as $lead_column_name) {

                        if (!empty($lead_data[$lead_column_name])) {
                            $insert_lead_data[$lead_column_name] = $lead_data[$lead_column_name];
                        }
                    }

                    $lead_status_name = "APPLICATION-NEW";
                    $lead_status_stage = "S4";
                    $lead_status_id = 4;

                    $insert_lead_data['source'] = "PRE-APPROVED";
                    $insert_lead_data['lead_data_source_id'] = 32;
                    $insert_lead_data['user_type'] = "REPEAT";
                    $insert_lead_data['status'] = $lead_status_name;
                    $insert_lead_data['stage'] = $lead_status_stage;
                    $insert_lead_data['lead_status_id'] = $lead_status_id;
                    $insert_lead_data['lead_stp_flag'] = 1;
                    $insert_lead_data['utm_source'] = "pre-approved-offeremail";
                    $insert_lead_data['utm_campaign'] = "pre-approved-offeremail";
                    $insert_lead_data['lead_entry_date'] = date("Y-m-d");
                    $insert_lead_data['created_on'] = date("Y-m-d H:i:s");
                    $insert_lead_data['ip'] = $ip_address;

                    $last_inserted_id = $this->Tasks->insert("leads", $insert_lead_data);

                    if (empty($last_inserted_id)) {
                        throw new Exception("Failed to save lead.");
                    }

                    $lead_customer_columns = array(
                        "first_name",
                        "middle_name",
                        "sur_name",
                        "gender",
                        "dob",
                        "pancard",
                        "pancard_ocr_verified_status",
                        "pancard_ocr_verified_on",
                        "pancard_verified_status",
                        "pancard_verified_on",
                        "email",
                        "email_verified_status",
                        "email_verified_on",
                        "alternate_email",
                        "alternate_email_verified_status",
                        "alternate_email_verified_on",
                        "mobile",
                        "mobile_verified_status",
                        "alternate_mobile",
                        "otp",
                        "current_house",
                        "current_locality",
                        "current_landmark",
                        "cr_residence_pincode",
                        "current_district",
                        "current_state",
                        "current_city",
                        "aa_same_as_current_address",
                        "aa_current_house",
                        "aa_current_locality",
                        "aa_current_landmark",
                        "aa_cr_residence_pincode",
                        "aa_current_district",
                        "aa_current_state",
                        "aa_current_city",
                        "aa_current_state_id",
                        "aa_current_city_id",
                        "aa_current_eaadhaar_address",
                        "current_residence_since",
                        "current_residence_type",
                        "current_residing_withfamily",
                        "current_res_status",
                        "state_id",
                        "city_id",
                        "aadhar_no",
                        "customer_religion_id",
                        "father_name",
                        "aadhaar_ocr_verified_status",
                        "aadhaar_ocr_verified_on",
                        "customer_ekyc_request_initiated_on",
                        "customer_ekyc_request_ip",
                        "customer_digital_ekyc_flag",
                        "customer_digital_ekyc_done_on",
                    );

                    foreach ($lead_customer_columns as $lead_column_name) {
                        if (!empty($lead_customer_data[$lead_column_name])) {
                            $insert_lead_customer_data[$lead_column_name] = $lead_customer_data[$lead_column_name];
                        }
                    }

                    $insert_lead_customer_data['customer_lead_id'] = $last_inserted_id;
                    $insert_lead_customer_data['created_date'] = date("Y-m-d H:i:s");

                    $lead_customer = $this->Tasks->insert("lead_customer", $insert_lead_customer_data);

                    if (empty($lead_customer)) {
                        throw new Exception("Failed to save Lead customer.");
                    }

                    $lead_reference_no = $this->Tasks->generateReferenceCode($last_inserted_id, $insert_lead_customer_data['first_name'], $insert_lead_customer_data['sur_name'], $insert_lead_customer_data['mobile']);

                    $update_lead_data = array();
                    $update_lead_data['lead_reference_no'] = $lead_reference_no;

                    $this->Tasks->update(['lead_id' => $lead_id], 'leads', $update_lead_data);

                    $customer_employment_columns = array(
                        "customer_id",
                        "company_id",
                        "product_id",
                        "employer_name",
                        "emp_state",
                        "emp_city",
                        "emp_district",
                        "emp_pincode",
                        "emp_house",
                        "emp_street",
                        "emp_landmark",
                        "emp_residence_since",
                        "emp_designation",
                        "emp_department",
                        "emp_employer_type",
                        "presentServiceTenure",
                        "emp_website",
                        "monthly_income",
                        "emp_salary_mode",
                        "industry",
                        "sector",
                        "income_type",
                        "salary_mode",
                        "emp_status",
                        "emp_locality",
                        "emp_lankmark",
                        "emp_shopNo",
                        "office_address",
                        "emp_email",
                        "emp_active",
                        "emp_deleted",
                        "state_id",
                        "city_id"
                    );

                    foreach ($customer_employment_columns as $lead_column_name) {
                        if (!empty($customer_employment_data[$lead_column_name])) {
                            $insert_customer_employment_data[$lead_column_name] = $customer_employment_data[$lead_column_name];
                        }
                    }

                    $insert_customer_employment_data['lead_id'] = $last_inserted_id;
                    $insert_customer_employment_data['created_on'] = date("Y-m-d H:i:s");

                    $customer_employment = $this->Tasks->insert("customer_employment", $insert_customer_employment_data);

                    if (empty($customer_employment)) {
                        throw new Exception("Failed to save Customer Employment.");
                    }

                    $customer_banking_columns = array(
                        "customer_id",
                        "bank_name",
                        "ifsc_code",
                        "branch",
                        "beneficiary_name",
                        "account",
                        "confirm_account",
                        "account_type",
                        "account_status",
                        "account_status_id",
                        "remark"
                    );

                    foreach ($customer_banking_columns as $lead_column_name) {
                        if (!empty($customer_banking_data[$lead_column_name])) {
                            $insert_customer_banking_data[$lead_column_name] = $customer_banking_data[$lead_column_name];
                        }
                    }

                    $insert_customer_banking_data['lead_id'] = $last_inserted_id;
                    $insert_customer_banking_data['created_on'] = date("Y-m-d H:i:s");

                    $customer_banking = $this->Tasks->insert("customer_banking", $insert_customer_banking_data);

                    if (empty($customer_banking)) {
                        throw new Exception("Failed to save Customer Banking.");
                    }

                    $customer_reference_columns = array(
                        "lcr_name",
                        "lcr_relationType",
                        "lcr_mobile"
                    );

                    foreach ($customer_reference_data as $row_data) {


                        $insert_customer_reference_data = array();
                        foreach ($customer_reference_columns as $lead_column_name) {
                            if (!empty($row_data[$lead_column_name])) {
                                $insert_customer_reference_data[$lead_column_name] = $row_data[$lead_column_name];
                            }
                        }

                        $insert_customer_reference_data['lcr_lead_id'] = $last_inserted_id;
                        $insert_customer_reference_data['lcr_created_on'] = date("Y-m-d H:i:s");

                        $lead_customer_references = $this->Tasks->insert("lead_customer_references", $insert_customer_reference_data);
                    }

                    $cam_columns = array(
                        "customer_id",
                        "company_id",
                        "product_id",
                        "ntc",
                        "run_other_pd_loan",
                        "delay_other_loan_30_days",
                        "job_stability",
                        "city_category",
                        "salary_credit1",
                        "salary_credit1_date",
                        "salary_credit1_amount",
                        "salary_credit2",
                        "salary_credit2_date",
                        "salary_credit2_amount",
                        "salary_credit3",
                        "salary_credit3_date",
                        "salary_credit3_amount",
                        "median_salary",
                        "salary_variance",
                        "salary_on_time",
                        "borrower_age",
                        "end_use",
                        "eligible_foir_percentage",
                        "eligible_loan",
                        "final_foir_percentage",
                        "foir_enhanced_by",
                        "cam_risk_profile",
                        "cam_appraised_obligations",
                        "cam_appraised_monthly_income",
                        "cam_blacklist_removed_flag"
                    );

                    foreach ($cam_columns as $lead_column_name) {
                        if (!empty($cam_data[$lead_column_name])) {
                            $insert_cam_data[$lead_column_name] = $cam_data[$lead_column_name];
                        }
                    }

                    $insert_cam_data['lead_id'] = $last_inserted_id;
                    $insert_cam_data['created_at'] = date("Y-m-d H:i:s");

                    $cam = $this->Tasks->insert("credit_analysis_memo", $insert_cam_data);

                    if (empty($cam)) {
                        throw new Exception("Failed to save Credit details.");
                    }

                    $insert_lead_followup_data = array();
                    $insert_lead_followup_data['lead_id'] = $last_inserted_id;
                    $insert_lead_followup_data["remarks"] = "Pre-Approved Application";
                    $insert_lead_followup_data['status'] = $lead_status_name;
                    $insert_lead_followup_data['stage'] = $lead_status_stage;
                    $insert_lead_followup_data["lead_followup_status_id"] = $lead_status_id;
                    $insert_lead_followup_data['created_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->insert("lead_followup", $insert_lead_followup_data);

                    $apiStatusId = 1;
                } catch (ErrorException $ex) {
                    $apiStatusId = 3;
                    $errorMessage = $ex->getMessage();
                } catch (Exception $e) {
                    $apiStatusId = 2;
                    $errorMessage = $e->getMessage();
                }

                if ($apiStatusId == 1) {
                    $lead_remarks = "Your have applied successfully. Your application reference no is : " . $lead_reference_no;

                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $lead_data['mobile'];
                    $sms_input_data['name'] = (($lead_data['gender'] == "MALE") ? "Mr. " : "Ms. ") . $lead_data['first_name'];
                    $sms_input_data['refrence_no'] = $lead_reference_no;

                    $CommonComponent->payday_sms_api(2, $lead_id, $sms_input_data);

                    $CommonComponent->sent_lead_thank_you_email($lead_id, $lead_data['email'], $lead_data['first_name'], $lead_reference_no);
                } else {
                    $lead_remarks = $errorMessage;
                }

                $response_array['status'] = $apiStatusId;
                $response_array['data'] = $lead_reference_no;
                $response_array['errors'] = !empty($errorMessage) ? $errorMessage : "";
                $response_array['message'] = !empty($lead_remarks) ? $lead_remarks : "";

                return json_encode($this->response($response_array, REST_Controller::HTTP_OK));
            }
        } else {
            $response_array['error'] = "Invalid API request.";
            return json_encode($this->response($response_array, REST_Controller::HTTP_OK));
        }
    }

    public function ScheduleAppointment_post() {
        $input_data = file_get_contents("php://input");
        $response_data = array('Status' => 0, 'Message' => '');

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
            $response_data['Message'] = 'UnAuthorized Access.';
            return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                $response_data['Message'] = strip_tags(validation_errors());
                return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
            } else {

                $lead_id = intval($this->encrypt->decode($post['lead_id']));

                if (!empty($lead_id)) {

                    $qry = "SELECT LD.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as full_name, LC.customer_appointment_schedule, LD.mobile, LD.pancard, LD.email, LD.lead_status_id ";
                    $qry .= " FROM leads LD INNER JOIN lead_customer LC ON (LD.lead_id = LC.customer_lead_id) ";
                    $qry .= " WHERE LD.lead_id = LC.customer_lead_id AND LD.lead_active = 1 AND LD.lead_id = '$lead_id'";

                    $response = $this->db->query($qry);

                    if ($response->num_rows() > 0) {
                        $row = $response->row_array();
                        $data = array();
                        $schedules_datetime = $row['customer_appointment_schedule'];

                        if (!empty($schedules_datetime)) {
                            $schedules_datetime = date("d-m-Y h:i A", strtotime($schedules_datetime));
                            $response_data['Status'] = 2;
                            $response_data['Message'] = 'Your appointment already scheduled at : ' . $schedules_datetime;

                            return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                        }

                        if (!in_array($row['lead_status_id'], [1, 2, 3])) {
                            $response_data['Status'] = 2;
                            $response_data['Message'] = 'Your case has been moved to the next steps.';

                            return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                        }


                        $data['lead_ref_id'] = $this->encrypt->encode($row['lead_id']);
                        $data['customer_full_name'] = $row['full_name'];
                        $data['customer_mobile'] = $row['mobile'];
                        $data['customer_pancard'] = $row['pancard'];
                        $data['customer_email'] = $row['email'];

                        $response_data['Status'] = 1;
                        $response_data['data'] = $data;
                        return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                    } else {
                        $response_data['Message'] = 'Record Not Found.';
                        return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                    }
                } else {
                    $response_data['Message'] = 'Invalid Token';
                    return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                }
            }
        } else {
            $response_data['Message'] = 'Request Method Post Failed.';
            return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
        }
    }

    public function SaveScheduleAppointment_post() {
        $response_data = array('Status' => 0, 'Message' => '');
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("schedule_datetime", "Schedule Date", "required|trim");
            $this->form_validation->set_rules("remarks", "Remarks", "required|trim");
            $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $lead_id = intval($this->encrypt->decode($post['lead_id']));

                if (!empty($lead_id)) {

                    $qry = "SELECT LD.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as full_name, LC.customer_appointment_schedule, LD.mobile, LD.pancard, LD.email, LD.lead_status_id, LD.status, LD.stage";
                    $qry .= " FROM leads LD INNER JOIN lead_customer LC ON (LD.lead_id = LC.customer_lead_id) ";
                    $qry .= " WHERE LD.lead_id = LC.customer_lead_id AND LD.lead_active = 1 AND LD.lead_id = '$lead_id'";

                    $response = $this->db->query($qry);

                    if ($response->num_rows() > 0) {
                        $leadDetails = $response->row_array();

                        $scheduled_datetime = $post['schedule_datetime'];
                        $remarks = strval($post['remarks']);
                        $current_datetime = date("Y-m-d H:i:s");
                        $scheduled_datetime_hours = date('His', strtotime($scheduled_datetime));

                        if ((($scheduled_datetime_hours < 100000) || ($scheduled_datetime_hours > 190000))) {
                            return json_encode($this->response(['Status' => 0, 'errormessage' => 'You can scheduled your appointment between 10AM to 7PM.']));
                        } else if ((strtotime($current_datetime) > strtotime($scheduled_datetime))) {
                            return json_encode($this->response(['Status' => 0, 'errormessage' => 'Please enter the valid scheduled appointment date time.']));
                        } else if (date('Ymd', strtotime($scheduled_datetime)) < date("Ymd", strtotime($current_datetime))) || (date('Ymd', strtotime($scheduled_datetime)) >  date('Ymd', strtotime('+1 day', strtotime($current_datetime)))) {
                            return json_encode($this->response(['Status' => 0, 'errormessage' => 'Appointment schedule date can not greater than 1 Day and less than today.']));
                        }


                        $insertData = array(
                            'customer_appointment_schedule' => $scheduled_datetime,
                            'customer_appointment_remark' => $remarks
                        );

                        $scheduled_datetime = date("d-m-Y h:i A", strtotime($scheduled_datetime));

                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $insertData);

                        $insert_log_array = array();
                        $insert_log_array['lead_id'] = $lead_id;
                        $insert_log_array['stage'] = $leadDetails['stage'];
                        $insert_log_array['status'] = $leadDetails['status'];
                        $insert_log_array['lead_followup_status_id'] = $leadDetails['lead_status_id'];
                        $insert_log_array['remarks'] = 'Callback Customer Scheduled at : ' . $scheduled_datetime . '<br>Remark : ' . $remarks;
                        $insert_log_array['created_on'] = $current_datetime;

                        $this->db->insert('lead_followup', $insert_log_array);

                        return json_encode($this->response(['Status' => 1, 'Message' => 'Appointment scheduled successfully.', 'schedules_datetime' => $scheduled_datetime], REST_Controller::HTTP_OK));
                    } else {
                        $response_data['Message'] = 'Record not found.';
                        return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                    }
                } else {
                    $response_data['Message'] = 'Invalid Token';
                    return json_encode($this->response($response_data, REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

}

?>
