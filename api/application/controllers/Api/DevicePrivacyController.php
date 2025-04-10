<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class DevicePrivacyController extends REST_Controller {

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
    

    public function getLoan_post()
    {
		$input_data = file_get_contents("php://input");
		//return $this->response($input_data, REST_Controller::HTTP_OK);
        if ($input_data) {
			$jsonInput = json_decode($input_data, true);
            $post = $this->security->xss_clean($jsonInput);
        } else {
            $post = $this->security->xss_clean($_POST);
        }
		
		$query = $this->db->select('lead_id, first_name, mobile, email, loan_amount,loan_no')->where('pancard', $post['panNumber'])->where('status', 'DISBURSED')->where('stage', 'S14')->from('leads')->order_by('lead_id','DESC')->limit(1)->get();
		$result = $query->row();
		print_r($result); die;
// 		if(isset($result) && !empty($result->lead_id)){
// 			$res = array('Status'=>1,'Message'=>'Record found successfully.');
// 			$data = $this->Lead->getLoanRepaymentDetails($result->lead_id);
// 			$res['data'] = $data; 
// 			return json_encode($this->response($res, REST_Controller::HTTP_OK));
// 		}
// 		else {
// 			return json_encode($this->response(['Status' => 2, 'Message' => 'No PAN found.'], REST_Controller::HTTP_OK));
// 		}
		
		
    }
    
    
}

?>
