<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CollectionController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo";
    public $tbl_loan = "loan";
    public $tbl_collection = "tbl_collection";

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Status_Model', 'Status');
        $this->load->model('Collection_Model', 'Collection');

        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $repayment_date = date('Y-m-d', strtotime('+5 days', strtotime(date('Y-m-d H:i:s'))));
        $conditions = 'LD.loan_no != "" and ';
        $conditions .= ' (LD.lead_status_id ="14" or LD.lead_status_id ="19") and ';
        $conditions .= ' date(CAM.repayment_date) BETWEEN "' . date('Y-m-d', strtotime(date('Y-m-d H:i:s'))) . '" and "' . date('Y-m-d', strtotime($repayment_date)) . '" ';

        $url = (base_url() . $this->uri->segment(1));
        $count = $this->Tasks->collection($conditions);
        $totalcount = $count->num_rows();
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $totalcount;
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
        $data['pageURL'] = $url;
        $data['totalcount'] = $totalcount;
        $data['leadDetails'] = $this->Tasks->collection($conditions, $config["per_page"], $page);

        $data["links"] = $this->pagination->create_links();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();
        $this->load->view('Tasks/GetLeadTaskList', $data);
    }

    public function closure() {
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        if ($this->uri->segment(1) == 'closure') {
            $conditions = ['LD.status' => "CLOSED", 'CO.payment_verification' => 1, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
        } else {
            $conditions = ['CO.payment_verification' => 0, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
        }
        $count = $this->Tasks->collection($conditions);
        $totalcount = $count->num_rows();
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $totalcount;
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
        $data['pageURL'] = $url;
        $data['totalcount'] = $totalcount;
        $data['leadDetails'] = $this->Tasks->collection($conditions, $config["per_page"], $page);

        $data["links"] = $this->pagination->create_links();
        $data["master_data_source"] = $this->Tasks->getDataSourceList();

        $this->load->view('Tasks/GetLeadTaskList', $data);
    }

    public function collectionDetails($lead_id, $refrence_no = null) {
        //        $table1 = 'leads LD';
        //        $table2 = 'collection CO';
        //        $join2 = 'CO.lead_id = LD.lead_id';
        //        $table3 = 'users closure_user';
        //        $join3 = 'closure_user.user_id = CO.closure_user_id';
        //        $table4 = 'users collection_executive';
        //        $join4 = 'collection_executive.user_id = CO.collection_executive_user_id';
        $lead_id = intval($lead_id);
        $conditions = ['CO.company_id' => company_id, 'CO.product_id' => product_id, 'CO.lead_id' => $lead_id, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
        if (!empty($refrence_no) && $refrence_no != null) {
            $conditions = ['CO.company_id' => company_id, 'CO.product_id' => product_id, 'CO.lead_id' => $lead_id, 'CO.refrence_no' => $refrence_no, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0, 'CO.payment_verification' => 1];
        }

        $select = 'LD.lead_id, LD.customer_id, LD.lead_status_id, CO.id, CO.loan_no, CO.payment_mode, CO.payment_mode_id, CO.discount, CO.refund, CO.date_of_recived, CO.received_amount, CO.refrence_no, CO.repayment_type, CO.remarks,  CO.closure_remarks, CO.payment_verification, CO.collection_executive_user_id, collection_executive.name as collection_executive_name, closure_user.name as closure_user_name, CO.collection_executive_payment_created_on, CO.closure_payment_updated_on, CO.docs';

        $data = $this->db->select($select)
            ->where($conditions)
            ->from('leads LD')
            ->join('collection CO', 'CO.lead_id = LD.lead_id', 'LEFT')
            ->join('users closure_user', 'closure_user.user_id = CO.closure_user_id', 'LEFT')
            ->join('users collection_executive', 'collection_executive.user_id = CO.collection_executive_user_id', 'LEFT')
            ->distinct()
            ->order_by('CO.id', 'ASC')
            ->get();

        //        $data = $this->Tasks->join_table($conditions, $select, $table1, $table2, $join2, $table3, $join3, $table4, $join4);
        return $data;
    }

    public function get_collection_followup_master_lists() {

        $response_followup_type = $this->Collection->lists_master_followup_type();
        $result['lists_master_followup_type'] = $response_followup_type->result_array();

        $response_followup_status = $this->Collection->lists_master_followup_status();
        $result['lists_master_followup_status'] = $response_followup_status->result_array();

        echo json_encode($result);
    }

    public function insert_loan_collection_followup() {

        $result = array('err' => '', 'msg' => '', 'status' => 0);
        $collection_followup_data = array();

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $collection_followup_type_id = $_POST['collection_followup_type_id'];
            $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));

            $conditions['lead_id'] = $lead_id;
            $lead_details = $this->Tasks->select($conditions, 'lead_id', 'leads');

            if (!empty($lead_details->num_rows())) {

                if ($collection_followup_type_id == 1) { // call
                    $this->form_validation->set_rules('collection_followup_type_id', 'Followup Type', 'required|trim');
                    $this->form_validation->set_rules('collection_followup_status_id', 'Followup status', 'required|trim');
                    $this->form_validation->set_rules('collection_next_schedule_date', 'Next Schedule Date', 'trim');
                    $this->form_validation->set_rules('followup_remarks', 'Remark', 'required|trim');

                    if ($this->form_validation->run() == FALSE) {
                        $result['err'] = strip_tags(validation_errors());
                    } else {
                        $followup_type_id = intval($this->input->post('collection_followup_type_id'));
                        $followup_status_id = intval($this->input->post('collection_followup_status_id'));
                        $collection_next_schedule_date = $this->input->post('collection_next_schedule_date');
                        $followup_remarks = strval($this->input->post('followup_remarks'));
                        $next_schedule_date = (($collection_next_schedule_date) ? date('Y-m-d H:i:s', strtotime($collection_next_schedule_date)) : NULL);

                        $collection_followup_data['lcf_lead_id'] = $lead_id;
                        $collection_followup_data['lcf_type_id'] = $followup_type_id;
                        $collection_followup_data['lcf_status_id'] = $followup_status_id;
                        $collection_followup_data['lcf_remarks'] = $followup_remarks;
                        $collection_followup_data['lcf_next_schedule_datetime'] = $next_schedule_date;
                        $collection_followup_data['lcf_user_id'] = $_SESSION['isUserSession']['user_id'];
                        $collection_followup_data['lcf_created_on'] = date('Y-m-d H:i:s');
                        $collection_followup_data['lcf_active'] = 1;
                        $collection_followup_data['lcf_deleted'] = 0;

                        $this->Tasks->insert($collection_followup_data, 'loan_collection_followup');
                        $last_id = $this->db->insert_id();

                        $insertLoanLastFollowUp = array();
                        $insertLoanLastFollowUp['lead_loan_last_followup_id'] = $last_id;
                        $insertLoanLastFollowUp['lead_loan_last_followup_type_id'] = $followup_status_id;
                        $insertLoanLastFollowUp['lead_loan_last_followup_user_id'] = $_SESSION['isUserSession']['user_id'];
                        $insertLoanLastFollowUp['lead_loan_last_followup_datetime'] = date('Y-m-d H:i:s');

                        $this->db->where('lead_id', $lead_id)->update('leads', $insertLoanLastFollowUp);
                        $result['msg'] = "Followup added successfully.";
                        $result['status'] = 1;
                    }
                } else if ($collection_followup_type_id == 2) { // sms
                    $this->form_validation->set_rules('collection_followup_type_id', 'Followup Type', 'required|trim');
                    $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
                    $this->form_validation->set_rules('collection_followup_sms_primary_id', 'SMS Template', 'required|trim');
                    $this->form_validation->set_rules('collection_followup_sms_content', 'SMS Content', 'required|trim');

                    if ($this->form_validation->run() == FALSE) {
                        $result['err'] = strip_tags(validation_errors());
                    } else {
                        $sms_data['followup_type_id'] = $collection_followup_type_id;
                        $sms_data['lead_id'] = $lead_id;
                        $sms_data['sms_primary_id'] = intval($this->input->post("collection_followup_sms_primary_id"));
                        $sms_data['sms_template_content'] = strval($this->input->post("collection_followup_sms_content"));

                        $result_data = $this->Collection->send_collection_followup_sms($sms_data);

                        if (!empty($result_data['status'])) {
                            $result['msg'] = $result_data['msg'];
                            $result['status'] = 1;
                        } else {
                            $result['err'] = "Failed to send sms.";
                        }
                    }
                } else if ($collection_followup_type_id == 3) { // whatsapp
                } else if ($collection_followup_type_id == 4) { // email
                    $this->form_validation->set_rules('collection_followup_type_id', 'Followup Type', 'required|trim');
                    $this->form_validation->set_rules('c_followup_email_template_id', 'Followup status', 'required|trim');
                    $this->form_validation->set_rules('email_subject', 'Email Subject', 'required|trim');
                    $this->form_validation->set_rules('email_cc_user', 'Email CC user', 'trim');
                    $this->form_validation->set_rules('email_body', 'Email Body', 'required|trim');

                    if ($this->form_validation->run() == FALSE) {
                        $result['err'] = strip_tags(validation_errors());
                    } else {
                        $email_data['followup_type_id'] = $collection_followup_type_id;
                        $email_data['lead_id'] = $lead_id;
                        $email_data['email_template_id'] = intval($this->input->post("c_followup_email_template_id"));
                        $email_data['email_subject'] = strval($this->input->post("email_subject"));
                        $email_data['email_cc_user'] = strval($this->input->post("email_cc_user"));
                        $email_data['email_body'] = strval($this->input->post("email_body"));

                        $result_data = $this->Collection->send_collection_followup_email($email_data);

                        if (!empty($result_data['status'])) {
                            $result['msg'] = $result_data['msg'];
                        } else {
                            $result['err'] = "Faild to send data to the customer.";
                        }
                    }
                }
            } else {
                $result['err'] = "Invalid application ID.";
            }
        } else {
            $result['err'] = "Invalid Request";
        }

        echo json_encode($result);
    }

    public function get_list_loan_collection_followup($leadID) {
        $result = array('err' => '', 'msg' => '', 'data' => '');
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        } else if (empty($leadID)) {
            $result['err'] = "Application no not found. Please check.";
        } else {
            $lead_id = intval($this->encrypt->decode($leadID));

            if (empty($lead_id)) {
                $result['err'] = "Application no not found. Please check.";
            } else {
                $followup_list = $this->Collection->get_list_collection_followup($lead_id);
                $result['data'] = $followup_list['data'];
                $result['msg'] = "Success";
            }
        }

        echo json_encode($result);
    }

    public function get_visit_request_lists($leadID) {
        $result = array('err' => '', 'msg' => '', 'data' => '');
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        if (empty($leadID)) {
            $result['err'] = "Application no not found. Please check.";
        } else {
            $lead_id = intval($this->encrypt->decode($leadID));

            $visit_list = $this->Collection->get_list_collection_visit($lead_id);
            $result['data'] = $visit_list['data'];
            $result['msg'] = "Success";
        }

        echo json_encode($result);
    }

    public function get_legal_notice($leadID) {
        //$lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));
        echo json_encode($leadID);
    }

    public function get_visit_request_user_lists($leadID) {

        $result = array('err' => '', 'status' => 0, 'scm_user_lists' => array());
        $conditions = array();

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        if (empty($leadID)) {
            $result['err'] = "Application no not found. Please check.";
        } else {
            $lead_id = intval($this->encrypt->decode($leadID));
            $visit_type_id = intval($this->input->post('visit_type_id')); // 1 => residence, 2 =>office

            $conditions['LD.lead_id'] = $lead_id;
            $conditions['URL.user_rl_location_type_id'] = 2; // 1 => city, 2 =>state, 3 => branch
            $conditions['visit_type_id'] = $visit_type_id;

            $scm_user_lists['status'] = 0;
            $cfe_user_lists['status'] = 0;

            if (in_array(agent, ['CO1', 'CO4'])) {
                $scm_user_lists = $this->Collection->scm_user_lists($conditions);
            }

            if (in_array(agent, ['CO2', 'CO3'])) {
                $cfe_user_lists = $this->Collection->cfe_user_lists();
            }

            if (!empty($scm_user_lists['status'])) {
                $result['scm_user_lists'] = $scm_user_lists['data'];
                $result['status'] = $scm_user_lists['status'];
            } else if (!empty($cfe_user_lists['status'])) {
                $result['cfe_user_lists'] = $cfe_user_lists['data'];
                $result['status'] = $cfe_user_lists['status'];
            } else {
                $result['err'] = "User not mapped.";
            }
        }

        echo json_encode($result);
    }

    public function insert_request_for_collection_visit() {
        $result = array('err' => '', 'msg' => '', 'status' => 0);
        $insert_request_visit_data = array();
        $conditions_send_email = array();
        $where = array();
        $col_visit_field_status_id = 0;
        $lead_visit_id = 0;

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            if (in_array(agent, ['CO1', 'CO2', 'CO3', 'CO4'])) {
                $this->form_validation->set_rules('visit_type_id', 'Visit Type', 'required|trim');
            }

            if (in_array(agent, ['CO1', 'CO4'])) {
                $this->form_validation->set_rules('visit_scm_user_id', 'SCM Name', 'required|trim');
            }

            if (in_array(agent, ['CO2', 'CO3'])) {
                $this->form_validation->set_rules('col_visit_id', 'Visit Reference ID', 'trim'); // required|
            }

            if (in_array(agent, ['CO2', 'CO3']) && $_POST['visit_status_id'] != 3) {
                $this->form_validation->set_rules('visit_rm_user_id', 'Assign User Name', 'required|trim');
            }

            if (in_array(agent, ['CO2', 'CO3', 'CFE1']) && $_POST['visit_status_id'] != 3) {
                $this->form_validation->set_rules('visit_status_id', 'Visit Status', 'required|trim');
            }

            $this->form_validation->set_rules('remarks', 'Remark', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $result['err'] = strip_tags(validation_errors());
            } else {
                $lead_id = $this->encrypt->decode($this->input->post('lead_id'));

                $conditions['lead_id'] = $lead_id;
                $lead_details = $this->Tasks->select($conditions, 'lead_id', 'leads');

                if (!empty($lead_details->num_rows())) {
                    $visit_type_id = $this->input->post('visit_type_id');
                    $visit_status_id = $this->input->post('visit_status_id');
                    $temp_data = $this->Collection->is_already_visit_running($lead_id, $visit_type_id);

                    if (!empty($temp_data['status']) && in_array($visit_status_id, [2, 4])) {
                        // $result['err'] = "Can't request. Visit already assigned to - " . $temp_data['data']['running_visit']['visit_allocated_to'];
                        $result['err'] = "Visit already in process.";
                    } else {

                        $remarks = $this->input->post('remarks');
                        $scm_user_id = $this->input->post('visit_scm_user_id');
                        $rm_user_id = $this->input->post('visit_rm_user_id');
                        if (!empty($this->input->post('col_visit_id'))) {
                            $lead_visit_id = $this->encrypt->decode($this->input->post('col_visit_id'));
                            $where = ['col_visit_id' => $lead_visit_id];
                        }

                        $repayment_details = $this->calculateRepaymentAmount($lead_id);

                        $conditions_send_email['lead_id'] = $lead_id;
                        $conditions_send_email['visit_type_id'] = $visit_type_id;
                        $conditions_send_email['total_due_amount'] = $repayment_details['total_due_amount'];
                        $conditions_send_email['loan_no'] = $repayment_details['loan_no'];

                        if (in_array(agent, ['CO1', 'CO4'])) {
                            $conditions_send_email['ce_user_id'] = $_SESSION['isUserSession']['user_id'];
                            $conditions_send_email['scm_user_id'] = $scm_user_id;
                            $col_visit_field_status_id = 1; // Pending

                            $insert_request_visit_data['col_visit_scm_id'] = $scm_user_id;
                            $insert_request_visit_data['col_visit_requested_by'] = $_SESSION['isUserSession']['user_id'];
                            $insert_request_visit_data['col_visit_requested_datetime'] = date('Y-m-d H:i:s');
                            $insert_request_visit_data['col_visit_requested_by_remarks'] = $remarks;
                        }

                        if (in_array(agent, ['CO2', 'CO3'])) {
                            $conditions_send_email['scm_user_id'] = $_SESSION['isUserSession']['user_id'];
                            $conditions_send_email['rm_user_id'] = $rm_user_id;
                            $col_visit_field_status_id = $visit_status_id; // 2=>Assign, 3=>Cancel, 4=>Hold, 5=>Completed

                            $insert_request_visit_data['col_visit_scm_id'] = $_SESSION['isUserSession']['user_id'];
                            $insert_request_visit_data['col_visit_allocated_to'] = (in_array($visit_status_id, [2, 4]) ? $rm_user_id : 0);
                            $insert_request_visit_data['col_visit_scm_remarks'] = $remarks;
                            $insert_request_visit_data['col_visit_allocate_on'] = date('Y-m-d H:i:s');
                            $insert_request_visit_data['col_visit_updated_on'] = date('Y-m-d H:i:s');

                            if (in_array($visit_status_id, [3])) {
                                $insert_request_visit_data['col_fe_visit_trip_status_id'] = NULL;
                                $insert_request_visit_data['col_visit_allocate_on'] = NULL;
                                $insert_request_visit_data['col_fe_visit_trip_start_longitude'] = NULL;
                                $insert_request_visit_data['col_fe_visit_trip_start_datetime'] = NULL;
                                $insert_request_visit_data['col_fe_device_id'] = NULL;
                            }
                        }

                        $insert_request_visit_data['col_visit_address_type'] = $visit_type_id;

                        if (in_array(agent, ['CFE1'])) {
                            unset($insert_request_visit_data['col_visit_address_type']);
                            $col_visit_field_status_id = $visit_status_id; // 2=>Assign, 3=>Cancel, 4=>Hold, 5=>Completed
                            $insert_request_visit_data['col_visit_field_remarks'] = $remarks;
                            $insert_request_visit_data['col_visit_field_datetime'] = date('Y-m-d H:i:s');
                        }

                        $this->Collection->send_email_for_visit($conditions_send_email);

                        $insert_request_visit_data['col_visit_field_status_id'] = $col_visit_field_status_id;

                        if (in_array(agent, ['CO1', 'CO2', 'CO3', 'CO4']) && empty($lead_visit_id)) {
                            $insert_request_visit_data['col_lead_id'] = $lead_id;
                            $insert_request_visit_data['col_visit_created_on'] = date('Y-m-d H:i:s');
                            $insert_request_visit_data['col_visit_active'] = 1;
                            $insert_request_visit_data['col_visit_deleted'] = 0;

                            $response_inserted_request_visit = $this->Tasks->insert($insert_request_visit_data, 'loan_collection_visit ');

                            if ($response_inserted_request_visit == true) {
                                $result['status'] = 1;
                                $result['msg'] = "Visit Requested Successfully.";

                                $email_sent_status = $this->Collection->send_email_for_visit($conditions_send_email);

                                if (empty($email_sent_status['status'])) {
                                    $result['msg'] .= " , " . $email_sent_status['error'];
                                }
                            } else {
                                $result['err'] = "Failed to request visit. try again";
                            }
                        } else if (in_array(agent, ['CO2', 'CO3'])) {
                            $response_visit_assigned = $this->db->where($where)->update('loan_collection_visit ', $insert_request_visit_data);

                            if ($response_visit_assigned == true) {
                                $result['status'] = 1;

                                if (in_array($visit_status_id, [2])) {
                                    $result['msg'] = "Visit Assigned Successfully.";
                                } else if (in_array($visit_status_id, [3])) {
                                    $result['msg'] = "Visit Cancel Successfully.";
                                } else if (in_array($visit_status_id, [4])) {
                                    $result['msg'] = "Visit Hold Successfully.";
                                }
                            } else {
                                $result['err'] = "Failed to assigned visit. try again";
                            }
                        } else if (in_array(agent, ['CFE1'])) {

                            $rm_response_visit_updated = $this->db->where($where)->update('loan_collection_visit ', $insert_request_visit_data);

                            if ($rm_response_visit_updated == true) {
                                $result['status'] = 1;
                                $result['msg'] = "Visit Updated Successfully.";
                            } else {
                                $result['err'] = "Failed to update visit. try again";
                            }
                        }
                    }
                } else {
                    $result['err'] = "Invalid application ID.";
                }
            }
        } else {
            $result['err'] = "Invalid Request";
        }

        echo json_encode($result);
    }

    public function getLoanDetail($conditions) {
        $fetch = 'L.lead_id, L.customer_id, L.loan_no';
        return $this->Tasks->select($conditions, $fetch, 'loan L');
    }

    public function getLeadDetail($conditions) {
        $fetch = 'LD.lead_id, LD.pancard, LD.customer_id, LD.status, LD.stage, LD.lead_status_id,LD.lead_black_list_flag,LD.loan_no, LD.lead_data_source_id';
        return $this->Tasks->select($conditions, $fetch, 'leads LD');
    }

    public function getCAMDetail($conditions) {
        $fetch = 'CAM.cam_id, CAM.lead_id, CAM.customer_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi';
        return $this->Tasks->select($conditions, $fetch, 'credit_analysis_memo CAM');
    }

    public function calculateRepaymentAmount($lead_id) {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $repay = $CommonComponent->get_loan_repayment_details($lead_id);

        $repayment = $repay['repayment_data'];

        return $repayment;
    }

    public function generateRepayLinkSMS($lead_id) {

        //$mobile = intval($_POST['mobile']);
        $lead_id = intval($this->encrypt->decode($lead_id));
        if (!empty($lead_id)) {
            $data = $this->Tasks->generate_Repay_Link_SMS($lead_id);
            return $data;
        }
    }

    public function repaymentLoanDetails() {
        if ($this->input->post('user_id') == '') {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('lead_id', 'Lead Id', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = intval($this->input->post('lead_id'));

                $data = $this->calculateRepaymentAmount($lead_id);

                $data['master_blacklist_reason'] = $this->db->select('m_br_id as id,m_br_name as reason')->where(['m_br_active' => 1, 'm_br_deleted' => 0])->from('master_blacklist_reject_reason')->get()->result();
                echo json_encode($data);
            }
        }
    }

    public function collectionHistory() {

        if ($this->input->post('user_id') == '') {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead Id', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));

                $sql = $this->collectionDetails($lead_id);

                $data['recoveryData'] = $this->Tasks->getRecoveryData($sql);

                echo json_encode($data);
            }
        }
    }
    public function paymentHistory($leadid) {

        $lead_id = $this->encrypt->decode($leadid);
        //echo $lead_id; die;
        $data = ['lead_id' => $lead_id, 'user_id' => user_id];

        $conditions['LD.lead_id'] = $lead_id;
        $leadData = $this->Tasks->getLeadDetails($conditions);
        $data['leadDetails'] = $leadData->row();

        $conditions = ['status_stage' => 'S16', 'status_active' => 1, 'status_deleted' => 0];
        $select = 'status_id, status_name, status_stage';
        $data['statusClosuer'] = $this->Tasks->select($conditions, $select, 'master_status');

        //echo "<pre>"; print_r($data);	 die;

        $this->load->view('Tasks/RecoveryHistory.php', $data);
        $this->load->view('Tasks/main_js.php');
    }


    public function loanClosingRequest($leadid) {

        $lead_id = $this->encrypt->decode($leadid);
        $status_id = $this->input->post('status_id');
        $collection_conditions = ['CO.lead_id' => $lead_id, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0, 'CO.payment_verification' => 0];
        $sql = $this->db->select("id")->where($collection_conditions)->from('collection CO')->order_by('CO.id', 'desc')->limit(1)->get();
        //echo $sql->num_rows(); die;
        if ($sql->num_rows() > 0) {
            $json['err'] = "Loan status cannot be changed! please verfiy all payment collect first.";
            echo json_encode($json);
        } else {
            $getLeadStatus = $this->db->select('status_name as status, status_stage as stage')->where('status_id', $status_id)->from('master_status')->get()->row_array();

            $insertLeadFollowupData = [
                'lead_id' => $lead_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $getLeadStatus['status'],
                'stage' => $getLeadStatus['stage'],
                'lead_followup_status_id' => $status_id,
                'created_on' => date('Y-m-d H:i:s'),
                'remarks' => "Update for " . $getLeadStatus['status']
            ];

            $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');

            $updateData = array(
                "status" =>  $getLeadStatus['status'],
                "stage" =>  $getLeadStatus['stage'],
                "lead_status_id" =>  $status_id,
            );
            $conditions = ['lead_id' => $lead_id];
            $this->Tasks->updateLeads($conditions, $updateData, 'leads');

            $json['msg'] = 'Status updated successfully.';
            echo json_encode($json);
        }
    }

    public function deleteCoustomerPayment() {
        if ($this->input->post('user_id') == '') {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('id', 'ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $id = $this->input->post('id');

                $collection_conditions = ['CO.id' => $id, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
                $sql = $this->db->select("*")->where($collection_conditions)->from('collection CO')->order_by('CO.id', 'desc')->limit(1)->get();

                if ($sql->num_rows() > 0) {
                    $collectionDetails = $sql->row_array();

                    if (!empty($collectionDetails['payment_verification'])) {

                        $json['err'] = "Collection details already updated by closure team.";
                    } else {

                        $lead_id = $collectionDetails['lead_id'];
                        $sql = $this->getLeadDetail(['lead_id' => $lead_id]);
                        $leadDetails = $sql->row_array();

                        if (in_array($leadDetails['lead_status_id'], array(16, 17))) {
                            $json['err'] = "Payment cannot be deleted on settle or closed cases.";
                        } else {

                            $conditions = ['id' => $id];
                            $data = ['collection_active' => 0, 'collection_deleted' => 1];
                            $result = $this->Tasks->globalUpdate($conditions, $data, 'collection');

                            $insertLeadFollowupData = [
                                'lead_id' => $lead_id,
                                'customer_id' => $leadDetails['customer_id'],
                                'user_id' => $_SESSION['isUserSession']['user_id'],
                                'status' => $leadDetails['status'],
                                'stage' => $leadDetails['stage'],
                                'lead_followup_status_id' => $leadDetails['lead_status_id'],
                                'created_on' => date('Y-m-d H:i:s'),
                                'remarks' => "Collection entry deleted | Col Id : " . $id
                            ];

                            $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');

                            if ($result == true) {
                                $json['msg'] = 'Record deleted successfully.';
                            } else {
                                $json['err'] = 'Record can not ne deleted.';
                            }
                        }
                    }
                } else {
                    $json['err'] = "Collection details not found.";
                }

                echo json_encode($json);
            }
        }
    }

    public function viewCustomerPaidSlip($recovery_id) {
        if (!empty($recovery_id)) {
            $query = $this->db->where("id", $recovery_id)->get('collection')->row_array();
            $img = $query['docs'];
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

    public function UpdatePayment() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead Id', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer Id', 'required|trim');
            $this->form_validation->set_rules('loan_no', 'Loan No', 'required|trim');
            $this->form_validation->set_rules('user_id', 'Session Expired', 'required|trim');
            $this->form_validation->set_rules('company_id', 'Company Id', 'required|trim');
            $this->form_validation->set_rules('product_id', 'Product Id', 'required|trim');
            $this->form_validation->set_rules('received_amount', 'Received Amount', 'required|trim');
            $this->form_validation->set_rules('refrence_no', 'Refrence No', 'required|trim');
            $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required|trim');
            $this->form_validation->set_rules('repayment_type', 'Payment Type', 'required|trim');
            $this->form_validation->set_rules('discount', 'Discount', 'required|trim');
            $this->form_validation->set_rules('refund', 'Refund', 'required|trim');
            if (agent == 'CO1' || agent == "CO2" || agent == "CR2" || agent == "CAGY" || agent == 'CO4') {
                $this->form_validation->set_rules('scm_remarks', 'SCM Remarks', 'required|trim');
            } else if (agent == 'AC1') {
                $this->form_validation->set_rules('date_of_recived', 'Date Of Received', 'required|trim');
                // $this->form_validation->set_rules('recovery_id', 'Recovery ID', 'required|trim');
                $this->form_validation->set_rules('ops_remarks', 'OPs Remarks', 'required|trim');
            }

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $recovery_id = strval($this->input->post('recovery_id'));
                $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));
                $customer_id = strval($this->input->post('customer_id'));
                $user_id = $_SESSION['isUserSession']['user_id'];
                $company_id = intval($this->input->post('company_id'));
                $product_id = intval($this->input->post('product_id'));
                $received_amount = doubleval($this->input->post('received_amount'));
                $refrence_no = strval($this->input->post('refrence_no'));
                $file_name = strval($this->input->post('refrence_no'));
                $payment_mode_id = strval($this->input->post('payment_mode'));
                $repayment_type = intval($this->input->post('repayment_type'));
                $discount = doubleval($this->input->post('discount'));
                $refund = doubleval($this->input->post('refund'));
                $scm_remarks = strval($this->input->post('scm_remarks'));
                $closure_remarks = strval($this->input->post('ops_remarks'));
                $payment_verification = strval($this->input->post('payment_verification'));
                $paymentSlips = "";
                //$collected_by = strval($this->input->post('collected_by'));
                $getLeadStatus = $this->db->select('status_name as status, status_stage as stage')->where('status_id', $repayment_type)->from('master_status')->get()->row_array();

                $sqlRecovery = $this->collectionDetails($lead_id, $refrence_no);
                $cond = ['lead_id' => $lead_id];

                $collected_by =  $_SESSION['isUserSession']['user_id'];

                $payment_mode_name = "";
                $temp_payment_mode = $this->Collection->get_master_payment_mode($payment_mode_id);

                if ($recovery_id != '') {
                    $duplicateCounts = $this->db->query("SELECT count(id) as counts FROM collection WHERE refrence_no = '$refrence_no' AND id != $recovery_id")->row_array();
                } else {
                    $duplicateCounts = $this->db->query("SELECT count(id) as counts FROM collection WHERE refrence_no = '$refrence_no'")->row_array();
                }

                if ($duplicateCounts['counts'] > 0) {
                    $json['err'] = "Duplicate payment not allowed.";
                    echo json_encode($json);
                    exit;
                }

                if (!empty($temp_payment_mode['status']) && !empty($payment_mode_id)) {

                    $payment_mode_data = $temp_payment_mode['data']['payment_mode_list'][0];
                    $payment_mode_name = $payment_mode_data['payment_mode_name'];

                    $sql = $this->getLoanDetail($cond);
                    $loan = $sql->row();

                    $sql = $this->getLeadDetail($cond);
                    $leadDetails = $sql->row();

                    if (!empty($recovery_id)) {

                        $collection_conditions = ['CO.id' => $recovery_id, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];

                        $sql = $this->db->select("*")->where($collection_conditions)->from('collection CO')->order_by('CO.id', 'desc')->limit(1)->get();

                        if ($sql->num_rows() > 0) {

                            $collectionDetails = $sql->row_array();

                            if (!empty($collectionDetails['payment_verification'])) {

                                $json['err'] = "Payment already verified";
                                echo json_encode($json);
                                exit;
                            }
                        } else {
                            $json['err'] = "Payment is not active to verified.";
                            echo json_encode($json);
                            exit;
                        }

                        if (agent == 'CO1' || agent == "CO2" || agent == 'CR2' || agent == "CAGY" || agent == 'CO4') {
                            $updateCollectionData = [
                                'customer_id' => $customer_id,
                                'loan_no' => $loan->loan_no,
                                'received_amount' => $received_amount,
                                'refrence_no' => $refrence_no,
                                'payment_mode_id' => $payment_mode_id,
                                'payment_mode' => $payment_mode_name,
                                'repayment_type' => $repayment_type,
                                'discount' => $discount,
                                'refund' => $refund,
                                'ip' => ip,
                                //'docs' => $paymentSlips,
                                'payment_verification' => 0,
                                'collection_executive_user_id' => $collected_by,
                                'collection_executive_payment_created_on' => date('Y-m-d H:i:s'),
                            ];

                            $conditions = ['lead_id' => $lead_id, 'id' => $recovery_id];
                            $result = $this->Tasks->updateLeads($conditions, $updateCollectionData, 'collection');
                            $insertLeadFollowupData = [
                                'lead_id' => $lead_id,
                                'customer_id' => $customer_id,
                                'user_id' => $_SESSION['isUserSession']['user_id'],
                                'status' => $getLeadStatus['status'],
                                'stage' => $getLeadStatus['stage'],
                                'lead_followup_status_id' => $leadDetails->lead_status_id,
                                'created_on' => date('Y-m-d H:i:s'),
                                'remarks' => "Update for " . $getLeadStatus['status'] . " | " . addslashes($scm_remarks)
                            ];

                            $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');

                            $json['msg'] = 'Payment updated successfully.';
                            echo json_encode($json);
                        } else if (agent == 'AC1') {

                            if ($payment_verification == 1) {
                                $payment_verification = 1;
                            } else if ($payment_verification == 2) {
                                $payment_verification = 2;
                            }
                            $date_of_recived = date('Y-m-d', strtotime($_POST['date_of_recived']));
                            $payment_verify_date = date('Y-m-d');
                            $updateClosuredata = [
                                'customer_id' => $customer_id,
                                'loan_no' => $loan->loan_no,
                                'received_amount' => $received_amount,
                                'refrence_no' => $refrence_no,
                                'payment_mode_id' => $payment_mode_id,
                                'payment_mode' => $payment_mode_name,
                                'repayment_type' => $repayment_type,
                                'discount' => $discount,
                                'refund' => $refund,
                                'ip' => ip,
                                'payment_verification' => $payment_verification,
                                'closure_user_id' => $_SESSION['isUserSession']['user_id'],
                                'closure_payment_updated_on' => date('Y-m-d H:i:s'),
                                'date_of_recived' => $date_of_recived,
                                'closure_remarks' => $closure_remarks
                            ];

                            if ($payment_verification == 1) {

                                $update_loan_array = array('loan_total_discount_amount' => 0, 'loan_principle_discount_amount' => 0, 'loan_interest_discount_amount' => 0, 'loan_penalty_discount_amount' => 0);

                                $this->Tasks->globalUpdate(['lead_id' => $lead_id], $update_loan_array, 'loan');

                                $repaymentDetails = $this->calculateRepaymentAmount($lead_id);

                                $total_payment_received = unformatMoney($repaymentDetails['total_received_amount']); // total paied amount by customer
                                $total_repayment_amount = unformatMoney($repaymentDetails['total_repayment_amount']); // paybale principle + intenerest + panelity
                                $repayment_amount_without_penality = unformatMoney($repaymentDetails['repayment_amount']); //paybale principle + intenerest
                                $advance_interest_amount_deducted = unformatMoney($repaymentDetails['advance_interest_amount_deducted']); //advance intesrest
                                $repayment_amount_without_penality = $repayment_amount_without_penality + $advance_interest_amount_deducted;

                                if ($repayment_type == 16) {

                                    //preclosure date
                                    if (strtotime($payment_verify_date) <= strtotime($repaymentDetails['repayment_date'])) {
                                        $repayment_with_real_interest = isset($repaymentDetails['repayment_with_real_interest']) ? $repaymentDetails['repayment_with_real_interest'] : $total_repayment_amount;
                                        if (($total_payment_received + $received_amount + $discount - $refund) == $total_repayment_amount) {
                                            $update_loan_array['loan_interest_discount_amount'] = $discount;
                                        } else if (($total_payment_received + $received_amount + $discount - $refund) == $repayment_with_real_interest) {
                                            //$update_loan_array['loan_interest_discount_amount'] = $discount;
                                        } else {
                                            $json['err'] = "Loan clousre amounts is incorrect." . ($total_payment_received + $received_amount + $discount - $refund) . " " . $total_repayment_amount . " " . $repayment_with_real_interest;
                                            echo json_encode($json);
                                            exit;
                                        }
                                    } else {

                                        if ((($total_payment_received + $received_amount) >= $repayment_amount_without_penality) && (($total_payment_received + $received_amount + $discount) == $total_repayment_amount)) {
                                            $update_loan_array['loan_penalty_discount_amount'] = $discount;
                                        } else {
                                            $json['err'] = "Loan clousre amount is incorrect.";
                                            echo json_encode($json);
                                            exit;
                                        }
                                    }
                                } else if ($repayment_type == 17) {
                                    //preclosure date
                                    if (strtotime($payment_verify_date) < strtotime($repaymentDetails['repayment_date'])) {
                                        $json['err'] = "Loan cannot be settle as date of received is less than repayment date.";
                                        echo json_encode($json);
                                        exit;
                                    } else {

                                        if (($total_payment_received + $received_amount + $discount) == $total_repayment_amount) {
                                            $principle_discount = 0;
                                            $penalty_discount = 0;

                                            if (($total_payment_received + $received_amount) < $repayment_amount_without_penality) {

                                                $principle_discount = $repayment_amount_without_penality - ($total_payment_received + $received_amount);

                                                $penalty_discount = $discount - $principle_discount;
                                            } else if (($total_payment_received + $received_amount) >= $repayment_amount_without_penality) {
                                                $principle_discount = 0;

                                                $penalty_discount = $discount;
                                            }

                                            $update_loan_array['loan_principle_discount_amount'] = $principle_discount;
                                            $update_loan_array['loan_penalty_discount_amount'] = $penalty_discount;
                                        } else {
                                            $json['err'] = "Loan settled amount is incorrect.";
                                            echo json_encode($json);
                                            exit;
                                        }
                                    }
                                } else if ($repayment_type == 18) {
                                    //preclosure date
                                    if (strtotime($payment_verify_date) < strtotime($repaymentDetails['repayment_date'])) {
                                        $json['err'] = "Loan cannot be settle as date of received is less than repayment date.";
                                        echo json_encode($json);
                                        exit;
                                    }
                                }
                            }

                            $conditions = ['id' => $recovery_id];
                            $result = $this->Tasks->updateLeads($conditions, $updateClosuredata, 'collection');

                            if ($payment_verification == 1) {
                                $updateLeadStatus = [
                                    'lead_status_id' => $repayment_type,
                                    'status' => $getLeadStatus['status'],
                                    'stage' => $getLeadStatus['stage'],
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];
                                $result = $this->Tasks->updateLeads(['lead_id' => $lead_id], $updateLeadStatus, 'leads');

                                $insertLeadFollowupData = [
                                    'lead_id' => $lead_id,
                                    'customer_id' => $customer_id,
                                    'user_id' => $_SESSION['isUserSession']['user_id'],
                                    'status' => $getLeadStatus['status'],
                                    'stage' => $getLeadStatus['stage'],
                                    'lead_followup_status_id' => $repayment_type,
                                    'created_on' => date('Y-m-d H:i:s'),
                                    'remarks' => "Approved for " . $getLeadStatus['status'] . " | " . addslashes($closure_remarks)
                                ];
                                $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');

                                $co_cnditions = [
                                    'CO.lead_id' => $leadDetails->lead_id
                                ];
                                $getRepayType = $this->db->select("*")->from('collection CO')->where($co_cnditions)->order_by('CO.lead_id', 'desc')->get();

                                $leadDetails = $getRepayType->row_array();
                                $repayType = $leadDetails['repayment_type'];


                                $lead_conditions = [
                                    'LD.lead_id' => $lead_id,
                                    'LD.lead_active' => 1,
                                    'LD.lead_deleted' => 0
                                ];

                                // Fetching the lead details
                                $sql = $this->db->select("*")->from('leads LD')->where($lead_conditions)->get();

                                if ($sql->num_rows() > 0) {
                                    $leadDetails = $sql->row_array();
                                    $pancard = $leadDetails['pancard'];

                                    $lead_pancard = ['LD.pancard' => $pancard,  'user_type' => 'UNPAID-REPEAT', 'LD.lead_active' => 1, 'LD.lead_deleted' => 0];
                                    $panverify = $this->db->select("*")
                                        ->from('leads LD')
                                        ->where($lead_pancard)
                                        ->order_by('LD.lead_id', 'desc')
                                        ->limit(1)
                                        ->get();
                                    $pancardVr =  $panverify->row_array();
                                    $repeat_lead_id = $pancardVr['lead_id'];
                                    $user_Type = $pancardVr['user_type'];
                                    $stage = $pancardVr['stage'];

                                    // Preparing the data for updating

                                    $updateLeadStatus = [
                                        'user_type' => 'REPEAT',
                                        'updated_on' => date("Y-m-d H:i:s")
                                    ];

                                    $where = ['user_type' => 'UNPAID-REPEAT', 'lead_active' => 1];
                                    $this->db->where($where)->where('lead_id', $repeat_lead_id)->order_by('lead_id', 'desc')
                                        ->limit(1)->update('leads', $updateLeadStatus);
                                }

                                if ($result) {

                                    if (!empty($update_loan_array)) {
                                        $update_loan_array['loan_total_discount_amount'] = $discount;
                                        $this->Tasks->globalUpdate(['lead_id' => $lead_id], $update_loan_array, 'loan');
                                    }

                                    $this->calculateRepaymentAmount($lead_id);

                                    if ($repayment_type == 16 && !in_array($leadDetails->lead_data_source_id, array(21, 27, 33))) { //Only for closed case NOC Sent instant basis.
                                        $data = $this->Tasks->sent_loan_closed_noc_letter($lead_id);

                                        if ($data == "false") {
                                            $json['err'] = json_encode('Payment verfied. But failed to sent fullpayment noc letter. Please try again.');
                                            echo json_encode($json);
                                            exit;
                                        } else {
                                            $json['msg'] = json_encode('Payment verfied. Fullpayment NOC Letter sent successfully.');
                                            echo json_encode($json);
                                            exit;
                                        }
                                    } else {

                                        $json['msg'] = json_encode('Payment verified successfully.');
                                        echo json_encode($json);
                                        exit;
                                    }
                                } else {
                                    $json['err'] = json_encode('Unable to verify payment at this time. Please try again.');
                                    echo json_encode($json);
                                    exit;
                                }
                            } else if ($payment_verification == 2) {
                                $insertLeadFollowupData = [
                                    'lead_id' => $lead_id,
                                    'customer_id' => $customer_id,
                                    'user_id' => $_SESSION['isUserSession']['user_id'],
                                    'status' => $getLeadStatus['status'],
                                    'stage' => $getLeadStatus['stage'],
                                    'lead_followup_status_id' => $leadDetails->lead_status_id,
                                    'created_on' => date('Y-m-d H:i:s'),
                                    'remarks' => "Rejected for " . $getLeadStatus['status'] . " | " . addslashes($closure_remarks)
                                ];
                                $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');
                                $json['msg'] = json_encode('Payment has been rejected successfully.');
                                echo json_encode($json);
                                exit;
                            }
                        }
                    } else if ($sqlRecovery->num_rows() == 0) {

                        if (in_array($leadDetails->lead_status_id, array(16))) {
                            $json['err'] = "Case already closed.";
                            echo json_encode($json);
                            exit;
                        }

                        if (agent == 'CO1' || agent == "CO2" || agent == "CR2" || agent == "CAGY" || agent == "CO4") {

                            if (LMS_DOC_S3_FLAG) {
                                $upload_return = uploadDocument($_FILES, $lead_id);
                                if ($upload_return['status'] == 1) {
                                    $paymentSlips = $upload_return['file_name'];
                                } else {
                                    $json['err'] = 'Please upload the screenshot!';
                                    echo json_encode($json);
                                    exit;
                                }
                            } else {
                                $file_name = $_FILES["file_name"]['name'];
                                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                                $extension = strtolower($extension);
                                $new_name = $lead_id . '_payment_slip_' . date('YmdHis') . '.' . $extension;
                                $config['file_name'] = $new_name;
                                $config['upload_path'] = 'upload/';
                                $config['allowed_types'] = 'pdf|jpg|png|jpeg';
                                $this->upload->initialize($config);
                                if (!$this->upload->do_upload('file_name')) {
                                    $json['err'] = $this->upload->display_errors();
                                    echo json_encode($json);
                                    exit;
                                } else {
                                    $filename = array('upload_data' => $this->upload->data());
                                    $paymentSlips = $filename['upload_data']['file_name'];
                                }
                            }
                        }

                        $insertCollectionData = [
                            'lead_id' => $lead_id,
                            'company_id' => $company_id,
                            'product_id' => $product_id,
                            'customer_id' => $customer_id,
                            'loan_no' => $loan->loan_no,
                            'received_amount' => $received_amount,
                            'refrence_no' => $refrence_no,
                            'payment_mode_id' => $payment_mode_id,
                            'payment_mode' => $payment_mode_name,
                            'repayment_type' => $repayment_type,
                            'discount' => $discount,
                            'refund' => $refund,
                            'ip' => ip,
                            'docs' => $paymentSlips,
                            'remarks' => addslashes($scm_remarks),
                            'collection_executive_user_id' => $collected_by,
                            'collection_executive_payment_created_on' => date('Y-m-d H:i:s'),
                        ];

                        if (!empty($_POST['date_of_recived'])) {
                            $insertCollectionData['date_of_recived'] = date('Y-m-d', strtotime($_POST['date_of_recived']));
                        }

                        $result = $this->Tasks->insert($insertCollectionData, 'collection');

                        $insertLeadFollowupData = [
                            'lead_id' => $lead_id,
                            'customer_id' => $customer_id,
                            'user_id' => $_SESSION['isUserSession']['user_id'],
                            'status' => $getLeadStatus['status'],
                            'stage' => $getLeadStatus['stage'],
                            'lead_followup_status_id' => $leadDetails->lead_status_id,
                            'created_on' => date('Y-m-d H:i:s'),
                            'remarks' => "Initiated for " . $getLeadStatus['status'] . " | " . addslashes($scm_remarks)
                        ];

                        $result2 = $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');
                        $json['msg'] = 'Upload Successfully.';
                        echo json_encode($json);
                    } else {
                        $json['err'] = 'The same reference no already exists in another received payment. Please change with valid reference no.';
                        echo json_encode($json);
                    }
                } else {
                    $json['err'] = 'Invalid payment mode..';
                    echo json_encode($json);
                }
            }
        }
    }

    public function MIS() {
        $data['MIS'] = $this->Tasks->getMISData();
        $this->load->view('MIS/index', $data);
    }

    public function getRecoveryData($lead_id) {
        $getRecoveryData = $this->Tasks->getRecoveryData($lead_id);
        echo json_encode($getRecoveryData);
    }

    public function getPaymentVerification($refrence_no) {
        $data = $this->db->where('refrence_no', $refrence_no)->get('recovery')->row_array();
        echo json_encode($data);
    }

    public function verifyCustomerPayment() {
        $recovery_id = $this->input->post('recovery_id');
        $lead_id = $this->input->post('lead_id');
        $loan_no = "";

        if (empty($recovery_id)) {
            $loanDetails = $this->db->select('loan.loan_no, loan.customer_id')->where('lead_id', $lead_id)->from('loan')->get()->row();
            $loan_no = $loanDetails->loan_no;
            $customer_id = $loanDetails->customer_id;
        } else {
            $recoveryDetails = $this->db->select('recovery.loan_no')->where('recovery_id', $recovery_id)->from('recovery')->get()->row();
            $loan_no = $recoveryDetails->loan_no;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead Id', 'required|trim');
            $this->form_validation->set_rules('payment_amount', 'Payment Amount', 'required|trim');
            $this->form_validation->set_rules('refrence_no', 'Refrence No', 'required|trim');
            $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required|trim');
            $this->form_validation->set_rules('payment_type', 'Payment Type', 'required|trim');
            $this->form_validation->set_rules('discount', 'Discount', 'required|trim');
            $this->form_validation->set_rules('remark', 'Remarks', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $payment_amount = $this->input->post('payment_amount');
                $refrence_no = $this->input->post('refrence_no');
                $payment_mode = $this->input->post('payment_mode');
                $payment_type = $this->input->post('payment_type');
                $discount = $this->input->post('discount');
                $remark = $this->input->post('remark');
                $date_of_recived = $this->input->post('date_of_recived');

                $recovery_status = "Approved";
                $dataInsert = [
                    'lead_id' => $lead_id,
                    'customer_id' => $customer_id,
                    'loan_no' => $loan_no,
                    'payment_amount' => $payment_amount,
                    'refrence_no' => $refrence_no,
                    'payment_mode' => $payment_mode,
                    'status' => $payment_type,
                    'discount' => $discount,
                    'remarks' => $remark,
                    'recovery_status' => $recovery_status,
                    'date_of_recived' => $date_of_recived,
                    'noc' => "Yes",
                    'PaymentVerify' => 1,
                    'recovery_by' => $_SESSION['isUserSession']['user_id'],
                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                ];
                $data = [
                    'loan_no' => $loan_no,
                    'payment_amount' => $payment_amount,
                    'refrence_no' => $refrence_no,
                    'payment_mode' => $payment_mode,
                    'status' => $payment_type,
                    'discount' => $discount,
                    'remarks' => $remark,
                    'recovery_status' => $recovery_status,
                    'date_of_recived' => $date_of_recived,
                    'PaymentVerify' => 1,
                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                ];
                if (empty($recovery_id)) {
                    $result = $this->db->insert('recovery', $dataInsert);

                    $this->db->where('lead_id', $lead_id)->update('leads', ['status' => $payment_type]);

                    if ($payment_type == "Full Payment") {
                        $this->NOC_letter($loan_no);
                    }
                } else {
                    $result = $this->db->where('lead_id', $lead_id)->where('recovery_id', $recovery_id)->update('recovery', $data);
                    $this->db->where('lead_id', $lead_id)->update('leads', ['status' => $payment_type]);
                }

                if ($result == true) {
                    $json['msg'] = "Payment Approved Successfully.";
                    echo json_encode($json);
                } else {
                    $json['err'] = "Payment Failed to Approved.";
                    echo json_encode($json);
                }
            }
        }
    }

    public function send_settlement_letter($lead_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if (!empty($lead_id)) {
            $data = $this->Tasks->nocSettledPayment($lead_id);
            if ($data == "false") {
                $json['err'] = json_encode('Failed to Send Letter');
                echo json_encode($json);
            } else {
                $json['msg'] = json_encode('Settled Case NOC Letter Send Successfully.');
                echo json_encode($json);
            }
        } else {
            $json['err'] = 'lead Id is Required.';
            echo json_encode($json);
        }
    }

    public function send_recovery_loan_Noc_letter($lead_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }
        //print_R($lead_id);
        if (isset($lead_id) && !empty($lead_id)) {
            $data = $this->Tasks->nocRecoveryPaymentLoan($this->encrypt->encode($lead_id));

            if ($data == "false") {
                $json['err'] = json_encode('Failed to Send Letter');
                echo json_encode($json);
            } else {
                $json['msg'] = json_encode('Recovery Case NOC Letter Send Successfully.');
                echo json_encode($json);
            }
        } else {

            $json['err'] = 'lead Id is Required.';
            echo json_encode($json);
        }
    }

    public function send_closed_letter($lead_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }
        if (!empty($lead_id)) {
            $data = $this->Tasks->sent_loan_closed_noc_letter($this->encrypt->decode($lead_id));
            if ($data == "false") {
                $json['err'] = json_encode('Failed to Send Letter');
                echo json_encode($json);
            } else {
                $json['msg'] = json_encode('Closed Case NOC Letter Send Successfully.');
                echo json_encode($json);
            }
        } else {
            $json['err'] = 'lead Id is Required.';
            echo json_encode($json);
        }
    }

    public function get_scm_rm_details() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }
        $rmlist = array();
        $is_SCM = 0;

        if ($_SESSION['isUserSession']['role_id'] == 8) {
            echo 'Testing Right';
            $is_SCM = 1;
        }
        if ($is_SCM == 1) {
            echo 'Testing Collection';
            $rmlist['rmlist'] = $this->Collection->get_scm_rm_roles();
        }

        $rmlist['is_SCM'] = $is_SCM;

        echo json_encode($rmlist);
    }

    public function addToBlackList() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('reason_id', 'Reason', 'required|trim');
            $this->form_validation->set_rules('lead_id', 'Remark', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $lead_id = intval($this->input->post('lead_id'));
                $reason_id = intval($this->input->post('reason_id'));
                $reason_remark = strval($this->input->post('remark'));

                $reasonDetails = $this->db->select('m_br_name')->where(['m_br_id' => $reason_id])->from('master_blacklist_reject_reason')->get()->row();
                $reason_value = $reasonDetails->m_br_name;

                $lead_data = $this->Tasks->getCustomerDetails($lead_id);

                if (!empty($lead_data)) {

                    if ($lead_data['lead_black_list_flag'] == 1) {
                        $json['err'] = 'Application already added in black list.';
                    } else {
                        $black_list_data = [
                            'bl_lead_id' => (!empty($lead_data['lead_id']) ? $lead_data['lead_id'] : ""),
                            'bl_loan_no' => (!empty($lead_data['loan_no']) ? $lead_data['loan_no'] : ""),
                            'bl_customer_first_name' => (!empty($lead_data['first_name']) ? strtoupper($lead_data['first_name']) : ""),
                            'bl_customer_middle_name' => (!empty($lead_data['middle_name']) ? strtoupper($lead_data['middle_name']) : ""),
                            'bl_customer_sur_name' => (!empty($lead_data['sur_name']) ? strtoupper($lead_data['sur_name']) : ""),
                            'bl_customer_mobile' => (!empty($lead_data['mobile']) ? $lead_data['mobile'] : ""),
                            'bl_customer_alternate_mobile' => (!empty($lead_data['alternate_mobile']) ? $lead_data['alternate_mobile'] : ""),
                            'bl_customer_dob' => (!empty($lead_data['dob']) ? $lead_data['dob'] : ""),
                            'bl_customer_pancard' => (!empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : ""),
                            'bl_customer_email' => (!empty($lead_data['email']) ? strtoupper($lead_data['email']) : ""),
                            'bl_customer_alternate_email' => (!empty($lead_data['alternate_email']) ? strtoupper($lead_data['alternate_email']) : ""),
                            'bl_city_id' => (!empty($lead_data['city_id']) ? $lead_data['city_id'] : ""),
                            'bl_state_id' => (!empty($lead_data['state_id']) ? $lead_data['state_id'] : ""),
                            'bl_reason_id' => $reason_id,
                            'bl_reason_remark' => addslashes($reason_remark),
                            'bl_created_user_id' => $_SESSION['isUserSession']['user_id'],
                            'bl_created_on' => date("Y-m-d H:i:s"),
                        ];

                        $result = $this->db->insert('customer_black_list', $black_list_data);

                        $this->db->where('lead_id', $lead_id)->update('leads', ['lead_black_list_flag' => 1, 'updated_on' => date("Y-m-d H:i:s")]);

                        $insertLeadFollowupData = [
                            'lead_id' => $lead_id,
                            'user_id' => $_SESSION['isUserSessidon']['user_id'],
                            'status' => $lead_data['status'],
                            'stage' => $lead_data['stage'],
                            'lead_followup_status_id' => $lead_data['lead_status_id'],
                            'created_on' => date("Y-m-d H:i:s"),
                            'remarks' => "Application has been black listed.<br>Blacklist Reason : $reason_value<br>Executive Remark : $reason_remark"
                        ];

                        $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');

                        if ($result == true) {
                            $json['msg'] = 'Record added successfully to black list';
                        } else {
                            $json['err'] = 'Record can not ne deleted.';
                        }
                    }

                    echo json_encode($json);
                } else {
                    $json['err'] = 'Application Details does not exist.';
                    echo json_encode($json);
                }
            }
        }
    }

    public function get_followup_template_lists() {

        $result_array = array();

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('followup_type_id', 'Followup Type ID', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $followup_type_id = $this->input->post('followup_type_id');
                $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));

                if (in_array($followup_type_id, [2, 3, 4])) { // 2=>SMS, 3=>WHATSAPP, 4=>EMAIL
                    $followup_template_id = intval($this->input->post('followup_template_id'));

                    if (!empty($followup_template_id)) {
                        $temp_data = $this->Collection->get_template_content($followup_type_id, $followup_template_id, $lead_id);
                    } else {
                        $temp_data = $this->Collection->get_template_lists($followup_type_id);
                    }

                    if (!empty($temp_data['status'])) {
                        $result_array = $temp_data['data'];
                    } else {
                        $result_array['err'] = "No Record Found.";
                    }
                }
            }
        } else {
            $result_array['err'] = "Invalid Request. try again";
        }

        echo json_encode($result_array);
    }

    public function collection_payment_verification() {
        $result_array = array();

        // $lead_id = $this->input->post('lead_id');
        $temp_data = $this->Collection->get_master_payment_mode();

        if (!empty($temp_data['status'])) {

            $result_array = array();
            // $lead_id = $this->input->post('lead_id');
            $temp_data = $this->Collection->get_master_payment_mode();

            if (empty($temp_data['status'])) {

                $result_array['master_payment_mode'] = $temp_data['data']['payment_mode_list'];
            }

            $result_array['master_payment_mode'] = $temp_data['data']['payment_mode_list'];
            echo json_encode($result_array);
        }
        //return $result_array;
        // echo json_encode($result_array);
    }

    public function confirm_is_cfe_visit_completed() {


        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('visit_id', 'Visit ID', 'required|trim');
            $this->form_validation->set_rules('flag', 'Approval Decision', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $visit_id = $this->input->post('visit_id');
                $flag = $this->input->post('flag');

                if (!empty($flag)) {
                    $conditions['col_visit_id'] = $visit_id;
                    $update_data['col_fe_visit_approval_status'] = $flag;
                    $update_data['col_fe_visit_approval_user_id'] = $_SESSION['isUserSession']['user_id'];
                    $update_data['col_fe_visit_approval_datetime'] = date("Y-m-d H:i:s");

                    $result = $this->Tasks->updateLeads($conditions, $update_data, 'loan_collection_visit');

                    $json['msg'] = 'CFE visit verified successfully.';
                    echo json_encode($json);
                } else {
                    $json['err'] = 'Please select approval decision.';
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = 'Invalid request asccess. try again!';
            echo json_encode($json);
            return false;
        }
    }

    public function generateEazyPayRepaymentLink() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('repay_loan_amount', 'Repayment Amount', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = strip_tags(validation_errors());
                echo json_encode($json);
            } else {

                $user_id = $this->input->post('user_id');

                $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));
                $repay_loan_amount = doubleval($this->input->post('repay_loan_amount'));

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $repay_encrypted_url = $CommonComponent->payday_repayment_api($user_id, $lead_id, $repay_loan_amount);

                if ($repay_encrypted_url['status'] == 1) {
                    $json['msg'] = "Repayment URL encrypted successfully.";
                    $json['repay_encrypted_url'] = $repay_encrypted_url['data'];
                } else {
                    $json['err'] = $repay_encrypted_url['errors'];
                    $json['repay_encrypted_url'] = "";
                }

                echo json_encode($json);
            }
        } else {
            $json['err'] = 'Invalid request asccess. try again!';
            echo json_encode($json);
            return false;
        }
    }

    public function save_reloan_collection_feedback() {

        $user_id = $_SESSION['isUserSession']['user_id'];

        if (empty($user_id)) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {


            $this->form_validation->set_rules('enc_lead_id', 'lead_id', 'required|trim');
            $this->form_validation->set_rules('remarks', 'Remarks', 'required|trim');
            $this->form_validation->set_rules('reloan_flag', 'Reloan', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = strip_tags(validation_errors());
                echo json_encode($json);
            } else {

                $lead_id = intval($this->encrypt->decode($this->input->post('enc_lead_id')));

                if (!empty($lead_id)) {

                    $getLeadDetail = $this->getLeadDetail(['lead_id' => $lead_id]);

                    $leadDetails = $getLeadDetail->row_array();

                    if (!empty($leadDetails)) {

                        $remarks = $this->input->post('remarks');
                        $reloan_flag = $this->input->post('reloan_flag');

                        $reloan_data = [
                            'customer_executive_reloan_flag' => $reloan_flag,
                            'customer_executive_reloan_remark' => addslashes($remarks),
                            'customer_executive_reloan_user_id' => $user_id,
                            'customer_executive_reloan_datetime' => date("Y-m-d H:i:s")
                        ];

                        $this->db->where('customer_lead_id', $lead_id)->update('lead_customer', $reloan_data);

                        if (!empty($leadDetails['pancard'])) {

                            $cif_data = [
                                'cif_executive_reloan_flag' => $reloan_flag,
                                'cif_executive_reloan_remark' => $remarks,
                                'cif_executive_reloan_user_id' => $user_id,
                                'cif_executive_reloan_datetime' => date("Y-m-d H:i:s")
                            ];

                            $this->db->where('cif_pancard', $leadDetails['pancard'])->update('cif_customer', $cif_data);
                        }

                        $remarks = 'Eligible For Reloan : ' . ($reloan_flag == 1 ? "YES" : "NO") . '<br>Remarks : ' . $remarks;

                        $insertLeadFollowupData = [
                            'lead_id' => $lead_id,
                            'customer_id' => $leadDetails['customer_id'],
                            'user_id' => $user_id,
                            'status' => $leadDetails['status'],
                            'stage' => $leadDetails['stage'],
                            'lead_followup_status_id' => $leadDetails['lead_status_id'],
                            'created_on' => date('Y-m-d H:i:s'),
                            'remarks' => $remarks
                        ];

                        $this->Tasks->insert($insertLeadFollowupData, 'lead_followup');
                        $json['msg'] = 'Remarks has been successfully updated.';
                        echo json_encode($json);
                    } else {
                        $json['err'] = 'Lead Details does not exist.';
                        echo json_encode($json);
                        return false;
                    }
                } else {
                    $json['err'] = 'Lead ID cannot be empty.';
                    echo json_encode($json);
                    return false;
                }
            }
        } else {
            $json['err'] = 'Invalid request asccess. try again!';
            echo json_encode($json);
            return false;
        }
    }

    public function downloadNocSettlementLetter($lead_id) {


        if (!empty($lead_id)) {

            $return_array = array("status" => 0, "err" => "");

            $query = $this->db->query("SELECT lead_id, loan_noc_settlement_letter,loan_settled_date FROM loan WHERE loan_noc_settlement_letter IS NOT NULL AND loan_noc_settlement_letter !='' AND lead_id=$lead_id");

            if ($query->num_rows() > 0) {
                $data = $query->row();
                $file_name = $data->loan_noc_settlement_letter;

                $return_array['file_name'] = $file_name;
                $return_array['status'] = 1;
            } else {
                $return_array['err'] = "NOC Settlement Letter does not exist.";
            }

            echo json_encode($return_array);
        }
    }

    public function downloadLegalNoticeLetter($enc_lead_id) {

        if (!empty($enc_lead_id)) {

            $return_array = array("status" => 0, "err" => "");

            $query = $this->db->query("SELECT lead_id, legal_notice_letter FROM loan WHERE legal_notice_letter IS NOT NULL AND legal_notice_letter !='' AND lead_id=$enc_lead_id");

            if ($query->num_rows() > 0) {
                $data = $query->row();

                $file_name = $data->legal_notice_letter;

                $return_array['file_name'] = $file_name;
                $return_array['status'] = 1;
            } else {
                $return_array['err'] = "Legal Notice Letter does not exist.";
            }

            echo json_encode($return_array);
        }
    }


    public function downloadNocSettlementClosingLetter($enc_lead_id) {

        if (!empty($enc_lead_id)) {
            $lead_id = $enc_lead_id;
            // $lead_id = intval($this->encrypt->decode($enc_lead_id));
            $return_array = array("status" => 0, "err" => "");

            $query = $this->db->query("SELECT lead_id, loan_noc_closing_letter FROM loan WHERE loan_noc_closing_letter IS NOT NULL AND loan_noc_closing_letter !='' AND lead_id=$lead_id");

            if ($query->num_rows() > 0) {
                $data = $query->row();
                $file_name = $data->loan_noc_closing_letter;

                $return_array['file_name'] = $file_name;
                $return_array['status'] = 1;
            } else {
                $return_array['err'] = "NOC Closing Letter does not exist.";
            }

            echo json_encode($return_array);
        }
    }

    public function generateRepayLinkMail($lead_id) {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }

        if (!empty($lead_id)) {

            $amount = $this->input->post('amount');

            $data = $this->Tasks->generate_repay_link_mail($lead_id, $_SESSION['isUserSession']['user_id'], $amount);

            if ($data == 1) {
                $json['msg'] = json_encode('Mail Sent Successfully.');
                echo json_encode($json);
            } else {
                $json['err'] = json_encode('Failed to Send Mail');
                echo json_encode($json);
            }
        } else {
            $json['err'] = 'lead Id is Required.';
            echo json_encode($json);
        }
    }
}
