<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RejectionController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Admin_Model');
        $this->load->model('Users/Rejection_Model', 'Reject');
        $this->load->model('Users/Email_Model', 'Email');
        // $this->load->model('SMS_Model', 'SMS');
        $this->load->model('Users/SMS_Model', 'SMS');
        $this->load->model('CAM_Model', 'CAM');
        $this->load->library('encrypt');

        $login = new IsLogin();
        $login->index();
    }

    public function resonForRejectLoan() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            $this->form_validation->set_rules('reason', 'Reject reason', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {


                $lead_id         = intval($this->encrypt->decode($this->input->post('lead_id')));
                $user_id         = $_SESSION['isUserSession']['user_id'];
                $customer_id     = strval($this->input->post('customer_id'));
                $reason_id       = strval($this->input->post('reason'));
                $reject_remark   = strval($this->input->post('reject_remark'));
                $audit           = $this->input->post('audit');
                if (empty($lead_id)) {
                    $json['err'] = "Missing Lead id";
                    echo json_encode($json);
                } else if (empty($reason_id)) {
                    $json['err'] = "Missing reason id";
                    echo json_encode($json);
                } else {

                    $leadsDetails = $this->Tasks->select(['lead_id' => $lead_id], 'first_name, email, mobile, lead_status_id', 'leads');

                    if ($leadsDetails->num_rows() > 0) {

                        $leads = $leadsDetails->row();

                        if (in_array($leads->lead_status_id, array(14, 16, 17, 18, 19))) {
                            $json['err'] = "Rejection is not allowed.";
                            echo json_encode($json);
                            return false;
                        } else if (in_array($leads->lead_status_id, array(12)) && agent != 'CA') {
                            $json['err'] = "Rejection is not allowed..";
                            echo json_encode($json);
                            return false;
                        }

                        $rejectDetails = $this->Reject->getRejectionReasonMaster(['id' => $reason_id]);

                        if ($rejectDetails->num_rows() > 0) {
                            $rejectDetails = $rejectDetails->row();

                            if (empty($reject_remark)) {
                                $reason = $rejectDetails->reason;
                            } else {
                                $reason = "Reason: " . $rejectDetails->reason . "<br>Remark: " . $reject_remark . "</br>";
                            }

                            $sms_sent_flag = $rejectDetails->sms_sent_flag;
                            $email_sent_flag = $rejectDetails->email_sent_flag;

                            $status = "REJECT";
                            $stage = "S9";
                            $status_id = 9;

                            $lead_data = array(
                                'status' => $status,
                                'stage' => $stage,
                                'lead_status_id' => $status_id,
                                'lead_rejected_reason_id' => $reason_id,
                                'lead_rejected_user_id' => $user_id,
                                'lead_rejected_datetime' => date('Y-m-d H:i:s'),
                                'updated_on' => date('Y-m-d H:i:s')
                            );

                            $tempDetails = $this->Tasks->select(['lrr_lead_id' => $lead_id, 'lrr_active' => 1, 'lrr_deleted' => 0], '*', 'lead_rejection_reasons');

                            $lead_rejection_count = $tempDetails->num_rows();

                            if ($lead_rejection_count > 0 && $lead_rejection_count <= NON_CONTACTABLE_ROTATE_COUNTER) {
                                $lead_data['lead_rejected_assign_user_id'] = NULL;
                                $lead_data['lead_rejected_assign_datetime'] = NULL;
                                unset($lead_data['lead_rejected_user_id']);
                                unset($lead_data['lead_rejected_datetime']);
                                $sms_sent_flag = 0;
                                $email_sent_flag = 0;
                            }

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

                            if ($sms_sent_flag) {
                                $sms_type_id = 4;
                                require_once (COMPONENT_PATH . 'CommonComponent.php');
                                $CommonComponent = new CommonComponent();
                                $CommonComponent->payday_sms_api($sms_type_id, $lead_id, ['lead_id' => $lead_id, 'mobile' => $leads->mobile]);
                            }

                            if ($email_sent_flag) {
                                $to_email = $leads->email;
                                $this->Reject->send_rejection_mail($lead_id, $lead_data, $to_email);
                            }

                            $conditions = ['lead_id' => $lead_id];

                            $result = $this->Tasks->updateLeads($conditions, $lead_data,'leads');
                            $this->Tasks->insert($lead_followup_data,'lead_followup'); 
                            if(!empty($audit)){
                                $lead_audit_list = $this->db->select('*')->where(['audit_lead_id'=>$lead_id])->from('lead_audit')->order_by('id','DESC')->get()->row();
                                if(!empty($lead_audit_list->id)){
                                   $audit_status = (!empty($lead_audit_list->audit_status) && $lead_audit_list->audit_status != '') ? $lead_audit_list->audit_status : ""; 
                                   $stage = (!empty($lead_audit_list->stage) && $lead_audit_list->stage != '') ? $lead_audit_list->stage : ""; 
                                   $audit_case_type_id = (!empty($lead_audit_list->audit_case_type_id) && $lead_audit_list->audit_case_type_id != '') ? $lead_audit_list->audit_case_type_id : ""; 
                                   $audit_lead_status_id = (!empty($lead_audit_list->audit_lead_status_id) && $lead_audit_list->audit_lead_status_id != '') ? $lead_audit_list->audit_lead_status_id : ""; 
                                   $audit_assign_date_time = (!empty($lead_audit_list->audit_assign_date_time) && $lead_audit_list->audit_assign_date_time != '') ? $lead_audit_list->audit_assign_date_time : ""; 
                                   $audit_assign_user_id = (!empty($lead_audit_list->audit_assign_user_id) && $lead_audit_list->audit_assign_user_id != '') ? $lead_audit_list->audit_assign_user_id : ""; 
                                }
                                $lead_followup_audit = array(
                                        'audit_lead_id' => $lead_id,
                                        'audit_user_id' => $_SESSION['isUserSession']['user_id'],
                                        'audit_remarks' => $reason,                                
                                        'audit_status' => $status,
                                        'audit_stage' => $stage,
                                        'audit_case_type_id' => $audit_case_type_id,
                                        'audit_lead_status_id' => $status_id,
                                        'audit_assign_date_time' => $audit_assign_date_time,
                                        'audit_assign_user_id' => $audit_assign_user_id
                                );
                                $this->Tasks->insert($lead_followup_audit,'lead_audit');                             
                            }
                            if ($result == true) {

                                $json['msg'] = 'Application Rejected Successfully.';

                                $insert_lead_reject_again = [
                                    'lrr_lead_id' => $lead_id,
                                    'lrr_user_id' => $user_id,
                                    'lrr_rejected_reason_id' => $reason_id,
                                    'lrr_rejected_remarks' => $reason,
                                    'lrr_rejected_datetime' => date("Y-m-d H:i:s"),
                                ];

                                $this->db->insert('lead_rejection_reasons', $insert_lead_reject_again);
                            } else {
                                $json['err'] = 'Failed to Reject Application.';
                            }
                            echo json_encode($json);
                        } else {
                            $json['err'] = 'Invalid reason selected';
                            echo json_encode($json);
                        }
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

    public function getRejectionReasonMaster() {
        if (product_id == 1) {
            $product_id = 1;
            if ($_SESSION['isUserSession']['labels'] == 'CR1') {
                $whereOnRole = 'user_access = "1" or user_access = "2"';
            } else if ($_SESSION['isUserSession']['labels'] == 'CR2') {
                $whereOnRole = 'user_access = "1" or user_access = "2"';
            } else if ($_SESSION['isUserSession']['labels'] == 'CR3') {
                $whereOnRole = 'user_access = "3" ';
            } else if ($_SESSION['isUserSession']['labels'] == 'DS1') {
                $whereOnRole = 'user_access = "4" ';
            } else {
                $whereOnRole = 'user_access = "1" or user_access = "2" or user_access = "3" or user_access = "4" ';
            }
        } else if (product_id == 2) {
            $product_id = 2;
            $whereOnRole = 'user_access = "1" ';
        }

        $where = ' company_id = "' . company_id . '" and product_id = "' . $product_id . '" and status = "1" and (' . $whereOnRole . ')';
        $rejectionList = $this->Reject->getRejectionReasonMaster($where);
        $data['rejectionLists'] = $rejectionList->result();
        echo json_encode($data);
    }

    public function rejectedTaskList() {
        $data['rejectedLists'] = $this->Tasks->rejectedTask();
        $this->load->view('Tasks/RejectTaskList', $data);
    }

    public function rejectedLeadDetails($lead_id) {
        $lead_id = intval($this->encrypt->decode($lead_id));
        $rejectedLists = $this->Tasks->rejectedLeadDetails($lead_id);
        echo json_encode($rejectedLists);
    }
}

?>
