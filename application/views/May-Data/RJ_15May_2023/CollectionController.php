<?php

defined('BASEPATH') OR exit('No direct script access allowed');

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

            $collection_followup_type_id = htmlspecialchars($_POST['collection_followup_type_id']);
            $lead_id = $this->encrypt->decode($this->input->post('lead_id'));

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
                        $followup_type_id = $this->input->post('collection_followup_type_id', true);
                        $followup_status_id = $this->input->post('collection_followup_status_id', true);
                        $collection_next_schedule_date = $this->input->post('collection_next_schedule_date', true);
                        $followup_remarks = $this->input->post('followup_remarks', true);
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
                        $sms_data['sms_primary_id'] = $this->input->post("collection_followup_sms_primary_id", true);
                        $sms_data['sms_template_content'] = $this->input->post("collection_followup_sms_content", true);

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
                        $email_data['email_template_id'] = $this->input->post("c_followup_email_template_id", true);
                        $email_data['email_subject'] = $this->input->post("email_subject", true);
                        $email_data['email_cc_user'] = $this->input->post("email_cc_user", true);
                        $email_data['email_body'] = $this->input->post("email_body", true);

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
            $lead_id = $this->encrypt->decode($leadID);

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
            $lead_id = $this->encrypt->decode($leadID);

            $visit_list = $this->Collection->get_list_collection_visit($lead_id);
            $result['data'] = $visit_list['data'];
            $result['msg'] = "Success";
        }

        echo json_encode($result);
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
            $lead_id = $this->encrypt->decode($leadID);
            $visit_type_id = $this->input->post('visit_type_id'); // 1 => residence, 2 =>office

            $conditions['LD.lead_id'] = $lead_id;
            $conditions['URL.user_rl_location_type_id'] = 2; // 1 => city, 2 =>state, 3 => branch
            $conditions['visit_type_id'] = $visit_type_id;

            $scm_user_lists['status'] = 0;
            $cfe_user_lists['status'] = 0;

            if (in_array(agent, ['CO1'])) {
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

            if (in_array($_SESSION['isUserSession']['labels'], ['CO1', 'CO2', 'CO3'])) {
                $this->form_validation->set_rules('visit_type_id', 'Visit Type', 'required|trim');
            }

            if (in_array($_SESSION['isUserSession']['labels'], ['CO1'])) {
                $this->form_validation->set_rules('visit_scm_user_id', 'SCM Name', 'required|trim');
            }

            if (in_array($_SESSION['isUserSession']['labels'], ['CO2', 'CO3'])) {
                $this->form_validation->set_rules('col_visit_id', 'Visit Reference ID', 'trim'); // required|
            }

            if (in_array($_SESSION['isUserSession']['labels'], ['CO2', 'CO3']) && $_POST['visit_status_id'] != 3) {
                $this->form_validation->set_rules('visit_rm_user_id', 'Assign User Name', 'required|trim');
            }

            if (in_array($_SESSION['isUserSession']['labels'], ['CO2', 'CO3', 'CFE1']) && $_POST['visit_status_id'] != 3) {
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

                        if (in_array($_SESSION['isUserSession']['labels'], ['CO1'])) {
                            $conditions_send_email['ce_user_id'] = $_SESSION['isUserSession']['user_id'];
                            $conditions_send_email['scm_user_id'] = $scm_user_id;
                            $col_visit_field_status_id = 1; // Pending

                            $insert_request_visit_data['col_visit_scm_id'] = $scm_user_id;
                            $insert_request_visit_data['col_visit_requested_by'] = $_SESSION['isUserSession']['user_id'];
                            $insert_request_visit_data['col_visit_requested_datetime'] = date('Y-m-d H:i:s');
                            $insert_request_visit_data['col_visit_requested_by_remarks'] = $remarks;
                        }

                        if (in_array($_SESSION['isUserSession']['labels'], ['CO2', 'CO3'])) {
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

                        if (in_array($_SESSION['isUserSession']['labels'], ['CFE1'])) {
                            unset($insert_request_visit_data['col_visit_address_type']);
                            $col_visit_field_status_id = $visit_status_id; // 2=>Assign, 3=>Cancel, 4=>Hold, 5=>Completed
                            $insert_request_visit_data['col_visit_field_remarks'] = $remarks;
                            $insert_request_visit_data['col_visit_field_datetime'] = date('Y-m-d H:i:s');
                        }

                        $this->Collection->send_email_for_visit($conditions_send_email);

                        $insert_request_visit_data['col_visit_field_status_id'] = $col_visit_field_status_id;

                        if (in_array($_SESSION['isUserSession']['labels'], ['CO1', 'CO2', 'CO3']) && empty($lead_visit_id)) {
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
                        } else if (in_array($_SESSION['isUserSession']['labels'], ['CO2', 'CO3'])) {
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
                        } else if (in_array($_SESSION['isUserSession']['labels'], ['CFE1'])) {

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
        $fetch = 'LD.lead_id, LD.customer_id, LD.status, LD.stage, LD.lead_status_id,LD.lead_black_list_flag,LD.loan_no, LD.lead_data_source_id';
        return $this->Tasks->select($conditions, $fetch, 'leads LD');
    }

    public function getCAMDetail($conditions) {
        $fetch = 'CAM.cam_id, CAM.lead_id, CAM.customer_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi';
        return $this->Tasks->select($conditions, $fetch, 'credit_analysis_memo CAM');
    }

    public function calculateRepaymentAmount($lead_id) {
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();
        $repay = $CommonComponent->get_loan_repayment_details($lead_id);
        $repayment = $repay['repayment_data'];
        return $repayment;
    }

    public function calculateRepaymentAmount_vin20220618($lead_id) {
        $conditions = ['lead_id' => $lead_id];
        $sql = $this->getLeadDetail($conditions);
        $leads = $sql->row();
        $sqlLoan = $this->getLoanDetail($conditions);
        $loan = $sqlLoan->row();
        $fetch = 'CO.date_of_recived, CO.recovery_status';
        $sql1 = $this->getCAMDetail($conditions);
        $collection_conditions = ['CO.lead_id' => $lead_id, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
        $sql2 = $this->db->select($fetch)->where($collection_conditions)->from('collection CO')->order_by('CO.id', 'desc')->limit(1)->get();

        $status = $leads->status;
        $stage = $leads->stage;

        $today_data = date('d-m-Y');
        $loan_recommended = 0;
        $roi = 0;
        $panel_roi = 0;
        $disbursal_date = '-';
        $repayment_date = '-';
        $tenure = 0;
        $repayment_amount = 0;
        $d_of_r = '-';

        if ($sql1->num_rows() > 0) {
            $cam = $sql1->row();
            $loan_recommended = $cam->loan_recommended;
            $roi = $cam->roi;
            $panel_roi = $roi + $roi;
            $disbursal_date = date('d-m-Y', strtotime($cam->disbursal_date));
            $repayment_date = date('d-m-Y', strtotime($cam->repayment_date));
            $tenure = $cam->tenure;
            $repayment_amount = $cam->repayment_amount;
        }
        if ($sql2->num_rows() > 0) {
            $collection = $sql2->row();
            $d_of_r = date('d-m-Y', strtotime($collection->date_of_recived));
        }

        if ($status == "CLOSED" || $status == "SETTLED") {
            $date1 = strtotime($d_of_r);
            $date2 = strtotime($disbursal_date);
            $date3 = strtotime($repayment_date);
            $date5 = strtotime($d_of_r);
            $diff = $date1 - $date2;
        } else {
            $date1 = strtotime($today_data);
            $date2 = strtotime($disbursal_date);
            $date3 = strtotime($repayment_date);
            $date5 = strtotime($d_of_r);
            $diff = $date1 - $date2;
        }

        $tenure = ($date3 - $date2) / (60 * 60 * 24);
        $repayment_amount = $loan_recommended + (($loan_recommended * $roi * $tenure) / 100);
        $rtenure = '';
        $ptenure = '';

        if ($date1 <= $date3) {
            $realdays = $date1 - $date2;
            $rtenure = ($realdays / 60 / 60 / 24);
        } else {
            $realdays = $date3 - $date2;
            $rtenure = ($realdays / 60 / 60 / 24);
        }

        if ($date1 <= $date3) {
            $realdays = $date1 - $date2;
            $ptenure = 0;
        } else {
            $endDate = $date1 - $date3;
            $oneDay = (60 * 60 * 24);
            $dateDays60 = ($oneDay * 60);
            $date4 = ($date3 + $dateDays60); // stopped LPI days

            if ($endDate <= $dateDays60) {
                $realdays = $date3 - $date2;
                $rtenure = ($realdays / 60 / 60 / 24);
                $paneldays = $date1 - $date3;
                $ptenure = ($paneldays / 60 / 60 / 24);
            } else {
                $ptenure = 60;
            }
        }

        $msg = "";

        if ($status == "CLOSED") {
            if ($date5 <= $date3) {
                $paidBeforeDays = ($date3 - $date5) / (60 * 60 * 24);
                $rtenure = $tenure - $paidBeforeDays;
                $ptenure = 0;
                $msg = ' - paid before ' . $paidBeforeDays . ' Days back';
            }
        }

        $realIntrest = ($loan_recommended * $roi * $rtenure) / 100;
        $penaltyIntrest = ($loan_recommended * ($panel_roi) * $ptenure) / 100;
        $repaymentAmt = ($loan_recommended + $realIntrest + $penaltyIntrest);

        $fetch3 = 'SUM(CO.received_amount) as total_paid';
        // $conditions3 = ['CO.payment_verification' => 1, 'CO.lead_id' => $lead_id, 'CO.recovery_status !=' => 2];
        // $sql13 = $this->Tasks->select($conditions3, $fetch3, 'collection CO');
        $conditions3 = ['payment_verification' => 1, 'lead_id' => $lead_id, 'collection_active' => 1, 'collection_deleted' => 0];
        $sql13 = $this->db->select('SUM(received_amount) as total_paid')->where($conditions3)->from('collection')->get();
        $recoveredAmount = $sql13->row();

        $ReceivedAmount = 0;
        if ($recoveredAmount->total_paid > 0) {
            $ReceivedAmount = $recoveredAmount->total_paid;
        }
        $todalDue = $repaymentAmt - $ReceivedAmount;

        $data['loan_no'] = $loan->loan_no;
        $data['lead_black_list_flag'] = !empty($leads->lead_black_list_flag) ? $leads->lead_black_list_flag : '';
        $data['status'] = $status;
        $data['disbursal_date'] = $disbursal_date;
        $data['repayment_date'] = $repayment_date;
        $data['loan_recommended'] = $loan_recommended;
        $data['roi'] = $roi;
        $data['panel_roi'] = $panel_roi;
        $data['tenure'] = $tenure;
        $data['penalty_days'] = round($ptenure);
        $data['real_interest'] = round($realIntrest);
        $data['penality_interest'] = round($penaltyIntrest);
        $data['repayment_amount'] = round($repayment_amount);
        $data['total_repayment_amount'] = round($repaymentAmt);
        $data['total_received_amount'] = round($ReceivedAmount);
        $data['total_due_amount'] = round($todalDue);
        return $data;
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
                $lead_id = $this->encrypt->decode($this->input->post('lead_id'));
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
                $lead_id = $this->input->post('lead_id');
                $sql = $this->collectionDetails($lead_id);
                $data['recoveryData'] = $this->Tasks->getRecoveryData($sql);

                echo json_encode($data);
            }
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
//            $this->form_validation->set_rules('customer_id', 'Customer Id', 'required|trim');
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
            if (agent == 'CO1' || agent == "CO2" || agent == "CR2") {
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
                $recovery_id = $this->input->post('recovery_id',true);
                $lead_id = $this->input->post('lead_id',true);
                $customer_id = $this->input->post('customer_id');

                $user_id = $_SESSION['isUserSession']['user_id'];
                $company_id = $this->input->post('company_id',true);
                $product_id = $this->input->post('product_id',true);
                $received_amount = $this->input->post('received_amount',true);
                $refrence_no = $this->input->post('refrence_no',true);
                $payment_mode_id = $this->input->post('payment_mode',true);
                $repayment_type = $this->input->post('repayment_type',true);
                $discount = $this->input->post('discount',true);
                $refund = $this->input->post('refund',true);
                $scm_remarks = $this->input->post('scm_remarks',true);
                $closure_remarks = $this->input->post('ops_remarks',true);
                $payment_verification = $this->input->post('payment_verification',true);
                $paymentSlips = "";
                $collected_by = $this->input->post('collected_by',true);
                $getLeadStatus = $this->db->select('status_name as status, status_stage as stage')->where('status_id', $repayment_type)->from('master_status')->get()->row_array();

                $sqlRecovery = $this->collectionDetails($lead_id, $refrence_no);
                $cond = ['lead_id' => $lead_id];

                $payment_mode_name = "";
                $temp_payment_mode = $this->Collection->get_master_payment_mode($payment_mode_id);

                if (!empty($temp_payment_mode['status']) && !empty($payment_mode_id)) {
                    $payment_mode_data = $temp_payment_mode['data']['payment_mode_list'][0];
                    $payment_mode_name = $payment_mode_data['payment_mode_name'];

                    $sql = $this->getLoanDetail($cond);
                    $loan = $sql->row();

                    $sql = $this->getLeadDetail($cond);
                    $leadDetails = $sql->row();

                    if (!empty($recovery_id)) {
                        if (agent == 'CO1' || agent == "CO2" || agent == 'CR2') {
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
                                'collection_executive_user_id' => $_SESSION['isUserSession']['user_id'],
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

                                        if (($total_payment_received + $received_amount + $discount) == $total_repayment_amount) {
                                            $update_loan_array['loan_interest_discount_amount'] = $discount;
                                        } else {
                                            $json['err'] = "Loan clousre amounts is incorrect.";
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

                                if ($result) {

                                    if (!empty($update_loan_array)) {
                                        $update_loan_array['loan_total_discount_amount'] = $discount;
                                        $this->Tasks->globalUpdate(['lead_id' => $lead_id], $update_loan_array, 'loan');
                                    }

                                    $this->calculateRepaymentAmount($lead_id);

                                    if ($repayment_type == 16 && !in_array($leadDetails->lead_data_source_id, array(21, 27, 33))) {//Only for closed case NOC Sent instant basis.
                                        $data = $this->Tasks->sent_loan_closed_noc_letter($lead_id);

                                        if ($data == "false") {
                                            $json['err'] = json_encode('Payment verfied. But failed to sent fullpayment noc letter. Please try again.');
                                            echo json_encode($json);
                                        } else {
                                            $json['msg'] = json_encode('Payment verfied. Fullpayment NOC Letter sent successfully.');
                                            echo json_encode($json);
                                        }
                                    } else {
                                        $json['msg'] = json_encode('Payment verified successfully.');
                                        echo json_encode($json);
                                    }
                                } else {
                                    $json['err'] = json_encode('Unable to verify payment at this time. Please try again.');
                                    echo json_encode($json);
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
                            }
                        }
                    } else if ($sqlRecovery->num_rows() == 0) {
                        if (agent == 'CO1' || agent == "CO2" || agent == "CR2") {

                            $upload_return = uploadDocument($_FILES, $lead_id);

                            if ($upload_return['status'] == 1) {
                                $paymentSlips = $upload_return['file_name'];
                            } else {
                                $json['err'] = 'Please upload the screenshot!';
                                echo json_encode($json);
                                exit;
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
                            'collection_executive_user_id' => $_SESSION['isUserSession']['user_id'],
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

    public function send_closed_letter($lead_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired.';
            echo json_encode($json);
            return false;
        }
        if (!empty($lead_id)) {
            $data = $this->Tasks->sent_loan_closed_noc_letter($lead_id);
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
            $is_SCM = 1;
        }
        if ($is_SCM == 1) {
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

                $lead_id = $this->encrypt->decode($this->input->post('lead_id'));
                $reason_id = $this->input->post('reason_id', true);
                $reason_remark = $this->input->post('remark',true);

                $reasonDetails = $this->db->select('m_br_name')->where(['m_br_id' => $reason_id])->from('master_blacklist_reject_reason')->get()->row();
                $reason_value = $reasonDetails->m_br_name;

                $lead_data = $this->Tasks->getCustomerDetails($lead_id);

                if (!empty($lead_data)) {

                    if ($lead_data['lead_black_list_flag'] == 1) {
                        $json['err'] = 'Application already added in black list.';
                    } else {
                        $black_list_data = [
                            'bl_lead_id' => (!empty($lead_data['lead_id']) ? $lead_data['lead_id'] : ""),
                            'bl_loan_no' => (!empty($lead_data['loan_no']) ? htmlspecialchars($lead_data['loan_no']) : ""),
                            'bl_customer_first_name' => (!empty($lead_data['first_name']) ? strtoupper(htmlspecialchars($lead_data['first_name'])) : ""),
                            'bl_customer_middle_name' => (!empty($lead_data['middle_name']) ? strtoupper(htmlspecialchars($lead_data['middle_name'])) : ""),
                            'bl_customer_sur_name' => (!empty($lead_data['sur_name']) ? strtoupper(htmlspecialchars($lead_data['sur_name'])) : ""),
                            'bl_customer_mobile' => (!empty($lead_data['mobile']) ? htmlspecialchars($lead_data['mobile']) : ""),
                            'bl_customer_alternate_mobile' => (!empty($lead_data['alternate_mobile']) ? htmlspecialchars($lead_data['alternate_mobile']) : ""),
                            'bl_customer_dob' => (!empty($lead_data['dob']) ? $lead_data['dob'] : ""),
                            'bl_customer_pancard' => (!empty($lead_data['pancard']) ? strtoupper(htmlspecialchars($lead_data['pancard'])) : ""),
                            'bl_customer_email' => (!empty($lead_data['email']) ? strtoupper(htmlspecialchars($lead_data['email'])) : ""),
                            'bl_customer_alternate_email' => (!empty($lead_data['alternate_email']) ? strtoupper(htmlspecialchars($lead_data['alternate_email'])) : ""),
                            'bl_city_id' => (!empty($lead_data['city_id']) ? htmlspecialchars($lead_data['city_id']) : ""),
                            'bl_state_id' => (!empty($lead_data['state_id']) ? htmlspecialchars($lead_data['state_id'] ): ""),
                            'bl_reason_id' => $reason_id,
                            'bl_reason_remark' => addslashes($reason_remark),
                            'bl_created_user_id' => $_SESSION['isUserSession']['user_id'],
                            'bl_created_on' => date("Y-m-d H:i:s"),
                        ];

                        $result = $this->db->insert('customer_black_list', $black_list_data);

                        $this->db->where('lead_id', $lead_id)->update('leads', ['lead_black_list_flag' => 1, 'updated_on' => date("Y-m-d H:i:s")]);

                        $insertLeadFollowupData = [
                            'lead_id' => $lead_id,
                            'user_id' => $_SESSION['isUserSession']['user_id'],
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
                $lead_id = $this->input->post('lead_id');

                if (in_array($followup_type_id, [2, 3, 4])) { // 2=>SMS, 3=>WHATSAPP, 4=>EMAIL
                    $followup_template_id = $this->input->post('followup_template_id');

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
            $result_array['master_payment_mode'] = $temp_data['data']['payment_mode_list'];
        }
        echo json_encode($result_array);
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
                $lead_id = $this->input->post('lead_id');
                $repay_loan_amount = $this->input->post('repay_loan_amount');

                require_once (COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();
                $repay_encrypted_url = $CommonComponent->payday_repayment_api($lead_id, $repay_loan_amount);

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

}

?>
