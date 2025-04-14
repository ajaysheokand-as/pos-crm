<?php

    defined('BASEPATH') or exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';

    require APPPATH . 'libraries/Format.php';

	class Loan4Credit extends REST_Controller

	{

		public function __construct()

		{

			parent::__construct();

			$this->load->model('Token_api', 'Tokens');

            $this->load->model('Task_Model');

			

     		date_default_timezone_set('Asia/Kolkata');

            define('created_on', date('Y-m-d H:i:s'));

            define('Tbl_docs', 'docs');

            define('Tbl_leads', 'leads');

            

            // $token = md5("S-370-Engineer_Vinay-Kumar_LW_Paday");

            
		}



		
	    

		public function sendOTPToCustomer($data)

	    {  

    // 		$this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric|is_natural");

    //         if($this->form_validation->run() == FALSE)

    //         {

    //             $this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

    //         }

    //         else

    //         {

                define('smsusername', urlencode("namanfinl"));

                define('smspassword', urlencode("ASX1@#SD"));

                define('entityid', 1201159134511282286);

                $lead_id = $data['lead_id'];

                $title = $data['title'];

                $name = $data['name'];

                $mobile = $data['mobile'];

                $otp = $data['otp'];

                $message = "Dear ". $title ." ". $name .",\nYour mobile verification\nOTP is: ". $otp .".\nPlease don't share it with anyone - LW (Naman Finlease)";

            

                $username       = smsusername;

                $password       = smspassword;

                $type           = 0;

                $dlr            = 1;

                $destination    = $mobile;

                $source         = "LWAPLY";

                $message        = urlencode($message);

                $entityid 		= 1201159134511282286;

                $tempid 		= 1207161976462053311;

                

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

                if($output == true){

                    return true;

                } else {

                    return false;

                }

            // }

	    }

	    

	    public function sendOTPAppliedSuccessfully($data)

	    {

	        $title = $data['title'];

	        $name = $data['name'];

	        $mobile = $data['mobile'];

	        

	        $message = "Dear ". $title ." ". $name .",\nYour loan application is\nsuccessfully submitted.\nWe will get back to you soon.\n- Loanwalle (Naman Finlease)";

            $username        = urlencode("namanfinl");

            $password        = urlencode("ASX1@#SD");

            $type            = 0;

            $dlr             = 1;

            $destination     = $mobile;

            $source         = "LWALLE";

            $message         = urlencode($message);

            $entityid        = 1201159134511282286;

            $tempid      = 1207161976525243363;

            

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

	    

        public function resendAppliedCustomerOTP_post()

        {

    		$input_data = file_get_contents("php://input");

    		$post = $this->security->xss_clean(json_decode($input_data,true));

    		if ($input_data) { 

    			$post = $this->security->xss_clean(json_decode($input_data,true));

    		} else {

    			$post = $this->security->xss_clean($_POST);

    		}

    		$headers = $this->input->request_headers();

            $token = $this->_token();

            $header_validation = (

        	    ($headers['Accept'] == "application/json")

            	&& ($token['token_Leads'] == $headers['Auth']) 

	        );



	        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation)

	        {   

                $this->form_validation->set_data($this->post());

        		$this->form_validation->set_rules("lead_id", "Lead ID", "required|trim|numeric|is_natural|regex_match[/^[0-9]+$/]");

                if($this->form_validation->run() == FALSE)

                {

	                json_encode($this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));

                }

                else

                {

					$query = $this->db->select('LD.customer_id, LD.lead_id, LD.name, LD.gender, LD.mobile')

					        ->where('LD.lead_id', $post['lead_id'])

					        ->from(Tbl_leads.' LD')

					        ->get();

                    

					if($query->num_rows() > 0) {

					    $row = $query->row();

					    $otp = rand(1000, 9999);

					    $data = [

					        'lead_id'       => $row->lead_id,

					        'title'         => ($row->gender == "MALE" || $row->gender == "Male") ? "Mr." : "Ms.",

					        'mobile'        => $row->mobile,

					        'otp'           => $otp,

				        ];

				        $this->db->where('lead_id', $post['lead_id'])->update(Tbl_leads.' LD', ['otp' => $otp]);

				        $this->sendOTPToCustomer($data);

        	            json_encode($this->response(['Status' => 1, 'Message' =>'Success.', 'Data' => "OTP Send Successfully"], REST_Controller::HTTP_OK));

        	        }else{

    	                json_encode($this->response(['Status' => 0, 'Message' =>'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));

        	        }

                }

            }else{

    	        json_encode($this->response(['Status' => 0, 'Message' =>'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));

            }

        }

	   
        

        public function userRegistration_post()

        {

    		$input_data = file_get_contents("php://input");

    		$post = $this->security->xss_clean(json_decode($input_data,true));

    		if ($input_data) { 

    			$post = $this->security->xss_clean(json_decode($input_data,true));

    		} else {

    			$post = $this->security->xss_clean($_POST);

    		}

	        if($_SERVER['REQUEST_METHOD'] == 'POST')

	        {   

                $this->form_validation->set_data($this->post());

        		$this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
        		$this->form_validation->set_rules("password", "Password", "required|trim");

                if($this->form_validation->run() == FALSE)

                {
	                json_encode($this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                }
                else
                {
                    $mobile = $post['mobile'];
                    $password = $post['password']; 
                    $otp = rand(1000, 9999);
    	            $data = [
    	                "mobile"    => $mobile,
    	                "otp"       => $otp
	                ];

					// $query = $this->db->select('customer_id')->where('mobile', $mobile)->from('customer')->get();
	                $query = $this->db->select('id')->where('phone', $mobile)->where('password', $password)->from('users')->get();


                    $customer_id = 0;

					if($query->num_rows() > 0) {
					    $result1 = $query->row();
					  	$customer_id = $result1->id;
						$this->db->where('id', $customer_id)->update('users', ['otp' => $otp]);
    	                $this->sendOTPForUserRegistrationVerification($data);
					}

					else

					{

						$last_row = $this->db->select('users.id')
						        ->from('usres')
						        ->order_by('id', 'desc')
						        ->limit(1)
						        ->get()
						        ->row();

						$str = preg_replace('/\D/', '', $last_row->customer_id);

						$customer_id= "FTLC".str_pad(($str + 1), 6, "0", STR_PAD_LEFT);

						$pancard = $mobile;

						$dataCustomer = array(

							'customer_id'	=> $customer_id,

							'mobile'		=> $mobile,

							'pancard'		=> $pancard,

							'otp'		    => $otp,

							'created_date'	=> updated_at

						);

						$resultCustomer = $this->db->insert('users', $dataCustomer);

    	                $this->sendOTPForUserRegistrationVerification($data);

					}

        	        if($customer_id){

        	            json_encode($this->response(['Status' => 1, 'Message' =>'Success.', 'customer_id' => $customer_id, 'mobile' => $mobile, 'pancard' => $pancard], REST_Controller::HTTP_OK));

        	        }else{

    	                json_encode($this->response(['Status' => 0, 'Message' =>'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));

        	        }

                }

            }else{

    	        json_encode($this->response(['Status' => 0, 'Message' =>'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));

            }

        }
        

       

        

        public function sendOTPForUserRegistrationVerification($data)

        {

            $mobile = $data['mobile'];

            $otp = $data['otp'];

            $message = "Dear Mr/Ms User,\nYour mobile verification\nOTP is: ". $otp .".\nPlease don't share it with anyone - LW (Naman Finlease)";

            

            $username       = urlencode("namanfinl");

            $password       = urlencode("ASX1@#SD");

            $type           = 0;

            $dlr            = 1;

            $destination    = $mobile;

            $source         = "LWAPLY";

            $message        = urlencode($message);

            $entityid       = 1201159134511282286;

            $tempid         = 1207161976462053311;

            

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