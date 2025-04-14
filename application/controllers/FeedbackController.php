<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FeedbackController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo CAM";

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Feedback_Model', 'Feedback');

        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");

        $login = new IsLogin();
        $login->index();
    }

    public function error_page() {
        $this->load->view('errors/index');
    }

    public function index($stage) {
//            error_reporting(E_ALL);
//            ini_set("display_errors", 1);
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
        $search_input_array = array();
        $where_in = array();

        if (!empty($_REQUEST['search']) && $_REQUEST['search'] == 1) {
            $search_input_array = $_REQUEST;
        }

        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2));
        $conditions = array();
        $data['totalcount'] = $this->Feedback->countLeads($conditions, $search_input_array, $where_in);

        $config = array();
        $config["base_url"] = $url;

        $page = !empty($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 0;

        if (!empty($_REQUEST['search']) && $_REQUEST['search'] == 1) {
            unset($_REQUEST['csrf_token']);
            unset($_REQUEST['per_page']);
            $config["base_url"] .= "?";
            $request_search_url = http_build_query($_REQUEST);
            $config["base_url"] .= $request_search_url;
        }

        $config['page_query_string'] = TRUE;
        $config["total_rows"] = $data['totalcount'];
        $config["per_page"] = 20;
        $config["uri_segment"] = 2;
        $config['full_tag_open'] = '<div class="pagging text-right"><nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav></div>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '<span aria-hidden="true"></span></span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';

        $this->pagination->initialize($config);

        $data['pageURL'] = $url;

        $data['leadDetails'] = $this->Feedback->index($conditions, $config["per_page"], $page, $search_input_array, $where_in);

        $data["links"] = $this->pagination->create_links();

        $data["master_data_source"] = $this->Tasks->getDataSourceList();

        if (!empty($search_input_array['ssid'])) {
            $data["master_city"] = $this->Tasks->getCityList($search_input_array['ssid']);
        }
        $data["master_state"] = $this->Tasks->getStateList();
        $data["master_branch"] = $this->Tasks->getBranchList();
        $data["search_input_array"] = $search_input_array;

        $this->load->view('Feedback/index', $data);
    }

    public function getLeadDetails($leadId) {

        $lead_id = intval($this->encrypt->decode($leadId));

        $data['isAnotherLeadInprocess'] = $this->Feedback->isAnotherLeadInprocess($lead_id);

        $conditions['LD.lead_id'] = $lead_id;
        $leadData = $this->Feedback->getLeadDetails($conditions);
        $data['leadDetails'] = $leadData->row();

        $data["master_data_source"] = $this->Feedback->getDataSourceList();

        $conditions = ['status_stage' => 'S16', 'status_active' => 1, 'status_deleted' => 0];
        $select = 'status_id, status_name, status_stage';
        $data['statusClosuer'] = $this->Feedback->select($conditions, $select, 'master_status');

        $this->load->view('Tasks/task_js.php', $data);
        $this->load->view('Tasks/main_js.php');
    }

    public function get_customer_feedback() {
        $result_array = array("status" => 0);
        $lead_id = $this->input->post('lead_id');
        if (!empty($lead_id)) {
            $lead_id = intval($this->encrypt->decode($lead_id));
            $feedback = $this->Feedback->get_customer_feedback($lead_id);

            if (!empty($feedback['status'])) {
                $result_array['status'] = 1;
                $result_array['feedback'] = $feedback['data'];
            }
        }
        echo json_encode($result_array);
    }

    public function view_customer_feedback($lead_id) {
        $result_array = array("status" => 0);
        if (!empty($lead_id)) {
            $lead_id = intval($this->encrypt->decode($lead_id));
            $feedback = $this->Feedback->get_customer_feedback($lead_id);

            if (!empty($feedback['status'])) {
                $result_array['status'] = 1;
                $result_array['feedback'] = $feedback;
            }
        }
        $this->load->view('Feedback/feedback_response', $result_array);
    }

    // function to export the XLX data into the database //

    public function __destruct() {
        $this->db->close();
    }

}
