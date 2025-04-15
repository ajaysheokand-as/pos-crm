<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ImportController extends CI_Controller {

    public function __construct() {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");

        $this->load->model('Task_Model', 'Tasks');

        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $this->load->view('Export/import');
    }

    public function sampleCSV() {

        $CSVarray = array(
            'sr_no' => 'sr_no',
            'name' => 'name',
            'mobile' => 'mobile',
            'alternate_mobile' => 'alternate_mobile',
            'email' => 'email',
            'alternate_email' => 'alternate_email',
            'designation' => 'designation',
            'company_name' => 'company_name',
            'state_name' => 'state_name',
            'city_name' => 'city_name',
            'pincode' => 'pincode',
            'gender' => 'gender',
            'loan_amount' => 'loan_amount',
            'obligations' => 'obligations',
            'monthly_income' => 'monthly_income',
            'pancard' => 'pancard',
            'dob' => 'dob',
            'coordinates' => 'coordinates',
            'utm_source' => 'utm_source',
            'utm_campaign' => 'utm_campaign',
            'coupon' => 'coupon',
            'rejectd_flag' => '',
        );

        $header = array_keys($CSVarray);

        $filename = 'import_sample' . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $file = fopen('php://output', 'w');
        fputcsv($file, $header);
        fclose($file);

        return redirect('adminViewUser', 'refresh');
    }

    public function importData() {

        $ip = $_SERVER['REMOTE_ADDR'];

        date_default_timezone_set('Asia/Kolkata');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');

            if ($this->form_validation->run() == FALSE) {

                $this->session->set_userData('err', 'Please Upload Valid CSV File!');

                $this->index();
            } else {

                $this->load->library('csvimport');

                $regex_name = '/[^A-Za-z ]/';
                $regex_utm_source = '/[^A-Za-z0-9 \-\.\_]/';
                $regex_phone = '/[^0-9]{10}$/';
                $regex_pin = '/[^0-9]{6}$/';

                $file_data = $this->csvimport->get_array($_FILES["csv_file"]["tmp_name"]);
                $insert_count = 0;
                $rejected_count = 0;
                if (!empty($file_data)) {
                    if (count($file_data) <= 3000) {
                        foreach ($file_data as $row) {
                            $fullname = preg_replace($regex_name, '', $row["name"]);
                            $pancard = trim(strtoupper($row["pancard"]));
                            $mobile = preg_replace($regex_phone, '', $row["mobile"]);
                            $alternate_mobile = preg_replace($regex_phone, '', $row["alternate_mobile"]);
                            $email = !empty($row["email"]) ? $row["email"] : "";
                            $alternate_email = !empty($row["alternate_email"]) ? $row["alternate_email"] : "";
                            $coordinates = !empty($row['coordinates']) ? $row['coordinates'] : "";
                            $monthly_income = intval($row['monthly_income']);
                            $loan_amount = intval($row['loan_amount']);
                            $obligations = intval($row['obligations']);
                            $gender = preg_replace($regex_name, '', $row["gender"]);
                            $gender = !empty($gender) ? strtoupper($gender) : "";
                            $utm_source = preg_replace($regex_utm_source, '', $row["utm_source"]);
                            $utm_source = !empty($utm_source) ? strtoupper($utm_source) : "";
                            $utm_campaign = preg_replace($regex_utm_source, '', $row["utm_campaign"]);
                            $utm_campaign = !empty($utm_campaign) ? strtoupper($utm_campaign) : "";
                            $pincode = (preg_replace($regex_pin, '', $row["pincode"]));
                            $coupon = !empty($row['coupon']) ? $row['coupon'] : '';
                            $city_name = !empty($row['city_name']) ? $row['city_name'] : "";
                            $state_name = !empty($row['state_name']) ? $row['state_name'] : "";
                            $designation = !empty($row['designation']) ? trim(strtoupper($row["designation"])) : "";
                            $company_name = !empty($row['company_name']) ? trim(strtoupper($row["company_name"])) : "";

                            $rejection_flag = 0;

                            if (!empty($row['rejectd_flag']) && $row['rejectd_flag'] == 1) {
                                $rejection_flag = 1;
                            }


                            $dob = "";
                            if (!empty($row["dob"])) {
                                $day = date('d', strtotime($row["dob"]));
                                $month = date('m', strtotime($row["dob"]));
                                $year = date('Y', strtotime($row["dob"]));
                                $dateOfBirth = $year . '-' . $month . '-' . $day;
                                $dob = ($dateOfBirth) ? $dateOfBirth : "";
                            }

                            if (empty($pancard) || !preg_match("/^([A-Za-z]{5})+([0-9]{4})+([A-Za-z]{1})$/", $pancard)) {
                                $pancard = "";
                            }

                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $email = "";
                            }


                            if (!filter_var($alternate_email, FILTER_VALIDATE_EMAIL)) {
                                $alternate_email = "";
                            }

                            if (!in_array($gender, array("MALE", "FEMALE"))) {
                                $gender = "";
                            }


                            if (!empty($fullname) && !empty($mobile) && !empty($utm_source) && !empty($pancard) && !empty($pincode)) {

                                $fullname_array = common_parse_full_name($fullname);

                                $first_name = $fullname_array['first_name'];
                                $middle_name = !empty($fullname_array['middle_name']) ? $fullname_array['middle_name'] : '';
                                $sur_name = !empty($fullname_array['last_name']) ? $fullname_array['last_name'] : "";
                                $city_id = "";
                                $state_id = "";

                                if (!empty($pincode)) { //If pincode available in excel
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


                                $insertDataLeads = array(
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
                                    'lead_entry_date' => date("Y-m-d"),
                                    'created_on' => date("Y-m-d H:i:s"),
                                    'source' => "Import",
                                    'ip' => $ip,
                                    'status' => "LEAD-NEW",
                                    'stage' => "S1",
                                    'lead_status_id' => 1,
                                    'qde_consent' => "Y",
                                    'lead_data_source_id' => 20,
                                    'coordinates' => $coordinates,
                                    'utm_source' => $utm_source,
                                    'utm_campaign' => $utm_campaign,
                                    'promocode' => $coupon,
                                );

                                if (strtoupper(trim($utm_source)) == "C4C") {
                                    $insertDataLeads['lead_data_source_id'] = 21;
                                    $insertDataLeads['source'] = 'C4C';
                                    $insertDataLeads['utm_source'] = 'IMPORT';
                                } else if (strtoupper(trim($utm_source)) == "REFCASE") {
                                    $insertDataLeads['lead_data_source_id'] = 27;
                                    $insertDataLeads['source'] = 'refcase';
                                    $insertDataLeads['utm_source'] = 'IMPORT';
                                }

                                if ($rejection_flag == 1) {
                                    $insertDataLeads['lead_rejected_reason_id'] = 52;
                                    $insertDataLeads['lead_rejected_datetime'] = date("Y-m-d H:i:s");
                                    $insertDataLeads['status'] = 'REJECT';
                                    $insertDataLeads['stage'] = 'S9';
                                    $insertDataLeads['lead_status_id'] = 9;
                                    $rejected_count++;
                                }

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
                                        'dob' => $dob,
                                        'mobile' => $mobile,
                                        'alternate_mobile' => $alternate_mobile,
                                        'email' => $email,
                                        'alternate_email' => $alternate_email,
                                        'pancard' => $pancard,
                                        'state_id' => $state_id,
                                        'city_id' => $city_id,
                                        'cr_residence_pincode' => $pincode,
                                        'created_date' => date("Y-m-d H:i:s")
                                    );

                                    if (empty($dob)) {
                                        unset($insertLeadsCustomer['dob']);
                                    }

                                    $this->db->insert('lead_customer', $insertLeadsCustomer);

                                    $insert_customer_employement = [
                                        'lead_id' => $lead_id,
                                        'emp_email' => $alternate_email,
                                        'monthly_income' => $monthly_income,
                                        'employer_name' => $company_name,
                                        'emp_designation' => $designation,
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
                                    } else if (!empty($mobile)) {

                                        $cif_query = $this->db->select('*')->where("cif_mobile = '$mobile'")->from('cif_customer')->get();

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

                                            if (empty($pancard)) {
                                                $update_data_lead_customer['pancard'] = $cif_result->cif_pancard;
                                            }
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

                                            if (empty($pancard)) {
                                                $update_data_leads['pancard'] = $cif_result->cif_pancard;
                                            }

                                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                                        }
                                    }

                                    // if ($rejection_flag != 1) {

                                    //     require_once (COMPONENT_PATH . 'CommonComponent.php');

                                    //     $CommonComponent = new CommonComponent();

                                    //     $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                                    //     if ($return_eligibility_array['status'] == 2) {
                                    //         $rejected_count++;
                                    //     }
                                    // }
                                }
                            }
                        }
                        $this->session->set_flashdata('msg', 'Data Import Successfully! | Total Records - ' . count($file_data) . " | Save Records - " . $insert_count . " | Rejected Records - " . $rejected_count);
                        return redirect('ViewImportData', 'refresh');
                    } else {
                        $this->session->set_flashdata('err', 'Please upload 3000 leads at time!');
                        return redirect('ViewImportData', 'refresh');
                    }
                } else {
                    return redirect('ViewImportData', 'refresh');
                }
            }
        } else {
            return redirect('ViewImportData', 'refresh');
        }
    }

}
