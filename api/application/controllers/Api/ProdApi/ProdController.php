<?php

    // defined('BASEPATH') OR exit('No direct script access allowed');
    require APPPATH . 'libraries/REST_Controller.php';
    require APPPATH . 'libraries/Format.php';
    class ProdController extends REST_Controller
    {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('updated_on', date('Y-m-d H:i:s'));
        define('currentdate', date('Y-m-d H:i:s'));


    }

 public function userRegistration_post() {

       // echo "hello"; die;
   $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data,true));
        if ($input_data){ 
            $post = $this->security->xss_clean(json_decode($input_data,true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
            } else {

                $mobile = $post['mobile'];
                $otp = rand(1000, 9999);

                $data = [
                    "mobile" => $mobile,
                    "otp" => $otp
                ];

                $dataCustomer = array(
                    'mobile'        => $mobile,
                    'otp'           => $otp,
                    'company_id'    => 1,
                    'product_id'    => 1,
                    'lead_status_id'=>1,
                    'lead_entry_date'=>date('Y-m-d'),
                    'lead_data_source_id'    => 2,
                    'ip'          => $_SERVER['REMOTE_ADDR'],
                    'qde_consent' => 'Y',
                    'created_on'  => updated_on,
                  
                );
                
                $resultCustomer = $this->db->insert('leads', $dataCustomer);
                $lead_id = $this->db->insert_id();  

                 $customerData = array(
                    'customer_lead_id' => $lead_id,
                    'mobile'           => $mobile,
                    'created_date'     => updated_on ,
                    'updated_at'       => updated_on ,
                );

                $cus = $this->db->insert('lead_customer', $customerData);


                            //$table='leads_otp_trans';
                            $transdata = array(
                            'lot_lead_id'           => $lead_id,
                            'lot_mobile_no'         => $mobile,
                            'lot_mobile_otp'        => $otp,
                            'lot_mobile_otp_type'   => '1',
                            'lot_otp_verify_flag'   => '0',   
                            'lot_otp_trigger_time'  =>  updated_on, 
                           );  

            $res = $this->CurdMode->globel_inset('leads_otp_trans',$transdata);
                 
        
           $this->Tasks->sendOTPForUserRegistrationVerification($data);  
            //json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'customer_id' => $customer_id], REST_Controller::HTTP_OK));
            $array = ['Status' => 1, 'Message' =>'Success.','customer_id' => $lead_id];
            if($lead_id){
                json_encode($this->response($array, REST_Controller::HTTP_OK));
            }else{
                json_encode($this->response(['Status' => 0, 'Message' =>'Failed.'], REST_Controller::HTTP_BAD_REQUEST));
            }



 
               


            } //end validation else
        } else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        }
    }



 public function userVerificationProd_post() {

       $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data,true));
        if ($input_data) { 
            $post = $this->security->xss_clean(json_decode($input_data,true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        //print_r($post); die;
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {   
             $this->form_validation->set_data($post);
             $this->form_validation->set_rules("mobile", "Mobile", "required|trim|numeric|is_natural|min_length[10]|max_length[10]|regex_match[/^[0-9]+$/]");
             $this->form_validation->set_rules("lead_id", "lead Id", "required|trim");
             $this->form_validation->set_rules("otp", "OTP", "required|trim|numeric|is_natural|min_length[4]|max_length[4]|regex_match[/^[0-9]+$/]");

            // $this->form_validation->set_rules("first_name", "First Name", "required|trim");
            if($this->form_validation->run() == FALSE)
            {
                json_encode($this->response(['Status' => 0, 'Message' =>validation_errors()], REST_Controller::HTTP_BAD_REQUEST));
            }
            else
            {
                $mobile = $post['mobile'];
               
                $lead_id = $post['lead_id'];//lead_id
                $otp = $post['otp'];
                $query = $this->db->select('lead_id')->where('mobile', $mobile)->where('lead_id', $lead_id)->where('otp', $otp)->from('leads')->get();

                if($query->num_rows() > 0) 
                {

 
                        $conditions = ['cif_mobile' => $post['mobile']];
                        $fetch = '*';
                        $table='cif_customer';
                        $lead_id=$post['lead_id'];
                        $query = $this->Tasks->selectdata($conditions, $fetch, 'cif_customer');
                      //echo "====>".$query->num_rows(); die;
                        if($query->num_rows() > 0) 
                        {
                               $sql = $query->row();

                                if($sql->cif_loan_is_disbursed > 0 )
                                    {
                                        $user_type="REPEAT";
                                    }
                                    else
                                    {
                                        $user_type= "New";
                                    }
                             
                              $dataCustomer = array(
                                'first_name'            =>  strtoupper($sql->cif_first_name),
                                'middle_name'           => strtoupper($sql->cif_middle_name),
                                'sur_name'              => strtoupper($sql->cif_sur_name),
                                'gender'                => strtoupper($sql->cif_gender),
                                'dob'                   => strtoupper($sql->cif_dob),
                                'pancard'               => strtoupper($sql->cif_pancard),
                                'email'                 => strtoupper($sql->cif_personal_email),
                                //'mobile'                => strtoupper($post['mobile']),
                                'current_district'      => $sql->cif_residence_address_3,
                                'current_house'         => strtoupper($sql->cif_residence_address_1),
                                'current_locality'      => strtoupper($sql->cif_residence_address_2),
                                'current_residence_since' => $sql->cif_residence_since ,
                                'state_id'              => $sql->cif_residence_state_id,   
                                'city_id'                  => $sql->cif_residence_city_id,
                                'current_state' =>$getStateName,
                                'current_city' =>$getCityName,
                                'cr_residence_pincode'         => $sql->cif_residence_pincode ,
                                'current_landmark'             => $sql->cif_residence_landmark,
                                'current_residence_type'       => $sql->cif_residence_type,
                                'current_residing_withfamily'  => $sql->cif_residence_residing_with_family ,
                                'alternate_mobile' => $sql->cif_alternate_mobile,
                                'updated_at'            => currentdate
                                
                            );

                              //echo "<pre>"; print_r($dataCustomer); die;
                             $res1 = $this->CurdMode->globel_update('lead_customer',$dataCustomer,$lead_id,'customer_lead_id');
                             if($res1)
                             {
                               // lead table      
                                      $table2='leads';
                                      $column1='lead_id';
                                      $update_id=$post['lead_id'];

                                      $updateLeadCUstomerId = array(
                                                'email'        =>  strtoupper($sql->cif_personal_email),
                                                'customer_id'  =>   $sql->cif_number,
                                                'pancard'  =>   $sql->cif_pancard,
                                                'state_id'  =>   $sql->cif_residence_state_id,
                                                'city_id'  =>   $sql->cif_residence_city_id,
                                                'updated_on'  =>   updated_on,
                                                'user_type'      =>$user_type
                                             );

                                    //  echo "<pre>";print_r($updateLeadCUstomerId);
                                 
                                    $this->CurdMode->globel_update($table2,$updateLeadCUstomerId,$update_id,$column1);

                                   //table customer emlpoyement

                                     $customer_employment = array(
                                        'lead_id' => $post['lead_id'],
                                        'customer_id' => $sql->cif_number,
                                        'employer_name'        => strtoupper($sql->cif_company_name),
                                        'emp_pincode'           => $sql->cif_office_pincode,
                                        'emp_state'                => $getStateName,   
                                        'emp_city'                 => $getCityName,
                                        'emp_landmark'        => strtoupper($sql->cif_office_address_landmark),
                                        'emp_shopNo' => strtoupper($sql->cif_office_address_1),
                                        'emp_locality'    => $sql->cif_office_address_2 ,
                                        'emp_lankmark'        => $sql->cif_office_address_landmark,

                                        'emp_residence_since'        =>$sql->cif_office_working_since ,

                                        'emp_designation' => $sql->cif_office_designation ,
                                        'emp_department' => $sql->cif_office_department,
                                        'emp_employer_type' =>$sql->cif_company_type_id,
                                        'emp_website' => $sql->cif_company_website,
                                        'emp_email' => $sql->cif_office_email ,
                                        'created_on' => updated_on,
                                        'updated_on'=> updated_on
                                    );

                                   //  echo "<pre>";print_r($customer_employment); die;

                                 $this->CurdMode->globel_inset('customer_employment',$customer_employment,$update_id,$column1);
                                  json_encode($this->response(['Status' => 1, 'Message' => 'OTP Verified.', 'customer_id' => $lead_id], REST_Controller::HTTP_OK));
                            //json_encode($this->response(['Status' => 1, 'flag'=> $flag ,'Message' =>'OTP Verified.',  'lead_id' => $lead_id ], REST_Controller::HTTP_OK));
                             }

                                $table2='leads_otp_trans';
                                $column='lot_lead_id';
                                $update_id=$post['lead_id'];

                                $dataleads_otp_trans = array(
                                        'lot_otp_verify_flag' => '1',
                                        'lot_otp_verify_time'    => updated_on,
                                     );
                                $dataleads = array(
                                        'lead_is_mobile_verified' => '1',
                                     );
                           
                                $res = $this->CurdMode->globel_update($table2,$dataleads_otp_trans,$update_id,$column);
                                $res1 = $this->CurdMode->globel_update('leads',$dataleads,$update_id,'lead_id');
                                     //lead_is_mobile_verified

                  json_encode($this->response(['Status' => 1, 'Message' => 'OTP Verified.', 'customer_id' => $lead_id], REST_Controller::HTTP_OK));
                                // json_encode($this->response(['Status' => 1, 'flag'=> 1 ,'Message' =>'OTP Verified.',  'lead_id' => $lead_id ], REST_Controller::HTTP_OK));
                         }
                          else 
                          {
                                   // echo "not found"; die;
                            $table2='leads_otp_trans';
                            $column='lot_lead_id';
                            $update_id=$post['lead_id'];

                                      $dataleads_otp_trans = array(
                                                'lot_otp_verify_flag' => '1',
                                             );
                                      $dataleads = array(
                                                'lead_is_mobile_verified' => '1',
                                             );
                                   
                                $res = $this->CurdMode->globel_update($table2,$dataleads_otp_trans,$update_id,$column);
                                $res1 = $this->CurdMode->globel_update('leads',$dataleads,$update_id,'lead_id');
                                  
                           json_encode($this->response(['Status' => 1, 'Message' => 'OTP Verified.', 'customer_id' => $lead_id], REST_Controller::HTTP_OK));

                             //json_encode($this->response(['Status' => 1, 'flag'=> 0 ,'Message' =>'OTP Verified.',  'lead_id' => $lead_id ], REST_Controller::HTTP_OK));


                         }
                   }
                else
                {
                   json_encode($this->response(['Status' => 0, 'Message' => 'Invalid OTP. Try Again'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                }
            }
        }else{

           json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        }
    }


   //***********************************function for saving the big form********************************* // 


    public function vinSaveTasks_post() {

         $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data,true));
        if ($input_data) { 
            $post = $this->security->xss_clean(json_decode($input_data,true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') 
        {
        $this->form_validation->set_data($post);
        $this->form_validation->set_rules("company_id", "Company ID", "required|trim|numeric|is_natural");
        $this->form_validation->set_rules("loan_amount", "Loan Amount", "required|trim|numeric|is_natural");
        $this->form_validation->set_rules("monthly_income", "Monthly Income", "required|trim|numeric|is_natural");
        $this->form_validation->set_rules("obligations", "Obligations", "trim|numeric|is_natural");
        $this->form_validation->set_rules("first_name", "First Name", "required|trim|min_length[1]|max_length[40]");
        $this->form_validation->set_rules("middle_name", "Middle Name", "trim");
        $this->form_validation->set_rules("sur_name", "Sur Name", "trim");
        $this->form_validation->set_rules("gender", "Gender", "required|trim|regex_match[/^[a-zA-Z]+$/]");
        $this->form_validation->set_rules("dob", "Date Of Birth", "trim");
        $this->form_validation->set_rules("pan", "Pan card", "required|trim|min_length[10]|max_length[10]|regex_match[/[a-zA-Z]{3}[p-pP-P]{1}[a-zA-Z]{1}\d{4}[a-zA-Z]{1}/]");
         $this->form_validation->set_rules("mobile", "Mobile No", "required|trim|numeric|min_length[10]|max_length[10]");
        $this->form_validation->set_rules("alternate_mobile", "Alternate Mobile No", "trim|numeric|min_length[10]|max_length[10]");
        $this->form_validation->set_rules("email_personal", "Personal Email", "required|trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
        $this->form_validation->set_rules("email_official", "Official Email", "trim|regex_match[/([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})/]");
        $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric");
        $this->form_validation->set_rules("city", "city", "required|trim");
        $this->form_validation->set_rules("pin", "Pincode", "required|trim|numeric|min_length[6]|max_length[6]");
        $this->form_validation->set_rules("otp", "OTP", "trim|numeric|min_length[4]|max_length[4]");
        $this->form_validation->set_rules("source", "Lead Source", "required|trim");
        $this->form_validation->set_rules("utm_source", "UTM Source", "trim");
        $this->form_validation->set_rules("utm_campaign", "UTM Compain", "trim");
        $this->form_validation->set_rules("coordinates", "coordinates", "trim");
        $this->form_validation->set_rules("ip", "IP", "trim");


       

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
            } 
            else 
            {

                  if (isset($post['coupon_code'])) {
                    $coupon = $post['coupon_code'];
                } else {
                    $coupon = "";
                }


               // echo "<pre>";print_r($post);
                $conditions = ['cif_pancard' => $post['pan']];
                $table='cif_customer';
                 $lead_id=$post['lead_id']; 
                $query = $this->Tasks->selectdata($conditions, '*', 'cif_customer');

                $day = date('d', strtotime($post['dob']));
                $month = date('m', strtotime($post['dob']));
                $year = date('Y', strtotime($post['dob']));
                $dateOfBirth = $year .'-'. $month .'-'. $day;
                $dob = ($dateOfBirth) ? $dateOfBirth : "";
                
              //getnumrowsData
        $customer_idstatus =  getcustId('leads','lead_id',$lead_id,'customer_id,lead_status_id');

        // $lead_status_id =  getcustId('leads','lead_id',$lead_id,'lead_status_id');
               $table='leads';
               $selectdata="customer_id,lead_status_id";
               $where=" where lead_id='$lead_id' and (lead_active='1' and lead_deleted='0' )";
               $num_rowsleads = getnumrowsData($selectdata, $table,$where);


          //echo "<pre>";print_r($num_rowsleads);
          //$num_rowsleads[0]['customer_id'];
        
                //if($query->num_rows() > 0 && empty($customer_idstatus) )  && empty($num_rowsleads[0]['customer_id'])
                if($query->num_rows() > 0  && empty($num_rowsleads[0]['customer_id']))
                {
                   
                      if($num_rowsleads[0]['lead_status_id'] <= 1  )
                       {
                       }
                       else
                       {
                           $array = array('status'=>0 , 'message'=>'Your Lead is under process.');
                            echo json_encode($array);
                            exit;                        
                       }


                    $sql = $query->row();

                //  echo "blank h"; die;
                  
                 
                 $getStateName= getcustId('master_state','m_state_id',$sql->cif_residence_state_id,'m_state_name');
                 $getCityName= getcustId('master_city','m_city_id',$sql->cif_residence_city_id,'m_city_name');

                 if($sql->cif_loan_is_disbursed > 0 )
                    {
                        $user_type="REPEAT";
                    }
                    else
                    {
                        $user_type= "New";
                    }
              
                      $dataCustomer = array(
                        //'first_name'            =>  strtoupper($sql->cif_first_name),
                        'middle_name'           => strtoupper($sql->cif_middle_name),
                        'sur_name'              => strtoupper($sql->cif_sur_name),
                        'gender'                => strtoupper($sql->cif_gender),
                        'dob'                   => strtoupper($sql->cif_dob),
                        'pancard'               => strtoupper($sql->cif_pancard),
                        'email'                 => strtoupper($post['email_personal']),
                        //'mobile'                => strtoupper($post['mobile']),
                        'current_district'      => $sql->cif_residence_address_3,
                        'current_house'         => strtoupper($sql->cif_residence_address_1),
                        'current_locality'      => strtoupper($sql->cif_residence_address_2),
                        'current_residence_since' => $sql->cif_residence_since ,
                        'state_id'              => $sql->cif_residence_state_id,   
                        'city_id'                  => $sql->cif_residence_city_id,
                      'current_state' =>$getStateName,
                    'current_city' =>$getCityName,
                    'cr_residence_pincode'         => $sql->cif_residence_pincode ,
                    'current_landmark'             => $sql->cif_residence_landmark,
                    'current_residence_type'       => $sql->cif_residence_type,
                    'current_residing_withfamily'  => $sql->cif_residence_residing_with_family ,
                    'alternate_mobile' => $sql->cif_alternate_mobile,
                        'updated_at'            => currentdate
                        
                    );

                      //echo "<pre>"; print_r($dataCustomer); die;
            $res1 = $this->CurdMode->globel_update('lead_customer',$dataCustomer,$lead_id,'customer_lead_id');
                 if($res1)
                 {
            // lead table      
                              $table2='leads';
                              $column1='lead_id';
                              $update_id=$post['lead_id'];

                              $updateLeadCUstomerId = array(
                                        'email'        =>  strtoupper($post['email_personal']),
                                        'customer_id'  =>   $sql->cif_number,
                                        'pancard'  =>   $sql->cif_pancard,
                                        'state_id'  =>   $sql->cif_residence_state_id,
                                        'city_id'  =>   $sql->cif_residence_city_id,
                                         'user_type'   =>$user_type,
                                        'updated_on'  =>   updated_on
                                     );

                            //  echo "<pre>";print_r($updateLeadCUstomerId);
                         
                            $this->CurdMode->globel_update($table2,$updateLeadCUstomerId,$update_id,$column1);

//table customer emlpoyement
  $table='customer_employment';
                     $lead_id =$post['lead_id'];
                     $column='lead_id';
                     
                  $status = getLeadIdstatus($table,$lead_id,$column); 
                      if($status=='0')
                       {

                 $customer_employment = array(
                    'lead_id'              => $lead_id,
                    'employer_name'        => strtoupper($sql->cif_company_name),
                    'emp_pincode'           => $sql->cif_office_pincode,
                    'emp_state'                => $sql->cif_residence_state_id,   
                    'emp_city'                 => $sql->cif_residence_city_id,
                    'office_address'        => strtoupper($sql->cif_office_address_landmark),
                    'emp_shopNo' => strtoupper($sql->cif_office_address_1),
                    'emp_locality'    => $sql->cif_office_address_2,
                    'emp_lankmark'        => $sql->cif_office_address_landmark,
                    'emp_landmark'        => $sql->cif_office_address_landmark,
                    'emp_residence_since'        =>$sql->cif_office_working_since ,
                    'emp_designation' => $sql->cif_office_designation,
                    'emp_department' => $sql->cif_office_department,
                    'emp_employer_type' => $sql->cif_company_type_id,
                    'emp_website' => $sql->cif_company_website ,
                    'emp_email' => $sql->cif_office_email ,
                    'created_on' => updated_on,
                    'updated_on'=> updated_on
                );

                  $res = $this->CurdMode->globel_inset('customer_employment',$customer_employment);

     //$this->CurdMode->globel_inset('customer_employment',$customer_employment,$update_id,$column1);
 }
 else
 {
     $customer_employment = array(
                    'employer_name'        => strtoupper($sql->cif_company_name),
                    'emp_pincode'           => $sql->cif_office_pincode,
                    'emp_state'                => $sql->cif_residence_state_id,   
                    'emp_city'                 => $sql->cif_residence_city_id,
                    'office_address'        => strtoupper($sql->cif_office_address_landmark),
                    'emp_shopNo' => strtoupper($sql->cif_office_address_1),
                    'emp_locality'    => $sql->cif_office_address_2,
                    'emp_lankmark'        => $sql->cif_office_address_landmark,
                    'emp_landmark'        => $sql->cif_office_address_landmark,
                    'emp_residence_since'        =>$sql->cif_office_working_since ,
                    'emp_designation' => $sql->cif_office_designation,
                    'emp_department' => $sql->cif_office_department,
                    'emp_employer_type' => $sql->cif_company_type_id,
                    'emp_website' => $sql->cif_company_website ,
                    'emp_email' => $sql->cif_office_email ,
                    'updated_on'=> updated_on
                );


     $res = $this->CurdMode->globel_update('customer_employment',$customer_employment,$lead_id,'lead_id');
 }





}

                    $array = array('lead_id'=>$lead_id , 'message'=>'Customer Save Successfully.', 'Status'=>1);
                    echo json_encode($array);
                    exit;                           
                }else{

                   // echo "2st"; die;
                   if($sql->cif_loan_is_disbursed > 0 )
                    {
                        $user_type="REPEAT";
                    }
                    else
                    {
                        $user_type= "New";
                    }

                    //echo "---".$user_type; die;
                   
             $getSource= getcustId('master_data_source','data_source_name',$post['source'],'data_source_id');

                   $referenceCode= $this->generateReferenceNumber($lead_id);
                   $mastrdata=$this->Tasks->getAllDataFromPincode($post['pin']); 


                   if(empty($mastrdata))
                   {
                     $city_id=$post['city'];
                     $city_name='';
                     $state_id=$post['state_id'];
                     $state_name='';
                   }
                   else
                   {
                     $city_id=$mastrdata[0]['city_id'];
                     $city_name=$mastrdata[0]['city_name'];
                     $state_id=$mastrdata[0]['state_id'];
                     $state_name=$mastrdata[0]['state_name'];
                   }
                 //echo $state_name; die;

               $data = array(
                'customer_id' => 1,
                'company_id' => 1,
                'loan_amount' => $post['loan_amount'],
                'obligations' => $post['obligations'],
                'first_name' => strtoupper($post['first_name']. ' ' .$post['middle_name'].' '.$post['sur_name']),
                'city_id' => $city_id,
                'pancard' => strtoupper($post['pan']),
                'mobile' => $post['mobile'],
                'email' => strtoupper($post['email_personal']),
                'alternate_email' => strtoupper($post['email_official']),
                'state_id' => $post['state_id'],
                'city' => strtoupper($city_name),
                'pincode' => $post['pin'],
                'promocode' => $coupon,
                'source' => $getSource,
                'utm_source' => strtoupper($post['utm_source']),
                'utm_campaign' => strtoupper(($post['utm_campaign']) ? $post['utm_campaign'] : NA),
                'updated_on' => created_on,
                'coordinates' => ($post['coordinates']) ? $post['coordinates'] : "",
                'otp' => $otp,
                'ip' => ($post['ip']) ? $post['ip'] : "",
                 'user_type'   =>$user_type,
                'lead_reference_no' => $referenceCode,
                );

              //refrence code
            //  echo "<pre>";print_r($data); die;

                     $res = $this->CurdMode->globel_update('leads',$data,$lead_id,'lead_id');
                    if($res == true){
                    
                        $dataCustomer = array(
                            'first_name' => strtoupper($post['first_name']),
                            'middle_name'=> strtoupper($post['middle_name']),
                            'sur_name'=> strtoupper($post['sur_name']),
                            'email' => strtoupper($post['email_personal']),
                            'current_state' => $state_name,
                            'current_city' => $city_name,
                            'state_id' => $state_id,
                            'city_id' => $city_id,
                            'alternate_mobile' => $post['alternate_mobile'],
                            'gender' => strtoupper($post['gender']),
                            'pancard' => strtoupper($post['pan']),
                            'updated_at' => updated_at
                        );
  //echo "<pre>";print_r($dataCustomer); die;
                        $this->CurdMode->globel_update('lead_customer',$dataCustomer,$lead_id,'customer_lead_id');


                  $status = getLeadIdstatus('customer_employment',$lead_id,'lead_id'); 
                      if($status=='0')
                       {
                           $customer_emp = array(
                            'lead_id'           => $lead_id,
                            'company_id'        => ($post['company_id'] ? $post['company_id'] : ""),
                            'product_id'        => ($post['product_id'] ? $post['product_id'] : ""),
                            'monthly_income'    => ($post['monthly_income'] ? $post['monthly_income'] : ""),
                            'created_on'        => currentdate
                            );
           
                            $this->db->insert('customer_employment', $customer_emp);
                           


                       }
                       else
                       {
                           // $lead_id =$post['lead_id'];
                            $customer_emp = array(
                            'company_id'        => ($post['company_id'] ? $post['company_id'] : ""),
                            'product_id'        => ($post['product_id'] ? $post['product_id'] : ""),
                            'monthly_income'    => ($post['monthly_income'] ? $post['monthly_income'] : ""),
                            'updated_on'        => currentdate
                            );
           
                          //  $this->db->insert('customer_employment', $customer_emp);
                        $res = $this->CurdMode->globel_update('customer_employment',$customer_emp,$lead_id,'lead_id');
                           

                       }

                     //   json_encode($this->response(, REST_Controller::HTTP_OK));
                            
                       $result_data = array(['Status' => 1, 'Message' => 'Record Save Successfully.', 'ApplicationNo' => $lead_id]);
                        echo json_encode($result_data);
                        exit;
                    }else{
                        json_encode($this->response(['Status' => 0, 'Message' => 'Server not responding your request.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                        exit;
                    }
                }

               
            } // end validation else
    } // end post method

         else {

            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    public function generateReferenceNumber($lead_id)
    {

          $conditions = ['customer_lead_id' => $lead_id];
                    $fetch      = 'first_name, sur_name, mobile';
                    $query      = $this->Tasks->selectdata($conditions, $fetch, 'lead_customer');
                    $sql = $query->row();
                    $first_name = $sql->first_name;
                    $last_name = $sql->sur_name;
                    $mobile = $sql->mobile;
                    $gender = $sql->gender;
                   return $referenceCode  = $this->Tasks->generateReferenceCode($lead_id, $first_name, $last_name, $mobile);
                     

    }


     public function getStatepro_post() {

      
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data,true));
        if ($input_data) { 
            $post = $this->security->xss_clean(json_decode($input_data,true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {


            $query = $this->db->query("SELECT state_id as id,UPPER(state) as state FROM tbl_state ");
                if($query->num_rows() > 0) {
                    $result1 = $query->result_array();
                    
                      json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $result1], REST_Controller::HTTP_OK));
                }
                else
                {
                      json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR)); 
                }

          /*  $this->form_validation->set_data($post);

            $result = $this->db->select('ST.old_state_id as state_id, ST.state,')->where('ST.status', 1)->from("tbl_state ST")->get();

            if ($result->num_rows() > 0) {

                $data = $result->result();

                json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
            } else {

                json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
            }
*/        } else {

            json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    public function getCitypro_post() {

        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        if ($input_data) {

            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {

            $post = $this->security->xss_clean($_POST);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_data($post);

            $this->form_validation->set_rules("state_id", "State ID", "required|trim|numeric|is_natural");

            if ($this->form_validation->run() == FALSE) {

                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
            } else {


                 $state_id = $post['state_id'];
              
                
                 $query = $this->db->query("SELECT m_city_state_id as state_id,UPPER(m_city_name) as city,m_city_code as city_code ,m_city_category as city_category  FROM `master_city` where m_city_state_id='$state_id' ");

                if($query->num_rows() > 0) {
                    $data = $query->result_array();
                    json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                }
                else
                {
                   json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                }






/*
                $result = $this->db->select('CT.old_state_id as state_id, CT.city, CT.city_code, CT.city_category')
                        ->where('CT.old_state_id', $post['state_id'])
                        ->where('CT.status', 1)
                        ->from("tbl_city CT")
                        ->get();

                if ($result->num_rows() > 0) {

                    $data = $result->result();

                    json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $data], REST_Controller::HTTP_OK));
                } else {

                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
                }*/
            }
        } else {

            json_encode($this->response(['Status' => 1, 'Message' => 'Request Method GET Failed.'], REST_Controller::HTTP_INTERNAL_SERVER_ERROR));
        }
    }


}

?>