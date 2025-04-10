<?php
// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
class UserApi extends REST_Controller {
	// public $white_listed_ips = array("208.109.63.229");
	public function __construct() {
		parent::__construct();
		$this->load->model('Lead_Model', 'Lead');
		$this->load->model('Task_Model', 'Tasks');
		date_default_timezone_set('Asia/Kolkata');
		define('created_on', date('Y-m-d H:i:s'));
		define('created_date', date('Y-m-d'));
		ini_set('max_execution_time', 3600);
		ini_set("memory_limit", "1024M");
	}


	public function SendOtp_post() {
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
			$this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
			// $this->form_validation->set_rules("source_id", "Source_Id", "required");
			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {
				$mobile = intval($post['mobile']);
				$otpFor = isset($post['otpFor']) ? $post['otpFor'] : 'registration';
				$source_id = isset($post['data_source_id']) ? intval($post['data_source_id']) : intval(@$post['source_id']);
				$utm_source = !empty($post['utm_source']) ? htmlspecialchars($post['utm_source']) : "";
				$utm_campaign = !empty($post['utm_campaign']) ? htmlspecialchars($post['utm_campaign']) : "";
				$utm_medium = !empty($post['utm_medium']) ? htmlspecialchars($post['utm_medium']) : "";
				$utm_term = !empty($post['utm_term']) ? htmlspecialchars($post['utm_term']) : "";
				$utm_content = !empty($post['utm_content']) ? htmlspecialchars($post['utm_content']) : "";
				$current_date = date("Y-m-d");
				$ip = '';
				$finbox_customer_id = '';
				$otp = rand(1000, 9999);
				$lead_status_id = 1;
				$user_type = 'NEW';

				require_once(COMPONENT_PATH . 'CommonComponent.php');
				$CommonComponent = new CommonComponent();


				if (isset($otpFor) && $otpFor == 'login') {
					$leadquery = $this->db->select('*')->where('mobile', $mobile)->where('lead_status_id', 1)->from('leads')->order_by('lead_id', 'DESC')->get();
				} else {
					$leadquery = $this->db->select('*')->where('mobile', $mobile)->where('lead_entry_date', $current_date)->from('leads')->order_by('lead_id', 'DESC')->get();
				}

				$ld_query = $leadquery->row();

				if (isset($ld_query) && $ld_query->lead_id > 0) {
					$otpQuery = $this->db->select('*')->where('lot_mobile_no', $mobile)->where("date(lot_otp_trigger_time) = '" . $current_date . "'")->from('leads_otp_trans')->order_by('lot_id', 'DESC')->get();
					$otpCount = $otpQuery->num_rows();
					$otpRes = $otpQuery->result_array();
					if ($otpCount >= 10) {
						return json_encode($this->response(['Status' => 2, 'Message' => "OTP limit exceeded for today. Please try again tomorrow."], REST_Controller::HTTP_OK));
					} else if (!empty($ld_query->first_name) && $ld_query->stage && $ld_query->lead_status_id > 1) {
						return json_encode($this->response(['Status' => 2, 'Message' => "Your application has been moved to next step."], REST_Controller::HTTP_OK));
					} else {
						$lead_id = $ld_query->lead_id;
						$update_new_data_leads = [
							'otp' => $otp,
							'updated_on' => date('Y-m-d H:i:s')
						];
						$leadnewotp = $this->db->where('lead_id', $lead_id)->update('leads', $update_new_data_leads);

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
							"mobile" => $mobile,
							"otp" => $otp
						];
						$sms_input_data = array();
						$sms_input_data['mobile'] = $mobile;
						$sms_input_data['name'] = "Customer";
						$sms_input_data['otp'] = $otp;
						$CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);
						$this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");
						$array = ['Status' => 1, 'Message' => 'OTP sent successfully..', 'lead_id' => $lead_id];
						if ($lead_otp_id) {
							return json_encode($this->response($array, REST_Controller::HTTP_OK));
						} else {
							return json_encode($this->response(['Status' => 2, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
						}
					}
				} else {

					$purposeofloanname = '';

					if ($source_id == 16) {
						$data_source_name = "AffiliatesWeb";
						$lead_data_source_id = 16;
					} else if ($source_id == 17) {
						$data_source_name = "AffiliatesApp";
						$lead_data_source_id = 17;
					} else if ($source_id == 33) {
						$data_source_name = "ARDSALARYONTIME";
						$lead_data_source_id = 33;
					} else if ($source_id == 34) {
						$data_source_name = "IOSSALARYONTIME";
						$lead_data_source_id = 34;
					} else {
						$data_source_name = "WebSalaryontime";
						$lead_data_source_id = 4;
					}
					$insertDataLeads = array(
						'mobile' => $mobile,
						'otp' => $otp,
						'company_id' => 1,
						'product_id' => 1,
						'lead_status_id' => $lead_status_id,
						'user_type' => $user_type,
						'stage' => 'S1',
						'status' => 'LEAD-NEW',
						'source' => $data_source_name,
						'lead_entry_date' => date('Y-m-d'),
						'lead_data_source_id' => $lead_data_source_id,
						'ip' => $ip,
						'lead_mobile_android_id' => $lead_mobile_android_id,
						'qde_consent' => 'Y',
						'term_and_condition' => "YES",
						'created_on' => date('Y-m-d H:i:s')
					);
					if ($utm_source != '') {
						$insertDataLeads['utm_source'] = $utm_source;
					}
					if ($utm_campaign != '') {
						$insertDataLeads['utm_campaign'] = $utm_campaign;
					}
					if ($utm_medium != '') {
						$insertDataLeads['utm_medium'] = $utm_medium;
					}
					if ($utm_term != '') {
						$insertDataLeads['utm_term'] = $utm_term;
					}
					if ($utm_content != '') {
						$insertDataLeads['utm_content'] = $utm_content;
					}

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
					$this->db->insert('lead_customer', $insertLeadsCustomer);

					$insertCustomerBanking = [
						'lead_id' => intval($lead_id),
						'updated_on' => date('Y-m-d H:i:s')
					];
					$this->db->insert('customer_banking', $insertCustomerBanking);

					$insertCustomerDocs = [
						'lead_id' => intval($lead_id),
						'mobile' => intval($mobile),
						'created_on' => date('Y-m-d H:i:s')
					];
					$this->db->insert('docs', $insertCustomerDocs);

					$this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "New lead applied");

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
					$sms_input_data['name'] = 'Customer';
					$sms_input_data['otp'] = $otp;
					$CommonComponent->payday_sms_api(1, $lead_id, $sms_input_data);
					$this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP sent to customer");
					$array = ['Status' => 1, 'Message' => 'OTP sent successfully.', 'lead_id' => $lead_id];
					if ($lead_otp_id) {
						return json_encode($this->response($array, REST_Controller::HTTP_OK));
					} else {
						return json_encode($this->response(['Status' => 2, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
					}
				}
			}
		}
	}

	public function VerifyOtp_post() {

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
			$this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
			$this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");
			$this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {


				$mobile = !empty($post['mobile']) ? intval($post['mobile']) : 0;
				$lead_id = intval($post['lead_id']);
				$otp = strval($post['otp']);

				$queryLead = $this->db->select('*')->where(['lead_id' => $lead_id])->where(['mobile' => $mobile])->from('leads')->get();

				$leadData = $queryLead->row();
				$lead_status_id = isset($leadData->lead_status_id) ? $leadData->lead_status_id : 0;

				if ($lead_status_id > 1) {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
				}
				if (!isset($leadData) && empty($leadData)) {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Wrong request data .'], REST_Controller::HTTP_OK));
				}
				$current_date = date("Y-m-d");
				$last_row = $this->db->select('lot_id,lot_mobile_otp,lot_otp_verify_flag')->where('lot_lead_id', $lead_id)->where("date(lot_otp_trigger_time) = '" . $current_date . "'")->from('leads_otp_trans')->order_by('lot_id', 'desc')->limit(1)->get()->row();
				//print_r($last_row); die;
				if (!isset($last_row) || empty($last_row) || $last_row->lot_otp_verify_flag == 1) {
					return json_encode($this->response(['Status' => 2, 'Message' => 'Please try to resend OTP.'], REST_Controller::HTTP_OK));
				}

				$lastotp = $last_row->lot_mobile_otp;
				$lot_id = $last_row->lot_id;

				if ($lastotp != $otp) {
					return json_encode($this->response(['Status' => 2, 'Message' => 'OTP verification failed. Please enter valid OTP.'], REST_Controller::HTTP_OK));
				}



				$update_lead_otp_trans_data = [
					'lot_otp_verify_time' => date('Y-m-d H:i:s'),
					'lot_otp_verify_flag' => 1,
				];
				$this->db->where('lot_id', $lot_id)->update('leads_otp_trans', $update_lead_otp_trans_data);

				$update_data_lead_customer = [
					'mobile_verified_status' => "YES",
					'updated_at' => date('Y-m-d H:i:s')
				];
				$this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

				$this->db->set('lead_is_mobile_verified', 1)->where('lead_id', $lead_id)->update('leads');

				$this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "OTP verified by customer");

				$mobile_number = $mobile;
				$steps['mobile_verification'] = 'DONE';
				$steps['personal_details'] = 'PENDING';
				$steps['documents_uploads'] = 'PENDING';
				if (empty($leadData->first_name) || empty($leadData->pancard)) {
					$steps['pan_verification'] = 'PENDING';
					$steps['step_stage'] = 'G2';
				} else if (!empty($leadData->first_name) && !empty($leadData->pancard) && !empty($leadData->loan_amount) && !empty($leadData->pincode) && !empty($leadData->city_id)) {
					$steps['personal_details'] = 'DONE';
					$steps['pan_verification'] = 'DONE';
					$steps['step_stage'] = 'G4';
				} else if (!empty($leadData->first_name) && !empty($leadData->pancard)) {

					$leadDataCust = $this->db->select('*')->from('lead_customer')->where('customer_lead_id', $lead_id)->get()->row();
					$name[0] = $leadDataCust->first_name;
					$name[1] = $leadDataCust->middle_name;
					$name[2] = $leadDataCust->sur_name;
					$steps['pan_verification'] = 'DONE';
					$steps['step_stage'] = 'G3';
					$steps['first_name'] = implode(" ", $name);
					$steps['pancard'] = $leadData->pancard;
				}
				return json_encode($this->response(['Status' => 1, 'Message' => 'OTP Verified.', 'lead_id' =>  $lead_id, 'data' => $steps], REST_Controller::HTTP_OK));
			}
		}
	}


