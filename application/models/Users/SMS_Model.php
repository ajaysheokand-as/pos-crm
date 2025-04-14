<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	 
    class SMS_Model extends CI_Model
    {
        private $table_send_sms = 'ftc_send_sms';

        public function __construct()
        {
        	parent::__construct();

	        define("username", urlencode("namanfinl"));
	        define("password", urlencode("ASX1@#SD"));
	        define("entityid", 1201159134511282286);
        }

		public function getRejectionSMS($company_id, $product_id, $smsType)
		{
		    $where = array('company_id' => $company_id, 'product_id' => $product_id, 'sms_type'=> $smsType, 'is_active'=> 1);
	        return $this->db->select('message')->where($where)->from($this->table_send_sms)->get();
		}
		
	    public function notification($mobile, $msg)
		{
            $username 		= username;
            $password 		= password;
            $type 			= 0;
            $dlr 			= 1;
            $destination 	= $mobile;
            $source 		= "LWALLE";
            $message 		= urlencode($msg);
            $entityid 		= entityid;
            $tempid 		= 1207162031164486731;
			
			$data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message";
			$url = "http://sms6.rmlconnect.net/bulksms/bulksms";
			
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

		public function smsForRejectLeads($application_no, $name, $mobile)
		{   
		    $message = "Dear Mr/Ms ". $name .",\nyour loan Application No. ". $application_no ."\nis declined due to not\nmeeting company policies\n- Loanwalle (Naman Finlease)";

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
            return $output = curl_exec($ch);
            curl_close($ch); 
		}
    }
?>