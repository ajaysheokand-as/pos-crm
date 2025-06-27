<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(getenv('WWW_PATH') . 'common_component/CommonComponent.php');

class ApiController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
            // Get the comma-separated origins from env
        // $origins_env = getenv('ALLOWED_ORIGINS');

        // // Convert to array if present, else use default
        // $allowed_origins = $origins_env
        //     ? array_map('trim', explode(',', $origins_env))
        //     : [
        //         'http://localhost:3000',
        //         'https://paisaonsalary.com'
        //     ];

        // // echo $allowed_origins;
        //             // log_message('error', message: 'Test in getLoan: ' . print_r($allowed_origins));

        // // Check if the incoming Origin is in the allowed list
        // if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        //     header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        //     header("Access-Control-Allow-Credentials: true");
        // }

        // // Always set these
        // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        // // Preflight handling
        // if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        //     http_response_code(200);
        //     exit();
        // }
        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");

        $this->load->model('Task_Model', 'Tasks');
        $this->load->library('form_validation'); // For input validation
        $this->load->helper('json_output'); 


    }

    public function importSingleLead()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        date_default_timezone_set('Asia/Kolkata');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Parse JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON format'
                ]);
                return;
            }

            // Set validation data
            $this->form_validation->set_data($input);

            // Define validation rules
            // $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('utm_source', 'UTM Source', 'required|trim');
            $this->form_validation->set_rules('pancard', 'Pancard', 'required|trim');
            $this->form_validation->set_rules('pincode', 'Pincode', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid input data: ' . validation_errors()
                ]);
                return;
            }

            // Define regex patterns
            $regex_name = '/[^A-Za-z ]/';
            $regex_utm_source = '/[^A-Za-z0-9 \._-]/'; // Fixed regex
            $regex_phone = '/[^0-9]/'; // Simplified to remove digits
            $regex_pin = '/[^0-9]/'; // Simplified to remove digits

            // Access and clean data
            $fullname = preg_replace($regex_name, '', $input['name'] ?? '');
            $pancard = trim(strtoupper($input['pancard'] ?? ''));
            $mobile = preg_replace($regex_phone, '', $input['mobile'] ?? '');
            $alternate_mobile = preg_replace($regex_phone, '', $input['alternate_mobile'] ?? '');
            $email = !empty($input['email']) ? $input['email'] : '';
            $alternate_email = !empty($input['alternate_email']) ? $input['alternate_email'] : '';
            $coordinates = !empty($input['coordinates']) ? $input['coordinates'] : '';
            $monthly_income = intval($input['monthly_income'] ?? 0);
            $loan_amount = intval($input['loan_amount'] ?? 0);
            $obligations = intval($input['obligations'] ?? 0);
            $gender = preg_replace($regex_name, '', $input['gender'] ?? '');
            $gender = !empty($gender) ? strtoupper($gender) : '';
            $utm_source = preg_replace($regex_utm_source, '', $input['utm_source'] ?? '');
            $utm_source = !empty($utm_source) ? strtoupper($utm_source) : '';
            $utm_campaign = preg_replace($regex_utm_source, '', $input['utm_campaign'] ?? '');
            $utm_campaign = !empty($utm_campaign) ? strtoupper($utm_campaign) : '';
            $pincode = preg_replace($regex_pin, '', $input['pincode'] ?? '');
            $coupon = !empty($input['coupon']) ? $input['coupon'] : '';
            $city_name = !empty($input['city_name']) ? $input['city_name'] : '';
            $state_name = !empty($input['state_name']) ? $input['state_name'] : '';
            $designation = !empty($input['designation']) ? trim(strtoupper($input['designation'])) : '';
            $company_name = !empty($input['company_name']) ? trim(strtoupper($input['company_name'])) : '';
            $rejection_flag = !empty($input['rejectd_flag']) && $input['rejectd_flag'] == 1 ? 1 : 0;

            // Check Pancard
            
            if (!empty($pancard)) {
                $result = $this->db
                    ->select('pancard, lead_id')
                    ->from('leads')
                    ->where('pancard', $pancard)
                    ->where('stage !=', 'S9')
                    ->where('status !=', 'REJECT')
                    ->where('lead_status_id !=', 9)
                    ->get();
                if ($result->num_rows() > 0) {
                    echo json_encode([
                    'status' => 'error',
                    "err_no" => 3,
                    'message' => 'This record already exists in our system. If you wish to reloan, please contact your relationship manager or support team.'
                ]);
                return;
                }
            }
            $dob = '';
            if (!empty($input['dob'])) {
                $dob = date('Y-m-d', strtotime($input['dob']));
            }

            if (empty($pancard) || !preg_match("/^([A-Za-z]{5})+([0-9]{4})+([A-Za-z]{1})$/", $pancard)) {
                $pancard = '';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = '';
            }

            if (!filter_var($alternate_email, FILTER_VALIDATE_EMAIL)) {
                $alternate_email = '';
            }

            if (!in_array($gender, ['MALE', 'FEMALE'])) {
                $gender = '';
            }

            if (empty($fullname) || empty($mobile) || empty($utm_source) || empty($pancard) || empty($pincode)) {
                echo json_encode([
                    'status' => 'error',
                    "err_no" => 2,
                    'data' => $this->input->post,
                    'message' => 'Required fields (name, mobile, utm_source, pancard, pincode) cannot be empty'
                ]);
                return;
            }

            $fullname_array = common_parse_full_name($fullname);
            $first_name = $fullname_array['first_name'];
            $middle_name = !empty($fullname_array['middle_name']) ? $fullname_array['middle_name'] : '';
            $sur_name = !empty($fullname_array['last_name']) ? $fullname_array['last_name'] : '';
            $city_id = '';
            $state_id = '';

            if (!empty($pincode)) {
                $result = $this->db->select('*')->where(['m_pincode_value' => $pincode])->from('master_pincode')->get();
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
                }
            }

            if (empty($city_id) && !empty($city_name)) {
                $city = $this->db->select('m_city_id,m_city_state_id')->from('master_city')->where('m_city_name', $city_name)->get();
                if ($city->num_rows() > 0) {
                    $city_array = $city->row_array();
                    $state_id = $city_array['m_city_state_id'];
                }
            }

            if (empty($state_id) && !empty($state_name)) {
                $state = $this->db->select('m_state_id')->from('master_state')->where('m_state_name', $state_name)->get();
                if ($state->num_rows() > 0) {
                    $state_array = $state->row_array();
                    $state_id = $state_array['m_state_id'];
                }
            }

            $lead_black_list_flag = 0;
            if (!empty($pancard)) {
                $result = $this->db->select('*')->where(['pancard' => $pancard])->from('blacklisted_pan')->get();
                if ($result->num_rows() > 0) {
                    $lead_black_list_flag = 1;
                }
            }

            $insertDataLeads = [
                'first_name' => $first_name,
                'mobile' => $mobile,
                'pancard' => $pancard,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'pincode' => $pincode,
                'email' => $email,
                'alternate_email' => $alternate_email,
                'loan_amount' => $loan_amount,
                'obligations' => $obligations,
                'user_type' => 'NEW',
                'lead_entry_date' => date('Y-m-d'),
                'created_on' => date('Y-m-d H:i:s'),
                'source' => 'API',
                'ip' => $ip,
                'status' => 'LEAD-NEW',
                'stage' => 'S1',
                'lead_status_id' => 1,
                'qde_consent' => 'Y',
                'lead_data_source_id' => 20,
                'coordinates' => $coordinates,
                'utm_source' => $utm_source,
                'utm_campaign' => $utm_campaign,
                'promocode' => $coupon,
                'lead_is_mobile_verified' => 1,
                'lead_black_list_flag' => $lead_black_list_flag
            ];

            if (strtoupper(trim($utm_source)) == 'C4C') {
                $insertDataLeads['lead_data_source_id'] = 21;
                $insertDataLeads['source'] = 'C4C';
                $insertDataLeads['utm_source'] = 'API';
            } elseif (strtoupper(trim($utm_source)) == 'REFCASE') {
                $insertDataLeads['lead_data_source_id'] = 27;
                $insertDataLeads['source'] = 'refcase';
                $insertDataLeads['utm_source'] = 'API';
            }

            if ($rejection_flag == 1) {
                $insertDataLeads['lead_rejected_reason_id'] = 52;
                $insertDataLeads['lead_rejected_datetime'] = date('Y-m-d H:i:s');
                $insertDataLeads['status'] = 'REJECT';
                $insertDataLeads['stage'] = 'S9';
                $insertDataLeads['lead_status_id'] = 9;
            }

            $this->db->insert('leads', $insertDataLeads);
            $lead_id = $this->db->insert_id();

            if (!empty($lead_id)) {
                $insertLeadsCustomer = [
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
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'cr_residence_pincode' => $pincode,
                    'created_date' => date('Y-m-d H:i:s')
                ];

                if (empty($dob)) {
                    unset($insertLeadsCustomer['dob']);
                }

                $this->db->insert('lead_customer', $insertLeadsCustomer);

                $insert_customer_employment = [
                    'lead_id' => $lead_id,
                    'emp_email' => $alternate_email,
                    'monthly_income' => $monthly_income,
                    'employer_name' => $company_name,
                    'emp_designation' => $designation,
                    'created_on' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('customer_employment', $insert_customer_employment);

                $cif_exist_flag = false;
                if (!empty($pancard)) {
                    $cif_query = $this->db->select('*')->where("cif_pancard = '$pancard'")->from('cif_customer')->get();
                    if ($cif_query->num_rows() > 0) {
                        $cif_result = $cif_query->row();
                        $cif_exist_flag = true;
                    }
                } elseif (!empty($mobile)) {
                    $cif_query = $this->db->select('*')->where("cif_mobile = '$mobile'")->from('cif_customer')->get();
                    if ($cif_query->num_rows() > 0) {
                        $cif_result = $cif_query->row();
                        $cif_exist_flag = true;
                    }
                }

                if ($cif_exist_flag) {
                    $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;
                    $user_type = $isdisbursedcheck > 0 ? 'REPEAT' : 'NEW';

                    $update_data_lead_customer = [
                        'dob' => $cif_result->cif_dob,
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
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if (empty($pancard)) {
                        $update_data_lead_customer['pancard'] = $cif_result->cif_pancard;
                    }
                    $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                    $update_customer_employment = [
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
                        'updated_on' => date('Y-m-d H:i:s')
                    ];

                    $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employment);

                    $update_data_leads = [
                        'customer_id' => $cif_result->cif_number,
                        'user_type' => $user_type,
                        'updated_on' => date('Y-m-d H:i:s')
                    ];

                    if (empty($pancard)) {
                        $update_data_leads['pancard'] = $cif_result->cif_pancard;
                    }

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Lead imported successfully',
                    'lead_id' => $lead_id,
                    'rejected' => $rejection_flag == 1
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to insert lead'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
        }
    }


  public function getLoan()
    {
        // Set the content type to JSON
        header('Content-Type: application/json');

        $input_data = file_get_contents("php://input");
        $post = [];

        if ($input_data) {
            $jsonInput = json_decode($input_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $post = $this->security->xss_clean($jsonInput);
            } else {
                // Handle JSON decoding error
                echo json_output(400, ['Status' => 0, 'Message' => 'Invalid JSON payload.']);
                return;
            }
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        // Validate the input (e.g., pancard is required)
        if (!isset($post['pancard']) || empty($post['pancard'])) {
            echo json_output(400, ['Status' => 0, 'Message' => 'PAN number is required.']);
            return;
        }

        try {
            $query = $this->db->select('lead_id, first_name, mobile, email, loan_amount, loan_no')
                ->where('pancard', $post['pancard'])
                ->where('status', 'DISBURSED')
                ->where('stage', 'S14')
                ->from('leads')
                ->order_by('lead_id', 'DESC')
                ->limit(1)
                ->get();

            $result = $query->row();

            if (isset($result) && !empty($result->lead_id)) {
                // Call the LeadModel using $this->LeadModel, not $this->leadModelObj
                    $leadModelObj = new LeadModel();

                $data = $leadModelObj->getLoanRepaymentDetails($result->lead_id);

                // Check if the LeadModel's method returned an error status
                if (isset($data['status']) && $data['status'] === 0) {
                    echo json_output(500, ['status' => 0, 'Message' => 'Failed to retrieve loan repayment details: ' . ($data['message'] ?? 'Unknown error in LeadModel.')]);
                    return;
                }

                $res = array(
                    'status' => 1,
                    'message' => 'Record found successfully.',
                    'data' => $data['repayment_data'] // This 'data' will contain 'repayment_data' and 'status' from LeadModel
                );
                echo json_output(200, $res);
            } else {
                echo json_output(200, ['Status' => 2, 'Message' => 'No PAN found.']);
            }
        } catch (Exception $e) {
            // Log the error for debugging
            log_message('error', 'Error in getLoan: ' . $e->getMessage());

            // Provide a more specific error message based on the exception
            // You might want to remove $e->getMessage() in production for security reasons
            echo json_output(500, ['Status' => 0, 'Message' => 'An internal server error occurred: ' . $e->getMessage()]);
        }
    }



}