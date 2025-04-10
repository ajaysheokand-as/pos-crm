<?php
// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
class CollectionApi extends REST_Controller {
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

	public function intiatePaymentReqest_post(){
     
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
            $this->form_validation->set_rules("order_id", "order_id", "required|trim");
            $this->form_validation->set_rules("amount", "amount", "required|trim|regex_match[/^[0-9]+$/]");
            $this->form_validation->set_rules("pan_number", "Pancard", "required|trim|min_length[10]|max_length[10]");
            $this->form_validation->set_rules("loan_number", "Loan No", "required|trim");
          
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 2, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            }
			else {
			    
			    $order_id = strval($post['order_id']);
                $rrn = strval($post['rrn']);
                $amount = intval($post['amount']);
                $loan_number = strval($post['loan_number']);
                $pan_number = intval($post['pan_number']);
                
                $queryLead = $this->db->select('*')->where(['pancard' => $pan_number])->where(['loan_no' => $loan_number])->from('leads')->get();
				$leadData = $queryLead->row();	

				$leadPancard = $leadData->pancard;
			
				
				if(isset($leadData->lead_status_id) && (in_array($leadData->lead_status_id,[14,19]))){
				  $insertDataPaymentRequest = array(
							'order_id' => $order_id,
							'rrn' => $rrn,
							'amount' => $amount,
							'loan_number' => $loan_number,
							'pan_number' => $leadPancard,
						);
					$this->db->insert('icici_collection_log', $insertDataPaymentRequest);
				   return json_encode($this->response(['Status' => 1, 'Message' => 'Request Accepted.','Data'=>$insertDataPaymentRequest], REST_Controller::HTTP_OK));
				}else{
				     return json_encode($this->response(['Status' => 0, 'Message' => 'No record Found.'], REST_Controller::HTTP_OK));
				}
             
			}
            
        }
   }
}

?>