<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CAMController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    // public $tbl_customer = 'customer C';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_foir_master = "foir_master FM";
    public $tbl_cam = "credit_analysis_memo CAM";

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Admin_Model', 'Admin');
        $this->load->model('Users/Rejection_Model', 'Reject');
        $this->load->model('Users/Email_Model', 'Email');
        $this->load->model('Users/SMS_Model', 'SMS');
        $this->load->model('CAM_Model', 'CAM');
        $this->load->library('encrypt');

        $login = new IsLogin();
        $login->index();
    }

    public function getCAMDetails($lead_id) {
        $lead_id=$this->encrypt->decode($lead_id);
        $conditions = ['CAM.lead_id' => $lead_id];
        $select = "CAM.cam_id,LD.cibil, CAM.company_id, CAM.product_id, CAM.lead_id, CAM.customer_id, CAM.user_id, CAM.ntc, CAM.run_other_pd_loan, CAM.delay_other_loan_30_days, CAM.job_stability, CAM.city_category, CAM.salary_credit1, CAM.salary_credit1_date, CAM.salary_credit1_amount, CAM.salary_credit2, CAM.salary_credit2_date, CAM.salary_credit2_amount, CAM.salary_credit3, CAM.salary_credit3_date, CAM.salary_credit3_amount, CAM.next_pay_date, CAM.median_salary, CAM.salary_variance, CAM.salary_on_time, CAM.borrower_age, CAM.end_use, CAM.eligible_foir_percentage, CAM.eligible_loan, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee, DATE_FORMAT(disbursal_date, '%d-%m-%Y') as disbursal_date , DATE_FORMAT(CAM.repayment_date, '%d-%m-%Y') as repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.b2b_disbursal, CAM.b2b_number, CAM.deviationsApprovedBy, CAM.remark, CAM.created_at, CAM.updated_by, CAM.updated_at,CAM.cam_risk_profile, CAM.cam_appraised_monthly_income as cam_monthly_income, CAM.cam_appraised_obligations as cam_obligations";

        $this->db->select($select);
        $this->db->distinct();
        $this->db->from($this->tbl_leads);
        $this->db->where($conditions);
        $this->db->join($this->tbl_customer, 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'inner');
        $this->db->join($this->tbl_cam, 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'inner');
        $sql = $this->db->get();

        if ($sql->num_rows() > 0) {
            $data['getCamDetails'] = $sql->row();
            $data['calculation'] = [];
        } else {
            $leadData = $this->Tasks->getCAMDetails($lead_id);
            $data['getCamDetails'] = $leadData->row();
            $data['calculation'] = $this->calculation($leadData->row());
        }
        echo json_encode($data);
    }

    public function calculation($result) {
        $dob = $result->dob;
        $cibil = $result->cibil;
        $presentServiceTenure = $result->presentServiceTenure;

        $today = date('Y-m-d', strtotime(timestamp));
        $diff = abs(strtotime($today) - strtotime($dob));
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        // $data['borrower_age'] = $years .' Y, '. $months .' M, '. $days .' days';
        $data['borrower_age'] = $years . 'Y, ' . $months . 'M';

        if ($presentServiceTenure > 12) {
            $pst = "HIGH";
        } else if ($presentServiceTenure > 6 && $presentServiceTenure <= 12) {
            $pst = "MEDIUM";
        } else if ($presentServiceTenure <= 6) {
            $pst = "LOW";
        }

        $data['job_stability'] = $pst;
        $ntc = "YES";
        if ($cibil >= 5) {
            $ntc = "NO";
        }
        $data['ntc'] = $ntc;
        $data['cibil'] = (($cibil != NULL) ? $cibil : '');
        return $data;
    }

    public function checkLoanEligibility() {
        $cc = $_POST['city_category'];
        $monthly_salary = $_POST['monthly_salary'];
        $obligations = $_POST['obligations'];
        $ae = $_POST['alternate_email'];
        $r_type = $_POST['current_residence_type'];
        $loan_recommended = $_POST['loan_recommended'];
        $median_salary = ($_POST['median_salary']) ? $_POST['median_salary'] : 0;
        $eligible_foir_percentage = ($_POST['eligible_foir_percentage']) ? $_POST['eligible_foir_percentage'] : 0;

        $foir_percent = 0;

        if (($cc == 'A') && $monthly_salary >= 50000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.7;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.75;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.8;
            }
        } else if (($cc == 'A') && $monthly_salary >= 30000 && $monthly_salary < 50000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.6;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.65;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.7;
            }
        } else if (($cc == 'A') && $monthly_salary >= 15000 && $monthly_salary <= 30000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.5;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.55;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.6;
            }
        } else if (($cc == 'A') && $monthly_salary < 15000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0;
            }
        } else if (($cc == 'B') && $monthly_salary >= 50000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.75;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.8;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.85;
            }
        } else if (($cc == 'B') && $monthly_salary >= 30000 && $monthly_salary < 50000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.65;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.7;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.75;
            }
        } else if (($cc == 'B') && $monthly_salary >= 15000 && $monthly_salary < 30000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.55;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.6;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.65;
            }
        } else if (($cc == 'B') && $monthly_salary >= 10000 && $monthly_salary < 15000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0.45;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.50;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0.55;
            }
        } else if (($cc == 'B') && $monthly_salary < 10000) {
            if (($ae == "" || $ae == null) && ($r_type == "" || $r_type == null)) {
                $foir_percent = 0;
            } else if (($ae != "" || $ae != null) || ($r_type != "" || $r_type != null)) {
                $foir_percent = 0;
            } else if (($ae != "" || $ae != null) && ($r_type != "" || $r_type != null)) {
                $foir_percent = 0;
            }
        }




        //       $eligible_loan = ($monthly_salary * $foir_percent) - $obligations;
        // loan_recommended >= loan_applied
        // $final_foir = (($loan_recommended + $obligations) / $monthly_salary) * 100;


        $data['eligible_foir_percentage'] = number_format($foir_percent * 100, 2);
        $data['eligible_loan'] = round((($monthly_salary - $obligations) * $foir_percent), 0);

        // echo "<pre>"; print_r($data); exit;
        echo json_encode($data);
    }

    public function calculateAmount() {
        $data = $this->calcAmount($_POST);
        echo json_encode($data);
    }

    public function calcAmount($input) {
        $loan_recommended = $input['loan_recommended'];
        $obligations = $input['obligations'];
        $monthly_salary = $input['monthly_salary'];
        $eligible_foir_percentage = $input['eligible_foir_percentage'];
        $roi = ($input['roi'] ? $input['roi'] : 1);
        $processing_fee_percent = ($input['processing_fee_percent']) ? ($input['processing_fee_percent']) : 0;
        $disbursal_date = $input['disbursal_date'];
        $repayment_date = $input['repayment_date'];

        $d1 = strtotime($disbursal_date);
        $d2 = strtotime($repayment_date);
        $tenure = 0;
        if (!empty($d2)) {
            $datediff = $d2 - $d1;
            $tenure = round($datediff / (60 * 60 * 24));
        }
        $admin_fee = (($loan_recommended * $processing_fee_percent) / 100);
        $gst = ($admin_fee * 18 / 118);
        $data['roi'] = $roi;
        $data['tenure'] = $tenure;
        $data['repayment_amount'] = ($loan_recommended + ($loan_recommended * $roi * $tenure) / 100);
        $data['admin_fee'] = $admin_fee;
        $data['adminFeeWithGST'] = round($gst);
        $data['adminFeeGST'] = round($gst);
        // $data['total_admin_fee'] = $admin_fee + round($newGST);
        $data['total_admin_fee'] = $admin_fee - round($gst);
        // $data['net_disbursal_amount'] = $loan_recommended - round($data['total_admin_fee']);
        $data['net_disbursal_amount'] = $loan_recommended - round($admin_fee);
        $data['final_foir_percentage'] = number_format((($loan_recommended + $obligations) / $monthly_salary) * 100, 2);
        $data['foir_enhanced_by'] = number_format($data['final_foir_percentage'] - $eligible_foir_percentage, 2);

        return $data;
    }

    public function calculateMedian($date) {
        $salary_amount = explode("-", $date);

        $dt = explode("-", $date);
        $d = 0;
        $daysGay = 0;
        if ($dt[0] >= $dt[1]) {
            $d = $dt[0];
            $daysGay = $dt[0] - $dt[1];
        } else if ($dt[1] >= $dt[2]) {
            $d = $dt[1];
            $daysGay = $dt[1] - $dt[2];
        } else if ($dt[2] >= $dt[0]) {
            $d = $dt[2];
            $daysGay = $dt[2] - $dt[0];
        }
        $next_month = date('m') + 1;
        $next_year = date('Y');
        if ($next_month > 12) {
            $next_month = $next_month - 12;
            $next_year = $next_year + 1;
        }
        $next_pay_date = $d . '-' . $next_month . '-' . $next_year;

        $salary_on_time = "LOW";
        if ($daysGay > 5) {
            $salary_on_time = "LOW";
        } else if ($daysGay > 3 && $daysGay < 5) {
            $salary_on_time = "MEDIUM";
        } else if ($daysGay <= 2) {
            $salary_on_time = "HIGH";
        }

        $data['salary_on_time'] = $salary_on_time;
        $data['next_pay_date'] = $next_pay_date;
        echo json_encode($data);
    }

    public function averageSalary($arr) {
        $arr = explode("-", $arr);
        // echo "<pre>"; print_r($arr); exit;
        $salary1 = ($arr[0] != "") ? $arr[0] : 0;
        $salary2 = ($arr[1] != "") ? $arr[1] : 0;
        $salary3 = ($arr[2] != "") ? $arr[2] : 0;

        $average_salary = $salary1;
        if (!empty($salary1) && empty($salary2) && empty($salary3)) {
            $count = 1;
        } else if (!empty($salary1) && !empty($salary2) && empty($salary3)) {
            $count = 2;
        } else if (!empty($salary1) && !empty($salary2) && !empty($salary3)) {
            $count = 3;
        } else {
            $count = 1;
        }

        if ($salary1 >= $salary2 && $salary1 >= $salary3) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        } else if ($salary2 >= $salary1 && $salary2 >= $salary3) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        } else if ($salary3 >= $salary1 && $salary3 >= $salary2) {
            $average_salary = ($salary1 + $salary2 + $salary3) / $count;
        }
        $data['average_salary'] = round($average_salary);
        $data['salary_variance'] = $this->salaryVariance($arr);
        echo json_encode($data);
    }

    public function salaryVariance($salary) {
        sort($salary);
        $x = 0;
        $y = 0;
        if ($salary[0] != '' && $salary[1] != '' && $salary[2] != '') {
            if ($salary[0] > $salary[1]) {
                $x = ($salary[0] - $salary[1]) / $salary[0];
            } else if ($salary[1] >= $salary[0]) {
                $x = ($salary[1] - $salary[0]) / $salary[1];
            }
            if ($salary[1] >= $salary[2]) {
                $y = ($salary[1] - $salary[2]) / $salary[1];
            } else if ($salary[2] >= $salary[1]) {
                $y = ($salary[2] - $salary[1]) / $salary[2];
            } else if ($salary[0] >= $salary[2]) {
                $x = ($salary[0] - $salary[2]) / $salary[0];
            }

            $variance = "-";
            $sVariance = ($x + $y) / 2;
            if ($sVariance <= 5) {
                // $variance = "LOW";
                $variance = "HIGH";
            } else if ($sVariance > 5 && $sVariance <= 9) {
                $variance = "MEDIUM";
            } else if ($sVariance <= 10) {
                // $variance = "HIGH";
                $variance = "LOW";
            }
        } else {
            $variance = "HIGH";
        }
        return $variance;
    }

    public function getMedian($arr) {
        sort($arr);
        $count = count($arr);
        $middleval = floor(($count - 1) / 2);
        if ($count % 2) {
            $median = $arr[$middleval];
        } else {
            $low = $arr[$middleval];
            $high = $arr[$middleval + 1];
            $median = (($low + $high) / 2);
        }
        return $median;
    }

    public function saveLACCAMDetails() {
        $json['err'] = 'Work In Progress.';
        echo json_encode($json);
        echo "lac cam : <pre>";
        print_r($_POST);
        exit;
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
            $this->form_validation->set_rules('loan_recommended', 'Loan Recomended', 'required|trim');
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
                $user_id = $this->input->post('user_id');
                $company_id = $this->input->post('company_id');
                $product_id = $this->input->post('product_id');
                $userType = $this->input->post('userType');
                $cibil = $this->input->post('cibil');
                $Active_CC = $this->input->post('Active_CC');
                $cc_statementDate = $this->input->post('cc_statementDate');
                $cc_paymentDueDate = $this->input->post('cc_paymentDueDate');
                $customer_bank_name = $this->input->post('customer_bank_name');
                $account_type = $this->input->post('account_type');
                $customer_account_no = $this->input->post('customer_account_no');
                $customer_confirm_account_no = $this->input->post('customer_confirm_account_no');
                $customer_name = $this->input->post('customer_name');
                $cc_limit = $this->input->post('cc_limit');
                $cc_outstanding = $this->input->post('cc_outstanding');
                $max_eligibility = $this->input->post('max_eligibility');
                $cc_name_Match_borrower_name = $this->input->post('cc_name_Match_borrower_name');
                $emiOnCard = $this->input->post('emiOnCard');
                $DPD30Plus = $this->input->post('DPD30Plus');
                $cc_statementAddress = $this->input->post('cc_statementAddress');
                $last3monthDPD = $this->input->post('last3monthDPD');
                $higherDPDLast3month = $this->input->post('higherDPDLast3month');
                $loan_applied = $this->input->post('loan_applied');
                $loan_recommended = $this->input->post('loan_recommended');
                $processing_fee = $this->input->post('processing_fee');
                $roi = $this->input->post('roi');
                $net_disbursal_amount = $this->input->post('net_disbursal_amount');
                $disbursal_date = $this->input->post('disbursal_date');
                $repayment_date = $this->input->post('repayment_date');
                $tenure = $this->input->post('tenure');
                $repayment_amount = $this->input->post('repayment_amount');
                $special_approval = $this->input->post('special_approval');
                $deviationsApprovedBy = "";
                if ($this->input->post('deviationsApprovedBy')) {
                    $deviationsApprovedBy = $this->input->post('deviationsApprovedBy');
                }
                $changeROI = $this->input->post('changeROI');
                $changeFee = $this->input->post('changeFee');
                $changeLoanAmount = $this->input->post('changeLoanAmount');
                $changeTenure = $this->input->post('changeTenure');
                $changeRTR = "NO";
                if (!empty($this->input->post('changeRTR'))) {
                    $changeRTR = $this->input->post('changeRTR');
                }
                $remark = $this->input->post('remark');

                $isDisburseBankAC = 'NO';
                $bankIFSC_Code = '';
                $bank_name = '';
                $bank_branch = '';
                $bankA_C_No = '';
                $confBankA_C_No = '';
                $bankHolder_name = '';
                $bank_account_type = '';

                if ($this->input->post('isDisburseBankAC') == "YES") {
                    $isDisburseBankAC = $this->input->post('isDisburseBankAC');
                    $bankIFSC_Code = $this->input->post('bankIFSC_Code');
                    $bank_name = $this->input->post('bank_name');
                    $bank_branch = $this->input->post('bank_branch');
                    $bankA_C_No = $this->input->post('bankA_C_No');
                    $confBankA_C_No = $this->input->post('confBankA_C_No');
                    $bankHolder_name = $this->input->post('bankHolder_name');
                    $bank_account_type = $this->input->post('bank_account_type');
                }

                $data = [
                    'company_id' => $company_id,
                    'product_id' => $product_id,
                    'lead_id' => $lead_id,
                    'userType' => $userType,
                    'cibil' => $cibil,
                    'Active_CC' => $Active_CC,
                    'cc_statementDate' => $cc_statementDate,
                    'cc_paymentDueDate' => $cc_paymentDueDate,
                    'customer_bank_name' => $customer_bank_name,
                    'account_type' => $account_type,
                    'customer_account_no' => $customer_account_no,
                    'customer_confirm_account_no' => $customer_confirm_account_no,
                    'customer_name' => $customer_name,
                    'cc_limit' => $cc_limit,
                    'cc_outstanding' => $cc_outstanding,
                    'max_eligibility' => $max_eligibility,
                    'cc_name_Match_borrower_name' => $cc_name_Match_borrower_name,
                    'emiOnCard' => $emiOnCard,
                    'DPD30Plus' => $DPD30Plus,
                    'cc_statementAddress' => $cc_statementAddress,
                    'last3monthDPD' => $last3monthDPD,
                    'higherDPDLast3month' => $higherDPDLast3month,
                    'isDisburseBankAC' => $isDisburseBankAC,
                    'bankIFSC_Code' => $bankIFSC_Code,
                    'bank_name' => $bank_name,
                    'bank_branch' => $bank_branch,
                    'bankA_C_No' => $bankA_C_No,
                    'confBankA_C_No' => $confBankA_C_No,
                    'bankHolder_name' => $bankHolder_name,
                    'bank_account_type' => $bank_account_type,
                    'loan_applied' => $loan_applied,
                    'loan_recommended' => $loan_recommended,
                    'processing_fee' => $processing_fee,
                    'roi' => $roi,
                    'net_disbursal_amount' => $net_disbursal_amount,
                    'disbursal_date' => $disbursal_date,
                    'repayment_date' => $repayment_date,
                    'tenure' => $tenure,
                    'repayment_amount' => $repayment_amount,
                    'special_approval' => $special_approval,
                    'deviationsApprovedBy' => $deviationsApprovedBy,
                    'changeROI' => $changeROI,
                    'changeFee' => $changeFee,
                    'changeLoanAmount' => $changeLoanAmount,
                    'changeTenure' => $changeTenure,
                    'changeRTR' => $changeRTR,
                    'remark' => $remark,
                    'cam_created_by' => $user_id,
                    'cam_created_date' => created_at,
                    'cam_updated_by' => $user_id,
                    'cam_updated_date' => created_at,
                ];

                $where = ['company_id' => company_id, 'product_id' => product_id];
                $sql = $this->db->select('CAM.cam_id')->where('CAM.lead_id', $lead_id)->from('tbl_cam as CAM')->order_by('CAM.cam_id', 'desc')->get();

                if ($sql->num_rows() > 0) {
                    $row = $sql->row();
                    $cam_id = $row->cam_id;
                    $result = $this->db->where('cam_id', $cam_id)->update('tbl_cam', $data);
                    $json['msg'] = "CAM Updated Successfully.";
                    echo json_encode($json);
                } else {
                    $result = $this->db->insert('tbl_cam', $data);
                    $json['msg'] = "CAM Save Successfully.";
                    echo json_encode($json);
                }
            }
        }
    }

    public function check_NaN($str) {
        if ($str == 'NaN') {
            $this->form_validation->set_message('check_NaN', 'The %s field can not be the input "NaN"');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function check_zero($str) {
        if ($str == 0) {
            $this->form_validation->set_message('check_zero', 'The %s field can not be the input "0"');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function check_dash($str) {
        if ($str == "-") {
            $this->form_validation->set_message('check_dash', 'The %s field can not be the input "-"');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function getPersonalDetails2() {

        // $queryCustomerPersonal = $this->Tasks->customerPersonalDetails(['LD.lead_id' => $lead_id]);
        $queryCustomerPersonal = $this->Tasks->customerPersonalDetails(['LD.lead_id' => 6098]);
        $personalAndEmployment = $queryCustomerPersonal->row_array();
        echo "<pre>";
        print_r($personalAndEmployment);
        exit;
        $data_Cif = [
            'cif_residence_address_1' => $personalAndEmployment['current_house'],
            'cif_residence_address_2' => $personalAndEmployment['current_locality'],
            'cif_residence_landmark' => $personalAndEmployment['current_landmark'],
            'cif_residence_city_id' => ($personalAndEmployment['city_id']) ? $personalAndEmployment['city_id'] : 0,
            'cif_residence_state_id' => ($personalAndEmployment['state_id']) ? $personalAndEmployment['state_id'] : 0,
            'cif_residence_pincode' => $personalAndEmployment['cr_residence_pincode'],
            'cif_residence_since' => $personalAndEmployment['current_residence_since'],
            'cif_residence_type' => $personalAndEmployment['current_residence_type'],
            'cif_residence_residing_with_family' => $personalAndEmployment['current_residing_withfamily'],
            'cif_aadhaar_no' => $personalAndEmployment['aadhar_no'],
            'cif_office_address_1' => $personalAndEmployment['emp_shopNo'],
            'cif_office_address_2' => $personalAndEmployment['emp_locality'],
            'cif_office_address_3' => $personalAndEmployment['office_address'],
            'cif_office_address_landmark' => $personalAndEmployment['emp_landmark'],
            'cif_office_city_id' => $personalAndEmployment['emp_city'],
            'cif_office_state_id' => $personalAndEmployment['emp_state'],
            'cif_office_pincode' => $personalAndEmployment['emp_pincode'],
            'cif_company_name' => $personalAndEmployment['employer_name'],
            'cif_company_website' => $personalAndEmployment['emp_website'],
            'cif_company_type_id' => $personalAndEmployment['emp_employer_type'],
            'cif_aadhaar_same_as_residence' => $personalAndEmployment['emp_residence_since'],
            'cif_aadhaar_address_1' => $personalAndEmployment['aa_current_house'],
            'cif_aadhaar_address_2' => $personalAndEmployment['aa_current_locality'],
            'cif_aadhaar_landmark' => $personalAndEmployment['aa_current_landmark'],
            'cif_aadhaar_city_id' => $personalAndEmployment['aa_current_city_id'],
            'cif_aadhaar_state_id' => $personalAndEmployment['aa_current_state_id'],
            'cif_aadhaar_pincode' => $personalAndEmployment['aa_cr_residence_pincode'],
            // 'cif_loan_is_disbursed' => $personalAndEmployment['aa_current_landmark'],
            'cif_office_working_since' => $personalAndEmployment['aa_current_landmark'],
            'cif_office_designation' => $personalAndEmployment['emp_designation'],
            'cif_office_department' => $personalAndEmployment['emp_department'],
        ];
        $this->Tasks->globalUpdate(['cif_customer' => $personalAndEmployment['customer_id']], $data_Cif, 'cif_customer');
        // $this->db->insert('cif_customer', $data_Cif);
    }

    public function savePaydayCAMDetails() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = False;
            echo json_encode($json);
            return false;
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');

            $this->form_validation->set_rules('company_id', 'Company ID', 'required|trim');
            $this->form_validation->set_rules('product_id', 'Product ID', 'required|trim');
            $this->form_validation->set_rules('run_other_pd_loan', 'Run other payday loan', 'required|trim');
            $this->form_validation->set_rules('delay_other_loan_30_days', 'Delay other loan in 30 days', 'required|trim');
            $this->form_validation->set_rules('next_pay_date', 'Next Pay Date', 'required|trim');
            $this->form_validation->set_rules('salary_credit1_date', 'Salary Date 1', 'required|trim');
            $this->form_validation->set_rules('salary_credit2_date', 'Salary Date 2', 'trim');
            $this->form_validation->set_rules('salary_credit3_date', 'Salary Date 3', 'trim');
            $this->form_validation->set_rules('salary_credit1_amount', 'Salary Amount 1', 'required|trim|is_natural_no_zero|max_length[10]');
            $this->form_validation->set_rules('salary_credit2_amount', 'Salary Amount 2', 'trim|max_length[10]');
            $this->form_validation->set_rules('salary_credit3_amount', 'Salary Amount 3', 'trim|max_length[10]');
            $this->form_validation->set_rules('processing_fee_percent', 'Admin Fee Percentage', 'required|trim');
            $this->form_validation->set_rules('loan_recommended', 'Loan Recommended', 'required|trim');
            $this->form_validation->set_rules('disbursal_date', 'Disbursal Date', 'required|trim');
            $this->form_validation->set_rules('repayment_date', 'Repayment Date', 'required|trim');
            $this->form_validation->set_rules('roi', 'ROI', 'required|trim|callback_check_zero');
            $this->form_validation->set_rules('risk_profile', 'Risk Profile', 'required|trim');
            $this->form_validation->set_rules('obligations', 'Appraised Obligations', 'required|trim');
            $this->form_validation->set_rules('monthly_salary', 'Appraised Salary', 'required|trim|is_natural_no_zero|max_length[10]');

            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = $this->encrypt->decode($this->input->post('lead_id',true));
                $eligible_loan = $this->input->post('eligible_loan',true);

                $queryCustomerPersonal = $this->Tasks->customerPersonalDetails(['LD.lead_id' => $lead_id]);

                $personalAndEmployment = $queryCustomerPersonal->row_array();
                $lead_data_source_id = $personalAndEmployment['lead_data_source_id'];

                if (empty($personalAndEmployment['current_house']) || empty($personalAndEmployment['current_locality']) || empty($personalAndEmployment['res_city_id']) || empty($personalAndEmployment['res_state_id']) || empty($personalAndEmployment['cr_residence_pincode'])) {
                    $json['err'] = "Please fill the mandatory fields in residence address.";
                    echo json_encode($json);
                    return false;
                }

                if (empty($personalAndEmployment['aa_current_house']) || empty($personalAndEmployment['aa_current_locality']) || empty($personalAndEmployment['aa_current_city_id']) || empty($personalAndEmployment['aa_current_state_id']) || empty($personalAndEmployment['aa_cr_residence_pincode'])) {
                    $json['err'] = "Please fill the mandatory fields in aadhaar address.";
                    echo json_encode($json);
                    return false;
                }

                if (empty($personalAndEmployment['emp_house']) || empty($personalAndEmployment['emp_street']) || empty($personalAndEmployment['office_city_id']) || empty($personalAndEmployment['office_state_id']) || empty($personalAndEmployment['emp_pincode'])) {
                    $json['err'] = "Please fill the mandatory fields in office address.";
                    echo json_encode($json);
                    return false;
                }

                if (empty($_POST['loan_recommended'])) {
                    $json['err'] = "Loan recommend amount cannot empty.";
                    echo json_encode($json);
                    return false;
                }

                if ($_POST['loan_recommended'] > $personalAndEmployment['loan_amount']) {
                    $json['err'] = "Loan recommended cannot be grater then applied loan amount.";
                    echo json_encode($json);
                    return false;
                }

                if (empty($eligible_loan)) {
                    $json['err'] = "Eligible loan amount cannot empty.";
                    echo json_encode($json);
                    return false;
                }

                if ($_POST['loan_recommended'] > $eligible_loan) {
                    $json['err'] = "Loan recommended cannot be grater then eligible loan amount.";
                    echo json_encode($json);
                    return false;
                }

                if ($_POST['loan_recommended'] > 115000 && !in_array($lead_data_source_id, array(21, 27, 33))) {
                    $json['err'] = "Loan recommended cannot be grater then Rs. 1,15,000.";
                    echo json_encode($json);
                    return false;
                }

                if ($_POST['loan_recommended'] > 500000 && in_array($lead_data_source_id, array(21, 27, 33))) {
                    $json['err'] = "Loan recommended cannot be grater then Rs. 5,00,000.";
                    echo json_encode($json);
                    return false;
                }

                $company_holiday = $this->Tasks->checkCompanyHolidayDate($_POST['repayment_date']);

                if ($company_holiday['status'] == 1) {
                    $json['err'] = $company_holiday['message'];
                    echo json_encode($json);
                    return false;
                }

                $data_calculation = [
                    'loan_recommended' => htmlspecialchars($_POST['loan_recommended']),
                    'obligations' => htmlspecialchars($_POST['obligations']),
                    'monthly_salary' => htmlspecialchars($_POST['monthly_salary']),
                    'eligible_foir_percentage' => htmlspecialchars($_POST['eligible_foir_percentage']),
                    'roi' => ($_POST['roi'] ? htmlspecialchars($_POST['roi']) : 1),
                    'processing_fee_percent' => ($_POST['processing_fee_percent']) ? htmlspecialchars($_POST['processing_fee_percent']) : 0,
                    'disbursal_date' => date("Y-m-d", strtotime($_POST['disbursal_date'])),
                    'repayment_date' => $_POST['repayment_date']
                ];

                $calculationAmount = $this->calcAmount($data_calculation);

                $data = [
                    'lead_id' => $lead_id,
                    'company_id' => $this->input->post('company_id',true),
                    'product_id' => $this->input->post('product_id',true),
                    'ntc' => $this->input->post('ntc'),
                    'run_other_pd_loan' => $this->input->post('run_other_pd_loan',true),
                    'delay_other_loan_30_days' => $this->input->post('delay_other_loan_30_days',true),
                    'job_stability' => $this->input->post('job_stability',true),
                    'city_category' => $this->input->post('city_category',true),
                    'salary_credit1_date' => $this->input->post('salary_credit1_date'),
                    'salary_credit2_date' => $this->input->post('salary_credit2_date'),
                    'salary_credit3_date' => $this->input->post('salary_credit3_date'),
                    'salary_credit1_amount' => $this->input->post('salary_credit1_amount',true),
                    'salary_credit2_amount' => $this->input->post('salary_credit2_amount',true),
                    'salary_credit3_amount' => $this->input->post('salary_credit3_amount',true),
                    'next_pay_date' => $this->input->post('next_pay_date'),
                    'median_salary' => $this->input->post('median_salary',true),
                    'salary_variance' => $this->input->post('salary_variance',true),
                    'salary_on_time' => $this->input->post('salary_on_time'),
                    'borrower_age' => $this->input->post('borrower_age',true),
                    'end_use' => $this->input->post('end_use'),
                    'eligible_foir_percentage' => $this->input->post('eligible_foir_percentage',true),
                    'eligible_loan' => round($eligible_loan, 0),
                    'loan_recommended' => $this->input->post('loan_recommended',true),
                    'final_foir_percentage' => $calculationAmount['final_foir_percentage'],
                    'foir_enhanced_by' => $calculationAmount['foir_enhanced_by'],
                    'processing_fee_percent' => $this->input->post('processing_fee_percent',true),
                    'roi' => $this->input->post('roi',true),
                    'admin_fee' => $calculationAmount['admin_fee'],
                    'disbursal_date' => date('Y-m-d', strtotime($this->input->post('disbursal_date'))),
                    'repayment_date' => date('Y-m-d', strtotime($this->input->post('repayment_date'))),
                    'adminFeeWithGST' => $calculationAmount['adminFeeWithGST'],
                    'total_admin_fee' => $calculationAmount['total_admin_fee'],
                    'tenure' => $calculationAmount['tenure'],
                    'net_disbursal_amount' => $calculationAmount['net_disbursal_amount'],
                    'repayment_amount' => $calculationAmount['repayment_amount'],
                    'panel_roi' => $this->input->post('roi') * 2,
                    'b2b_disbursal' => $this->input->post('b2b_disbursal',true),
                    'b2b_number' => $this->input->post('b2b_number',true),
                    'cam_risk_profile' => $this->input->post('risk_profile',true),
                    'cam_appraised_obligations' => !empty($this->input->post('obligations')) ? $this->input->post('obligations',true) : 0,
                    'cam_appraised_monthly_income' => !empty($this->input->post('monthly_salary')) ? $this->input->post('monthly_salary',true) : 0,
                    'deviationsApprovedBy' => $this->input->post('deviationsApprovedBy') ? $this->input->post('deviationsApprovedBy',true) : "",
                    'remark' => $this->input->post('remark',true),
                ];

                $conditions = ['CAM.lead_id' => $lead_id];
                $fetch = 'CAM.cam_id, CAM.lead_id, CAM.user_id';
                $sql = $this->Tasks->select($conditions, $fetch, $this->tbl_cam);
//                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['obligations' => $_POST['obligations']], 'leads');
//                $this->Tasks->globalUpdate(['lead_id' => $lead_id], ['monthly_income' => $_POST['monthly_salary']], 'customer_employment');

                if ($sql->num_rows() > 0) {
                    $row = $sql->row();
                    if ($_SESSION['isUserSession']['user_id'] != $row->user_id) {
                        $arrData = [
                            'updated_by' => $_SESSION['isUserSession']['user_id'],
                            'updated_at' => timestamp,
                            'cam_status' => 1,
                        ];
                        $data = array_merge($data, $arrData);
                    }

                    $conditions2 = ['cam_id' => $row->cam_id];
                    $this->Tasks->globalUpdate($conditions2, $data, 'credit_analysis_memo');
                    $json['msg'] = "CAM Updated Successfully.";
                    echo json_encode($json);
                } else {
                    $arrData = [
                        'created_by' => $_SESSION['isUserSession']['user_id'],
                        'user_id' => $_SESSION['isUserSession']['user_id'],
                        'created_at' => timestamp,
                        'cam_status' => 1,
                    ];

                    $data = array_merge($data, $arrData);
                    $result = $this->db->insert('credit_analysis_memo', $data);
                    $json['msg'] = "CAM Save Successfully.";
                    echo json_encode($json);
                }
            }
        }
    }

    public function reEditCAM($lead_id) {
        $status = ['status' => "SEND BACK"];
        $this->Task_Model->updateLeads($lead_id, $status);
        $this->CAM->updateCAM($lead_id, $status);
        $json['msg'] = "Lead Send Back Successfully.";
        echo json_encode($json);
    }

    public function headCAMApproved($lead_id) {
        $status = "SANCTION";
        $personalDetails = $this->Task_Model->getPersonalDetails($lead_id);
        $rowMail = $this->Email->getMailAndSendTocustomer(company_id, product_id, $status);
        if ($rowMail->num_rows() > 0) {
            $mail = $rowMail->row();
            $emailArr = [
                'email' => $personalDetails['leadDetails']['email'],
                'alternateEmail' => $personalDetails['leadDetails']['alternateEmail']
            ];
            $i = 1;
            foreach ($emailArr as $emailID) {
                if (!empty($emailID)) {
                    $AgreementLetter = $this->loanAgreementLetter($lead_id, $i);
                    $loanAgreementRequest = $this->sentmail($AgreementLetter, $emailID, $mail);
                    if ($loanAgreementRequest > 0) {
                        $json['notification'] = "Mail send Successfully.";
                        echo json_encode($json);
                    } else {
                        $json['notification'] = "Failed to send Sanction Mail.";
                        echo json_encode($json);
                    }
                }
                $i++;
            }
        }

        $data = [
            "lead_id" => $lead_id,
            // "loan_no" 				    => "NULL",
            "company_id" => $AgreementLetter['company_id'] ? $AgreementLetter['company_id'] : company_id,
            "product_id" => $AgreementLetter['product_id'] ? $AgreementLetter['product_id'] : product_id,
            "customer_id" => $AgreementLetter['customer_id'] ? $AgreementLetter['customer_id'] : $personalDetails['leadDetails']['customer_id'],
            "customer_name" => $AgreementLetter['name'] ? $AgreementLetter['name'] : $personalDetails['leadDetails']['name'],
            "pancard" => $AgreementLetter['pancard'] ? $AgreementLetter['pancard'] : $personalDetails['leadDetails']['pancard'],
            "loanAgreementLetter" => $AgreementLetter['message'] ? $AgreementLetter['message'] : "",
            "loanAgreementRequest" => $loanAgreementRequest ? $loanAgreementRequest : 0,
            "agrementRequestedDate" => updated_at,
            "created_by" => user_id,
            "created_on" => updated_at,
        ];

        $CAMdata = [
            'status' => $status,
            'sanctioned_by' => user_id,
            'sanctioned_date' => updated_at,
        ];

        $status = ['status' => $status];
        $this->Task_Model->updateLeads($lead_id, $status);
        $this->CAM->updateCAM($lead_id, $CAMdata);
        $result = $this->db->insert('tbl_disburse', $data);
        if ($result == 1) {
            $json['msg'] = "Lead Sanction Successfully.";
        } else {
            $json['err'] = "Sanction Failed. try again";
        }
        echo json_encode($json);
    }

    public function loanAgreementLetter($lead_id, $count) {
        $personalDetails = $this->Task_Model->getPersonalDetails($lead_id);
        $loan = $this->Task_Model->getCAMDetails($lead_id);

        $company_id = $personalDetails['leadDetails']['company_id'];
        $product_id = product_id;
        $customer_id = $personalDetails['leadDetails']['customer_id'];
        $name = $personalDetails['leadDetails']['name'];
        $email = $personalDetails['leadDetails']['email'];
        $alternateEmail = $personalDetails['leadDetails']['alternateEmail'];
        $gender = $personalDetails['leadDetails']['gender'];
        $dob = $personalDetails['leadDetails']['dob'];
        $pancard = $personalDetails['leadDetails']['pancard'];
        $loan_no = '';
        $loan_amount = $loan['camDetails']['loan_recommended'];
        $admin_fee = $loan['camDetails']['processing_fee'];
        $adminFeeWithGST = $loan['camDetails']['adminFeeWithGST'];
        $netDueAmount = $loan['camDetails']['net_disbursal_amount'];
        $tenure = $loan['camDetails']['tenure'];
        $disbursal_date = $loan['camDetails']['disbursal_date'];
        $repayment_date = $loan['camDetails']['repayment_date'];
        $roi = $loan['camDetails']['roi'];
        $repayment_amount = $loan['camDetails']['repayment_amount'];
        $isDisburseBankAC = $loan['camDetails']['isDisburseBankAC'];

        $accountNo = "Credit Card Account Number";
        $holderName = "Credit Card Holder Name";
        $bankName = "Credit Card Bank Name";
        $accountType = "Credit Card Type";
        $customer_account_no = $loan['camDetails']['customer_account_no'];
        $customer_name = $loan['camDetails']['customer_name'];
        $customer_bank = $loan['camDetails']['customer_bank_name'];
        $account_type = $loan['camDetails']['account_type'];

        if ($isDisburseBankAC == "YES") {
            $accountNo = "Bank Account Number";
            $holderName = "Bank Holder Name";
            $bankName = "Bank Name";
            $accountType = "Account Type";

            $customer_account_no = $loan['camDetails']['bankA_C_No'];
            $customer_name = $loan['camDetails']['bankHolder_name'];
            $customer_bank = $loan['camDetails']['bank_name'];
            $account_type = $loan['camDetails']['bank_account_type'];
        }

        $mr = "Ms. ";
        if ($gender == "MALE" || $gender == "Male") {
            $mr = "Mr. ";
        }

        if (strlen($tenure) == 1) {
            $tenure = "0" . $tenure;
        }

        $where = ['company_id' => company_id, 'product_id' => product_id];
        $logo = $this->db->select('title, link, url')->where($where)->from('logo')->get()->row();
        if (company_id == 2 && product_id == 1) {
            $disburseIn = "Bank";
        } else if (company_id == 2 && product_id == 2) {
            $disburseIn = "Credit Card";
        }
        $message = '
                     <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">                
                     <html xmlns="http://www.w3.org/1999/xhtml">                 
                     <head>                 
                     <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />                 
                     <title>' . $logo->title . '</title>                 
                     </head>                 
                     <body>                                  
                     <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:22px;">                   
                     <tr>                     
                     <td width="381" align="left">
                     <img src="' . $logo->url . '" width="234" height="60" /></td>                     
                     <td width="11" align="left">&nbsp;</td>                     
                     <td width="384" align="right">
                     <table width="100%" border="0">                       
                     <tr>                         
                     <td align="right"><strong>' . $mr . '' . strtoupper($name) . '</strong></td>                       
                     </tr>                       
                     <tr>                         
                         <td align="right"><strong>Loan No.:</strong> ' . $loan_no . ' </td>                       
                         </tr>                     
                         </table></td>                   
                     </tr>                   
                     <tr>                     
                     <td colspan="3"><hr / style="background:#ddd !important;"></td>                   
                     </tr>                   
                     <tr>                     
                     <td colspan="3">&nbsp;</td>                   
                     </tr>                   
                        <tr>                     
                            <td rowspan="7" align="left" valign="top">
                                <table width="100%" border="0">                       
                                    <tr>                         
                                        <td align="left" valign="middle">Dear  ' . strtoupper($name) . ',</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td align="left" valign="middle">Thank  you for choosing us and giving us the opportunity to be of service to you. Hope  you are satisfied with us.</td>                       
                                    </tr>                       
                                    <tr>                         
                                    <td align="left" valign="middle">In  response to your loan application, we are pleased to sanction you a personal  loan with the following terms and conditions. Please go through the terms and  conditions carefully and give your consent so that we may proceed with the disbursal of your loan and credit your credit card account.</td>                       
                                    </tr>                       
                                    <tr>                         
                                    <td align="left" valign="middle">You can repay the loan  via this link <a href="https://eazypay.icicibank.com/homePage" target="_blank" style="text-decoration:blink; color:#1a5ee6;"><span style="background : orange; color : #fff; padding : 2px;">https://eazypay.icicibank.com/homePage</span></a>                         or UPI ID <span style="background : orange; color : #fff; padding : 2px;">8076329281@okbizaxis</span>. Kindly make the payment in the name of Naman  Finlease Pvt. Ltd. </td>                       
                                    </tr>                     
                                </table>
                            </td>                     
                            <td>&nbsp;</td>                     
                            <td rowspan="7" align="center" valign="top"><img src="' . base_url("public/img/image-loan.jpg") . '" width="384" height="261" / style="border:solid 1px #ccc; padding:5px;"></td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td>&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><img src="' . base_url('public/img/img.png') . ' " alt="text" width="26" height="10" /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left">' . $logo->title . '.com is powered by Naman Finlease Pvt.  Ltd. with registered office at S-370, Panchsheel Park, New Delhi-110017 </td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center" style="padding-top : 8px;padding-bottom : 10px;"><strong>Most  Important Terms and Conditions (MITC)</strong></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left">
                                <table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">                       
                                    <tr>                         
                                        <td width="42%" bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Name </td>                         
                                        <td width="58%" bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $mr . '' . strtoupper($name) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Loan Amount</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rs. ' . round($loan_amount) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Administrative Fee</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rs. ' . number_format($admin_fee, 2) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Admin Fee with GST (18 %)</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rs. ' . round($adminFeeWithGST) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Net Disbursal Amount</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rs. ' . round($netDueAmount) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Disbursal Date</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . date('d-m-Y', strtotime($disbursal_date)) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Tenure</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $tenure . ' days</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Repayment Date</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . date('d-m-Y', strtotime($repayment_date)) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rate of Interest</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $roi . ' % per day (365.00 % per annum)</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Repayment Amount</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Rs. ' . round($repayment_amount) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; Late Payment Penalty *</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; 1.00 % per day (365.00% per annum)</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $accountNo . '</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $customer_account_no . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $holderName . '</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . strtoupper($customer_name) . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $bankName . '</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $customer_bank . '</td>                       
                                    </tr>                       
                                    <tr>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $accountType . '</td>                         
                                        <td bgcolor="#FFFFFF" style="padding:5px;">&nbsp; ' . $account_type . '</td>                       
                                    </tr>                     
                                </table>                                          
                            </td>                   
                        </tr>                                        
                        <tr>                     
                            <td colspan="3" align="left"><em>* Additional Interest Rate is applicable over and above the Rate of  Interest applicable to your loan for the delayed period of the loan.</em></td>                     
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><img src="' . base_url('public/img/img.png') . '" alt="text" width="26" height="10" /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><strong>Loan amount  to be credited directly to your ' . $disburseIn . ' account as per your explicit  instructions. </strong></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left">Non-payment of loan on time will affect your CIBIL Score adversely and will reduce your ability to avail further loans from banks and financial institutions in future. Please provide your confirmation through the link below in agreement to the above terms.</td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><img src="' . base_url('public/img/img.png') . '" alt="text" width="26" height="10" /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><strong>Best Regards,</strong><br /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><strong>Team ' . $logo->title . '</strong></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><img src="' . base_url('public/img/img.png') . '" alt="text" width="26" height="10" /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center"><p style="text-align: left;background: #0070c0;padding: 5px 10px; color: #fff; border-radius: 20px; font-style: italic; border: 1px #065892 solid;width: 77%;">
                                <span>"I hereby agree to the above loan terms and conditions and authorise ' . $logo->title . '.com to credit my ' . $disburseIn . ' account with the loan money as per details conveyed above. I remain committed to repay the loan within due date and liable to legal prosecution on the event of default in the repayment of loan with all interest and charges as applicable."</span></p></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="left"><img src="' . base_url('public/img/img.png') . '" alt="text" width="26" height="10" /></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center"><a href="' . base_url('loan-Agreement-Letter-Response/' . $lead_id . '/' . $count) . '" style="background: #ff0000; color: #fff; padding: 11px 13px; border-radius: 3px; text-decoration: blink; font-weight: bold; text-transform: uppercase;">&quot;Agree &amp; Confirm&quot; </a> 
                            <img src="' . base_url('public/img/hand.gif') . '" width="40" height="25"  style="position: relative; top: 8px;"></td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center">&nbsp;</td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center">Our financial friendship has been started.</td>                   
                        </tr>                   
                        <tr>                     
                            <td colspan="3" align="center">You are special !!</td>                   
                        </tr>                 
                    </table>                 
                </body>                 
            </html>
		    ';
        $data = [
            'company_id' => $company_id,
            'customer_id' => $customer_id,
            'name' => $name,
            'pancard' => $pancard,
            'email' => $email,
            'alternateEmail' => $alternateEmail,
            'message' => $message,
        ];

        return $data;
    }

    public function sentmail($data, $email, $mail) {
        $mailRequest = 0;
        $authMail = $this->Email->getAuthMail(company_id, product_id);

        if ($authMail->num_rows() > 0) {
            $mailAuth = $authMail->row();

            $config['protocol'] = $mailAuth->protocol;
            $config['smtp_host'] = $mailAuth->smtp_host;
            $config['smtp_port'] = $mailAuth->smtp_port;
            $config['smtp_timeout'] = '7';
            $config['smtp_user'] = $mailAuth->smtp_user;
            $config['smtp_pass'] = $mailAuth->smtp_pass;
            $config['charset'] = 'utf-8';
            $config['newline'] = "\r\n";
            $config['mailtype'] = 'html';
            $config['validation'] = TRUE;
            $this->load->library('email');
            $this->email->initialize($config);
            $this->email->from($mail->from_email);
            $this->email->to($email);
            $this->email->bcc($mail->bcc);
            $this->email->subject($mail->subject);
            $this->email->message($data['message']);
            if ($this->email->send() == true) {
                $mailRequest = 1;
            }
        }
        return $mailRequest;
    }

    public function CAMData($lead_id) {
        $leadDetails = $this->Task_Model->getLeadDetails($lead_id);
        $data['leadDetails'] = $leadDetails->row();

        $getCustomerDocs = $this->Task_Model->getCustomerDocs($lead_id);
        $data['getCustomerDocs'] = $getCustomerDocs->result();

        $getPersonalDetails = $this->Task_Model->getPersonalDetails($lead_id);
        $data['getPersonalDetails'] = $getPersonalDetails;

        $usr_created_by = $getPersonalDetails['leadDetails']['usr_created_by'];
        $usr_created_at = $getPersonalDetails['leadDetails']['usr_created_at'];
        $user1 = $this->Admin->getUser($usr_created_by);
        $processBy = $user1->row();

        $sanctioned_by = $getPersonalDetails['leadDetails']['sanctioned_by'];
        $sanctioned_date = $getPersonalDetails['leadDetails']['sanctioned_date'];
        $user2 = $this->Admin->getUser($sanctioned_by);
        $sanctionedBy = $user2->row();

        $getCAMDetails = $this->Task_Model->getCAMDetails($lead_id);
        $data['getCAMDetails'] = $getCAMDetails;

        $getDisbursalDetails = $this->Task_Model->getDisbursalDetails($lead_id);
        $data['getDisbursalDetails'] = $getDisbursalDetails;

        $disburseBy = $getDisbursalDetails['Disburse']['disburse_By'];
        $disburse_date = $getDisbursalDetails['Disburse']['disburse_date'];

        $user3 = $this->Admin->getUser($disburseBy);
        $disbursedBy = $user3->row();

        $data['UsersProcessBy'] = [
            'processBy' => $processBy->name,
            'processDate' => $usr_created_at,
            'sanctioned_by' => $sanctionedBy->name,
            'sanctioned_date' => $sanctioned_date,
            'disburse_by' => $disbursedBy->name,
            'disburse_date' => $disburse_date,
        ];
        $bday = new DateTime($data['getPersonalDetails']['leadDetails']['dob']);
        $today = new Datetime(date('d-m-Y'));
        $diff = $today->diff($bday);
        $data['yourAge'] = $diff->y . " years, " . $diff->m . " months, " . $diff->d . " days";

        return $data;
    }

    public function viewCAM($lead_id, $token) {
        // Check supplied token is valid
        // if ( ! ($this->_check_token($token)))
        // {
        //     $this->session->set_flashdata('status', 'Error');
        //     redirect('account/private_messages');
        // }
        $data = $this->CAMData($lead_id);
        $this->load->view('Tasks/cam', $data);
    }

    function _check_token($token) {
        return ($token === $_COOKIE[$this->csrf_cookie_name]);
    }

    public function downloadCAM($lead_id) {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetProtection(array());
        $header = array(
            'odd' => array(
                'L' => array(
                    'content' => '<img src="' . base_url('/public/img/') . 'namanfinlease.png" />',
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color' => '#000000'
                ),
                'C' => array(
                    'content' => '',
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color' => '#000000'
                ),
                'R' => array(
                    'content' => '<img src="https://fintechcloud.in/lac/public/img/laclogo.png" />',
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color' => '#000000'
                ),
                'line' => 0,
            ),
            'even' => array()
        );
        $mpdf->SetHeader($header);
        $data = $this->CAMData($lead_id);
        $html = $this->load->view('Tasks/cam', $data, true);
        $mpdf->AddPage('', // L - landscape, P - portrait 
                '', '', '', '',
                5, // margin_left
                5, // margin right
                20, // margin top
                20, // margin bottom
                0, // margin header
                0); // margin footer
        $mpdf->WriteHTML($html);
        $mpdf->defaultfooterline = 1;
        $file_name = $data['getPersonalDetails']['leadDetails']['name'] . "_" . $data['getPersonalDetails']['selectedCity'] . "_" . $data['getPersonalDetails']['leadDetails']['created_on'];
        $mpdf->Output($file_name . '.pdf', 'D');
    }

    public function resentEkycEmail($lead_id) {
        
        $lead_id=$this->encrypt->decode($lead_id);
            
        $query = $this->db->query("SELECT LD.lead_id, LD.lead_status_id, LD.lead_screener_assign_user_id, LD.lead_branch_id, LD.user_type, C.pancard, C.aadhar_no, LD.lead_data_source_id, C.customer_digital_ekyc_flag FROM leads LD INNER JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id) WHERE LD.lead_id = " . $lead_id);

        $leadDetails = $query->row_array();
        
        if (empty($leadDetails)) {
            $json['err'] = "Lead details does not exist.[CAM01]";
            echo json_encode($json);
            return false;
        }

        if ($leadDetails['customer_digital_ekyc_flag'] == 1) {
            $json['err'] = "E-KYC already done.[CAM02]";
            echo json_encode($json);
            return false;
        }

        $return_val = $this->Tasks->sent_ekyc_request_email($lead_id);

        if ($return_val == "true") {
            $json['msg'] = "E-KYC email resent successfully.";
            echo json_encode($json);
            return false;
        } else {
            $json['err'] = "E-KYC email sending failed.[CAM03]";
            echo json_encode($json);
            return false;
        }
    }

}

?>
