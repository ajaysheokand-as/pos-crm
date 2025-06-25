<?php

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class InstantJourneyController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        // Remove any existing CORS headers
    header_remove("Access-Control-Allow-Origin");

        $this->load->model('Instant_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('updated_on', date('Y-m-d H:i:s'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }

    public $razorpay_url = "";
    public $mobile_app_source_id = 1;
    public $commonComponent = null;
    public $journey_type_id = 2;
    public $source_name = 'ORGANIC';
    public $data_source_id = 4;
    // public $whitelisted_numbers = array(9170004606, 9289767308, 9717708655, 8279750539);
    public $whitelisted_numbers = [];

    public function instantAppVersionCheck_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $token_auth = $this->_token();

        $token = $token_auth['token_android'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("version", "Version", "required");
            $this->form_validation->set_rules("app_name", "App Name", "required");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {
                $version = $post['version'];
                $app_name = $post['app_name'];
                $app_store_id = NULL;

                $current_version = '2.0.0';

                if (in_array($app_name, array("Speedoloan"))) {

                    if ($version == $current_version) { // || $version == '3.0.0'
                        return json_encode($this->response(['Status' => 1, 'Message' => "Success", 'version' => $version, "app_store_id" => $app_store_id, "company_name" => COMPANY_NAME], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 2, 'Message' => "Please update the new version", 'version' => $version, "app_store_id" => $app_store_id], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 2, 'Message' => "Invalid App Name", 'version' => $version, "app_store_id" => $app_store_id], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 4, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function appCustomerRegisteration_post() {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $this->commonComponent = new CommonComponent();

        $apiStatusData = NULL;
        $apiStatusMessage = '';
        $apiStatusId = NULL;

        $response_array = array();
        $request_array = array();
        $get_cif_customer = array();
        $update_pje_journey = array();
        $update_customer_profile = array();
        $pancard = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $page_name = $post["event_name"];
        $is_existing_customer = ((!empty($post['is_existing_customer']) && $post['is_existing_customer'] == 1) ? 1 : 2);
        $cust_profile_id = (!empty($post["profile_id"]) ? $this->encrypt->decode($post["profile_id"]) : NULL);
        $mobile = (!empty($post['mobile']) ? $post['mobile'] : NULL);
        $pancard = (!empty($post['pancard']) ? strtoupper($post['pancard']) : NULL);
        $first_name = (!empty($post['first_name']) ? strtoupper($post['first_name']) : NULL);
        $middle_name = (!empty($post['middle_name']) ? strtoupper($post['middle_name']) : NULL);
        $sur_name = (!empty($post['sur_name']) ? strtoupper($post['sur_name']) : NULL);
        $dob = (!empty($post['dob']) ? date("Y-m-d", strtotime($post['dob'])) : NULL);
        $gender = (!empty($post['gender']) ? $post['gender'] : NULL);
        $geo_lat = (!empty($post['geo_lat']) ? $post['geo_lat'] : NULL);
        $geo_long = (!empty($post['geo_long']) ? $post['geo_long'] : NULL);
        $pincode = (!empty($post['residence_pincode']) ? $post['residence_pincode'] : NULL);
        $city_id = (!empty($post['residence_city_id']) ? $post['residence_city_id'] : NULL);
        $state_id = (!empty($post['residence_state_id']) ? $post['residence_state_id'] : NULL);
        $residence_type_id = (!empty($post["residence_type_id"]) ? $post["residence_type_id"] : NULL);
        $residence_address_1 = (!empty($post["residence_address_1"]) ? strtoupper($post["residence_address_1"]) : NULL);
        $residence_address_2 = (!empty($post["residence_address_2"]) ? strtoupper($post["residence_address_2"]) : NULL);
        $residence_landmark = (!empty($post["residence_landmark"]) ? strtoupper($post["residence_landmark"]) : NULL);
        $personal_email = (!empty($post["personal_email"]) ? strtoupper($post["personal_email"]) : NULL);
        $lead_id = (!empty($post["lead_id"]) ? $this->encrypt->decode($post["lead_id"]) : NULL);
        $income_type_id = !empty($post['income_type_id']) ? $post['income_type_id'] : NULL;
        $monthly_income = !empty($post['monthly_income']) ? $post['monthly_income'] : NULL;
        $salary_mode_id = !empty($post['salary_mode_id']) ? $post['salary_mode_id'] : NULL;
        $salary_date = !empty($post['salary_date']) ? $post['salary_date'] : NULL;
        $marital_status = !empty($post['marital_status_id']) ? $post['marital_status_id'] : NULL;
        $spouse_name = !empty($post['spouse_name']) ? strtoupper($post['spouse_name']) : NULL;
        $spouse_mobile = !empty($post['spouse_mobile']) ? strtoupper($post['spouse_mobile']) : NULL;
        $otp = !empty($post['otp']) ? $post['otp'] : NULL;
        $file = !empty($post['file']) ? $post['file'] : NULL;
        $ext = !empty($post['file_ext']) ? $post['file_ext'] : NULL;
        $fcm_token = !empty($post['fcm_token']) ? $post['fcm_token'] : NULL;
        $utm_source = !empty($post['utm_source']) ? strtoupper($post['utm_source']) : $this->source_name;
        $utm_medium = !empty($post['utm_medium']) ? strtoupper($post['utm_medium']) : $this->source_name;
        $utm_campaign = !empty($post['utm_campaign']) ? strtoupper($post['utm_campaign']) : $this->source_name;
        $utm_term = !empty($post['utm_term']) ? strtoupper($post['utm_term']) : $this->source_name;
        $appfyler_uid = !empty($post['appfyler_uid']) ? $post['appfyler_uid'] : NULL;
        $appfyler_advertiser_id = !empty($post['appfyler_advertiser_id']) ? $post['appfyler_advertiser_id'] : NULL;
        $obligations = !empty($post['obligations']) ? $post['obligations'] : 0;
        $device_id = !empty($post['device_id']) ? $post['device_id'] : NULL;

        if (!empty($ext)) {
            if (strpos($ext, ".") == false) {
                $ext = str_replace(".", "", $ext);
            }
        }

        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];
        $browser = $this->agent->browser();

        $temp_post = $post;
        if (isset($temp_post['file'])) {
            $temp_post['file'] = "File Base64 Format";
        }

        $insert_log_array = array();
        $insert_log_array['mapp_ip_address'] = $ip_address;
        $insert_log_array['mapp_browser_info'] = $browser;
        $insert_log_array['mapp_action_name'] = $page_name;
        $insert_log_array['mapp_profile_id'] = $cust_profile_id;
        $insert_log_array['mapp_request'] = json_encode($temp_post);

        $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

        $error_message['required'] = "%s is required!";
        $error_message['alpha_numeric'] = "%s must contain letters and numbers only!";
        $error_message['numeric'] = "%s must contain numbers only!";
        
        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            if (empty($page_name)) {
                throw new ErrorException("Invalid Page Access!");
            }

            $token_verification = $this->checkAuthorization($headers, $page_name);


            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            switch ($page_name) {

                case "login":

                    $insert_profile_flag = 0;

                    if ($is_existing_customer == 2) { // NO
                        $error_message['min_length[10]'] = "%s must contain 10 digits!";

                        $this->form_validation->set_data($post);
                        $this->form_validation->set_rules("mobile", "Mobile No", "required|numeric|min_length[10]|max_length[10]", $error_message);

                        if ($this->form_validation->run() == FALSE) {
                            throw new ErrorException(strip_tags(validation_errors()));
                        }

                        $search_by = $mobile;

                        $search_type = 1;
                    }

                    $check_existing_profile = $this->Tasks->get_customer_profile_details($search_by, $search_type);

                    if ($check_existing_profile['status'] != 1) {
                        $insert_profile_flag = 1;
                    } else {
                        $cust_profile_id = $check_existing_profile['customer_profile_details']['cp_id'];
                        $cp_data_source_id = !empty($check_existing_profile['customer_profile_details']['cp_data_source_id']) ? $check_existing_profile['customer_profile_details']['cp_data_source_id'] : 0;
                        $cp_utm_source = !empty($check_existing_profile['customer_profile_details']['cp_utm_source']) ? $check_existing_profile['customer_profile_details']['cp_utm_source'] : "";
                        $cp_device_id = !empty($check_existing_profile['customer_profile_details']['cp_device_id']) ? $check_existing_profile['customer_profile_details']['cp_device_id'] : NULL;
                    }

                    if (false) {

                        $get_stored_device_id = $this->Tasks->selectdata(['cp_device_id' => $device_id], 'cp_device_id,cp_mobile', 'customer_profile');

                        if ($get_stored_device_id->num_rows() > 0) {
                            $stored_device_id = $get_stored_device_id->row_array();
                            if (($stored_device_id['cp_device_id'] == $device_id) && ($mobile != $stored_device_id['cp_mobile'])) {
                                throw new ErrorException("You are already registered with us.");
                            }
                        }
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if ($insert_profile_flag == 1) {
                        $insert_customer_profile = array();
                        $insert_customer_profile['cp_mobile'] = $mobile;
                        $insert_customer_profile['cp_geo_lat'] = $geo_lat;
                        $insert_customer_profile['cp_geo_long'] = $geo_long;
                        $insert_customer_profile['cp_login_geo_lat'] = $geo_lat;
                        $insert_customer_profile['cp_login_geo_long'] = $geo_long;
                        $insert_customer_profile['cp_is_journey_completed'] = 0;
                        $insert_customer_profile['cp_journey_type_id'] = $this->journey_type_id;
                        $insert_customer_profile['cp_journey_stage'] = $journey_id;
                        $insert_customer_profile['cp_data_source_id'] = $this->data_source_id;
                        $insert_customer_profile['cp_utm_source'] = $utm_source;
                        $insert_customer_profile['cp_utm_medium'] = $utm_medium;
                        $insert_customer_profile['cp_utm_campaign'] = $utm_campaign;
                        $insert_customer_profile['cp_utm_term'] = $utm_term;
                        $insert_customer_profile['cp_fcm_token'] = $fcm_token;
                        $insert_customer_profile['cp_adjust_adid'] = $appfyler_uid;
                        $insert_customer_profile['cp_adjust_gps_adid'] = $appfyler_advertiser_id;
                        $insert_customer_profile['cp_ip_address'] = $ip_address;
                        $insert_customer_profile['cp_app_src_id'] = $this->mobile_app_source_id;
                        $insert_customer_profile['cp_created_at'] = date("Y-m-d H:i:s");
                        $insert_customer_profile['cp_device_id'] = $device_id;
                        $this->Tasks->insert("customer_profile", $insert_customer_profile);
                        $cust_profile_id = $this->db->insert_id();
                        $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, "Customer Profile Created");
                    } else {

                        $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                        if ($check_page_status == false) {
                            $update_customer_profile['cp_journey_stage'] = $journey_id;
                        }

                        $update_customer_profile['cp_login_fcm_token'] = $fcm_token;
                        $update_customer_profile['cp_login_geo_lat'] = $geo_lat;
                        $update_customer_profile['cp_login_geo_long'] = $geo_long;
                        $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                        $update_customer_profile['cp_device_id'] = $device_id;

                        if ($cp_data_source_id != $this->data_source_id) {
                            $update_customer_profile['cp_data_source_id'] = $this->data_source_id;
                            $update_customer_profile['cp_app_src_id'] = $this->mobile_app_source_id;
                            $update_customer_profile['cp_utm_source'] = $utm_source;
                            $update_customer_profile['cp_utm_medium'] = $utm_medium;
                            $update_customer_profile['cp_utm_campaign'] = $utm_campaign;
                            $update_customer_profile['cp_utm_term'] = $utm_term;
                            $update_customer_profile['cp_journey_type_id'] = $this->journey_type_id;
                            $update_customer_profile['cp_journey_stage'] = $journey_id;
                            $update_customer_profile['cp_adjust_adid'] = $appfyler_uid;
                            $update_customer_profile['cp_adjust_gps_adid'] = $appfyler_advertiser_id;
                        } else if ($cp_utm_source != $utm_source) {
                            $update_customer_profile['cp_utm_source'] = $utm_source;
                            $update_customer_profile['cp_utm_medium'] = $utm_medium;
                            $update_customer_profile['cp_utm_campaign'] = $utm_campaign;
                            $update_customer_profile['cp_utm_term'] = $utm_term;
                            $update_customer_profile['cp_adjust_adid'] = $appfyler_uid;
                            $update_customer_profile['cp_adjust_gps_adid'] = $appfyler_advertiser_id;
                        }

                        $update_customer_profile["cp_doable_to_application_status"] = 0;

                        $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);
                    }

                    $response_array['cust_profile_id'] = $this->encrypt->encode($cust_profile_id);
                    $response_array['mobile'] = $mobile;
                    // $response_array['appflyer_id'] = $cust_profile_id;
                    $response_array['next_step'] = "otp_verify";
                    $response_array['step_percentage'] = 0;

                    $otp = rand(1111, 9999);

                    if (!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers[0])) {
                        $otp = 7488;
                    }

                    $lead_id = !empty($check_existing_profile['customer_profile_details']['cp_lead_id']) ? $check_existing_profile['customer_profile_details']['cp_lead_id'] : NULL;

                    $insertDataOTP = array(
                        'lot_lead_id' => $lead_id,
                        'lot_profile_id' => $cust_profile_id,
                        'lot_mobile_no' => $mobile,
                        'lot_mobile_otp' => $otp,
                        'lot_mobile_otp_type' => 5,
                        'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
                        'lot_otp_valid_time' => 10,
                    );

                    $this->Tasks->insert('leads_otp_trans', $insertDataOTP);

                    $lead_otp_id = $this->db->insert_id();

                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = "Customer";
                    $sms_input_data['otp'] = $otp;

                    if (!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                        $otp = 7488;
                        $send_otp = array();
                        $send_otp['status'] = 1;
                    } else {
                        $send_otp = $this->commonComponent->payday_sms_api(1, $lead_id, $sms_input_data);
                    }

                    if ($send_otp['status'] != 1) {
                        throw new ErrorException("Mobile number is invalid");
                    }

                    if (empty($lead_otp_id)) {
                        throw new ErrorException("An error occurred!");
                    }

                    $message = "OTP has been sent successfully.";

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);
                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    break;

                case "otp_verify":

                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("otp", "Requried OTP", "required|numeric");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $check_existing_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($check_existing_profile['status'] != 1) {
                        throw new Exception("Customer is not registered!");
                    }

                    $get_existing_customer = $check_existing_profile['customer_profile_details'];

                    $mobile = $get_existing_customer['cp_mobile'];

                    $check_active_loan = $this->Tasks->get_active_loan($get_existing_customer['cp_pancard']);

                    if ($check_active_loan['status'] == 1) {
                        $get_active_loan = $check_active_loan['get_active_loan'];
                        if ($get_active_loan['active_loan_count'] > 0) {
                            $active_loan_flag = 1;
                        }
                    }

                    if (!empty($get_existing_customer['cp_lead_id'])) {
                        $lead_id = $get_existing_customer['cp_lead_id'];
                        if (!empty($lead_id)) {
                            $get_lead_details = $this->Tasks->getLeadDetails($lead_id);
                            if ($get_lead_details['status'] == 1) {
                                $leadDetails = $get_lead_details['lead_details'];
                                if (in_array($leadDetails['lead_status_id'], array(8, 9, 40, 14, 16, 17, 18, 19))) {
                                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                                    $lead_id = NULL;
                                    $update_customer_profile['cp_lead_id'] = NULL;
                                    $update_customer_profile['cp_journey_stage'] = NULL;
                                    $update_customer_profile['cp_is_journey_completed'] = NULL;
                                    $update_customer_profile['cp_is_cif_fetched'] = 0;
                                    $get_existing_customer['cp_is_cif_fetched'] = NULL;
                                }
                            }
                        }
                    }

                    $get_otp_trans_logs = $this->Tasks->get_otp_trans_logs($mobile, $otp);

                    $result1 = $get_otp_trans_logs['otp_trans_logs'];

                    if ($otp != $result1['lot_mobile_otp']) {
                        throw new ErrorException('Kindly enter the correct OTP or resend/generate a new one.');
                    }

                    $login_date_time = date('Y-m-d H:i:s');
                    $mot_otp_valid_time = $result1['lot_otp_valid_time'];
                    $mot_otp_trigger_time = $result1['lot_otp_trigger_time'];

                    $dateTimeObject1 = date_create($mot_otp_trigger_time);
                    $dateTimeObject2 = date_create(date('Y-m-d H:i:s'));
                    $interval = date_diff($dateTimeObject1, $dateTimeObject2);
                    $interval->format('%R%a days');
                    $min = $interval->days * 24 * 60;
                    $min += $interval->h * 60;
                    $min += $interval->i;

                    $ctime = $min;
                    $validtime = $mot_otp_valid_time;

                    if ($validtime < $ctime) {
                        throw new ErrorException('Your OTP has expired. Please resend or generate a new OTP.');
                    }


                    $token = md5($cust_profile_id . date('YmdHis') . rand(1111, 9999));
                    $exptime = $new_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                    $insert_mobileapp_login_trans = array();
                    $insert_mobileapp_login_trans['mlt_token'] = $token;
                    $insert_mobileapp_login_trans['mlt_valid_datetime'] = $exptime;
                    $insert_mobileapp_login_trans['mlt_request_ip'] = $ip_address;
                    $insert_mobileapp_login_trans['mlt_browser_history'] = $browser;
                    $insert_mobileapp_login_trans['mlt_login_time'] = $login_date_time;
                    $insert_mobileapp_login_trans['mlt_profile_id'] = $cust_profile_id;

                    $this->Tasks->insertMobileAppLoginTrans($lead_id, $insert_mobileapp_login_trans);

                    $user_type = !empty($get_existing_customer['cp_user_type']) ? $get_existing_customer['cp_user_type'] : "NEW";

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $leads_otp_trans_conditions['lot_id'] = $result1['lot_id'];
                    $update_leads_otp_trans['lot_otp_verify_flag'] = 1;

                    $update_customer_profile['cp_user_type'] = $user_type;
                    $update_customer_profile['cp_is_mobile_verified'] = 1;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $response = $this->Tasks->update($leads_otp_trans_conditions, 'leads_otp_trans', $update_leads_otp_trans);

                    if (empty($response)) {
                        throw new ErrorException("Some error occured. Please try again.");
                    }

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);

                    $response_array['app_login_token'] = $token;
                    $response_array['mobile_verified_status'] = 1;
                    $message = "OTP has been successfully verified.";

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $get_profile_event = $this->Tasks->getProfileEvents($cust_profile_id);
                    if ($get_profile_event['status'] == 1) {
                        $get_profile_event = $get_profile_event['event_data'];
                    }

                    if ($get_existing_customer['cp_registration_successful'] == 1) {
                        $response_array['next_step'] = "register_now";
                    } else {
                        $response_array['next_step'] = "pancard_verification";
                        if ($get_profile_event['pje_pancard_verification'] == 1) {
                            $response_array['next_step'] = "personal_details";
                            if ($get_profile_event['pje_personal_details'] == 1) {
                                $response_array['next_step'] = "residence_details";
                                if ($get_profile_event['pje_residence_details'] == 1) {
                                    $response_array['next_step'] = "income_details";
                                    if ($get_profile_event['pje_income_details'] == 1) {
                                        $response_array['next_step'] = "selfie_upload";
                                        if ($get_profile_event['pje_selfie_upload']) {
                                            $response_array['next_step'] = "register_now";
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $response_array['user_type'] = $user_type;
                    $response_array['eligibility_status'] = !empty($get_existing_customer['cp_lead_id']) ? 1 : 0;
                    $response_array['step_percentage'] = 0;
                    $response_array['pancard'] = $get_existing_customer["cp_pancard"];
                    break;

                case "resend_otp":
                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Customer Profile ID", "required");

                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile_details['status'] != 1) {
                        throw new ErrorException('Customer Profile Not Found');
                    }

                    $otp = rand(1111, 9999);

                    $customer_profile_details = $get_customer_profile_details['customer_profile_details'];

                    $mobile = $customer_profile_details['cp_mobile'];

                    if (!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                        $otp = 7488;
                    }

                    $lead_id = !empty($customer_profile_details['cp_lead_id']) ? $customer_profile_details['cp_lead_id'] : NULL;

                    $insertDataOTP = array(
                        'lot_lead_id' => $lead_id,
                        'lot_profile_id' => $cust_profile_id,
                        'lot_mobile_no' => $mobile,
                        'lot_mobile_otp' => $otp,
                        'lot_mobile_otp_type' => 5,
                        'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
                        'lot_otp_valid_time' => 10,
                    );

                    $this->Tasks->insert('leads_otp_trans', $insertDataOTP);

                    $lead_otp_id = $this->db->insert_id();

                    if (empty($lead_otp_id)) {
                        throw new ErrorException("An error occurred!");
                    }

                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $mobile;
                    $sms_input_data['name'] = "Customer";
                    $sms_input_data['otp'] = $otp;

                    $send_otp = $this->commonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                    if ($send_otp['status'] != 1) {
                        throw new ErrorException("Invalid mobile number.");
                    }

                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $message = "OTP has been resent successfully.";
                    $response_array = '';

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    $response_array['next_step'] = "otp_verify";
                    $response_array['step_percentage'] = 0;
                    break;
                case "pancard_verification":

                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("pancard", "Pancard", "required|exact_length[10]|alpha_numeric");

                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 1);

                    if ($stage_verification['status'] != 1) {
                        throw new ErrorException($stage_verification['message']);
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $get_existing_customer = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_existing_customer['status'] != 1) {
                        throw new ErrorException("Customer details could not be found.");
                    }

                    $customer_profile_details = $get_existing_customer['customer_profile_details'];

                    $update_customer_profile['cp_pancard'] = $pancard;
                    $update_customer_profile['cp_monthly_income'] = $monthly_income;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $request_array['profile_flag'] = 1;
                    $request_array['profile_id'] = $cust_profile_id;
                    $request_array['profile_journey_stage_id'] = $journey_id;

                    // if ($customer_profile_details['cp_pancard_verified_status'] != 1) {
                    $pan_verification_response = $this->commonComponent->call_pan_verification_api_call("DIGITAP_PANEXTENSION", 0, $request_array);
                    // }

                    if ($pan_verification_response['status'] != 1) {
                        throw new ErrorException("Your PAN card could not be verified. Please try again.");
                    }

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    if (!empty($pancard)) { //  && ($customer_profile_details['cp_is_cif_fetched'] != 1) && ($customer_profile_details['cp_data_delete_flag'] != 1)

                        $get_cif_customer = $this->Tasks->get_cif_customer_details($pancard);

                        if ($get_cif_customer['status'] == 1) {
                            $get_cif_customer_details = $get_cif_customer['data']['cif_customer_details'];
                            $update_customer_profile['cp_is_cif_fetched'] = 1;
                            $update_customer_profile['cp_cif_no'] = !empty($get_cif_customer_details['cif_number']) ? $get_cif_customer_details['cif_number'] : NULL;
                            //                        $update_customer_profile['cp_first_name'] = !empty($get_cif_customer_details['cif_first_name']) ? $get_cif_customer_details['cif_first_name'] : NULL;
                            //                        $update_customer_profile['cp_middle_name'] = !empty($get_cif_customer_details['cif_middle_name']) ? $get_cif_customer_details['cif_middle_name'] : NULL;
                            //                        $update_customer_profile['cp_sur_name'] = !empty($get_cif_customer_details['cif_sur_name']) ? $get_cif_customer_details['cif_sur_name'] : NULL;
                            $update_customer_profile['cp_dob'] = (!empty($get_cif_customer_details['cif_dob']) && $get_cif_customer_details['cif_dob'] != '0000-00-00') ? date("Y-m-d", strtotime($get_cif_customer_details['cif_dob'])) : NULL;
                            $update_customer_profile['cp_gender'] = !empty($get_cif_customer_details['cif_gender']) ? $get_cif_customer_details['cif_gender'] : NULL;
                            $update_customer_profile['cp_residence_address_1'] = !empty($get_cif_customer_details['cif_residence_address_1']) ? $get_cif_customer_details['cif_residence_address_1'] : NULL;
                            $update_customer_profile['cp_residence_address_2'] = !empty($get_cif_customer_details['cif_residence_address_2']) ? $get_cif_customer_details['cif_residence_address_2'] : NULL;
                            $update_customer_profile['cp_residence_landmark'] = !empty($get_cif_customer_details['cif_residence_landmark']) ? $get_cif_customer_details['cif_residence_landmark'] : NULL;
                            $update_customer_profile['cp_residence_state_id'] = !empty($get_cif_customer_details['cif_residence_state_id']) ? $get_cif_customer_details['cif_residence_state_id'] : NULL;
                            $update_customer_profile['cp_residence_city_id'] = !empty($get_cif_customer_details['cif_residence_city_id']) ? $get_cif_customer_details['cif_residence_city_id'] : NULL;
                            $update_customer_profile['cp_residence_pincode'] = !empty($get_cif_customer_details['cif_residence_pincode']) ? $get_cif_customer_details['cif_residence_pincode'] : NULL;
                            $update_customer_profile['cp_personal_email'] = !empty($get_cif_customer_details['cif_personal_email']) ? $get_cif_customer_details['cif_personal_email'] : NULL;
                            $update_customer_profile['cp_office_email'] = !empty($get_cif_customer_details['cif_office_email']) ? $get_cif_customer_details['cif_office_email'] : NULL;
                            $update_customer_profile['cp_alternate_mobile'] = !empty($get_cif_customer_details['cif_alternate_mobile']) ? $get_cif_customer_details['cif_alternate_mobile'] : NULL;
                            $update_customer_profile['cp_marital_status_id'] = !empty($get_cif_customer_details['cif_marital_status_id']) ? $get_cif_customer_details['cif_marital_status_id'] : NULL;
                            $update_customer_profile['cp_spouse_name'] = !empty($get_cif_customer_details['cif_spouse_name']) ? $get_cif_customer_details['cif_spouse_name'] : NULL;
                            $update_customer_profile['cp_aadhaar_no'] = !empty($get_cif_customer_details['cif_aadhaar_no']) ? $get_cif_customer_details['cif_aadhaar_no'] : NULL;
                            $update_customer_profile['cp_religion_id'] = !empty($get_cif_customer_details['cif_religion_id']) ? $get_cif_customer_details['cif_religion_id'] : NULL;
                            $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                            if ($get_cif_customer_details['cif_loan_is_disbursed'] > 0) {
                                $update_customer_profile['cp_user_type'] = "REPEAT";

                                $check_active_loan = $this->Tasks->get_active_loan($pancard);

                                if ($check_active_loan['status'] == 1) {
                                    $get_active_loan = $check_active_loan['get_active_loan'];
                                    if ($get_active_loan['active_loan_count'] > 0) {
                                        $active_loan_flag = 1;
                                        $user_type = "UNPAID-REPEAT";
                                    }
                                }

                                $cif_digital_ekyc_datetime = !empty($get_cif_customer_details['cif_digital_ekyc_datetime']) ? $get_cif_customer_details['cif_digital_ekyc_datetime'] : NULL;
                                if (!empty($cif_digital_ekyc_datetime) && strtotime($cp_digital_ekyc_datetime) >= strtotime("-90 Days")) {
                                    $update_customer_profile['cp_first_name'] = !empty($get_cif_customer_details['cif_first_name']) ? $get_cif_customer_details['cif_first_name'] : NULL;
                                    $update_customer_profile['cp_middle_name'] = !empty($get_cif_customer_details['cif_middle_name']) ? $get_cif_customer_details['cif_middle_name'] : NULL;
                                    $update_customer_profile['cp_sur_name'] = !empty($get_cif_customer_details['cif_sur_name']) ? $get_cif_customer_details['cif_sur_name'] : NULL;

                                    $update_customer_profile['cp_aadhaar_address_1'] = !empty($get_cif_customer_details['cif_aadhaar_address_1']) ? $get_cif_customer_details['cif_aadhaar_address_1'] : NULL;
                                    $update_customer_profile['cp_aadhaar_address_2'] = !empty($get_cif_customer_details['cif_aadhaar_address_2']) ? $get_cif_customer_details['cif_aadhaar_address_2'] : NULL;
                                    $update_customer_profile['cp_aadhaar_landmark'] = !empty($get_cif_customer_details['cif_aadhaar_landmark']) ? $get_cif_customer_details['cif_aadhaar_landmark'] : NULL;
                                    $update_customer_profile['cp_aadhaar_city_id'] = !empty($get_cif_customer_details['cif_aadhaar_city_id']) ? $get_cif_customer_details['cif_aadhaar_city_id'] : NULL;
                                    $update_customer_profile['cp_aadhaar_state_id'] = !empty($get_cif_customer_details['cif_aadhaar_state_id']) ? $get_cif_customer_details['cif_aadhaar_state_id'] : NULL;
                                    $update_customer_profile['cp_aadhaar_pincode'] = !empty($get_cif_customer_details['cif_aadhaar_pincode']) ? $get_cif_customer_details['cif_aadhaar_pincode'] : NULL;
                                }
                            }

                            if (!empty($get_cif_customer_details['cif_residence_type'])) {

                                $getResidenceTypeId = $this->Tasks->selectdata(array('m_residence_type_name' => $get_cif_customer_details['cif_residence_type']), '*', 'master_residence_type');
                                if ($getResidenceTypeId->num_rows() > 0) {
                                    $getMasterResidenceType = $getResidenceTypeId->row_array();
                                    $update_customer_profile['cp_residence_type_id'] = $getMasterResidenceType['m_residence_type_id'];
                                }
                            }

                            $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);
                            $customer_dob = (!empty($get_cif_customer_details['cif_dob']) && $get_cif_customer_details['cif_dob'] != '0000-00-00') ? date("d-m-Y", strtotime($get_cif_customer_details['cif_dob'])) : NULL;
                        }
                    } else {
                        $customer_dob = (!empty($customer_profile_details['cp_dob']) && $customer_profile_details['cp_dob'] != '0000-00-00') ? date("d-m-Y", strtotime($customer_profile_details['cp_dob'])) : NULL;
                    }

                    $message = "Your PAN card has been successfully verified.";

                    $response_array['pan_verified_status'] = 1;
                    $response_array['full_name'] = !empty($pan_verification_response['data']['name']) ? $pan_verification_response['data']['name'] : NULL;
                    $response_array['father_name'] = !empty($pan_verification_response['data']['fatherName']) ? $pan_verification_response['data']['fatherName'] : NULL;
                    $response_array['dob'] = !empty($customer_dob) ? $customer_dob :  $pan_verification_response['data']['dob'];

                    //$this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    $response_array['next_step'] = "personal_details";
                    $response_array['step_percentage'] = 20;
                    break;

                case "personal_details":
                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("dob", "Date of Birth", "required");
                    $this->form_validation->set_rules("gender", "Gender", "required");
                    $this->form_validation->set_rules("personal_email", "Personal email", "required|valid_email");
                    $this->form_validation->set_rules("marital_status_id", "Marital Status", "required");

                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_customer_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile['status'] != 1) {
                        throw new Exception("Customer is not registered!");
                    }

                    $get_customer_profile = $get_customer_profile['customer_profile_details'];

                    $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 2);

                    if ($stage_verification['status'] != 1) {
                        throw new ErrorException($stage_verification['message']);
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (($marital_status == 2) && empty($spouse_name)) {
                        throw new Exception("Enter Spouse Name if married");
                    } else if ($marital_status == 2 && !empty($spouse_name)) {
                        $spouse_name = strtoupper($spouse_name);
                        $spouse_mobile = $spouse_mobile;
                    } else {
                        $spouse_name = NULL;
                        $spouse_mobile = NULL;
                    }

                    $update_customer_profile['cp_dob'] = $dob;
                    $update_customer_profile['cp_gender'] = $gender;
                    $update_customer_profile['cp_personal_email'] = $personal_email;
                    $update_customer_profile['cp_marital_status_id'] = $marital_status;
                    $update_customer_profile['cp_spouse_name'] = $spouse_name;
                    $update_customer_profile['cp_spouse_mobile'] = $spouse_mobile;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    if ($get_customer_profile['cp_personal_email'] != $personal_email) {
                        $update_customer_profile['cp_personal_email_verified_status'] = 0;
                        $update_customer_profile['cp_personal_email_verified_on'] = NULL;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    if (empty($update_status)) {
                        throw new ErrorException("Something went wrong.");
                    }

                    $request_array['profile_flag'] = 1;
                    $request_array['profile_id'] = $cust_profile_id;
                    $request_array['email_type'] = 1;

                    // $verify_personal_email = $this->commonComponent->call_email_verification_api(0, $request_array);

                    // if ($verify_personal_email['status'] != 1) {
                    //     throw new ErrorException("Please enter the valid personal email address.");
                    // }

                    $message = "Personal details have been saved successfully.";

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    $response_array['next_step'] = "residence_details";
                    $response_array['step_percentage'] = 40;
                    break;
                case "residence_details":
                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("residence_type_id", "Residence Type", "required");
                    $this->form_validation->set_rules("residence_address_1", "Residence Address 1", "required");
                    $this->form_validation->set_rules("residence_address_2", "Residence Address 2", "required");
                    $this->form_validation->set_rules("residence_pincode", "Residence Pincode", "required");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_customer_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile['status'] != 1) {
                        throw new Exception("Customer is not registered!");
                    }

                    $get_customer_profile = $get_customer_profile['customer_profile_details'];

                    $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 2);

                    if ($stage_verification['status'] != 1) {
                        throw new ErrorException($stage_verification['message']);
                    }

                    $getCityStateByPincode = $this->Tasks->getCityStateByPincode($pincode);

                    if ($getCityStateByPincode['status'] != 1) {
                        throw new ErrorException("The entered pincode does not exist. Please try again with the correct pincode for your current address.");
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $city_id = $getCityStateByPincode['get_city_state_details']['city_id'];
                    $city_name = $getCityStateByPincode['get_city_state_details']['city_name'];
                    $state_id = $getCityStateByPincode['get_city_state_details']['state_id'];
                    $state_name = $getCityStateByPincode['get_city_state_details']['state_name'];
                    $pincode = $getCityStateByPincode['get_city_state_details']['pincode'];
                    $branch_id = $getCityStateByPincode['get_city_state_details']['branch_id'];

                    $response_array['city_id'] = $city_id;
                    $response_array['city_name'] = $city_name;
                    $response_array['state_id'] = $state_id;
                    $response_array['state_name'] = $state_name;
                    $response_array['pincode'] = $pincode;

                    $update_customer_profile['cp_residence_state_id'] = $state_id;
                    $update_customer_profile['cp_residence_city_id'] = $city_id;
                    $update_customer_profile['cp_residence_pincode'] = $pincode;
                    $update_customer_profile['cp_residence_type_id'] = $residence_type_id;
                    $update_customer_profile['cp_residence_branch_id'] = $branch_id;
                    $update_customer_profile['cp_residence_address_1'] = $residence_address_1;
                    $update_customer_profile['cp_residence_address_2'] = $residence_address_2;
                    $update_customer_profile['cp_residence_landmark'] = $residence_landmark;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    if (empty($update_status)) {
                        throw new ErrorException("Something went wrong.");
                    }

                    $message = "Residence details saved successfully.";

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);

                    // $response_array['cust_profile_id'] = $this->encrypt->encode($cust_profile_id);
                    $response_array['next_step'] = "income_details";
                    $response_array['step_percentage'] = 60;
                    break;
                case "income_details":
                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("income_type_id", "Income Type", "required|numeric");
                    $this->form_validation->set_rules("monthly_income", "Monthly Salary", "required|numeric");
                    $this->form_validation->set_rules("salary_mode_id", "Salary Mode", "required|numeric");
                    $this->form_validation->set_rules("salary_date", "Salary Date", "required");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile_details['status'] != 1) {
                        throw new ErrorException("Customer Details Not Found..");
                    }

                    $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 1);

                    if ($stage_verification['status'] != 1) {
                        throw new ErrorException($stage_verification['message']);
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $update_customer_profile['cp_income_type_id'] = $income_type_id;
                    $update_customer_profile['cp_monthly_income'] = $monthly_income;
                    $update_customer_profile['cp_salary_mode'] = $salary_mode_id;
                    $update_customer_profile['cp_salary_date'] = date("Y-m-d", strtotime($salary_date));
                    $update_customer_profile['cp_obligations'] = $obligations;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $message = "Income details saved successfully.";

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, $message);

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    $response_array['next_step'] = "selfie_upload";
                    $response_array['step_percentage'] = 80;
                    break;
                case "selfie_upload":

                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[jpg,png,jpeg,JPG,JPEG,PNG]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_customer_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile['status'] != 1) {
                        throw new Exception("Customer is not registered!");
                    }

                    $get_customer_profile = $get_customer_profile['customer_profile_details'];

                    $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 2);

                    if ($stage_verification['status'] != 1) {
                        throw new ErrorException($stage_verification['message']);
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['new_file_name'] = "cp_" . $cust_profile_id;
                    $request_array['ext'] = $ext;

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document(0, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $request_array['new_file_name'] . "_api_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }
                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("Selfie not uploaded! Try Again");
                    }

                    $update_customer_profile['cp_profile_pic'] = $image_name;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                    $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, "Selfie Uploaded");

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);

                    $message = "Selfie uploaded successfully";
                    $response_array['selfie_doc_url'] = COLLEX_DOC_URL . $image_name;
                    $response_array['next_step'] = "register_now";
                    $response_array['step_percentage'] = 100;
                    break;
                case "register_now":
                    $this->form_validation->set_data($post);
                    $this->form_validation->set_rules("profile_id", "Profile ID", "required");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

                    if ($get_customer_profile_details['status'] != 1) {
                        throw new ErrorException("Customer Details Not Found..");
                    }

                    $get_customer_profile = $get_customer_profile_details['customer_profile_details'];

                    $pancard = $get_customer_profile['cp_pancard'];
                    $pancard_verified_status = $get_customer_profile['cp_pancard_verified_status'];
                    $first_name = $get_customer_profile['cp_first_name'];
                    $middle_name = $get_customer_profile['cp_middle_name'];
                    $sur_name = $get_customer_profile['cp_sur_name'];
                    $dob = $get_customer_profile['cp_dob'];
                    $gender = $get_customer_profile['cp_gender'];
                    $marital_status_id = $get_customer_profile['cp_marital_status_id'];
                    $spouse_name = $get_customer_profile['cp_spouse_name'];
                    $personal_email = $get_customer_profile['cp_personal_email'];
                    $personal_email_verified_status = $get_customer_profile['cp_personal_email_verified_status'];
                    $residence_address_1 = $get_customer_profile['cp_residence_address_1'];
                    $residence_address_2 = $get_customer_profile['cp_residence_address_2'];
                    $residence_landmark = $get_customer_profile['cp_residence_landmark'];
                    $residence_pincode = $get_customer_profile['cp_residence_pincode'];
                    $city_id = $get_customer_profile['cp_residence_city_id'];
                    $state_id = $get_customer_profile['cp_residence_state_id'];
                    $residence_type_id = $get_customer_profile['cp_residence_type_id'];
                    $profile_pic = $get_customer_profile['cp_profile_pic'];
                    $mobile_verified_status = $get_customer_profile['cp_is_mobile_verified'];
                    $income_type_id = $get_customer_profile['cp_income_type_id'];
                    $monthly_income = $get_customer_profile['cp_monthly_income'];
                    $salary_mode_id = $get_customer_profile['cp_salary_mode'];
                    $salary_date = $get_customer_profile['cp_salary_date'];
                    $registration_successful = $get_customer_profile['cp_registration_successful'];

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("registration_successful", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if ($registration_successful != 1) {

                        $getProfileJourneyEvents = $this->Tasks->select_data_by_filter(['pje_profile_id' => $cust_profile_id, 'pje_active' => 1, 'pje_deleted' => 0], '*', 'profile_journey_events', 'pje_id DESC', 1);

                        if ($getProfileJourneyEvents->num_rows() > 0) {
                            $profile_journey_events = $getProfileJourneyEvents->row_array();
                        }

                        if ($profile_journey_events['pje_pancard_verification'] != 1) {
                            throw new ErrorException("PAN number not filled");
                        }

                        if ($profile_journey_events['pje_personal_details'] != 1) {
                            throw new ErrorException("Personal details not filled");
                        }

                        if ($profile_journey_events['pje_residence_details'] != 1) {
                            throw new ErrorException("Residence details not filled");
                        }

                        if ($profile_journey_events['pje_income_details'] != 1) {
                            throw new ErrorException("Income Details not filled");
                        }

                        if ($profile_journey_events['pje_selfie_upload'] != 1) {
                            throw new ErrorException("Profile pic not uploaded");
                        }

                        if (empty($pancard)) {
                            throw new ErrorException("PAN number cannot be empty");
                        }

                        if ($pancard_verified_status != 1) {
                            throw new ErrorException("PAN number not verified");
                        }

                        if (empty($dob)) {
                            throw new ErrorException("DOB cannot be empty");
                        }

                        if (empty($gender)) {
                            throw new ErrorException("Please choose gender");
                        }

                        if (empty($marital_status_id)) {
                            throw new ErrorException("Please select marital status");
                        }

                        if (empty($personal_email)) {
                            throw new ErrorException("Personal email cannot be empty");
                        }

                        // if ($personal_email_verified_status != 1) {
                        //     throw new ErrorException("Personal email not verified");
                        // }

                        if (empty($residence_address_1)) {
                            throw new ErrorException("Residence address 1 cannot be empty");
                        }

                        if (empty($residence_address_2)) {
                            throw new ErrorException("Residence address 2 cannot be empty");
                        }

                        if (empty($residence_pincode)) {
                            throw new ErrorException("Residence pincode cannot be empty");
                        }

                        if (empty($residence_type_id)) {
                            throw new ErrorException("Residence type cannot be empty");
                        }

                        if (empty($income_type_id)) {
                            throw new ErrorException("Please choose employment type");
                        }

                        if (empty($monthly_income)) {
                            throw new ErrorException("Monthly Income cannot be empty");
                        }

                        if (empty($salary_mode_id)) {
                            throw new ErrorException("Please choose salary mode");
                        }

                        if (empty($salary_date)) {
                            throw new ErrorException("Salary Date cannot be empty");
                        }

                        if (empty($profile_pic)) {
                            throw new ErrorException("Selfie not uploaded");
                        }

                        $stage_verification = $this->applicationStageVerification($cust_profile_id, "registration_successful", 2);

                        if ($stage_verification['status'] != 1) {
                            throw new ErrorException($stage_verification['message']);
                        }

                        $update_customer_profile['cp_registration_successful'] = 1;
                        $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");

                        $check_page_status = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                        if ($check_page_status == false) {
                            $update_customer_profile['cp_journey_stage'] = $journey_id;
                        }

                        $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                        $this->Tasks->insertProfileFollowupLog($cust_profile_id, $journey_id, "User Registered Successfully");

                        $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id);
                    }

                    $lead_status_id = 41;
                    $lead_status = 'LEAD-REGISTRATION';
                    $lead_stage = 'S1';

                    $update_flag = 0;

                    $get_data_source_details = $this->Tasks->selectdata(['data_source_id' => $this->data_source_id], 'data_source_name', 'master_data_source');

                    if ($get_data_source_details->num_rows() > 0) {
                        $data_source_details = $get_data_source_details->row_array();
                    }

                    $data_source_name = !empty($data_source_details['data_source_name']) ? $data_source_details['data_source_name'] : $this->source_name;

                    $get_cif_customer_details = $this->Tasks->get_cif_customer_details($pancard);

                    // $get_affiliate_data = $this->Tasks->selectdata(['mmc_affiliate_flag' => 1, 'mmc_active' => 1, 'mmc_deleted' => 0, 'mmc_name' => $get_customer_profile['cp_utm_source']], 'mmc_name', 'master_marketing_channel');

                    $user_type = "NEW";

                    if ($get_cif_customer_details['status'] == 1) {
                        $get_cif_customer = $get_cif_customer_details['data']['cif_customer_details'];
                        if ($get_cif_customer['cif_loan_is_disbursed'] > 0) {
                            $user_type = "REPEAT";

                            $check_active_loan = $this->Tasks->get_active_loan($pancard);

                            if ($check_active_loan['status'] == 1) {
                                $get_active_loan = $check_active_loan['get_active_loan'];
                                if ($get_active_loan['active_loan_count'] > 0) {
                                    $user_type = "UNPAID-REPEAT";
                                }
                            }
                            $update_customer_profile['cp_user_type'] = $user_type;

                            // if ($get_affiliate_data->num_rows() > 0) {
                            // $get_customer_profile['cp_utm_source'] = 'ORGANIC';
                            // $get_customer_profile['cp_utm_campaign'] = 'ORGANIC';
                            // $get_customer_profile['cp_utm_medium'] = 'ORGANIC';
                            // $get_customer_profile['cp_utm_term'] = 'ORGANIC';

                            $update_customer_profile_source['cp_status_id'] = $lead_status_id;
                            $update_customer_profile_source['cp_user_type'] = $user_type;
                            $update_customer_profile_source['cp_updated_at'] = date("Y-m-d H:i:s");
                            $update_customer_profile_source['cp_utm_source'] = 'ORGANIC';
                            $update_customer_profile_source['cp_utm_medium'] = 'ORGANIC';
                            $update_customer_profile_source['cp_utm_campaign'] = 'ORGANIC';
                            $update_customer_profile_source['cp_utm_term'] = 'ORGANIC';
                            $update_customer_profile_source['cp_adjust_adid'] = "";

                            $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile_source);
                            // }
                        }
                    }

                    $insertDataLeads = array(
                        'first_name' => $get_customer_profile['cp_first_name'],
                        'mobile' => $get_customer_profile['cp_mobile'],
                        'customer_id' => $get_customer_profile['cp_cif_no'],
                        'pancard' => $get_customer_profile['cp_pancard'],
                        'lead_is_mobile_verified' => $get_customer_profile['cp_is_mobile_verified'],
                        'email' => $get_customer_profile['cp_personal_email'],
                        'alternate_email' => $get_customer_profile['cp_office_email'],
                        'company_id' => 1,
                        'product_id' => 1,
                        'lead_status_id' => $lead_status_id,
                        'lead_data_source_id' => $this->data_source_id,
                        'source' => $data_source_name,
                        'utm_source' => $get_customer_profile['cp_utm_source'],
                        'utm_campaign' => $get_customer_profile['cp_utm_campaign'],
                        'utm_medium' => $get_customer_profile['cp_utm_medium'],
                        'utm_term' => $get_customer_profile['cp_utm_term'],
                        'user_type' => $user_type,
                        'status' => $lead_status,
                        'stage' => $lead_stage,
                        'lead_entry_date' => date('Y-m-d'),
                        'qde_consent' => 'Y',
                        'term_and_condition' => "YES",
                        'created_on' => date('Y-m-d H:i:s'),
                        'city_id' => $get_customer_profile['cp_residence_city_id'],
                        'state_id' => $get_customer_profile['cp_residence_state_id'],
                        'pincode' => $get_customer_profile['cp_residence_pincode'],
                        'lead_branch_id' => $get_customer_profile['cp_residence_branch_id'],
                        'coordinates' => $get_customer_profile['cp_geo_lat'] . "," . $get_customer_profile['cp_geo_long'],
                        'ip' => $get_customer_profile['cp_ip_address'],
                        'lead_journey_type_id' => $this->journey_type_id,
                        'lead_journey_stage_id' => $journey_id,
                        'lead_customer_profile_id' => $get_customer_profile['cp_id'],
                        'monthly_salary_amount' => $monthly_income,
                        'obligations' => !empty($get_customer_profile['cp_obligations']) ? $get_customer_profile['cp_obligations'] : 0
                    );

                    if (!empty($get_customer_profile['cp_lead_id'])) {
                        $get_lead_details = $this->Tasks->getLeadDetails($get_customer_profile['cp_lead_id']);
                        if ($get_lead_details['status'] == 1) {
                            $leadDetails = $get_lead_details['lead_details'];
                            // if (date("Y-m-d", strtotime($leadDetails['lead_entry_date'])) == date('Y-m-d')) {
                            if (!in_array($leadDetails['lead_status_id'], array(8, 9))) {
                                $lead_id = $get_customer_profile['cp_lead_id'];
                                $update_flag = 1;
                            }
                        }
                    }

                    $dedupeRequestArray = array('mobile' => $get_customer_profile['cp_mobile'], 'pancard' => $get_customer_profile['cp_pancard'], 'email' => $get_customer_profile['cp_personal_email']);

                    $dedupeReturnArray = $this->commonComponent->check_customer_dedupe($dedupeRequestArray);

                    if (!empty($dedupeReturnArray['status']) && $dedupeReturnArray['status'] == 1) {
                        throw new ErrorException("You have already applied. We will reach out to you soon.");
                    }

                    if ($update_flag == 0) {
                        $this->Tasks->insert('leads', $insertDataLeads);
                        $lead_id = $this->db->insert_id();
                        $lead_gen_remark = "Lead Generated Lead ID : $lead_id";
                        $lead_gen_remark .= "<br/>UTM Source : " . $insertDataLeads['utm_source'];
                        $lead_gen_remark .= "<br/>UTM Medium : " . $insertDataLeads['utm_medium'];
                        $lead_gen_remark .= "<br/>UTM Campaign : " . $insertDataLeads['utm_campaign'];
                        $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $lead_gen_remark);
                    } else {
                        unset($insertDataLeads['lead_status_id']);
                        unset($insertDataLeads['status']);
                        unset($insertDataLeads['stage']);
                        unset($insertDataLeads['lead_data_source_id']);
                        unset($insertDataLeads['source']);
                        unset($insertDataLeads['utm_source']);
                        unset($insertDataLeads['utm_medium']);
                        unset($insertDataLeads['utm_campaign']);
                        unset($insertDataLeads['utm_term']);
                        unset($insertDataLeads['created_on']);
                        unset($insertDataLeads['lead_journey_type_id']);
                        unset($insertDataLeads['lead_entry_date']);
                        unset($insertDataLeads['lead_customer_profile_id']);
                        $insertDataLeads['updated_on'] = date("Y-m-d H:i:s");
                        $this->Tasks->updateLeads($lead_id, $insertDataLeads);
                    }

                    if (empty($lead_id)) {
                        throw new ErrorException("Something went wrong...");
                    }

                    $get_master_residence = $this->Tasks->getMasterResidence($get_customer_profile['cp_residence_type_id']);

                    $get_master_residence = $get_master_residence['master_residence_details'];

                    $insertLeadsCustomer = array(
                        'customer_lead_id' => $lead_id,
                        'first_name' => $get_customer_profile['cp_first_name'],
                        'middle_name' => $get_customer_profile['cp_middle_name'],
                        'sur_name' => $get_customer_profile['cp_sur_name'],
                        'dob' => date('Y-m-d', strtotime($get_customer_profile['cp_dob'])),
                        'mobile' => $get_customer_profile['cp_mobile'],
                        'alternate_mobile' => $get_customer_profile['cp_alternate_mobile'],
                        'mobile_verified_status' => (($get_customer_profile['cp_is_mobile_verified'] == 1) ? "YES" : "NO"),
                        'pancard' => $get_customer_profile['cp_pancard'],
                        'pancard_verified_status' => $get_customer_profile['cp_pancard_verified_status'],
                        'pancard_verified_on' => $get_customer_profile['cp_pancard_verified_on'],
                        'father_name' => $get_customer_profile['cp_father_name'],
                        'gender' => (($get_customer_profile['cp_gender'] == 1) ? "MALE" : "FEMALE"),
                        'email' => $get_customer_profile['cp_personal_email'],
                        'email_verified_status' => (($get_customer_profile['cp_personal_email_verified_status'] == 1) ? "YES" : "NO"),
                        'email_verified_on' => $get_customer_profile['cp_personal_email_verified_on'],
                        'alternate_email' => $get_customer_profile['cp_office_email'],
                        'current_house' => $get_customer_profile['cp_residence_address_1'],
                        'current_locality' => $get_customer_profile['cp_residence_address_2'],
                        'current_landmark' => $get_customer_profile['cp_residence_landmark'],
                        'current_city' => $get_customer_profile['cp_residence_city_id'],
                        'current_state' => $get_customer_profile['cp_residence_state_id'],
                        'city_id' => $get_customer_profile['cp_residence_city_id'],
                        'state_id' => $get_customer_profile['cp_residence_state_id'],
                        'cr_residence_pincode' => $get_customer_profile['cp_residence_pincode'],
                        'current_residence_type' => $get_master_residence['m_residence_type_name'],
                        'customer_marital_status_id' => $get_customer_profile['cp_marital_status_id'],
                        'customer_religion_id' => $get_customer_profile['cp_religion_id'],
                        'customer_spouse_name' => $get_customer_profile['cp_spouse_name'],
                        'aadhar_no' => $get_customer_profile['cp_aadhaar_no'],
                        'created_date' => date('Y-m-d H:i:s'),
                        'customer_adjust_adid' => $get_customer_profile['cp_adjust_adid'],
                        'customer_adjust_gps_adid' => $get_customer_profile['cp_adjust_gps_adid']
                    );

                    $insert_customer_employement = array();

                    $ekyc_flag = 0;

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {

                        $update_data_leads = [
                            'customer_id' => $get_cif_customer['cif_number'],
                            'updated_on' => date('Y-m-d H:i:s')
                        ];

                        $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        if (($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") && ($get_customer_profile['cp_aadhaar_no'] == $get_cif_customer['cif_aadhaar_no']) && ($get_cif_customer['cif_digital_ekyc_flag'] == 1) && !empty($get_cif_customer['cif_digital_ekyc_datetime'])) {
                            $camp_kyc_date = strtotime(date("Y-m-d", strtotime("+90 day", strtotime($get_cif_customer['cif_digital_ekyc_datetime']))));
                            $camp_current_datetime = strtotime(date("Y-m-d"));
                            if ($camp_kyc_date > $camp_current_datetime) {
                                $ekyc_flag = 1;
                                $insertLeadsCustomer['customer_digital_ekyc_flag'] = $ekyc_flag;
                                $insertLeadsCustomer['customer_digital_ekyc_done_on'] = $get_cif_customer['cif_digital_ekyc_datetime'];
                                $insertLeadsCustomer['aa_current_house'] = $get_cif_customer['cif_aadhaar_address_1'];
                                $insertLeadsCustomer['aa_current_locality'] = $get_cif_customer['cif_aadhaar_address_2'];
                                $insertLeadsCustomer['aa_current_landmark'] = $get_cif_customer['cif_aadhaar_landmark'];
                                $insertLeadsCustomer['aa_cr_residence_pincode'] = $get_cif_customer['cif_aadhaar_pincode'];
                                $insertLeadsCustomer['aa_current_state_id'] = $get_cif_customer['cif_aadhaar_state_id'];
                                $insertLeadsCustomer['aa_current_city_id'] = $get_cif_customer['cif_aadhaar_city_id'];
                            }
                        }

                        $insert_customer_employement = [
                            'customer_id' => $get_cif_customer['cif_number'],
                            'employer_name' => $get_cif_customer['cif_company_name'],
                            'emp_pincode' => $get_cif_customer['cif_office_pincode'],
                            'emp_house' => $get_cif_customer['cif_office_address_1'],
                            'emp_street' => $get_cif_customer['cif_office_address_2'],
                            'emp_landmark' => $get_cif_customer['cif_office_address_landmark'],
                            'emp_residence_since' => $get_cif_customer['cif_office_working_since'],
                            'emp_shopNo' => $get_cif_customer['cif_office_address_1'],
                            'emp_designation' => $get_cif_customer['cif_office_designation'],
                            'emp_department' => $get_cif_customer['cif_office_department'],
                            'emp_employer_type' => $get_cif_customer['cif_company_type_id'],
                            'emp_website' => $get_cif_customer['cif_company_website'],
                            'emp_email' => $get_cif_customer['cif_office_email'],
                            'state_id' => $get_cif_customer['cif_office_state_id'],
                            'city_id' => $get_cif_customer['cif_office_city_id'],
                            'emp_occupation_id' => $get_cif_customer['cif_occupation_id']
                        ];

                        $insertLeadsCustomer['father_name'] = !empty($get_cif_customer['cif_father_name']) ? $get_cif_customer['cif_father_name'] : $get_customer_profile['cp_father_name'];
                        $insertLeadsCustomer['customer_qualification_id'] = $get_cif_customer['cif_qualification_id'];
                        $insertLeadsCustomer['customer_spouse_name'] = $get_cif_customer['cif_spouse_name'];
                        $insertLeadsCustomer['customer_spouse_occupation_id'] = $get_cif_customer['cif_spouse_occupation_id'];
                        $insertLeadsCustomer['customer_marital_status_id'] = $get_cif_customer['cif_marital_status_id'];
                        $insertLeadsCustomer['current_residence_since'] = date("Y-m-d", strtotime($get_cif_customer['cif_residence_since']));
                    }

                    $get_salary_mode_by_id = $this->Tasks->getSalaryModeById($salary_mode_id);

                    $insert_customer_employement['lead_id'] = $lead_id;
                    $insert_customer_employement['monthly_income'] = $monthly_income;
                    $insert_customer_employement['income_type'] = $income_type_id;
                    $insert_customer_employement['salary_mode'] = $get_salary_mode_by_id['master_salary_mode_details']['m_salary_mode_name'];
                    $insert_customer_employement['created_on'] = date("Y-m-d H:i:s");

                    $get_master_document = $this->Tasks->getMasterDocument(18);

                    $get_master_document = $get_master_document['master_document_details'];

                    $insert_docs_array = array();

                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => NULL,
                        "pancard" => $get_customer_profile['cp_pancard'],
                        "mobile" => $get_customer_profile['cp_mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => "",
                        "file" => $get_customer_profile['cp_profile_pic'],
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    if ($update_flag == 0) {
                        $this->Tasks->insert('lead_customer', $insertLeadsCustomer);
                        $this->Tasks->insert('customer_employment', $insert_customer_employement);
                        $this->Tasks->insert('docs', $insert_docs_array);
                        $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "New lead applied : " . $user_type);

                        if ($get_customer_profile['cp_is_mobile_verified'] == 1) {
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP Verified Successfully");
                        }

                        if ($user_type == "REPEAT") {
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "REPEAT CUSTOMER");
                        } else if ($user_type == "UNPAID-REPEAT") {
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "UNPAID-REPEAT CUSTOMER");
                        }

                        if ($get_customer_profile['cp_personal_email_verified_status'] == 1) {
                            $update_profile_followup_conditions['profile_followup_profile_id'] = $cust_profile_id;
                            $update_profile_followup_conditions['profile_followup_status_id'] = 42;
                            $update_profile_followup_conditions['DATE(profile_followup_created_on)'] = date("Y-m-d", strtotime($get_customer_profile['cp_personal_email_verified_on']));

                            $get_followup_data = $this->Tasks->selectprofilefollowup($update_profile_followup_conditions, "*", "customer_profile_followup", "profile_followup_id DESC", 1);
                            if ($get_followup_data->num_rows() > 0) {
                                $get_followup_data = $get_followup_data->row_array();
                                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $get_followup_data['profile_followup_remarks']);
                            }
                        }

                        if ($get_customer_profile['cp_pancard_verified_status'] == 1) {
                            $update_profile_followup_conditions['profile_followup_profile_id'] = $cust_profile_id;
                            $update_profile_followup_conditions['profile_followup_status_id'] = 20;
                            $update_profile_followup_conditions['DATE(profile_followup_created_on)'] = date("Y-m-d", strtotime($get_customer_profile['cp_pancard_verified_on']));

                            $get_followup_data = $this->Tasks->selectprofilefollowup($update_profile_followup_conditions, "*", "customer_profile_followup", "profile_followup_id DESC", 1);

                            if ($get_followup_data->num_rows() > 0) {
                                $get_followup_data = $get_followup_data->row_array();
                                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $get_followup_data['profile_followup_remarks']);
                            }
                        }
                    } else {
                        unset($insertLeadsCustomer['created_date']);
                        unset($insertLeadsCustomer['customer_lead_id']);
                        $insertLeadsCustomer['updated_at'] = date("Y-m-d H:i:s");
                        $this->Tasks->updateLeadCustomer($lead_id, $insertLeadsCustomer);
                        $insert_customer_employement['updated_on'] = date("Y-m-d H:i:s");
                        unset($insert_customer_employement['created_on']);
                        unset($insert_customer_employement['lead_id']);
                        $this->Tasks->updateCustomerEmployment($lead_id, $insert_customer_employement);
                        $this->Tasks->update(array("lead_id" => $lead_id), 'docs', $insert_docs_array);
                    }

                    $this->Tasks->insertProfileFollowupLog($cust_profile_id, $page_name, "Lead Generated Succesfully. Lead ID: " . $lead_id);
                    // Generate Reference No
                    $get_reference_no = $this->Tasks->generateReferenceCode($lead_id, $get_customer_profile['cp_first_name'], $get_customer_profile['cp_sur_name'], $get_customer_profile['cp_mobile']);
                    $this->Tasks->updateLeads($lead_id, array('lead_reference_no' => $get_reference_no));

                    // Update Lead ID in PAN Verification Api Log
                    $update_poi_veri_logs_data['poi_veri_lead_id'] = $lead_id;

                    $update_poi_veri_logs_conditions['poi_veri_profile_id'] = $cust_profile_id;
                    $update_poi_veri_logs_conditions['DATE(poi_veri_response_datetime)'] = date("Y-m-d", strtotime($get_customer_profile['cp_pancard_verified_on']));

                    $this->Tasks->update($update_poi_veri_logs_conditions, "api_poi_verification_logs", $update_poi_veri_logs_data);

                    // // Update Lead Id in Email Verification Api Log
                    // $update_ev_logs_data['ev_lead_id'] = $lead_id;

                    $update_ev_logs_conditions['ev_profile_id'] = $cust_profile_id;
                    $update_ev_logs_conditions['DATE(ev_response_datetime)'] = date("Y-m-d", strtotime($get_customer_profile['cp_personal_email_verified_on']));

                    // $this->Tasks->update($update_ev_logs_conditions, "api_email_verification_logs", $update_ev_logs_data);

                    // Update Lead ID in OTP Trans Log
                    $get_otp_trans_logs = $this->Tasks->select_data_by_filter(['lot_profile_id' => $cust_profile_id], 'lot_id', 'leads_otp_trans', 'lot_otp_verify_time DESC', 1);
                    $otp_trans_logs = $get_otp_trans_logs->row_array();

                    $update_lead_otp_trans['lot_lead_id'] = $lead_id;

                    $this->Tasks->update(['lot_id' => $otp_trans_logs['lot_id']], 'leads_otp_trans', $update_lead_otp_trans);

                    $update_customer_profile_lead_id['cp_lead_id'] = $lead_id;
                    $update_customer_profile_lead_id['cp_status_id'] = $lead_status_id;
                    $update_customer_profile_lead_id['cp_user_type'] = $user_type;
                    $update_customer_profile_lead_id['cp_updated_at'] = date("Y-m-d H:i:s");

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile_lead_id);

                    $updateProfileJourneyEvents['pje_lead_id'] = $lead_id;

                    $this->Tasks->updateJourneyEvents($cust_profile_id, $journey_id, 1, $this->journey_type_id, $updateProfileJourneyEvents);

                    $getProfileRegistrationStatus = $this->Tasks->selectJourneyEvents($cust_profile_id, $journey_id, 1);

                    if ($getProfileRegistrationStatus == true) {
                        $leadJourneyEvents['lje_lead_id'] = $lead_id;
                        $leadJourneyEvents['lje_profile_id'] = $cust_profile_id;
                        $leadJourneyEvents['lje_login'] = 1;
                        $leadJourneyEvents['lje_otp_verify'] = 1;
                        $leadJourneyEvents['lje_resend_otp'] = 1;
                        $leadJourneyEvents['lje_residence_pincode'] = 1;
                        $leadJourneyEvents['lje_pancard_verification'] = 1;
                        $leadJourneyEvents['lje_income_details'] = 1;
                        $leadJourneyEvents['lje_personal_details'] = 1;
                        $leadJourneyEvents['lje_residence_details'] = 1;
                        $leadJourneyEvents['lje_promocode'] = 1;
                        $leadJourneyEvents['lje_selfie_upload'] = 1;
                        $leadJourneyEvents['lje_registration_successful'] = 1;
                        $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $leadJourneyEvents);
                    }

                    if (empty($update_status)) {
                        throw new ErrorException("Something went wrong....");
                    }

                    // if ($user_type == "NEW") {

                    //     $return_bl_array = setOtherRepeatCustomer($lead_id, "bl", 1);

                    //     $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "B001 Check - $pancard : " . $return_bl_array['message']);

                    //     if ($return_bl_array['status'] != 1) {
                    //         $return_lw_array = setOtherRepeatCustomer($lead_id, "lw", 1);
                    //         $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "L001 Check - $pancard : " . $return_lw_array['message']);
                    //     }
                    // }

                    $check_eligibility = $this->commonComponent->run_eligibility($lead_id);

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("check_eligibility", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                    if ($check_eligibility['status'] != 1) {

                        // $this->commonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, array('event_type_id' => 2));

                        $eligibility_content = "We regret to inform you that you are not eligible for a loan based on our internal guidelines.";
                        $eligibility_reason = "";

                        $eligibility_flags = $check_eligibility['eligibility_flags'];

                        if ($eligibility_flags['lerr_emp_type_flag'] != 1) {
                            $eligibility_reason = "We only provide loan to salaried personel.";
                        } else if ($eligibility_flags['lerr_cust_income_flag'] != 1) {
                            $eligibility_reason = "You are a little below our salary criteria.";
                        } else if ($eligibility_flags['lerr_city_flag'] != 1) {
                            $eligibility_reason = "We are yet to start providing loan services in your city.";
                        } else if ($eligibility_flags['lerr_salary_mode_flag'] != 1) {
                            $eligibility_reason = "Due to our internal policy, we only provide loans to personel receiving salary in bank only.";
                        } else {
                            $eligibility_reason = "Other - Internal Policy";
                        }

                        $eligibility_failed_content = array('eligibility_content' => $eligibility_content, 'eligibility_reason' => $eligibility_reason);

                        $get_journey_stage = $this->Tasks->getMasterJourneyStage("eligibility_failed", $this->journey_type_id);

                        if ($get_journey_stage['status'] == 1) {
                            $get_journey_stage = $get_journey_stage['master_journey_stage'];
                            $journey_id = $get_journey_stage['m_journey_id'];
                        }


                        $update_profile_data['cp_status_id'] = 8;
                        $update_profile_data['cp_lead_id'] = NULL;
                        $update_profile_data['cp_updated_at'] = date("Y-m-d H:i:s");

                        $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                        if ($check_page_status == false) {
                            $update_profile_data['cp_journey_stage'] = $journey_id;
                        }

                        $this->Tasks->updateCustomerProfile($cust_profile_id, $update_profile_data);

                        $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                        //                    $update_pje_journey['pje_reject'] = 1;
                        //                    $update_pje_journey['pje_eligibility_failed'] = 1;
                        //                    $this->Tasks->resetJourneyEvents($cust_profile_id, 1, $update_pje_journey);

                        $update_lje_journey['lje_reject'] = 1;
                        $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $update_lje_journey);

                        throw new RuntimeException("You are not eligible for a loan.");
                    }

                    if ($monthly_income < 35000 && $user_type == "NEW") {

                        $update_array = array();
                        $update_array['status'] = 'SYSTEM-REJECT';
                        $update_array['stage'] = 'S8';
                        $update_array['lead_status_id'] = 8;
                        $update_array['updated_on'] = date("Y-m-d H:i:s");
                        $update_array['lead_rejected_reason_id'] = 14;
                        $update_array['lead_rejected_datetime'] = date("Y-m-d H:i:s");

                        $this->Tasks->updateLeads($lead_id, $update_array);

                        $this->Tasks->insertApplicationLog($lead_id, 8, "Salary < 35000 - Case System Rejected");

                        $eligibility_reason = "You are a little below our salary criteria.";
                        throw new RuntimeException("You are not eligible for a loan.");
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("eligibility_confirmed", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_status = $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                    // if (($get_affiliate_data->num_rows() > 0) && ($user_type == "NEW")) {
                    //     $this->commonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, array('event_type_id' => 1));
                    // }


                    if (($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") && !empty($pancard) && !empty($lead_id)) {

                        $this->Tasks->insertRepeatCustomerBankingDetails($pancard, $lead_id);

                        $this->Tasks->insertRepeatCustomerReferenceDetails($pancard, $lead_id);

                        $lead_status_id = 42;
                        $lead_stage = 'S1';
                        $lead_status = 'LEAD-PARTIAL';

                        if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                            $lead_status_id = 4;
                            $lead_stage = 'S4';
                            $lead_status = 'APPLICATION-NEW';
                        }

                        $update_data['lead_status_id'] = $lead_status_id;
                        $update_data['status'] = $lead_status;
                        $update_data['stage'] = $lead_stage;

                        if (!empty($insert_customer_employement)) {
                            //update employment details event

                            $this->Tasks->update(['lje_lead_id' => $lead_id], 'lead_journey_events', ['lje_employment_details' => 1]);
                            $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Employment details added successfully.");
                        }

                        // if ($ekyc_flag == 1) {
                        //     //update ekyc event

                        //     $this->Tasks->update(['lje_lead_id' => $lead_id], 'lead_journey_events', ['lje_ekyc_initiated' => 1]);
                        //     $this->Tasks->update(['lje_lead_id' => $lead_id], 'lead_journey_events', ['lje_ekyc_verified' => 1]);
                        //     $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Ekyc verified successfully.");
                        // }

                        //check residence proof
                        $get_last_residence_proof = $this->Tasks->get_last_residence_proof($pancard);

                        if ($get_last_residence_proof['status'] == 1) {
                            $get_residence_proof_count = $get_last_residence_proof['data']['residence_proof_docs_count'];

                            if ($get_residence_proof_count >= 2) {
                                $this->Tasks->update(['lje_lead_id' => $lead_id], 'lead_journey_events', ['lje_residence_proof_upload' => 1]);
                                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Residence proof uploaded.");
                            }
                        }
                    }

                    $message = "Great news! You are eligible for a loan.";

                    $response_array['lead_id'] = $this->encrypt->encode($lead_id);
                    $response_array['appflyer_lead_id'] = $lead_id;

                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateLeads($lead_id, $update_data);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "Lead generated successfully. ID : " . $lead_id);

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);
                    $response_array['next_step'] = "generate_loan_quote";
                    $response_array['step_percentage'] = 100;
                    break;
                default:
                    throw new ErrorException("Event not found.");
                    break;
            }
            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) { //1=>Api Success, 2=>Api Error, 3=>Eligibility Failed, 4=>Session Expired
            $apiStatusId = 2;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        } catch (CustomException $ce) {
            $apiStatusId = 5;
            $apiStatusData = NULL;
            $apiStatusMessage = $ce->getMessage();
        }

        if ($page_name == "register_now" && $apiStatusId == 3) {
            $apiStatusData = $eligibility_failed_content;
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function saveleadDetails_post() {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $this->commonComponent = new CommonComponent();

        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $update_data = array();

        $message = '';

        $headers = $this->input->request_headers();

        $page_name = $post["event_name"];
        $cust_profile_id = (!empty($post["profile_id"]) ? $this->encrypt->decode($post["profile_id"]) : NULL);
        $pancard = (!empty($post['pancard']) ? $post['pancard'] : NULL);
        $emp_work_mode = (!empty($post['work_mode']) ? $post['work_mode'] : NULL);
        $emp_company_name = (!empty($post['company_name']) ? $post['company_name'] : NULL);
        $emp_company_type_id = (!empty($post['company_type_id']) ? $post['company_type_id'] : NULL);
        $emp_designation = (!empty($post['designation']) ? $post['designation'] : NULL);
        $emp_pincode = (!empty($post['pincode']) ? $post['pincode'] : NULL);
        $emp_address_1 = (!empty($post["address_1"]) ? $post["address_1"] : NULL);
        $emp_address_2 = (!empty($post["address_2"]) ? $post["address_2"] : NULL);
        $emp_landmark = (!empty($post["landmark"]) ? $post["landmark"] : NULL);
        $office_email = (!empty($post["office_email"]) ? $post["office_email"] : NULL);
        $emp_since = (!empty($post["emp_since"]) ? $post["emp_since"] : NULL);
        $lead_id = (!empty($post["lead_id"]) ? $this->encrypt->decode($post["lead_id"]) : NULL);
        $file = !empty($post['file']) ? $post['file'] : NULL;
        $ext = !empty($post['file_ext']) ? $post['file_ext'] : NULL;
        $file_password = !empty($post['file_password']) ? $post['file_password'] : NULL;
        $doc_type = (!empty($post['doc_type']) ? $post['doc_type'] : NULL);
        $aadhaar_last_digits = (!empty($post['aadhaar_no']) ? $post['aadhaar_no'] : NULL);
        $bank_account_number = (!empty($post['account_number']) ? $post['account_number'] : NULL);
        $cnf_bank_account_number = (!empty($post['confirm_account_number']) ? $post['confirm_account_number'] : NULL);
        $bank_account_ifsc = (!empty($post['account_ifsc']) ? $post['account_ifsc'] : NULL);
        $bank_account_type_id = (!empty($post['account_type_id']) ? $post['account_type_id'] : NULL);
        $loan_amount = (!empty($post['amount']) ? $post['amount'] : NULL);
        $loan_tenure = (!empty($post['tenure']) ? $post['tenure'] : NULL);
        $loan_purpose_id = (!empty($post['purpose_id']) ? $post['purpose_id'] : NULL);
        $loan_quote_accepted_id = (!empty($post['accepted_id']) && $post['accepted_id'] == 1) ? 1 : 2;
        $bank_verification_consent = (!empty($post['verification_consent']) && $post['verification_consent'] == 1) ? 1 : 2;
        $run_account_aggregator = (!empty($post['run_account_aggregator']) ? $post['run_account_aggregator'] : 0);
        $run_ekyc_flag = ((!empty($post['run_ekyc_flag']) && $post['run_ekyc_flag'] == 2) ? 2 : 1);

        $journey_type_id = 2;

        if (!empty($ext)) {
            if (strpos($ext, ".") == false) {
                $ext = str_replace(".", "", $ext);
            }
        }

        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];
        $browser = $this->agent->browser();

        $temp_post = $post;
        if (isset($temp_post['file'])) {
            $temp_post['file'] = "File Base64 Format";
        }

        $insert_log_array = array();
        $insert_log_array['mapp_ip_address'] = $ip_address;
        $insert_log_array['mapp_browser_info'] = $browser;
        $insert_log_array['mapp_action_name'] = $page_name;
        $insert_log_array['mapp_request'] = json_encode($temp_post);
        $insert_log_array['mapp_profile_id'] = $cust_profile_id;

        $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            if (empty($page_name)) {
                throw new ErrorException("Invalid Page Access!");
            }

            $token_verification = $this->checkAuthorization($headers, $page_name);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $stage_verification = $this->applicationStageVerification($cust_profile_id, $page_name, 3);

            if ($stage_verification['status'] != 1) {
                throw new ErrorException($stage_verification['message']);
            }

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("profile_id", "Profile ID", "required");
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_customer_profile_details['status'] != 1) {
                throw new ErrorException("Your profile detail does not found.");
            }

            $customer_profile_details = $get_customer_profile_details['customer_profile_details'];

            $lead_id = $customer_profile_details['cp_lead_id'];
            $user_type = $customer_profile_details['cp_user_type'];

            $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

            if ($get_lead_details['status'] != 1) {
                throw new ErrorException("Application details not found!");
            }

            $leadDetails = $get_lead_details['lead_details'];

            if (in_array($leadDetails['lead_status_id'], array(1, 4))) {
                $get_journey_stage = $this->Tasks->getMasterJourneyStage("reject", $this->journey_type_id);
                throw new RuntimeException("Your application has been moved to next stage. Please check your application status.");
            }

            if (in_array($leadDetails['lead_status_id'], array(8, 9))) {
                $get_journey_stage = $this->Tasks->getMasterJourneyStage("reject", $this->journey_type_id);

                if ($get_journey_stage['status'] == 1) {
                    $get_journey_stage = $get_journey_stage['master_journey_stage'];
                    $journey_id = $get_journey_stage['m_journey_id'];
                }


                $update_profile_data['cp_status_id'] = 9;
                $update_profile_data['cp_lead_id'] = NULL;
                $update_profile_data['cp_journey_stage'] = NULL;
                $update_profile_data['cp_is_journey_completed'] = NULL;
                $update_profile_data['cp_updated_at'] = date("Y-m-d H:i:s");

                $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                if ($check_page_status == false) {
                    $update_profile_data['cp_journey_stage'] = $journey_id;
                }

                $this->Tasks->updateCustomerProfile($cust_profile_id, $update_profile_data);

                $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                throw new RuntimeException("Your application has been rejected. Please try applying again.");
            }

            switch ($page_name) {
                case "generate_loan_quote":
                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $bre_quote_engine = $this->commonComponent->call_bre_quote_engine($lead_id);

                    if ($bre_quote_engine['status'] != 1) {
                        throw new ErrorException($bre_quote_engine['error']);
                    }

                    $loan_quote_remark = "A loan quote has been successfully generated for the customer.";
                    $loan_quote_remark .= "<br/> eligible_foir_percentage : " . $bre_quote_engine['eligible_foir_percentage'];
                    $loan_quote_remark .= "<br/> min_loan_amount : " . $bre_quote_engine['min_loan_amount'];
                    $loan_quote_remark .= "<br/> max_loan_amount : " . $bre_quote_engine['max_loan_amount'];

                    $loan_quote_remark .= "<br/> min_loan_tenure : " . $bre_quote_engine['min_loan_tenure'] . " days";
                    $loan_quote_remark .= "<br/> max_loan_tenure : " . $bre_quote_engine['max_loan_tenure'] . " days";
                    $loan_quote_remark .= "<br/> interest_rate : " . $bre_quote_engine['interest_rate'] . "% per day";

                    $loan_quote_remark .= "<br/> processing_fee : " . $bre_quote_engine['processing_fee'];

                    // $response_array['max_loan_amount'] = (($bre_quote_engine['max_loan_amount'] > 100000 ? 100000 : $bre_quote_engine['max_loan_amount'] <= 0 ? 100000 : $bre_quote_engine);
                    $response_array['max_loan_amount'] = 100000;
                    if ($bre_quote_engine['max_loan_amount'] > 100000) {
                        $response_array['max_loan_amount'] = 100000;
                    } else if ($bre_quote_engine['max_loan_amount'] <= 0) {
                        $response_array['max_loan_amount'] = 100000;
                    } else {
                        $response_array['max_loan_amount'] = $bre_quote_engine['max_loan_amount'];
                    }

                    $response_array['min_loan_amount'] = $bre_quote_engine['min_loan_amount'];
                    $response_array['min_loan_tenure'] = $bre_quote_engine['min_loan_tenure'];
                    $response_array['max_loan_tenure'] = $bre_quote_engine['max_loan_tenure'];
                    $response_array['interest_rate'] = round($bre_quote_engine['interest_rate'], 2);
                    $response_array['processing_fee'] = $bre_quote_engine['processing_fee'];

                    $response_array['next_step'] = "loan_quotation_decision";
                    $response_array['step_percentage'] = 0;

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $loan_quote_remark);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_journey_stage'] = $journey_id;
                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    break;

                case "loan_quotation_decision":

                    $this->form_validation->set_rules("amount", "Loan amount", "required");
                    $this->form_validation->set_rules("tenure", "Loan tenure", "required|numeric");
                    $this->form_validation->set_rules("purpose_id", "Loan Purpose", "required|numeric");
                    $this->form_validation->set_rules("accepted_id", "Loan Quote Accpetance", "required|numeric");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $loan_quote_accepted_id = 1;
                    $monthly_income = $leadDetails['monthly_salary_amount'];

                    if (($loan_quote_accepted_id == 1 && $monthly_income > 35000) || $user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $get_loan_purpose_details = $this->Tasks->selectdata(['enduse_id' => $loan_purpose_id], 'enduse_name', 'master_enduse');

                        if ($get_loan_purpose_details->num_rows() > 0) {
                            $loan_purpose_details = $get_loan_purpose_details->row_array();
                        }

                        $update_data['loan_amount'] = $loan_amount;
                        $update_data['tenure'] = $loan_tenure;
                        $update_data['purpose'] = $loan_purpose_details['enduse_name'];

                        $lead_status_id = 42;
                        $lead_stage = 'S1';
                        $lead_status = 'LEAD-PARTIAL';

                        if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                            $lead_status_id = 4;
                            $lead_stage = 'S4';
                            $lead_status = 'APPLICATION-NEW';
                        }

                        $message = "Loan Details Saved Successfully";
                        $lead_remarks = "Loan quote has been accepted.";
                        $lead_remarks .= "<br/>Applied Loan Amount : $loan_amount";
                        $lead_remarks .= "<br/>Applied Loan Tenure : $loan_tenure";
                    } elseif ($loan_quote_accepted_id == 1 && $monthly_income <= 26000) {
                        $lead_status_id = 8;
                        $lead_stage = 'S8';
                        $lead_status = 'SYSTEM-REJECT';

                        $update_data['lead_rejected_reason_id'] = 14;
                        $update_data['lead_rejected_datetime'] = date("Y-m-d H:i:s");

                        $update_customer_profile['cp_lead_id'] = NULL;

                        $insertLeadRejectionReason['lrr_lead_id'] = $lead_id;
                        $insertLeadRejectionReason['lrr_rejected_reason_id'] = 14;
                        $insertLeadRejectionReason['lrr_rejected_remarks'] = 'Loan offer not accepted';
                        $this->Tasks->insert("lead_rejection_reasons", $insertLeadRejectionReason);

                        $update_lje_journey['lje_reject'] = 1;
                        $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $update_lje_journey);

                        $message = "Thank you for applying for the loan. We're sorry to see you go.";
                        $lead_remarks = "Loan quote has been rejected.";
                    } else {
                        $lead_status_id = 9;
                        $lead_stage = 'S9';
                        $lead_status = 'REJECT';

                        $update_data['lead_rejected_reason_id'] = 57;
                        $update_data['lead_rejected_datetime'] = date("Y-m-d H:i:s");

                        $update_customer_profile['cp_lead_id'] = NULL;

                        $insertLeadRejectionReason['lrr_lead_id'] = $lead_id;
                        $insertLeadRejectionReason['lrr_rejected_reason_id'] = 57;
                        $insertLeadRejectionReason['lrr_rejected_remarks'] = 'Loan offer not accepted';
                        $this->Tasks->insert("lead_rejection_reasons", $insertLeadRejectionReason);

                        $update_lje_journey['lje_reject'] = 1;
                        $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $update_lje_journey);

                        $message = "Thank you for applying for the loan. We're sorry to see you go.";
                        $lead_remarks = "Loan quote has been rejected.";
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
                    if ($lead_status_id == 9 || $lead_status_id == 8) {
                        throw new RuntimeException($message);
                    }

                    $response_array = NULL;
                    $response_array['next_step'] = "employment_details";
                    $response_array['step_percentage'] = 0;
                    break;

                case "employment_details":
                    $this->form_validation->set_rules("work_mode", "Work Mode", "required");
                    $this->form_validation->set_rules("company_name", "Company Name", "required");
                    $this->form_validation->set_rules("address_1", "Company Address Line1", "required");
                    $this->form_validation->set_rules("company_type_id", "Company Name", "required");
                    $this->form_validation->set_rules("pincode", "Employment Address Pincode", "required|numeric");
                    $this->form_validation->set_rules("designation", "Employmee Designation", "required");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }
                    $update_customer_employment = array();

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!empty($office_email)) {
                        if ($office_email == $customer_profile_details['cp_personal_email']) {
                            throw new ErrorException("Official email and Personal email cannot be same");
                        }
                    }

                    $getCityStateByPincode = $this->Tasks->getCityStateByPincode($emp_pincode);
                    if ($getCityStateByPincode['status'] != 1) {
                        throw new ErrorException("Pincode not found");
                    }

                    $response_array['city_id'] = $getCityStateByPincode['get_city_state_details']['city_id'];
                    $response_array['city_name'] = $getCityStateByPincode['get_city_state_details']['city_name'];
                    $response_array['state_id'] = $getCityStateByPincode['get_city_state_details']['state_id'];
                    $response_array['state_name'] = $getCityStateByPincode['get_city_state_details']['state_name'];
                    $response_array['pincode'] = $getCityStateByPincode['get_city_state_details']['pincode'];

                    $response_array['next_step'] = "bank_statement_upload";
                    $response_array['step_percentage'] = 15;

                    $get_company_type = $this->Tasks->selectdata(['m_company_type_id' => $emp_company_type_id], 'm_company_type_name', 'master_company_type');

                    if ($get_company_type->num_rows() <= 0) {
                        throw new ErrorException("Invalid company type selected");
                    }

                    $company_type = $get_company_type->row_array();
                    $update_customer_employment['emp_email'] = $office_email;
                    $update_customer_employment['emp_work_mode'] = $emp_work_mode;
                    $update_customer_employment['state_id'] = $response_array['state_id'];
                    $update_customer_employment['city_id'] = $response_array['city_id'];
                    $update_customer_employment['emp_state'] = $response_array['state_name'];
                    $update_customer_employment['emp_city'] = $response_array['city_name'];
                    $update_customer_employment['emp_pincode'] = $emp_pincode;
                    $update_customer_employment['emp_house'] = $emp_address_1;
                    $update_customer_employment['emp_street'] = $emp_address_2;
                    $update_customer_employment['emp_landmark'] = $emp_landmark;
                    $update_customer_employment['employer_name'] = $emp_company_name;
                    $update_customer_employment['emp_employer_type'] = $company_type['m_company_type_name'];
                    $update_customer_employment['emp_designation'] = $emp_designation;
                    $update_customer_employment['updated_on'] = date("Y-m-d H:i:s");
                    $update_customer_employment['emp_email'] = $office_email;
                    $update_customer_employment['emp_residence_since'] = date("Y-m-d", strtotime($emp_since));
                    $this->Tasks->updateCustomerEmployment($lead_id, $update_customer_employment);

                    $message = "Employment Details Saved Successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['alternate_email'] = $office_email;
                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);
                    $update_data = array();
                    $update_data['alternate_email'] = $office_email;
                    $this->Tasks->updateLeadCustomer($lead_id, $update_data);
                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $response_array = NULL;
                    $response_array['next_step'] = "bank_statement_upload";
                    $response_array['step_percentage'] = 20;
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    break;

                case "bank_statement_upload":
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[pdf,PDF]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!in_array($ext, array("pdf", "PDF"))) {
                        throw new ErrorException("Bank Statement should be in pdf format only.");
                    }

                    $leadDetails = $this->Tasks->getLeadDetails($lead_id);

                    $leadDetails = $leadDetails['lead_details'];

                    $get_master_document = $this->Tasks->getMasterDocument(6);

                    $get_master_document = $get_master_document['master_document_details'];

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['ext'] = strtolower($ext);

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document($lead_id, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $lead_id . "_lms_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }

                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("Document not uploaded! Try Again");
                    }

                    $insert_docs_array = array();
                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => !empty($file_password) ? $file_password : "",
                        "file" => $image_name,
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->Tasks->insert('docs', $insert_docs_array);
                    $message = "Bank statement uploaded successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    $response_array = NULL;
                    $response_array['next_step'] = "banking_details";
                    $response_array['step_percentage'] = 30;
                    break;

                case "pay_slip_upload":
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[pdf,PDF,jpg,png,jpeg,JPG,JPEG,PNG]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!in_array($ext, array("pdf", "PDF", "jpg", "png", "jpeg", "JPG", "JPEG", "PNG"))) {
                        throw new ErrorException("Pay Slip should be in pdf,jpg, or png format only.");
                    }

                    $leadDetails = $this->Tasks->getLeadDetails($lead_id);

                    $leadDetails = $leadDetails['lead_details'];

                    $get_master_document = $this->Tasks->getMasterDocument(16);

                    $get_master_document = $get_master_document['master_document_details'];

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['ext'] = strtolower($ext);

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document($lead_id, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $lead_id . "_lms_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }
                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("Document not uploaded! Try Again");
                    }

                    $insert_docs_array = array();
                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => !empty($file_password) ? $file_password : "",
                        "file" => $image_name,
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->Tasks->insert('docs', $insert_docs_array);

                    $message = "Pay slip uploaded successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    $response_array = NULL;
                    $response_array['next_step'] = "banking_details";
                    $response_array['step_percentage'] = 45;
                    break;

                case "pan_upload":
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[pdf,jpg,png,jpeg,JPG,JPEG,PNG]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!in_array($ext, array("pdf", "jpg", "png", "jpeg", "JPG", "JPEG", "PNG"))) {
                        throw new ErrorException("Pancard should be in pdf, jpg, or png format only.");
                    }

                    $leadDetails = $this->Tasks->getLeadDetails($lead_id);

                    $leadDetails = $leadDetails['lead_details'];

                    $get_master_document = $this->Tasks->getMasterDocument(4);

                    $get_master_document = $get_master_document['master_document_details'];

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['ext'] = $ext;

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document($lead_id, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $lead_id . "_lms_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }
                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("PAN card document does not uploaded. Please try again.");
                    }


                    $insert_docs_array = array();
                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => !empty($file_password) ? $file_password : "",
                        "file" => $image_name,
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->Tasks->insert('docs', $insert_docs_array);

                    $message = "Pancard uploaded successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    $response_array = NULL;
                    $response_array['next_step'] = "banking_details";
                    $response_array['step_percentage'] = 60;
                    break;

                case "aadhaar_upload":
                    $this->form_validation->set_rules("doc_type", "Document Type", "required");
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[pdf,jpg,png,jpeg,JPG,JPEG,PNG]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $run_aadhaar_ocr = 0;

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!in_array($ext, array("pdf", "jpg", "png", "jpeg", "JPG", "JPEG", "PNG"))) {
                        throw new ErrorException("Aadhaar should be in pdf, jpg, or png format only.");
                    }

                    if ($doc_type == "aadhaar_front") {
                        $document_type_id = 1;
                    } else if ($doc_type == "aadhaar_back") {
                        $document_type_id = 2;
                    } else if ($doc_type == "eaadhaar") {
                        $document_type_id = 3;
                    }

                    $leadDetails = $this->Tasks->getLeadDetails($lead_id);

                    $leadDetails = $leadDetails['lead_details'];

                    $get_master_document = $this->Tasks->getMasterDocument($document_type_id);

                    $get_master_document = $get_master_document['master_document_details'];

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['ext'] = $ext;

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document($lead_id, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $lead_id . "_lms_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }
                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("Document not uploaded! Try Again");
                    }

                    $insert_docs_array = array();

                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => !empty($file_password) ? $file_password : "",
                        "file" => $image_name,
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->Tasks->insert('docs', $insert_docs_array);

                    if ($document_type_id == 2) {

                        $check_aadhaar_front = $this->Tasks->selectdata(['docs_master_id' => 1, 'lead_id' => $lead_id], '*', 'docs');

                        if ($check_aadhaar_front->num_rows() > 0) {
                            $run_aadhaar_ocr = 1;
                        }
                    }

                    if ($document_type_id == 3) {
                        $run_aadhaar_ocr = 1;
                    }

                    $message = ucwords(str_replace("_", " ", $doc_type)) . " uploaded successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    $response_array = NULL;
                    $response_array['next_step'] = "banking_details";
                    $response_array['step_percentage'] = 75;
                    break;

                case "residence_proof_upload":
                    $this->form_validation->set_rules("doc_type", "Document Type", "required");
                    $this->form_validation->set_rules("file", "File", "required");
                    $this->form_validation->set_rules("file_ext", "File Extension", "required|in_list[pdf,PDF,jpg,png,jpeg,JPG,JPEG,PNG]");
                    if ($this->form_validation->run() == FALSE) {
                        throw new ErrorException(strip_tags(validation_errors()));
                    }

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    if (!in_array($ext, array("pdf", "PDF", "jpg", "png", "jpeg", "JPG", "JPEG", "PNG"))) {
                        throw new ErrorException(ucwords(str_replace("_", " ", $doc_type)) . " should be in pdf, jpg, or png format only.");
                    }

                    if ($doc_type == "credit_card_statement") {
                        $document_type_id = 13;
                    } else if ($doc_type == "electricity_bill") {
                        $document_type_id = 33;
                    } else if ($doc_type == "landline_bill") {
                        $document_type_id = 34;
                    } else if ($doc_type == "gas_bill") {
                        $document_type_id = 35;
                    } else if ($doc_type == "water_bill") {
                        $document_type_id = 36;
                    } else if ($doc_type == "rent_agreement") {
                        $document_type_id = 100;
                    }

                    $leadDetails = $this->Tasks->getLeadDetails($lead_id);

                    $leadDetails = $leadDetails['lead_details'];

                    $get_master_document = $this->Tasks->getMasterDocument($document_type_id);

                    $get_master_document = $get_master_document['master_document_details'];

                    $request_array['flag'] = 1;
                    $request_array['file'] = $file;
                    $request_array['ext'] = strtotime($ext);

                    if (API_DOC_S3_FLAG == true) {
                        $upload_file = $this->commonComponent->upload_document($lead_id, $request_array);
                        $image_name = $upload_file['file_name'];
                    } else {
                        $image_name = $lead_id . "_lms_" . date("YmdHis") . "_" . rand(1000, 9999) . "." . $ext;
                        $image_upload_dir = UPLOAD_PATH . $image_name;
                        $doc_file = file_put_contents($image_upload_dir, base64_decode($file));
                        if (!empty($doc_file)) {
                            $upload_file['status'] = 1;
                        } else {
                            $upload_file['status'] = 0;
                        }
                    }
                    if ($upload_file['status'] != 1) {
                        throw new ErrorException("Document not uploaded! Try Again");
                    }

                    $insert_docs_array = array();
                    $insert_docs_array = [
                        "lead_id" => $lead_id,
                        "docs_master_id" => $get_master_document['id'],
                        "customer_id" => $leadDetails['customer_id'],
                        "pancard" => $leadDetails['pancard'],
                        "mobile" => $leadDetails['mobile'],
                        "docs_type" => $get_master_document['docs_type'],
                        "sub_docs_type" => $get_master_document['docs_sub_type'],
                        "pwd" => !empty($file_password) ? $file_password : "",
                        "file" => $image_name,
                        "ip" => $ip_address,
                        "created_on" => date('Y-m-d H:i:s')
                    ];

                    $this->Tasks->insert('docs', $insert_docs_array);
                    $message = ucwords(str_replace("_", " ", $doc_type)) . " uploaded successfully";

                    $lead_status_id = 42;
                    $lead_stage = 'S1';
                    $lead_status = 'LEAD-PARTIAL';

                    if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                        $lead_status_id = 4;
                        $lead_stage = 'S4';
                        $lead_status = 'APPLICATION-NEW';
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $message);
                    $response_array = NULL;
                    $response_array['next_step'] = "banking_details";
                    $response_array['step_percentage'] = 90;
                    break;

                case "banking_details":

                    if ($bank_verification_consent == 1) {
                        $this->form_validation->set_rules("account_number", "Bank Account No", "required");
                        $this->form_validation->set_rules("confirm_account_number", "Bank Account No", "required");
                        $this->form_validation->set_rules("account_ifsc", "IFSC Code", "required|alpha_numeric");
                        $this->form_validation->set_rules("account_type_id", "Bank Account Type", "required");
                        if ($this->form_validation->run() == FALSE) {
                            throw new ErrorException(strip_tags(validation_errors()));
                        }

                        if ($bank_account_number != $cnf_bank_account_number) {
                            throw new ErrorException("Account number do not match.");
                        }

                        $get_bank_details = $this->Tasks->get_bank_details($bank_account_ifsc);

                        if ($get_bank_details['status'] != 1) {
                            $get_bank_details['bank_account_details']['bank_name'] = "NA";
                            $get_bank_details['bank_account_details']['bank_ifsc'] = $bank_account_ifsc;
                            $get_bank_details['bank_account_details']['bank_branch'] = "NA";
                        }

                        $get_bank_details = $get_bank_details['bank_account_details'];

                        $get_bank_account_type = $this->Tasks->getBankTypeById($bank_account_type_id);

                        if ($get_bank_account_type['status'] != 1) {
                            throw new ErrorException("Invalid Bank Account Type");
                        }

                        $get_bank_account_type = $get_bank_account_type['master_bank_type_details'];

                        $get_customer_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

                        if ($get_customer_details['status'] != 1) {
                            throw new ErrorException("Customer details not found!");
                        }

                        $get_customer_details = $get_customer_details['customer_profile_details'];

                        $customer_full_name = !empty($get_customer_details['cp_first_name']) ? $get_customer_details['cp_first_name'] : '';
                        $customer_full_name .= !empty($get_customer_details['cp_middle_name']) ? ' ' . $get_customer_details['cp_middle_name'] : '';
                        $customer_full_name .= !empty($get_customer_details['cp_sur_name']) ? ' ' . $get_customer_details['cp_sur_name'] : '';

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

                        // $verify_bank_account = $this->commonComponent->payday_bank_account_verification_api($lead_id, array('cust_banking_id' => $cust_banking_id));

                        // if ($verify_bank_account['status'] != 1) {
                        //     throw new ErrorException($verify_bank_account['error_msg']);
                        // }

                        // $conditions2 = ['lead_id' => $lead_id, "account_status_id" => 1];
                        // $data2 = ['account_status' => "NO", "account_status_id" => 0];
                        // $this->Tasks->update($conditions2, "customer_banking", $data2);

                        // $data = [
                        //     'account_status' => "ACCOUNT AND NAME VERIFIED SUCCESSFULLY",
                        //     'account_status_id' => 1,
                        //     'remark' => 'OK',
                        //     'updated_on' => date("Y-m-d H:i:s")
                        // ];

                        // $conditions = ['id' => $cust_banking_id];

                        // $result = $this->Tasks->update($conditions, "customer_banking", $data);

                        // if ($result != 1) {
                        //     throw new ErrorException('Failed to Verify. try again');
                        // }
                    }

                    // Generate Application No
                    $get_application_no = $this->Tasks->generateApplicationNo($lead_id);
                    $this->Tasks->updateLeads($lead_id, array('application_no' => $get_application_no));

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage($page_name, $this->journey_type_id);
                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("thank_you", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }

                    $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

                    if ($get_lead_details['status'] != 1) {
                        throw new Exception("Application details doesn't exist");
                    }

                    $lead_details = $get_lead_details['lead_details'];

                    $reference_no = $lead_details['lead_reference_no'];

                    // if (($lead_details['product_id'] == 1) && ($lead_details['user_type'] == "REPEAT")) {
                    //     $cam = $this->generateCAM($lead_id);
                    // }

                    $lead_status_id = 4;
                    $lead_stage = 'S4';
                    $lead_status = 'APPLICATION-NEW';

                    $update_data['lead_doable_to_application_status'] = 0;
                    if ($get_customer_details['cp_doable_to_application_status'] == 1) {
                        $update_data['lead_doable_to_application_status'] = 1; //campaign
                    } else if (!empty($lead_details['lead_screener_assign_user_id']) && in_array($lead_details['lead_status_id'], array(41, 42))) {
                        $update_data['lead_doable_to_application_status'] = 3; //Assisted Model
                    }

                    if (!empty($lead_details['lead_screener_assign_user_id']) && in_array($lead_details['lead_status_id'], array(41, 42))) {
                        $update_data['lead_credit_assign_user_id'] = $lead_details['lead_screener_assign_user_id'];
                        $update_data['lead_credit_assign_datetime'] = date("Y-m-d H:i:s");
                        $lead_status_id = 5;
                        $lead_stage = 'S5';
                        $lead_status = 'APPLICATION-INPROCESS';
                        $lead_remarks = "Application Move to Process as Assisted Model Doable Lead";
                        $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
                    }

                    $update_data['lead_status_id'] = $lead_status_id;
                    $update_data['status'] = $lead_status;
                    $update_data['stage'] = $lead_stage;
                    $update_data['application_status'] = 1;
                    $update_data['lead_application_created_on'] = date("Y-m-d H:i:s");
                    $update_data['lead_journey_stage_id'] = $journey_id;
                    $update_data['updated_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->updateLeads($lead_id, $update_data);

                    $check_page_status = $this->Tasks->selectJourneyEvents($lead_id, $journey_id, 2);

                    if ($check_page_status == false) {
                        $update_customer_profile['cp_journey_stage'] = $journey_id;
                    }

                    $update_customer_profile['cp_status_id'] = $lead_status_id;
                    $update_customer_profile['cp_is_journey_completed'] = 1;
                    $update_customer_profile['cp_data_delete_flag'] = 0;
                    $update_customer_profile['cp_data_delete_datetime'] = NULL;
                    $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id);

                    $message = "Your loan application has been successfully submitted. Your application reference number is $reference_no. We will contact you soon.";
                    $sms_input_data = array();
                    $sms_input_data['mobile'] = $lead_details['mobile'];
                    $sms_input_data['name'] = $lead_details['first_name'];
                    $sms_input_data['refrence_no'] = $reference_no;

                    // $this->commonComponent->payday_sms_api(2, $lead_id, $sms_input_data);

                    $this->commonComponent->sent_lead_thank_you_email($lead_id, $lead_details['email'], $lead_details['first_name'], $reference_no);

                    $cp_utm_source = $get_customer_details['cp_utm_source'];
                    // $cp_user_type = $get_customer_details['cp_user_type'];
                    // $get_affiliate_data = $this->Tasks->selectdata(['mmc_affiliate_flag' => 1, 'mmc_active' => 1, 'mmc_deleted' => 0, 'mmc_name' => $cp_utm_source], 'mmc_name', 'master_marketing_channel');

                    // if (($get_affiliate_data->num_rows() > 0) && ($cp_user_type == "NEW")) {
                    //     $this->commonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, array('event_type_id' => 3));
                    // }

                    // $this->commonComponent->call_whatsapp_api(1, $lead_id);

                    $lead_remarks = "The application has been submitted with reference number $reference_no.";
                    $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
                    $response_array['next_step'] = "completed";
                    $response_array['step_percentage'] = 100;
                    break;
                default:
                    throw new ErrorException("Page Not Found");
                    break;
            }

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        } catch (CustomException $ce) {
            $apiStatusId = 5;
            $apiStatusData = NULL;
            $apiStatusMessage = $ce->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getCustomerDetails_post() {

        $response_array = array();

        $apiStatusData = array();

        $screen_details = array();

        $customer_details = array();

        $bank_statement_upload = 0;

        $pay_slip_upload = 0;

        $residence_proof_upload = 0;

        $doc_details = array();

        $aadhaar_fetched = 2;

        $pan_fetched = 2;

        $residence_details = 0;

        $selfie_upload = 0;

        $generate_loan_quote = 0;

        $loan_quote = 0;

        $application_status = array();

        $application_stage_submitted = 0;
        $application_stage_in_review = 0;
        $application_stage_sanctioned = 0;
        $application_stage_eSign = 0;
        $application_stage_disbursed = 0;

        $loan_recommended = null;
        $roi = null;
        $penal_roi = null;
        $sanction_date = null;
        $repayment_amount = null;
        $repayment_date = null;
        $tenure = null;
        $admin_processing_fee = null;

        $application_stage = array();

        $income_verification = 0;
        $check_eligibility = 0;
        $pan_verification = 0;
        $personal_details_verification = 0;
        $registration_verification = 0;
        $loan_quote_verification = 0;
        $employment_details_verification = 0;
        $ekyc_verification = 0;
        $documents_upload = 0;
        $bank_details_verification = 0;
        $application_submission = 0;

        $aa_run_flag = 0;
        $account_aggregator_consent = 0;
        $bank_statement_fetched = 0;
        $ekyc_skip_button_flag = 0;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        $cust_profile_id = !empty($post['profile_id']) ? $this->encrypt->decode($post['profile_id']) : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($cust_profile_id)) {
                throw new ErrorException("Lead ID or Customer Profile ID cannot be empty");
            }

            $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];
            $browser = $this->agent->browser();
            $insert_log_array = array();
            $insert_log_array['mapp_ip_address'] = $ip_address;
            $insert_log_array['mapp_browser_info'] = $browser;
            $insert_log_array['mapp_action_name'] = "getCustomerDetails";
            $insert_log_array['mapp_request'] = json_encode($post);
            $insert_log_array['mapp_profile_id'] = $cust_profile_id;
            $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

            $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_customer_profile_details['status'] != 1) {
                throw new ErrorException("Customer Details Not Found");
            }

            $get_customer_profile_details = $get_customer_profile_details['customer_profile_details'];
            $lead_id = !empty($get_customer_profile_details['cp_lead_id']) ? $get_customer_profile_details['cp_lead_id'] : NULL;

            $mobile = (!empty($get_customer_profile_details['cp_mobile']) ? $get_customer_profile_details['cp_mobile'] : NULL);
            $mobile_verified_status = (!empty($get_customer_profile_details['cp_is_mobile_verified']) ? $get_customer_profile_details['cp_is_mobile_verified'] : NULL);
            $pancard = (!empty($get_customer_profile_details['cp_pancard']) ? $get_customer_profile_details['cp_pancard'] : NULL);
            $pancard_verified_status = (!empty($get_customer_profile_details['cp_pancard_verified_status']) ? $get_customer_profile_details['cp_pancard_verified_status'] : NULL);
            $first_name = (!empty($get_customer_profile_details['cp_first_name']) ? $get_customer_profile_details['cp_first_name'] : NULL);
            $middle_name = (!empty($get_customer_profile_details['cp_middle_name']) ? $get_customer_profile_details['cp_middle_name'] : NULL);
            $sur_name = (!empty($get_customer_profile_details['cp_sur_name']) ? $get_customer_profile_details['cp_sur_name'] : NULL);
            $dob = (!empty($get_customer_profile_details['cp_dob']) ? date("d-m-Y", strtotime($get_customer_profile_details['cp_dob'])) : NULL);
            $gender = (!empty($get_customer_profile_details['cp_gender']) ? $get_customer_profile_details['cp_gender'] : NULL);
            $geo_lat = (!empty($get_customer_profile_details['cp_geo_lat']) ? $get_customer_profile_details['cp_geo_lat'] : NULL);
            $geo_long = (!empty($get_customer_profile_details['cp_geo_long']) ? $get_customer_profile_details['cp_geo_long'] : NULL);
            $residence_pincode = (!empty($get_customer_profile_details['cp_residence_pincode']) ? $get_customer_profile_details['cp_residence_pincode'] : NULL);
            $residence_city_id = (!empty($get_customer_profile_details['cp_residence_city_id']) ? $get_customer_profile_details['cp_residence_city_id'] : NULL);
            $residence_state_id = (!empty($get_customer_profile_details['cp_residence_state_id']) ? $get_customer_profile_details['cp_residence_state_id'] : NULL);
            $residence_type_id = (!empty($get_customer_profile_details["cp_residence_type_id"]) ? $get_customer_profile_details["cp_residence_type_id"] : NULL);
            $residence_address_1 = (!empty($get_customer_profile_details["cp_residence_address_1"]) ? $get_customer_profile_details["cp_residence_address_1"] : NULL);
            $residence_address_2 = (!empty($get_customer_profile_details["cp_residence_address_2"]) ? $get_customer_profile_details["cp_residence_address_2"] : NULL);
            $residence_landmark = (!empty($get_customer_profile_details["cp_residence_landmark"]) ? $get_customer_profile_details["cp_residence_landmark"] : NULL);
            $personal_email = (!empty($get_customer_profile_details["cp_personal_email"]) ? $get_customer_profile_details["cp_personal_email"] : NULL);
            $personal_email_verified_status = (!empty($get_customer_profile_details["cp_personal_email_verified_status"]) ? $get_customer_profile_details["cp_personal_email_verified_status"] : NULL);
            $income_type_id = !empty($get_customer_profile_details['cp_income_type_id']) ? $get_customer_profile_details['cp_income_type_id'] : NULL;
            $monthly_income = !empty($get_customer_profile_details['cp_monthly_income']) ? $get_customer_profile_details['cp_monthly_income'] : NULL;
            $salary_mode_id = !empty($get_customer_profile_details['cp_salary_mode']) ? $get_customer_profile_details['cp_salary_mode'] : NULL;
            $salary_date = !empty($get_customer_profile_details['cp_salary_date']) ? $get_customer_profile_details['cp_salary_date'] : NULL;
            $marital_status_id = !empty($get_customer_profile_details['cp_marital_status_id']) ? $get_customer_profile_details['cp_marital_status_id'] : NULL;
            $spouse_name = !empty($get_customer_profile_details['cp_spouse_name']) ? $get_customer_profile_details['cp_spouse_name'] : NULL;
            $journey_stage_id = !empty($get_customer_profile_details['cp_journey_stage']) ? $get_customer_profile_details['cp_journey_stage'] : NULL;
            $selfie_doc = (!empty($get_customer_profile_details['cp_profile_pic']) ? COLLEX_DOC_URL . $get_customer_profile_details['cp_profile_pic'] : NULL);
            $user_type = (!empty($get_customer_profile_details['cp_user_type']) && $get_customer_profile_details['cp_user_type'] == "REPEAT") ? "REPEAT" : "NEW";
            $is_journey_completed = (!empty($get_customer_profile_details['cp_is_journey_completed']) && $get_customer_profile_details['cp_is_journey_completed'] == 1) ? 1 : 0;
            $registration_successful = (!empty($get_customer_profile_details['cp_registration_successful']) && $get_customer_profile_details['cp_registration_successful'] == 1) ? 1 : 0;
            $aadhaar_no = !empty($get_customer_profile_details['cp_aadhaar_no']) ? $get_customer_profile_details['cp_aadhaar_no'] : 0;

            $get_journey_events = $this->Tasks->selectdata(['pje_profile_id' => $cust_profile_id], '*', 'profile_journey_events');

            if ($get_journey_events->num_rows() > 0) {
                $journey_events = $get_journey_events->row_array();
            }

            if (!empty($lead_id)) {

                $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

                if ($get_lead_details['status'] == 1) {
                    $lead_details = $get_lead_details['lead_details'];
                }

                if (in_array($lead_details['lead_status_id'], array(8, 9))) {
                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("reject", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }


                    $update_profile_data['cp_status_id'] = 9;
                    $update_profile_data['cp_lead_id'] = NULL;

                    $update_profile_data['cp_journey_stage'] = NULL;
                    $update_profile_data['cp_is_journey_completed'] = NULL;
                    $update_profile_data['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_profile_data);

                    $update_lje_journey['lje_reject'] = 1;
                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $update_lje_journey);
                    $journey_stage_id = NULL;

                    throw new RuntimeException("Your application has been rejected. Please try applying again.");
                }
                if (($lead_details['application_status'] > 0)) {
                    $application_stage_submitted = 2;
                    $application_stage_in_review = 1;
                } else if (($lead_details['lead_reference_no'] > 0)) {
                    $application_stage_submitted = 1;
                }

                if (!empty($lead_details['lead_credit_approve_user_id'])) {
                    $application_stage_sanctioned = 2;
                    $application_stage_eSign = 1;
                    $application_stage_in_review = 2;
                } else if (!empty($lead_details['lead_credit_assign_user_id'])) {
                    $application_stage_sanctioned = 1;
                    $application_stage_in_review = 2;
                }



                $get_cam_details = $this->Tasks->get_cam_details($lead_id);

                if ($get_cam_details['status'] == 1) {
                    $cam_details = $get_cam_details['data']['cam_details'];
                    if (!empty($cam_details['cam_sanction_letter_esgin_file_name']) && !empty($cam_details['cam_sanction_letter_esgin_on'])) {
                        $application_stage_eSign = 2;
                        $application_stage_sanctioned = 2;
                        $application_stage_in_review = 2;
                        $application_stage_disbursed = 1;
                    }

                    $loan_recommended = $cam_details['loan_recommended'];
                    $roi = $cam_details['roi'];
                    $penal_roi = $cam_details['panel_roi'];
                    $repayment_amount = $cam_details['repayment_amount'];
                    $repayment_date = date("d-m-Y", strtotime($cam_details['repayment_date']));
                    $sanction_date = date("d-m-Y", strtotime($cam_details['cam_sanction_letter_esgin_on']));
                    $tenure = $cam_details['tenure'];
                    $admin_processing_fee = $cam_details['admin_fee'];
                }

                if (!empty($lead_details['lead_disbursal_approve_user_id'])) {
                    $application_stage_disbursed = 2;
                    $application_stage_eSign = 2;
                    $application_stage_sanctioned = 2;
                    $application_stage_in_review = 2;
                }



                $get_lead_customer_details = $this->Tasks->get_lead_customer_details($lead_id);

                if ($get_lead_customer_details['status'] == 1) {
                    $lead_customer_details = $get_lead_customer_details['lead_customer_details'];
                }

                $get_employment_details = $this->Tasks->get_customer_employment_details($lead_id);

                if ($get_employment_details['status'] == 1) {
                    $get_customer_employment_details = $get_employment_details['customer_employment_details'];
                }

                $get_banking_details = $this->Tasks->get_customer_banking_details($lead_id);

                if ($get_banking_details['status'] == 1) {
                    $get_customer_banking_details = $get_banking_details['data']['customer_banking_details'];
                }

                $get_lead_journey_events = $this->Tasks->selectdata(['lje_lead_id' => $lead_id], '*', 'lead_journey_events');

                if ($get_lead_journey_events->num_rows() > 0) {
                    $lead_journey_events = $get_lead_journey_events->row_array();
                }

                if ($lead_customer_details['customer_account_aggregator_run_flag'] == 1) {
                    $aa_run_flag = 1;
                    if ($lead_customer_details['customer_account_aggregator_consent_verify_flag'] == 1) {
                        $account_aggregator_consent = 1;
                        if ($lead_customer_details['customer_account_aggregator_bank_statement_fetch_flag'] == 1) {
                            $bank_statement_fetched = 1;
                        }
                    } else if ($lead_customer_details['customer_account_aggregator_consent_verify_flag'] == 2) {
                        $account_aggregator_consent = 2;
                    } else if ($lead_customer_details['customer_account_aggregator_consent_verify_flag'] == 3) {
                        $account_aggregator_consent = 3;
                    }
                } else if ($lead_customer_details['customer_account_aggregator_run_flag'] == 2) {
                    $aa_run_flag = 2;
                }
            }

            $office_email = (!empty($lead_customer_details["alternate_email"]) ? $lead_customer_details["alternate_email"] : NULL);
            $office_email_verified_status = (!empty($lead_customer_details["alternate_email_verified_status"]) && $lead_customer_details["alternate_email_verified_status"] == "YES") ? 1 : 0;
            $aadhaar_last_digits = (!empty($lead_customer_details['aadhar_no']) ? $lead_customer_details['aadhar_no'] : NULL);
            $ekyc_verified_status = ((!empty($lead_customer_details['customer_digital_ekyc_flag']) && $lead_customer_details['customer_digital_ekyc_flag'] == 1) ? 1 : 0);

            if ($ekyc_verified_status == 1) {

                $search_by = 2;

                $get_digilocker_documents = $this->Tasks->getDigilockerDocuments($pancard, $search_by);

                if ($get_digilocker_documents['status'] == 1) {
                    $get_digilocker_documents = $get_digilocker_documents['get_digilocker_documents'];
                    foreach ($get_digilocker_documents as $docs) {
                        if ($docs['docs_master_id'] == 20) {
                            $aadhaar_fetched = 1;
                        } else if ($docs['docs_master_id'] == 21) {
                            $pan_fetched = 1;
                        }
                    }
                }
            }

            $get_residence_pincode_details = $this->Tasks->getCityStateByPincode($residence_pincode);
            $residence_city_name = $get_residence_pincode_details['get_city_state_details']['city_name'];
            $residence_state_name = $get_residence_pincode_details['get_city_state_details']['state_name'];

            $emp_work_mode = (!empty($get_customer_employment_details['emp_work_mode']) ? $get_customer_employment_details['emp_work_mode'] : NULL);
            $emp_company_name = (!empty($get_customer_employment_details['employer_name']) ? $get_customer_employment_details['employer_name'] : NULL);
            $emp_company_type = (!empty($get_customer_employment_details['emp_employer_type']) ? $get_customer_employment_details['emp_employer_type'] : NULL);
            $emp_designation = (!empty($get_customer_employment_details['emp_designation']) ? $get_customer_employment_details['emp_designation'] : NULL);
            $emp_pincode = (!empty($get_customer_employment_details['emp_pincode']) ? $get_customer_employment_details['emp_pincode'] : NULL);
            $emp_address_1 = (!empty($get_customer_employment_details["emp_house"]) ? $get_customer_employment_details["emp_house"] : NULL);
            $emp_address_2 = (!empty($get_customer_employment_details["emp_street"]) ? $get_customer_employment_details["emp_street"] : NULL);
            $emp_landmark = (!empty($get_customer_employment_details["emp_landmark"]) ? $get_customer_employment_details["emp_landmark"] : NULL);
            $bank_account_number = (!empty($get_customer_banking_details['account'])) ? $get_customer_banking_details['account'] : NULL;
            $cnf_bank_account_number = (!empty($get_customer_banking_details['confirm_account'])) ? $get_customer_banking_details['confirm_account'] : NULL;
            $bank_account_ifsc = (!empty($get_customer_banking_details['ifsc_code'])) ? $get_customer_banking_details['ifsc_code'] : NULL;
            $bank_account_name = (!empty($get_customer_banking_details['bank_name'])) ? $get_customer_banking_details['bank_name'] : NULL;
            $bank_branch = (!empty($get_customer_banking_details['branch'])) ? $get_customer_banking_details['branch'] : NULL;
            $bank_account_status_id = (!empty($get_customer_banking_details['account_status_id']) && $get_customer_banking_details['account_status_id'] == 1) ? 1 : 0;

            $conditions_bank_type['m_bank_type_name'] = $get_customer_banking_details['account_type'];
            $bank_account_type = $this->Tasks->selectdata($conditions_bank_type, "*", "master_bank_type");

            if ($bank_account_type->num_rows() > 0) {
                $bank_account_type = $bank_account_type->row_array();
                $bank_account_type_id = $bank_account_type['m_bank_type_id'];
                $bank_account_type_name = $bank_account_type['m_bank_type_name'];
            }

            $get_company_type_id = $this->Tasks->selectdata(['m_company_type_name' => $emp_company_type], 'm_company_type_id', 'master_company_type');

            if ($get_company_type_id->num_rows() > 0) {
                $company_type_id = $get_company_type_id->row_array();
            }

            $emp_company_type_id = !(empty($company_type_id['m_company_type_id'])) ? $company_type_id['m_company_type_id'] : NULL;

            $get_employment_pincode_details = $this->Tasks->getCityStateByPincode($emp_pincode);
            $emp_city_name = $get_employment_pincode_details['get_city_state_details']['city_name'];
            $emp_state_name = $get_employment_pincode_details['get_city_state_details']['state_name'];
            $emp_state_id = $get_employment_pincode_details['get_city_state_details']['state_id'];
            $emp_city_id = $get_employment_pincode_details['get_city_state_details']['city_id'];

            $get_salary_mode_by_id = $this->Tasks->getSalaryModeById($salary_mode_id);
            $salary_mode_name = $get_salary_mode_by_id['master_salary_mode_details']['m_salary_mode_name'];

            $get_marital_status_by_id = $this->Tasks->getMaritalStatusById($marital_status_id);
            $marital_status_name = $get_marital_status_by_id['master_marital_status_details']['m_marital_status_name'];

            $get_master_residence_type_by_id = $this->Tasks->getMasterResidence($residence_type_id);
            $residence_type_name = $get_master_residence_type_by_id['master_residence_details']['m_residence_type_name'];

            $conditions_stage['m_journey_id'] = $journey_stage_id;
            $conditions_stage['m_journey_type_id'] = 2;
            $conditions_stage['m_journey_active'] = 1;
            $conditions_stage['m_journey_deleted'] = 0;

            $screen_details['last_page'] = null;

            if ($journey_events['pje_income_details'] == 1) {
                $income_verification = 1;
            }
            if (($journey_events['pje_pancard_verification'] == 1) && ($pancard_verified_status == 1)) {
                $pan_verification = 1;
            }
            if ($journey_events['pje_personal_details'] == 1) {
                $personal_details_verification = 2;
            }
            if ($journey_events['pje_residence_details'] == 1) {
                $residence_details = 2;
            }
            if ($journey_events['pje_selfie_upload'] == 1) {
                $selfie_upload = 2;
            }
            if (($journey_events['pje_registration_successful'] == 1) && ($registration_successful == 1)) {
                $registration_successful = 1;
            }

            $check_eligibility = 2;
            $generate_loan_quote = 0;
            $loan_quote = 0;
            $employment_details_verification = 0;
            $bank_statement_upload = 0;
            $pay_slip_upload = 0;
            $residence_proof_upload = 0;
            $bank_details_verification = 0;
            $ekyc_verified = 0;
            $account_aggregator = 0;
            $account_aggregator_verify = 0;
            $lead_eligibility_flag = 0;

            if ($user_type == "REPEAT" || $user_type == "UNPAID-REPEAT") {
                $get_last_residence_proof = $this->Tasks->get_last_residence_proof($pancard);

                if ($get_last_residence_proof['status'] == 1) {
                    $get_residence_proof_count = $get_last_residence_proof['data']['residence_proof_docs_count'];
                }
            }

            if ((!empty($lead_details['lead_status_id']) && !in_array($lead_details['lead_status_id'], array(8))) || $lead_journey_events['lje_eligibility_confirmed'] == 1) {
                $lead_eligibility_flag = 1;
            }

            if ($lead_eligibility_flag == 1 && ($journey_events['pje_income_details'] == 1)) {
                $check_eligibility = 1;
                $loan_quote = 2;
                if (($mobile == "7976832734")) {
                    $ekyc_verified_status = 1;
                    $aadhaar_fetched = 1;
                    $pan_fetched = 1;
                }
                if ($ekyc_verified_status == 1) {
                    $ekyc_verified = 1;
                }
            }



            if (($lead_journey_events['lje_generate_loan_quote'] == 1) && ($lead_eligibility_flag == 1)) {
                $generate_loan_quote = 1;
            }

            if (($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1)) {
                $loan_quote = 1;
                $employment_details_verification = 2;
                // if (!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                if ($user_type == "REPEAT") {
                    $employment_details_verification = 1;
                }
                // }
            }

            if ($generate_loan_quote == 1 && $loan_quote == 1) {
                $loan_quote_verification = 1;
            }

            if (($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1)) {
                $employment_details_verification = 1;
                if ($ekyc_verified_status == 1) {
                    $ekyc_verified = 1;
                    $account_aggregator = 2;
                    if (($mobile == "7976832734")) {
                        $account_aggregator = 1;
                        $bank_statement_upload = 2;
                        $lead_journey_events['lje_account_aggregator'] = 1;
                    }
                } else if ($lead_journey_events['lje_ekyc_skipped'] == 1) {
                    $ekyc_verified = 1;
                    $ekyc_verified_status = 1;
                    $account_aggregator = 2;
                }
            }

            if (($lead_journey_events['lje_account_aggregator'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1)) {
                if ($aa_run_flag == 1) {
                    if ($account_aggregator_consent == 1) {
                        $account_aggregator = 1;
                        $account_aggregator_verify = 1;
                        if ($bank_statement_fetched == 1) {
                            $bank_statement_upload = 1;
                            $pay_slip_upload = 2;
                        } else {
                            $bank_statement_upload = 2;
                        }
                        // if(!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                        if ($user_type == "REPEAT") {
                            if ($get_residence_proof_count >= 2) {
                                $residence_proof_upload = 1;
                                $bank_details_verification = 2;
                            } else {
                                $residence_proof_upload = 2;
                            }
                        } else {
                            $residence_proof_upload = 2;
                        }
                        // }
                    } else if ($account_aggregator_consent == 2) {
                        $account_aggregator = 2;
                        $account_aggregator_verify = 2;
                    } else if ($account_aggregator_consent == 3) {
                        $account_aggregator = 2;
                        $account_aggregator_verify = 2;
                    }
                } else if ($aa_run_flag == 2) {
                    $account_aggregator = 1;
                    $bank_statement_upload = 2;
                    // if(!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                    if ($user_type == "REPEAT") {
                        if ($get_residence_proof_count >= 2) {
                            $residence_proof_upload = 1;
                            $bank_details_verification = 2;
                        } else {
                            $residence_proof_upload = 2;
                        }
                    } else {
                        $residence_proof_upload = 2;
                    }
                    // }
                }
            }

            if (($lead_journey_events['lje_bank_statement_upload'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1)) {
                $bank_statement_upload = 1;
                $pay_slip_upload = 2;
            }

            if (($lead_journey_events['lje_pay_slip_upload'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1) && ($lead_journey_events['lje_account_aggregator'] == 1)) {
                $pay_slip_upload = 1;
                if ($pan_fetched != 1) {
                    $pan_fetched = 2;
                } else if ($aadhaar_fetched != 1) {
                    $aadhaar_fetched = 2;
                } else {
                    $residence_proof_upload = 2;
                    // if( !empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                    if ($user_type == "REPEAT") {
                        if ($get_residence_proof_count >= 2) {
                            $residence_proof_upload = 1;
                            $bank_details_verification = 2;
                        } else {
                            $residence_proof_upload = 2;
                        }
                    }
                    // }
                }
            }

            if (($lead_journey_events['lje_pan_upload'] == 1) && ($lead_customer_details['pancard_ocr_verified_status'] == 1) && ($ekyc_verified_status == 1)) {
                $pan_fetched = 1;
                if ($aadhaar_fetched != 1) {
                    $aadhaar_fetched = 2;
                } else {
                    $residence_proof_upload = 2;
                    // if(!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                    if ($user_type == "REPEAT") {
                        if ($get_residence_proof_count >= 2) {
                            $residence_proof_upload = 1;
                            $bank_details_verification = 2;
                        } else {
                            $residence_proof_upload = 2;
                        }
                    }
                    // }
                }
            }

            if (($lead_journey_events['lje_aadhaar_upload'] == 1) && ($lead_customer_details['aadhaar_ocr_verified_status'] == 1) && ($ekyc_verified_status == 1)) {
                $aadhaar_fetched = 1;
                $residence_proof_upload = 2;
                // if(!empty($this->whitelisted_numbers) && in_array($mobile, $this->whitelisted_numbers)) {
                if ($user_type == "REPEAT") {
                    if ($get_residence_proof_count >= 2) {
                        $residence_proof_upload = 1;
                        $bank_details_verification = 2;
                    } else {
                        $residence_proof_upload = 2;
                    }
                }
                // }
            }

            if (($lead_journey_events['lje_residence_proof_upload'] == 1) && ($lead_journey_events['lje_pay_slip_upload'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1) && ($lead_journey_events['lje_account_aggregator'] == 1)) {
                $residence_proof_upload = 1;
                $bank_details_verification = 2;
            }

            if (($lead_journey_events['lje_banking_details'] == 1) && ($lead_journey_events['lje_residence_proof_upload'] == 1) && ($lead_journey_events['lje_pay_slip_upload'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1) && ($lead_journey_events['lje_account_aggregator'] == 1)) {
                $bank_details_verification = 1;
            }

            if (($lead_journey_events['lje_thank_you'] == 1) && ($lead_journey_events['lje_residence_proof_upload'] == 1) && ($lead_journey_events['lje_pay_slip_upload'] == 1) && ($lead_journey_events['lje_employment_details'] == 1) && ($lead_journey_events['lje_loan_quote'] == 1) && ($lead_eligibility_flag == 1) && ($ekyc_verified_status == 1) && ($lead_journey_events['lje_account_aggregator'] == 1)) {
                $application_submission = 1;
            }

            if (($lead_journey_events['lje_pay_slip_upload'] == 1) && ($lead_journey_events['lje_residence_proof_upload'] == 1) && ($aadhaar_fetched == 1) && ($pan_fetched == 1)) {
                $documents_upload = 1;
                if (($lead_journey_events['lje_bank_statement_upload'] == 1) || ($bank_statement_fetched == 1)) {
                    $documents_upload = 1;
                }
            }

            if ($lead_journey_events['lje_ekyc_initiated'] == 1 && $ekyc_verified_status != 1) {
                $ekyc_skip_button_flag = 1;
            }

            $screen_details['pancard_verification'] = $pan_verification;

            $screen_details['residence_pincode'] = 2;

            $screen_details['income_details'] = 2;

            $screen_details['personal_details'] = $personal_details_verification;

            $screen_details['promocode'] = 2;

            $screen_details['pancard_verification'] = $pan_verification;
            $screen_details['residence_details'] = $residence_details;
            $screen_details['selfie_upload'] = $selfie_upload;
            $screen_details['registration_successful'] = $registration_successful;
            $screen_details['check_eligibility'] = $check_eligibility;
            $screen_details['generate_loan_quote'] = $generate_loan_quote;
            $screen_details['loan_quote'] = $loan_quote;
            $screen_details['employment_work_mode'] = 2;
            $screen_details['employment_details'] = $employment_details_verification;

            $screen_details['banking_details'] = $bank_details_verification;

            $screen_details['aadhaar_upload'] = $aadhaar_fetched;
            $screen_details['residence_proof_upload'] = $residence_proof_upload;
            $screen_details['bank_statement_upload'] = $bank_statement_upload;
            $screen_details['pay_slip_upload'] = $pay_slip_upload;
            $screen_details['pan_upload'] = $pan_fetched;
            $screen_details['account_aggregator'] = $account_aggregator;
            $screen_details['account_aggregator_verify'] = $account_aggregator_verify;

            $screen_details['ekyc_initiated'] = $ekyc_verified;
            $screen_details['ekyc_verified'] = $ekyc_verified;

            $get_profile_filled_percent = $this->getApplicationFilledPercent($cust_profile_id);

            if (!empty($get_profile_filled_percent)) {
                $profile_filled_percent = $get_profile_filled_percent['profile_filled_percent'];
                $application_filled_percent = $get_profile_filled_percent['application_filled_percent'];
                $profile_journey_events = $get_profile_filled_percent['profile_journey_events'];
                $lead_journey_events = $get_profile_filled_percent['lead_journey_events'];
            }

            $app_filled_percent = 0;

            if ($registration_successful == 1) {
                if (!empty($lead_id)) {
                    $app_filled_percent = $application_filled_percent;
                } else {
                    $app_filled_percent = 70;
                }
            } else {
                $app_filled_percent = $profile_filled_percent;
            }

            //            if (!empty($lead_id)) {

            $residence_proof_docs_array = array(13, 33, 34, 35, 36, 100);

            $required = 2;

            $uploaded = 1;

            $bank_statement_doc = array();
            $pay_slip_doc = array();
            $aadhaar_front_doc = array();
            $aadhaar_back_doc = array();
            $pan_doc = array();
            $selfie_doc = array();
            $landline_bill_doc = array();
            $electricity_bill_doc = array();
            $water_bill_doc = array();
            $gas_bill_doc = array();
            $rent_agreement_doc = array();
            $credit_card_statement_doc = array();

            $required_docs_array = array(1, 2, 4, 6, 16, 18);

            if ($aadhaar_fetched == 1 && $pan_fetched == 1) {
                $doc_status['required'] = 2;
                $doc_status['uploaded'] = 1;
                $doc_status['file_name'] = null;
                $doc_status['file_ext_allowed'] = null;
                $aadhaar_front_doc = $doc_status;
                $aadhaar_back_doc = $doc_status;
                $pan_doc = $doc_status;
                $required_docs_array = array(6, 16, 18);
            } else if ($aadhaar_fetched == 1) {
                $doc_status['required'] = 2;
                $doc_status['uploaded'] = 1;
                $doc_status['file_name'] = null;
                $doc_status['file_ext_allowed'] = null;
                $aadhaar_front_doc = $doc_status;
                $aadhaar_back_doc = $doc_status;
                $required_docs_array = array(4, 6, 16, 18);
            } else if ($pan_fetched == 1) {
                $doc_status['required'] = 2;
                $doc_status['uploaded'] = 1;
                $doc_status['file_name'] = null;
                $doc_status['file_ext_allowed'] = null;
                $pan_doc = $doc_status;
                $required_docs_array = array(1, 2, 6, 16, 18);
            }

            foreach ($required_docs_array as $doc_id) {
                $get_uploaded_docs = $this->Tasks->get_lead_docs_by_master_doc_id($doc_id, $lead_id);

                if ($get_uploaded_docs['status'] == 1) {
                    $uploaded_docs = $get_uploaded_docs['doc_details'];
                    $required = 2;
                    $uploaded = 1;
                    $file_name = COLLEX_DOC_URL . $uploaded_docs['file_name'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                } else {
                    $required = 1;
                    $uploaded = 2;
                    $file_name = NULL;
                    $file_ext = 'pdf,jpg,png,jpeg,JPG,PNG,JPEG';
                }

                $doc_status['required'] = $required;
                $doc_status['uploaded'] = $uploaded;
                $doc_status['file_name'] = $file_name;
                $doc_status['file_ext_allowed'] = $file_ext;
                if ($doc_id == 1) {
                    $aadhaar_front_doc = $doc_status;
                } else if ($doc_id == 2) {
                    $aadhaar_back_doc = $doc_status;
                } else if ($doc_id == 4) {
                    $pan_doc = $doc_status;
                } else if ($doc_id == 6) {
                    $doc_status['file_ext_allowed'] = 'pdf';
                    $bank_statement_doc = $doc_status;
                } else if ($doc_id == 16) {
                    $pay_slip_doc = $doc_status;
                } else if ($doc_id == 18) {
                    $selfie_doc = $doc_status;
                }
            }

            foreach ($residence_proof_docs_array as $rdoc_id) {
                $get_uploaded_docs = $this->Tasks->get_lead_docs_by_master_doc_id($rdoc_id, $lead_id);
                if ($get_uploaded_docs['status'] == 1) {
                    $uploaded_docs = $get_uploaded_docs['doc_details'];
                    $required = 2;
                    $uploaded = 1;
                    $file_name = COLLEX_DOC_URL . $uploaded_docs['file_name'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                } else {
                    $required = 2;
                    $uploaded = 2;
                    $file_name = NULL;
                    $file_ext = 'pdf,jpg,png,jpeg,JPG,PNG,JPEG';
                }

                $doc_status['required'] = $required;
                $doc_status['uploaded'] = $uploaded;
                $doc_status['file_name'] = $file_name;
                $doc_status['file_ext_allowed'] = 'pdf,jpg,png,jpeg,JPG,PNG,JPEG';
                if ($rdoc_id == 13) {
                    $credit_card_statement_doc = $doc_status;
                } else if ($rdoc_id == 33) {
                    $electricity_bill_doc = $doc_status;
                } else if ($rdoc_id == 34) {
                    $landline_bill_doc = $doc_status;
                } else if ($rdoc_id == 35) {
                    $gas_bill_doc = $doc_status;
                } else if ($rdoc_id == 36) {
                    $water_bill_doc = $doc_status;
                } else if ($rdoc_id == 100) {
                    $rent_agreement_doc = $doc_status;
                }
            }
            //            }

            $check_active_loan = $this->Tasks->get_active_loan($pancard);

            if ($check_active_loan['status'] == 1) {
                $get_active_loan = $check_active_loan['get_active_loan'];
                if ($get_active_loan['active_loan_count'] > 0) {
                    $application_stage_submitted = 2;
                    $application_stage_in_review = 2;
                    $application_stage_sanctioned = 2;
                    $application_stage_eSign = 2;
                    $application_stage_disbursed = 2;
                }
            }

            $doc_details['bank_statement'] = $bank_statement_doc;
            $doc_details['pay_slip'] = $pay_slip_doc;
            $doc_details['aadhaar_front'] = $aadhaar_front_doc;
            $doc_details['aadhaar_back'] = $aadhaar_back_doc;
            $doc_details['pancard'] = $pan_doc;
            $doc_details['selfie'] = $selfie_doc;
            $doc_details['electricity_bill'] = $electricity_bill_doc;
            $doc_details['landline_bill'] = $landline_bill_doc;
            $doc_details['gas_bill'] = $gas_bill_doc;
            $doc_details['water_bill'] = $water_bill_doc;
            $doc_details['credit_card_statement'] = $credit_card_statement_doc;
            $doc_details['rent_agreement'] = $rent_agreement_doc;

            $application_status['application_submitted'] = $application_stage_submitted;
            $application_status['application_in_review'] = $application_stage_in_review;
            $application_status['sanction'] = $application_stage_sanctioned;
            $application_status['esign'] = $application_stage_eSign;
            $application_status['disbursement'] = $application_stage_disbursed;

            $loan_details['loan_amount'] = $loan_recommended;
            $loan_details['roi'] = $roi;
            $loan_details['penal_roi'] = $penal_roi;
            $loan_details['sanction_date'] = $sanction_date;
            $loan_details['repayment_amount'] = $repayment_amount;
            $loan_details['repayment_date'] = $repayment_date;
            $loan_details['tenure'] = $tenure;
            $loan_details['admin_processing_fee'] = $admin_processing_fee;

            $customer_details['existing_customer'] = ($user_type == "REPEAT") ? true : false;
            $customer_details['mobile'] = $mobile;
            $customer_details['mobile_verified'] = $mobile_verified_status;
            $customer_details['pancard'] = $pancard;
            $customer_details['pancard_verified_status'] = $pancard_verified_status;
            //            $customer_details['first_name'] = $first_name;
            //            $customer_details['middle_name'] = $middle_name;
            //            $customer_details['sur_name'] = $sur_name;
            $customer_details['full_name'] = !empty($first_name) ? $first_name : '';
            $customer_details['full_name'] .= !empty($middle_name) ? ' ' . $middle_name : '';
            $customer_details['full_name'] .= !empty($sur_name) ? ' ' . $sur_name : '';

            $customer_details['dob'] = $dob;
            $customer_details['gender'] = $gender;
            $customer_details['residence_pincode'] = $residence_pincode;
            $customer_details['residence_city_id'] = $residence_city_id;
            $customer_details['residence_state_id'] = $residence_state_id;
            $customer_details['residence_city_name'] = $residence_city_name;
            $customer_details['residence_state_name'] = $residence_state_name;
            $customer_details["residence_type_id"] = $residence_type_id;
            $customer_details["residence_type_name"] = $residence_type_name;
            $customer_details["residence_address_1"] = $residence_address_1;
            $customer_details["residence_address_2"] = $residence_address_2;
            $customer_details["residence_landmark"] = $residence_landmark;
            $customer_details["personal_email"] = $personal_email;
            $customer_details["personal_email_verified_status"] = $personal_email_verified_status;
            $customer_details['income_type_id'] = $income_type_id;
            $customer_details['monthly_income'] = $monthly_income;
            $customer_details['salary_mode_id'] = $salary_mode_id;
            $customer_details['salary_mode_name'] = $salary_mode_name;
            $customer_details['salary_date'] = !(empty($salary_date)) ? date("d-m-Y", strtotime($salary_date)) : NULL;
            $customer_details['marital_status_id'] = $marital_status_id;
            $customer_details['marital_status_name'] = $marital_status_name;
            $customer_details['spouse_name'] = $spouse_name;
            $customer_details['ekyc_verified_status'] = $ekyc_verified_status;
            $customer_details['aadhaar_fetched'] = (!empty($aadhaar_fetched) ? $aadhaar_fetched : 0);
            $customer_details['pan_fetched'] = (!empty($pan_fetched) ? $pan_fetched : 0);
            $customer_details['emp_work_mode'] = $emp_work_mode;
            $customer_details['emp_company_name'] = $emp_company_name;
            $customer_details['emp_company_type'] = $emp_company_type;
            $customer_details['emp_company_type_id'] = $emp_company_type_id;
            $customer_details['emp_designation'] = $emp_designation;
            $customer_details['emp_pincode'] = $emp_pincode;
            $customer_details['emp_city_id'] = $emp_city_id;
            $customer_details['emp_state_id'] = $emp_state_id;
            $customer_details['emp_city_name'] = $emp_city_name;
            $customer_details['emp_state_name'] = $emp_state_name;
            $customer_details["emp_address_1"] = $emp_address_1;
            $customer_details["emp_address_2"] = $emp_address_2;
            $customer_details["emp_landmark"] = $emp_landmark;
            $customer_details["office_email"] = $office_email;
            $customer_details["office_email_verified_status"] = $office_email_verified_status;
            $customer_details["lead_id"] = $this->encrypt->encode($lead_id);
            $customer_details['aadhaar_no'] = $aadhaar_last_digits;
            $customer_details['bank_account_number'] = $bank_account_number;
            $customer_details['cnf_bank_account_number'] = $cnf_bank_account_number;
            $customer_details['bank_account_ifsc'] = $bank_account_ifsc;
            $customer_details['bank_account_name'] = $bank_account_name;
            $customer_details['bank_account_branch'] = $bank_branch;
            $customer_details['bank_account_type_id'] = $bank_account_type_id;
            $customer_details['bank_account_type_name'] = $bank_account_type_name;
            $customer_details['bank_account_status'] = $bank_account_status_id;
            $customer_details['customer_type'] = $user_type;
            $customer_details['application_filled_percent'] = $app_filled_percent;
            $customer_details['ekyc_skip_button_flag'] = $ekyc_skip_button_flag;

            $response_array['customer_details'] = $customer_details;

            $response_array['screen_details'] = $screen_details;

            $response_array['doc_details'] = $doc_details;

            $response_array['application_status'] = $application_status;

            $response_array['loan_details'] = !empty($loan_details) ? $loan_details : NULL;

            $message = "Customer Details Found";

            if (in_array($lead_details['lead_status_id'], array(8, 9, 16, 40))) {
                $update_customer_profile['cp_lead_id'] = NULL;
                $update_customer_profile['cp_status_id'] = NULL;
                $update_customer_profile['cp_journey_stage'] = NULL;
                $update_customer_profile['cp_is_journey_completed'] = 0;
                $update_customer_profile['cp_is_cif_fetched'] = 0;
                $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                //                $this->Tasks->resetJourneyEvents($cust_profile_id, 1);
            }

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getCityStateByPincode_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $pincode = (!empty($post['pincode'])) ? $post['pincode'] : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($pincode)) {
                throw new ErrorException("Pincode cannot be empty!");
            }

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("pincode", "Pincode", "required|numeric", array("numeric" => "Pincode must contain numbers only"));
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $get_city_state_by_pincode = $this->Tasks->getCityStateByPincode($pincode);

            if ($get_city_state_by_pincode['status'] != 1) {
                throw new ErrorException("Pincode not found");
            }

            $response_array['city_id'] = $get_city_state_by_pincode['get_city_state_details']['city_id'];
            $response_array['city_name'] = $get_city_state_by_pincode['get_city_state_details']['city_name'];
            $response_array['state_id'] = $get_city_state_by_pincode['get_city_state_details']['state_id'];
            $response_array['state_name'] = $get_city_state_by_pincode['get_city_state_details']['state_name'];
            $response_array['pincode'] = $get_city_state_by_pincode['get_city_state_details']['pincode'];

            $apiStatusMessage = 'Pincode Details Found.';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterSalaryMode_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }



        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_salary_mode = $this->Tasks->getMasterSalaryMode();

            if ($get_master_salary_mode['status'] != 1) {
                throw new ErrorException("Salary mode not found");
            }

            $get_master_salary_mode = $get_master_salary_mode['master_salary_mode'];

            foreach ($get_master_salary_mode as $salary_mode) {
                $salary_modes['salary_mode_id'] = $salary_mode['m_salary_mode_id'];
                $salary_modes['salary_mode_name'] = $salary_mode['m_salary_mode_name'];
                $response_array[] = $salary_modes;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterMaritalStatus_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }



        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_marital_status = $this->Tasks->getMasterMaritalStatus();

            if ($get_master_marital_status['status'] != 1) {
                throw new ErrorException("Marital Status not found");
            }

            $get_master_marital_status = $get_master_marital_status['master_marital_status'];

            foreach ($get_master_marital_status as $marital_status) {
                $marital_status_type['marital_status_id'] = $marital_status['m_marital_status_id'];
                $marital_status_type['marital_status_name'] = $marital_status['m_marital_status_name'];
                $response_array[] = $marital_status_type;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterBankType_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }



        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_bank_type = $this->Tasks->getMasterBankType();

            if ($get_master_bank_type['status'] != 1) {
                throw new ErrorException("Bank Type not found");
            }

            $get_master_bank_type = $get_master_bank_type['master_bank_type'];

            foreach ($get_master_bank_type as $bank_type) {
                $master_bank_type['bank_type_id'] = $bank_type['m_bank_type_id'];
                $master_bank_type['bank_type_name'] = $bank_type['m_bank_type_name'];
                $response_array[] = $master_bank_type;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterLoanPurpose_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }



        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_loan_purpose = $this->Tasks->getMasterLoanPurpose();

            if ($get_master_loan_purpose['status'] != 1) {
                throw new ErrorException("Loan Purpose not found");
            }

            $get_master_loan_purpose = $get_master_loan_purpose['master_loan_purpose'];

            foreach ($get_master_loan_purpose as $loan_purpose) {
                $master_loan_purpose['loan_purpose_id'] = $loan_purpose['enduse_id'];
                $master_loan_purpose['loan_purpose_name'] = $loan_purpose['enduse_name'];
                $response_array[] = $master_loan_purpose;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterResidenceType_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_residence_type = $this->Tasks->getMasterResidenceType();

            if ($get_master_residence_type['status'] != 1) {
                throw new ErrorException("Loan Purpose not found");
            }

            $get_master_residence_type = $get_master_residence_type['master_residence_type'];

            foreach ($get_master_residence_type as $residence_type) {
                $master_residence_type['residence_type_id'] = $residence_type['m_residence_type_id'];
                $master_residence_type['residence_type_name'] = $residence_type['m_residence_type_name'];
                $response_array[] = $master_residence_type;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getMasterCompanyType_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $get_master_company_type = $this->Tasks->getMasterCompanyType();

            if ($get_master_company_type['status'] != 1) {
                throw new ErrorException("Company Type not found");
            }

            $get_master_company_type = $get_master_company_type['master_company_type'];

            foreach ($get_master_company_type as $company_type) {
                $master_company_type['company_type_id'] = $company_type['m_company_type_id'];
                $master_company_type['company_type_name'] = $company_type['m_company_type_name'];
                $response_array[] = $master_company_type;
            }

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getBankDetailsByIfsc_post() {
        $response_array = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $bank_account_ifsc = (!empty($post['bank_account_ifsc'])) ? $post['bank_account_ifsc'] : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($bank_account_ifsc)) {
                throw new Exception("IFSC Code cannot be empty!");
            }

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("bank_account_ifsc", "bank_account_ifsc", "required|alpha_numeric", array("alpha_numeric" => "IFSC Code must contain letters and numbers only"));
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $bank_account_details = $this->Tasks->get_bank_details($bank_account_ifsc);

            if ($bank_account_details['status'] != 1) {
                throw new ErrorException("Bank Details not found");
            }

            $bank_account_details = $bank_account_details['bank_account_details'];

            $response_array['bank_account_name'] = $bank_account_details['bank_name'];
            $response_array['bank_account_branch'] = $bank_account_details['bank_branch'];
            $response_array['bank_account_ifsc'] = $bank_account_details['bank_ifsc'];

            $apiStatusMessage = 'Bank Details Found.';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getBankIfscList_post() {
        $response_array = array();

        $bank_ifsc_list = array();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $bank_account_ifsc = (!empty($post['bank_account_ifsc'])) ? $post['bank_account_ifsc'] : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($bank_account_ifsc)) {
                throw new Exception("IFSC Code cannot be empty!");
            }

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("bank_account_ifsc", "bank_account_ifsc", "required|alpha_numeric", array("alpha_numeric" => "IFSC Code must contain letters and numbers only"));
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $bank_account_details = $this->Tasks->get_bank_details($bank_account_ifsc, 1);

            if ($bank_account_details['status'] != 1) {
                throw new ErrorException("Bank Details not found");
            }

            $bank_account_details = $bank_account_details['bank_account_details'];

            foreach ($bank_account_details as $bank_ifsc) {
                $bank_ifsc_list[]['bank_account_ifsc'] = $bank_ifsc['bank_ifsc'];
            }

            $response_array['bank_ifsc_list'] = $bank_ifsc_list;

            $apiStatusMessage = '';
            $apiStatusId = 1;
            $apiStatusData = $response_array;
        } catch (ErrorException $er) {
            $apiStatusId = 2;
            $apiStatusMessage = $er->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function checkAuthorization($headers, $page_name = null) {

        $return_array = array('status' => 1, 'message' => '');

        $whitelisted_pages = array('login', 'otp_verify', 'resend_otp');

        $token_auth = $this->_token();

        $token = $token_auth['token_android'];

        if (!empty($headers['Auth']) && ($token != base64_decode($headers['Auth']))) {
            $return_array['status'] = 2;
            $return_array['message'] = "Unauthorized Request!";
        } else if (!in_array($page_name, $whitelisted_pages)) {
            $token_verification = $this->Tasks->check_validationToken($headers['Authtoken']);
            if (empty($token_verification['status'])) {
                $return_array['status'] = 4;
                $return_array['message'] = "Session Expired";
            }
        }
        return $return_array;
    }

    public function applicationStageVerification($cust_profile_id, $page_name, $type = 1) {
        $return_array = array('status' => 1, 'message' => '');
        $otp_verify_flag = 0;
        $pan_verify_flag = 0;
        $registration_flag = 0;
        $status = 1;
        $message = '';

        $page_list = array(
            'residence_pincode',
            'income_details',
            'pancard_verification',
            'personal_details',
            'residence_details',
            'promocode',
            'selfie_upload',
            'registration_successful',
            'generate_loan_quote',
            'loan_quotation_decision',
            'employment_work_mode',
            'employment_details',
            'ekyc_initiated',
            'ekyc_verified',
            'bank_statement_upload',
            'pay_slip_upload',
            'pan_upload',
            'aadhaar_upload',
            'residence_proof_upload',
            'banking_details',
            'check_eligibility',
            'account_aggregator',
            'account_aggregator_verify'
        );

        if (!in_array($page_name, $page_list)) {
            $status = 0;
        }

        if ($status == 1) {

            if (!empty($cust_profile_id)) {
                $get_customer_details = $this->Tasks->get_customer_profile_details($cust_profile_id);
                if ($get_customer_details['status'] == 1) {
                    $customer_details = $get_customer_details['customer_profile_details'];

                    if ($type == 1) {
                        $otp_verify_flag = 1;
                    }

                    if ($type == 2) {
                        $otp_verify_flag = 1;
                        $pan_verify_flag = 1;
                    }

                    if ($type == 3) {
                        $otp_verify_flag = 1;
                        $pan_verify_flag = 1;
                        $registration_flag = 1;
                    }

                    if ($otp_verify_flag == 1 && $status == 1) {
                        $get_otp_verify_status = $this->Tasks->select_data_by_filter(['lot_profile_id' => $cust_profile_id], 'lot_otp_verify_flag', 'leads_otp_trans', 'lot_id DESC', 1);
                        if ($get_otp_verify_status->num_rows() > 0) {
                            $otp_verify_status = $get_otp_verify_status->row_array();
                            if ($otp_verify_status['lot_otp_verify_flag'] != 1) {
                                $status = 0;
                                $message = 'Please verify the OTP to proceed further.';
                            }
                        }
                    }

                    if ($pan_verify_flag == 1 && $status == 1) {
                        if ($customer_details['cp_pancard_verified_status'] != 1) {
                            $status = 0;
                            $message = 'Please verify the PAN card.';
                        }
                    }

                    if ($registration_flag == 1 && $status == 1) {
                        if ($customer_details['cp_registration_successful'] != 1) {
                            $status = 0;
                            $message = 'You registration is not completed. Please complete the registeration to get instant personal loan.';
                        }
                    }

                    $lead_id = !empty($customer_details['cp_lead_id']) ? $customer_details['cp_lead_id'] : 0;

                    if (!empty($lead_id)) {
                        $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

                        if ($get_lead_details['status'] == 1) {
                            $lead_details = $get_lead_details['lead_details'];

                            if (!in_array($lead_details['lead_status_id'], [1, 4, 41, 42])) {
                                $status = 0;
                                $message = 'Your application has been move to next step. Please check the application status.';
                                if (in_array($lead_details['lead_status_id'], array(14, 17, 18, 19))) {
                                    $message = 'Please close your current loan to get the new loan.';
                                }
                            }
                        }
                    }
                } else {
                    $status = 0;
                    $message = 'You detail does not found. Please login again.';
                }
            } else {
                $status = 0;
                $message = 'Invalid access of profile. Please login again.';
            }
        }

        $return_array['status'] = $status;
        $return_array['message'] = $message;

        return $return_array;
    }

    public function getProfileDetails_post() {

        $response_array = array();

        $apiStatusData = array();

        $screen_details = array();

        $customer_details = array();

        $pan_verification = 0;

        $personal_details = 0;

        $residence_details = 0;

        $selfie_upload = 0;

        $income_details = 0;

        $profile_filled_percent = 0;

        $profile_edit_flag = 1;

        $active_loan_flag = 0;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        $cust_profile_id = !empty($post['cust_profile_id']) ? $this->encrypt->decode($post['cust_profile_id']) : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($cust_profile_id)) {
                throw new ErrorException("Lead ID or Customer Profile ID cannot be empty");
            }

            $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];

            $browser = $this->agent->browser();

            $insert_log_array = array();
            $insert_log_array['mapp_ip_address'] = $ip_address;
            $insert_log_array['mapp_browser_info'] = $browser;
            $insert_log_array['mapp_action_name'] = "getProfileDetails";
            $insert_log_array['mapp_request'] = json_encode($post);
            $insert_log_array['mapp_profile_id'] = $cust_profile_id;
            $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

            $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_customer_profile_details['status'] != 1) {
                throw new ErrorException("Customer Details Not Found");
            }

            $get_customer_profile_details = $get_customer_profile_details['customer_profile_details'];

            $mobile = (!empty($get_customer_profile_details['cp_mobile']) ? $get_customer_profile_details['cp_mobile'] : NULL);
            $mobile_verified_status = (!empty($get_customer_profile_details['cp_is_mobile_verified']) ? $get_customer_profile_details['cp_is_mobile_verified'] : NULL);
            $pancard = (!empty($get_customer_profile_details['cp_pancard']) ? $get_customer_profile_details['cp_pancard'] : NULL);
            $pancard_verified_status = (!empty($get_customer_profile_details['cp_pancard_verified_status']) ? $get_customer_profile_details['cp_pancard_verified_status'] : NULL);
            $first_name = (!empty($get_customer_profile_details['cp_first_name']) ? $get_customer_profile_details['cp_first_name'] : NULL);
            $middle_name = (!empty($get_customer_profile_details['cp_middle_name']) ? $get_customer_profile_details['cp_middle_name'] : NULL);
            $sur_name = (!empty($get_customer_profile_details['cp_sur_name']) ? $get_customer_profile_details['cp_sur_name'] : NULL);
            $dob = ((!empty($get_customer_profile_details['cp_dob']) && $get_customer_profile_details['cp_dob'] != '0000-00-00') ? date("d-m-Y", strtotime($get_customer_profile_details['cp_dob'])) : NULL);
            $gender = (!empty($get_customer_profile_details['cp_gender']) ? $get_customer_profile_details['cp_gender'] : NULL);
            $residence_pincode = (!empty($get_customer_profile_details['cp_residence_pincode']) ? $get_customer_profile_details['cp_residence_pincode'] : NULL);
            $residence_city_id = (!empty($get_customer_profile_details['cp_residence_city_id']) ? $get_customer_profile_details['cp_residence_city_id'] : NULL);
            $residence_state_id = (!empty($get_customer_profile_details['cp_residence_state_id']) ? $get_customer_profile_details['cp_residence_state_id'] : NULL);
            $residence_type_id = (!empty($get_customer_profile_details["cp_residence_type_id"]) ? $get_customer_profile_details["cp_residence_type_id"] : NULL);
            $residence_address_1 = (!empty($get_customer_profile_details["cp_residence_address_1"]) ? $get_customer_profile_details["cp_residence_address_1"] : NULL);
            $residence_address_2 = (!empty($get_customer_profile_details["cp_residence_address_2"]) ? $get_customer_profile_details["cp_residence_address_2"] : NULL);
            $residence_landmark = (!empty($get_customer_profile_details["cp_residence_landmark"]) ? $get_customer_profile_details["cp_residence_landmark"] : NULL);
            $personal_email = (!empty($get_customer_profile_details["cp_personal_email"]) ? $get_customer_profile_details["cp_personal_email"] : NULL);
            $personal_email_verified_status = (!empty($get_customer_profile_details["cp_personal_email_verified_status"]) ? $get_customer_profile_details["cp_personal_email_verified_status"] : NULL);
            $marital_status_id = !empty($get_customer_profile_details['cp_marital_status_id']) ? $get_customer_profile_details['cp_marital_status_id'] : NULL;
            $spouse_name = !empty($get_customer_profile_details['cp_spouse_name']) ? $get_customer_profile_details['cp_spouse_name'] : NULL;
            $profile_pic = (!empty($get_customer_profile_details['cp_profile_pic']) ? COLLEX_DOC_URL . $get_customer_profile_details['cp_profile_pic'] : NULL);
            $user_type = (!empty($get_customer_profile_details['cp_user_type']) && $get_customer_profile_details['cp_user_type'] == "REPEAT") ? "REPEAT" : "NEW";
            $registration_successful = (!empty($get_customer_profile_details['cp_registration_successful']) && $get_customer_profile_details['cp_registration_successful'] == 1) ? 1 : 0;
            $lead_id = (!empty($get_customer_profile_details['cp_lead_id'])) ? $get_customer_profile_details['cp_lead_id'] : NULL;
            $income_type_id = !empty($get_customer_profile_details['cp_income_type_id']) ? $get_customer_profile_details['cp_income_type_id'] : NULL;
            $monthly_income = !empty($get_customer_profile_details['cp_monthly_income']) ? $get_customer_profile_details['cp_monthly_income'] : NULL;
            $salary_mode_id = !empty($get_customer_profile_details['cp_salary_mode']) ? $get_customer_profile_details['cp_salary_mode'] : NULL;
            $salary_date = !empty($get_customer_profile_details['cp_salary_date']) ? $get_customer_profile_details['cp_salary_date'] : NULL;
            $obligations = !empty($get_customer_profile_details['cp_obligations']) ? $get_customer_profile_details['cp_obligations'] : 0;

            $get_residence_pincode_details = $this->Tasks->getCityStateByPincode($residence_pincode);
            $residence_city_name = $get_residence_pincode_details['get_city_state_details']['city_name'];
            $residence_state_name = $get_residence_pincode_details['get_city_state_details']['state_name'];

            $get_salary_mode_by_id = $this->Tasks->getSalaryModeById($salary_mode_id);
            $salary_mode_name = $get_salary_mode_by_id['master_salary_mode_details']['m_salary_mode_name'];

            $get_marital_status_by_id = $this->Tasks->getMaritalStatusById($marital_status_id);
            $marital_status_name = $get_marital_status_by_id['master_marital_status_details']['m_marital_status_name'];

            $get_master_residence_type_by_id = $this->Tasks->getMasterResidence($residence_type_id);
            $residence_type_name = $get_master_residence_type_by_id['master_residence_details']['m_residence_type_name'];

            if ($pancard_verified_status == 1) {
                $pan_verification = 1;
            }

            $get_profile_filled_percent = $this->getApplicationFilledPercent($cust_profile_id);

            if (!empty($get_profile_filled_percent)) {
                $profile_filled_percent = $get_profile_filled_percent['profile_filled_percent'];
                $profile_journey_events = $get_profile_filled_percent['profile_journey_events'];
            }

            if ($profile_journey_events['pje_personal_details'] == 1) {
                $personal_details = 2;
            }

            if ($profile_journey_events['pje_residence_details'] == 1) {
                $residence_details = 2;
            }

            if ($profile_journey_events['pje_income_details'] == 1) {
                $income_details = 2;
            }

            if ($profile_journey_events['pje_selfie_upload'] == 1) {
                $selfie_upload = 2;
            }

            $check_active_loan = $this->Tasks->get_active_loan($pancard);

            if ($check_active_loan['status'] == 1) {
                $get_active_loan = $check_active_loan['get_active_loan'];
                if ($get_active_loan['active_loan_count'] > 0) {
                    $profile_edit_flag = 0;
                }
            }

            $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

            if ($get_lead_details['status'] == 1) {
                $lead_details = $get_lead_details['lead_details'];
                if (!in_array($lead_details['lead_status_id'], array(16, 17, 18, 8, 9))) {
                    $profile_edit_flag = 0;
                }
            }

            $screen_details['pancard_verification'] = $pan_verification;

            $screen_details['personal_details'] = $personal_details;

            $screen_details['residence_details'] = $residence_details;

            $screen_details['income_details'] = $income_details;

            $screen_details['selfie_upload'] = $selfie_upload;

            $screen_details['registration_successful'] = $registration_successful;

            $customer_details['existing_customer'] = ($user_type == "REPEAT") ? true : false;
            $customer_details['mobile'] = $mobile;
            $customer_details['mobile_verified'] = $mobile_verified_status;
            $customer_details['pancard'] = $pancard;
            $customer_details['pancard_verified_status'] = $pancard_verified_status;
            $customer_details['full_name'] = !empty($first_name) ? $first_name : '';
            $customer_details['full_name'] .= !empty($middle_name) ? ' ' . $middle_name : '';
            $customer_details['full_name'] .= !empty($sur_name) ? ' ' . $sur_name : '';

            $customer_details['dob'] = $dob;
            $customer_details['gender'] = $gender;
            $customer_details['residence_pincode'] = $residence_pincode;
            $customer_details['residence_city_id'] = $residence_city_id;
            $customer_details['residence_state_id'] = $residence_state_id;
            $customer_details['residence_city_name'] = $residence_city_name;
            $customer_details['residence_state_name'] = $residence_state_name;
            $customer_details["residence_type_id"] = $residence_type_id;
            $customer_details["residence_type_name"] = $residence_type_name;
            $customer_details["residence_address_1"] = $residence_address_1;
            $customer_details["residence_address_2"] = $residence_address_2;
            $customer_details["residence_landmark"] = $residence_landmark;
            $customer_details["personal_email"] = $personal_email;
            $customer_details["personal_email_verified_status"] = $personal_email_verified_status;
            $customer_details['marital_status_id'] = $marital_status_id;
            $customer_details['marital_status_name'] = $marital_status_name;
            $customer_details['spouse_name'] = $spouse_name;
            $customer_details['profile_pic'] = $profile_pic;
            $customer_details['profile_percent'] = $profile_filled_percent;
            $customer_details['profile_edit_flag'] = $profile_edit_flag;
            //$customer_details['registration_successful'] = $registration_successful;
            $customer_details['income_type_id'] = $income_type_id;
            $customer_details['monthly_income'] = $monthly_income;
            $customer_details['salary_mode_id'] = $salary_mode_id;
            $customer_details['salary_mode_name'] = $salary_mode_name;
            $customer_details['salary_date'] = !(empty($salary_date)) ? date("d-m-Y", strtotime($salary_date)) : NULL;
            $customer_details['eligibility_btn_flag'] = 1;
            $customer_details['eligibility_btn_name'] = "Check Eligibility";
            $customer_details['preview_btn_name'] = ($registration_successful == 1) ? "View Profile" : "Preview Details";
            $customer_details['profile_top_content'] = "Discover exclusive opportunities tailored just for you. Complete your details to check eligibility and enjoy personalized benefits.";
            $customer_details['profile_bottom_content'] = "Review details before submitting. Ensure accuracy for a seamless experience.";
            $customer_details['obligations'] = (float) $obligations;

            if ($registration_successful != 1) {
                $preview_bottom_text = "Don't let uncertainty hold you back. It's time to explore the possibilities. Click below to check your eligibility today!";
            } else {
                $preview_bottom_text = "Check whether you are eligible for faster loan approval or not.";
            }

            if ($profile_edit_flag == 0) {
                $preview_bottom_text = "If you've applied for a loan or currently enjoy the benefits of an active loan with us, rest assured – we've got you covered!";
            }

            $customer_details['preview_bottom_text'] = $preview_bottom_text;

            $response_array['customer_details'] = $customer_details;

            $response_array['screen_details'] = $screen_details;

            $message = "Customer Details Found";

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getDashboardData_post() {

        $response_array = array();

        $apiStatusData = array();

        $app_banners = array();

        $profile_filled_percent = 0;

        $application_filled_percent = 0;

        $left_side_panel = NULL;

        $application_submitted = 0;

        $profile_registration_text = NULL;

        $loan_application_text = NULL;

        $app_banner_title = NULL;

        $app_banner_progress_percent = 0;

        $app_banner_text = NULL;

        $app_banner_btn_text = NULL;

        $app_banner_btn_active_flag = 0;

        $app_banner_btn_goto_flag = 0;

        $profile_edit_flag = 1;

        $active_loan_flag = 0;

        $journey_whitelisted_flag = 0;

        $show_loan_history_btn_flag = 0;
        $next_event_name = "pancard_verification";

        $loan_recommended = null;
        $roi = 0;
        $penal_roi = 0;
        $sanction_date = null;
        $repayment_amount = 0;
        $repayment_date = null;
        $tenure = 0;
        $admin_processing_fee = 0;
        $remaining_days = 0;
        $total_due = 0;
        $loan_no = NULL;
        $active_loan_details = array();
        $collection_history = array();

        $input_data = file_get_contents("php://input");

        $full_name = NULL;

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $cust_profile_id = !empty($post['profile_id']) ? $this->encrypt->decode($post['profile_id']) : NULL;

        $headers = $this->input->request_headers();

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($cust_profile_id)) {
                throw new ErrorException("Customer Profile ID cannot be empty");
            }

            $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];

            $browser = $this->agent->browser();

            $insert_log_array = array();
            $insert_log_array['mapp_ip_address'] = $ip_address;
            $insert_log_array['mapp_browser_info'] = $browser;
            $insert_log_array['mapp_action_name'] = "getDashboardDetails";
            $insert_log_array['mapp_request'] = json_encode($post);
            $insert_log_array['mapp_profile_id'] = $cust_profile_id;
            $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

            $get_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_profile_details['status'] != 1) {
                throw new ErrorException("Customer Details Not Found");
            }

            $get_customer_profile_details = $get_profile_details['customer_profile_details'];

            $profile_data = array();
            foreach ($get_customer_profile_details as $key => $value) {
                if (!empty($key)) {
                    $keyChange = str_replace("cp_", "", $key);
                    $profile_data[$keyChange] = $value;
                    unset($profile_data[$key]);
                }
            }
            $response_array['profile_details'] = $profile_data;
            $response_array['profile_details']['first_name'] = $profile_data['first_name'] . ' ' . $profile_data['middle_name'] . ' ' . $profile_data['sur_name'];

            $residence_pincode = (!empty($get_customer_profile_details['cp_residence_pincode']) ? $get_customer_profile_details['cp_residence_pincode'] : NULL);
            $getCityStateByPincode = $this->Tasks->getCityStateByPincode($residence_pincode);
            if ($getCityStateByPincode['status'] == 1) {
                $response_array['profile_details']['residence_city'] = $getCityStateByPincode['get_city_state_details']['city_name'];
                $response_array['profile_details']['residence_state'] = $getCityStateByPincode['get_city_state_details']['state_name'];
            }



            $mobile = (!empty($get_customer_profile_details['cp_mobile']) ? $get_customer_profile_details['cp_mobile'] : NULL);
            $pancard = (!empty($get_customer_profile_details['cp_pancard']) ? $get_customer_profile_details['cp_pancard'] : NULL);
            $first_name = (!empty($get_customer_profile_details['cp_first_name']) ? $get_customer_profile_details['cp_first_name'] : NULL);
            $middle_name = (!empty($get_customer_profile_details['cp_middle_name']) ? $get_customer_profile_details['cp_middle_name'] : NULL);
            $sur_name = (!empty($get_customer_profile_details['cp_sur_name']) ? $get_customer_profile_details['cp_sur_name'] : NULL);
            $dob = (!empty($get_customer_profile_details['cp_dob']) ? $get_customer_profile_details['cp_dob'] : NULL);
            $profile_pic = (!empty($get_customer_profile_details['cp_profile_pic']) ? COLLEX_DOC_URL . $get_customer_profile_details['cp_profile_pic'] : NULL);
            $registration_successful = (!empty($get_customer_profile_details['cp_registration_successful']) && $get_customer_profile_details['cp_registration_successful'] == 1) ? 1 : 0;
            $lead_id = !empty($get_customer_profile_details['cp_lead_id']) ? $get_customer_profile_details['cp_lead_id'] : NULL;

            $full_name = !empty($first_name) ? $first_name : '';
            $full_name .= !empty($middle_name) ? ' ' . $middle_name : '';
            $full_name .= !empty($sur_name) ? ' ' . $sur_name : '';

            if ($registration_successful == 1) {
                $next_event_name = "register_now";
            }


            $get_profile_event = $this->Tasks->getProfileEvents($cust_profile_id);
            if ($get_profile_event['status'] == 1) {
                $get_profile_event = $get_profile_event['event_data'];
            }

            $next_event_name = "pancard_verification";
            if ($get_profile_event['pje_pancard_verification'] == 1) {
                $next_event_name = "personal_details";
                if ($get_profile_event['pje_personal_details'] == 1) {
                    $next_event_name = "residence_details";
                    if ($get_profile_event['pje_residence_details'] == 1) {
                        $next_event_name = "income_details";
                        if ($get_profile_event['pje_income_details'] == 1) {
                            $next_event_name = "selfie_upload";
                            if ($get_profile_event['pje_selfie_upload']) {
                                $next_event_name = "register_now";
                            }
                        }
                    }
                }
            }

            if (!empty($lead_id)) {

                $get_lead_details = $this->Tasks->getLeadDetails($lead_id);

                if ($get_lead_details['status'] == 1) {
                    $lead_details = $get_lead_details['lead_details'];
                }

                if ($get_lead_details['user_type'] == 'REPEAT') {
                    $journey_whitelisted_flag = 1;
                }

                if (in_array($lead_details['lead_status_id'], array(8, 9))) {
                    $get_journey_stage = $this->Tasks->getMasterJourneyStage("reject", $this->journey_type_id);

                    if ($get_journey_stage['status'] == 1) {
                        $get_journey_stage = $get_journey_stage['master_journey_stage'];
                        $journey_id = $get_journey_stage['m_journey_id'];
                    }


                    $update_profile_data['cp_status_id'] = 9;
                    $update_profile_data['cp_lead_id'] = NULL;

                    $update_profile_data['cp_journey_stage'] = NULL;
                    $update_profile_data['cp_is_journey_completed'] = NULL;
                    $update_profile_data['cp_updated_at'] = date("Y-m-d H:i:s");
                    $this->Tasks->updateCustomerProfile($cust_profile_id, $update_profile_data);

                    // $update_pje_journey['pje_reject'] = 1;
                    // $this->Tasks->resetJourneyEvents($cust_profile_id, 1, $update_pje_journey);

                    $update_lje_journey['lje_reject'] = 1;
                    $this->Tasks->updateJourneyEvents($lead_id, $journey_id, 2, $this->journey_type_id, $update_lje_journey);
                }



                $get_journey_event = $this->Tasks->getJourneyEvents($lead_id);
                if ($get_journey_event['status'] == 1) {
                    $get_journey_event = $get_journey_event['event_data'];
                }

                $is_registration_successful = $get_journey_event['lje_registration_successful'];
                $next_event_name = "register_now";
                if ($is_registration_successful == 1) {
                    $next_event_name = "generate_loan_quote";
                    if ($get_journey_event['lje_generate_loan_quote'] == 1) {
                        $next_event_name = "loan_quotation_decision";
                        if ($get_journey_event['lje_loan_quotation_decision'] == 1) {
                            $next_event_name = "employment_details";
                            if ($get_journey_event['lje_employment_details'] == 1) {
                                $next_event_name = "bank_statement_upload";
                                if ($get_journey_event['lje_bank_statement_upload']) {
                                    $next_event_name = "banking_details";
                                    if ($get_journey_event['lje_banking_details'] == 1) {
                                        $next_event_name = "completed";
                                    }
                                }
                            }
                        }
                    }
                }
                $repeat_loan_details = $this->Tasks->get_repeat_customer_details($lead_id);

                if (!empty($repeat_loan_details['data'])) {
                    $response_array = array_merge($response_array, $repeat_loan_details['data']);
                }
            }

            $check_active_loan = $this->Tasks->get_active_loan($get_customer_profile_details['cp_pancard']);

            if ($check_active_loan['status'] == 1) {
                $get_active_loan = $check_active_loan['get_active_loan'];
                if ($get_active_loan['active_loan_count'] > 0) {
                    $active_loan_flag = 1;
                }
            }

            if ($active_loan_flag == 1) {
                $active_data = $this->db->query("SELECT lead_id, lead_status_id FROM leads WHERE pancard='" . $get_customer_profile_details['cp_pancard'] . "' AND lead_status_id IN(14, 19) ORDER BY lead_id DESC LIMIT 1")->row();
                $active_lead_id = $active_data->lead_id;

                $get_active_lead_details = $this->Tasks->getLeadDetails($active_lead_id);

                if ($get_active_lead_details['status'] == 1) {
                    $active_lead_details = $get_active_lead_details['lead_details'];
                }

                if (in_array($active_lead_details['lead_status_id'], array(14, 19))) {
                    $get_cam_details = $this->Tasks->get_cam_details($active_lead_id);
                    if ($get_cam_details['status'] == 1) {
                        $cam_details = $get_cam_details['data']['cam_details'];
                        $loan_recommended = $cam_details['loan_recommended'];
                        $roi = $cam_details['roi'];
                        $penal_roi = $cam_details['panel_roi'];
                        $repayment_amount = $cam_details['repayment_amount'];
                        $status_name = $cam_details['status_name'];
                        $repayment_date = date("d-m-Y", strtotime($cam_details['repayment_date']));
                        $disbursal_date = date("d-m-Y", strtotime($cam_details['disbursal_date']));
                        $sanction_date = date("d-m-Y", strtotime($cam_details['cam_sanction_letter_esgin_on']));
                        $tenure = $cam_details['tenure'];
                        $admin_processing_fee = $cam_details['admin_fee'];
                    }

                    $get_loan_details = $this->Tasks->selectdata(['lead_id' => $active_lead_id], '*', 'loan');
                    if ($get_loan_details->num_rows() > 0) {
                        $loan_details = $get_loan_details->row_array();
                        $loan_no = $loan_details['loan_no'];
                    }

                    $date_diff = strtotime($cam_details['repayment_date']) - strtotime(date("Y-m-d"));

                    $remaining_days = round($date_diff / (60 * 60 * 24));

                    $total_due = $loan_details['loan_total_payable_amount'] - $loan_details['loan_total_received_amount'];

                    $active_loan_details['lead_id'] = $active_lead_id;
                    $active_loan_details['loan_no'] = $loan_no;
                    $active_loan_details['loan_recommended'] = (int) $loan_recommended;
                    $active_loan_details['repayment_amount'] = (int) $repayment_amount;
                    $active_loan_details['repayment_date'] = $repayment_date;
                    $active_loan_details['disbursal_date'] = $disbursal_date;
                    $active_loan_details['remaining_days'] = (int) $remaining_days;
                    $active_loan_details['tenure'] = (int) $tenure;
                    $active_loan_details['roi'] = $roi;
                    $active_loan_details['total_due'] = (int) $total_due;
                    $active_loan_details['status_name'] = $status_name;
                    $active_loan_details['total_received_amount'] = (int) $loan_details['total_received_amount'];
                    // $active_loan_details = NULL;
                }


                $get_collection_history = $this->Tasks->getCollectionHistory($active_lead_id);
                if ($get_collection_history['status'] == 1) {
                    $collection_history = $get_collection_history['collection_history'];
                }

                if (strtotime($cam_details['repayment_date']) < strtotime(date("Y-m-d"))) {
                    $next_event_name = "completed";
                }

                $application_submitted = 1;
                $application_filled_percent = 100;
                $journey_whitelisted_flag = 1;
            }

            // $getLoanHistory = $this->Tasks->get_loan_history($get_customer_profile_details['cp_pancard']);

            // if ($getLoanHistory['status'] == 1) {
            //     $getProductTypeOfCustomer = $this->Tasks->selectdata(['cif_pancard' => $get_customer_profile_details['cp_pancard']], 'cif_product_id', 'cif_customer');
            //     if ($getProductTypeOfCustomer->num_rows() > 0) {
            //         $getProductTypeOfCustomer = $getProductTypeOfCustomer->row_array();
            //         $show_loan_history_btn_flag = (int) $getProductTypeOfCustomer['cif_product_id'];
            //     }
            // }

            $get_profile_filled_percent = $this->getApplicationFilledPercent($cust_profile_id);

            if (!empty($get_profile_filled_percent)) {
                $profile_filled_percent = $get_profile_filled_percent['profile_filled_percent'];
                $application_filled_percent = $get_profile_filled_percent['application_filled_percent'];
                $profile_journey_events = $get_profile_filled_percent['profile_journey_events'];
                $lead_journey_events = $get_profile_filled_percent['lead_journey_events'];
            }

            if (!empty($profile_journey_events)) {
                if ($profile_journey_events['pje_pancard_verification'] == 1) {
                    $pancard_verification = 1;
                }

                if ($profile_journey_events['pje_personal_details'] == 1) {
                    $personal_details = 1;
                }

                if ($profile_journey_events['pje_residence_details'] == 1) {
                    $residence_details = 1;
                }

                if ($profile_journey_events['pje_income_details'] == 1) {
                    $income_details = 1;
                }

                if ($profile_journey_events['pje_selfie_upload'] == 1) {
                    $selfie_upload = 1;
                }
            }

            $banner = array();

            $banner_count = 1;

            $banners = array(
                "https://paisaonsalary.com/static/media/banner_1.eb27168ad28641522f20.jpg" => NULL,
                "https://paisaonsalary.com/static/media/banner_2.fe0329d0745ef4408199.jpg" => NULL,
                "https://paisaonsalary.com/static/media/banner_3.7d5cb8133b10bd3ed8d6.jpg" => NULL
            );

            foreach ($banners as $imgUrl => $redirectUrl) {
                $banner['id'] = $banner_count;
                $banner['imgUrl'] = $imgUrl;
                $banner['redirectUrl'] = $redirectUrl;
                $app_banners[] = $banner;
                $banner_count++;
            }

            if ($active_loan_flag != 1) {
                if ($registration_successful == 1 && $pancard_verification == 1 && $personal_details == 1 && $residence_details == 1 && $income_details == 1 && $selfie_upload == 1) {
                    if (!empty($get_customer_profile_details['cp_lead_id'])) {
                        if ($lead_details['application_status'] > 0) {
                            $app_banner_progress_percent = $application_filled_percent;
                        } else if (in_array($lead_details['lead_status_id'], array(2, 3)) && in_array($lead_details['lead_journey_type_id'], array(4, 5)) && !empty($lead_details['lead_screener_assign_user_id'])) {
                            $app_banner_progress_percent = $application_filled_percent;
                        } else {
                            $app_banner_progress_percent = $application_filled_percent;
                        }
                    } else {
                        $app_banner_progress_percent = $application_filled_percent;
                        $app_banner_progress_percent = 70;
                    }
                } else {
                    $app_banner_progress_percent = $profile_filled_percent;
                }
            } else {
                $app_banner_progress_percent = 100;
                $profile_edit_flag = 0;
                if ($check_active_loan['get_active_loan']["product_id"] == 2) {
                    $app_banner_btn_active_flag = 1;
                    $app_banner_btn_goto_flag = 6;
                }
            }

            // if (!empty($this->whitelisted_numbers) && in_array($mobile, $whitelisted_numbers)) {
            //     $journey_whitelisted_flag = 1;
            // }

            $app_banner_status = array();

            $app_banner_status['app_banner_progress_percent'] = $app_banner_progress_percent;

            // $response_array['mobile'] = $mobile;
            $response_array['full_name'] = !empty($full_name) ? $full_name : NULL;
            $response_array['profile_filled_percent'] = $profile_filled_percent;
            $response_array['profile_pic'] = $profile_pic;
            $response_array['registration_successful'] = $registration_successful;
            $response_array['active_loan_details'] = $active_loan_details;
            $response_array['collection_history'] = $collection_history;
            $response_array['next_event_name'] = $next_event_name;
            // $response_array['pancard'] = $pancard;
            $response_array['banners'] = $app_banners;

            if ($lead_details['application_status'] == 1) {
                $application_submitted = 1;
            }

            $assigned_user_id = !empty($lead_details['lead_credit_assign_user_id']) ? $lead_details['lead_credit_assign_user_id'] : $lead_details['lead_screener_assign_user_id'];
            $response_array['application_submitted'] = $application_submitted;
            $response_array['application_filled_percent'] = !empty($application_filled_percent) ? $application_filled_percent : 0;
            $response_array['journey_whitelisted_flag'] = $journey_whitelisted_flag;
            $response_array['contact_us_number'] = REGISTED_MOBILE;
            $response_array['contact_us_email'] = INFO_EMAIL;
            $response_array['contact_us_whatsapp'] = REGISTED_WHATSAPP_MOBILE;
            $response_array['excutive_details'] = $this->getExcutiveDetails($assigned_user_id);

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = !empty($message) ? $message : NULL;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getExcutiveDetails($user_id) {
        $return_array = array();
        $excutive_details = $this->Tasks->getExcutiveDetails($user_id);

        $return_array = $excutive_details['data'];

        return $return_array;
    }

    public function getLeadList_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean($input_data);
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $post = json_decode($post, true);
        $pancard = !empty($post['pancard']) ? $post['pancard'] : NULL;

        $headers = $this->input->request_headers();

        $response_array = array();
        $apiStatusData = array();
        $lead_list = array();

        try {
            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers);
            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            if (empty($pancard)) {
                throw new ErrorException("Pancard cannot be empty");
            }

            $get_lead_list = $this->Tasks->get_lead_list($pancard);
            if ($get_lead_list['status'] == 1) {
                $lead_list = $get_lead_list['leads_data'];
            }

            $response_array['lead_list'] = $lead_list;
            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = "Lead List Found";
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }
        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getApplicationFilledPercent($cust_profile_id) {
        $profile_filled_percent = 10;
        $application_filled_percent = 0;
        $return_array = array();
        if (empty($cust_profile_id)) {
            return null;
        }

        $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

        if ($get_customer_profile_details['status'] != 1) {
            throw new ErrorException("Customer Details Not Found");
        }

        $get_customer_profile_details = $get_customer_profile_details['customer_profile_details'];

        $get_journey_events = $this->Tasks->select_data_by_filter(['pje_profile_id' => $cust_profile_id], '*', 'profile_journey_events', 'pje_id DESC', 1);

        if ($get_journey_events->num_rows() > 0) {
            $journey_events = $get_journey_events->row_array();
        }

        if (($journey_events['pje_pancard_verification'] == 1) && ($get_customer_profile_details['cp_pancard_verified_status'] == 1)) {
            //            $profile_filled_percent += 25;
            $profile_filled_percent = 30;
        }

        if (($journey_events['pje_personal_details'] == 1) && ($get_customer_profile_details['cp_pancard_verified_status'] == 1)) {
            //            $profile_filled_percent += 25;
            $profile_filled_percent = 60;
        }

        if ($journey_events['pje_personal_details'] == 1 && $journey_events['pje_residence_details'] == 1 && $get_customer_profile_details['cp_pancard_verified_status'] == 1) {
            //            if (!empty($get_customer_profile_details['cp_residence_address_1'])) {
            //                $profile_filled_percent += 5;
            //            }
            //            if (!empty($get_customer_profile_details['cp_residence_address_2'])) {
            //                $profile_filled_percent += 5;
            //            }
            //            if (!empty($get_customer_profile_details['cp_residence_landmark'])) {
            //                $profile_filled_percent += 5;
            //            }
            //            if (!empty($get_customer_profile_details['cp_residence_pincode'])) {
            //                $profile_filled_percent += 5;
            //            }
            //            if (!empty($get_customer_profile_details['cp_residence_type_id'])) {
            //                $profile_filled_percent += 5;
            //            }
            $profile_filled_percent = 90;
        }

        if ($journey_events['pje_personal_details'] == 1 && $journey_events['pje_residence_details'] == 1 && $journey_events['pje_income_details'] == 1 && $get_customer_profile_details['cp_pancard_verified_status'] == 1) {
            //            $profile_filled_percent += 25;
            $profile_filled_percent = 95;
        }

        if ($journey_events['pje_personal_details'] == 1 && $journey_events['pje_residence_details'] == 1 && $journey_events['pje_income_details'] == 1 && $journey_events['pje_selfie_upload'] == 1 && $get_customer_profile_details['cp_pancard_verified_status'] == 1) {
            //            $profile_filled_percent += 25;
            $profile_filled_percent = 100;
        }

        //        if (($journey_events['pje_personal_details'] == 1 && $journey_events['pje_residence_details'] == 1 && $journey_events['pje_income_details'] == 1 && $journey_events['pje_selfie_upload'] == 1) && ($get_customer_profile_details['cp_pancard_verified_status'] == 1)) {
        ////            $profile_filled_percent += 15;
        //            $profile_filled_percent = 95;
        //        }
        //        if (($get_customer_profile_details['cp_registration_successful'] == 1) && ($journey_events['pje_personal_details'] == 1 && $journey_events['pje_residence_details'] == 1 && $journey_events['pje_income_details'] == 1 && $journey_events['pje_selfie_upload'] == 1) && ($get_customer_profile_details['cp_pancard_verified_status'] == 1)) {
        //            $profile_filled_percent = 100;
        //        }

        if (($get_customer_profile_details['cp_registration_successful'] == 1) && !empty($get_customer_profile_details['cp_lead_id']) && !in_array($get_customer_profile_details['cp_status_id'], array(8, 9))) {
            $get_lead_journey_events = $this->Tasks->selectdata(['lje_lead_id' => $get_customer_profile_details['cp_lead_id']], '*', 'lead_journey_events');

            if ($get_lead_journey_events->num_rows() > 0) {
                $lead_journey_events = $get_lead_journey_events->row_array();
            }

            if ($get_customer_profile_details['cp_registration_successful'] == 1 || $lead_journey_events['lje_eligibility_confirmed'] == 1) {
                $application_filled_percent += 70;
            }


            if ($lead_journey_events['lje_loan_quote'] == 1) {
                $application_filled_percent += 10;
            }
            if ($lead_journey_events['lje_employment_details'] == 1) {
                //                $application_filled_percent += 5;
            }
            if ($lead_journey_events['lje_ekyc_verified'] == 1 || $lead_journey_events['lje_ekyc_skipped'] == 1) {
                $application_filled_percent += 10;
            }
            if ($lead_journey_events['lje_account_aggregator'] == 1) {
                //                $application_filled_percent += 5;
            }
            //            if ($lead_journey_events['lje_bank_statement_upload'] == 1) {
            //                $application_filled_percent += 5;
            //            }
            //            if ($lead_journey_events['lje_pay_slip_upload'] == 1) {
            //                $application_filled_percent += 5;
            //            }
            //            if ($lead_journey_events['lje_pan_upload'] == 1) {
            //                $application_filled_percent += 5;
            //            }
            //            if ($lead_journey_events['lje_aadhaar_upload'] == 1) {
            //                $application_filled_percent += 5;
            //            }
            if ($lead_journey_events['lje_residence_proof_upload'] == 1) {
                $application_filled_percent += 5;
            }

            if ($lead_journey_events['lje_banking_details'] == 1) {
                $application_filled_percent += 5;
            }

            if ($lead_journey_events['lje_thank_you'] == 1) {
                $application_filled_percent = 100;
            }

            $return_array['application_filled_percent'] = $application_filled_percent;
            $return_array['lead_journey_events'] = $lead_journey_events;
        }
        $return_array['profile_filled_percent'] = $profile_filled_percent;
        $return_array['profile_journey_events'] = $journey_events;
        return $return_array;
    }

    public function deleteCustomerProfile_post() {
        $response_array = array();

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $this->commonComponent = new CommonComponent();

        $apiStatusData = NULL;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $update_data = array();

        $message = '';

        $headers = $this->input->request_headers();

        $page_name = $post["current_page"];
        $cust_profile_id = (!empty($post["cust_profile_id"]) ? $this->encrypt->decode($post["cust_profile_id"]) : NULL);
        $delete_profile_consent = (!empty($post['delete_profile_consent']) && $post['delete_profile_consent'] == 1) ? 1 : NULL;
        $delete_profile_otp = !empty($post['delete_profile_otp']) ? $post['delete_profile_otp'] : NULL;

        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];
        $browser = $this->agent->browser();

        $insert_log_array = array();
        $insert_log_array['mapp_ip_address'] = $ip_address;
        $insert_log_array['mapp_browser_info'] = $browser;
        $insert_log_array['mapp_action_name'] = $page_name;
        $insert_log_array['mapp_request'] = json_encode($post);
        $insert_log_array['mapp_profile_id'] = $cust_profile_id;
        $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            if (empty($page_name)) {
                throw new ErrorException("Invalid Page Access!");
            }

            $token_verification = $this->checkAuthorization($headers, $page_name);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("cust_profile_id", "Customer Profile ID", "required");
            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_customer_profile_details['status'] != 1) {
                throw new ErrorException("Your profile detail does not found.");
            }

            $customer_profile_details = $get_customer_profile_details['customer_profile_details'];

            $mobile = $customer_profile_details['cp_mobile'];

            $check_active_loan = $this->Tasks->get_active_loan($customer_profile_details['cp_pancard']);

            if ($check_active_loan['status'] == 1) {
                $get_active_loan = $check_active_loan['get_active_loan'];
                if ($get_active_loan['active_loan_count'] > 0) {
                    throw new ErrorException("Please repay your active loan first");
                }
            }

            if ($page_name == "delete_profile_send_otp") {
                $otp = rand(1111, 9999);

                if ($mobile == "9560807913") {
                    $otp = 1989;
                }

                $lead_id = !empty($customer_profile_details['cp_lead_id']) ? $customer_profile_details['cp_lead_id'] : NULL;

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_profile_id' => $cust_profile_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 6,
                    'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
                    'lot_otp_valid_time' => 10,
                );

                $this->Tasks->insert('leads_otp_trans', $insertDataOTP);

                $lead_otp_id = $this->db->insert_id();

                $sms_input_data = array();
                $sms_input_data['mobile'] = $mobile;
                $sms_input_data['name'] = "Customer";
                $sms_input_data['otp'] = $otp;

                $send_otp = $this->commonComponent->payday_sms_api(1, $lead_id, $sms_input_data);

                if ($send_otp['status'] != 1) {
                    throw new ErrorException("Mobile number is invalid");
                }

                if (empty($lead_otp_id)) {
                    throw new ErrorException("An error occurred!");
                }

                $message = 'OTP sent successfully';
            } else if ($page_name == "delete_profile_verify_otp") {
                $this->form_validation->set_rules("delete_profile_otp", "OTP", "required|numeric");
                $this->form_validation->set_rules("delete_profile_consent", "Delete Profile Consent", "required|numeric");
                if ($this->form_validation->run() == FALSE) {
                    throw new ErrorException(strip_tags(validation_errors()));
                }

                if ($delete_profile_consent != 1) {
                    throw new ErrorException("Consent Not Provided");
                }

                $check_existing_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

                if ($check_existing_profile['status'] != 1) {
                    throw new Exception("Customer is not registered!");
                }

                $get_existing_customer = $check_existing_profile['customer_profile_details'];

                $mobile = $get_existing_customer['cp_mobile'];

                $get_otp_trans_logs = $this->Tasks->get_otp_trans_logs($mobile, $delete_profile_otp);

                $result1 = $get_otp_trans_logs['otp_trans_logs'];

                if ($delete_profile_otp != $result1['lot_mobile_otp']) {
                    throw new ErrorException('Please enter correct otp or resent/generate otp.');
                }

                $login_date_time = date('Y-m-d H:i:s');
                $mot_otp_valid_time = $result1['lot_otp_valid_time'];
                $mot_otp_trigger_time = $result1['lot_otp_trigger_time'];

                $dateTimeObject1 = date_create($mot_otp_trigger_time);
                $dateTimeObject2 = date_create(date('Y-m-d H:i:s'));
                $interval = date_diff($dateTimeObject1, $dateTimeObject2);
                $interval->format('%R%a days');
                $min = $interval->days * 24 * 60;
                $min += $interval->h * 60;
                $min += $interval->i;

                $ctime = $min;
                $validtime = $mot_otp_valid_time;

                if ($validtime < $ctime) {
                    throw new ErrorException('Your OTP has been expired, Please resent/generate the otp again.');
                }

                $leads_otp_trans_conditions['lot_id'] = $result1['lot_id'];
                $update_leads_otp_trans['lot_otp_verify_flag'] = 1;

                $this->Tasks->update($leads_otp_trans_conditions, 'leads_otp_trans', $update_leads_otp_trans);

                $update_customer_profile = array();
                $update_customer_profile['cp_is_mobile_verified'] = NULL;
                $update_customer_profile['cp_pancard'] = NULL;
                $update_customer_profile['cp_pancard_verified_status'] = NULL;
                $update_customer_profile['cp_pancard_verified_on'] = NULL;
                $update_customer_profile['cp_geo_lat'] = NULL;
                $update_customer_profile['cp_geo_long'] = NULL;
                $update_customer_profile['cp_login_geo_lat'] = NULL;
                $update_customer_profile['cp_login_geo_long'] = NULL;
                $update_customer_profile['cp_is_journey_completed'] = NULL;
                $update_customer_profile['cp_journey_type_id'] = NULL;
                $update_customer_profile['cp_journey_stage'] = NULL;
                $update_customer_profile['cp_fcm_token'] = NULL;
                $update_customer_profile['cp_login_fcm_token'] = NULL;
                $update_customer_profile['cp_login_geo_lat'] = NULL;
                $update_customer_profile['cp_login_geo_long'] = NULL;
                $update_customer_profile['cp_status_id'] = NULL;
                $update_customer_profile['cp_is_cif_fetched'] = NULL;
                $update_customer_profile['cp_cif_no'] = NULL;
                $update_customer_profile['cp_first_name'] = NULL;
                $update_customer_profile['cp_middle_name'] = NULL;
                $update_customer_profile['cp_sur_name'] = NULL;
                $update_customer_profile['cp_dob'] = NULL;
                $update_customer_profile['cp_gender'] = NULL;
                $update_customer_profile['cp_residence_address_1'] = NULL;
                $update_customer_profile['cp_residence_address_2'] = NULL;
                $update_customer_profile['cp_residence_type_id'] = NULL;
                $update_customer_profile['cp_residence_landmark'] = NULL;
                $update_customer_profile['cp_residence_pincode'] = NULL;
                $update_customer_profile['cp_residence_city_id'] = NULL;
                $update_customer_profile['cp_residence_state_id'] = NULL;
                $update_customer_profile['cp_residence_branch_id'] = NULL;
                $update_customer_profile['cp_personal_email'] = NULL;
                $update_customer_profile['cp_personal_email_verified_status'] = NULL;
                $update_customer_profile['cp_personal_email_verified_on'] = NULL;
                $update_customer_profile['cp_office_email'] = NULL;
                $update_customer_profile['cp_alternate_mobile'] = NULL;
                $update_customer_profile['cp_marital_status_id'] = NULL;
                $update_customer_profile['cp_spouse_name'] = NULL;
                $update_customer_profile['cp_aadhaar_no'] = NULL;
                $update_customer_profile['cp_religion_id'] = NULL;
                $update_customer_profile['cp_updated_at'] = date("Y-m-d H:i:s");
                $update_customer_profile['cp_father_name'] = NULL;
                $update_customer_profile['cp_user_type'] = NULL;
                $update_customer_profile['cp_profile_pic'] = NULL;
                $update_customer_profile['cp_income_type_id'] = NULL;
                $update_customer_profile['cp_monthly_income'] = NULL;
                $update_customer_profile['cp_salary_date'] = NULL;
                $update_customer_profile['cp_salary_mode'] = NULL;
                $update_customer_profile['cp_registration_successful'] = NULL;
                $update_customer_profile['cp_lead_id'] = NULL;

                $update_customer_profile['cp_data_delete_flag'] = 1;
                $update_customer_profile['cp_data_delete_datetime'] = date("Y-m-d H:i:s");

                $this->Tasks->updateCustomerProfile($cust_profile_id, $update_customer_profile);

                $this->Tasks->resetJourneyEvents($cust_profile_id, 1);

                $message = 'Account Deleted Successfully';
            } else {
                throw new ErrorException("Page Not Found");
            }

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        } catch (CustomException $ce) {
            $apiStatusId = 5;
            $apiStatusData = NULL;
            $apiStatusMessage = $ce->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function getLoanHistory_post() {
        $response_array = array();

        $loan_data = array();

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $message = null;

        $headers = $this->input->request_headers();

        $page_name = $post["current_page"];

        $cust_profile_id = (!empty($post["cust_profile_id"]) ? $this->encrypt->decode($post["cust_profile_id"]) : NULL);

        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];

        $browser = $this->agent->browser();

        $insert_log_array = array();
        $insert_log_array['mapp_ip_address'] = $ip_address;
        $insert_log_array['mapp_browser_info'] = $browser;
        $insert_log_array['mapp_action_name'] = $page_name;
        $insert_log_array['mapp_request'] = json_encode($post);
        $insert_log_array['mapp_profile_id'] = $cust_profile_id;
        $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers, $page_name);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("cust_profile_id", "Customer Profile ID", "required");

            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $get_customer_profile_details = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($get_customer_profile_details['status'] != 1) {
                throw new ErrorException("Your profile detail does not found.");
            }

            $customer_profile_details = $get_customer_profile_details['customer_profile_details'];

            $pancard = $customer_profile_details['cp_pancard'];

            if ($customer_profile_details['cp_registration_successful'] != 1) {
                throw new ErrorException("User not registered.");
            }

            $getLoanHistory = $this->Tasks->get_loan_history($pancard);

            if ($getLoanHistory['status'] != 1) {
                throw new ErrorException("Loan History Not Found.");
            }

            $loanHistory = $getLoanHistory['data'];

            foreach ($loanHistory as $loan_history) {
                if ($loan_history["product_id"] == 2) {
                    //if(false){
                    if (in_array($loan_history['lead_status_id'], array(14, 19))) {
                        $loan_data['loan_active_status'] = 1;
                    } else if (in_array($loan_history['lead_status_id'], array(16))) {
                        $loan_data['loan_active_status'] = 0;
                    } else if (in_array($loan_history['lead_status_id'], array(17, 18, 40))) {
                        $loan_data['loan_active_status'] = 2;
                    }
                } else if ($loan_history["product_id"] == 1) {
                    if (in_array($loan_history['lead_status_id'], array(14, 19))) {
                        $loan_data['loan_active_status'] = 1;
                    } else if (in_array($loan_history['lead_status_id'], array(16, 17, 18))) {
                        $loan_data['loan_active_status'] = 0;
                    } else if (in_array($loan_history['lead_status_id'], array(40))) {
                        $loan_data['loan_active_status'] = 2;
                    }
                }
                $loan_data['lead_id'] = $loan_history['lead_id'];
                $loan_data['loan_no'] = $loan_history['loan_no'];
                $loan_data['loan_recommended'] = (int) $loan_history['loan_recommended'];
                $loan_data['roi'] = (int) $loan_history['roi'];
                $loan_data['repayment_amount'] = (int) $loan_history['repayment_amount'];
                $loan_data['repayment_date'] = !empty($loan_history['repayment_date']) ? date("d-m-Y", strtotime($loan_history['repayment_date'])) : '-';
                $loan_data['tenure'] = (int) $loan_history['tenure'];
                $loan_data['disbursal_date'] = $loan_history['disbursal_date'];
                $loan_data['penal_roi'] = (int) $loan_history['panel_roi'];
                $loan_data['loan_status'] = $loan_history['status'];
                $loan_data['loan_total_payable_amount'] = (int) $loan_history['loan_total_payable_amount'];
                $loan_data['loan_total_received_amount'] = (int) $loan_history['loan_total_received_amount'];
                $loan_data['loan_total_penalty_amount'] = ($loan_history['loan_total_payable_amount'] - $loan_history['repayment_amount']);
                $loan_data['loan_total_outstanding_amount'] = (int) $loan_history['loan_total_outstanding_amount'];

                $response_array[] = $loan_data;
            }

            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        } catch (CustomException $ce) {
            $apiStatusId = 5;
            $apiStatusData = NULL;
            $apiStatusMessage = $ce->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function initiateRazorRequest_post() {

        $apiStatusData = array();
        $apiStatusId = 0;
        $response_status = 2;
        $apiResponseDateTime = "";
        $apiRequestDateTime = date('Y-m-d H:i:s');
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestArray = array();
        $provider_id = 4;
        $curlError = "";
        $userAndPass = "";
        $payment_status_id = 0;
        $hardcode_response = false;

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $debug = !empty($_REQUEST['rptest']) ? 1 : 0;

        $headers = $this->input->request_headers();

        $token_auth = $this->_token();

        $token = $token_auth['token_android_digital'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            try {

                $lead_id = $post["lead_id"];
                $input_amount = $post["amount"];

                if (empty($lead_id)) {
                    throw new Exception("Lead id missing");
                }

                if (empty($input_amount)) {
                    throw new Exception("Loan amount missing");
                }

                $leadDetails = $this->Tasks->getLeadDetails($lead_id);
                if ($leadDetails['status'] != 1) {
                    throw new Exception("Lead details not found");
                }

                $leadDetails = $leadDetails['lead_details'];
                if (!in_array($leadDetails['lead_status_id'], array(14, 19))) {
                    throw new Exception("Customer Loan is not active");
                }

                $loan_no = $leadDetails['loan_no'];
                if (empty($loan_no)) {
                    throw new Exception("Customer Loan is not active");
                }

                $gatewayCredentials = $this->Tasks->getPaymentGateway($leadDetails['company_id'], 13);

                if ($gatewayCredentials['status'] != 1) {
                    throw new Exception("Gateway credentials not found");
                }

                $gatewayCredentials = $gatewayCredentials['data'];

                $apiStatusData['gateway_key'] = $gatewayCredentials['mpg_gateway_key'];
                $apiStatusData['gateway_secret_key'] = $gatewayCredentials['mpg_gateway_secret_key'];
                $apiStatusData['company_id'] = $leadDetails['company_id'];

                $apiRequestArray = array(
                    "amount" => ($input_amount * 100),
                    "currency" => "INR",
                    "receipt" => $loan_no,
                    "notes" => array(
                        "lead_id" => $lead_id,
                        "loan_no" => $loan_no,
                        "payment_requested_by" => $payment_requested_by
                    )
                );

                $apiUrl = $this->razorpay_url . "/v1/orders";

                $apiRequestJson = json_encode($apiRequestArray);

                $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

                $apiHeaders[] = "Content-Type: application/json";
                $apiHeaders[] = "Content-Length: " . strlen($apiRequestJson);
                $userAndPass = $gatewayCredentials['mpg_gateway_key'] . ":" . $gatewayCredentials['mpg_gateway_secret_key'];
                if ($debug == 1) {
                    echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                    echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
                    echo "<br/><br/> =======User & Password Plain======<br/><br/>" . $userAndPass;
                }

                if ($hardcode_response) {
                    $apiResponseJson = '{
                    "id": "order_NZyXn4bgxUi4Rj",
                    "entity": "order",
                    "amount": 100,
                    "amount_paid": 0,
                    "amount_due": 100,
                    "currency": "INR",
                    "receipt": "Receipt no. 1",
                    "offer_id": null,
                    "status": "created",
                    "attempts": 0,
                    "notes": {
                        "lead_id": "1234",
                        "loan_no": "RP123"
                    },
                    "created_at": 1707742510
                }';
                } else {
                    $curl = curl_init($apiUrl);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, $userAndPass);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
                    $apiResponseJson = curl_exec($curl);
                }

                $apiResponseDateTime = date('Y-m-d H:i:s');
                $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

                if ($debug == 1) {
                    echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
                }

                if (curl_errno($curl) && !$hardcode_response) {
                    $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
                    curl_close($curl);
                    throw new RuntimeException("Something went wrong. Please try after sometime.");
                } else {

                    if (isset($curl)) {
                        curl_close($curl);
                    }

                    $apiResponseData = json_decode($apiResponseJson, true);

                    if (!empty($apiResponseData)) {
                        if (!empty($apiResponseData)) {
                            if (isset($apiResponseData['id']) && !empty($apiResponseData['id'])) {
                                $apiStatusId = 1;
                                $razor_id = $apiResponseData['id'];
                            } else {
                                $temp_error = !empty(json_encode($apiResponseData['error'])) ? json_encode($apiResponseData['error']) : "Some error occurred. Please try again..";
                                throw new ErrorException($temp_error);
                            }
                        } else {
                            $temp_error = !empty(json_encode($apiResponseData['error'])) ? json_encode($apiResponseData['error']) : "Some error occurred. Please try again.";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        throw new ErrorException("Invalid api response..");
                    }
                }
            } catch (ErrorException $le) {
                $apiStatusId = 2;
                $apiStatusMessage = $le->getMessage();
            } catch (RuntimeException $le) {
                $apiStatusId = 3;
                $apiStatusMessage = $curlError;
            } catch (Exception $e) {
                $apiStatusId = 4;
                $apiStatusMessage = $e->getMessage();
            }

            $apiStatusData['razor_order_id'] = $razor_id;
            $apiStatusData['payment_amount'] = $input_amount;
            $apiStatusData['lead_id'] = $lead_id;

            if ($apiStatusId == 1) {
                $payment_status_id = 1;
                $response_status = 1;
            } else if ($apiStatusId != 4) {
                $payment_status_id = 3;
                $response_status = 2;
            } else {
                $payment_status_id = 4;
                $response_status = 2;
            }
            $product_id = !empty($post['product_type']) && $post['product_type'] == 1 ? 2 : 1;
            $insertDataRepaymentLogs = array(
                'repayment_product_id' => $product_id,
                'repayment_provider_id' => $provider_id, // 4=> Razor Pay
                'repayment_method_id' => 1, //Request Initiated
                'repayment_source_id' => 3, // through Mobile
                'repayment_api_status_id' => $payment_status_id,
                'repayment_lead_id' => $lead_id,
                'repayment_trans_no' => $order_id,
                'repayment_amount' => $input_amount,
                'repayment_request' => $apiRequestJson,
                'repayment_response' => $apiResponseJson,
                'repayment_errors' => !empty($apiStatusMessage) ? $apiStatusMessage : null,
                'repayment_request_datetime' => $apiRequestDateTime,
                'repayment_response_datetime' => $apiResponseDateTime,
                'repayment_user_id' => null
            );

            $this->db->insert('api_repayment_logs', $insertDataRepaymentLogs);

            return json_encode($this->response(['Status' => $response_status, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => $response_status, 'Message' => "Unauthorized Access: You do not have permission to access this resource.", 'Data' => array()], REST_Controller::HTTP_OK));
        }
    }

    public function completeRazorRequest_post() {
        $apiStatusId = 0;
        $response_status = 2;
        $apiResponseDateTime = "";
        $apiResponseJson = "";
        $company_id = 1;
        $lead_id = 0;
        $loan_no = "";
        $curlError = "";
        $userAndPass = "";
        $hardcode_response = false;
        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $debug = !empty($_REQUEST['rptest']) ? 1 : 0;
        $product_id = !empty($post['product_type']) && $post['product_type'] == 1 ? 2 : 1;
        $headers = $this->input->request_headers();

        $token_auth = $this->_token();

        $token = $token_auth['token_android_digital'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            try {
                $lead_id = $post['lead_id'];
                $leadDetails = $this->Tasks->getLeadDetails($lead_id);
                if ($leadDetails['status'] != 1) {
                    throw new Exception("Lead details not found");
                }

                $leadDetails = $leadDetails['lead_details'];
                $gatewayCredentials = $this->Tasks->getPaymentGateway($leadDetails['company_id'], 13);

                if ($gatewayCredentials['status'] != 1) {
                    throw new Exception("Gateway credentials not found");
                }

                $company_id = $leadDetails['company_id'];
                $loan_no = $leadDetails['loan_no'];

                $gatewayCredentials = $gatewayCredentials['data'];

                $razorpay_payment_id = $post["razorpay_payment_id"];
                $userAndPass = $gatewayCredentials['mpg_gateway_key'] . ":" . $gatewayCredentials['mpg_gateway_secret_key'];

                if (empty($razorpay_payment_id)) {
                    throw new Exception("Payment Id missing");
                }

                $apiUrl = $this->razorpay_url . "/v1/payments/" . $razorpay_payment_id;

                $apiHeaders = array("Content-Type: application/json");

                if ($debug == 1) {
                    echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                }

                if ($hardcode_response) {
                    $apiResponseJson = '{
                        "id": "pay_O4nvC3tBc5pNTn",
                        "entity": "payment",
                        "amount": 1000236,
                        "currency": "INR",
                        "status": "captured",
                        "order_id": "order_O4nuiJFQZUETWI",
                        "invoice_id": null,
                        "international": false,
                        "method": "upi",
                        "amount_refunded": 0,
                        "refund_status": null,
                        "captured": true,
                        "description": "Repayment of Loan",
                        "card_id": null,
                        "bank": null,
                        "wallet": null,
                        "vpa": "7709582359@ybl",
                        "email": "void@razorpay.com",
                        "contact": "+919834731557",
                        "notes": {
                            "lead_id": "691293",
                            "loan_no": "RPPLZ00000015258",
                            "payment_requested_by": 0
                        },
                        "fee": 236,
                        "tax": 36,
                        "error_code": null,
                        "error_description": null,
                        "error_source": null,
                        "error_step": null,
                        "error_reason": null,
                        "acquirer_data": {
                            "rrn": "412117693353",
                            "upi_transaction_id": "AXId056f4f631a74aab8be85eea7038532d"
                        },
                        "created_at": 1714473645,
                        "provider": null,
                        "upi": {
                            "payer_account_type": "bank_account",
                            "vpa": "7709582359@ybl"
                        },
                        "reward": null
                    }';
                } else {

                    $curl = curl_init($apiUrl);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, $userAndPass);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $apiResponseJson = curl_exec($curl);
                }

                $apiResponseDateTime = date('Y-m-d H:i:s');
                $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

                if ($debug == 1) {
                    echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
                }

                if (curl_errno($curl) && !$hardcode_response) {
                    $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
                    curl_close($curl);
                    throw new RuntimeException("Something went wrong. Please try after sometime.");
                } else {

                    if (isset($curl)) {
                        curl_close($curl);
                    }

                    $apiResponseData = json_decode($apiResponseJson, true);

                    if (!empty($apiResponseData)) {
                        if (!empty($apiResponseData)) {
                            if (isset($apiResponseData['id']) && !empty($apiResponseData['id'])) {
                                $apiStatusId = 1;
                                $order_status = $apiResponseData['status'];
                                $order_id = $apiResponseData['order_id'];
                                $trans_fee = (($apiResponseData['fee'] > 0) ? ($apiResponseData['fee'] / 100) : 0);
                                $service_tax = (($apiResponseData['tax'] > 0) ? ($apiResponseData['tax'] / 100) : 0);
                                $payment_mode = $apiResponseData['method'];
                                $payment_amount = ((($apiResponseData['amount']) / 100) - $trans_fee);
                                $apiStatusMessage = "Payment successfull";
                            } else {
                                $temp_error = !empty(json_encode($apiResponseData['error'])) ? json_encode($apiResponseData['error']) : "Some error occurred. Please try again..";
                                throw new ErrorException($temp_error);
                            }
                        } else {
                            $temp_error = !empty(json_encode($apiResponseData['error'])) ? json_encode($apiResponseData['error']) : "Some error occurred. Please try again.";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        throw new ErrorException("Invalid api response..");
                    }
                }
            } catch (ErrorException $le) {
                $apiStatusId = 2;
                $apiStatusMessage = $le->getMessage();
            } catch (RuntimeException $le) {
                $apiStatusId = 3;
                $apiStatusMessage = $curlError;
            } catch (Exception $e) {
                $apiStatusId = 4;
                $apiStatusMessage = $e->getMessage();
            }

            if ($apiStatusId == 1) {
                $payment_status_id = 1;
                $response_status = 1;
            } else if ($apiStatusId != 4) {
                $payment_status_id = 3;
                $response_status = 2;
            } else {
                $payment_status_id = 4;
                $response_status = 2;
            }

            $updateDataRepaymentLogs = array(
                'repayment_method_id' => 2,
                'repayment_api_status_id' => $payment_status_id,
                'repayment_amount' => $payment_amount,
                'repayment_trans_fee' => $trans_fee,
                'repayment_service_tax' => $service_tax,
                'repayment_payment_mode' => $payment_mode,
                'repayment_errors' => $apiStatusMessage,
                'repayment_response' => $apiResponseJson,
                'repayment_bank_reference_no' => $razorpay_payment_id,
                'repayment_response_datetime' => $apiResponseDateTime
            );

            $this->db->where(['repayment_trans_no' => $order_id, 'repayment_api_status_id' => 2])->update('api_repayment_logs', $updateDataRepaymentLogs);

            if ($payment_status_id == 1) {
                $insertCollectinData = array(
                    'company_id' => $company_id,
                    'product_id' => $product_id,
                    'lead_id' => $lead_id,
                    'loan_no' => $loan_no,
                    'payment_mode' => 'Razorpay',
                    'payment_mode_id' => 13,
                    'received_amount' => $payment_amount,
                    'refrence_no' => $razorpay_payment_id,
                    'date_of_recived' => date("Y-m-d"),
                    'repayment_type' => 19,
                    'remarks' => $post['remarks'],
                    'ip' => $ip_address,
                    'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                    'payment_verification' => 0,
                    'refund' => 0
                );

                $this->db->insert('collection', $insertCollectinData);
            }

            return json_encode($this->response(['Status' => $response_status, 'Message' => $apiStatusMessage, 'Data' => $apiResponseData], REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => $response_status, 'Message' => "Unauthorized Access: You do not have permission to access this resource.", 'Data' => array()], REST_Controller::HTTP_OK));
        }
    }

    /*------------emi scheduler---------------*/

    public function EmiSchedulerMethod_post() {

        $response_array = array();

        $loan_data = array();

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $message = null;

        $headers = $this->input->request_headers();

        $page_name = $post["current_page"];

        $loan_account_no = (!empty($post["loan_account_no"]) ? $post["loan_account_no"] : NULL);
        $ip_address = !empty($this->input->ip_address()) ? $this->input->ip_address() : $_SERVER['REMOTE_ADDR'];

        $browser = $this->agent->browser();

        $insert_log_array = array();
        $insert_log_array['mapp_ip_address'] = $ip_address;
        $insert_log_array['mapp_browser_info'] = $browser;
        $insert_log_array['mapp_action_name'] = $page_name;
        $insert_log_array['mapp_request'] = json_encode($post);
        $insert_log_array['mapp_profile_id'] = $cust_profile_id;
        $mapp_log_id = $this->Tasks->insertMobileApplicationLog($this->mobile_app_source_id, $lead_id, $insert_log_array);

        try {

            if ($this->input->method(TRUE) != "POST") {
                throw new Exception("Request Method Post Failed");
            }

            $token_verification = $this->checkAuthorization($headers, $page_name);

            if ($token_verification['status'] != 1) {
                throw new Exception($token_verification['message']);
            }
            //print_r($post);die;
            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("loan_account_no", "loan account no is required", "required");

            if ($this->form_validation->run() == FALSE) {
                throw new ErrorException(strip_tags(validation_errors()));
            }

            $getEmiHistory = $this->Tasks->emiScheduler($loan_account_no);

            if ($getEmiHistory['status'] != 1) {
                throw new ErrorException("emi schedule is not found.");
            }


            $response_array = $getEmiHistory['data'];
            $curr_date = date('Y-m-d');
            foreach ($response_array as $key => $value) {
                $response_array[$key]["emi_total_amount"] = (int)($value["principal_amount"] + $value["interest_amount"] + $value["bounce_charge"] + $value["penalty_amount"] + $value["emi_penalty_dpd"]);
                $response_array[$key]["principal_amount"] = (int)$value["principal_amount"];
                $response_array[$key]["interest_amount"] = (int)$value["interest_amount"];
                $response_array[$key]["bounce_charge"] = (int)$value["bounce_charge"];
                $response_array[$key]["penalty_amount"] = (int)$value["penalty_amount"];
                $response_array[$key]["emi_penalty_dpd"] = (int)$value["emi_penalty_dpd"];
                $response_array[$key]["emi_amount"] = (int)$value["emi_amount"];
                $response_array[$key]["emi_date"] = date('d-m-Y', strtotime($value["emi_date"]));
                if ($value["emi_status"] == "PENDING") {
                    $response_array[$key]["active_status"] = 1;
                } else {
                    $response_array[$key]["active_status"] = 0;
                }
            }
            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = $message;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        } catch (CustomException $ce) {
            $apiStatusId = 5;
            $apiStatusData = NULL;
            $apiStatusMessage = $ce->getMessage();
        }

        $update_log_data['mapp_response'] = (!empty($apiStatusData) ? json_encode($apiStatusData) : NULL);
        $update_log_data['mapp_errors'] = $apiStatusMessage;
        $update_log_data['mapp_api_status_id'] = $apiStatusId;
        $this->Tasks->updateMobileApplicationLog($mapp_log_id, $lead_id, $update_log_data);

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function generateCAM($lead_id) {
        // error_reporting(E_ALL);
        // ini_set("display_errors", 1);
        if (empty($lead_id)) {
            return null;
        }

        $getleadDetails = $this->Tasks->getLeadDetails($lead_id);

        if ($getleadDetails['status'] == 1) {
            $lead_details = $getleadDetails['lead_details'];


            $getLeadCustomerDetails = $this->Tasks->get_lead_customer_details($lead_id);

            if (!empty($getLeadCustomerDetails['status'] == 1)) {
                $get_lead_customer_details = $getLeadCustomerDetails['lead_customer_details'];

                $get_city_category = $this->Tasks->selectdata(['m_city_id' => $get_lead_customer_details['city_id']], 'm_city_category', 'master_city');

                $get_city_category = $get_city_category->row_array();

                $city_category = $get_city_category['m_city_category'];

                $dob = date("Y-m-d", strtotime($get_lead_customer_details['dob']));
                $cibil = $lead_details['cibil'];
                $product_id = $lead_details['product_id'];

                $getCustomerEmployment = $this->Tasks->get_customer_employment_details($lead_id);
                if ($getCustomerEmployment['status'] == 1) {
                    $get_customer_employment_details = $getCustomerEmployment['customer_employment_details'];
                    $doj = date("Y-m-d", strtotime($get_customer_employment_details['emp_residence_since']));

                    $date_diff = abs(strtotime(date('d-m-Y')) - strtotime($doj));
                    $years = floor($date_diff / (365 * 60 * 60 * 24));
                    $presentServiceTenure = floor(($date_diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                }

                $get_salary_details = $this->Tasks->selectdata(['aa_lead_id' => $lead_id, 'aa_active' => 1, 'aa_deleted' => 0, 'aa_method_id' => 5], 'aa_response', 'api_account_aggregator_logs');

                if ($get_salary_details->num_rows() > 0) {
                    $get_salary_details = $get_salary_details->row_array();
                    $salary_details = json_decode($get_salary_details['aa_response'], true);
                    $salary_details = $salary_details['AA_Output']['bankStatementAnalysis']['bank_account'][0]['total_credit_salary'];
                }

                $salary_dates = array();
                $salary_amount = array();
                $salary_day = array();
                foreach ($salary_details as $salary) {

                    if (!empty($salary['lastPaymentDate'])) {
                        $salary_dates[] = date("Y-m-d", strtotime($salary['lastPaymentDate']));
                    }
                    if (!empty($salary['total_salary_credits'])) {
                        $salary_amount[] = $salary['total_salary_credits'];
                    }
                    if (!empty($salary['lastPaymentDate'])) {
                        $salary_day[] = date("d", strtotime($salary['lastPaymentDate']));
                    }
                }

                $salary_date_string = implode("-", $salary_day);
                $next_salary_date_calc = $this->Tasks->calculateMedian($salary_date_string);
                $salary_on_time = $next_salary_date_calc['salary_on_time'];
                $next_pay_date = !empty($next_salary_date_calc['next_pay_date']) ? date("Y-m-d", strtotime($next_salary_date_calc['next_pay_date'])) : "";

                $salary_amount_string = implode("-", $salary_amount);
                $get_average_salary = $this->Tasks->averageSalary($salary_amount_string);
                $average_salary = $get_average_salary['average_salary'];
                $salary_variance = $get_average_salary['salary_variance'];

                $get_ntc_criteria = $this->Tasks->calculation_ntc($dob, $cibil, $presentServiceTenure);
                $borrower_age = $get_ntc_criteria['borrower_age'];
                $job_stability = $get_ntc_criteria['job_stability'];
                $ntc = $get_ntc_criteria['ntc'];
                $cibil = $get_ntc_criteria['cibil'];

                $request_array = array();
                $request_array['cam_appraised_monthly_income'] = $salary_amount[0];

                $bre_quote_engine = $this->commonComponent->call_bre_quote_engine($lead_id, $request_array);

                if ($bre_quote_engine['status'] != 1) {
                    $return_array['errors'] = $bre_quote_engine['error'];
                } else {
                    $eligible_foir_percentage = $bre_quote_engine['eligible_foir_percentage'];
                    $eligible_loan = $bre_quote_engine['max_loan_amount'];
                    $tenure = $bre_quote_engine['max_tenure'];
                }

                $eligibile_loan = '';

                $input = array();
                $input['loan_recommended'] = $eligible_loan;
                $input['obligations'] = $lead_details['obligations'];
                $input['monthly_salary'] = $salary_amount[0];
                $input['eligible_foir_percentage'] = $eligible_foir_percentage;
                $input['roi'] = 0.75;
                $input['processing_fee_percent'] = 15;

                $disbursal_date = date("Y-m-d");
                $repayment_date = date("Y-m-d", strtotime($next_pay_date . "+5 days"));

                $get_cam_calculation = $this->Tasks->calcAmount($input);

                $data = [
                    'lead_id' => $lead_id,
                    'company_id' => 2,
                    'product_id' => $product_id,
                    'ntc' => $ntc,
                    'run_other_pd_loan' => "NO",
                    'delay_other_loan_30_days' => "NO",
                    'city_category' => $city_category,
                    'salary_credit1_date' => $salary_dates[0],
                    'salary_credit2_date' => $salary_dates[1],
                    'salary_credit3_date' => $salary_dates[2],
                    'salary_credit1_amount' => $salary_amount[0],
                    'salary_credit2_amount' => $salary_amount[1],
                    'salary_credit3_amount' => $salary_amount[2],
                    'next_pay_date' => $next_pay_date,
                    'median_salary' => $average_salary,
                    'salary_variance' => $salary_variance,
                    'salary_on_time' => $salary_on_time,
                    'eligible_foir_percentage' => $eligible_foir_percentage,
                    'eligible_loan' => round($eligible_loan, 0),
                    'loan_recommended' => $eligible_loan,
                    'final_foir_percentage' => $get_cam_calculation['final_foir_percentage'],
                    'foir_enhanced_by' => $get_cam_calculation['foir_enhanced_by'],
                    'processing_fee_percent' => $input['processing_fee_percent'],
                    'roi' => $input['roi'],
                    'cam_processing_fee_gst_type_id' => 2, //GST Execlusive
                    'admin_fee' => $get_cam_calculation['admin_fee'], //Total Admin Fee with GST
                    'disbursal_date' => $input['disbursal_date'],
                    'adminFeeWithGST' => $get_cam_calculation['adminFeeWithGST'], //Only GST
                    'total_admin_fee' => $get_cam_calculation['total_admin_fee'], //Net Admin Fee
                    'tenure' => $get_cam_calculation['tenure'],
                    'net_disbursal_amount' => $get_cam_calculation['net_disbursal_amount'],
                    'repayment_amount' => $get_cam_calculation['repayment_amount'],
                    'panel_roi' => $input['roi'] * 2,
                    'cam_appraised_obligations' => $input['obligations'],
                    'cam_appraised_monthly_income' => $input['monthly_salary'],
                    'deviationsApprovedBy' => "",
                    'remark' => 'Autofill',
                    'created_at' => date("Y-m-d H:i:s"),
                    'cam_status' => 1,
                ];

                $insert = 0;


                if ($this->Tasks->insert("credit_analysis_memo", $data)) {
                    $insert = 1;
                }

                return $insert;

                // if ($product_id == 1) {
                //     $repayment_date = $input['repayment_date'];
                // } else if ($product_id == 2) {
                //     $annualInterestRate = $roi * 12;
                //     $first_emi_start_day = $input['first_emi_start_day'];
                // }
            }
        }
    }

    public function check_customer_mandatory_documents_post() {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $cust_profile_id = !empty($post['profile_id']) ? $this->encrypt->decode($post['profile_id']) : NULL;

        try {
            if (empty($cust_profile_id)) {
                throw new Exception("Profile id missing");
            }

            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $this->commonComponent = new CommonComponent();

            $check_existing_profile = $this->Tasks->get_customer_profile_details($cust_profile_id);

            if ($check_existing_profile['status'] != 1) {
                throw new ErrorException("Customer profile not found.");
            }

            $check_existing_profile = $check_existing_profile['customer_profile_details'];
            $lead_id = $check_existing_profile['cp_lead_id'];
            $user_type = $check_existing_profile['cp_user_type'];

            if (empty($lead_id)) {
                throw new ErrorException("Lead id not found.");
            }

            $getCustomerMandatoryDocuments = $this->commonComponent->check_customer_mandatory_documents($lead_id);

            // if ($getCustomerMandatoryDocuments['status'] == 1) {
            //     throw new ErrorException("All Required Documents are uploaded.");
            // }

            $requested_array = array(
                1 => array(
                    "doc_type" => "aadhaar_front",
                    "event_name" => "aadhaar_upload",
                ),
                2 => array(
                    "doc_type" => "aadhaar_back",
                    "event_name" => "aadhaar_upload",
                ),
                4 => array(
                    "event_name" => "pan_upload",
                    "doc_type" => "PApan_uploadN",
                ),
                16 => array(
                    "event_name" => "pay_slip_upload",
                    "doc_type" => "pay_slip_upload",
                ),
                6 => array(
                    "event_name" => "bank_statement_upload",
                    "doc_type" => "bank_statement_upload",
                ),
                8 => array(
                    "event_name" => "residence_proof_upload",
                    "doc_type" => "electricity_bill",
                )
            );


            $response_array = array();

            if ($getCustomerMandatoryDocuments['status'] == 0 && $user_type == "NEW") {
                foreach ($getCustomerMandatoryDocuments['data'] as $key => $value) {
                    $response_array[$key] = $requested_array[$value['id']];
                    $response_array[$key]['name'] = $value['name'];
                    $response_array[$key]['allowed_format'] = $value['allowed_format'];
                }
            } else {
                $response_array[] = [
                    'event_name' => "residence_proof_upload",
                    'doc_type' => "electricity_bill",
                    'name' => "Residence Proof",
                    'allowed_format' => "document"
                ];

                $response_array[] = [
                    'event_name' => "bank_statement_upload",
                    'doc_type' => "bank_statement_upload",
                    'name' => "Bank Statement",
                    'allowed_format' => "document"
                ];
            }


            $apiStatusId = 1;
            $apiStatusData = !empty($response_array) ? $response_array : NULL;
            $apiStatusMessage = NULL;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    public function generateRazorpayOrderID_post() {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $lead_id = !empty($post['lead_id']) ? $post['lead_id'] : NULL;
        $rzp_amount = !empty($post['rzp_amount']) ? $post['rzp_amount'] : NULL;

        try {
            if (empty($lead_id)) {
                throw new Exception("Lead id missing");
            }

            if (empty($rzp_amount)) {
                throw new Exception("rzp_amount id missing");
            }

            $getLeadDetails = $this->Tasks->getLeadDetails($lead_id);

            if ($getLeadDetails['status'] != 1) {
                throw new ErrorException("Lead details not found.");
            }

            $lead_details = $getLeadDetails['lead_details'];

            $repayment_data = array(
                'loan_no' => $lead_details['loan_no'],
                'total_due_amount' => $rzp_amount
            );

            $createRazorPayOrderID = $this->createRazorPayOrderID($lead_id, $repayment_data);

            if ($createRazorPayOrderID['Status'] != 1) {
                throw new ErrorException("Razorpay Order ID not generated.");
            }

            $apiStatusId = 1;
            $apiStatusData = $createRazorPayOrderID;
            $apiStatusMessage = NULL;
        } catch (ErrorException $err) {
            $apiStatusId = 2;
            $apiStatusData = NULL;
            $apiStatusMessage = $err->getMessage();
        } catch (RuntimeException $re) {
            $apiStatusId = 3;
            $apiStatusData = NULL;
            $apiStatusMessage = $re->getMessage();
        } catch (Exception $ex) {
            $apiStatusId = 4;
            $apiStatusData = NULL;
            $apiStatusMessage = $ex->getMessage();
        }

        return json_encode($this->response(['Status' => $apiStatusId, 'Message' => $apiStatusMessage, 'Data' => $apiStatusData], REST_Controller::HTTP_OK));
    }

    function createRazorPayOrderID($lead_id, $repayment_data) {
        $curl = curl_init();
        $return_data = [];

        // Calculate amount in paise and prepare payload for RazorPay API request
        $rp_amount = round(($repayment_data['total_due_amount'] * 100), 2);
        $loan_no = $repayment_data['loan_no'];

        $payload = json_encode([
            "amount" => $rp_amount,
            "currency" => "INR",
            "receipt" => $loan_no . "-" . date("YmdHis"),
            "notes" => [
                "orderid" => $loan_no,
                "lead_id" => $lead_id
            ],
            "partial_payment" => true,
            // "first_payment_min_amount" => 10000
        ]);

        $key_id = "rzp_live_gSedwg0IRWdr5a";
        $key_secret = "5gDjxpdq7DhwrM7MA4W8Eqg2";

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $key_id . ':' . $key_secret,
            CURLOPT_POSTFIELDS => $payload
        ]);

        // Execute cURL request and handle response
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            // Handle error
            $return_data['Status'] = 0;
            $return_data['error_message'] = curl_error($curl);
        } else {
            $decoded_response = json_decode($response, true);
            $return_data['Status'] = 1;
            $return_data['order_id'] = $decoded_response['id'] ?? null;
            $return_data['rzp_amount'] = $rp_amount;
        }

        // Close cURL session
        curl_close($curl);

        return $return_data;
    }

    function verifyRazorPayCheckPaymentStatus_post() {

        // Replace with your Razorpay Key and Secret
        $apiKey = 'rzp_live_gSedwg0IRWdr5a';
        $apiSecret = '5gDjxpdq7DhwrM7MA4W8Eqg2';

        header('Content-Type: application/json');

        // Get the JSON body from the request (Sent by Razorpay or frontend)
        $inputData = json_decode(file_get_contents("php://input"), true);

        try {
            // Check if all required fields are present
            if (
                !isset($inputData['razorpay_payment_id']) ||
                !isset($inputData['razorpay_order_id']) ||
                !isset($inputData['razorpay_signature'])
            ) {
                throw new Exception('Missing required parameters.');
            }

            $razorpayPaymentId = $inputData['razorpay_payment_id'];
            $razorpayOrderId = $inputData['razorpay_order_id'];
            $razorpaySignature = $inputData['razorpay_signature'];

            // Step 1: Create the string to hash by concatenating order_id and payment_id
            $generatedSignature = $razorpayOrderId . '|' . $razorpayPaymentId;

            // Step 2: Calculate HMAC SHA256 of the generatedSignature using Razorpay Secret as key
            $calculatedSignature = hash_hmac('sha256', $generatedSignature, $apiSecret);

            // Step 3: Compare the calculated signature with the one provided by Razorpay
            if (!hash_equals($calculatedSignature, $razorpaySignature)) {
                throw new Exception('Payment verification failed using signature.');
            }

            // If signature verification is successful, we can also check the payment status from Razorpay API
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.razorpay.com/v1/payments/' . $razorpayPaymentId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERPWD => $apiKey . ':' . $apiSecret,
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // Convert the response to JSON format
            $paymentData = json_decode($response, true);

            // Check if the payment is successful
            if ($paymentData['status'] == 'captured') {
                $this->insertRecoveryResponse($paymentData);
                // Return success response
                echo json_encode([
                    "Status" => 1,
                    "Message" => "Payment captured successfully.",
                    "Data" => $paymentData
                ]);
                exit;
            } else {
                // Payment status is not captured, return failure message
                echo json_encode([
                    "Status" => 0,
                    "Message" => "Payment not successful, status: " . $paymentData['status']
                ]);
                exit;
            }
        } catch (Exception $e) {
            // Log the error (optional, to debug in production environments)
            error_log($e->getMessage());

            // Return failure response with error message
            echo json_encode([
                "status" => "failure",
                "message" => $e->getMessage()
            ]);
            exit;
        }
    }

    function insertRecoveryResponse($data) {

        // Check if data is provided
        if (!empty($data)) {

            // Decode the JSON data
            $decoded_data = $data;

            if ($decoded_data['amount'] > 0 &&  isset($decoded_data['notes']) && isset($decoded_data['notes'])) {

                $actual_amount = ($decoded_data['amount'] - ($decoded_data['fee'])) / 100;
                // $actual_amount = number_format($actual_amount / 100, 2, '.', '');
                $lead_id = (int)$decoded_data['notes']['lead_id'];
                $bank_rrn = $decoded_data['acquirer_data']['rrn'];
                $bank_transaction_id = $decoded_data['acquirer_data']['bank_transaction_id'];

                if ($lead_id > 0) {

                    $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no FROM leads WHERE lead_active = 1 AND lead_id = ?";

                    $query_data = $this->db->query($query, array($lead_id))->row_array();

                    if ($query_data) {

                        $recoveryData = array(
                            'company_id' => $query_data['company_id'],
                            'lead_id' => $query_data['lead_id'],
                            'customer_id' => $query_data['customer_id'],
                            'loan_no' => $query_data['loan_no'],
                            'payment_mode' => 'Razorpay',
                            'payment_mode_id' => 13,
                            'received_amount' => $actual_amount,
                            'refrence_no' => (!empty($bank_rrn) ? $bank_rrn : $bank_transaction_id),
                            'date_of_recived' => date("Y-m-d"),
                            'repayment_type' => 19,
                            'remarks' => 'Payment Received through Razor Pay',
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                            'payment_verification' => 0,
                        );

                        // Insert the recovery data into the collection table
                        $this->db->insert('collection', $recoveryData);
                    } else {
                        // Log an error or handle case where lead_id does not exist
                        log_message('error', "Lead not found for lead_id: {$lead_id}");
                    }
                } else {
                    // Invalid lead_id
                    echo json_encode([
                        "status" => 0,
                        "message"  => "Invalid lead_id: {$lead_id}"
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => 0,
                    "message"  => "Json Parse failed."
                ]);
                exit;
            }
        } else {
            // Handle case where $data is empty
            echo json_encode([
                "status" => 0,
                "message"  => 'Empty data received'
            ]);
            exit;
        }
    }

    public function getLeadDetail_post() {


        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $lead_id = $post['lead_id'];

        // Ensure lead_id is provided
        if (!$lead_id) {
            return json_encode($this->response(['Status' => 0, 'Message' => "Lead ID is required."], REST_Controller::HTTP_BAD_REQUEST));
        }

        // Validate headers
        $headers = $this->input->request_headers();

        $token_verification = $this->checkAuthorization($headers);

        if ($token_verification['status'] != 1) {
            return json_encode($this->response(['Status' => 0, 'Message' => "Unauthorized access."], REST_Controller::HTTP_UNAUTHORIZED));
        }

        // Check if the request method is GET and headers are valid
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Sanitize lead_id
            $lead_id = intval($lead_id);

            // Prepare SQL query
            $select = "SELECT LD.lead_id, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.monthly_salary_amount,
                        LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(' ', LD.first_name,
                        C.middle_name, C.sur_name) as cust_full_name, LD.check_cibil_status, LD.email, C.alternate_email, C.gender,
                        LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, LD.lead_stp_flag,
                        DATE_FORMAT(LD.lead_final_disbursed_date, '%d-%m-%Y') AS lead_final_disbursed_date, LD.user_type, LD.pancard,
                        LD.loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode, CE.monthly_income,
                        LD.source, LD.utm_source, LD.utm_campaign, DATE_FORMAT(C.dob, '%d-%m-%Y') AS dob, LD.state_id, LD.city_id,
                        LD.lead_branch_id, ST.m_state_name, CT.m_city_name, LD.pincode, LD.status, LD.stage, LD.lead_status_id,
                        LD.schedule_time, LD.created_on, LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition,
                        LD.application_status, LD.lead_fi_residence_status_id,
                        LD.lead_fi_office_status_id, LD.scheduled_date, CAM.loan_recommended as sanctionedAmount,
                        CAM.repayment_amount, DATE_FORMAT(CAM.disbursal_date, '%d-%m-%Y') AS disbursal_date,
                        LD.lead_credit_assign_user_id, LD.lead_screener_assign_user_id, LD.lead_disbursal_assign_user_id,
                        DATE_FORMAT(LD.lead_screener_assign_datetime, '%d-%m-%Y %H:%i:%s') as screenedOn,
                        DATE_FORMAT(LD.lead_credit_approve_datetime, '%d-%m-%Y %H:%i:%s') as sanctionedOn, L.loan_status_id,
                        LD.lead_disbursal_approve_datetime,
                        L.loan_disbursement_trans_status_id, C.customer_religion_id, religion.religion_name, branch.m_branch_name,
                        C.customer_spouse_name, C.customer_spouse_occupation_id, C.customer_qualification_id,
                        C.current_residence_type, C.father_name, C.pancard_verified_status, C.customer_digital_ekyc_flag,
                        C.alternate_email_verified_status, C.customer_appointment_schedule, C.customer_appointment_remark,
                        LD.lead_rejected_assign_user_id, LD.lead_rejected_reason_id, LD.lead_rejected_assign_counter, C.customer_bre_run_flag,
                        CAM.city_category, MRT.m_marital_status_name as marital_status, MOC.m_occupation_name as occupation,
                        MQ.m_qualification_name as qualification
                        FROM leads LD
                        LEFT JOIN lead_customer C ON C.customer_lead_id = LD.lead_id
                        LEFT JOIN customer_employment CE ON CE.lead_id = LD.lead_id
                        LEFT JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id
                        LEFT JOIN loan L ON L.lead_id = LD.lead_id
                        LEFT JOIN master_state ST ON ST.m_state_id = LD.state_id
                        LEFT JOIN master_city CT ON CT.m_city_id = LD.city_id
                        LEFT JOIN master_data_source DS ON DS.data_source_id = LD.lead_data_source_id
                        LEFT JOIN master_religion religion ON religion.religion_id = C.customer_religion_id
                        LEFT JOIN master_qualification MQ ON MQ.m_qualification_id = C.customer_qualification_id
                        LEFT JOIN master_marital_status MRT ON MRT.m_marital_status_id = C.customer_marital_status_id
                        LEFT JOIN master_occupation MOC ON MOC.m_occupation_id = C.customer_spouse_occupation_id
                        LEFT JOIN master_branch branch ON branch.m_branch_id = LD.lead_branch_id
                        WHERE LD.lead_active = 1
                        AND LD.lead_deleted = 0
                        AND LD.lead_id = $lead_id";

            // Execute query
            $result = $this->db->query($select)->result_array();

            $this->db->select('received_amount,date_of_recived,remarks');
            $this->db->from('collection');
            $this->db->where('lead_id', $lead_id);
            $this->db->where_in('payment_verification', [1]);
            $this->db->order_by('lead_id', 'DESC');
            $query1 = $this->db->get();
            $result1 = $query1->result_array();


            if (!empty($result)) {
                return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $result, 'collection' => $result1], REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => "Unauthorized access."], REST_Controller::HTTP_UNAUTHORIZED));
        }
    }
}
