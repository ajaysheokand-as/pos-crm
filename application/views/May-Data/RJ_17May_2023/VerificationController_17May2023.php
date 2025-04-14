<?php

defined('BASEPATH') OR exit('No direct script access allowed');

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

    public function getVerificationDetails($leadID) {

        $return_status = 0;
        $return_error = '';
        $return_data = '';

        $lead_id = $this->encrypt->decode($leadID);

        if (!empty($lead_id)) {

            $lead_details = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, customer_id, status, stage, lead_status_id', 'leads');

            if ($lead_details->num_rows() > 0) {

                $leadDetails = $lead_details->row();

                $query_cust = $this->db->select('*')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();

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
                } else if (!empty($getVerificationdata['email']) && in_array(agent, ['CR2', 'CR3']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && $lead_customer->customer_bre_run_flag==0) {
                    $personal_email_isVerified = '<input type="checkbox" class="checkbox-verif" id="personalEmailVerification" name="personalEmailVerification" onclick="email_verification_api_call(&quot;'.$this->encrypt->encode($lead_id).'&quot;, 1)" autocomplete="off" >';
                }

                $alternate_email_isVerified = '-';
                $alternate_email_verified_on = '-';
                if ($getVerificationdata['alternate_email_verified_status'] == 'YES') {
                    $alternate_email_isVerified = $getVerificationdata['alternate_email_verified_status'];
                    $alternate_email_verified_on = date("d-m-Y H:i:s", strtotime($getVerificationdata['alternate_email_verified_on']));
                } else if (!empty($getVerificationdata['alternate_email']) && in_array(agent, ['CR2', 'CR3']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"]) && $lead_customer->customer_bre_run_flag==0) {
                    $alternate_email_isVerified = "<input type='checkbox' class='checkbox-verif' id='officeEmailVerification' name='officeEmailVerification' onclick='email_verification_api_call('".$this->encrypt->encode($lead_id)."', 2)' autocomplete='off' >";
                }



                $office_email_verified_on = '-';
                if (isset($getVerificationdata['office_email_verified_on']) == '' || isset($getVerificationdata['office_email_verified_on']) == '-') {
                    $office_email_verified_on = ($getVerificationdata['office_email_verified_on'] ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_email_verified_on'])) : '-');
                }

                $aadhar_verified = '-';
//                if (isset($getVerificationdata['aadhar_verified']) == '' || isset($getVerificationdata['aadhar_verified']) == '-') {
//                    $aadhar_verified = "YES";
//                }

                $app_download_on = '-';
//                if (isset($getVerificationdata['app_download_on']) == '' || isset($getVerificationdata['app_download_on']) == '-') {
//                    $app_download_on = "YES";
//                }


                $init_mobile_verification = '-';
                if (isset($getVerificationdata['init_mobile_verification']) == 'YES') {
                    $init_mobile_verification = "checked disabled";
                }

                $mobile_otp = '-';
                if (isset($getVerificationdata['mobile_otp']) == "" || isset($getVerificationdata['mobile_otp']) == "-") {
                    $mobile_otp = "YES";
                }

                $init_residence_cpv = '-';
                if (in_array(agent, ['CR2']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"])) {
                    if ($getVerificationdata['init_residence_cpv'] == 'YES') {
                        $init_residence_cpv_assign = "checked disabled";
                    } else {
                        $init_residence_cpv_assign = "";
                    }

                    $init_residence_cpv = '<input type="checkbox"' . $init_residence_cpv_assign . '  name="residenceCPV" id="residenceCPV" class="checkbox-verif" onclick="initiateFiCPV(' . $lead_id . ', 1)" value=""  >';
                }

                $init_office_cpv = '-';
                if (in_array(agent, ['CR2']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"])) {
                    if ($getVerificationdata['init_office_cpv'] == 'YES') {
                        $init_office_cpv_assign = "checked disabled";
                    } else {
                        $init_office_cpv_assign = "";
                    }
                    $init_office_cpv = '<input type="checkbox"' . $init_office_cpv_assign . '  name="officeCPV" id="officeCPV" class="checkbox-verif" value=""   onclick="initiateFiCPV(' . $lead_id . ', 2)">';
                }

                $scm_fi_res_name = '-';
                if (!empty($getVerificationdata['scm_fi_res_name']) && $getVerificationdata['scm_fi_res_name'] != null) {
                    $scm_fi_res_name = $getVerificationdata['scm_fi_res_name'];
                }

                $scm_fi_office_user = '-';
                if (!empty($getVerificationdata['scm_fi_office_user']) && $getVerificationdata['scm_fi_office_user'] != null) {
                    $scm_fi_office_user = $getVerificationdata['scm_fi_office_user'];
                }

                $residence_initiated_on = '-';
                if (!empty($getVerificationdata['residence_initiated_on']) && $getVerificationdata['residence_initiated_on'] != null) {
                    $residence_initiated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['residence_initiated_on']));
                }

                $office_initiated_on = '-';
                if (!empty($getVerificationdata['office_initiated_on']) && $getVerificationdata['office_initiated_on'] != null) {
                    $office_initiated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['office_initiated_on']));
                }

                $rm_fi_res_name = '-';
                if (!empty($getVerificationdata['rm_fi_res_name']) && $getVerificationdata['rm_fi_res_name'] != null) {
                    $rm_fi_res_name = $getVerificationdata['rm_fi_res_name'];
                }

                $rm_fi_office_user = '-';
                if (!empty($getVerificationdata['rm_fi_office_user']) && $getVerificationdata['rm_fi_office_user'] != null) {
                    $rm_fi_office_user = $getVerificationdata['rm_fi_office_user'];
                }

                $residence_cpv_allocated_on = '-';
                if (!empty($getVerificationdata['residence_cpv_allocated_on']) && $getVerificationdata['residence_cpv_allocated_on'] != null) {
                    $residence_cpv_allocated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['residence_cpv_allocated_on']));
                }

                $office_cvp_allocated_on = '-';
                if (!empty($getVerificationdata['office_cvp_allocated_on']) && $getVerificationdata['office_cvp_allocated_on'] != null) {
                    $office_cvp_allocated_on = date('d-m-Y H:i:s', strtotime($getVerificationdata['office_cvp_allocated_on']));
                }

                $pan_verified_str = "-";

                if ($lead_customer->pancard_verified_status == 1 && $lead_customer->pancard_ocr_verified_status == 1) {
                    $pan_verified_str = "PAN Verified on " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_verified_on));
                    $pan_verified_str .= "<br/>PAN OCR Verified on " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_ocr_verified_on));
                } else if ($lead_customer->pancard_verified_status == 1) {
                    $pan_verified_str = "PAN Verified on " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_verified_on));
                } else if ($lead_customer->pancard_ocr_verified_status == 1) {
                    $pan_verified_str = "PAN OCR Verified on " . date("d-m-Y H:i:s", strtotime($lead_customer->pancard_ocr_verified_on));
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

                if ($lead_customer->customer_digital_ekyc_flag == 1) {
                    $aadhar_kyc_verified_str = "E-KYC Verified : YES";
                }

                if (!empty($lead_customer->customer_ekyc_request_initiated_on)) {
                    $aadhar_kyc_verified_str .= "<br/>E-KYC Requested On : " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_ekyc_request_initiated_on));
                }

                if ($lead_customer->customer_digital_ekyc_flag == 1) {
                    $aadhar_kyc_verified_str .= "<br/>E-KYC Verified on : " . date("d-m-Y H:i:s", strtotime($lead_customer->customer_digital_ekyc_done_on));
                }

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
                                <th>Personal Email Verification</th>                
                                <td>' . $personal_email_isVerified . '</td>
                                <th>Personal Email Verified On </th>
                                <td>' . $email_verified_on . '</td>            
                            </tr>          

                            <tr>                
                                <th>Office Email Verification</th>                
                                <td>' . $alternate_email_isVerified . '</td>
                                <th>Office Email Verified On </th>
                                <td>' . $alternate_email_verified_on . '</td>          
                            </tr>  

                            <tr>                
                                <th>PAN Verification</th>                
                                <td>' . $pan_verified_str . '</td>                
                                <th>Digital E-KYC Verified</th>                
                                <td>' . $aadhar_kyc_verified_str . '</td>
                            </tr>    

                            <tr>                
                                <th>Bank Statement Verified</th>                
                                <td>
                                    ' . ((in_array(agent, ['CR2']) && in_array($leadDetails->stage, ["S4", "S5", "S6", "S11"])) ?
                        '<select class="form-control inputField" id="isBankStatementVerified" name="isBankStatementVerified" autocomplete="off" disabled="">
                                        <option value="">SELECT</option>
                                        <option value="YES" ' . (isset($getVerificationdata['bank_statement_verified']) == "YES" ? "SELECTED" : "") . '>YES</option>
                                        <option value="NO" ' . (isset($getVerificationdata['bank_statement_verified']) == "NO" ? "SELECTED" : "") . '>NO</option>
                                    </select>' : '-') . '
                                </td>                
                                <th>App Downloaded On</th>               
                                <td>' . $app_download_on . '</td>            
                            </tr>             
 
                            <tr>                
                                <th>Sanction Letter eSigned </th>                
                                <td>' . $esgin_sanction_letter . '</td>                
                                <th>&nbsp;</th>                
                                <td>&nbsp;</td>            
                            </tr>
                            
                               
                        </table>        

                    </div>';
                /* <tr>                
                  <th>Initiate Residence CPV</th>
                  <td>' . $init_residence_cpv . '</td>
                  <th>Initiate Office CPV</th>
                  <td>' . $init_office_cpv . '</td>
                  </tr>
                  <tr>
                  <th>Residence CPV Allocated To - SCM</th>
                  <td>' . $scm_fi_res_name . '</td>
                  <th>Office CPV Allocated To - SCM</th>
                  <td>' . $scm_fi_office_user . '</td>
                  </tr>
                  <tr>
                  <th>Residence CPV Allocated On - SCM</th>
                  <td>' . $residence_initiated_on . '</td>
                  <th>Office CPV Allocated On - SCM</th>
                  <td>' . $office_initiated_on . '</td>
                  </tr>

                  <tr>
                  <th>Residence CPV Allocated To - RM</th>
                  <td>' . $rm_fi_res_name . '</td>
                  <th>Office CPV Allocated To - RM</th>
                  <td>' . $rm_fi_office_user . '</td>
                  </tr>

                  <tr>
                  <th>Residence CPV Allocated On - RM</th>
                  <td>' . $residence_cpv_allocated_on . '</td>
                  <th>Office CPV Allocated On - RM</th>
                  <td>' . $office_cvp_allocated_on . '</td>
                  </tr> */
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

        $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
        $email_verification_type = $this->input->post('email_verification_type',true);
        $flag = $this->input->post('flag',true);

        if (!empty($lead_id)) {

            $lead_details = $this->Tasks->select(['customer_lead_id' => $lead_id], 'customer_lead_id', 'lead_customer');

            if ($lead_details->num_rows() > 0) {

                $api_response = array();

                if ($flag == "YES" && in_array($email_verification_type, array(1, 2))) {

                    if ($email_verification_type == 2) {
                        require_once (COMPONENT_PATH . 'CommonComponent.php');

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
                        $this->load->helper('integration/payday_email_verification_api_helper');

                        $method_name = 'EMAIL_VALIDATION';
                        $request_array = array();
                        $request_array['email_type'] = $email_verification_type;
                        $api_response = payday_email_verification_api_call($method_name, $lead_id, $request_array);
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
        $lead_id = $this->encrypt->decode($leadID);
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

    public function __destruct() {
        $this->db->close();
    }

}

?>
