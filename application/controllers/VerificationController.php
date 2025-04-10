<?php

defined('BASEPATH') or exit('No direct script access allowed');

class VerificationController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo CAM";

    public function __construct() {
        parent::__construct();
        $this->load->model('Leadmod', 'Leads');
        $this->load->model('Task_Model', 'Tasks');

        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");

        $login = new IsLogin();
        $login->index();
    }

    public function index($stage) {

        $cuser_id = $_SESSION['isUserSession']['user_id'];
        $this->load->library("pagination");

        if ($this->uri->segment(1) == 'office-verification') {
            $conditions = "LD.company_id='" . company_id . "' AND LD.product_id='" . product_id . "' AND LD.lead_fi_scm_office_assign_user_id=" . $cuser_id . " and lead_fi_office_status_id=1"; //  AND LD.stage IN('S5','S6')
        } else {
            $conditions = "LD.company_id='" . company_id . "' AND LD.product_id='" . product_id . "' AND LD.lead_fi_scm_residence_assign_user_id=" . $cuser_id . " and lead_fi_residence_status_id=1"; //  AND LD.stage IN('S5','S6')
        }


        $url = (base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2));
        $data['totalcount'] = $this->Tasks->countLeads($conditions);
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $data['totalcount'];
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;
        $config['full_tag_open'] = '<div class="pagging text-right"><nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav></div>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '<span aria-hidden="true"></span></span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['pageURL'] = $url;

        $data['leadDetails'] = $this->Tasks->index($conditions, $config["per_page"], $page);
        //        echo "<pre>";print_r( $data['leadDetails']->result_array() ); die;
        $data["links"] = $this->pagination->create_links();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();

        // echo "=====> ".$_SESSION['isUserSession']['labels'];; die;
        if ($_SESSION['isUserSession']['labels'] == 'CO2') {
            $data["collver"] = 'collectionuserlist';
        } else {
            $data["uqickCall"] = '';
        }

        //        echo "<pre>";print_r( $data['leadDetails'] ); die;

        $this->load->view('Tasks/GetLeadTaskList', $data);
    }

    function assignLeadToCollectionuser() {
        $data = $_POST['data'];
        $lead_id = $data['lead_id'];
        $user_id = $data['user_id']; //type
        $type = $data['type']; //type

        $upd_id = $lead_id;
        $table = 'tbl_verification';
        $column = "lead_id";

        date_default_timezone_set("Asia/Kolkata");
        $currentdate = date('Y-m-d H:i:s');

        if ($type == 'office') {
            $updateData = array(
                'office_cpv_allocated_to' => $user_id,
                'office_col_status' => 1,
                'office_cvp_allocated_on' => $currentdate
            );
            $updateData1 = array("lead_fi_executive_office_assign_user_id" => $user_id);
        } else {
            $updateData = array(
                'residece_cpv_allocated_to' => $user_id,
                'office_residence_status' => 1,
                'residence_cpv_allocated_on' => $currentdate
            );
            $updateData1 = array("lead_fi_executive_residence_assign_user_id" => $user_id);
        }

        $res = $this->Leadmod->globel_update($table, $updateData, $upd_id, $column);

        $res = $this->Leadmod->globel_update('leads', $updateData1, $lead_id, 'lead_id');
    }

    public function getPanNoOnDeteail($leadID) {
        $return_status = 0;
        $return_error = '';
        $return_data = '';
        $lead_id = intval($this->encrypt->decode($leadID));
        $pancard = $this->input->post('pan_number');
        $action = $this->input->post('action');
        if (!empty($lead_id)) {
            $return_data = $this->db->select('customer_lead_id,pancard,first_name')->where('customer_lead_id', $lead_id)->from('lead_customer')->get()->row();
            if ($return_data->pancard == $pancard) {
                $return_error = "Pan number already exists.<br>Please use another pan number!";
                $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data);
            } else {
                if ($action == '1') {
                    require_once(COMPONENT_PATH . 'CommonComponent.php');
                    $CommonComponent = new CommonComponent();
                    $request_array['dual_pancard'] = $pancard;
                    $pan_veri_return = $CommonComponent->call_pan_verification_api($lead_id, $request_array);
                    //echo '<pre>';print_r($pan_veri_return);die;
                    if ($pan_veri_return['status'] == 1) {
                        if ($pan_veri_return['pan_valid_status'] == 2) {
                            $return_status = 1;
                            $return_error = "PAN Verified";
                            $resData = $pan_veri_return['data'];
                        } else {
                            $return_status = 0;
                            $return_error = "Customer Name does not matched with PAN Detail. Please check the application log.";
                        }
                    } else {
                        $return_status = 0;
                        $return_error = trim($pan_veri_return['errors']);
                    }
                    /*
                      $lead_followup_list = $this->db->select('*')->where(['lead_id'=>$lead_id])->from('lead_followup')->order_by('id','DESC')->get()->row();
                      if(!empty($lead_followup_list->id) && $lead_followup_list->id > 0) {
                      $lead_followup_remark = "Comming soon";
                      $insertLeadFollowup = [
                      'customer_id'=>$lead_followup_list->customer_id,
                      'lead_id'=>$lead_id,
                      'user_id'=>$_SESSION['isUserSession']['user_id'],
                      'remarks'=>$lead_followup_remark,
                      'status'=>$lead_followup_list->status,
                      'stage'=>$lead_followup_list->stage,
                      'created_on'=>date('Y-m-d H:i:s'),
                      'lead_followup_status_id' => $lead_followup_list->lead_followup_status_id
                      ];
                      $inserted_id = $this->db->insert('lead_followup',$insertLeadFollowup);
                      if ($inserted_id) {
                      $return_array['status'] = 1;
                      }
                      }
                     */
                    //$return_error = "Successfully!";
                } else {
                    $return_status = 1;
                    $return_error = "Right";
                }
                $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data, 'respone_data' => $resData);
            }
        } else {
            $return_error = "Invalid request. Please try again.";
            $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data);
        }
        echo json_encode($data);
    }


    public function checkDualPanVerification($leadID) {
        $return_status = 0;
        $return_error = '';
        $lead_data = $return_data = [];
        $lead_id = intval($this->encrypt->decode($leadID));
        $pancard = $this->input->post('pan_number');
        $action = 1;
        //$action = $this->input->post('action');

        if (!empty($lead_id)) {

            $return_data = $this->db->select('customer_lead_id,pancard,first_name,middle_name,sur_name,dob')->where('customer_lead_id', $lead_id)->from('lead_customer')->get()->row();
            if ($return_data && $return_data->pancard == $pancard) {

                $return_error = "Pan number already exists.<br>Please use another pan number!";
                $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data);
            } else {

                $first_name = !empty($return_data->first_name) ? trim(strtoupper($return_data->first_name)) : "";
                $middle_name = !empty($return_data->middle_name) ? " " . trim(strtoupper($return_data->middle_name)) : "";
                $sur_name = !empty($return_data->sur_name) ? " " . trim(strtoupper($return_data->sur_name)) : "";
                $customer_full_name = $first_name . $middle_name . $sur_name;
                $dob = !empty($return_data->dob) ? trim(strtoupper($return_data->dob)) : "";

                $lead_data['name'] = $customer_full_name;
                $lead_data['dob'] = $dob;
                $lead_data['pancard'] = !empty($return_data->pancard) ? trim(strtoupper($return_data->pancard)) : "";

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.signzy.app/api/v3/pan/fetch',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode(array("panNumber" => $pancard)),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                        'Content-Type: application/json'
                    ),
                ));
                $apiResponseJson = curl_exec($curl);
                $curlError = curl_error($curl);
                curl_close($curl);

                $apiResponseArr = json_decode($apiResponseJson, true);

                file_put_contents('response.txt', "\n\n Time: " . date("F j, Y, g:i a") . " - " . $apiResponseJson, FILE_APPEND);

                if (is_array($apiResponseArr) && isset($apiResponseArr['result']) && !empty($apiResponseArr['result'])) {
                    $return_data = array(); // Ensure $return_data is initialized as an array
                    $return_data['pancard'] = isset($apiResponseArr['result']['number']) ? strtoupper($apiResponseArr['result']['number']) : '';
                    $return_data['name'] = isset($apiResponseArr['result']['name']) ? strtoupper($apiResponseArr['result']['name']) : '';
                    $return_data['dob'] = isset($apiResponseArr['result']['dob']) ? $apiResponseArr['result']['dob'] : '';
                    $return_data['fatherName'] = isset($apiResponseArr['result']['fatherName']) ? strtoupper($apiResponseArr['result']['fatherName']) : '';

                    $return_status = 1;
                    $return_error = "PAN Verified";
                    if (strcasecmp($return_data['name'], $customer_full_name) == 0) {
                        $return_error = "Name matched";
                    }
                }

                $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data, 'lead_data' => $lead_data);
                //print_r($data); // Corrected semicolon
            }
        } else {
            $return_error = "Invalid request. Please try again.";
            $data = array('status' => $return_status, 'message' => $return_error, 'data' => $return_data);
        }
        echo json_encode($data);
    }


    public function getVerificationDetails($leadID) {

        $return_status = 0;
        $return_error = '';
        $return_data = '';

        $lead_id = intval($this->encrypt->decode($leadID));

        if (!empty($lead_id)) {

            $lead_details = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, customer_id, status, stage, lead_status_id, lead_credit_assign_user_id', 'leads');

            if ($lead_details->num_rows() > 0) {

                $leadDetails = $lead_details->row();
                $select_lead_customer = "aadhaar_ocr_verified_on, pancard_ocr_verified_status, pancard_ocr_verified_on, customer_ekyc_request_ip, customer_digital_ekyc_flag, customer_ekyc_request_initiated_on, customer_docs_available, customer_digital_ekyc_done_on, customer_lead_id, customer_bre_run_flag, aadhaar_ocr_verified_status";
                $query_cust = $this->db->select($select_lead_customer)->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

                $lead_customer = $query_cust->row();

                $this->load->model('Verification_Model', 'Verification');

                $getVerificationdata = $this->Verification->getVerificationData($lead_id);

                $lead_is_mobile_verified = '-';
                if (!empty($getVerificationdata['lead_is_mobile_verified']) && isset($getVerificationdata['lead_is_mobile_verified']) != null) {
                    $lead_is_mobile_verified = "YES";
                }

                $alternate_mobile_verified = '-';
                if (isset($getVerificationdata['alternate_mobile_verified']) && isset($getVerificationdata['alternate_mobile_verified']) != null) {
                    $alternate_mobile_verified = "YES";
                }

                $personal_email_isVerified = '-';
                $email_verified_on = '-';
                if ($getVerificationdata['email_verified_status'] == 'YES') {
                    $personal_email_isVerified = $getVerificationdata['email_verified_status'];
                    $email_verified_on = date("d-m-Y H:i:s", strtotime($getVerificationdata['email_verified_on']));
                } else if (!empty($getVerificationdata['email']) && ((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                    $personal_email_isVerified = '<input type="checkbox" class="checkbox-verif" id="personalEmailVerification" name="personalEmailVerification" onclick="email_verification_api_call(' . $lead_id . ', 1)" autocomplete="off" >';
                }

                $alternate_email_isVerified = '-';
                $alternate_email_verified_on = '-';
                if ($getVerificationdata['alternate_email_verified_status'] == 'YES') {
                    $alternate_email_isVerified = $getVerificationdata['alternate_email_verified_status'];
                    $alternate_email_verified_on = date("d-m-Y H:i:s", strtotime($getVerificationdata['alternate_email_verified_on']));
                } else if (!empty($getVerificationdata['alternate_email']) && in_array(agent, ['CR2', 'CR3']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && $lead_customer->customer_bre_run_flag == 0) {
                    $alternate_email_isVerified = "<input type='checkbox' class='checkbox-verif' id='officeEmailVerification' name='officeEmailVerification' onclick='email_verification_api_call(&quot;" . $this->encrypt->encode($lead_id) . "&quot;, 2)' autocomplete='off' >";
                }

                // $office_email_verified_on = '-';
                // if (isset($getVerificationdata['office_email_verified_on']) == '' || isset($getVerificationdata['office_email_verified_on']) == '-') {
                //     $office_email_verified_on = ($getVerificationdata['office_email_verified_on'] ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_email_verified_on'])) : '-');
                // }

                // $aadhar_verified = '-';
                // if (isset($getVerificationdata['aadhar_verified']) == '' || isset($getVerificationdata['aadhar_verified']) == '-') {
                //     $aadhar_verified = "YES";
                // }

                // $app_download_on = '-';
                // if (isset($getVerificationdata['app_download_on']) == '' || isset($getVerificationdata['app_download_on']) == '-') {
                //     $app_download_on = "YES";
                // }


                // $init_mobile_verification = '-';
                // if (isset($getVerificationdata['init_mobile_verification']) == 'YES') {
                //     $init_mobile_verification = "checked disabled";
                // }

                // $mobile_otp = '-';
                // if (isset($getVerificationdata['mobile_otp']) == "" || isset($getVerificationdata['mobile_otp']) == "-") {
                //     $mobile_otp = "YES";
                // }

                // $init_residence_cpv = '-';
                // if (in_array(agent, ['CR2']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"])) {
                //     if ($getVerificationdata['init_residence_cpv'] == 'YES') {
                //         $init_residence_cpv_assign = "checked disabled";
                //     } else {
                //         $init_residence_cpv_assign = "";
                //     }

                //     $init_residence_cpv = '<input type="checkbox"' . $init_residence_cpv_assign . '  name="residenceCPV" id="residenceCPV" class="checkbox-verif" onclick="initiateFiCPV(' . $lead_id . ', 1)" value=""  >';
                // }

                // $init_office_cpv = '-';
                // if (in_array(agent, ['CR2']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"])) {
                //     if ($getVerificationdata['init_office_cpv'] == 'YES') {
                //         $init_office_cpv_assign = "checked disabled";
                //     } else {
                //         $init_office_cpv_assign = "";
                //     }
                //     $init_office_cpv = '<input type="checkbox"' . $init_office_cpv_assign . '  name="officeCPV" id="officeCPV" class="checkbox-verif" value=""   onclick="initiateFiCPV(' . $lead_id . ', 2)">';
                // }

                // $scm_fi_res_name = '-';
                // if (!empty($getVerificationdata['scm_fi_res_name']) && $getVerificationdata['scm_fi_res_name'] != null) {
                //     $scm_fi_res_name = $getVerificationdata['scm_fi_res_name'];
                // }

                // $scm_fi_office_user = '-';
                // if (!empty($getVerificationdata['scm_fi_office_user']) && $getVerificationdata['scm_fi_office_user'] != null) {
                //     $scm_fi_office_user = $getVerificationdata['scm_fi_office_user'];
                // }

                // $residence_initiated_on = '-';
                // if (!empty($getVerificationdata['residence_initiated_on']) && $getVerificationdata['residence_initiated_on'] != null) {
                //     $residence_initiated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['residence_initiated_on']));
                // }

                // $office_initiated_on = '-';
                // if (!empty($getVerificationdata['office_initiated_on']) && $getVerificationdata['office_initiated_on'] != null) {
                //     $office_initiated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['office_initiated_on']));
                // }

                // $rm_fi_res_name = '-';
                // if (!empty($getVerificationdata['rm_fi_res_name']) && $getVerificationdata['rm_fi_res_name'] != null) {
                //     $rm_fi_res_name = $getVerificationdata['rm_fi_res_name'];
                // }

                // $rm_fi_office_user = '-';
                // if (!empty($getVerificationdata['rm_fi_office_user']) && $getVerificationdata['rm_fi_office_user'] != null) {
                //     $rm_fi_office_user = $getVerificationdata['rm_fi_office_user'];
                // }

                // $residence_cpv_allocated_on = '-';
                // if (!empty($getVerificationdata['residence_cpv_allocated_on']) && $getVerificationdata['residence_cpv_allocated_on'] != null) {
                //     $residence_cpv_allocated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['residence_cpv_allocated_on']));
                // }

                // $office_cvp_allocated_on = '-';
                // if (!empty($getVerificationdata['office_cvp_allocated_on']) && $getVerificationdata['office_cvp_allocated_on'] != null) {
                //     $office_cvp_allocated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['office_cvp_allocated_on']));
                // }

                $aadhaar_doc_details = $this->Tasks->select('docs_master_id in (1,2) AND docs_active = 1 AND docs_deleted = 0 AND lead_id=' . $lead_id, 'lead_id', 'docs');

                $aadhaar_ocr_verified = '-';

                if ($aadhaar_doc_details->num_rows() > 0) {
                    if ($lead_customer->aadhaar_ocr_verified_status == 1) {
                        $aadhaar_ocr_verified = "Aadhaar OCR Verified: YES";
                        $aadhaar_ocr_verified .= "<br>Aadhaar OCR Verified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->aadhaar_ocr_verified_on));
                    } else if (((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                        $aadhaar_ocr_verified = '<input type="checkbox" class="checkbox-verif" id="aadhaarOcrVerification" name="aadhaarOcrVerification" onclick="ocr_verification_api_call(&quot;' . $this->encrypt->encode($lead_id) . '&quot;, 1)" autocomplete="off" >';
                    }
                }

                $pan_doc_details = $this->Tasks->select('docs_master_id in (4) AND docs_active = 1 AND docs_deleted = 0 AND lead_id=' . $lead_id, '*', 'docs');

                $pan_ocr_verified = '-';

                if ($pan_doc_details->num_rows() > 0) {
                    if ($lead_customer->pancard_ocr_verified_status == 1) {
                        $pan_ocr_verified = "PAN OCR Verified: YES";
                        $pan_ocr_verified .= "<br>PAN OCR Verified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_ocr_verified_on));
                    } else if (((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                        $pan_ocr_verified = '<input type="checkbox" class="checkbox-verif" id="panOcrVerification" name="panOcrVerification" onclick="ocr_verification_api_call(&quot;' . $this->encrypt->encode($lead_id) . '&quot;, 2)" autocomplete="off" >';
                    }
                }

                $pan_verified_str = "-";

                if (!empty($lead_customer->customer_ekyc_request_ip) && $lead_customer->customer_docs_available == 2) {
                    $pan_verified_str = "PAN Verified on " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_ocr_verified_on));
                }

                $esgin_sanction_letter = '-';
                if (!empty($getVerificationdata['cam_sanction_letter_esgin_file_name'])) {
                    $esgin_sanction_letter = "<a href='" . base_url('download-document-file/' . $lead_id . '/3') . "' target='_blank'>Download</a>";
                    $esgin_sanction_letter .= "<br/>eSigned On : " . date('d-m-Y H:i:s', strtotime($getVerificationdata['cam_sanction_letter_esgin_on']));
                    $esgin_sanction_letter .= "<br/>eSigned IP : " . $getVerificationdata['cam_sanction_letter_ip_address'];
                } else if ($leadDetails->lead_status_id == 12 && !empty($getVerificationdata['cam_sanction_letter_ip_address']) && agent == "CR3") {
                    $esgin_sanction_letter = "<a href='" . base_url('sanction-esign-response') . "?refstr=$leadID" . "' target='_blank'>Check Status</a>";
                }

                $aadhar_kyc_verified_str = "E-KYC Verified : NO";

                if ($lead_customer->customer_digital_ekyc_flag == 1 && !empty($lead_customer->customer_ekyc_request_initiated_on)) {
                    $aadhar_kyc_verified_str = "E-KYC Verified : YES";
                }

                if ($lead_customer->customer_digital_ekyc_flag == 1 && !empty($lead_customer->customer_ekyc_request_initiated_on)) {
                    $aadhar_kyc_verified_str .= "<br/>E-KYC Requested On : " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_ekyc_request_initiated_on));
                }

                if ($lead_customer->customer_digital_ekyc_flag == 1) {
                    $aadhar_kyc_verified_str .= "<br/>E-KYC Verified on : " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_digital_ekyc_done_on));
                }

                // if (in_array($lead_customer->customer_selfie_aadhaar_photo_match_flag, array(1, 2))) {
                //     $face_match_str = "Selfie and Aadhaar Result : ";
                //     if ($lead_customer->customer_selfie_aadhaar_photo_match_flag == 1) {
                //         $face_match_str .= " Matched";
                //     } else if ($lead_customer->customer_selfie_aadhaar_photo_match_flag == 2) {
                //         $face_match_str .= " Parital Matched";
                //     }

                //     $face_match_str .= "<br>Verified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_selfie_aadhaar_photo_match_verified_on));
                // } else if (((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                //     $face_match_str = '<input type="checkbox" class="checkbox-verif" id="facematchVerification" name="facematchVerification" onclick="face_match_verification_api_call(&quot;' . $this->encrypt->encode($lead_id) . '&quot;, 1)" autocomplete="off" >';
                // }

                $br_res_address_match_str = '-';

                // if (in_array($lead_customer->customer_bureau_and_residence_address_match_flag, array(1, 2, 3))) {
                //     $br_res_address_match_str = "Result : ";
                //     if ($lead_customer->customer_bureau_and_residence_address_match_flag == 1) {
                //         $br_res_address_match_str .= "Matched";
                //     } else if ($lead_customer->customer_bureau_and_residence_address_match_flag == 2) {
                //         $br_res_address_match_str .= "Parial Matched";
                //     } else if ($lead_customer->customer_bureau_and_residence_address_match_flag == 3) {
                //         $br_res_address_match_str .= "Not-Matched";
                //     }
                //     $br_res_address_match_str .= "<brVerified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_bureau_and_residence_address_match_verified_on));
                // } else if (((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                //     $br_res_address_match_str = '<input type="checkbox" class="checkbox-verif" id="BRaddressmatchVerification" name="BRaddressmatchVerification" onclick="address_match_verification_api_call(&quot;' . $this->encrypt->encode($lead_id) . '&quot;, 2)" autocomplete="off" >';
                // }

                $br_adhr_address_match_str = '-';

                // if (in_array($lead_customer->customer_bureau_and_aadhaar_address_match_flag, array(1, 2, 3))) {

                //     $br_adhr_address_match_str = "Result : ";
                //     if ($lead_customer->customer_bureau_and_aadhaar_address_match_flag == 1) {
                //         $br_adhr_address_match_str .= "Matched";
                //     } else if ($lead_customer->customer_bureau_and_aadhaar_address_match_flag == 2) {
                //         $br_adhr_address_match_str .= "Parial Matched";
                //     } else if ($lead_customer->customer_bureau_and_aadhaar_address_match_flag == 3) {
                //         $br_adhr_address_match_str .= "Not-Matched";
                //     }
                //     $br_adhr_address_match_str .= "<br>Verified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_bureau_and_aadhaar_address_match_verified_on));
                // } else if (((in_array(agent, ['CR2', 'CR3']) && user_id == $leadDetails->lead_credit_assign_user_id) || agent == "CA") && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && empty($lead_customer->customer_bre_run_flag)) {
                //     $br_adhr_address_match_str = '<input type="checkbox" class="checkbox-verif" id="BAaddressmatchVerification" name="BAaddressmatchVerification" onclick="address_match_verification_api_call(&quot;' . $this->encrypt->encode($lead_id) . '&quot;, 4)" autocomplete="off" >';
                // }

                // $api_verification_logs = $this->db->select('*')->where(['poi_veri_lead_id' => $lead_id])->from('api_poi_verification_logs')->where('poi_other_pan_veri_flag', 1)->order_by('poi_veri_id', 'DESC')->get()->row();
                // $data_respone = json_decode($api_verification_logs->poi_veri_response);
                // if (!empty($data_respone->response->result)) {
                //     $resData = $data_respone->response->result;
                //     $number = 'Number: ' . $resData->number;
                //     $name = 'Name: ' . $resData->name;
                //     $fatherName = 'Father Name: ' . $resData->fatherName;
                // } else {
                //     $number = '-';
                // }

                //                 $consent_status = '-';
                // //                if (in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && (agent == "CA" || (in_array(agent, ['CR2']) && user_id == $leadDetails->lead_credit_assign_user_id))) {
                //                 if ($lead_customer->customer_account_aggregator_consent_verify_flag == 1) {
                //                     $consent_status = "Consent Status: APPROVED";
                //                     $consent_status .= "<br />Consent Verified on: " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_account_aggregator_consent_verify_datetime));
                //                     if ($lead_customer->customer_account_aggregator_bank_statement_fetch_flag == 1) {
                //                         $consent_status .= "<br />Bank Statement Fetched: YES";
                //                         $consent_status .= "<br />Bank Statement Fetched on: " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_account_aggregator_bank_statement_fetch_datetime));
                //                     } else {
                //                         $consent_status .= "<br />Bank Statement Fetched: NO";
                //                         $consent_status .= '<br />Bank Statement Fetched on: -';
                //                     }
                //                 } else if ($lead_customer->customer_account_aggregator_consent_verify_flag == 2) {
                //                     $consent_status = "Consent Status: PENDING";
                //                     $consent_status .= '<br />Consent Verified on: -';
                //                 } else if (in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && (agent == "CA" || (in_array(agent, ['CR2']) && user_id == $leadDetails->lead_credit_assign_user_id))) {
                //                     $consent_status = '<input type="checkbox" class="checkbox-verif" id="send_account_aggregator_link" name="send_account_aggregator_link" onclick="send_account_aggregator_url(\'' . $leadID . '\')" autocomplete="off" >';
                //                 }
                //                }
                $return_data = '
                    <div class="table-responsive">
                         <table class="table table-bordered">
                            <tr>
                                <th>Mobile verified</th>
                                <td>' . $lead_is_mobile_verified . '</td>
                                <th>Alternate Mobile verified</th>
                                <td>' . $alternate_mobile_verified . '</td>
                            </tr>

                             <tr>
                                <th>Office Email Verification</th>
                                <td>' . $alternate_email_isVerified . '</td>
                                <th>Office Email Verified On </th>
                                <td>' . $alternate_email_verified_on . '</td>
                            </tr>

                            <tr>
                                <th>Personal Email Verification</th>
                                <td>' . $personal_email_isVerified . '</td>
                                <th>Personal Email Verified On </th>
                                <td>' . $email_verified_on . '</td>
                            </tr>


                            <tr>
                                <th>PAN Verification</th>
                                <td>' . $pan_verified_str . '</td>
                                <th>Digital E-KYC Verified</th>
                                <td>' . $aadhar_kyc_verified_str . '</td>
                            </tr>

                            <tr>
                                <th>Aadhaar OCR Verification</th>
                                <td>' . $aadhaar_ocr_verified . '</td>
                                <th>PAN OCR Verification</th>
                                <td>' . $pan_ocr_verified . '</td>
                            </tr>



							<tr>
                                <th>Other Dual PAN Number&nbsp;<span class="required_Fields">*</span></th>
                                <td><input type="hidden" name="lead_id" id="lead_id" value="' . $this->encrypt->encode($leadDetails->lead_id) . '"><input type="text" name="pan_number" autocomplete="off" class="form-control inputField" id="dual_pan_number" placeholder="Enter Your PAN Number." maxlength="12"  value="" required="" oninput="this.value = this.value.toUpperCase()" /><span onclick="validatePanNumber()" style="color: #5000ff;line-height: 0px;display: block;text-align: right;cursor: pointer;">Click to Verify</span><span id="errorpancard"></span></td>
                                <th>Pan number</th>
                                <td><span id="datapancard">-</span></td>
                            </tr>
							<tr><th colspan="4">Dual Pancard Details</th></tr>
							<tr>
							    <th id="respone_data_lead" colspan="2"></th>
								<td id="respone_data_pan" colspan="2"></td>
							</tr>
                        </table>

                    </div>';


                $return_status = 1;
            } else {
                $return_error = "Your request is not matched with our database.";
            }
        } else {
            $return_error = "Invalid request. Please Try Again.";
        }

        $data = array('status' => $return_status, 'errors' => $return_error, 'data' => $return_data);
        echo json_encode($data);
    }



    public function email_verification_api_call() {
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        // $lead_id = $this->encrypt->decode($this->input->post('lead_id'));
        $lead_id = $this->input->post('lead_id');
        $email_verification_type = $this->input->post('email_verification_type');
        $flag = $this->input->post('flag');

        if (!empty($lead_id)) {

            // $lead_details = $this->Tasks->select(['customer_lead_id' => $lead_id], 'customer_lead_id', 'lead_customer');
            $lead_details = $this->db->get_where('lead_customer', ['customer_lead_id' => $lead_id]);



            if ($lead_details->num_rows() > 0) {

                $api_response = array();

                if ($flag == "YES" && in_array($email_verification_type, array(1, 2))) {
                    if ($email_verification_type == 2) {
                        require_once(COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();
                        $office_email_return = $CommonComponent->call_office_email_verification_api($lead_id);
                        $api_response['status'] = $office_email_return['status'];
                        $api_response['email'] = $office_email_return['email'];
                        $api_response['error_msg'] = $office_email_return['errors'];

                        $api_response['email_validate'] = "NO";

                        if ($office_email_return['email_validate_status'] == 1) {
                            $api_response['email_validate'] = 'YES';
                        }
                    } else {
                        // $this->load->helper('integration/payday_email_verification_api_helper');
                        require_once(COMPONENT_PATH . 'CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        // $method_name = 'SIGNZY_EMAIL_VALIDATE';
                        $request_array = array();
                        $request_array['email_type'] = $email_verification_type;
                        $api_response = $CommonComponent->call_email_verification_api($lead_id, $request_array);
                    }




                    if (!empty($api_response['status']) && $api_response['status'] == 1) {

                        if ($api_response['email_validate'] == "YES") {
                            $responseArray['success_msg'] = "Email Validation : Email verified successfully. | Email : " . $api_response['email'];
                        } else {
                            $responseArray['error_msg'] = "Email Validation : Email validation failed. | Email : " . $api_response['email'];
                        }
                    } else {
                        $responseArray['error_msg'] = "Email Validation : " . $api_response['error_msg'] . " | Email : " . $api_response['email'];
                    }
                } else {
                    $responseArray['error_msg'] = "Somethig went wrong. Please check at your end.";
                }
            } else {
                $responseArray['error_msg'] = "Application details not found.";
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }

        echo json_encode($responseArray);
    }

    public function get_Banking_Analysis_Data($leadID) {
        $lead_id = intval($this->encrypt->decode($leadID));
        //        unset($responseArray);
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        if (!empty($lead_id)) {

            $this->load->helper('banking_analysis_helper');
            $result = get_Banking_Analysis_Response_Data($lead_id);

            if (!empty($result['data'])) {
                $responseArray['success_msg'] = $result['data'];
            } else if (!empty($result['success_msg'])) {
                $responseArray['success_msg'] = $result['success_msg'];
            } else {
                $responseArray['error_msg'] = $result['error_msg'];
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }

        echo json_encode($responseArray);
    }

    public function ocr_verification_api_call() {
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        $api_call_flag = 1;

        $lead_id = $this->encrypt->decode($this->input->post('lead_id', true));
        $ocr_verification_type = $this->input->post('ocr_verification_type', true);

        if (!empty($lead_id)) {
            $lead_details = $this->Tasks->select(['lead_id' => $lead_id], '*', 'leads');

            if ($lead_details->num_rows() > 0) {

                $api_response = array();

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                if (in_array($ocr_verification_type, array(1, 2))) {
                    if ($ocr_verification_type == 1) {
                        $aadhaar_doc_details = $this->Tasks->select('docs_master_id in (1) AND docs_active = 1 AND docs_deleted = 0 AND lead_id=' . $lead_id, '*', 'docs');
                        if ($aadhaar_doc_details->num_rows() <= 0) {
                            $api_call_flag = 0;
                            $responseArray['error_msg'] = "Upload Aadhaar Front.";
                        }

                        $aadhaar_doc_details = $this->Tasks->select('docs_master_id in (2) AND docs_active = 1 AND docs_deleted = 0 AND lead_id=' . $lead_id, '*', 'docs');
                        if ($aadhaar_doc_details->num_rows() <= 0) {
                            $api_call_flag = 0;
                            $responseArray['error_msg'] = "Upload Aadhaar Rear.";
                        }

                        if ($api_call_flag == 1) {
                            $aadhaar_ocr_response = $CommonComponent->call_aadhaar_ocr_api($lead_id);
                            $api_response['status'] = $aadhaar_ocr_response['status'];
                            $api_response['error_msg'] = $aadhaar_ocr_response['errors'];
                            if (!empty($api_response['status']) && $api_response['status'] == 1) {
                                if ($aadhaar_ocr_response['aadhaar_valid_status'] == 1) {
                                    $responseArray['success_msg'] = "Aadhaar OCR Verified.";
                                } else {
                                    $responseArray['error_msg'] = "Aadhaar OCR Verification : Aadhaar OCR verification failed.";
                                }
                            } else {
                                $responseArray['error_msg'] = "Aadhaar OCR Verification : " . $api_response['error_msg'];
                            }
                        }
                    } else if ($ocr_verification_type == 2) {
                        $pan_doc_details = $this->Tasks->select('docs_master_id in (4) AND docs_active = 1 AND docs_deleted = 0 AND lead_id=' . $lead_id, '*', 'docs');
                        if ($pan_doc_details->num_rows() <= 0) {
                            $api_call_flag = 0;
                            $responseArray['error_msg'] = "Upload PAN Document.";
                        }
                        if ($api_call_flag == 1) {
                            $pan_ocr_response = $CommonComponent->call_pan_ocr_api($lead_id);
                            $api_response['status'] = $pan_ocr_response['status'];
                            $api_response['error_msg'] = $pan_ocr_response['errors'];

                            if (!empty($api_response['status']) && $api_response['status'] == 1) {
                                if ($pan_ocr_response['pan_valid_status'] == 1) {
                                    $responseArray['success_msg'] = "PAN OCR Verified.";
                                } else {
                                    $responseArray['error_msg'] = "PAN OCR Verification : PAN OCR verification failed.";
                                }
                            } else {
                                $responseArray['error_msg'] = "PAN OCR Verification : " . $api_response['error_msg'];
                            }
                        }
                    }
                } else {
                    $responseArray['error_msg'] = "OCR called for invalid document.";
                }
            } else {
                $responseArray['error_msg'] = "Application details not found.";
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }
        echo json_encode($responseArray);
    }

    public function face_match_verification_api_call() {
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        $lead_id = $this->encrypt->decode($this->input->post('lead_id', true));
        $verification_type = $this->input->post('verification_type', true);

        if (!empty($lead_id)) {
            $lead_customer_details = $this->Tasks->select(['customer_lead_id' => $lead_id], '*', 'lead_customer');

            if ($lead_customer_details->num_rows() > 0) {

                $api_response = array();

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $api_response = $CommonComponent->call_face_match_api($lead_id, $verification_type);

                if (!empty($api_response['status']) && ($api_response['status'] == 1)) {
                    if ($api_response['match_status'] == 1) {
                        $responseArray['success_msg'] = "Face Matched Successfully.";
                    } else if ($api_response['match_status'] == 2) {
                        $responseArray['success_msg'] = "Face Matched Partially.";
                    } else {
                        $responseArray['error_msg'] = "Face Do Not Match. Upload another selfie and Try Again.";
                    }
                } else {
                    $responseArray['error_msg'] = $api_response['errors'];
                }
            } else {
                $responseArray['error_msg'] = "Lead Details Not Found";
            }
        } else {
            $responseArray['error_msg'] = "Lead ID cannot be empty";
        }
        echo json_encode($responseArray);
    }

    public function address_match_verification_api_call() {
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        $lead_id = $this->encrypt->decode($this->input->post('lead_id', true));
        $verification_type = $this->input->post('verification_type', true);

        if (!empty($lead_id)) {
            $lead_customer_details = $this->Tasks->select(['customer_lead_id' => $lead_id], '*', 'lead_customer');

            if ($lead_customer_details->num_rows() > 0) {

                $api_response = array();

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $api_response = $CommonComponent->call_address_match_api($lead_id, $verification_type);
                if (!empty($api_response['status']) && ($api_response['status'] == 1)) {
                    if ($verification_type == 2) {
                        if ($api_response['match_status'] == 1) {
                            $responseArray['success_msg'] = "Bureau and Current Address Matched Successfully.";
                        } else if ($api_response['match_status'] == 2) {
                            $responseArray['success_msg'] = "Bureau and Current Address Matched Partially.";
                        } else {
                            $responseArray['error_msg'] = "Bureau and Current Address Do Not Match.";
                        }
                    } else if ($verification_type == 4) {
                        if ($api_response['match_status'] == 1) {
                            $responseArray['success_msg'] = "Bureau and Aadhaar Address Matched Successfully.";
                        } else if ($api_response['match_status'] == 2) {
                            $responseArray['success_msg'] = "Bureau and Aadhaar Address Matched Partially.";
                        } else {
                            $responseArray['error_msg'] = "Bureau and Aadhaar Address Do Not Match.";
                        }
                    }
                } else {
                    $responseArray['error_msg'] = $api_response['errors'];
                }
            } else {
                $responseArray['error_msg'] = "Lead Details Not Found";
            }
        } else {
            $responseArray['error_msg'] = "Lead ID cannot be empty";
        }
        echo json_encode($responseArray);
    }

    public function get_Banking_Account_Aggregator($enc_lead_id) {

        $lead_id = $this->encrypt->decode($enc_lead_id);

        $responseArray = array("errSession" => "", "success_msg" => "", "error_msg" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
            exit;
        }

        if (!empty($lead_id)) {
            $this->load->helper('bank_account_aggregator');
            $result = get_account_aggregator_bank_statement_data($enc_lead_id, $lead_id);
            if ($result['status'] == 1) {
                $responseArray['success_msg'] = $result['data'];
            } else {
                $responseArray['error_msg'] = "Something went wrong";
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }
        echo json_encode($responseArray);
    }

    function send_account_aggregator_url($enc_lead_id) {

        $lead_id = $this->encrypt->decode($enc_lead_id);

        $responseArray = array("status" => 0, "message" => "", "data" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        if (!empty($lead_id)) {
            $return_val = $this->Tasks->sent_account_aggregator_request_email($lead_id);
            if ($return_val == "true") {
                $responseArray['status'] = 1;
                $responseArray['message'] = "Account aggregator email & sms sent successfully.";
            } else {
                $responseArray['message'] = "Account aggregator email sending failed.";
            }
        } else {
            $responseArray['message'] = "Lead ID cannot be empty.";
        }

        echo json_encode($responseArray);
    }

    function verify_account_aggregator_consent($enc_lead_id) {

        $lead_id = $this->encrypt->decode($enc_lead_id);

        $responseArray = array("status" => 0, "message" => "", "data" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
            exit;
        }

        if (!empty($lead_id)) {
            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();

            $account_aggregator_verification_status = $CommonComponent->call_payday_account_aggregator("CONSENT_STATUS", $lead_id);

            if ($account_aggregator_verification_status['status'] == 1) {
                if (strtoupper($account_aggregator_verification_status['consentStatus']) == "PENDING") {
                    //                    sleep(3);
                    $account_aggregator_verification_status = $CommonComponent->call_payday_account_aggregator("CONSENT_STATUS", $lead_id);

                    if ($account_aggregator_verification_status['status'] != 1) {
                        $responseArray['message'] = $account_aggregator_verification_status['error_msg'];
                        echo json_encode($responseArray);
                        exit;
                    }
                } else if (strtoupper($account_aggregator_verification_status['consentStatus']) == "APPROVED") {
                    //                    sleep(5);
                    $account_aggregator_data_fetch_status = $CommonComponent->call_payday_account_aggregator("DATA_FETCH_STATUS", $lead_id);

                    if ($account_aggregator_data_fetch_status['status'] != 1) {
                        $responseArray['message'] = $account_aggregator_data_fetch_status['error_msg'];
                        echo json_encode($responseArray);
                        exit;
                    }

                    if (strtoupper($account_aggregator_data_fetch_status['dateFetchStatus']) == "DATA_READY") {
                        $account_aggregator_bank_statement_fetch = $CommonComponent->call_payday_account_aggregator("BANK_STATEMENT_FETCH", $lead_id);

                        if ($account_aggregator_bank_statement_fetch['status'] == 1) {
                            $responseArray['message'] = "Bank Statement Fetched Succesfully";
                            $responseArray['status'] = 1;
                        } else {
                            $responseArray['message'] = $account_aggregator_bank_statement_fetch['error_msg'];
                            echo json_encode($responseArray);
                            exit;
                        }
                    }
                } else if (strtoupper($account_aggregator_verification_status['consentStatus']) == "REJECTED") {
                    $responseArray['message'] = "Consent Rejected.";
                    $this->Tasks->updateQuery(['aa_lead_id' => $lead_id], 'api_account_aggregator_logs', ['aa_active' => 0, 'aa_deleted' => 1]);
                } else {
                    $responseArray['message'] = "Consent Verification In Progress.";
                }
            } else {
                $responseArray['message'] = $account_aggregator_verification_status['error_msg'];
            }
        } else {
            $responseArray['message'] = "Lead ID cannot be empty.";
        }

        echo json_encode($responseArray);
    }

    function fetch_aa_bank_statement($enc_lead_id) {

        $lead_id = $this->encrypt->decode($enc_lead_id);

        $responseArray = array("status" => 0, "message" => "", "data" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
            exit;
        }

        if (!empty($lead_id)) {
            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();

            $account_aggregator_data_fetch_status = $CommonComponent->call_payday_account_aggregator("DATA_FETCH_STATUS", $lead_id);

            if ($account_aggregator_data_fetch_status['status'] != 1) {
                $responseArray['message'] = $account_aggregator_data_fetch_status['error_msg'];
                echo json_encode($responseArray);
                exit;
            }

            if (strtoupper($account_aggregator_data_fetch_status['dateFetchStatus']) == "DATA_READY") {
                //                sleep(5);
                $account_aggregator_bank_statement_fetch = $CommonComponent->call_payday_account_aggregator("BANK_STATEMENT_FETCH", $lead_id);

                if ($account_aggregator_bank_statement_fetch['status'] == 1) {
                    $responseArray['message'] = "Bank Statement Fetched Succesfully";
                    $responseArray['status'] = 1;
                } else {
                    $responseArray['message'] = $account_aggregator_bank_statement_fetch['error_msg'];
                    echo json_encode($responseArray);
                    exit;
                }
            } else if ($account_aggregator_data_fetch_status['dateFetchStatus'] == "PENDING") {
                $responseArray['message'] = "Data Fetch in progress";
            } else if ($account_aggregator_data_fetch_status['response_data']['dateFetchStatus'] == "DATA_DENIED") {
                $responseArray['message'] = "Bank Statement Fetch denied by customer";
            } else {
                $responseArray['message'] = "Something went wrong.";
            }
        } else {
            $responseArray['message'] = "Lead ID cannot be empty.";
        }
        echo json_encode($responseArray);
    }

    public function __destruct() {
        $this->db->close();
    }
}
