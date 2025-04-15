<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';

class TaskApi extends REST_Controller {

    public function __construct() {

        parent::__construct();

//        $this->load->model('Token_api', 'Tokens');

        $this->load->model('Task_Model');

        date_default_timezone_set('Asia/Kolkata');

        define('created_date', date('Y-m-d'));
        define('created_on', date('Y-m-d H:i:s'));

        define('Tbl_docs', 'docs');

        define('Tbl_leads', 'leads');

        // $token = md5("S-370-Engineer_Vinay-Kumar_LW_Paday");
    }

    public function getLoanDetails_get($loan_no) {

        if (!empty($loan_no)) {

            $result = $this->db->select("*")->where('loan_no', $loan_no)->from('loan')->get()->row();

            if ($result) {

                json_encode($this->response($result, REST_Controller::HTTP_OK));
            } else {

                json_encode($this->response(['Status' => "Failed", 'Message' => 'Failed to generate token.'], REST_Controller::HTTP_OK));
            }
        } else {

            json_encode($this->response(['Status' => "Failed", 'Message' => 'Invalid company Id'], REST_Controller::HTTP_OK));
        }
    }

    public function generateAccessToken_get($company_id) {

        if (!empty($company_id)) {

            $result = $this->Tokens->create_token($company_id);

            if ($result) {

                json_encode($this->response($result, REST_Controller::HTTP_OK));
            } else {

                json_encode($this->response(['Status' => "Failed", 'Message' => 'Failed to generate token.'], REST_Controller::HTTP_OK));
            }
        } else {

            json_encode($this->response(['Status' => "Failed", 'Message' => 'Invalid company Id'], REST_Controller::HTTP_OK));
        }
    }

