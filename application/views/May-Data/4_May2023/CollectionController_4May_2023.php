<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class CollectionController extends REST_Controller {

    private $app_version = 1;
    public $api_status = 0;
    public $result_array = array('status' => 0);

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('CollectionApp/Collection_Model', 'Collex');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
    }

    public function request($input_data) {

        if (!empty($input_data)) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        return $post;
    }

    public function collexAppVersionCheck_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("version", "Version", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                $this->result_array['message'] = strip_tags(validation_errors());
            } else {
                $current_version = $this->app_version;
                $version = htmlspecialchars($post['version']);

                if ($version == $current_version) {
                    $this->result_array['status'] = 1;
                    $this->result_array['success'] = "App version verified.";
                    $this->result_array['version'] = round($current_version, 2);
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $this->result_array['message'] = "Please update the new version.";
                    $this->result_array['version'] = round($current_version, 2);
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            }
        } else {
            $this->result_array['message'] = "Request Method Post Failed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexAuth_post() {
        $insert_otp_trans_array = array();
        $conditions = array();

        $post = $this->request(file_get_contents("php://input"));

        $headers = $this->input->request_headers();
        $token = $this->_token();
//        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("password", "password", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                $this->result_array['message'] = strip_tags(validation_errors());
                $this->api_status = REST_Controller::HTTP_OK;
            } else {
                $mobile = htmlspecialchars($post['mobile']);
                $password = md5(htmlspecialchars($post['password']));

                $conditions['U.mobile'] = $mobile;
                $conditions['U.password'] = $password;
                //print_r($conditions); die;
                $conditions['U.user_status_id'] = 1;
                $conditions['U.user_active'] = 1;
                $conditions['U.user_deleted'] = 0;
                $conditions['UR.user_role_type_id'] = 13;

                $this->db->select('U.user_id, U.name, U.mobile, UR.user_role_id');
                $this->db->from('users U');
                $this->db->join('user_roles UR', 'UR.user_role_user_id = U.user_id');
                $this->db->where($conditions);
                $users_details = $this->db->get();

                if (!empty($users_details->num_rows())) {
                    $users = $users_details->row_array();

                    $otp = 1122;

                    if (ENVIRONMENT == "development") {

                        $otp = rand(1000, 9999);

                        if ($mobile == 9560807913) {
                            $otp = 1989;
                        } else if ($mobile == 9369815048) {
                            $otp = 1122;
                        } else if ($mobile == 8936962573) {
                            $otp = 1212;
                        }

                        $sms_input_data = array();
                        $sms_input_data['mobile'] = $mobile;
                        $sms_input_data['name'] = $users['name'];
                        $sms_input_data['otp'] = $otp;

                        require_once (COMPONENT_PATH . 'CommonComponent.php');
                        $CommonComponent = new CommonComponent();

                        $res = $CommonComponent->payday_sms_api(1, "", $sms_input_data);
                    }

                    $user_id = $users['user_id'];
                    $name = $users['name'];
                    $mobile = $users['mobile'];
                    $user_role_id = $users['user_role_id'];

                    $insert_otp_trans_array['lot_user_id'] = $user_id;
                    $insert_otp_trans_array['lot_mobile_no'] = $mobile;
                    $insert_otp_trans_array['lot_mobile_otp'] = $otp;
                    $insert_otp_trans_array['lot_mobile_otp_type'] = 4; // Collection App
                    $insert_otp_trans_array['lot_otp_trigger_time'] = date('Y-m-d H:i:s');
                    $insert_otp_trans_array['lot_otp_verify_flag'] = 0;
                    $insert_otp_trans_array['lot_otp_valid_time'] = 10;
                    $insert_otp_trans_array['lot_active'] = 1;
                    $insert_otp_trans_array['lot_deleted'] = 0;

                    $response = $this->Collex->globel_insert('leads_otp_trans', $insert_otp_trans_array);

                    if (!empty($response)) {
                        $this->result_array['status'] = 1;
                        $this->result_array['message'] = "OTP send to your registered mobile.";
                        $this->result_array['data']['user_id'] = $this->encrypt->encode($user_id);
                        $this->api_status = REST_Controller::HTTP_OK;
                        $this->Collex->insertUserActivity($user_id, $user_role_id, 1);
                    }
                } else {
                    $this->result_array['message'] = "Invalid user access.";
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            }
        } else {
            $this->result_array['message'] = "Request method post failed. try again";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexAuthOtpVerification_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("user_id", "user_id", "required|trim");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                $this->result_array['message'] = strip_tags(validation_errors());
                $this->api_status = REST_Controller::HTTP_OK;
            } else {
                $user_id = $this->encrypt->decode($post['user_id']);
                $otp = $post['otp'];

                $selquery = "SELECT lot_id,lot_otp_valid_time,lot_user_id,lot_otp_trigger_time FROM leads_otp_trans where lot_user_id='$user_id' and lot_mobile_otp='$otp'  order by lot_id desc limit 1";

                $query = $this->db->query($selquery);
                

                if (!empty($query->num_rows())) {
                    $result1 = $query->row();
                    $login_date_time = date('Y-m-d H:i:s');
                    $user_id = $result1->lot_user_id;
                    $mot_otp_valid_time = $result1->lot_otp_valid_time;
                    $mot_otp_trigger_time = $result1->lot_otp_trigger_time;
                    $update_id = $result1->lot_id;

                    $dateTimeObject1 = date_create($mot_otp_trigger_time);
                    $dateTimeObject2 = date_create(date('Y-m-d H:i:s'));
                    $interval = date_diff($dateTimeObject1, $dateTimeObject2);
                    $interval->format('%R%a days');
                    $min = $interval->days * 24 * 60;
                    $min += $interval->h * 60;
                    $min += $interval->i;

                    $ctime = $min;
                    $validtime = $mot_otp_valid_time;

                    if ($validtime >= $ctime) {

                        $token = md5($user_id . date('Y-m-d H:i:s') . rand(1111, 9999));
                        $exptime = $new_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                        $insert_mobileapp_login_trans = array();
                        $insert_mobileapp_login_trans['mlt_user_id'] = $user_id;
                        $insert_mobileapp_login_trans['mlt_token'] = $token;
                        $insert_mobileapp_login_trans['mlt_token_valid_time'] = 30;
                        $insert_mobileapp_login_trans['mlt_valid_datetime'] = $exptime;
                        $insert_mobileapp_login_trans['mlt_request_ip'] = $_SERVER['REMOTE_ADDR'];
                        $insert_mobileapp_login_trans['mlt_browser_history'] = $_SERVER['HTTP_USER_AGENT'];
                        $insert_mobileapp_login_trans['mlt_app_version'] = 1;
                        $insert_mobileapp_login_trans['mlt_active'] = 1;
                        $insert_mobileapp_login_trans['mlt_created_on'] = date('Y-m-d H:i:s');
                        $insert_mobileapp_login_trans['mlt_login_time'] = $login_date_time;

                        $res = $this->Collex->globel_insert('mobileapp_login_trans', $insert_mobileapp_login_trans);

                        $where['lot_id'] = $result1->lot_id;
                        $dataleads_otp_trans['lot_otp_verify_flag'] = 1;

                        $response = $this->Collex->globel_update('leads_otp_trans', $dataleads_otp_trans, $where);
                        $this->Collex->globel_update('users', ['user_last_login_datetime' => $login_date_time], ['user_id' => $user_id]);

                        if (!empty($response)) {
                            $this->result_array['status'] = 1;
                            $this->result_array['success'] = "OTP verified Successfully.";
                            $this->result_array['data']['user_id'] = $this->encrypt->encode($user_id);
                            $this->result_array['data']['Auth_Token'] = $token;

                            $running_visit = $this->Collex->get_user_running_visit($user_id);
                            $this->result_array['data']['running_visit'] = null;
                            if (!empty($running_visit['status'])) {
                                $this->result_array['data']['running_visit'] = $running_visit['data']['running_visit'];
                            }
                            $this->api_status = REST_Controller::HTTP_OK;
                        } else {
                            $this->result_array['message'] = "Some error occured. Please try again.[DB-COV]";
                            $this->api_status = REST_Controller::HTTP_OK;
                        }
                    } else {
                        $this->result_array['message'] = "OTP Expired.";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                } else {
                    $this->result_array['message'] = "Invalid OTP. Try Again";
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            }
        } else {
            $this->result_array['message'] = "Request Method Post Failed";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetUserProfile_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("user_id", "user_id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $user_id = $this->encrypt->decode($post['user_id']);
                    $where['U.user_id'] = $user_id;
                    $return_data = $this->Collex->get_agent_profile($where);

                    if (!empty($return_data['status'])) {
                        $this->result_array['status'] = 1;
                        $this->result_array['data'] = $return_data['data'];
                        $this->api_status = REST_Controller::HTTP_OK;
                    } else {
                        $this->result_array['message'] = "No Record Found.";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetVisitAndManagerDetails_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "lead_id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    $lead_details = $this->Collex->get_lead_details($lead_id);

                    if (!empty($lead_details->num_rows())) {

                        $return_data = $this->Collex->get_visit_and_manager_details($lead_id);

                        if (!empty($return_data)) {
                            $this->result_array['status'] = 1;
                            $this->result_array['data']['visit_and_manager_details'] = $return_data;
                            $this->api_status = REST_Controller::HTTP_OK;
                        } else {
                            $this->result_array['message'] = "No Record Found.";
                            $this->api_status = REST_Controller::HTTP_OK;
                        }
                    } else {
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetRepaymentDetails_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
           
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "lead_id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    //$lead_id = $post['lead_id'];
                    $lead_details = $this->Collex->get_lead_details($lead_id);

                    if (!empty($lead_details->num_rows())) {
                        $return_data = $this->Collex->get_repayment_details($lead_id);

                        if (!empty($return_data)) {
                            $this->result_array['status'] = 1;
                            $this->result_array['data']['repayment_details'] = $return_data;
                            $this->api_status = REST_Controller::HTTP_OK;
                        } else {
                            $this->result_array['message'] = "No Record Found.";
                            $this->api_status = REST_Controller::HTTP_OK;
                        }
                    } else {
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetTotalCollection_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);
    
            if (!empty($token_verification['status'])) {
               
                if ($post['page'] == 'TotalPendingCollectionList') {
                    $where['LCV.col_visit_allocated_to'] = $this->encrypt->decode($post['user_id']);

                    $config["per_page"] = 10;
                    $page = !empty($post['per_page']) ? intval($post['per_page']) : 0; // 10
//                        $return_data = $this->Collex->get_total_collection_lists($where, $config["per_page"], $page);
                    $return_data = $this->Collex->get_total_collection_lists($where);
                    if (!empty($return_data['status'])) {
                        $this->result_array['status'] = 1;
                        $this->result_array['data'] = $return_data['data'];
                        $this->api_status = REST_Controller::HTTP_OK;
                    } else {
                        $this->result_array['message'] = "No Record Found.";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                } else {
                    $this->result_array['message'] = "Invalid page request - <b>" . $post['page'] . "</b>";
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexLoanDetailPayment_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array("status" => 0, "message"=>"");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->result_array['message'] = "No Record Found.";
                $this->api_status = REST_Controller::HTTP_OK;

                $return_payment_data = $this->Collex->get_LoanDetail_payment_mode($post['loan_no']);

                if (!empty($return_payment_data['status'])) {
                    unset($this->result_array['error']);
                    $return_data['status'] = 1;
                    $return_data['message'] = "Record Found";
                    $return_data['data'] = $return_payment_data['data'];
                }
            } else {
                $return_data['status'] = 440;
                $return_data['message'] = "Session Expired.";
            }
        } else {
            $return_data['message'] = "Request method not allowed.";

        }
        return json_encode($this->response($return_data, REST_Controller::HTTP_OK));
        }
    public function test_api_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "lead_id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    $lead_details = $this->Collex->get_lead_details($lead_id);

                    if (!empty($lead_details->num_rows())) {
                        $return_data = $this->Collex->get_repayment_details($lead_id);

                        if (!empty($return_data)) {
                            $this->result_array['status'] = 1;
                            $this->result_array['data']['repayment_details'] = $return_data;
                            $this->api_status = REST_Controller::HTTP_OK;
                        } else {
                            $this->result_array['message'] = "No Record Found.";
                            $this->api_status = REST_Controller::HTTP_OK;
                        }
                    } else {
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetLoanDetails_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");

                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    $lead_details = $this->Collex->get_lead_details($lead_id);

                    if (!empty($lead_details->num_rows())) {

                        $this->result_array['message'] = "No Record Found.";
                        $this->api_status = REST_Controller::HTTP_OK;

                        if ($post['page'] == 'GetLoanDetail') {
                            $return_data = $this->Collex->get_loan_details($lead_id);

                            if (!empty($return_data['status'])) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['data'] = $return_data['data'];
                            }
                        } // end else if condition
                        else if ($post['page'] == 'GetResidenceDetail') {
                            $return_data = $this->Collex->get_customer_residence_details($lead_id);

                            if (!empty($return_data['status'])) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['data'] = $return_data['data'];
                            }
                        } // end else if condition
                        else if ($post['page'] == 'GetRefrenceDetail') {

                            $return_data = $this->Collex->get_customer_reference_details($lead_id);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['data']['reference_list'] = $return_data;
                            }
                        } // end else if condition
                        else if ($post['page'] == 'GetOfficeDetail') {

                            $return_data = $this->Collex->get_customer_office_details($lead_id);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['data'] = $return_data['data'];
                            }
                        } // end else if condition
                        else if ($post['page'] == 'GetLastCollectionFollowup') {

                            $return_data = $this->Collex->get_collection_followup_details($lead_id);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['data'] = $return_data['data'];
                            }
                        } // end else if condition
                    } else {
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexFeStartEndVisit_post() {
        $post = $this->request(file_get_contents("php://input"));
//            $update_visit = array();
        $conditions = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");
                $this->form_validation->set_rules("visit_id", "Visit Id", "required|trim");
                $this->form_validation->set_rules("visit_trip_status_id", "Visit Trip Status Id", "required|trim");
                $this->form_validation->set_rules("visit_trip_latitude", "Visit Trip Start Latitude", "required|trim");
                $this->form_validation->set_rules("visit_trip_longitude", "Visit Trip Start Longitude", "required|trim");
                $this->form_validation->set_rules("user_device_id", "User Device Id", "required|trim");

                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    $visit_id = $this->encrypt->decode($post['visit_id']);

                    $conditions['col_lead_id'] = $lead_id;
                    $conditions['col_visit_id'] = $visit_id;

                    $visit_details = $this->Collex->get_visit_details($conditions);

                    if (!empty($visit_details->num_rows())) {
                        $visit_data = $visit_details->row_array();
                        $cfe_visit_status_id = $visit_data['col_fe_visit_trip_status_id']; // start, stop, cancel, complete, end
                        $loan_visit_status_id = $visit_data['col_visit_field_status_id']; // Assign, cancel, hold, complete

                        $this->result_array['message'] = "Failed to update visit.";
                        $this->api_status = REST_Controller::HTTP_OK;

                        if (in_array($post['visit_trip_status_id'], [1]) && in_array($loan_visit_status_id, [2])) { // Start Visit
                            $update_visit = array();

                            $update_visit['col_fe_device_id'] = $post['user_device_id'];
                            $update_visit['col_fe_visit_trip_status_id'] = 1; // 1=>Start Visit, 2=>Cancel Visit, 3=>Stop Visit, 1=>End Visit
                            $update_visit['col_fe_visit_trip_start_latitude'] = htmlspecialchars($post['visit_trip_latitude']);
                            $update_visit['col_fe_visit_trip_start_longitude'] = htmlspecialchars($post['visit_trip_longitude']);
                            $update_visit['col_fe_visit_trip_start_datetime'] = date('Y-m-d H:i:s');
                            $update_visit['col_fe_visit_end_latitude'] = NULL;
                            $update_visit['col_fe_visit_end_longitude'] = NULL;
                            $update_visit['col_fe_visit_end_datetime'] = NULL;

                            $return_data = $this->Collex->globel_update('loan_collection_visit', $update_visit, $conditions);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['visit_status'] = 1;
                                $this->result_array['message'] = "Visit Start Successfully.";
                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } else if (in_array($post['visit_trip_status_id'], [2]) && in_array($loan_visit_status_id, [2])) { // 2 => Cancel Visit, 4=>End Visit
                            $update_visit = array();
                            $update_visit['col_fe_visit_trip_status_id'] = 0;
                            $update_visit['col_fe_visit_trip_start_latitude'] = NULL;
                            $update_visit['col_fe_visit_trip_start_longitude'] = NULL;
                            $update_visit['col_fe_visit_trip_start_datetime'] = NULL;
                            $update_visit['col_fe_visit_trip_stop_latitude'] = NULL;
                            $update_visit['col_fe_visit_trip_stop_longitude'] = NULL;
                            $update_visit['col_fe_visit_trip_stop_datetime'] = NULL;
                            $update_visit['col_fe_visit_end_latitude'] = htmlspecialchars($post['visit_trip_latitude']);
                            $update_visit['col_fe_visit_end_longitude'] = htmlspecialchars($post['visit_trip_longitude']);
                            $update_visit['col_fe_visit_end_datetime'] = date('Y-m-d H:i:s');

                            $return_data = $this->Collex->globel_update('loan_collection_visit', $update_visit, $conditions);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['visit_status'] = 2;
                                $this->result_array['message'] = "Visit Cancel Successfully.";
                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } else if (in_array($post['visit_trip_status_id'], [3]) && in_array($loan_visit_status_id, [2])) { // Stop Visit
                            $update_visit = array();
                            $update_visit['col_fe_visit_trip_status_id'] = 3;
                            $update_visit['col_fe_visit_trip_stop_latitude'] = htmlspecialchars($post['visit_trip_latitude']);
                            $update_visit['col_fe_visit_trip_stop_longitude'] = htmlspecialchars($post['visit_trip_longitude']);
                            $update_visit['col_fe_visit_trip_stop_datetime'] = date('Y-m-d H:i:s');

                            $return_data = $this->Collex->globel_update('loan_collection_visit', $update_visit, $conditions);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['visit_status'] = 3;
                                $this->result_array['message'] = "Visit Stop Successfully.";
                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } else if (in_array($post['visit_trip_status_id'], [4]) && in_array($loan_visit_status_id, [2])) { // 4=>End Visit
                            $update_visit = array();
                            $update_visit['col_fe_visit_trip_status_id'] = 4;
                            $update_visit['col_visit_field_status_id'] = 5;
                            $update_visit['col_fe_visit_end_latitude'] = htmlspecialchars($post['visit_trip_latitude']);
                            $update_visit['col_fe_visit_end_longitude'] = htmlspecialchars($post['visit_trip_longitude']);
                            $update_visit['col_fe_visit_end_datetime'] = date('Y-m-d H:i:s');

                            $return_data = $this->Collex->globel_update('loan_collection_visit', $update_visit, $conditions);

                            if (!empty($return_data)) {
                                unset($this->result_array['error']);
                                $this->result_array['status'] = 1;
                                $this->result_array['visit_status'] = 4;
                                $this->result_array['message'] = "Visit Completed Successfully.";

                                $email_sent_status = $this->Collex->send_email_for_visit($visit_id);

                                if (empty($email_sent_status['status'])) {
                                    $this->result_array['message'] .= " , " . $email_sent_status['error'];
                                }

                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } else {
                            $this->result_array['status'] = 1;
                            $this->result_array['visit_status'] = 0;
                            $this->result_array['message'] = "Visit Canceled. You can't process for the next step.";
                            $this->api_status = REST_Controller::HTTP_OK;
                        }
                    } else {
                        $this->result_array['status'] = 1;
                        $this->result_array['visit_status'] = 0;
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetListPaymentMode_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->result_array['message'] = "No Record Found.";
                $this->api_status = REST_Controller::HTTP_OK;

                $return_data = $this->Collex->get_master_payment_mode($post['mpm_id']);

                if (!empty($return_data['status'])) {
                    unset($this->result_array['error']);
                    $this->result_array['status'] = 1;
                    $this->result_array['data'] = $return_data['data'];
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexGetListMasterStatus_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->result_array['message'] = "No Record Found.";
                $this->api_status = REST_Controller::HTTP_OK;

                $return_data = $this->Collex->get_master_status($post['repay_status_id']);

                if (!empty($return_data['status'])) {
                    unset($this->result_array['error']);
                    $this->result_array['status'] = 1;
                    $this->result_array['data'] = $return_data['data'];
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexAuthLogout_post() {
        $post = $this->request(file_get_contents("php://input"));
        $conditions = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("user_id", "User Id", "required|trim");

                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $user_id = $this->encrypt->decode($post['user_id']);

                    $conditions['U.user_id'] = $user_id;

                    $this->db->select('U.*, UR.user_role_id');
                    $this->db->from('users U');
                    $this->db->join('user_roles UR', 'UR.user_role_user_id = U.user_id');
                    $this->db->where($conditions);

                    $query = $this->db->get();

                    if (!empty($query->num_rows())) {
                        $users_details = $query->row_array();
                        $user_role_id = $users_details['user_role_id'];

                        $mobileapp_login = $this->db->select('mlt_id')->where('mlt_user_id', $user_id)->from('mobileapp_login_trans')->order_by('mlt_id', 'desc')->limit(1)->get()->row_array();

                        $mlt_id = $mobileapp_login['mlt_id'];

                        $update_mobileapp_login_trans['mlt_updated_on'] = date('Y-m-d H:i:s');
                        $update_mobileapp_login_trans['mlt_active'] = 0;
                        $update_mobileapp_login_trans['mlt_deleted'] = 1;

                        $this->db->where('mlt_id', $mlt_id)->update('mobileapp_login_trans', $update_mobileapp_login_trans);
                        $this->Collex->insertUserActivity($user_id, $user_role_id, 3);

                        $this->result_array['status'] = 1;
                        $this->result_array['message'] = "User logout successfully.";
                        $this->api_status = REST_Controller::HTTP_OK;
                    } else {
                        $this->result_array['message'] = "Failed to Logout. Please try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexReturnFromVisit_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array();
        $docs_array = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);

                $this->form_validation->set_rules("user_id", "User Id", "required|trim");
                $this->form_validation->set_rules("return_visit_type", "Return Visit Type", "required|trim");
                $this->form_validation->set_rules("remarks", "Remarks", "required|trim");
                $this->form_validation->set_rules("total_visit_distance", "Total Visit Distance", "required|trim");
                $this->form_validation->set_rules("return_visit_latitude", "Return Visit Latitude", "required|trim");
                $this->form_validation->set_rules("return_visit_longitude", "Return Visit Longitude", "required|trim");
                $this->form_validation->set_rules("upload_selfie", "Upload Selfie", "required|trim");

                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $user_id = $this->encrypt->decode($post['user_id']);

                    $cfe_last_visit = $this->Collex->get_cfe_last_visits($user_id);

                    if (!empty($cfe_last_visit['status'])) {
                        $cfe_last_visit_details = $cfe_last_visit['data']['running_visit'];

                        if (!empty($cfe_last_visit_details['col_fe_rtoh_return_type'])) {
                            $this->result_array['message'] = "You have already submitted the last return from visit.";
                            $this->api_status = REST_Controller::HTTP_OK;
                        } else {

                            $col_visit_id = $cfe_last_visit_details['col_visit_id'];
                            $lead_id = $cfe_last_visit_details['lead_id'];

                            $remarks = htmlspecialchars(addslashes($post['remarks']));
                            $return_visit_type = htmlspecialchars($post['return_visit_type']);
                            $total_visit_distance = htmlspecialchars($post['total_visit_distance']);
                            $return_visit_latitude = htmlspecialchars($post['return_visit_latitude']);
                            $return_visit_longitude = htmlspecialchars($post['return_visit_longitude']);

                            $lcf_fe_upload_selfie_return_from_visit = htmlspecialchars($post['upload_selfie']); // 14

                            $update_loan_collection_followup['col_fe_rtoh_return_type'] = htmlspecialchars($return_visit_type);
                            $update_loan_collection_followup['col_fe_rtoh_remarks'] = htmlspecialchars($remarks);
                            $update_loan_collection_followup['col_fe_rtoh_return_datetime'] = date("Y-m-d H:i:s");
                            $update_loan_collection_followup['col_fe_rtoh_total_distance_covered'] = htmlspecialchars($total_visit_distance);
                            $update_loan_collection_followup['col_fe_rtoh_end_latitude'] = htmlspecialchars($return_visit_latitude);
                            $update_loan_collection_followup['col_fe_rtoh_end_longitude'] = htmlspecialchars($return_visit_longitude);

                            $conditions['col_visit_id'] = $col_visit_id;
                            $conditions['col_lead_id'] = $lead_id;
                            $conditions['col_visit_active'] = 1;
                            $conditions['col_visit_deleted'] = 0;

                            $this->Collex->globel_update("loan_collection_visit", $update_loan_collection_followup, $conditions);

                            $this->result_array['status'] = 1;
                            $this->result_array['message'] = "Returned from visit successfully.";

                            if (!empty($lcf_fe_upload_selfie_return_from_visit)) {
                                $docs = $this->Collex->upload_base64encode($col_visit_id, ["lcf_fe_upload_selfie_return_from_visit" => $lcf_fe_upload_selfie_return_from_visit]);

                                if (!empty($docs['status'])) {
                                    $docs_array['col_fe_rtoh_upload_selfie'] = $docs['data']['col_fe_rtoh_upload_selfie'];
                                } else {
                                    $this->result_array['message'] = "failed to upload Selfie. try again";
                                }
                            }
                        }
                    } else {
                        $this->result_array['message'] = "You have not completed any visit today. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function collexUpdateFollowupAndCollection_post() {
        $post = $this->request(file_get_contents("php://input"));
        $return_data = array();
        $docs_array = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->input->request_headers();
            $token_verification = $this->Collex->check_validationToken($headers['Authtoken']);

            if (!empty($token_verification['status'])) {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("collection_followup_id", "Visit Status Id", "required|trim");
                $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");
                $this->form_validation->set_rules("visit_id", "Visit", "required|trim");
                $this->form_validation->set_rules("user_id", "User Id", "required|trim");
                $this->form_validation->set_rules("remarks", "Remarks", "required|trim");

                if (in_array($post['collection_followup_id'], [1])) { // Followup
                    $this->form_validation->set_rules("total_visit_distance", "Total Visit Distance", "required|trim");
                    $this->form_validation->set_rules("next_collection_followup_date", "Next Collection Followup Date", "trim");
                    $this->form_validation->set_rules("upload_selfie", "Upload Selfie", "required|trim");
                    $this->form_validation->set_rules("upload_location", "Upload Location", "required");
                }

                if (in_array($post['collection_followup_id'], [2])) { // Update Payment
                    $this->form_validation->set_rules("collection_id", "Collection Id", "trim");
                    $this->form_validation->set_rules("repayment_amount", "Upload Payment Screen Short", "required|trim");
                    $this->form_validation->set_rules("repayment_mode", "Repayment Mode", "required|trim");
                    $this->form_validation->set_rules("upload_payment", "Upload Payment Screen Short", "required|trim");
                }

                if ($this->form_validation->run() == FALSE) {
                    $this->result_array['message'] = strip_tags(validation_errors());
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $lead_id = $this->encrypt->decode($post['lead_id']);
                    $lead_details = $this->Collex->get_lead_details($lead_id);

                    if (!empty($lead_details->num_rows())) {

                        $followup_inserted_id = 0;
                        $collection_inserted_id = $this->encrypt->decode($post['collection_id']);
                        $visit_id = $this->encrypt->decode($post['visit_id']);
                        $user_id = $this->encrypt->decode($post['user_id']);
                        $remarks = htmlspecialchars(addslashes($post['remarks']));

                        $condition_visit['col_visit_id'] = htmlspecialchars($visit_id);
                        $total_visit_distance = htmlspecialchars($post['total_visit_distance']);
                        $total_received_amount = htmlspecialchars(trim($post['repayment_amount']));
                        $next_collection_followup_date = htmlspecialchars(strtotime($post['next_collection_followup_date']));
                        $next_collection_followup_date = ($post['next_collection_followup_date'] ? date('Y-m-d H:i:s', strtotime($post['next_collection_followup_date'])) : NULL);

                        if (in_array($post['collection_followup_id'], [1])) { // UpdateNextCollectionFollowup
                            $today = strtotime(date("Y-m-d H:i:s"));

                            $lcf_fe_upload_selfie = htmlspecialchars($post['upload_selfie']); // 14
                            $lcf_fe_upload_location = htmlspecialchars($post['upload_location']); // 14

                            $insert_loan_collection_followup['lcf_lead_id'] = htmlspecialchars($lead_id);
                            $insert_loan_collection_followup['lcf_next_schedule_datetime'] = htmlspecialchars($next_collection_followup_date);
                            $insert_loan_collection_followup['lcf_user_id'] = htmlspecialchars($user_id);
                            $insert_loan_collection_followup['lcf_remarks'] = htmlspecialchars($remarks);
                            $insert_loan_collection_followup['lcf_created_on'] = date("Y-m-d H:i:s");
                            $insert_loan_collection_followup['total_distance_covered'] = htmlspecialchars($total_visit_distance);
                            $insert_loan_collection_followup['lcf_active'] = 1;
                            $insert_loan_collection_followup['lcf_deleted'] = 0;

                            $followup_inserted_id = $this->Collex->globel_insert("loan_collection_followup", $insert_loan_collection_followup);

                            if (!empty($followup_inserted_id)) {
                                $this->result_array['status'] = 1;
                                $this->result_array['message'] = "Followup save successfully.";

                                if (!empty($lcf_fe_upload_selfie)) {
                                    $docs = $this->Collex->upload_base64encode($followup_inserted_id, ["lcf_fe_upload_selfie" => $lcf_fe_upload_selfie]);

                                    if (!empty($docs['status'])) {
                                        $docs_array['lcf_fe_upload_selfie'] = $docs['data']['lcf_fe_upload_selfie'];
                                        $this->result_array['message'] .= "Selfie uploaded successfully.";
                                    } else {
                                        $this->result_array['message'] = "failed to upload Selfie. try again";
                                    }
                                }

                                if (!empty($lcf_fe_upload_location)) {
                                    $docs = $this->Collex->upload_base64encode($followup_inserted_id, ["lcf_fe_upload_location" => $lcf_fe_upload_location]);

                                    if (!empty($docs['status'])) {
                                        $docs_array['lcf_fe_upload_location'] = $docs['data']['lcf_fe_upload_location'];
                                        $this->result_array['message'] .= "Location uploaded successfully.";
                                    } else {
                                        $this->result_array['message'] = "failed to upload Location. try again";
                                    }
                                }
                            } else {
                                $this->result_array['message'] = "Failed to save followup. try again";
                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } else if (in_array($post['collection_followup_id'], [2])) { // Update Payment
                            $leads = $lead_details->row_array();

                            $company_id = $leads['company_id'];
                            $product_id = $leads['product_id'];
                            $customer_id = $leads['customer_id'];
                            $loan_no = $leads['loan_no'];
                            $upload_payment = htmlspecialchars($post['upload_payment']);

                            $payment_mode = $this->Collex->get_master_payment_mode($post['repayment_mode']);
                            $repayment = $this->Collex->get_repayment_details($lead_id);
                            $total_due_amount = $repayment['total_due_amount'];

                            if (empty($total_received_amount)) {
                                $this->result_array['message'] = "The entered amount cannot be nill.";
                                $this->api_status = REST_Controller::HTTP_OK;
                            } else if (!empty($total_due_amount) && ($total_due_amount >= $total_received_amount)) {
                                $repayment_type = (($total_received_amount == $total_due_amount) ? 16 : 19);

                                $insert_collection['lead_id'] = htmlspecialchars($lead_id);
                                $insert_collection['company_id'] = htmlspecialchars($company_id);
                                $insert_collection['product_id'] = htmlspecialchars($product_id);
                                $insert_collection['customer_id'] = htmlspecialchars($customer_id);
                                $insert_collection['loan_no'] = htmlspecialchars($loan_no);
                                $insert_collection['received_amount'] = htmlspecialchars($total_received_amount);
                                $insert_collection['payment_mode'] = $payment_mode['data']['payment_mode_list'][0]['payment_mode_name'];
                                $insert_collection['payment_mode_id'] = $payment_mode['data']['payment_mode_list'][0]['payment_mode_id'];
                                $insert_collection['repayment_type'] = $repayment_type;
                                $insert_collection['remarks'] = htmlspecialchars($remarks);
                                $insert_collection['collection_executive_user_id'] = trim($user_id);
                                $insert_collection['collection_executive_payment_created_on'] = date("Y-m-d H:i:s");
                                $insert_collection['payment_verification'] = 0;
                                $insert_collection['collection_active'] = 1;
                                $insert_collection['collection_deleted'] = 0;

                                if (empty($collection_inserted_id)) {
                                    $collection_inserted_id = $this->Collex->globel_insert("collection", $insert_collection);
                                    $this->result_array['status'] = 1;
                                    $this->result_array['message'] = "Payment save successfully.";
                                } else if (!empty($collection_inserted_id)) {
                                    $condition_collection['id'] = $collection_inserted_id;
                                    $update = $this->Collex->globel_update("collection", $insert_collection, $condition_collection);
                                    $this->result_array['status'] = 1;
                                    $this->result_array['message'] = "Payment update successfully.";
                                }

                                $this->result_array['data']['collection_id'] = $this->encrypt->encode($collection_inserted_id);

                                if (!empty($upload_payment)) {
                                    $docs = $this->Collex->upload_base64encode($collection_inserted_id, ["docs" => $upload_payment]);

                                    if (!empty($docs['status'])) {
                                        $docs_array['payment_screenshot'] = $docs['data']['docs'];
                                        $this->result_array['message'] .= "Screenshot upload successfully.";
                                    } else {
                                        $this->result_array['message'] = "Failed to upload Screenshot. try again";
                                    }
                                }
                            } else {
                                $this->result_array['message'] = "The entered amount cannot be greater than the total due amount. - Rs. " . $total_due_amount;
                                $this->api_status = REST_Controller::HTTP_OK;
                            }
                        } // end else if condition

                        if (!empty($visit_id)) {
                            if (!empty($docs_array['lcf_fe_upload_selfie'])) {
                                $update_visit['col_fe_visit_upload_selfie'] = $docs_array['lcf_fe_upload_selfie'];
                            }

                            if (!empty($docs_array['lcf_fe_upload_location'])) {
                                $update_visit['col_fe_visit_upload_location'] = $docs_array['lcf_fe_upload_location'];
                            }

                            if (!empty($docs_array['payment_screenshot'])) {
                                $update_visit['col_fe_visit_payment_screenshot'] = $docs_array['payment_screenshot'];
                            }

                            if (!empty($total_visit_distance)) {
                                $update_visit['col_fe_visit_total_distance_covered'] = $total_visit_distance;
                            }

                            if (!empty($total_received_amount)) {
                                $update_visit['col_fe_visit_total_amount_received'] = $total_received_amount;
                            }

                            if (!empty($next_collection_followup_date)) {
                                $update_visit['col_visit_field_schedule_datetime'] = $next_collection_followup_date;
                            }

                            if (!empty($remarks)) {
                                $update_visit['col_visit_field_remarks'] = htmlspecialchars($remarks);
                                $update_visit['col_visit_field_datetime'] = date("Y-m-d H:i:s");
                            }


                            $update = $this->Collex->globel_update("loan_collection_visit", $update_visit, $condition_visit);
                        }
                    } else {
                        $this->result_array['message'] = "Invalid request ID. try again";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                }
            } else {
                $this->result_array['status'] = 440;
                $this->result_array['message'] = "Session Expired.";
                $this->api_status = REST_Controller::HTTP_OK;
            }
        } else {
            $this->result_array['message'] = "Request method not allowed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function get_credgenics_data_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->result_array['status'] = 1;
            $this->result_array['data'] = $post;
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

}

?>
