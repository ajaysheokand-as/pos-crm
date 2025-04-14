<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class LeadApi extends REST_Controller {

   // public $white_listed_ips = array("208.109.63.229");

	public function __construct() {
        parent::__construct();
        $this->load->model('Lead_Model', 'Lead');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }
    
	
    public function UserLogin_post() {
       
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = '';
		if($token['token_Leads'] == base64_decode($headers['Auth'])){
			 return json_encode($this->response(['Status' => 2, 'Message' => "Invalid Token."], REST_Controller::HTTP_OK));
		}

            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("source_id", "Source_Id", "required");
            
            if ($this->form_validation->run() == FALSE) {
        
                return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {
        
                $mobile = intval($post['mobile']);            
                $source_id = intval($post['source_id']);
                $utm_source = !empty($post['utm_source']) ? htmlspecialchars($post['utm_source']) : "";
                $utm_campaign = !empty($post['utm_campaign']) ? htmlspecialchars($post['utm_campaign']) : "";
                $utm_medium = !empty($post['utm_medium']) ? htmlspecialchars($post['utm_medium']) : "";
                $utm_term = !empty($post['utm_term']) ? htmlspecialchars($post['utm_term']) : "";
                $utm_content = !empty($post['utm_content']) ? htmlspecialchars($post['utm_content']) : "";
               
                $leadquery = $this->db->select('*')->where('mobile', $mobile)->from('leads')->order_by('lead_id', 'DESC')->get();
                $ld_query = $leadquery->row();

                   if($ld_query->pancard){                 
                    return json_encode($this->response(['Status' => 0, 'Message' => "You have already applied for the day. Please try again tomorrow."], REST_Controller::HTTP_OK));
                    }
					else {
                        $mobilenew =  $ld_query->mobile; 
                        $otp = rand(1000, 9999);
                        $update_new_data_leads = [
                            'otp' => $otp,
                            'updated_on' => date('Y-m-d H:i:s')
                        ];
                       $leadnewotp = $this->db->where('mobile', $mobilenew)->update('leads', $update_new_data_leads);

                    if(!empty($leadnewotp))  { 
                        $insertDataOTP = array(
							'lot_lead_id' => $ld_query->lead_id,
							'lot_mobile_no' => $mobile,
							'lot_mobile_otp' => $otp,
							'lot_mobile_otp_type' => 2,
							'lot_otp_trigger_time' => date('Y-m-d H:i:s'),
		
							);  
    
                        $this->db->insert('leads_otp_trans', $insertDataOTP);
                        $lead_otp_id = $this->db->insert_id();
   
                         $query = $this->db->select('lot_mobile_no')->where('lot_mobile_no', $mobile)->where('lot_lead_id', $ld_query->lead_id)->from('leads_otp_trans')->get();
                         if ($query->num_rows() > 3) {
                                return json_encode($this->response(['Status' => 0, 'Message' => 'You can not resend otp more than 3 times.', 'Mobile' => $mobile], REST_Controller::HTTP_OK));
                            }
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
      
                       return json_encode($this->response(['Status' => 3, 'lead_id'=>$ld_query->lead_id, 'Message' => "Otp Resend Succussfully."], REST_Controller::HTTP_OK));
                      }

                    }

                $otp = rand(1000, 9999);
                
                $purposeofloanname = '';

                $query = $this->Tasks->selectdata(['enduse_id' => $purposeofloan], 'enduse_name', 'master_enduse');

                if ($query->num_rows() > 0) {
                    $sql = $query->row();
                    $purposeofloanname = $sql->enduse_name;
                }
                if ($source_id == 16) {
                    
                    $data_source_name = "AffiliatesWeb";
                    $lead_data_source_id = 16;
                } 
                else if($source_id == 17){
                    
                    $data_source_name = "AffiliatesApp";
                    $lead_data_source_id = 17;
                }
                else if($source_id == 21){
                    
                    $data_source_name = "MessageSalaryOnTime";
                    $lead_data_source_id = 21;
                }
                else {
                   
                    $data_source_name = "WebSalaryontime";
                    $lead_data_source_id = 4;
                }

                $lead_status_id = 1;
                $user_type = 'NEW';

                $insertDataLeads = array(
                    'mobile' => $mobile,
                    'otp' => $otp,
                    'company_id' => 1,
                    'product_id' => 1,
                    'lead_status_id' => 1,
                    'user_type' => $user_type,
                    'stage' => 'S1',
                    'status' => 'LEAD-NEW',
                    'source' => $data_source_name,
                    'lead_entry_date' => date('Y-m-d'),
                    'lead_data_source_id' => $lead_data_source_id,
                    'ip' => $ip,
                    'lead_mobile_android_id' => $lead_mobile_android_id,
                    'qde_consent' => 'Y',
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    // 'utm_medium' => $utm_medium,
                    // 'utm_term' => $utm_term,
                    // 'utm_content' => $utm_content,
                    'term_and_condition' => "YES",
                    'created_on' => date('Y-m-d H:i:s')
                    
                );

                $this->db->insert('leads', $insertDataLeads);
                $lead_id = $this->db->insert_id();
    
                if (empty($lead_id)) {
                    return json_encode($this->response(['Status' => 2, 'Message' => "Some error occurred due to data set. Please try again."], REST_Controller::HTTP_OK));
                }

                $insertEnquiryDataLeads = array(
                'cust_enquiry_lead_id' => $lead_id,
                'cust_enquiry_mobile' => $mobile,
                'cust_enquiry_data_source_id' => $data_source_name,
                'cust_enquiry_ip_address' => $ip,
                'cust_enquiry_type_id' => 1,
                'cust_enquiry_remarks' => "OTP NOT VERIFIED",
                'cust_enquiry_created_datetime' => date('Y-m-d H:i:s')
                
            );

                $this->db->insert('customer_enquiry', $insertEnquiryDataLeads);
             
                
                $insertLeadsCustomer = array(
                    'customer_lead_id' => intval($lead_id),
                    'mobile' => intval($mobile),
                    'customer_lead_finbox_cust_id' => intval($finbox_customer_id),
                    'created_date' => date('Y-m-d H:i:s'),
                );
                 $insertCustomerBanking = [
                            'lead_id' => intval($lead_id),
                            'updated_on' => date('Y-m-d H:i:s')
                        ];
                        
                $insertCustomerDocs = [
                    'lead_id' => intval($lead_id),
                    'mobile' => intval($mobile),
                    'created_on' => date('Y-m-d H:i:s')
                ];
                

                $this->db->insert('lead_customer', $insertLeadsCustomer);
                $this->db->insert('docs', $insertCustomerDocs);
                $this->db->insert('customer_banking', $insertCustomerBanking);

                $this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "New lead applied");

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
                        
                            'gender' => $gender,
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                        $update_data_leads = [
                            'customer_id' => $cif_result->cif_number,
                            'user_type' => $user_type,
                            'updated_on' => date('Y-m-d H:i:s')
                        ];
                        

                        $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        

                        $empquery = $this->db->select('id')->where('lead_id', $lead_id)->from('customer_employment')->get();
                        $empquery = $empquery->row();
                        $emp_id = !empty($empquery->id) ? $empquery->id : 0;

                        $insert_customer_employement = [
                            'lead_id' => $lead_id,
                            'customer_id' => $cif_result->cif_number
                    
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


                // $return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

                if ($return_eligibility_array['status'] == 2) {
                    return json_encode($this->response(['Status' => 2, 'Message' => $return_eligibility_array['error']], REST_Controller::HTTP_OK));
                }

                $insertDataOTP = array(
                    'lot_lead_id' => $lead_id,
                    'lot_mobile_no' => $mobile,
                    'lot_mobile_otp' => $otp,
                    'lot_mobile_otp_type' => 2,
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

                $array = ['Status' => 1, 'Message' => 'Registeration successfull.', 'lead_id' => $lead_id];

                if ($lead_otp_id) {
                    return json_encode($this->response($array, REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 2, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }

    }

}

?>
