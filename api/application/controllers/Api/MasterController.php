<?php
// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class MasterController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
    }

    //*********** Api for Get States *************//
    public function masterAPI_post() {

        $input_data = file_get_contents("php://input");
        //$post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
      
            if ($post['apiname'] == 'getstate') {

                //$num_rows = $this->MasterModel->getStateData();
                $num_rows = [ [ "id" => "1", "name" => "Andaman and Nicobar Islands" ], [ "id" => "2", "name" => "Andhra Pradesh" ], [ "id" => "3", "name" => "Arunachal Pradesh" ], [ "id" => "4", "name" => "Assam" ], [ "id" => "5", "name" => "Bihar" ], [ "id" => "6", "name" => "Chandigarh" ], [ "id" => "7", "name" => "Chhattisgarh" ], [ "id" => "8", "name" => "Dadra and Nagar haveli" ], [ "id" => "9", "name" => "Daman and Diu" ], [ "id" => "10", "name" => "Delhi" ], [ "id" => "11", "name" => "Goa " ], [ "id" => "12", "name" => "Gujarat" ], [ "id" => "13", "name" => "Haryana" ], [ "id" => "14", "name" => "Himachal Pradesh" ], [ "id" => "15", "name" => "Jammu and Kashmir" ], [ "id" => "16", "name" => "Jharkhand" ], [ "id" => "17", "name" => "Karnataka" ], [ "id" => "18", "name" => "Kerala" ], [ "id" => "19", "name" => "Lakshadweep" ], [ "id" => "20", "name" => "Madhya Pradesh" ], [ "id" => "21", "name" => "Maharashtra" ], [ "id" => "22", "name" => "Manipur" ], [ "id" => "23", "name" => "Meghalaya" ], [ "id" => "25", "name" => "Nagaland" ], [ "id" => "26", "name" => "Orissa" ], [ "id" => "27", "name" => "Pondicherry" ], [ "id" => "28", "name" => "Punjab" ], [ "id" => "29", "name" => "Rajasthan" ], [ "id" => "30", "name" => "Sikkim" ], [ "id" => "31", "name" => "Tamil Nadu" ], [ "id" => "37", "name" => "Telangana" ], [ "id" => "32", "name" => "Tripura" ], [ "id" => "34", "name" => "Uttar Pradesh" ], [ "id" => "33", "name" => "Uttarakhand" ], [ "id" => "35", "name" => "West Bengal" ] ];
                $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
            } else if ($post['apiname'] == 'getcity') {

                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {

                    $state_id = $post['id'];
                    $num_rows = $this->MasterModel->getCityData($state_id);
                    
                    $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
                    return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getallcity') {
                
                $num_rows = $this->MasterModel->getAllCityData($post['sourceable']);
                
               
                
                $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);

                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
                
            }else if ($post['apiname'] == 'getdocs') {
                
                $num_rows = $this->MasterModel->getAllDocument();   
               
                $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
                
            }else if ($post['apiname'] == 'getDocumentList') {
                $lead_id = $this->encrypt->decode($post['lead_id']);
                $num_rows = $this->MasterModel->getAllDocumentFile($lead_id);                
                $result_data = array('Status' => 1, 'Message' => 'Data found','lead_id'=>$post['lead_id'],'data' => $num_rows);
                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
                
            } else if ($post['apiname'] == 'getpincode') {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {
                    $city_id = $post['id'];
                    $num_rows = $this->MasterModel->getPincode($city_id);
                    $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
                    return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getpurposeofloan') {
                $num_rows = $this->MasterModel->getPurposeOfLoan();
                $result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
                return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
            }
    }
    
    public function masterNewAPI_post() {
        $input_data = file_get_contents("php://input");        
        //$post = $this->security->xss_clean(json_decode($input_data, true));
        if($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        } 
        
        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
        //echo '<pre>';print_r($post);die;
        // if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) 
        // {   
            foreach($post['records'] as $key=>$val)
            {
              $apiname = $val['apiname'];              
              if($apiname == 'getstate'){
                $getAllstate = $this->MasterModel->getStateData(); 
                if(!empty($getAllstate) && count($getAllstate)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                } 
                $result_data['getAllstate'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllstate);
              }              
              if($apiname == 'getcitybystate'){ 
                $state_id = $val['state_id'];
                $getAllCityByState = $this->MasterModel->getCityData($state_id);
                if(!empty($getAllCityByState) && count($getAllCityByState)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status['status'] = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllCityByState'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllCityByState);
              } 

              if($apiname == 'getallcity'){ 
                $sourceable = $val['sourceable'];
                $getAllCity = $this->MasterModel->getAllCityData($sourceable);
                if(!empty($getAllCity) && count($getAllCity)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                } 
                $result_data['getAllCity'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllCity);
              } 

              if($apiname == 'getpincodebycity'){
                $city_id = $val['city_id'];
                $getAllPincodeByCity = $this->MasterModel->getPincode($city_id); 
                if(!empty($getAllPincodeByCity) && count($getAllPincodeByCity)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status['status'] = 0;
                  $message['message'] = 'Data not found';
                } 
                $result_data['getAllPincodeByCity'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllPincodeByCity);                
              } 

              if($apiname == 'getpurposeofloan'){
                $getAllPurposeOfLoan = $this->MasterModel->getPurposeOfLoan();
                if(!empty($getAllPurposeOfLoan) && count($getAllPurposeOfLoan)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllPurposeOfLoan'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllPurposeOfLoan);
              }

              if($apiname == 'getindustry'){
                $getAllIndustry = $this->MasterModel->getIndustryData();
                if(!empty($getAllIndustry) && count($getAllIndustry)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllIndustry'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllIndustry);
              }

              if($apiname == 'getmaritalstatus'){
                $getAllMaritalStatus = $this->MasterModel->getMaritalStatusData();
                if(!empty($getAllMaritalStatus) && count($getAllMaritalStatus)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllMaritalStatus'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllMaritalStatus);
              }

              if($apiname == 'getoccupation'){
                $getAllOccupation = $this->MasterModel->getOccupationData();
                if(!empty($getAllOccupation) && count($getAllOccupation)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllOccupation'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllOccupation);
              }

              if($apiname == 'getdesignation'){
                $getAllDesignation = $this->MasterModel->getDesignationData();
                if(!empty($getAllDesignation) && count($getAllDesignation)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllDesignation'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllDesignation);
              }

              if($apiname == 'getsalarymode'){
                $getAllSalaryMode = $this->MasterModel->getSalaryModeData();
                if(!empty($getAllSalaryMode) && count($getAllSalaryMode)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllSalaryMode'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllSalaryMode);
              }
              
              if($apiname == 'getqualification'){
                $getAllQualification = $this->MasterModel->getQualificationData();
                if(!empty($getAllQualification) && count($getAllQualification)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllQualification'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllQualification);
              }

              if($apiname == 'getcompanytype'){
                $getAllCompanyType = $this->MasterModel->getCompanyTypeData();
                if(!empty($getAllCompanyType) && count($getAllCompanyType)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllCompanyType'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllCompanyType);
              }

              if($apiname == 'getresidencetype'){
                $getAllSidenceType = $this->MasterModel->getSidenceTypeData();
                if(!empty($getAllSidenceType) && count($getAllSidenceType)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllSidenceType'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllSidenceType);
              }

              if($apiname == 'getbanktype'){
                $getAllBankType = $this->MasterModel->getBankTypeData();
                if(!empty($getAllBankType) && count($getAllBankType)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllBankType'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllBankType);
              } 
              if($apiname == 'getreligion'){
                $getAllReligion = $this->MasterModel->getReligionData();
                if(!empty($getAllReligion) && count($getAllReligion)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllReligion'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllReligion);
              }
              if($apiname == 'getdepartment'){
                $getAllDepartment = $this->MasterModel->getDepartmentData();
                if(!empty($getAllDepartment) && count($getAllDepartment)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllDepartment'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllDepartment);
              }
              if($apiname == 'getrelation'){
                $getAllRelation = $this->MasterModel->getRelationData();
                if(!empty($getAllRelation) && count($getAllRelation)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                }
                $result_data['getAllRelation'] = array('Status'=>$status,'Message'=>$message,'data'=>$getAllRelation);
              }
              if($apiname == 'setCurrentAddressProof'){
                $getCurrentAddressProof = $this->MasterModel->getCurrentAddressProofData(); 
                if(!empty($getCurrentAddressProof) && count($getCurrentAddressProof)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                } 
                $result_data['getCurrentAddressProof'] = array('Status'=>$status,'Message'=>$message,'data'=>$getCurrentAddressProof);
              } 
              if($apiname == 'setOthersKYC'){
                $getOthersKYC = $this->MasterModel->getOthersKYCData(); 
                if(!empty($getOthersKYC) && count($getOthersKYC)>0){
                  $status = 1; 
                  $message = 'Data found';
                }else{
                  $status = 0;
                  $message = 'Data not found';
                } 
                $result_data['getOthersKYC'] = array('Status'=>$status,'Message'=>$message,'data'=>$getOthersKYC);
              }
          }
          return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
        // } 
        // else 
        // {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        // }
    }
    
    public function masterAPIDoc_post() {
        $input_data = file_get_contents("php://input");
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));
        
        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
		if($post['apiname'] == 'getDocType') {
			$docs_type = $post['docs_type'];
			$num_rows = $this->MasterModel->getDocumentByIDData($docs_type);
			$result_data = array('Status' => 1, 'Message' => 'Data found', 'data' => $num_rows);
			return json_encode($this->response($result_data, REST_Controller::HTTP_OK));
		}
        // } 
        // else 
        // {
        //     return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        // }
    }

}

?>
