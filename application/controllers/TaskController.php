<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('max_execution_time', 3600);
ini_set("memory_limit", "1024M");

class TaskController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo CAM";

    public function __construct() {
        parent::__construct();
        $this->load->model('Leadmod', 'Leads');
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Admin_Model', 'Admin');
        $this->load->model('CAM_Model', 'CAM');
        $this->load->model('Docs_Model', 'Docs');
        $this->load->model('Users/Email_Model', 'Email');
        $this->load->model('Users/SMS_Model', 'SMS');

        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");

        $login = new IsLogin();
        $login->index();
    }

    public function error_page() {
        $this->load->view('errors/index');
    }

    public function index($stage) {

        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
        $search_input_array = array();
        $where_in = array();

        if (!empty($_REQUEST['search']) && $_REQUEST['search'] == 1) {
            $search_input_array = $_REQUEST;
        }

        $this->load->library("pagination");

        $url = (base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3));

        $conditions = array();



        if (!empty($stage)) {
            $conditions["LD.stage"] = $stage;
        }

        if ($stage == "S1" && $this->uri->segment(1) == "screeninLeads") {
            $where_in['LD.lead_status_id'] = array(1);
        }

        if ($stage == "S1" && $this->uri->segment(1) == "partialLeads") {
            $where_in['LD.lead_status_id'] = array(42);
        }

        if (in_array($stage, ['S9'])) {
            $conditions["LD.lead_status_id"] = 9;

            if ($_REQUEST['search'] != 1) {
                $conditions["LD.lead_entry_date >="] = '2021-04-01';
            }
        }

        if ($this->uri->segment(2) == "S4" && $this->uri->segment(1) == "GetLeadTaskList") {
            unset($conditions["LD.stage"]);
            $conditions['LD.stage'] = $this->uri->segment(2);
            if (!empty($this->uri->segment(3))) {
                $conditions["LD.user_type"] = $this->uri->segment(3);
            }
        }

        if (in_array($stage, ['S2', 'S3', 'S9']) && $_SESSION['isUserSession']['labels'] == 'CR1') {
            $conditions["LD.lead_screener_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
        }

        if (in_array($stage, ['S5', 'S6', 'S10', 'S11', 'S12', 'S9']) && $_SESSION['isUserSession']['labels'] == 'CR2') {
            $conditions["LD.lead_credit_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
        }

        /* if (in_array($stage, ['S14']) && $_SESSION['isUserSession']['labels'] == 'CR2'  && $_SESSION['isUserSession']['labels'] == 'CO3') {
            $conditions["LD.lead_credit_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
        } */
        if (in_array($stage, ['S14']) && ($_SESSION['isUserSession']['labels'] == 'CR2'  || $_SESSION['isUserSession']['labels'] == 'CO3')) {
            $conditions["LD.lead_credit_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
        }
        if (in_array($stage, ['S14']) && ($_SESSION['isUserSession']['labels'] == 'CO1'  || $_SESSION['isUserSession']['labels'] == 'CO3')) {
            $where_in['LD.lead_status_id'] = array(19, 12, 13, 14);
        }

        if (in_array($stage, array("S13", "S21", "S22", "S25")) && $_SESSION['isUserSession']['labels'] == 'DS1') {
            $conditions["LD.lead_disbursal_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
        }

        if (in_array($stage, array("S31", "S32", "S33", "S34")) && in_array($_SESSION['isUserSession']['labels'], ['AH'])) {
            $where_in['LD.lead_status_id'] = array(44, 45, 46, 47);
        }

        if (in_array($stage, array("S32", "S33", "S34")) && in_array($_SESSION['isUserSession']['labels'], ['AM'])) {
            $where_in['LD.lead_status_id'] = array(45, 46, 47);
            $conditions['LD.lead_audit_assign_user_id'] = intval($_SESSION['isUserSession']['user_id']);
        }

        if (in_array($stage, array("S31")) && in_array($_SESSION['isUserSession']['labels'], ['AM'])) {
            $conditions['LD.lead_status_id'] = 44;
        }

        if ($this->uri->segment(1) == "audit-recommended-applications") {
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = array(44, 45, 46, 47);
            if (isset($_SESSION['isUserSession']['labels']) && $_SESSION['isUserSession']['labels'] == 'CR2') {
                $conditions["LD.lead_credit_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
            }
        }

        if ($this->uri->segment(1) == "collection") {
            unset($conditions["LD.stage"]);
            $from_repayment_date = date('Y-m-d', strtotime('-0 days', strtotime(date("Y-m-d"))));
            $to_repayment_date = date('Y-m-d', strtotime('+7 days', strtotime(date("Y-m-d"))));
            $conditions['LD.loan_no !='] = '';
            //$where_in['LD.lead_status_id'] = array(14);
            $conditions['CAM.repayment_date >='] = $from_repayment_date;
            $conditions['CAM.repayment_date <='] = $to_repayment_date;
        } else if ($this->uri->segment(1) == 'residence-verification' && in_array($_SESSION['isUserSession']['labels'], ['CO2'])) {
            unset($conditions["LD.stage"]);
            $conditions["LD.lead_fi_scm_residence_assign_user_id"] = intval($_SESSION['isUserSession']['user_id']);
            $conditions["LD.lead_fi_residence_status_id"] = 1;
        } else if ($this->uri->segment(1) == 'office-verification' && in_array($_SESSION['isUserSession']['labels'], ['CO2'])) {
            unset($conditions["LD.stage"]);
            $conditions["LD.lead_fi_scm_office_assign_user_id"] = intval($_SESSION['isUserSession']['user_id']);
            $conditions["LD.lead_fi_office_status_id"] = 1;
        } else if ($this->uri->segment(1) == 'closure') {
            unset($conditions["LD.stage"]);
            $conditions['LD.lead_status_id'] = 16;
        } else if ($this->uri->segment(1) == 'preclosure') {
            unset($conditions["LD.stage"]);
            $conditions['LD.lead_status_id >='] = 14;
            $conditions['CO.payment_verification'] = 0;
            $conditions['CO.collection_active'] = 1;
            $conditions['CO.collection_deleted'] = 0;
        } else if ($this->uri->segment(1) == "collection-pending") { // && !in_array($stage, ['S14', 'S16'])
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = [14, 16];
            $conditions['L.loan_recovery_status_id'] = 1;
        } else if ($this->uri->segment(1) == "recovery-pending") { // && !in_array($stage, ['S14', 'S16'])
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['L.loan_recovery_status_id'] = 2;
        } else if ($this->uri->segment(1) == "legal") { // && !in_array($stage, ['S14', 'S16'])
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['L.loan_recovery_status_id'] = 3;
        } else if ($this->uri->segment(1) == "settlement") { // && !in_array($stage, ['S14', 'S16'])
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = [17];
        } else if ($this->uri->segment(1) == "write-off") {
            unset($conditions["LD.stage"]);
            $where_in['LD.lead_status_id'] = [18];
        } else if (in_array($this->uri->segment(1), ["visitrequest"])) {
            unset($conditions["LD.stage"]);
            $conditions['LCV.col_visit_field_status_id'] = 1;
        } else if (in_array($this->uri->segment(1), ["visitpending"])) {
            unset($conditions["LD.stage"]);
            $conditions['LCV.col_visit_field_status_id'] = 2;
        } else if (in_array($this->uri->segment(1), ["visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $conditions['LCV.col_visit_field_status_id'] = 5;
        } else if ($this->uri->segment(1) == 'not-contactable') {
            unset($conditions['LD.lead_screener_assign_user_id']);
            //            $where_in['LD.lead_rejected_reason_id'] = [7, 31];
            //$conditions['LD.lead_rejected_assign_user_id>'] = 0;
            //if (agent == 'CR1') {
            $conditions['LD.lead_rejected_assign_user_id'] = user_id;
            //}
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'pre-collection' && in_array($_SESSION['isUserSession']['labels'], ['CO1', 'CR2'])) {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['LD.lead_collection_executive_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            $conditions['CAM.repayment_date >='] = date('Y-m-d', strtotime('-20 days'));
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('+5 days'));

            if (agent == "CR2") {
                unset($conditions['LD.lead_collection_executive_assign_user_id']);
                $conditions['LD.lead_credit_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            }
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'collection' && in_array($_SESSION['isUserSession']['labels'], ['CO1', 'CR2'])) {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['LD.lead_collection_executive_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            $conditions['CAM.repayment_date >='] = date('Y-m-d', strtotime('-60 days'));
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('-21 days'));

            if (agent == "CR2") {
                unset($conditions['LD.lead_collection_executive_assign_user_id']);
                $conditions['LD.lead_credit_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            }
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'recovery' && in_array($_SESSION['isUserSession']['labels'], ['CO1'])) {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['LD.lead_collection_executive_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('-61 days'));
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'pre-collection') {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            unset($conditions["LD.lead_collection_executive_assign_user_id"]);
            unset($conditions["LD.lead_credit_assign_user_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['CAM.repayment_date >='] = date('Y-m-d', strtotime('-20 days'));
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('+5 days'));
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'collection') {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            unset($conditions["LD.lead_collection_executive_assign_user_id"]);
            unset($conditions["LD.lead_credit_assign_user_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['CAM.repayment_date >='] = date('Y-m-d', strtotime('-60 days'));
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('-21 days'));
        } else if ($this->uri->segment(1) == 'assigned' && $this->uri->segment(2) == 'recovery') {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            unset($conditions["LD.lead_collection_executive_assign_user_id"]);
            unset($conditions["LD.lead_credit_assign_user_id"]);
            $where_in['LD.lead_status_id'] = [14, 19];
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('-61 days'));
        } else if ($this->uri->segment(1) == 'outstanding-cases' && $this->uri->segment(2) == 'NA') {
            unset($conditions["LD.stage"]);
            unset($conditions["LD.lead_status_id"]);
            unset($conditions["LD.lead_collection_executive_assign_user_id"]);
            unset($conditions["LD.lead_credit_assign_user_id"]);
            if (agent == "CR2") {
                $conditions['LD.lead_credit_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            }
            $conditions['CAM.repayment_date <='] = date('Y-m-d', strtotime('-15 days'));
            $where_in['LD.lead_status_id'] = [14, 19];
        }

        if (agent == "CO1" && !empty($_SESSION['isUserSession']['user_branch'])) {
            $where_in['LD.lead_branch_id'] = $_SESSION['isUserSession']['user_branch'];
        }

        if (agent == "CO2" && !empty($_SESSION['isUserSession']['user_state'])) {
            $where_in['LD.state_id'] = $_SESSION['isUserSession']['user_state'];
        }

        // print_r($_REQUEST['search']);
        // exit();

        $data['totalcount'] = $this->Tasks->countLeads($conditions, $search_input_array, $where_in);
        // $data['totalcount'] = $this->Tasks->countLeads($conditions,$where_in);
        $data['loan_recommended_total'] = $this->Tasks->countAmountLeads($conditions, $search_input_array, $where_in);
        $data['total_outstanding'] = $this->Tasks->totalOutstandingAmount($conditions, $search_input_array, $where_in);

        $config = array();
        $config["base_url"] = $url;

        // $page = !empty($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 0;
        $page = $this->input->get('per_page', TRUE) ? $this->input->get('per_page', TRUE) : 0;

        if (!empty($_REQUEST['search']) && $_REQUEST['search'] == 1) {
            unset($_REQUEST['csrf_token']);
            unset($_REQUEST['per_page']);
            $config["base_url"] .= "?";
            $request_search_url = http_build_query($_REQUEST);
            $config["base_url"] .= $request_search_url;
        }

        $config['page_query_string'] = TRUE;
        $config["total_rows"] = $data['totalcount'];
        $config["per_page"] = 25;
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

        if (isset($_REQUEST['sOrderBy']) && !empty($_REQUEST['sOrderBy'])) {
            $conditions["sOrderBy"] = $_REQUEST['sOrderBy'];
        }
        // $data['leadDetails'] = $this->Tasks->index($conditions, $config["per_page"], $page, $search_input_array, $where_in);
        // $data['leadDetails'] = $this->Tasks->index($page,$search_input_array, $where_in);
        $data['leadDetails'] = $this->Tasks->index($conditions, $config["per_page"], $page, $search_input_array, $where_in);
        // print_r($page);
        // exit;

        $data["totalDisbursePendingAmount"] = 0;

        if (!empty($stage) && $stage == "S13") {
            $disbursePendingConditions = array();
            $disbursePendingConditions["CAM.cam_active"] = 1;
            $disbursePendingConditions["CAM.cam_deleted"] = 0;
            $disbursePendingConditions["LD.lead_status_id"] = 13;

            if ($_SESSION['isUserSession']['labels'] == 'DS1') {
                $disbursePendingConditions["LD.lead_disbursal_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
            }

            $totalDisbursePendingAmount = $this->db->select_sum('CAM.loan_recommended')->where($disbursePendingConditions)->from('leads LD')->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'left')->get();
            $totalDisbursePendingAmount = $totalDisbursePendingAmount->row();
            $data["totalDisbursePendingAmount"] = !empty($totalDisbursePendingAmount->loan_recommended) ? $totalDisbursePendingAmount->loan_recommended : 0;
        }

        if (in_array($_SESSION['isUserSession']['labels'], ['AM', 'AH', 'CR2', 'CR3'])) {
            $disbursePendingConditions = array();
            $disbursePendingConditions["CAM.cam_active"] = 1;
            $disbursePendingConditions["CAM.cam_deleted"] = 0;
            $AppDisbursedConditionsIn["LD.lead_status_id"] = array(44, 45, 46, 47);

            if ($_SESSION['isUserSession']['labels'] == 'AM') {
                unset($AppDisbursedConditionsIn["LD.lead_status_id"]);
                $disbursePendingConditions["LD.lead_audit_assign_user_id"] = $_SESSION['isUserSession']['user_id'];
                $AppDisbursedConditionsIn["LD.lead_status_id"] = 44;
            }

            $totalDisbursePendingAmount = $this->db->select_sum('CAM.loan_recommended')->where($disbursePendingConditions)->where_in('LD.lead_status_id', $AppDisbursedConditionsIn['LD.lead_status_id'])->from('leads LD')->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'left')->get();
            $totalDisbursePendingAmount = $totalDisbursePendingAmount->row();
            $data["totalDisbursePendingAmount"] = !empty($totalDisbursePendingAmount->loan_recommended) ? $totalDisbursePendingAmount->loan_recommended : 0;
        }



        // $data["totalInprocessAmount"] = 0;

        // if (!empty($stage) && $stage == "S10") {
        //     $AppinprocessConditions = array();
        //     $AppinprocessConditions["CAM.cam_active"] = 1;
        //     $AppinprocessConditions["CAM.cam_deleted"] = 0;
        //     $AppinprocessConditions["LD.lead_status_id"] = 10;

        //     if ($_SESSION['isUserSession']['labels'] == 'CR2') {
        //         $AppinprocessConditions["LD.lead_credithead_assign_datetime"] = $_SESSION['isUserSession']['user_id'];
        //     }

        //     $totalInprocessAmount = $this->db->select_sum('CAM.loan_recommended')->where($AppinprocessConditions)->from('leads LD')->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'left')->get();
        //     $totalInprocessAmount = $totalInprocessAmount->row();
        //     $data["totalInprocessAmount"] = !empty($totalInprocessAmount->loan_recommended) ? $totalInprocessAmount->loan_recommended : 0;
        // }

        // $data["totalDisbursedAmount"] = 0;

        // if (!empty($stage) && $stage == "S14") {
        //     $AppDisbursedConditions = array();
        //     $AppDisbursedConditions["CAM.cam_active"] = 1;
        //     $AppDisbursedConditions["CAM.cam_deleted"] = 0;
        //     $AppDisbursedConditions["LD.lead_status_id"] = 14;

        //     if ($_SESSION['isUserSession']['labels'] == 'DS2') {
        //         $AppDisbursedConditions["LD.lead_credithead_assign_datetime"] = $_SESSION['isUserSession']['user_id'];
        //     }

        //     $totalDisbursedAmount = $this->db->select_sum('CAM.loan_recommended')->where($AppDisbursedConditions)->from('leads LD')->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'left')->get();
        //     $totalDisbursedAmount = $totalDisbursedAmount->row();
        //     $data["totalDisbursedAmount"] = !empty($totalDisbursedAmount->loan_recommended) ? $totalDisbursedAmount->loan_recommended : 0;
        // }
        $data["links"] = $this->pagination->create_links();

        $data["master_data_source"] = $this->Tasks->getDataSourceList();
        $data["master_city"] = $this->Tasks->getCityList($search_input_array['ssid']);
        $data["master_state"] = $this->Tasks->getStateList();
        $data["master_branch"] = $this->Tasks->getBranchList();
        $data["user_list"] = $this->Tasks->getUserList($stage);
        $data["search_input_array"] = $search_input_array;

        if ($stage == 'S5' && $_SESSION['isUserSession']['labels'] == 'CR2') {
            $data["uqickCall"] = 'button';
        } else {
            $data["uqickCall"] = '';
        }

        if (($this->uri->segment(1) == 'audit-recommended-applications' || $this->uri->segment(1) == 'audit-new') &&  in_array($_SESSION['isUserSession']['labels'], ['AM', 'AH', 'CR2', 'CR3', 'AU'])) {
            $this->load->view('Audit/audit_list', $data);
        } else {
            $this->load->view('Tasks/GetLeadTaskList', $data);
        }
    }

    public function enquires() {
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        $conditions = "enquiry.cust_enquiry_active='" . 1 . "' AND enquiry.cust_enquiry_deleted=0 AND (enquiry.cust_enquiry_lead_id IS NULL OR enquiry.cust_enquiry_lead_id=0)";

        if (!empty($this->input->post('search_input')) && !empty($this->input->post('search_type'))) {
            $search_type = $this->input->post('search_type');
            $search_input = $this->input->post('search_input');
            if ($search_type == 1) {
                $conditions .= " AND enquiry.cust_enquiry_mobile='$search_input'";
            } else if ($search_type == 2) {
                $conditions .= " AND enquiry.cust_enquiry_email='$search_input'";
            } else if ($search_type == 3) {
                $conditions .= " AND enquiry.cust_enquiry_id=$search_input";
            }
        }


        $data['totalcount'] = $this->Tasks->enquiriesCount($conditions);
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $data['totalcount']; // get count leads
        $config["per_page"] = 10;
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
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $data['links'] = $this->pagination->create_links();
        $data['pageURL'] = $url;

        $data['leadDetails'] = $this->Tasks->enquires($conditions, $config["per_page"], $page);

        $this->load->view('Enquires/enquires', $data);
    }

    public function getLeadDetails($leadId) {
        // error_reporting(E_ALL);
        //   ini_set("display_errors", 1);
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");

        $lead_id = $this->encrypt->decode($leadId);

        $data['isAnotherLeadInprocess'] = $this->Tasks->isAnotherLeadInprocess($lead_id);

        $conditions['LD.lead_id'] = $lead_id;
        $leadData = $this->Tasks->getLeadDetails($conditions);

        $data['leadDetails'] = $leadData->row();


        $sql2 = $this->Tasks->select(['CAM.lead_id' => $lead_id], 'CAM.cam_status', $this->tbl_cam);
        $data['camDetails'] = (object) ['cam_status' => 0];
        if ($sql2->num_rows() > 0) {
            $data['camDetails'] = $sql2->row();
        }
        $data['docs_master'] = $this->Docs->docs_type_master();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();
        $data["master_bank_account_status"] = $this->Tasks->getBankAccountStatusList();
        $data["master_disbursement_bank_list"] = $this->Tasks->getDisbursementBankList();

        $conditions = ['status_stage' => 'S16', 'status_active' => 1, 'status_deleted' => 0];
        $select = 'status_id, status_name, status_stage';
        $data['statusClosuer'] = $this->Tasks->select($conditions, $select, 'master_status');

        $this->load->view('Tasks/task_js.php', $data);
        $this->load->view('Tasks/main_js.php');
    }


    public function sendLegalnotice() {
        $lead_id = $this->input->post('lead_id');
        //  $lead_id = 4;

        // Fetch lead details
        $sql = $this->db->select('LD.*, LD.first_name, LD.lead_status_id, LD.lead_final_disbursed_date, C.current_house, L.loan_no, L.loan_total_outstanding_amount, L.loan_interest_payable_amount, L.loan_penalty_payable_amount, CAM.loan_recommended, CAM.repayment_amount')
            ->from('leads LD')
            ->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT')
            ->join('loan L', 'L.lead_id = LD.lead_id', 'LEFT')
            ->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT')
            ->where('LD.lead_id', $lead_id)
            ->get();

        $result = $sql->row();
        //print_r($result); die;

        $email_to = $result->email;
        $firstname = $result->first_name;
        $lead_status_id = $result->lead_status_id;
        $leadstatus = $result->status;
        $leadStage = $result->stage;
        $current_house = $result->current_house;
        $loan_no = $result->loan_no;
        $loan_recommended = $result->loan_recommended;
        $interest = $result->loan_interest_payable_amount;
        $totalinterest = $result->loan_total_outstanding_amount;
        $panelty = $result->loan_penalty_payable_amount;
        $repayment_amount = $result->repayment_amount;
        $final_disbursed_date = $result->lead_final_disbursed_date;
        $current_date = date('d, M Y');

        if (!$result) {
            echo json_encode(['status' => 0, 'msg' => 'Lead not found.']);
            die;
        }

        if (isset($lead_status_id) && ($lead_status_id != 14) && ($lead_status_id != 19)) {
            $return_array['status'] = 0;
            $return_array['msg'] = "Not allow to send Legal Notice on Disburse Status!";
            echo json_encode($return_array);
            die;
        }

        //$email_to = "sandeeptech2contact@gmail.com";
        $cc_mail = "info@tejasloan.com";
        $subject = "Legal Notice Letter";
        $message = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Legal Notice</title>
                        <style>
                            body {
                                font-family: \'Courier\', \'Courier New\', monospace;
                                line-height: 1.6;
                                color: #222;
                                font-size: small;
                                letter-spacing: 0.5px;
                            }
                            .container {
                                width: 800px; /* Set a fixed width */
                                margin: 0 auto;
                                padding: 0;
                            }
                            .legal-notice h2 {
                                text-align: center;
                                margin: 0;
                                color: #222;
                                font-size:10px;
                            }
                            .legal-notice p, .legal-notice ol {
                                margin: 0;
                            }
                            .legal-notice ol {
                                padding-left: 20px;
                            }
                            .legal-notice ol li {
                                margin-bottom: 10px;
                            }
                            .signature {
                                text-align: right;
                                margin-top: 30px;
                                color: #333;
                            }
                            .legal_cr_1 {
                                float: left;
                                width: 25%;
                            }
                            .legal_cr {
                                float: left;
                                width: 42%;
                            }
                            .legalnotice {
                                width: 92%;
                            }
                            section.legal-notice {
                                padding: 2px;
                            }
                            header img {
                                width: 100%;
                                max-width: 800px; /* Match the container width */
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container" style="font-family: Courier,arial;">
                            <header>
                                <img src="' . base_url() . 'public/images/legal-header.jpg" width="800"> <!-- Adjust width to match container -->
                            </header>
                            <section class="legal-notice">
                                <p style="color:#000;"><strong>DELHI</strong><br/><span style="float: right;">' . $current_date . '</span></p>
                                <p style="color:#000;">TO ,<br>SHRI/SMT <b> ' . $firstname . ' </b><br>R/O - <b>' . $current_house . ' </b></p><br/>
                                <h2 style="color:#000;">Legal Notice (without prejudice)</h2>
                                <p style="color:#000;">Sir/Ma\'am</p>
                                <p style="color:#000;">Under instructions from and on behalf of my client <strong style="background:#FFFF00;">Agrim Fincap Pvt. Ltd.</strong> with the brand name <strong style="background:#FFFF00;">Tejas Loan</strong> having its office S-370, Panchsheel Park, New Delhi - 110017, I address you as under.</p>
                                <ol>
                                    <li style="color:#000;">That you had approached my client for a short-term loan as you were in dire need of money on <strong>' . date('d, M Y', strtotime($final_disbursed_date)) . '</strong>.</li>
                                    <li style="color:#000;">That pursuant to the terms and conditions of the Loan agreement form as agreed by you, you were provided the short-term loan of Rs. <strong>' . $loan_recommended . '</strong> with Loan No.<strong> ' . $loan_no . '</strong> at a mutually agreed rate of interest.</li>
                                    <li style="color:#000;">That you had promised and agreed to repay the said loan amount with applicable interest rate but you did not repay the total amount of Rs.<strong>' . $totalinterest . '</strong> till date. You have breached the loan contract that you entered into with my client namely <strong>Agrim Fincap Pvt. Ltd.</strong> with the brand name <strong>Tejas Loan</strong>.</li>
                                    <li style="color:#000;">That thus by your act and conduct it is evident that since the time of availing such loan you had malafide intention.</li>
                                    <li style="color:#000;background:#FFFF00;">That as on date an amount of Rs.<strong>' . $totalinterest . ' </strong> is due and payable by you in the aforesaid connection to our client.</li>
                                </ol>
                                <p style="color:#000;">I therefore by means of the present legal notice call upon you the notice to make the payment of the aforesaid amount Rs <strong>' . $totalinterest . '</strong> to my client immediately after receipt of this notice by you, failing which my client shall be constrained to initiate legal proceedings against you under the provisions of section 318, 337 & 338 of THE Bhartiya Nyaya Sanhita 2023 entirely at your cost, risk, and responsibility.</p>
                                <p style="color:#000;">A copy of this notice has been retained in my office for further reference, record, and action.</p><br/>
                                <p style="color:#000;" class="signature">Yours faithfully</p>
                                <img src="' . base_url() . 'public/images/sign.png" style="float: right;width: 100px;margin-right: 31px;"/><br/><br/>
                                <p style="color:#000;clear: both;" class="signature"><strong>(HARMOD LAMBA)<br>Advocate</strong></p>
                            </section>
                        </div>
                    </body>
                    </html>';
        //echo $message; die;
        $file_name = "legal_letter_" . $lead_id . "_" . date('Ymd') . ".pdf";
        $file_path_with_name = UPLOAD_LEGAL_PATH . $file_name;

        $file_url_path = LMS_URL . $file_name;
        $from_email = "info@tejasloan.com";

        require_once __DIR__ . '/../../vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($message);
        $mpdf->Output($file_path_with_name, 'F');

        if (file_exists($file_path_with_name)) {

            require_once(COMPONENT_PATH . "CommonComponent.php");
            $CommonComponent = new CommonComponent();
            $request_array = array();
            $request_array['flag'] = 1;
            $request_array['file'] = base64_encode(file_get_contents($file_path_with_name));
            $request_array['ext'] = pathinfo($file_path_with_name, PATHINFO_EXTENSION);

            $upload_return = $CommonComponent->upload_document($lead_id, $request_array);
            // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
            $return_array = common_send_email($email_to, $subject, $message, "", $cc_mail = "info@tejasloan.com", $from_email, "", $file_path_with_name, $file_name, 'legal_notice.pdf');

            if ($upload_return['status'] == 1) {
                $return_array['status'] = 1;
                $file_name = $upload_return['file_name'];
                unlink($file_path_with_name);
            }

            $return_array['status'] = 1;
            $return_array['msg'] = "Legal Notice Send Successfully";

            // update column
            $update_legal_letter = ['legal_notice_letter' => $file_name];
            $this->db->where('lead_id', $lead_id)->update('loan', $update_legal_letter);

            //Insert logs
            $insertLegalNotice = array(
                'legal_email_provider' => "MailGun",
                'legal_email_sent_to' => $email_to,
                'legal_email_content' => $file_url_path,
                'legal_email_api_status_id' => $return_array['status'],
                'legal_email_lead_id' => $lead_id,
                'legal_notice_send_by' => $_SESSION['isUserSession']['user_id'],
                'legal_email_created_on ' => date('Y-m-d H:i:s')
            );
            $this->db->insert('legal_email_logs', $insertLegalNotice);

            $insertApiLog = array(
                'created_on ' => date('Y-m-d H:i:s'),
                'status' => $leadstatus,
                'stage' => $leadStage,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'lead_id' => $lead_id,
                'lead_followup_status_id' => $lead_status_id,
                'reason' => "Legal Notice Send successfully"
            );
            $this->db->insert('lead_followup', $insertApiLog);
            $this->db->insert_id();
            echo json_encode($return_array);
            die;
        }
    }


    public function getLeadDetails_new($leadId) {
        // error_reporting(E_ALL);
        //   ini_set("display_errors", 1);

        $lead_id = $this->encrypt->decode($leadId);

        $data['isAnotherLeadInprocess'] = $this->Tasks->isAnotherLeadInprocess($lead_id);

        $conditions['LD.lead_id'] = $lead_id;
        $leadData = $this->Tasks->getLeadDetails($conditions);

        $data['leadDetails'] = $leadData->row();


        $sql2 = $this->Tasks->select(['CAM.lead_id' => $lead_id], 'CAM.cam_status', $this->tbl_cam);
        $data['camDetails'] = (object) ['cam_status' => 0];
        if ($sql2->num_rows() > 0) {
            $data['camDetails'] = $sql2->row();
        }
        $data['docs_master'] = $this->Docs->docs_type_master();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();
        $data["master_bank_account_status"] = $this->Tasks->getBankAccountStatusList();
        $data["master_disbursement_bank_list"] = $this->Tasks->getDisbursementBankList();

        $conditions = ['status_stage' => 'S16', 'status_active' => 1, 'status_deleted' => 0];
        $select = 'status_id, status_name, status_stage';
        $data['statusClosuer'] = $this->Tasks->select($conditions, $select, 'master_status');

        $this->load->view('Tasks/task_js_new.php', $data);
        $this->load->view('Tasks/main_js.php');
    }

    public function getEnquiryDetails($cust_enquiry_id) {
        $cust_enquiry_id = $this->encrypt->decode($cust_enquiry_id);
        $conditions = ['enquiry.cust_enquiry_id' => $cust_enquiry_id];
        $leadData = $this->Tasks->enquires($conditions);
        $data['leadDetails'] = $leadData->row();
        $data['docs_master'] = $this->Docs->docs_type_master();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();

        $stateArr = $this->Tasks->getState();
        $data['state'] = $stateArr->result();

        $this->load->view('Enquires/enquiry_application', $data);
    }

    public function getPincode($city_id) {
        $pincodeArr = $this->Tasks->getPincode($city_id);
        $json['pincode'] = $pincodeArr->result();
        echo json_encode($json);
    }

    // public function sendLegalnotice()
    // {
    //     $result_array = array();

    //     if (empty($_SESSION['isUserSession']['user_id'])) {
    //         $json['errSession'] = 'Session Expired.';
    //         echo json_encode($json);
    //         return false;
    //     }


    //         $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');

    //         if ($this->form_validation->run() == FALSE) {
    //             $json['err'] = validation_errors();
    //             echo json_encode($json);
    //         } else {

    //             $lead_id = $this->encrypt->decode($leadId);
    //             $sql = $this->db->select('leads.lead_id')->where('lead_id', $lead_id)->from('leads')->get()->row();
    //             $leadID = $sql->lead_id;
    //             print_r($pancard); die;

    //         }

    //     //echo json_encode($result_array);
    // }

    public function getCity($state_id = null) {
        $cityArr = $this->Tasks->getCity($state_id);
        $json['city'] = $cityArr->result();
        echo json_encode($json);
    }

    public function getState() {
        $stateArr = $this->Tasks->getState();
        // print_r($stateArr); die;
        $json['state'] = $stateArr->result();
        echo json_encode($json);
    }

    public function getReligion() {
        $stateArr = $this->Tasks->getReligion();
        $json['religion'] = $stateArr->result();
        echo json_encode($json);
    }

    public function getMaritalStatus() {
        $stateArr = $this->Tasks->getMaritalStatus();
        $json['MaritalStatus'] = $stateArr->result();
        echo json_encode($json);
    }

    public function getQualification() {
        $stateArr = $this->Tasks->getQualification();
        $json['Qualification'] = $stateArr->result();
        echo json_encode($json);
    }

    public function getSpouseOccupation() {
        $stateArr = $this->Tasks->getSpouseOccupation();
        $json['SpouseOccupation'] = $stateArr->result();
        echo json_encode($json);
    }

    // Qualification
    // SpouseOccupation

    public function apiPincode($pincode) {
        $url = 'https://api.postalpincode.in/pincode/' . $pincode;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        $array = $result[0]->PostOffice;

        echo json_encode($array);
    }

    public function scmConfRequest() {
        if ($this->input->post('user_id') == '') {
            $json['err'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('user_id', 'Session Expired', 'required|trim');
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
                return false;
            } else {
                $where = ['company_id' => company_id, 'product_id' => product_id];
                $lead_id = $this->input->post('lead_id');
                $customer_id = $this->input->post('customer_id');

                echo "else called : <pre>";
                // print_r($_POST);
                // exit;

                $data1 = [
                    'status' => $status,
                    'stage' => $stage,
                ];
                $data2 = [
                    'lead_id' => $lead_id,
                    'customer_id' => $this->input->post('customer_id'),
                    'user_id' => $this->input->post('user_id'),
                    'status' => $status,
                    'stage' => $stage,
                    'remarks' => $this->input->post('hold_remark'),
                    'scheduled_date' => date('d-m-Y h:i:sa', strtotime($this->input->post('hold_date'))),
                    'created_on' => date("Y-m-d H:i:s"),
                ];

                $conditions = ['lead_id' => $lead_id];
                $this->Tasks->updateLeads($conditions, $data1, 'leads');
                $this->Tasks->insert($data2, 'lead_followup');
                $data['msg'] = 'Application Hold Successfuly.';
                echo json_encode($data);
            }
        } else {
            $json['err'] = 'Invalid Request.';
            echo json_encode($json);
        }
    }

    public function getLeadDisbursed1() {
        $limit = $this->input->post('limit');
        $start = $this->input->post('start');
        $data = $this->Tasks->leadDisbursed1($limit, $start);
        $output = '
                <table class="table dt-tables table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                    <thead>
                        <tr>
                            <th><b>Sr. No</b></th>
                            <th><b>Action</b></th>
                            <th><b>Applicationdsf No</b></th>
                            <th><b>Borrower</b></th>
                            <th><b>State</b></th>
                            <th><b>City</b></th>
                            <th><b>Mobile</b></th>
                            <th><b>Email</b></th>
                            <th><b>PAN</b></th>
                            <th><b>Source</b></th>
                            <th><b>Status</b></th>
                            <th><b>Initiated On</b></th>
                        </tr>
                    </thead>
                    <tbody>
            ';
        if ($data->num_rows() > 0) {
            $i = $start++;
            foreach ($data->result() as $row) {
                $output .= '
                    <div class="post_data">
                            <tr class="table-default">
                                <td>' . $start++ . '</td>
                                <td>
                                    <a href="#" onclick="viewLeadsDetails(' . $row->lead_id . ')" id="viewLeadsDetails" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil-square-o" title="View Costomer Details"></i></a>
                                </td>
                                <td></td>
                                <td>' . strtoupper($row->name . " " . $row->middle_name . " " . $row->sur_name) . '</td>
                                <td>' . strtoupper($row->state) . '</td>
                                <td>' . strtoupper($row->city) . '</td>
                                <td>' . $row->mobile . '</td>
                                <td>' . $row->email . '</td>
                                <td>' . strtoupper($row->pancard) . '</td>
                                <td>' . $row->source . '</td>
                                <td>' . strtoupper($row->status) . '</td>
                                <td>' . date('d-m-Y', strtotime($row->created_on)) . '</td>
                            </tr>
                    </div>
                    ';
            }
            $output .= '</tbody></table>';
        }
        echo $output;
    }

    public function viewOldHistory($leadId) {
        $lead_id = $this->encrypt->decode($leadId);
        $leadData = $this->Tasks->internalDedupe($lead_id);
        //        $data_source_array = $this->Tasks->getDataSourceList();
        //echo "<pre>"; print_r($leadData); die;
        $data = '<div class="table-responsive">
		    <table class="table table-hover table-striped table-bordered">
                  	<thead>
                        <tr class="table-primary">
                            <th class="whitespace">Lead ID</th>
                            <th class="whitespace">Applied&nbsp;On</th>
                            <th class="whitespace">Status</th>
                            <th class="whitespace">Loan&nbsp;No</th>
                            <th class="whitespace">Borrower</th>
                            <th class="whitespace">Father Name</th>
                            <th class="whitespace">DOB</th>
                            <th class="whitespace">PAN</th>
                            <th class="whitespace">Mobile</th>
                            <th class="whitespace">Alternate Mobile</th>
                            <th class="whitespace">Email</th>
                            <th class="whitespace">Alternate Email</th>
                            <th class="whitespace">Aaddhaar</th>
                            <th class="whitespace">State</th>
                            <th class="whitespace">City</th>
                            <th class="whitespace">Loan&nbsp;Amount</th>
                            <th class="whitespace">Disbursed&nbsp;On</th>
                            <th class="whitespace">Source</th>
                            <th class="whitespace">Reject Reason</th>
                            <th class="whitespace">Rejected By</th>
                        </tr>
                  	</thead>';
        if (isset($leadData['result']) && count($leadData['result']) > 0) {
            $lead_data = $leadData['lead_data'];
            $i = 1;
            foreach ($leadData['result'] as $colum) {
                //                $sql3 = $this->Tasks->select(['lead_id' => $colum->lead_id], 'disbursal_date', 'credit_analysis_memo');
                //                $cam = $sql3->row();
                $data .= '<tbody>
                            <tr>
                                <td class="whitespace"><a href="' . base_url('getleadDetails/' . $this->encrypt->encode($colum->lead_id)) . '">' . $colum->lead_id . '</a></td>
                                <td class="whitespace">' . date('d-m-Y H:i', strtotime($colum->lead_initiated_date)) . '</td>
                                <td class="whitespace">' . (!empty($colum->status) ? $colum->status : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->loan_no) ? $colum->loan_no : '-') . '</td>
                                <td class="whitespace"><span ' . (($colum->first_name == $lead_data['first_name']) ? 'style="color:red"' : '') . ' >' . $colum->first_name . ' </span>' . $colum->middle_name . ' ' . $colum->sur_name . '</td>
                                <td class="whitespace"><span ' . (($colum->father_name == $lead_data['father_name']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->father_name) ? $colum->father_name : '-') . '</span></td>
                                <td class="whitespace"><span ' . (($colum->dob == $lead_data['dob']) ? 'style="color:red"' : '') . ' >'  . (!empty($colum->dob) ? date("d-m-Y", strtotime($colum->dob)) : '-') . '</span></td>
                                <td class="whitespace"><span ' . (($colum->pancard == $lead_data['pancard']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->pancard) ? $colum->pancard : '-') . '</span></td>

                                <td class="whitespace"><span ' . (($colum->mobile == $lead_data['mobile'] || $colum->mobile == $lead_data['alternate_mobile']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->mobile) ? $colum->mobile : '-') . '</span></td>

                                <td class="whitespace"><span ' . (($colum->alternate_mobile == $lead_data['alternate_mobile'] || $colum->alternate_mobile == $lead_data['mobile']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->alternate_mobile) ? $colum->alternate_mobile : '-') . '</span></td>

                                <td class="whitespace"><span ' . (($colum->email == $lead_data['email']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->email) ? $colum->email : '-') . '</span></td>

                                <td class="whitespace"><span ' . (($colum->alternate_email == $lead_data['alternate_email']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->alternate_email) ? $colum->alternate_email : '-') . '</span></td>

                                <td class="whitespace"><span ' . (($colum->aadhar_no == $lead_data['aadhar_no']) ? 'style="color:red"' : '') . ' >' . (!empty($colum->aadhar_no) ? $colum->aadhar_no : '-') . '</span></td>

                                <td class="whitespace">' . (!empty($colum->state) ? $colum->state : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->city) ? $colum->city : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->loan_amount) ? $colum->loan_amount : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->disbursal_date) ? date("d-m-Y", strtotime($colum->disbursal_date)) : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->source) ? $colum->source : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->reject_reason) ? $colum->reject_reason : '-') . '</td>
                                <td class="whitespace">' . (!empty($colum->rejected_by_name) ? $colum->rejected_by_name : '-') . '</td>
                            </tr>';
                $i++;
            }
        } else {
            $data .= '<tbody><tr><td colspan="16" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        echo json_encode($data);
    }

    public function oldUserHistory($lead_id) {
        $sql = $this->db->select('pancard, mobile')->where('lead_id', $lead_id)->from('leads')->get();
        $result = $sql->row();
        $pancard = $result->pancard;
        if (empty($pancard)) {
            $result = $sql->result();
            foreach ($result as $row) {
                if (!empty($row->pancard)) {
                    $pancard = $row->pancard;
                    break;
                }
            }
        }
        $this->db->select('leads.lead_id, leads.name, leads.email, leads.pancard, tb_states.state, leads.created_on, leads.source, leads.status, leads.credit_manager_id, leads.partPayment,
		            loan.loan_amount, loan.loan_tenure, loan.loan_intrest, loan.loan_repay_amount, loan.loan_repay_date, loan.loan_disburse_date, loan.loan_admin_fee')
            ->where('leads.pancard', $pancard)
            ->where('leads.loan_approved', 3)
            ->from(tableLeads)
            ->join('tb_states', 'leads.state_id = tb_states.id')
            ->join('loan', 'leads.lead_id = loan.lead_id');
        $query = $this->db->order_by('leads.lead_id', 'desc')->get();
        $data['taskCount'] = $query->num_rows();
        $data['listTask'] = $query->result();

        $data = '<div class="table-responsive">
		        <table class="table table-hover table-striped">
                  <thead>
                    <tr class="table-primary">
                      <th><b>Sr. No</b></th>
                        <th><b>Action</b></th>
                        <th><b>Borrower Name</b></th>
                        <th><b>Email</b></th>
                        <th><b>Pancard</b></th>
                        <th><b>Loan Amount</b></th>
                        <th><b>Loan Tenure</b></th>
                        <th><b>Loan Interest</b></th>
                        <th><b>Loan Repay Amount</b></th>
                        <th><b>Loan Repay Date</b></th>
                        <th><b>Loan Disbursed Date</b></th>
                        <th><b>Loan Admin Fee</b></th>
                        <th><b>Center</b></th>
                        <th><b>Initiated On</b></th>
                        <th><b>Lead Source</b></th>
                        <th><b>Lead Status</b></th>
                    </tr>
                  </thead>';
        if ($effected_rows) {
            $i = 1;
            foreach ($effected_rows as $column) {
                if ($column->status == 'Full Payment' || $column->status == 'Settelment') {
                    $optn = '<i class="fa fa-check" style="font-size:24px;color:green"></i>';
                    $status = 'Full Payment';
                } else {
                    $status = 'ACTIVE';
                }
                $data .= '<tbody>
                		<tr>
							<td>' . $i . '</th>
							<td>' . $optn . '</td>
							<td>' . $colum->name . '</td>
                            <td>' . $colum->email . '</td>
                            <td>' . $colum->pancard . '</td>
                            <td>' . $colum->loan_amount . '</td>
                            <td>' . $colum->loan_tenure . '</td>
                            <td>' . $colum->loan_intrest . '</td>
                            <td>' . $colum->loan_repay_amount . '</td>
                            <td>' . $colum->loan_repay_date . '</td>
                            <td>' . $colum->loan_disburse_date . '</td>
                            <td>' . $colum->loan_admin_fee . '</td>
                            <td>' . $colum->state . '</td>
                            <td>' . $colum->created_on . '</td>
                            <td>' . $colum->source . '</td>
						</tr>';
            }

            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan="8" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        echo json_encode($data);

        $this->load->view('Tasks/oldHistory', $data);
    }

    public function TaskList() {
        $this->index();
    }

    public function getDocumentSubType($docs_type) {
        $docs_type = str_ireplace("%20", " ", trim($docs_type));
        $docsSubMaster = $this->Docs->getDocumentSubType($docs_type);
        $data = $docsSubMaster->result();
        echo json_encode($data);
    }

    public function getDocsUsingAjax($lead_id) {
        $lead_id =  $this->encrypt->decode($lead_id);

        $sql = $this->db->select('leads.pancard')->where('lead_id', $lead_id)->from('leads')->get()->row();

        $pancard = $sql->pancard;

        $fetch = "D.lead_id, U.name, D.application_no, D.docs_id, D.docs_type, D.sub_docs_type, D.pwd, D.file, D.created_on";

        $cond_str = "(D.lead_id=" . $lead_id;

        if (!empty($pancard)) {
            $cond_str .= " OR D.pancard='$pancard'";
        }

        $cond_str .= ") AND docs_active=1 AND docs_deleted=0";

        $docsDetails = $this->db->select($fetch)
            ->where($cond_str)
            ->from('docs D')
            ->join('users U', 'U.user_id = D.upload_by', 'left')
            ->order_by('D.docs_id', 'desc')
            ->get();

        // $conditions = ['D.customer_id' => $this->input->post("customer_id")];
        //        $conditions = ['D.lead_id' => $lead_idc];
        //        $join2 = 'U.user_id = D.upload_by';
        //        $docsDetails = $this->Tasks->join_two_table_with_where($conditions, $fetch, $this->tbl_docs, $this->tbl_users, $join2);
        //        $this->db->order_by('D.docs_id', 'desc');
        //<th class="whitespace" scope="col"><b>Application&nbsp;No.</b></th>
        $data = '<div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" style="margin-top: 10px;">
                  <thead>
                    <tr class="table-primary">
                      <th class="whitespace" scope="col"><b>Doc ID</b></th>
                      <th class="whitespace" scope="col"><b>Lead ID</b></th>
                      <th class="whitespace" scope="col"><b>Document&nbsp;Type</b></th>
                      <th class="whitespace" scope="col"><b>Document&nbsp;Name</b></th>
                      <th class="whitespace" scope="col"><b>Password</b></th>
                      <th class="whitespace" scope="col"><b>Uploaded&nbsp;By</b></th>
                      <th class="whitespace" scope="col"><b>Uploaded&nbsp;On</b></th>
                      <th class="whitespace" scope="col"><b>Action</b></th>
                    </tr>
                </thead>';
        if ($docsDetails->num_rows() > 0) {
            // onclick="viewCustomerDocs('.$column->docs_id.')"
            $i = 1;
            foreach ($docsDetails->result() as $column) {
                $date = $column->created_on;
                $newDate = date("d-m-Y H:i:s", strtotime($date));
                $deleteDocs = '';
                if ((agent == "CR2" || agent == "CA" || agent == "SA") && ($leadDetails->stage == "S5" || $leadDetails->stage == "S6" || $leadDetails->stage == "S11")) {
                    //                    $deleteDocs = '<a onclick="deleteCustomerDocs(' . $column->docs_id . ')"><i class="fa fa-trash" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a>';
                } else {
                }
                //							<td class="whitespace">' . (($column->application_no != null) ? $column->application_no : '-') . '</td>
                $data .= '<tbody>
                		<tr ' . (($lead_id != $column->lead_id) ? "class='danger'" : "") . '>
							<td class="whitespace">' . $column->docs_id . '</td>
							<td class="whitespace">' . $column->lead_id . '</td>
							<td class="whitespace">' . $column->docs_type . '</td>
							<td class="whitespace">' . $column->sub_docs_type . '</td>
                            <td class="whitespace">' . (($column->pwd != null || $column->pwd != '') ? $column->pwd : '-') . '</td>
							<td class="whitespace">' . (($column->name != null) ? $column->name : '-') . '</td>
							<td class="whitespace">' . $newDate . '</td>

							<td class="whitespace">
							 	<a href="' . base_url("view-document-file/" . $column->docs_id . "/1") . '" target="_blank"><i class="fa fa-eye" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a>
                                ' . $deleteDocs . '
								<a href="' . base_url("download-document-file/" . $column->docs_id . "/1") . '" download><i class="fa fa-download" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a>
							</td>
						</tr>';
            }
            // 	<a onclick="editCustomerDocs('.$column->docs_id.')"><i class="fa fa-pencil" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a>
            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan="9" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        echo json_encode($data);
    }

    public function deleteCustomerDocsById($docs_id) {
        $docs_row = $this->db->select("*")->from("docs")->where("docs_id", $docs_id)->get()->row();
        $lead_id = $docs_row->lead_id;
        if (!empty($docs_id)) {
            $query = $this->db->where("docs_id", $docs_id)->delete('docs');
            $response = ['result' => $query, "lead_id" => $lead_id];
            echo json_encode($response);
        }
    }

    public function viewCustomerDocs($docs_id) {
        if (!empty($docs_id)) {
            $query = $this->db->where("docs_id", $docs_id)->get('docs')->row_array();
            $img = $query['file'];
            $match_http = substr($img, 0, 4);
            if ($match_http == "http") {
                echo json_encode($img);
            } else {
                if (!empty($img)) {
                    echo json_encode(base_url() . 'upload/' . $img);
                } else {
                    echo json_encode(base_url() . 'public/images/avtar-image.jpg');
                }
            }
        }
    }

    public function viewCustomerDocsById($docs_id) {
        if (!empty($docs_id)) {
            $query = $this->db->select('*')->where("docs_id", $docs_id)->get('docs')->row_array();
            echo json_encode($query);
        }
    }

    public function downloadCustomerdocs($docs_id) {
        if (!empty($docs_id)) {
            $query = $this->db->where("docs_id", $docs_id)->get('docs')->row_array();
            $img = $query['file'];
            $match_http = substr($img, 0, 4);
            if ($match_http == "http") {
                // echo json_encode($img);
                force_download($img, live . $img);
            } else {
                if (server == "localhost") {
                    force_download($img, base_url() . localhost . $img);
                } else {
                    force_download($img, live . $img);
                }
            }
        }
    }

    public function notification($mobile, $msg) {
        $username = username;
        $password = password;
        $type = 0;
        $dlr = 1;
        $destination = 8936962573;
        $source = "LWALLE";
        $message = urlencode($msg);
        $entityid = entityid;
        $tempid = 1207161976542817007;

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

    //     public function saveCustomerDocs() {
    // //        error_reporting(E_ALL);
    // //        ini_set('display_errors', 1);
    //         if ($this->input->post('user_id') == '') {
    //             $json['errSession'] = "Session Expired";
    //             echo json_encode($json);
    //         }
    //         $lead_id = $this->input->post('lead_id');
    //         $customer_id = $this->input->post('customer_id');
    //         $user_id = $this->input->post('user_id');
    //         $company_id = $this->input->post('company_id');
    //         $product_id = $this->input->post('product_id');
    //         $docs_id = $this->input->post('docs_id');
    //         $docs_type = $this->input->post('docuemnt_type');
    //         $sub_docs_type = $this->input->post('document_name');
    //         $password = $this->input->post('password');
    //         if (!empty($lead_id) && !empty($customer_id)) {
    //             if (isset($_FILES['file_name']['name'])) {
    //                 $config['upload_path'] = 'upload/';
    //                 $config['allowed_types'] = 'pdf|jpg|png|jpeg';
    //                 $this->upload->initialize($config);
    //                 if (!$this->upload->do_upload('file_name')) {
    //                     $json['err'] = $this->upload->display_errors();
    //                     echo json_encode($json);
    //                 } else {
    //                     $data = array('upload_data' => $this->upload->data());
    //                     $image = $data['upload_data']['file_name'];
    //                     if (empty($docs_id)) {
    //                         $fetch = 'C.pancard, C.mobile';
    //                         $join2 = "LD.customer_id = C.customer_id";
    //                         $getLeads = $this->Tasks->join_two_table($fetch, $this->tbl_customer, $this->tbl_leads, $join2);
    //                         $lead = $getLeads->row();
    //                         $data = array(
    //                             'lead_id' => $lead_id,
    //                             'company_id' => $company_id,
    //                             'customer_id' => $customer_id,
    //                             'pancard' => $lead->pancard,
    //                             'mobile' => $lead->mobile,
    //                             'docs_type' => $docs_type,
    //                             'sub_docs_type' => $sub_docs_type,
    //                             'file' => $image,
    //                             'pwd' => $password,
    //                             'ip' => ip,
    //                             'upload_by' => $user_id,
    //                             'created_on' => date("Y-m-d H:i:s")
    //                         );
    //                         $result = $this->Tasks->insert($data, 'docs');
    //                         $json['msg'] = 'Docs saved successfully.';
    //                         echo json_encode($json);
    //                     } else {
    //                         // $data = array (
    //                         //     'pwd'           => $password,
    //                         //     'docs_type'     => $docs_type,
    //                         //     'sub_docs_type' => $sub_docs_type,
    //                         //     'file'          => $image,
    //                         //     'ip'            => ip,
    //                         //     'upload_by'     => 1,
    //                         //     'created_on'    => date("Y-m-d H:i:s")
    //                         // );
    //                         //       $where = ['company_id' => $company_id];
    //                         // $this->db->where($where)->where('lead_id', $lead_id)->where('docs_id', $docs_id)->update('docs', $data);
    //                         $json['msg'] = 'Docs updated successfully.';
    //                         echo json_encode($json);
    //                     }
    //                 }
    //             } else {
    //                 $json['err'] = "Failed to save Docs. Try Again.";
    //                 echo json_encode($json);
    //             }
    //         } else {
    //             $json['err'] = "Failed to save Docs. Try Again.";
    //             echo json_encode($json);
    //         }
    //     }

    public function saveCustomerDocs() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        $lead_id = $this->input->post('lead_id');
        $lead_id =  $this->encrypt->decode($lead_id);
        $user_id = intval($this->input->post('user_id'));
        $company_id = intval($this->input->post('company_id'));
        $product_id = intval($this->input->post('product_id'));
        $docs_id = intval($this->input->post('docs_id'));
        $sub_docs_type_id = strval($this->input->post('document_name'));

        $tmpDocsDetails = $this->Docs->getDocumentMasterById($sub_docs_type_id);

        $documentMasterDetails = $tmpDocsDetails->row_array();

        $docs_type = $documentMasterDetails['docs_type'];
        $sub_docs_type = $documentMasterDetails['docs_sub_type'];

        $password = $this->input->post('password');

        if (!empty($lead_id)) {
            if (!empty($_FILES['file_name']['name'])) {
                $doc_upload_flag = false;

                if (LMS_DOC_S3_FLAG) {

                    $upload_return = uploadDocument($_FILES, $lead_id);

                    if ($upload_return['status'] == 1) {
                        $upload_file_name = $upload_return['file_name'];

                        $doc_upload_flag = true;
                    } else {
                        $json['err'] = "Document save failed. Please try again.";
                        echo json_encode($json);
                        exit;
                    }
                } else {
                    $file_name = $_FILES["file_name"]['name'];
                    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $extension = strtolower($extension);
                    $new_name = $lead_id . '_' . $sub_docs_type_id . '_lms_' . date('YmdHis') . '.' . $extension;

                    //                $filesize = $_FILES['file_name']['size'];
                    //
                    //                $file_size_in_mb = round(($filesize / 1024 / 1024), 0);
                    //
                    //                if ($file_size_in_mb > 2) {
                    //
                    //                    $json['err'] = "File size is more than $file_size_in_mb MB";
                    //                    echo json_encode($json);
                    //                    return;
                    //                }
                    $config['file_name'] = $new_name;
                    $config['upload_path'] = 'upload/';
                    $config['allowed_types'] = 'pdf|jpg|png|jpeg';
                    $config['file_ext_tolower'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('file_name')) {
                        $json['err'] = $this->upload->display_errors();
                        echo json_encode($json);
                        exit;
                    } else {

                        $data = array('upload_data' => $this->upload->data());
                        $upload_file_name = $data['upload_data']['file_name'];
                        $doc_upload_flag = true;
                    }
                }

                if ($doc_upload_flag == true) {
                    $fetch = 'LD.application_no, C.pancard, C.mobile';
                    $join2 = "LD.lead_id = C.customer_lead_id";
                    $conditions = ['LD.lead_id' => $lead_id];

                    $getLeads = $this->Tasks->join_two_table_with_where($conditions, $fetch, $this->tbl_customer, $this->tbl_leads, $join2);
                    $lead = $getLeads->row();

                    if (empty($lead->pancard)) {
                        $json['err'] = "Failed to save docs due to Pancard.";
                        echo json_encode($json);
                        exit;
                    }

                    $data = array(
                        'lead_id' => $lead_id,
                        'application_no' => $lead->application_no,
                        'company_id' => $company_id,
                        'pancard' => $lead->pancard,
                        'mobile' => $lead->mobile,
                        'docs_type' => $docs_type,
                        'sub_docs_type' => $sub_docs_type,
                        'file' => $upload_file_name,
                        'pwd' => $password,
                        'ip' => ip,
                        'upload_by' => $user_id,
                        'created_on' => date("Y-m-d H:i:s"),
                        'docs_master_id' => $sub_docs_type_id
                    );
                    $result = $this->Tasks->insert($data, 'docs');
                    $json['msg'] = 'Docs saved successfully.';
                    echo json_encode($json);
                } else {
                    $json['err'] = "Failed to save Docs. Try Again...";
                    echo json_encode($json);
                }
            } else {
                $json['err'] = "Failed to save Docs. Try Again.";
                echo json_encode($json);
            }
        } else {
            $json['err'] = "Failed to save Docs. Try Again.";
            echo json_encode($json);
        }
    }

    public function allocateLeads() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['err'] = "Session Expired";
            echo json_encode($json);
        } else {

            if (!empty($_POST["checkList"])) {

                foreach ($_POST["checkList"] as $lead_id) {

                    $empDetails = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, lead_status_id', 'leads');
                    $empDetails = $empDetails->row();

                    $label = $_SESSION['isUserSession']['labels'];
                    $login_user_name = $_SESSION['isUserSession']['name'];
                    $lead_remark = "Lead allocate by self - " . $login_user_name;
                    if ($label == 'CR1' || $label == 'CA' || $label == 'SA') {

                        if (!in_array($empDetails->lead_status_id, [41, 42, 1])) {
                            continue;
                        }

                        $status = "LEAD-INPROCESS";
                        $status_id = 2;
                        $stage = "S2";

                        $assign_user_id = 'lead_screener_assign_user_id';
                        $assign_datetime = 'lead_screener_assign_datetime';
                    } else if ($label == 'CR2' || $label == 'CA' || $label == 'SA') {

                        if ($empDetails->lead_status_id != 4) {
                            continue;
                        }

                        $status = "APPLICATION-INPROCESS";
                        $stage = "S5";
                        $status_id = 5;

                        $assign_user_id = 'lead_credit_assign_user_id';
                        $assign_datetime = 'lead_credit_assign_datetime';
                    } else if ($label == 'DS1' || $label == 'DS2' || $label == 'CA' || $label == 'SA') {

                        if ($empDetails->lead_status_id != 25) {
                            continue;
                        }

                        $status = "DISBURSAL-INPROCESS";
                        $stage = "S21";
                        $status_id = 30;

                        $assign_user_id = 'lead_disbursal_assign_user_id';
                        $assign_datetime = 'lead_disbursal_assign_datetime';
                    } else {
                        continue;
                    }

                    $conditions = ['lead_id' => $lead_id];

                    $lead_details = $this->Tasks->select($conditions, "lead_id, $assign_user_id, user_type, lead_screener_assign_user_id, lead_data_source_id", 'leads');

                    if ($lead_details->num_rows() > 0) {


                        $lead_details = $lead_details->row_array();

                        $lead_data_source_id = $lead_details['lead_data_source_id'];

                        if (!empty($lead_details['lead_id'])) {
                            if (!empty($lead_details[$assign_user_id])) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }

                    $update_lead_data = [
                        'status' => $status,
                        'lead_status_id' => $status_id,
                        'stage' => $stage,
                        $assign_user_id => $_SESSION['isUserSession']['user_id'],
                        $assign_datetime => date('Y-m-d H:i:s'),
                        'updated_on' => date('Y-m-d H:i:s')
                    ];

                    if (empty($lead_details["lead_screener_assign_user_id"])) {
                        $update_lead_data['lead_screener_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
                        $update_lead_data['lead_screener_assign_datetime'] = date('Y-m-d H:i:s');
                    }

                    $insert_lead_followup = [
                        'lead_id' => $lead_id,
                        'user_id' => $_SESSION['isUserSession']['user_id'],
                        'status' => $status,
                        'stage' => $stage,
                        'created_on' => date("Y-m-d H:i:s"),
                        'lead_followup_status_id' => $status_id,
                        'remarks' => $lead_remark
                    ];

                    $conditions = ['lead_id' => $lead_id];

                    if ($_SESSION['isUserSession']['user_id'] == 82 && ($lead_details['user_type'] == "REPEAT" || $lead_details['user_type'] == "UNPAID-REPEAT")) {
                        continue;
                    }

                    $this->Tasks->updateLeads($conditions, $update_lead_data, $this->tbl_leads);

                    if ($label == 'CR1' || $label == 'CA' || $label == 'SA') {
                        if ($label == 'CR1' && ENVIRONMENT == 'production') {
                            $this->load->helper('integration/payday_runo_call_api');
                            $method_name = 'LEAD_CAT_SANCTION';
                            payday_call_management_api_call($method_name, $lead_id);
                        }
                    }

                    if ($label == 'DS1') {

                        $dataLoan = [
                            "status" => $status,
                            "loan_status_id" => $status_id,
                        ];

                        $conditions = ['lead_id' => $lead_id];

                        $this->Tasks->updateLeads($conditions, $dataLoan, 'loan');
                    }


                    $this->Tasks->insert($insert_lead_followup, 'lead_followup');
                }
                echo "true";
            } else {
                $json['err'] = "Please select at least one record";
                echo json_encode($json);
            }
        }
    }

    public function rejectedLeadMoveToProcess() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $data['err'] = "Session Expired";
            echo json_encode($data);
        } else {
            $lead_id = $this->input->post('lead_id');

            if (!empty($lead_id)) {

                $leadDetails = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, lead_status_id, lead_rejected_reason_id, lead_rejected_assign_user_id', 'leads');

                if ($leadDetails->num_rows() > 0) {

                    $leadDetails = $leadDetails->row();

                    if ($leadDetails->lead_rejected_assign_user_id == user_id && $leadDetails->lead_status_id == 9 && in_array($leadDetails->lead_rejected_reason_id, array(7, 31))) {

                        $status = "LEAD-INPROCESS";
                        $stage = "S2";
                        $lead_status_id = 2;

                        $data = [
                            'lead_status_id' => $lead_status_id,
                            'status' => $status,
                            'stage' => $stage,
                            'lead_screener_assign_user_id' => user_id,
                            'lead_screener_assign_datetime' => date("Y-m-d H:i:s"),
                            'lead_screener_recommend_datetime' => date("Y-m-d H:i:s"),
                            'lead_credit_assign_user_id' => NULL,
                            'lead_credit_assign_datetime' => NULL,
                            'lead_credit_recommend_datetime' => NULL,
                            'scheduled_date' => NULL,
                            'lead_rejected_reason_id' => NULL,
                            'lead_rejected_user_id' => NULL,
                            'lead_rejected_datetime' => NULL,
                            'lead_stp_flag' => NULL,
                            'lead_rejected_assign_user_id' => NULL,
                            'lead_rejected_assign_datetime' => NULL
                        ];

                        $this->db->where('lead_id', $lead_id);

                        $return_update_flag = $this->db->update('leads', $data);

                        if ($return_update_flag) {

                            $insert_lead_followup = [
                                'lead_id' => $lead_id,
                                'user_id' => user_id,
                                'status' => $status,
                                'stage' => $stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $lead_status_id,
                                'remarks' => "Rejected Lead Move to In-Process"
                            ];

                            $this->Tasks->insert($insert_lead_followup, 'lead_followup');

                            $this->session->set_flashdata('success', "Reporting has been updated successfully.");
                            $data['msg'] = 1;
                            echo json_encode($data);
                        }
                    } else {
                        $data['err'] = "Lead is not in current stage to process.";
                        echo json_encode($data);
                    }
                } else {
                    $data['err'] = "Missing Lead Details";
                    echo json_encode($data);
                }
            } else {
                $data['err'] = "Missing Lead ID";
                echo json_encode($data);
            }
        }
    }

    public function reallocate() {

        echo "<pre>";
        print_r($_POST);
        exit;
    }

    public function initiateFiCPV() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->input->post('lead_id');
                $customer_id = $this->input->post('customer_id');
                $visit_type = $this->input->post('visit_type');
                $is_visit = $this->input->post('is_visit');

                $conditions = ['lead_id' => $lead_id];
                $scm_user_id = 0;
                $residence_status_id = 0;
                if ($is_visit == "YES") {
                    $select3 = 'state_id';
                    $sql3 = $this->Tasks->select($conditions, $select3, 'leads');
                    $customer_state = $sql3->row();

                    //                        $conditions2 = ['role_id' => 8]; // role = CO2 for collection
                    //                        $select2 = 'user_id, name, branch';
                    //                        $sql2 = $this->Tasks->select($conditions2, $select2, 'users');
                    $where = array();
                    $where['UR.user_role_type_id'] = 8; // sttate collection Manager
                    $where['UR.user_role_active'] = 1;
                    $where['UR.user_role_deleted'] = 0;
                    $where['URL.user_rl_location_type_id'] = 2; // state
                    $where['URL.user_rl_active'] = 1;
                    $where['URL.user_rl_deleted'] = 0;

                    $this->db->select('UR.user_role_user_id as user_id, URL.user_rl_location_id');
                    $this->db->from('user_roles UR');
                    $this->db->where($where);
                    $this->db->join('user_role_locations URL', 'URL.user_rl_role_id = UR.user_role_id', 'INNER');
                    $sql2 = $this->db->get();

                    if (!empty($sql2->num_rows())) {
                        $scmUser = $sql2->result();
                        foreach ($scmUser as $user_role) {
                            if (in_array($customer_state->state_id, [$user_role->user_rl_location_id])) {
                                $scm_user_id = $user_role->user_id;
                                break;
                            }
                        }
                        $residence_status_id = 1;
                    }
                }

                if (empty($scm_user_id)) {
                    $json['err'] = "No any SCM is mapped with lead states. Please contact to admin and allocate more states for SCM";
                    echo json_encode($json);
                } else {
                    $verification_data = array();
                    $lead_columns_arr = array();

                    $verification_data['lead_id'] = $lead_id;
                    $verification_data['company_id'] = company_id;
                    $verification_data['product_id'] = product_id;

                    if ($visit_type == 1) { // residenceCPV
                        $lead_columns_arr["lead_fi_scm_residence_assign_user_id"] = $scm_user_id;
                        $lead_columns_arr["lead_fi_residence_status_id"] = $residence_status_id;

                        $verification_data["init_residence_cpv"] = $is_visit;
                        $verification_data["office_residence_status"] = 1;
                        $verification_data["residence_initiated_on"] = date('Y-m-d H:i:s');
                    } else if ($visit_type == 2) { // officeCPV
                        $lead_columns_arr["lead_fi_scm_office_assign_user_id"] = $scm_user_id;
                        $lead_columns_arr["lead_fi_office_status_id"] = $residence_status_id;

                        $verification_data["init_office_cpv"] = $is_visit;
                        $verification_data["office_report_status"] = 1;
                        $verification_data["office_initiated_on"] = date('Y-m-d H:i:s');
                    }

                    $select = 'verify_id, lead_id';
                    $this->Tasks->globalUpdate($conditions, $lead_columns_arr, 'leads');

                    $sql = $this->Tasks->select($conditions, $select, 'tbl_verification');
                    if ($sql->num_rows() > 0) {
                        $verification = $sql->row();
                        $conditions2 = ['verify_id' => $verification->verify_id];
                        $this->Tasks->globalUpdate($conditions, $verification_data, 'tbl_verification');

                        $json['msg'] = "Visit Requested Successfully.";
                        echo json_encode($json);
                    } else {
                        $result = $this->Tasks->insert($verification_data, 'tbl_verification');
                        $json['msg'] = "Visit Requested Successfully.";
                        echo json_encode($json);
                    }
                }
            }
        }
    }

    public function resonForDuplicateLeads() {
        if (isset($_POST["checkList"])) {
            $login_user_name = $_SESSION['isUserSession']['name'];
            foreach ($_POST["checkList"] as $item) {
                $lead_id = $item;
                $lead_status_id = 7;
                $conditions = ['lead_id' => $lead_id];
                $data = [
                    'lead_rejected_reason_id' => 1,
                    'lead_rejected_user_id' => user_id,
                    'lead_rejected_datetime' => date("Y-m-d H:i:s"),
                    'lead_status_id' => $lead_status_id,
                    'status' => 'DUPLICATE',
                    'stage' => 'S7'
                ];
                $this->Tasks->update($conditions, $data);

                $lead_remark = "Duplicate Lead marked by " . $login_user_name;

                $lead_followup_arr = [
                    'lead_id' => $lead_id,
                    'user_id' => user_id,
                    'status' => 'DUPLICATE',
                    'stage' => 'S7',
                    'created_on' => date("Y-m-d H:i:s"),
                    'lead_followup_status_id' => $lead_status_id,
                    'remarks' => $lead_remark
                ];

                $this->Tasks->insert($lead_followup_arr, 'lead_followup');
            }
            echo "true";
        } else {
            echo "false";
        }
    }

    public function duplicateTaskList() {
        $taskLists = $this->Tasks->duplicateTask();
        $data['taskCount'] = $taskLists->num_rows();
        $data['listTask'] = $taskLists->result();

        $this->load->view('Tasks/DuplicateTaskList', $data);
    }

    public function duplicateLeadDetails($lead_id) {
        $taskLists = $this->Tasks->duplicateTaskList($lead_id);
        echo json_encode($taskLists);
    }

    public function saveHoldleads($lead_id) {
        $lead_id = $this->encrypt->decode($lead_id);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $status = $this->input->post('status');
            $stage = $this->input->post('stage');
            $hold_date = $this->input->post('hold_date');
            $hold_remark = $this->input->post('hold_remark');

            if (empty($_SESSION['isUserSession']['user_id'])) {
                $json['err'] = "Session Expired";
                echo json_encode($json);
            } else if (empty($lead_id)) {
                $json['err'] = "Lead id not found.";
                echo json_encode($json);
            } else if (empty($hold_date)) {
                $json['err'] = "Lead Hold date is missing";
                echo json_encode($json);
            } else {

                $empDetails = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, lead_status_id, lead_disbursal_assign_user_id, ', 'leads');
                $empDetails = $empDetails->row();

                if (in_array($empDetails->lead_status_id, [14, 16, 17, 18, 19])) {
                    $json['err'] = "You are not authorized to take this action.";
                    echo json_encode($json);
                    return false;
                }

                if (agent == 'CR1') {
                    if ($empDetails->lead_status_id != 2) {
                        $json['err'] = "You are not authorized to take this action.";
                        echo json_encode($json);
                        return false;
                    }

                    $status = "LEAD-HOLD";
                    $stage = "S3";
                    $lead_status_id = 3;
                } else if (agent == 'CR2') {
                    if ($empDetails->lead_status_id != 5) {
                        $json['err'] = "You are not authorized to take this action.";
                        echo json_encode($json);
                        return false;
                    }

                    $status = "APPLICATION-HOLD";
                    $stage = "S6";
                    $lead_status_id = 6;
                } else if (agent == 'DS1' || agent == 'DS2') {
                    if (!in_array($empDetails->lead_status_id, [30, 13]) && $empDetails->lead_disbursal_assign_user_id != user_id) {
                        $json['err'] = "You are not authorized to take this action.";
                        echo json_encode($json);
                        exit;
                    }

                    $status = "DISBURSAL-HOLD";
                    $stage = "S22";
                    $lead_status_id = 35;
                }

                $data1 = [
                    'status' => $status,
                    'stage' => $stage,
                    'lead_status_id' => $lead_status_id,
                    'scheduled_date' => date('Y-m-d H:i:s', strtotime($hold_date)),
                ];

                $data2 = [
                    'lead_id' => $lead_id,
                    'customer_id' => $this->input->post('customer_id'),
                    'user_id' => $_SESSION['isUserSession']['user_id'],
                    'status' => $status,
                    'stage' => $stage,
                    'lead_followup_status_id' => $lead_status_id,
                    'remarks' => $hold_remark . "<br>scheduled date : " . $hold_date,
                    'created_on' => date("Y-m-d H:i:s"),
                ];

                $conditions = ['lead_id' => $lead_id];
                $this->Tasks->updateLeads($conditions, $data1, 'leads');

                if (agent == 'DS1' && $empDetails->lead_disbursal_assign_user_id != user_id) {

                    $dataLoan = [
                        "status" => $status,
                        "loan_status_id" => $lead_status_id,
                    ];

                    $conditions = ['lead_id' => $lead_id];

                    $this->Tasks->updateLeads($conditions, $dataLoan, 'loan');
                }

                $this->Tasks->insert($data2, 'lead_followup');
                $data['msg'] = 'Application Hold Successfuly.';
                echo json_encode($data);
            }
        } else {
            $json['err'] = "Invalid access.";
            echo json_encode($json);
        }
    }

    public function sanctionleads() {


        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
        $user_labels = !empty($_SESSION['isUserSession']['labels']) ? $_SESSION['isUserSession']['labels'] : "";
        $cam_blacklist_removed_flag = 0;
        $allow_sanction_head = array(65, 2, 3, 116, 45, 166,180, 505, 506, 182, 189, 191, 205, 207);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $lead_id = $this->input->post('lead_id');
            $lead_id =  $this->encrypt->decode($lead_id);

            $remarks = strip_tags($this->input->post('remarks'));

            if (empty($remarks)) {
                $json['err'] = "Remarks is required.";
                echo json_encode($json);
                exit;
            }
            $sql = "SELECT DISTINCT LD.lead_id, LD.lead_data_source_id, LD.lead_status_id, LD.lead_screener_assign_user_id, LD.lead_branch_id, LD.user_type, C.pancard";
            $sql .= " ,C.first_name,C.middle_name,sur_name,C.gender,C.pancard_ocr_verified_status";
            $sql .= " ,C.email_verified_status, C.customer_digital_ekyc_flag, CAM.cam_appraised_monthly_income";
            $sql .= " ,CAM.cam_status, CAM.eligible_loan, CAM.loan_recommended, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee as total_pf_with_gst, CAM.adminFeeWithGST as calculated_gst, CAM.total_admin_fee as net_pf_without_gst";
            $sql .= " ,CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.repayment_amount, CAM.net_disbursal_amount, CAM.cam_advance_interest_amount";
            $sql .= " ,CAM.cam_processing_fee_gst_type_id, C.customer_bre_run_flag";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C ON(LD.lead_id=C.customer_lead_id)";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id)";
            $sql .= " INNER JOIN customer_employment CE ON(LD.lead_id=CE.lead_id)";
            $sql .= " WHERE LD.lead_id=" . $lead_id;

            $sql2 = $this->db->query($sql);
            $cam = $sql2->row();


            $approval_loan_amount = ($cam->cam_appraised_monthly_income * 0.6); //60% of monthly income;
            $approval_loan_roi = $cam->roi;
            $lead_data_source_id = $cam->lead_data_source_id;


            if (empty($user_id)) {
                $json['err'] = "Session Expired";
                echo json_encode($json);
            } else if (($sql2->num_rows() == 0)) {
                $json['err'] = "CAM details not found.";
                echo json_encode($json);
            } else if (!in_array($cam->lead_status_id, [5, 6, 10, 47, 11])) {
                $json['err'] = "Unautherized access.";
                echo json_encode($json);
                return false;
            } else if (($cam->cam_status == 0)) {
                $json['err'] = "Something found wrong in CAM, Please re-check";
                echo json_encode($json);
            } else if (empty($cam->eligible_loan)) {
                $json['err'] = "Eligible loan amount cannot be empty.";
                echo json_encode($json);
            } else if ($cam->email_verified_status != "YES" && false) {
                $json['err'] = "Personal email id is not varified";
                echo json_encode($json);
                //} else if ($cam->customer_bre_run_flag != 1) {
                //$json['err'] = "Please run the BRE.";
                //echo json_encode($json);
            } else if (ENVIRONMENT == 'production' && !in_array($cam->pancard_ocr_verified_status, array(1, 2))) {
                $json['err'] = "Customer e-kyc not verified.";
                echo json_encode($json);
            } else if ($cam->loan_recommended >= 40000 && !in_array($user_id, $allow_sanction_head)) {
                $json['err'] = "Loan Recommended is more than 40K, Please recommend this case to sanction head only.";
                echo json_encode($json);
            } else if (!empty($approval_loan_amount) && $cam->loan_recommended > $approval_loan_amount && !in_array($user_id, $allow_sanction_head)) {
                $json['err'] = "Loan Recommended is more than 60% of customer income. Please recommend this case to sanction head only.";
                echo json_encode($json);
            } else if ($cam->tenure < 7 && !in_array($user_id, $allow_sanction_head)) {
                $json['err'] = "Loan Tenure is less than 7 days. Please recommend this case to sanction head only.";
                echo json_encode($json);
            } else if (($cam->tenure < 7 || $cam->tenure > 370)) {
                $json['err'] = "Loan Tenure cannot be less than 7 days or greater than 370 days.";
                echo json_encode($json);
            } else if ($cam->loan_recommended > 1000000) {
                $json['err'] = "Loan Recommended is more than 10 lac, We does not allowed this loan amount.";
                echo json_encode($json);
            } else if ($cam->loan_recommended > 115000 && !in_array($user_id, array(210))) {
                $json['err'] = "Loan Recommended is more than Rs. 1,15,000, We does not allowed this loan amount.";
                echo json_encode($json);
            } else if (empty($approval_loan_roi)) {
                $json['err'] = "Loan Recommened ROI cannot be empty";
            } else if ($approval_loan_roi > 2) {
                $json['err'] = "Loan Recommened ROI is higher then 2%";
                echo json_encode($json);
            } else {



                /*$breRuleResult = $this->Tasks->select(['lbrr_lead_id' => $lead_id, 'lbrr_active' => 1], 'lbrr_id,lbrr_rule_manual_decision_id', 'lead_bre_rule_result');

                if ($breRuleResult->num_rows() <= 0 && false) {
                    $json['err'] = "Please run bre to process the case.";
                    echo json_encode($json);
                    return false;
                }

                $breRuleResultArray = $breRuleResult->result_array();

                foreach ($breRuleResultArray as $breResultData) {

                    if ($breResultData['lbrr_rule_manual_decision_id'] == 2) {
                        $json['err'] = "Please take the decision for refer rule.";
                        echo json_encode($json);
                        return;
                    }

                    if ($breResultData['lbrr_rule_manual_decision_id'] == 3) {
                        $json['err'] = "This case cannot move forward as policy is rejected";
                        echo json_encode($json);
                        return;
                    }
                }*/
                


                $isAnotherLeadInprocess = $this->Tasks->isAnotherLeadInprocess($lead_id);
                

                if ($isAnotherLeadInprocess->num_rows() > 0) {
                    $another_lead = $isAnotherLeadInprocess->row();
                    $json['err'] = 'Already one application ' . $another_lead->lead_id . ' of same customer ' . $another_lead->first_name . ' with status - ' . $another_lead->status . ' is In process.[Error-S01]';
                    echo json_encode($json);
                    return false;
                }

                $isBlackListed = $this->Tasks->checkBlackListedCustomer($lead_id);

                if ($isBlackListed['status'] == 1) {
                    $json['err'] = $isBlackListed['error_msg'];
                    echo json_encode($json);
                    return false;
                }

                // $cityStateSourcing = $this->Tasks->checkCityStateSourcing($lead_id);

                // if ($cityStateSourcing['status'] != 1) {
                //     $json['err'] = $cityStateSourcing['error_msg'];
                //     echo json_encode($json);
                //     return false;
                // }

                $bankingDataReturnArr = $this->Tasks->getCustomerAccountDetails($lead_id);


                if ($bankingDataReturnArr['status'] === 1) {

                    $bankingDetails = $bankingDataReturnArr['banking_data'];

                    if (empty($bankingDetails)) {

                        $json['err'] = 'Customer banking details not found.';
                        echo json_encode($json);
                        return false;
                    } else {
                        $beneName = !empty($bankingDetails["beneficiary_name"]) ? $bankingDetails["beneficiary_name"] : "";
                        $beneAccNo = !empty($bankingDetails["account"]) ? $bankingDetails["account"] : "";
                        $beneIFSC = !empty($bankingDetails["ifsc_code"]) ? $bankingDetails["ifsc_code"] : "";
                    }
                } else {
                    $json['err'] = 'Please verify the customer banking details.';
                    echo json_encode($json);
                    return false;
                }

                $customerReference = $this->Tasks->getCustomerReferenceDetails($lead_id);

                

                if (count($customerReference['customer_reference']) < 2) {
                    $json['err'] = "Please add customer reference - " . count($customerReference['customer_reference']);
                    echo json_encode($json);
                    return false;
                }

                $queryCustomerPersonal = $this->Tasks->customerPersonalDetails(['LD.lead_id' => $lead_id]);

                

                $personalAndEmployment = $queryCustomerPersonal->row_array();

                

                $customer_data = [
                    'cif_first_name' => !empty($personalAndEmployment['first_name']) ? $personalAndEmployment['first_name'] : "",
                    'cif_middle_name' => !empty($personalAndEmployment['middle_name']) ? $personalAndEmployment['middle_name'] : "",
                    'cif_sur_name' => !empty($personalAndEmployment['sur_name']) ? $personalAndEmployment['sur_name'] : "",
                    'cif_gender' => ((strtoupper($personalAndEmployment['gender']) == 'MALE') ? 1 : 2),
                    'cif_dob' => !empty($personalAndEmployment['dob']) ? $personalAndEmployment['dob'] : "",
                    'cif_personal_email' => $personalAndEmployment['email'],
                    'cif_office_email' => $personalAndEmployment['alternate_email'],
                    'cif_mobile' => $personalAndEmployment['mobile'],
                    'cif_alternate_mobile' => $personalAndEmployment['alternate_mobile'],
                    'cif_residence_address_1' => $personalAndEmployment['current_house'],
                    'cif_residence_address_2' => $personalAndEmployment['current_locality'],
                    'cif_residence_landmark' => $personalAndEmployment['current_landmark'],
                    'cif_residence_city_id' => ($personalAndEmployment['res_city_id']) ? $personalAndEmployment['res_city_id'] : 0,
                    'cif_residence_state_id' => ($personalAndEmployment['res_state_id']) ? $personalAndEmployment['res_state_id'] : 0,
                    'cif_residence_pincode' => $personalAndEmployment['cr_residence_pincode'],
                    'cif_residence_since' => $personalAndEmployment['current_residence_since'],
                    'cif_residence_type' => $personalAndEmployment['current_residence_type'],
                    'cif_residence_residing_with_family' => $personalAndEmployment['current_residing_withfamily'],
                    'cif_aadhaar_no' => $personalAndEmployment['aadhar_no'],
                    'cif_office_address_1' => $personalAndEmployment['emp_house'],
                    'cif_office_address_2' => $personalAndEmployment['emp_street'],
                    'cif_office_address_landmark' => $personalAndEmployment['emp_landmark'],
                    'cif_office_city_id' => ($personalAndEmployment['office_city_id']) ? $personalAndEmployment['office_city_id'] : 0,
                    'cif_office_state_id' => ($personalAndEmployment['office_state_id']) ? $personalAndEmployment['office_state_id'] : 0,
                    'cif_office_pincode' => $personalAndEmployment['emp_pincode'],
                    'cif_company_name' => $personalAndEmployment['employer_name'],
                    'cif_company_website' => $personalAndEmployment['emp_website'],
                    'cif_company_type_id' => $personalAndEmployment['emp_employer_type'],
                    'cif_aadhaar_same_as_residence' => ($personalAndEmployment['C.aa_same_as_current_address'] == "YES") ? 1 : 0,
                    'cif_aadhaar_address_1' => $personalAndEmployment['aa_current_house'],
                    'cif_aadhaar_address_2' => $personalAndEmployment['aa_current_locality'],
                    'cif_aadhaar_landmark' => $personalAndEmployment['aa_current_landmark'],
                    'cif_aadhaar_city_id' => $personalAndEmployment['aa_current_city_id'],
                    'cif_aadhaar_state_id' => $personalAndEmployment['aa_current_state_id'],
                    'cif_aadhaar_pincode' => $personalAndEmployment['aa_cr_residence_pincode'],
                    'cif_office_working_since' => $personalAndEmployment['emp_residence_since'],
                    'cif_office_designation' => $personalAndEmployment['emp_designation'],
                    'cif_office_department' => $personalAndEmployment['emp_department'],
                    'cif_income_type' => $personalAndEmployment['income_type'],
                    'cif_digital_ekyc_flag' => $personalAndEmployment['customer_digital_ekyc_flag'],
                    'cif_digital_ekyc_datetime' => $personalAndEmployment['customer_digital_ekyc_done_on'],
                    'cif_pancard_verified' => $personalAndEmployment['pancard_verified_status'],
                    'cif_pancard_verified_on' => $personalAndEmployment['pancard_verified_on']
                ];

                


                // $query_cif = $this->db->select('cif_id, cif_number, cif_pancard, cif_mobile')->where('cif_pancard', $cam->pancard)->from('cif_customer')->get();
                $query_cif = $this->db->select('cif_mobile')->where('cif_mobile', $cam->mobile)->from('cif_customer')->get();
                

                if ($query_cif->num_rows() > 0) {

                    $cif = $query_cif->row_array();

                    $customer_id = $cif['cif_number'];

                    $cif_id = $cif['cif_id'];

                    $customer_data['cif_updated_by'] = $user_id;
                    $customer_data['cif_updated_on'] = date("Y-m-d H:i:s");
                    //  $cif_flag = $this->Tasks->globalUpdate(['cif_id' => $cif_id], $customer_data, 'cif_customer');
                } else {

                    $last_row = $this->db->select('cif_id as customer_id')->from('cif_customer')->order_by('cif_id', 'desc')->limit(1)->get()->row();
                    $str = preg_replace('/\D/', '', $last_row->customer_id);
                    $customer_id = "FTC" . str_pad(($str + 1), 8, "0", STR_PAD_LEFT); // FTC00000004

                    $customer_data['cif_pancard'] = trim($cam->pancard);
                    $customer_data['cif_number'] = $customer_id;
                    $customer_data['cif_created_by'] = $user_id;
                    $customer_data['cif_created_on'] = date("Y-m-d H:i:s");
                    $cif_flag = $this->db->insert('cif_customer', $customer_data);
                }

                // if (empty($cif_flag)) {
                //     $json['err'] = 'CIF is unable to create. Please check with IT Team.';
                //     echo json_encode($json);
                //     return false;
                // }

                //if customer is blacklisted before and removed from the list then we need to tag the same
                //$isBlackListedRemoved = $this->Tasks->checkBlackListedCustomer($lead_id, 1);

                // print_r($lead_id); die;

                // if ($isBlackListedRemoved['status'] == 1) {
                //     $cam_blacklist_removed_flag = 1;
                // }

                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['customer_id' => $customer_id], 'leads');
                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['customer_id' => $customer_id], 'customer_employment');
                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['customer_id' => $customer_id], 'docs');
                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['customer_id' => $customer_id], 'customer_banking');
                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['customer_id' => $customer_id, 'cam_blacklist_removed_flag' => $cam_blacklist_removed_flag, 'cam_sanction_remarks' => addslashes($remarks)], 'credit_analysis_memo');

                $pdf_return = $this->Tasks->generateSanctionLetter($lead_id);
                // print_r($pdf_return);
                // exit;
                // if ($pdf_return['status'] == 0) {

                //     $json['err'] = $pdf_return['errors'];
                //     echo json_encode($json);
                //     return false;
                // }

                $status = "SANCTION";
                $stage = "S12";
                $lead_status_id = 12;

                $loan_no = $this->Tasks->generateLoanNo($lead_id);

                if (!empty($loan_no)) {

                    $loan_insert_array = [
                        'lead_id' => $lead_id,
                        'customer_id' => $customer_id,
                        'loan_no' => $loan_no,
                        'status' => $status,
                        'loan_status_id' => $lead_status_id,
                        'loanAgreementRequest' => 1,
                        'agrementRequestedDate' => date("Y-m-d H:i:s"),
                        'user_id' => $user_id,
                        'created_on' => date("Y-m-d H:i:s")
                    ];

                    $this->Tasks->insert($loan_insert_array, 'loan');

                    $loan_id = $this->db->insert_id();

                    if (!empty($loan_insert_array)) {

                        $data = [
                            'status' => $status,
                            'stage' => $stage,
                            'lead_status_id' => $lead_status_id,
                            'lead_credit_approve_user_id' => $user_id,
                            'lead_credit_approve_datetime' => date("Y-m-d H:i:s")
                        ];

                        if (agent == "CR3") {
                            $data['lead_credithead_assign_user_id'] = $user_id;
                            $data['lead_credithead_assign_datetime'] = date("Y-m-d H:i:s");
                        }

                        $conditions = ['lead_id' => $lead_id];

                        $return_val = $this->Tasks->updateLeads($conditions, $data, 'leads');

                        if ($return_val) {

                            $sanction_remark = $remarks;
                            $sanction_remark .= "<br/>Sanctioned";
                            if ($cam_blacklist_removed_flag == 1) {
                                $sanction_remark .= "<br>Blacklist Removed: YES";
                            }

                            $sanction_remark .= "<br>Eligible Loan Amt (Rs.): " . (!empty($cam->eligible_loan) ? $cam->eligible_loan : "");
                            $sanction_remark .= "<br>Approved Loan Amt (Rs.): " . (!empty($cam->loan_recommended) ? $cam->loan_recommended : "");
                            $sanction_remark .= "<br>Approved ROI (%): " . (!empty($cam->roi) ? round($cam->roi, 2) : "");
                            $sanction_remark .= "<br>Approved Tenure (Days): " . (!empty($cam->tenure) ? $cam->tenure : "");
                            $sanction_remark .= "<br>Approved Processing Fee: " . (!empty($cam->processing_fee_percent) ? round($cam->processing_fee_percent, 2) . "%" : "");
                            $sanction_remark .= "<br>18% GST is " . (($cam->cam_processing_fee_gst_type_id == 2) ? "Exclusive" : "Inclusive");
                            $sanction_remark .= "<br>Approved Total Admin Fee (Rs.): " . (!empty($cam->total_pf_with_gst) ? round($cam->total_pf_with_gst, 2) : "");
                            $sanction_remark .= "<br>Approved Admin Fee 18% GST (Rs.): " . (!empty($cam->calculated_gst) ? round($cam->calculated_gst, 2) : "");
                            $sanction_remark .= "<br>Approved Net Admin Fee (Rs.): " . (!empty($cam->net_pf_without_gst) ? round($cam->net_pf_without_gst, 2) : "");
                            $sanction_remark .= "<br>Disbursal Date : " . (!empty($cam->disbursal_date) ? date("d-m-Y", strtotime($cam->disbursal_date)) : "");
                            $sanction_remark .= "<br>Net Disbursal Amt (Rs.): " . (!empty($cam->net_disbursal_amount) ? $cam->net_disbursal_amount : "");
                            $sanction_remark .= "<br>Repayment Date : " . (!empty($cam->repayment_date) ? date("d-m-Y", strtotime($cam->repayment_date)) : "");
                            $sanction_remark .= "<br>Repayment Amt (Rs.): " . (!empty($cam->repayment_amount) ? $cam->repayment_amount : "");

                            $lead_followup_insert_array = [
                                'lead_id' => $lead_id,
                                'customer_id' => $customer_id,
                                'user_id' => $user_id,
                                'status' => $status,
                                'stage' => $stage,
                                'lead_followup_status_id' => $lead_status_id,
                                'remarks' => addslashes($sanction_remark),
                                'created_on' => date("Y-m-d H:i:s")
                            ];

                            $this->Tasks->insert($lead_followup_insert_array, 'lead_followup');

                            if ($cam->customer_digital_ekyc_flag == 2) {

                                $lead_followup_insert_array = [
                                    'lead_id' => $lead_id,
                                    'customer_id' => $customer_id,
                                    'user_id' => $user_id,
                                    'status' => $status,
                                    'stage' => $stage,
                                    'lead_followup_status_id' => $lead_status_id,
                                    'remarks' => "Re-EKYC Needed due to error on ekyc api",
                                    'created_on' => date("Y-m-d H:i:s")
                                ];
                            }
                            $data['msg'] = 'Application Sanctioned.';
                            //
                            //if ($lead_data_source_id == 2) {

                            $sendLetter = $this->Tasks->sendSanctionMail($lead_id);
                            // print_r($sendLetter); die;

                            // if ($sendLetter['status'] == 1) {
                            //    echo $data['msg'] = 'Application Sanctioned.';
                            // } else {
                            //    echo $data['msg'] = 'Application Sanctioned. Email sent error : ' . $sendLetter['error'];
                            // }
                            // exit();
                            // }
                            echo json_encode($data);
                        } else {
                            $json['err'] = "Unable to update in lead details.";
                            echo json_encode($json);
                        }
                    } else {
                        $json['err'] = "Unable to insert in loan details.";
                        echo json_encode($json);
                    }
                } else {
                    $json['err'] = "Unable to generate loan number.";
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = "Invalid access.";
            echo json_encode($json);
        }
    }

    public function leadRecommend() {

        //   error_reporting(E_ALL);
        //   ini_set("display_errors", 1);

        $user_id = $_SESSION['isUserSession']['user_id'];
        $lead_remark = 'Leads Recommended.';

        if (empty($user_id)) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        if (!empty($_POST["lead_id"])) {

            $lead_id = $this->input->post('lead_id');
            $lead_id =  $this->encrypt->decode($lead_id);

            if (agent != "CR1") {
                $json['err'] = "You are not authrized to take this action.[U01]";
                echo json_encode($json);
                return false;
            }

            $query = $this->db->query("SELECT LD.first_name, LD.lead_id, LD.lead_status_id, LD.lead_screener_assign_user_id, LD.lead_branch_id, LD.user_type, C.pancard, C.aadhar_no, LD.lead_data_source_id, C.alternate_email,C.alternate_email_verified_status,C.pancard_verified_status FROM leads LD INNER JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id) WHERE LD.lead_id = " . $lead_id);
            $leadDetails = $query->row_array();

            $pan_query = $this->db->query("SELECT * FROM `api_poi_verification_logs` WHERE poi_veri_lead_id=" . $lead_id . " AND poi_veri_api_status_id=1 ORDER BY `poi_veri_id` DESC LIMIT 1");
            $pan_Data = $pan_query->row_array();
            $pan_Details = json_decode($pan_Data['poi_veri_response'], true);
            

            // if (empty($pan_Details)) {
            //     $json['err'] = "PAN not verified.";
            //     echo json_encode($json);
            //     return false;
            // }

            $update_data_lead_customer = array();

            if (empty($leadDetails)) {
                $json['err'] = "Lead details does not exist.[L01]";
                echo json_encode($json);
                return false;
            }

            if ($user_id != $leadDetails['lead_screener_assign_user_id']) {
                $json['err'] = "You are not authrized to take this action.[U02]";
                echo json_encode($json);
                return false;
            }

            if (!in_array($leadDetails['lead_status_id'], array(2, 3))) {
                $json['err'] = "You are not authrized to take this action.[S01]";
                echo json_encode($json);
                return false;
            }

            // if (empty($leadDetails['lead_branch_id'])) {
            //     $json['err'] = "Lead branch is not available. Please check your city is map with branch?.[S02]";
            //     echo json_encode($json);
            //     return false;
            // }

            if (empty($leadDetails['pancard'])) {
                $json['err'] = "PAN is not available. Please check pan no.[S03]";
                echo json_encode($json);
                return false;
            }

            // if (strtoupper($leadDetails['first_name']) != strtoupper($pan_Details['result']['firstName'])) {
            //     $json['err'] = "First name does not matched with PAN Card[S03]";
            //     echo json_encode($json);
            //     return false;
            // }

            $isBlackListed = $this->Tasks->checkBlackListedCustomer($lead_id);

            if ($isBlackListed['status'] == 1) {
                $json['err'] = $isBlackListed['error_msg'];
                echo json_encode($json);
                return false;
            }

            if (empty($leadDetails['aadhar_no'])) {
                $json['err'] = "Aadhaar last 4 digit is not available. Please check aadhaar no.[S04]";
                echo json_encode($json);
                return false;
            }

            $pancard = $leadDetails['pancard'];
            $aadhar = $leadDetails['aadhar_no'];
            $lead_data_source_id = $leadDetails['lead_data_source_id'];

            $conditions = ['lead_id' => $lead_id];
            $remark = '';


            $isBlackListed = $this->Tasks->checkBlackListedCustomer($lead_id);

            if ($isBlackListed['status'] == 1) {
                $json['err'] = $isBlackListed['error_msg'];
                echo json_encode($json);
                return false;
            }


            // $cityStateSourcing = $this->Tasks->checkCityStateSourcing($lead_id);

            // if ($cityStateSourcing['status'] != 1) {
            //     $json['err'] = $cityStateSourcing['error_msg'];
            //     echo json_encode($json);
            //     return false;
            // }

            // print_r($cityStateSourcing); die;


            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();
            //production
            if (ENVIRONMENT == 'production') {

                $docs_data = $CommonComponent->check_customer_mandatory_documents($lead_id);

                if (empty($docs_data['status'])) {
                    $json['err'] = $docs_data['error'];
                    echo json_encode($json);
                    return false;
                }

                $pan_validate_status = 0;
                $pan_ocr_status = 0;
                $aadhaar_ocr_status = 0;

                if (!empty($leadDetails['alternate_email']) && $leadDetails['alternate_email_verified_status'] != "YES") {
                    $office_email_return = $CommonComponent->call_office_email_verification_api($lead_id);

                    if ($office_email_return['status'] == 1 && $office_email_return['email_validate_status'] == 1) {
                        $lead_remark .= "<br/>Office Email Verified";
                    }
                } else if (!empty($leadDetails['alternate_email']) && $leadDetails['alternate_email_verified_status'] == "YES") {
                    $lead_remark .= "<br/>Office Email Verified";
                }

                // if (!empty($leadDetails['email']) && $leadDetails['email_verified_status'] != "YES") {
                //     $email_return = $CommonComponent->call_email_verification_api($lead_id);

                //     if ($email_return['status'] == 1 && $email_return['email_validate_status'] == 1) {
                //         $lead_remark .= "<br/>Personal Email Verified";
                //     }
                // } else if (!empty($leadDetails['email']) && $leadDetails['email_verified_status'] == "YES") {
                //     $lead_remark .= "<br/>Personal Email Verified";
                // }

                $pan_veri_return = $CommonComponent->call_pan_verification_api($lead_id);
                // print_r($pan_veri_return);die;


                if ($pan_veri_return['status'] == 1) {

                    if ($pan_veri_return['pan_valid_status'] == 2) {

                        $pan_validate_status = 1;
                        $lead_remark .= "<br/>PAN Verified";
                    } else {
                        $json['err'] = "Customer Name does not matched with PAN Detail. Please check the application log.";
                        echo json_encode($json);
                        return false;
                    }
                } else {

                    $json['err'] = trim($pan_veri_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else if ($leadDetails['pancard_verified_status'] == 1) {
                $pan_validate_status = 1;
                $lead_remark .= "<br/>PAN Verified";
            }

            $panDocsDetails = $this->Docs->getLeadDocumentWithTypeDetails($lead_id, 4);

            if ($leadDetails['user_type'] != "REPEAT" || $panDocsDetails['status'] == 1) {


                $pan_ocr_return = $CommonComponent->call_pan_ocr_api($lead_id);
                // print_r($pan_ocr_return);die;
                if ($pan_ocr_return['status'] == 1) {

                    if ($pan_ocr_return['pan_valid_status'] == 1) {
                        $pan_ocr_status = 1;
                        $lead_remark .= "<br/>PAN OCR Verified";
                    } else {
                        $pan_ocr_status = 1;
                        $json['err'] = "Customer PAN does not matched with PAN OCR Detail. Please check the application log.";
                        echo json_encode($json);
                        return false;
                    }
                } else {
                    $pan_ocr_status = 1;
                    $json['err'] = trim($pan_ocr_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else {
                $pan_ocr_status = 1;
            }

            if ($pan_validate_status != 1 && $pan_ocr_status != 1) {
                $json['err'] = "Something went wrong. Please contact to IT Team.";
                echo json_encode($json);
                return false;
            }


            $aadhaarDocsDetails = $this->Docs->getLeadDocumentWithTypeDetails($lead_id, "1,2");

            if ($leadDetails['user_type'] != "REPEAT" || $aadhaarDocsDetails['status'] == 1) {


                $aadhaar_ocr_return = $CommonComponent->call_aadhaar_ocr_api($lead_id);

                if ($aadhaar_ocr_return['status'] == 1) {

                    if ($aadhaar_ocr_return['aadhaar_valid_status'] == 1) {
                        $aadhaar_ocr_status = 1;
                        $lead_remark .= "<br/>Aadhaar OCR Verified";
                    } else {
                        $json['err'] = "Customer Aadhaar does not matched with Aadhaar OCR Detail. Please check the application log.";
                        echo json_encode($json);
                        return false;
                    }
                } else {
                    $json['err'] = trim($aadhaar_ocr_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else {
                $aadhaar_ocr_status = 1;
            }

            if ($aadhaar_ocr_status != 1) {
                $json['err'] = "Something went wrong. Please contact to IT Team.";
                echo json_encode($json);
                return false;
            }
        }


        $conditions_user_roles = array();
        $update_lead_data = array();
        $update_lead_followup_data = array();

        $conditions_user_roles['user_roles.user_role_user_id'] = $_SESSION['isUserSession']['user_id'];
        $conditions_user_roles['user_roles.user_role_active'] = 1;
        $conditions_user_roles['user_roles.user_role_deleted'] = 0;

        $user_roles = $this->Tasks->checkUserHaveManyRoles($conditions_user_roles);

        if (!empty($user_roles['status']) && in_array(3, $user_roles['user_roles'])) { // credit manager
            $status = "APPLICATION-INPROCESS";
            $stage = "S5";
            $lead_status_id = 5;

            $update_lead_data['lead_credit_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
            $update_lead_data['lead_credit_assign_datetime'] = date("Y-m-d H:i:s");

            $login_user_name = $_SESSION['isUserSession']['name'];
            $lead_remark .= "<br/>Application allocate by self - " . $login_user_name;
            $lead_remark .= "<br/>Application moves to in-process as user have credit manager role.";
        } else {
            $status = "APPLICATION-NEW";
            $stage = "S4";
            $lead_status_id = 4;
        }

        if (!empty($leadDetails['lead_journey_type_id']) && $leadDetails['lead_journey_type_id'] == 5) {
            $update_lead_data['lead_doable_to_application_status'] = 2; // 0=>Customer, 1=>Campaign, 2=> Self Model, 3=> Assisted Model
        }

        $update_lead_data['status'] = $status;
        $update_lead_data['stage'] = $stage;
        $update_lead_data['lead_status_id'] = $lead_status_id;
        $update_lead_data['lead_screener_recommend_datetime'] = date("Y-m-d H:i:s");
        $update_lead_data['updated_on'] = date("Y-m-d H:i:s");

        $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');

        $update_lead_followup_data['lead_id'] = $lead_id;
        $update_lead_followup_data['user_id'] = $user_id;
        $update_lead_followup_data['status'] = $status;
        $update_lead_followup_data['stage'] = $stage;
        $update_lead_followup_data['lead_followup_status_id'] = $lead_status_id;
        $update_lead_followup_data['remarks'] = $lead_remark;
        $update_lead_followup_data['created_on'] = date('Y-m-d H:i:s');

        $this->Tasks->insert($update_lead_followup_data, 'lead_followup');

        if (!empty($pancard)) {

            $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();

            if ($cif_query->num_rows() > 0) {

                $cif_result = $cif_query->row();

                $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;

                if ($isdisbursedcheck > 0) {
                    $user_type = "REPEAT";
                } else {
                    $user_type = "NEW";
                }

                $update_data_lead_customer = [
                    'current_house' => $cif_result->cif_residence_address_1,
                    'current_locality' => $cif_result->cif_residence_address_2,
                    'current_landmark' => $cif_result->cif_residence_landmark,
                    'current_residence_type' => $cif_result->cif_residence_type,
                    'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                    'current_residence_since' => $cif_result->cif_residence_since,
                    'updated_at' => date("Y-m-d H:i:s")
                ];

                $update_data_lead_customer['customer_digital_ekyc_flag'] = 0;
                $update_data_lead_customer['customer_digital_ekyc_done_on'] = NULL;

                if ($aadhar == $cif_result->cif_aadhaar_no && !empty($cif_result->cif_aadhaar_no)) {
                    $update_data_lead_customer['aa_current_house'] = $cif_result->cif_aadhaar_address_1;
                    $update_data_lead_customer['aa_current_locality'] = $cif_result->cif_aadhaar_address_2;
                    $update_data_lead_customer['aa_current_landmark'] = $cif_result->cif_aadhaar_landmark;
                    $update_data_lead_customer['aa_cr_residence_pincode'] = $cif_result->cif_aadhaar_pincode;
                    $update_data_lead_customer['aa_current_state_id'] = $cif_result->cif_aadhaar_state_id;
                    $update_data_lead_customer['aa_current_city_id'] = $cif_result->cif_aadhaar_city_id;

                    if ($user_type == "REPEAT" && $cif_result->cif_digital_ekyc_flag == 1 && !empty($cif_result->cif_digital_ekyc_datetime)) {
                        $camp_kyc_date = strtotime(date("Y-m-d", strtotime("+90 day", strtotime($cif_result->cif_digital_ekyc_datetime))));
                        $camp_current_datetime = strtotime(date("Y-m-d"));
                        if ($camp_kyc_date > $camp_current_datetime) {
                            $update_data_lead_customer['customer_digital_ekyc_flag'] = 1;
                            $update_data_lead_customer['customer_digital_ekyc_done_on'] = $cif_result->cif_digital_ekyc_datetime;
                        }
                    }
                }

                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                $update_customer_employement = [
                    'customer_id' => $cif_result->cif_number,
                    'employer_name' => $cif_result->cif_company_name,
                    'emp_pincode' => $cif_result->cif_office_pincode,
                    'emp_house' => $cif_result->cif_office_address_1,
                    'emp_street' => $cif_result->cif_office_address_2,
                    'emp_landmark' => $cif_result->cif_office_address_landmark,
                    'emp_residence_since' => $cif_result->cif_office_working_since,
                    'emp_shopNo' => $cif_result->cif_office_address_1,
                    'emp_designation' => $cif_result->cif_office_designation,
                    'emp_department' => $cif_result->cif_office_department,
                    'emp_employer_type' => $cif_result->cif_company_type_id,
                    'emp_website' => $cif_result->cif_company_website,
                    'city_id' => $cif_result->cif_office_city_id,
                    'state_id' => $cif_result->cif_office_state_id,
                    'updated_on' => date("Y-m-d H:i:s"),
                ];

                $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                $update_data_leads = [
                    'customer_id' => $cif_result->cif_number,
                    'user_type' => $user_type,
                    'updated_on' => date("Y-m-d H:i:s")
                ];
                $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
            } else {
                $user_type = "NEW";
                $update_data_leads = [
                    'customer_id' => '',
                    'user_type' => $user_type,
                    'updated_on' => date("Y-m-d H:i:s")
                ];

                $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
            }
        }

        if ($user_type == "REPEAT") {

            $sql_customer_banking = "SELECT CB.* FROM leads LD";
            $sql_customer_banking .= " INNER JOIN customer_banking CB ON (CB.lead_id = LD.lead_id)";
            $sql_customer_banking .= " WHERE LD.pancard= '" . $pancard . "' AND CB.account_status_id = 1 AND LD.lead_status_id IN (14, 16, 17, 19)";
            $sql_customer_banking .= " AND CB.customer_banking_active=1 AND CB.customer_banking_deleted=0";
            $sql_customer_banking .= " ORDER BY CB.id DESC";
            $sql_customer_banking .= " LIMIT 0,1";

            $query_customer_banking = $this->db->query($sql_customer_banking);

            if ($query_customer_banking->num_rows() > 0) {

                $repeatCustomerBanking = $query_customer_banking->row_array();
                $insert_customer_banking_data = array();
                $insert_customer_banking_data['customer_id'] = $repeatCustomerBanking['customer_id'];
                $insert_customer_banking_data['lead_id'] = $lead_id;
                $insert_customer_banking_data['user_id'] = $user_id;
                $insert_customer_banking_data['bank_name'] = $repeatCustomerBanking['bank_name'];
                $insert_customer_banking_data['ifsc_code'] = $repeatCustomerBanking['ifsc_code'];
                $insert_customer_banking_data['branch'] = $repeatCustomerBanking['branch'];
                $insert_customer_banking_data['beneficiary_name'] = $repeatCustomerBanking['beneficiary_name'];
                $insert_customer_banking_data['account'] = $repeatCustomerBanking['account'];
                $insert_customer_banking_data['confirm_account'] = $repeatCustomerBanking['confirm_account'];
                $insert_customer_banking_data['account_type'] = $repeatCustomerBanking['account_type'];
                $insert_customer_banking_data['account_status'] = NULL;
                $insert_customer_banking_data['account_status_id'] = NULL;
                $insert_customer_banking_data['remark'] = "Repeat case banking - " . $repeatCustomerBanking['remark'];
                $insert_customer_banking_data['created_by'] = $user_id;
                $insert_customer_banking_data['created_on'] = date("Y-m-d H:i:s");

                $this->Tasks->insert($insert_customer_banking_data, 'customer_banking');
            }

            $sql_customer_references = "SELECT LCR.*";
            $sql_customer_references .= " FROM leads LD";
            $sql_customer_references .= " INNER JOIN lead_customer_references LCR ON (LCR.lcr_lead_id = LD.lead_id)";
            $sql_customer_references .= " WHERE LD.pancard= '" . $pancard . "' AND LD.lead_status_id IN (14, 16, 17, 19)";
            $sql_customer_references .= " ORDER BY LCR.lcr_id DESC";
            $sql_customer_references .= " LIMIT 0, 2";

            $query_customer_references = $this->db->query($sql_customer_references);

            if ($query_customer_references->num_rows() > 0) {

                $repeatCustomerReferences = $query_customer_references->result_array();

                foreach ($repeatCustomerReferences as $row) {

                    $insert_customer_references_data = array();
                    $insert_customer_references_data['lcr_lead_id'] = $lead_id;
                    $insert_customer_references_data['lcr_name'] = $row['lcr_name'];
                    $insert_customer_references_data['lcr_relationType'] = $row['lcr_relationType'];
                    $insert_customer_references_data['lcr_mobile'] = $row['lcr_mobile'];
                    $insert_customer_references_data['lcr_created_by'] = $user_id;
                    $insert_customer_references_data['lcr_created_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->insert($insert_customer_references_data, 'lead_customer_references');
                }
            }
        }

        $json['msg'] = $lead_remark;

        if ($update_data_lead_customer['customer_digital_ekyc_flag'] == 0) {
            $this->Tasks->sent_ekyc_request_email($lead_id);
        }

        echo json_encode($json);
    }


    public function disburseRecommend() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        if (!empty($_POST["lead_id"])) {
            $empDetails = $this->Tasks->select(['lead_id' => $_POST["lead_id"]], 'lead_id, lead_status_id', 'leads');
            $empDetails = $empDetails->row();

            if ($empDetails->lead_status_id == 30) {
                $json['err'] = "You are not authorized to take this action.";
                echo json_encode($json);
                return false;
            }
        }

        // print_r($_POST);
        // die;

        if (empty($_POST["remarks"])) {
            $json['err'] = "Remark is required.";
            echo json_encode($json);
        } else if (!empty($_POST["lead_id"])) {
            $lead_id = $this->input->post('lead_id');
            $lead_id = $this->encrypt->decode($this->input->post('lead_id'));
            $customer_id = $this->input->post('customer_id');
            $remarks = $this->input->post('remarks');
            $status = "DISBURSE-PENDING";
            $stage = "S13";
            $lead_status_id = 13;
            $data = ['status' => $status, "stage" => $stage, 'lead_status_id' => $lead_status_id, 'lead_disbursal_recommend_datetime' => date("Y-m-d H:i:s"), 'updated_on' => date("Y-m-d H:i:s")];

            $data2 = [
                'lead_id' => $lead_id,
                'customer_id' => $customer_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $status,
                "stage" => $stage,
                "lead_followup_status_id" => $lead_status_id,
                'remarks' => $remarks,
                'created_on' => date('Y-m-d H:i:s')
            ];
            $conditions = ['lead_id' => $lead_id];
            $this->Tasks->updateLeads($conditions, $data, 'leads');
            $dataLoan = [
                "status" => $status,
                "loan_status_id" => $lead_status_id,
            ];

            $conditions = ['lead_id' => $lead_id];

            $this->Tasks->updateLeads($conditions, $dataLoan, 'loan');

            $this->Tasks->insert($data2, 'lead_followup');
            $data['msg'] = $remarks;
            echo json_encode($data);
        }
    }

    public function disburseWaived() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }
        if (empty($_POST["remarks"])) {
            $json['err'] = "Remark is required.";
            echo json_encode($json);
        } else if (!empty($_POST["lead_id"])) {
            $lead_id = $this->input->post('lead_id');
            $customer_id = $this->input->post('customer_id');
            $remarks = $this->input->post('remarks');

            $status = "DISBURSED-WAIVED";
            $stage = "S30";
            $lead_status_id = 40;

            $update_data_cam = array();
            $update_data_loan = array();
            $update_data_leads = array();
            $update_data_lead_followup = array();

            $conditions = ['lead_id' => $lead_id]; // conditions

            $query = $this->db->query('SELECT lead_id, loan_recommended FROM `credit_analysis_memo` WHERE lead_id=' . $lead_id);
            $camDetails = $query->row_array();

            $update_data_cam['processing_fee_percent'] = 0;
            $update_data_cam['admin_fee'] = 0;
            $update_data_cam['total_admin_fee'] = 0;
            $update_data_cam['adminFeeWithGST'] = 0;
            $update_data_cam['cam_advance_interest_amount'] = 0;
            $update_data_cam['net_disbursal_amount'] = $camDetails['loan_recommended'];
            $update_data_cam['repayment_amount'] = $camDetails['loan_recommended'];

            $this->Tasks->updateLeads($conditions, $update_data_cam, 'credit_analysis_memo');

            $update_data_loan['status'] = $status;
            $update_data_loan['loan_status_id'] = $lead_status_id;
            $update_data_loan['loan_disburse_waive_user_id'] = $_SESSION['isUserSession']['user_id'];
            $update_data_loan['loan_disburse_waive_datetime'] = date('Y-m-d H:i:s');

            $this->Tasks->updateLeads($conditions, $update_data_loan, 'loan');

            $update_data_leads['status'] = $status;
            $update_data_leads['stage'] = $stage;
            $update_data_leads['lead_status_id'] = $lead_status_id;
            $update_data_leads['updated_on'] = date("Y-m-d H:i:s");

            $this->Tasks->updateLeads($conditions, $update_data_leads, 'leads');

            $update_data_lead_followup['lead_id'] = $lead_id;
            $update_data_lead_followup['customer_id'] = $customer_id;
            $update_data_lead_followup['user_id'] = $_SESSION['isUserSession']['user_id'];
            $update_data_lead_followup['status'] = $status;
            $update_data_lead_followup['stage'] = $stage;
            $update_data_lead_followup['lead_followup_status_id'] = $lead_status_id;
            $update_data_lead_followup['remarks'] = $remarks;
            $update_data_lead_followup['created_on'] = date('Y-m-d H:i:s');

            $this->Tasks->insert($update_data_lead_followup, 'lead_followup');

            $this->Tasks->nocDisbursalWaivedOFF($lead_id, $remarks);

            $data['msg'] = $remarks;
            echo json_encode($data);
        }
    }

    public function disbursalSendBack() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            return false;
        }

        if (!empty($_POST["lead_id"])) {
            $lead_id = $this->input->post('lead_id');
            $remarks = $this->input->post('remark');
            $lead_id =  $this->encrypt->decode($lead_id);
            $status = "DISBURSAL-SEND-BACK";
            $stage = "S25";
            $lead_status_id = 37;

            $update_lead_data = [
                'status' => $status,
                'stage' => $stage,
                'lead_status_id' => $lead_status_id,
                'updated_on' => date("Y-m-d H:i:s")
            ];

            $insert_lead_followup = [
                'lead_id' => $lead_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $status,
                "stage" => $stage,
                "lead_followup_status_id" => $lead_status_id,
                'remarks' => $remarks,
                'created_on' => date("Y-m-d H:i:s")
            ];

            $conditions = ['lead_id' => $lead_id];

            $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');

            $this->Tasks->insert($insert_lead_followup, 'lead_followup');

            $data['msg'] = $remarks;
            echo json_encode($data);
        }
    }

    public function leadSendBack() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            return false;
        }
        if (isset($_POST["lead_id"])) {
            $lead_id = $this->input->post('lead_id');
            $remarks = $this->input->post('remark');
            $lead_id =  $this->encrypt->decode($lead_id);

            $leadsDetails = $this->Tasks->select(['lead_id' => $lead_id], 'first_name, email, mobile, lead_status_id', 'leads');

            if ($leadsDetails->num_rows() > 0) {

                $leadsDetails = $leadsDetails->row();

                if (!in_array($leadsDetails->lead_status_id, array(10))) {
                    $json['err'] = "Invalid Access";
                    echo json_encode($json);
                    return false;
                }
            }


            $status = "APPLICATION-SEND-BACK";
            $stage = "S11";
            $lead_status_id = 11;

            if ($lead_status_id) {
                $update_lead_bre_run = [
                    'customer_bre_run_flag' => 0,
                    'customer_bre_run_datetime' => NULL
                ];
                $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_lead_bre_run);
            }

            $update_lead_data = [
                'status' => $status,
                'stage' => $stage,
                'lead_status_id' => $lead_status_id,
                'updated_on' => date("Y-m-d H:i:s")

            ];

            $insert_lead_followup = [
                'lead_id' => $lead_id,
                'customer_id' => $this->input->post('customer_id'),
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $status,
                "stage" => $stage,
                "lead_followup_status_id" => $lead_status_id,
                'remarks' => $remarks,
                'created_on' => date("Y-m-d H:i:s")
            ];


            $conditions = ['lead_id' => $lead_id];
            // print_r($conditions); die;

            $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');

            $this->Tasks->insert($insert_lead_followup, 'lead_followup');

            $data['msg'] = 'Application Send Back.';

            echo json_encode($data);
        }
    }

    public function getPersonalDetails($lead_id) {
        $lead_id =  $this->encrypt->decode($lead_id);
        $conditions = ['LD.lead_id' => $lead_id];
        $personalDetails = $this->Tasks->index($conditions);
        $data['personalDetails1'] = $personalDetails->row();

        echo json_encode($data);
    }

    public function getResidenceDetails($lead_id) {
        $lead_id =  $this->encrypt->decode($lead_id);
        $query = $this->Tasks->getResidenceDetails($lead_id);
        $row = $query->row();
        $data['residenceDetails'] = [
            "current_house" => !empty($row->current_house) ? $row->current_house : "",
            "current_locality" => !empty($row->current_locality) ? $row->current_locality : "",
            "aadhar_no" => !empty($row->aadhar_no) ? $row->aadhar_no : "",
            "current_landmark" => !empty($row->current_landmark) ? $row->current_landmark : "",
            "current_residence_since" => !empty($row->current_residence_since) ? date('d-m-Y', strtotime($row->current_residence_since)) : "",
            "current_residence_type" => !empty($row->current_residence_type) ? $row->current_residence_type : "",
            "current_residing_withfamily" => !empty($row->current_residing_withfamily) ? $row->current_residing_withfamily : "",
            "current_state" => !empty($row->current_state) ? $row->current_state : "",
            "current_city" => !empty($row->current_city) ? $row->current_city : "",
            "state_id" => !empty($row->state_id) ? $row->state_id : "",
            "city_id" => !empty($row->city_id) ? $row->city_id : "",
            "current_district" => !empty($row->current_district) ? $row->current_district : "",
            "cr_residence_pincode" => !empty($row->cr_residence_pincode) ? $row->cr_residence_pincode : "",
            "current_res_status" => !empty($row->current_res_status) ? $row->current_res_status : "",
            "aa_same_as_current_address" => !empty($row->aa_same_as_current_address) ? $row->aa_same_as_current_address : "",
            "aa_current_house" => !empty($row->aa_current_house) ? $row->aa_current_house : "",
            "aa_current_locality" => !empty($row->aa_current_locality) ? $row->aa_current_locality : "",
            "aa_current_landmark" => !empty($row->aa_current_landmark) ? $row->aa_current_landmark : "",
            "aa_current_state" => !empty($row->aa_current_state) ? $row->aa_current_state : "",
            "aa_current_city" => !empty($row->aa_current_city) ? $row->aa_current_city : "",
            "aa_current_district" => !empty($row->aa_current_district) ? $row->aa_current_district : "",
            "aa_current_city_id" => !empty($row->aa_current_city_id) ? $row->aa_current_city_id : "",
            "aa_current_state_id" => !empty($row->aa_current_state_id) ? $row->aa_current_state_id : "",
            "aa_cr_residence_pincode" => !empty($row->aa_cr_residence_pincode) ? $row->aa_cr_residence_pincode : "",
            "res_state" => !empty($row->res_state) ? strtoupper($row->res_state) : "",
            "res_city" => !empty($row->res_city) ? strtoupper($row->res_city) : "",
            "aadhar_state" => !empty($row->aadhar_state) ? strtoupper($row->aadhar_state) : "",
            "aadhar_city" => !empty($row->aadhar_city) ? strtoupper($row->aadhar_city) : "",
            "aadhar_no" => !empty($row->aadhar_no) ? $row->aadhar_no : "",
        ];
        echo json_encode($data);
    }

    public function getEmploymentDetails($lead_id) {
        //   echo $lead_id; die;
        $lead_id =  $this->encrypt->decode($lead_id);

        if (!empty($lead_id)) {

            $query = $this->Tasks->getEmploymentDetails($lead_id);

            $data['department'] = $this->Tasks->getDepartmentMaster();
            $row = $query->row();
            $data['employmentDetails'] = [
                'customer_id' => !empty($row->customer_id) ? $row->customer_id : "",
                'employer_name' => !empty($row->employer_name) ? $row->employer_name : "",
                'emp_state' => !empty($row->emp_state) ? $row->emp_state : "",
                'emp_city' => !empty($row->emp_city) ? $row->emp_city : "",
                'emp_pincode' => !empty($row->emp_pincode) ? $row->emp_pincode : "",
                'emp_house' => !empty($row->emp_house) ? $row->emp_house : "",
                'emp_street' => !empty($row->emp_street) ? $row->emp_street : "",
                'emp_landmark' => !empty($row->emp_landmark) ? $row->emp_landmark : "",
                'emp_residence_since' => !empty($row->emp_residence_since) ? date('d-m-Y', strtotime($row->emp_residence_since)) : "",
                'presentServiceTenure' => !empty($row->presentServiceTenure) ? $row->presentServiceTenure : "",
                'emp_designation' => !empty($row->emp_designation) ? $row->emp_designation : "",
                'emp_department' => !empty($row->emp_department) ? $row->emp_department : "",
                'emp_employer_type' => !empty($row->emp_employer_type) ? $row->emp_employer_type : "",
                'emp_website' => !empty($row->emp_website) ? $row->emp_website : "",
                'monthly_income' => !empty($row->monthly_income) ? $row->monthly_income : "",
                'income_type' => !empty($row->income_type) ? $row->income_type : "",
                'industry' => !empty($row->industry) ? $row->industry : "",
                'sector' => !empty($row->sector) ? $row->sector : "",
                'salary_mode' => !empty($row->salary_mode) ? $row->salary_mode : "",
                'emp_status' => !empty($row->emp_status) ? $row->emp_status : "",
                'created_on' => !empty($row->created_on) ? $row->created_on : "",
                'state' => !empty($row->m_state_name) ? strtoupper($row->m_state_name) : "",
                'city' => !empty($row->m_city_name) ? strtoupper($row->m_city_name) : "",
                'department_name' => !empty($row->department_name) ? strtoupper($row->department_name) : "",
                'state_id' => !empty($row->state_id) ? $row->state_id : "",
                'city_id' => !empty($row->city_id) ? $row->city_id : "",
                'emp_work_mode' => !empty($row->emp_work_mode) ? $row->emp_work_mode : ""
                // 'm_occupation_name' => !empty($row->m_occupation_name) ? strtoupper($row->m_occupation_name) : "",
                // 'emp_occupation_id' => !empty($row->emp_occupation_id) ? $row->emp_occupation_id : ""
            ];
        }
        echo json_encode($data);
    }

    public function getReferenceDetails($lead_id) {
        $lead_id =  $this->encrypt->decode($lead_id);
        $data['refrence'] = getrefrenceData('lead_customer_references', $lead_id);
        echo json_encode($data);
    }

    public function getApplicationDetails($lead_id) {

        $leadID =  $this->encrypt->decode($lead_id);
        $conditions = ['LD.lead_id' => $leadID];
        $applicationDetails = $this->Tasks->index($conditions);
        $data['application'] = $applicationDetails->row();

        echo json_encode($data);
    }

    public function convertEnquiryToApplication() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('cust_enquiry_id', 'Enquiry ID', 'required|trim');
            $this->form_validation->set_rules('loan_applied', 'Loan Applied', 'required|trim|numeric|min_length[3]|max_length[5]');
            $this->form_validation->set_rules('loan_tenure', 'Loan Tenure', 'required|trim|numeric|min_length[1]|max_length[3]');
            $this->form_validation->set_rules('loan_purpose', 'Loan Purpose', 'required|trim');
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|min_length[3]|max_length[50]');
            $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim|min_length[3]|max_length[50]');
            $this->form_validation->set_rules('sur_name', 'Surname', 'trim|min_length[3]|max_length[50]');
            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');

            $this->form_validation->set_rules('dob', 'DOB', 'required|trim');
            $this->form_validation->set_rules('pancard', 'Pancard', 'required|trim|alpha_numeric|exact_length[10]');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|trim');
            $this->form_validation->set_rules('salary_mode', 'Salary Mode', 'required|trim');
            $this->form_validation->set_rules('monthly_income', 'Salary', 'required|trim|numeric');
            $this->form_validation->set_rules('obligations', 'Obligations', 'required|trim|numeric');

            $this->form_validation->set_rules('state', 'State', 'required|trim');
            $this->form_validation->set_rules('city', 'City', 'required|trim');
            $this->form_validation->set_rules('pincode', 'Pincode', 'required|trim');
            $this->form_validation->set_rules('obligations', 'Obligations', 'required|trim|numeric');

            $this->form_validation->set_rules('customer_marital_status_id', 'Marital Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $cust_enquiry_id = $this->input->post('cust_enquiry_id');

                $first_name = strtoupper($this->input->post('first_name'));
                $middle_name = strtoupper($this->input->post('middle_name'));
                $sur_name = strtoupper($this->input->post('sur_name'));
                $email = strtoupper($this->input->post('email'));
                $alternate_email = strtoupper($this->input->post('alternate_email'));
                $city_state_id = intval($this->input->post('state'));
                $city_id = intval($this->input->post('city'));
                $pincode = intval($this->input->post('pincode'));

                $loan_amount = intval($this->input->post('loan_applied'));
                $obligations = intval($this->input->post('obligations'));
                $monthly_income = intval($this->input->post('monthly_income'));
                $lead_data_source_id = $this->input->post('source_id');
                $pancard = strtoupper($this->input->post('pancard'));
                $utm_source = "bharatloan.com";
                $utm_campaign = "bharatloan.com";
                $gender = $this->input->post('gender');
                $dob = date("Y-m-d", strtotime($this->input->post('dob')));
                $mobile = $this->input->post('mobile');
                $alternate_mobile = $this->input->post('alternate_mobile');
                $coordinates = $this->input->post('geo_coordinates');
                $ip = $this->input->post('ip');
                $tenure = $this->input->post('loan_tenure');
                $loan_purpose = $this->input->post('loan_purpose');
                $salary_mode = $this->input->post('salary_mode');
                $loan_purpose = $this->input->post('loan_purpose');
                $marital_status = $this->input->post('customer_marital_status_id');
                $qualification = $this->input->post('customer_qualification_id');
                $spouse_name = $this->input->post('customer_spouse_name');
                $spouse_occupation = $this->input->post('customer_spouse_occupation_id');

                $data_source_array = $this->Tasks->getDataSourceList();

                $source = $data_source_array[$lead_data_source_id];

                $insertDataLeads = array(
                    'first_name' => $first_name,
                    'mobile' => $mobile,
                    'pancard' => $pancard,
                    'state_id' => $city_state_id,
                    'city_id' => $city_id,
                    'pincode' => $pincode,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'loan_amount' => $loan_amount,
                    'tenure' => $tenure,
                    'purpose' => $loan_purpose,
                    'obligations' => $obligations,
                    'user_type' => 'NEW',
                    'lead_entry_date' => date("Y-m-d"),
                    'created_on' => date("Y-m-d H:i:s"),
                    'source' => $source,
                    'ip' => $ip,
                    'status' => "LEAD-NEW",
                    'stage' => "S1",
                    'lead_status_id' => 1,
                    'qde_consent' => "Y",
                    'term_and_condition' => "YES",
                    'lead_data_source_id' => $lead_data_source_id,
                    'coordinates' => $coordinates,
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                );

                $this->db->insert('leads', $insertDataLeads);

                $lead_id = $this->db->insert_id();

                if (!empty($lead_id)) {

                    $insertLeadsCustomer = array(
                        'customer_lead_id' => $lead_id,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'mobile' => $mobile,
                        'alternate_mobile' => $alternate_mobile,
                        'email' => $email,
                        'alternate_email' => $alternate_email,
                        'pancard' => $pancard,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'cr_residence_pincode' => $pincode,
                        'customer_marital_status_id' => $marital_status,
                        'customer_qualification_id' => $qualification,
                        'customer_spouse_name' => $spouse_name,
                        'customer_spouse_occupation_id' => $spouse_occupation,
                        'created_date' => date("Y-m-d H:i:s")
                    );

                    $this->db->insert('lead_customer', $insertLeadsCustomer);

                    $insert_customer_employement = [
                        'lead_id' => $lead_id,
                        'emp_email' => $alternate_email,
                        'monthly_income' => $monthly_income,
                        'salary_mode' => $salary_mode,
                        'emp_created_by' => $_SESSION['isUserSession']['user_id'],
                        'created_on' => date("Y-m-d H:i:s")
                    ];

                    $this->db->insert('customer_employment', $insert_customer_employement);

                    if (!empty($pancard)) {
                        $cif_query = $this->db->select('*')->where('cif_pancard', $pancard)->from('cif_customer')->get();

                        if ($cif_query->num_rows() > 0) {
                            $cif_result = $cif_query->row();

                            $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;

                            if ($isdisbursedcheck > 0) {
                                $user_type = "REPEAT";
                            } else {
                                $user_type = "NEW";
                            }

                            $update_data_lead_customer = [
                                'current_house' => $cif_result->cif_residence_address_1,
                                'current_locality' => $cif_result->cif_residence_address_2,
                                'current_landmark' => $cif_result->cif_residence_landmark,
                                'current_residence_type' => $cif_result->cif_residence_type,
                                'current_residing_withfamily' => $cif_result->cif_residence_residing_with_family,
                                'current_residence_since' => $cif_result->cif_residence_since,
                                'aa_same_as_current_address' => $cif_result->cif_aadhaar_same_as_residence,
                                'aa_current_house' => $cif_result->cif_aadhaar_address_1,
                                'aa_current_locality' => $cif_result->cif_aadhaar_address_2,
                                'aa_current_landmark' => $cif_result->cif_aadhaar_landmark,
                                'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                                'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                                'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                                'aadhar_no' => $cif_result->cif_aadhaar_no,
                                'aa_cr_residence_pincode' => $cif_result->cif_aadhaar_pincode,
                                'aa_current_state_id' => $cif_result->cif_aadhaar_state_id,
                                'aa_current_city_id' => $cif_result->cif_aadhaar_city_id,
                                'aadhar_no' => $cif_result->cif_aadhaar_no,
                                'customer_marital_status_id' => $cif_result->cif_marital_status_id,
                                'customer_spouse_name' => $cif_result->cif_spouse_name,
                                'customer_spouse_occupation_id' => $cif_result->cif_spouse_occupation_id,
                                'customer_qualification_id' => $cif_result->cif_qualification_id,
                                'updated_at' => date("Y-m-d H:i:s")
                            ];
                            $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $update_data_lead_customer);

                            $update_customer_employement = [
                                'customer_id' => $cif_result->cif_number,
                                'employer_name' => $cif_result->cif_company_name,
                                'emp_pincode' => $cif_result->cif_office_pincode,
                                'emp_house' => $cif_result->cif_office_address_1,
                                'emp_street' => $cif_result->cif_office_address_2,
                                'emp_landmark' => $cif_result->cif_office_address_landmark,
                                'emp_residence_since' => $cif_result->cif_office_working_since,
                                'emp_shopNo' => $cif_result->cif_office_address_1,
                                'emp_designation' => $cif_result->cif_office_designation,
                                'emp_department' => $cif_result->cif_office_department,
                                'emp_employer_type' => $cif_result->cif_company_type_id,
                                'emp_website' => $cif_result->cif_company_website,
                                'city_id' => $cif_result->cif_office_city_id,
                                'state_id' => $cif_result->cif_office_state_id,
                                'updated_on' => date("Y-m-d H:i:s"),
                            ];

                            $this->db->where('lead_id', $lead_id)->update('customer_employment', $update_customer_employement);

                            $update_data_leads = [
                                'customer_id' => $cif_result->cif_number,
                                'user_type' => $user_type,
                                'updated_on' => date("Y-m-d H:i:s")
                            ];
                            $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                        }
                    }

                    $reference_no = $this->generateReferenceCode($lead_id, $first_name, $sur_name, $mobile);

                    $application_no = $this->Tasks->generateApplicationNo($lead_id);

                    $update_data_leads = [
                        'lead_reference_no' => $reference_no,
                        'application_no' => $application_no,
                        'application_status' => 1,
                        'status' => 'LEAD-INPROCESS',
                        'stage' => 'S2',
                        'lead_status_id' => '2',
                        'lead_screener_assign_user_id' => $_SESSION['isUserSession']['user_id'],
                        'lead_screener_assign_datetime' => date("Y-m-d H:i:s"),
                        'updated_on' => date("Y-m-d H:i:s")
                    ];

                    $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                }

                $result = $this->Tasks->globalUpdate(['cust_enquiry_id' => $cust_enquiry_id], ['cust_enquiry_lead_id' => $lead_id], 'customer_enquiry');

                if ($result == true) {
                    $json['msg'] = "Lead Updated Successfully.";
                } else {
                    $json['err'] = "Failed to Updated Customer Details.";
                }
                echo json_encode($json);
            }
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    private function generateReferenceCode($lead_id, $first_name, $last_name, $mobile) {

        $code_mix = array($lead_id[rand(0, strlen($lead_id) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $mobile[rand(0, strlen($mobile) - 1)], $mobile[rand(0, strlen($mobile) - 1)]);

        shuffle($code_mix);

        $referenceID = "#Tejas";

        foreach ($code_mix as $each) {

            $referenceID .= $each;
        }

        $referenceID = str_replace(" ", "X", $referenceID);

        $referenceID = strtoupper($referenceID);

        return $referenceID;
    }

    public function insertApplication() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            $this->form_validation->set_rules('company_id', 'Company ID', 'required|trim');
            $this->form_validation->set_rules('product_id', 'Product ID', 'required|trim');
            $this->form_validation->set_rules('loan_applied', 'Loan Applied', 'required|trim|numeric');
            $this->form_validation->set_rules('loan_tenure', 'Loan Tenure', 'required|trim|numeric|min_length[1]|max_length[3]');
            $this->form_validation->set_rules('loan_purpose', 'Loan Purpose', 'required|trim');
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|min_length[1]|max_length[50]');
            $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim|min_length[1]|max_length[50]');
            $this->form_validation->set_rules('sur_name', 'Surname', 'trim|min_length[1]|max_length[50]');
            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');

            $this->form_validation->set_rules('dob', 'DOB', 'required|trim');
            $this->form_validation->set_rules('pancard', 'Pancard', 'required|trim|alpha_numeric|exact_length[10]');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim|exact_length[10]');
            $this->form_validation->set_rules('alternate_mobile', 'Alternate Mobile', 'trim|exact_length[10]');
            $this->form_validation->set_rules('email', 'Email', 'required|trim');
            $this->form_validation->set_rules('salary_mode', 'Salary Mode', 'required|trim');
            $this->form_validation->set_rules('monthly_income', 'Salary', 'required|trim|numeric');
            $this->form_validation->set_rules('income_type', 'Income Type', 'required|trim|numeric');
            $this->form_validation->set_rules('obligations', 'Obligations', 'required|trim|numeric');

            $this->form_validation->set_rules('state', 'State', 'required|trim');
            $this->form_validation->set_rules('city', 'City', 'required|trim');
            $this->form_validation->set_rules('pincode', 'Pincode', 'required|trim');
            $this->form_validation->set_rules('religion', 'Religion', 'required|trim');
            $this->form_validation->set_rules('aadhar', 'Aadhaar Last 4 digit', 'required|trim|numeric|exact_length[4]');
            $this->form_validation->set_rules('customer_marital_status_id', 'Marital Status', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->input->post('lead_id');

                $lead_id =  $this->encrypt->decode($lead_id);


                $leadDetails = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, lead_reference_no, pancard', 'leads');


                if ($leadDetails->num_rows() > 0) {

                    $leadDetails = $leadDetails->row();


                    $lead_id = $leadDetails->lead_id;
                    $lead_reference_no = !empty($leadDetails->lead_reference_no) ? $leadDetails->lead_reference_no : "";
                    $lead_pancard = !empty($leadDetails->pancard) ? trim(strtoupper($leadDetails->pancard)) : "";

                    $customer_id = $this->input->post('customer_id');


                    $first_name = strtoupper($this->input->post('first_name'));
                    $middle_name = strtoupper($this->input->post('middle_name'));
                    $sur_name = strtoupper($this->input->post('sur_name'));
                    $email = strtoupper($this->input->post('email'));
                    $alternate_email = strtoupper($this->input->post('alternate_email'));
                    $city_state_id = intval($this->input->post('state'));
                    $city_id = intval($this->input->post('city'));
                    $religion_id = intval($this->input->post('religion'));

                    $pincode = intval($this->input->post('pincode'));

                    $loan_amount = intval($this->input->post('loan_applied'));
                    $obligations = intval($this->input->post('obligations'));
                    $monthly_income = intval($this->input->post('monthly_income'));

                    $pancard = trim(strtoupper($this->input->post('pancard')));

                    $gender = $this->input->post('gender');
                    $dob = date("Y-m-d", strtotime($this->input->post('dob')));
                    $mobile = $this->input->post('mobile');
                    $alternate_mobile = $this->input->post('alternate_mobile');

                    $tenure = $this->input->post('loan_tenure');
                    $loan_purpose = $this->input->post('loan_purpose');
                    $salary_mode = $this->input->post('salary_mode');
                    $income_type = $this->input->post('income_type');
                    $aadhar = $this->input->post('aadhar');
                    $marital_status = $this->input->post('customer_marital_status_id');
                    $qualification = $this->input->post('customer_qualification_id');
                    $spouse_name = $this->input->post('customer_spouse_name');
                    $spouse_occupation = $this->input->post('customer_spouse_occupation_id');

                    if (!empty($lead_pancard) && !empty($pancard) && $lead_pancard != $pancard) {
                        $json['err'] = "Pancard number change is not allowed.";
                        echo json_encode($json);
                        return false;
                    }
                    $conditions = ['customer_lead_id' => $lead_id];
                    $update_lead_customer = [
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'sur_name' => $sur_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'pancard' => $pancard,
                        'mobile' => $mobile,
                        'alternate_mobile' => $alternate_mobile,
                        'email' => $email,
                        'alternate_email' => $alternate_email,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'customer_religion_id' => $religion_id,
                        'cr_residence_pincode' => $pincode,
                        'aadhar_no' => $aadhar,
                        'customer_marital_status_id' => $marital_status,
                        'customer_qualification_id' => $qualification,
                        'customer_spouse_name' => $spouse_name,
                        'customer_spouse_occupation_id' => $spouse_occupation,
                        'updated_at' => date("Y-m-d H:i:s")
                    ];


                    $result = $this->Tasks->globalUpdate($conditions, $update_lead_customer, 'lead_customer');
                    // print_r($result); die;

                    $application_no = $this->Tasks->generateApplicationNo($lead_id);

                    if (empty($application_no)) {
                        $json['err'] = "Failed to generate Application No.";
                        echo json_encode($json);
                        return false;
                    }

                    $branch_data = $this->Tasks->getBranchDetails($city_id);
                    $lead_branch_id = (($branch_data['status'] == 1) ? $branch_data['branch_data']['m_branch_id'] : 0);
                    $update_lead_data = [
                        'customer_id' => $customer_id,
                        'first_name' => $first_name,
                        'mobile' => $mobile,
                        'application_no' => $application_no,
                        'email' => $email,
                        'alternate_email' => $alternate_email,
                        'pancard' => $pancard,
                        'loan_amount' => $loan_amount,
                        'tenure' => $tenure,
                        'purpose' => $loan_purpose,
                        'state_id' => $city_state_id,
                        'city_id' => $city_id,
                        'lead_branch_id' => $lead_branch_id,
                        'pincode' => $pincode,
                        'obligations' => $obligations,
                        'application_status' => 1,
                        'updated_on' => date("Y-m-d H:i:s")
                    ];

                    $conditions2 = ['lead_id' => $lead_id];

                    $result = $this->Tasks->globalUpdate($conditions2, $update_lead_data, 'leads');

                    $empDetails = $this->Tasks->select($conditions2, 'lead_id', 'customer_employment');

                    if ($empDetails->num_rows() > 0) {

                        $insert_customer_employment = [
                            'customer_id' => $customer_id,
                            'monthly_income' => $monthly_income,
                            'salary_mode' => $salary_mode,
                            'income_type' => $income_type,
                            'updated_on' => date("Y-m-d H:i:s"),
                            'emp_email' => $alternate_email,
                            'emp_updated_by' => $_SESSION['isUserSession']['user_id']
                        ];

                        $this->Tasks->globalUpdate($conditions2, $insert_customer_employment, 'customer_employment');
                    } else {

                        $update_customer_employment = [
                            'customer_id' => $customer_id,
                            'lead_id' => $lead_id,
                            'monthly_income' => $monthly_income,
                            'salary_mode' => $salary_mode,
                            'income_type' => $income_type,
                            'emp_email' => $alternate_email,
                            'created_on' => date("Y-m-d H:i:s"),
                            'emp_created_by' => $_SESSION['isUserSession']['user_id']
                        ];

                        $this->Tasks->insert($update_customer_employment, 'customer_employment');
                    }

                    if (empty($lead_reference_no)) {
                        $reference_no = $this->generateReferenceCode($lead_id, $first_name, $sur_name, $mobile);

                        $update_data_leads = [
                            'lead_reference_no' => $reference_no,
                            'updated_on' => date("Y-m-d H:i:s")
                        ];

                        $this->db->where('lead_id', $lead_id)->update('leads', $update_data_leads);
                    }

                    if ($result == true) {
                        $json['msg'] = "Lead Updated Successfully.";
                    } else {
                        $json['err'] = "Failed to Updated Customer Details.";
                    }
                    echo json_encode($json);
                } else {
                    $json['err'] = "Invalid Lead id.";
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function insertPersonal() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
            $this->form_validation->set_rules('sur_name', 'Surname', 'trim');
            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');

            $this->form_validation->set_rules('dob', 'DOB', 'required|trim');
            $this->form_validation->set_rules('pancard', 'Pancard', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim|exact_length[10]');
            $this->form_validation->set_rules('alternate_mobile', 'Alternate Mobile', 'trim|exact_length[10]');
            $this->form_validation->set_rules('email', 'Email', 'required|trim');

            $this->form_validation->set_rules('customer_marital_status_id', 'Marital Status', 'required|trim');
            $this->form_validation->set_rules('customer_religion_id', 'Religion', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
            } else {

                $lead_id = $this->input->post('lead_id');
                $pancard = trim(strtoupper($this->input->post('pancard')));

                $leadDetails = $this->Tasks->select(['lead_id' => $lead_id], 'lead_id, lead_reference_no, pancard', 'leads');

                if ($leadDetails->num_rows() > 0) {

                    $leadDetails = $leadDetails->row();

                    $lead_pancard = trim(strtoupper($leadDetails->pancard));

                    if (!empty($lead_pancard) && !empty($pancard) && $lead_pancard != $pancard) {
                        $json['err'] = "Pancard number change is not allowed.";
                    } else {

                        $conditions = ['customer_lead_id' => $this->input->post('lead_id')];
                        $data = [
                            'first_name' => $this->input->post('first_name'),
                            'middle_name' => $this->input->post('middle_name'),
                            'sur_name' => $this->input->post('sur_name'),
                            'gender' => $this->input->post('gender'),
                            'dob' => date("Y-m-d", strtotime($this->input->post('dob'))),
                            'pancard' => $pancard,
                            'aadhar_no' => $this->input->post('aadhar_no'),
                            'mobile' => $this->input->post('mobile'),
                            'alternate_mobile' => $this->input->post('alternate_mobile'),
                            'email' => $this->input->post('email'),
                            'alternate_email' => $this->input->post('alternate_email'),
                            'customer_marital_status_id' => $this->input->post('customer_marital_status_id'),
                            'customer_religion_id' => $this->input->post('customer_religion_id')
                        ];

                        $result = $this->Tasks->globalUpdate($conditions, $data, $this->tbl_customer);

                        $data2 = [
                            'first_name' => $this->input->post('first_name'),
                            'mobile' => $this->input->post('mobile'),
                            'email' => $this->input->post('email'),
                            'alternate_email' => $this->input->post('alternate_email'),
                            'pancard' => $this->input->post('pancard'),
                        ];

                        $conditions2 = ['lead_id' => $lead_id];
                        $result = $this->Tasks->globalUpdate($conditions2, $data2, 'leads');
                        if ($result == true) {
                            if (($data['mobile'] != $data['alternate_mobile']) && ($data['email'] != $data['alternate_email'])) {
                                $json['msg'] = "Customer Details Updated Successfully.";
                            } else {
                                $json['err'] = "Customer detail can not be same with your existing mobile no. or email id.";
                            }
                        } else {
                            $json['err'] = "Failed to Updated Customer Details.";
                        }
                    }
                } else {
                    $json['err'] = "Failed to Updated Customer Details..";
                }
            }
        } else {
            $json['err'] = "Invalid Request";
        }
        echo json_encode($json);
    }

    public function insertResidence() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('hfBulNo1', 'Residence Address Line 1', 'required|trim');
            $this->form_validation->set_rules('lcss1', 'Residence Address Line 2', 'required|trim');
            $this->form_validation->set_rules('lankmark1', 'Residence Landmark', 'trim');
            $this->form_validation->set_rules('state1', 'Residence State', 'required|trim');
            $this->form_validation->set_rules('city1', 'Residence City', 'required|trim');
            $this->form_validation->set_rules('pincode1', 'Residence Pincode', 'required|trim');
            $this->form_validation->set_rules('district1', 'Residence District', 'trim');
            $this->form_validation->set_rules('res_aadhar', 'Aadhaar', 'required|trim');
            $this->form_validation->set_rules('addharAddressSameasAbove', 'Is aadhaar address same as residence address', 'trim');

            $this->form_validation->set_rules('hfBulNo2', 'Aadhaar Address Line 1', 'required|trim');
            $this->form_validation->set_rules('lcss2', 'Aadhaar Address Line 2', 'trim');
            $this->form_validation->set_rules('landmark2', 'Aadhaar Landmark', 'trim');
            $this->form_validation->set_rules('state2', 'Aadhaar State', 'required|trim');
            // $this->form_validation->set_rules('city2', 'Aadhaar City', 'required|trim');
            // $this->form_validation->set_rules('pincode2', 'Aadhaar Pincode', 'required|trim');
            $this->form_validation->set_rules('district2', 'Aadhaar District', 'trim');
            $this->form_validation->set_rules('presentResidenceType', 'Present Residence Type', 'required|trim');
            $this->form_validation->set_rules('residenceSince', 'Residence Since', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
            } else {

                $lead_id = $this->input->post('lead_id');
                $lead_id =  $this->encrypt->decode($lead_id);

                $lead_update = array();
                $lead_branch_id = 0;
                $conditions = ['C.customer_lead_id' => $lead_id];

                $scm_conf = $this->input->post('district2');
                $state1 = $this->input->post('state1');
                $city1 = $this->input->post('city1');
                $city_id = $city1;
                $pincode1 = $this->input->post('pincode1');

                $dataResidence = [
                    'current_house' => $this->input->post('hfBulNo1'),
                    'current_locality' => $this->input->post('lcss1'),
                    'current_landmark' => $this->input->post('lankmark1'),
                    'current_state' => $state1,
                    'current_city' => $city1,
                    'state_id' => $state1,
                    'city_id' => $city1,
                    'cr_residence_pincode' => $pincode1,
                    'current_district' => $this->input->post('district1'),
                    'aadhar_no' => $this->input->post('res_aadhar'),
                    'aa_same_as_current_address' => $this->input->post('addharAddressSameasAbove'),
                    'aa_current_house' => $this->input->post('hfBulNo2'),
                    'aa_current_locality' => $this->input->post('lcss2'),
                    'aa_current_landmark' => $this->input->post('landmark2'),
                    'aa_current_state' => $this->input->post('state2'),
                    'aa_current_city' => $this->input->post('city2'),
                    'aa_current_state_id' => $this->input->post('state2'),
                    'aa_current_city_id' => $this->input->post('city2'),
                    'aa_cr_residence_pincode' => $this->input->post('pincode2'),
                    'aa_current_district' => $this->input->post('district2'),
                    'current_residence_type' => $this->input->post('presentResidenceType'),
                    'current_residence_since' => date('Y-m-d', strtotime($this->input->post('residenceSince')))
                ];



                $result = $this->Tasks->globalUpdate($conditions, $dataResidence, $this->tbl_customer);


                $conditionsLead = ['lead_id' => $lead_id];


                $fetchLeadsData = 'state_id, city_id, pincode';
                $leadsQuery = $this->Tasks->select($conditionsLead, $fetchLeadsData, 'leads');
                $leadsData = $leadsQuery->row();



                $branch_data = $this->Tasks->getBranchDetails($city_id);
                $lead_branch_id = (($branch_data['status'] == 1) ? $branch_data['branch_data']['m_branch_id'] : 0);

                if (empty($lead_branch_id)) {
                    $json['err'] = "Branch does not mapped with City.";
                    echo json_encode($json);
                    return false;
                }

                $lead_update['check_cibil_status'] = 0;
                $lead_update['state_id'] = $state1;
                $lead_update['city_id'] = $city1;
                $lead_update['pincode'] = $pincode1;
                $lead_update['lead_branch_id'] = $lead_branch_id;

                $result2 = $this->Tasks->globalUpdate($conditionsLead, $lead_update, 'leads');

                //                    }
                if ($result == true && $result2 == true) {
                    $json['msg'] = "Residence Details Updated Successfully.";
                } else {
                    $json['err'] = "Failed to Updated Residence Details.";
                }
            }
            echo json_encode($json);
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function insertEmployment() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('officeEmpName', 'Office/ Employer Name', 'required|trim');
            $this->form_validation->set_rules('hfBulNo3', 'Office Address Line 1', 'required|trim');
            $this->form_validation->set_rules('lcss3', 'Office Address Line 2', 'required|trim');
            $this->form_validation->set_rules('lankmark3', 'Office Address Landmark', 'trim');
            $this->form_validation->set_rules('state3', 'Office State', 'required|trim');
            $this->form_validation->set_rules('city3', 'Office City', 'required|trim');
            $this->form_validation->set_rules('pincode3', 'Office Pincode', 'required|trim');
            $this->form_validation->set_rules('district3', 'District', 'trim');
            $this->form_validation->set_rules('emp_website', 'Website', 'trim');
            $this->form_validation->set_rules('employeeType', 'Employee Type', 'required|trim');
            $this->form_validation->set_rules('industry', 'Industry', 'trim');
            $this->form_validation->set_rules('sector', 'Sector', 'trim');
            $this->form_validation->set_rules('department', 'Department', 'trim');
            $this->form_validation->set_rules('designation', 'Designation', 'trim');
            $this->form_validation->set_rules('emp_work_mode', 'Work Mode', 'required|trim');
            $this->form_validation->set_rules('employedSince', 'Employed Since', 'required|trim');
            $this->form_validation->set_rules('presentServiceTenure', 'Present Service Tenure', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
            } else {
                $employment_data = array();

                $lead_id = $this->input->post('lead_id');
                //$lead_id =  $this->encrypt->decode($lead_id);
                // print_r($lead_id); die;
                $conditions = ['CE.lead_id' => $lead_id];

                $employedSince = $this->input->post('employedSince');

                $date_diff = abs(strtotime(date('d-m-Y')) - strtotime($employedSince));
                $years = floor($date_diff / (365 * 60 * 60 * 24));
                $presentServiceTenure = floor(($date_diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                $employment_data['lead_id'] = $lead_id;
                $employment_data['employer_name'] = $this->input->post('officeEmpName');
                $employment_data['emp_house'] = $this->input->post('hfBulNo3');
                $employment_data['emp_street'] = $this->input->post('lcss3');
                $employment_data['emp_landmark'] = $this->input->post('lankmark3');
                $employment_data['emp_state'] = $this->input->post('state3');
                $employment_data['state_id'] = $this->input->post('state3');
                $employment_data['city_id'] = $this->input->post('city3');
                $employment_data['emp_pincode'] = $this->input->post('pincode3');
                $employment_data['emp_website'] = $this->input->post('emp_website');
                $employment_data['emp_employer_type'] = $this->input->post('employeeType');
                $employment_data['emp_department'] = $this->input->post('department');
                $employment_data['emp_designation'] = $this->input->post('designation');
                $employment_data['emp_work_mode'] = $this->input->post('emp_work_mode');
                $employment_data['emp_residence_since'] = date('Y-m-d', strtotime($employedSince));
                $employment_data['presentServiceTenure'] = $presentServiceTenure;
                $employment_data['emp_status'] = "YES";

                $fetch2 = "CE.lead_id";

                $employmentDetails = $this->Tasks->select($conditions, $fetch2, $this->tbl_customer_employment);


                if ($employmentDetails->num_rows() == 0) {
                    $result = $this->Tasks->insert($employment_data, 'customer_employment');
                } else {
                    $result = $this->Tasks->globalUpdate($conditions, $employment_data, $this->tbl_customer_employment);
                    //   print_r($employment_data);
                }
                if ($result == true) {
                    $json['msg'] = "Employment Details Added Successfully.";
                } else {
                    $json['err'] = "Failed to Updated Employment Details.";
                }
            }
            echo json_encode($json);
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function insertReference() {

        $currentdate = date('Y-m-d H:i:s');
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('refrence1', 'Reference Name', 'required|trim');
            $this->form_validation->set_rules('relation1', 'Relation Type', 'required|trim');
            $this->form_validation->set_rules('refrence1mobile', 'Mobile', 'required|trim|exact_length[10]');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $lead_id = $this->input->post('lead_id');
                $lead_id = $this->encrypt->decode($lead_id);
                $reference_name = $this->input->post('refrence1');
                $reference_relation = $this->input->post('relation1');
                $reference_mobile = trim($this->input->post('refrence1mobile'));

                $dataRefrence = [
                    'lcr_lead_id' => $lead_id,
                    'lcr_name' => $reference_name,
                    'lcr_relationType' => $reference_relation,
                    'lcr_mobile' => $reference_mobile,
                    'lcr_created_on' => $currentdate,
                    'lcr_created_by' => $_SESSION['isUserSession']['user_id']
                ];
                $where = " WHERE customer_lead_id =$lead_id AND (mobile='$reference_mobile' OR alternate_mobile='$reference_mobile')";

                $totalExistMobile = getCouts('lead_customer', $where);

                $sql = "SELECT lcr_mobile FROM lead_customer_references WHERE lcr_lead_id = ? AND lcr_mobile = ? AND lcr_active=1 and lcr_deleted=0 ";
                $query = $this->db->query($sql, [$lead_id, $reference_mobile]);

                if ($query->num_rows() > 0) {
                    $json['err'] = "Duplicate mobile number cannot be entered.";
                    echo json_encode($json);
                    return;
                }

                if (!empty($totalExistMobile)) {
                    $json['err'] = "Customer mobile or alternate mobile number cannot be entered.";
                    echo json_encode($json);
                    return;
                }

                $where = " where lcr_lead_id ='$lead_id'  and ( lcr_active=1 and lcr_deleted=0 )";

                $totalcount = getCouts('lead_customer_references', $where);
                $totalcount = intval($totalcount);

                $b = 5;

                if ($b >= $totalcount) {

                    $result = $this->Leadmod->globel_inset('lead_customer_references', $dataRefrence); //die;

                    if ($result) {

                        $json['msg'] = "Reference added successfully.";
                        echo json_encode($json);
                    } else {
                        $json['err'] = "Failed to added reference details.";
                        echo json_encode($json);
                    }
                } else {
                    $json['err'] = "Only 5 references allowed.";
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function updateReference() {

        $currentdate = date('Y-m-d H:i:s');
        if ($this->input->post('upd_user_id') == "") {

            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('upd_refrence1', 'refrence Name', 'required|trim');
            $this->form_validation->set_rules('upd_relation1', 'Relation Type', 'required|trim');
            $this->form_validation->set_rules('upd_refrence1mobile', 'Mobile', 'required|trim|exact_length[10]');

            if ($this->form_validation->run() == FALSE) {

                $json['err'] = validation_errors();
            } else {

                $lead_id = $this->input->post('upd_lead_id');

                $dataRefrence = [
                    'lcr_name' => $this->input->post('upd_refrence1'),
                    'lcr_relationType' => $this->input->post('upd_relation1'),
                    'lcr_mobile' => $this->input->post('upd_refrence1mobile'),
                    'lcr_updated_on' => $currentdate,
                    'lcr_udpated_by' => $_SESSION['isUserSession']['user_id']
                ];

                $result = $this->Leadmod->globel_update('lead_customer_references', $dataRefrence, $lead_id, 'lcr_id');

                if ($result == '1') {
                    $json['msg'] = "Reference Updated Successfully.";
                    echo json_encode($json);
                } else {
                    $json['err'] = "Failed to Updated Reference Details.";
                    echo json_encode($json);
                }
            }
        } else {

            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function deleteData() {
        $post = $_POST['data'];
        $id = $post['lead_id'];

        $dataRefrence = [
            'lcr_active' => 0,
            'lcr_deleted' => 1,
            'lcr_updated_on' => date("Y-m-d H:i:s"),
        ];

        $result = $this->Leadmod->globel_update('lead_customer_references', $dataRefrence, $id, 'lcr_id');
        if ($result == '1') {
            $json['msg'] = "Refrence Delete Successfully.";

            echo json_encode($json);
        } else {
            $json['err'] = "Failed to Delete Refrence Details.";
            echo json_encode($json);
        }
    }

    public function saveCustomerPersonalDetails() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('borrower_name', 'Borrower Name', 'required|trim');
            $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
            $this->form_validation->set_rules('dob', 'DOB', 'required|trim');
            $this->form_validation->set_rules('pancard', 'PAN', 'required|trim');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim|exact_length[10]');
            $this->form_validation->set_rules('alternate_no', 'Alternate Mobile', 'trim|exact_length[10]');
            // $this->form_validation->set_rules('alternateEmail', 'Alternate Email Id', 'required|trim');
            $this->form_validation->set_rules('state', 'State', 'required|trim');
            $this->form_validation->set_rules('city', 'City', 'required|trim');
            $this->form_validation->set_rules('pincode', 'Pincode', 'required|trim');
            $this->form_validation->set_rules('aadhar', 'Aadhar', 'required|trim');
            $this->form_validation->set_rules('residentialType', 'Residence Type', 'required|trim');
            // $this->form_validation->set_rules('residential_proof', 'Residential Proof', 'required|trim');
            $this->form_validation->set_rules('residence_address_line1', 'Recidence Address Line 1', 'required|trim');
            $this->form_validation->set_rules('residence_address_line2', 'Recidence Address Line 2', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->input->post('leadID');
                $company_id = $this->input->post('company_id');
                $product_id = $this->input->post('product_id');
                $user_id = $this->input->post('user_id');
                $borrower_name = $this->input->post('borrower_name');
                $borrower_mname = $this->input->post('borrower_mname');
                $borrower_lname = $this->input->post('borrower_lname');
                $gender = $this->input->post('gender');
                $dob = $this->input->post('dob');
                $pancard = $this->input->post('pancard');
                $mobile = $this->input->post('mobile');
                $alternate_no = $this->input->post('alternate_no');
                $email = $this->input->post('email');
                $state = $this->input->post('state');
                $city = $this->input->post('city');
                $pincode = $this->input->post('pincode');
                $lead_initiated_date = $this->input->post('lead_initiated_date');
                $post_office = $this->input->post('post_office');
                $alternateEmail = $this->input->post('alternateEmail');
                $aadhar = $this->input->post('aadhar');
                $residentialType = $this->input->post('residentialType');

                $other_address_proof = $this->input->post('other_add_proof');
                $residential_proof = $this->input->post('residential_proof');
                $residence_address_line1 = $this->input->post('residence_address_line1');
                $residence_address_line2 = $this->input->post('residence_address_line2');

                $isPresentAddress = "NO";
                if ($this->input->post('isPresentAddress') == "YES") {
                    $isPresentAddress = $this->input->post('isPresentAddress');
                }

                $presentAddressType = $this->input->post('presentAddressType');
                $present_address_line1 = $this->input->post('present_address_line1');
                $present_address_line2 = $this->input->post('present_address_line2');
                $employer_business = $this->input->post('employer_business');
                $office_address = $this->input->post('office_address');
                $office_website = $this->input->post('office_website');

                $data = [
                    'company_id' => $company_id,
                    'product_id' => $product_id,
                    'lead_id' => $lead_id,
                    'borrower_name' => $borrower_name,
                    'middle_name' => $borrower_mname,
                    'surname' => $borrower_lname,
                    'gender' => $gender,
                    'dob' => $dob,
                    'pancard' => $pancard,
                    'mobile' => $mobile,
                    'alternate_no' => $alternate_no,
                    'email' => $email,
                    'alternateEmail' => $alternateEmail,
                    'state' => $state,
                    'city' => $city,
                    'pincode' => $pincode,
                    'lead_initiated_date' => $lead_initiated_date,
                    'post_office' => $post_office,
                    'aadhar' => $aadhar,
                    'residentialType' => $residentialType,
                    'other_address_proof' => $other_address_proof,
                    'residential_proof' => $residential_proof,
                    'residence_address_line1' => $residence_address_line1,
                    'residence_address_line2' => $residence_address_line2,
                    'isPresentAddress' => $isPresentAddress,
                    // 'presentAddressType' 		=> $presentAddressType,
                    'present_address_line1' => $present_address_line1,
                    'present_address_line2' => $present_address_line2,
                    'employer_business' => $employer_business,
                    'office_address' => $office_address,
                    'office_website' => $office_website,
                ];

                $status = ['status' => "IN PROCESS"];
                $updateLead = ['status' => "IN PROCESS", 'state_id' => $state, 'city' => $city];

                // 	$query1 = $this->db->select('count(customer_id) as total, customer_id')->where('pancard', $pancard)->from('customer')->get()->result();
                // 	if($result1[0]->total > 0) {
                // 	  	$customer_id = $result1[0]->customer_id;
                // 	}
                // 	else
                // 	{
                // 		$last_row = $this->db->select('customer.customer_id')->from('customer')->order_by('customer_id', 'desc')->limit(1)->get()->row();
                // 		$str = preg_replace('/\D/', '', $last_row->customer_id);
                // 		$customer_id= "FTC". str_pad(($str + 1), 6, "0", STR_PAD_LEFT);
                // 		$dataCustomer = array(
                // 			'customer_id'	=> $customer_id,
                // 			'name'			=> $borrower_name,
                // 			'email'			=> $email,
                // 			'alternateEmail'=> $alternateEmail,
                // 			'mobile'		=> $mobile,
                // 			'alternate_no'	=> $alternate_no,
                // 			'pancard'		=> $pancard,
                // 			'aadhar_no'		=> $aadhar,
                // 			'created_date'	=> updated_at
                // 		);
                // 		$this->db->insert('customer', $dataCustomer);
                // 	}

                $where = ['company_id' => $company_id, 'product_id' => $product_id];
                $sql = $this->db->where($where)->where('lead_id', $lead_id)->from('tbl_cam')->order_by('tbl_cam.cam_id', 'desc')->get();

                $row = $sql->row();

                if ($sql->num_rows() > 0) {
                    $insertDate = [
                        'usr_updated_by' => $user_id,
                        'usr_updated_at' => created_at,
                    ];
                    $data = array_merge($insertDate, $data);
                    $cam_id = $row->cam_id;
                    $result = $this->db->where('cam_id', $cam_id)->update('tbl_cam', $data);
                    $updateleads = $this->db->where($where)->where('lead_id', $lead_id)->update('leads', ["state_id" => $state, "city" => $city]);

                    $this->CAM->updateCAM($lead_id, $status);
                } else {
                    $insertDate = [
                        'lead_id' => $lead_id,
                        // 'customer_id' 				=> $customer_id,
                        'usr_created_by' => user_id,
                        'usr_created_at' => created_at,
                    ];
                    $data = array_merge($insertDate, $data);
                    $result = $this->db->insert('tbl_cam', $data);
                    $cam_id = $this->db->insert_id();

                    $this->Tasks->updateLeads($lead_id, $updateLead);
                    $this->CAM->updateCAM($lead_id, $status);
                }

                if ($result == 1) {
                    $json['msg'] = "Personal Details Updated Successfully.";
                    echo json_encode($json);
                } else {
                    $json['err'] = "Personal Details failed to Update.";
                    echo json_encode($json);
                }
            }
        }
    }

    public function LACLeadRecommendation() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('Active_CC', 'Active CC', 'required|trim');
            $this->form_validation->set_rules('cc_statementDate', 'CC Statement Date', 'required|trim');
            $this->form_validation->set_rules('cc_paymentDueDate', 'CC Payment Date', 'required|trim');
            $this->form_validation->set_rules('cc_paymentDueDate', 'CC Payment Date', 'required|trim');
            $this->form_validation->set_rules('customer_bank_name', 'CC Bank', 'required|trim');
            $this->form_validation->set_rules('account_type', 'CC Type', 'required|trim');
            $this->form_validation->set_rules('customer_account_no', 'CC No', 'required|trim');
            $this->form_validation->set_rules('customer_confirm_account_no', 'CC Confirm No', 'required|trim');
            $this->form_validation->set_rules('customer_name', 'CC User Name', 'required|trim');
            $this->form_validation->set_rules('cc_limit', 'CC Limit', 'required|trim');
            $this->form_validation->set_rules('cc_outstanding', 'CC Outstanding', 'required|trim');
            $this->form_validation->set_rules('cc_name_Match_borrower_name', 'CC Name Match Borrower Name', 'required|trim');
            $this->form_validation->set_rules('emiOnCard', 'EMI On Card', 'required|trim');
            $this->form_validation->set_rules('DPD30Plus', '30+ DPD In Last 3 Month', 'required|trim');
            $this->form_validation->set_rules('cc_statementAddress', 'CC Statement Address', 'required|trim');
            $this->form_validation->set_rules('last3monthDPD', 'Last 3 Month DPD', 'required|trim');
            $this->form_validation->set_rules('loan_recomended', 'Loan Recomended', 'required|trim');
            $this->form_validation->set_rules('processing_fee', 'Admin Fee', 'required|trim');
            $this->form_validation->set_rules('roi', 'ROI', 'required|trim');
            $this->form_validation->set_rules('disbursal_date', 'Disbursal Date', 'required|trim');
            $this->form_validation->set_rules('repayment_date', 'Repayment Date', 'required|trim');

            if ($this->input->post('isDisburseBankAC') == "YES") {
                $this->form_validation->set_rules('bankIFSC_Code', 'Bank IFSC Code', 'required|trim');
                $this->form_validation->set_rules('bank_name', 'Bank Name', 'required|trim');
                $this->form_validation->set_rules('bank_branch', 'Bank Branch', 'required|trim');
                $this->form_validation->set_rules('bankA_C_No', 'Bank A/C No', 'required|trim');
                $this->form_validation->set_rules('confBankA_C_No', 'Conf Bank A/C No', 'required|trim');
                $this->form_validation->set_rules('bankHolder_name', 'Bank Holder Name', 'required|trim');
                $this->form_validation->set_rules('bank_account_type', 'Bank A/C Type', 'required|trim');
            }

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->input->post('leadID');
                $statusCam = ['status' => "RECOMMEND"];
                $statusLeads = ['status' => "RECOMMEND", "screener_status" => 4];
                $this->Tasks->updateLeads($lead_id, $statusLeads);
                $this->CAM->updateCAM($lead_id, $statusCam);
                $json['msg'] = "Lead Recomendation Done.";
                echo json_encode($json);
            }
        }
    }

    public function PaydayLeadRecommendation() {

        //         error_reporting(E_ALL);
        // ini_set("display_errors", 1);

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        // if ($this->input->server('REQUEST_METHOD') == 'POST') {
        $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
        // $this->form_validation->set_rules('customer_id', 'Company ID', 'required|trim');
        //             $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
        //             $this->form_validation->set_rules('loan_recommended', 'Loan Recommended', 'required|trim');
        //             $this->form_validation->set_rules('admin_fee', 'Admin Fee', 'required|trim');
        //             $this->form_validation->set_rules('roi', 'ROI', 'required|trim');
        //             $this->form_validation->set_rules('disbursal_date', 'Disbursal Date', 'required|trim');
        //             $this->form_validation->set_rules('repayment_date', 'Repayment Date', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            $json['err'] = validation_errors();
            echo json_encode($json);
        } else {
            $update_lead_data = array();
            $insert_lead_followup = array();

            $lead_id = $this->input->post('lead_id');

            $lead_id = $this->encrypt->decode($_POST['lead_id']);

            $leadsDetails = $this->Tasks->select(['lead_id' => $lead_id], 'first_name, email, mobile, lead_status_id, audit_send_back', 'leads');
            if ($leadsDetails->num_rows() > 0) {

                $leadsDetails = $leadsDetails->row();

                if (!in_array($leadsDetails->lead_status_id, array(5, 6, 11))) {
                    $json['err'] = "Invalid Access";
                    echo json_encode($json);
                    return false;
                }
            }

            // $leadCustomerDetails = $this->Tasks->select(['customer_lead_id' => $lead_id], 'customer_bre_run_flag', 'lead_customer');

            // if ($leadCustomerDetails->num_rows() > 0 && false) {

            //     $leadCustomerDetails = $leadCustomerDetails->row();

            //     if (!in_array($leadCustomerDetails->customer_bre_run_flag, array(1))) {
            //         $json['err'] = "Please run the BRE.";
            //         echo json_encode($json);
            //         return false;
            //     }
            // }

            $leadCustomerDetails = $this->Tasks->select(['customer_lead_id' => $lead_id], 'customer_bre_run_flag, alternate_mobile, customer_digital_ekyc_flag', 'lead_customer');

            $leadCustomerDetails = $leadCustomerDetails->row();

            if ($leadCustomerDetails->customer_bre_run_flag == 0) {
                $json['err'] = "Please run the BRE.";
                echo json_encode($json);
                return false;
            }
            // print_r($leadCustomerDetails);
            // exit;

            // if ($leadCustomerDetails->customer_digital_ekyc_flag == 0) {
            //     $json['err'] = "Digital E-KYC is not done.";
            //     echo json_encode($json);
            //     return false;
            // }

            // $bankVerificationDetails = $this->Tasks->select(['bav_lead_id' => $lead_id], 'bav_api_status_id,bav_errors', 'api_bank_account_verification_logs');

            // $bankVerificationDetails = $bankVerificationDetails->row();

            // if ($bankVerificationDetails->bav_api_status_id != 1) {
            //     $json['err'] = "Run penny drop before recommending.";
            //     echo json_encode($json);
            //     return false;
            // }

            $bankStatementDetails = $this->Tasks->select(['cart_lead_id' => $lead_id], 'cart_api_status_id,cart_errors', 'api_banking_cart_log');

            $bankStatementDetails = $bankStatementDetails->row();

            if ($bankStatementDetails->cart_api_status_id != 1) {
                $json['err'] = "Run bank statement analyser before recommending.";
                echo json_encode($json);
                return false;
            }

            $conditions = ['company_id' => company_id, 'product_id' => product_id, 'lead_id' => $lead_id];
            $fetch = 'CAM.cam_id, CAM.remark,CAM.loan_recommended,CAM.admin_fee,CAM.roi,CAM.disbursal_date,CAM.repayment_date';
            $sql = $this->Tasks->select($conditions, $fetch, $this->tbl_cam);

            if ($sql->num_rows() > 0) {

                $camDetails = $sql->row();

                $breRuleResult = $this->Tasks->select(['lbrr_lead_id' => $lead_id, 'lbrr_active' => 1], 'lbrr_id,lbrr_rule_manual_decision_id', 'lead_bre_rule_result');

                if (empty($camDetails->loan_recommended)) {
                    $json['err'] = "Missing Loan Recommend Amount";
                    echo json_encode($json);
                } else if (empty($camDetails->admin_fee) && false) {
                    $json['err'] = "Missing Loan Admin Fee Amount";
                    echo json_encode($json);
                } else if (empty($camDetails->roi)) {
                    $json['err'] = "Missing Loan ROI";
                    echo json_encode($json);
                } else if (empty($camDetails->disbursal_date)) {
                    $json['err'] = "Missing Loan Disbursal Date";
                    echo json_encode($json);
                } else if (empty($leadCustomerDetails->alternate_mobile)) {
                    $json['err'] = "Alternate Mobile Number Missing";
                    echo json_encode($json);
                    return false;
                } else if (empty($camDetails->repayment_date)) {
                    $json['err'] = "Missing Loan Repayment Date";
                    echo json_encode($json);
                } else if ($leadCustomerDetails->customer_digital_ekyc_flag = 0) {
                    $json['err'] = "Missing Digital E-KYC";
                    echo json_encode($json);
                } else if ($breRuleResult->num_rows() <= 0 && false) {
                    $json['err'] = "Please run bre to process the case.";
                    echo json_encode($json);
                } else {

                    $breRuleResultArray = $breRuleResult->result_array();

                    foreach ($breRuleResultArray as $breResultData) {

                        if ($breResultData['lbrr_rule_manual_decision_id'] == 2) {
                            $json['err'] = "Please take the decision for refer rule.";
                            echo json_encode($json);
                            return;
                        }

                        if ($breResultData['lbrr_rule_manual_decision_id'] == 3) {
                            $json['err'] = "This case cannot move forward as policy is rejected";
                            echo json_encode($json);
                            return;
                        }
                    }

                    if (isset($leadsDetails->audit_send_back) && $leadsDetails->audit_send_back == 1) {
                        $status = "AUDIT-INPROCESS";
                        $stage = "S32";
                        $lead_status_id = 45;
                    } else {
                        $status = "APPLICATION-RECOMMENDED";
                        $stage = "S10";
                        $lead_status_id = 10;
                    }

                    $update_lead_data['status'] = $status;
                    $update_lead_data['stage'] = $stage;
                    $update_lead_data['lead_status_id'] = $lead_status_id;
                    $update_lead_data['lead_credit_recommend_datetime'] = date("Y-m-d H:i:s");
                    $update_lead_data['updated_on'] = date("Y-m-d H:i:s");

                    if ($leadsDetails->audit_send_back == 1) {
                        unset($update_lead_data['lead_credit_recommend_datetime']);
                    }

                    $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');

                    $insert_lead_followup['lead_id'] = $lead_id;
                    $insert_lead_followup['user_id'] = $_SESSION['isUserSession']['user_id'];
                    $insert_lead_followup['status'] = $status;
                    $insert_lead_followup['stage'] = $stage;
                    $insert_lead_followup['lead_followup_status_id'] = $lead_status_id;
                    $insert_lead_followup['remarks'] = $camDetails->remark;
                    $insert_lead_followup['created_on'] = date("Y-m-d H:i:s");

                    $this->Tasks->insert($insert_lead_followup, 'lead_followup');

                    $json['msg'] = "Lead Recommend Done.";
                    echo json_encode($json);
                }
                // } else {
                //     $json['err'] = 'Failed to recommend Leads.';
                //     echo json_encode($json);
                // }
            }
        }
    }

    public function validateCustomerPersonalDetails() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('employeeType', 'Employee Type', 'required|trim');
            $this->form_validation->set_rules('dateOfJoining', 'Date Of Joining', 'required|trim');
            $this->form_validation->set_rules('designation', 'Designation', 'required|trim');
            $this->form_validation->set_rules('currentEmployer', 'Current Employer', 'required|trim');
            $this->form_validation->set_rules('companyAddress', 'Company Address', 'required|trim');
            $this->form_validation->set_rules('otherDetails', 'Other Details', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['error'] = validation_errors();
                echo json_encode($json);
            } else {
                $data = array(
                    'lead_id' => $lead_id,
                    'employeeType' => $this->input->post('employeeType'),
                    'dateOfJoining' => $this->input->post('dateOfJoining'),
                    'designation' => $this->input->post('designation'),
                    'currentEmployer' => $this->input->post('currentEmployer'),
                    'companyAddress' => $this->input->post('companyAddress'),
                    'otherDetails' => $this->input->post('otherDetails'),
                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                );
                $result = $this->db->insert('tbl_customerEmployeeDetails', $data);
                $this->db->where('lead_id', $lead_id)->update('leads', ['employeeDetailsAdded' => 1]);
                echo json_encode($result);
            }
        }
    }

    public function quickCallLeadId() {
        $arr = $_POST['data'];
        $totalId = implode(",", $arr);
        $postsize = sizeof($_POST['data']);

        $getSize = $this->Leadmod->selectdata('leads', $totalId);
        if ($postsize == $getSize) {

            foreach ($_POST['data'] as $key => $value) {

                $data = array('quick_call_type' => 1);
                $this->load->helper('integration/payday_quick_call_api');
                $return_array = payday_quickcall_api_call("LEAD_PUSH", $value, $data);
                // echo "<pre>";print_r($return_array);
                if (empty($empty_array)) {
                    echo "false";
                } else {
                    echo "true";
                }
            }
        }
    }

    public function getLeadHistoryLogs($lead_id) {

        $lead_id = $this->encrypt->decode($lead_id);
        $leadData = $this->Tasks->getLeadLogs($lead_id);

        $data = '<div class="table-responsive">
		    <table class="table table-hover table-striped table-bordered">
                  	<thead>
                        <tr class="table-primary">
                            <th class="whitespace">Log&nbsp;Date</th>
                            <th class="whitespace">Status</th>
                            <th class="whitespace">User&nbsp;Name</th>
                            <th class="whitespace">Lead Remarks</th>
                        </tr>
                  	</thead>';
        if (!empty($leadData) && $leadData->num_rows() > 0) {
            $i = 1;
            foreach ($leadData->result() as $colum) {

                if (!empty($colum->reason)) {
                    $remarks = $colum->reason . "<br/>" . $colum->remarks;
                } else {
                    $remarks = $colum->remarks;
                }

                $data .= '<tbody>
                            <tr>
                                <td class="whitespace">' . (($colum->created_on) ? date("d-m-Y H:i:s", strtotime($colum->created_on)) : '-') . '</th>
                                <td class="whitespace">' . (($colum->status_name) ? $colum->status_name : '-') . '</td>
                                <td class="whitespace">' . (($colum->name) ? $colum->name : '-') . '</td>
                                <td class="whitespace">' . (($remarks) ? $remarks : '-') . '</td>
                            </tr>';
                $i++;
            }
        } else {
            $data .= '<tbody><tr><td colspan="16" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        echo json_encode($data);
    }

    public function getSanctionFollowupLogs($lead_id) {

        $leadData = $this->Tasks->getSanctionFollowupLogs($lead_id);

        $data = '<div class="table-responsive">
		    <table class="table table-hover table-striped table-bordered">
                  	<thead>
                        <tr class="table-primary">
                            <th class="whitespace">Log&nbsp;Date</th>
                            <th class="whitespace">User&nbsp;Name</th>
                            <th class="whitespace">Log&nbsp;Type</th>
                            <th class="whitespace">Status</th>
                            <th class="whitespace">User Remarks</th>
                        </tr>
                  	</thead>';

        if (!empty($leadData) && $leadData->num_rows() > 0) {
            $i = 1;
            foreach ($leadData->result() as $colum) {

                $data .= '<tbody>
                            <tr>
                                <td class="whitespace">' . (($colum->lsf_created_on) ? date("d-m-Y H:i:s", strtotime($colum->lsf_created_on)) : '-') . '</th>
                                <td class="whitespace">' . (($colum->name) ? $colum->name : '-') . '</td>
                                <td class="whitespace">RUNO Call</th>
                                <td class="whitespace">' . (($colum->m_sf_status_name) ? $colum->m_sf_status_name : '-') . '</td>
                                <td class="whitespace">' . (($colum->lsf_remarks) ? $colum->lsf_remarks : '-') . '</td>
                            </tr>';
                $i++;
            }
        } else {
            $data .= '<tbody><tr><td colspan="5" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        echo json_encode($data);
    }

    public function sentRepaymentReminderOnMail() {
        die;
        $message_type = 'EMAIL_REMINDER';
        $SmsRow = $this->Tasks->getRepaymentReminderSend($message_type);

        foreach ($SmsRow as $return_sms) {

            if ($return_sms['lead_status_id'] != 14 && $return_sms['lead_status_id'] != 19) {
                $return_sms['status'] = 0;
                $return_sms['msg'] = "Not Disburse Status!";
                echo json_encode($return_sms);
                die;
            }

            $firstname = $return_sms['first_name'];
            $lead_id = $return_sms['lead_id'];
            //$email_to = $return_sms['email'];
            // $email_to = 'tech.lead@salarywalle.com';
            $email_to = 'sunny@fintechbasket.com';
            $mobile = $return_sms['mobile'];
            $address = $return_sms['current_house'];
            $loan_recommended = $return_sms['loan_recommended'];
            $loan_no = $return_sms['loan_no'];
            $repayment_amount = $return_sms['repayment_amount'];
            $repayment_date = $return_sms['repayment_date'];
            $days_until_repayment = $return_sms['days_until_repayment'];
            $insertReminderAttention = array(
                'lead_id' => $lead_id,
                'title' => "Reminder Payment Attention On Mail",
                'message_type' => "EMAIL_REMINDER",
                'created_on' => date('Y-m-d'),
                'status' => 1
            );

            if (isset($days_until_repayment) && $days_until_repayment >= 1 && $days_until_repayment <= 7) {
                $subject = "Reminder Payment Attention";
                $message = '<!DOCTYPE html><html lang="en">
					<head>
						<style> body {
							font-family: Arial, Helvetica, sans-serif;
							line-height: 1.6;
							background-color: #f4f4f4;
							margin: 0;
							padding: 0;
							color:#222;
						} .container {
							margin: 0px auto;
							padding: 0px;
							box-shadow: 0 0 10px rgba(0,0,0,0.1);
						} section.legal-notice {
							padding: 20px;
						}
					</style>
				</head>
				<body>
					<div class="container">
						<section class="legal-notice">
							<h2 style="color:#000;">Dear Customer,</h2>
							<p  style="color:#000;">This is a friendly reminder that your outstanding loan amount is <b>' . $repayment_amount . '</b>. <br>
							Please note that you have <b>' . $days_until_repayment . '</b> days left until your repayment date. </p>
							<p style="color:#000;">Thank you for your attention to this matter.</p>
							<p>Best regards,</p>
							<b>' . COMPANY_NAME . '</b><br>
							<b>' . REGISTED_MOBILE . '</b><br>
							<b>' . BRAND_NAME . '</b>

						</section>
					</div>
				</body>
				</html>';


                /* require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==7)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==6)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==5)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else  if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==4)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else  if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==3)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else  if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==2)){
                    $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else if(isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment']==1)){
                     $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
                }else{
                    echo 'Testing Wrong';
                } */
                $this->db->insert('customer_msg_reminder', $insertReminderAttention);
                //echo "<pre>";
                //print_r($insertReminderAttendence); echo "</pre><br/>";
            } else {
                echo 'Mail reminder Allready sent';
            }
        }
    }

    public function sentRepaymentReminderOnSMS() {

        $message_type = 'SMS_REMINDER';
        $SmsRow = $this->Tasks->getRepaymentReminderSend($message_type);
        foreach ($SmsRow as $return_sms) {

            if ($return_sms['lead_status_id'] != 14 && $return_sms['lead_status_id'] != 19) {
                $return_sms['status'] = 0;
                $return_sms['msg'] = "Not Disburse Status!";
                echo json_encode($return_sms);
                die;
            }

            $firstname = $return_sms['first_name'];
            $lead_id = $return_sms['lead_id'];
            //$email_to = $return_sms['email'];
            $mobile = $return_sms['mobile'];
            //$mobile = '9716763608';
            $address = $return_sms['current_house'];
            $loan_recommended = $return_sms['loan_recommended'];
            $loan_no = $return_sms['loan_no'];
            $repayment_amount = $return_sms['repayment_amount'];
            $repayment_date = $return_sms['repayment_date'];
            $days_until_repayment = $return_sms['days_until_repayment'];



            if (isset($days_until_repayment) && $days_until_repayment >= 1 && $days_until_repayment <= 7) {

                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();

                $sms_input_data = array(
                    'mobile' => $mobile,
                    'first_name' => $firstname,
                    'repayment_amount' => $repayment_amount,
                    'loan_no' => $loan_no,
                    'repayment_date' => $repayment_date
                );

                $sms_veri_return = $CommonComponent->payday_sms_api(2, $lead_id, $sms_input_data);
                // print_r($sms_veri_return);
                $insertReminderAttention = array(
                    'lead_id' => $lead_id,
                    'title' => "Reminder Payment Attention On SMS",
                    'created_on' => date('Y-m-d'),
                    'message_type' => "SMS_REMINDER",
                    'status' => 1
                );

                $this->db->insert('customer_msg_reminder', $insertReminderAttention);
            } else {
                echo 'SMS Allready Sent Successfully';
            }
        }
    }


    public function update_unpaidRepeat() {

        if ($this->input->server('REQUEST_METHOD') == 'GET') {

            // Check if 'lead_id' is missing or empty
            $leadId = $this->input->get('lead_id');
            $lead_id = intval($this->encrypt->decode($leadId));
            if (!isset($lead_id) || empty($lead_id)) {
                $json['err'] = 'lead_id required.';
                echo json_encode($json);
                return;
            } else {
                $sql = $this->db->select('a.lead_id, a.user_type, a.status, a.pancard')
                    ->from('leads a')
                    ->join('leads b', 'a.pancard = b.pancard', 'INNER')
                    ->where('b.lead_id', $lead_id)
                    ->where('a.user_type', 'UNPAID-REPEAT')
                    ->get();

                $result = $sql->result_array();
                if (isset($result[0]['lead_id']) && $result[0]['lead_id'] > 0) {
                    $sql = $this->db->select('a.lead_id')
                        ->from('leads a')
                        ->where('a.pancard', $result[0]['pancard'])
                        ->where_in('a.lead_status_id', [13, 14, 19])
                        ->get();

                    $row = $sql->row();
                    if (count($row) > 0) {
                        $json['err'] = "Failed to Update.";
                        $json['row'] = $row;
                        echo json_encode($json);
                        die;
                    } else {
                        $updateLead = ['user_type' => 'REPEAT'];
                        $id = $this->db->where('lead_id', $result[0]['lead_id'])->update('leads', $updateLead);
                    }
                }


                if (!empty($id)) {
                    $json['msg'] = "Updated Successfully.";
                } else {
                    $json['err'] = "Failed to Update.";
                    $json['result'] = $result;
                }
                echo json_encode($json);
            }
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function auditNew() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $this->sendJsonResponse(['errSession' => "Session Expired"]);
            return;
        }

        $lead_id = $this->input->post('lead_id');
        if (!empty($lead_id)) {
            $lead_id = $this->encrypt->decode($lead_id);
            $status = "AUDIT-NEW";
            $stage = "S31";
            $lead_status_id = 44;
            $remarks = 'Application moved to Audit New';

            $query = $this->Tasks->select(['lead_id' => $lead_id], "lead_id,  lead_status_id", 'leads');
            $lead_details = $query->row();

            if ($lead_details->lead_status_id != 10) {
                $this->sendJsonResponse(['errSession' => "Session Expired"]);
                return false;
            }

            $data = [
                'status' => $status,
                'stage' => $stage,
                'lead_status_id' => $lead_status_id
            ];

            if ($_SESSION['isUserSession']['labels'] == "CR2") {
                $data['lead_credit_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
                $data['lead_credit_assign_datetime'] =  date('Y-m-d H:i:s');
            } else {
                $data['lead_credithead_assign_user_id'] = $_SESSION['isUserSession']['user_id'];
                $data['lead_credithead_assign_datetime'] = date('Y-m-d H:i:s');
            }

            $data2 = [
                'lead_id' => $lead_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $status,
                'stage' => $stage,
                'lead_followup_status_id' => $lead_status_id,
                'remarks' => $remarks,
                'created_on' => date('Y-m-d H:i:s')
            ];

            $conditions = ['lead_id' => $lead_id];
            $this->Tasks->updateLeads($conditions, $data, 'leads');
            $this->Tasks->insert($data2, 'lead_followup');
            $this->sendJsonResponse(['msg' => $remarks]);
        }
    }

    public function saveAuditHoldleads($lead_id) {
        $lead_id = $this->encrypt->decode($lead_id);

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->sendJsonResponse(['err' => 'Invalid access.']);
            return;
        }

        $hold_date = $this->input->post('hold_date');
        $hold_remark = $this->input->post('hold_remark');
        $user_id = $_SESSION['isUserSession']['user_id'] ?? null;

        if (!$user_id) {
            $this->sendJsonResponse(['err' => 'Session Expired']);
            return;
        }

        if (empty($lead_id)) {
            $this->sendJsonResponse(['err' => 'Lead id not found.']);
            return;
        }

        if (empty($hold_date)) {
            $this->sendJsonResponse(['err' => 'Lead Hold date is missing']);
            return;
        }

        $status = "AUDIT-HOLD";
        $stage = "S33";
        $lead_status_id = 46;

        $data1 = [
            'status' => $status,
            'stage' => $stage,
            'lead_status_id' => $lead_status_id,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime($hold_date)),
        ];

        $data2 = [
            'lead_id' => $lead_id,
            'customer_id' => $this->input->post('customer_id'),
            'user_id' => $user_id,
            'status' => $status,
            'stage' => $stage,
            'lead_followup_status_id' => $lead_status_id,
            'remarks' => $hold_remark . "<br>scheduled date : " . $hold_date,
            'created_on' => date("Y-m-d H:i:s"),
        ];

        $conditions = ['lead_id' => $lead_id];
        $this->Tasks->updateLeads($conditions, $data1, 'leads');
        $this->Tasks->insert($data2, 'lead_followup');

        $this->sendJsonResponse(['msg' => 'Audit Hold Successfully.']);
    }

    private function sendJsonResponse($data) {
        echo json_encode($data);
    }

    public function __destruct() {
        $this->db->close();
    }

    
    public function testSanc() {
        $pdf_return = $this->Tasks->newSanctionLetter($lead_id);
        print_r($pdf_return);
        exit;
    }
}
