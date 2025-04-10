<?php

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class DashboardController extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '1000M');
    }


    public function DashboardData_post()
    {
        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {

            $toDate = date('Y-m-d');
            $fromDate = date('Y-m-d', strtotime('-1 year', strtotime($toDate)));

            $qry = "SELECT LD.lead_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_total_outstanding_amount, LD.user_type, concat_ws(' ',LC.first_name,LC.middle_name,LC.sur_name) as customer_name, LD.mobile, LD.email, CAM.disbursal_date, CAM.repayment_date, CAM.roi, CAM.tenure, MB.m_branch_name, U.name AS sanction_by , LD.status ";

            $qry .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) INNER JOIN master_branch MB ON(LD.lead_branch_id=MB.m_branch_id) LEFT JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";

            $qry .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_id=LC.customer_lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >= '$fromDate' ORDER BY repayment_date ASC";

            $result = $this->db->query($qry)->result_array();

            $data = array();

            if (!empty($result)) {
                $data['status'] = 1;
                $data['data'] = $result;
            } else {
                $data['status'] = 0;
            }

            return json_encode($this->response($data, REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function VisitData_post()
    {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("visit_end", "Date", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $fromDate = $post['visit_end'];

                if (empty($fromDate)) {
                    $toDate = date('Y-m-d H:i:s');
                    $fromDate = date('Y-m-d H:i:s', strtotime('-1 year', strtotime($toDate)));
                }

                $qry = "SELECT LCV.col_lead_id as `Lead ID`, MB.m_branch_name as `Branch Name`, L.loan_no as `Loan Number`, U.name as `Allocated To FE Name`, LCV.col_visit_field_remarks as `FE Remarks`, LCV.col_fe_visit_end_datetime as `Visit End DateTime` ";

                $qry .= "FROM loan_collection_visit LCV INNER JOIN loan L ON(LCV.col_lead_id=L.lead_id) INNER JOIN leads LD ON(LCV.col_lead_id=LD.lead_id) INNER JOIN master_branch MB ON(LD.lead_branch_id=MB.m_branch_id) INNER JOIN users U ON(LCV.col_visit_allocated_to=U.user_id) ";

                $qry .= "WHERE LCV.col_lead_id=L.lead_id AND L.loan_status_id=14 AND LCV.col_fe_visit_end_datetime > '$fromDate' AND LCV.col_lead_id=LD.lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND LCV.col_visit_active=1 AND LCV.col_visit_field_status_id=5 ORDER BY LCV.col_fe_visit_end_datetime ASC";

                $result = $this->db->query($qry)->result_array();

                $data = array();

                if (!empty($result)) {
                    $data['status'] = 1;
                    $data['data'] = $result;
                } else {
                    $data['status'] = 0;
                }

                return json_encode($this->response($data, REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function CallData_post()
    {
        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();

        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_chatbot'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("call_time", "Date", "trim");

            if ($this->form_validation->run() == FALSE) {
                return json_encode($this->response(['Status' => 0, 'Message' => strip_tags(validation_errors())], REST_Controller::HTTP_OK));
            } else {

                $fromDate = $post['call_time'];

                if (empty($fromDate)) {
                    $toDate = date('Y-m-d H:i:s');
                    $fromDate = date('Y-m-d H:i:s', strtotime('-1 year', strtotime($toDate)));
                }

                $qry = "SELECT LCF.lcf_lead_id, L.loan_no, CAM.repayment_date, MFT.m_followup_type_name, MFS.m_followup_status_name, LCF.lcf_remarks, U.name, LCF.lcf_next_schedule_datetime, LCF.lcf_created_on  ";

                $qry .= "FROM loan_collection_followup LCF INNER JOIN users U ON(LCF.lcf_user_id=U.user_id) LEFT JOIN master_followup_status MFS ON(LCF.lcf_status_id=MFS.m_followup_status_id) INNER JOIN master_followup_type MFT ON(LCF.lcf_type_id=MFT.m_followup_type_id) INNER JOIN loan L ON(LCF.lcf_lead_id=L.lead_id) INNER JOIN credit_analysis_memo CAM ON(LCF.lcf_lead_id=CAM.lead_id) ";

                $qry .= "WHERE LCF.lcf_active=1 AND LCF.lcf_lead_id=CAM.lead_id AND LCF.lcf_type_id=1 AND LCF.lcf_created_on > '$fromDate' ";

                $result = $this->db->query($qry)->result_array();

                foreach ($result as $res) {

                    $followdate = $res['lcf_created_on'];
                    $repaydate = $res['repayment_date'];

                    $bucket = 'Recovery';
                    if ($repaydate > $followdate) {
                        $bucket = 'Pre-Collection';
                    }

                    $resdult_data[] = array(
                        'Lead Id' => $res['lcf_lead_id'],
                        'FollowUp Date' => $followdate,
                        'FollowUp By' => $res['name'],
                        'Loan No' => $res['loan_no'],
                        'FollowUp Type' => $res['m_followup_type_name'],
                        'FollowUp Status' => $res['m_followup_status_name'],
                        'Remarks' => $res['lcf_remarks'],
                        'Month-Year' => date_format(date_create($repaydate), "M-y"),
                        'Next FollowUp Date' => $res['lcf_next_schedule_datetime'],
                        'Bucket' => $bucket,
                    );
                }

                $data = array();

                if (!empty($result)) {
                    $data['status'] = 1;
                    $data['data'] = $resdult_data;
                } else {
                    $data['status'] = 0;
                }
            }
            return json_encode($this->response($data, REST_Controller::HTTP_OK));
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }
}
