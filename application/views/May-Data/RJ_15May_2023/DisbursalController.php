<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class DisbursalController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_customer_banking = 'customer_banking CB';
    private $payment_mode_array = array(1 => "Online", 2 => "Offline");
    private $payment_method_array = array(1 => "IMPS", 2 => "NEFT");

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', "Tasks");
        $this->load->model('Disburse_Model', 'DM');
        $this->load->model('Product_Model', 'PM');
        $this->load->model('CAM_Model', 'CAM');
        $this->load->model('Emails_Model');

        $login = new IsLogin();
        $login->index();
    }

    public function getCAMDetails($lead_id) {
        return $this->Tasks->getCAMDetails($lead_id);
    }

    public function leadFollowUpUser($lead_id) {
        $conditions2 = ['LF.lead_id' => $lead_id];
        $fetch = 'U.user_id, U.labels, U.name, LF.created_on';
        $table11 = 'lead_followup LF';
        $table12 = 'users U';
        $join12 = 'LF.user_id = U.user_id';
        $followUpUser = $this->Tasks->join_two_table_with_where($conditions2, $fetch, $table11, $table12, $join12);

        $processed_by = '-';
        $processed_on = '-';
        $sanctioned_by = '-';
        $sanctioned_on = '-';

        if ($followUpUser->num_rows() > 0) {
            foreach ($followUpUser->result() as $row) {
                if ($row->labels == "CR1") {
                    $processed_by = $row->name;
                    $processed_on = date('d-m-Y h:i', strtotime($row->created_on));
                } else if ($row->labels == "CR2") {
                    $sanctioned_by = $row->name;
                    $sanctioned_on = date('d-m-Y h:i', strtotime($row->created_on));
                }
            }
        }
        $data = [
            'processed_by' => $processed_by,
            'processed_on' => $processed_on,
            'sanctioned_by' => $sanctioned_by,
            'sanctioned_on' => $sanctioned_on,
        ];
        return $data;
    }

    public function getSanctionDetails() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired';
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('customer_id', 'Company ID', 'trim');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
                $leadData = $this->getCAMDetails($lead_id);
                $data['camDetails'] = $leadData->row();

                // $data['LeadFollowup'] = $this->leadFollowUpUser($lead_id);
                echo json_encode($data);
            }
        }
    }

    public function sendDisbursalMail($lead_id) {
        return $this->Tasks->sendDisbursalMail($lead_id);
    }

    public function resendDisbursalMail() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired';
            echo json_encode($json);
        } else if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));

                $sql = $this->Tasks->select(['lead_id' => $lead_id], 'lead_status_id', 'leads');

                $getLeadDetails = $sql->row();

                if (!empty($getLeadDetails->lead_status_id)) {


                    if ($getLeadDetails->lead_status_id == 12) {

                        $sendLetter = $this->Tasks->sendSanctionMail($lead_id);
                        $this->Tasks->sendSanctionSMS($lead_id);

                        if ($sendLetter['status'] == 1) {

                            $loan_data = [
                                'loanAgreementRequest' => 1,
                                'agrementRequestedDate' => date("Y-m-d H:i:s")
                            ];

                            $conditions = ['lead_id' => $lead_id];
                            $this->Tasks->updateLeads($conditions, $loan_data, 'loan');

                            $json['msg'] = "Sanction Letter email sent successfully.";
                            echo json_encode($json);
                        } else {
                            $json['err'] = $sendLetter['error'];
                            echo json_encode($json);
                        }
                    } else if ($getLeadDetails->lead_status_id == 14) {

                        $sendLetter = $this->Tasks->sendDisbursalMail($lead_id);

                        if ($sendLetter == 1) {
                            $json['msg'] = "Disbursal email sent successfully.";
                            echo json_encode($json);
                        } else {
                            $json['err'] = "Disbursal failed to sent email. try again!";
                            echo json_encode($json);
                        }
                    } else {
                        $json['err'] = "Application has been move to next step.";
                        echo json_encode($json);
                    }
                } else {
                    $json['err'] = "Lead id is not valid.";
                    echo json_encode($json);
                }
            }
        }
    }

    public function getCustomerBanking() {
        $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
        if (!empty($lead_id)) {

            $fetch = 'CB.id, CB.customer_id, CB.lead_id, CB.bank_name, CB.ifsc_code, CB.branch,CB.account_status_id,CB.beneficiary_name, CB.account, CB.confirm_account, CB.account_type, CB.account_status, CB.remark, DATE_FORMAT(CB.created_on,"%d-%m-%Y %H:%i:%s") as created_on, DATE_FORMAT(CB.updated_on,"%d-%m-%Y %H:%i:%s") as updated_on';

            $conditions = ['CB.lead_id' => $lead_id, 'CB.customer_banking_active' => 1, 'CB.customer_banking_deleted' => 0];

            $allDisbursalBank = $this->Tasks->select($conditions, $fetch, $this->tbl_customer_banking);
            $data['allDisbursalBankCount'] = $allDisbursalBank->num_rows();
            $data['allDisbursalBank'] = $allDisbursalBank->result();

            $disbursalBank = $this->getCustomerDisbBanking($lead_id);
            $data['disbursalBankCount'] = $disbursalBank->num_rows();
            $data['disbursalBank'] = $disbursalBank->row();
            echo json_encode($data);
        }
    }

    public function getCustomerDisbBanking($lead_id) {
        $fetch = 'CB.id, CB.customer_id, CB.lead_id,CB.beneficiary_name, CB.bank_name, CB.ifsc_code, CB.branch, CB.account, CB.confirm_account, CB.account_type, CB.account_status, CB.remark, DATE_FORMAT(CB.created_on,"%d-%m-%Y %H:%i:%s") as created_on, DATE_FORMAT(CB.updated_on,"%d-%m-%Y %H:%i:%s") as updated_on';
        $conditions = ['CB.lead_id' => $lead_id, 'CB.customer_banking_active' => 1, 'CB.customer_banking_deleted' => 0, 'CB.account_status_id' => 1];
        return $this->Tasks->select($conditions, $fetch, $this->tbl_customer_banking);
    }

    public function getCustomerBankDetails() {
        echo "here";
    }
        if (!isset($_REQUEST['searchTerm'])) {
            $json = [];
        } else {
            $search = $_REQUEST['searchTerm'];
            print_r($search);
            $sql = "SELECT bank.bank_id, bank.bank_ifsc FROM tbl_bank_details as bank
		                WHERE bank_ifsc LIKE '%" . $search . "%' LIMIT 10";
            $result = $this->db->query($sql);
            $bankData = $result->result_array();
            print_r($bankData);
            foreach ($bankData as $row) {
                $json[] = ['bank_id' => $row['bank_id'], 'bank_ifsc' => $row['bank_ifsc']];
            }
        }
        echo json_encode($json);
    }

    public function getBankNameByIfscCode() {
        if (!empty($this->input->post('ifsc_code'))) {
            $ifsc_code = $this->input->post('ifsc_code');
            $result = $this->db->select('bank.bank_name, bank.bank_branch')->where('bank_ifsc', $ifsc_code)->from('tbl_bank_details as bank')->get()->row();
            echo json_encode($result);
        }
    }

    public function addBeneficiary() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            $this->form_validation->set_rules('beneficiary_name', 'Beneficiary Name', 'required|trim');
            $this->form_validation->set_rules('bankA_C_No', 'Bank A/C No', 'required|trim');
            $this->form_validation->set_rules('confBankA_C_No', 'Conf Bank A/C No', 'required|trim');
            $this->form_validation->set_rules('customer_ifsc_code', 'Customer ifsc Code', 'required|trim');
            $this->form_validation->set_rules('customer_bank_ac_type', 'Customers Bank A/C Type', 'required|trim');
            $this->form_validation->set_rules('customer_bank_name', 'Customer Bank Name', 'required|trim');
            $this->form_validation->set_rules('customer_bank_branch', 'Customer Bank Branch', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $data = [
                    'lead_id' => $this->encrypt->decode($this->input->post('lead_id',true)),
                    'customer_id' => $this->input->post('customer_id',true),
                    'user_id' => $this->input->post('user_id',true),
                    'beneficiary_name' => $this->input->post('beneficiary_name',true),
                    // 'company_id' 				=> $this->input->post('company_id'),
                    'account' => $this->input->post('bankA_C_No',true),
                    'confirm_account' => $this->input->post('confBankA_C_No',true),
                    'ifsc_code' => $this->input->post('customer_ifsc_code',true),
                    'account_type' => $this->input->post('customer_bank_ac_type',true),
                    'bank_name' => $this->input->post('customer_bank_name',true),
                    'branch' => $this->input->post('customer_bank_branch',true),
                    'account_status' => 'NO',
                    'account_status_id' => 0,
                    'created_by' => $_SESSION['isUserSession']['user_id'],
                    'created_on' => date("Y-m-d H:i:s")
                ];

                $result = $this->Tasks->insert($data, 'customer_banking');
                if ($result == 1) {
                    $json['msg'] = 'Beneficiary Added Successfully.';
                } else {
                    $json['err'] = 'Beneficiary Failed to Add. try again';
                }
                echo json_encode($json);
            }
        }
    }

    public function verifyDisbursalBank() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|trim');
            // $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            $this->form_validation->set_rules('list_bank_AC_No', 'Please Select Bank A/C No', 'required|trim');
            $this->form_validation->set_rules('bank_ac_verification', 'Bank A/C Verification', 'required|trim');
            $this->form_validation->set_rules('remarks', 'Remarks', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {

                $id = $this->input->post('list_bank_AC_No',true);
                $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
                $verification_type_id = $this->encrypt->decode($this->input->post('bank_ac_verification',true));

                $master_bank_account_status = $this->Tasks->getBankAccountStatusList();

                if (!empty($master_bank_account_status[$verification_type_id])) {

                    if ($verification_type_id == 1) {

                        if (ENVIRONMENT == 'production' || true) {

                            require_once (COMPONENT_PATH . 'CommonComponent.php');

                            $CommonComponent = new CommonComponent();

                            $return_bank_verification_array = $CommonComponent->payday_bank_account_verification_api($lead_id, array('cust_banking_id' => $id));

                            if ($return_bank_verification_array['status'] != 1) {

                                $json['err'] = $return_bank_verification_array['error_msg'];
                                echo json_encode($json);
                                exit;
                            }
                        }
                    }

                    $conditions2 = ['CB.lead_id' => $lead_id, "CB.account_status_id" => 1];
                    $data2 = ['CB.account_status' => "NO", "CB.account_status_id" => 0];
                    $this->Tasks->globalUpdate($conditions2, $data2, $this->tbl_customer_banking);

                    $data = [
                        'account_status' => $master_bank_account_status[$verification_type_id],
                        'account_status_id' => $verification_type_id,
                        'remark' => $this->input->post('remarks'),
                        'updated_on' => date("Y-m-d H:i:s")
                    ];

                    $conditions = ['CB.id' => $id];
                    $result = $this->Tasks->globalUpdate($conditions, $data, $this->tbl_customer_banking);
                    if ($result == 1) {
                        $json['msg'] = 'Verified Successfully.';
                    } else {
                        $json['err'] = 'Failed to Verify. try again';
                    }
                } else {
                    $json['err'] = 'Account status is out of range.';
                }
                echo json_encode($json);
            }
        }
    }

    public function allowDisbursalToBank() {
        if ($this->input->post('user_id') == '') {
            $json['errSession'] = 'Session Expired';
            json_encode($json);
            return false;
        } else {
            if ($this->input->server('REQUEST_METHOD') == 'POST') {
                $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
                $this->form_validation->set_rules('company_id', 'Company ID', 'required|trim');
                $this->form_validation->set_rules('product_id', 'Product ID', 'required|trim');
                $this->form_validation->set_rules('user_id', 'Session Expired', 'required|trim');
                $this->form_validation->set_rules('payableAccount', 'Payable Account', 'required|trim');
                $this->form_validation->set_rules('channel', 'Channel', 'required|trim');
                $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required|trim');
                $this->form_validation->set_rules('payable_amount', 'Payable Amount', 'required|trim');
                $this->form_validation->set_rules('disbursal_date', 'Disbursal Date', 'required|trim');
                $this->form_validation->set_rules('disbursal_remarks', 'Remarks', 'required|trim');
                if ($this->form_validation->run() == FALSE) {
                    $json['err'] = validation_errors();
                    echo json_encode($json);
                } else {
                    $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
                    $disbursal_date = $this->input->post('disbursal_date');
                    $payment_method_id = $this->input->post('channel',true);
                    $payment_mode_id = $this->input->post('payment_mode',true);
                    $disbursal_remarks = $this->input->post('disbursal_remarks',true);

                    $disbursement_bank_array = $this->Tasks->getDisbursementBankList();

                    $payableAccount = $disbursement_bank_array[$this->input->post('payableAccount')];

                    $leadDetails = $this->db->select('lead_id, status, stage, lead_status_id, customer_id, pancard')->where(['lead_id' => $lead_id])->from('leads')->get()->row_array();

                    if (empty($leadDetails)) {
                        $json['err'] = 'Application does not exist.';
                        echo json_encode($json);
                        return false;
                    } else if (empty($payableAccount)) {
                        $json['err'] = 'Payable Account is out of range';
                        echo json_encode($json);
                        return false;
                    } else if (empty($this->payment_mode_array[$payment_mode_id])) {
                        $json['err'] = 'Payment mode is out of range';
                        echo json_encode($json);
                        return false;
                    } else if (empty($this->payment_method_array[$payment_method_id])) {
                        $json['err'] = 'Payment method is out of range';
                        echo json_encode($json);
                        return false;
                    } else if (empty($lead_id)) {
                        $json['err'] = 'Lead reference cannot be zero';
                        echo json_encode($json);
                        return false;
                    } else if (empty($disbursal_date)) {
                        $json['err'] = 'Disbursement Date cannot be blank';
                        echo json_encode($json);
                        return false;
                    } else if (empty($disbursal_remarks)) {
                        $json['err'] = 'Disbursement remarks cannot be blank';
                        echo json_encode($json);
                        return false;
                    } else if ($payableAccount['disb_bank_imps_api_active'] == 1 && $payment_method_id == 1 && $payment_mode_id == 2) {
                        $json['err'] = 'Disbursement has to be made online using IMPS.';
                        echo json_encode($json);
                        return false;
                    } else if ($payableAccount['disb_bank_neft_api_active'] == 1 && $payment_method_id == 2 && $payment_mode_id == 2) {
                        $json['err'] = 'Disbursement has to be made online using NEFT.';
                        echo json_encode($json);
                        return false;
                    } else if ($payableAccount['disb_bank_imps_api_active'] == 0 && $payment_method_id == 1 && $payment_mode_id == 1) {
                        $json['err'] = 'Disbursement has to be made offline using IMPS.';
                        echo json_encode($json);
                        return false;
                    } else if ($payableAccount['disb_bank_neft_api_active'] == 0 && $payment_method_id == 2 && $payment_mode_id == 1) {
                        $json['err'] = 'Disbursement has to be made offline using NEFT.';
                        echo json_encode($json);
                        return false;
                    }

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



                    $isAnotherLeadInprocess = $this->Tasks->isAnotherLeadInprocess($lead_id);

                    if ($isAnotherLeadInprocess->num_rows() > 0) {
                        $another_lead = $isAnotherLeadInprocess->row();
                        $json['err'] = 'Already one application ' . $another_lead->lead_id . ' of same customer ' . $another_lead->first_name . ' with status - ' . $another_lead->status . ' is In process.[Error-D01]';
                        echo json_encode($json);
                        return false;
                    }

                    $isBlackListed = $this->Tasks->checkBlackListedCustomer($lead_id);

                    if ($isBlackListed['status'] == 1) {
                        $json['err'] = $isBlackListed['error_msg'];
                        echo json_encode($json);
                        return false;
                    }


                    $fetch = 'CAM.cam_id, CAM.loan_recommended, CAM.roi, CAM.processing_fee_percent, CAM.disbursal_date, CAM.repayment_date';

                    $query = $this->Tasks->select(['CAM.lead_id' => $lead_id, 'CAM.cam_active' => 1, 'CAM.cam_deleted' => 0], $fetch, "credit_analysis_memo CAM");
                    $camDetails = $query->row();

                    $query = $this->Tasks->select(['lead_id' => $lead_id, 'loan_active' => 1, 'loan_deleted' => 0], "loan_id, loan_no", "loan L");
                    $loanDetails = $query->row();

                    if (!empty($camDetails) && !empty($loanDetails)) {

                        $Arr_input = [
                            'loan_recommended' => $camDetails->loan_recommended,
                            'roi' => $camDetails->roi,
                            'processing_fee_percent' => $camDetails->processing_fee_percent,
                            'disbursal_date' => date('Y-m-d', strtotime($disbursal_date)),
                            'repayment_date' => $camDetails->repayment_date
                        ];

                        $calcAmount = $this->Tasks->calcAmount($Arr_input);

                        $CamData = [
                            'tenure' => $calcAmount['tenure'],
                            'repayment_amount' => $calcAmount['repayment_amount'],
                            'admin_fee' => $calcAmount['admin_fee'],
                            'adminFeeWithGST' => $calcAmount['adminFeeWithGST'],
                            'total_admin_fee' => $calcAmount['total_admin_fee'],
                            'disbursal_date' => date('Y-m-d', strtotime($disbursal_date)),
                        ];

                        $this->Tasks->updateLeads(['cam_id' => $camDetails->cam_id], $CamData, 'credit_analysis_memo');

                        $lead_followup = [
                            'lead_id' => $lead_id,
                            'user_id' => $_SESSION['isUserSession']['user_id'],
                            'status' => $leadDetails["status"],
                            'stage' => $leadDetails["stage"],
                            'lead_followup_status_id' => $leadDetails["lead_status_id"],
                            'remarks' => addslashes($disbursal_remarks),
                            'created_on' => date("Y-m-d H:i:s")
                        ];

                        $this->Tasks->insert($lead_followup, 'lead_followup');

                        //Loading config for disbursement api
                        $this->load->helper('integration/payday_disbursement_api');

                        $loan_data = [
                            'loan_disbursement_bank_id' => $payableAccount['disb_bank_id'],
                            'loan_disbursement_payment_mode_id' => $payment_mode_id,
                            'loan_disbursement_payment_type_id' => $payment_method_id,
                            'mode_of_payment' => $this->payment_mode_array[$payment_mode_id],
                            'company_account_no' => $payableAccount['disb_bank_account_no'],
                            'channel' => $this->payment_method_array[$payment_method_id],
                            'recommended_amount' => $calcAmount['net_disbursal_amount'],
//                                'status' => 'DISBURSED',
//                                'loan_status_id' => 14,
                            'updated_by' => $_SESSION['isUserSession']['user_id'],
                            'updated_on' => date("Y-m-d H:i:s")
                        ];

                        $this->Tasks->updateLeads(['loan_id' => $loanDetails->loan_id], $loan_data, 'loan');

                        $request_array = array();
                        $request_array['bank_id'] = $payableAccount["disb_bank_id"];
                        $request_array['payment_mode_id'] = $payment_mode_id;
                        $request_array['payment_type_id'] = $payment_method_id;

                        $disbursement_return_array = payday_loan_disbursement_call($lead_id, $request_array);

                        if ($disbursement_return_array['status'] == 1) {

                            $user_type = "NEW";

                            $cif_query = $this->db->select('*')->where('cif_pancard', $leadDetails['pancard'])->from('cif_customer')->get();

                            if ($cif_query->num_rows() > 0) {
                                $cif_result = $cif_query->row();

                                $isdisbursedcheck = $cif_result->cif_loan_is_disbursed;

                                if ($isdisbursedcheck > 0) {
                                    $user_type = "REPEAT";
                                } else {
                                    $user_type = "NEW";
                                }
                            }

                            if ($payment_mode_id == 1) {



                                $loan_reference_no = $disbursement_return_array['payment_reference'];
                                $status = 'DISBURSED';
                                $stage = 'S14';
                                $lead_status_id = 14;

                                $loan_data = [
                                    'disburse_refrence_no' => $loan_reference_no,
                                    'status' => $status,
                                    'loan_status_id' => $lead_status_id,
                                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];

                                $lead_followup = [
                                    'lead_id' => $lead_id,
                                    'user_id' => $_SESSION['isUserSession']['user_id'],
                                    'status' => $status,
                                    'stage' => $stage,
                                    'lead_followup_status_id' => $lead_status_id,
                                    'remarks' => "DISBURSED - Online",
                                    'created_on' => date("Y-m-d H:i:s")
                                ];

                                $lead_data = [
                                    'user_type' => $user_type,
                                    'status' => $status,
                                    'stage' => $stage,
                                    'lead_status_id' => $lead_status_id,
                                    'loan_no' => $loanDetails->loan_no,
                                    'lead_disbursal_approve_user_id' => $_SESSION['isUserSession']['user_id'],
                                    'lead_disbursal_approve_datetime' => date("Y-m-d H:i:s"),
                                    'lead_final_disbursed_date' => date("Y-m-d"),
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];

                                $result2 = $this->Tasks->updateLeads(['loan_id' => $loanDetails->loan_id], $loan_data, 'loan');
                                $result3 = $this->Tasks->insert($lead_followup, 'lead_followup');
                                $result1 = $this->Tasks->updateLeads(['lead_id' => $lead_id], $lead_data, 'leads');

                                $cif_data_update = [
                                    'cif_loan_is_disbursed' => 1,
                                    'cif_updated_by' => $_SESSION['isUserSession']['user_id'],
                                    'cif_updated_on' => date("Y-m-d H:i:s")
                                ];

                                if (!empty($leadDetails["customer_id"])) {
                                    $this->Tasks->updateLeads(['cif_number' => $leadDetails["customer_id"]], $cif_data_update, 'cif_customer');
                                }

                                if ($result1 == 1 && $result2 == 1 && $result3 == 1) {

                                    $this->Tasks->sendDisbursalMail($lead_id); // Send disbursal letter
                                    $this->Tasks->sendDisbursalSms($lead_id); // Send Disbursal SMS

                                    $json['msg'] = 'Loan Disbursed Successfully using online api.';
                                    echo json_encode($json);
                                } else {
                                    $json['err'] = 'Data updation error. Please check offline.';
                                    echo json_encode($json);
                                }
                            } else {

                                $loan_data = [
                                    'status' => 'DISBURSED',
                                    'loan_status_id' => 14,
                                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];

                                $this->Tasks->updateLeads(['loan_id' => $loanDetails->loan_id], $loan_data, 'loan');

                                $lead_data = [
                                    'user_type' => $user_type,
                                    'loan_no' => $loanDetails->loan_no,
                                    'lead_disbursal_approve_user_id' => $_SESSION['isUserSession']['user_id'],
                                    'lead_disbursal_approve_datetime' => date("Y-m-d H:i:s"),
                                    'lead_final_disbursed_date' => date("Y-m-d"),
                                    'updated_on' => date("Y-m-d H:i:s")
                                ];
//
                                $result = $this->Tasks->updateLeads(['lead_id' => $lead_id], $lead_data, 'leads');

                                $cif_data_update = [
                                    'cif_loan_is_disbursed' => 1,
                                    'cif_updated_by' => $_SESSION['isUserSession']['user_id'],
                                    'cif_updated_on' => date("Y-m-d H:i:s")
                                ];

                                if (!empty($leadDetails["customer_id"])) {
                                    $this->Tasks->updateLeads(['cif_number' => $leadDetails["customer_id"]], $cif_data_update, 'cif_customer');
                                }

                                if ($result == 1) {
                                    $json['msg'] = 'Disbursed Successfully.';
                                    echo json_encode($json);
                                } else {
                                    $json['err'] = 'Disbursed Failed. try again';
                                    echo json_encode($json);
                                }
                            }
                        } else {
                            $json['err'] = !empty($disbursement_return_array['error_msg']) ? $disbursement_return_array['error_msg'] : "Disbursed Failed. try again";
                            echo json_encode($json);
                        }
                    } else {
                        $json['err'] = "CAM or Loan details not found.";
                        echo json_encode($json);
                    }
                }
            }
        }
    }

    public function UpdateDisburseReferenceNo() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = 'Session Expired';
            json_encode($json);
            return false;
        }
