<?php

//defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class TaskApiNew extends REST_Controller {

    public $white_listed_ips = array("13.126.63.92");

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
        ini_set("memory_limit", "1024M");
        ini_set('max_execution_time', 7200);
    }

    public function personalLoanApply_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }
        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("full_name", "Name", "required|trim|min_length[3]|max_length[60]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("source", "Lead Source", "required|trim");
            $this->form_validation->set_rules("pancard", "Pancard", "required|trim|exact_length[10]|alpha_numeric");
            $this->form_validation->set_rules("coordinates", "coordinates", "trim");
            $this->form_validation->set_rules("ip", "IP", "trim");
            $this->form_validation->set_rules("city_id", "CITY", "required|trim|numeric");
            $this->form_validation->set_rules("income_type", "Employment type", "required|trim|numeric");
            $this->form_validation->set_rules("purposeofloan", "Purpose of Loan", "required|trim|numeric");
            $this->form_validation->set_rules("monthly_salary", "Monthly Salary", "required|trim|numeric|min_length[5]|max_length[7]");
            $this->form_validation->set_rules("loan_amount", "Required Loan Amount", "required|trim|numeric|min_length[4]|max_length[6]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();

                $full_name = strtoupper($post['full_name']);
                $temp_name_array = $this->Tasks->common_parse_full_name($full_name);
                $first_name = !empty($temp_name_array['first_name']) ? strtoupper($temp_name_array['first_name']) : "";
                $middle_name = !empty($temp_name_array['middle_name']) ? strtoupper($temp_name_array['middle_name']) : "";
                $last_name = !empty($temp_name_array['last_name']) ? strtoupper($temp_name_array['last_name']) : "";
                $mobile = !empty($post['mobile']) ? $post['mobile'] : "";
                $email = !empty($post['email']) ? strtoupper($post['email']) : "";
                $city_id = !empty($post['city_id']) ? $post['city_id'] : "";
                $pancard = !empty($post['pancard']) ? $post['pancard'] : "";
                $income_type = !empty($post['income_type']) ? $post['income_type'] : "";
                $purposeofloan = !empty($post['purposeofloan']) ? $post['purposeofloan'] : "";
                $loan_amount = !empty($post['loan_amount']) ? $post['loan_amount'] : "";
                $monthly_salary = !empty($post['monthly_salary']) ? $post['monthly_salary'] : "";
                $ipAddress = !empty($post['ip']) ? $post['ip'] : "";
                $lead_source = !empty($post['source']) ? $post['source'] : "";
                $utm_source = !empty($post['utm_source']) ? $post['utm_source'] : "";
                $utm_campaign = !empty($post['utm_campaign']) ? $post['utm_campaign'] : "";
                $coordinates = !empty($post['coordinates']) ? $post['coordinates'] : "";

                $checkDuplicateDataArray = array('mobile' => $mobile, 'pancard' => $pancard, 'email' => $email);

                $resposeDuplicateDataArray = $CommonComponent->check_customer_dedupe($checkDuplicateDataArray);
                if (!empty($resposeDuplicateDataArray['status']) && $resposeDuplicateDataArray['status'] == 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "You have already applied for the day. Please try again tomorrow."], REST_Controller::HTTP_OK));
                }

                $query = $this->Tasks->selectdata(['m_city_id' => $city_id], 'm_city_state_id,m_city_branch_id', 'master_city');

                if ($query->num_rows() > 0) {
                    $sql = $query->row();
                    $city_state_id = $sql->m_city_state_id;
                    $city_branch_id = $sql->m_city_branch_id;
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

                if ($mobile == "9560807913") {//Hardcoded otp testing... donot remove
                    $otp = 1989;
                } else if ($mobile == "9369815048") {//Hardcoded otp testing... donot remove
                    $otp = 1906;
                }


                $lead_status_id = 1;
                $lead_status_stage = 'S1';
                $lead_status_name = 'LEAD-NEW';
                $lead_user_type = 'NEW';

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'email' => $email,
                    'pancard' => $pancard,
                    'otp' => $otp,
                    'user_type' => $lead_user_type,
                    'lead_entry_date' => date("Y-m-d"),
                    'created_on' => date("Y-m-d H:i:s"),
                    'source' => $lead_source,
                    'ip' => $ipAddress,
                    'status' => $lead_status_name,
                    'stage' => $lead_status_stage,
                    'lead_status_id' => $lead_status_id,
                    'lead_branch_id' => $city_branch_id,
                    'qde_consent' => 'Y',
                    'term_and_condition' => "YES",
                    'lead_data_source_id' => $post['lead_data_source_id'],
                    'coordinates' => $coordinates,
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'loan_amount' => $loan_amount,
                    'purpose' => $purposeofloanname
                );

                $InsertLeads = $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                if (empty($lead_id)) {
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
                    'created_date' => date("Y-m-d H:i:s")
                );

                $InsertLeadCustomer = $this->db->insert('lead_customer', $insertLeadsCustomer);

                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Instant Loan Lead Applied");

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
                        if(!empty($cif_result->cif_alternate_mobile) && $cif_result->cif_alternate_mobile != '0') {
                            $alternate_mobile = $cif_result->cif_alternate_mobile;
                        }
                        $update_data_lead_customer = [
                            'middle_name' => !empty($middle_name) ? $middle_name : $cif_result->cif_middle_name,
                            'sur_name' => !empty($last_name) ? $last_name : $cif_result->cif_sur_name,
                            'gender' => $gender,
                            'dob' => $cif_result->cif_dob,
                            'alternate_email' => $cif_result->cif_office_email,
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
                            'updated_at' => date("Y-m-d H:i:s")
                        ];
                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);
                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $cif_result->cif_office_email,
                            'pincode' => $cif_result->cif_residence_pincode,
                            'user_type' => $user_type,
                            'updated_on' => date("Y-m-d H:i:s")
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
                        $insert_customer_employement['updated_on'] = date("Y-m-d H:i:s");
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = date("Y-m-d H:i:s");
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }
                }

                $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                if ($return_eligibility_array['status'] == 2) {
                    return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                }

                $data = ["mobile" => $mobile, "otp" => $otp];

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 1,
                    'lot_otp_trigger_time' => date("Y-m-d H:i:s"),
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
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Your registeration saved successfully. Complete the few steps to know your Loan Offer.', 'mobile' => $mobile, 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Record'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function verifyAppliedCustomerOTPNew_post() {
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
            //$this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $mobile = $post['mobile'];
                $lead_id = $post['lead_id'];
                $otp = $post['otp'];
                $query = $this->db->select('lead_id,first_name,mobile,email,lead_status_id,city_id,state_id,loan_amount')->where('lead_id', $lead_id)->from('leads')->get();
                $query_cust = $this->db->select('pancard,first_name,middle_name,sur_name,gender,dob,email,alternate_email,alternate_mobile')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $empquery = $this->db->select('id,monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $result = $query->row();
                $result_cust = $query_cust->row();
                $empquery = $empquery->row();
                $existing_lead_id = $result->lead_id;
                $lead_status_id = $result->lead_status_id;
                $loan_amount = intval($result->loan_amount);
                $monthly_salary = intval($empquery->monthly_income);
                $first_name = $result_cust->first_name;
                $middle_name = $result_cust->middle_name;
                $last_name = $result_cust->sur_name;
                $gender = $result_cust->gender;
                $email = $result_cust->email;
                $alternate_email = $result_cust->alternate_email;
                if(!empty($result_cust->alternate_mobile) && $result_cust->alternate_mobile != '0') {
                  $alternate_mobile = $result_cust->alternate_mobile;
                }
                $pancard = $result_cust->pancard;
                $dob = $result_cust->dob;
                $lead_status_id = $result_cust->lead_status_id;
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
                $update_data_lead_customer = ['mobile_verified_status' => "YES", 'updated_at' => date('Y-m-d H:i:s')];
                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);
                $this->db->insert('lead_followup', ['lead_id' => $lead_id, 'lead_followup_status_id' => $lead_status_id, 'remarks' => 'OTP verify successfully', 'created_on' => date("Y-m-d H:i:s")]);
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

                $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id,docs_sub_type', 'docs_master');
                $tempDetails = $query->result_array();
                $docs_master = array();
                foreach ($tempDetails as $document_data) {
                    $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                }
                return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'Customer_data' => $Customer_data, 'Lead_id' => $lead_id, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getVerifyEkyc_post() {
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
            $lead_qry = $this->db->query("select * from lead_customer where customer_lead_id='" . $post['lead_id'] . "' AND customer_digital_ekyc_flag='1'");
            if ($lead_qry->num_rows() > 0) {
                $status = 1;
                $message = 'Your eKYC has been successfully verified.';
            } else {
                $leadDetails = [];
                $status = 0;
                $message = 'Please verify e-KYC,Then move next step.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message], REST_Controller::HTTP_OK));
        }
    }

    public function getAlternateMobile_post() {
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
            $lead_qry = $this->db->query("select * from leads where lead_id='" . $post['lead_id'] . "' AND mobile='" . $post['alternate_mobile'] . "'");
            if ($lead_qry->num_rows() > 0) {
                $status = 1;
                $message = 'Personal number and alternate number should be not same.';
            } else {
                $leadDetails = [];
                $status = 0;
                $message = 'Proceed.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message], REST_Controller::HTTP_OK));
        }
    }
    
    public function getMobileResidential_post() {
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
            $lead_qry = $this->db->query("select * from lead_customer where customer_lead_id='".$post['lead_id']."' AND (alternate_mobile='".$post['mobile']."' OR mobile='".$post['mobile']."')");
            if ($lead_qry->num_rows() > 0) {
                $status = 1;
                $message = 'Personal number,alternamte number & residential reference no should be not same.';
            } else {
                $leadDetails = [];
                $status = 0;
                $message = 'Proceed.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message], REST_Controller::HTTP_OK));
        }
    }
    
    public function getMobileEmployment_post() {
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
            $lead_qry = $this->db->query("select * from lead_customer where customer_lead_id='".$post['lead_id']."' AND (alternate_mobile='".$post['mobile']."' OR mobile='".$post['mobile']."')");
            if ($lead_qry->num_rows() > 0) {
                $status = 1;
                $message = 'Personal number,alternamte number & employment reference no should be not same.';
            } else {
                $leadDetails = [];
                $status = 0;
                $message = 'Proceed.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message], REST_Controller::HTTP_OK));
        }
    }

    public function getAlternateEmail_post() {
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
            $lead_qry = $this->db->query("select * from leads where lead_id='" . $post['lead_id'] . "' AND email='" . $post['alternate_email'] . "'");
            if ($lead_qry->num_rows() > 0) {
                $status = 1;
                $message = 'Personal email and office email should be not same.';
            } else {
                $leadDetails = [];
                $status = 0;
                $message = 'Proceed.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message], REST_Controller::HTTP_OK));
        }
    }

    public function getBankNameByIfscCode_post() {
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
            $result = $this->db->select('bank.bank_id,bank.bank_name,bank.bank_branch')->where('bank_ifsc', $post['ifsc_code'])->from('tbl_bank_details as bank')->get()->row();
            if ($result->bank_id != '') {
                $status = 1;
                $message = 'Data found.';
            } else {
                $status = 0;
                $message = 'Not data found.';
            }
            return json_encode($this->response(['Status' => $status, 'Message' => $message, 'Data' => $result], REST_Controller::HTTP_OK));
        }
    }

    public function getCustomerBankDetails_post() {
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
            $search = $post['search'];
            $sql = "SELECT bank.bank_id, bank.bank_ifsc FROM tbl_bank_details as bank WHERE bank_ifsc LIKE '%" . $search . "%' LIMIT 10";
            $result = $this->db->query($sql);
            $bankData = $result->result_array();
            foreach ($bankData as $row) {
                $json[] = ['bank_id' => $row['bank_id'], 'bank_ifsc' => $row['bank_ifsc']];
            }
            return json_encode($this->response(['Data' => $json], REST_Controller::HTTP_OK));
        }
    }

    public function getLoanDetailByLeadId_post() {
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
            $lead_qry = $this->db->query("select lead_id,loan_amount,mobile,obligations,pancard,tenure from leads where lead_id='" . $post['lead_id'] . "'");
            if ($lead_qry->num_rows() > 0) {
                $otp_trans_qry = $this->db->query("select lot_otp_verify_flag from leads_otp_trans where lot_lead_id='" . $post['lead_id'] . "' order by lot_otp_verify_flag desc");
                $customer_qry = $this->db->query("select LC.*,MRT.m_residence_type_id as residenceType from lead_customer LC left join master_residence_type MRT on MRT.m_residence_type_name=LC.current_residence_type where customer_lead_id='" . $post['lead_id'] . "'");
                $customer_ref_qry1 = $this->db->query("select lcr_name,lcr_mobile,lcr_relationType,ref_type from lead_customer_references where ref_type='1' AND lcr_lead_id='" . $post['lead_id'] . "'");
                $customer_ref_qry2 = $this->db->query("select lcr_name,lcr_mobile,lcr_relationType,ref_type from lead_customer_references where ref_type='2' AND lcr_lead_id='" . $post['lead_id'] . "'");
                $employment_qry = $this->db->query("select MDT.department_id emp_department,CE.employer_name,CE.emp_house,CE.emp_street,CE.emp_landmark,MI.m_industry_id industry,CE.emp_residence_since,CE.monthly_income,CE.emp_work_mode,CE.company_id,CE.emp_state,CE.emp_city,MD.m_designation_id emp_designation,MSM.m_salary_mode_id salary_mode,CE.emp_residence_since,CE.emp_occupation_id,CE.emp_pincode from customer_employment CE left join master_industry MI on MI.m_industry_name=CE.industry left join master_designation MD on MD.m_designation_name=CE.emp_designation left join master_department MDT on MDT.department_name=CE.emp_department left join master_salary_mode MSM on MSM.m_salary_mode_name=CE.salary_mode where CE.lead_id='" . $post['lead_id'] . "'");
                $banking_qry = $this->db->query("select bank_name,branch,ifsc_code,account,confirm_account,same_account from customer_banking where lead_id='" . $post['lead_id'] . "'");
                $docs_qry = $this->db->query("select docs_id,file,pancard,mobile,docs_type,sub_docs_type,docs_master_id from docs where lead_id='" . $post['lead_id'] . "'");
                $credit_qry = $this->db->query("select salary_credit1_date,salary_credit2_date,salary_credit3_date,salary_credit1_amount,salary_credit2_amount,salary_credit3_amount,next_pay_date from credit_analysis_memo where lead_id='" . $post['lead_id'] . "'");

                $leadDetails['lead_arr'] = $lead_qry->row_array();
                $leadDetails['otp_trans_arr'] = $otp_trans_qry->row_array();
                $leadDetails['customer_arr'] = $customer_qry->row_array();
                $leadDetails['customer_ref_arr1'] = $customer_ref_qry1->row_array();
                $leadDetails['customer_ref_arr2'] = $customer_ref_qry2->row_array();
                $leadDetails['employment_arr'] = $employment_qry->row_array();
                $leadDetails['banking_arr'] = $banking_qry->row_array();
                $leadDetails['credit_arr'] = $credit_qry->row_array();
                $leadDetails['docs_arr'] = $docs_qry->result_array();
            } else {
                $leadDetails = [];
            }
            return json_encode($this->response(['Status' => 1, 'Message' => 'Get lead information.', 'Data' => $leadDetails], REST_Controller::HTTP_OK));
        }
    }

    public function viewDocumentFile_post() {
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
            $document_id = $post['document_id'];
            $documentDetails = $this->db->select('*')->where(['docs_id' => $document_id])->from('docs')->get()->row_array();
            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $file_path = base64_encode(file_get_contents(COMP_DOC_URL . $documentDetails['file']));
            return json_encode($this->response(['status' => 1, 'Message' => 'Get image path.', 'doc_data' => $file_path,'file'=>$documentDetails['file']], REST_Controller::HTTP_OK));
        }
    }

    public function savePersonalDetailsNew_post() {
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
            $this->form_validation->set_rules("gender", "Gender", "required|trim");
            $this->form_validation->set_rules("dob", "Date Of Birth", "required|trim");
            //$this->form_validation->set_rules("alternate_mobile_no", "Alternate Mobile No", "trim|exact_length[10]|numeric");
            //$this->form_validation->set_rules("alternate_email", "Alternate Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = $post['lead_id'];
                $gender = $post['gender'];
                $dob = $post['dob'];
                $alternate_mobile = !empty($post['alternate_mobile']) ? $post['alternate_mobile'] : NULL;
                $email_office = $post['email_office'];
                $marital_status = $post['marital_status'];
                $spouse_name = $post['spouse_name'];
                $customer_spouse_occupation = $post['customer_spouse_occupation'];
                $highest_qualification = $post['highest_qualification'];
                $religion = $post['religion'];
                if ($post['obligations'] != '0.00' || $post['obligations'] != '') {
                    $obligations = $post['obligations'];
                } else {
                    $obligations = $post['obligations'];
                }
                $aadhar_no = $post['aadhar_no'];
                $residence_type = $post['residence_type'];
                $next_salary_date = $post['next_salary_date'];

                $esidence_type_name_data = $this->db->select('m_residence_type_name')->where('m_residence_type_id', $residence_type)->from('master_residence_type')->get()->row();
                if ($esidence_type_name_data->m_residence_type_name != '') {
                    $residence_type = $esidence_type_name_data->m_residence_type_name;
                }

                $query = $this->db->select('lead_id,loan_amount,lead_status_id,lead_is_mobile_verified,city_id, state_id,customer_id,loan_amount,mobile,pancard,email')->where('lead_id', $lead_id)->from('leads')->get();
                $result = $query->row();
                $existing_lead_id = $result->lead_id;
                $lead_is_mobile_verified = $result->lead_is_mobile_verified;
                $city_id = $result->city_id;
                $state_id = $result->state_id;
                $customer_id = $result->customer_id;
                $pancard = $result->pancard;
                $mobile = $result->mobile;
                $loan_amount = intval($result->loan_amount);

                $query_cust = $this->db->select('pancard,first_name,middle_name,sur_name,gender,dob,email,alternate_email,alternate_mobile')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $result_cust = $query_cust->row();
                $first_name = $result_cust->first_name;
                $middle_name = $result_cust->middle_name;
                $sur_name = $result_cust->sur_name;

                $email_personal = $result->email;
                $empquery = $this->db->select('id,monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;
                $monthly_income = intval($empquery->monthly_income);

                if ($existing_lead_id != $lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid access for the application.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_is_mobile_verified != 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application OTP not verified.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                if ($lead_status_id > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }
                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();
                /*Start cam table*/
                $bre_quote_info = $CommonComponent->call_bre_quote_engine($lead_id);                
                              
                $dob = date('Y-m-d', strtotime($dob));
                $arrCreditAnalysisEmp = [
                    'lead_id' => $lead_id,
                    'next_pay_date' => $next_salary_date,
                    'roi' => $bre_quote_info['interest_rate'],
                    'eligible_foir_percentage' => $bre_quote_info['eligible_foir_percentage'],
                    'processing_fee_percent' => $bre_quote_info['processing_fee'],
                    'eligible_loan' => $bre_quote_info['max_loan_amount'],
                    'tenure' => $bre_quote_info['max_loan_tenure'],
                    'loan_recommended' => $bre_quote_info['min_loan_amount'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $cam_data = $this->db->select('cam_id')->where('lead_id', $lead_id)->from('credit_analysis_memo')->get()->row();
                if (isset($cam_data->cam_id) && $cam_data->cam_id != '') {
                    $this->db->where('cam_id', $cam_data->cam_id)->update('credit_analysis_memo', $arrCreditAnalysisEmp);
                } else {
                    $this->db->insert('credit_analysis_memo', $arrCreditAnalysisEmp);
                }

                $existing_customer_flag = false;              
                
                
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
                            'customer_marital_status_id' => $marital_status,
                            'customer_spouse_name' => $spouse_name,
                            'customer_spouse_occupation_id' => $customer_spouse_occupation,
                            'customer_qualification_id' => $highest_qualification,
                            'customer_religion_id' => $religion,
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
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $update_cust_leads = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $email_office,
                            'pincode' => $cif_result->cif_residence_pincode,
                            'user_type' => $user_type,
                            'obligations' => $obligations,
                            'updated_on' => date('Y-m-d H:i:s')
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
                            'updated_on' => date('Y-m-d H:i:s'),
                        ];

                        if (!empty($emp_id)) {
                            $insert_customer_employement['updated_on'] = date('Y-m-d H:i:s');
                            $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                        } else {
                            $insert_customer_employement['created_on'] = date('Y-m-d H:i:s');
                            $this->db->insert('customer_employment', $insert_customer_employement);
                        }
                        $update_leads = $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                        if ($return_eligibility_array['status'] == 2) {
                            return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                        }

                        if ($update_leads == true && $update_cust_leads == true) {
                            return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'loan_amount' => $loan_amount, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id], REST_Controller::HTTP_OK));
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
                        'aadhar_no' => $aadhar_no,
                        'mobile' => $mobile,
                        'current_residence_type' => $residence_type,
                        'customer_spouse_occupation_id' => $customer_spouse_occupation,
                        'alternate_mobile' => $alternate_mobile,
                        'customer_marital_status_id' => $marital_status,
                        'customer_spouse_name' => $spouse_name,
                        'customer_qualification_id' => $highest_qualification,
                        'customer_religion_id' => $religion,
                        'email' => $email_personal,
                        'alternate_email' => $email_office,
                        'updated_at' => date("Y-m-d H:i:s"),
                    );

                    $dataLeads = array(
                        'mobile' => $mobile,
                        'email' => $email_personal,
                        'obligations' => $obligations,
                        'alternate_email' => $email_office,
                        'updated_on' => date("Y-m-d H:i:s"),
                    );

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'customer_id' => $customer_id,
                        'emp_email' => $email_office,
                        'updated_on' => date("Y-m-d H:i:s"),
                    ];

                    if (!empty($emp_id)) {
                        $insert_customer_employement['updated_on'] = date("Y-m-d H:i:s");
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = date("Y-m-d H:i:s");
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }
                    $res_lead = $this->db->where('lead_id', $lead_id)->update('leads', $dataLeads);
                    $res_customer = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $dataCustomer);
                    $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);
                    if ($return_eligibility_array['status'] == 2) {
                        return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                    }
                    if ($res_lead == true && $res_customer == true) {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'loan_amount' => $loan_amount, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id, 'loan_amount' => $loan_amount, 'monthly_salary' => $monthly_income], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                    }
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function saveResidentialDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($this->post());
            //$this->form_validation->set_rules("residence_type", "residence type", "required");
            $this->form_validation->set_rules("residing_since", "residing since", "required");
            $this->form_validation->set_rules("caddress1", "address", "required");
            $this->form_validation->set_rules("reference_name", "name", "required|trim");
            $this->form_validation->set_rules("reference_mobile", "mobile", "required|trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("reference_relation", "relation", "required");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = $post['lead_id'];
                $residing_since = $post['residing_since'];
                $caddress1 = $post['caddress1'];
                $caddress2 = $post['caddress2'];
                $clandmark = $post['clandmark'];
                $cstate = $post['cstate'];
                $ccity = $post['ccity'];
                $cpincode = $post['cpincode'];
                $permanent_address = $post['permanent_address'];
                $paddress1 = $post['paddress1'];
                $paddress2 = $post['paddress2'];
                $plandmark = $post['plandmark'];
                $pstate = $post['pstate'];
                $pcity = $post['pcity'];
                $ppincode = $post['ppincode'];
                $reference_name = $post['reference_name'];
                $reference_mobile = $post['reference_mobile'];
                $reference_relation = $post['reference_relation'];

                $dataResidence = [
                    'current_house' => $caddress1,
                    'current_locality' => $caddress2,
                    'current_landmark' => $clandmark,
                    'current_state' => $cstate,
                    'current_city' => $ccity,
                    'state_id' => $cstate,
                    'city_id' => $ccity,
                    'cr_residence_pincode' => $cpincode,
                    'current_district' => '',
                    'aa_same_as_current_address' => $permanent_address,
                    'aa_current_house' => $paddress1,
                    'aa_current_locality' => $paddress2,
                    'aa_current_landmark' => $plandmark,
                    'aa_current_state' => $pstate,
                    'aa_current_city' => $pcity,
                    'aa_current_state_id' => $pstate,
                    'aa_current_city_id' => $pcity,
                    'aa_cr_residence_pincode' => $ppincode,
                    'aa_current_district' => '',
                    'current_residence_since' => date('Y-m-d', strtotime($residing_since))
                ];

                $dataArr = [
                    'pincode' => $cpincode
                ];

                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $dataResidence);
                $this->db->where('lead_id', $lead_id)->update('leads', $dataArr);
                $arrReferences = [
                    'lcr_lead_id' => $lead_id,
                    'lcr_name' => $reference_name,
                    'lcr_relationType' => $reference_relation,
                    'ref_type' => 1,
                    'lcr_mobile' => $reference_mobile,
                    'lcr_active' => 1,
                    'lcr_created_on' => date('Y-m-d H:i:s'),
                    'lcr_updated_on' => date('Y-m-d H:i:s')
                ];

                $references_data = $this->db->select('lcr_id,lcr_lead_id,ref_type')->where('lcr_lead_id', $lead_id)->where('ref_type', 1)->from('lead_customer_references')->get()->row();
                if (isset($references_data->lcr_id) && $references_data->lcr_id != '') {
                    $this->db->where('lcr_id', $references_data->lcr_id)->update('lead_customer_references', $arrReferences);
                } else {
                    $this->db->insert('lead_customer_references', $arrReferences);
                }
                return json_encode($this->response(['Status' => 1, 'Message' => 'Successfully updated residential.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function saveEmploymentDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("employer_company_name", "company name", "required");
            $this->form_validation->set_rules("company_type", "company type", "required");
            $this->form_validation->set_rules("department", "department", "required");
            $this->form_validation->set_rules("designation", "designation", "required");
            $this->form_validation->set_rules("emp_name", "name", "required|trim");
            $this->form_validation->set_rules("emp_mobile", "mobile", "required|trim|exact_length[10]|numeric");
            $this->form_validation->set_rules("emp_relation", "relation", "required");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $salary_mode_name = '';
                $department_name = '';
                $lead_id = $post['lead_id'];
                $work_mode = $post['work_mode'];
                $employer_company_name = $post['employer_company_name'];
                $company_type = $post['company_type'];
                $department = $post['department'];
                $designation = $post['designation'];
                $industry = $post['industry'];
                $employed_since_current = $post['employed_since_current'];
                $salary_mode = $post['salary_mode'];
                $net_monthly_income = $post['net_monthly_income'];
                $salary_date1 = $post['salary_date1'];
                $salary_date2 = $post['salary_date2'];
                $salary_date3 = $post['salary_date3'];
                $amount1 = $post['amount1'];
                $amount2 = $post['amount2'];
                $amount3 = $post['amount3'];
                $emp_occupation_id = $post['emp_occupation_id'];
                $emp_address1 = $post['emp_address1'];
                $emp_address2 = $post['emp_address2'];
                $emp_landmark = $post['emp_landmark'];
                $emp_state = $post['emp_state'];
                $emp_city = $post['emp_city'];
                $emp_pincode = $post['emp_pincode'];
                $emp_name = $post['emp_name'];
                $emp_mobile = $post['emp_mobile'];
                $emp_relation = $post['emp_relation'];

                $salary_mode_data = $this->db->select('m_salary_mode_name')->where('m_salary_mode_id', $salary_mode)->from('master_salary_mode')->get()->row();
                if ($salary_mode_data->m_salary_mode_name != '') {
                    $salary_mode_name = $salary_mode_data->m_salary_mode_name;
                }

                $department_name_data = $this->db->select('department_name')->where('department_id', $department)->from('master_department')->get()->row();
                if ($department_name_data->department_name != '') {
                    $department_name = $department_name_data->department_name;
                }

                $designation_name_data = $this->db->select('m_designation_name')->where('m_designation_id', $designation)->from('master_designation')->get()->row();
                if ($designation_name_data->m_designation_name != '') {
                    $designation_name = $designation_name_data->m_designation_name;
                }

                $industry_name_data = $this->db->select('m_industry_name')->where('m_industry_id', $industry)->from('master_industry')->get()->row();
                if ($industry_name_data->m_industry_name != '') {
                    $industry_name = $industry_name_data->m_industry_name;
                }
                $employed_since_current = date('Y-m-d', strtotime($employed_since_current));
                list($preYear, $preMonth, $preDay) = explode('-', $employed_since_current);
                list($currentYear, $currentMonth, $currentDay) = explode('-', date("Y-m-d"));

                $customer_employment_since_month_counter = (12 - $preMonth) + ($currentMonth) + 1 + (12 * ($currentYear - $preYear - 1));
                
                $company_type_name_data = $this->db->select('m_company_type_name')->where('m_company_type_id',$company_type)->from('master_company_type')->get()->row();
                if ($company_type_name_data->m_company_type_name != '') {
                    $company_type_name = $company_type_name_data->m_company_type_name;
                }
                
                $arrEmployment = [
                    'employer_name' => $employer_company_name,
                    'company_id' => $company_type,
                    'emp_work_mode' => $work_mode,
                    'emp_occupation_id' => $emp_occupation_id,
                    'emp_department' => $department_name,
                    'emp_designation' => $designation_name,
                    'industry' => $industry_name,
                    'emp_residence_since' => $employed_since_current,
                    'emp_salary_mode' => $salary_mode_name,
                    'salary_mode' => $salary_mode_name,
                    'monthly_income' => $net_monthly_income,
                    'emp_house' => $emp_address1,
                    'emp_street' => $emp_address2,
                    'emp_landmark' => $emp_landmark,
                    'emp_state' => $emp_state,
                    'state_id' => $emp_state,
                    'city_id' => $emp_city,
                    'emp_city' => $emp_city,
                    'emp_pincode' => $emp_pincode,
                    'emp_website' => '',
                    'emp_employer_type' => $company_type_name,
                    'presentServiceTenure' => $customer_employment_since_month_counter,
                    'emp_status' => 'YES'
                ];
                $count_data = $this->db->select('*')->where('lead_id', $lead_id)->from('customer_employment')->get()->num_rows();
                if ($count_data == 0) {
                    $this->db->insert('customer_employment', $arrEmployment);
                    $msg = "Employment Details Added Successfully.";
                } else {
                    $this->db->where('lead_id', $lead_id)->update('customer_employment', $arrEmployment);
                    $msg = "Employment Details Updated Successfully.";
                }
                $arrReferencesEmp = [
                    'lcr_lead_id' => $lead_id,
                    'lcr_name' => $emp_name,
                    'lcr_relationType' => $emp_relation,
                    'ref_type' => 2,
                    'lcr_mobile' => $emp_mobile,
                    'lcr_active' => 1,
                    'lcr_created_on' => date('Y-m-d H:i:s'),
                    'lcr_updated_on' => date('Y-m-d H:i:s')
                ];
                $references_data = $this->db->select('lcr_id,lcr_lead_id,ref_type')->where('lcr_lead_id', $lead_id)->where('ref_type', 2)->from('lead_customer_references')->get()->row();
                if (isset($references_data->lcr_id) && $references_data->lcr_id != '') {
                    $this->db->where('lcr_id', $references_data->lcr_id)->update('lead_customer_references', $arrReferencesEmp);
                } else {
                    $this->db->insert('lead_customer_references', $arrReferencesEmp);
                }
                /* Start median_salary */

                $salary3 = $salary2 = $salary1 = "";
                $salary_array = [];

                if (!empty($amount1)) {
                    $salary_array[] = $salary1 = $amount3;
                }

                if (!empty($amount2)) {
                    $salary_array[] = $salary2 = $amount3;
                }

                if (!empty($amount3)) {
                    $salary_array[] = $salary3 = $amount3;
                }


                $average_salary = 0;

                if (!empty($salary_array)) {
                    $average_salary = round(array_sum($salary_array) / count($salary_array));
                }


                /* Start salary_variance */
                $x = 0;
                $y = 0;
                if ($salary1 != '' && $salary2 != '' && $salary3 != '') {
                    if ($salary1 > $salary2) {
                        $x = ($salary1 - $salary2) / $salary1;
                    } else if ($salary2 >= $salary1) {
                        $x = ($salary2 - $salary1) / $salary2;
                    }
                    if ($salary2 >= $salary3) {
                        $y = ($salary2 - $salary3) / $salary2;
                    } else if ($salary3 >= $salary2) {
                        $y = ($salary3 - $salary2) / $salary3;
                    } else if ($salary1 >= $salary3) {
                        $x = ($salary1 - $salary3) / $salary1;
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
                /* Start salary_on_time */
                $date1 = explode("/", $salary_date1);
                $date2 = explode("/", $salary_date2);
                $date3 = explode("/", $salary_date3);

                $dt = $date1 + $date2 + $date3;
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
                //$next_pay_date = $next_pay_date;
                $lead_data = $this->db->query("select cibil,tenure,purpose,obligations from leads where lead_id='" . $lead_id . "'")->row_array();
                $customer_lead_data = $this->db->query("select dob from lead_customer where customer_lead_id='" . $lead_id . "'")->row_array();
                $cam_data = $this->db->select('cam_id,loan_recommended,eligible_loan,eligible_foir_percentage,roi,processing_fee_percent,disbursal_date,tenure,repayment_date')->where('lead_id', $lead_id)->from('credit_analysis_memo')->get()->row();
                $employment_data = $this->db->query("select monthly_income,presentServiceTenure from customer_employment where lead_id='" . $lead_id . "'")->row_array();

                /* Start ntc */
                $dob = $customer_lead_data['dob'];
                $cibil = $lead_data['cibil'];
                $presentServiceTenure = $employment_data['presentServiceTenure'];

                $today = date('Y-m-d');
                $diff = abs(strtotime($today) - strtotime($dob));
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

                // $data['borrower_age'] = $years .' Y, '. $months .' M, '. $days .' days';
                $borrower_age = $years . 'Y, ' . $months . 'M';

                if ($presentServiceTenure > 12) {
                    $pst = "HIGH";
                } else if ($presentServiceTenure > 6 && $presentServiceTenure <= 12) {
                    $pst = "MEDIUM";
                } else if ($presentServiceTenure <= 6) {
                    $pst = "LOW";
                }

                $job_stability = $pst;

                $ntc = "YES";

                if (in_array($cibil, array(11, 12, 14, 15, 16, 17, 18))) {// for consumer score
                    $ntc = "YES";
                } else if ($cibi >= 300) {
                    $ntc = "NO";
                }

                $loan_recommended = $cam_data->loan_recommended;
                $obligations = $lead_data['obligations'];
                $monthly_salary = $employment_data['monthly_income'];
                $eligible_foir_percentage = $cam_data->eligible_foir_percentage;
                $roi = ($cam_data->roi ? $cam_data->roi : 1);
                $processing_fee_percent = ($cam_data->processing_fee_percent) ? $cam_data->processing_fee_percent : 0;
                $disbursal_date = $cam_data->disbursal_date;
                $repayment_date = $cam_data->repayment_date;

                $d1 = strtotime($disbursal_date);
                $d2 = strtotime($repayment_date);
                $tenure = 0;
                if (!empty($d2)) {
                    $datediff = $d2 - $d1;
                    $tenure = round($datediff / (60 * 60 * 24));
                }

                $admin_fee = round(($loan_recommended * $processing_fee_percent) / 100);
                $gst = round(($admin_fee * 18) / 100);
                $total_admin_fee = round($admin_fee + $gst);

                $panel_roi = $roi * 2;
                $repayment_amount = ($loan_recommended + ($loan_recommended * $roi * $tenure) / 100);

                $net_disbursal_amount = $loan_recommended - $total_admin_fee;
                $final_foir_percentage = number_format((($loan_recommended + $obligations) / $monthly_salary) * 100, 2);
                $foir_enhanced_by = number_format($final_foir_percentage - $eligible_foir_percentage, 2);

                $arrCreditAnalysisEmp = [
                    'lead_id' => $lead_id,
                    'job_stability' => $job_stability,
                    'city_category' => 'B',
                    'ntc' => $ntc,
                    'roi' => $roi,
                    'panel_roi' => $panel_roi,
                    'tenure' => $tenure,
                    'cam_processing_fee_gst_type_id' => 2,
                    'admin_fee' => $total_admin_fee,
                    'repayment_amount' => $repayment_amount,
                    'adminFeeWithGST' => $gst,
                    'total_admin_fee' => $admin_fee,
                    'net_disbursal_amount' => $net_disbursal_amount,
                    'final_foir_percentage' => $final_foir_percentage,
                    'foir_enhanced_by' => $foir_enhanced_by,
                    'end_use' => $lead_data['purpose'],
                    'borrower_age' => $borrower_age,
                    'run_other_pd_loan' => 'NO',
                    'delay_other_loan_30_days' => 'NO',
                    'cam_appraised_monthly_income' => $average_salary,
                    'cam_appraised_obligations' => '0.00',
                    'salary_variance' => $variance,
                    'salary_on_time' => $salary_on_time,
                    'median_salary' => $average_salary,
                    'salary_credit1_date' => $salary_date1,
                    'salary_credit2_date' => $salary_date2,
                    'salary_credit3_date' => $salary_date3,
                    'salary_credit1_amount' => $amount1,
                    'salary_credit2_amount' => $amount2,
                    'salary_credit3_amount' => $amount3,
                    'remark' => 'Lead generated from Instant journey',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if (!empty($cam_data->cam_id)) {
                    $this->db->where('cam_id', $cam_data->cam_id)->update('credit_analysis_memo', $arrCreditAnalysisEmp);
                } else {
                    $this->db->insert('credit_analysis_memo', $arrCreditAnalysisEmp);
                }
                return json_encode($this->response(['Status' => 1, 'Message' => $msg, 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function saveBankingDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("bank_account_no", "bank account no", "required|trim|numeric");
            $this->form_validation->set_rules("ifsc_code", "ifsc code", "required|trim");
            $this->form_validation->set_rules("bank_name", "bank name", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = $post['lead_id'];
                $bank_account_no = $post['bank_account_no'];
                $reconfirm_bank_account_no = $post['reconfirm_bank_account_no'];
                $ifsc_code = $post['ifsc_code'];
                $bank_name = $post['bank_name'];
                $branch_name = $post['branch_name'];
                $bank_account_type = $post['bank_account_type'];
                $same_account = $post['same_account'];
                /*
                  $ext                       = $post['ext'];
                  $image_name = $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999);
                  $imgUrl = $image_name . "." . $ext;
                  $image_upload_dir = UPLOAD_PATH . $imgUrl;
                  $flag = file_put_contents($image_upload_dir, base64_decode($post['cancelled_cheque']));
                 */

                $get_bank_name = $this->db->select('m_bank_type_name')->where('m_bank_type_id', $bank_account_type)->from('master_bank_type')->get()->row();
                $get_customer_name = $this->db->select('first_name,middle_name,sur_name')->where('customer_lead_id', $lead_id)->from('lead_customer')->get()->row();
                $full_name = $get_customer_name->first_name . ' ' . $get_customer_name->middle_name . ' ' . $get_customer_name->sur_name;
                $arrBankingInfo = [
                    'lead_id' => $lead_id,
                    'bank_name' => $bank_name,
                    'ifsc_code' => $ifsc_code,
                    'branch' => $branch_name,
                    'beneficiary_name' => $full_name,
                    'account' => $bank_account_no,
                    //'cancelled_cheque'=>$imgUrl,
                    'confirm_account' => $reconfirm_bank_account_no,
                    'account_type' => $get_bank_name->m_bank_type_name,
                    'same_account' => $same_account,
                    'created_on' => date('Y-m-d H:i:s'),
                    'updated_on' => date('Y-m-d H:i:s')
                ];
                $sql_qry = $this->db->select('*')->where('lead_id', $lead_id)->from('customer_banking')->get();
                if ($sql_qry->num_rows() == 0) {
                    $this->db->insert('customer_banking', $arrBankingInfo);
                    $bankId = $this->db->insert_id();
                    $msg = "Bank Details Added Successfully.";
                } else {
                    $this->db->where('lead_id', $lead_id)->update('customer_banking', $arrBankingInfo);
                    $bankIRecord = $sql_qry->row();
                    $bankId = $bankIRecord->id;
                    $msg = "Bank Details Updated Successfully.";
                }
                return json_encode($this->response(['Status' => 1, 'Message' => $msg, 'lead_id' => $lead_id, 'bankId' => $bankId], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function uploadDocument_post() {
        $lead_id = 0;
        $apiStatusId = 0;
        $mobile = '';
        $apiStatusMessage = "";
        $ReferenceCode = "";
        $method_name = "BANK_STATEMENT_UPLOAD";
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
            if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
                throw new Exception("UnAuthorized Access.");
            }
            $email_message .= "<br/>Step 3";
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $email_message .= "<br/>Step 4";
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
                $this->form_validation->set_rules("docs_type", "Docs Type", "required|trim");
                //$this->form_validation->set_rules("file", "Document", "required|trim");
                //$this->form_validation->set_rules("ext", "Extension", "required|trim");
                $email_message .= "<br/>Step 5";
                if ($this->form_validation->run() == FALSE) {
                    $email_message .= "<br/>Step 6";
                    throw new Exception(validation_errors());
                } else {
                    $email_message .= "<br/>Step 7";
                    $lead_id = $post['lead_id'];
                    $document_id = $post['docs_type'];
                    $password = $post['password'];
                    $ReferenceCode = $post['refrence_no'];
                    $ip = !empty($post['ip']) ? $post['ip'] : "";
                    $ext = $post['ext'];
                    $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id,docs_sub_type', 'docs_master');
                    $tempDetails = $query->result_array();
                    $docs_master = array();
                    foreach ($tempDetails as $document_data) {
                        $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                    }
                    $query = $this->db->select('lead_id,customer_id,lead_status_id,lead_reference_no,pancard,mobile,lead_is_mobile_verified')->where('lead_id', $lead_id)->from('leads')->get();
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

                        if ($ext != 'pdf' && in_array($document_id, array(6, 7, 13, 16))) {
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
                          if ($flag) {
                         */

                        $upload_return = uploadDocument(base64_decode($post['file']), $lead_id, 1, $ext);
                        if ($upload_return['status'] == 1) {
                            $imgUrl = $upload_return['file_name'];
                        } else {
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

                        $count_data = $this->db->select('docs_id')->where('lead_id', $lead_id)->where('docs_master_id', $document_id)->where('pancard', $pancard)->from('docs')->get()->row();

                        if ($count_data->docs_id != '') {
                            $this->db->where('docs_id', $count_data->docs_id)->update('docs', $insert_document_data);
                            $docsId = $count_data->docs_id;
                        } else {
                            $result = $this->db->insert('docs', $insert_document_data);
                            $docsId = $this->db->insert_id();
                        }
                        if (!empty($document_id) && $document_id == '6') {
                            $request_array = array();
                            if (!empty($count_data->docs_id)) {
                                $request_array['doc_id'] = $count_data->docs_id;
                            }
                            require_once(COMPONENT_PATH . 'CommonComponent.php');
                            $CommonComponent = new CommonComponent();
                            $bank_analysis_upload = $CommonComponent->call_payday_bank_analysis($method_name, $lead_id, $request_array);
                            /*
                            if ($bank_analysis_upload['status'] != 1) {
                                $email_message .= "<br/>Step 16";
                                throw new Exception($bank_analysis_upload['error_msg']);
                            } 
                            */
                        }
                        if ($ext == 'pdf' || $ext == 'PDF') {
                            $path = '<a href="' . WEBSITE_URL . 'view-document-file/' . $docsId . '/1?t=' . time() . '" target="_blank"><img  src="' . WEBSITE_URL . 'public/images/pdf.png" width="100" height="50" /></a>';
                        } else {
                            $path = '<img src="' . WEBSITE_URL . 'view-document-file/' . $docsId . '/1?t=' . time() . '" width="200" height="100"><br>';
                        }

                        $email_message .= "<br/>Step 19";
                        if (!empty($docsId)) {
                            $email_message .= "<br/>Step 20";
                            $apiStatusId = 1;
                            $apiStatusMessage = "Documents uploaded Successfully. You can upload more documents";
                        } else {
                            $email_message .= "<br/>Step 21";
                            throw new Exception('Unable to upload docs. You can contact to customer care.');
                        }
                        /*
                          } else {
                          $email_message .= "<br/>Step 22";
                          throw new Exception('Failed to save Docs. Try Again');
                          }
                         */
                        $email_message .= "<br/>Step 23";
                    }
                    $email_message .= "<br/>Step 24";
                }
                $email_message .= "<br/>Step 25";
            } else {
                $email_message .= "<br/>Step 26";
                throw new Exception('Request Method Post Failed.');
            }
        } catch (Exception $e) {
            $apiStatusId = 0;
            $apiStatusMessage = $e->getMessage();
        }
        $email_message .= "<br/>Step 26";
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Mobile' => $mobile, 'lead_id' => $lead_id, 'file_name' => $path, 'doc_name' => $docs_sub_type, 'refrence_no' => $ReferenceCode, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
    }

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

                $mobile = $post['mobile'];
                $lead_id = $post['lead_id'];
                $otp = rand(1000, 9999);
                $data = ["mobile" => $mobile, "otp" => $otp];
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
                    'lot_otp_trigger_time' => date("Y-m-d H:i:s")
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
                    return json_encode($this->response(['Status' => 1, 'Message' => 'OTP resend successfully', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to resend OTP.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function updateCase_post() {
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
                $lead_id = $post['lead_id'];
                $case_type = $post['case_type'];
                $updateArr = array('lead_status_id' => 9, 'stage' => 'S9', 'status' => 'REJECT', 'lead_rejected_datetime' => date('Y-m-d H:i:s'), 'lead_rejected_reason_id' => 57);
                $updated = $this->db->where('lead_id', $lead_id)->update('leads', $updateArr);
                if ($updated) {
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Rejected successfully', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to reject.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function verificationAPI_post() {
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
                $lead_id = $post['lead_id'];
                $interest_rate = $post['interest_rate'];
                $eligible_foir_percentage = $post['eligible_foir_percentage'];
                $processing_fee = $post['processing_fee'];
                $eligible_loan = $post['eligible_loan'];
                $recommended_amt = $post['recommended_amt'];
                $tenure = $post['tenure'];
                $disbursal_date = $post['disbursal_date'];
                $repayment_date = $post['repayment_date'];
                $arrCreditAnalysis = [
                    'roi' => $interest_rate,
                    'eligible_foir_percentage' => $eligible_foir_percentage,
                    'processing_fee_percent' => $processing_fee,
                    'disbursal_date' => $disbursal_date,
                    'repayment_date' => $repayment_date,
                    'eligible_loan' => $eligible_loan,
                    'tenure' => $tenure,
                    'loan_recommended' => $recommended_amt
                ];

                $this->db->where('lead_id', $lead_id)->update('credit_analysis_memo', $arrCreditAnalysis);
                $this->db->where('lead_id', $lead_id)->update('leads', ['tenure' => $tenure]);
                $get_lead_customer = $this->db->query("select email_verified_status,alternate_email_verified_status,pancard_verified_status,alternate_email from lead_customer where customer_lead_id='" . $lead_id . "'")->row_array();
                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();

                //if (empty($get_lead_customer['email_verified_status']) && $get_lead_customer['email_verified_status'] == '') {
                    //return json_encode($this->response(['Status' => 0, 'Message' =>'ajit'], REST_Controller::HTTP_OK));
                    $primary_email_return = $CommonComponent->call_email_verification_api($lead_id, ['email_type' => 1]);
                    //if ($primary_email_return['status'] == 1 && $primary_email_return['email_validate_status'] == 1) {
                        //$return_status_id = 1;
                        //$return_message = "Primary email Verified";
                        //$return_data = array('step' => $step, 'form_name' => $form_name, 'lead_id' => $lead_id);
                    //} else {
                        //return json_encode($this->response(['Status' => 0, 'Message' => "Primary email does not vailid email. Please check the application log.", 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    //}
                //}
                if (!empty($get_lead_customer['alternate_email']) && $get_lead_customer['alternate_email'] != '') {
                    $CommonComponent->call_office_email_verification_api($lead_id);
                }
                /*
                  if (empty($get_lead_customer['alternate_email_verified_status']) && $get_lead_customer['alternate_email_verified_status'] =='') {
                  $office_email_return = $CommonComponent->call_office_email_verification_api($lead_id);
                  if ($office_email_return['status'] == 1 && $office_email_return['email_validate_status'] == 1) {
                  //$return_status_id = 1;
                  //$return_message = "Office email Verified";
                  //$return_data = array('step' => $step, 'form_name' => $form_name, 'lead_id' => $lead_id);
                  } else {
                  json_encode($this->response(['Status' => 0, 'Message' => "Office email does not vailid email. Please check the application log.", 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                  }
                  }
                 */
                //if (empty($get_lead_customer['pancard_verified_status']) && $get_lead_customer['pancard_verified_status'] != '1') {
                    $pan_veri_return = $CommonComponent->call_pan_verification_api($lead_id);
                    //if ($pan_veri_return['status'] == 1) {
                        //if ($pan_veri_return['pan_valid_status'] == 1) {
                            //$return_status_id = 1;
                            //$return_message = "PAN Verified";
                            //$return_data = array('step' => $step, 'form_name' => $form_name, 'lead_id' => $lead_id);
                       // } else {
                            //return json_encode($this->response(['Status' => 0, 'Message' => 'Customer Name does not matched with PAN Detail. Please check the application log.'], REST_Controller::HTTP_OK));
                        //}
                    //} else {
                        //return json_encode($this->response(['Status' => 0, 'Message' => $pan_veri_return['errors'], 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    //}
                //}
                if ($get_lead_customer['email_verified_status'] == 'YES' && $get_lead_customer['pancard_verified_status'] == '1') {
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Verified', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Verified', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function bankAccountVerification_post() {
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
                $lead_id = $post['lead_id'];
                $bankId = $post['bankId'];
                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();
                $return_bank_verification_array = $CommonComponent->payday_bank_account_verification_api($lead_id, array('cust_banking_id' => $bankId));
                if ($return_bank_verification_array['status'] != 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => $return_bank_verification_array['error_msg']], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 1, 'Message' => $data['data']['Message']], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function loanQuoteEngine_post() {
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
                $lead_id = $post['lead_id'];
                $get_leads = $this->db->query("select loan_amount from leads where lead_id='" . $lead_id . "'")->row_array();                
                require_once(COMPONENT_PATH . 'CommonComponent.php');                
                $CommonComponent = new CommonComponent();
                $bre_quote_info = $CommonComponent->call_bre_quote_engine($lead_id);
                $arrData = ['eligible_foir_percentage'=>$bre_quote_info['eligible_foir_percentage'],'max_loan_amount'=>$bre_quote_info['max_loan_amount'],'loan_recommended'=>$bre_quote_info['min_loan_amount'],'interest_rate'=>$bre_quote_info['interest_rate'],'max_loan_tenure'=>$bre_quote_info['max_loan_tenure'],'processing_fee'=>$bre_quote_info['processing_fee'],'loan_amount'=>$get_leads['loan_amount']];
                return json_encode($this->response(['Status' => 1, 'Message' => 'Get loan quote engine for data.', 'Data' => $arrData, 'loan_amount' => $get_leads['loan_amount']], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }
    
    public function onloadLoanQuoteEngine_post() {
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
                $lead_id = $post['lead_id'];
                $get_leads = $this->db->query("select loan_amount from leads where lead_id='" . $lead_id . "'")->row_array();
                $cam_data = $this->db->query("select eligible_foir_percentage,eligible_loan,loan_recommended,roi,tenure,processing_fee_percent from credit_analysis_memo where lead_id='" . $lead_id . "'")->row_array();
                /*
                require_once(COMPONENT_PATH . 'CommonComponent.php');                
                $CommonComponent = new CommonComponent();
                $bre_quote_info = $CommonComponent->call_bre_quote_engine($lead_id);
                */
                $arrData = ['eligible_foir_percentage'=>$cam_data['eligible_foir_percentage'],'eligible_loan'=>$cam_data['eligible_loan'],'loan_recommended'=>$cam_data['loan_recommended'],'roi'=>$cam_data['roi'],'tenure'=>$cam_data['tenure'],'processing_fee_percent'=>$cam_data['processing_fee_percent'],'loan_amount'=>$get_leads['loan_amount']];
                return json_encode($this->response(['Status' => 1, 'Message' => 'Get loan quote engine for data.', 'Data' => $arrData, 'loan_amount' => $get_leads['loan_amount']], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }
    
    public function documentVerificationAPI_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
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
                $lead_id = $post['lead_id'];
                require_once (COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();
                $docs_data = $CommonComponent->check_customer_mandatory_documents($lead_id);
                if ($docs_data['status'] == 0) {
                    return json_encode($this->response(['Status' => 0, 'Message' => $docs_data['error']], REST_Controller::HTTP_OK));
                } else {
                    $aadhaar_ocr_return = $CommonComponent->call_aadhaar_ocr_api($lead_id);
                    /*
                      if ($aadhaar_ocr_return['status'] == 1) {
                      if ($aadhaar_ocr_return['aadhaar_valid_status'] == 1) {
                      //json_encode($this->response(['Status'=>1,'Message'=>'Aadhaar OCR Verified'], REST_Controller::HTTP_OK));
                      } else {
                      return json_encode($this->response(['Status' => 0, 'Message' => 'Customer Aadhaar does not matched with Aadhaar OCR Detail. Please check the application log.'], REST_Controller::HTTP_OK));
                      }
                      } else {
                      return json_encode($this->response(['Status' => 0, 'Message' => $aadhaar_ocr_return['errors']], REST_Controller::HTTP_OK));
                      }
                     */
                    $pan_ocr_return = $CommonComponent->call_pan_ocr_api($lead_id);
                    /*
                      if ($pan_ocr_return['status'] == 1) {
                      if ($pan_ocr_return['pan_valid_status'] == 1) {
                      //json_encode($this->response(['Status'=>1,'Message'=>'PAN OCR Verified'], REST_Controller::HTTP_OK));
                      } else {
                      return json_encode($this->response(['Status' => 0, 'Message' => 'Customer PAN does not matched with PAN OCR Detail. Please check the application log.'], REST_Controller::HTTP_OK));
                      }
                      } else {
                      return json_encode($this->response(['Status' => 0, 'Message' => $pan_ocr_return['errors']], REST_Controller::HTTP_OK));
                      }
                     */
                    $docs_recod = $this->db->query("select docs_id,file,pancard,mobile,docs_type,sub_docs_type,docs_master_id from docs where docs_master_id='6' AND lead_id='" . $lead_id . "'")->row_array();
                    if ($docs_recod->docs_id != '') {
                        $method_name = "BANK_STATEMENT_DOWNLOAD";
                        $request_array['doc_id'] = $docs_recod->docs_id;
                        $CommonComponent->call_payday_bank_analysis($method_name, $lead_id, $request_array);
                    }

                    $CommonComponent->call_bureau_api($lead_id);

                    $return_bre_response = $CommonComponent->call_bre_rule_engine($lead_id);

                    //if ($aadhaar_ocr_return['status'] == '1' && $aadhaar_ocr_return['aadhaar_valid_status'] == '1' && $pan_ocr_return['status'] == '1' && $pan_ocr_return['pan_valid_status'] == '1') {
                    $lead_recod = $this->db->query("select email,first_name,user_type,lead_reference_no,mobile from leads where lead_id=$lead_id")->row_array();
                    $lead_customer_recod = $this->db->query("select first_name,sur_name from lead_customer where customer_lead_id=$lead_id")->row_array();
                    $application_no = $this->Tasks->generateApplicationNo($lead_id);
                    $reference_no = $this->Tasks->generateReferenceCode($lead_id, $lead_recod['first_name'], $lead_customer_recod['sur_name'], $lead_recod['mobile']);
                    if ($lead_recod['user_type'] == 'NEW') {
                        $last_assignment = $this->Tasks->get_instant_lead_last_assignment();
                        if ($last_assignment['status'] == 1) {
                            $last_assignment = $last_assignment['data'];
                            if ($last_assignment['lead_credithead_assign_user_id'] == 349) {
                                $lead_credithead_assign_user_id = 47;
                                $lead_credithead_assign_datetime = date("Y-m-d H:i:s");
                            } else {
                                $lead_credithead_assign_user_id = 349;
                                $lead_credithead_assign_datetime = date("Y-m-d H:i:s");
                            }
                        } else {
                            $lead_credithead_assign_user_id = 349;
                            $lead_credithead_assign_datetime = date("Y-m-d H:i:s");
                        }
                    } else {
                        $lead_credithead_assign_user_id = 47;
                        $lead_credithead_assign_datetime = date("Y-m-d H:i:s");
                    }



                    if (in_array($return_bre_response['bre_decision_status_id'], array(1, 2))) {
                        $cam_data = $this->db->select('cam_id')->where('lead_id', $lead_id)->from('credit_analysis_memo')->get()->row();
                        if ($cam_data->cam_id > 0) {
                            $this->db->where('cam_id', $cam_data->cam_id)->update('credit_analysis_memo', ['cam_status' => 1]);
                        }
                        $this->db->where('lead_id', $lead_id)->update('leads', ['status' => 'APPLICATION-RECOMMENDED', 'application_status' => 1, 'stage' => 'S10', 'lead_status_id' => 10, 'lead_credithead_assign_user_id' => $lead_credithead_assign_user_id, 'lead_credithead_assign_datetime' => $lead_credithead_assign_datetime, 'application_no' => $application_no, 'lead_reference_no' => $reference_no]);
                    } else {
                        $this->db->where('lead_id', $lead_id)->update('leads', ['application_no' => $application_no, 'lead_reference_no' => $reference_no]);
                    }


                    $CommonComponent->sent_lead_thank_you_email($lead_id, $lead_recod['email'], $lead_recod['first_name'], $reference_no);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Verified[1]', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    //}
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function test_post() {
        $reference_no = $this->Tasks->generateReferenceCode(15880, 'Ajit', 'Singh', '88888888888');
        echo $reference_no;
        die;
        $application_no = $this->Tasks->generateApplicationNo(15880);
        echo '<pre>';
        print_r(application_no);
        die;
    }
    
    // public function saveCustomerDocument_post() {
        
    //     $lead_id = 0;
    //     $apiStatusId = 0;
    //     $mobile = '';
    //     $apiStatusMessage = "";
    //     $ReferenceCode = "";
    //     $docs_master = [];

    //     $input_data = file_get_contents("php://input");

    //         if ($input_data) {
    //             $post = $this->security->xss_clean(json_decode($input_data, true));
    //         } else {
    //             $post = $this->security->xss_clean($_POST);
    //         }

    //             $this->form_validation->set_data($post);
    //             // $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
    //             //  $this->form_validation->set_rules("docs_type", "Docs Type", "required|trim");
               
    //             // if ($this->form_validation->run() == FALSE) {
    //             //       echo 'Testing wrong';
    //             //     throw new Exception(validation_errors());
    //             // } else {
                    
    //               // echo 'Testing New';
                                     
    //                 $lead_id       = $post['lead_id'];
    //                 $document_id   = $post['docs_type'];
    //                 $upload_return = $post['doc_file'];
                    
    //                 $config['allowed_types']        = 'jpeg|jpg|png';
    //                 $config['max_size']             = 100; // in KB
    //                 $config['max_width']            = 1024;
    //                 $config['max_height']           = 768;
                    
    //                 $this->load->library('upload', $config);
    //                 $image_upload_dir = UPLOAD_PATH . $upload_return;
    //               //  print_r($image_upload_dir); die;
    //                 $doc_uploaded = file_put_contents($image_upload_dir, base64_decode($post['doc_file']));
                    
                    
    //                 $query = $this->db->select('lead_id,customer_id,lead_status_id,lead_reference_no,pancard,mobile,lead_is_mobile_verified')->where('lead_id', $lead_id)->from('leads')->get();
    //                 $email_message .= "<br/>Step 8";
    //                 if ($query->num_rows() > 0) {
    //                     $email_message .= "<br/>Step 9";
    //                     $result = $query->row();
    //                     $existing_lead_id = $result->lead_id;
    //                     $pancard = $result->pancard;
    //                     $ReferenceCode = $result->lead_reference_no;
    //                     $lead_status_id = $result->lead_status_id;
    //                     $customer_id = $result->customer_id;
    //                     $mobile = $result->mobile;
    //                     $lead_is_mobile_verified = $result->lead_is_mobile_verified;
    //                     if ($existing_lead_id != $lead_id) {
    //                         $email_message .= "<br/>Step 10";
    //                         throw new Exception('Invalid access for the application.');
    //                     }

    //                     if ($lead_is_mobile_verified != 1) {
    //                         $email_message .= "<br/>Step 11";
    //                         throw new Exception('Application OTP not verified.');
    //                     }

    //                     if ($lead_status_id > 1) {
    //                         $email_message .= "<br/>Step 12";
    //                         throw new Exception('Your application has been move to next step.');
    //                     }

    //                     if ($ext != 'pdf' && in_array($document_id, array(6, 7, 13, 16))) {
    //                         throw new Exception('Only pdf file allowed.');
    //                     }

    //                     if (!in_array($ext, array('jpg', 'jpeg', 'png')) && in_array($document_id, array(18))) {
    //                         throw new Exception('Only jpg, jpeg, png file allowed.');
    //                     }

    //                     $query = $this->db->select('id,docs_type,docs_sub_type')->where('id', $document_id)->from('docs_master')->get();

    //                     if ($query->num_rows() == 0) {
    //                         $email_message .= "<br/>Step 13";
    //                         throw new Exception('Document type is out of range.');
    //                     } else {
    //                         $email_message .= "<br/>Step 14";
    //                         $documentMaster = $query->row();
    //                         $document_type_id = $documentMaster->id;
    //                         $docs_type = $documentMaster->docs_type;
    //                         $docs_sub_type = $documentMaster->docs_sub_type;
    //                     }
                  
    //                 $insert_document_data = [
    //                     "lead_id" => $lead_id,
    //                     "docs_type" => $document_id,
    //                     "file" => $doc_uploaded,
                      
    //                     "created_on" => date("Y-m-d H:i:s")
    //                 ];                            
    //                   $result = $this->db->insert('docs', $insert_document_data);

    //                         // if (!empty($docsId)) {
        
    //                         //     $apiStatusId = 1;
    //                         //     $apiStatusMessage = "Documents uploaded Successfully. You can upload more documents";
    //                         // } else {
                               
    //                         //     throw new Exception('Unable to upload docs. You can contact to customer care.');
    //                         // }
    //                     /*} else {
    //                         $email_message .= "<br/>Step 22";
    //                         throw new Exception('Failed to save Docs. Try Again');
    //                     }*/
                        
    //                 //}
    //                 }

    //     //return json_encode($this->response(['Status' =>0, 'Message' => $insert_document_data], REST_Controller::HTTP_OK));
    //     return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Mobile' => $mobile, 'lead_id' => $lead_id, 'refrence_no' => $ReferenceCode, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
    
//}
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
            // if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {
            //     throw new Exception("UnAuthorized Access.");
            // }
            $email_message .= "<br/>Step 3";
            // if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $email_message .= "<br/>Step 4";
                $this->form_validation->set_data($post);
                // $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
                // $this->form_validation->set_rules("docs_type", "Docs Type", "required|trim");
                //$this->form_validation->set_rules("file", "Document", "required|trim");
                //$this->form_validation->set_rules("ext", "Extension", "required|trim");
                $email_message .= "<br/>Step 5";
                // if ($this->form_validation->run() == FALSE) {
                //     $email_message .= "<br/>Step 6";
                //     throw new Exception(validation_errors());
                // } else {
                  
                    $email_message .= "<br/>Step 7";
                    $lead_id = $post['lead_id'];
                    $document_id = $post['docs_type'];
                    $password = $post['password'];
                    $ip = !empty($post['ip']) ? $post['ip'] : "";
                    $ext = $post['ext'];
                    $query = $this->Tasks->selectdata(['document_active' => 1, 'document_deleted' => 0, 'docs_type!=' => 'DIGILOCKER'], 'id,docs_sub_type', 'docs_master');
                    $tempDetails = $query->result_array();
                   // print_r($tempDetails); die;
                    $docs_master = array();
                    foreach ($tempDetails as $document_data) {
                        $docs_master[$document_data['id']] = $document_data['docs_sub_type'];
                    }
                    $query = $this->db->select('lead_id,customer_id,lead_status_id,lead_reference_no,pancard,mobile,lead_is_mobile_verified')->where('lead_id', $lead_id)->from('leads')->get();
                    $email_message .= "<br/>Step 8";
                    if ($query->num_rows() > 0) {
                        $email_message .= "<br/>Step 9";
                        $result = $query->row();
                        $existing_lead_id = $result->lead_id;
                        $pancard = $result->pancard;
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

                        if ($ext != 'pdf' && in_array($document_id, array(6, 7, 13, 16))) {
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
                        //  $email_message .= "<br/>Step 18";
                          /*if ($flag) {
                         */

                        // $upload_return = uploadDocument(base64_decode($post['file']), $lead_id, 1, $ext);
                        // if ($upload_return['status'] == 1) {
                        //     $imgUrl = $upload_return['file_name'];
                        // } else {
                        //     $email_message .= "<br/>Step 15";
                        //     throw new Exception('Please upload the document!');
                        // }
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

                        $count_data = $this->db->select('docs_id')->where('lead_id', $lead_id)->where('docs_master_id', $document_id)->where('pancard', $pancard)->from('docs')->get()->row();

                        if ($count_data->docs_id != '') {
                            $this->db->where('docs_id', $count_data->docs_id)->update('docs', $insert_document_data);
                            $docsId = $count_data->docs_id;
                        } else {
                            $result = $this->db->insert('docs', $insert_document_data);
                            $docsId = $this->db->insert_id();
                        }
                       

                        $email_message .= "<br/>Step 19";
                        if (!empty($docsId)) {
                            $email_message .= "<br/>Step 20";
                            $apiStatusId = 1;
                            $apiStatusMessage = "Documents uploaded Successfully. You can upload more documents";
                        } else {
                            $email_message .= "<br/>Step 21";
                            throw new Exception('Unable to upload docs. You can contact to customer care.');
                        }
                        /*
                          } else {
                          $email_message .= "<br/>Step 22";
                          throw new Exception('Failed to save Docs. Try Again');
                          }
                         */
                        $email_message .= "<br/>Step 23";
                    }
                    $email_message .= "<br/>Step 24";
               // }
                $email_message .= "<br/>Step 25";
            // } else {
            //     $email_message .= "<br/>Step 26";
            //     throw new Exception('Request Method Post Failed.');
            // }
        } catch (Exception $e) {
            $apiStatusId = 0;
            $apiStatusMessage = $e->getMessage();
        }
        $email_message .= "<br/>Step 26";
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Mobile' => $mobile, 'lead_id' => $lead_id, 'file_name' => $path, 'doc_name' => $docs_sub_type, 'refrence_no' => $ReferenceCode, 'document_master' => $docs_master], REST_Controller::HTTP_OK));
    }
    
}

?>
