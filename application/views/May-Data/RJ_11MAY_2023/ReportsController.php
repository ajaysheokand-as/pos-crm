<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ReportsController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_Model');
        $this->load->model('Task_Model', 'Tasks');
        ini_set('memory_limit', '1000M');
        set_time_limit(300);
        $login = new IsLogin();
        $login->index();
    }

    public function index()
    {
        $data['masterExport'] = $this->Report_Model->ExportMaster();
        $this->load->view('Export/mis_report', $data);
    }

    public function FilterMISReports()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $this->form_validation->set_rules('report_id', 'Report Type', 'trim');
            $this->form_validation->set_rules('from_date', 'From Date', 'trim');
            $this->form_validation->set_rules('to_date', 'To Date', 'trim');
            $this->form_validation->set_rules('financial_year', 'Financial Year', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url('exportData/'), 'refresh');
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $report_id = $this->input->post('report_id');
                $fromDate = $this->input->post('from_date');
                $toDate = $this->input->post('to_date');
                $month_data = $this->input->post('month_data');
                $financial_year = $this->input->post('financial_year');

                $insertApiLog = array();
                $insertApiLog["mal_mis_id"] = $report_id;
                $insertApiLog["mal_start_date"] = !empty($toDate) ? date('Y-m-d', strtotime($fromDate)) : NULL;
                $insertApiLog["mal_end_date"] = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : NULL;
                $insertApiLog["mal_user_id"] = $user_id;
                $insertApiLog["mal_created_on"] = date("Y-m-d H:i:s");
                $insertApiLog["mal_user_platform"] = $this->agent->platform();
                $insertApiLog["mal_user_browser"] = $this->agent->browser() . ' ' . $this->agent->version();
                $insertApiLog["mal_user_agent"] = $this->agent->agent_string();
                $insertApiLog["mal_user_ip"] = $this->input->ip_address();
                $insertApiLog["mal_user_role_id"] = $_SESSION['isUserSession']['user_role_id'];

                $this->db->insert("mis_access_logs", $insertApiLog);

                if ($report_id == 2) { //Export Data TAT                    
                    $this->sanctionTATReport($report_id);
                } else if ($report_id == 1) {
                    $this->LeadSource($fromDate, $toDate);
                } else if ($report_id == 3) {
                    $this->TotalSanctionReport($fromDate, $toDate);
                } else if ($report_id == 4) {
                    $this->SanctionKPIReport($report_id, $month_data);
                } else if ($report_id == 5) {
                    $this->CollectionPercentageReport($fromDate, $toDate);
                } else if ($report_id == 6) {
                    $this->DisbursalSummaryReport($month_data);
                } else if ($report_id == 7) {
                    $this->MonthwisePendingCollection($report_id);
                } else if ($report_id == 8) {
                    $this->LeadSourceStatus($fromDate, $toDate);
                } else if ($report_id == 9) {
                    $this->outstandingSanctionCaseReport($report_id, $month_data);
                } else if ($report_id == 10) {
                    $this->CollectionCallwithtimeReport($fromDate, $toDate);
                } else if ($report_id == 11) {
                    $this->UserTypeOutstandingReport($report_id, $month_data);
                } else if ($report_id == 12) {
                    $this->CollectionCallwithStatusReport($fromDate, $toDate);
                } else if ($report_id == 13) {
                    $this->MonthlyCollectionReport($report_id, $month_data);
                } else if ($report_id == 14) {
                    $this->MonthlyDisbursalReport($report_id, $month_data);
                } else if ($report_id == 15) {
                    $this->HourlyDisbursalReport($fromDate, $toDate);
                } else if ($report_id == 16) {
                    $this->BranchwiseVisitReport($fromDate, $toDate);
                } else if ($report_id == 17) {
                    $this->RMwiseVisitReport($fromDate, $toDate);
                } else if ($report_id == 18) {
                    $this->SanctionProductivityFreshReport($report_id, $fromDate);
                } else if ($report_id == 19) {
                    $this->SanctionProductivityRepeatReport($report_id, $fromDate);
                } else if ($report_id == 20) {
                    $this->RMConveyanceReport($fromDate, $toDate);
                } else if ($report_id == 21) {
                    $this->PaymentAnalysisReport($report_id, $financial_year);
                } else if ($report_id == 22) {
                    $this->DateWisePreCollectionReport($report_id, $month_data);
                } else if ($report_id == 23) {
                    $this->DateWiseCollectionReport($report_id, $month_data);
                } else if ($report_id == 24) {
                    $this->DateWiseRecoveryReport($report_id, $month_data);
                } else if ($report_id == 25) {
                    $this->LeadStatusSanctionWiseNewReport($fromDate, $toDate);
                } else if ($report_id == 26) {
                    $this->LeadStatusSanctionWiseRepeatReport($fromDate, $toDate);
                } else if ($report_id == 27) {
                    $this->HourlyStatusWiseReport($fromDate, $toDate);
                } else if ($report_id == 28) {
                    $this->LeadUTMSourceStatusReport($fromDate, $toDate);
                } else if ($report_id == 29) {
                    $this->outstandingSanctionAmountReport($report_id, $month_data);
                } else if ($report_id == 30) {
                    $this->CollectionBucketCaseWiseReport($fromDate, $toDate);
                } else if ($report_id == 31) {
                    $this->LeadSourcingCityWiseStatusReport($fromDate, $toDate);
                } else if ($report_id == 32) {
                    $this->LeadCityWiseStatusReport($fromDate, $toDate);
                } else if ($report_id == 33) {
                    $this->EMIPorfolioReportDisbursalReport($fromDate, $toDate);
                } else if ($report_id == 34) {
                    $this->FYdisbursementcollectionReport($report_id, $financial_year);
                } else if ($report_id == 35) {
                    $this->FYrepaymentcollectionReport($report_id, $financial_year);
                } else if ($report_id == 36) {
                    $this->OutstandingReportCasesDateRangeReport($fromDate, $toDate);
                } else if ($report_id == 37) {
                    $this->EMIPorfolioReportRepaymentReport($fromDate, $toDate);
                }
            }
        } else {
            $data['reportData'] = '<div class="alert alert-success alert-dismissible"><strong style="color:red; ">IP Address Changed.</strong></div>';
            echo json_encode($data);
        }
    }

    //////////////////----Report----////////////////////////////



    public function LeadSource($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->LeadSourceReport($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function sanctionTATReport($mis_report_id)
    {
        if (!empty($mis_report_id)) {
            $data['reportData'] = $this->Report_Model->SanctionTATReport($mis_report_id);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function outstandingReport($mis_report_id)
    {
        if (!empty($mis_report_id)) {
            $data['reportData'] = $this->Report_Model->outstandingReport($mis_report_id);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function TotalSanctionReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->TotalSanctionModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function SanctionKPIReport($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $data['reportData'] = $this->Report_Model->SanctionKPIModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function CollectionPercentageReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->TotalCollectionPercentageModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function CollectionMonthwiseSegregationReport($fromDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->CollectionMonthwiseReport($fromDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function DisbursalSummaryReport($month_data)
    {
        if (!empty($month_data)) {
            $data['reportData'] = $this->Report_Model->DisbursedReport($month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function MonthwisePendingCollection($report_id)
    {
        if (!empty($report_id)) {
            $data['reportData'] = $this->Report_Model->MonthwisePendingCollectionModel($report_id);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadSourceStatus($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->LeadSourceStatusModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function outstandingSanctionCaseReport($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $data['reportData'] = $this->Report_Model->OutstandingReportCasesModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function outstandingSanctionAmountReport($report_id, $month_data)
    {
        if (!empty($month_data)) {
            $data['reportData'] = $this->Report_Model->OutstandingReportAmountModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function CollectionCallwithtimeReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->CollectionCallwithtimeModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function CollectionCallwithStatusReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->CollectionCallwithStatusModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function UserTypeOutstandingReport($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $data['reportData'] = $this->Report_Model->UserTypeOutstandingModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function MonthlyCollectionReport($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $data['reportData'] = $this->Report_Model->MonthwiseCollectionModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function MonthlyDisbursalReport($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $data['reportData'] = $this->Report_Model->MonthwiseDisbursalModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function HourlyDisbursalReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->HourlyDisbursalModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function BranchwiseVisitReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->BranchwiseVisitModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function RMwiseVisitReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->RMwiseVisitModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function SanctionProductivityFreshReport($report_id, $fromDate)
    {
        if (!empty($report_id) && !empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->SanctionProductivityNew($report_id, $fromDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function SanctionProductivityRepeatReport($report_id, $fromDate)
    {
        if (!empty($report_id) && !empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->SanctionProductivityRepeat($report_id, $fromDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function RMConveyanceReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $data['reportData'] = $this->Report_Model->RMConveyanceModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function PaymentAnalysisReport($report_id, $financial_year)
    {
        if (!empty($report_id)) {
            $data['reportData'] = $this->Report_Model->PaymentAnalysis($report_id, $financial_year);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }
    public function DateWisePreCollectionReport($report_id, $month_data)
    {
        if (!empty($report_id)) {
            $data['reportData'] = $this->Report_Model->DateWisePreCollectionModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function DateWiseCollectionReport($report_id, $month_data)
    {
        if (!empty($report_id)) {
            $data['reportData'] = $this->Report_Model->DateWiseCollectionModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function DateWiseRecoveryReport($report_id, $month_data)
    {
        if (!empty($report_id)) {
            $data['reportData'] = $this->Report_Model->DateWiseRecoveryModel($report_id, $month_data);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadStatusSanctionWiseNewReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->LeadStatusSanctionWiseNewModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadStatusSanctionWiseRepeatReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->LeadStatusSanctionWiseRepeatModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function HourlyStatusWiseReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->HourlyStatusWiseModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadUTMSourceStatusReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->LeadUTMSourceStatusModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function CollectionBucketCaseWiseReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->CollectionBucketCaseWiseModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadSourcingCityWiseStatusReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->LeadSourcingCityWiseStatusModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function LeadCityWiseStatusReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->LeadCityWiseStatusModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function EMIPorfolioReportDisbursalReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->EMIPortfolioDisbursalModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function EMIPorfolioReportRepaymentReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->EMIPortfolioRepaymentModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function FYdisbursementcollectionReport($report_id, $financial_year)
    {
        if (!empty($financial_year)) {
            $data['reportData'] = $this->Report_Model->FYdisbursementcollectionModel($report_id, $financial_year);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function FYrepaymentcollectionReport($report_id, $financial_year)
    {
        if (!empty($financial_year)) {
            $data['reportData'] = $this->Report_Model->FYrepaymentcollectionModel($report_id, $financial_year);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }

    public function OutstandingReportCasesDateRangeReport($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $data['reportData'] = $this->Report_Model->OutstandingReportCasesDateRangeModel($fromDate, $toDate);
            echo json_encode($data);
        } else {
            $data['reportData'] = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            echo json_encode($data);
        }
    }
}
