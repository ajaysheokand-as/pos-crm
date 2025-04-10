<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class ChatBotController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('CollectionApp/Collection_Model', 'Collex');
    }

    public function qdeAppSaveRegistration_post() {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("full_name", "Name", "required|trim|max_length[50]");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("pincode", "pincode", "required|min_length[6]|max_length[6]");
            $this->form_validation->set_rules("income_type", "Income Type", "required|trim|numeric");
            $this->form_validation->set_rules("purpose_of_loan", "Purpose of Loan", "required|trim");
            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|min_length[5]|max_length[7]");
            $this->form_validation->set_rules("loan_amount", "Required Loan Amount", "required|trim|numeric|min_length[4]|max_length[6]");
            $this->form_validation->set_rules("pancard", "Pancard", "required|trim|regex_match[/([A-Za-z]{5}[0-9]{4}[A-Za-z]{1})/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {
                $city_id = 0;
                $state_id = 0;
                $full_name = strtoupper($post['full_name']);
                $mobile = $post['mobile'];
                $email = strtoupper($post['email']);
                $pincode = $post['pincode'];
                $coordinates = !empty($post['coordinates']) ? $post['coordinates'] : '';
                $lead_data_source_id = 25;
                $data_source_name = 'CHATBOT';
                $utm_source = !empty($post['utm_source']) ? 'CHATBOT' . "-" . $post['utm_source'] : 'CHATBOT';
                $utm_campaign = 'CHATBOT';
                $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

                $pancard = !empty($post['pancard']) ? strtoupper($post['pancard']) : "";
                $income_type = !empty($post['income_type']) ? $post['income_type'] : "";
                $purposeofloan = !empty($post['purpose_of_loan']) ? $post['purpose_of_loan'] : "";
                $loan_amount = !empty($post['loan_amount']) ? $post['loan_amount'] : "";
                $monthly_salary = !empty($post['monthly_income']) ? $post['monthly_income'] : "";

                $otp = rand(1000, 9999);

                if ($mobile == "9560807913") { //Hardcoded otp testing... donot remove
                    $otp = 1989;
                }

                $temp_name_array = $this->Tasks->common_parse_full_name($full_name);

                $first_name = !empty($temp_name_array['first_name']) ? strtoupper($temp_name_array['first_name']) : "";
                $middle_name = !empty($temp_name_array['middle_name']) ? strtoupper($temp_name_array['middle_name']) : "";
                $last_name = !empty($temp_name_array['last_name']) ? strtoupper($temp_name_array['last_name']) : "";

                $purposeofloanname = '';

                $query = $this->Tasks->selectdata(['enduse_id' => $purposeofloan], 'enduse_name', 'master_enduse');

                if ($query->num_rows() > 0) {
                    $sql = $query->row();
                    $purposeofloanname = $sql->enduse_name;
                }

                if (!empty($pincode)) { //If pincode available in excel
                    $result = $this->db->select('*')->where(["m_pincode_value" => $pincode, "m_pincode_active" => 1, "m_pincode_deleted" => 0])->from("master_pincode")->get();

                    if ($result->num_rows() > 0) {

                        $pincode_array = $result->row_array();

                        $city_id = $pincode_array['m_pincode_city_id'];

                        if (!empty($city_id)) {

                            $city = $this->db->select('m_city_id,m_city_state_id')->from('master_city')->where('m_city_id', $city_id)->get();

                            if ($city->num_rows() > 0) {

                                $city_array = $city->row_array();

                                $state_id = $city_array['m_city_state_id'];
                            }
                        }
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => "Pincode does not exist."], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Please enter the pincode."], REST_Controller::HTTP_OK));
                }

                $lead_status_id = 1;
                $user_type = "NEW";

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'otp' => $otp,
                    'email' => $email,
                    'pancard' => $pancard,
                    'company_id' => 1,
                    'product_id' => 1,
                    'lead_status_id' => $lead_status_id,
                    'user_type' => $user_type,
                    'stage' => 'S1',
                    'status' => 'LEAD-NEW',
                    'source' => $data_source_name,
                    'lead_entry_date' => date('Y-m-d'),
                    'lead_data_source_id' => $lead_data_source_id,
                    'ip' => $ip,
                    'qde_consent' => 'Y',
                    'term_and_condition' => "YES",
                    'created_on' => date('Y-m-d H:i:s'),
                    'city_id' => $city_id,
                    'state_id' => $state_id,
                    'pincode' => $pincode,
                    'coordinates' => $coordinates,
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'loan_amount' => $loan_amount,
                    'tenure' => 30,
                    'purpose' => $purposeofloanname
                );

                $this->db->insert('leads', $insertDataLeads);

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
                    'pancard' => $pancard,
                    'email' => $email,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'cr_residence_pincode' => $pincode,
                    'created_date' => date('Y-m-d H:i:s')
                );

                $this->db->insert('lead_customer', $insertLeadsCustomer);

                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Chatbot new lead applied");

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
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'pancard' => $cif_result->cif_pancard,
                            'alternate_email' => $cif_result->cif_office_email,
                            'pincode' => $cif_result->cif_residence_pincode,
                            'user_type' => $user_type,
                            'updated_on' => date('Y-m-d H:i:s')
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
                        $insert_customer_employement['updated_on'] = date('Y-m-d H:i:s');
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = date('Y-m-d H:i:s');
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }
                }

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                if ($return_eligibility_array['status'] == 2) {
                    return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                }

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 3,
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

                if ($user_type == "NEW") {

                    $return_lw_array = setLWRepeatCustomer($lead_id);

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "NF Check - $pancard : " . $return_lw_array['message']);
                }

                $array = ['Status' => 1, 'Message' => 'Lead save successfully.', 'lead_id' => $lead_id, 'mobile' => $mobile];

                if ($lead_otp_id) {
                    return json_encode($this->response($array, REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppOtpVerify_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $mobile = !empty($post['mobile']) ? $post['mobile'] : 0;
                $lead_id = $post['lead_id'];
                $otp = $post['otp'];

                $query = $this->db->select('lead_id, mobile, lead_status_id, pancard, user_type, email')->where(['lead_id' => $lead_id])->from('leads')->get();
                $query_cust = $this->db->select('first_name,middle_name,sur_name')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

                if ($query->num_rows() > 0) {

                    $query = $query->row();
                    $result_cust = $query_cust->row();
                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                    $empquery = $empquery->row();
                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;
                    $lead_status_id = $query->lead_status_id;
                    $first_name = $result_cust->first_name;
                    $middle_name = $result_cust->middle_name;
                    $last_name = $result_cust->sur_name;
                    $pancard = !empty($query->pancard) ? $query->pancard : "";
                    $mobile = !empty($query->mobile) ? $query->mobile : "";
                    $email = !empty($query->email) ? $query->email : "";
                    $user_type = !empty($query->user_type) ? $query->user_type : "";

                    if ($lead_status_id > 1) {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
                    }

                    $last_row = $this->db->select('lot_id,lot_mobile_otp')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();

                    $lastotp = $last_row->lot_mobile_otp;
                    $lot_id = $last_row->lot_id;

                    if ($lastotp != $otp) {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'OTP verification failed. Please try again.'], REST_Controller::HTTP_OK));
                    }

                    $update_lead_otp_trans_data = [
                        'lot_otp_verify_time' => date('Y-m-d H:i:s'),
                        'lot_otp_verify_flag' => 1,
                    ];

                    $update_data_lead_customer = [
                        'mobile_verified_status' => "YES",
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                    $this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);
                    $this->db->set('lead_is_mobile_verified', 1)->where('lead_id', $lead_id)->update('leads');

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP verified by customer");

                    $referenceCode = $this->Tasks->generateReferenceCode($lead_id, $first_name, $last_name, $mobile);

                    $this->db->set('lead_reference_no', $referenceCode)->where('lead_id', $lead_id)->update('leads');

                    require_once (COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = $first_name;
                    $sms_input_data['refrence_no'] = $referenceCode;

                    $CommonComponent->payday_sms_api(2, $lead_id, $sms_input_data);

                    $CommonComponent->sent_lead_thank_you_email($lead_id, $email, $first_name, $referenceCode);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'OTP verified successfully.', 'lead_id' => $lead_id, 'referenceCode' => $referenceCode, 'name' => $first_name, 'mobile' => "91" . $mobile, 'user_type' => $user_type], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application does not exist.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppResendOTP_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
            $this->form_validation->set_rules("mobile_update_flag", "Mobile Update Flag", "trim");

            $lead_id = $post['lead_id'];
            $mobile = $post['mobile'];
            $mobile_update_flag = (!empty($post['mobile_update_flag']) && $post['mobile_update_flag'] == 1) ? 1 : 0;

            $table = 'leads';
            $selectdata = 'first_name as name, mobile, email,lead_status_id';
            $where = "WHERE lead_id = '$lead_id'";

            $num_rowsleads = getnumrowsData($selectdata, $table, $where);

            if ($num_rowsleads == 0) {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Application does not exist.'], REST_Controller::HTTP_OK));
            } else {
                $lead_status_id = $num_rowsleads[0]['lead_status_id'];
                $lead_id = $post['lead_id'];
                $mobile = $post['mobile'];
                $first_name = $num_rowsleads[0]['name'];

                $otp = rand(1000, 9999);

                if ($mobile == "9953931000") { //Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") { //Hardcoded otp testing... donot remove
                    $otp = 1989;
                }

                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $dataleads = array(
                    'otp' => $otp,
                );

                if ($mobile_update_flag == 1) {
                    $dataleads["mobile"] = $mobile;
                    $update_data_lead_customer = ["mobile" => $mobile];
                }

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 3,
                    'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
                );

                $this->db->insert('leads_otp_trans', $insertDataOTP);

                $res = $this->CurdMode->globel_update('leads', $dataleads, $lead_id, 'lead_id');

                if ($mobile_update_flag == 1) {
                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);
                }

                if ($res) {

                    require_once (COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();
                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = $first_name;
                    $sms_input_data['otp'] = $otp;

                    $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP resend successfully." . ($mobile_update_flag == 1) ? " Mobile number changed" : "");

                    return json_encode($this->response(['Status' => 1, 'Message' => 'OTP resent successfull.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'OTP not sent successfully.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppReLoanRequest_post() {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("pan", "PAN", "required|trim|min_length[10]|max_length[10]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $pancard = trim(strtoupper($post['pan']));
                $mobile = "";
                $coordinates = !empty($post['coordinates']) ? $post['coordinates'] : '';
                $cif_result = "";

                if (!empty($pancard)) {

                    $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();

                    if ($cif_query->num_rows() > 0) {

                        $cif_result = $cif_query->row();

                        $cif_loan_is_disbursed = $cif_result->cif_loan_is_disbursed;

                        if ($cif_loan_is_disbursed > 0) {
                            $mobile = $cif_result->cif_mobile;
                        } else {
                            return json_encode($this->response(['Status' => 3, 'Message' => 'You are not our existing customer. Please apply from instant loan.'], REST_Controller::HTTP_OK));
                        }
                    } else {
                        return json_encode($this->response(['Status' => 3, 'Message' => 'Pan number does not registered with us.'], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Please enter the pan number.'], REST_Controller::HTTP_OK));
                }

                if (!empty($mobile)) {
                    $first_name = $cif_result->cif_first_name;
                    $middle_name = $cif_result->cif_middle_name;
                    $last_name = $cif_result->cif_sur_name;
                    $email = strtoupper($cif_result->cif_personal_email);
                    $city_id = $cif_result->cif_residence_city_id;
                    $city_state_id = $cif_result->cif_residence_state_id;
                    $pincode = $cif_result->cif_residence_pincode;
                    $lead_data_source_id = 25;
                    $data_source_name = 'CHATBOT';

                    $utm_source = !empty($post['utm_source']) ? 'CHATBOTRELOAN' . "-" . $post['utm_source'] : 'CHATBOTRELOAN';

                    $utm_campaign = 'CHATBOTRELOAN';
                    $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

                    $otp = rand(1000, 9999);

                    if ($mobile == "9560807913") { //Hardcoded otp testing... donot remove
                        $otp = 1989;
                    }

                    $lead_status_id = 1;

                    $insertDataLeads = array(
                        'first_name' => $first_name,
                        'mobile' => $mobile,
                        'pincode' => $pincode,
                        'pancard' => $pancard,
                        'otp' => $otp,
                        'email' => $email,
                        'company_id' => 1,
                        'product_id' => 1,
                        'lead_status_id' => $lead_status_id,
                        'user_type' => 'REPEAT',
                        'stage' => 'S1',
                        'status' => 'LEAD-NEW',
                        'source' => $data_source_name,
                        'lead_entry_date' => date('Y-m-d'),
                        'lead_data_source_id' => $lead_data_source_id,
                        'ip' => $ip,
                        'qde_consent' => 'Y',
                        'term_and_condition' => "YES",
                        'created_on' => date('Y-m-d H:i:s'),
                        'city_id' => $city_id,
                        'state_id' => $city_state_id,
                        'coordinates' => $coordinates,
                        'utm_source' => $utm_source,
                        'utm_campaign' => $utm_campaign,
                    );

                    $this->db->insert('leads', $insertDataLeads);

                    $lead_id = $this->db->insert_id();

                    if (empty($lead_id)) {
                        return json_encode($this->response(['Status' => 0, 'Message' => "Some error occurred due to data set. Please try again."], REST_Controller::HTTP_OK));
                    }


                    $insertLeadsCustomer = array(
                        'customer_lead_id' => $lead_id,
                        'pancard' => $pancard,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $last_name,
                        'mobile' => $mobile,
                        'email' => $email,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'created_date' => date('Y-m-d H:i:s')
                    );

                    $this->db->insert('lead_customer', $insertLeadsCustomer);

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Reloan new lead applied");

                    require_once(COMPONENT_PATH . 'CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                    if ($return_eligibility_array['status'] == 2) {
                        return json_encode($this->response(['Status' => 2, 'Message' => "Eligibility Failed."], REST_Controller::HTTP_OK));
                    }

                    $insertDataOTP = array(
                        'lot_lead_id' => $lead_id,
                        'lot_mobile_no' => $mobile,
                        'lot_mobile_otp' => $otp,
                        'lot_mobile_otp_type' => 3,
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
                    $sms_input_data['name'] = $first_name;
                    $sms_input_data['otp'] = $otp;

                    $CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");

                    $array = ['Status' => 1, 'Message' => 'OTP sent successfully.', 'lead_id' => $lead_id, 'mobile' => $mobile];

                    if ($lead_otp_id) {
                        return json_encode($this->response($array, REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Mobile number does not mapped with PAN.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppLoanStatus_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("pan", "PAN", "trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("loan_no", "LOAN NO", "trim|max_length[16]|max_length[16]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $pancard = strtoupper($post['pan']);

                $loan_no = strtoupper($post['loan_no']);

                if (empty($pancard) && empty($loan_no)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Please enter either PAN or Loan No."], REST_Controller::HTTP_OK));
                }

                $query = "SELECT LD.lead_id, LD.pancard, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) full_name, LD.loan_no, CAM.loan_recommended, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.repayment_amount, CAM.roi, MS.status_name, MS.status_customer_label";
                $query .= " FROM credit_analysis_memo CAM INNER JOIN lead_customer LC ON(CAM.lead_id=LC.customer_lead_id)";
                $query .= " INNER JOIN leads LD ON(CAM.lead_id=LD.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id)";
                $query .= " INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id)";
                $query .= " WHERE ";

                if (!empty($pancard)) {
                    $query .= "LD.pancard='$pancard' ";
                } else if (!empty($loan_no)) {
                    $query .= "LD.loan_no='$loan_no' ";
                }

                $query .= " AND L.loan_status_id = 14 AND L.loan_active = 1 AND LD.lead_active = 1 ORDER BY LD.lead_id DESC LIMIT 1";

                $result = $this->db->query($query)->row_array();

                if (empty($result)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "No Record found."], REST_Controller::HTTP_OK));
                }

                $loan_no = $result['loan_no'];
                $lead_id = $result['lead_id'];
                $pancard = strtoupper($result['pancard']);
                $full_name = strtoupper($result['full_name']);

                $return_repayment = $this->Collex->get_repayment_details($lead_id);

                if (!empty($return_repayment)) {
                    $return_repayment_details = $return_repayment;
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Repayment details not found"], REST_Controller::HTTP_OK));
                }


                $loan_amount = $return_repayment_details['loan_recommended'];
                $repay_amount = $return_repayment_details['repayment_amount'];
                $roi = $return_repayment_details['roi'];
                $disbursal_date = $return_repayment_details['disbursal_date'];
                $repay_date = $return_repayment_details['repayment_date'];
                $tenure = $return_repayment_details['tenure'];
                $Status = $result['status_customer_label'];

                $total_received_amount = $return_repayment_details['total_received_amount'];
                $total_due_amount = $return_repayment_details['total_due_amount'];
                $penalty_days = $return_repayment_details['penalty_days'];
                $total_repayment_amount = $return_repayment_details['total_repayment_amount'];

                $return_array = [
                    'Status' => 1,
                    'Message' => 'Loan Details',
                    'lead_id' => $lead_id,
                    'full_name' => $full_name,
                    'loan_no' => $loan_no,
                    'loan_amount' => $loan_amount,
                    'repay_amount' => $repay_amount,
                    'roi' => $roi,
                    'disbursal_date' => $disbursal_date,
                    'repay_date' => $repay_date,
                    'tenure' => $tenure,
                    'total_payable_amount' => $total_repayment_amount,
                    'total_due_amount' => $total_due_amount,
                    'total_received_amount' => $total_received_amount,
                    'penalty_days' => $penalty_days,
                    'current_loan_status' => $Status,
                ];
                $return_array['is_last_loan_closed'] = 0;

                $query = $this->db->select('LD.lead_id, LD.mobile, LD.lead_status_id, LD.pancard')
                        ->where(['LD.pancard' => $pancard])
                        ->from('leads LD')
                        ->join('loan L', 'LD.lead_id=L.lead_id AND L.loan_status_id=14', 'INNER')
                        ->order_by('lead_id', 'desc')
                        ->get();

                if ($query->num_rows() > 0) {

                    $query = $query->row();

                    $lead_status_id = $query->lead_status_id;

                    if ($lead_status_id == 16) {
                        $return_array['is_last_loan_closed'] = 1;
                        $return_array['Message'] = "Dear " . $full_name . ", Your last loan was closed. Do you need Re-loan?";
                    }
                }

                return json_encode($this->response($return_array, REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppLeadStatus_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "MOBILE", "trim|min_length[10]|max_length[10]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $mobile = trim($post['mobile']);

                if (empty($mobile)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Please enter 10 digit mobile number."], REST_Controller::HTTP_OK));
                }

                $query = "SELECT LD.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) full_name, CAM.loan_recommended, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.repayment_amount, CAM.roi, MS.status_name, MS.status_stage, MS.status_customer_label";
                $query .= " FROM leads LD INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id)";
                $query .= " INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id)";
                $query .= " LEFT JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id)";
                $query .= " WHERE LC.mobile = '$mobile'";
                $query .= " AND LD.lead_active = 1 ORDER BY LD.lead_id DESC LIMIT 1";

                $result = $this->db->query($query)->row_array();

                if (empty($result)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "No Record found."], REST_Controller::HTTP_OK));
                }


                $lead_id = $result['lead_id'];
                $full_name = strtoupper($result['full_name']);
                $loan_amount = !empty($result['loan_recommended']) ? $result['loan_recommended'] : "-";
                $repay_amount = !empty($result['repayment_amount']) ? $result['repayment_amount'] : "-";
                $roi = !empty($result['roi']) ? $result['roi'] : "-";
                $disbursal_date = !empty($result['disbursal_date']) ? $result['disbursal_date'] : "-";
                $repay_date = !empty($result['repayment_date']) ? $result['repayment_date'] : "-";
                $tenure = !empty($result['tenure']) ? $result['tenure'] : "-";
                $Status = $result['status_customer_label'];

                $return_array = [
                    'Status' => 1,
                    'Message' => 'Lead Details',
                    'lead_id' => $lead_id,
                    'full_name' => $full_name,
                    'loan_amount' => $loan_amount,
                    'repay_amount' => $repay_amount,
                    'roi' => $roi,
                    'disbursal_date' => $disbursal_date,
                    'repay_date' => $repay_date,
                    'tenure' => $tenure,
                    'current_loan_status' => $Status,
                ];

                return json_encode($this->response($return_array, REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppMasterAPI_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            if ($post['apiname'] == 'getstate') {

                $num_rows = $this->MasterModel->getStateData();
                return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
            } else if ($post['apiname'] == 'getcity') {

                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {

                    $state_id = $post['id'];
                    $num_rows = $this->MasterModel->getCityData($state_id);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getpincode') {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {
                    $city_id = $post['id'];
                    $num_rows = $this->MasterModel->getPincode($city_id);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getpupposeofloan') {

                $num_rows = $this->MasterModel->getPurposeOfLoan();

                $master_data = array();
                foreach ($num_rows as $master_values) {
                    $temp_data['title'] = $master_values['name'];
                    $temp_data['text'] = $master_values['id'];
                    $master_data[] = $temp_data;
                }

                return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'options' => $master_data], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

}