	function panVerification_post() {
		$input_data = file_get_contents("php://input");
		$post = $this->security->xss_clean(json_decode($input_data, true));

		if (empty($post)) {
			$post = $this->security->xss_clean($_POST);
		}

		$headers = $this->input->request_headers();
		$token = $this->_token();
		$header_validation = ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']));

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

			$this->form_validation->set_data($post);

			$this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
			$this->form_validation->set_rules(
				"panNumber",
				"PAN",
				"required|trim|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/]"
			);
			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
			} else {
				$lead_id                  = $post['lead_id'];
				$panNumber                = $post['panNumber'];
				$loan_amount                = isset($post['loan_amount']) ? $post['loan_amount'] : '';
				$monthly_salary_amount      = isset($post['monthly_salary_amount']) ? $post['monthly_salary_amount'] : '';


				$check_pancard = $this->db->select('id')->where('pancard', $panNumber)->from('blacklisted_pan')->get();
				$checkBlackList = $check_pancard->row();
				if (isset($checkBlackList) && count($checkBlackList) > 0) {
					$this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $checkBlackList->customer_name, 'status' => 'REJECT',  'stage' => 'S9',  'lead_status_id' => '9', 'lead_rejected_reason_id' => 41]);
					$this->Tasks->insertApplicationLog($lead_id, 9, "Rejected due to the PAN blacklist.");
					return json_encode($this->response(['Status' => 3, 'message' => 'Thankyou for contacting us, We will get back to you soon.'], REST_Controller::HTTP_OK));
				}


				$check_pancard_bl = $this->db->select('bl_id,bl_customer_first_name')->where('bl_customer_pancard', $panNumber)->from('customer_black_list')->get();
				$checkBlackListCust = $check_pancard_bl->row();
				if (isset($checkBlackListCust) && count($checkBlackListCust) > 0) {
					$this->db->where('lead_id', $lead_id)->update('leads', ['pancard' => $panNumber, 'first_name' => $checkBlackListCust->bl_customer_first_name, 'status' => 'REJECT',  'stage' => 'S9',  'lead_status_id' => '9', 'lead_rejected_reason_id' => 41]);
					$this->Tasks->insertApplicationLog($lead_id, 9, "Rejected due to the PAN blacklist.");
					return json_encode($this->response(['Status' => 3, 'message' => 'Thankyou for contacting us, We will get back to you soon.'], REST_Controller::HTTP_OK));
				}

				$queryLead = $this->db->select('*')->where(['lead_id' => $lead_id])->from('leads')->get();
				$leadData = $queryLead->row();
				$lead_status_id = isset($leadData->lead_status_id) ? $leadData->lead_status_id : 0;

				if (isset($leadData) && $lead_status_id > 1) {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
				}
				if (isset($leadData) && $leadData->lead_is_mobile_verified != 1) {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Mobile number not Verified.'], REST_Controller::HTTP_OK));
				}
				if (!isset($leadData) || empty($leadData)) {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Wrong request data .'], REST_Controller::HTTP_OK));
				}


				/* Repeat Condition */

				$leadRepSql = $this->db->select('*')->where('pancard', $panNumber)->where_in('lead_status_id', [14, 16, 17, 18, 19])->from('leads')->order_by('lead_id', 'desc')->get();
				$leadsRepeat = $leadRepSql->row();

				/* Add Repeat Code By Rohit Jain Start Code*/

				$leadApply = $leadsRepeat->lead_id;
				$oldPancard = $leadsRepeat->pancard;

				$leadRepSql_new = $this->db->select('*')->where('pancard', $panNumber)->from('leads')->get();
				$leadsRepeat_new = $leadRepSql_new->result_array();

				if (!empty($leadsRepeat_new)) {
					$update_reject_leads = [
						'stage' => 'S8',
						'status' => 'SYSTEM-REJECT',
						'lead_status_id' => 8,
						'user_type' => 'REPEAT',
						'lead_rejected_reason_id' => 17,
						'lead_rejected_datetime' => date("Y-m-d H:i:s"),
						'updated_on' => date("Y-m-d H:i:s")
					];

					$this->db->where('pancard', $panNumber)->where_not_in('lead_status_id', [14, 16, 17, 18, 19])->where_in('user_type', 'UNPAID-REPEAT')->where_not_in('lead_id', $lead_id)->update('leads', $update_reject_leads);
				}

				//return json_encode($this->response($leadsRepeat, REST_Controller::HTTP_OK));
				if (isset($leadsRepeat) && count($leadsRepeat) > 0) {
					$cifDetailSql = $this->db->select('*')->where('cif_pancard', $panNumber)->from('cif_customer')->get();
					$cifDetail = $cifDetailSql->row();
					if (isset($cifDetail) && count($cifDetail) > 0 && $leadsRepeat->pancard == $cifDetail->cif_pancard) {
						$dob = $cifDetail->cif_dob;
						$first_name = $cifDetail->cif_first_name;
						$middle_name = $cifDetail->cif_middle_name;
						$sur_name = $cifDetail->cif_sur_name;
						$application_no = $this->Tasks->generateApplicationNo($lead_id);
						$gender = "MALE";
						if ($cifDetail->cif_gender == 2) {
							$gender = "FEMALE";
						}

						$old_lead_id = $leadsRepeat->lead_id;
						$leadCustomerSql = $this->db->select('*')->where('customer_lead_id', $old_lead_id)->from('lead_customer')->get();
						$leadCustomer = $leadCustomerSql->row();
						if (isset($leadCustomer) && count($leadCustomer) > 0) {
							$name = $leadCustomer->first_name . ' ' . $leadCustomer->middle_name . $leadCustomer->sur_name;
							$update_data_lead_customer = [
								'first_name' => $leadCustomer->first_name,
								'middle_name' => $leadCustomer->middle_name,
								'sur_name' => $leadCustomer->sur_name,
								'gender' => $gender,
								'dob' => $dob,
								'pancard' => $leadCustomer->pancard,
								'email' => $leadCustomer->email,
								'alternate_email' => $leadCustomer->alternate_email,
								'alternate_mobile' => $leadCustomer->alternate_mobile,
								'pancard_verified_status' => $leadCustomer->pancard_verified_status,
								'pancard_verified_on' => $leadCustomer->pancard_verified_on,
								'pancard_ocr_verified_status' => $leadCustomer->pancard_ocr_verified_status,
								'pancard_ocr_verified_on' => $leadCustomer->pancard_ocr_verified_on,
								'email_verified_status' => $leadCustomer->email_verified_status,
								'email_verified_on' => $leadCustomer->email_verified_on,
								'alternate_email_verified_status' => $leadCustomer->alternate_email_verified_status,
								'alternate_email_verified_on' => $leadCustomer->alternate_email_verified_on,
								'customer_ekyc_request_ip' => $leadCustomer->customer_ekyc_request_ip,
								'aadhaar_ocr_verified_status' => $leadCustomer->aadhaar_ocr_verified_status,
								'customer_spouse_name' => $leadCustomer->customer_spouse_name,
								'aa_current_eaadhaar_address' => $leadCustomer->aa_current_eaadhaar_address,
								'customer_qualification_id' => $leadCustomer->customer_qualification_id,
								'aadhaar_ocr_verified_on' => $leadCustomer->aadhaar_ocr_verified_on,
								'customer_docs_available' =>  $leadCustomer->customer_docs_available,
								'customer_ekyc_request_initiated_on' => $leadCustomer->customer_ekyc_request_initiated_on,
								'customer_religion_id' => $leadCustomer->customer_religion_id,
								'customer_spouse_occupation_id' => $leadCustomer->customer_spouse_occupation_id,
								'customer_marital_status_id' => $leadCustomer->customer_marital_status_id,
								'customer_spouse_name' => $leadCustomer->customer_spouse_name,
								'customer_digital_ekyc_flag' => $leadCustomer->customer_digital_ekyc_flag,
								'customer_digital_ekyc_done_on' => $leadCustomer->customer_digital_ekyc_done_on,
								'current_state' => $leadCustomer->current_state,
								'current_city' => $leadCustomer->current_city,
								'current_house' => $leadCustomer->current_house,
								'current_locality' => $leadCustomer->current_locality,
								'current_landmark' => $leadCustomer->current_landmark,
								'current_residence_type' => $leadCustomer->current_residence_type,
								'cr_residence_pincode' => $leadCustomer->cr_residence_pincode,
								'current_residing_withfamily' => $leadCustomer->current_residing_withfamily,
								'current_residence_since' => $leadCustomer->current_residence_since,
								'aa_same_as_current_address' => $leadCustomer->aa_same_as_current_address,
								'city_id' => $leadCustomer->city_id,
								'state_id' => $leadCustomer->state_id,
								'aa_current_city_id' => $leadCustomer->aa_current_city_id,
								'aa_current_state_id' => $leadCustomer->aa_current_state_id,
								'aa_current_city' => $leadCustomer->aa_current_city,
								'aa_current_state' => $leadCustomer->aa_current_state,
								'aa_current_district' => $leadCustomer->aa_current_district,
								'aa_current_house' => $leadCustomer->aa_current_house,
								'aa_current_locality' => $leadCustomer->aa_current_locality,
								'aa_current_landmark' => $leadCustomer->aa_current_landmark,
								'aa_cr_residence_pincode' => $leadCustomer->aa_cr_residence_pincode,
								'aa_current_state_id' => $leadCustomer->aa_current_state_id,
								'aa_current_city_id' => $leadCustomer->aa_current_city_id,
								'aadhar_no' => $leadCustomer->aadhar_no,
								'updated_at' => date("Y-m-d H:i:s")
							];

							$update_cust_leads = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

							$insert_customer_employement = [
								'lead_id' => $lead_id,
								'customer_id' => $cifDetail->cif_number,
								'employer_name' => $cifDetail->cif_company_name,
								'emp_pincode' => $cifDetail->cif_office_pincode,
								'emp_house' => $cifDetail->cif_office_address_1,
								'emp_street' => $cifDetail->cif_office_address_2,
								'emp_landmark' => $cifDetail->cif_office_address_landmark,
								'emp_residence_since' => $cifDetail->cif_office_working_since,
								'emp_shopNo' => $cifDetail->cif_office_address_1,
								'emp_designation' => $cifDetail->cif_office_designation,
								'emp_department' => $cifDetail->cif_office_department,
								'emp_employer_type' => $cifDetail->cif_company_type_id,
								'emp_website' => $cifDetail->cif_company_website,
								'emp_email' => $cifDetail->cif_office_email,
								'city_id' => $cifDetail->cif_office_city_id,
								'state_id' => $cifDetail->cif_office_state_id,
								'updated_on' => date("Y-m-d H:i:s"),
							];
							$insert_customer_employement['created_on'] = date("Y-m-d H:i:s");
							$this->db->insert('customer_employment', $insert_customer_employement);

							$update_data_leads = [
								'first_name' => $leadCustomer->first_name,
								'pancard' => $leadCustomer->pancard,
								'monthly_salary_amount' => $leadsRepeat->monthly_salary_amount,
								'application_no' => $application_no,
								'customer_id' => $cifDetail->cif_number,
								'stage' => 'S4',
								'status' => 'APPLICATION-NEW',
								'lead_status_id' => 4,
								'alternate_email' => $leadsRepeat->alternate_email,
								'email' => $cifDetail->cif_personal_email,
								'pincode' => $cifDetail->cif_aadhaar_pincode,
								'state_id' => $cifDetail->cif_aadhaar_state_id,
								'city_id' => $cifDetail->cif_aadhaar_city_id,
								'user_type' => 'REPEAT',
								'updated_on' => date("Y-m-d H:i:s")
							];

							if (in_array($leadsRepeat->lead_status_id, [17, 18])) {
								$update_data_leads = [
									'first_name' => $leadCustomer->first_name,
									'pancard' => $leadCustomer->pancard,
									'monthly_salary_amount' => $leadsRepeat->monthly_salary_amount,
									'application_no' => $application_no,
									'customer_id' => $cifDetail->cif_number,
									'stage' => 'S8',
									'status' => 'SYSTEM-REJECT',
									'lead_status_id' => 8,
									'lead_rejected_reason_id' => 17,
									'lead_rejected_datetime' => date("Y-m-d H:i:s"),
									'alternate_email' => $leadsRepeat->alternate_email,
									'email' => $cifDetail->cif_personal_email,
									'pincode' => $cifDetail->cif_aadhaar_pincode,
									'state_id' => $cifDetail->cif_aadhaar_state_id,
									'city_id' => $cifDetail->cif_aadhaar_city_id,
									'user_type' => 'REPEAT',
									'updated_on' => date("Y-m-d H:i:s")
								];
							}

							foreach ($leadsRepeat_new as $unpaidRepeat) {

								if (in_array($unpaidRepeat['lead_status_id'], [14, 19])) {
									$update_data_leads['user_type'] = 'UNPAID-REPEAT';
									break;
								}
							}

							$this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
						}
						$this->Tasks->insertApplicationLog($lead_id, 1, "Repeat Lead.");

						$steps['mobile_verification'] = 'DONE';
						$steps['pan_verification'] = 'DONE';

						$steps['step_stage'] = 'G3';
						$steps['personal_details'] = 'PENDING';
						$steps['documents_uploads'] = 'PENDING';
						return json_encode($this->response(['Status' => 1, 'Message' => 'Pan verification successful.', 'lead_id' =>  $lead_id, 'name' =>  $name, 'data' => $steps], REST_Controller::HTTP_OK));
					}
				}
				/* End repeat Condition */

				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://api.signzy.app/api/v3/pan/fetchV2',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => '{
					 "number": "' . $panNumber . '",
					 "returnIndividualTaxComplianceInfo": "true"
				}',
					CURLOPT_HTTPHEADER => array(
						'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
						'Content-Type: application/json'
					),
				));

				$response = curl_exec($curl);
				curl_close($curl);

				/* $response = '{"result":{"name":"JAMSHEER ULLALUM MEETHAL","number":"AFMPU9884P","typeOfHolder":"Individual or Person","isIndividual":true,"isValid":true,"firstName":"JAMSHEER","middleName":"","lastName":"ULLALUM MEETHAL","title":"","panStatus":"VALID","panStatusCode":"E","aadhaarSeedingStatus":"Successful","aadhaarSeedingStatusCode":"Y","lastUpdatedOn":""}}'; */

				$responseData = json_decode($response, true);

				$insertDataPOI = array(
					'poi_veri_provider' => 1,
					'poi_veri_method_id' => 1,
					'poi_veri_lead_id' => $lead_id,
					'poi_veri_proof_no' => $panNumber,
					'poi_veri_api_status_id' => 1,
					'poi_veri_request_datetime' => date('Y-m-d H:i:s'),
					'poi_veri_response_datetime' => date('Y-m-d H:i:s'),
					'poi_veri_response' => $response
				);

				if (isset($responseData['result']) && isset($responseData['result']['name'])) {
					$insertDataPOI['poi_veri_api_status_id'] = 1;
					$this->db->insert('api_poi_verification_logs', $insertDataPOI);

					$name = $responseData['result']['name'];
					$names = explode(" ", $responseData['result']['name']);

					$leadUpdateData = [
						'pancard' => $panNumber,
						'first_name' => $names[0],
						'loan_amount' => $loan_amount,
						'monthly_salary_amount' => $monthly_salary_amount
					];
					//$leadUpdateData['loan_amount'] = '';
					if (isset($monthly_salary_amount) && $monthly_salary_amount <= 30000) {
						$leadUpdateData['utm_source'] = null;
						$leadUpdateData['utm_campaign'] = null;
						$leadUpdateData['utm_medium'] = null;
						$leadUpdateData['utm_term'] = null;
						$leadUpdateData['utm_content'] = null;
					}

					$this->db->where('lead_id', $lead_id)->update('leads', $leadUpdateData);
					$this->db->where('Customer_lead_id', $lead_id)->update('lead_customer', ['pancard' => $panNumber, 'first_name' => $names[0], 'middle_name' => $names[1] ?? "", 'sur_name' => $names[2] ?? ""]);
					$this->db->where('lead_id', $lead_id)->update('docs', ['pancard' => $panNumber]);

					$this->Tasks->insertApplicationLog($lead_id, $lead_status_id, "PAN Verification Done.");

					$steps['mobile_verification'] = 'DONE';
					$steps['pan_verification'] = 'DONE';
					$steps['step_stage'] = 'G3';
					$steps['personal_details'] = 'PENDING';
					$steps['documents_uploads'] = 'PENDING';
					return json_encode($this->response(['Status' => 1, 'Message' => 'Pan verification successful.', 'lead_id' =>  $lead_id, 'name' =>  $name, 'data' => $steps], REST_Controller::HTTP_OK));
				} else {

					$insertDataPOI['poi_veri_api_status_id'] = 2;
					$this->db->insert('api_poi_verification_logs', $insertDataPOI);

					$steps['mobile_verification'] = 'DONE';
					$steps['pan_verification'] = 'PENDING';
					$steps['step_stage'] = 'G2';
					$steps['personal_details'] = 'PENDING';
					$steps['documents_uploads'] = 'PENDING';
					return json_encode($this->response(['Status' => 2, 'Message' => 'Pan verification failed.', 'lead_id' =>  $lead_id, 'name' =>  '', 'data' => $steps], REST_Controller::HTTP_OK));
				}
			}
		}
	}


	public function updatePersonalDetails_post() {

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
			$this->form_validation->set_rules("dob", "dob", "required|trim");
			$this->form_validation->set_rules("lead_id", "Lead Id", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
			$this->form_validation->set_rules("email", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
			$this->form_validation->set_rules("city_id", "City Name", "required|trim");
			$this->form_validation->set_rules("state_id", "State Name", "required|trim");
			$this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
			$this->form_validation->set_rules("monthly_salary_amount", "Monthly Amount", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");
			$this->form_validation->set_rules("pincode", "Pincode", "required|trim|numeric|is_natural|min_length[6]|max_length[6]|regex_match[/^[0-9]+$/]");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$lead_id = intval($post['lead_id']);
				$dob = $post['dob'];
				$pincode = strtoupper(strval($post['pincode']));
				$email = strtoupper(strval($post['email']));
				$city_id = $post['city_id'];
				$state_id = $post['state_id'];
				$loan_amount = !empty($post['loan_amount']) ? doubleval($post['loan_amount']) : "";
				$monthly_amount = !empty($post['monthly_salary_amount']) ? doubleval($post['monthly_salary_amount']) : 0;


				$queryLead = $this->db->select('*')->where(['lead_id' => $lead_id])->from('leads')->get();
				$leadData = $queryLead->row();

				$lead_status_id = isset($leadData->lead_status_id) ? $leadData->lead_status_id : 0;

				if ($lead_status_id > 1 && $leadData->user_type != 'REPEAT') {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Your application has been moved to next step.'], REST_Controller::HTTP_OK));
				}

				if (!isset($leadData) || empty($leadData)) {
					return json_encode($this->response(['Status' => 2, 'Message' => 'Wrong request data .'], REST_Controller::HTTP_OK));
				}

				$update_personal_details = [
					'pincode' => $pincode,
					'email' => $email,
					'city_id' => $city_id,
					'state_id' => $state_id,
					'loan_amount' => $loan_amount,
					'monthly_salary_amount' => $monthly_amount,
					'updated_on' => date('Y-m-d H:i:s')
				];

				$steps['mobile_verification'] = 'DONE';
				$steps['pan_verification'] = 'DONE';
				$steps['personal_details'] = 'DONE';
				$steps['step_stage'] = 'G4';
				$steps['documents_uploads'] = 'PENDING';

				$response = ['Status' => 1, 'Message' => 'Personal Detail Save successfull.', 'lead_id' => $lead_id, 'data' => $steps];

				$this->Tasks->insertApplicationLog($lead_id, 1, "Update Personal Detail. (Occupation: " . @$post['occupation'] . ")");

				$leaddt = $this->db->where('lead_id', $lead_id)->update('leads', $update_personal_details);

				$update_lead_customer = [
					'dob' => $dob
				];
				$this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_lead_customer);

				require_once(COMPONENT_PATH . 'CommonComponent.php');
				$CommonComponent = new CommonComponent();

				$return_eligibility_array = $CommonComponent->run_eligibility($lead_id);

				if ($return_eligibility_array['status'] == 2) {
					return json_encode($this->response(['Status' => 3, 'Message' => 'Customer not eligible for loan due to city not in active list', 'lead_id' => $lead_id, 'data' => $steps], REST_Controller::HTTP_OK));
				}

				return json_encode($this->response($response, REST_Controller::HTTP_OK));
			}
		}
	}


	public function saveCustomerDocument_post() {

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
			$this->form_validation->set_rules("lead_id", "Lead ID", "trim");
			$this->form_validation->set_rules("docs_id", "Docs Type", "required|trim");
			$this->form_validation->set_rules("file", "File", "required|trim");
			$this->form_validation->set_rules("ext", "Extension", "required|trim|in_list[jpg,jpeg,png,pdf,heic,JPG,JPEG,PNG,PDF,HEIC]");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$lead_id = intval($post['lead_id']);
				$docs_id = intval($post['docs_id']);
				$docs_file = strval($post['file']);
				$docs_password = isset($post['password']) ? strval($post['password']) : '';
				$docs_extension = trim(strtolower(strval($post['ext'])));

				$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'heic'];
				if (!in_array($docs_extension, $allowed_extensions)) {
					return json_encode($this->response(['Status' => 2, 'Message' => "Invalid file extension. Allowed extensions are .jpg, .jpeg, .png, .pdf."], REST_Controller::HTTP_OK));
				}

				$decodedData = base64_decode($docs_file, true);
				if ($decodedData === false) {
					return json_encode($this->response(['Status' => 2, 'Message' => "Invalid file data"], REST_Controller::HTTP_OK));
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
					return json_encode($this->response(['Status' => 2, 'Message' => "Invalid Dcoument."], REST_Controller::HTTP_OK));
				}
				$docs_master = $num_rowsleads[0];

				$this->db->select('docs_master_id');
				$this->db->from('docs');
				$this->db->where('lead_id', $lead_id);
				$query = $this->db->get();
				$result = $query->result_array();

				$doc_master_id = array();
				foreach ($result as $row) {
					$doc_master_id[] = $row['docs_master_id'];
				}

				if (in_array($docs_id, $doc_master_id)) {
					return json_encode($this->response(['Status' => 1, 'Message' => 'Document already saved'], REST_Controller::HTTP_OK));
				} else {
					$doc_master_id[] = $docs_id;
				}

				$ext = $docs_extension;
				if (!empty($docs_extension)) {
					if (strpos($docs_extension, ".") == false) {
						$ext = str_replace(".", "", $docs_extension);
					}
				}

				$request_array['flag'] = 1;
				$request_array['file'] = $docs_file;
				$request_array['new_file_name'] = $leadDetails['mobile'];
				$request_array['ext'] = $ext;

				require_once(COMPONENT_PATH . 'CommonComponent.php');
				$CommonComponent = new CommonComponent();

				if (API_DOC_S3_FLAG == true) {
					$upload_file = $CommonComponent->upload_document(0, $request_array);
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
				return json_encode($this->response(['Status' => 1, 'Message' => 'Document uploaded successfully', 'lead_id' =>  $lead_id], REST_Controller::HTTP_OK));
				if ($upload_file['status'] == 1) {

					$required_id = [1, 2, 4, 6];
					$result_ids = array();

					foreach ($required_id as $id) {
						if (!in_array($id, $doc_master_id)) {
							$result_ids[] = $id;
						}
					}

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
						"created_on" => date('Y-m-d H:i:s')
					];
					$this->db->insert('docs', $data);
					$docsInsertId = $this->db->insert_id();

					$this->Tasks->insertApplicationLog($lead_id, $leadDetails['lead_status_id'], "Document upload Done. (Document Type: " . $docs_master['docs_sub_type'] . ")");

					if (empty($result_ids)) {

						$update_new_lead_customer = [
							'customer_docs_available' => 1
						];
						$upload_by_customer = $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_new_lead_customer);

						$steps['mobile_verification'] = 'DONE';
						$steps['pan_verification'] = 'DONE';
						$steps['step_stage'] = 'G5';
						$steps['personal_details'] = 'DONE';
						$steps['documents_uploads'] = 'DONE';
						return json_encode($this->response(['Status' => 1, 'Message' => 'Document uploaded successfully.', 'lead_id' =>  $lead_id, 'data' => $steps, "Required_id" => $result_ids], REST_Controller::HTTP_OK));
					} else {
						$steps['mobile_verification'] = 'DONE';
						$steps['pan_verification'] = 'DONE';
						$steps['step_stage'] = 'G4';
						$steps['personal_details'] = 'DONE';
						$steps['documents_uploads'] = 'PENDING';
						return json_encode($this->response(['Status' => 1, 'Message' => 'Document uploaded successfully', 'lead_id' =>  $lead_id, 'data' => $steps, "Required_id" => $result_ids], REST_Controller::HTTP_OK));
					}
				} else {
					return json_encode($this->response(['Status' => 0, 'Message' => 'Failed to save Docs. Try Again', "Required_id" => $required_id], REST_Controller::HTTP_OK));
				}
			}
		} else {
			return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
		}
	}


	public function getLeadStatus_post() {

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
			$this->form_validation->set_rules("lead_id", "Lead Id", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$lead_id = intval($post['lead_id']);
				$mobile_status = 'PENDING';
				$pan_status = 'PENDING';
				$personal_status = 'PENDING';
				$document_status = 'PENDING';
				$stage_status = 'G1';

				$this->db->select('lead_is_mobile_verified, pancard, first_name, city_id, pincode');
				$this->db->from('leads');
				$this->db->where('lead_id', $lead_id);
				$query = $this->db->get();
				$result = $query->result_array();
				if (isset($result) && !empty($result) && isset($result[0])) {
					$lead_is_mobile_verified = !empty($result[0]['lead_is_mobile_verified']) ? $result[0]['lead_is_mobile_verified'] : null;
					$pancard = !empty($result[0]['pancard']) ? $result[0]['pancard'] : null;
					$first_name = !empty($result[0]['first_name']) ? $result[0]['first_name'] : null;
					$city_id = !empty($result[0]['city_id']) ? $result[0]['city_id'] : null;
					$pincode = !empty($result[0]['pincode']) ? $result[0]['pincode'] : null;

					if ($lead_is_mobile_verified) {
						$mobile_status = 'DONE';
						$stage_status = 'G2';
					}

					if ($lead_is_mobile_verified && $pancard && $first_name) {
						$pan_status = 'DONE';
						$stage_status = 'G3';
					}

					if ($lead_is_mobile_verified && $pancard && $first_name && $city_id && $pincode) {
						$personal_status = 'DONE';
						$stage_status = 'G4';
					}
				} else {

					return json_encode($this->response(['Status' => 2, 'Message' => "Your Application doesn't exist."], REST_Controller::HTTP_OK));
				}


				$this->db->select('docs_master_id');
				$this->db->from('docs');
				$this->db->where('lead_id', $lead_id);
				$query2 = $this->db->get();
				$result2 = $query2->result_array();
				$doc_master_id = array();

				foreach ($result2 as $row) {
					$doc_master_id[] = $row['docs_master_id'];
				}

				$required_id = [1, 2, 4, 6];
				$result_ids = array();

				foreach ($required_id as $id) {
					if (!in_array($id, $doc_master_id)) {
						$result_ids[] = $id;
					}
				}

				if (empty($result_ids)) {
					$document_status = 'DONE';
					$stage_status = 'G5';
				}

				$steps['mobile_verification'] = $mobile_status;
				$steps['step_stage'] = $stage_status;
				$steps['pan_verify'] = $pan_status;
				$steps['personal_details'] = $personal_status;
				$steps['documents_uploads'] = $document_status;

				return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $steps, 'result_ids' => $result_ids], REST_Controller::HTTP_OK));
			}
		} else {
			return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
		}
	}

	public function getDashboard_post() {
		$input_data = file_get_contents("php://input");

		if ($input_data) {
			$post = $this->security->xss_clean(json_decode($input_data, true));
		} else {
			$post = $this->security->xss_clean($this->input->post());
		}

		$headers = $this->input->request_headers();
		$token = $this->_token();
		$header_validation = (
			isset($headers['Accept']) && $headers['Accept'] == "application/json" &&
			isset($headers['Auth']) && $token['token_Leads'] == base64_decode($headers['Auth'])
		);

		if ($_SERVER['REQUEST_METHOD'] === 'POST' && $header_validation) {

			$this->form_validation->set_data($post);
			$this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric');
			$this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");

			if ($this->form_validation->run() === FALSE) {
				return $this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK);
			} else {
				$mobile = $post['mobile'];
				$lead_id = $post['lead_id'];

				$this->db->select('l.lead_id, l.pancard, l.email, l.status, l.mobile, l.monthly_salary_amount, l.loan_amount, l.created_on, lc.first_name, lc.middle_name, lc.sur_name');
				$this->db->from('leads l');
				$this->db->where('l.lead_id', $lead_id);
				$this->db->where('l.mobile', $mobile);
				$this->db->join('lead_customer lc', 'lc.customer_lead_id = l.lead_id', 'left');
				$this->db->order_by('l.lead_id', 'DESC');
				$query = $this->db->get();
				$leadData = $query->row();

				if (!isset($leadData) || empty($leadData)) {
					return json_encode($this->response(['Status' => 2, 'Message' => 'Wrong request data .'], REST_Controller::HTTP_OK));
				}
				$pancard = isset($leadData->pancard) ? $leadData->pancard : null;
				$profileName = isset($leadData->first_name) ? implode(' ', [$leadData->first_name, $leadData->middle_name, $leadData->sur_name]) : null;
				$profileMobile = $mobile;
				$status = isset($leadData->status) ? $leadData->status : null;
				$lead_id = isset($leadData->lead_id) ? $leadData->lead_id : null;

				if (empty($pancard)) {
					$this->db->select('l.lead_id, l.pancard, l.status, l.mobile, lc.first_name, lc.middle_name, lc.sur_name');
					$this->db->from('leads l');
					$this->db->where('l.mobile', $mobile);
					$this->db->where('l.pancard is not null');
					$this->db->where('l.pancard !=', '');
					$this->db->join('lead_customer lc', 'lc.customer_lead_id = l.lead_id', 'left');
					$this->db->order_by('l.lead_id', 'DESC');
					$query = $this->db->get();
					$proData = $query->row();
					//return $this->response($proData, REST_Controller::HTTP_OK);

					$profileName = isset($proData->first_name) ? implode(' ', [$proData->first_name, $proData->middle_name, $proData->sur_name]) : null;
					$profileMobile = $mobile;
					$pancard = isset($proData->pancard) ? $proData->pancard : null;
					$status = isset($proData->status) ? $proData->status : null;
					$lead_id = isset($proData->lead_id) ? $proData->lead_id : null;
				}
				if ($status == 'DISBURSED') {
					$repaymentdata = $this->Lead->getLoanRepaymentDetails($lead_id);
				} else {
					$this->db->select('l.lead_id');
					$this->db->from('leads l');
					$this->db->where('l.mobile', $mobile);
					$this->db->where_in('l.status', ['DISBURSED', 'PART-PAYMENT']);
					$this->db->order_by('l.lead_id', 'DESC');
					$query = $this->db->get();
					$checkDis = $query->row();
					$lead_id = isset($checkDis->lead_id) ? $checkDis->lead_id : null;
					if ($lead_id) {
						$repaymentdata = $this->Lead->getLoanRepaymentDetails($lead_id);
					}
				}
				$this->db->select('l.lead_id, l.pancard, l.email, l.status, l.mobile, l.monthly_salary_amount, l.loan_amount, l.created_on, lc.first_name, lc.middle_name, lc.sur_name');
				$this->db->from('leads l');
				$this->db->where('l.mobile', $mobile);
				//$this->db->where_in('l.status', ['DISBURSED', 'NEW LEAD']);
				$this->db->join('lead_customer lc', 'lc.customer_lead_id = l.lead_id', 'left');
				$this->db->order_by('l.lead_id', 'DESC');
				$query = $this->db->get();
				$leads = $query->result_array();

				$res['Status'] = 1;
				$res['Message'] = 'Success';
				$res['currentLead'] = $leadData;
				$res['profileData']['full_name'] = $profileName;
				$res['profileData']['mobile'] = $profileMobile;
				$res['profileData']['pancard'] = $pancard;
				$res['profileData']['image'] = null;
				$res['profileData']['outstanding_amt'] = isset($repaymentdata['repayment_data']['total_due_amount']) ? $repaymentdata['repayment_data']['total_due_amount'] : 0;
				$res['leads'] = isset($leads) ? $leads : [];
				$res['repaymentdata'] = isset($repaymentdata['repayment_data']) ? $repaymentdata['repayment_data'] : [];

				return $this->response($res, REST_Controller::HTTP_OK);
			}
		} else {
			return $this->response(['Status' => 0, 'Message' => 'Request Method Post Failed or Authentication Failed.'], REST_Controller::HTTP_OK);
		}
	}

	public function getAllLeads_post() {

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
			$this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric');

			if ($this->form_validation->run() == FALSE) {

				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$mobile = $post['mobile'];

				$this->db->select('l.lead_id, l.pancard, l.email, l.status, l.mobile, l.monthly_salary_amount, l.loan_amount, l.created_on, lc.first_name, lc.middle_name, lc.sur_name');
				$this->db->from('leads l');
				$this->db->where('l.mobile', $mobile);
				//$this->db->where_in('l.status', ['DISBURSED', 'NEW LEAD']);
				$this->db->join('lead_customer lc', 'lc.customer_lead_id = l.lead_id', 'left');
				$this->db->order_by('l.lead_id', 'DESC');
				$query = $this->db->get();
				$result = $query->result_array();


				if (!empty($result)) {

					return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $result], REST_Controller::HTTP_OK));
				} else {

					return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
				}
			}
		}
	}


	public function getLeadDetail_post() {

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
			$this->form_validation->set_rules("lead_id", "Lead ID", "trim");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$lead_id = intval($post['lead_id']);

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
				$result = $this->db->query($select)->result_array();

				if (!empty($result)) {

					return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $result], REST_Controller::HTTP_OK));
				} else {

					return json_encode($this->response(['Status' => 0, 'Message' => "Application does not exist."], REST_Controller::HTTP_OK));
				}
			}
		}
	}

	public function getCibil_post() {

		$input_data = file_get_contents("php://input");

		if ($input_data) {
			$jsonInput = json_decode($input_data, true);
			$post = $this->security->xss_clean($jsonInput);
		} else {
			$post = $this->security->xss_clean($_POST);
		}

		$headers = $this->input->request_headers();
		$token = $this->_token();
		$header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

			$this->form_validation->set_data($post);
			$this->form_validation->set_rules("pancard", "PANCARD", "trim");

			if ($this->form_validation->run() == FALSE) {
				return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
			} else {

				$pancard = $post['pancard'];


				$this->db->select("cibilScore");
				$this->db->from('tbl_cibil_log');
				$this->db->where('pancard', $pancard);
				$this->db->order_by('cibilScore', 'DESC')->limit(1);
				$query = $this->db->get();
				$result = $query->row_array();
				$cibilScore = isset($result['cibilScore']) ? $result['cibilScore'] : null;


				if (!empty($cibilScore)) {

					return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'cibilScore' => $cibilScore], REST_Controller::HTTP_OK));
				} else {
					return json_encode($this->response(['Status' => 0, 'Message' => "Cibil not exist.", 'cibilScore' => 0], REST_Controller::HTTP_OK));
				}
			}
		} else {
			return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
		}
	}

	public function testApi_post() {
		$input_data = file_get_contents("php://input");
		if ($input_data) {
			$post = $this->security->xss_clean(json_decode($input_data, true));
		} else {
			$post = $this->security->xss_clean($_POST);
		}

		$headers = $this->input->request_headers();
		$token = $this->_token();
		$header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
		//if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
		$headers['ttt'] = base64_decode($headers['Auth']);
		return json_encode($this->response($token, REST_Controller::HTTP_OK));
		//}
	}
}