    public function getState_get() {

        $input_data = file_get_contents("php://input");

        $get = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $get = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $get = $this->security->xss_clean($_GET);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $this->form_validation->set_data($this->get());

            $result = $this->db->select('ST.m_state_id as state_id, ST.m_state_name as state')->where(['ST.m_state_active' => 1, 'ST.m_state_is_sourcing' => 1])->from("master_state ST")->get();

            if ($result->num_rows() > 0) {

                $data = $result->result();

                return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
            } else {

                return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
            }
        } else {

            return json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getCity_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric|is_natural");

            if ($this->form_validation->run() == FALSE) {

                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $result = $this->db->select('CT.m_city_state_id as state_id, CT.m_city_id as city_id,CT.m_city_name as city, CT.m_city_code as city_code, CT.m_city_category as city_category')
                        ->where('CT.m_city_state_id', $post['state_id'])
                        ->where('CT.m_city_active', 1)
                        ->where('CT.m_city_is_sourcing', 1)
                        ->from("master_city CT")
                        ->get();

                if ($result->num_rows() > 0) {

                    $data = $result->result();

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {

                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getPincodeByCityId_post() {
        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("city_id", "City ID", "required|trim|numeric|is_natural");

            if ($this->form_validation->run() == FALSE) {

                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $result = $this->db->select('MP.m_pincode_id as pincode_id, MP.m_pincode_city_id as city_id,MP.m_pincode_value as pincode')
                        ->where('MP.m_pincode_city_id', $post['city_id'])
                        ->where('MP.m_pincode_active', 1)
                        ->from("master_pincode MP")
                        ->get();

                if ($result->num_rows() > 0) {

                    $data = $result->result();

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {

                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function vinSaveTasksLAC_post() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $result = $this->db->insert(Tbl_leads, $_POST);

            if ($result == 1) {

                json_encode($this->response($result, REST_Controller::HTTP_OK));
            } else {

                json_encode($this->response(['Request Method Post Failed.'], REST_Controller::HTTP_OK));
            }
        } else {

            json_encode($this->response(['Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function blocked_vinSaveTasks_post() {//OLD Mobile App action
        $return_data = "";
        $lead_id = 0;
        $input_data = file_get_contents("php://input");

        if (!empty($input_data)) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $data_array = array();
        $data_array['mapp_data_source_str'] = !empty($post['source']) ? $post['source'] : "NA";
        $data_array['mapp_action_name'] = "vinSaveTasks_post";
        $data_array['mapp_browser_info'] = !empty($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "NA";
        $data_array['mapp_browser_info'] = $data_array['mapp_browser_info'] . " | " . $_SERVER["REQUEST_URI"];
        $data_array['mapp_browser_info'] = addslashes($data_array['mapp_browser_info']);
        $data_array['mapp_customer_id'] = ($post['customer_id']) ? $post['customer_id'] : "";
        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
        $data_array['mapp_api_status_id'] = 1;
        $data_array['mapp_request'] = json_encode($post);
        $data_array['mapp_request_datetime'] = date("Y-m-d H:i:s");

        $insert_log_id = $this->insertUpdateAppLog($data_array);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("first_name", "First Name", "required|trim|min_length[1]|max_length[40]");

            $this->form_validation->set_rules("middle_name", "Middle Name", "trim");

            $this->form_validation->set_rules("sur_name", "Sur Name", "trim");

            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|numeric|min_length[10]|max_length[10]");

            $this->form_validation->set_rules("pan", "Pan card", "required|trim|min_length[10]|max_length[10]|regex_match[/[a-zA-Z]{3}[p-pP-P]{1}[a-zA-Z]{1}\d{4}[a-zA-Z]{1}/]");

            $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            $this->form_validation->set_rules("email_official", "Official Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            $this->form_validation->set_rules("dob", "Date Of Birth", "trim");

            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");

            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|is_natural");

            $this->form_validation->set_rules("obligations", "Obligations", "trim|numeric|is_natural");

            $this->form_validation->set_rules("gender", "Gender", "required|trim|regex_match[/^[a-zA-Z]+$/]");

            $this->form_validation->set_rules("alternate_mobile", "Alternate Mobile No", "trim|numeric|min_length[10]|max_length[10]");

            $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric");

            $this->form_validation->set_rules("city", "city", "required|trim");

            $this->form_validation->set_rules("pin", "Pincode", "required|trim|numeric|min_length[6]|max_length[6]");

            $this->form_validation->set_rules("otp", "OTP", "trim|numeric|min_length[4]|max_length[4]");

            $this->form_validation->set_rules("source", "Lead Source", "required|trim");

            $this->form_validation->set_rules("utm_source", "UTM Source", "trim");

            $this->form_validation->set_rules("utm_campaign", "UTM Compain", "trim");

            $this->form_validation->set_rules("coordinates", "coordinates", "trim");

//            $this->form_validation->set_rules("ip", "IP", "trim");

            if ($this->form_validation->run() == FALSE) {
                $return_data = ['Status' => 0, 'Message' => validation_errors()];
                if (!empty($insert_log_id)) {
                    $data_array = array();
                    $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                    $data_array['mapp_api_status_id'] = 2;
                    $data_array['mapp_response'] = json_encode($return_data);
                    $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                    $this->insertUpdateAppLog($data_array, $insert_log_id);
                }
                return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
            } else {

                $lead_id = 0;

                if (!empty($post['customer_id'])) {
                    $query = $this->db->select('lead_id')->where('lead_id', $post['customer_id'])->from('leads')->get();
                    if ($query->num_rows() > 0) {
                        $result1 = $query->row();
                        $lead_id = $result1->lead_id;
                    }
                }


                $day = date('d', strtotime($post['dob']));

                $month = date('m', strtotime($post['dob']));

                $year = date('Y', strtotime($post['dob']));

                $dateOfBirth = $year . '-' . $month . '-' . $day;

                $dob = ($dateOfBirth) ? $dateOfBirth : "";

                $otp = ($post['otp']) ? $post['otp'] : rand(1000, 9999);

                $mobile = $post['mobile'];
                $alternate_mobile = $post['alternate_mobile'];

                if ($mobile == "9953931000") {//Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                }

                $coupon = "";

                if (isset($post['coupon_code'])) {
                    $coupon = $post['coupon_code'];
                }

                $first_name = strtoupper($post['first_name']);
                $middle_name = strtoupper($post['middle_name']);
                $sur_name = strtoupper($post['sur_name']);
                $email = strtoupper($post['email_personal']);
                $alternate_email = strtoupper($post['email_official']);
                $city_state_id = intval($post['state_id']);
                $city_id = intval($post['city']);
                $pincode = intval($post['pin']);
                $loan_amount = intval($post['loan_amount']);
                $obligations = intval($post['obligations']);
                $monthly_income = intval($post['monthly_income']);
                $source = strtoupper($post['source']);
                $pancard = strtoupper($post['pan']);
                $utm_source = !empty($post['utm_source']) ? strtoupper($post['utm_source']) : "";
                $utm_campaign = !empty($post['utm_campaign']) ? strtoupper($post['utm_campaign']) : "";
                $gender = strtoupper($post['gender']);

                if (!empty($pincode)) {
                    $result = $this->db->select('*')->where(["m_pincode_value" => $pincode, 'm_pincode_city_id' => $city_id])->from("master_pincode")->get();
                    if ($result->num_rows() == 0) {
                        $pincode = "";
                    }
                }

                if (!empty($source)) {
                    $result = $this->db->select('*')->where(["data_source_name" => $source])->from("master_data_source")->get();
                    if ($result->num_rows() > 0) {
                        $result = $result->row();
                        $post['lead_data_source_id'] = $result->data_source_id;
                    }
                }

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'pancard' => $pancard,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'pincode' => $pincode,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'loan_amount' => $loan_amount,
                    'obligations' => $obligations,
                    'otp' => $otp,
                    'user_type' => 'NEW',
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => $source,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'status' => "LEAD-NEW",
                    'stage' => "S1",
                    'lead_status_id' => 1,
                    'qde_consent' => "Y",
                    'lead_data_source_id' => $post['lead_data_source_id'],
                    'coordinates' => ($post['coordinates']) ? $post['coordinates'] : "",
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'promocode' => $coupon,
                );

                if (!empty($lead_id)) {
                    $this->db->where('lead_id', $lead_id)->update('leads', $insertDataLeads);
                } else {
                    $this->db->insert('leads', $insertDataLeads);
                    $lead_id = $this->db->insert_id();
                }


                if (!empty($lead_id)) {

                    $insertLeadsCustomer = array(
                        'customer_lead_id' => $lead_id,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'mobile' => $mobile,
                        'alternate_mobile' => $alternate_mobile,
                        'email' => $email,
                        'alternate_email' => $alternate_email,
                        'pancard' => $pancard,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'cr_residence_pincode' => $pincode,
                        'created_date' => created_on
                    );

                    $query = $this->db->select('customer_seq_id')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

                    if ($query->num_rows() > 0) {
                        $result1 = $query->row();
                        $customer_seq_id = $result1->customer_seq_id;
                        $this->db->where('customer_seq_id', $customer_seq_id)->update('lead_customer', $insertLeadsCustomer);
                    } else {
                        $this->db->insert('lead_customer', $insertLeadsCustomer);
                    }

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'emp_email' => $alternate_email,
                        'monthly_income' => $monthly_income,
                    ];

                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                    $empquery = $empquery->row();
                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                    if (!empty($emp_id)) {
                        $insert_customer_employement['updated_on'] = created_on;
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = created_on;
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }


                    if (!empty($pancard)) {
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
                                'current_house' => $cif_result->cif_residence_address_1,
                                'current_locality' => $cif_result->cif_residence_address_2,
                                'current_landmark' => $cif_result->cif_residence_landmark,
                                'current_residence_type' => $cif_result->cif_residence_type,
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

                            $update_customer_employement = [
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
                                'city_id' => $cif_result->cif_office_city_id,
                                'state_id' => $cif_result->cif_office_state_id,
                                'updated_on' => created_on,
                            ];

                            $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                            $update_data_leads = [
                                'customer_id' => $cif_result->cif_number,
                                'user_type' => $user_type,
                                'updated_on' => created_on
                            ];
                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        }
                    }

                    $reference_no = $this->generateReferenceCode($lead_id, $first_name, $sur_name, $mobile);

                    $update_data_leads = [
                        'lead_reference_no' => $reference_no,
                        'updated_on' => created_on
                    ];

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                    $dataSMS = [
                        'lead_id' => $lead_id,
                        'title' => ($gender == "MALE") ? "Mr." : "Ms.",
                        'name' => $first_name,
                        'mobile' => $mobile,
                        'otp' => $otp,
                    ];

                    $return_data = ['Status' => 1, 'Message' => 'Record Save Successfully.', 'ApplicationNo' => $lead_id];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                } else {
                    $return_data = ['Status' => 0, 'Message' => 'Server not responding your request.'];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }
            }
        } else {
            $return_data = ['Status' => 0, 'Message' => 'Request Method Post Failed.'];
            if (!empty($insert_log_id)) {
                $data_array = array();
                $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                $data_array['mapp_api_status_id'] = 2;
                $data_array['mapp_response'] = json_encode($return_data);
                $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                $this->insertUpdateAppLog($data_array, $insert_log_id);
            }
            return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
        }
    }

    public function saveLendingPage_post() {
        $return_data = "";
        $lead_id = 0;
        $input_data = file_get_contents("php://input");

        if (!empty($input_data)) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $data_array = array();
        $data_array['mapp_data_source_str'] = !empty($post['source']) ? $post['source'] : "NA";
        $data_array['mapp_action_name'] = "saveLendingPage_post";
        $data_array['mapp_browser_info'] = !empty($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "NA";
        $data_array['mapp_browser_info'] = $data_array['mapp_browser_info'] . " | " . $_SERVER["REQUEST_URI"];
        $data_array['mapp_browser_info'] = addslashes($data_array['mapp_browser_info']);
        $data_array['mapp_customer_id'] = ($post['customer_id']) ? $post['customer_id'] : "";
        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
        $data_array['mapp_api_status_id'] = 1;
        $data_array['mapp_request'] = json_encode($post);
        $data_array['mapp_request_datetime'] = date("Y-m-d H:i:s");

        $insert_log_id = $this->insertUpdateAppLog($data_array);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

//            $this->form_validation->set_rules("company_id", "Company ID", "required|trim|numeric|is_natural");

            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");

            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|is_natural");

            $this->form_validation->set_rules("obligations", "Obligations", "trim|numeric|is_natural");

            $this->form_validation->set_rules("first_name", "First Name", "required|trim|min_length[1]|max_length[40]");

            $this->form_validation->set_rules("middle_name", "Middle Name", "trim");

            $this->form_validation->set_rules("sur_name", "Sur Name", "trim");

            $this->form_validation->set_rules("gender", "Gender", "trim|regex_match[/^[a-zA-Z]+$/]");

            $this->form_validation->set_rules("dob", "Date Of Birth", "trim");

            $this->form_validation->set_rules("pan", "Pan card", "required|trim|min_length[10]|max_length[10]|regex_match[/[a-zA-Z]{3}[p-pP-P]{1}[a-zA-Z]{1}\d{4}[a-zA-Z]{1}/]");

            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|numeric|min_length[10]|max_length[10]");

            $this->form_validation->set_rules("alternate_mobile", "Alternate Mobile No", "trim|numeric|min_length[10]|max_length[10]");

            $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            $this->form_validation->set_rules("email_official", "Official Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            $this->form_validation->set_rules("state_id", "State", "required|trim|numeric");

            $this->form_validation->set_rules("city", "City", "required|trim");

            $this->form_validation->set_rules("pin", "Pincode", "trim|numeric|min_length[6]|max_length[6]");

//            $this->form_validation->set_rules("otp", "OTP", "trim|numeric|min_length[4]|max_length[4]");

            $this->form_validation->set_rules("source", "Lead Source", "required|trim");

            $this->form_validation->set_rules("utm_source", "UTM Source", "trim");

            $this->form_validation->set_rules("utm_campaign", "UTM Compain", "trim");

            $this->form_validation->set_rules("coordinates", "coordinates", "trim");

            $this->form_validation->set_rules("ip", "IP", "trim");

            if ($this->form_validation->run() == FALSE) {
                $return_data = ['Status' => 0, 'Message' => validation_errors()];
                if (!empty($insert_log_id)) {
                    $data_array = array();
                    $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                    $data_array['mapp_api_status_id'] = 2;
                    $data_array['mapp_response'] = json_encode($return_data);
                    $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                    $this->insertUpdateAppLog($data_array, $insert_log_id);
                }
                return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
            } else {

                $dob = !empty($post['dob']) ? $post['dob'] : "";

                if (!empty($dob)) {

                    $day = date('d', strtotime($post['dob']));

                    $month = date('m', strtotime($post['dob']));

                    $year = date('Y', strtotime($post['dob']));

                    $dateOfBirth = $year . '-' . $month . '-' . $day;

                    $dob = ($dateOfBirth) ? $dateOfBirth : "";
                }

                $otp = ($post['otp']) ? $post['otp'] : rand(1000, 9999);

                $mobile = $post['mobile'];
                $alternate_mobile = $post['alternate_mobile'];

                if ($mobile == "9953931000") {//Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") {
                    $otp = 1989;
                }

                $coupon = "";

                if (isset($post['coupon_code'])) {
                    $coupon = $post['coupon_code'];
                }

                $first_name = strtoupper($post['first_name']);
                $middle_name = strtoupper($post['middle_name']);
                $sur_name = strtoupper($post['sur_name']);
                $email = strtoupper($post['email_personal']);
                $alternate_email = strtoupper($post['email_official']);
                $city_state_id = intval($post['state_id']);
                $city_id = intval($post['city']);
                $pincode = intval($post['pin']);
                $loan_amount = intval($post['loan_amount']);
                $obligations = intval($post['obligations']);
                $monthly_income = intval($post['monthly_income']);
                $source = strtoupper($post['source']);
                $pancard = strtoupper($post['pan']);
                $utm_source = !empty($post['utm_source']) ? strtoupper($post['utm_source']) : "";
                $utm_campaign = !empty($post['utm_campaign']) ? strtoupper($post['utm_campaign']) : "";
                $gender = strtoupper($post['gender']);

                if (!empty($pincode)) {
                    $result = $this->db->select('*')->where(["m_pincode_value" => $pincode, 'm_pincode_city_id' => $city_id])->from("master_pincode")->get();
                    if ($result->num_rows() == 0) {
                        $pincode = "";
                    }
                }



                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'pancard' => $pancard,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'pincode' => $pincode,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'loan_amount' => $loan_amount,
                    'obligations' => $obligations,
                    'otp' => $otp,
                    'user_type' => 'NEW',
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => $source,
                    'ip' => $post['ip'],
                    'status' => "LEAD-NEW",
                    'stage' => "S1",
                    'lead_status_id' => 1,
                    'qde_consent' => "Y",
                    'lead_data_source_id' => $post['lead_data_source_id'],
                    'coordinates' => ($post['coordinates']) ? $post['coordinates'] : "",
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'promocode' => $coupon,
                );

                if (strtoupper(trim($utm_source)) == "C4C") {
                    $insertDataLeads['lead_data_source_id'] = 21;
                    $insertDataLeads['source'] = 'C4C';
                } else if (strtoupper(trim($utm_source)) == "REFCASE") {
                    $insertDataLeads['lead_data_source_id'] = 27;
                    $insertDataLeads['source'] = 'REFCASE';
                }

                $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                if (!empty($lead_id)) {

                    $insertLeadsCustomer = array(
                        'customer_lead_id' => $lead_id,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'mobile' => $mobile,
                        'alternate_mobile' => $alternate_mobile,
                        'email' => $email,
                        'alternate_email' => $alternate_email,
                        'pancard' => $pancard,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'cr_residence_pincode' => $pincode,
                        'created_date' => created_on
                    );

                    if (!empty($dob)) {
                        $insertLeadsCustomer['dob'] = $dob;
                    }

                    $this->db->insert('lead_customer', $insertLeadsCustomer);

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'emp_email' => $alternate_email,
                        'monthly_income' => $monthly_income,
                        'created_on' => created_on
                    ];

                    $this->db->insert('customer_employment', $insert_customer_employement);

                    if (!empty($pancard)) {
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
                                'current_house' => $cif_result->cif_residence_address_1,
                                'current_locality' => $cif_result->cif_residence_address_2,
                                'current_landmark' => $cif_result->cif_residence_landmark,
                                'current_residence_type' => $cif_result->cif_residence_type,
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

                            $update_customer_employement = [
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
                                'city_id' => $cif_result->cif_office_city_id,
                                'state_id' => $cif_result->cif_office_state_id,
                                'updated_on' => created_on,
                            ];

                            $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                            $update_data_leads = [
                                'customer_id' => $cif_result->cif_number,
                                'user_type' => $user_type,
                                'updated_on' => created_on
                            ];
                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        }
                    }

                    $reference_no = $this->generateReferenceCode($lead_id, $first_name, $sur_name, $mobile);

                    $update_data_leads = [
                        'lead_reference_no' => $reference_no,
                        'updated_on' => created_on
                    ];

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                    $dataSMS = [
                        'lead_id' => $lead_id,
                        'title' => ($gender == "MALE") ? "Mr." : "Ms.",
                        'name' => $first_name,
                        'mobile' => $mobile,
                        'otp' => $otp,
                    ];

                    if (!in_array(strtoupper(trim($utm_source)), array("C4C", "REFCASE", "SANCTION"))) {



                        require_once (COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                        if ($return_eligibility_array['status'] == 2) {
                            return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                        }
                    }

                    if (!in_array(strtoupper(trim($utm_source)), array("C4C", "REFCASE", "SANCTION"))) {
                        $this->sendOTPToCustomer($dataSMS);
                    }

                    $return_data = ['Status' => 1, 'Message' => 'Record Save Successfully.', 'ApplicationNo' => $lead_id];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                } else {
                    $return_data = ['Status' => 0, 'Message' => 'Server not responding your request.'];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }
            }
        } else {
            $return_data = ['Status' => 0, 'Message' => 'Request Method Post Failed.'];
            if (!empty($insert_log_id)) {
                $data_array = array();
                $data_array['mapp_lead_id'] = !empty($lead_id) ? $lead_id : "";
                $data_array['mapp_api_status_id'] = 2;
                $data_array['mapp_response'] = json_encode($return_data);
                $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                $this->insertUpdateAppLog($data_array, $insert_log_id);
            }
            return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
        }
    }

    public function SendOtpForLoanStatus_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $otp = rand(1000, 9999);
            $mobile = $post['mobile'];
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|min_length[10]");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $result = $this->db->select('lead_id,sur_name,mobile,pancard')
                        ->where('mobile', $mobile)
                        ->from("leads")
                        ->get();

                if ($result->num_rows() > 0) {
                    foreach ($result->result() as $row) {
                        $lead_id = $row->lead_id;
                        $sur_name = $row->sur_name;
                        $mobile = $row->mobile;
                        $pancard = $row->pancard;
                    }

                    $dataSMS = [
                        'lead_id' => $lead_id,
                        'title' => ($post['gender'] == "MALE" || $post['gender'] == "Male") ? "Mr." : "Ms.",
                        'name' => $sur_name,
                        'mobile' => $mobile,
                        'otp' => $otp,
                    ];
                    // print_r($dataSMS); exit;
                    $this->sendOTPToCustomer($dataSMS);
                    $update = $this->db->set('otp', $otp)->where('lead_id', $lead_id)->where('mobile', $mobile)->update('leads');
                    if ($update) {
                        json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $row], REST_Controller::HTTP_OK));
                    }
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function SaveEnquiry_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (

                ($headers['Accept'] == "application/json") && ($token['token_Leads'] == $headers['Auth'])

                );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("Name", "First Name", "required|trim|min_length[1]|max_length[40]");

            $this->form_validation->set_rules("Mobile", "Mobile No", "required|trim|numeric|min_length[10]|max_length[10]");

            $this->form_validation->set_rules("Email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            $this->form_validation->set_rules("Professional_status", "Professional Status", "required|trim");

            $this->form_validation->set_rules("Salary_mode", "Salary Mode", "required|trim");

            $this->form_validation->set_rules("Salary_above_15k", "Salary Above 15k", "required|trim");

            $this->form_validation->set_rules("Salary_range", "Salary Range", "required|trim");

            $this->form_validation->set_rules("Loan_amount", "Loan Amount", "required|trim");

            $this->form_validation->set_rules("State_id", "State ID", "required|trim|numeric");

            $this->form_validation->set_rules("City", "City", "required|trim");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $data = array(
                    'Name' => strtoupper($post['Name']),
                    'Mobile' => $post['Mobile'],
                    'Email' => strtoupper($post['Email']),
                    'Professional_status' => $post['Professional_status'],
                    'Salary_mode' => $post['Salary_mode'],
                    'Salary_above_15k' => $post['Salary_above_15k'],
                    'Salary_range' => $post['Salary_range'],
                    'Loan_amount' => $post['Loan_amount'],
                    'State_id' => $post['State_id'],
                    'City' => strtoupper($post['City']),
                );

                if ($this->db->insert('Contact_Enquiry', $data)) {

                    json_encode($this->response(['Status' => 1, 'Message' => 'Record Save Successfully.']));
                } else {

                    json_encode($this->response(['Status' => 0, 'Message' => 'Server not responding your request.']));
                }
            }
        } else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.']));
        }
    }

    public function sendOTPToCustomer($data) {

        // 		$this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric|is_natural");
        //         if($this->form_validation->run() == FALSE)
        //         {
        //             $this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_OK);
        //         }
        //         else
        //         {

        define('smsusername', urlencode("namanfinl"));

        define('smspassword', urlencode("ASX1@#SD"));

        define('entityid', 1201159134511282286);

        $lead_id = $data['lead_id'];

        $title = $data['title'];

        $name = $data['name'];

        $mobile = $data['mobile'];

        $otp = $data['otp'];

        $message = "Dear " . $title . " " . $name . ",\nYour mobile verification\nOTP is: " . $otp . ".\nPlease don't share it with anyone - LW (Naman Finlease)";

        $username = smsusername;

        $password = smspassword;

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

        if ($output == true) {

            return true;
        } else {

            return false;
        }

        // }
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

    public function resendAppliedCustomerOTP_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (

                ($headers['Accept'] == "application/json") && ($token['token_Leads'] == $headers['Auth'])

                );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $query = $this->db->select('LD.customer_id, LD.lead_id, LD.name, LD.gender, LD.mobile')
                        ->where('LD.lead_id', $post['lead_id'])
                        ->from(Tbl_leads . ' LD')
                        ->get();

                if ($query->num_rows() > 0) {

                    $row = $query->row();

                    $otp = rand(1000, 9999);

                    $data = [
                        'lead_id' => $row->lead_id,
                        'title' => ($row->gender == "MALE" || $row->gender == "Male") ? "Mr." : "Ms.",
                        'mobile' => $row->mobile,
                        'otp' => $otp,
                    ];

                    $this->db->where('lead_id', $post['lead_id'])->update(Tbl_leads . ' LD', ['otp' => $otp]);

                    $this->sendOTPToCustomer($data);

                    json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => "OTP Send Successfully"], REST_Controller::HTTP_OK));
                } else {

                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //Used for Loanwalle.in AND Paisa On Time

    public function saveLendingPageOTPVerify_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $query = $this->db->select('LD.customer_id,LD.lead_id,LD.first_name as name, LD.pancard')
                        ->where('LD.lead_id', $post['lead_id'])
                        ->where('LD.otp', $post['otp'])
                        ->from('leads LD')
                        ->get();

                if ($query->num_rows() > 0) {
                    $row = $query->row();
                    $data = [
                        'customer_id' => ($row->customer_id) ? $row->customer_id : 0,
                        'lead_id' => $row->lead_id,
                        'gender' => '',
                        'pancard' => $row->pancard,
                    ];

                    $update_data_leads = [
                        'lead_is_mobile_verified' => 1,
                        'updated_on' => created_on
                    ];

                    $this->db->where('lead_id', $post['lead_id'])->update('leads', $update_data_leads);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function verifyAppliedCustomerOTP_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $query = $this->db->select('LD.customer_id,LD.lead_id,LD.first_name as name, LD.pancard')
                        ->where('LD.lead_id', $post['lead_id'])
                        ->where('LD.otp', $post['otp'])
                        ->from('leads LD')
                        ->get();

                if ($query->num_rows() > 0) {
                    $row = $query->row();
                    $data = [
                        'customer_id' => ($row->customer_id) ? $row->customer_id : 0,
                        'lead_id' => $row->lead_id,
                        'gender' => '',
                        'pancard' => $row->pancard,
                    ];

                    $update_data_leads = [
                        'lead_is_mobile_verified' => 1,
                        'updated_on' => created_on
                    ];

                    $this->db->where('lead_id', $post['lead_id'])->update('leads', $update_data_leads);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function updateCustomerDetails($customer_id, $data) {

        return $this->db->where('customer_id', $customer_id)->update('customer', $data);
    }

    public function getSaveLeads_post() {
//
//        error_reporting(E_ALL);
//        ini_set('disnplay_errors', 1);

        $input_data = file_get_contents("php://input");

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural");

            if ($this->form_validation->run() == FALSE) {

                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

//                $result = $this->db->select('LD.lead_id, LD.company_id, LD.customer_id, LD.loan_amount, LD.monthly_income, LD.obligations, LD.name, LD.gender, LD.dob, LD.pancard, LD.mobile, LD.alternateMobileNo, LD.email, LD.alternateEmailAddress, LD.status, ST.state, LD.city, LD.pincode, LD.created_on')
//                        ->where('LD.lead_id', $post['lead_id'])
//                        ->from("leads LD")
//                        ->join('tbl_state ST', 'ST.old_state_id = LD.state_id', 'left')
//                        ->get();

                $select = 'LD.lead_id, LD.company_id, LD.customer_id, LD.loan_amount, CE.monthly_income, LD.obligations, concat_ws(" ",C.first_name,C.middle_name, C.sur_name) as name, C.gender, C.dob, LD.pancard, LD.mobile, C.alternate_email as alternateEmailAddress, C.alternate_mobile as alternateMobileNo, LD.email, ST.m_state_name as state, CT.m_city_name as city, LD.pincode, LD.status, LD.created_on';

                $this->db->select($select);
                $this->db->from('leads LD');
                $this->db->join('master_state ST', 'ST.m_state_id = LD.state_id', 'left');
                $this->db->join('master_city CT', 'CT.m_city_id = LD.city_id', 'left');
                $this->db->join('lead_customer C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'left');
                $this->db->join('customer_employment CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
                $this->db->where(['LD.lead_id' => $lead_id]);

                $result = $this->db->order_by('LD.lead_id', 'desc')->get();

                if ($result->num_rows() > 0) {

                    $data = $result->row();

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {

                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    ////Old mobile active methods
    public function userRegistration_post() {//first mobile input via mobile apps/ resent otp also
        $return_data = "";
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        return json_encode($this->response(['Status' => 0, 'Message' => 'Please update your application.'], REST_Controller::HTTP_OK));
        exit;
        $data_array = array();
        $data_array['mapp_data_source_str'] = !empty($post['source']) ? $post['source'] : "NA";
        $data_array['mapp_action_name'] = "userRegistration_post";
        $data_array['mapp_browser_info'] = !empty($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "NA";
        $data_array['mapp_browser_info'] = $data_array['mapp_browser_info'] . " | " . $_SERVER["REQUEST_URI"];
        $data_array['mapp_browser_info'] = addslashes($data_array['mapp_browser_info']);
        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
        $data_array['mapp_api_status_id'] = 1;
        $data_array['mapp_request'] = json_encode($post);
        $data_array['mapp_request_datetime'] = date("Y-m-d H:i:s");

        $insert_log_id = $this->insertUpdateAppLog($data_array);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {
                $return_data = ['Status' => 0, 'Message' => validation_errors()];
                if (!empty($insert_log_id)) {
                    $data_array = array();
                    $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                    $data_array['mapp_api_status_id'] = 2;
                    $data_array['mapp_response'] = json_encode($return_data);
                    $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                    $this->insertUpdateAppLog($data_array, $insert_log_id);
                }
                return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
            } else {

                $mobile = $post['mobile'];

                $otp = rand(1000, 9999);

                if ($mobile == "9953931000") {//Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") {//Hardcoded otp testing... donot remove
                    $otp = 1989;
                }
                $lead_id = 0;

                if (!empty($post['customer_id'])) {
                    $query = $this->db->select('lead_id')->where('lead_id', $post['customer_id'])->from('leads')->get();
                    if ($query->num_rows() > 0) {
                        $result1 = $query->row();
                        $lead_id = $result1->lead_id;
                    }
                }

                $insertDataLeads = array(
                    'mobile' => $mobile,
                    'otp' => $otp,
                    'user_type' => 'NEW',
                    'lead_entry_date' => created_date,
                    'created_on' => created_on,
                    'source' => $post['source'],
                    'ip' => $post['ip'],
                    'status' => "LEAD-NEW",
                    'stage' => "S1",
                    'lead_status_id' => 1,
                    'qde_consent' => "Y",
                    'coordinates' => ($post['coordinates']) ? $post['coordinates'] : "",
                    'utm_source' => ($post['utm_source'] ? $post['utm_source'] : ''),
                    'utm_campaign' => ($post['utm_campaign'] ? $post['utm_campaign'] : ''),
                );

                if (trim($post['source']) == "AppLoan4Smile") {
                    $insertDataLeads['lead_data_source_id'] = 8;
                    $insertDataLeads['source'] = 'AppLoan4Smile';
                } else if (trim($post['source']) == "AppLoanwalle") {
                    $insertDataLeads['lead_data_source_id'] = 2;
                    $insertDataLeads['source'] = 'AppLoanwalle';
                } else {
                    $insertDataLeads['lead_data_source_id'] = 8;
                    $insertDataLeads['source'] = 'AppLoan4Smile';
                }

                if (!empty($lead_id)) {
                    $this->db->where('lead_id', $lead_id)->update('leads', $insertDataLeads);
                } else {
                    $this->db->insert('leads', $insertDataLeads);
                    $lead_id = $this->db->insert_id();
                }

                if (!$lead_id) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Some error occurred due to data set. Please try again."], REST_Controller::HTTP_OK));
                }

                $insertLeadsCustomer = array(
                    'customer_lead_id' => $lead_id,
                    'mobile' => $mobile,
                    'created_date' => created_on
                );

                $query = $this->db->select('customer_seq_id')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

                if ($query->num_rows() > 0) {
                    $result1 = $query->row();
                    $customer_seq_id = $result1->customer_seq_id;
                    $this->db->where('customer_seq_id', $customer_seq_id)->update('lead_customer', $insertLeadsCustomer);
                } else {
                    $this->db->insert('lead_customer', $insertLeadsCustomer);
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

                $this->db->insert('leads_otp_trans', $insertDataOTP);

                $lead_otp_id = $this->db->insert_id();

                $this->sendOTPForUserRegistrationVerification($data);

                if (!empty($lead_id)) {

                    $return_data = ['Status' => 1, 'Message' => 'Success.', 'customer_id' => $lead_id];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_customer_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                } else {

                    $return_data = ['Status' => 0, 'Message' => 'Failed.'];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_customer_id'] = !empty($lead_id) ? $lead_id : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }

                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }
            }
        } else {
            $return_data = ['Status' => 0, 'Message' => 'Request Method Post Failed.'];
            if (!empty($insert_log_id)) {
                $data_array = array();
                $data_array['mapp_customer_id'] = !empty($lead_id) ? $lead_id : "";
                $data_array['mapp_api_status_id'] = 2;
                $data_array['mapp_response'] = json_encode($return_data);
                $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                $this->insertUpdateAppLog($data_array, $insert_log_id);
            }
            return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
        }
    }

    public function userVerification_post() {//Old mobile active methods
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        $data_array = array();
        $data_array['mapp_data_source_str'] = !empty($post['source']) ? $post['source'] : "NA";
        $data_array['mapp_action_name'] = "userVerification_post";
        $data_array['mapp_browser_info'] = !empty($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "NA";
        $data_array['mapp_browser_info'] = $data_array['mapp_browser_info'] . " | " . $_SERVER["REQUEST_URI"];
        $data_array['mapp_browser_info'] = addslashes($data_array['mapp_browser_info']);
        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
        $data_array['mapp_customer_id'] = !empty($post['customer_id']) ? $post['customer_id'] : "";
        $data_array['mapp_api_status_id'] = 1;
        $data_array['mapp_request'] = json_encode($post);
        $data_array['mapp_request_datetime'] = date("Y-m-d H:i:s");

        $insert_log_id = $this->insertUpdateAppLog($data_array);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");

            $this->form_validation->set_rules("customer_id", "Customer ID", "required|trim");

            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {
                $return_data = ['Status' => 0, 'Message' => validation_errors()];

                if (!empty($insert_log_id)) {
                    $data_array = array();
                    $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                    $data_array['mapp_api_status_id'] = 2;
                    $data_array['mapp_response'] = json_encode($return_data);
                    $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                    $this->insertUpdateAppLog($data_array, $insert_log_id);
                }

                return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['customer_id'];
                $otp = $post['otp'];

                $query = $this->db->select('lead_id,first_name,mobile,email,lead_status_id,city_id, state_id')->where('lead_id', $lead_id)->from('leads')->get();

                $result = $query->row();
                $existing_lead_id = $result->lead_id;
                $lead_status_id = $result->lead_status_id;
                $city_id = $result->city_id;
                $state_id = $result->state_id;
                $first_name = $result->first_name;
                $email = $result->email;
                $mobile = $result->mobile;

                $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                if ($existing_lead_id != $lead_id) {
                    $return_data = ['Status' => 0, 'Message' => 'Invalid access for the application.'];
                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }


                if ($lead_status_id > 1) {
                    $return_data = ['Status' => 0, 'Message' => 'Your application has been moved to next step.'];
                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }
                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }

                $last_row = $this->db->select('lot_id,lot_mobile_otp')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();
                $lastotp = $last_row->lot_mobile_otp;
                $lot_id = $last_row->lot_id;

                if ($lastotp != $otp) {

                    $return_data = ['Status' => 0, 'Message' => 'OTP verification failed. Please try again.'];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }

                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }

                $cif_query = $this->db->select('*')->where('cif_mobile', $mobile)->from('cif_customer')->get();

                if ($cif_query->num_rows() > 0) {
                    $cif_result = $cif_query->row();

                    $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;
                    if ($isdisbursedcheck > 0) {
                        $user_type = "REPEAT";
                    } else {
                        $user_type = "NEW";
                    }

                    $gender = "MALE";

                    if ($cif_result->cif_gender == 2) {
                        $gender = "FEMALE";
                    }

                    $update_data_lead_customer = [
                        'mobile_verified_status' => "YES",
                        'first_name' => $cif_result->cif_first_name,
                        'middle_name' => $cif_result->cif_middle_name,
                        'sur_name' => $cif_result->cif_sur_name,
                        'gender' => $gender,
                        'dob' => $cif_result->cif_dob,
                        'pancard' => $cif_result->cif_pancard,
                        'alternate_email' => $cif_result->cif_office_email,
                        'alternate_mobile' => $cif_result->cif_alternate_mobile,
                        'current_house' => $cif_result->cif_residence_address_1,
                        'current_locality' => $cif_result->cif_residence_address_2,
                        'current_landmark' => $cif_result->cif_residence_landmark,
                        'current_residence_type' => $cif_result->cif_residence_type,
                        'state_id' => $cif_result->cif_residence_state_id,
                        'city_id' => $cif_result->cif_residence_city_id,
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
                        'state_id' => $cif_result->cif_residence_state_id,
                        'city_id' => $cif_result->cif_residence_city_id,
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
                        'state_id' => $cif_result->cif_office_state_id,
                        'city_id' => $cif_result->cif_office_city_id,
                    ];

                    if (!empty($emp_id)) {
                        $insert_customer_employement['updated_on'] = created_on;
                        $this->db->where('id', $emp_id)->update('customer_employment', $insert_customer_employement);
                    } else {
                        $insert_customer_employement['created_on'] = created_on;
                        $this->db->insert('customer_employment', $insert_customer_employement);
                    }

                    $update_lead_otp_trans_data = [
                        'lot_otp_verify_time' => created_on,
                        'lot_otp_verify_flag' => 1,
                    ];

                    $this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);

                    $update_data_leads['lead_is_mobile_verified'] = 1;

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                    $Customer_data = [
                        'first_name' => $first_name,
                        'middle_name' => $cif_result->cif_middle_name,
                        'sur_name' => $cif_result->cif_sur_name,
                        'gender' => $cif_result->cif_gender,
                        'dob' => date("d-m-Y", strtotime($cif_result->cif_dob)),
                        'pancard' => $cif_result->cif_pancard,
                        'email' => $email,
                        'alternate_email' => $cif_result->cif_office_email,
                        'mobile' => $cif_result->cif_mobile,
                        'alternate_mobile' => $cif_result->cif_alternate_mobile,
                    ];

                    $Leads_data = [
                        'first_name' => $first_name,
                        'mobile' => $mobile,
                        'email' => $email,
                    ];

                    $return_data = ['Status' => 1, 'Message' => 'OTP Verified.', 'Customer_data' => $Customer_data, 'customer_id' => $lead_id, 'Leads_data' => $Leads_data];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }

                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                } else {

                    $Customer_data = [
                        'first_name' => $first_name,
                        'middle_name' => '',
                        'sur_name' => '',
                        'gender' => '',
                        'dob' => '',
                        'pancard' => '',
                        'email' => $email,
                        'alternate_email' => '',
                        'mobile' => $mobile,
                        'alternate_mobile' => '',
                    ];

                    $Leads_data = [
                        'first_name' => $first_name,
                        'mobile' => $mobile,
                        'email' => $email,
                    ];

                    $update_lead_otp_trans_data = [
                        'lot_otp_verify_time' => created_on,
                        'lot_otp_verify_flag' => 1,
                    ];

                    $update_data_lead_customer = [
                        'mobile_verified_status' => "YES",
                        'updated_at' => created_on
                    ];

                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                    $this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);
                    $this->db->set('lead_is_mobile_verified', 1)->where('lead_id', $lead_id)->update('leads');

                    $return_data = ['Status' => 1, 'Message' => 'OTP Verified.', 'Customer_data' => $Customer_data, 'customer_id' => $lead_id, 'Leads_data' => $Leads_data];

                    if (!empty($insert_log_id)) {
                        $data_array = array();
                        $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                        $data_array['mapp_api_status_id'] = 2;
                        $data_array['mapp_response'] = json_encode($return_data);
                        $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                        $this->insertUpdateAppLog($data_array, $insert_log_id);
                    }

                    return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
                }
            }
        } else {

            $return_data = ['Status' => 0, 'Message' => 'Request Method Post Failed.'];

            if (!empty($insert_log_id)) {
                $data_array = array();
                $data_array['mapp_mobile'] = !empty($post['mobile']) ? $post['mobile'] : "";
                $data_array['mapp_api_status_id'] = 2;
                $data_array['mapp_response'] = json_encode($return_data);
                $data_array['mapp_response_datetime'] = date("Y-m-d H:i:s");
                $this->insertUpdateAppLog($data_array, $insert_log_id);
            }

            return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
        }
    }

    public function sendOTPForUserRegistrationVerification($data) {

        $mobile = $data['mobile'];

        $otp = $data['otp'];

        $message = "Dear Mr/Ms User,\nYour mobile verification\nOTP is: " . $otp . ".\nPlease don't share it with anyone - LW (Naman Finlease)";

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

    public function getCustomerDocument_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }



        $headers = $this->input->request_headers();

        $token = $this->_token();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($token['token_Docs'] == $headers['Auth'])) {

            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("customer_id", "Customer ID", "required|trim|regex_match[/^[a-zA-Z0-9]+$/]");

            $this->form_validation->set_rules("pan_no", "PAN No", "required|trim|min_length[10]|max_length[10]|regex_match[/^[a-zA-Z0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $query = $this->db->select("D.docs_id, D.lead_id, D.company_id, D.customer_id, D.pancard, D.docs, D.type, D.pwd, D.file, D.created_on ")
                        ->where('D.pancard', $post['pan_no'])
                        ->from(Tbl_docs . " D")
                        ->order_by('D.docs_id', 'desc')
                        ->get();

                if ($query->num_rows() > 0) {

                    $result1 = $query->result();

                    json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'Data' => $result1], REST_Controller::HTTP_OK));
                } else {

                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Invalid Request, Auth Failed. Try Again!'], REST_Controller::HTTP_OK));
        }
    }

    public function saveCustomerDocument_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|regex_match[/^[0-9]+$/]");

            $this->form_validation->set_rules("company_id", "Company ID", "required|trim|regex_match[/^[0-9]+$/]");

            $this->form_validation->set_rules("customer_id", "Customer ID", "required|trim|regex_match[/^[a-zA-Z0-9]+$/]");

            $this->form_validation->set_rules("pan_no", "PAN No", "required|trim|regex_match[/^[a-zA-Z0-9]+$/]");

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|regex_match[/^[0-9]+$/]");

            $this->form_validation->set_rules("proof", "Proof For KYC", "required|trim");

            $this->form_validation->set_rules("docs_type", "Docs Type", "required|trim");

            $this->form_validation->set_rules("password", "Password", "trim");

            $this->form_validation->set_rules("file", "File", "required|trim");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                // echo "<pre>"; print_r($post['docs_type']); exit;

                date_default_timezone_set("Asia/Calcutta");

                $image_name = date("dmYHis") . "_" . $post['lead_id'] . "_" . $post['customer_id'];

                $ext = '.jpeg';

                if ($post['docs_type'] == 'Bank Statement') {

                    $ext = '.pdf';
                } else if ($post['docs_type'] == 'Bankstatement') {//fixed on 2021-12-15
                    $post['docs_type'] = 'Bank Statement';
                    $ext = '.pdf';
                }

                $image_ext = rand(microtime(true) * 1000) . $ext;

                $imgUrl = $image_name . $image_ext;

                $image_upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/upload/" . $imgUrl;

                $flag = file_put_contents($image_upload_dir, base64_decode($post['file']));

                if ($flag) {

                    $data = [
                        "lead_id" => $post['lead_id'],
                        "company_id" => $post['company_id'],
                        "customer_id" => $post['customer_id'],
                        "pancard" => $post['pan_no'],
                        "mobile" => $post['mobile'],
                        "docs" => $post['proof'],
                        "type" => $post['docs_type'],
                        "pwd" => $post['password'],
                        "file" => $imgUrl,
                        "created_on" => created_on
                    ];

                    $result = $this->db->insert(Tbl_docs, $data);

                    $docsId = $this->db->insert_id();

                    json_encode($this->response(['Status' => 1, 'Message' => 'Docs Uploaded Successfully.', 'docs_id' => $docsId], REST_Controller::HTTP_OK));
                } else {

                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed to save Docs. Try Again'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getUserImage_post() {



        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (

                ($headers['Accept'] == "application/json") && ($token['token_Leads'] == $headers['Auth'])

                );

        if (isset($_FILES["file"]["name"]) && $_POST['id'] != '') {



            $uploaddir = 'upload/';

            $path = $_FILES['file']['name'];

            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $user_file = time() . rand() . '.' . $ext;

            if ($ext == 'pdf') {

                $uploadfile = $uploaddir . $user_file;

                if ($_FILES["file"]["name"]) {

                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadfile)) {

                        $arr = array('success' => "true", 'msg' => 'Image has been uploaded successfully');

                        $data = array(
                            'uid' => $_POST['id'],
                            'file' => $uploadfile
                        );

                        if ($this->db->insert('docs', $data)) {

                            $arr = array('success' => "true", 'msg' => 'Data inserted');
                        } else {

                            $arr = array('success' => "false", 'msg' => 'Something went Wrong');
                        }
                    } else {

                        $arr = array('success' => "false", 'msg' => 'Image has not uploaded');
                    }



                    echo json_encode($arr);
                }
            } else {

                $arr = array('success' => "false", 'msg' => 'Please Select PDF Only');

                echo json_encode($arr);
            }
        } else {

            $arr = array('success' => "false", 'msg' => 'Parameters are missing');

            echo json_encode($arr);
        }
    }

    private function generateReferenceCode($lead_id, $first_name, $last_name, $mobile) {

        $code_mix = array($lead_id[rand(0, strlen($lead_id) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $mobile[rand(0, strlen($mobile) - 1)], $mobile[rand(0, strlen($mobile) - 1)]);

        shuffle($code_mix);

        $referenceID = "#LW";

        foreach ($code_mix as $each) {

            $referenceID .= $each;
        }

        $referenceID = str_replace(" ", "X", $referenceID);

        $referenceID = strtoupper($referenceID);

        return $referenceID;
    }

    private function insertUpdateAppLog($data_array, $log_id = 0) {

        if (!empty($log_id)) {
            $log_id = $this->db->where('mapp_log_id', $log_id)->update('api_mobile_app_logs', $data_array);
        } else {
            $this->db->insert('api_mobile_app_logs', $data_array);
            $log_id = $this->db->insert_id();
        }

        return $log_id;
    }

}

?>
