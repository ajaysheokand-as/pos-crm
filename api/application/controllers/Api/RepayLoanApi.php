<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class RepayLoanApi extends REST_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        define('created_on', date('Y-m-d H:i:s'));
        define('created_date', date('Y-m-d'));
    }

    public function index_get($pancard = "") {

        if (!empty($pancard)) {

            $data = $this->db->get_where("leads", ['pancard' => $pancard])->row_array();
        } else {

            $data = $this->db->get("leads")->result();
        }

        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function getDataByPancard_post() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_rules("pancard", "Pancard", "trim|required|min_length[10]|max_length[10]|regex_match[/[a-zA-z]{5}\d{4}[a-zA-Z]{1}/]");
            $this->form_validation->set_rules("is_otp_required", "OTP", "trim"); // is_otp_required => 1
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(validation_errors(), REST_Controller::HTTP_OK));
            } else {

                $pancard = $this->input->post('pancard');
                $is_otp_required = $this->input->post('is_otp_required');

                $conditions = ['leads.pancard' => $pancard, 'leads.lead_active' => 1];

                $this->db->select('loan.*,credit_analysis_memo.roi, credit_analysis_memo.disbursal_date, credit_analysis_memo.repayment_date,leads.first_name, leads.mobile, leads.lead_status_id, leads.email, leads.pancard, leads.status, credit_analysis_memo.loan_recommended');
                $this->db->from("leads");
                $this->db->join('credit_analysis_memo', 'leads.lead_id = credit_analysis_memo.lead_id', 'INNER');
                $this->db->join('loan', 'leads.lead_id = loan.lead_id', 'INNER');
                $this->db->where($conditions);
                $this->db->where_in('leads.lead_status_id', [14, 16, 17, 18, 19]);
                $this->db->order_by('leads.lead_id', 'DESC');
                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                    $effected_rows = $query->result();
                    if (!empty($is_otp_required)) {
                        $lead_details = $query->row_array();
                        $mobile = $lead_details['mobile'];
                        $otp = rand(1000, 9999);

                        $send_data_otp = [
                            "mobile" => $mobile,
                            "otp" => $otp
                        ];

                        //                        $this->Tasks->sendOTPForUserRegistrationVerification($send_data_otp);
                        $result['mobile'] = $mobile;
                        $result['otp'] = $otp;
                    } else {
                        $result = $effected_rows;
                    }

                    return json_encode($this->response(['status' => 1, 'data' => $result], REST_Controller::HTTP_OK));
                } else {

                    return json_encode($this->response(['status' => 0, 'message', 'No Record Found.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['status' => 0, 'message', 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getProductDetails_post() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_rules("lead_id", "Lead Id", "trim|required");
            if ($this->form_validation->run() == FALSE) {
                echo json_encode($this->response(validation_errors(), REST_Controller::HTTP_OK));
            } else {

                $lead_id = $this->encrypt->decode($this->input->post('lead_id'));
                $user_id = $this->encrypt->decode($this->input->post('user_id'));

                $conditions = ['leads.lead_id' => $lead_id, 'leads.lead_active' => 1];

                $this->db->select('loan.*,credit_analysis_memo.roi, credit_analysis_memo.disbursal_date, credit_analysis_memo.repayment_date,lead_customer.first_name,lead_customer.sur_name, leads.mobile, leads.lead_status_id, leads.email, leads.pancard, leads.status, credit_analysis_memo.loan_recommended,lead_customer.aa_current_house,lead_customer.aa_cr_residence_pincode,lead_customer.aa_current_state,lead_customer.aa_current_city');
                $this->db->from("leads");
                $this->db->join('credit_analysis_memo', 'leads.lead_id = credit_analysis_memo.lead_id', 'INNER');
                $this->db->join('loan', 'leads.lead_id = loan.lead_id', 'INNER');
                $this->db->join('lead_customer', 'lead_customer.customer_lead_id = leads.lead_id', 'INNER');
                $this->db->join('master_state', 'leads.state_id = master_state.m_state_id', 'LEFT');
                $this->db->where($conditions);
                $this->db->where_in('leads.lead_status_id', [14, 16, 17, 18, 19]);
                $this->db->order_by('leads.lead_id', 'DESC');

                //                    $select = "SELECT loan.*,credit_analysis_memo.roi, credit_analysis_memo.disbursal_date, credit_analysis_memo.repayment_date,lead_customer.first_name,lead_customer.sur_name, leads.mobile, leads.lead_status_id, leads.email, leads.pancard, leads.status, credit_analysis_memo.loan_recommended ";
                //                    $select .= "FROM leads INNER JOIN credit_analysis_memo ON(leads.lead_id = credit_analysis_memo.lead_id) INNER JOIN lead_customer ON(lead_customer.customer_lead_id = leads.lead_id) INNER JOIN loan ON(leads.lead_id = loan.lead_id) ";
                //                    $select .= "WHERE leads.lead_id = $lead_id AND leads.lead_active = 1 AND leads.lead_status_id IN(14, 16, 17, 18, 19) ";
                //                    $select .= "ORDER BY leads.lead_id DESC";

                $query = $this->db->get();
                $effected_rows = $query->row_array();

                if (!empty($effected_rows)) {

                    $sql = $this->db->query("SELECT IFNULL(SUM(`received_amount`), 0) as payment_amount FROM collection WHERE collection_active = 1 AND collection_deleted = 0 AND payment_verification = 1 AND lead_id = " . $lead_id);
                    $payment_amount = $sql->row_array();

                    //                    $result = json_encode($effected_rows);
                    $effected_rows['payment_amount'] = $payment_amount['payment_amount'];
                    $effected_rows['user_id'] = $user_id;
                    return json_encode($this->response(['status' => 1, 'data' => $effected_rows], REST_Controller::HTTP_OK));
                } else {

                    return json_encode($this->response(['status' => 0, 'message' => 'No Record found.'], REST_Controller::HTTP_OK));
                }
            }
        } else {

            return json_encode($this->response(['status' => 0, 'message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getProductDetails1_post() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->form_validation->set_rules("lead_id", "Lead Id", "trim|required");

            if ($this->form_validation->run() == FALSE) {

                $this->response(validation_errors(), REST_Controller::HTTP_OK);
            } else {

                $lead_id = $this->input->post('lead_id');

                $getProductDetails = $this->db->query("SELECT L.branch, L.lead_id, L.loan_no, L.lan, L.customer_name, L.email, C.mobile, C.pancard,

        	                    L.loan_amount, L.loan_intrest, L.loan_disburse_date, L.loan_repay_date, LL.status, C.tenure, L.loan_repay_amount

        	                    FROM loan L

        	                    INNER JOIN credit_analysis_memo C ON L.lead_id=C.lead_id

        	                    INNER JOIN leads LL ON L.lead_id=LL.lead_id

        	                        AND LL.status IN('Disbursed', 'Part Payment')

        	                        AND C.status LIKE'%Sanction%'

        	                        AND L.status LIKE'%Disbursed%'

        	                        AND L.lead_id=" . $lead_id);

                //               echo $this->db->last_query();

                $data['itemInfo'] = $getProductDetails->result();

                $sql = $this->db->query("SELECT SUM(`payment_amount`) as payment_amount FROM recovery WHERE PaymentVerify = 1 AND lead_id = " . $lead_id);

                $query = $this->db->query("SELECT IFNULL(SUM(`payment_amount`), 0) as bouncing_charge FROM recovery WHERE status  IN ('Bouncing Charges') AND lead_id = " . $lead_id);

                $data['payment_amount'] = $sql->result();

                $data['bouncing_charge'] = $query->result();

                $data['currency_code'] = 'INR';
                $data['hrjjjj'] = '========';

                //$result = json_encode($data);

                $result = $data;
                $status = $data['itemInfo'][0]->status;
                //echo "<pre>";print_r($data['itemInfo']); echo "hi"; die;

                $pancard = $data['itemInfo'][0]->pancard;
                $loan_amount = $data['itemInfo'][0]->loan_amount;
                $loan_no = $data['itemInfo'][0]->loan_no;
                $f_tenure = $data['itemInfo'][0]->tenure;
                $customer_name = $data['itemInfo'][0]->customer_name;
                $emailId = $data['itemInfo'][0]->email;
                $mobile = $data['itemInfo'][0]->mobile;
                $loan_intrest = $data['itemInfo'][0]->loan_intrest;
                $loan_repay_date = $data['itemInfo'][0]->loan_repay_date;
                $loan_disburse_date = $data['itemInfo'][0]->loan_disburse_date;
                $status = $data['itemInfo'][0]->status;
                $currency_code = $data['itemInfo'][0]->currency_code;
                $loan_repay_amount = $data['itemInfo'][0]->loan_repay_amount;
                $Bouncing_charge = 0;
                if ($status == 'Full Payment' || $status == 'Settelment') {
                    $status = 'Closed';
                } else {
                    $status = 'Active';
                }

                $loanAmt = $loan_amount;
                $roi = $loan_intrest;
                date_default_timezone_set('Asia/Kolkata');

                $disburseddate = date('Y-m-d', strtotime($loan_disburse_date));
                $repaymentdate = date('Y-m-d', strtotime($loan_repay_date));
                $now = date('Y-m-d');
                $date1 = strtotime($now);
                $date2 = strtotime($disburseddate);
                $date3 = strtotime($repaymentdate);
                $diff = $date3 - $date2;
                $tenure = ($diff / 60 / 60 / 24);

                $realint = $date1 - $date2;
                $realDays = ($realint / 60 / 60 / 24);

                if ($date1 <= $date3) {
                    $realdays = $date1 - $date2;
                    $rtenure = ($realdays / 60 / 60 / 24);
                    $ptenure = 0;
                } else {
                    $endDate = $date1 - $date3;
                    $oneDay = (60 * 60 * 24);
                    $dateDays60 = ($oneDay * 60);

                    $realdays = $date3 - $date2;
                    $rtenure = ($realdays / 60 / 60 / 24);
                    if ($endDate <= $dateDays60) {
                        $paneldays = $date1 - $date3;
                        $ptenure = ($paneldays / 60 / 60 / 24);
                    } else {
                        $ptenure = 60;
                    }
                }

                $realIntrest = ($loan_amount * $loan_intrest * $rtenure) / 100;
                $penaltyIntrest = ($loan_amount * $loan_intrest * 2 * $ptenure) / 100;
                $paidAmount = $payment_amount;
                $repayAmount = $loan_amount + $realIntrest + $penaltyIntrest - $paidAmount;

                $lead_id = $lead_id;
                $productinfo = $loan_no;
                $txnid = time();
                $surl = $surl;
                $furl = $furl;
                $key_id = 'rzp_test_zNnHRltGuhdp2m';
                $currency_code = $currency_code;
                $total = ($repayAmount);
                $amount = $repayAmount;
                $merchant_order_id = $loan_no;
                $card_holder_name = $customer_name;
                $email = $emailId;
                $phone = $mobile;
                $name = 'Aman Fincap Limited';
                $return_url = $rurl;
                //echo  "=====".$data['itemInfo']->branch;
                //  echo "<pre>";print_r($data['itemInfo']);
                $dataa = array(
                    'branch' => $data['itemInfo'][0]->branch,
                    'loan_no' => $data['itemInfo'][0]->loan_no,
                    'customer_name' => $data['itemInfo'][0]->customer_name,
                    'email' => $data['itemInfo'][0]->email,
                    'mobile' => $data['itemInfo'][0]->mobile,
                    'pancard' => $data['itemInfo'][0]->pancard,
                    'loan_amount' => $data['itemInfo'][0]->loan_amount,
                    'Disbursed_date' => date("j-F-Y ", strtotime($data['itemInfo'][0]->loan_disburse_date)),
                    'ROI' => $data['itemInfo'][0]->loan_intrest,
                    'Repayment_date' => date("j-F-Y ", strtotime($data['itemInfo'][0]->loan_repay_date)),
                    'Real_Tenure' => $rtenure,
                    'Real_Interest' => $realIntrest,
                    'Penalty_Tenure' => $ptenure,
                    'Penal_Interest' => $penaltyIntrest,
                    'Paid_Amount' => $data['payment_amount'][0]->payment_amount,
                    'Totalamountdueasontoday' => $repayAmount,
                );

                $da = array('success' => "true", 'data' => $dataa);
                echo $result = json_encode($da);
                //$this->response($result, REST_Controller::HTTP_OK);
            }
        } else {

            $this->response(['Request Method Post Failed.'], REST_Controller::HTTP_OK);
        }
    }

    public function recoveryInsert_post() {
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

            $this->form_validation->set_data($this->post());

            //            $this->form_validation->set_rules("company_id", "Company ID", "trim|required");

            $this->form_validation->set_rules("lead_id", "Lead ID", "trim|required");

            $this->form_validation->set_rules("loan_no", "Loan No", "trim|required");

            $this->form_validation->set_rules("easypay_trans_no", "Easypay Trans No", "trim|required");

            $this->form_validation->set_rules("lw_refrence_no", "LW Refrence No", "trim|required");

            $this->form_validation->set_rules("payment_mode", "Payment Mode", "trim|required");

            $this->form_validation->set_rules("status", "Status", "trim|required");

            $this->form_validation->set_rules("recovery_status", "Recovery Status", "trim|required");

            $this->form_validation->set_rules("company_account_no", "Company Account No", "trim|required");

            $this->form_validation->set_rules("remarks", "Remark", "trim|required");

            $this->form_validation->set_rules("ip", "User IP", "trim|required");

            $this->form_validation->set_rules("recovery_by", "Recovered By", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $query = $this->db->select('id, refrence_no', 'user_id')->where(['refrence_no' => $post['easypay_trans_no'], 'loan_no' => $post['loan_no'], 'user_id' => $post['collection_executive_user_id']])->from('collection')->get();
                if ($query->num_rows() > 0) {
                    return json_encode($this->response(['Status' => 0, 'Message' => "Payment Reference already exists"], REST_Controller::HTTP_OK));
                } else {
                    //                    error_reporting(E_ALL);
                    //                    ini_set('display_errors', 1);
                    $recoveryData = array(
                        'company_id' => $post['company_id'],
                        'lead_id' => $post['lead_id'],
                        'customer_id' => $post['customer_id'],
                        'loan_no' => $post['loan_no'],
                        'payment_mode' => 'Easy Pay',
                        'payment_mode_id' => 2,
                        'received_amount' => $post['payment_amount'],
                        'refrence_no' => $post['easypay_trans_no'],
                        'company_account_no' => $post['company_account_no'],
                        'date_of_recived' => date("Y-m-d"),
                        'repayment_type' => 19,
                        'remarks' => $post['remarks'],
                        'ip' => $post['ip'],
                        'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                        'payment_verification' => 0,
                    );
                    //                    echo "here3";
                    $conditions = array();
                    $conditions['repayment_lead_id'] = $post['lead_id'];
                    $conditions['repayment_trans_no'] = $post['lw_refrence_no'];
                    $conditions['repayment_active'] = 1;
                    $conditions['repayment_deleted'] = 0;
                    //                    $conditions['repayment_source_id'] = 2;

                    $select = 'repayment_log_id, repayment_lead_id, repayment_api_status_id, repayment_source_id, repayment_user_id, ';
                    $select .= 'repayment_trans_no';

                    $this->db->select($select);
                    $this->db->from('api_repayment_logs');
                    $this->db->where($conditions);
                    $query = $this->db->order_by('repayment_log_id', 'DESC')->get();
                    //                    $query = $this->db->get();//->order_by('repayment_log_id', 'DESC')
                    //                    echo $this->db->last_query();

                    if ($query->num_rows() > 0) {

                        $row_data = $query->row_array();

                        $repayment_log_id = $row_data['repayment_log_id'];
                        $repayment_user_id = !empty($row_data['repayment_user_id']) ? $row_data['repayment_user_id'] : 0;
                        $update_repay_log_data = array();
                        $update_repay_log_data['repayment_response_datetime'] = date('Y-m-d H:i:s');

                        $this->db->where(['repayment_log_id' => $repayment_log_id])->update('api_repayment_logs', $update_repay_log_data);

                        $recoveryData['collection_executive_user_id'] = $repayment_user_id;
                    }
                    //                    echo json_encode($recoveryData);

                    $this->db->insert('collection', $recoveryData);
                    //                    echo $this->db->last_query();
                    $id = $this->db->insert_id();
                    if (!empty($id)) {
                        return json_encode($this->response(['Status' => 1, 'Message' => 'Payment successfull.'], REST_Controller::HTTP_OK));
                    } else {
                        return json_encode($this->response(['Status' => 0, 'Message' => 'Payment failed due to insert.'], REST_Controller::HTTP_OK));
                    }
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function getState_post() {

        $data = $this->db->select('*')->from('tb_states')->get()->result();

        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function getCity_post() {

        $state_id = $this->input->post('state_id');

        $data = $this->db->select('*')->from('tb_city')->where('state_id', $state_id)->get()->result();

        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function getLoanDetails_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("loan_no", "Loan NO", "required|trim|regex_match[/^[a-zA-Z0-9]+$/]");
            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $query = $this->db->select('L.*')
                    ->where('L.loan_no', $post['loan_no'])
                    ->from('loan L')
                    ->get();

                if ($query->num_rows() > 0) {
                    $row = $query->row();

                    json_encode($this->response(['Status' => 1, 'Message' => 'Success.', 'Data' => $row], REST_Controller::HTTP_OK));
                } else {
                    json_encode($this->response(['Status' => 0, 'Message' => 'Failed.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayPaymentRequest_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        //return  json_encode($this->response(['Status' => 0, 'Message' => $post], REST_Controller::HTTP_OK));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("amount", "payble amount", "required|numeric");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => $post], REST_Controller::HTTP_OK));
            } else {
                //return json_encode($this->response(['Status' => 0, 'Message' => $post], REST_Controller::HTTP_OK));

                $insertDataRepaymentLogs = array(
                    'repayment_product_id' => $post['product_info'],
                    'repayment_provider_id' => $post['provider_id'],
                    'repayment_method_id' => $post['method_id'],
                    'repayment_lead_id' => $post['lead_id'],
                    'repayment_trans_no' => $post['trans_no'],
                    'repayment_tid' => $post['tid'],
                    'repayment_order_id' => $post['order_id'],
                    'repayment_amount' => $post['amount'],
                    'repayment_request' => $input_data,
                    'repayment_request_datetime' => date("Y-m-d H:i:s"),
                    'repayment_source_id' => 1
                );


                $insertRepaymentLogs = $this->db->insert('api_repayment_logs', $insertDataRepaymentLogs);
                $repayment_log_id = $this->db->insert_id();

                if (isset($repayment_log_id)) {
                    $update_repayment_log = $this->db->set('repayment_api_status_id', 5)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');
                    json_encode($this->response(['Status' => 1, 'Message' => 'Repay Api Request Logs Added Successfully', 'repayment_log_id' => $repayment_log_id], REST_Controller::HTTP_OK));
                } else {
                    $update_repayment_log = $this->db->set('repayment_api_status_id', 0)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');
                    json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Logs'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayLogsRequest_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("repayment_product_id", "Repayment Product Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("repayment_provider_id", "Repayment Provider Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("repayment_method_id", "Repayment Method Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("repayment_lead_id", "Repayment Lead Id", "required|trim");
            $this->form_validation->set_rules("repayment_trans_no", "Repayment Trans No", "required|trim");
            $this->form_validation->set_rules("repayment_request", "Repayment Request", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $insertDataRepaymentLogs = array(
                    'repayment_product_id' => $post['repayment_product_id'],
                    'repayment_provider_id' => $post['repayment_provider_id'],
                    'repayment_method_id' => $post['repayment_method_id'],
                    'repayment_lead_id' => $post['repayment_lead_id'],
                    'repayment_trans_no' => $post['repayment_trans_no'],
                    'repayment_request' => $post['repayment_request'],
                    'repayment_request_datetime' => created_on,
                );

                $insertRepaymentLogs = $this->db->insert('api_repayment_logs', $insertDataRepaymentLogs);
                $repayment_log_id = $this->db->insert_id();

                if (isset($repayment_log_id)) {

                    $update_repayment_log = $this->db->set('repayment_api_status_id', 1)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');

                    json_encode($this->response(['Status' => 1, 'Message' => 'Repay Api Request Logs Added Successfully', 'repayment_log_id' => $repayment_log_id], REST_Controller::HTTP_OK));
                } else {
                    $update_repayment_log = $this->db->set('repayment_api_status_id', 0)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');
                    json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Logs'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayLogsCCAvenuePaymentResponce_post() {

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

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("repayment_order_id", "Order Id", "required|numeric|is_natural|trim");
            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $repayment_order_id = $post['repayment_order_id'];
                $insertDataResposeLogs = array(
                    'repayment_api_status_id' => $post['status'],
                    'repayment_transaction_date' => strtotime('Y-m-d H:i:s', $post['repayment_transaction_date']),
                    'repayment_tracking_id' => $post['repayment_tracking_id'],
                    'repayment_response' => json_encode($post['repayment_response']),
                    'repayment_bank_reference_no' => $post['repayment_bank_reference_no'],
                    'repayment_response_datetime' => date("Y-m-d H:i:s")
                );
                $this->db->where('repayment_order_id', $repayment_order_id)->update('api_repayment_logs', $insertDataResposeLogs);
                return json_encode($this->response(['Status' => 1, 'Message' => 'Repay Api Response Logs updated Successfully', 'repayment_order_id' => $post['repayment_order_id']], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayLogsResponce_post() {

        $input_data = file_get_contents("php://input");
        //        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("repayment_product_id", "Repayment Product Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("repayment_lead_id", "Repayment Lead Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("repayment_method_id", "Repayment Method Id", "required|numeric|is_natural|trim");
            $this->form_validation->set_rules("lw_refrence_no", "LW Refrence No", "required|trim");

            $this->form_validation->set_rules("repayment_response", "Repayment Response", "trim");
            $this->form_validation->set_rules("repayment_errors", "Repayment Errors", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $insertDataResposeLogs = array(
                    //                    'repayment_product_id' => $post['repayment_product_id'],
                    //                    'repayment_provider_id' => $post['repayment_provider_id'],
                    //                    'repayment_method_id' => $post['repayment_method_id'],
                    //                    'repayment_lead_id' => $post['repayment_lead_id'],
                    //                    'repayment_trans_no' => $post['repayment_trans_no'],
                    'repayment_response' => $post['repayment_response'],
                    'repayment_errors' => $post['repayment_errors'],
                    'repayment_response_datetime' => date("Y-m-d H:i:s")
                );

                $conditions = array();
                $conditions['repayment_lead_id'] = $post['repayment_lead_id'];
                $conditions['repayment_trans_no'] = $post['lw_refrence_no'];
                $conditions['repayment_active'] = 1;
                $conditions['repayment_deleted'] = 0;
                //                $conditions['repayment_source_id'] = 2;

                $select = 'repayment_log_id, repayment_lead_id, repayment_api_status_id, repayment_source_id, repayment_user_id, ';
                $select .= 'repayment_trans_no';

                $this->db->select($select);
                $this->db->from('api_repayment_logs');
                $this->db->where($conditions);
                $query = $this->db->order_by('repayment_log_id', 'DESC')->get();
                //                    $query = $this->db->get();//->order_by('repayment_log_id', 'DESC')
                //                echo $this->db->last_query();

                if ($query->num_rows() > 0) {
                    $row_data = $query->row_array();
                    //                    echo json_encode($row_data);
                    $repayment_log_id = $row_data['repayment_log_id'];
                    $this->db->where(['repayment_log_id' => $repayment_log_id])->update('api_repayment_logs', $insertDataResposeLogs);
                } else {
                    //                    echo " here2";
                }


                $json_response_arr = json_decode($post['repayment_response'], true);

                $api_status_id = $json_response_arr['apiStatusId'];

                //                $insertRepaymentLogs = $this->db->insert('api_repayment_logs', $insertDataResposeLogs);
                //                $repayment_log_id = $this->db->insert_id();

                if (isset($repayment_log_id)) {

                    $update_repayment_log = $this->db->set('repayment_api_status_id', $api_status_id)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Repay Api Response Logs Added Successfully', 'repayment_log_id' => $repayment_log_id], REST_Controller::HTTP_OK));
                } else {
                    $update_repayment_log = $this->db->set('repayment_api_status_id', $api_status_id)->where('repayment_log_id', $repayment_log_id)->update('api_repayment_logs');
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Unable to Add Logs'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayCallBack_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());

            $this->form_validation->set_rules("easypay_trans_no", "easypay trans no", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {
                $result = $this->db->select("*")->where("refrence_no", $post['easypay_trans_no'])->from("collection")->get();
                if ($result->num_rows() > 0) {

                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $result->row_array()], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'Payment not done.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function RepayCallBackMailData_post() {

        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (
            ($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth']))
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules("lead_id", "lead Id", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
            } else {

                $lead_id = $post['lead_id'];
                $query = $this->db->select('email, mobile, gender')->where('customer_lead_id', $lead_id)->from('lead_customer')->get();
                $result = $query->row_array();

                if ($result) {
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $result], REST_Controller::HTTP_OK));
                } else {
                    return json_encode($this->response(['Status' => 0, 'Message' => 'No record Found.'], REST_Controller::HTTP_OK));
                }
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function payUpaymentconfirm_post() {

        // Log the $_POST data to a file for debugging
        file_put_contents(APPPATH . 'logs/payu_response.log', print_r($_POST, true), FILE_APPEND);

        // Collect POST data from PayU response
        $postData = $this->input->post();
        $status = $postData['status'];
        $firstname = $postData['firstname'];
        $amount = $postData['amount'];
        $txnid = $postData['txnid'];
        $posted_hash = $postData['hash'];
        $key = $postData['key'];
        $productinfo = $postData['productinfo'];
        $email = $postData['email'];
        $udf5 = $postData['udf5'];    // User-defined data - lead_id
        $SALT = "PfZjLuzIV1B8ePKOwClbqrFWEBcC1LZD";   // Use the same SALT key from PayU

        // Log insert
        $repayment_request = $postData['payment_source'];
        $repayment_response = $postData['status'];

        // Hash Generation with - $udf5
        $additionalCharges = $postData['additionalCharges'] ?? '';
        $retHashSeq = $additionalCharges ?
            $additionalCharges . '|' . $SALT . '|' . $status . '||||||' . $udf5 . '|||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key :
            $SALT . '|' . $status . '||||||' . $udf5 . '|||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;

        // Generate hash to verify with posted hash
        $hash = hash("sha512", $retHashSeq);

        // Verify transaction status
        if ($status == 'success' && $posted_hash == $hash) {
            echo "<h2>Transaction Successful</h2>";
            echo "<p>Thank you, $firstname. Your transaction ID is $txnid.</p>";
            echo "<p>We have received a payment of Rs. $amount.</p>";
            echo "<p>Your email: $email</p>";

            // Call verify_payment to further validate
            if ($this->verify_payment($key, $txnid, $status)) {
                echo "Payment Verified.....from PayU";

                // Check for an existing transaction with the same transaction ID in 'collection' table
                $existingTransaction = $this->db->get_where('collection', ['refrence_no' => $txnid])->row_array();

                if (!$existingTransaction) {
                    // Get lead details from the database
                    $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no FROM leads WHERE lead_active = 1 AND lead_id = ?";
                    $query_data = $this->db->query($query, [$udf5])->row_array();

                    if ($query_data) {
                        // Prepare data to insert into collection table
                        $dataToInsert = [
                            'company_id' => $query_data['company_id'],
                            'lead_id' => $udf5,
                            'customer_id' => $query_data['customer_id'],
                            'loan_no' => $query_data['loan_no'],
                            'payment_mode' => 'PayU',
                            'payment_mode_id' => 14,
                            'received_amount' => $amount,
                            'refrence_no' => $txnid,
                            'date_of_recived' => date("Y-m-d"),
                            'repayment_type' => 19,
                            'remarks' => 'Payment Received through PayU',
                            'collection_executive_payment_created_on' => date("Y-m-d H:i:s"),
                            'payment_verification' => 0
                        ];

                        // Insert the data into the collection table
                        $this->db->insert('collection', $dataToInsert);
                        echo "Payment data inserted successfully.";
                    }
                }

                // Check for an existing transaction with the same transaction ID in 'collection' table
                $logTransaction = $this->db->get_where('api_repayment_logs', ['repayment_trans_no' => $postData['txnid']])->row_array();

                if (empty($logTransaction)) {
                    // Prepare data to insert log table - api_repayment_logs
                    $insertDataRepaymentLogsPayu = [
                        'repayment_product_id' => 1,       // 1 - Loan
                        'repayment_provider_id' => 4,       // PayU - 4 Type
                        'repayment_method_id' => 1,
                        'repayment_lead_id' => $udf5,
                        'repayment_trans_no' => $postData['txnid'],
                        'repayment_tid' => $$postData['bank_ref_num'],
                        'repayment_amount' => $amount,
                        'repayment_api_status_id' => $repayment_response == 'success' ? 1 : 5,
                        'repayment_request' => $repayment_request,
                        'repayment_response' => json_encode($postData),
                        'repayment_request_datetime' => date("Y-m-d H:i:s"),
                        'repayment_response_datetime' => date("Y-m-d H:i:s"),
                        'repayment_transaction_date' => $repayment_response == 'success' ? date("Y-m-d H:i:s") : null
                    ];

                    $this->db->insert('api_repayment_logs', $insertDataRepaymentLogsPayu);
                }
            } else {
                echo "Payment Verification Failed.....";
            }
        } else {
            echo "<h2>Transaction Failed</h2>";
            echo "<p>Unfortunately, the transaction could not be verified Or Hash verification failed.</p>";
        }
    }

    public function payUfail_post() {
        echo "<h2>Transaction Failed</h2>";
        echo "<p>The transaction was unsuccessful. Please try again later.</p>";

        echo "<pre>", print_r($_POST, true), "</pre>";

        $postData = $this->input->post();
        $status = $postData['status'];
        $amount = $postData['amount'];
        $lead_id = $postData['udf5'];
        $payment_source = $postData['payment_source'];

        if ($status === 'failure') {

            // Check for an existing transaction with the same transaction ID in 'collection' table
            $existingTransaction = $this->db->get_where('api_repayment_logs', ['repayment_trans_no' => $postData['txnid']])->row_array();

            if (empty($existingTransaction)) {
                $insertDataRepaymentLogsPayu = [
                    'repayment_product_id' => 1,       // 1 - Loan
                    'repayment_provider_id' => 4,       // PayU - 4 Type
                    'repayment_method_id' => 1,
                    'repayment_lead_id' => $lead_id,
                    'repayment_trans_no' => $postData['txnid'],
                    'repayment_tid' => $$postData['bank_ref_num'],
                    'repayment_api_status_id' => 5,
                    'repayment_amount' => $amount,
                    'repayment_request' => $payment_source,
                    'repayment_response' => json_encode($postData),
                    'repayment_request_datetime' => date("Y-m-d H:i:s"),
                    'repayment_response_datetime' => date("Y-m-d H:i:s")
                ];
            }

            if ($this->db->insert('api_repayment_logs', $insertDataRepaymentLogsPayu)) {
                echo "<p>Log entry created successfully.</p>";
            } else {
                echo "<p>Failed to insert log entry. Error: ", $this->db->error()['message'], "</p>";
            }
        } else {
            echo "<p>No data to process for insertion.</p>";
        }
    }


    public function payUcancel_post() {
        echo "<h2>Transaction Cancelled</h2>";
        echo "<p>You have cancelled the transaction.</p>";
        echo "<pre>", print_r($_POST, true), "</pre>";
    }


    function verify_payment($key, $txnid, $status) {
        $command = "verify_payment";
        $salt = "PfZjLuzIV1B8ePKOwClbqrFWEBcC1LZD";
        $hash_str = implode('|', [$key, $command, $txnid, $salt]);
        $hash = hash('sha512', $hash_str);

        $params = [
            'key' => $key,
            'hash' => strtolower($hash),
            'var1' => $txnid,
            'command' => $command
        ];

        $url = "https://info.payu.in/merchant/postservice?form=2";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            file_put_contents(APPPATH . 'logs/curl_error.log', "cURL Error: " . curl_error($ch), FILE_APPEND);
        }
        curl_close($ch);

        file_put_contents(APPPATH . 'logs/verify_payment_response.log', $response, FILE_APPEND);

        $responseArray = json_decode($response, true);

        return isset($responseArray['transaction_details'][$txnid]['status']) &&
            $responseArray['transaction_details'][$txnid]['status'] === $status;
    }

    public function payuOrders_post() {
        // Read and sanitize input data
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true) ?: $_POST);

        // Read headers and validate token
        $headers = $this->input->request_headers();
        $token = $this->_token();

        if (!isset($headers['Accept'], $headers['Auth'])) {
            return $this->response([
                'Status' => 0,
                'Message' => 'Invalid request headers.'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $header_validation = ($headers['Accept'] === "application/json") && ($token['repayment'] === base64_decode($headers['Auth']));

        if (!$header_validation) {
            return $this->response([
                'Status' => 0,
                'Message' => 'Unauthorized request.'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Validation: Check for required fields
        $required_fields = ['amount', 'productinfo', 'firstname', 'email', 'udf5'];
        foreach ($required_fields as $field) {
            if (empty($post[$field])) {
                return $this->response([
                    'Status' => 0,
                    'Message' => "Missing required field: $field."
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        try {
            // PayU configuration
            $MERCHANT_KEY = "LrvBUp";
            $SALT = "PfZjLuzIV1B8ePKOwClbqrFWEBcC1LZD";
            $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

            // Construct hash string
            $hash_string = implode('|', [
                $MERCHANT_KEY,
                $txnid,
                $post['amount'],
                $post['productinfo'],
                $post['firstname'],
                $post['email'],
                '',
                '',
                '',
                '',
                $post['udf5'],
                '',
                '',
                '',
                '',
                '',
                $SALT
            ]);

            // Generate hash
            $hash = strtolower(hash('sha512', $hash_string));

            // Define response URLs
            $base_url = base_url("/Api/RepayLoanApi/");
            $response_data = [
                "parameters" => [
                    "hash" => $hash,
                    "txnid" => $txnid,
                    "surl" => $base_url . "payUpaymentconfirm",
                    "furl" => $base_url . "payUfail",
                    "curl" => $base_url . "payUcancel"
                ]
            ];

            // Return success response
            return $this->response([
                'Status' => 1,
                'Message' => 'Success',
                'data' => $response_data
            ], REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            // Error handling
            return $this->response([
                'Status' => 0,
                'Message' => 'An error occurred: ' . $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function iciciQRRequest_post() {

        // Read and sanitize input data
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true) ?: $_POST);

        // Read headers and validate token
        $headers = $this->input->request_headers();
        $token = $this->_token();

        // if (!isset($headers['Accept'], $headers['Auth'])) {
        //     return $this->response([
        //         'Status' => 0,
        //         'Message' => 'Invalid request headers.'
        //     ], REST_Controller::HTTP_BAD_REQUEST);
        // }

        // $header_validation = ($headers['Accept'] === "application/json") && ($token['repayment'] === base64_decode($headers['Auth']));

        // if (!$header_validation) {
        //     return $this->response([
        //         'Status' => 0,
        //         'Message' => 'Unauthorized request.'
        //     ], REST_Controller::HTTP_UNAUTHORIZED);
        // }

        // Validation: Check for required fields
        $required_fields = ['lead_id', 'amount'];
        foreach ($required_fields as $field) {
            if (empty($post[$field])) {
                return $this->response([
                    'Status' => 0,
                    'Message' => "Missing required field: $field."
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        try {

            $lead_id = $post['lead_id'];
            $amount = $post['amount'];

            // Get lead details from the database
            $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no FROM leads WHERE lead_active = 1 AND lead_id = ?";
            $query_data = $this->db->query($query, [$lead_id])->row_array();

            if (empty($query_data)) {
                throw new Exception("Lead not found.");
            }

            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $response = $CommonComponent->call_qrcode_api($lead_id, array("amount" => $amount, "user_id" => $post['user_id']));

            if ($response['status'] != 1) {
                throw new Exception($response['errors']);
            }

            $transaction_id = $response['transaction_id'];
            $amount = $response['amount'];

            $insertDataRepaymentLogsPayu = array(
                'repayment_product_id' => 1,
                'repayment_provider_id' => 5,
                'repayment_method_id' => 1,
                'repayment_lead_id' => $lead_id,
                'repayment_trans_no' => $transaction_id,
                'repayment_api_status_id' => 5,
                'repayment_amount' => $amount,
                'repayment_request' => json_encode($response),
                'repayment_response' => null,
                'repayment_request_datetime' => date("Y-m-d H:i:s"),
                'repayment_response_datetime' => null
            );

            $this->db->insert('api_repayment_logs', $insertDataRepaymentLogsPayu);

            $apiStatusId = 1;
        } catch (Exception $e) {
            $apiStatusId = 2;
            $errorMessage = $e->getMessage();
        }

        // Return success response
        return $this->response([
            'Status' => $apiStatusId,
            'Message' => $errorMessage ?? 'Success',
            'data' => $response,
            'url' => $response['data']['qrCodeUrl'],
        ], REST_Controller::HTTP_OK);
    }

    public function iciciUPIRequest_post() {

        // Read and sanitize input data
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true) ?: $_POST);

        // Read headers and validate token
        $headers = $this->input->request_headers();
        $token = $this->_token();

        // if (!isset($headers['Accept'], $headers['Auth'])) {
        //     return $this->response([
        //         'Status' => 0,
        //         'Message' => 'Invalid request headers.'
        //     ], REST_Controller::HTTP_BAD_REQUEST);
        // }

        // $header_validation = ($headers['Accept'] === "application/json") && ($token['repayment'] === base64_decode($headers['Auth']));

        // if (!$header_validation) {
        //     return $this->response([
        //         'Status' => 0,
        //         'Message' => 'Unauthorized request.'
        //     ], REST_Controller::HTTP_UNAUTHORIZED);
        // }

        // Validation: Check for required fields
        $required_fields = ['lead_id', 'amount', 'customer_vpa'];
        foreach ($required_fields as $field) {
            if (empty($post[$field])) {
                return $this->response([
                    'Status' => 0,
                    'Message' => "Missing required field: $field."
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        try {

            $lead_id = $post['lead_id'];
            $amount = $post['amount'];
            $customer_vpa = $post['customer_vpa'];

            // Get lead details from the database
            $query = "SELECT lead_id, customer_id, company_id, product_id, loan_no FROM leads WHERE lead_active = 1 AND lead_id = ?";
            $query_data = $this->db->query($query, [$lead_id])->row_array();

            if (empty($query_data)) {
                throw new Exception("Lead not found.");
            }

            require_once(COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $response = $CommonComponent->call_collectpay_api($lead_id, array("customer_vpa" => $customer_vpa, "amount" => $amount, "user_id" => $post['user_id']));

            if ($response['status'] != 1) {
                throw new Exception($response['errors']);
            }

            $transaction_id = $response['transaction_id'];
            $amount = $response['amount'];

            $insertDataRepaymentLogsPayu = array(
                'repayment_product_id' => 1,
                'repayment_provider_id' => 5,
                'repayment_method_id' => 1,
                'repayment_lead_id' => $lead_id,
                'repayment_trans_no' => $transaction_id,
                'repayment_api_status_id' => 5,
                'repayment_amount' => $amount,
                'repayment_request' => json_encode($response),
                'repayment_response' => null,
                'repayment_request_datetime' => date("Y-m-d H:i:s"),
                'repayment_response_datetime' => null
            );

            $this->db->insert('api_repayment_logs', $insertDataRepaymentLogsPayu);

            $apiStatusId = 1;
        } catch (Exception $e) {
            $apiStatusId = 2;
            $errorMessage = $e->getMessage();
        }

        // Return success response
        return $this->response([
            'Status' => $apiStatusId,
            'Message' => $errorMessage ?? 'Success'
        ], REST_Controller::HTTP_OK);
    }
}
