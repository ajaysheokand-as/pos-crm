<?php

defined('BASEPATH') or exit('No direct script access allowed');

class AuditController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo";
    public $tbl_loan = "loan";
    public $tbl_collection = "tbl_collection";
    public $tbl_audit = "audit";

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Status_Model', 'Status');
        $this->load->model('Collection_Model', 'Collection');
        $login = new IsLogin();
        $login->index();
    }

    public function get_list_audit_followup($leadID) {
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
                $leadId = $this->input->post('lead_id');
                $user_id = $this->input->post('user_id');
                $audit   = $this->input->post('audit');
                //echo $lead_id.'-'.$user_id.'-'.$audit;die;
                $followup_list = $this->Tasks->get_list_audit_followup($lead_id);
                $result['data'] = $followup_list['data'];
                $result['msg'] = "Success";
            }
        }
        echo json_encode($result);
    }

    public function resonForApprovalLoan() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('comment', 'Comment', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id         = intval($this->encrypt->decode($this->input->post('lead_id')));
                $user_id         = $_SESSION['isUserSession']['user_id'];
                $comment         = $this->input->post('comment');
                $audit           = $this->input->post('audit');
                if (empty($lead_id)) {
                    $json['err'] = "Missing Lead id";
                    echo json_encode($json);
                } else if (empty($comment)) {
                    $json['err'] = "Missing comments";
                    echo json_encode($json);
                } else {
                    $leadsDetails = $this->Tasks->select(['lead_id' => $lead_id], 'first_name, email, mobile, lead_status_id', 'leads');
                    if ($leadsDetails->num_rows() > 0) {
                        $leads = $leadsDetails->row();
                        $lead_audit_list = $this->db->select('*')->where(['audit_lead_id' => $lead_id])->from('lead_audit')->order_by('id', 'DESC')->get()->row();
                        if (!empty($lead_audit_list->id)) {
                            $audit_status = (!empty($lead_audit_list->audit_status) && $lead_audit_list->audit_status != '') ? $lead_audit_list->audit_status : "";
                            $stage = (!empty($lead_audit_list->stage) && $lead_audit_list->stage != '') ? $lead_audit_list->stage : "";
                            $audit_case_type_id = (!empty($lead_audit_list->audit_case_type_id) && $lead_audit_list->audit_case_type_id != '') ? $lead_audit_list->audit_case_type_id : "";
                            $audit_lead_status_id = (!empty($lead_audit_list->audit_lead_status_id) && $lead_audit_list->audit_lead_status_id != '') ? $lead_audit_list->audit_lead_status_id : "";
                            $audit_assign_date_time = (!empty($lead_audit_list->audit_assign_date_time) && $lead_audit_list->audit_assign_date_time != '') ? $lead_audit_list->audit_assign_date_time : "";
                            $audit_assign_user_id = (!empty($lead_audit_list->audit_assign_user_id) && $lead_audit_list->audit_assign_user_id != '') ? $lead_audit_list->audit_assign_user_id : "";
                        }
                        $lead_followup_audit = array(
                            'audit_lead_id' => $lead_id,
                            'audit_user_id' => $user_id,
                            'audit_remarks' => $comment,
                            'audit_status' => $audit_status,
                            'audit_stage' => $stage,
                            'audit_case_type_id' => $audit_case_type_id,
                            'audit_lead_status_id' => $audit_lead_status_id,
                            'audit_assign_date_time' => $audit_assign_date_time,
                            'audit_assign_user_id' => $audit_assign_user_id
                        );
                        $this->Tasks->insert($lead_followup_audit, 'lead_audit');
                        $json['msg'] = 'Application approval successfully.';
                        echo json_encode($json);
                    } else {
                        $json['err'] = 'Application Details does not exist.';
                        echo json_encode($json);
                    }
                }
            }
        } else {
            $json['err'] = "Lead Id is missing";
            echo json_encode($json);
        }
    }

    public function sendToPreAudit() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id  = intval($this->encrypt->decode($this->input->post('lead_id')));
                $user_id  = $_SESSION['isUserSession']['user_id'];
                $reason   = 'This case goes to for Audit.';
                $audit_type = $this->input->post('audit_type');
                if (empty($lead_id)) {
                    $json['err'] = "Missing Lead id";
                    echo json_encode($json);
                } else {
                    $status = "PRE-AUDIT-NEW";
                    $stage = "S31";
                    $status_id = 43;

                    $lead_followup_data = array(
                        'customer_id' => ($customer_id) ? $customer_id : "",
                        'lead_id' => $lead_id,
                        'user_id' => $user_id,
                        'status' => $status,
                        'stage' => $stage,
                        'remarks' => $reason,
                        'lead_followup_status_id' => $status_id,
                        'created_on' => date('Y-m-d H:i:s')
                    );
                    $this->Tasks->insert($lead_followup_data, 'lead_followup');
                    $lead_audit_list = $this->db->select('*')->where(['audit_lead_id' => $lead_id])->from('lead_audit')->order_by('id', 'DESC')->get()->row();
                    if (!empty($lead_audit_list->id)) {
                        $audit_status = (!empty($lead_audit_list->audit_status) && $lead_audit_list->audit_status != '') ? $lead_audit_list->audit_status : "";
                        $stage = (!empty($lead_audit_list->stage) && $lead_audit_list->stage != '') ? $lead_audit_list->stage : "";
                        $audit_case_type_id = (!empty($lead_audit_list->audit_case_type_id) && $lead_audit_list->audit_case_type_id != '') ? $lead_audit_list->audit_case_type_id : "";
                        $audit_lead_status_id = (!empty($lead_audit_list->audit_lead_status_id) && $lead_audit_list->audit_lead_status_id != '') ? $lead_audit_list->audit_lead_status_id : "";
                        $audit_assign_date_time = (!empty($lead_audit_list->audit_assign_date_time) && $lead_audit_list->audit_assign_date_time != '') ? $lead_audit_list->audit_assign_date_time : "";
                        $audit_assign_user_id = (!empty($lead_audit_list->audit_assign_user_id) && $lead_audit_list->audit_assign_user_id != '') ? $lead_audit_list->audit_assign_user_id : "";
                    } else {
                        $audit_status =  $status;
                        $stage = $stage;
                        $audit_case_type_id = $audit_type;
                        $audit_assign_date_time = date('Y-m-d H:i:s');
                    }
                    $lead_followup_audit = array(
                        'audit_lead_id' => $lead_id,
                        'audit_user_id' => '',
                        'audit_remarks' => $reason,
                        'audit_status' => $audit_status,
                        'audit_stage' => $stage,
                        'audit_case_type_id' => $audit_case_type_id,
                        'audit_lead_status_id' => $status_id,
                        'audit_assign_date_time' => $audit_assign_date_time,
                        'audit_assign_user_id' => $user_id
                    );
                    $this->Tasks->insert($lead_followup_audit, 'lead_audit');
                    $this->db->where(['lead_id' => $lead_id])->update('loan', ['loan_audit_type' => $audit_type]);
                    $response_assigned_audit = $this->db->where(['lead_id' => $lead_id])->update('leads', ['status' => "PRE-AUDIT-NEW", 'stage' => "S31", 'lead_status_id' => 43, 'lead_audit_assign_user_id' => $user_id, 'lead_audit_assign_date_time' => date('Y-m-d H:i:s')]);
                    $json['msg'] = 'Sent to back successfully for audit.';
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = "Lead Id is missing";
            echo json_encode($json);
        }
    }

    public function sendToPostAudit() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id  = intval($this->encrypt->decode($this->input->post('lead_id')));
                $user_id  = $_SESSION['isUserSession']['user_id'];
                $reason   = 'This case goes to for Audit.';
                $audit_type = $this->input->post('audit_type');
                if (empty($lead_id)) {
                    $json['err'] = "Missing Lead id";
                    echo json_encode($json);
                } else {
                    $get_lead_detail = $this->db->select('email,mobile,status,stage,lead_status_id')->where('lead_id', $lead_id)->from('leads')->get()->row_array();
                    $status = $get_lead_detail['status'];
                    $stage = $get_lead_detail['stage'];
                    $status_id = $get_lead_detail['lead_status_id'];
                    $lead_followup_data = array(
                        'customer_id' => ($customer_id) ? $customer_id : "",
                        'lead_id' => $lead_id,
                        'user_id' => $user_id,
                        'status' => $status,
                        'stage' => $stage,
                        'remarks' => $reason,
                        'lead_followup_status_id' => $status_id,
                        'created_on' => date('Y-m-d H:i:s')
                    );
                    $this->Tasks->insert($lead_followup_data, 'lead_followup');
                    $lead_audit_list = $this->db->select('*')->where(['audit_lead_id' => $lead_id])->from('lead_audit')->order_by('id', 'DESC')->get()->row();
                    if (!empty($lead_audit_list->id)) {
                        $audit_status = (!empty($lead_audit_list->audit_status) && $lead_audit_list->audit_status != '') ? $lead_audit_list->audit_status : "";
                        $stage = (!empty($lead_audit_list->stage) && $lead_audit_list->stage != '') ? $lead_audit_list->stage : "";
                        $audit_case_type_id = (!empty($lead_audit_list->audit_case_type_id) && $lead_audit_list->audit_case_type_id != '') ? $lead_audit_list->audit_case_type_id : "";
                        $audit_lead_status_id = (!empty($lead_audit_list->audit_lead_status_id) && $lead_audit_list->audit_lead_status_id != '') ? $lead_audit_list->audit_lead_status_id : "";
                        $audit_assign_date_time = (!empty($lead_audit_list->audit_assign_date_time) && $lead_audit_list->audit_assign_date_time != '') ? $lead_audit_list->audit_assign_date_time : "";
                        $audit_assign_user_id = (!empty($lead_audit_list->audit_assign_user_id) && $lead_audit_list->audit_assign_user_id != '') ? $lead_audit_list->audit_assign_user_id : "";
                    } else {
                        $audit_status =  $status;
                        $stage = $stage;
                        $audit_case_type_id = $audit_type;
                        $audit_assign_date_time = date('Y-m-d H:i:s');
                    }
                    $lead_followup_audit = array(
                        'audit_lead_id' => $lead_id,
                        'audit_user_id' => '',
                        'audit_remarks' => $reason,
                        'audit_status' => $audit_status,
                        'audit_stage' => $stage,
                        'audit_case_type_id' => $audit_case_type_id,
                        'audit_lead_status_id' => $status_id,
                        'audit_assign_date_time' => $audit_assign_date_time,
                        'audit_assign_user_id' => $user_id
                    );
                    $this->Tasks->insert($lead_followup_audit, 'lead_audit');
                    $this->db->where(['lead_id' => $lead_id])->update('loan', ['loan_audit_type' => $audit_type, 'loan_post_audit_flag' => 1]);
                    $response_assigned_audit = $this->db->where(['lead_id' => $lead_id])->update('leads', ['lead_audit_assign_user_id' => $user_id, 'lead_audit_assign_date_time' => date('Y-m-d H:i:s')]);
                    $json['msg'] = 'Sent to back successfully for audit.';
                    echo json_encode($json);
                }
            }
        } else {
            $json['err'] = "Lead Id is missing";
            echo json_encode($json);
        }
    }

    public function allocatePreAudit() {
        $user_id = $_SESSION['isUserSession']['user_id'] ?? null;
        if (!$user_id) {
            echo json_encode(['err' => "Session Expired"]);
            return;
        }

        $checkList = $_POST["checkList"] ?? [];
        if (empty($checkList)) {
            echo json_encode(['err' => "Please select at least one record"]);
            return;
        }

        $login_user_name = $_SESSION['isUserSession']['name'];
        $lead_remark = "Lead allocate by self - " . $login_user_name;
        $status = "AUDIT-INPROCESS";
        $status_id = 45;
        $stage = "S32";
        $current_time = date('Y-m-d H:i:s');

        $update_lead_data = [
            'status' => $status,
            'lead_status_id' => $status_id,
            'stage' => $stage,
            'lead_audit_assign_user_id' => $user_id,
            'lead_audit_assign_date_time' => $current_time
        ];

        $insert_lead_followup = [
            'user_id' => $user_id,
            'status' => $status,
            'stage' => $stage,
            'created_on' => $current_time,
            'lead_followup_status_id' => $status_id,
            'remarks' => $lead_remark
        ];

        foreach ($checkList as $lead_id) {

            $query = $this->Tasks->select(['lead_id' => $lead_id], "lead_id,  lead_status_id", 'leads');
            $lead_details = $query->row();

            if ($lead_details->lead_status_id != 44) {
                continue;
            }

            $this->Tasks->updateLeads(['lead_id' => $lead_id], $update_lead_data, 'leads');
            $insert_lead_followup['lead_id'] = $lead_id;
            $this->Tasks->insert($insert_lead_followup, 'lead_followup');
        }
        echo json_encode(['msg' => "Leads allocated successfully"]);
    }

    public function auditSendBack() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            echo json_encode(['errSession' => "Session Expired"]);
            return;
        }

        $lead_id = $this->input->post('lead_id');
        $remarks = $this->input->post('remark');

        if ($lead_id) {
            $lead_id = $this->encrypt->decode($lead_id);
            $status = "APPLICATION-SEND-BACK";
            $stage = "S11";
            $lead_status_id = 11;
            $current_time = date("Y-m-d H:i:s");
            $user_id = $_SESSION['isUserSession']['user_id'];

            $update_lead_data = [
                'status' => $status,
                'stage' => $stage,
                'audit_send_back' => 1,
                'lead_status_id' => $lead_status_id,
                'updated_on' => $current_time
            ];

            $insert_lead_followup = [
                'lead_id' => $lead_id,
                'user_id' => $user_id,
                'status' => $status,
                'stage' => $stage,
                'lead_followup_status_id' => $lead_status_id,
                'remarks' => $remarks,
                'created_on' => $current_time
            ];

            $conditions = ['lead_id' => $lead_id];

            $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');
            $this->Tasks->insert($insert_lead_followup, 'lead_followup');

            echo json_encode(['msg' => $remarks]);
        }
    }

    public function auditLeadRecommend() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            echo json_encode(['errSession' => "Session Expired"]);
            return;
        }

        $lead_id = $this->input->post('lead_id');
        if (!$lead_id) {
            return;
        }

        $lead_id = $this->encrypt->decode($lead_id);
        $status = "AUDIT-RECOMMENDED";
        $stage = "S34";
        $lead_status_id = 47;
        $remarks = 'Audit Recommended';
        $current_time = date("Y-m-d H:i:s");
        $user_id = $_SESSION['isUserSession']['user_id'];

        $update_lead_data = [
            'status' => $status,
            'stage' => $stage,
            'lead_status_id' => $lead_status_id,
            'updated_on' => $current_time
        ];

        $insert_lead_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $status,
            'stage' => $stage,
            'lead_followup_status_id' => $lead_status_id,
            'remarks' => $remarks,
            'created_on' => $current_time
        ];

        $conditions = ['lead_id' => $lead_id];
        $this->Tasks->updateLeads($conditions, $update_lead_data, 'leads');
        $this->Tasks->insert($insert_lead_followup, 'lead_followup');

        echo json_encode(['msg' => $remarks]);
    }
}
