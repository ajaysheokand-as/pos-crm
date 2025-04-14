<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Support_Model extends CI_Model {

    public function delete($conditions) {
        return $this->db->where($conditions)->delete($this->table);
    }

    public function updatePersonalData($data) {
		
        $user_id = $data['user_id'];
        $check_email = $data['check_email'];
        $check_alternate_email = $data['check_alternate_email'];
        $check_mobile = $data['check_mobile'];
        $check_alternate_mobile = $data['check_alternate_mobile'];
        $lead_id = $data['lead_id'];
        $email = $data['email'];
        $alternate_email = $data['alternate_email'];
        $mobile = $data['mobile'];
        $alternate_mobile = $data['alternate_mobile'];
        $loan_amount = $data['loan_amount'];
        $pancard = $data['pancard'];
        $gender = $data['gender'];
        $dob = date('Y-m-d', strtotime($data['dob']));
        $religion_id = $data['religion_id'];
        $marital_status_id = $data['marital_status_id'];
        $qualification = $data['qualification'];
        $spouse_name = $data['spouse_name'];
        $spouse_occupation_id = $data['spouse_occupation_id'];
        $current_house = $data['current_house'];
        $current_locality = $data['current_locality'];
        $current_landmark = $data['current_landmark'];
        $current_state = $data['current_state'];
        $current_city = $data['current_city'];
        $residence_pincode = $data['residence_pincode'];
        $source = $data['source'];
        $utm_source = $data['utm_source'];
        $utm_campaign = $data['utm_campaign'];
        $lead_data_source = $data['lead_data_source'];
        $lead_stp_flag = $data['lead_stp_flag'];
        $lead_screener_assign_user = $data['lead_screener_assign_user'];
        $stage = $data['stage'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];
        //echo '<pre>';print_r($data);die;
        //echo $this->db->last_query();die;
        $arr_data_leads = array();
        $arr_data_lead_customer = array();		
        $this->insertApplicationLog($lead_id,$lead_followup_remark);
        $get_master_status = $this->db->select('status_id,status_name,status_stage,status_order')->from('master_status')->where('status_stage', $stage)->where('status_active', 1)->get()->row_array();
        $arr_data_docs = ['pancard' => $pancard];
        $arr_data_leads = ['email' => $email, 'alternate_email' => $alternate_email, 'mobile' => $mobile, 'loan_amount' => $loan_amount, 'pancard' => $pancard, 'source' => $source, 'utm_source' => $utm_source, 'utm_campaign' => $utm_campaign, 'lead_data_source_id' => $lead_data_source, 'lead_stp_flag' => $lead_stp_flag, 'stage' => $stage, 'status' => $get_master_status['status_name'], 'lead_status_id' => $get_master_status['status_id'], 'lead_screener_assign_user_id' => $lead_screener_assign_user, 'lead_screener_assign_datetime' => date('Y-m-d H:i:s')];
        $arr_data_lead_customer = ['customer_qualification_id' => $qualification, 'pancard' => $pancard, 'gender' => $gender, 'dob' => $dob, 'customer_religion_id' => $religion_id, 'customer_marital_status_id' => $marital_status_id, 'customer_spouse_name' => $spouse_name, 'current_house' => $current_house, 'current_locality' => $current_locality, 'current_landmark' => $current_landmark, 'current_state' => $current_state, 'current_city' => $current_city, 'cr_residence_pincode' => $residence_pincode, 'customer_spouse_occupation_id' => $spouse_occupation_id];
        $cif_customer_arr = ['cif_gender' => $gender, 'cif_dob' => $dob, 'cif_pancard' => $pancard, 'cif_alternate_mobile' => $alternate_mobile, 'cif_religion_id' => $religion_id, 'cif_marital_status_id' => $marital_status_id, 'cif_spouse_name' => $spouse_name, 'cif_spouse_occupation_id' => $spouse_occupation_id, 'cif_residence_address_1' => $current_house, 'cif_residence_address_2' => $current_locality, 'cif_residence_landmark' => $current_landmark, 'cif_residence_state_id' => $current_state, 'cif_residence_city_id' => $current_city, 'cif_residence_pincode' => $residence_pincode];
        if ($check_email == 'true') {
            $arr_data_lead_customer['email'] = $email;
            $arr_data_lead_customer['email_verified_status'] = '';
            $arr_data_lead_customer['email_verified_on'] = '';
            $cif_customer_arr['cif_personal_email'] = $email;
        } else {
            unset($arr_data_lead_customer['email']);
            unset($cif_customer_arr['cif_personal_email']);
        }
        if ($check_alternate_email == 'true') {
            $arr_data_lead_customer['alternate_email'] = $alternate_email;
            $arr_data_lead_customer['alternate_email_verified_status'] = '';
            $arr_data_lead_customer['alternate_email_verified_on'] = '';
            $cif_customer_arr['cif_office_email'] = $alternate_email;
        } else {
            unset($arr_data_lead_customer['alternate_email']);
            unset($cif_customer_arr['cif_office_email']);
        }
        if ($check_mobile == 'true') {
            $arr_data_lead_customer['mobile'] = $mobile;
            $arr_data_lead_customer['mobile_verified_status'] = '';
            $cif_customer_arr['cif_mobile'] = $mobile;
        } else {
            unset($arr_data_lead_customer['mobile']);
            unset($cif_customer_arr['cif_mobile']);
        }
        if ($check_alternate_mobile == 'true') {
            $arr_data_lead_customer['alternate_mobile'] = $alternate_mobile;
            $cif_customer_arr['cif_mobile'] = $alternate_mobile;
        } else {
            unset($arr_data_lead_customer['mobile']);
            unset($cif_customer_arr['cif_mobile']);
        }
        if (!empty($pancard)) {
            $cif_customer_count = $this->db->select('*')->where(['cif_pancard' => $pancard])->from('cif_customer')->get()->num_rows();
            if ($cif_customer_count > 0) {
                $this->db->where(['cif_pancard' => $pancard])->update('cif_customer', $cif_customer_arr);
            }
        }
        $this->db->where(['lead_id' => $lead_id])->update('leads', $arr_data_leads);		
        $this->db->where(['lead_id' => $lead_id])->update('docs', $arr_data_docs);
        $updated = $this->db->where(['customer_lead_id' => $lead_id])->update('lead_customer', $arr_data_lead_customer);
        return $updated;
    }

    public function updateEmploymentData($data) {
        $user_id = $data['user_id'];
        $lead_id = $data['lead_id'];
        $employer_name = $data['employer_name'];
        $emp_email = $data['emp_email'];
        $emp_house = $data['emp_house'];
        $emp_street = $data['emp_street'];
        $emp_landmark = $data['emp_landmark'];
        $state = $data['state'];
        $city = $data['city'];
        $emp_pincode = $data['emp_pincode'];
        $emp_residence_since = $data['emp_residence_since'];
        $emp_designation = $data['emp_designation'];
        $emp_department = $data['emp_department'];
        $emp_occupation_id = $data['emp_occupation_id'];
        $emp_employer_type = $data['emp_employer_type'];
        $salary_mode = $data['salary_mode'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];

        $arr_data_customer_employment = [
            'emp_occupation_id' => $emp_occupation_id,
            'employer_name' => $employer_name,
            'emp_email' => $emp_email,
            'emp_house' => $emp_house,
            'emp_street' => $emp_street,
            'emp_landmark' => $emp_landmark,
            'state_id' => $state,
            'city_id' => $city,
            'emp_pincode' => $emp_pincode,
            'emp_residence_since' => $emp_residence_since,
            'emp_designation' => $emp_designation,
            'emp_department' => $emp_department,
            'emp_employer_type' => $emp_employer_type,
            'salary_mode' => $salary_mode];

        $updated = $this->db->where(['lead_id' => $lead_id])->update('customer_employment', $arr_data_customer_employment);

        $this->insertApplicationLog($lead_id, $lead_followup_remark);

        return $updated;
    }

    public function updateReferenceData($data) {
        $user_id = $data['user_id'];
        $lcr_id = $data['lcr_id'];
        $lead_id = $data['lead_id'];
        $lcr_name = $data['lcr_name'];
        $lcr_mobile = $data['lcr_mobile'];
        $lcr_relationType = $data['lcr_relationType'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];

        $arr_data_customer_reference = ['lcr_name' => $lcr_name, 'lcr_mobile' => $lcr_mobile, 'lcr_relationType' => $lcr_relationType];
        $updated = $this->db->where(['lcr_id' => $lcr_id])->update('lead_customer_references', $arr_data_customer_reference);
        $this->insertApplicationLog($lead_id, $lead_followup_remark);
        return $updated;
    }

    public function updateTransactionData($data) {
        $user_id = $data['user_id'];
        $disb_trans_id = $data['disb_trans_id'];
        $lead_id = $data['lead_id'];
        $disburse_refrence_no = $data['disburse_refrence_no'];
        $disb_trans_payment_mode = $data['disb_trans_payment_mode'];
        $loan_disbursement_payment_type = $data['loan_disbursement_payment_type'];
        $disb_trans_status = $data['disb_trans_status'];
        $remarks = $data['remarks'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];

        if ($disb_trans_payment_mode == '1') {
            $mode_of_payment = 'Online';
        } else {
            $mode_of_payment = 'Offline';
        }
        if ($loan_disbursement_payment_type == '1') {
            $channel = 'IMPS';
        } else {
            $channel = 'NEFT';
        }
        $arr_data_trans = ['disb_trans_status_id' => $disb_trans_status];
        $arr_data_loan = ['mode_of_payment' => '', 'channel' => '', 'loan_disbursement_payment_mode_id' => $disb_trans_payment_mode, 'loan_disbursement_payment_type_id' => $loan_disbursement_payment_type, 'disburse_refrence_no' => $disburse_refrence_no, 'remarks' => $remarks, 'loan_disbursement_trans_status_id' => NULL, 'loan_disbursement_trans_status_datetime' => NULL, 'loan_disbursement_trans_log_id' => NULL];
        $this->db->where(['lead_id' => $lead_id])->update('loan', $arr_data_loan);
        $updated = $this->db->where(['disb_trans_id' => $disb_trans_id])->update('lead_disbursement_trans_log', $arr_data_trans);
        $this->insertApplicationLog($lead_id, $lead_followup_remark);
        return $updated;
    }

    public function updateBankData($data) {
        $user_id = $data['user_id'];
        $bank_name = $data['bank_name'];
        $lead_id = $data['lead_id'];
        $ifsc_code = $data['ifsc_code'];
        $beneficiary_name = $data['beneficiary_name'];
        $account_status = $data['account_status'];
        $account = $data['account'];
        $account_type = $data['account_type'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];

        $arr_data_customer_banke = ['bank_name' => $bank_name, 'ifsc_code' => $ifsc_code, 'beneficiary_name' => $beneficiary_name, 'account_status' => $account_status, 'account' => $account, 'account_type' => $account_type];
        $updated = $this->db->where(['lead_id' => $lead_id])->update('customer_banking', $arr_data_customer_banke);
        $this->insertApplicationLog($lead_id, $lead_followup_remark);
        return $updated;
    }

    public function updateDocsData($data) {
        $user_id = $data['user_id'];
        $lead_id = $data['lead_id'];
        //$pancard = $data['pancard'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];
        //$arr_data_docs = ['pancard' => $pancard];
        //$updated = $this->db->where(['lead_id' => $lead_id])->update('docs', $arr_data_docs);
        $updated = $this->insertApplicationLog($lead_id,$lead_followup_remark);
        //echo '<pre>';print_r($updated);die;
        return $updated;
    }

    public function updateCAMData($data) {
        $user_id = $data['user_id'];
        $lead_id = $data['lead_id'];
        $salary_credit1_date = $data['salary_credit1_date'];
        $salary_credit1_amount = $data['salary_credit1_amount'];
        $salary_credit2_date = $data['salary_credit2_date'];
        $salary_credit2_amount = $data['salary_credit2_amount'];
        $salary_credit3_date = $data['salary_credit3_date'];
        $salary_credit3_amount = $data['salary_credit3_amount'];
        $next_pay_date = $data['next_pay_date'];
        $median_salary = $data['median_salary'];
        $remark = $data['remark'];
        $lead_followup_remark = $data['lead_followup_remark'];
        $updated_by = $data['updated_by'];
        $updated_on = $data['updated_on'];

        if (!empty($salary_credit1_amount) && empty($salary_credit2_amount) && empty($salary_credit3_amount)) {
            $median_salary = ($salary_credit1_amount) / 1;
        }
        if (!empty($salary_credit1_amount) && !empty($salary_credit2_amount) && empty($salary_credit3_amount)) {
            $total_amt = ($salary_credit1_amount + $salary_credit2_amount);
            $median_salary = $total_amt / 2;
        }
        if (!empty($salary_credit1_amount) && !empty($salary_credit2_amount) && !empty($salary_credit3_amount)) {
            $total_amt = ($salary_credit1_amount + $salary_credit2_amount + $salary_credit3_amount);
            $median_salary = $total_amt / 3;
        }

        $arr_data_cam = ['salary_credit1_date' => $salary_credit1_date, 'salary_credit1_amount' => $salary_credit1_amount, 'salary_credit2_date' => $salary_credit2_date, 'salary_credit2_amount' => $salary_credit2_amount, 'salary_credit3_date' => $salary_credit3_date, 'salary_credit3_amount' => $salary_credit3_amount, 'next_pay_date' => $next_pay_date, 'median_salary' => $median_salary, 'remark' => $remark];
        $updated = $this->db->where(['lead_id' => $lead_id])->update('credit_analysis_memo', $arr_data_cam);
        $this->insertApplicationLog($lead_id, $lead_followup_remark);
        return $updated;
    }

    public function insertApplicationLog($lead_id, $lead_followup_remark, $lead_followup_status_id = null) {

        $return_array = array("status" => 0, "message" => "");
        $user_id = $_SESSION['isUserSession']['user_id'];

        if (!empty($lead_id) && !empty($lead_followup_remark)) {

            $lead_followup_list = $this->db->select('*')->where(['lead_id' => $lead_id])->from('lead_followup')->order_by('id', 'DESC')->get()->row();
            if (!empty($lead_followup_list->id) && $lead_followup_list->id > 0) {
                $insertLeadFollowup = [
                    'customer_id' => $lead_followup_list->customer_id,
                    'lead_id' => $lead_id,
                    'user_id' => $user_id,
                    'remarks' => $lead_followup_remark,
                    'status' => $lead_followup_list->status,
                    'stage' => $lead_followup_list->stage,
                    'created_on' => date('Y-m-d H:i:s'),
                    'lead_followup_status_id' => $lead_followup_list->lead_followup_status_id
                ];
                $inserted_id = $this->db->insert('lead_followup', $insertLeadFollowup);

                if ($inserted_id) {
                    $return_array['status'] = 1;
                }
            } else {
                if (empty($lead_followup_status_id)) {
                    $return_array['message'] = "lead_status_id is blank";
                    return $return_array;
                }

                $insertLeadFollowup = [
                    'customer_id' => "",
                    'lead_id' => $lead_id,
                    'user_id' => $user_id,
                    'remarks' => $lead_followup_remark,
                    'created_on' => date('Y-m-d H:i:s'),
                    'lead_followup_status_id' => $lead_followup_status_id
                ];
                $inserted_id = $this->db->insert('lead_followup', $insertLeadFollowup);

                if ($inserted_id) {
                    $return_array['status'] = 1;
                }
            }
        }
        return $return_array;
    }
	
	public function pincodeListCount($conditions) {
        $this->db->select("m_pincode_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('m_pincode_active', 1);
        $this->db->where('m_pincode_deleted', 0);
        return $this->db->from('master_pincode')->get()->num_rows();
    }
	
	public function pincodeList($limit, $start = null, $conditions = array()) {
        $this->db->select('*');
        $this->db->from("master_pincode");
        $this->db->distinct();
        $this->db->limit($limit,$start);
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('m_pincode_active',1);
        $this->db->where('m_pincode_deleted',0);
        $return = $this->db->order_by('m_pincode_id','desc')->get()->result_array();
        return $return;
    }
    
       public function checkSupportPincode($pincode,$support_pincode_id=0) {
         $return_val = false;
         $condition = array();
         $condition["LOWER(m_pincode_value)"] = trim(strtolower($pincode));
         if(!empty($m_pincode_id)) {
            $condition["m_pincode_id!="] = $m_pincode_id;		   
         }
                 $condition["m_pincode_active="] = 1;
                 $condition["m_pincode_deleted="] = 0;
         $tempDetails = $this->db->select('m_pincode_value')->from('master_pincode')->where($condition)->get();
         if($tempDetails->num_rows()) {
             $return_val = true;
         }
         return $return_val;
     }
}

?>
