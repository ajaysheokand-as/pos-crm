<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	require APPPATH . 'libraries/REST_Controller.php';
	class RepayLoanApi extends REST_Controller
	{
		public function __construct()
		{
			parent::__construct();
		}

        public function getLoanDetails_post()
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
        	    ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])) 
	        );

	        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation)
	        {   
                $this->form_validation->set_data($this->post());
        		$this->form_validation->set_rules("loan_no", "Loan NO", "required|trim|regex_match[/^[a-zA-Z0-9]+$/]");
                if($this->form_validation->run() == FALSE)
                {
	                json_encode($this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                }
                else
                {
					$query = $this->db->select('L.*')
					        ->where('L.loan_no', $post['loan_no'])
					        ->from('loan L')
					        ->get();

					if($query->num_rows() > 0) 
					{
					    $row = $query->row();
					    
        	            json_encode($this->response(['Status' => 1, 'Message' =>'Success.', 'Data' => $row], REST_Controller::HTTP_OK));
        	        }else{
    	                json_encode($this->response(['Status' => 0, 'Message' =>'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        	        }
                }
            }else{
    	        json_encode($this->response(['Status' => 0, 'Message' =>'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
            }
        }
	    
	    
        public function maildisburse()
        {
             $loan_no = "Nfpl01234";
            $request = [
                'to'        => 'vinay@loanwalle.com',
                'from'      => 'info@loanwalle.com',
                'subject'   => 'sanction 98789789789',
                'message'   => 'hi'
            ];
                
            $cartJsonData = json_encode($request);
            
            $url = 'https://www.loanwalle.com/DisbursalController/restSentMail';
            $curl = curl_init($url);
            curl_setopt_array($curl, array(
              //CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_POST => true,
              CURLOPT_POSTFIELDS => $request,
              CURLOPT_HTTPHEADER => array(
                // 'content-type:application/json',
                // 'Content-Length:'. strlen($cartJsonData),
                // 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhcGlfdG9rZW4iOiJEZWVwYWsgd2l0aCB2aW5heSB0ZXN0aW5nIGFwaSBpbiBqd3QiLCJBUElfVElNRSI6MTYxNzcwNzQ5Nn0.OoDOOZLdli9MGoa03fz_0ahs03SCBHXOxehd7LUVizA'
              ),
            ));
            
            $response = curl_exec($curl);
            
            $err = curl_error($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else{
                $loanDetails = json_decode(($response), true);
            }
		    
        }
	}



?>