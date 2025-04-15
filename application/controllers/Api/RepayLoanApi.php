<?php

	defined('BASEPATH') OR exit('No direct script access allowed');



	require APPPATH . 'libraries/REST_Controller.php';

	class RepayLoanApi extends REST_Controller

	{

		public function __construct()

		{

			parent::__construct();

		}



		public function index_get($pancard = "")

		{

			if(!empty($pancard)){

            	$data = $this->db->get_where("leads", ['pancard' => $pancard])->row_array();

	        } else {

	            $data = $this->db->get("leads")->result();

	        }

	     

	        $this->response($data, REST_Controller::HTTP_OK);

		}

		

		public function getDataByPancard_post()

		{	

	        if($_SERVER['REQUEST_METHOD'] == 'POST')

            {

                $this->form_validation->set_rules("pancard", "Pancard", "trim|required|min_length[10]|max_length[10]|regex_match[/[a-zA-z]{5}\d{4}[a-zA-Z]{1}/]");
                $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|numeric|min_length[10]|max_length[10]"); 

                if($this->form_validation->run() == FALSE)

                {

        	        $this->response(validation_errors(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

                }

                else

                {

        	        $pancard = $this->input->post('pancard');

        	        $mobile  = $this->input->post('mobile');

                    $query = $this->db->select('loan.*, credit.mobile, credit.email, credit.pancard, leads.status')

                        ->where("leads.pancard LIKE '%$pancard%'")

                        ->where("leads.mobile LIKE '%$mobile%'")

                        ->where("loan.loan_status !='Pre Disburse'")

                        ->where("loan.loan_status !='Disburse Pending'")

                        ->where('leads.loan_approved', 3)

                        ->from("leads")

                        // ->join('tb_states', 'leads.state_id = tb_states.id')

                        ->join('credit', 'leads.lead_id = credit.lead_id')

                        ->join('loan', 'leads.lead_id = loan.lead_id')

                        ->order_by('leads.lead_id', 'desc')

                        ->get();

        			$effected_rows = $query->result();

        			if($effected_rows > 0)

        			{

    	    	        $result = json_encode($effected_rows);

            	        $this->response($result, REST_Controller::HTTP_OK);

        			} else {

    	                $this->response(['No Record found.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

        			}

                }

            } else {

    	        $this->response(['Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

            }

		}

	    

	    public function getProductDetails_post()

	    {

	        if($_SERVER['REQUEST_METHOD'] == 'POST')

            {

                $this->form_validation->set_rules("lead_id", "Lead Id", "trim|required");

                if($this->form_validation->run() == FALSE)

                {

        	        $this->response(validation_errors(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

                }

                else

                {

        	        $lead_id = $this->input->post('lead_id');

        	        $getProductDetails = $this->db->query("SELECT L.branch, L.lead_id, L.loan_no, L.lan, L.customer_name, L.email, C.mobile, C.pancard, 

        	                    L.loan_amount, L.loan_intrest, L.loan_disburse_date, L.loan_repay_date, LL.status, C.tenure, L.loan_repay_amount

        	                    FROM loan L 

        	                    INNER JOIN credit C ON L.lead_id=C.lead_id 

        	                    INNER JOIN leads LL ON L.lead_id=LL.lead_id 

        	                        AND LL.status IN('Disbursed', 'Part Payment') 

        	                        AND C.status LIKE'%Sanction%' 

        	                        AND L.loan_status LIKE'%Disbursed%' 

        	                        AND L.lead_id=".$lead_id);

                    

        	        $data['itemInfo'] = $getProductDetails->result();

                

        		    $sql = $this->db->query("SELECT SUM(`payment_amount`) as payment_amount FROM recovery WHERE PaymentVerify = 1 AND lead_id = ".$lead_id);

        		    

        			$query = $this->db->query("SELECT IFNULL(SUM(`payment_amount`), 0) as bouncing_charge FROM recovery WHERE status  IN ('Bouncing Charges') AND lead_id = ".$lead_id);

        

        			$data['payment_amount'] = $sql->result();

        			$data['bouncing_charge'] = $query->result();

        	        $data['currency_code'] = 'INR';
        	        // $data['hrjjjj'] = '========';

        	        

	    	      //$result = json_encode($data);

	    	        $result = $data;

        	        $this->response($result, REST_Controller::HTTP_OK);

                }

            } else {

    	        $this->response(['Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

            }

	    }

	    public function getProductDetails1_post()

	    {

	        if($_SERVER['REQUEST_METHOD'] == 'POST')

            {

                $this->form_validation->set_rules("lead_id", "Lead Id", "trim|required");

                if($this->form_validation->run() == FALSE)

                {

        	        $this->response(validation_errors(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

                }

                else

                {

        	        $lead_id = $this->input->post('lead_id');

        	        $getProductDetails = $this->db->query("SELECT L.branch, L.lead_id, L.loan_no, L.lan, L.customer_name, L.email, C.mobile, C.pancard, 

        	                    L.loan_amount, L.loan_intrest, L.loan_disburse_date, L.loan_repay_date, LL.status, C.tenure, L.loan_repay_amount

        	                    FROM loan L 

        	                    INNER JOIN credit C ON L.lead_id=C.lead_id 

        	                    INNER JOIN leads LL ON L.lead_id=LL.lead_id 

        	                        AND LL.status IN('Disbursed', 'Part Payment') 

        	                        AND C.status LIKE'%Sanction%' 

        	                        AND L.loan_status LIKE'%Disbursed%' 

        	                        AND L.lead_id=".$lead_id);

                   echo $this->db->last_query();

        	        $data['itemInfo'] = $getProductDetails->result();

                

        		    $sql = $this->db->query("SELECT SUM(`payment_amount`) as payment_amount FROM recovery WHERE PaymentVerify = 1 AND lead_id = ".$lead_id);

        		    

        			$query = $this->db->query("SELECT IFNULL(SUM(`payment_amount`), 0) as bouncing_charge FROM recovery WHERE status  IN ('Bouncing Charges') AND lead_id = ".$lead_id);

        

        			$data['payment_amount'] = $sql->result();

        			$data['bouncing_charge'] = $query->result();

        	        $data['currency_code'] = 'INR';
        	        $data['hrjjjj'] = '========';

        	        

	    	      //$result = json_encode($data);

	    	        $result = $data;
	    	    $status=$data['itemInfo'][0]->status;
	    	       //echo "<pre>";print_r($data['itemInfo']); echo "hi"; die;
	    	       
	    	       $pancard      		= $data['itemInfo'][0]->pancard;
	                	$loan_amount  		=  $data['itemInfo'][0]->loan_amount;
	                	$loan_no      		=  $data['itemInfo'][0]->loan_no;
	                	$f_tenure     	=  $data['itemInfo'][0]->tenure;
	                	$customer_name 		=  $data['itemInfo'][0]->customer_name;
	                	$emailId        	=  $data['itemInfo'][0]->email;
	                	$mobile       		=  $data['itemInfo'][0]->mobile;
	                	$loan_intrest  		=  $data['itemInfo'][0]->loan_intrest;
	                	$loan_repay_date 	=  $data['itemInfo'][0]->loan_repay_date;
	                	$loan_disburse_date =  $data['itemInfo'][0]->loan_disburse_date;
	                	$status       	=  $data['itemInfo'][0]->status;
	                	$currency_code 	=  $data['itemInfo'][0]->currency_code;
	                	$loan_repay_amount =  $data['itemInfo'][0]->loan_repay_amount;
	                	$Bouncing_charge   	= 0;
	    	       if($status == 'Full Payment' || $status == 'Settelment')
		                    {
		                    	$status = 'Closed';
		                    }
		                    else
		                    {
		                    	$status = 'Active';
		                    }

		                    $loanAmt = $loan_amount;
		        			$roi = $loan_intrest; 
		        			date_default_timezone_set('Asia/Kolkata');

		        			$disburseddate = date('Y-m-d', strtotime($loan_disburse_date)); 
		                    $repaymentdate = date('Y-m-d', strtotime($loan_repay_date));
		                    $now = date('Y-m-d');
		                    $date1 = strtotime($now);  
		                    $date2 = strtotime($disburseddate);
		                    $date3 = strtotime($repaymentdate);
		                    $diff = $date3 - $date2;
		                    $tenure = ($diff / 60/60/24); 
		                    
		                    $realint = $date1 - $date2;
		                    $realDays = ($realint / 60/60/24); 
		                    
		                    if($date1 <= $date3)
		                    {
		                        $realdays = $date1 - $date2;
		                        $rtenure = ($realdays / 60/60/24);
		                        $ptenure = 0;
		                    }
		                    else
		                    {
		                        $endDate = $date1 - $date3;
					            $oneDay = (60*60*24);
					            $dateDays60 = ($oneDay * 60); 
					            
					             
		                        $realdays = $date3 - $date2;
		                        $rtenure = ($realdays / 60/60/24);
					               if($endDate <= $dateDays60) 
					               { 
    		                        $paneldays = $date1 - $date3;
    		                        $ptenure = ($paneldays / 60/60/24);
					               }
					               else
					                {
    				                    $ptenure = 60;
    				                }
		                    } 
		                   
		                    $realIntrest 	= ($loan_amount*$loan_intrest*$rtenure)/100; 
		                    $penaltyIntrest = ($loan_amount*$loan_intrest*2*$ptenure)/100;
		                    $paidAmount 	= $payment_amount;
		                    $repayAmount 	= $loan_amount + $realIntrest + $penaltyIntrest - $paidAmount;
		                   
	                        $lead_id=$lead_id;
	                        $productinfo = $loan_no;
	                        $txnid = time();
	                        $surl = $surl;
	                        $furl = $furl;        
	                        $key_id = 'rzp_test_zNnHRltGuhdp2m';
	                        $currency_code = $currency_code;            
	                        $total = ($repayAmount); 
	                        $amount = $repayAmount;
	                        $merchant_order_id = $loan_no;
	                        $card_holder_name = $customer_name;
	                        $email = $emailId;
	                        $phone = $mobile;
	                        $name = 'Aman Fincap Limited';
	                        $return_url = $rurl;
	                      //echo  "=====".$data['itemInfo']->branch;
	                    //  echo "<pre>";print_r($data['itemInfo']);
	                        $dataa=array(
	                            'branch'=> $data['itemInfo'][0]->branch,
	                            'loan_no'=> $data['itemInfo'][0]->loan_no,
	                            'customer_name'=> $data['itemInfo'][0]->customer_name,
	                            'email'=> $data['itemInfo'][0]->email,
	                            'mobile'=> $data['itemInfo'][0]->mobile,
	                            'pancard'=> $data['itemInfo'][0]->pancard,
	                            'loan_amount'=> $data['itemInfo'][0]->loan_amount,
	                            'Disbursed_date'=> date("j-F-Y ", strtotime($data['itemInfo'][0]->loan_disburse_date)),
	                            'ROI'=> $data['itemInfo'][0]->loan_intrest,
	                            'Repayment_date'=> date("j-F-Y ", strtotime($data['itemInfo'][0]->loan_repay_date)),
	                            'Real_Tenure'=> $rtenure,
	                            'Real_Interest'=> $realIntrest,
	                            'Penalty_Tenure'=> $ptenure,
	                            'Penal_Interest'=> $penaltyIntrest,
	                            'Paid_Amount'=>$data['payment_amount'][0]->payment_amount,
	                            'Totalamountdueasontoday'=> $repayAmount,
	                            
	                            );
	                            
	                         $da = array('success' => "true",'data' => $dataa);
                             echo     $result = json_encode($da);
        	        //$this->response($result, REST_Controller::HTTP_OK);

                }

            } else {

    	        $this->response(['Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

            }

	    }


	    public function recoveryInsert_post()
        {
            if($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                $this->form_validation->set_rules("company_id", "Company ID", "trim|required");
                $this->form_validation->set_rules("lead_id", "Lead ID", "trim|required");
                $this->form_validation->set_rules("loan_no", "Loan No", "trim|required");
                $this->form_validation->set_rules("refrence_no", "Refrence No", "trim|required");
                $this->form_validation->set_rules("payment_mode", "Payment Mode", "trim|required");
                $this->form_validation->set_rules("status", "Status", "trim|required");
                $this->form_validation->set_rules("recovery_status", "Recovery Status", "trim|required");
                $this->form_validation->set_rules("company_account_no", "Company Account No", "trim|required");
                $this->form_validation->set_rules("remarks", "Remark", "trim|required");
                $this->form_validation->set_rules("ip", "User IP", "trim|required");
                $this->form_validation->set_rules("recovery_by", "Recovered By", "trim|required");
                if($this->form_validation->run() == FALSE)
                {
                    $this->response(validation_errors(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    echo "if called : <pre>"; print_r(validation_errors()); exit;
                }
                else
                {  
                    $recoveryData =  array(
                        'company_id'        =>$this->input->post('company_id'),
                        'lead_id'           =>$this->input->post('lead_id'),
                        'customer_id'       =>$this->input->post('customer_id'),
                        'loan_no'           =>$this->input->post('loan_no'),  
                        'payment_mode'      =>$this->input->post('payment_mode'),
                        'lan'               =>$this->input->post('lan'),
                        'payment_amount'    =>$this->input->post('payment_amount'),
                        'refrence_no'       =>$this->input->post('refrence_no'),
                        'status'            =>$this->input->post('status'),
                        'company_account_no'=>$this->input->post('company_account_no'),
                        'extraamount'       =>$this->input->post('extraamount'),
                        'date_of_recived'   =>$this->input->post('date_of_recived'),
                        'sattelment'        =>$this->input->post('sattelment'),
                        'docs'              =>$this->input->post('docs'),
                        'recovery_status'   =>$this->input->post('recovery_status'),
                        'remarks'           =>$this->input->post('remarks'),
                        'noc'               =>$this->input->post('noc'),
                        'ip'                =>$this->input->post('ip'),
                        'recovery_by'       =>$this->input->post('recovery_by'),
                        'created_on'        =>$this->input->post('created_on'),
                        'PaymentVerify'     =>$this->input->post('PaymentVerify'),
                        'updated_by'        =>$this->input->post('updated_by'),
                        'updated_at'        =>$this->input->post('updated_at'),
                    );
                    echo "if called recoveryData : <pre>"; print_r($recoveryData);

                    $this->db->insert('recovery', $recoveryData);
                    $id = $this->db->insert_id();
                    if(!empty($id))
                    {
                    echo "if else called ID : <pre>"; print_r(validation_errors());
                        $this->response(200, REST_Controller::HTTP_OK);
                    }else{
                        $this->response(201, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    echo "if else called ID error : <pre>"; print_r(validation_errors());
                    } 
                }  
            }
            else
            {
                $this->response(500, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    echo "else called : <pre>"; print_r(validation_errors()); exit;
            }    
        }
        

        public function getState_post()

        {

           $data = $this->db->select('*')->from('tb_states')->get()->result();

           $this->response($data, REST_Controller::HTTP_OK); 

        }



        public function getCity_post()

        { 

            $state_id = $this->input->post('state_id');            

            $data = $this->db->select('*')->from('tb_city')->where('state_id', $state_id)->get()->result();

            $this->response($data, REST_Controller::HTTP_OK); 

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
	    

	   // public function index_put($id)

	   // {

	   //     $input = $this->put();

	   //     $this->db->update('leads', $input, array('id'=>$id));

	     

	   //     $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);

	   // }

	     

	   // public function index_delete($id)

	   // {

	   //     $this->db->delete('leads', array('id'=>$id));

	       

	   //     $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);

	   // }



	}



?>