<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
    class Sms_Model extends CI_Model
    {
		public function __construct()
		{
			parent::__construct();
            // $this->load->model('Task_Model');
	        define("username", urlencode("namanfinl"));
	        define("password", urlencode("ASX1@#SD"));
	        define("entityid", 1201159134511282286);

	    	$login = new IsLogin();
	    	$login->index();
		}
		
		public function queryGetLeadDetails($lead_id)
		{
		    return $this->db->select('name, mobile, gender, lead_id')->where('lead_id', $lead_id)->from('leads')->get()->row();   
		}
		
		public function queryGetCreditDetails($lead_id)
		{
		    return $this->db->select('loan_amount_approved, tenure, roi, lead_id')->where('lead_id', $lead_id)->from('credit')->get()->row();   
		}
		
		public function queryGetLoanDetails($lead_id)
		{
		    return $this->db->select('loan_amount, loan_admin_fee, customer_account_no, customer_bank, lead_id')->where('lead_id', $lead_id)->from('loan')->get()->row();   
		}
		
		public function smsSendRequestForUploadDocs($lead_id)
		{   
		    $query = $this->queryGetLeadDetails($lead_id);
		    $title = 'Ms.';
		    if($query->gender == 'Male' || $query->gender == 'MALE') {
		        $title = 'Mr.';
		    }
		    
		    $q_name = strtolower($query->name);
            preg_match('/[^ ]*$/', $q_name, $results);
            $name = ucfirst($results[0]);
		    $mobile = $query->mobile;
		    
		    $message = "Dear ". $title ." ". $name .",\nPlease email your documents \nfor quick sanction of loan at \nemail info@loanwalle.com \n- KYC (Aadhar, Pan Card, Voter id etc),\nlast 3 months bank statements,\nlast 2 months salary slip \n- Loanwalle (Naman Finlease)";
            $username 		= username;
            $password 		= password;
            $type 			= 0;
            $dlr 			= 1;
            $destination 	= $mobile;
            $source 		= "LWALLE";
            $message 		= urlencode($message);
            $entityid 		= entityid;
            $tempid 		= 1207161976652200579;
            
            $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
            $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
            				CURLOPT_URL => $url,
            				CURLOPT_RETURNTRANSFER => true,
            				CURLOPT_POST => true,
            				CURLOPT_POSTFIELDS => $data
            			));
            $output = curl_exec($ch);
            curl_close($ch); 
		}
		
		public function smsForSanctionedLoan($lead_id)
		{
		    $query = $this->queryGetLeadDetails($lead_id);
		    $queryGetCreditDetails = $this->queryGetCreditDetails($lead_id);
		    
		    $title = 'Ms.';
		    if($query->gender == 'Male' || $query->gender == 'MALE') {
		        $title = 'Mr.';
		    }
		    
		    $q_name = strtolower($query->name);
            preg_match('/[^ ]*$/', $q_name, $results);
            $name = ucfirst($results[0]);
		    $mobile = $query->mobile;
		    
		    $amount = number_format($queryGetCreditDetails->loan_amount_approved, 2);
		    $tenure = $queryGetCreditDetails->tenure;
		    $roi = $queryGetCreditDetails->roi. " %";
		    $message = "Dear ". $title ." ". $name .",\nyour loan amount is sanctioned\nfor Rs. ". $amount ."\nfor tenor of ". $tenure ." days\nat ROI of ". $roi ." per day\n- Loanwalle (Naman Finlease)";

            $username 		= username;
            $password 		= password;
            $type 			= 0;
            $dlr 			= 1;
            $destination 	= $mobile;
            $source 		= "LWALLE";
            $message 		= urlencode($message);
            $entityid 		= entityid;
            $tempid 		= 1207161976664361996;
            
            $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
            $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
            				CURLOPT_URL => $url,
            				CURLOPT_RETURNTRANSFER => true,
            				CURLOPT_POST => true,
            				CURLOPT_POSTFIELDS => $data
            			));
            $output = curl_exec($ch);
            curl_close($ch); 
		}
		
		public function smsForLoanDisbursed($lead_id)
		{
		    $query = $this->queryGetLeadDetails($lead_id);
		    $queryGetLoanDetails = $this->queryGetLoanDetails($lead_id);
		    
		    $title = 'Ms.';
		    if($query->gender == 'Male' || $query->gender == 'MALE') {
		        $title = 'Mr.';
		    }
		    
		    $q_name = strtolower($query->name);
            preg_match('/[^ ]*$/', $q_name, $results);
            $name = ucfirst($results[0]);
            
		    $mobile = $query->mobile;
		    $loan_admin_fee = $queryGetLoanDetails->loan_admin_fee;
		    $amount = $queryGetLoanDetails->loan_amount;
		    $disbursed_amount = number_format(($amount - $loan_admin_fee), 2);
		    $customer_account_no = $queryGetLoanDetails->customer_account_no;
		    $customer_bank = $queryGetLoanDetails->customer_bank;
		    $message = "Dear ". $title ." ". $name .", your loan amount is disbursed for Rs. ". $disbursed_amount ." to your bank account No ". $customer_account_no ." with ". $customer_bank ." - Loanwalle (Naman Finlease)";

            $username 		= username;
            $password 		= password;
            $type 			= 0;
            $dlr 			= 1;
            $destination 	= $mobile;
            $source 		= "LWALLE";
            $message 		= urlencode($message);
            $entityid 		= entityid;
            $tempid 		= 1207161976681576707;
            
            $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
            $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
            				CURLOPT_URL => $url,
            				CURLOPT_RETURNTRANSFER => true,
            				CURLOPT_POST => true,
            				CURLOPT_POSTFIELDS => $data
            			));
            $output = curl_exec($ch);
            curl_close($ch); 
		}
		
		public function smsForRejectLeads($lead_id)
		{
		    $query = $this->queryGetLeadDetails($lead_id);
		    $title = 'Ms.';
		    if($query->gender == 'Male' || $query->gender == 'MALE') {
		        $title = 'Mr.';
		    }
		    
		    $q_name = strtolower($query->name);
            preg_match('/[^ ]*$/', $q_name, $results);
            $name = ucfirst($results[0]);
		    $mobile = $query->mobile;
		    $lead_id = $query->lead_id;
		    
		    $message = "Dear Mr/Ms ". $name .",\nyour loan Application No. ". $lead_id ."\nis declined due to not\nmeeting company policies\n- Loanwalle (Naman Finlease)";

            $username 		= username;
            $password 		= password;
            $type 			= 0;
            $dlr 			= 1;
            $destination 	= $mobile;
            $source 		= "LWALLE";
            $message 		= urlencode($message);
            $entityid 		= entityid;
            $tempid 		= 1207162031164486731;
            
            $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
            $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
            				CURLOPT_URL => $url,
            				CURLOPT_RETURNTRANSFER => true,
            				CURLOPT_POST => true,
            				CURLOPT_POSTFIELDS => $data
            			));
            $output = curl_exec($ch);
            curl_close($ch); 
		}

    }
?>