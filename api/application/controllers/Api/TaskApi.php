<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';


class TaskApi extends REST_Controller {

    // public $white_listed_ips = array("208.109.63.229");

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }



    public function CsPersonalDetails_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("dob", "dob", "required|trim");
        $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        //$this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("email", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
        $this->form_validation->set_rules("city_id", "City Name", "required|trim");
        $this->form_validation->set_rules("state_id", "State Name", "required|trim");
        $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("monthly_salary_amount", "Monthly Amount", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("pincode", "Pincode", "required|trim|numeric|is_natural|min_length[6]|max_length[6]|regex_match[/^[0-9]+$/]");

        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
        } else {

            $flag = 1;
            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            //$full_name = strtoupper(strval($post['first_name']));
            // $mobile = intval($post['mobile']);
            $lead_id = intval($post['lead_id']);
            $dob = $post['dob'];
            $pincode = strtoupper(strval($post['pincode']));
            $email = strtoupper(strval($post['email']));
            $city_id = $post['city_id'];
            $state_id = $post['state_id'];
            $loan_amount = !empty($post['loan_amount']) ? doubleval($post['loan_amount']) : "";
            $monthly_amount = !empty($post['monthly_salary_amount']) ? doubleval($post['monthly_salary_amount']) : 0;

            $lead_status_id = 1;
            $user_type = 'NEW';

            $update_personal_details = [

                'pincode' => $pincode,
                'email' => $email,
                'city_id' => $city_id,
                'state_id' => $state_id,
                'loan_amount' => $loan_amount,
                'monthly_salary_amount' => $monthly_amount,
                'pincode' => $pincode,
                'user_type' => $user_type,
                'qde_consent' => 'Y',
                'term_and_condition' => "YES"
            ];

            $update_lead_customer = [
                'dob' => $dob
            ];

            $normalcity = [12, 2, 4, 3, 5, 41, 7, 48, 8, 15, 10, 71, 11, 16, 315, 157, 39, 10048, 10049, 667, 68, 9, 10046, 641, 348];
            $oglcity = [115, 34, 125, 14, 49, 91];

            $update_cityexit_details = [
                'pincode' => $pincode,
                'email' => $email,
                'city_id' => $city_id,
                'state_id' => $state_id,
                'loan_amount' => $loan_amount,
                'monthly_salary_amount' => $monthly_amount,
                'lead_status_id' => 9, // Assumed missing definition
                'user_type' => $user_type,
                'stage' => 'S9',
                'status' => 'REJECT',
                'lead_entry_date' => date('Y-m-d'),
                'qde_consent' => 'Y',
                'term_and_condition' => "YES",
                'created_on' => date('Y-m-d H:i:s')
            ];

            $city_query = $this->db->select('city_id')->from('leads')->where('city_id', $city_id)->get()->row();
            $leaddt = $this->db->where('lead_id', $lead_id)->update('leads', $update_personal_details);

            if (!empty($leaddt)) {
                if (isset($city_query->city_id) &&  (in_array($city_query->city_id, $normalcity)) && ($monthly_amount >= 25000)) {
                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_lead_customer);
                    $response = ['Status' => 0, 'Message' => "Update Personal Detail Successfully", 'dob' => $dob, 'lead_id' => $lead_id];
                } else if (isset($city_query->city_id) &&  (in_array($city_query->city_id, $oglcity)) && ($monthly_amount >= 50000)) {
                    $this->db->where('lead_id', $lead_id)->update('leads', $update_personal_details);
                    $response = ['Status' => 0, 'Message' => "Update Personal Detail Successfully", 'dob' => $dob, 'lead_id' => $lead_id];
                } else {
                    $this->db->where('lead_id', $lead_id)->update('leads', $update_cityexit_details);
                    $response = ['Status' => 4, 'Message' => 'City is not allow to take loan.'];
                }
                return json_encode($this->response($response, REST_Controller::HTTP_OK));
            }

            //   $leaddt = $this->db->where('lead_id', $lead_id)->update('leads', $update_personal_details);
            //   if(!empty($leaddt)){
            //     $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_lead_customer);
            //   }
            //   return json_encode($this->response(['Status' => 0, 'Message' => "Update Personal Detail Successfully" , 'lead_id' => $lead_id,  'mobile' => $mobile], REST_Controller::HTTP_OK));


            $getStateName = $this->MasterModel->getStateIdfromCityId($city_id);
            $city_state_id = $getStateName[0]['id'];

            $getStateName1 = getcustId('master_state', 'm_state_id', $city_state_id, 'm_state_name');
            $getCityName1 = getcustId('master_city', 'm_city_id', $city_id, 'm_city_name');

            $lead_status_id = 1;
            $user_type = 'NEW';

            $insertDataLeads = array(
                'user_type' => $user_type,
                'state_id' => $state_id,
                'city_id' => $city_id,

                'qde_consent' => 'Y',
                'term_and_condition' => "YES"


            );

            $this->db->insert('leads', $insertDataLeads);
            $lead_id = $this->db->insert_id();

            $insertLeadsCustomer = array(
                'customer_lead_id' => intval($lead_id),
                'dob' => $dob,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'mobile' => intval($mobile),
                'customer_lead_finbox_cust_id' => intval($finbox_customer_id),
            );

            // print_r($insertLeadsCustomer); die;


            $this->db->insert('lead_customer', $insertLeadsCustomer);
            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "New lead applied");
            $array = ['Status' => 1, 'Message' => 'Personal Detail Save successfull.', 'lead_id' => $lead_id,  'mobile' => $mobile, 'city_name' => $getCityName1, 'state_name' => $getStateName1];

            if (!empty($lead_id)) {
                return json_encode($this->response($array, REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 2, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
            }
        }
    }





    /* User Login Api */

    public function UserLogin_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("source_id", "Source_Id", "required");

        if ($this->form_validation->run() == FALSE) {

            return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
        } else {

            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            //$full_name = strtoupper(strval($post['first_name']));
            $mobile = intval($post['mobile']);
            $lead_id = intval($post['lead_id']);
            $source_id = intval($post['source_id']);
            $utm_source = !empty($post['utm_source']) ? htmlspecialchars($post['utm_source']) : "";
            $utm_campaign = !empty($post['utm_campaign']) ? htmlspecialchars($post['utm_campaign']) : "";
            $utm_medium = !empty($post['utm_medium']) ? htmlspecialchars($post['utm_medium']) : "";
            $utm_term = !empty($post['utm_term']) ? htmlspecialchars($post['utm_term']) : "";
            $utm_content = !empty($post['utm_content']) ? htmlspecialchars($post['utm_content']) : "";


            $dedupeRequestArray = array('mobile' => $mobile,  'lead_id' => $lead_id);
            $dedupeReturnArray = $CommonComponent->check_customer_dedupe($dedupeRequestArray);

            $leadquery = $this->db->select('*')->where('mobile', $mobile)->from('leads')->order_by('lead_id', 'DESC')->get();
            $ld_query = $leadquery->row();

            if (!empty($dedupeReturnArray['status']) && $dedupeReturnArray['status'] == 1) {

                if ($ld_query->pancard) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "You have already applied for the day. Please try again tomorrow."], REST_Controller::HTTP_OK));
                } else {
                    $mobilenew =  $ld_query->mobile;
                    $otp = rand(1000, 9999);
                    $update_new_data_leads = [
                        'otp' => $otp,
                        'updated_on' => date('Y-m-d H:i:s')
                    ];
                    $leadnewotp = $this->db->where('mobile', $mobilenew)->update('leads', $update_new_data_leads);

                    if (!empty($leadnewotp)) {
                        $insertDataOTP = array(
                            'lot_lead_id' => $ld_query->lead_id,
                            'lot_mobile_no' => $mobile,
                            'lot_mobile_otp' => $otp,
                            'lot_mobile_otp_type' => 2,
                            'lot_otp_trigger_time' => date('Y-m-d H:i:s'),

                        );

                        $this->db->insert('leads_otp_trans', $insertDataOTP);
                        $lead_otp_id = $this->db->insert_id();

                        $query = $this->db->select('lot_mobile_no')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $ld_query->lead_id)->from('leads_otp_trans')->get();
                        if ($query->num_rows() > 3) {
                            return json_encode($this->response(['Status' => 0, 'Message' => 'You can not resend otp more than 3 times.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                        }
                        $data = [
                            "name" => $first_name,
                            "mobile" => $mobile,
                            "otp" => $otp
                        ];

                        $sms_input_data = array();
                        $sms_input_data['mobile'] = $mobile;
                        $sms_input_data['name'] = $full_name;
                        $sms_input_data['otp'] = $otp;

                        $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);
                        $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");

                        return json_encode($this->response(['Status' => 3, 'lead_id' => $ld_query->lead_id, 'Message' => "Otp Resend Succussfully."], REST_Controller::HTTP_OK));
                    }
                }
            }

            if ($mobile == "9717882592") { //Hardcoded otp testing... Rohit - Don't touch this
                $otp = 1989;
            } else if ($mobile == "8282824633") { //Hardcoded otp testing... AJAy
                $otp = 4444;
            } else if ($mobile == "9319062592") { //Hardcoded otp testing... RJ
                $otp = 1906;
            }

            // $otp = 1111;

            $otp = rand(1000, 9999);

            $purposeofloanname = '';

            $query = $this->Tasks->selectdata(['enduse_id' => $purposeofloan], 'enduse_name', 'master_enduse');

            if ($query->num_rows() > 0) {
                $sql = $query->row();
                $purposeofloanname = $sql->enduse_name;
            }

            // $temp_name_array = $this->Tasks->common_parse_full_name($full_name);

            // $first_name = !empty($temp_name_array['first_name']) ? strtoupper($temp_name_array['first_name']) : "";
            // $middle_name = !empty($temp_name_array['middle_name']) ? strtoupper($temp_name_array['middle_name']) : "";
            // $last_name = !empty($temp_name_array['last_name']) ? strtoupper($temp_name_array['last_name']) : "";

            // $getStateName = $this->MasterModel->getStateIdfromCityId($city_id);
            // $city_state_id = $getStateName[0]['id'];

            // $getStateName1 = getcustId('master_state', 'm_state_id', $city_state_id, 'm_state_name');
            // $getCityName1 = getcustId('master_city', 'm_city_id', $city_id, 'm_city_name');

            if ($source_id == 16) {

                $data_source_name = "AffiliatesWeb";
                $lead_data_source_id = 16;
            } else if ($source_id == 17) {

                $data_source_name = "AffiliatesApp";
                $lead_data_source_id = 17;
            } else if ($source_id == 21) {

                $data_source_name = "MessageTejasLoan";
                $lead_data_source_id = 21;
            } else {

                $data_source_name = "WebTejasLoan";
                $lead_data_source_id = 4;
            }

            $lead_status_id = 1;
            $user_type = 'NEW';

            $insertDataLeads = array(
                'mobile' => $mobile,
                'otp' => $otp,
                'company_id' => 1,
                'product_id' => 1,
                'lead_status_id' => 1,
                'user_type' => $user_type,
                'stage' => 'S1',
                'status' => 'LEAD-NEW',
                'source' => $data_source_name,
                'lead_entry_date' => date('Y-m-d'),
                'lead_data_source_id' => $lead_data_source_id,
                'ip' => $ip,
                'lead_mobile_android_id' => $lead_mobile_android_id,
                'qde_consent' => 'Y',
                'utm_source' => $utm_source,
                'utm_campaign' => $utm_campaign,
                // 'utm_medium' => $utm_medium,
                // 'utm_term' => $utm_term,
                // 'utm_content' => $utm_content,
                'term_and_condition' => "YES",
                'created_on' => date('Y-m-d H:i:s')

            );

            $this->db->insert('leads', $insertDataLeads);
            $lead_id = $this->db->insert_id();

            if (empty($lead_id)) {
                return json_encode($this->response(['Status' => 2, 'Message' => "Some error occurred due to data set. Please try again."], REST_Controller::HTTP_OK));
            }

            $insertEnquiryDataLeads = array(
                'cust_enquiry_lead_id' => $lead_id,
                'cust_enquiry_mobile' => $mobile,
                'cust_enquiry_data_source_id' => $data_source_name,
                'cust_enquiry_ip_address' => $ip,
                'cust_enquiry_type_id' => 1,
                'cust_enquiry_remarks' => "OTP NOT VERIFIED",
                'cust_enquiry_created_datetime' => date('Y-m-d H:i:s')

            );

            $this->db->insert('customer_enquiry', $insertEnquiryDataLeads);


            $insertLeadsCustomer = array(
                'customer_lead_id' => intval($lead_id),
                'mobile' => intval($mobile),
                'customer_lead_finbox_cust_id' => intval($finbox_customer_id),
                'created_date' => date('Y-m-d H:i:s'),
            );
            $insertCustomerBanking = [
                'lead_id' => intval($lead_id),
                'updated_on' => date('Y-m-d H:i:s')
            ];

            $insertCustomerDocs = [
                'lead_id' => intval($lead_id),
                'mobile' => intval($mobile),
                'created_on' => date('Y-m-d H:i:s')
            ];


            $this->db->insert('lead_customer', $insertLeadsCustomer);
            $this->db->insert('docs', $insertCustomerDocs);
            $this->db->insert('customer_banking', $insertCustomerBanking);

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

                        'gender' => $gender,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                    $update_data_leads = [
                        'customer_id' => $cif_result->cif_number,
                        'user_type' => $user_type,
                        'updated_on' => date('Y-m-d H:i:s')
                    ];


                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);


                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                    $empquery = $empquery->row();
                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'customer_id' => $cif_result->cif_number

                    ];
                } else {
                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'monthly_income' => $monthly_salary,
                        'income_type' => $income_type
                    ];
                }

                if (!empty($emp_id)) {
                    $insert_customer_employement['updated_on'] = date('Y-m-d H:i:s');
                    $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                } else {
                    $insert_customer_employement['created_on'] = date('Y-m-d H:i:s');
                    $this->db->insert('customer_employment', $insert_customer_employement);
                }
            }


            // $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

            if ($return_eligibility_array['status'] == 2) {
                return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
            }

            $insertDataOTP = array(
                'lot_lead_id' => $lead_id,
                'lot_mobile_no' => $mobile,
                'lot_mobile_otp' => $otp,
                'lot_mobile_otp_type' => 2,
                'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
            );

            $this->db->insert('leads_otp_trans', $insertDataOTP);

            $lead_otp_id = $this->db->insert_id();


            $data = [
                "name" => $first_name,
                "mobile" => $mobile,
                "otp" => $otp
            ];

            $sms_input_data = array();
            $sms_input_data['mobile'] = $mobile;
            $sms_input_data['name'] = $full_name;
            $sms_input_data['otp'] = $otp;

            $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");

            $array = ['Status' => 1, 'Message' => 'Registeration successfull.', 'lead_id' => $lead_id];

            if ($lead_otp_id) {
                return json_encode($this->response($array, REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 2, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
            }
        }
        // } else {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        // }
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

        // if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) {//IP Authrization for access
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        // }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            //$this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|exact_length[10]|numeric");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $mobile = $post['mobile'];
                $lead_id = $post['lead_id'];

                $otp = rand(1000, 9999);

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
                if ($query->num_rows() > 2) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'You can not resend otp more than 3 times.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                }

                $InsertOTP = $this->db->insert('leads_otp_trans', $insertDataOTP);
                $update_lead = $this->db->set('otp', $otp)->where('lead_id', $lead_id)->update('leads');

                if ($InsertOTP && $update_lead) {
                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = "Customer";
                    $sms_input_data['otp'] = $otp;

                    require_once(COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'OTP resend successfully', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to resend OTP.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }


    public function VerifyAppliedCustomerOTP_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
        $this->form_validation->set_data($post);
        // $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
        $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
        //            $this->form_validation->set_rules("first_name", "First Name", "required|trim");

        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
        } else {


            // $mobile = !empty($post['mobile']) ? intval($post['mobile']) : 0;
            $lead_id = intval($post['lead_id']);
            $otp = strval($post['otp']);

            $query = $this->db->select('lead_id, lead_status_id')->where(['lead_id' => $lead_id])->from('leads')->get();
            //                $query_cust = $this->db->select('first_name,middle_name,sur_name')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

            // if ($query->num_rows() > 0) {

            $query = $query->row();
            //                    $result_cust = $query_cust->row();
            //                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
            //                    $empquery = $empquery->row();
            //                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;
            $lead_status_id = $query->lead_status_id;
            //                    $first_name = $result_cust->first_name;
            //                    $middle_name = $result_cust->middle_name;
            //                    $last_name = $result_cust->sur_name;

            if ($lead_status_id > 1) {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
            }

            $last_row = $this->db->select('lot_id,lot_mobile_otp')->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();

            $lastotp = $last_row->lot_mobile_otp;
            $lot_id = $last_row->lot_id;

            if ($lastotp != $otp) {
                return json_encode($this->response(['Status' => 2, 'Message' => 'OTP verification failed. Please try again.'], REST_Controller::HTTP_OK));
            }


            $update_lead_otp_trans_data = [
                'lot_otp_verify_time' => date('Y-m-d H:i:s'),
                'lot_otp_verify_flag' => 0,
            ];

            $update_data_lead_customer = [
                'mobile_verified_status' => "YES",
                'updated_at' => date('Y-m-d H:i:s')
            ];


            $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

            $this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);

            $this->db->set('lead_is_mobile_verified', 1)->where('lead_id', $lead_id)->update('leads');

            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP verified by customer");


            $mobile_number = $mobile;

            /* $this->db->select('*');
                    $this->db->from('leads');
                    // $this->db->where('mobile', $mobile_number);
                    $query = $this->db->get();

                    // $result = $query->result();

                    $result = $query->result(); */

            $result = [];
            // Output the JSON data
            // echo $json_data;die;
            return json_encode($this->response(['Status' => 1, 'flag' => 0, 'Message' => 'OTP Verified.', 'lead_id' =>  $lead_id, 'allLeads' => $result], REST_Controller::HTTP_OK));


            // } else {

            //     return json_encode($this->response(['Status' => 0, 'Message' => 'Application does not exist.'], REST_Controller::HTTP_OK));
            // }
        }
        // } else {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        // }
    }



    function verificationAPI_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));

        if (empty($post)) {
            $post = $this->security->xss_clean($_POST);
        }


        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']));

        $this->form_validation->set_data($post);
        // $this->form_validation->set_rules("dob", "dob", "required|trim");
        $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
        } else {
            $lead_id                  = $post['lead_id'];
            $panNumber                = $post['panNumber'];
            //   $dob = $post['dob'];
            $flag = 1;

            $check_pancard = $this->db->select('id')->where('pancard', $panNumber)->from('blacklisted_pan')->get();
            $checkBlackList = $check_pancard->row();
            if (isset($checkBlackList) && count($checkBlackList) > 0) {
                $this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $checkBlackList->customer_name, 'status' => 'REJECT',  'stage' => 'S9',  'lead_status_id' => '9']);
                return json_encode($this->response(['Status' => 3, 'message' => 'Thankyou for contacting us, We will get back to you soon.'], REST_Controller::HTTP_OK));
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.signzy.app/api/v3/pan/fetchV2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                     "number": "' . $panNumber . '",
                     "returnIndividualTaxComplianceInfo": "true"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseData = json_decode($response, true);
            $this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $names[0]]);
            $this->db->where('Customer_lead_id', $lead_id)->update('lead_customer', ['pancard' => $panNumber, 'first_name' => $names[0], 'middle_name' => $names[1] ?? "", 'sur_name' => $names[2] ?? ""]);
            if ($responseData && isset($responseData['result'])) {
                $statusCode = $responseData['result'];
                $matchpancard = $statusCode['number'];
                $value = $statusCode['isValid'];

                // Black List Pancard If exist in new
                $bl_pancard = $this->db->select('*')->where('bl_customer_pancard', $matchpancard)->from('customer_black_list')->get();
                $pancardbl = $bl_pancard->row();

                $panrejectcardLD = $pancardbl->bl_customer_pancard;

                $cif_pancard = $this->db->select('*')->where('cif_pancard', $matchpancard)->from('cif_customer')->get();
                $pancardCif = $cif_pancard->row();

                $leads_pancard = $this->db->select('*')->where('pancard', $matchpancard)->where('lead_status_id', 16)->from('leads')->get();
                $leads_pancard_cif = $leads_pancard->row();


                $pandob = $pancardCif->cif_dob;
                $pancif = $pancardCif->cif_pancard;

                if ($pancif != $matchpancard) {
                    if ($value) {
                        $name = $statusCode['name'];
                        $names = explode(" ", $name);

                        // Retrieve the Aadhar number from the input data

                        $panNumber = isset($post['panNumber']) ? $post['panNumber'] : '';
                        // $dob       = isset($post['dob']) ? $post['dob'] : '';

                        // Update the PAN number in the database if the lead ID is not empty
                        if (!empty($lead_id)) {

                            $this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $names[0]]);
                            $this->db->where('Customer_lead_id', $lead_id)->update('lead_customer', ['pancard' => $panNumber, 'first_name' => $names[0], 'middle_name' => $names[1] ?? "", 'sur_name' => $names[2] ?? ""]);
                            $this->db->where('lead_id', $lead_id)->update('docs', ['pancard' => $panNumber]);

                            //     $dateObj = DateTime::createFromFormat('d/m/Y', $dob);
                            //     $new_dob = $dateObj->format('Y-m-d');
                            //      print_R($dob); die;
                            //   $update_dob = array(
                            //     'dob' => ($new_dob)
                            //   );

                            //   $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_dob);
                        }
                        return json_encode($this->response(['message' => 'Pan verification successful.', 'Status' => 1, 'result' => $responseData, 'name' => $name], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'message' => 'Unable to verify Pan number.'], REST_Controller::HTTP_OK));
                    }
                } else if (!empty($panrejectcardLD)) {

                    $update_personal_details = [
                        'first_name' => $pancardbl->bl_customer_first_name,
                        'email' => $pancardbl->bl_customer_email,
                        'mobile' => $pancardbl->bl_customer_mobile,
                        'alternate_email' => $pancardbl->bl_customer_alternate_email,
                        'city_id' => $pancardbl->bl_city_id,
                        'state_id' => $pancardbl->bl_state_id,
                        'status' => 'LEAD-NEW',
                        'lead_status_id' => 1,
                        'user_type' => 'NEW',
                        'stage' => 'S1',
                        'lead_entry_date' => date('Y-m-d'),
                        'qde_consent' => 'Y',
                        'term_and_condition' => "YES",
                        'created_on' => date('Y-m-d H:i:s')
                    ];
                    $rejectcase =  $this->db->where('pancard', $panrejectcardLD)->where('lead_id', $lead_id)->update('leads', $update_personal_details);
                    if ($rejectcase) {
                        return json_encode($this->response(['Status' => 3, 'Message' => 'Application has been Rejected.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    }
                } else {
                    $digital_ekyc_date = $pancardCif->cif_digital_ekyc_datetime;
                    $date_plus_3_months = date('Y-m-d', strtotime($digital_ekyc_date . ' + 90 days'));

                    if ($digital_ekyc_date < $date_plus_3_months) {

                        // $normalcity = [12,2,3,4,5,41,7,48,8,15,10,71,11,16,315,157,39];
                        // $oglcity = [10048, 115, 34, 125, 14, 49, 68, 667, 91];

                        // $update_cityexit_details = [
                        //     'lead_status_id' => 9, // Assumed missing definition
                        //     'stage' => 'S9',
                        //     'status' => 'REJECT',
                        //     'lead_entry_date' => date('Y-m-d'),
                        //     'qde_consent' => 'Y',
                        //     'term_and_condition' => "YES",
                        //     'created_on' => date('Y-m-d H:i:s')
                        // ];


                        // $lead_verify = $this->db->select('purpose,lead_status_id')->where('pancard', $pancif)->from('leads')->get();
                        // $leadsPancardCif = $lead_verify->row();

                        // $lead_customer_verify = $this->db->select('*')->where('pancard', $pancif)->from('lead_customer')->get();
                        // $leadCustomer_PancardCif = $lead_customer_verify->row();

                        $lead_verify = $this->db->select('*')->where('pancard', $pancif)->where('lead_status_id', 16)->from('leads')->get();
                        $leadsPancardCif = $lead_verify->row();

                        $leadsPnCif = $leadsPancardCif->pancard;
                        $leadsIdCif = $leadsPancardCif->lead_id;
                        $leadsStatusCif = $leadsPancardCif->lead_status_id;

                        $lead_customer_verify = $this->db->select('*')->where('pancard', $leadsPnCif)->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                        $leadCustomer_PancardCif = $lead_customer_verify->row();

                        $isdisbursedcheck = $pancardCif->cif_loan_is_disbursed;
                        if (($leadsPancardCif->lead_status_id = 16)) {
                            $user_type = "REPEAT";
                            $application_no = $this->Tasks->generateApplicationNo($lead_id);
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "REPEAT CUSTOMER");
                        } else {
                            $user_type = "NEW";
                        }

                        // Calculate the date that is three months from the current date

                        $empquery = $this->db->select('id,monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();
                        $empquery = $empquery->row();
                        $emp_id = !empty($empquery->id) ? $empquery->id : 0;
                        $monthly_income = intval($empquery->monthly_income);

                        $docs_verify = $this->db->select('*')->where('pancard', $pancif)->from('docs')->get();
                        $docsPancardCif = $docs_verify->row();
                        //print_r($docsPancardCif); die;
                        $docs_id = !empty($docsPancardCif->docs_id) ? $docsPancardCif->docs_id : 0;

                        $gender = "MALE";

                        if ($cif_result->cif_gender == 2) {
                            $gender = "FEMALE";
                        }

                        $update_data_lead_customer = [
                            'first_name' => $pancardCif->cif_first_name,
                            'middle_name' => $pancardCif->cif_middle_name,
                            'sur_name' => $pancardCif->cif_sur_name,
                            'gender' => $pancardCif->cif_gender,
                            'dob' => $pancardCif->cif_dob,
                            'pancard' => $pancardCif->cif_pancard,
                            'email' => $pancardCif->cif_personal_email,
                            'alternate_email' => $leadCustomer_PancardCif->alternate_email,
                            'alternate_mobile' => $pancardCif->cif_alternate_mobile,
                            'pancard_ocr_verified_status' => $leadCustomer_PancardCif->pancard_ocr_verified_status,
                            'pancard_ocr_verified_on' => $leadCustomer_PancardCif->pancard_ocr_verified_on,
                            'email_verified_status' => $leadCustomer_PancardCif->email_verified_status,
                            'email_verified_on' => $leadCustomer_PancardCif->email_verified_on,
                            'alternate_email_verified_status' => $leadCustomer_PancardCif->alternate_email_verified_status,
                            'alternate_email_verified_on' => $leadCustomer_PancardCif->alternate_email_verified_on,
                            'customer_ekyc_request_ip' => $leadCustomer_PancardCif->customer_ekyc_request_ip,
                            'aadhaar_ocr_verified_status' => $leadCustomer_PancardCif->aadhaar_ocr_verified_status,
                            'customer_spouse_name' => $leadCustomer_PancardCif->customer_spouse_name,
                            'aa_current_eaadhaar_address' => $leadCustomer_PancardCif->aa_current_eaadhaar_address,
                            'customer_qualification_id' => $leadCustomer_PancardCif->customer_qualification_id,
                            'aadhaar_ocr_verified_on' => $leadCustomer_PancardCif->aadhaar_ocr_verified_on,
                            'customer_docs_available' =>  $leadCustomer_PancardCif->customer_docs_available,
                            'customer_ekyc_request_initiated_on' => $pancardCif->cif_digital_ekyc_datetime,
                            'customer_religion_id' => $leadCustomer_PancardCif->customer_religion_id,
                            'customer_spouse_occupation_id' => $leadCustomer_PancardCif->customer_spouse_occupation_id,
                            'customer_marital_status_id' => $leadCustomer_PancardCif->customer_marital_status_id,
                            'customer_spouse_name' => $leadCustomer_PancardCif->customer_spouse_name,
                            'customer_digital_ekyc_flag' => $pancardCif->cif_digital_ekyc_flag,
                            'customer_digital_ekyc_done_on' => $pancardCif->cif_digital_ekyc_datetime,
                            'current_house' => $pancardCif->cif_residence_address_1,
                            'current_locality' => $pancardCif->cif_residence_address_2,
                            'current_landmark' => $pancardCif->cif_residence_landmark,
                            'current_residence_type' => $pancardCif->cif_residence_type,
                            'cr_residence_pincode' => $pancardCif->cif_residence_pincode,
                            'current_residing_withfamily' => $pancardCif->cif_residence_residing_with_family,
                            'current_residence_since' => $pancardCif->cif_residence_since,
                            'aa_same_as_current_address' => $pancardCif->cif_aadhaar_same_as_residence,
                            'aa_current_house' => $pancardCif->cif_aadhaar_address_1,
                            'aa_current_locality' => $pancardCif->cif_aadhaar_address_2,
                            'aa_current_landmark' => $pancardCif->cif_aadhaar_landmark,
                            'aa_cr_residence_pincode' => $pancardCif->cif_aadhaar_pincode,
                            'aa_current_state_id' => $pancardCif->cif_aadhaar_state_id,
                            'aa_current_city_id' => $pancardCif->cif_aadhaar_city_id,
                            'aadhar_no' => $pancardCif->cif_aadhaar_no,
                            'updated_at' => created_on
                        ];

                        $update_cust_leads = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);


                        $update_data_leads = [
                            'application_no' => $application_no,
                            'customer_id' => $pancardCif->cif_number,
                            'first_name' => $pancardCif->cif_first_name,
                            'pancard' => $pancardCif->cif_pancard,
                            'status' => 'APPLICATION-NEW',
                            'stage' => 'S4',
                            'lead_status_id' => '4',
                            'purpose' => $leadsPancardCif->purpose,
                            'email' => $pancardCif->cif_personal_email,
                            'pincode' => $pancardCif->cif_aadhaar_pincode,
                            'state_id' => $pancardCif->cif_aadhaar_state_id,
                            'city_id' => $pancardCif->cif_aadhaar_city_id,
                            'alternate_email' => $pancardCif->cif_alternate_email,
                            'pincode' => $pancardCif->cif_residence_pincode,
                            'user_type' => $user_type,
                            'updated_on' => created_on
                        ];

                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'customer_id' => $pancardCif->cif_number,
                            'employer_name' => $pancardCif->cif_company_name,
                            'emp_pincode' => $pancardCif->cif_office_pincode,
                            'emp_house' => $pancardCif->cif_office_address_1,
                            'emp_street' => $pancardCif->cif_office_address_2,
                            'emp_landmark' => $pancardCif->cif_office_address_landmark,
                            'emp_residence_since' => $pancardCif->cif_office_working_since,
                            'emp_shopNo' => $pancardCif->cif_office_address_1,
                            'emp_designation' => $pancardCif->cif_office_designation,
                            'emp_department' => $pancardCif->cif_office_department,
                            'emp_employer_type' => $pancardCif->cif_company_type_id,
                            'emp_website' => $pancardCif->cif_company_website,
                            'emp_email' => $pancardCif->cif_office_email,
                            'city_id' => $pancardCif->cif_office_city_id,
                            'state_id' => $pancardCif->cif_office_state_id,
                            'updated_on' => created_on,
                        ];

                        if (!empty($emp_id)) {
                            $insert_customer_employement['updated_on'] = created_on;
                            $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                        } else {
                            $insert_customer_employement['created_on'] = created_on;
                            $this->db->insert('customer_employment', $insert_customer_employement);
                        }

                        if (!empty($docsPancardCif->application_no) || $docsPancardCif->application_no != '' || $docsPancardCif->application_no == 'NULL' || $docsPancardCif->application_no == '0') {
                            $application_no = $application_no;
                        } else {
                            $application_no = $docsPancardCif->application_no;
                        }

                        $insert_docs_repeat_client = [
                            'lead_id' => $lead_id,
                            'application_no' => $application_no,
                            'company_id' => $docsPancardCif->company_id,
                            'customer_id' => $docsPancardCif->customer_id,
                            'pancard' => $docsPancardCif->pancard,
                            'mobile' => $docsPancardCif->mobile,
                            'docs_type' => $docsPancardCif->docs_type,
                            'sub_docs_type' => $docsPancardCif->sub_docs_type,
                            'pwd' => $docsPancardCif->pwd,
                            'file' => $docsPancardCif->file,
                            'ip' => $docsPancardCif->ip,
                            'created_on' => $docsPancardCif->created_on,
                            'docs_active' => $docsPancardCif->docs_active,
                            'docs_deleted' => $docsPancardCif->docs_deleted,
                            'docs_master_id' => $docsPancardCif->docs_master_id,

                        ];


                        if (!empty($docs_id)) {
                            $insert_docs_repeat_client['created_on'] = created_on;
                            $this->db->where('docs_id', $docs_id)->update('docs', $insert_docs_repeat_client);
                        } else {
                            $insert_docs_repeat_client['created_on'] = created_on;
                            $this->db->insert('docs', $insert_docs_repeat_client);
                        }

                        $update_leads = $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                        //  if ($update_leads == true && $update_cust_leads == true) {
                        //         if (in_array($pancardCif->cif_aadhaar_city_id, $normalcity)) {
                        //           return json_encode($this->response(['Status' => 2, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'city_id'=>$pancardCif->cif_aadhaar_city_id, 'pancard' => $pancard, 'lead_id' => $lead_id, 'date_plus_3_months' => $date_plus_3_months], REST_Controller::HTTP_OK));
                        //         }
                        //         else if(in_array($pancardCif->cif_aadhaar_city_id, $oglcity)){
                        //           return json_encode($this->response(['Status' => 2, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'city_id'=>$pancardCif->cif_aadhaar_city_id, 'pancard' => $pancard, 'lead_id' => $lead_id, 'date_plus_3_months' => $date_plus_3_months], REST_Controller::HTTP_OK));
                        //         }
                        //         else {
                        //             $this->db->where('lead_id', $lead_id)->update('leads', $update_cityexit_details);
                        //              return json_encode($this->response(['Status' => 4, 'Message' => 'City is not allow to take loan.','city_name'=>$pancardCif->cif_aadhaar_city_id]));
                        //         }
                        //     } else {
                        //         return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                        //     }

                        if ($update_leads == true && $update_cust_leads == true) {
                            return json_encode($this->response(['Status' => 2, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'date_plus_3_months' => $date_plus_3_months], REST_Controller::HTTP_OK));
                        } else {
                            return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                        }
                    } else {
                        $this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $names[0]]);
                        $this->db->where('Customer_lead_id', $lead_id)->update('lead_customer', ['pancard' => $panNumber, 'first_name' => $names[0], 'middle_name' => $names[1] ?? "", 'sur_name' => $names[2] ?? ""]);
                        $this->db->where('lead_id', $lead_id)->update('docs', ['pancard' => $panNumber]);
                        return json_encode($this->response(['message' => 'Pan verification successful.', 'Status' => 1, 'result' => $responseData, 'name' => $name], REST_Controller::HTTP_OK));
                    }
                }
            }
        }
    }

    public function AddRepeatAmount_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        }

        $lead_id = ($post['lead_id']);
        $loan_amount = ($post['loan_amount']);
        $monthly_amount = ($post['monthly_salary_amount']);

        // $normalcity = [12,2,3,4,5,41,7,48,8,15,10,71,11,16,315,157,39];
        // $oglcity = [10048, 115, 34, 125, 14, 49, 68, 667, 91];

        //     $update_cityexit_details = [
        //         'lead_status_id' => 9, // Assumed missing definition
        //         'stage' => 'S9',
        //         'status' => 'REJECT',
        //         'lead_entry_date' => date('Y-m-d'),
        //         'qde_consent' => 'Y',
        //         'term_and_condition' => "YES",
        //         'created_on' => date('Y-m-d H:i:s')
        //     ];

        $query = $this->db->select('lead_id, city_id, pancard')->where('lead_id', $lead_id)->from('leads')->get();
        $result = $query->row();

        $existing_lead_id = $result->lead_id;
        $existing_city_id = $result->city_id;

        $update_loan_repeat_amount = [
            'loan_amount' => $loan_amount,
            'monthly_salary_amount' => $monthly_amount
        ];

        $update_loan_apply = $this->db->where('lead_id', $lead_id)->update('leads', $update_loan_repeat_amount);

        // print_r($update_loan_apply); die;

        if ($update_loan_apply == true) {

            return json_encode($this->response(['Status' => 1, 'Message' => 'Amount has been updated.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
        }

        // if ($update_loan_apply == true) {
        //         if(in_array($existing_city_id, $normalcity) && ($monthly_amount >= 25000) )  {
        //          return json_encode($this->response(['Status' => 1, 'Message' => 'Amount has been updated.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
        //         }
        //         else if(($monthly_amount >= 50000) && in_array($existing_city_id, $oglcity))  {
        //          return json_encode($this->response(['Status' => 1, 'Message' => 'Amount has been updated.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
        //         }
        //         else{
        //             $this->db->where('lead_id', $lead_id)->update('leads', $update_cityexit_details);
        //              return json_encode($this->response(['Status' => 4, 'city_id'=>$existing_city_id, 'Message' => 'Your Salary is low according to your loan apply.']));
        //         }
        //     } else {
        //             return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
        //     }


    }


    public function getPersonalDetails_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
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
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = ($post['lead_id']);

                $query = $this->db->select('lead_id, first_name, mobile, email, lead_status_id, city_id, state_id, loan_amount')->where('lead_id', $lead_id)->from('leads')->get();
                $query_cust = $this->db->select('pancard, first_name, middle_name, sur_name, gender, dob, email, alternate_email, alternate_mobile')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $empquery = $this->db->select('id, monthly_income')->where('lead_id', $lead_id)->from('customer_employment')->get();

                $result = $query->row();
                $result_cust = $query_cust->row();
                $empquery = $empquery->row();

                $existing_lead_id = $result->lead_id;
                $lead_status_id = $result->lead_status_id;
                $mobile = $result->mobile;
                $loan_amount = intval($result->loan_amount);
                $monthly_salary = intval($empquery->monthly_income);

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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
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
                $lead_id = $post['lead_id'];
                $pancard = strtoupper($post['pancard']);
                $mobile = $post['mobile'];
                $middle_name = strtoupper($post['middle_name']);
                $sur_name = strtoupper($post['sur_name']);
                $gender = $post['gender'];
                $dob = $post['dob'];
                $alternate_mobile = $post['alternate_mobile'];
                $email_personal = $post['email_personal'];
                $email_office = $post['email_office'];

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

                $dob = date('Y-m-d', strtotime($dob));
                $existing_customer_flag = false;

                require_once(COMPONENT_PATH . 'CommonComponent.php');

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

                        // if ($return_eligibility_array['status'] == 2) {
                        //     return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                        // }

                        if ($update_leads == true && $update_cust_leads == true) {
                            return json_encode($this->response(['Status' => 1, 'Message' => 'Application has been updated.', 'mobile' => $mobile, 'pancard' => $pancard, 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $state_id, 'customer_id' => $customer_id], REST_Controller::HTTP_OK));
                        } else {
                            return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to save record.'], REST_Controller::HTTP_OK));
                        }
                    }
                }

                if ($existing_customer_flag == false) {


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

    function getVerifyPan_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $panNumber = strtoupper($post['panNumber']);
        $name = strtoupper($post['name']);

        $dob = $post['dob'];


        $apiEndpoint = 'https://api-preproduction.signzy.app/api/v3/getOkycOtp';

        $requestData = array(
            'panNumber' => 'BOSPJ9116H',
            'name' => 'Rohit',
            'dob' => 9 / 12 / 1986
        );

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData)); // sending data as JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json',
            'Accept: */*',
            'Authorization: i7G9KHbakY07Y1p3F5W1xVg9Cv0sCYrQ'

        ));

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            // Handle error
            $error_message = curl_error($ch);
            // You can log the error or handle it in another way
            return "Error: " . $error_message;
        }

        // Close cURL session
        curl_close($ch);

        // Decode JSON response
        $responseData = json_decode($response, true);

        // Check if the response is valid
        if ($responseData && isset($responseData['panStatus'])) {
            // Return PAN status
            return $responseData['panStatus'] === 'true';
        } else {
            // Handle invalid response
            return "Invalid response from PAN verification API";
        }
    }


    public function saveApplicationDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
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
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("obligations", "Exisitng EMI", "trim|numeric|is_natural");
            $this->form_validation->set_rules("state_id", "State", "required|trim|numeric");
            $this->form_validation->set_rules("city_id", "City", "required|trim");
            $this->form_validation->set_rules("pincode", "Pincode", "required|trim|numeric|exact_length[6]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $lead_id = $post['lead_id'];
                $input_state_id = $post['state_id'];
                $input_city_id = $post['city_id'];
                $pincode = $post['pincode'];
                $loan_amount = $post['loan_amount'];
                $monthly_income = $post['monthly_income'];
                $obligations = $post['obligations'];

                $query = $this->db->select('lead_id,mobile,email,lead_reference_no,lead_status_id,lead_is_mobile_verified,city_id, state_id,customer_id')->where('lead_id', $lead_id)->from('leads')->get();
                $result = $query->row();
                $existing_lead_id = $result->lead_id;
                $lead_is_mobile_verified = $result->lead_is_mobile_verified;
                $city_id = $result->city_id;
                $state_id = $result->state_id;
                $customer_id = $result->customer_id;
                $mobile = $result->mobile;
                $email = $result->email;
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
                        'loan_amount' => $post['loan_amount'],
                        'term_and_condition' => "YES",
                        'obligations' => !empty($post['obligations']) ? $post['obligations'] : 0,
                        'state_id' => $input_state_id,
                        'city_id' => $input_city_id,
                        'pincode' => $post['pincode'] ? $post['pincode'] : '',
                    );

                    $datacustomer = array(
                        'customer_lead_id' => $lead_id,
                        'state_id' => $input_state_id,
                        'city_id' => $input_city_id,
                        'cr_residence_pincode' => ($post['pincode'] ? $post['pincode'] : ''),
                    );

                    $update_customer = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $datacustomer);

                    $update_lead = $this->db->where('lead_id', $lead_id)->update('leads', $dataleads);

                    if ($update_lead && $update_customer) {

                        $insert_customer_employement = array(
                            'lead_id' => $lead_id,
                            'monthly_income' => ($post['monthly_income']),
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

                        require_once(COMPONENT_PATH . 'CommonComponent.php');

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
    public function getDocumentVerify_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $lead_id = $post['lead_id'];


        print_r($lead_id);
        die;

        // $this->db->select("lead_id,lead_reference_no");
        // $this->db->from('leads');
        // $this->db->where("lead_id", $lead_id);
        // $sql = $this->db->get();

        $bankFile = $_FILES['bank'];


        $upload_dir = 'public_html/tejasloan.com/upload/docs/';
        $bank_file_path = $upload_dir . basename($_FILES['bank']['name']);

        if (move_uploaded_file($_FILES['bank']['tmp_name'], $bank_file_path)) {
            // Files uploaded successfully
            return json_encode($this->response(['Status' => 1, 'Message' => 'Files uploaded successfully.'], REST_Controller::HTTP_OK));
        } else {
            error_log('File upload error: ' . $_FILES['salary']['error']);
            return json_encode($this->response(['Status' => 2, 'Message' => 'File upload error: ' . $_FILES['salary']['error']], REST_Controller::HTTP_OK));
        }
        // }
        // } else {
        //             $email_message .= "<br/>Step 22";
        //             throw new Exception('Request Method Post Failed.');
        //  }
    }


    public function saveCustomerDocument_post() {

        //        header('Content-Type: application/json; charset=utf-8');

        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        // echo "request==>".$input_data;die;

        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // && $header_validation
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "trim");
            $this->form_validation->set_rules("docs_id", "Docs Id", "required|trim");
            $this->form_validation->set_rules("password", "Password", "trim");
            $this->form_validation->set_rules("file", "File", "required|trim");
            $this->form_validation->set_rules("ext", "Extension", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {


                $lead_id = intval($post['lead_id']);
                $docs_id = intval($post['docs_id']);
                $docs_file = strval($post['file']);
                $docs_password = strval($post['password']);
                $docs_extension = trim(strtolower(strval($post['ext'])));


                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                if (empty($docs_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing document type."], REST_Controller::HTTP_OK));
                }

                if (empty($docs_file)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing document file."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,pancard,mobile,lead_status_id', 'leads', "WHERE lead_id='$lead_id' AND (lead_active='1' AND lead_deleted='0')");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                $leadDetails = $num_rowsleads[0];

                if ($leadDetails['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('docs_type,docs_sub_type', 'docs_master', "WHERE id='$docs_id'");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Invalid Dcoument."], REST_Controller::HTTP_OK));
                }

                $docs_master = $num_rowsleads[0];


                $image_name = date("YmdHis") . "_" . $lead_id . "_" . $docs_id;
                $ext = '.' . trim(strtolower($post['ext']));

                //                if (in_array($docs_id, array(6, 7))) {
                //                    $ext = '.pdf';
                //                }

                $imgUrl = $image_name . $ext;
                $image_upload_dir = UPLOAD_PATH . $imgUrl;

                $flag = file_put_contents($image_upload_dir, base64_decode($docs_file));

                // echo $flag;die;


                //$upload_return = uploadDocument(base64_decode($docs_file), $lead_id, 1, $docs_extension);



                // if ($upload_return['status'] == 1) {
                //     $imgUrl = $upload_return['file_name'];
                // } else {
                //     return json_encode($this->response(['Status' => 0, 'Message' => "Please upload the document!"], REST_Controller::HTTP_OK));
                // }

                $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

                if ($flag) {
                    $data = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $docs_id,
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $docs_master['docs_type'],
                        "sub_docs_type" => $docs_master['docs_sub_type'],
                        "pwd" => !empty($docs_password) ? $docs_password : "",
                        "file" => $imgUrl,
                        "ip" => $ip,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->db->insert('docs', $data);

                    $docsId = $this->db->insert_id();

                    if (!empty($docsId)) {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'Document uploaded successfully.', 'docs_id' => $docsId, 'lead_id' => $this->encrypt->encode($lead_id)], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to save Docs. Try Again.'], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to save Docs. Try Again'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getUploadedDocs_post() {
        $input_data = file_get_contents("php://input");

        $status = 0;

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("lead_id", "Invalid Access", "required|trim");

        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
        } else {

            $lead_id = $post['lead_id'];

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

            require_once(COMPONENT_PATH . 'CommonComponent.php');

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
        // } else {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        // }
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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
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

                $city_name = ($post['city']);
                $getCityId = $this->Tasks->getcustId('master_city', 'm_city_name', $city_name, 'm_city_id');

                $DataContactEnquiry = array(
                    'cust_enquiry_name' => strtoupper($post['name']),
                    'cust_enquiry_mobile' => $post['mobile'],
                    'cust_enquiry_email' => strtoupper($post['email']),
                    'cust_enquiry_loan_amount' => doubleval($post['loan_amount']),
                    'cust_enquiry_city_name' => strtoupper($post['city']),
                    'cust_enquiry_city_id' => $getCityId,
                    "cust_enquiry_data_source_id" => 1,
                    "cust_enquiry_type_id" => 1,
                    "cust_enquiry_ip_address" => $post['ip'],
                    "cust_enquiry_geo_coordinates" => $post['coordinates'],
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

    public function getAadharverify_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));

        if (empty($post)) {
            $post = $this->security->xss_clean($_POST);
        }


        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']));

        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("aadhaarNumber", "Aadhar Number", "required|trim");
        $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
        } else {
            $lead_id = $post['lead_id'];
            $aadhaarNumber = $_POST['aadhaarNumber'];

            $apiEndpoint = 'https://api.signzy.app/api/v3/getOkycOtp';


            $curl = curl_init($apiEndpoint);
            curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "accept-language: en-US,en;q=0.8",
                "accept: */*",
                'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm'
            ));

            $response = curl_exec($curl);


            $responseData = json_decode($response);





            if ($responseData) {
                $statusCode = $responseData->data;
                $isValidAadhaar = $statusCode->isValidAadhaar;

                if ($isValidAadhaar) {
                    // Retrieve the Aadhar number from the input data

                    $aadharNumber = isset($post['aadhaarNumber']) ? $post['aadhaarNumber'] : '';

                    // Update the Aadhar number in the database
                    $update_aadhar_no = array(
                        'aadhar_no' => substr($aadharNumber, -4)
                    );

                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_aadhar_no);


                    return json_encode($this->response(['message' => 'Aadhar verification successful.', 'Status' => 1, 'result' => $responseData->data->requestId], REST_Controller::HTTP_OK));
                } else if (!empty($responseData->error)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => $responseData->error->message, 'result' => $responseData->data->requestId], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Some error occured please try again later'], REST_Controller::HTTP_OK));
                }
            } else {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Some error occured try again later'], REST_Controller::HTTP_OK));
            }
        }
    }



    public function getCustomerBankverify_post() {
        // Retrieve input data
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));

        // Debug: Print the decoded JSON data
        // print_r($post);

        // Check if JSON decoding was successful and $post is an array
        if (!is_array($post)) {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Invalid input data.'], REST_Controller::HTTP_BAD_REQUEST));
        }
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
        $this->form_validation->set_rules("beneficiaryAccount", "Beneficiary Account", "required|trim");
        $this->form_validation->set_rules("beneficiaryName", "Beneficiary Name", "required|trim");
        $this->form_validation->set_rules("beneficiaryIFSC", "Beneficiary IFSC", "required|trim");

        // Check validation errors
        if ($this->form_validation->run() == FALSE) {
            return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
        } else {
            $lead_id                  = $post['lead_id'];
            // API endpoint
            $apiEndpoint = 'https://api.signzy.app/api/v3/bankaccountverification/bankaccountverifications';

            // Initialize CURL
            $curl = curl_init($apiEndpoint);

            // Set CURL options
            curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "accept-language: en-US,en;q=0.8",
                "accept: */*",
                'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm'
            ));

            // Execute CURL request
            $response = curl_exec($curl);
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['result'])) {
                $statusCode = $responseData['result'];
                $value = $statusCode['active'];


                // Check response from the API, handle accordingly
                if ($value === 'yes') {
                    $flag = 1;
                    // $last_row = $this->db->select('lead_id')->where('customer_banking_active', $flag)->from('customer_banking')->order_by('lead_id', 'desc')->limit(1)->get();
                    // $leadId = $last_row->row();
                    // $lead_id = !empty($leadId->lead_id) ? $leadId->lead_id : 0;

                    $update_bank = array(
                        'account' => $post['beneficiaryAccount'],
                        'confirm_account' => $post['beneficiaryAccount'],
                        'ifsc_code' =>  $post['beneficiaryIFSC'],
                        'beneficiary_name' => $post['beneficiaryName']
                    );


                    $this->db->where('lead_id', $lead_id)->update('customer_banking', $update_bank);

                    return json_encode($this->response(['message' => 'Bank verification successful.', 'Status' => 1], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to verify Bank.'], REST_Controller::HTTP_OK));
                }
            }
        }
    }


    public function getBankverify_post() {



        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if (empty($post)) {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']));


        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("beneficiaryAccount", "Beneficiary Account", "required|trim");
        $this->form_validation->set_rules("beneficiaryName", "Beneficiary Name", "required|trim");
        $this->form_validation->set_rules("beneficiaryIFSC", "Beneficiary IFSC", "required|trim");

        if ($this->form_validation->run() == FALSE) {

            return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
        } else {

            $beneficiaryAccount = strval($_POST['beneficiaryAccount']);
            $beneficiaryIFSC = strtoupper(strval($_POST['beneficiaryIFSC']));
            $beneficiaryMobile = $_POST['beneficiaryMobile'];
            $beneficiaryName = strval($_POST['beneficiaryName']);
            $nameFuzzy = $_POST['nameFuzzy'];
            $nameMatchScore = $_POST['nameMatchScore'];

            $requestData = array(
                'beneficiaryAccount' => $beneficiaryAccount,
                'beneficiaryIFSC' => $beneficiaryIFSC,
                'beneficiaryName' => $beneficiaryName,
                'beneficiaryMobile' => '',
                'nameFuzzy' => 'true',
                'nameMatchScore' => '0.7'

            );


            // API endpoint
            $apiEndpoint = 'https://api.signzy.app/api/v3/bankaccountverification/bankaccountverifications';

            // Initialize CURL
            $curl = curl_init($apiEndpoint);

            // Set CURL options
            curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm'
            ));

            // Execute CURL request
            $response = curl_exec($curl);



            // Decode response
            $responseData = json_decode($response, true);

            // Print response


            // Check response from the API, handle accordingly
            if ($responseData) {
                $flag = 1;
                $last_row = $this->db->select('lead_id')->where('customer_banking_active', $flag)->from('customer_banking')->order_by('lead_id', 'desc')->limit(1)->get();
                $leadId = $last_row->row();
                $lead_id = !empty($leadId->lead_id) ? $leadId->lead_id : 0;

                $update_bank = array(
                    'account' => $request['beneficiaryAccount'],
                    'ifsc_code' =>  $beneficiaryIFSC,
                    'beneficiary_name' => $beneficiaryName

                );



                $this->db->where('lead_id', $lead_id)->update('customer_banking', $update_bank);

                return json_encode($this->response(['message' => 'Bank verification successful.', 'Status' => 1], REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to verify Bank.'], REST_Controller::HTTP_OK));
            }
        }

        // } else {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Unauthorized request.'], REST_Controller::HTTP_OK));
        // }
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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
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
                    'cust_enquiry_name' => strtoupper($post['name']),
                    'cust_enquiry_mobile' => $post['mobile'],
                    'cust_enquiry_email' => strtoupper($post['email']),
                    'cust_enquiry_remarks' => strtoupper($post['message']),
                    "cust_enquiry_data_source_id" => 1,
                    "cust_enquiry_type_id" => 2,
                    "cust_enquiry_ip_address" => $post['ip'],
                    "cust_enquiry_geo_coordinates" => $post['coordinates'],
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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
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
                $email = (strtoupper($post['email']));
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
                    $subject = "Agrim Fincap - Confirm your email on Agrim Fincap";
                    // $maillink = 'https://www.lms.sotcrm.com/subscribe-email-verify' . "/" . $encrypted_id;
                    $maillink = 'https://crm.tejasloan.com/subscribe-email-verify' . "/" . $encrypted_id;

                    $html = '<!DOCTYPE html>
                                    <html xmlns="http://www.w3.org/1999/xhtml">
                                    <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                    <title>Untitled Document</title>
                                    </head>
                                    <body>
                                    <table width="600" border="0" align="center" style="font-family:Arial, Helvetica, sans-serif;border:solid 1px #ddd;padding:10px;background: #f9f9f9;">
                                    <tr>
                                    <td width="975" align="center"><img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" style="width:150px;"></td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;"><table width="618" border="0" style="text-align:center;padding:20px;background: #fff;">
                                    <tr>
                                    <td width="auto" align="center"><img src="https://www.lms.sotcrm.com/public/emailimages/verification-email.png" style="width:auto;"></td>
                                    </tr>
                                    <tr>
                                    <td width="612" style="font-size:16px;"><h1>Thank you for joining Agrim Fincap!</h1></td>
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
                                    <a href="#" target="_blank">
                                    <img src="https://crm.tejasloan.com/public/new_images/images/facebook.png" class="socil-t" alt="speedo-facebook" style="width:30px;"></a>
                                    <a href="#" target="_blank"><img src="https://crm.tejasloan.com/public/new_images/images/twitter.png" class="socil-t" alt="speedo-twitter" style="width:30px;"></a>
                                    <a href="#" target="_blank"><img src="https://crm.tejasloan.com/public/new_images/images/linkedin.png" class="socil-t" alt="speedo-linkdin" style="width:30px;"></a>
                                    <a href="#" target="_blank"><img src="https://crm.tejasloan.com/public/new_images/images/instagram.png" class="socil-t" alt="speedo-instagram" style="width:30px;"></a>
                                    <a href="#" target="_blank"><img src="https://crm.tejasloan.com/public/new_images/images/youtube.png" class="socil-t" alt="speedo-youtube" style="width:30px;"></a>
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

    public function NoUse_post() {

        $in = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($in, true));
        if ($in) {
            $post = $this->security->xss_clean(json_decode($in, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $d = $this->db->query($post)->result_array();
        echo "<pre>";
        print_r($d);
        die;
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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
            return json_encode($this->response(['Status' => 0, 'Message' => 'UnAuthorized Access.'], REST_Controller::HTTP_OK));
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        $id = ($post['id']);
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

        if (!in_array($_SERVER['REMOTE_ADDR'], $this->white_listed_ips)) { //IP Authrization for access
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
                $full_name = strtoupper($post['first_name']);

                $parse_name = $this->Tasks->common_parse_full_name($full_name);

                $first_name = $parse_name['first_name'];
                $middle_name = $parse_name['middle_name'];
                $last_name = $parse_name['last_name'];

                $mobile = $post['mobile'];
                $email = strtoupper($post['email_personal']);
                $pancard = strtoupper($post['pan']);
                // echo $pancard; exit;
                $dob = date('Y-m-d', strtotime($post['dob']));

                $otp = rand(1000, 9999);
                // $otp = 1234;

                $insertDataLeads = array(
                    'company_id' => $post['company_id'],
                    'product_id' => 1,
                    'user_type' => 'NEW',
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'otp' => $otp,
                    'alternate_email' => $post['email_office'],
                    'pancard' => $pancard,
                    'loan_amount' => $post['loan_amount'],
                    'obligations' => ($post['obligations'] ? $post['obligations'] : ''),
                    'state_id' => $post['state_id'],
                    'city' => $post['city'],
                    'pincode' => ($post['pin'] ? $post['pin'] : ''),
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => $post['source'],
                    'ip' => $post['ip'],
                    'lead_status_id' => 1,
                    'loan_amount' => $post['loan_amount'],
                    'qde_consent' => $post['checkbox'],
                    // 'lead_data_source_id'   => $post['lead_data_source_id'],
                    'coordinates' => ($post['coordinates'] ? $post['coordinates'] : ""),
                    'utm_source' => ($post['utm_source'] ? $post['utm_source'] : ''),
                    'utm_campaign' => ($post['utm_campaign'] ? $post['utm_campaign'] : ''),
                );

                $InsertLeads = $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                $insertLeadsCustomer = array(
                    'customer_lead_id' => $lead_id,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'sur_name' => $last_name,
                    'dob' => $dob,
                    'gender' => (strtoupper($post['gender'])),
                    'pancard' => $pancard,
                    'mobile' => ($post['mobile']),
                    'alternate_mobile' => ($post['alternate_mobile']),
                    'email' => $email,
                    'alternate_email' => ($post['email_office']),
                    'state_id' => ($post['state_id']),
                    'current_city' => ($post['city']),
                    'cr_residence_pincode' => ($post['pin'] ? ($post['pin']) : ''),
                    'created_date' => created_on
                );

                $customer_emp = array(
                    'lead_id' => $lead_id,
                    'company_id' => ($post['company_id']),
                    'product_id' => 1,
                    'monthly_income' => ($post['monthly_income']),
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

                require_once(COMPONENT_PATH . 'CommonComponent.php');

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

    public function BankVerify_post() {

        $apiEndpoint = 'https://api-preproduction.signzy.app/api/v3/bankaccountverification/bankaccountverifications';
        $beneficiaryAccount = $_POST['beneficiaryAccount'];
        $beneficiaryIFSC = $_POST['beneficiaryIFSC'];
        $beneficiaryMobile = $_POST['beneficiaryMobile'];
        $beneficiaryName = $_POST['beneficiaryName'];
        $nameFuzzy = $_POST['nameFuzzy'];
        $nameMatchScore = $_POST['nameMatchScore'];


        $requestData = array(
            "beneficiaryAccount" => $beneficiaryAccount,
            "beneficiaryIFSC" => $beneficiaryIFSC,
            "beneficiaryMobile" => '',
            "beneficiaryName" => $beneficiaryName,
            "nameFuzzy" => 'true',
            "nameMatchScore" => 0.9

        );

        $curl = curl_init($apiUrl);

        curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "accept-language: en-US,en;q=0.8",
            "accept: */*",
            'Authorization: i7G9KHbakY07Y1p3F5W1xVg9Cv0sCYrQ'

        ));
        $response = curl_exec($curl);
        $responseData = json_decode($response, true);
        $data = $responseData['result'];


        if ($data !== null && isset($data['active']) && $data['active'] == 'yes') {
            json_encode($this->response(['Status' => 1, 'Message' => 'Account verify successfully.'], REST_Controller::HTTP_OK));
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Verification Failed.'], REST_Controller::HTTP_OK));
        }
    }


    public function BankingVerification_post() {

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("bank_account_number", "Bank Account No", "required");
            $this->form_validation->set_rules("bank_account_ifsc", "IFSC Code", "required|alpha_numeric");
            $this->form_validation->set_rules("bank_account_type_id", "Bank Account Type", "required");
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }


            $beneficiaryAccount = $_POST['bank_account_number'];
            $beneficiaryIFSC = $_POST['bank_account_ifsc'];
            $beneficiaryName = $_POST['bank_account_type_id'];
            $nameFuzzy = $_POST['nameFuzzy'];
            $nameMatchScore = $_POST['nameMatchScore'];



            if ($bank_account_number != $cnf_bank_account_number) {
                throw new ErrorException("Account number do not match.");
            }

            // $get_bank_details = $this->Tasks->get_bank_details($bank_account_ifsc);

            // if ($get_bank_details['status'] != 1) {
            //     throw new ErrorException("Bank details not found.");
            // }

            // $get_bank_details = $get_bank_details['bank_account_details'];

            // $get_bank_account_type = $this->Tasks->getBankTypeById($bank_account_type_id);

            // if ($get_bank_account_type['status'] != 1) {
            //     throw new ErrorException("Invalid Bank Account Type");
            // }

            // $get_bank_account_type = $get_bank_account_type['master_bank_type_details'];

            // $get_customer_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            // if ($get_customer_details['status'] != 1) {
            //     throw new ErrorException("Customer details not found!");
            // }

            // $get_customer_details = $get_customer_details['customer_profile_details'];

            // $customer_full_name = !empty($get_customer_details['cp_first_name']) ? $get_customer_details['cp_first_name'] : '';
            // $customer_full_name .= !empty($get_customer_details['cp_middle_name']) ? ' ' . $get_customer_details['cp_middle_name'] : '';
            // $customer_full_name .= !empty($get_customer_details['cp_sur_name']) ? ' ' . $get_customer_details['cp_sur_name'] : '';



            // Prepare request data
            $requestData = array(
                "beneficiaryAccount" => $beneficiaryAccount,
                "beneficiaryIFSC" => $beneficiaryIFSC,
                "beneficiaryMobile" => '',
                "beneficiaryName" => $beneficiaryName,
                "nameFuzzy" => 'true',
                "nameMatchScore" => 0.9

            );

            $last_row = $this->db->select('lead_id, mobile')->where('lead_active', $flag)->from('leads')->order_by('lead_id', 'desc')->limit(1)->get();
            $leadId = $last_row->row();
            $lead_id = !empty($leadId->lead_id) ? $leadId->lead_id : 0;
            $mobile = !empty($leadId->mobile) ? $leadId->mobile : '';


            $insert_customer_banking = [
                'lead_id' => $lead_id,
                'bank_name' => $get_bank_details['bank_name'],
                'ifsc_code' => $get_bank_details['bank_ifsc'],
                'branch' => $get_bank_details['bank_branch'],
                'beneficiary_name' => $customer_full_name,
                'account' => $bank_account_number,
                'confirm_account' => $cnf_bank_account_number,
                'account_type' => $get_bank_account_type['m_bank_type_name'],
                'created_on' => date('Y-m-d H:i:s'),
            ];

            $cust_banking_id = $this->Tasks->insert("customer_banking", $insert_customer_banking);
            print_r("Testing");
            die;

            $verify_bank_account = $this->commonComponent->payday_bank_account_verification_api($lead_id, array('cust_banking_id' => $cust_banking_id));

            if ($verify_bank_account['status'] != 1) {
                throw new ErrorException($verify_bank_account['error_msg']);
            }

            $conditions2 = ['lead_id' => $lead_id, "account_status_id" => 1];
            $data2 = ['account_status' => "NO", "account_status_id" => 0];
            $this->Tasks->update($conditions2, "customer_banking", $data2);

            $data = [
                'account_status' => "ACCOUNT AND NAME VERIFIED SUCCESSFULLY",
                'account_status_id' => 1,
                'remark' => 'OK',
                'updated_on' => date("Y-m-d H:i:s")
            ];

            $conditions = ['id' => $cust_banking_id];

            $result = $this->Tasks->update($conditions, "customer_banking", $data);

            if ($result != 1) {
                throw new ErrorException('Failed to Verify. try again');
            }
        }
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

                    $ip_address = ($post['ip']);

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

                    require_once(COMPONENT_PATH . 'CommonComponent.php');

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

                $lead_id = $this->encrypt->decode($post['lead_id']);

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

                $lead_id = $this->encrypt->decode($post['lead_id']);

                if (!empty($lead_id)) {

                    $qry = "SELECT LD.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as full_name, LC.customer_appointment_schedule, LD.mobile, LD.pancard, LD.email, LD.lead_status_id, LD.status, LD.stage";
                    $qry .= " FROM leads LD INNER JOIN lead_customer LC ON (LD.lead_id = LC.customer_lead_id) ";
                    $qry .= " WHERE LD.lead_id = LC.customer_lead_id AND LD.lead_active = 1 AND LD.lead_id = '$lead_id'";

                    $response = $this->db->query($qry);

                    if ($response->num_rows() > 0) {
                        $leadDetails = $response->row_array();

                        $scheduled_datetime = $post['schedule_datetime'];
                        $remarks = $post['remarks'];
                        $current_datetime = date("Y-m-d H:i:s");
                        $scheduled_datetime_hours = date('His', strtotime($scheduled_datetime));

                        if ((($scheduled_datetime_hours < 100000) || ($scheduled_datetime_hours > 190000))) {
                            return json_encode($this->response(['Status' => 0, 'errormessage' => 'You can scheduled your appointment between 10AM to 7PM.']));
                        } else if ((strtotime($current_datetime) > strtotime($scheduled_datetime))) {
                            return json_encode($this->response(['Status' => 0, 'errormessage' => 'Please enter the valid scheduled appointment date time.']));
                        } else if ((intval(date('Ymd', strtotime($scheduled_datetime))) < intval(date("Ymd", strtotime($current_datetime)))) || (intval(date('Ymd', strtotime($scheduled_datetime))) > intval(date('Ymd', strtotime('+1 day', strtotime($current_datetime)))))) {
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
