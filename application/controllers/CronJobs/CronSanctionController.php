<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronSanctionController extends CI_Controller {

    var $cron_notification_email = 'info@tejasloan.com';
    var $cron_repeat_utm_sources = array('REPEAT', 'REPEATNF', 'NFREPEATSMS', 'LWREPEATDB');

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronSanction_Model', 'SanctionModel');
    }

    public function index() {
        //        $email_return = $this->lead_allocation_email_notification("info@tejasloan.com", "Ajay Kumar", "Rohit Kumar", 9319062592, 'info@tejasloan.com');
        //        print_r($email_return);
    }

    public function getFacebookCampaignData() {

        $start_datetime = date("d-m-Y H:i:s");

        $cron_name = "getfacebookcampaigndata";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD Facebook Lead Save  - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $return_report_data = array();
        $return_result_data = array();

        $this->load->helper('integration/payday_fb_call_api');

        $return_fb_form_name_array = payday_fb_campaign_api_call("GET_PAGE_FORM");

        if ($return_fb_form_name_array['status'] == 1) {

            if (!empty($return_fb_form_name_array['lead_data'])) {

                $form_name_array = $return_fb_form_name_array['lead_data'];

                foreach ($form_name_array as $form_name_value) {

                    $fb_form_leads_count = intval($form_name_value['leads_count']);
                    $fb_form_id = $form_name_value['id'];
                    $fb_form_status = strtoupper($form_name_value['status']);
                    $fb_form_name = $form_name_value['name'];

                    if ($fb_form_leads_count > 0 && $fb_form_status == 'ACTIVE') {

                        $return_fb_form_lead_array = payday_fb_campaign_api_call("GET_FORM_DATA", $fb_form_id, 59);

                        if ($return_fb_form_lead_array['status'] == 1) {

                            $form_lead_data_array = $return_fb_form_lead_array['lead_data'];

                            $return_result_data = $this->process_facebook_lead_data($form_lead_data_array, $fb_form_id);

                            $return_report_data[] = array(
                                "facebook_form_id" => $fb_form_id,
                                "facebook_form_name" => $fb_form_name,
                                "facebook_form_leads" => $fb_form_leads_count,
                                "facebook_data" => $form_lead_data_array,
                                "result_data" => $return_result_data
                            );

                            //                            echo "<br/><br/><br/> Form Name : " . $fb_form_name;
                        }
                    }
                }
            }
        }

        $message = json_encode($return_report_data);

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, 0, 0);
        }

        //        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
    }

    function process_facebook_lead_data($form_lead_data_array, $fb_form_id = "") {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $regex_phone = '/[^0-9]{10}$/';
        $regex_name = '/[^A-Za-z ]/';
        $regex_name_digit = '/[^A-Za-z0-9 ]/';
        $regex_pin = '/[^0-9]{6}$/';

        $rejected_count = 0;
        $duplicate_count = 0;
        $insert_count = 0;
        $elgibility_rejected_count = 0;
        $success_count = 0;
        $failed_count = 0;
        $rejected_mandatory_count = 0;

        $utm_source = "FBLEADFORM";
        $utm_campaign = "FBLEADFORM";

        if ($fb_form_id == "1664528427300826") {
            $utm_source = "FBLEADFORM_Internal";
            $utm_campaign = "FBLEADFORM_Internal";
        } else if ($fb_form_id == '718285456578070') {
            $utm_source = "FBLEADFORM_TYM";
            $utm_campaign = "FBLEADFORM_TYM";
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'];

        $form_lead_data_array = trim_data_array($form_lead_data_array);

        foreach ($form_lead_data_array as $form_data) {

            $user_type = "NEW";
            $employment_type = (isset($form_data['salaried']) && !empty($form_data['salaried'])) ? trim(strtoupper($form_data['salaried'])) : "";

            if (empty($employment_type)) {
                $employment_type = (isset($form_data['are_you_salaried_?']) && !empty($form_data['are_you_salaried_?'])) ? trim(strtoupper($form_data['are_you_salaried_?'])) : "";
            }

            $full_name = (isset($form_data['full_name']) && !empty($form_data['full_name'])) ? trim(strtoupper($form_data['full_name'])) : "";
            $gender = (isset($form_data['gender']) && !empty($form_data['gender'])) ? trim(strtoupper($form_data['gender'])) : "";
            $mobile = (isset($form_data['phone_number']) && !empty($form_data['phone_number'])) ? $form_data['phone_number'] : "";
            $state_name = (isset($form_data['state']) && !empty($form_data['state'])) ? trim(strtoupper($form_data['state'])) : "";
            $city_name = (isset($form_data['city']) && !empty($form_data['city'])) ? trim(strtoupper($form_data['city'])) : "";
            $pincode = (isset($form_data['post_code']) && !empty($form_data['post_code'])) ? $form_data['post_code'] : "";
            $email = (isset($form_data['email']) && !empty($form_data['email'])) ? trim(strtoupper($form_data['email'])) : "";
            $pancard = (isset($form_data['pan_no']) && !empty($form_data['pan_no'])) ? trim(strtoupper($form_data['pan_no'])) : "";
            $company_name = (isset($form_data['company_name']) && !empty($form_data['company_name'])) ? trim(strtoupper($form_data['company_name'])) : "";
            $designation = (isset($form_data['job_title']) && !empty($form_data['job_title'])) ? trim(strtoupper($form_data['job_title'])) : "";

            $employment_type = preg_replace($regex_name, '', $employment_type);
            $employment_type = trim($employment_type);

            $fullname_array = common_parse_full_name($full_name);

            $first_name = $fullname_array['first_name'];
            $middle_name = !empty($fullname_array['middle_name']) ? $fullname_array['middle_name'] : '';
            $sur_name = !empty($fullname_array['last_name']) ? $fullname_array['last_name'] : "";

            $gender = preg_replace($regex_name, '', $gender);
            $gender = trim(strtoupper($gender));

            $mobile = substr($mobile, -10, 10);
            $mobile = preg_replace($regex_phone, '', $mobile);
            $mobile = trim($mobile);

            $pincode = substr($pincode, -6, 6);
            $pincode = preg_replace($regex_pin, '', $pincode);
            $pincode = trim($pincode);

            $state_name = preg_replace($regex_name, '', $state_name);
            $state_name = trim($state_name);

            $city_name = preg_replace($regex_name_digit, '', $city_name);
            $city_name = trim($city_name);

            $designation = preg_replace($regex_name_digit, '', $designation);
            $designation = trim($designation);

            $company_name = preg_replace($regex_name_digit, '', $company_name);
            $company_name = trim($company_name);

            $email = str_replace('\U0040', "@", $email);

            if (empty($pancard) || !preg_match("/^([A-Za-z]{5})+([0-9]{4})+([A-Za-z]{1})$/", $pancard)) {
                $pancard = "";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = "";
            }
            if (!in_array($gender, array("MALE", "FEMALE"))) {
                $gender = "";
            }

            if (!in_array($employment_type, array("YES", "NO"))) {
                $employment_type = "";
            }

            $city_id = "";
            $state_id = "";

            if (!empty($pincode)) {
                $result = $this->db->select('*')->where(["m_pincode_value" => $pincode])->from("master_pincode")->get();
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

            if (empty($city_id) && !empty($city_name)) { //if pincode not avialable then city name searched
                $city = $this->db->select('m_city_id,m_city_state_id')->from('master_city')->where('m_city_name', $city_name)->get();
                if ($city->num_rows() > 0) {
                    $city_array = $city->row_array();
                    $state_id = $city_array['m_city_state_id'];
                }
            }

            if (empty($state_id) && !empty($state_name)) { //if city not avialable then state name searched
                $state = $this->db->select('m_state_id')->from('master_state')->where('m_state_name', $state_name)->get();
                if ($state->num_rows() > 0) {
                    $state_array = $state->row_array();
                    $state_id = $state_array['m_state_id'];
                }
            }

            if ($employment_type == "YES" && !empty($pancard) && !empty($first_name) && !empty($mobile) && (!empty($pincode) || !empty($city_id)) && !empty($email)) {

                $dedupeRequestArray = array('mobile' => $mobile, 'pancard' => $pancard, 'email' => $email);

                $dedupeReturnArray = $CommonComponent->check_customer_dedupe($dedupeRequestArray);

                if (!empty($dedupeReturnArray['status']) && $dedupeReturnArray['status'] == 1) {
                    $rejected_count++;
                    $duplicate_count++;
                } else {

                    $insertDataLeads = array(
                        'first_name' => $first_name,
                        'mobile' => $mobile,
                        'pancard' => $pancard,
                        'state_id' => $state_id,
                        'city_id' => $city_id,
                        'pincode' => $pincode,
                        'email' => $email,
                        'loan_amount' => 0,
                        'obligations' => 0,
                        'user_type' => $user_type,
                        'lead_entry_date' => date("Y-m-d"),
                        'created_on' => date("Y-m-d H:i:s"),
                        'source' => "FBLEADFORM",
                        'ip' => $ipAddress,
                        'status' => "LEAD-NEW",
                        'stage' => "S1",
                        'lead_status_id' => 1,
                        'qde_consent' => "Y",
                        'lead_data_source_id' => 3,
                        'utm_source' => $utm_source,
                        'utm_campaign' => $utm_campaign,
                    );

                    $this->db->insert('leads', $insertDataLeads);

                    $lead_id = $this->db->insert_id();

                    if (!empty($lead_id)) {

                        $insert_count++;

                        $insertLeadsCustomer = array(
                            'customer_lead_id' => $lead_id,
                            'first_name' => $first_name,
                            'middle_name' => $middle_name,
                            'sur_name' => $sur_name,
                            'gender' => $gender,
                            'mobile' => $mobile,
                            'email' => $email,
                            'pancard' => $pancard,
                            'state_id' => $state_id,
                            'city_id' => $city_id,
                            'cr_residence_pincode' => $pincode,
                            'created_date' => date("Y-m-d H:i:s")
                        );

                        $this->db->insert('lead_customer', $insertLeadsCustomer);

                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'employer_name' => $company_name,
                            'emp_designation' => $designation,
                            'income_type' => 1,
                            'created_on' => date("Y-m-d h:i:s")
                        ];

                        $this->db->insert('customer_employment', $insert_customer_employement);

                        $cif_exist_flag = false;

                        if (!empty($pancard)) {
                            $cif_query = $this->db->select('*')->where("cif_pancard = '$pancard'")->from('cif_customer')->get();
                            if ($cif_query->num_rows() > 0) {
                                $cif_result = $cif_query->row();
                                $cif_exist_flag = true;
                            }
                        }

                        if ($cif_exist_flag) {
                            if (!empty($cif_result)) {

                                $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;

                                if ($isdisbursedcheck > 0) {
                                    $user_type = "REPEAT";
                                } else {
                                    $user_type = "NEW";
                                }

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
                                    'updated_at' => date("Y-m-d H:i:s")
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
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];

                                $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                                $update_data_leads = [
                                    'customer_id' => $cif_result->cif_number,
                                    'user_type' => $user_type,
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];

                                $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                            }
                        }

                        $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                        if ($return_eligibility_array['status'] == 2) {
                            $rejected_count++;
                            $elgibility_rejected_count++;
                        } else {
                            $success_count++;
                            if ($user_type == "NEW") {
                                setLWRepeatCustomer($lead_id);
                            }
                        }
                    } else {
                        $failed_count++;
                    }
                }
            } else {
                $rejected_count++;
                $rejected_mandatory_count++;
            }
        }


        $return_array = array(
            "total_count" => count($form_lead_data_array),
            "insert_count" => $insert_count,
            "success_count" => $success_count,
            "rejected_count" => $rejected_count,
            "rejected_mandatory_count" => $rejected_mandatory_count,
            "duplicate_count" => $duplicate_count,
            "elgibility_rejected_count" => $elgibility_rejected_count,
            "failed_count" => $failed_count
        );

        return $return_array;
    }

    public function userLeadList() {
        $user_type = isset($_GET['userType']) ? addslashes(strtoupper($_GET['userType'])) : null;
        $teamList = $this->SanctionModel->get_users_lead_list($user_type);
        //echo "<pre>";
        //print_r($teamList);
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>User ID</th>";
        echo "<th>Name</th>";
        //echo "<th>Email</th>";
        //echo "<th>Mobile</th>";
        echo "<th>Total Leads</th>";
        //echo "<th>User Status ID</th>";
        echo "<th>User Active Flag</th>";
        echo "<th>User Active Case Type</th>";
        echo "</tr>";

        foreach ($teamList['data'] as $k => $user) {
            echo "<tr>";
            echo "<td>" . $k++ . "</td>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td>{$user['name']}</td>";
            //echo "<td>{$user['email']}</td>";
            //echo "<td>{$user['mobile']}</td>";
            echo "<td>{$user['total_leads']}</td>";
            //echo "<td>{$user['user_status_id']}</td>";
            //echo "<td>{$user['user_active_flag']}</td>";
            echo ($user['user_active_flag'] == 1) ? "<td style='color:green'>Active</td>" : "<td style='color:red'>Inactive</td>";
            echo ($user['user_active_case_type'] == 2) ? "<td style='font-weight: bold'>Repeat</td>" : "<td>Fresh</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    public function screenerLeadAllocation_noUse() {
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        if (intval(date('H')) >= 8 && intval(date('H')) <= 23) {
        } else {
            die("NO WORKING HOURS");
        }

        if (isset($_REQUEST['orderBy'])) {
            $orderBy = addslashes($_REQUEST['orderBy']);
        } else {
            $orderBy = null;
        }

        if (isset($_REQUEST['count'])) {
            $bucketSize = ($_REQUEST['count'] > 50) ? 15 : addslashes($_REQUEST['count']);
        } else {
            $bucketSize = 15;
        }


        $cron_name = "screenerleadallocationcode";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        //$tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD : SCREENER LEAD ALLOCATION  - start time :" . $start_datetime;

        //$cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);
        $master_user_lead = array();
        $userType = isset($_GET['userType']) ? addslashes(strtoupper($_GET['userType'])) : null;
        $teamList = $this->SanctionModel->get_users_lead_list();
        //echo '<pre>'; print_r($teamList); die;
        if (!empty($teamList) && $teamList['status'] == 1) {
            $sanTeams = $teamList['data'];
            $i = 0;
            foreach ($sanTeams as $user_data) {
                if (isset($user_data['user_active_flag']) && $user_data['user_active_flag'] == 1) {
                    if (isset($userType) && $userType == 'REPEAT') {
                        if (isset($user_data['user_active_case_type']) && $user_data['user_active_case_type'] == 2) {
                            $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                            $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                            $master_user_lead[$i]['user_active_case_type'] = $user_data['user_active_case_type'];
                            $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                            $master_user_lead[$i]['name'] = $user_data['name'];
                            $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                            $master_user_lead[$i]['email'] = $user_data['email'];
                            $master_user_lead[$i]['assigned'] = 0;
                            $master_user_lead[$i]['call_assigned'] = 0;
                            $master_user_lead[$i]['email_sent'] = 0;
                            //$master_user_lead[$i]['sms_sent'] = 0;
                            $i++;
                        }
                    } else {
                        if (empty($user_data['user_active_case_type']) || $user_data['user_active_case_type'] == 1) {
                            $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                            $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                            $master_user_lead[$i]['user_active_case_type'] = $user_data['user_active_case_type'];
                            $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                            $master_user_lead[$i]['name'] = $user_data['name'];
                            $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                            $master_user_lead[$i]['email'] = $user_data['email'];
                            $master_user_lead[$i]['assigned'] = 0;
                            $master_user_lead[$i]['call_assigned'] = 0;
                            $master_user_lead[$i]['email_sent'] = 0;
                            //$master_user_lead[$i]['sms_sent'] = 0;
                            $i++;
                        }
                    }
                }
            }

            //echo '<pre>'; print_r($master_user_lead); die;
            //$message .= json_encode($master_user_lead) . '<br/>';
            $tempDetails = $this->SanctionModel->get_lead_new_list($userType, $orderBy);
            //echo '<pre>';print_r($tempDetails);die;

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {
                $allLeads = $tempDetails = $tempDetails['data'];
                $a = $b = 0;
                foreach ($tempDetails as $kk => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $customer_name = ucwords(strtolower($customer_data['first_name']));
                    $master_user_leadO = $master_user_lead;
                    foreach ($master_user_lead as $user_key => $user_data) {

                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = $user_data['count'];
                        $lead_assigned_count = $user_data['assigned'];
                        $lead_call_assigned_count = $user_data['call_assigned'];
                        $lead_email_sent_count = $user_data['email_sent'];
                        //$lead_sms_sent_count = $user_data['sms_sent'];

                        $user_name = ucwords(strtolower($user_data['name']));
                        $user_mobile = $user_data['mobile'];
                        $user_email = strtolower($user_data['email']);

                        //echo '<pre>'; print_r($user_data); die;
                        /*
						if(empty($user_active_flag) || empty($user_active_case_type) || ($user_type != $user_active_case_type) || $user_total_count >= 40 || $lead_assigned_count >= 40) {
							 continue;
						}
						*/
                        if ($user_total_count >= $bucketSize || $lead_assigned_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        //echo '<pre>'; print_r($lead_udpate_data);die;
                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {
                            $b++;
                            $user_total_count = $user_total_count + 1;
                            $lead_assigned_count = $lead_assigned_count + 1;
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                            $lead_remark = "Lead Auto Allocated to " . $user_name;
                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            //echo '<pre>'; print_r($insert_lead_followup);die;
                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;


                            /* if (!empty($email)) {
								 echo '<pre>';
								//$email_return = $this->lead_allocation_email_notification($email, $customer_name, $user_name, $user_mobile, $user_email);

								if ($email_return['status'] == 1) {
									$master_user_lead[$user_key]['email_sent'] = $lead_email_sent_count + 1;
								}
							} */
                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                    if ($master_user_leadO === $master_user_lead) {
                        break;
                    }

                    /* echo "<br/>---<br/><pre>".$kk;
					print_r($master_user_leadO);
				    echo "<pre>";
					print_r($master_user_lead);
					if($master_user_leadO === $master_user_lead){
						break;
					}
				    echo "<br/>-------------------------<br/><pre>"; */
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "Total Leads: " . count($allLeads) . " || Total allocation: " . $b .  '<br/>';
                //$message .= json_encode($master_user_lead) . '<br/>';

            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No Active User";
        }


        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        // if (!empty($cron_insert_id)) {
        //     $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        // }
        echo $message;
    }

    public function screenerLeadAllocationG30K() {
        // echo "<pre>";
        $bucketSize = 100;
        $cronLimit = 25;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);


        if (intval(date('H')) >= 9 && intval(date('Hi')) <= 2330) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "screenerLeadAllocationG30K";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        // require_once(COMPONENT_PATH . 'CommonComponent.php');

        // $CommonComponent = new CommonComponent();

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD SCREENER LEAD ALLOCATION ABOVE 30K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_users_lead_list(2);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                // $master_user_lead[$i]['user_active_case_type'] = $user_data['user_active_case_type'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                // $master_user_lead[$i]['call_assigned'] = 0;
                // $master_user_lead[$i]['email_sent'] = 0;
                // $master_user_lead[$i]['sms_sent'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount >='] = 30000;
            $filter['LD.lead_entry_date >='] = '2024-10-02';
            $filter['LD.stage ='] = 'S1';
            $filter['LD.created_on <='] = date("Y-m-d H:i:s", strtotime('-30 minutes'));
            // $filter['LD.lead_status_id ='] = 1;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $lead_index => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if ($monthly_salary < 30000 || !in_array($customer_data['lead_status_id'], [1, 41, 42]) || $customer_data['stage'] != 'S1') {
                        continue;
                    }

                    if ($mobile == "9170004606") {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        // $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        // $lead_call_assigned_count = intval($user_data['call_assigned']);
                        // $lead_email_sent_count = intval($user_data['email_sent']);
                        // $lead_sms_sent_count = intval($user_data['sms_sent']);
                        $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));
                        // $user_mobile = $user_data['mobile'];
                        // $user_email = strtolower($user_data['email']);

                        if (empty($user_active_flag) || (($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $monthly_salary < 30000 || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_doable_to_application_status'] = 2;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            // $runo_return = $CommonComponent->call_runo_management_api('LEAD_CAT_SANCTION', $lead_id);

                            // if ($runo_return['status'] == 1) {
                            //     $master_user_lead[$user_key]['call_assigned'] = $lead_call_assigned_count + 1;
                            // }

                            // if (!empty($email)) {

                            //     $email_return = $this->lead_allocation_email_notification($email, $customer_name, $user_name, $user_mobile, $user_email);

                            //     if ($email_return['status'] == 1) {
                            //         $master_user_lead[$user_key]['email_sent'] = $lead_email_sent_count + 1;
                            //     }
                            // }

                            // if (!empty($mobile)) {


                            //     $sms_return = $CommonComponent->payday_sms_api(3, $lead_id, array('mobile' => $mobile, 'name' => $customer_name, 'executive_name' => $user_name, 'executive_mobile' => $user_mobile));

                            //     if ($sms_return['status'] == 1) {
                            //         $master_user_lead[$user_key]['sms_sent'] = $lead_sms_sent_count + 1;
                            //     }
                            // }

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    // Extract 'count' and sort
                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        echo $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, 'info@tejasloan.com');

        if ($return_array['status'] == 1) {
            echo json_encode(['Status' => 1, 'Message' => 'Success']);
        }
    }

    public function screenerLeadAllocationG50K() {
        echo "<pre>";
        $bucketSize = 50;
        $cronLimit = 10;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);


        if (intval(date('H')) >= 9 && intval(date('Hi')) <= 2330) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "screenerLeadAllocationG50K";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD SCREENER LEAD ALLOCATION ABOVE 50K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_screener_users_lead_list(2);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount >='] = 50000;
            // $filter['LD.lead_entry_date >='] = '2024-10-02';
            $filter['LD.stage ='] = 'S1';
            $filter['LD.created_on <='] = date("Y-m-d H:i:s", strtotime('-30 minutes'));
            // $filter['LD.lead_status_id ='] = 1;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $lead_index => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if (!in_array($customer_data['lead_status_id'], [1, 41, 42]) || $customer_data['stage'] != 'S1') {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        // $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));

                        if ((empty($user_active_flag) || ($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_doable_to_application_status'] = 2;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;
                            $lead_assigned_count = $lead_assigned_count + 1;
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                            $lead_remark = "Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        echo $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, 'info@tejasloan.com');
    }

    public function screenerLeadAllocationB50K() {
        echo "<pre>";
        $bucketSize = 100;
        $cronLimit = 15;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);


        if (intval(date('H')) >= 9 && intval(date('Hi')) <= 2330) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "screenerLeadAllocationB50K";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD SCREENER LEAD ALLOCATION BELOW 50K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_screener_users_lead_list(1);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount <'] = 50000;
            $filter['LD.monthly_salary_amount >='] = 26000;
            // $filter['LD.lead_entry_date >='] = '2024-10-02';
            $filter['LD.stage ='] = 'S1';
            $filter['LD.created_on <='] = date("Y-m-d H:i:s", strtotime('-30 minutes'));
            // $filter['LD.lead_status_id ='] = 1;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $lead_index => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if (!in_array($customer_data['lead_status_id'], [1, 41, 42]) || $customer_data['stage'] != 'S1') {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        // $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));

                        if ((empty($user_active_flag) || ($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_doable_to_application_status'] = 2;
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;
                            $lead_assigned_count = $lead_assigned_count + 1;
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                            $lead_remark = "Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        echo $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, 'info@tejasloan.com');
    }

    public function creditApllicationAllocationG50K() {
        echo "<pre>";
        $bucketSize = 50;
        $cronLimit = 10;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        if (intval(date('H')) >= 9 && intval(date('Hi')) <= 2330) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "creditApllicationAllocationG50K";

        $status_name = "APPLICATION-INPROCESS";
        $status_id = 5;
        $status_stage = "S5";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD CREDIT APPLICATION ALLOCATION ABOVE 50K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_credit_users_lead_list(2);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount >='] = 50000;
            $filter['LD.lead_status_id ='] = 4;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $lead_index => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    $screener_user_id = $customer_data['lead_screener_assign_user_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if (!in_array($customer_data['lead_status_id'], [4]) || $customer_data['stage'] != 'S4') {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        // $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));
                        // $user_mobile = $user_data['mobile'];
                        // $user_email = strtolower($user_data['email']);

                        if (empty($user_active_flag) || (($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_credit_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_credit_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        if (empty($screener_user_id)) {
                            $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                            $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        }

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Application Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }
                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Application Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        echo $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, '');
    }

    public function creditApllicationAllocationB50K() {
        // echo "<pre>";
        $bucketSize = 50;
        $cronLimit = 15;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        if (intval(date('H')) >= 9 && intval(date('Hi')) <= 2330) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "creditApllicationAllocationB50K";

        $status_name = "APPLICATION-INPROCESS";
        $status_id = 5;
        $status_stage = "S5";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD CREDIT APPLICATION ALLOCATION BELOW 50K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_credit_users_lead_list(1);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount <'] = 50000;
            $filter['LD.monthly_salary_amount >='] = 26000;
            $filter['LD.lead_status_id ='] = 4;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $lead_index => $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    $screener_user_id = $customer_data['lead_screener_assign_user_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if (!in_array($customer_data['lead_status_id'], [4]) || $customer_data['stage'] != 'S4') {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        // $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));
                        // $user_mobile = $user_data['mobile'];
                        // $user_email = strtolower($user_data['email']);

                        if (empty($user_active_flag) || (($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_credit_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_credit_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        if (empty($screener_user_id)) {
                            $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                            $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        }

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Application Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }
                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Application Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        echo $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, '');
    }

    public function screenerLeadAllocationB30K() {
        // echo "<pre>";
        $bucketSize = 250;
        $cronLimit = 25;
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        if (intval(date('H')) >= 9 && intval(date('H')) < 23) {
        } else {
            echo json_encode(['Status' => 2, 'Message' => 'NO WORKING HOURS']);
            exit;
        }

        $cron_name = "screenerLeadAllocationB30K";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        // require_once(COMPONENT_PATH . 'CommonComponent.php');

        // $CommonComponent = new CommonComponent();

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD SCREENER LEAD ALLOCATION Below 30K - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_users_lead_list(1);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                if (empty($user_data['user_active_flag'])) {
                    continue;
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                // $master_user_lead[$i]['user_active_case_type'] = $user_data['user_active_case_type'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                // $master_user_lead[$i]['call_assigned'] = 0;
                // $master_user_lead[$i]['email_sent'] = 0;
                // $master_user_lead[$i]['sms_sent'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $filter = array();
            $filter['LD.monthly_salary_amount <'] = 30000;
            $filter['LD.monthly_salary_amount >'] = 25000;
            $filter['LD.lead_entry_date >='] = '2024-10-02';
            $filter['LD.stage ='] = 'S1';
            $filter['LD.lead_status_id ='] = 1;
            $filter['LD.user_type ='] = 'NEW';

            $tempDetails = $this->SanctionModel->get_lead_list($filter);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';
                $$monthly_salary = 0;

                foreach ($tempDetails as $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    // $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $monthly_salary = $customer_data['monthly_salary_amount'];
                    // $customer_name = ucwords(strtolower($customer_data['first_name']));
                    // $user_type = 0;

                    if ($monthly_salary < 25000 || $monthly_salary >= 30000 || $customer_data['lead_status_id'] != 1 || $customer_data['stage'] != 'S1') {
                        continue;
                    }

                    if ($mobile == "9170004606") {
                        continue;
                    }

                    // if ($customer_data['user_type'] == "NEW" && in_array($customer_data['lead_data_source_id'], array(5))) {
                    //     $user_type = 2;
                    // } else if ($customer_data['user_type'] == "NEW") {
                    //     $user_type = 1;
                    // } else if ($customer_data['user_type'] == "REPEAT") {
                    //     $user_type = 2;
                    // }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        // $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = intval($user_data['count']);
                        $user_total_inprocess_leads = intval($user_data['inprocess_leads']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        // $lead_call_assigned_count = intval($user_data['call_assigned']);
                        // $lead_email_sent_count = intval($user_data['email_sent']);
                        // $lead_sms_sent_count = intval($user_data['sms_sent']);
                        $lead_total_today_process_leads = intval($user_data['total_today_process_leads']);

                        $user_name = ucwords(strtolower($user_data['name']));
                        // $user_mobile = $user_data['mobile'];
                        // $user_email = strtolower($user_data['email']);

                        if (empty($user_active_flag) || (($user_total_inprocess_leads + $lead_assigned_count) >= $cronLimit) || $lead_assigned_count >= $cronLimit || $monthly_salary >= 30000 || $user_total_count >= $bucketSize) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            // $runo_return = $CommonComponent->call_runo_management_api('LEAD_CAT_SANCTION', $lead_id);

                            // if ($runo_return['status'] == 1) {
                            //     $master_user_lead[$user_key]['call_assigned'] = $lead_call_assigned_count + 1;
                            // }

                            // if (!empty($email)) {

                            //     $email_return = $this->lead_allocation_email_notification($email, $customer_name, $user_name, $user_mobile, $user_email);

                            //     if ($email_return['status'] == 1) {
                            //         $master_user_lead[$user_key]['email_sent'] = $lead_email_sent_count + 1;
                            //     }
                            // }

                            // if (!empty($mobile)) {


                            //     $sms_return = $CommonComponent->payday_sms_api(3, $lead_id, array('mobile' => $mobile, 'name' => $customer_name, 'executive_name' => $user_name, 'executive_mobile' => $user_mobile));

                            //     if ($sms_return['status'] == 1) {
                            //         $master_user_lead[$user_key]['sms_sent'] = $lead_sms_sent_count + 1;
                            //     }
                            // }

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    // Extract 'count' and sort
                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        // $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
        // echo $message;
        echo json_encode(['Status' => 1, 'Message' => $message]);
        exit;
    }

    public function move_lead_hold_to_screener() {
        // echo "<pre>";
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        // if (intval(date('H')) >= 9 && intval(date('H')) < 23) {
        // } else {
        //     die("NO WORKING HOURS");
        // }

        $cron_name = "move_lead_hold_to_screener";

        $status_name = "LEAD-INPROCESS";
        $status_id = 2;
        $status_stage = "S2";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo json_encode(['Status' => 2, 'Message' => 'Already Cron in prcoess']);
            exit;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD Move Lead Hold To Screener - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_users_lead_list(1);

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {
                // if (empty($user_data['user_active_flag'])) {
                //     continue;
                // }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['inprocess_leads'] = intval($user_data['total_current_inprocess_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['total_today_process_leads'] = $user_data['total_today_process_leads'];
                $master_user_lead[$i]['assigned'] = 0;
                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $tempDetails = $this->SanctionModel->get_lead_hold(36);

            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';

                foreach ($tempDetails as $customer_data) {

                    $lead_id = $customer_data['lead_id'];

                    if ($customer_data['lead_status_id'] != 3 || $customer_data['stage'] != 'S3') {
                        continue;
                    }

                    foreach ($master_user_lead as $user_key => $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_total_count = intval($user_data['count']);
                        $lead_assigned_count = intval($user_data['assigned']);

                        $user_name = ucwords(strtolower($user_data['name']));

                        $lead_udpate_data = array();
                        $lead_udpate_data['status'] = $status_name;
                        $lead_udpate_data['stage'] = $status_stage;
                        $lead_udpate_data['lead_status_id'] = $status_id;
                        $lead_udpate_data['lead_screener_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    // Extract 'count' and sort
                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Move Lead Hold To Screener : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;

        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, 'info@tejasloan.com');
        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
        // echo $message;
        echo json_encode(['Status' => 1, 'Message' => $message]);
        exit;
    }

    public function notContactableLeadAllocation() {
        //        echo "<pre>";
        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $email_counter = array('update_record' => 0, 'update_failed' => 0);

        if (intval(date('H')) >= 9 && intval(date('H')) < 22) {
        } else {
            die("NO WORKING HOURS");
        }

        $cron_name = "notcontactableleadallocation";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        //$tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);
        //        if (!empty($tempDetails['status'])) {
        //            echo "Already Cron in prcoess";
        //            die;
        //        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD NOT CONTACTABLE LEAD ALLOCATION  - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->get_not_contactable_users_lead_list();

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {

            $tempDetails = $tempDetails['data'];

            $i = 0;
            foreach ($tempDetails as $user_data) {

                $master_user_lead[$i]['rejected_lead_array'] = [];

                $tempDetailsRejectedLeads = $this->SanctionModel->get_not_contactable_user_rejected_leads($user_data['user_id']);

                if (!empty($tempDetailsRejectedLeads) && $tempDetailsRejectedLeads['status'] == 1) {
                    $master_user_lead[$i]['rejected_lead_array'] = $tempDetailsRejectedLeads['data'];
                }

                $master_user_lead[$i]['user_id'] = $user_data['user_id'];
                $master_user_lead[$i]['user_active_flag'] = $user_data['user_active_flag'];
                $master_user_lead[$i]['user_active_case_type'] = $user_data['user_active_case_type'];
                $master_user_lead[$i]['count'] = intval($user_data['total_leads']);
                $master_user_lead[$i]['name'] = $user_data['name'];
                $master_user_lead[$i]['mobile'] = $user_data['mobile'];
                $master_user_lead[$i]['email'] = $user_data['email'];
                $master_user_lead[$i]['assigned'] = 0;
                $master_user_lead[$i]['call_assigned'] = 0;
                $master_user_lead[$i]['email_sent'] = 0;

                $i++;
            }

            $message = "Total Users = " . count($master_user_lead) . '<br/>';

            $tempDetails = $this->SanctionModel->get_not_contactable_lead_list();
            //echo "<pre>";
            //print_r($tempDetails);
            //die;
            if (!empty($tempDetails) && $tempDetails['status'] == 1) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';

                foreach ($tempDetails as $customer_data) {

                    $lead_id = $customer_data['lead_id'];
                    $lead_status_id = $customer_data['lead_status_id'];
                    $email = $customer_data['email'];
                    $mobile = $customer_data['mobile'];
                    $customer_name = ucwords(strtolower($customer_data['first_name']));
                    $lead_rejected_assign_counter = ($customer_data['lead_rejected_assign_counter'] > 0) ? $customer_data['lead_rejected_assign_counter'] : 0;

                    $user_type = 0;

                    if ($customer_data['user_type'] == "NEW" && in_array(strtoupper($customer_data['utm_source']), $this->cron_repeat_utm_sources)) {
                        $user_type = 2;
                    } else if ($customer_data['user_type'] == "NEW") {
                        $user_type = 1;
                    } else if ($customer_data['user_type'] == "REPEAT") {
                        $user_type = 2;
                    }

                    foreach ($master_user_lead as $user_key => $user_data) {

                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        $user_active_case_type = $user_data['user_active_case_type'];
                        $user_total_count = intval($user_data['count']);
                        $lead_assigned_count = intval($user_data['assigned']);
                        $lead_call_assigned_counter = intval($user_data['call_assigned']);
                        $lead_email_sent_count = intval($user_data['email_sent']);
                        $lead_rejected_lead_array = $user_data['rejected_lead_array'];

                        $user_name = ucwords(strtolower($user_data['name']));
                        $user_mobile = $user_data['mobile'];
                        $user_email = strtolower($user_data['email']);

                        if (empty($user_active_flag) || empty($user_active_case_type) || ($user_type != $user_active_case_type) || in_array($lead_id, $lead_rejected_lead_array) || $user_total_count >= 20 || $lead_assigned_count >= 20) {
                            echo $user_data["name"] . " is here<br>";
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data['updated_on'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['lead_rejected_assign_user_id'] = $user_id;
                        $lead_udpate_data['lead_rejected_assign_datetime'] = date('Y-m-d H:i:s');
                        $lead_udpate_data['lead_rejected_assign_counter'] = $lead_rejected_assign_counter + 1;

                        $update_flag = $this->SanctionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $email_counter['update_record'] = $email_counter['update_record'] + 1;

                            $lead_remark = "Not Contactable Lead Auto Allocated to " . $user_name;

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => $user_id,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $lead_status_id,
                                'remarks' => $lead_remark
                            );

                            $this->SanctionModel->insert('lead_followup', $insert_lead_followup);

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;

                            //                            $this->load->helper('integration/payday_runo_call_api');
                            //                            $runo_return = payday_call_management_api_call('LEAD_CAT_SANCTION', $lead_id);
                            //                            if ($runo_return['status'] == 1) {
                            //                                $master_user_lead[$user_key]['call_assigned'] = $lead_call_assigned_count + 1;
                            //                            }

                            break;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }

                    $user_master_key = array_column($master_user_lead, 'count');
                    array_multisort($user_master_key, SORT_ASC, $master_user_lead);
                }

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;
        //        echo $message;
        //        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id, $email_counter['update_record'], $email_counter['update_failed']);
        }
    }

    public function sanctionTargetUpdate() {

        $start_datetime = date("d-m-Y H:i:s");
        $message = "";

        $cron_name = "sanctiontargetupdate";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SanctionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD SANCTION TRAGET UPDATE  - start time :" . $start_datetime;

        $cron_insert_id = $this->SanctionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();

        $tempDetails = $this->SanctionModel->update_sanction_target();

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {
            $message = $tempDetails['message'];
        } else {
            $message = "No User Data";
        }

        $tempDetails = $this->SanctionModel->update_sanction_collection_history();

        if (!empty($tempDetails) && $tempDetails['status'] == 1) {
            $message .= "<br/><br/>Collection History : " . $tempDetails['message'];
        } else {
            $message .= "<br/><br/>Collection History : No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;

        //        $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

        if (!empty($cron_insert_id)) {
            $this->SanctionModel->update_cron_logs($cron_insert_id);
        }
    }

    private function lead_allocation_email_notification($email_to, $customer_name, $screener_name, $screener_mobile, $screener_email) {

        $email_subject = "Loan Application Received | " . BRAND_NAME;

        $html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>' . BRAND_NAME . '</title>
                            </head>
                            <body>
                                <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; border:solid 1px #ddd; font-size:14px; line-height:22px;">
                                    <tr>
                                        <td width="648"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/credit-communication.jpg" alt="header" width="650" height="474" border="0" usemap="#Map" /></td>
                                    </tr>
                                    <tr>
                                        <td width="648">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="center"><table width="95%" border="0" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="60%" align="left"><p style="margin:0px 0px 5px 0px; font-size:19px;"><strong>Dear ' . $customer_name . ',</strong></p></td>
                                                    <td width="40%" align="left">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" valign="top"> Thank you for showing your interest in ' . BRAND_NAME . '. <br/>
                                                        <br/>
                                                        I got your emergency loan application.<br/>
                                                        We recognise your urgency and will prioritise your application.<br/>
                                                        <br/>
                                                        Please have the appropriate paperwork on hand.<br/>
                                                        I will contact you as soon as possible. <br/>
                                                        <br/>
                                                        <b>Regards</b>,<br/>
                                                        ' . $screener_name . '<br/>
                                                        +91-' . $screener_mobile . '<br/>
                                                        Team ' . BRAND_NAME . '<br/></td>
                                                    <td align="left" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size:13px !important; line-height:20px;">
                                                            <tr>
                                                                <td valign="middle" bgcolor="#004A89" style="border-radius:5px;"><span style="background: #004a89;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/Application-Verification.png" width="30" height="30" alt="Application-Verification" /></span></td>
                                                                <td valign="top"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/line.png" width="10" height="55" /></td>
                                                                <td align="left" valign="top"><strong> Application Verification</strong><br />
                                                                    Digital eKyc using Aadhaar & Document Verification.</td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center" valign="top"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/line-height.png" width="2" height="55" alt="lineheight" /></td>
                                                                <td align="center" valign="top">&nbsp;</td>
                                                                <td align="left" valign="top">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td valign="middle" bgcolor="#EC4E4E" style="border-radius:5px;"><span style="background: #ec4e4e;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/Digital-E-Sign.png" width="30" height="30" alt="Digital-E-Sign" /></span></td>
                                                                <td valign="top"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/line.png" width="10" height="55" /></td>
                                                                <td valign="top"><strong>Digital E-Sign</strong><br />
                                                                    E-Sign on Sanction Letter using Aadhaar.</td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center" valign="top"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/line-height.png" width="2" height="55" alt="lineheight" /></td>
                                                                <td align="center" valign="top">&nbsp;</td>
                                                                <td align="center" valign="top">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td valign="middle" bgcolor="#008915" style="border-radius:5px;"><span style="background: #008915;padding:10px;float: left;border-radius: 3px;"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/Get-Loan.png" width="30" height="30" alt="Get-Loan" /></span></td>
                                                                <td valign="top"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/line.png" width="10" height="55" /></td>
                                                                <td valign="top"><strong>Get Loan</strong><br />
                                                                    The loan amount will credit to your bank account in no time. </td>
                                                            </tr>
                                                        </table></td>
                                                </tr>
                                            </table></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="center"><img src="' . WEBSITE_URL . 'public/emailimages/credit-communication/image/contact-details.png" alt="footer" width="577" height="76" border="0" usemap="#Map2" /></td>
                                    </tr>
                                    <tr>
                                        <td align="center">&nbsp;</td>
                                    </tr>
                                </table>
                                <map name="Map" id="Map">
                                    <area shape="rect" coords="14,13,233,67" href="' . WEBSITE_URL . '" target="_blank" />
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

        // $return = $this->middlewareEmail($email_to, $email_subject, $html_message, '', 28, '', $screener_email);
        // return $return;

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        $return_array = common_send_email($email_to, $email_subject, $html_message, "", "$screener_email", "", "", "", "", "");
        return $return_array;
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 99, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@tejasloan.com";

            $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to, "");

            if (!empty($return_array) && $return_array['status'] == 1) {
                $status = $return_array['status'];
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }

            if ($status == 1) {
                $status = $status;
                $error = $return_array['error'];

                $insert_log_array = array();
                $insert_log_array['email_provider'] = $provider_name;
                $insert_log_array['email_type_id'] = $email_type_id;
                $insert_log_array['email_address'] = $email;
                $insert_log_array['email_content'] = addslashes($message);
                $insert_log_array['email_api_status_id'] = $status;
                $insert_log_array['email_errors'] = $error;
                $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

                $this->SanctionModel->emaillog_insert($insert_log_array);
            }

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }
}
