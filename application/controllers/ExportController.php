<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ExportController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Export_Model');
        $this->load->model('Report_Model');
        $this->load->model('Task_Model', 'Tasks');
        ini_set('memory_limit', '10000M');
        set_time_limit(300);
        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $data['masterExport'] = $this->Export_Model->ExportMaster();
        //echo "<pre>"; print_r($data); die;
        $this->load->view('Export/export', $data);
    }

    public function dataExport($export_header_array, $export_data_array, $file_name) {
        $result = $this->Export_Model->ReportName($file_name['report_name']);
        $filename = $result[0]['m_export_name'] . '_' . $file_name['fromdate'] . ' _ ' . $file_name['toDate'] . "_download_" . date('YmdHis') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $file = fopen('php://output', 'w');
        fputcsv($file, $export_header_array);
        if (!empty($export_data_array)) {
            foreach ($export_data_array as $export_data) {
                fputcsv($file, $export_data);
            }
        }
        fclose($file);
        exit;
    }

    public function FilterExportReports() {

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $user_id = $_SESSION['isUserSession']['user_id'];

            // if (!in_array($user_id, [37, 21, 65]))
            // if (!in_array($user_id, [21]))
            //     if (agent != "CA") {
            //         if ((date("Hi") >= 930 && date("Hi") <= 1830)) {
            //             $this->session->set_flashdata('msg', '<strong style="color:red">You can not export data between 9:30 AM to 6:30 PM.</strong>');
            //             return redirect(base_url('exportData/'), 'refresh');
            //         }
            //     }

            $this->form_validation->set_rules('report_id', 'Report Type', 'trim');
            $this->form_validation->set_rules('from_date', 'From Date', 'trim');
            $this->form_validation->set_rules('to_date', 'To Date', 'trim');

            $user_id = $_SESSION['isUserSession']['user_id'];
            $report_id = intval($this->input->post('report_id'));
            $fromDate = $this->input->post('from_date');
            $toDate = $this->input->post('to_date');

            // $permission_access = $this->Export_Model->getExportPermissionList($report_id);

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url('exportData/'), 'refresh');
            }
            // else if ($permission_access['status'] == 0) {
            //     $file_name = $this->Export_Model->ReportName($report_id);
            //     $this->session->set_flashdata('err', 'Unauthorized Access: ' . $file_name[0]['m_export_name']);
            //     return redirect(base_url('exportData/'), 'refresh');
            // }
            else {
                $this->session->unset_userdata('err');
                $insertApiLog = array();
                $insertApiLog["eal_export_id"] = $report_id;
                $insertApiLog["eal_start_date"] = !empty($toDate) ? date('Y-m-d', strtotime($fromDate)) : NULL;
                $insertApiLog["eal_end_date"] = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : NULL;
                $insertApiLog["eal_user_id"] = $user_id;
                $insertApiLog["eal_created_on"] = date("Y-m-d H:i:s");
                $insertApiLog["eal_user_platform"] = $this->agent->platform();
                $insertApiLog["eal_user_browser"] = $this->agent->browser() . ' ' . $this->agent->version();
                $insertApiLog["eal_user_agent"] = $this->agent->agent_string();
                $insertApiLog["eal_user_ip"] = $this->input->ip_address();
                $insertApiLog["eal_user_role_id"] = $_SESSION['isUserSession']['user_role_id'];

                $this->db->insert("export_access_logs", $insertApiLog);
                if ($report_id == 1) { //Export Data Lead Duplicate........
                    $this->exportCSVLeadDuplicate($fromDate, $toDate);
                } else if ($report_id == 3) { //Export Data Lead Total........
                    $this->exportCSVLeadTotal($fromDate, $toDate);
                } else if ($report_id == 4) { //Export Data Lead Rejected........
                    $this->exportCSVLeadRejected($fromDate, $toDate);
                } else if ($report_id == 5) { //Export Data Total Sanction........
                    $this->exportCSVTotalSanction($fromDate, $toDate);
                } else if ($report_id == 6) { //Export Data Loan Pre Disburse........
                    $this->exportCSVDashboardData($fromDate, $toDate);
                } else if ($report_id == 7) { //Export Data Loan Disbursed........
                    $this->exportCSVLoanDisbursed($fromDate, $toDate);
                } else if ($report_id == 8) { //Export Data Loan Pending........
                    $this->exportCSVLoanPending($fromDate, $toDate);
                } else if ($report_id == 9) { //Export Data Loan Close..........
                    $this->exportCSVLoanClosed($fromDate, $toDate);
                } else if ($report_id == 10) { //Export Data Pending Recovery........
                    $this->exportCSVPendingRecovery($fromDate, $toDate);
                } else if ($report_id == 11) { //Export Data Collection........
                    $this->exportCSVCollection($fromDate, $toDate);
                } else if ($report_id == 12) { //Export Data Total Recovery........
                    $this->exportCSVTotalRecovery($fromDate, $toDate);
                } else if ($report_id == 13) { //Export Data A/C Reports........
                    $this->exportCSVACReport($fromDate, $toDate);
                } else if ($report_id == 15) { //Export Data Tally........
                    $this->exportCSVCSVTally($fromDate, $toDate);
                } else if ($report_id == 16) { //Export Data Cibil Report........
                    $this->exportCibilReport($fromDate, $toDate);
                } else if ($report_id == 18) { //Export Screener Report........
                    $this->exportCSVLoanDisbursedSendback($fromDate, $toDate);
                } else if ($report_id == 19) { //Export Disbursal Hold Report........
                    $this->exportCSVLoanDisbursedHold($fromDate, $toDate);
                } else if ($report_id == 20) { //Export Screener Report........
                    $this->exportCSVBlackListed($fromDate, $toDate);
                } else if ($report_id == 21) { //Export Screener Report........
                    $this->exportCSVPreCollection($fromDate, $toDate);
                } else if ($report_id == 22) { //Export Screener Report........
                    $this->exportCSVPendingCollectionVerification($fromDate, $toDate);
                } else if ($report_id == 23) { //Export Screener Report........
                    $this->exportCSVLegalData($fromDate, $toDate);
                } else if ($report_id == 24) { //Export Screener Report........
                    $this->exportCSVVisitRequested($fromDate, $toDate);
                } else if ($report_id == 25) { //Export Screener Report........
                    $this->exportCSVVisitCompleted($fromDate, $toDate);
                } else if ($report_id == 26) { //Export Screener Report........
                    $this->exportCSVVisitPending($fromDate, $toDate);
                } else if ($report_id == 27) { //Export Screener Report........
                    $this->exportCSVLoanWaived($fromDate, $toDate);
                } else if ($report_id == 28) { //Export Screener Report........
                    $this->exportCSVPANindiaSumary($fromDate, $toDate);
                } else if ($report_id == 29) { //Export Screener Report........
                    $this->exportCSVOutstandingData($fromDate, $toDate);
                } else if ($report_id == 30) { //Export Screener Report........
                    $this->exportCSVLoanPool($fromDate, $toDate);
                } else if ($report_id == 31) { //Export Screener Report........
                    $this->exportCSVFollowUp($fromDate, $toDate);
                } else if ($report_id == 32) { //Export Screener Report........
                    $this->exportCSVPaymentRejected($fromDate, $toDate);
                } else if ($report_id == 33) { //Export Screener Report........
                    $this->exportCSVBOBLoanDisbursed($fromDate, $toDate);
                } else if ($report_id == 34) { //Export Screener Report........
                    $this->exportCSVBOBTotalRecovery($fromDate, $toDate);
                } else if ($report_id == 35) { //Export Screener Report........
                    $this->exportCSVSuspenseVerified($fromDate, $toDate);
                } else if ($report_id == 36) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVBOBDisbursalPendingSheet($fromDate, $toDate);
                } else if ($report_id == 37) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVNewLoanDisbursed($fromDate, $toDate);
                } else if ($report_id == 38) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVNewCollectionReport($fromDate, $toDate);
                } else if ($report_id == 39) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVLoanDumpReport($fromDate, $toDate);
                } else if ($report_id == 40) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVtotalApprovedSanction($fromDate, $toDate);
                } else if ($report_id == 41) { //Export BOB CMS SHEET for Disbursal
                    $this->exportCSVbrerulesresult($fromDate, $toDate);
                } else if ($report_id == 43) { //Export ICICI Bank CMS Sheet
                    $this->exportPartialLeaddata($fromDate, $toDate);
                }
                // else if ($report_id == 43) { //Export ICICI Bank CMS Sheet
                //     $this->exportCSVICICIBANK($fromDate, $toDate);
                // }
                else if ($report_id == 44) { //Export ICICI Bank CMS Sheet
                    $this->exportCSVLegalNoticeSentLog($fromDate, $toDate);
                } else if ($report_id == 45) { //Export Disbursal Account Report
                    $this->exportCSVMasterDisbursalReport($fromDate, $toDate);
                } else if ($report_id == 46) { //Export Disbursal Account Report
                    $this->exportCSVDisbursalAccountReport($fromDate, $toDate);
                } else if ($report_id == 47) { //TAT REPORT
                    $this->exportCSVAuditTatReport($fromDate, $toDate);
                } else if ($report_id == 48) { //Export Closed Loan
                    $this->exportClosedLoan($fromDate, $toDate);
                } else if ($report_id == 49) { //TAT REPORT
                    $this->exportCSVReloanTatReport($fromDate, $toDate);
                } else if ($report_id == 50) { //TAT REPORT
                    $this->exportCSVLowConversionTatReport($fromDate, $toDate);
                } else if ($report_id == 51) { //TAT REPORT
                    $this->exportCSVHighConversionTatReport($fromDate, $toDate);
                } else if ($report_id == 52) { // Lead Interaction Summary
                    $this->exportCSVLeadInteractionSummaryReport($fromDate, $toDate);
                }
            }
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Invalid Method.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    /////////////////////// Export Data /////////////////////////////


    public function exportCSVLeadTotal($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        if ((round($datediff / 86400) <= 60)) {
            $result = $this->Export_Model->ExportLeadTotal($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }

        // $user_id = $_SESSION['isUserSession']['user_id'];
        // $result = $this->Export_Model->ExportLeadTotal($fromDate, $toDate);

        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {

                $journey_type = "";

                if ($res['lead_is_mobile_verified'] == 1) {
                    $otp = 'YES';
                } else {
                    $otp = 'NO';
                }

                if ($res['lead_journey_type_id'] == 2) {
                    $journey_type = 'App Digital Journey';
                } else if ($res['lead_journey_type_id'] == 3) {
                    $journey_type = 'Web Digital Journey';
                } else if ($res['lead_journey_type_id'] == 4) {
                    $journey_type = 'Convert To Normal';
                } else {
                    $journey_type = 'Normal Journey';
                }

                $docs_upload_str = "NA";
                if ($res['customer_docs_available'] == 1) {
                    $docs_upload_str = "Customer";
                } else if ($res['customer_docs_available'] == 2) {
                    $docs_upload_str = "User";
                }

                $export_data_array[$i] = array(
                    "Lead ID" => $res['lead_id'],
                    "Loan No" => $res['loan_no'],
                    "Customer ID" => $res['customer_id'],
                    "Customer Name" => $res['first_name'],
                    "Mobile" => $res['mobile'],
                    "Religion" => $res['religion_name'],
                    "PanCard" => $res['pancard'],
                    "OTP verification" => $otp,
                    "User Type" => $res['user_type'],
                    "Monthly Income" => $res['monthly_salary_amount'],
                    "Loan Applied" => $res['loan_amount'],
                    "Loan Recommended" => $res['loan_recommended'],
                    "Admin Fee" => $res['admin_fee'],
                    "Tenure" => $res['tenure'],
                    "Interest" => $res['roi'],
                    "Repayment Amount" => $res['repayment_amount'],
                    "Repayment Date" => $res['repayment_date'],
                    "CIBIL" => $res['cibil'],
                    "Obligations" => $res['obligations'],
                    "Journey Type" => $journey_type,
                    "Lead Source" => $res['source'],
                    "UTM Source" => $res['utm_source'],
                    "UTM Medium" => $res['utm_medium'],
                    "UTM Campaign" => $res['utm_campaign'],
                    "UTM Term" => $res['utm_term'],
                    "DOB" => $res['dob'],
                    "Gender" => $res['gender'],
                    "Branch" => $res['branch'],
                    "State" => $res['lead_state_name'],
                    "Current State" => $res['m_state_name'],
                    "City" => $res['m_city_name'],
                    "Status" => $res['status'],
                    "Initiated DateTime" => $res['created_on'],
                    "Docs Uploaded By" => $docs_upload_str,
                    "Screen By" => $res['screenname'],
                    "Screener Assign DateTime" => $res['lead_screener_assign_datetime'],
                    "Screener Recommended DateTime" => $res['lead_screener_recommend_datetime'],
                    "Sanction By" => $res['sanctionby'],
                    "Sanction Assign DateTime" => $res['lead_credit_assign_datetime'],
                    "Sanction Recommended DateTime" => $res['lead_credit_recommend_datetime'],
                    "Sanction Approved By" => $res['sanctionapproveby'],
                    "Sanction Approved DateTime" => $res['lead_credit_approve_datetime'],
                    "CAM SAVE DateTime" => $res['created_at'],
                    "Customer Acceptance DateTime" => $res['agrementResponseDate'],
                    "Disbursal By" => $res['disburseverifiedby'],
                    "Disbursal Assign DateTime" => $res['lead_disbursal_assign_datetime'],
                    "Disbursal Recommended DateTime" => $res['lead_disbursal_recommend_datetime'],
                    "Disbursal Approved By" => $res['finaldisbursed'],
                    "Final Disbursed DateTime" => $res['lead_disbursal_approve_datetime'],
                    "Rejected Reason" => $res['rejected_reason'],
                    "Lead Rejected Assign User Name" => $res['lead_rejected_assign_user_name'],
                    "Lead Rejected Assign Date Time" => $res['lead_rejected_assign_datetime'],
                    "Lead Rejected Assign Counter" => $res['lead_rejected_assign_counter']
                );

                $user_id = $_SESSION['isUserSession']['user_id'];


                $export_data_array[$i]["Mobile"] = $res['mobile'];
                $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                $export_data_array[$i]['Email'] = $res['email'];
                $export_data_array[$i]['Alternate Email'] = $res['alternate_email'];


                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            $this->index();
        }
    }

    public function exportCSVLeadDuplicate($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportLeadDuplicate($fromDate, $toDate);
        if (!empty($result->num_rows())) {

            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'Lead ID' => $res['lead_id'],
                    'Customer ID' => $res['customer_id'],
                    'Customer Name' => $res['first_name'],
                    'Purpose' => $res['purpose'],
                    'User Type' => $res['user_type'],
                    'Monthly Income' => $res['monthly_income'],
                    'CIBIL' => $res['cibil'],
                    'Obligations' => $res['obligations'],
                    'Lead Source' => $res['source'],
                    'Address' => $res['current_house'],
                    'Area' => $res['current_locality'],
                    'Landmark' => $res['current_landmark'],
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Pincode' => $res['cr_residence_pincode'],
                    'Residental Proof' => '',
                    'Residental No' => $res['aadhar_no'],
                    'Residental Type' => $res['current_residence_type'],
                    'Status' => $res['status'],
                    'UTM Source ' => $res['utm_source'],
                    'Initiated Date' => $res['lead_entry_date'],
                    'Updated By' => $res['screener'],
                    'Modified Date' => $res['lead_screener_assign_datetime'],
                    // 'Salary Mode' => '',
                    // 'Reason Of Duplicate' => '',
                    // 'Coupon Code' => '',
                );
            }
            $export_header_array = array_keys($export_data_array[0]);

            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            $this->index();
        }
    }

    public function exportCSVLeadRejected($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        $user_id = $_SESSION['isUserSession']['user_id'];

        if ((round($datediff / 86400) <= 90) || in_array($user_id, array(161, 406, 97))) {
            $result = $this->Export_Model->ExportLeadRejected($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }


        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {
                $lerr_dob_flag = 'NA';
                $lerr_city_flag = 'NA';
                $lerr_state_flag = 'NA';
                $lerr_salary_mode_flag = 'NA';
                $lerr_cust_blacklisted_flag = 'NA';
                $lerr_cust_reject_flag = 'NA';
                $lerr_cust_income_flag = 'NA';
                $lerr_emp_type_flag = 'NA';
                $lerr_cust_duplicate_flag = 'NA';
                $lerr_cust_other_entity_repeat_flag = 'NA';
                $lerr_cust_other_marketing_repeat_flag = 'NA';
                $lerr_pincode_flag = 'NA';
                $lerr_blacklisted_pincode_flag = 'NA';

                if ($res['lerr_dob_flag'] > 0) {
                    $lerr_dob_flag = $res['lerr_dob_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_city_flag'] > 0) {
                    $lerr_city_flag = $res['lerr_city_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_state_flag'] > 0) {
                    $lerr_state_flag = $res['lerr_state_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_cust_blacklisted_flag'] > 0) {
                    $lerr_cust_blacklisted_flag = $res['lerr_cust_blacklisted_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_cust_reject_flag'] > 0) {
                    $lerr_cust_reject_flag = $res['lerr_cust_reject_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_cust_income_flag'] > 0) {
                    $lerr_cust_income_flag = $res['lerr_cust_income_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_emp_type_flag'] > 0) {
                    $lerr_emp_type_flag = $res['lerr_emp_type_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_cust_duplicate_flag'] > 0) {
                    $lerr_cust_duplicate_flag = $res['lerr_cust_duplicate_flag'] == 1 ? 'PASS' : 'FAIL';
                }

                if ($res['lerr_salary_mode_flag'] > 0) {
                    $lerr_salary_mode_flag = $res['lerr_salary_mode_flag'] == 1 ? 'PASS' : 'FAIL';
                }

                if ($res['lerr_cust_other_entity_repeat_flag'] > 0) {
                    $lerr_cust_other_entity_repeat_flag = $res['lerr_cust_other_entity_repeat_flag'] == 1 ? 'PASS' : 'FAIL';
                }

                if ($res['lerr_cust_other_marketing_repeat_flag'] > 0) {
                    $lerr_cust_other_marketing_repeat_flag = $res['lerr_cust_other_marketing_repeat_flag'] == 1 ? 'PASS' : 'FAIL';
                }

                if ($res['lerr_pincode_flag'] > 0) {
                    $lerr_pincode_flag = $res['lerr_pincode_flag'] == 1 ? 'PASS' : 'FAIL';
                }
                if ($res['lerr_blacklisted_pincode_flag'] > 0) {
                    $lerr_blacklisted_pincode_flag = $res['lerr_blacklisted_pincode_flag'] == 1 ? 'PASS' : 'FAIL';
                }


                $export_data_array[$i] = array(
                    "Lead ID" => $res['lead_id'],
                    "Loan No" => $res['loan_no'],
                    "Customer Name" => $res['first_name'],
                    "PanCard" => $res['pancard'],
                    "Mobile" => $res['mobile'],
                    "User Type" => $res['user_type'],
                    "Loan Amount" => $res['loan_recommended'],
                    "Monthly Income" => $res['monthly_income'],
                    "Loan Applied" => $res['loan_amount'],
                    "CIBIL" => $res['cibil'],
                    "Obligations" => $res['obligations'],
                    "Lead Source" => $res['source'],
                    "UTM Source" => $res['utm_source'],
                    "UTM Campaign" => $res['utm_campaign'],
                    "DOB" => $res['dob'],
                    "Gender" => $res['gender'],
                    "State" => $res['m_state_name'],
                    "City" => $res['m_city_name'],
                    "Status" => $res['status'],
                    "Initiated Date" => $res['lead_entry_date'],
                    "Screen By" => $res['sname'],
                    "Screener Process Date" => $res['lead_screener_assign_datetime'],
                    "Sanction By" => $res['cname'],
                    "Modified Date" => $res['updated_at'],
                    "Sanction Process Date" => $res['lead_credit_assign_datetime'],
                    "Rejected Date" => $res['rejecteddate'],
                    "Rejected By" => $res['rejectedby'],
                    "Reason" => $res['remarks'],
                    "DOB Rules" => $lerr_dob_flag,
                    "Pincode Rules" => $lerr_pincode_flag,
                    "City Rules" => $lerr_city_flag,
                    "State Rules" => $lerr_state_flag,
                    "Salary Mode Rules" => $lerr_salary_mode_flag,
                    "Blacklisted Rules" => $lerr_cust_blacklisted_flag,
                    "Pincode Blacklisted Rules" => $lerr_blacklisted_pincode_flag,
                    "Reject Rules" => $lerr_cust_reject_flag,
                    "Income Rules" => $lerr_cust_income_flag,
                    "Emp Type Rules" => $lerr_emp_type_flag,
                    "Cust Duplicate Rules" => $lerr_cust_duplicate_flag,
                    "Other Entity Repeat Rules" => $lerr_cust_other_entity_repeat_flag,
                    "Other Marketing Repeat Rules" => $lerr_cust_other_marketing_repeat_flag,
                );

                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanDisbursed($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        $user_id = $_SESSION['isUserSession']['user_id'];

        if ((round($datediff / 86400) <= 90) || in_array($user_id, array(161, 406, 97))) {
            $result = $this->Export_Model->ExportDisbursed($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }


        if (!empty($result->num_rows())) {
            $export_data_array = array();

            $i = 0;
            foreach ($result->result_array() as $res) {

                $cust_account_no = "'" . $res['account'];

                $minutes = '';
                $hours = '';
                $min = '';
                if (!empty($res['lead_disbursal_approve_datetime']) && !empty($res['created_on'])) {
                    $minutes = (strtotime($res['lead_disbursal_approve_datetime']) - strtotime($res['created_on'])) / 60;
                    $hours = floor($minutes / 60);
                    $min = floor($minutes - ($hours * 60));
                    $tat = $hours . ":" . $min;
                }

                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    // 'Net Disbused Amount' => ($res['loan_recommended'] - $res['admin_fee']),
                    // 'Admin Fee' => $res['admin_fee'],
                    'Net Disbused Amount' => ($res['loan_recommended'] - $res['total_admin_fee']),
                    'Total Processing Fee' => $res['total_admin_fee'], 
                    'Net Processing Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Lead Initiated Date' => $res['created_on'],
                    'Risk Profile' => $res['cam_risk_profile'],
                    'Lead Source' => $res['source'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Sanction Approved By' => $res['sanction_approve_by'],
                    'Loan Initiated By' => $res['loan_initiat_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Loan Disbursed By' => $res['loan_disburse_by'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                    'Disbursal TAT' => $tat,
                    'UTM Source' => $res['utm_source'],
                    'UTM Campaign' => $res['utm_campaign']
                );
                $user_id = $_SESSION['isUserSession']['user_id'];
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                //                if ($user_id == 66 || $user_id == 64) {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVTotalSanction($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportSanction($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $cust_account_no = "'" . $res['account'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Branch Name' => $res['current_city'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Cibil Score' => $res['cibil'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated By' => $res['screenby'],
                    'Loan Initiated Date' => $res['created_on'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVtotalApprovedSanction($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportApprovedSanction($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $cust_account_no = "'" . $res['account'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Branch Name' => $res['current_city'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Cibil Score' => $res['cibil'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Sanction Approved Date' => $res['lead_credit_approve_datetime'],
                    'Loan Initiated By' => $res['screenby'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Status' => $res['status'],
                    'Source' => $res['utm_source'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVDashboardData($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportDashboardDataModel($fromDate, $toDate);
        if (!empty($result)) {
            $result = $result->result_array();
            $export_header_array = array_keys($result[0]);
            $this->dataExport($export_header_array, $result, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanPending($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportDisbursedPending($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $cust_account_no = "'" . $res['account'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Source' => $res['source'],
                    'UTM Source' => $res['utm_source'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Sanctioned By' => $res['sname'],
                    'Approved By' => $res['sanctio_approve_by'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated By' => $res['screenby'],
                    'Loan Initiated Date' => $res['created_on'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanClosed($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        //echo $report_id;die;
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        $user_id = $_SESSION['isUserSession']['user_id'];

        if ((round($datediff / 86400) <= 90) || in_array($user_id, array(161, 406, 97))) {
            $result = $this->Export_Model->ExportLoanClosed($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }



        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $loanNO = $res['loan_no'];
                $export_data_array[] = array(
                    "lead ID" => $res['lead_id'],
                    "State Name" => $res['m_state_name'],
                    "City Name" => $res['m_city_name'],
                    "Loan No" => $loanNO,
                    "Customer Name" => $res['full_name'],
                    "Pancard" => $res['pancard'],
                    "User Type" => $res['user_type'],
                    "Loan Amount" => $res['loan_recommended'],
                    // "Real Interest" => $realinterest,
                    "Loan Repay Amount" => $res['repayment_amount'],
                    "ROI" => $res['roi'],
                    "Tenure" => $res['tenure'],
                    "Loan Disburse Date" => $res['disbursal_date'],
                    "Loan Repay Date" => $res['repayment_date'],
                    "Date Of Received" => $res['date_of_recived'],
                    "Loan Status" => $res['status'],
                    // "Payment Verification" => $res['PaymentVerify'],
                    "Payment Mode" => $res['payment_mode'],
                    "Principal Recieved" => $res['loan_principle_received_amount'],
                    "Interest Received" => $res['loan_interest_received_amount'],
                    "Penal Received" => $res['loan_penalty_received_amount'],
                    "Total Discount" => $res['loan_total_discount_amount'],
                    "Total Received Amount" => $res['loan_total_received_amount'],
                    "Recovery By" => $res['rname'],
                    "Recovery Date" => $res['collection_executive_payment_created_on'],
                    "Verified By" => $res['closure_name'],
                    "Verified Date" => $res['closure_payment_updated_on'],
                    "Company Account Number" => $res['company_account_no'],
                    "Refrence Number" => $res['refrence_no'],
                    "Remark" => $res['remarks'],
                    "NOC" => $res['noc'],
                    "NOC Sent By" => $res['noc_sent_by'],
                    "NOC Sent DateTime" => $res['loan_noc_letter_sent_datetime'],
                    // "Coupon Code" => $res[],
                    // "Email" => $res[],
                );
            }


            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVPendingRecovery($fromDate, $toDate) {
        $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
        $this->index();
    }

    public function exportCSVCollection($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportCollectionReport($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {
                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Branch Name' => $res['m_city_name'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Gender' => $res['gender'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Permanent Address' => $res['Address'],
                    'Company Name' => $res['employer_name'],
                    // 'Address as per Aadhar' => $res['aadharaddress'],
                    'Office Address' => $res['emp_house'],
                    'Pincode' => $res['pincode'],
                    'Residence Type' => $res['current_residence_type'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'DOB' => $res['dob'],
                    'Cibil Score' => $res['cibil'],
                    'Customer Bank Account Number' => $res['account'],
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Lead Initiated DateTime' => $res['lead_created_on'],
                    'Risk Profile' => $res['cam_risk_profile'],
                    'Sanctioned By' => $res['sname'],
                    'Approved By' => $res['sanctio_approve_by'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated By' => $res['screenby'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Loan Disbursed By' => $res['loan_dname'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                    'UTM Source' => $res['utm_source'],
                    'Salary Date' => $res['salary_credit1_date'],
                    'Salary Amount' => $res['salary_credit1_amount'],
                    'Lead Source' => $res['lead_source'],
                    'Emp Designation' => $res['emp_designation'],
                    'Emp Department' => $res['emp_department'],
                    'Emp Sector' => $res['emp_employer_type'],
                    'Emp Website' => $res['emp_website'],
                );

                $user_id = $_SESSION['isUserSession']['user_id'];
                if ($user_id == 184) {
                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                    $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                    $export_data_array[$i]["Email"] = $res['email'];
                    $export_data_array[$i]["Alternate Email"] = $res['alternate_email'];
                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVTotalRecovery($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportTotalRecovery($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'ROI' => $res['roi'],
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $res['repayment_date'],
                    'Received Amount' => $res['received_amount'],
                    'Date Of Received' => $res['date_of_recived'],
                    'Loan Status' => $res['status'],
                    // 'Lead Status' => $res['leadstatus'],
                    'Payment Mode' => $res['payment_mode'],
                    'Discount' => $res['discount'],
                    'Refund' => $res['refund'],
                    'Recovery By' => $res['rname'],
                    'Recovery Date' => $res['collection_executive_payment_created_on'],
                    'Approved By' => $res['closure_name'],
                    'Approved Date' => $res['closure_payment_updated_on'],
                    'Company Account Number' => $res['company_account_no'],
                    'Refrence Number' => $res['refrence_no'],
                    'Collection Remarks' => $res['remarks'],
                    'Closure Remark' => $res['closure_remarks'],
                    'Collection Type' => $res['collection_type'],
                    'NOC' => $res['noc'],
                    'Source' => $res['source'],
                    // 'Case Type' => '',
                    // 'Coupon Code' => '',
                    // 'POS Pending' => '',
                );
            }

            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVCSVTally($fromDate, $toDate) {
        $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
        redirect(base_url('exportData/'), 'refresh');
    }

    function splitTextForCibil($text, $maxLength = 40) {
        $words = explode(' ', $text);
        $resultParts = [];
        $currentPart = '';

        foreach ($words as $word) {
            if (strlen($currentPart) + strlen($word) + 1 > $maxLength) {
                $resultParts[] = trim($currentPart);
                $currentPart = $word;
            } else {
                if (!empty($currentPart)) {
                    $currentPart .= ' ';
                }
                $currentPart .= $word;
            }
        }

        if (!empty($currentPart)) {
            $resultParts[] = trim($currentPart);
        }

        return $resultParts;
    }

    public function exportCibilReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportCibilReport($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {

                $toDate = date('Y-m-d', strtotime($toDate));
                $lead_id = $res['lead_id'];
                $loan_no = $res['loan_no'];
                // print_r($loan_no);
                // exit;
                $result1 = $this->db->select_sum('received_amount')->where("lead_id = '$lead_id' AND date_of_recived <= '$toDate' AND payment_verification = 1 AND collection_active = 1 AND collection_deleted = 0")->from('collection')->get();
                $ReceivedAmount = $result1->row_array();
                $totalReceivedAmount = round($ReceivedAmount['received_amount']);

                $result = $this->db->select('lead_id, date_of_recived, repayment_type, discount, repayment_type')->where("lead_id = '$lead_id' AND date_of_recived <= '$toDate' AND payment_verification = 1 AND collection_active = 1 AND collection_deleted = 0")->from('collection')->order_by('id DESC')->get();
                $last_payment = $result->row_array();

                $repayment_type_id = $last_payment['repayment_type'];
                $loan_amount = round($res['loan_recommended']);
                $roi = $res['roi'];
                $repay_date = strtotime($res['repayment_date']);
                $disbursal_date = strtotime($res['disbursal_date']);
                $tenure = ($repay_date - $disbursal_date) / (60 * 60 * 24);
                $repay_amount = round(($loan_amount * ($roi / 100) * $tenure) + $loan_amount);

                $loan_closed_date = '';
                if ($repayment_type_id == 16 || $repayment_type_id == 17) {
                    $loan_closed_date = !empty($last_payment['date_of_recived']) ? date("dmY", strtotime($last_payment['date_of_recived'])) : "";
                }

                $setted_WO_status_id = '';
                $discount_amt = '';
                $written_off_amount = '';
                $written_off_principle = '';
                if ($repayment_type_id == 17 || $repayment_type_id == 18) {
                    $setted_WO_status_id = '03';
                    $discount_amt = round($last_payment['discount']);
                    $written_off_amount = 0;
                    $written_off_principle = 0;
                }

                $residence_type = "";
                if (strtoupper($res['current_residence_type']) == 'OWNED') {
                    $residence_type = "01";
                } else {
                    $residence_type = "02";
                }


                $aadhar_no = preg_replace("!\s+!", "", $res['aadhar_no']);
                $mobile = preg_replace("!\s+!", "", $res['mobile']);
                $alternate_mobile = preg_replace("!\s+!", "", $res['alternate_mobile']);

                $date1 = strtotime($toDate);
                $date2 = strtotime($res['repayment_date']);
                $dpd = ($date1 - $date2) / (60 * 60 * 24);

                $amt_due = 0;
                if ($dpd > 60 && empty($loan_closed_date) && $repayment_type_id != 17 && $repayment_type_id != 18) {
                    $total_amt_due = round(((60 * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                    $amt_due = $total_amt_due - $totalReceivedAmount;
                } elseif ($dpd <= 60 && $dpd >= 0 && empty($loan_closed_date) && $repayment_type_id != 17 && $repayment_type_id != 18) {
                    $total_amt_due = round((($dpd * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                    $amt_due = $total_amt_due - $totalReceivedAmount;
                }
                if ($amt_due < 0) {
                    $amt_due = 0;
                }

                if ($dpd > 999) {
                    $due_past_date = 999;
                } elseif ($dpd < 0) {
                    $due_past_date = 0;
                } else {
                    $due_past_date = $dpd;
                }

                $Asset_Classification = '';
                if ($due_past_date <= 90) {
                    $Asset_Classification = "01";
                } elseif ($due_past_date >= 91 && $due_past_date <= 180) {
                    $Asset_Classification = "02";
                } elseif ($due_past_date >= 181 && $due_past_date <= 360) {
                    $Asset_Classification = "03";
                } elseif ($due_past_date >= 361) {
                    $Asset_Classification = "04";
                }

                $gender = '';
                if (strtoupper($res['gender']) == "MALE") {
                    $gender = 2;
                } elseif (strtoupper($res['gender']) == "FEMALE") {
                    $gender = 1;
                }

                if ($repayment_type_id == 16 || $repayment_type_id == 17 || $repayment_type_id == 18) {
                    $due_past_date = 0;
                }

                if ($repayment_type_id == 16 || $repayment_type_id == 17) {
                    $repay_amount = 0;
                }

                $arrayAddress = $this->splitTextForCibil($res['Address']);

                $export_data_array[] = array(
                    'Lead ID' => $res['lead_id'],
                    'Consumer Name' => $res['full_name'],
                    'Date of Birth' => date("dmY", strtotime($res['dob'])),
                    'Gender' => $gender,
                    'Income Tax ID Number' => $res['pancard'],
                    'Passport No' => '',
                    'Passport Issue Date' => '',
                    'Passport Expiry Date' => '',
                    'Voter ID' => '',
                    'Driving License' => '',
                    'Driving License Issue Date' => '',
                    'Driving License Expiry Date' => '',
                    'Ration Card Number' => '',
                    'Universal ID Number' => strlen($aadhar_no) == 12 ? $aadhar_no : "",
                    'Additional ID #1' => '',
                    'Additional ID #2' => '',
                    'Mobile No' => $mobile,
                    'Landline No' => '',
                    'Telephone No.Office' => '',
                    'Extension Office' => '',
                    'Telephone No.Other' => strlen($alternate_mobile) != 10 ? "" : $alternate_mobile,
                    'Extension Other' => '',
                    'Email ID1' => $res['email'],
                    'Email ID2' => $res['alternate_email'],
                    'Address1' => $arrayAddress[0],
                    'State Code' => $res['state_id'],
                    'PIN Code' => $res['pincode'],
                    'Address Category1' => $residence_type,
                    'Residence Code1' => $residence_type,
                    'Address2' => '',
                    'State Code2' => '',
                    'PIN Code2' => '',
                    'Address Category2' => '',
                    'Residence Code2' => '',
                    'Current/New Member Code' => 'NB42350001',
                    'Current/New Member Short Name' => 'NAMFINPL',
                    'Curr/New Account No' => $loan_no,
                    'Account Type' => '05',
                    'Ownership Indicator' => '1',
                    'Date Opened/Disbursed' => date("dmY", strtotime($res['disbursal_date'])),
                    'Date of Last Payment' => !empty($last_payment['date_of_recived']) ? date("dmY", strtotime($last_payment['date_of_recived'])) : '',
                    'Date Closed' => $loan_closed_date,
                    'Date Reported' => DATE('dmY', $date1),
                    'High Credit/Sanctioned Amt' => $loan_amount,
                    'Current Balance' => $repay_amount,
                    'Amt Overdue' => $amt_due,
                    'No of Days Past Due' => $due_past_date,
                    'Old Mbr Code' => '',
                    'Old Mbr Short Name' => '',
                    'Old No Type' => '',
                    'Old Acc Type' => '',
                    'Old Ownership Indicator' => '',
                    'Suit Filed / Wilful Default' => '',
                    'Written-off and Settled Status' => $setted_WO_status_id,
                    'Asset Classification' => $Asset_Classification,
                    'Value of Collateral' => '',
                    'Type of Collateral' => '',
                    'Credit Limit' => '',
                    'Cash Limit' => '',
                    'Rate of Interest' => '',
                    'Repayment Tenure' => '',
                    'EMI Amount' => '',
                    'Written- off Amount (Total)' => $written_off_amount,
                    'Written- off Principal Amount' => $written_off_principle,
                    'Settlement Amt' => $discount_amt,
                    'Payment Frequency' => '',
                    'Actual Payment Amt' => '',
                    'Occupation Code' => '',
                    'Income' => '',
                    'Net/Gross Income Indicator' => '',
                    'Monthly/Annual Income Indicator' => '',
                    'received_amount' => $totalReceivedAmount,
                    'Address2' => $arrayAddress[1],
                    'Address3' => $arrayAddress[2],
                    'Address4' => $arrayAddress[3],
                );
                if (agent == "MR" || $user_id == 406) {
                    $export_data_array[$i]["Mobile No"] = 'XXXXXX1234';
                    $export_data_array[$i]["Telephone No.Other"] = 'XXXXXX1234';
                    $export_data_array[$i]["Email ID1"] = 'XXXXX@gmail.com';
                    $export_data_array[$i]["Email ID2"] = 'XXXXX@gmail.com';
                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVACReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportACReport($fromDate, $toDate);
        if (!empty($result->num_rows())) {

            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {

                $cust_account_no = "'" . $res['account'];

                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'State Name' => $res['m_state_name'],
                    'Branch Name' => $res['current_city'],
                    'Customer Id' => $res['customer_id'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Net Disbursed Amount' => $res['recommended_amount'],
                    'Admin Fee' => $res['total_admin_fee'],
                    'Admin Fee GST' => $res['adminFeeWithGST'],
                    'Total Admin Fee' => $res['admin_fee'],
                    'IGST' => number_format($res['IGST'], 2),
                    'CGST' => number_format($res['CGST'], 2),
                    'SGST' => number_format($res['SGST'], 2),
                    'Processing' => number_format($res['processingfee'], 2),
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Sanctioned By' => $res['sname'],
                    'Approved By' => $res['sanctio_approve_by'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Disbursed By' => $res['loan_dname'],
                    'Loan Disbursed Date' => $res['lead_final_disbursed_date'],
                );

                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportScreenerTATReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportScreenerTAT($fromDate, $toDate);
        // print_r($result); exit;
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Screener Name' => $res['screener'],
                    'Screener Date' => $res['lead_screener_assign_datetime'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCreditTATReport($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportCreditTAT($fromDate, $toDate);
        // print_r($result); exit;
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Sanction By' => $res['credit'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportTATReport($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->TAT_model($fromDate, $toDate);
        // print_r($result); exit;
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'lead_id' => $res['lead_id'],
                    'city' => $res['m_city_name'],
                    'state' => $res['m_state_name'],
                    'customer_name' => $res['first_name'],
                    'pancard' => $res['pancard'],
                    'loan_no' => $res['loan_no'],
                    'current_status' => $res['status'],
                    'source' => $res['source'],
                    'user_type' => $res['user_type'],
                    'lead_created' => $res['created_on'],
                    'screener_name' => $res['screener_name'],
                    'screener_assign_datetime' => $res['lead_screener_assign_datetime'],
                    'lead_rejected_by' => $res['rejected_by'],
                    'rejected_datetime' => $res['lead_rejected_datetime'],
                    'rejection_reason' => $res['reason'],
                    'sanction_name' => $res['credit_name'],
                    'credit_assign_datetime' => $res['lead_credit_assign_datetime'],
                    'credit_approve_datetime' => $res['lead_credit_approve_datetime'],
                    'approved_by' => $res['credit_head'],
                    'credithead_assign_datetime' => $res['lead_credithead_assign_datetime'],
                    // 'Collection Executive Name' => $res['collection_name'],
                    // 'collection_executive_upload_datetime' => $res['collection_executive_payment_created_on'],
                    // 'Collection Approve By' => $res['collection_approve'],
                    // 'closure_assign_datetime' => $res['closure_payment_updated_on'],
                    'disburse_assigned' => $res['disburse_assign_name'],
                    'disbursal_assign_datetime' => $res['lead_disbursal_assign_datetime'],
                    'disburse_approve_by' => $res['disburse_approve_name'],
                    'disbursal_approve_datetime' => $res['lead_disbursal_approve_datetime'],
                    'final_disbursed_date' => $res['lead_final_disbursed_date'],
                    'loan_amount' => $res['loan_amount'],
                    'repayment_amount' => $res['repayment_amount'],
                    'disbursal_date' => $res['disbursal_date'],
                    'repayment_date' => $res['repayment_date'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanDisbursedSendback($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportDisbursedSendback($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $cust_account_no = "'" . $res['account'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Approved By' => $res['sanctio_approve_by'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated By' => $res['loan_initial_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Remarks' => $res['send_back_remark'],
                    // 'Loan Disbursed DateTime' => $res['lead_disbursal_approve_datetime'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanDisbursedHold($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportDisbursedHold($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            foreach ($result->result_array() as $res) {
                $cust_account_no = "'" . $res['account'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Lead Initiated Date' => $res['lead_entry_date'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Approved By' => $res['sanctio_approve_by'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated By' => $res['loan_initial_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVBlackListed($fromDate, $toDate) {
        $master_array = array(1 => "LW", 2 => "FM", 3 => "LT", 4 => "AS");
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportBlackListed($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'Lead Id' => $res['bl_lead_id'],
                    'Source' => $master_array[$res['bl_source_entity']],
                    'Loan No' => $res['bl_loan_no'],
                    'Customer Name' => $res['full_name'],
                    'DOB' => date("d-m-Y", strtotime($res['bl_customer_dob'])),
                    'PAN' => $res['bl_customer_pancard'],
                    'Email' => $res['bl_customer_email'],
                    'Alternate Email' => $res['bl_customer_alternate_email'],
                    'Rejection Reason' => $res['m_br_name'],
                    'Remarks' => $res['bl_reason_remark'],
                    'Black Listed By' => $res['user_name_added_by'],
                    'Created On' => date("d-m-Y H:i:s", strtotime($res['bl_created_on'])),
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['bl_customer_mobile'];
                //                    $export_data_array[$i]["Alternate Mobile"] = $res['bl_customer_alternate_mobile'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVPendingCollectionVerification($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportPendingCollectionverification($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Branch' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'ROI' => $res['roi'],
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $res['repayment_date'],
                    'Received Amount' => $res['received_amount'],
                    'Date Of Received' => $res['date_of_recived'],
                    'Request Collection Status' => $res['status'],
                    'Discount' => $res['discount'],
                    'Payment Mode' => $res['payment_mode'],
                    'Recovery By' => $res['rname'],
                    'Recovery Date' => $res['collection_executive_payment_created_on'],
                    'Company Account Number' => $res['company_account_no'],
                    'Refrence Number' => $res['refrence_no'],
                    'Collection Remarks' => $res['remarks'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVPreCollection($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportPreCollection($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            $i = 0;
            $user_id = $_SESSION['isUserSession']['user_id'];
            foreach ($result->result_array() as $res) {

                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Status' => $res['status'],
                    'Loan No' => $res['loan_no'],
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Customer Name' => $res['cust_full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $res['repayment_date'],
                    'Sanction By' => $res['credit_manager_name'],
                    'Source' => $res['source'],
                    'Upto 10 DPD Executive' => $res['pre_user_name1'],
                    'Upto 10 DPD Assign Datetime' => $res['lead_pre_collection_executive_assign_datetime1'],
                    'DPD 11 to 40 DPD Executive' => $res['pre_user_name2'],
                    'DPD 11 to 40 DPD Assign Datetime' => $res['lead_pre_collection_executive_assign_datetime2'],
                    'DPD 41 to 60 DPD Executive' => $res['coll_user_name1'],
                    'DPD 41 to 60 DPD Assign Datetime' => $res['lead_collection_executive_assign_datetime1'],
                    'DPD 61 Above DPD' => $res['coll_user_name2'],
                    'DPD 61 Above DPD Assign Datetime' => $res['lead_collection_executive_assign_datetime2'],
                );
                if (in_array($user_id, array(47, 66, 406, 161))) {
                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                    $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                    $export_data_array[$i]['Email'] = $res['email'];
                    $export_data_array[$i]['Alternate Email'] = $res['alternate_email'];
                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLegalData($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportLegalData($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $loan_no = $res['loan_no'];
                $query = $this->db->select_sum('received_amount')->from('collection')->where("loan_no = '$loan_no' AND collection_active = 1 AND payment_verification=1")->get();
                $collected_amount = $query->row_array();

                $current_date = strtotime(date('d-m-Y'));
                $repay_date = strtotime($res['repayment_date']);
                $dpd = ($current_date - $repay_date) / (60 * 60 * 24);
                $loan_amount = $res['loan_recommended'];
                $roi = $res['roi'];
                $repay_amount = $res['repayment_amount'];

                $late_panality = 0;
                if ($dpd > 60) {
                    $late_panality = round(((60 * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                } elseif ($dpd <= 60) {
                    $late_panality = round((($dpd * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                }

                //                $late_panality = ($loan_amount * ($roi * 2) / 100) * $dpd;
                $total_due = $late_panality + $res['repayment_amount'];

                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Status' => $res['status_name'],
                    'Branch Name' => $res['m_branch_name'],
                    'Loan No' => $loan_no,
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $loan_amount,
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $roi,
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Current Address' => $res['Address'],
                    'Current Pincode' => $res['pincode'],
                    'Aadhar Address' => $res['aadharaddress'],
                    'Aadhar Pincode' => $res['aa_cr_residence_pincode'],
                    'Sanction By' => $res['name'],
                    'Repeat Type' => $res['user_type'],
                    'DPD' => $dpd,
                    'Late Panality' => $late_panality,
                    'Total Due' => $total_due,
                    'Part Payment' => $collected_amount['received_amount'] ? $collected_amount['received_amount'] : 0,
                    'Final Amount' => $total_due - $collected_amount['received_amount'],
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVVisitRequested($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportVisitRequet($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $type = $res['col_visit_address_type'];

                if ($type == 1) {
                    $address_type = 'Residence';
                    $address = $res['residence_address'];
                } elseif ($type == 2) {
                    $address_type = 'Office';
                    $address = $res['office_address'];
                }

                $export_data_array[] = array(
                    'lead ID' => $res['col_lead_id'],
                    'Branch Name' => $res['m_branch_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Repayment Date' => $res['repayment_date'],
                    'Requested By' => $res['coll_name'],
                    'Request Remarks' => $res['col_visit_requested_by_remarks'],
                    'Requested DateTime' => $res['col_visit_requested_datetime'],
                    'SCM Name' => $res['scm_name'],
                    'SCM Remarks' => $res['col_visit_requested_by_remarks'],
                    'Address Type' => $address_type,
                    'Address' => $address,
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Pincode' => $res['pincode'],
                    'Requested DateTime' => $res['col_visit_requested_datetime'],
                    'Status' => 'Visit-Requested'
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVVisitCompleted($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportVisitCompleted($fromDate, $toDate);
        if (!empty($result->num_rows())) {

            foreach ($result->result_array() as $res) {

                $type = $res['col_visit_address_type'];
                $lead_id = $res['col_lead_id'];
                $address_type = "";
                $address = "";
                $approved = "";
                $visit_type = "";

                $city = "";
                $state = "";
                $pincode = "";

                if ($type == 1) {
                    $address_type = 'Residence';
                    $address = $res['residence_address'];
                    $city = $res['m_city_name'];
                    $state = $res['m_state_name'];
                    $pincode = $res['pincode'];
                } elseif ($type == 2) {
                    $address_type = 'Office';
                    $address = $res['office_address'];
                    $city = $res['off_citys'];
                    $state = $res['off_states'];
                    $pincode = $res['emp_pincode'];
                }


                if ($res['col_fe_visit_approval_status'] == 1) {
                    $approved = 'Approved';
                } else
                if ($res['col_fe_visit_approval_status'] == 2) {
                    $approved = 'Rejected';
                }

                if (!empty($res['col_fe_rtoh_return_type']) && $res['col_fe_rtoh_return_type'] == 1) {
                    $visit_type = 'Return To Home';
                } else if (!empty($res['col_fe_rtoh_return_type']) && $res['col_fe_rtoh_return_type'] == 2) {
                    $visit_type = 'Return To Office';
                }

                $export_data_array[] = array(
                    'Lead ID' => $lead_id,
                    'Branch Name' => $res['m_branch_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'User Type' => $res['user_type'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Repayment Date' => $res['repayment_date'],
                    'Requested By' => $res['coll_name'],
                    'Request Remarks' => $res['col_visit_requested_by_remarks'],
                    'Requested DateTime' => $res['col_visit_requested_datetime'],
                    'SCM Name' => $res['scm_name'],
                    'SCM Remarks' => $res['col_visit_scm_remarks'],
                    'Allocated DateTime' => $res['col_visit_allocate_on'],
                    'Allocated To FE Name' => $res['rm_name'],
                    // 'col_visit_allocated_to' => $res['col_visit_allocated_to'],
                    'FE Remarks' => $res['col_visit_field_remarks'],
                    'Address Type' => $address_type,
                    'Address' => $address,
                    'City' => $city,
                    'State' => $state,
                    'Pincode' => $pincode,
                    'Visit Start DateTime' => $res['col_fe_visit_trip_start_datetime'],
                    'Visit Start Latitude' => $res['col_fe_visit_trip_start_latitude'],
                    'Visit Start Longitude' => $res['col_fe_visit_trip_start_longitude'],
                    'Visit Stop DateTime' => $res['col_fe_visit_trip_stop_datetime'],
                    'Visit End DateTime' => $res['col_fe_visit_end_datetime'],
                    'Visit End Latitude' => $res['col_fe_visit_end_latitude'],
                    'Visit End Longitude' => $res['col_fe_visit_end_longitude'],
                    'RTH DateTime' => $res['col_fe_rtoh_return_datetime'],
                    'RTH Latitude' => $res['col_fe_rtoh_end_latitude'],
                    'RTH Longitude' => $res['col_fe_rtoh_end_longitude'],
                    'Visit Distance' => $res['col_fe_visit_total_distance_covered'],
                    'Status' => 'Visit-Completed',
                    'RTH Type' => $visit_type,
                    'RTH Distance' => $res['col_fe_rtoh_total_distance_covered'],
                    'Approval Status' => $approved,
                    'Approved By' => $res['approved_by'],
                    'Approval DateTime' => $res['col_fe_visit_approval_datetime'],
                    // 'Verified Collection' => 0
                );
                // if (agent == "CA") {
                //     $export_data_array[$i]["Mobile"] = $res['mobile'];
                // }
                // $i++;
            }

            ////////////////

            if (agent == "CA") {
                $fromDate = date('Y-m-d', strtotime($fromDate));
                $toDate = date('Y-m-d', strtotime($toDate));
                $q1 = "SELECT COL.lead_id, COL.received_amount, COL.collection_executive_user_id,COL.collection_executive_payment_created_on FROM collection COL WHERE COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived >= '$fromDate' AND COL.date_of_recived <= '$toDate' AND COL.collection_executive_user_id>0";

                $collection_result = $this->db->query($q1)->result_array();

                // $report_array = array();
                foreach ($export_data_array as $exportKey => $exportData) {

                    foreach ($collection_result as $row) {
                        if ($exportData['Lead ID'] == $row['lead_id'] && $exportData['col_visit_allocated_to'] == $row['collection_executive_user_id'] && $exportData['Visit Stop DateTime'] >= $row['collection_executive_payment_created_on'] && $row['collection_executive_payment_created_on'] <= $exportData['Visit End DateTime']) {
                            $export_data_array[$exportKey]['Verified Collection'] = $row['received_amount'];
                            break;
                        }
                    }
                }
            }
            // echo "<pre>";
            // print_r($export_data_array);
            // exit;
            ///////

            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVVisitPending($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportVisitPending($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $type = $res['col_visit_address_type'];

                if ($type == 1) {
                    $address_type = 'Residence';
                    $address = $res['residence_address'];
                } elseif ($type == 2) {
                    $address_type = 'Office';
                    $address = $res['office_address'];
                }

                $export_data_array[] = array(
                    'lead ID' => $res['col_lead_id'],
                    'Branch Name' => $res['m_branch_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Repayment Date' => $res['repayment_date'],
                    'Requested By' => $res['coll_name'],
                    'Request Remarks' => $res['col_visit_requested_by_remarks'],
                    'Requested DateTime' => $res['col_visit_requested_datetime'],
                    'SCM Name' => $res['scm_name'],
                    'SCM Remarks' => $res['col_visit_scm_remarks'],
                    'Allocated DateTime' => $res['col_visit_allocate_on'],
                    'Allocated To FE Name' => $res['rm_name'],
                    'FE Remarks' => $res['col_visit_field_remarks'],
                    'Address Type' => $address_type,
                    'Address' => $address,
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Pincode' => $res['pincode'],
                    'Visit Start DateTime' => $res['col_fe_visit_trip_start_datetime'],
                    'Visit Stop DateTime' => $res['col_fe_visit_trip_stop_datetime'],
                    'Visit End DateTime' => $res['col_fe_visit_end_datetime'],
                    'Distance' => $res['col_fe_visit_total_distance_covered'],
                    'Collected Amount' => $res['col_fe_visit_total_amount_received'],
                    'Status' => 'Vist-Pending'
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanWaived($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        $user_id = $_SESSION['isUserSession']['user_id'];

        if ((round($datediff / 86400) <= 90) || in_array($user_id, array(161, 406, 97))) {
            $result = $this->Export_Model->ExportLoanWaived($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }


        if (!empty($result->num_rows())) {
            $export_data_array = array();

            $i = 0;
            foreach ($result->result_array() as $res) {

                $cust_account_no = "'" . $res['account'];

                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Net Disbused Amount' => ($res['loan_recommended'] - $res['admin_fee']),
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Lead Source' => $res['source'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Sanction Approved By' => $res['sanction_approve_by'],
                    'Loan Initiated By' => $res['loan_initiat_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Loan Disbursed By' => $res['loan_disburse_by'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVPANindiaSumary($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportPanindiasummary($fromDate, $toDate);
        if (!empty($result->num_rows())) {

            foreach ($result->result_array() as $res) {

                $loan_no = $res['loan_no'];
                // $loan_no = 'LWUAT00000001612';
                $repay_date = $res['repayment_date'];
                $loan_amount = $res['loan_recommended'];
                $roi = $res['roi'];
                $tenure = $res['tenure'];
                $repay_amount = $res['repayment_amount'];
                $current_date = strtotime($toDate);
                $status = $res['status'];
                $dpd = 0;

                $dpd = ($current_date - strtotime($repay_date)) / (60 * 60 * 24);

                $q = "SELECT received_amount, date_of_recived FROM collection WHERE loan_no='$loan_no' AND payment_verification = 1 AND collection_active=1 AND collection_deleted=0 AND date_of_recived <= '$repay_date' ORDER BY id DESC";
                $q1 = "SELECT received_amount, date_of_recived FROM collection WHERE loan_no='$loan_no' AND payment_verification = 1 AND collection_active=1 AND collection_deleted=0 AND date_of_recived > '$repay_date' ORDER BY id DESC";

                $result = $this->db->query($q)->result_array();
                $result1 = $this->db->query($q1)->result_array();

                $pre_collection_amount = 0;
                $crm_collection_amount = 0;
                $active_pos = 0;

                foreach ($result as $pre) {
                    $pre_collection_amount += $pre['received_amount'];
                }
                foreach ($result1 as $crm) {
                    $crm_collection_amount += $crm['received_amount'];
                }

                $total_amount = $pre_collection_amount + $crm_collection_amount;
                if ($dpd > 0) {
                    $active_pos = $loan_amount - $total_amount;
                }

                $loan_close_date = '';
                if ($status == 'CLOSED' || $status == 'SETTLED' || $status == 'WRITEOFF') {
                    $amt_due = 0;
                    $dpd = 0;
                    $loan_close_date = !empty($result1[0]['date_of_recived']) ? $result1[0]['date_of_recived'] : $result[0]['date_of_recived'];
                } else {
                    if ($dpd > 60) {
                        $total_amt_due = round(((60 * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                        $amt_due = $total_amt_due - $total_amount;
                    } elseif ($dpd > 0 && $dpd <= 60) {
                        $total_amt_due = round((($dpd * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                        $amt_due = $total_amt_due - $total_amount;
                    }
                }
                if ($dpd < 0) {
                    $dpd = 0;
                }

                if ($res['loan_noc_letter_sent_status'] == 1) {
                    $noc = 'YES';
                } else {
                    $noc = 'NO';
                }

                if ($dpd > 0 && $dpd <= 30) {
                    $bucket = '1-30 DPD';
                } elseif ($dpd > 30 && $dpd <= 60) {
                    $bucket = '30-60 DPD';
                } elseif ($dpd > 60 && $dpd <= 90) {
                    $bucket = '60-90 DPD';
                } elseif ($dpd > 90 && $dpd <= 120) {
                    $bucket = '90-120 DPD';
                } elseif ($dpd > 120 && $dpd <= 150) {
                    $bucket = '120-150 DPD';
                } elseif ($dpd > 150 && $dpd <= 180) {
                    $bucket = '150-180 DPD';
                } elseif ($dpd > 180) {
                    $bucket = '180+ DPD';
                } elseif ($dpd < 0) {
                    $bucket = 'Pre-Collection';
                }


                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Branch' => $res['branch'],
                    'State' => $res['state'],
                    'City' => $res['m_city_name'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $loan_no,
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $tenure,
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $repay_date,
                    'Bucket' => $bucket,
                    'DPD' => $dpd,
                    'Last Payment Date' => !empty($result1[0]['date_of_recived']) ? $result1[0]['date_of_recived'] : $result[0]['date_of_recived'],
                    'Loan Close Date' => $loan_close_date,
                    'Pre-Collection Amount' => !empty($pre_collection_amount) ? $pre_collection_amount : 0,
                    '(+1Day)-Collection Amount' => !empty($crm_collection_amount) ? $crm_collection_amount : 0,
                    'Total Collection' => !empty($total_amount) ? $total_amount : 0,
                    'Active POS' => $active_pos > 0 ? $active_pos : 0,
                    'Outstanding Amount' => $amt_due > 0 ? $amt_due : 0,
                    'Permanent Address' => $res['Address'],
                    'Address as per Aadhar' => $res['aadharaddress'],
                    'Pincode' => $res['pincode'],
                    'Residence Type' => $res['current_residence_type'],
                    'Cibil Score' => $res['cibil'],
                    'Cust. Type' => $res['user_type'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Approved By' => $res['sanctionproveby'],
                    'UTM Source' => $res['utm_source'],
                    'Salary Date' => $res['salary_credit1_date'],
                    'Salary Amount' => $res['salary_credit1_amount'],
                    'Loan Status' => $res['status'],
                    'NOC Status' => $noc,
                    'NOC Sent DateTime' => $res['loan_noc_letter_sent_datetime'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVOutstandingData($fromDate, $toDate) {
        $user_id = $_SESSION['isUserSession']['user_id'];
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportOutstandingData($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $loan_no = $res['loan_no'];
                $repay_date = $res['repayment_date'];
                $loan_amount = $res['loan_recommended'];
                $roi = $res['roi'];
                $tenure = $res['tenure'];
                $repay_amount = $res['repayment_amount'];

                $current_date = strtotime(date("Y-m-d"));

                $dpd = ($current_date - strtotime($repay_date)) / (60 * 60 * 24);

                if (empty($dpd) || $dpd < 0) {
                    $dpd = 0;
                }

                $bucket = '';
                if ($dpd > 0 && $dpd <= 30) {
                    $bucket = '1-30 DPD';
                } elseif ($dpd > 30 && $dpd <= 60) {
                    $bucket = '31-60 DPD';
                } elseif ($dpd > 60 && $dpd <= 90) {
                    $bucket = '61-90 DPD';
                } elseif ($dpd > 90 && $dpd <= 120) {
                    $bucket = '91-120 DPD';
                } elseif ($dpd > 120 && $dpd <= 150) {
                    $bucket = '121-150 DPD';
                } elseif ($dpd > 150 && $dpd <= 180) {
                    $bucket = '151-180 DPD';
                } elseif ($dpd > 180) {
                    $bucket = '180+ DPD';
                } else {
                    $bucket = 'Pre-Collection';
                }


                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Loan No' => $loan_no,
                    'Branch' => $res['m_branch_name'],
                    'Customer Name' => $res['cust_full_name'],
                    'Gender' => $res['gender'],
                    'Loan Amount' => $loan_amount,
                    'ROI' => $roi,
                    'Tenure' => $tenure,
                    'Loan Repay Amount' => $repay_amount,
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $repay_date,
                    'Current Address' => $res['current_address'],
                    'Current City' => $res['m_city_name'],
                    'Current State' => $res['m_state_name'],
                    'Current Pincode' => $res['pincode'],
                    'Office Address' => $res['office_address'],
                    'Office City' => $res['emp_city'],
                    'office State' => $res['emp_state'],
                    'Office Pincode' => $res['office_pincode'],
                    'Sanction By' => $res['credit_manager_name'],
                    'Bucket' => $bucket,
                    'DPD' => $dpd,
                    'Total Collection' => $res['loan_total_received_amount'],
                    'Active POS' => $res['loan_principle_outstanding_amount'],
                    'Outstanding Amount' => $res['loan_total_outstanding_amount'],
                    'Source' => $res['source'],
                    'User Type' => $res['user_type'],
                    'Monthly Income' => $res['monthly_income'],
                    'Status' => $res['status'],
                );

                if ($user_id == 184 || $user_id == 406 || $user_id == 66) {
                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                    $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                    $export_data_array[$i]["Email"] = $res['email'];
                    $export_data_array[$i]["Alternate Email"] = $res['alternate_email'];
                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanPool($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportLoanPool($fromDate, $toDate);

        if (!empty($result->num_rows())) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $loan_no = $res['loan_no'];
                $repay_date = $res['repayment_date'];

                $date1 = strtotime($toDate);
                $date2 = strtotime($res['repayment_date']);

                if (!empty($res['last_payment_date'])) {
                    $date2 = $repay_date > $res['last_payment_date'] ? strtotime($res['last_payment_date']) : strtotime($repay_date);
                    if ($repay_date < $res['last_payment_date']) {
                        $date1 = $res['last_payment_date'];
                    }
                }

                $duepastdate = ($date1 - $date2) / (60 * 60 * 24);

                $dpd = 0;
                if ($duepastdate > 0) {
                    $dpd = $duepastdate;
                }

                $tenure = ($date2 - strtotime($res['disbursal_date'])) / (60 * 60 * 24);
                $loan_amount = $res['sanction_loan_amount'];
                $roi = $res['sanction_roi'];
                $panel_roi = $roi * 2;
                $repay_amount = ($loan_amount * ($roi / 100) * $tenure) + $loan_amount;
                $interest_amount = $repay_amount - $loan_amount;
                $panel_amount = 0;
                if ($dpd > 0 && $dpd <= 60) {
                    $panel_amount = ($loan_amount * ($panel_roi / 100) * $dpd) <= 0 ? 0 : ($loan_amount * ($panel_roi / 100) * $dpd);
                } elseif ($dpd > 60) {
                    $panel_amount = ($loan_amount * ($panel_roi / 100) * 60) <= 0 ? 0 : ($loan_amount * ($panel_roi / 100) * 60);
                }

                $total_received_amount = $res['collection_amount_as_on_to_date'];

                $active_ios = 0;
                $active_pos = 0;
                $active_panelos = 0;
                $collection_interest = 0;
                $collection_principal = 0;
                $collection_panelint = 0;

                if ($total_received_amount > 0) {
                    if ($total_received_amount >= $interest_amount) {
                        $collection_interest = $interest_amount;
                    } else {
                        if (($interest_amount - $total_received_amount) <= 0) {
                            $collection_interest = 0;
                        } else {
                            $collection_interest = $interest_amount - $total_received_amount;
                        }
                    }


                    if (($total_received_amount - $interest_amount) >= $loan_amount) {
                        $collection_principal = $loan_amount;
                    } else {
                        if (($loan_amount - ($total_received_amount - $interest_amount)) <= 0 && ($total_received_amount - $interest_amount) >= 0) {
                            $collection_principal = 0;
                        } else {
                            $collection_principal = $loan_amount - ($loan_amount - ($total_received_amount - $interest_amount));
                        }
                    }


                    if (($total_received_amount - $interest_amount - $loan_amount) >= $panel_amount) {
                        $collection_panelint = $panel_amount;
                    } else {
                        if (($panel_amount - ($total_received_amount - $interest_amount - $loan_amount)) <= 0 && ($total_received_amount - $interest_amount - $loan_amount) >= 0) {
                            $collection_panelint = 0;
                        } else {
                            $collection_panelint = $panel_amount - ($panel_amount - ($total_received_amount - $interest_amount - $loan_amount)) <= 0 ? 0 : $panel_amount - ($panel_amount - ($total_received_amount - $interest_amount - $loan_amount));
                        }
                    }
                }


                if ($dpd > 0 && ($res['lead_status_id'] == 14 || $res['lead_status_id'] == 19)) {

                    if ($total_received_amount >= $interest_amount) {
                        $active_ios = 0;
                    } else {
                        $active_ios = ($interest_amount - $total_received_amount) <= 0 ? 0 : $interest_amount - $total_received_amount;
                    }
                    if (($total_received_amount - $interest_amount) >= $loan_amount) {
                        $active_pos = 0;
                    } else {
                        if (($total_received_amount - $interest_amount) <= 0) {
                            $active_pos = $loan_amount;
                        } else {
                            $active_pos = ($loan_amount - ($total_received_amount - $interest_amount)) <= 0 ? 0 : $loan_amount - ($total_received_amount - $interest_amount);
                        }
                    }
                    if (($total_received_amount - $interest_amount - $loan_amount) >= $panel_amount) {
                        $active_panelos = 0;
                    } else {
                        if (($total_received_amount - $interest_amount - $loan_amount) <= 0) {
                            $active_panelos = $panel_amount;
                        } else {

                            $active_panelos = $panel_amount - ($total_received_amount - $interest_amount - $loan_amount) <= 0 ? 0 : $panel_amount - ($total_received_amount - $interest_amount - $loan_amount);
                        }
                    }
                }

                //                        if ($dpd > 60) {
                //                            $total_amt_due = round(((60 * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                //                            $amt_due = $total_amt_due - $total_received_amount;
                //                        } elseif ($dpd > 0 && $dpd <= 60) {
                //                            $total_amt_due = round((($dpd * $loan_amount * ($roi * 2)) / 100) + $repay_amount);
                //                            $amt_due = $total_amt_due - $total_received_amount;
                //                        }
                //                        if ($amt_due < 0) {
                //                            $amt_due = 0;
                //                        }

                if ($dpd > 0 && $dpd <= 30) {
                    $bucket = '1-30 DPD';
                } elseif ($dpd > 30 && $dpd <= 60) {
                    $bucket = '31-60 DPD';
                } elseif ($dpd > 60 && $dpd <= 90) {
                    $bucket = '61-90 DPD';
                } elseif ($dpd > 90 && $dpd <= 120) {
                    $bucket = '91-120 DPD';
                } elseif ($dpd > 120 && $dpd <= 150) {
                    $bucket = '121-150 DPD';
                } elseif ($dpd > 150 && $dpd <= 180) {
                    $bucket = '151-180 DPD';
                } elseif ($dpd > 180) {
                    $bucket = '180+ DPD';
                } elseif ($dpd <= 0) {
                    $bucket = 'Pre-Collection';
                }


                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Lead Entry Date' => $res['created_on'],
                    'Lead Entry DateTime' => $res['lead_entry_date'],
                    'Source' => $res['source'],
                    'Source' => $res['source'],
                    'Status' => $res['status'],
                    'Loan No' => $loan_no,
                    'User Type' => $res['user_type'],
                    'Customer Name' => $res['customer_name'],
                    'Pancard' => $res['pancard'],
                    'Loan Amount' => $loan_amount,
                    'ROI' => $roi,
                    'Panel ROI' => $panel_roi,
                    'Tenure' => $tenure,
                    'Loan Repay Amount' => $repay_amount,
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $repay_date,
                    'Interest' => $interest_amount,
                    'Penal Interest' => $panel_amount,
                    'Bucket' => $bucket,
                    'DPD' => $dpd,
                    "Collection 31 Mar'22" => $res['coll_amt_as_on_31march2022'],
                    'Total Collection' => !empty($total_received_amount) ? $total_received_amount : 0,
                    'Last Payment' => $res['last_payment_date'],
                    'Collection IOS' => $collection_interest,
                    'Collection POS' => $collection_principal,
                    'Collection Panel OS' => $collection_panelint,
                    'Active IOS' => $active_ios,
                    'Active POS' => $active_pos,
                    'Active Panel OS' => $active_panelos,
                );
                //                        if (agent == "CA") {
                //                            $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                            $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                //                            $export_data_array[$i]["Email"] = $res['email'];
                //                            $export_data_array[$i]["Alternate Email"] = $res['alternate_email'];
                //                        }
                //                        $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVFollowUp($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportFollowUp($fromDate, $toDate);

        if (!empty($result->num_rows())) {

            foreach ($result->result_array() as $res) {

                $followdate = date('Y-m-d', strtotime($res['lcf_created_on']));
                $repaydate = $res['repayment_date'];

                if ($repaydate >= $followdate) {
                    $bucket = 'Pre-Collection';
                } elseif (date('Y-m-d', strtotime('+10 Days', strtotime($repaydate))) >= $followdate) {
                    $bucket = 'Collection';
                } else {
                    $bucket = 'Recovery';
                }


                $export_data_array[] = array(
                    'Lead Id' => $res['lcf_lead_id'],
                    'FollowUp Date' => $followdate,
                    'FollowUp By' => $res['name'],
                    'Loan No' => $res['loan_no'],
                    'FollowUp Type' => $res['m_followup_type_name'],
                    'FollowUp Status' => $res['m_followup_status_name'],
                    'Remarks' => $res['lcf_remarks'],
                    'Repay Date' => $repaydate,
                    'Month-Year' => date_format(date_create($repaydate), "M-y"),
                    'Next FollowUp Date' => $res['lcf_next_schedule_datetime'],
                    'Bucket' => $bucket,
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVPaymentRejected($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportRejectedPayments($fromDate, $toDate);

        if (!empty($result->num_rows())) {
            $i = 0;
            foreach ($result->result_array() as $res) {

                $ref_no = "'" . $res['refrence_no'];

                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Loan Status' => $res['loan_status'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Uploaded Executive Name' => $res['executive_name'],
                    'Uploaded Executive DateTime' => $res['uploaded_date'],
                    'Uploaded Executive Status' => $res['executive_status'],
                    'Closure Name' => $res['Closure_name'],
                    'Uploaded Updated DateTime' => $res['Closur_updated_date'],
                    'Amount' => $res['received_amount'],
                    'Reference Number' => $ref_no,
                    'Payment Mode' => $res['payment_mode'],
                    'Executive Remarks' => $res['Executive_remarks'],
                    'Closure Remarks' => $res['Closure_remarks'],
                );

                //                if (agent == 'CA') {
                //                    $export_data_array[$i]['Mobile'] = $res['mobile'];
                //                    $export_data_array[$i]['Email'] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVBOBLoanDisbursed($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportBOBDisbursed($fromDate, $toDate);

        if (!empty($result->num_rows())) {
            $export_data_array = array();

            $i = 0;

            foreach ($result->result_array() as $res) {

                $cust_account_no = "'" . $res['account'];
                $total_collection = 0;
                $actual_int = 0;

                if (!empty($res['collection_amount'])) {
                    $total_collection = $res['collection_amount'];
                    $actual_int = $total_collection - $res['loan_recommended'];
                }


                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'User Type' => $res['user_type'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Net Disbused Amount' => ($res['loan_recommended'] - $res['admin_fee']),
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Interest Received Amount' => $res['loan_interest_received_amount'],
                    'Panelty Received Amount' => $res['loan_penalty_received_amount'],
                    'Total Interest Received' => ($actual_int > 0 ? $actual_int : 0),
                    'Panelty Discount Amount' => $res['loan_penalty_discount_amount'],
                    'Panelty Outstanding Amount' => $res['loan_penalty_outstanding_amount'],
                    'Total Collection Amount' => $res['collection_amount'],
                    'Principal Outstanding Amount' => $res['loan_principle_outstanding_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'Company Bank Name' => 'Bank of Baroda',
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Currecnt Status' => $res['current_status'],
                    'Screen By' => $res['screenby'],
                    'Screen Date' => $res['lead_screener_assign_datetime'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Lead Source' => $res['source'],
                    'Sanction Approved By' => $res['sanction_approve_by'],
                    'Sanction Approved Date' => $res['lead_credit_approve_datetime'],
                    'Loan Initiated By' => $res['loan_initiat_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Loan Disbursed By' => $res['loan_disburse_by'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                    'Total Collection Call' => $res['total_calls'],
                    'Total Collection Visit' => $res['total_visits'],
                    'Last Collection Call Status' => $res['call_status'],
                    'Last Collection Remark' => $res['call_remarks'],
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVBOBTotalRecovery($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportBOBTotalRecovery($fromDate, $toDate);
        if ($result->num_rows() > 0) {
            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'ROI' => $res['roi'],
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $res['repayment_date'],
                    'Received Amount' => $res['received_amount'],
                    'Date Of Received' => $res['date_of_recived'],
                    'Loan Status' => $res['status'],
                    'Payment Mode' => $res['payment_mode'],
                    'Discount' => $res['discount'],
                    'Recovery By' => $res['rname'],
                    'Recovery Date' => $res['collection_executive_payment_created_on'],
                    'Approved By' => $res['closure_name'],
                    'Approved Date' => $res['closure_payment_updated_on'],
                    'Company Account Number' => $res['company_account_no'],
                    'Refrence Number' => $res['refrence_no'],
                    'Collection Remarks' => $res['remarks'],
                    'Closure Remark' => $res['closure_remarks'],
                    'NOC' => $res['noc'],
                    'Source' => $res['source'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVSuspenseVerified($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportSuspenseRecoveryModel($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            foreach ($result->result_array() as $res) {
                $export_data_array[] = array(
                    'lead ID' => $res['lead_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'State' => $res['m_state_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'ROI' => $res['roi'],
                    'Loan Disburse Date' => $res['disbursal_date'],
                    'Loan Repay Date' => $res['repayment_date'],
                    'Received Amount' => $res['received_amount'],
                    'Date Of Received' => $res['date_of_recived'],
                    'Suspense Delay Days' => $res['suspense_day'],
                    'Loan Status' => $res['status'],
                    // 'Lead Status' => $res['leadstatus'],
                    'Payment Mode' => $res['payment_mode'],
                    'Discount' => $res['discount'],
                    'Recovery By' => $res['rname'],
                    'Recovery Date' => $res['collection_executive_payment_created_on'],
                    'Approved By' => $res['closure_name'],
                    'Approved Date' => $res['closure_payment_updated_on'],
                    'Company Account Number' => $res['company_account_no'],
                    'Refrence Number' => $res['refrence_no'],
                    'Collection Remarks' => $res['remarks'],
                    'Closure Remark' => $res['closure_remarks'],
                    'NOC' => $res['noc'],
                    'Source' => $res['source'],
                    // 'Case Type' => '',
                    // 'Coupon Code' => '',
                    // 'POS Pending' => '',
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVBOBDisbursalPendingSheet($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportBOBDisbursalPending($fromDate, $toDate);

        //        traceObject($result);

        if (!empty($fromDate) && !empty($toDate)) {

            foreach ($result->result_array() as $res) {

                $export_data_array[] = array(
                    'CUSTOM_DETAILS1' => '',
                    'Value Date' => date("d/m/Y"),
                    'Message Type' => 'IMPS',
                    'Debit Account No.' => $res['company_disb_account_no'],
                    'Beneficiary Name' => $res['beneficiary_name'],
                    'Payment Amount' => $res['net_disbursal_amount'],
                    'Beneficiary Bank Swift Code / IFSC Code' => $res['ifsc_code'],
                    'Beneficiary Account No.' => "`" . $res['account'],
                    'Transaction Type Code' => 'IMPS',
                    'CUSTOM_DETAILS2' => '',
                    'CUSTOM_DETAILS3' => '',
                    'CUSTOM_DETAILS4' => '',
                    'CUSTOM_DETAILS5' => '',
                    'CUSTOM_DETAILS6' => '',
                    'Remarks' => $res['loan_no'],
                    'Purpose Of Payment' => 'LOAN DISBURSED',
                );
            }
            //            traceObject($export_data_array);
            //            die;
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVNewLoanDisbursed($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        $user_id = $_SESSION['isUserSession']['user_id'];

        if ((round($datediff / 86400) <= 90) || in_array($user_id, array(161, 406, 97))) {
            $result = $this->Export_Model->ExportNewDisbursed($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            $i = 0;
            foreach ($result->result_array() as $res) {
                $lead_id = 0;
                $cust_account_no = "'" . $res['account'];
                $lead_id = $res['lead_id'];
                $sql = "SELECT COL.received_amount, COL.lead_id, COL.date_of_recived FROM collection COL WHERE COL.payment_verification=1 AND COL.collection_active=1 AND COL.lead_id=$lead_id;";
                $data = $this->db->query($sql);
                $result = $data->result_array();

                $pre_collection_amount = 0;
                $upto11days = 0;
                $recovery_amount = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $c_date = strtotime($row['date_of_recived']);
                        $repay_date = strtotime($res['repayment_date']);
                        if ($c_date <= $repay_date) {
                            $pre_collection_amount += $row['received_amount'];
                        } elseif ($c_date > $repay_date && $c_date <= strtotime('+10 day', $repay_date)) {
                            $upto11days += $row['received_amount'];
                        } else {
                            $recovery_amount += $row['received_amount'];
                        }
                    }
                }



                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Id' => $res['customer_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'Pincode' => $res['cr_residence_pincode'],
                    'Pan Number' => $res['pancard'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Net Disbused Amount' => ($res['loan_recommended'] - $res['total_admin_fee']),
                    'Total Processing Fee' => $res['total_admin_fee'],
                    'Net Processing Fee' => $res['total_admin_fee'] - $res['adminFeeWithGST'],
                    'Processing Fee GST' => $res['adminFeeWithGST'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Mode Of Payment' => $res['mode_of_payment'],
                    'Company Bank Account Number' => $res['company_account_no'],
                    'Customer Bank Account Number' => $cust_account_no,
                    'Customer Bank Name' => $res['bank_name'],
                    'Customer Bank IFSC' => $res['ifsc_code'],
                    'Refrence No Of Disbursement' => $res['disburse_refrence_no'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Risk Profile' => $res['cam_risk_profile'],
                    'Lead Source' => $res['source'],
                    'UTM Source' => $res['utm_source'],
                    'UTM Campaign' => $res['utm_campaign'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Sanction Approved By' => $res['sanction_approve_by'],
                    'Loan Initiated By' => $res['loan_initiat_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Loan Disbursed By' => $res['loan_disburse_by'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                    'Current Status' => $res['current_status'],
                    'Principle Payable' => $res['loan_principle_payable_amount'],
                    'Principle Rcvd' => $res['loan_principle_received_amount'],
                    'Principle Outstanding' => $res['loan_principle_outstanding_amount'],
                    'Principle Discount' => $res['loan_principle_discount_amount'],
                    'Interest Payable' => $res['loan_interest_payable_amount'],
                    'Interest Rcvd' => $res['loan_interest_received_amount'],
                    'Interest Outstanding' => $res['loan_interest_outstanding_amount'],
                    'Interest Discount' => $res['loan_interest_discount_amount'],
                    'Panalty Payable' => $res['loan_penalty_payable_amount'],
                    'Panalty Rcvd' => $res['loan_penalty_received_amount'],
                    'Panalty Outstanding' => $res['loan_penalty_outstanding_amount'],
                    'Panalty Discount' => $res['loan_penalty_discount_amount'],
                    'Total Rcvd' => $res['loan_total_received_amount'],
                    'Total Outstanding' => $res['loan_total_outstanding_amount'],
                    'Total Discount' => $res['loan_total_discount_amount'],
                    'Loan Closure Date' => $res['loan_closure_date'],
                    'Loan Settled Date' => $res['loan_settled_date'],
                    'Pre Collection' => $pre_collection_amount,
                    'Up to 10 Days Collection' => $upto11days,
                    'Above 10 Days Collection' => $recovery_amount
                );
                //                if (agent == "CA") {
                //                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                //                    $export_data_array[$i]["Email"] = $res['email'];
                //                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVNewCollectionReport($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        if (in_array(agent, ["CO1", "CO2", "CO3"]) && strtotime($toDate) > strtotime('-10 day', strtotime(date('Y-m-d')))) {
            $file_name = $this->Export_Model->ReportName($report_id);
            $this->session->set_flashdata('err', 'Unauthorized Access: Data out of your Bucket Range. <br>Report Name: ' . $file_name[0]['m_export_name']);
            return redirect(base_url('exportData/'), 'refresh');
        }

        $result = $this->Export_Model->ExportCollectionModel($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            $collection_result = $this->Report_Model->PreCollectionCalculationModel($fromDate, $toDate);

            $i = 0;
            foreach ($result->result_array() as $res) {

                $lead_id = $res['lead_id'];

                $export_data_array[$i] = array(
                    'Lead Id' => $res['lead_id'],
                    'Branch' => $res['m_branch_name'],
                    'City' => $res['m_city_name'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['full_name'],
                    'Loan Amount' => $res['loan_recommended'],
                    'Net Disbused Amount' => ($res['loan_recommended'] - $res['admin_fee']),
                    'Admin Fee' => $res['admin_fee'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Loan Repay Amount' => $res['repayment_amount'],
                    'Disbursement Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Disbursement Status' => $res['status'],
                    'Repeat Type' => $res['user_type'],
                    'Sanctioned By' => $res['sname'],
                    'Sanction Date' => $res['lead_credit_assign_datetime'],
                    'Loan Initiated Date' => $res['created_on'],
                    'Lead Source' => $res['source'],
                    'Screen By' => $res['screenby'],
                    'Sanctioned By' => $res['sanctionby'],
                    'Sanction DateTime' => $res['lead_credit_assign_datetime'],
                    'Sanction Approved By' => $res['sanction_approve_by'],
                    'Loan Initiated By' => $res['loan_initiat_by'],
                    'Loan Initiated DateTime' => $res['lead_disbursal_assign_datetime'],
                    'Loan Recommanded DateTime' => $res['lead_disbursal_recommend_datetime'],
                    'Loan Disbursed By' => $res['loan_disburse_by'],
                    'Final Disbursed Date' => $res['lead_disbursal_approve_datetime'],
                    'Current Status' => $res['current_status'],
                    'Total Rcvd' => $res['loan_total_received_amount'],
                    'Total Outstanding' => $res['loan_total_outstanding_amount'],
                    'Total Discount' => $res['loan_total_discount_amount'],
                    'Loan Closure Date' => $res['loan_closure_date'],
                    'Loan Settled Date' => $res['loan_settled_date'],
                    'Loan Write-Off Date' => $res['loan_writeoff_date'],
                    'Upto 10 DPD Executive' => $res['pre_user_name1'],
                    'Upto 10 DPD Assign Datetime' => $res['lead_pre_collection_executive_assign_datetime1'],
                    'DPD 11 to 40 DPD Executive' => $res['pre_user_name2'],
                    'DPD 11 to 40 DPD Assign Datetime' => $res['lead_pre_collection_executive_assign_datetime2'],
                    'DPD 41 to 60 DPD Executive' => $res['coll_user_name1'],
                    'DPD 41 to 60 DPD Assign Datetime' => $res['lead_collection_executive_assign_datetime1'],
                    'DPD 61 Above DPD' => $res['coll_user_name2'],
                    'DPD 61 Above DPD Assign Datetime' => $res['lead_collection_executive_assign_datetime2'],
                );

                $user_id = $_SESSION['isUserSession']['user_id'];

                if ($user_id == 184 || $user_id == 406 || $user_id == 66) {
                    $export_data_array[$i]["Mobile"] = $res['mobile'];
                    $export_data_array[$i]["Alternate Mobile"] = $res['alternate_mobile'];
                    $export_data_array[$i]["Email"] = $res['email'];
                    $export_data_array[$i]["Alternate Email"] = $res['alternate_email'];
                }

                if (isset($collection_result[$lead_id])) {

                    // ********* Pre-Collection ************
                    $export_data_array[$i]['Pre Status'] = (!empty($collection_result[$lead_id]['pre_collection']['status']) ? $collection_result[$lead_id]['pre_collection']['status'] : '');
                    $export_data_array[$i]['Pre Total Rcvd'] = ($collection_result[$lead_id]['pre_collection']['total_collection'] > 0 ? $collection_result[$lead_id]['pre_collection']['total_collection'] : 0);
                    //                        $export_data_array[$i]['Pre Principle Payable'] = ($collection_result[$lead_id]['pre_collection']['loan_recommended'] > 0 ? $collection_result[$lead_id]['pre_collection']['loan_recommended'] : 0);
                    //                        $export_data_array[$i]['Pre Principle Rcvd'] = ($collection_result[$lead_id]['pre_collection']['prnl_rcvd'] > 0 ? $collection_result[$lead_id]['pre_collection']['prnl_rcvd'] : 0);
                    //                        $export_data_array[$i]['Pre Principle Outstanding'] = ($collection_result[$lead_id]['pre_collection']['prnl_outstanding'] > 0 ? $collection_result[$lead_id]['pre_collection']['prnl_outstanding'] : 0);
                    //                        $export_data_array[$i]['Pre Interest Payable'] = ($collection_result[$lead_id]['pre_collection']['payable_int'] > 0 ? $collection_result[$lead_id]['pre_collection']['payable_int'] : 0);
                    //                        $export_data_array[$i]['Pre Interest Rcvd'] = ($collection_result[$lead_id]['pre_collection']['int_rcvd'] > 0 ? $collection_result[$lead_id]['pre_collection']['int_rcvd'] : 0);
                    //                        $export_data_array[$i]['Pre Interest Outstanding'] = ($collection_result[$lead_id]['pre_collection']['int_outstanding'] > 0 ? $collection_result[$lead_id]['pre_collection']['int_outstanding'] : 0);
                    //                        $export_data_array[$i]['Pre Discount'] = ($collection_result[$lead_id]['pre_collection']['discount_amount'] > 0 ? $collection_result[$lead_id]['pre_collection']['discount_amount'] : 0);
                    $export_data_array[$i]['Pre Rcvd Date'] = $collection_result[$lead_id]['pre_collection']['received_date'];

                    // ********* Pre-Collection upto 10 days ************
                    $export_data_array[$i]['Pre_10 Status'] = (!empty($collection_result[$lead_id]['pre_collection_10']['status']) ? $collection_result[$lead_id]['pre_collection_10']['status'] : '');
                    $export_data_array[$i]['Pre_10 Total Rcvd'] = ($collection_result[$lead_id]['pre_collection_10']['total_collection'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['total_collection'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Principle Payable'] = ($collection_result[$lead_id]['pre_collection_10']['collection_payable'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['collection_payable'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Principle Rcvd'] = ($collection_result[$lead_id]['pre_collection_10']['prnl_rcvd'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['prnl_rcvd'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Principle Outstanding'] = ($collection_result[$lead_id]['pre_collection_10']['prnl_outstanding'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['prnl_outstanding'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Interest Payable'] = ($collection_result[$lead_id]['pre_collection_10']['payable_int'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['payable_int'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Interest Rcvd'] = ($collection_result[$lead_id]['pre_collection_10']['int_rcvd'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['int_rcvd'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Interest Outstanding'] = ($collection_result[$lead_id]['pre_collection_10']['int_outstanding'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['int_outstanding'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Penalty Payable'] = ($collection_result[$lead_id]['pre_collection_10']['penal_payable'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['penal_payable'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Penalty Rcvd'] = ($collection_result[$lead_id]['pre_collection_10']['penal_rcvd'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['penal_rcvd'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Penalty Outstanding'] = ($collection_result[$lead_id]['pre_collection_10']['penal_outstanding'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['penal_outstanding'] : 0);
                    //                        $export_data_array[$i]['Pre_10 Discount'] = ($collection_result[$lead_id]['pre_collection_10']['discount_amount'] > 0 ? $collection_result[$lead_id]['pre_collection_10']['discount_amount'] : 0);
                    $export_data_array[$i]['Pre_10 Rcvd Date'] = $collection_result[$lead_id]['pre_collection_10']['received_date'];

                    // ********* Collection upto 11 to 60 days ************
                    $export_data_array[$i]['Coll Status'] = (!empty($collection_result[$lead_id]['collection']['status']) ? $collection_result[$lead_id]['collection']['status'] : '');
                    $export_data_array[$i]['Coll Total Rcvd'] = ($collection_result[$lead_id]['collection']['total_collection'] > 0 ? $collection_result[$lead_id]['collection']['total_collection'] : 0);
                    //                        $export_data_array[$i]['Coll Principle Payable'] = ($collection_result[$lead_id]['collection']['collection_payable'] > 0 ? $collection_result[$lead_id]['collection']['collection_payable'] : 0);
                    //                        $export_data_array[$i]['Coll Principle Rcvd'] = ($collection_result[$lead_id]['collection']['prnl_rcvd'] > 0 ? $collection_result[$lead_id]['collection']['prnl_rcvd'] : 0);
                    //                        $export_data_array[$i]['Coll Principle Outstanding'] = ($collection_result[$lead_id]['collection']['prnl_outstanding'] > 0 ? $collection_result[$lead_id]['collection']['prnl_outstanding'] : 0);
                    //                        $export_data_array[$i]['Coll Interest Payable'] = ($collection_result[$lead_id]['collection']['payable_int'] > 0 ? $collection_result[$lead_id]['collection']['payable_int'] : 0);
                    //                        $export_data_array[$i]['Coll Interest Rcvd'] = ($collection_result[$lead_id]['collection']['int_rcvd'] > 0 ? $collection_result[$lead_id]['collection']['int_rcvd'] : 0);
                    //                        $export_data_array[$i]['Coll Interest Outstanding'] = ($collection_result[$lead_id]['collection']['int_outstanding'] > 0 ? $collection_result[$lead_id]['collection']['int_outstanding'] : 0);
                    //                        $export_data_array[$i]['Coll Penalty Payable'] = ($collection_result[$lead_id]['collection']['penal_payable'] > 0 ? $collection_result[$lead_id]['collection']['penal_payable'] : 0);
                    //                        $export_data_array[$i]['Coll Penalty Rcvd'] = ($collection_result[$lead_id]['collection']['penal_rcvd'] > 0 ? $collection_result[$lead_id]['collection']['penal_rcvd'] : 0);
                    //                        $export_data_array[$i]['Coll Penalty Outstanding'] = ($collection_result[$lead_id]['collection']['penal_outstanding'] > 0 ? $collection_result[$lead_id]['collection']['penal_outstanding'] : 0);
                    //                        $export_data_array[$i]['Coll Discount'] = ($collection_result[$lead_id]['collection']['discount_amount'] > 0 ? $collection_result[$lead_id]['collection']['discount_amount'] : 0);
                    $export_data_array[$i]['Coll Rcvd Date'] = $collection_result[$lead_id]['collection']['received_date'];

                    // ********* Recovery upto 61 to 180 days ************
                    $export_data_array[$i]['Recovery Status'] = (!empty($collection_result[$lead_id]['recovery']['status']) ? $collection_result[$lead_id]['recovery']['status'] : '');
                    $export_data_array[$i]['Recovery Total Rcvd'] = ($collection_result[$lead_id]['recovery']['total_recovery'] > 0 ? $collection_result[$lead_id]['recovery']['total_recovery'] : 0);
                    //                        $export_data_array[$i]['Recovery Principle Payable'] = ($collection_result[$lead_id]['recovery']['recovery_payable'] > 0 ? $collection_result[$lead_id]['recovery']['recovery_payable'] : 0);
                    //                        $export_data_array[$i]['Recovery Principle Rcvd'] = ($collection_result[$lead_id]['recovery']['prnl_rcvd'] > 0 ? $collection_result[$lead_id]['recovery']['prnl_rcvd'] : 0);
                    //                        $export_data_array[$i]['Recovery Principle Outstanding'] = ($collection_result[$lead_id]['recovery']['prnl_outstanding'] > 0 ? $collection_result[$lead_id]['recovery']['prnl_outstanding'] : 0);
                    //                        $export_data_array[$i]['Recovery Interest Payable'] = ($collection_result[$lead_id]['recovery']['payable_int'] > 0 ? $collection_result[$lead_id]['recovery']['payable_int'] : 0);
                    //                        $export_data_array[$i]['Recovery Interest Rcvd'] = ($collection_result[$lead_id]['recovery']['int_rcvd'] > 0 ? $collection_result[$lead_id]['recovery']['int_rcvd'] : 0);
                    //                        $export_data_array[$i]['Recovery Interest Outstanding'] = ($collection_result[$lead_id]['recovery']['int_outstanding'] > 0 ? $collection_result[$lead_id]['recovery']['int_outstanding'] : 0);
                    //                        $export_data_array[$i]['Recovery Penalty Payable'] = ($collection_result[$lead_id]['recovery']['penal_payable'] > 0 ? $collection_result[$lead_id]['recovery']['penal_payable'] : 0);
                    //                        $export_data_array[$i]['Recovery Penalty Rcvd'] = ($collection_result[$lead_id]['recovery']['penal_rcvd'] > 0 ? $collection_result[$lead_id]['recovery']['penal_rcvd'] : 0);
                    //                        $export_data_array[$i]['Recovery Penalty Outstanding'] = ($collection_result[$lead_id]['recovery']['penal_outstanding'] > 0 ? $collection_result[$lead_id]['recovery']['penal_outstanding'] : 0);
                    //                        $export_data_array[$i]['Recovery Discount'] = ($collection_result[$lead_id]['recovery']['discount_amount'] > 0 ? $collection_result[$lead_id]['recovery']['discount_amount'] : 0);
                    $export_data_array[$i]['Recovery Rcvd Date'] = $collection_result[$lead_id]['recovery']['received_date'];

                    // ********* Legal upto 180+ days ************
                    $export_data_array[$i]['Legal Status'] = (!empty($collection_result[$lead_id]['legal']['status']) ? $collection_result[$lead_id]['legal']['status'] : '');
                    $export_data_array[$i]['Legal Total Rcvd'] = ($collection_result[$lead_id]['legal']['total_recovery'] > 0 ? $collection_result[$lead_id]['legal']['total_recovery'] : 0);
                    //                        $export_data_array[$i]['Legal Principle Payable'] = ($collection_result[$lead_id]['legal']['recovery_payable'] > 0 ? $collection_result[$lead_id]['legal']['recovery_payable'] : 0);
                    //                        $export_data_array[$i]['Legal Principle Rcvd'] = ($collection_result[$lead_id]['legal']['prnl_rcvd'] > 0 ? $collection_result[$lead_id]['legal']['prnl_rcvd'] : 0);
                    //                        $export_data_array[$i]['Legal Principle Outstanding'] = ($collection_result[$lead_id]['legal']['prnl_outstanding'] > 0 ? $collection_result[$lead_id]['legal']['prnl_outstanding'] : 0);
                    //                        $export_data_array[$i]['Legal Interest Payable'] = ($collection_result[$lead_id]['legal']['payable_int'] > 0 ? $collection_result[$lead_id]['legal']['payable_int'] : 0);
                    //                        $export_data_array[$i]['Legal Interest Rcvd'] = ($collection_result[$lead_id]['legal']['int_rcvd'] > 0 ? $collection_result[$lead_id]['legal']['int_rcvd'] : 0);
                    //                        $export_data_array[$i]['Legal Interest Outstanding'] = ($collection_result[$lead_id]['legal']['int_outstanding'] > 0 ? $collection_result[$lead_id]['legal']['int_outstanding'] : 0);
                    //                        $export_data_array[$i]['Legal Penalty Payable'] = ($collection_result[$lead_id]['legal']['penal_payable'] > 0 ? $collection_result[$lead_id]['legal']['penal_payable'] : 0);
                    //                        $export_data_array[$i]['Legal Penalty Rcvd'] = ($collection_result[$lead_id]['legal']['penal_rcvd'] > 0 ? $collection_result[$lead_id]['legal']['penal_rcvd'] : 0);
                    //                        $export_data_array[$i]['Legal Penalty Outstanding'] = ($collection_result[$lead_id]['legal']['penal_outstanding'] > 0 ? $collection_result[$lead_id]['legal']['penal_outstanding'] : 0);
                    //                        $export_data_array[$i]['Legal Discount'] = ($collection_result[$lead_id]['legal']['discount_amount'] > 0 ? $collection_result[$lead_id]['legal']['discount_amount'] : 0);
                    $export_data_array[$i]['Legal Rcvd Date'] = $collection_result[$lead_id]['legal']['received_date'];
                }
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLoanDumpReport($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportLoanDumpModel($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {

                $is_blacklisted = "NO";
                if ($res['lead_black_list_flag'] == 1) {
                    $is_blacklisted = "YES";
                }

                $is_stp = "NO";
                if ($res['lead_stp_flag'] == 1) {
                    $is_stp = "YES";
                }

                $is_pan_verified = "NO";
                if ($res['pancard_verified_status'] == 1) {
                    $is_pan_verified = "YES";
                }

                $is_pan_ocr_verified = "NO";
                if ($res['pancard_ocr_verified_status'] == 1) {
                    $is_pan_ocr_verified = "YES";
                }

                $is_aadhar_ocr_verified = "NO";
                if ($res['aadhaar_ocr_verified_status'] == 1) {
                    $is_aadhar_ocr_verified = "YES";
                }

                $is_kyc_done = "NO";
                if ($res['customer_digital_ekyc_flag'] == 1) {
                    $is_kyc_done = "YES";
                }

                $cam_blacklist_removed_flag = "NO";
                if ($res['cam_blacklist_removed_flag'] == 1) {
                    $cam_blacklist_removed_flag = "YES";
                }

                $account_status_id = "NO";
                if ($res['account_status_id'] == 1) {
                    $account_status_id = "YES";
                }

                $loan_disbursement_payment_mode_id = "OFFLINE";
                if ($res['loan_disbursement_payment_mode_id'] == 1) {
                    $loan_disbursement_payment_mode_id = "ONLINE";
                }

                $loan_disbursement_payment_type_id = "NEFT";
                if ($res['loan_disbursement_payment_type_id'] == 1) {
                    $loan_disbursement_payment_type_id = "IMPS";
                }

                $mobile_verified_status = "NO";
                if ($res['mobile_verified_status'] == 1) {
                    $mobile_verified_status = "YES";
                }

                $income_type = "";
                if ($res['income_type'] == 1) {
                    $income_type = "SALARIED";
                } else {
                    $income_type = "SELF EMPLOYED";
                }

                $export_data_array[] = array(
                    'LEAD_ID' => $res['lead_id'],
                    'CUSTOMER_ID' => $res['customer_id'],
                    'LOAN_NO' => $res['loan_no'],
                    'LOAN_PURPOSE' => $res['purpose'],
                    'USER_TYPE' => $res['user_type'],
                    'PANCARD' => $res['pancard'],
                    'MOBILE' => $res['mobile'],
                    'APPLIED_LOAN_AMOUNT' => $res['loan_amount'],
                    'APPLIED_TENURE' => $res['tenure'],
                    'CIBIL' => $res['cibil'],
                    'OBLIGATIONS_BY_USER' => $res['obligations'],
                    'PROMOCODE' => $res['promocode'],
                    'LEAD_SOURCE' => $res['source'],
                    'BRANCH' => $res['BRANCH'],
                    'STATE' => $res['STATE'],
                    'CITY' => $res['CITY'],
                    'PINCODE' => $res['pincode'],
                    'APPLIED_LAT_LONG' => $res['coordinates'],
                    'CURRENT_STATUS' => $res['current_status'],
                    'UTM_SOURCE' => $res['utm_source'],
                    'UTM_CAMPAIGN' => $res['utm_campaign'],
                    'APPLIED_IP' => $res['ip'],
                    'LEAD_INITIATED_DATETIME' => $res['created_on'],
                    'LEAD_INITIATED_DATE' => $res['lead_entry_date'],
                    'REFERENCE_NO' => $res['lead_reference_no'],
                    'SCREEN_ASSIGNED_TO' => $res['SCREEN_ASSIGNED_TO'],
                    'SCREEN_ASSIGNED_DATETIME' => $res['lead_screener_assign_datetime'],
                    'SCREEN_RECOMMENDED_DATETIME' => $res['lead_screener_recommend_datetime'],
                    'CREDIT_ASSIGN_TO' => $res['CREDIT_ASSIGN_TO'],
                    'CREDIT_ASSIGNED_DATETIME' => $res['lead_credit_assign_datetime'],
                    'CREDIT_RECOMMENDED_DATETIME' => $res['lead_credit_recommend_datetime'],
                    'SANCTION_ASSIGNED_BY' => $res['SANCTION_ASSIGNED_BY'],
                    'SANCTION_ASSIGNED_DATETIME' => $res['lead_credithead_assign_datetime'],
                    'SANCTION_APPROVED_BY' => $res['SANCTION_APPROVED_BY'],
                    'SANCTION_APPROVED_DATETIME' => $res['lead_credit_approve_datetime'],
                    'DISBURSAL_ASSIGNED_BY' => $res['DISBURSAL_ASSIGNED_BY'],
                    'DISBURSAL_ASSIGNED_DATETIME' => $res['lead_disbursal_assign_datetime'],
                    'DISBURSAL_RECOMMENDED_DATETIME' => $res['lead_disbursal_recommend_datetime'],
                    'DISBURSAL_APPROVED_BY' => $res['DISBURSAL_APPROVED_BY'],
                    'DISBURSAL_APPROVED_DATETIME' => $res['lead_disbursal_approve_datetime'],
                    'FINAL_DISBURSAL_DATE' => $res['lead_final_disbursed_date'],
                    'REJECTED_REASON' => $res['lead_rejected_reason_id'],
                    'REJECTED_BY' => $res['REJECTED_BY'],
                    'REJECTED_DATETIME' => $res['lead_rejected_datetime'],
                    'IS_BLACKLISTED' => $is_blacklisted,
                    'IS_STP' => $is_stp,
                    'FIRST_NAME' => $res['first_name'],
                    'MIDDLE_NAME' => $res['middle_name'],
                    'LAST_NAME' => $res['sur_name'],
                    'GENDER' => $res['gender'],
                    'DOB' => $res['dob'],
                    'IS_PANCARD_VERIFIED' => $is_pan_verified,
                    'PAN_VERIFIED_DATETIME' => $res['pancard_verified_on'],
                    'IS_OCR_PANCARD_VERIFIED' => $is_pan_ocr_verified,
                    'OCR_PAN_VERIFIED_DATETIME' => $res['pancard_ocr_verified_on'],
                    'IS_EMAIL_VERIFIED' => $res['email_verified_status'],
                    'EMAIL_VERIFIED_DATETIME' => $res['email_verified_on'],
                    'IS_ALTERNATE_EMAIL_VERIFIED' => $res['alternate_email_verified_status'],
                    'IS_ALTERNATE_EMAIL_VERIFIED_DATETIME' => $res['alternate_email_verified_on'],
                    'IS_MOBILE_VERIFIED' => $mobile_verified_status,
                    'CURRENT_HOUSE_ADDRESS' => $res['current_house'],
                    'CURRENT_LOCALITY' => $res['current_locality'],
                    'CURRENT_LANDMARK' => $res['current_landmark'],
                    'CURRENT_PINCODE' => $res['cr_residence_pincode'],
                    'AADHAAR_HOUSE_ADDRESS' => $res['aa_current_house'],
                    'AADHAAR_LOCALITY' => $res['aa_current_locality'],
                    'AADHAAR_LANDMARK' => $res['aa_current_landmark'],
                    'AADHAAR_PINCODE' => $res['aa_cr_residence_pincode'],
                    'AADHAAR_STATE' => $res['AADHAAR_STATE'],
                    'AADHAAR_CITY' => $res['AADHAAR_CITY'],
                    'CURRENT_RESIDENCE_SINCE' => $res['current_residence_since'],
                    'CURRENT_RESIDENCE_TYPE' => $res['current_residence_type'],
                    'IS_RESIDING_WITH_FAMILY' => $res['current_residing_withfamily'],
                    'CURRENT_STATE' => $res['CURRENT_STATE'],
                    'CURRENT_CITY' => $res['CURRENT_CITY'],
                    'LAST_FOUR_AADHAAR_DIGITS' => $res['aadhar_no'],
                    'RELIGION' => $res['RELIGION'],
                    'FATHER_NAME' => $res['father_name'],
                    'IS_AADAHAAR_OCR_VERIFIED' => $is_aadhar_ocr_verified,
                    'AADAHAAR_OCR_VERIFIED_DATETIME' => $res['aadhaar_ocr_verified_on'],
                    'EKYC_REQUEST_INITIATED_DATETIME' => $res['customer_ekyc_request_initiated_on'],
                    'EKYC_REQUESTED_IP' => $res['customer_ekyc_request_ip'],
                    'IS_EKYC_DONE' => $is_kyc_done,
                    'EKYC_COMPLETED_DATETIME' => $res['customer_digital_ekyc_done_on'],
                    'IS_NEW_TO_CREDIT' => $res['ntc'],
                    'IS_HAVING_OTHER_PD' => $res['run_other_pd_loan'],
                    'DELAY_OTHER_LOAN_30_DAYS' => $res['delay_other_loan_30_days'],
                    'JOB_STABILITY' => $res['job_stability'],
                    'CITY_CATEGORY' => $res['city_category'],
                    'SALARY_CREDIT_1_DATE' => $res['salary_credit1_date'],
                    'SALARY_CREDIT_1_AMOUNT' => $res['salary_credit1_amount'],
                    'SALARY_CREDIT_2_DATE' => $res['salary_credit2_date'],
                    'SALARY_CREDIT_2_AMOUNT' => $res['salary_credit2_amount'],
                    'SALARY_CREDIT_3_DATE' => $res['salary_credit3_date'],
                    'SALARY_CREDIT_3_AMOUNT' => $res['salary_credit3_amount'],
                    'NEXT_PAY_DATE' => $res['next_pay_date'],
                    'MEDIAN_SALARY' => $res['median_salary'],
                    'CUSTOMER_AGE' => $res['borrower_age'],
                    'ELIGIBLE_FOIR_PERCENTAGE' => $res['eligible_foir_percentage'],
                    'ELIGIBLE_LOAN' => $res['eligible_loan'],
                    'LOAN_RECOMMENDED' => $res['loan_recommended'],
                    'PROCESSING_FEE_PERCENT' => $res['processing_fee_percent'],
                    'ROI' => $res['roi'],
                    'ADMIN_FEE' => $res['admin_fee'],
                    'DISBURSAL_DATE' => $res['disbursal_date'],
                    'REPAYMENT_DATE' => $res['repayment_date'],
                    'ADMIN_FEE_WITH_GST' => $res['adminFeeWithGST'],
                    'TOTAL_ADMIN_FEE' => $res['total_admin_fee'],
                    'TENURE' => $res['tenure'],
                    'NET_DISBURSAL_AMOUNT' => $res['net_disbursal_amount'],
                    'REPAYMENT_AMOUNT' => $res['repayment_amount'],
                    'PENAL_ROI' => $res['panel_roi'],
                    'CREDIT_EXECUTIVE_REMARK' => $res['remark'],
                    'CAM_CREATED_BY' => $res['CAM_CREATED_BY'],
                    'CAM_CREATED_AT' => $res['created_at'],
                    'UPDATED_BY' => $res['UPDATED_BY'],
                    'UPDATED_AT' => $res['updated_at'],
                    'CAM_SANCTION_LETTER_FILE_NAME' => $res['cam_sanction_letter_file_name'],
                    'CAM_SANCTION_LETTER_ESGIN_TYPE_ID' => $res['cam_sanction_letter_esgin_type_id'],
                    'CAM_SANCTION_LETTER_ESGIN_FILE_NAME' => $res['cam_sanction_letter_esgin_file_name'],
                    'CAM_SANCTION_LETTER_ESGIN_ON' => $res['cam_sanction_letter_esgin_on'],
                    'CAM_SANCTION_LETTER_IP_ADDRESS' => $res['cam_sanction_letter_ip_address'],
                    'CAM_SANCTION_LETTER_ESGIN_COUNT' => $res['cam_sanction_letter_esgin_count'],
                    'CAM_RISK_PROFILE' => $res['cam_risk_profile'],
                    'CAM_RISK_SCORE' => $res['cam_risk_score'],
                    'CAM_ADVANCE_INTEREST_AMOUNT' => $res['cam_advance_interest_amount'],
                    'CAM_APPRAISED_OBLIGATIONS' => $res['cam_appraised_obligations'],
                    'CAM_APPRAISED_MONTHLY_INCOME' => $res['cam_appraised_monthly_income'],
                    'CAM_BLACKLIST_REMOVED_FLAG' => $cam_blacklist_removed_flag,
                    'CAM_SANCTION_REMARKS' => $res['cam_sanction_remarks'],
                    'RECOMMENDED_AMOUNT' => $res['recommended_amount'],
                    'DISBURSE_REFRENCE_NO' => $res['disburse_refrence_no'],
                    'STATUS' => $res['status'],
                    'AGREEMENT_REQUESTED_DATE' => $res['agrementRequestedDate'],
                    'LOAN_AGREEMENT_RESPONSE' => $res['loanAgreementResponse'],
                    'AGREEMENT_USER_IP' => $res['agrementUserIP'],
                    'AGREEMENT_RESPONSE_DATE' => $res['agrementResponseDate'],
                    'DISBURSAL_MODE_OF_PAYMENT' => $res['mode_of_payment'],
                    'CHANNEL' => $res['channel'],
                    'LOAN_CREATED_BY' => $res['LOAN_CREATED_BY'],
                    'LOAN_CREATED_ON' => $res['created_on'],
                    'LOAN_UPDATED_BY' => $res['LOAN_UPDATED_BY'],
                    'LOAN_UPDATED_ON' => $res['updated_on'],
                    'LOAN_DISBURSEMENT_PAYMENT_MODE' => $loan_disbursement_payment_mode_id,
                    'LOAN_DISBURSEMENT_PAYMENT_TYPE' => $loan_disbursement_payment_type_id,
                    'IS_LOAN_NOC_LETTER_SENT_STATUS' => $res['loan_noc_letter_sent_status'],
                    'LOAN_NOC_LETTER_SENT_DATETIME' => $res['loan_noc_letter_sent_datetime'],
                    'LOAN_NOC_LETTER_SENT_BY' => $res['loan_noc_letter_sent_user_id'],
                    'LOAN_BUREAU_REPORT_FLAG' => $res['loan_bureau_report_flag'],
                    'LOAN_PRINCIPAL_PAYABLE_AMOUNT' => $res['loan_principle_payable_amount'],
                    'LOAN_INTEREST_PAYABLE_AMOUNT' => $res['loan_interest_payable_amount'],
                    'LOAN_PENALTY_PAYABLE_AMOUNT' => $res['loan_penalty_payable_amount'],
                    'LOAN_PRINCIPAL_RECEIVED_AMOUNT' => $res['loan_principle_received_amount'],
                    'LOAN_INTEREST_RECEIVED_AMOUNT' => $res['loan_interest_received_amount'],
                    'LOAN_PENALTY_RECEIVED_AMOUNT' => $res['loan_penalty_received_amount'],
                    'LOAN_PRINCIPAL_DISCOUNT_AMOUNT' => $res['loan_principle_discount_amount'],
                    'LOAN_INTEREST_DISCOUNT_AMOUNT' => $res['loan_interest_discount_amount'],
                    'LOAN_PENALTY_DISCOUNT_AMOUNT' => $res['loan_penalty_discount_amount'],
                    'LOAN_PRINCIPAL_OUTSTANDING_AMOUNT' => $res['loan_principle_outstanding_amount'],
                    'LOAN_INTEREST_OUTSTANDING_AMOUNT' => $res['loan_interest_outstanding_amount'],
                    'LOAN_PENALTY_OUTSTANDING_AMOUNT' => $res['loan_penalty_outstanding_amount'],
                    'LOAN_TOTAL_PAYABLE_AMOUNT' => $res['loan_total_payable_amount'],
                    'LOAN_TOTAL_RECEIVED_AMOUNT' => $res['loan_total_received_amount'],
                    'LOAN_TOTAL_DISCOUNT_AMOUNT' => $res['loan_total_discount_amount'],
                    'LOAN_TOTAL_OUTSTANDING_AMOUNT' => $res['loan_total_outstanding_amount'],
                    'LOAN_CLOSURE_DATE' => $res['loan_closure_date'],
                    'LOAN_SETTLED_DATE' => $res['loan_settled_date'],
                    'LOAN_WRITEOFF_DATE' => $res['loan_writeoff_date'],
                    'EMPLOYER_NAME' => $res['employer_name'],
                    'EMP_PINCODE' => $res['emp_pincode'],
                    'EMP_ADDRESS_LINE_1' => $res['emp_house'],
                    'EMP_STREET' => $res['emp_street'],
                    'EMP_LANDMARK' => $res['emp_landmark'],
                    'EMP_WORKING_SINCE' => $res['emp_residence_since'],
                    'EMP_DESIGNATION' => $res['emp_designation'],
                    'EMP_DEPARTMENT' => $res['emp_department'],
                    'EMP_EMPLOYER_TYPE' => $res['emp_employer_type'],
                    'PRESENT_SERVICE_TENURE_AT_APPLICATION' => $res['presentServiceTenure'],
                    'EMP_WEBSITE' => $res['emp_website'],
                    'MONTHLY_INCOME_MENTIONED' => $res['monthly_income'],
                    'EMP_SALARY_MODE' => $res['income_type'],
                    'SALARY_MODE' => $res['salary_mode'],
                    'EMP_LOCALITY' => $res['emp_locality'],
                    'EMP_STATE' => $res['EMP_STATE'],
                    'EMP_CITY' => $res['EMP_CITY'],
                    'CUSTOMER_BANK_NAME' => $res['bank_name'],
                    'CUSTOMER_IFSC_CODE' => $res['ifsc_code'],
                    'CUSTOMER_BRANCH' => $res['branch'],
                    'CUSTOMER_BENEFICIARY_NAME' => $res['beneficiary_name'],
                    'CUSTOMER_ACCOUNT_NO' => $res['account'],
                    'CUSTOMER_ACCOUNT_TYPE' => $res['account_type'],
                    'IS_ACCOUNT_VERIFIED' => $account_status_id,
                    'BANKING_CREATED_BY' => $res['BANKING_CREATED_BY'],
                    'BANKING_CREATED_ON' => $res['created_on'],
                    'BANKING_UPDATED_BY' => $res['BANKING_UPDATED_BY'],
                    'BANKING_UPDATED_ON' => $res['updated_on'],
                    'EMPLOYEMENT_TYPE' => $income_type,
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVbrerulesresult($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportBRERulesResultModel($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {

                $sytem_decision = "Not Applicable";
                if ($res['lbrr_rule_system_decision_id'] == 1) {
                    $sytem_decision = "Approved";
                } else if ($res['lbrr_rule_system_decision_id'] == 2) {
                    $sytem_decision = "Referred";
                } else if ($res['lbrr_rule_system_decision_id'] == 3) {
                    $sytem_decision = "Rejected";
                }

                $manual_decision = "Not Applicable";
                if ($res['lbrr_rule_manual_decision_id'] == 1) {
                    $manual_decision = "Approved";
                } else if ($res['lbrr_rule_manual_decision_id'] == 2) {
                    $manual_decision = "Referred";
                } else if ($res['lbrr_rule_manual_decision_id'] == 3) {
                    $manual_decision = "Rejected";
                }

                $export_data_array[] = array(
                    'LEAD ID' => $res['lbrr_lead_id'],
                    'Rule Name' => $res['lbrr_rule_name'],
                    'Rule Category' => $res['m_bre_cat_name'],
                    'Cut-Off Value' => $res['lbrr_rule_cutoff_value'],
                    'Actual Value' => $res['lbrr_rule_actual_value'],
                    'Inputs Value' => $res['lbrr_rule_actual_value'],
                    'System Decision' => $sytem_decision,
                    'Manual Decision' => $manual_decision,
                    'Manual Remarks' => $res['lbrr_rule_manual_decision_remarks'],
                    'Created DateTime' => $res['lbrr_created_on'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLegalNoticeSentLog($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportLegalNoticeSentLogModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {

                $legal_notice_type = $res['legal_notice_type_id'] == 1 ? "BY POST" : "BY EMAIL";
                $legal_notice_return_status = $res['legal_notice_return_status'] == 1 ? "YES" : "NO";

                $export_data_array[] = array(
                    'Lead Id' => $res['legal_notice_lead_id'],
                    'Loan No' => $res['legal_notice_loan_no'],
                    'Name of Customer' => $res['first_name'],
                    'Phone No. of Customer' => $res['mobile'],
                    'Pancard' => $res['pancard'],
                    'Sent Type' => $legal_notice_type,
                    'Sent DateTime' => $res['legal_notice_sent_datetime'],
                    'Transaction No' => $res['legal_notice_sent_txn_no'],
                    'Address of Customer with Pincode' => $res['aadhaar_address'],
                    'Return Status' => $legal_notice_return_status,
                    'Return DateTime' => $res['legal_notice_return_datetime'],
                    'Return Remarks' => $res['legal_notice_return_remarks'],
                    'DPD on Legal' => $res['legal_notice_total_dpd_days'],
                    'Total Due on Legal' => $res['legal_notice_total_due_amount'],
                    'Total Amount Recevied Legal Sent Date' => $res['legal_notice_total_received_amount'],
                    'Legal Sent By' => $res['sent_by'],
                    'Created On' => $res['legal_notice_created_on']
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }


    public function exportPartialLeaddata($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->exportPartialLeaddataModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {
                if ($res['lead_is_mobile_verified'] == 1) {
                    $mobile_verified = 'Name Not Verified';
                } else {
                    $mobile_verified = 'OTP Not Verified';
                }
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Mobile' => $res['mobile'],
                    'AffiliatesWeb' => $res['source'],
                    'lead_is_mobile_verified' => $mobile_verified,
                    'Applyed On' => $res['created_on']

                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVSalariedBorrowers($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportSalariedBorrowersModel($fromDate, $toDate);
        if (!empty($result->num_rows())) {
            $export_data_array = array();

            foreach ($result->result_array() as $res) {



                $export_data_array[] = array(
                    'First Name' => $res['first_name'],
                    'Last Name' => $res['sur_name'],
                    'Mobile Number' => $res['mobile'],
                    'Reference No' => $res['lead_id'],
                    'Email ID' => $res['email'],
                    'PAN' => $res['pancard'],
                    'Aadhaar No' => $res['aadhar_no'],
                    'Net Monthly Income' => $res['monthly_income'],
                    'DOB' => $res['dob'],
                    'House Type' => $res['current_residence_type'],
                    'Current Address' => $res['current_address'],
                    'Pin Code' => $res['cr_residence_pincode'],
                    'Currently Living Here Since' => $res['current_residence_since'],
                    'Permanent Address' => $res['aadhar_address'],
                    'Permanent Pincode' => $res['aa_cr_residence_pincode'],
                    'Permanent House Type' => 'OTHER',
                    'Employment Type' => ($res['income_type'] == 1 ? "SALARIED" : "SELF EMPLOYED"),
                    'Mode Of Salary' => $res['salary_mode'],
                    'Are You Currently Paying Any EMI' => ($res['obligations'] > 0 ? "Yes" : "No"),
                    'Loan Amount' => $res['loan_recommended'],
                    'Tenure' => $res['tenure'],
                    'ROI' => $res['roi'],
                    'Purpose Of Loan' => $res['purpose'],
                    "Father's/Husband's Name" => $res['father_name'],
                    'Total Work Experience (in Years)' => $res['presentServiceTenure'],
                    'Gender' => $res['gender'],
                    'Marital Status' => "OTHER",
                    'Number Of Dependents' => 0,
                    'Highest Education' => "OTHER",
                    'Company Name' => $res['employer_name'],
                    'Company Contact Number' => $res[''],
                    'Company Address' => $res['office_address'],
                    'Company Pincode' => $res['emp_pincode'],
                    'Company Type' => $res['emp_employer_type'],
                    'Designation' => $res['emp_designation'],
                    'Working With Current Employer' => $res['emp_residence_since'],
                    'Account Holder Name' => $res['beneficiary_name'],
                    'IFSC Code' => $res['ifsc_code'],
                    'Bank Name' => $res['bank_name'],
                    'Account No' => $res['account'],
                    'Confirm Account No' => $res['confirm_account'],
                    'Account Type' => $res['account_type'],
                    'Is Field Your Itr' => "No",
                    'Business Type' => "",
                    'Firm Name' => "",
                    'Firm Type' => "",
                    'Industry Type' => "",
                    'Have Company Pan' => "",
                    'Company Pan' => "",
                    'Yearly Income' => "",
                    'Duration Working With Current Company' => "",
                    'Businss Company Address' => "",
                    'Businss Company Pincode' => "",
                    'Businss Contact Number' => "",
                    'Business Address Type' => "",
                    'Have Pos Machine' => "",
                    'Spouse Working Status' => "",
                    'Monthly Earning Of Spouse' => "",
                    'Number Of Kids' => ""
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVICICIBANK($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);
        $result = $this->Export_Model->ExportICICIBANKModel($fromDate, $toDate);
        if (!empty($fromDate) && !empty($toDate)) {
            $report_data = $result->result_array();
            //echo '<pre>';print_r($report_data);die;
            foreach ($report_data as $res) {
                $export_data_array[] = array(
                    'PYMT_PROD_TYPE_CODE' => $res['mode_of_payment'],
                    'PYMT_MODE' => $res['channel'],
                    'DEBIT_ACC_NO' => $res['company_disb_account_no'],
                    'BNF_NAME' => $res['beneficiary_name'],
                    'BENE_ACC_NO' => $res['account'],
                    'BENE_IFSC' => $res['ifsc_code'],
                    'AMOUNT' => $res['net_disbursal_amount'],
                    'DEBIT_NARR' => 'LOAN DSBURSED',
                    'CREDIT_NARR' => 'LOAN DSBURSED',
                    'MOBILE_NUM' => $res['mobile'],
                    'EMAIL_ID' => $res['email'],
                    'REMARK' => $res['loan_no'],
                    'PYMT_DATE' => '',
                    'REF_NO' => $res['disburse_refrence_no'],
                    'ADDL_INFO1' => '',
                    'ADDL_INFO2' => '',
                    'ADDL_INFO3' => '',
                    'ADDL_INFO4' => '',
                    'ADDL_INFO5' => ''
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVDisbursalAccountReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportDibsursalAccountModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            if (!empty($report_data)) {
                foreach ($report_data as $res) {

                    $net_disbursal_amt = $res['sanction_loan_amount'] - $res['total_admin_fees'];

                    $export_data_array[] = array(
                        'LEAD ID' => $res['lead_id'],
                        'LOAN NO' => $res['loan_no'],
                        'CUSTOMER NAME' => $res['customer_name'],
                        'LOAN AMOUNT' => $res['sanction_loan_amount'],
                        'PROCESSING FEE (RS.)' => $res['net_admin_fees'],
                        'CGST AMOUNT#9(%)' => number_format($res['CGST'], 2),
                        'SGST AMOUNT#9(%)' => number_format($res['SGST'], 2),
                        'IGST AMOUNT#18(%)' => number_format($res['IGST'], 2),
                        'TOTAL PROCESSING FEE (RS)' => $res['total_admin_fees'],
                        'NET DISBURSED AMOUNT' => $net_disbursal_amt,
                        'RESDENTIAL STATE' => $res['state_name'],
                    );
                }

                if (!empty($export_data_array)) {
                    $export_header_array = array_keys($export_data_array[0]);
                } else {
                    echo "No data available to export.";
                }
            } else {
                echo "No report data available.";
            }
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVMasterDisbursalReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->ExportMasterDisbursalModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {

                $net_disbursal_amt = $res['sanction_loan_amount'] - $res['total_admin_fees'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Applyed On' => $res['created_on'],
                    'Loan No' => $res['loan_no'],
                    'Customer Name' => $res['customer_name'],
                    'Pancard' => $res['pancard'],
                    'Loan Amount' => $res['sanction_loan_amount'],
                    'final foir(%)' => $res['final_foir_percentage'],
                    'Total Admin Fee (Rs)' => $res['total_admin_fees'],
                    'Net Disbursed Amount' => $net_disbursal_amt,
                    'EMP1' => '-',
                    'EMP2' => '-',
                    'EMP3' => '-',
                    'EMP4' => '-',
                    'EMP5' => '-',
                    'EMP6' => '-',
                    'EMP7' => '-',
                    'DISBURSAL DATE' => $res['disbursal_date'],
                    'repay date' => $res['repayment_date'],
                    'TENURE DAYS' => $res['tenure'],
                    'repay amount' => $res['repayment_amount'],
                    'USER TYPE' => $res['user_type'],
                    'CREDIT ASIGN BY' => $res['sanction_by']
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVAuditTatReport($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        if ((round($datediff / 86400) <= 60)) {
            $result = $this->Export_Model->ExportLeadTotal($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }

        $result = $this->Export_Model->ExportAuditTatModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Loan No' => $res['loan_no'],
                    'USER TYPE' => $res['user_type'],
                    'status ' => $res['status'],
                    'credit_manager_tat' => $res['credit_manager_tat'],
                    'credit_manager_name ' => $res['credit_manager_name'],
                    'credit_head_tat' => $res['credit_head_tat'],
                    'credit_head_name' => $res['credit_head_name'],
                    'audit_tat' => $res['audit_tat'],
                    'audit_name' => $res['audit_name'],
                    'disbursal_tat' => $res['disbursal_tat'],
                    'disbursal_name' => $res['disbursal_name'],
                );
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportClosedLoan($fromDate, $toDate) {

        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $user_id = $_SESSION['isUserSession']['user_id'];

            $from = strtotime($fromDate);
            $to = strtotime($toDate); // or your date as well
            $datediff = $to - $from;

            if ((round($datediff / 86400) <= 90) || in_array($user_id, array(1, 6))) {
                $result = $this->Export_Model->ExportClosedLoanModel($fromDate, $toDate);
            } else {
                $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
                redirect(base_url('exportData/'), 'refresh');
            }


            $report_data = $result->result_array();
            print_r($report_data);
            die;

            foreach ($report_data as $res) {

                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'Customer Name' => $res['name'],
                    'Pancard' => $res['pancard'],
                    'Mobile' => $res['mobile'],
                    'Lead Status' => $res['status'],
                    'Loan Recommended' => $res['loan_recommended'],
                    'Disbursal Date' => $res['disbursal_date'],
                    'Repayment Date' => $res['repayment_date'],
                    'Loan Closure Date' => $res['loan_closure_date'],
                    'DPD' => $res['DPD']
                );
            }

            $export_header_array = array_keys($export_data_array[0]);

            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVReloanTatReport($fromDate, $toDate) {


        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->exportCSVReloanTatModel($fromDate, $toDate);

        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {

                if ($res['lead_is_mobile_verified'] == 1) {
                    $mobile_verified = 'Name Not Verified';
                } else {
                    $mobile_verified = 'OTP Not Verified';
                }

                $net_disbursal_amt = $res['sanction_loan_amount'] - $res['total_admin_fees'];
                $export_data_array[] = array(
                    'Lead Id' => $res['lead_id'],
                    'name' => $res['first_name'],
                    'Pancard' => $res['pancard'],
                    'status' => $res['status'],
                    'mobile no' => $res['mobile'],
                    'closing date' => $res['loan_closure_date']

                );
            }

            $export_header_array = array_keys($export_data_array[0]);

            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLowConversionTatReport($fromDate, $toDate) {


        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->exportCSVLowConversionTatReportModel($fromDate, $toDate);


        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {

                if ($res['lead_is_mobile_verified'] == 1) {
                    $mobile_verified = 'Name Not Verified';
                } else {
                    $mobile_verified = 'OTP Not Verified';
                }

                $net_disbursal_amt = $res['sanction_loan_amount'] - $res['total_admin_fees'];
                $export_data_array[] = array(
                    'name' => $res['name'],
                    'total lead' => $res['TOTAL_LEAD'],
                    'lead in process' => $res['LEAD_INPROCESS'],
                    'lead hold' => $res['LEAD_HOLD'],
                    'application in process' => $res['APPLICATION_INPROCESS'],
                    'reject' => $res['REJECT'],
                    'disbursed ' => $res['DISBURSED'],
                    'dibursment %' => $res['DISBURSEMENT_PERCENTAGE']




                );
            }

            $export_header_array = array_keys($export_data_array[0]);

            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }


    public function exportCSVHighConversionTatReport($fromDate, $toDate) {


        $report_id = intval($this->input->post('report_id'));

        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $result = $this->Export_Model->exportCSVHighConversionTatReportModel($fromDate, $toDate);


        if (!empty($fromDate) && !empty($toDate)) {

            $report_data = $result->result_array();

            foreach ($report_data as $res) {

                if ($res['lead_is_mobile_verified'] == 1) {
                    $mobile_verified = 'Name Not Verified';
                } else {
                    $mobile_verified = 'OTP Not Verified';
                }

                $net_disbursal_amt = $res['sanction_loan_amount'] - $res['total_admin_fees'];
                $export_data_array[] = array(
                    'name' => $res['name'],
                    'total lead' => $res['TOTAL_LEAD'],
                    'lead in process' => $res['LEAD_INPROCESS'],
                    'lead hold' => $res['LEAD_HOLD'],
                    'application in process' => $res['APPLICATION_INPROCESS'],
                    'reject' => $res['REJECT'],
                    'disbursed ' => $res['DISBURSED'],
                    'dibursment %' => $res['DISBURSEMENT_PERCENTAGE']




                );
            }

            $export_header_array = array_keys($export_data_array[0]);

            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLeadInteractionSummaryReport($fromDate, $toDate) {
        $report_id = intval($this->input->post('report_id'));
        $file_name = array('report_name' => $report_id, 'fromdate' => $fromDate, 'toDate' => $toDate);

        $from = strtotime($fromDate);
        $to = strtotime($toDate); // or your date as well
        $datediff = $to - $from;

        if ((round($datediff / 86400) <= 60)) {
            $result = $this->Export_Model->exportLeadInteractionSummary($fromDate, $toDate);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">Date Range To Long.</strong>');
            redirect(base_url('exportData/'), 'refresh');
        }

        if (!empty($result->num_rows())) {
            $export_data_array = array();
            $i = 0;
            foreach ($result->result_array() as $res) {

                $export_data_array[$i] = array(
                    "Lead ID" => $res['lead_id'],
                    "Monthly Salary" => $res['monthly_salary_amount'],
                    "Current Status" => $res['status'],
                    "First Screener" => $res['first_assignment_user_name'],
                    "First Screener DateTime" => $res['first_assignment_date'],
                    "Screener" => $res['screener_user_name'],
                    "Screener DateTime" => $res['lead_screener_assign_datetime'],
                    "Credit Manager" => $res['credit_user_name'],
                    "Credit Manager DateTime" => $res['lead_credit_assign_datetime'],
                    "Credit Head" => $res['credithead_user_name'],
                    "Credit Head DateTime" => $res['lead_credithead_assign_datetime'],
                    "Audit Manager" => $res['audit_user_name'],
                    "Audit DateTime" => $res['lead_audit_assign_date_time'],
                    "Sanction By" => $res['SanctionBy'],
                    "Sanction DateTime" => $res['lead_credit_approve_datetime'],
                    "Disbursal Manager" => $res['disbursal_user_name'],
                    "Disbursal DateTime" => $res['lead_disbursal_assign_datetime'],
                    "Disbursal Head" => $res['disbursal_approve_user_name'],
                    "Disbursal Head DateTime" => $res['lead_disbursal_approve_datetime'],
                    "Disbursal Date" => $res['disbursal_date'],
                    "Repayment Date" => $res['repayment_date'],
                    "Loan Amount" => $res['loan_recommended'],
                    "Repayment Amount" => $res['repayment_amount'],
                    "UTM Source" => $res['utm_source'],
                    "UTM Campaign" => $res['utm_campaign'],
                    "Source" => $res['source'],
                    "User Type" => $res['user_type'],
                );
                $i++;
            }
            $export_header_array = array_keys($export_data_array[0]);
            $this->dataExport($export_header_array, $export_data_array, $file_name);
        } else {
            $this->session->set_flashdata('msg', '<strong style="color:red">No Records Found.</strong>');
            $this->index();
        }
    }
}