//        if ($this->input->post('customer_id') == '') {
//            $json['err'] = 'Customer ID is required.';
//            json_encode($json);
//            return false;
//        }
        if(isset($_FILES["file_name"]["name"])) {
            $lead_id = $this->input->post('lead_id');
            $upload_return = uploadDocument($_FILES, $lead_id);
            if($upload_return['status'] == 0) 
            {
               $json['err'] = 'Please upload the screenshot!';
               echo json_encode($json);
               exit;
              
            } else {
               $image = $upload_return['file_name'];     
            
            
            /*
            $config['upload_path'] = realpath(FCPATH . 'upload');
            $config['allowed_types'] = 'jpg|png|jpeg';
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $json['err'] = $this->upload->display_errors();
                echo json_encode($json);
            } else {
                $data = array('upload_data' => $this->upload->data());
                $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
                $user_id = $this->input->post('user_id',true);
                $loan_reference_no = $this->input->post('loan_reference_no',true);
                $image = $data['upload_data']['file_name'];

                $status = 'DISBURSED';
                $stage = 'S14';
                $lead_status_id = 14;

                $loan_data = [
                    'disburse_refrence_no' => $loan_reference_no,
                    'screenshot' => $image,
                    'status' => $status,
                    'loan_status_id' => $lead_status_id,
                    'updated_by' => $_SESSION['isUserSession']['user_id'],
                    'updated_on' => date("Y-m-d H:i:s")
                ];

                $lead_followup = [
                    'lead_id' => $lead_id,
                    'user_id' => $_SESSION['isUserSession']['user_id'],
                    'status' => $status,
                    'stage' => $stage,
                    'lead_followup_status_id' => $lead_status_id,
                    'remarks' => "DISBURSED",
                    'created_on' => date("Y-m-d H:i:s")
                ];

                $lead_data = [
                    'status' => $status,
                    'stage' => $stage,
                    'lead_status_id' => $lead_status_id,
                    'updated_on' => date("Y-m-d H:i:s")
                ];

                $conditions = ['lead_id' => $lead_id];
                $result2 = $this->Tasks->updateLeads($conditions, $loan_data, 'loan');
                $result3 = $this->Tasks->insert($lead_followup, 'lead_followup');
                $result1 = $this->Tasks->updateLeads($conditions, $lead_data, 'leads');

                if ($result1 == 1 && $result2 == 1 && $result3 == 1) {
                    $this->Tasks->sendDisbursalMail($lead_id); // Send disbursal letter
                    $this->Tasks->sendDisbursalSms($lead_id); // Send Disbursal SMS
                    $json['msg'] = 'Loan Disbursed Successfully.';
                    echo json_encode($json);
                    return true;
                } else {
                    $json['err'] = 'Failed to update Reference no, try again';
                    echo json_encode($json);
                    return false;
                }
            }
        }
    }

    public function getAgreementFile($lead_id) {
        if (!empty($lead_id)) {
            $fetchDisburse = 'D.customer_name, D.status, D.loanAgreementLetter';
            $queryDisburse = $this->Tasks->getAgreementDetails($lead_id, $fetchDisburse);
            $data = $queryDisburse->row();
            return $data;
        }
    }

    public function viewAgreementLetter($lead_id) {
        $lead_id=$this->encrypt->decode($lead_id);
        $data = $this->getAgreementFile($lead_id);
        echo $data->loanAgreementLetter;
    }

    public function addBankDetails() {
        $this->load->view('Disbursal/addBankDetails');
    }

    public function printDisbursalLetter($lead_id) {

        if (agent == 'CR3' && !empty($lead_id)) {
            echo $this->Tasks->sendDisbursalMail($lead_id, true);
        } else {
            return redirect(base_url("logout"));
        }
    }

    public function send_data() {
        $lead_id = $_GET['lead_id'];
        $this->Tasks->sendDisbursalMail($lead_id); // Send disbursal letter
        $this->Tasks->sendDisbursalSms($lead_id); // Send Disbursal SMS
    }

}

?>
