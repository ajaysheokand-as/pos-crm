<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ThirdPartyAPIController extends CI_Controller {

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

    public function setSMSAnalyzer() {
        $lead_id = intval($this->encrypt->decode($_POST['lead_id']));
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "", "sal_log_id" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }
        if (!empty($lead_id)) {
            $get_details = $this->db->query('select customer_sms_analyse_access_flag,customer_sms_analyse_id from lead_customer where customer_lead_id = ' . $lead_id . ' AND customer_active = 1');
            $data = $get_details->row();
            if (!empty($data->customer_sms_analyse_access_flag) == '1') {
                $request_array['userId'] = $data->customer_sms_analyse_id;
            }
            //echo json_encode($data->customer_sms_analyse_id);die;			
            require_once(COMPONENT_PATH . "CommonComponent.php");
            $CommonComponent = new CommonComponent();
            $api_response = $CommonComponent->call_payday_sms_analyser('GET_SYNC_ID', $lead_id, $request_array);
            if (!empty($api_response['status']) && ($api_response['status'] == 1) && !empty($api_response['sal_sync_id'])) {
                $responseArray['success_msg'] = "Get sync id successfully....";
                $responseArray['sal_log_id'] = $api_response['log_id'];
            }
        }
        echo json_encode($responseArray);
    }

    public function getSyncId() {
        $lead_id = intval($this->encrypt->decode($_POST['lead_id']));
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "");
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }
        if (!empty($lead_id)) {
            $get_details = $this->db->query('SELECT * FROM `api_sms_analyser_log` where sal_lead_id =' . $lead_id . ' AND sal_method_id = 4 ORDER BY sal_log_id DESC LIMIT 1');
            if ($get_details->num_rows() > 0) {
                $data = $get_details->row();
                $sms_response = json_decode($data->sal_response_ur_encrpt, true);
                $GENERAL_VARIABLES = $sms_response['GENERAL VARIABLES'];
                $OVERALL = $sms_response['OVERALL'];
                $ENTITY = $sms_response['ENTITY'];
                $return_data .= '<table class="table table-bordered table-striped">';

                $return_data .= '<h4 style="font-size: 12px; font-weight:bold;">OVERALL</h4>';
                if (!empty($GENERAL_VARIABLES)) {
                    $return_data .= '<thead><tr>
						<th scope="col">DATA QUALITY SCORE (Phase 2)</th>
						<th scope="col">DELINQUENCY SCORE (Phase 3)</th>
						<th scope="col">FLAG SALARIED</th>
						<th scope="col">CHEQUE BOUNCED/RETURN (LAST 3 MONTHS)</th>
						<th scope="col">CASA ACCOUNTS</th>
						<th scope="col">AVERAGE MONTHLY CASH OUTFLOW (LAST 3 MONTHS)</th>
						<th scope="col">AVERAGE MONTHLY INFLOW(LAST 3 MONTHS)</th>
						<th scope="col">AVERAGE MONTHLY BALANCE (LAST 3 MONTHS)</th>
						<th scope="col">CREDIT CARDS</th>
						<th scope="col">AVG MONTHLY CC EXPENSE(3 MONTHS)</th>
						<th scope="col">MOBILE WALLETS</th>
						<th scope="col">WALLET REFILLED (LAST 3 MONTHS)</th>
						<th scope="col">AVG WALLET EXPENSE(3 MONTHS)</th>
						<th scope="col">UTILITIES</th>
						<th scope="col">AVG MONTHLY UTILITYEXPENSE(3 MONTHS)</th>
						<th scope="col">FLAG POSTPAID CONNECTION</th>
						<th scope="col">LOAN ACCOUNTS</th>
						<th scope="col">LOAN CLOSED(3 MONTHS)</th>
						<th scope="col">INSURANCE</th>
						<th scope="col">FLAG MUTUAL FUND/SIP</th>
						<th scope="col">FLAG DEPOSIT INVESTMENT</th>
						<th scope="col">AVERAGE INVESTMENT EXPENSE</th>
						<th scope="col">TDS DEDUCTION</th>
						<th scope="col">CURRENT EPF PASSBOOK BALANCE</th>
                    </tr></thead>';
                    $return_data .= '<tbody><tr>
						<td>' . (($GENERAL_VARIABLES['SCORE']) ? $GENERAL_VARIABLES['SCORE'] : '-') . '</td>
						<td>' . (($GENERAL_VARIABLES['SCORE_V2']) ? $GENERAL_VARIABLES['SCORE_V2'] : '-') . '</td>
						<td>-</td>
						<td>-</td>
						<td>3</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['CREDIT_CARD_SMS_COUNT']) ? $GENERAL_VARIABLES['CREDIT_CARD_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['WALLET_SMS_COUNT']) ? $GENERAL_VARIABLES['WALLET_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['UTILITY_SMS_COUNT']) ? $GENERAL_VARIABLES['UTILITY_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['LOAN_SMS_COUNT']) ? $GENERAL_VARIABLES['LOAN_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['INSURANCE_SMS_COUNT']) ? $GENERAL_VARIABLES['INSURANCE_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>' . (($GENERAL_VARIABLES['INVESTMENT_DEPOSIT_SMS_COUNT']) ? $GENERAL_VARIABLES['INVESTMENT_DEPOSIT_SMS_COUNT'] : '-') . '</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
					 </tr></tbody></table>';
                }
                $return_data .= '<h4 style="font-size: 12px; font-weight:bold;">OVER ALL</h4>';

                $static_arr = array(
                    "CASA" => array("amt_average_salary_credited",
                        "amt_avg_bank_balance",
                        "amt_avg_bank_balance_30_days",
                        "amt_avg_credit_per_transaction_180_days",
                        "amt_avg_credit_per_transaction_30_days",
                        "amt_avg_credit_per_transaction_360_days",
                        "amt_avg_credit_per_transaction_90_days",
                        "amt_avg_credit_per_transaction_lifetime",
                        "amt_avg_daily_closing_balance_180_days",
                        "amt_avg_daily_closing_balance_30_days",
                        "amt_avg_daily_closing_balance_360_days",
                        "amt_avg_daily_closing_balance_90_days",
                        "amt_avg_daily_closing_balance_lifetime",
                        "amt_avg_daily_credit_transactions_180_days",
                        "amt_avg_daily_credit_transactions_30_days",
                        "amt_avg_daily_credit_transactions_360_days",
                        "amt_avg_daily_credit_transactions_90_days",
                        "amt_avg_daily_credit_transactions_lifetime",
                        "amt_avg_daily_debit_card_tran_180_days",
                        "amt_avg_daily_debit_card_tran_30_days",
                        "amt_avg_daily_debit_card_tran_360_days",
                        "amt_avg_daily_debit_card_tran_90_days",
                        "amt_avg_daily_debit_card_tran_lifetime",
                        "amt_avg_daily_debit_transactions_180_days",
                        "amt_avg_daily_debit_transactions_30_days",
                        "amt_avg_daily_debit_transactions_360_days",
                        "amt_avg_daily_debit_transactions_90_days",
                        "amt_avg_daily_debit_transactions_lifetime",
                        "amt_avg_debit_card_tran_per_tran_180_days",
                        "amt_avg_debit_card_tran_per_tran_30_days",
                        "amt_avg_debit_card_tran_per_tran_360_days",
                        "amt_avg_debit_card_tran_per_tran_90_days",
                        "amt_avg_debit_card_tran_per_tran_lifetime",
                        "amt_avg_debit_per_transaction_180_days",
                        "amt_avg_debit_per_transaction_30_days",
                        "amt_avg_debit_per_transaction_360_days",
                        "amt_avg_debit_per_transaction_90_days",
                        "amt_avg_debit_per_transaction_lifetime",
                        "amt_avg_maximum_balance_per_month_180_days",
                        "amt_avg_maximum_balance_per_month_30_days",
                        "amt_avg_maximum_balance_per_month_360_days",
                        "amt_avg_maximum_balance_per_month_90_days",
                        "amt_avg_maximum_balance_per_month_lifetime",
                        "amt_avg_minimum_balance_per_month_180_days",
                        "amt_avg_minimum_balance_per_month_30_days",
                        "amt_avg_minimum_balance_per_month_360_days",
                        "amt_avg_minimum_balance_per_month_90_days",
                        "amt_avg_minimum_balance_per_month_lifetime",
                        "amt_avg_monthly_atm_withdrawal_180_days",
                        "amt_avg_monthly_atm_withdrawal_30_days",
                        "amt_avg_monthly_atm_withdrawal_360_days",
                        "amt_avg_monthly_atm_withdrawal_90_days",
                        "amt_avg_monthly_atm_withdrawal_lifetime",
                        "amt_avg_monthly_credit_transaction_180_days",
                        "amt_avg_monthly_credit_transaction_30_days",
                        "amt_avg_monthly_credit_transaction_360_days",
                        "amt_avg_monthly_credit_transaction_90_days",
                        "amt_avg_monthly_credit_transaction_lifetime",
                        "amt_avg_monthly_debit_card_tran_180_days",
                        "amt_avg_monthly_debit_card_tran_30_days",
                        "amt_avg_monthly_debit_card_tran_360_days",
                        "amt_avg_monthly_debit_card_tran_90_days",
                        "amt_avg_monthly_debit_card_tran_lifetime",
                        "amt_avg_monthly_debit_transaction_180_days",
                        "amt_avg_monthly_debit_transaction_30_days",
                        "amt_avg_monthly_debit_transaction_360_days",
                        "amt_avg_monthly_debit_transaction_90_days",
                        "amt_avg_monthly_debit_transaction_lifetime",
                        "amt_avg_surplus_per_month",
                        "amt_avg_withdrawal_per_tran_180_days",
                        "amt_avg_withdrawal_per_tran_30_days",
                        "amt_avg_withdrawal_per_tran_360_days",
                        "amt_avg_withdrawal_per_tran_90_days",
                        "amt_avg_withdrawal_per_tran_lifetime",
                        "amt_credit_transaction_180_days",
                        "amt_credit_transaction_30_days",
                        "amt_credit_transaction_360_days",
                        "amt_credit_transaction_90_days",
                        "amt_credit_transaction_lifetime",
                        "amt_cummulative_maximum_balance_180_days",
                        "amt_cummulative_maximum_balance_30_days",
                        "amt_cummulative_maximum_balance_360_days",
                        "amt_cummulative_maximum_balance_90_days",
                        "amt_cummulative_maximum_balance_lifetime",
                        "amt_cummulative_minimum_balance_180_days",
                        "amt_cummulative_minimum_balance_30_days",
                        "amt_cummulative_minimum_balance_360_days",
                        "amt_cummulative_minimum_balance_90_days",
                        "amt_cummulative_minimum_balance_lifetime",
                        "amt_debit_transaction_180_days",
                        "amt_debit_transaction_30_days",
                        "amt_debit_transaction_360_days",
                        "amt_debit_transaction_90_days",
                        "amt_debit_transaction_lifetime",
                        "amt_maximum_salary_credited",
                        "amt_minimum_salary_credited",
                        "amt_monthly_avg_balance_180_days",
                        "amt_monthly_avg_balance_30_days",
                        "amt_monthly_avg_balance_360_days",
                        "amt_monthly_avg_balance_90_days",
                        "amt_monthly_avg_balance_lifetime",
                        "amt_total_atm_trans_180_days",
                        "amt_total_atm_trans_30_days",
                        "amt_total_atm_trans_360_days",
                        "amt_total_atm_trans_90_days",
                        "amt_total_atm_trans_lifetime",
                        "amt_total_debit_card_tran_180_days",
                        "amt_total_debit_card_tran_30_days",
                        "amt_total_debit_card_tran_360_days",
                        "amt_total_debit_card_tran_90_days",
                        "amt_total_debit_card_tran_lifetime",
                        "cnt_atm_tran_180_days",
                        "cnt_atm_tran_30_days",
                        "cnt_atm_tran_360_days",
                        "cnt_atm_tran_90_days",
                        "cnt_atm_tran_lifetime",
                        "cnt_avg_daily_credit_transactions_180_days",
                        "cnt_avg_daily_credit_transactions_30_days",
                        "cnt_avg_daily_credit_transactions_360_days",
                        "cnt_avg_daily_credit_transactions_90_days",
                        "cnt_avg_daily_credit_transactions_lifetime",
                        "cnt_avg_daily_debit_card_tran_180_days",
                        "cnt_avg_daily_debit_card_tran_30_days",
                        "cnt_avg_daily_debit_card_tran_360_days",
                        "cnt_avg_daily_debit_card_tran_90_days",
                        "cnt_avg_daily_debit_card_tran_lifetime",
                        "cnt_avg_daily_debit_transactions_180_days",
                        "cnt_avg_daily_debit_transactions_30_days",
                        "cnt_avg_daily_debit_transactions_360_days",
                        "cnt_avg_daily_debit_transactions_90_days",
                        "cnt_avg_daily_debit_transactions_lifetime",
                        "cnt_below_mab_penalty_occurances_180_days",
                        "cnt_below_mab_penalty_occurances_30_days",
                        "cnt_below_mab_penalty_occurances_360_days",
                        "cnt_below_mab_penalty_occurances_90_days",
                        "cnt_below_mab_penalty_occurances_lifetime",
                        "cnt_below_qab_penalty_occurances_180_days",
                        "cnt_below_qab_penalty_occurances_30_days",
                        "cnt_below_qab_penalty_occurances_360_days",
                        "cnt_below_qab_penalty_occurances_90_days",
                        "cnt_below_qab_penalty_occurances_lifetime",
                        "cnt_casa_accounts",
                        "cnt_cheques_returned_180_days",
                        "cnt_cheques_returned_30_days",
                        "cnt_cheques_returned_360_days",
                        "cnt_cheques_returned_90_days",
                        "cnt_cheques_returned_insufficient_fund_180_days",
                        "cnt_cheques_returned_insufficient_fund_30_days",
                        "cnt_cheques_returned_insufficient_fund_360_days",
                        "cnt_cheques_returned_insufficient_fund_90_days",
                        "cnt_cheques_returned_insufficient_fund_lifetime",
                        "cnt_cheques_returned_lifetime",
                        "cnt_credit_applications_rejected_180_days",
                        "cnt_credit_applications_rejected_30_days",
                        "cnt_credit_applications_rejected_360_days",
                        "cnt_credit_applications_rejected_90_days",
                        "cnt_credit_applications_rejected_lifetime",
                        "cnt_credit_card_overlimit_occurances_180_days",
                        "cnt_credit_card_overlimit_occurances_30_days",
                        "cnt_credit_card_overlimit_occurances_360_days",
                        "cnt_credit_card_overlimit_occurances_90_days",
                        "cnt_credit_card_overlimit_occurances_lifetime",
                        "cnt_credit_transaction_180_days",
                        "cnt_credit_transaction_30_days",
                        "cnt_credit_transaction_360_days",
                        "cnt_credit_transaction_90_days",
                        "cnt_credit_transaction_lifetime",
                        "cnt_debit_card_tran_180_days",
                        "cnt_debit_card_tran_30_days",
                        "cnt_debit_card_tran_360_days",
                        "cnt_debit_card_tran_90_days",
                        "cnt_debit_card_tran_lifetime",
                        "cnt_debit_transaction_180_days",
                        "cnt_debit_transaction_30_days",
                        "cnt_debit_transaction_360_days",
                        "cnt_debit_transaction_90_days",
                        "cnt_debit_transaction_lifetime",
                        "cnt_neft_rtgs_imps_tran_180_days",
                        "cnt_neft_rtgs_imps_tran_30_days",
                        "cnt_neft_rtgs_imps_tran_360_days",
                        "cnt_neft_rtgs_imps_tran_90_days",
                        "cnt_neft_rtgs_imps_tran_lifetime",
                        "cnt_negative_events_180_days",
                        "cnt_number_of_days_of_bank_data",
                        "cnt_tran_declined_insufficient_fund_180_days",
                        "cnt_tran_declined_insufficient_fund_30_days",
                        "cnt_tran_declined_insufficient_fund_360_days",
                        "cnt_tran_declined_insufficient_fund_90_days",
                        "cnt_tran_declined_insufficient_fund_lifetime",
                        "cnt_unique_banks_having_casa_relationship_with",
                        "cnt_zero_neg_bal_180_days",
                        "cnt_zero_neg_bal_30_days",
                        "cnt_zero_neg_bal_360_days",
                        "cnt_zero_neg_bal_90_days",
                        "cnt_zero_neg_bal_lifetime",
                        "flag_cheque_returned",
                        "name_salary_account_bank",
                        "ratio_amt_avg_credit_per_tran_30_to_60_120_days",
                        "ratio_amt_avg_credit_per_tran_30_to_90_days",
                        "ratio_amt_avg_daily_closing_balance_30_days_to_60_120_days",
                        "ratio_amt_avg_daily_closing_balance_30_days_to_90_days",
                        "ratio_amt_avg_daily_credit_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_daily_credit_trans_30_days_to_90_days",
                        "ratio_amt_avg_daily_debit_card_tran_30_days_to_60_120_days",
                        "ratio_amt_avg_daily_debit_card_tran_30_days_to_90_days",
                        "ratio_amt_avg_daily_debit_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_daily_debit_trans_30_days_to_90_days",
                        "ratio_amt_avg_dbt_per_tran_30_to_60_120_days",
                        "ratio_amt_avg_dbt_per_tran_30_to_90_days",
                        "ratio_amt_avg_debit_card_trans_per_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_debit_card_trans_per_trans_30_days_to_90_days",
                        "ratio_amt_avg_maximum_balance_per_month_30_days_to_60_120_days",
                        "ratio_amt_avg_maximum_balance_per_month_30_days_to_90_days",
                        "ratio_amt_avg_minimum_balance_per_month_30_days_to_60_120_days",
                        "ratio_amt_avg_minimum_balance_per_month_30_days_to_90_days",
                        "ratio_amt_avg_monthly_atm_withdrawal_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_atm_withdrawal_30_days_to_90_days",
                        "ratio_amt_avg_monthly_credit_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_credit_trans_30_days_to_90_days",
                        "ratio_amt_avg_monthly_debit_card_tran_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_debit_card_tran_30_days_to_90_days",
                        "ratio_amt_avg_monthly_debit_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_debit_trans_30_days_to_90_days",
                        "ratio_amt_avg_withdrawal_per_tran_30_days_60_120_days",
                        "ratio_amt_avg_withdrawal_per_tran_30_days_90_days",
                        "ratio_amt_monthly_avg_balance_30_days_60_120_days",
                        "ratio_amt_monthly_avg_balance_30_days_90_days",
                        "ratio_avg_monthly_atm_trans_to_spend_lifetime",
                        "ratio_avg_monthly_atm_trans_to_spend_per_month_180_days",
                        "ratio_avg_monthly_atm_trans_to_spend_per_month_30_days",
                        "ratio_avg_monthly_atm_trans_to_spend_per_month_360_days",
                        "ratio_avg_monthly_atm_trans_to_spend_per_month_90_days",
                        "ratio_avg_monthly_debit_to_credit_amount_180_days",
                        "ratio_avg_monthly_debit_to_credit_amount_30_days",
                        "ratio_avg_monthly_debit_to_credit_amount_360_days",
                        "ratio_avg_monthly_debit_to_credit_amount_90_days",
                        "ratio_avg_monthly_debit_to_credit_amount_lifetime",
                        "ratio_cnt_avg_daily_credit_trans_30_days_to_60_120_days",
                        "ratio_cnt_avg_daily_credit_trans_30_days_to_90_days",
                        "ratio_cnt_avg_daily_debit_card_tran_30_days_to_60_120_days",
                        "ratio_cnt_avg_daily_debit_card_tran_30_days_to_90_days",
                        "ratio_cnt_avg_daily_debit_trans_30_days_to_60_120_days",
                        "ratio_cnt_avg_daily_debit_trans_30_days_to_90_days",
                        "ratio_cnt_cheques_returned_30_days_to_90_days",
                        "ratio_cnt_cheques_returned_30_to_60_120_days",
                        "ratio_of_ratio_avg_mon_dbt_to_crdt_amt_30d_to_60d_120d",
                        "ratio_of_ratio_avg_mon_dbt_to_crdt_amt_30d_to_90d",
                        "ratio_total_debit_to_credit_amount_180_days",
                        "ratio_total_debit_to_credit_amount_30_days",
                        "ratio_total_debit_to_credit_amount_360_days",
                        "ratio_total_debit_to_credit_amount_90_days",
                        "ratio_total_debit_to_credit_amount_lifetime",
                        "salary_account_bank_number",
                        "time_since_last_negative_event",
                        "amt_average_salary_credited_lst_3mon",
                        "max_average_salary_credited_lst_3mon",
                        "amt_average_salary_credited_lst_6mon",
                        "max_average_salary_credited_lst_6mon",
                        "avg_amt_monthly_recurring_debit_6mon",
                        "avg_amt_monthly_recurring_credit_6mon",
                        "amt_self_trf_lifetime",
                        "amt_self_trf_360_days",
                        "amt_self_trf_180_days",
                        "amt_self_trf_90_days",
                        "amt_self_trf_30_days",
                        "amt_avg_monthly_credit_transaction_lifetime_nostrf",
                        "amt_avg_monthly_credit_transaction_360_days_nostrf",
                        "amt_avg_monthly_credit_transaction_180_days_nostrf",
                        "amt_avg_monthly_credit_transaction_90_days_nostrf",
                        "amt_avg_monthly_credit_transaction_30_days_nostrf",
                        "avg_amt_monthly_recurring_debit_3mon",
                        "avg_amt_monthly_recurring_credit_3mon",
                        "monthly_amt_credit_m0",
                        "monthly_amt_credit_m1",
                        "monthly_amt_credit_m2",
                        "monthly_amt_credit_m3",
                        "monthly_amt_credit_m4",
                        "monthly_amt_credit_m5",
                        "monthly_amt_credit_m6",
                        "monthly_amt_debit_m0",
                        "monthly_amt_debit_m1",
                        "monthly_amt_debit_m2",
                        "monthly_amt_debit_m3",
                        "monthly_amt_debit_m4",
                        "monthly_amt_debit_m5",
                        "monthly_amt_debit_m6",
                        "monthly_amt_credit_wo_self_transfer_m0",
                        "monthly_amt_credit_wo_self_transfer_m1",
                        "monthly_amt_credit_wo_self_transfer_m2",
                        "monthly_amt_credit_wo_self_transfer_m3",
                        "monthly_amt_credit_wo_self_transfer_m4",
                        "monthly_amt_credit_wo_self_transfer_m5",
                        "monthly_amt_credit_wo_self_transfer_m6",
                        "monthly_amt_debit_wo_self_transfer_m0",
                        "monthly_amt_debit_wo_self_transfer_m1",
                        "monthly_amt_debit_wo_self_transfer_m2",
                        "monthly_amt_debit_wo_self_transfer_m3",
                        "monthly_amt_debit_wo_self_transfer_m4",
                        "monthly_amt_debit_wo_self_transfer_m5",
                        "monthly_amt_debit_wo_self_transfer_m6",
                        "monthly_salary_credit_m0",
                        "monthly_salary_credit_m1",
                        "monthly_salary_credit_m2",
                        "monthly_salary_credit_m3",
                        "monthly_salary_credit_m4",
                        "monthly_salary_credit_m5",
                        "monthly_salary_credit_m6",
                        "monthly_only_salary_credit_m0",
                        "monthly_only_salary_credit_m1",
                        "monthly_only_salary_credit_m2",
                        "monthly_only_salary_credit_m3",
                        "monthly_only_salary_credit_m4",
                        "monthly_only_salary_credit_m5",
                        "monthly_only_salary_credit_m6",
                        "monthly_cash_withdrawal_m0",
                        "monthly_cash_withdrawal_m1",
                        "monthly_cash_withdrawal_m2",
                        "monthly_cash_withdrawal_m3",
                        "monthly_cash_withdrawal_m4",
                        "monthly_cash_withdrawal_m5",
                        "monthly_cash_withdrawal_m6",
                        "cnt_bounce_30_days",
                        "cnt_bounce_90_days",
                        "cnt_bounce_180_days",
                        "cnt_bounce_lifetime",
                        "flag_salary_present_3_months",
                        "flag_salary_credit_m1",
                        "amt_avg_sal_credited_lst_6mon_exc_sync_month",
                        "flag_bank_balance_present_4_months",
                        "flag_bank_balance_m1",
                        "amt_avg_bank_balance_m0",
                        "amt_avg_bank_balance_m1",
                        "amt_avg_bank_balance_m2",
                        "amt_avg_bank_balance_m3",
                        "amt_avg_bank_balance_m4",
                        "amt_avg_bank_balance_m5",
                        "amt_avg_bank_balance_m6",
                        "amt_avg_bank_bal_lst_6mon_exc_sync_month"),
                    "CREDIT CARD" => array(
                        "allocated_cr_limit",
                        "amt_avg_mon_cc_trans_180_days",
                        "amt_avg_mon_cc_trans_30_days",
                        "amt_avg_mon_cc_trans_360_days",
                        "amt_avg_mon_cc_trans_90_days",
                        "amt_avg_mon_cc_trans_lifetime",
                        "amt_avg_per_cc_trans_180_days",
                        "amt_avg_per_cc_trans_30_days",
                        "amt_avg_per_cc_trans_360_days",
                        "amt_avg_per_cc_trans_90_days",
                        "amt_avg_per_cc_trans_lifetime",
                        "amt_cc_trans_180_days",
                        "amt_cc_trans_30_days",
                        "amt_cc_trans_360_days",
                        "amt_cc_trans_7_days",
                        "amt_cc_trans_90_days",
                        "amt_cc_trans_lifetime",
                        "avg_cr_util_per_mon_180_days",
                        "avg_cr_util_per_mon_30_days",
                        "avg_cr_util_per_mon_360_days",
                        "avg_cr_util_per_mon_90_days",
                        "avg_cr_util_per_mon_lifetime",
                        "cnt_avg_mon_cc_trans_180_days",
                        "cnt_avg_mon_cc_trans_30_days",
                        "cnt_avg_mon_cc_trans_360_days",
                        "cnt_avg_mon_cc_trans_90_days",
                        "cnt_avg_mon_cc_trans_lifetime",
                        "cnt_cc",
                        "cnt_cc_applied_30_days",
                        "cnt_cc_trans_180_days",
                        "cnt_cc_trans_30_days",
                        "cnt_cc_trans_360_days",
                        "cnt_cc_trans_90_days",
                        "cnt_cc_trans_lifetime",
                        "cnt_neg_cc_trans_180_days",
                        "cnt_neg_cc_trans_30_days",
                        "cnt_neg_cc_trans_360_days",
                        "cnt_neg_cc_trans_90_days",
                        "cnt_neg_cc_trans_lifetime",
                        "cnt_unique_banks_cc",
                        "flag_overlimit_on_cc",
                        "missed_cc_bill_payment_180_days",
                        "missed_cc_bill_payment_30_days",
                        "missed_cc_bill_payment_360_days",
                        "missed_cc_bill_payment_90_days",
                        "missed_cc_bill_payment_lifetime",
                        "perc_mon_360_days_payment_eqt_amt_due",
                        "perc_mon_360_days_payment_grt_amt_due",
                        "perc_mon_360_days_payment_grt_or_eqt_min_amt_due_and_lt_amt_due",
                        "perc_mon_360_days_payment_lt_min_amt_due",
                        "trans_decl_overlimit_180_days",
                        "ratio_amt_avg_mon_cc_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_mon_cc_trans_30_days_to_90_days",
                        "ratio_amt_avg_mon_cc_trans_7_days_to_8_14_days",
                        "ratio_amt_avg_per_cc_trans_30_days_to_60_120_days",
                        "ratio_amt_avg_per_cc_trans_30_days_to_90_days",
                        "ratio_amt_avg_per_cc_trans_7_days_to_8_14_days",
                        "ratio_avg_cc_dbt_amt_to_cr_amt_180_days",
                        "ratio_avg_cc_dbt_amt_to_cr_amt_30_days",
                        "ratio_avg_cc_dbt_amt_to_cr_amt_360_days",
                        "ratio_avg_cc_dbt_amt_to_cr_amt_90_days",
                        "ratio_avg_cc_dbt_amt_to_cr_amt_lifetime",
                        "ratio_avg_cr_util_per_mon_30_days_to_60_120_days",
                        "ratio_avg_cr_util_per_mon_30_days_to_90_days",
                        "ratio_cnt_avg_mon_cc_trans_30_days_to_60_120_days",
                        "ratio_cnt_avg_mon_cc_trans_30_days_to_90_days",
                        "ratio_trans_decl_overlimit_30_days_to_60_120_days",
                        "ratio_trans_decl_overlimit_30_days_to_90_days",
                        "trans_decl_overlimit_180_days",
                        "trans_decl_overlimit_30_days",
                        "trans_decl_overlimit_360_days",
                        "trans_decl_overlimit_90_days",
                        "trans_decl_overlimit_lifetime",
                        "pre_approved_cc",
                        "cnt_credit_applications_rejected_180_days",
                        "cnt_credit_applications_rejected_30_days",
                        "cnt_credit_applications_rejected_360_days",
                        "cnt_credit_applications_rejected_90_days",
                        "cnt_credit_applications_rejected_lifetime"
                    ),
                    "INSURANCE" => array(
                        "amt_life_insurance_coverage",
                        "amt_medical_insurance_coverage",
                        "amt_other_insurance_coverage",
                        "amt_property_insurance_coverage",
                        "amt_vehicle_insurance_coverage",
                        "avg_amt_ins_payment_180_days",
                        "avg_amt_ins_payment_30_days",
                        "avg_amt_ins_payment_360_days",
                        "avg_amt_ins_payment_90_days",
                        "avg_amt_ins_payment_lifetime",
                        "avg_amt_missed_insurance_payment_180_days",
                        "avg_amt_missed_insurance_payment_30_days",
                        "avg_amt_missed_insurance_payment_360_days",
                        "avg_amt_missed_insurance_payment_90_days",
                        "cnt_active_insurance",
                        "cnt_insurance",
                        "cnt_insurance_exc_vhcl",
                        "cnt_missed_insurance_payment_180_days",
                        "cnt_missed_insurance_payment_30_days",
                        "cnt_missed_insurance_payment_360_days",
                        "cnt_missed_insurance_payment_90_days",
                        "cnt_unique_insurance_type",
                        "cnt_unique_insurance_type_exc_vhcl",
                        "flag_insur_paid_and_no_default",
                        "flag_insurance_claim",
                        "flag_life_insurance",
                        "flag_medical_insurance",
                        "flag_missed_insurance_payments",
                        "flag_other_insurance",
                        "flag_property_insurance",
                        "flag_term_insurance",
                        "flag_vehicle_insurance"
                    ),
                    "LOAN" => array(
                        "amt_avg_current_loan_liability",
                        "amt_avg_missed_payments_180_days",
                        "amt_avg_missed_payments_30_days",
                        "amt_avg_missed_payments_360_days",
                        "amt_avg_missed_payments_90_days",
                        "amt_avg_missed_payments_lifetime",
                        "amt_avg_monthly_loan_liability_180_days",
                        "amt_avg_monthly_loan_liability_30_days",
                        "amt_avg_monthly_loan_liability_360_days",
                        "amt_avg_monthly_loan_liability_90_days",
                        "amt_avg_monthly_loan_liability_lifetime",
                        "amt_loan_disbursement_180_days",
                        "amt_loan_disbursement_30_days",
                        "amt_loan_disbursement_360_days",
                        "amt_loan_disbursement_90_days",
                        "amt_loan_disbursement_lifetime",
                        "amt_recent_default",
                        "avg_amt_loan_disbursement_180_days",
                        "avg_amt_loan_disbursement_30_days",
                        "avg_amt_loan_disbursement_360_days",
                        "avg_amt_loan_disbursement_90_days",
                        "avg_amt_loan_disbursement_lifetime",
                        "avg_amt_loan_due_ahead_15_days",
                        "avg_amt_loan_due_ahead_30_days",
                        "avg_amt_loan_due_ahead_7_days",
                        "avg_amt_loan_payment_180_days",
                        "avg_amt_loan_payment_30_days",
                        "avg_amt_loan_payment_360_days",
                        "avg_amt_loan_payment_90_days",
                        "avg_amt_loan_payment_lifetime",
                        "cnt_competitor_apps",
                        "cnt_competitor_default",
                        "cnt_competitor_loan_ac",
                        "cnt_loan_accounts_180_days",
                        "cnt_loan_accounts_30_days",
                        "cnt_loan_accounts_360_days",
                        "cnt_loan_accounts_90_days",
                        "cnt_loan_accounts_lifetime",
                        "cnt_loan_closed_180_days",
                        "cnt_loan_closed_30_days",
                        "cnt_loan_closed_360_days",
                        "cnt_loan_closed_90_days",
                        "cnt_loan_closed_lifetime",
                        "cnt_loan_disbursed_180_days",
                        "cnt_loan_disbursed_30_days",
                        "cnt_loan_disbursed_360_days",
                        "cnt_loan_disbursed_90_days",
                        "cnt_loan_disbursed_lifetime",
                        "cnt_loan_payments_missed_180_days",
                        "cnt_loan_payments_missed_30_days",
                        "cnt_loan_payments_missed_360_days",
                        "cnt_loan_payments_missed_90_days",
                        "cnt_loan_payments_missed_lifetime",
                        "cnt_negative_events_6_months",
                        "cnt_unique_senders_loan_due_ahead_15_days",
                        "cnt_unique_senders_loan_due_ahead_30_days",
                        "cnt_unique_senders_loan_due_ahead_7_days",
                        "cnt_unique_senders_loan_due_future",
                        "count_fin_loan_apps",
                        "count_unique_loan_sender_default",
                        "date_time_since_negative_event",
                        "days_since_recent_default",
                        "flag_auto_loan",
                        "flag_auto_loan_and_negative",
                        "flag_bounced",
                        "flag_business_loan",
                        "flag_default_lst_15_days",
                        "flag_default_lst_30_days",
                        "flag_default_lst_30d_with_due_ahead_30d",
                        "flag_default_lst_7_days",
                        "flag_education_loan",
                        "flag_gold_loan",
                        "flag_gold_loan_and_negative",
                        "flag_home_loan",
                        "flag_home_loan_and_no_negative",
                        "flag_legal_notice_alert",
                        "flag_missed_loan_emis",
                        "flag_no_default_and_no_due_ahead",
                        "flag_other_loan",
                        "flag_personal_loan",
                        "pct_competitor_default",
                        "pct_unique_loan_sender_default",
                        "ratio_amt_avg_monthly_loan_liability_90_days_to_180_days",
                        "ratio_cnt_loan_accounts_90_days_to_180_days",
                        "cnt_loan_applications_made_180_days",
                        "cnt_loan_applications_rejected_180_days",
                        "pre_approved_loan",
                        "cnt_pl_loan_applications_made_90_days",
                        "cnt_stpl_loan_applications_made_90_days",
                        "latest_emi_obligation"),
                    "INVESTMENT DEPOSIT" => array(
                        "amt_avg_invested_nps",
                        "amt_avg_monthly_mutual_fund_1_months",
                        "amt_avg_monthly_mutual_fund_3_months",
                        "amt_avg_monthly_mutual_fund_6_months",
                        "amt_avg_monthly_mutual_fund_lifetime",
                        "amt_deposit_invest_1_months",
                        "amt_deposit_invest_3_months",
                        "amt_deposit_invest_6_months",
                        "amt_deposit_invest_lifetime",
                        "amt_epf_1_months",
                        "amt_epf_3_months",
                        "amt_epf_lifetime",
                        "amt_invstmnt_expense_180_days",
                        "amt_invstmnt_expense_30_days",
                        "amt_invstmnt_expense_360_days",
                        "amt_invstmnt_expense_90_days",
                        "amt_invstmnt_expense_lifetime",
                        "amt_invstmnt_income_180_days",
                        "amt_invstmnt_income_30_days",
                        "amt_invstmnt_income_360_days",
                        "amt_invstmnt_income_90_days",
                        "amt_invstmnt_income_lifetime",
                        "amt_mutual_fund_12_months",
                        "amt_mutual_fund_1_months",
                        "amt_mutual_fund_3_months",
                        "amt_mutual_fund_6_months",
                        "amt_mutual_fund_lifetime",
                        "amt_redemption_transactions_mutual_fund_1_months",
                        "amt_redemption_transactions_mutual_fund_3_months",
                        "amt_redemption_transactions_mutual_fund_6_months",
                        "amt_redemption_transactions_mutual_fund_lifetime",
                        "amt_sum_all_accounts_latest_invested_nps",
                        "avg_amt_deposit_invest_1_months",
                        "avg_amt_deposit_invest_3_months",
                        "avg_amt_deposit_invest_6_months",
                        "avg_amt_deposit_invest_lifetime",
                        "avg_amt_invstmnt_expense_180_days",
                        "avg_amt_invstmnt_expense_30_days",
                        "avg_amt_invstmnt_expense_360_days",
                        "avg_amt_invstmnt_expense_90_days",
                        "avg_amt_invstmnt_expense_lifetime",
                        "avg_amt_invstmnt_income_180_days",
                        "avg_amt_invstmnt_income_30_days",
                        "avg_amt_invstmnt_income_360_days",
                        "avg_amt_invstmnt_income_90_days",
                        "avg_amt_invstmnt_income_lifetime",
                        "avg_amt_mutual_fund_1_months",
                        "avg_amt_mutual_fund_3_months",
                        "avg_amt_mutual_fund_6_months",
                        "avg_amt_mutual_fund_lifetime",
                        "cnt_accounts_nps",
                        "cnt_deposit_booked_1_months",
                        "cnt_deposit_booked_3_months",
                        "cnt_deposit_booked_6_months",
                        "cnt_deposit_booked_lifetime",
                        "cnt_epf_account",
                        "cnt_fixed_deposit_booked_1_months",
                        "cnt_fixed_deposit_booked_3_months",
                        "cnt_fixed_deposit_booked_6_months",
                        "cnt_fixed_deposit_booked_lifetime",
                        "cnt_negative_events_mutual_fund_1_months",
                        "cnt_negative_events_mutual_fund_3_months",
                        "cnt_negative_events_mutual_fund_6_months",
                        "cnt_negative_events_mutual_fund_lifetime",
                        "cnt_recurring_deposit_booked_1_months",
                        "cnt_recurring_deposit_booked_3_months",
                        "cnt_recurring_deposit_booked_6_months",
                        "cnt_recurring_deposit_booked_lifetime",
                        "cnt_redemption_transactions_mutual_fund_1_months",
                        "cnt_redemption_transactions_mutual_fund_3_months",
                        "cnt_redemption_transactions_mutual_fund_6_months",
                        "cnt_redemption_transactions_mutual_fund_lifetime",
                        "cnt_sip_accounts",
                        "cnt_term_deposit_booked_1_months",
                        "cnt_term_deposit_booked_3_months",
                        "cnt_term_deposit_booked_6_months",
                        "cnt_term_deposit_booked_lifetime",
                        "cnt_transactions_mutual_fund_1_months",
                        "cnt_transactions_mutual_fund_3_months",
                        "cnt_transactions_mutual_fund_6_months",
                        "cnt_transactions_mutual_fund_lifetime",
                        "curr_epf_passbook_balance",
                        "flag_crypto",
                        "flag_dep_acct",
                        "flag_dep_sum",
                        "flag_epf_account",
                        "flag_fixed_dep_acct",
                        "flag_mf_account",
                        "flag_mf_non_sip",
                        "flag_mf_sip",
                        "flag_neg_event_nps",
                        "flag_nps_account",
                        "flag_recurring_dep_acct",
                        "flag_term_dep_acct",
                        "flag_trading",
                        "sum_flag_diff_inv_category",
                        "pf_savings_m1",
                        "pf_savings_m2",
                        "pf_savings_m3",
                        "pf_savings_m4",
                        "pf_savings_m5",
                        "pf_savings_m6",
                        "mutual_fund_savings_m0",
                        "mutual_fund_savings_m1",
                        "mutual_fund_savings_m2",
                        "mutual_fund_savings_m3",
                        "mutual_fund_savings_m4",
                        "mutual_fund_savings_m5",
                        "mutual_fund_savings_m6"),
                    "ITR" => array('amt_cummulative_tds_latest_quarter_current_year', 'flag_income_tax_intimation_last_year', 'flag_itr_file_last_year', 'flag_itr_refund_last_year', 'max_amt_cummulative_tds_lifetime'),
                    "WALLET" => array(
                        "amt_avg_total_credit_transactions_1_months",
                        "amt_avg_total_credit_transactions_3_months",
                        "amt_avg_total_credit_transactions_6_months",
                        "amt_avg_total_debit_transactions_1_months",
                        "amt_avg_total_debit_transactions_3_months",
                        "amt_avg_total_debit_transactions_6_months",
                        "amt_bills_paid_180_days",
                        "amt_bills_paid_30_days",
                        "amt_bills_paid_360_days",
                        "amt_bills_paid_90_days",
                        "amt_bills_paid_lifetime",
                        "amt_money_transfers_credit",
                        "amt_total_credit_transactions",
                        "amt_total_credit_transactions_180_days",
                        "amt_total_credit_transactions_30_days",
                        "amt_total_credit_transactions_360_days",
                        "amt_total_credit_transactions_90_days",
                        "amt_total_debit_transactions",
                        "amt_total_debit_transactions_180_days",
                        "amt_total_debit_transactions_30_days",
                        "amt_total_debit_transactions_360_days",
                        "amt_total_debit_transactions_90_days",
                        "amt_total_wallet_topup_180_days",
                        "amt_total_wallet_topup_30_days",
                        "amt_total_wallet_topup_360_days",
                        "amt_total_wallet_topup_90_days",
                        "amt_total_wallet_topup_lifetime",
                        "cnt_bills_due_180_days",
                        "cnt_bills_due_30_days",
                        "cnt_bills_due_360_days",
                        "cnt_bills_due_90_days",
                        "cnt_bills_paid_180_days",
                        "cnt_bills_paid_30_days",
                        "cnt_bills_paid_360_days",
                        "cnt_bills_paid_90_days",
                        "cnt_bills_paid_lifetime",
                        "cnt_mobile_wallets",
                        "cnt_money_transfers_credit",
                        "cnt_total_credit_transactions",
                        "cnt_total_credit_transactions_180_days",
                        "cnt_total_credit_transactions_30_days",
                        "cnt_total_credit_transactions_360_days",
                        "cnt_total_credit_transactions_90_days",
                        "cnt_total_debit_transactions",
                        "cnt_total_debit_transactions_180_days",
                        "cnt_total_debit_transactions_30_days",
                        "cnt_total_debit_transactions_360_days",
                        "cnt_total_debit_transactions_90_days",
                        "cnt_wallet_topup_180_days",
                        "cnt_wallet_topup_30_days",
                        "cnt_wallet_topup_360_days",
                        "cnt_wallet_topup_90_days",
                        "cnt_wallet_topup_lifetime",
                        "percent_bill_pay_from_total_wallet_dbt_trans_1_months",
                        "percent_bill_pay_from_total_wallet_dbt_trans_3_months",
                        "percent_bill_pay_from_total_wallet_dbt_trans_6_months"
                    ),
                    "UTILITY" => array(
                        "amt_avg_monthly_broadband_fixed_line_bill_180_days",
                        "amt_avg_monthly_broadband_fixed_line_bill_30_days",
                        "amt_avg_monthly_broadband_fixed_line_bill_360_days",
                        "amt_avg_monthly_broadband_fixed_line_bill_90_days",
                        "amt_avg_monthly_broadband_fixed_line_bill_lifetime",
                        "amt_avg_monthly_dth_bill_180_days",
                        "amt_avg_monthly_dth_bill_30_days",
                        "amt_avg_monthly_dth_bill_360_days",
                        "amt_avg_monthly_dth_bill_90_days",
                        "amt_avg_monthly_dth_bill_lifetime",
                        "amt_avg_monthly_electricity_bill_180_days",
                        "amt_avg_monthly_electricity_bill_30_days",
                        "amt_avg_monthly_electricity_bill_360_days",
                        "amt_avg_monthly_electricity_bill_90_days",
                        "amt_avg_monthly_electricity_bill_lifetime",
                        "amt_avg_monthly_gas_bill_180_days",
                        "amt_avg_monthly_gas_bill_30_days",
                        "amt_avg_monthly_gas_bill_360_days",
                        "amt_avg_monthly_gas_bill_90_days",
                        "amt_avg_monthly_gas_bill_lifetime",
                        "amt_avg_monthly_other_utilities_bill_180_days",
                        "amt_avg_monthly_other_utilities_bill_30_dayss",
                        "amt_avg_monthly_other_utilities_bill_360_days",
                        "amt_avg_monthly_other_utilities_bill_90_days",
                        "amt_avg_monthly_other_utilities_bill_lifetime",
                        "amt_avg_monthly_postpaid_bill_180_days",
                        "amt_avg_monthly_postpaid_bill_30_days",
                        "amt_avg_monthly_postpaid_bill_360_days",
                        "amt_avg_monthly_postpaid_bill_90_days",
                        "amt_avg_monthly_postpaid_bill_lifetime",
                        "amt_avg_monthly_recharge_180_days",
                        "amt_avg_monthly_recharge_30_days",
                        "amt_avg_monthly_recharge_360_days",
                        "amt_avg_monthly_recharge_90_days",
                        "amt_avg_monthly_recharge_lifetime",
                        "amt_avg_monthly_util_bills_180_days",
                        "amt_avg_monthly_util_bills_30_days",
                        "amt_avg_monthly_util_bills_360_days",
                        "amt_avg_monthly_util_bills_90_days",
                        "amt_avg_monthly_util_bills_lifetime",
                        "amt_broadband_fixed_line_bill_180_days",
                        "amt_broadband_fixed_line_bill_30_days",
                        "amt_broadband_fixed_line_bill_360_days",
                        "amt_broadband_fixed_line_bill_90_days",
                        "amt_broadband_fixed_line_bill_lifetime",
                        "amt_dth_bill_180_days",
                        "amt_dth_bill_30_days",
                        "amt_dth_bill_360_days",
                        "amt_dth_bill_90_days",
                        "amt_dth_bill_lifetime",
                        "amt_electricity_bill_180_days",
                        "amt_electricity_bill_30_days",
                        "amt_electricity_bill_360_days",
                        "amt_electricity_bill_90_days",
                        "amt_electricity_bill_lifetime",
                        "amt_gas_bill_180_days",
                        "amt_gas_bill_30_days",
                        "amt_gas_bill_360_days",
                        "amt_gas_bill_90_days",
                        "amt_gas_bill_lifetime",
                        "amt_other_utilities_bill_180_days",
                        "amt_other_utilities_bill_30_days",
                        "amt_other_utilities_bill_360_days",
                        "amt_other_utilities_bill_90_days",
                        "amt_other_utilities_bill_lifetime",
                        "amt_postpaid_bill_180_days",
                        "amt_postpaid_bill_30_days",
                        "amt_postpaid_bill_360_days",
                        "amt_postpaid_bill_90_days",
                        "amt_postpaid_bill_lifetime",
                        "amt_total_recharges_120_150_days",
                        "amt_total_recharges_150_180_days",
                        "amt_total_recharges_180_days",
                        "amt_total_recharges_30_60_days",
                        "amt_total_recharges_30_days",
                        "amt_total_recharges_360_days",
                        "amt_total_recharges_60_90_days",
                        "amt_total_recharges_90_120_days",
                        "amt_total_recharges_90_days",
                        "amt_total_recharges_lifetime",
                        "amt_util_bills_180_days",
                        "amt_util_bills_30_days",
                        "amt_util_bills_360_days",
                        "amt_util_bills_90_days",
                        "amt_util_bills_lifetime",
                        "cnt_broadband_telephone_connections",
                        "cnt_postpaid_bill_180_days",
                        "cnt_postpaid_bill_30_days",
                        "cnt_postpaid_bill_360_days",
                        "cnt_postpaid_bill_90_days",
                        "cnt_postpaid_bill_lifetime",
                        "cnt_postpaid_connections",
                        "cnt_prepaid_connections",
                        "cnt_recharges_120_150_days",
                        "cnt_recharges_150_180_days",
                        "cnt_recharges_180_days",
                        "cnt_recharges_30_60_days",
                        "cnt_recharges_30_days",
                        "cnt_recharges_360_days",
                        "cnt_recharges_60_90_days",
                        "cnt_recharges_90_120_days",
                        "cnt_recharges_90_days",
                        "cnt_recharges_lifetime",
                        "cnt_recharges_with_amt_0_50",
                        "cnt_recharges_with_amt_0_50_in_30_60_days",
                        "cnt_recharges_with_amt_0_50_in_30_days",
                        "cnt_recharges_with_amt_0_50_in_60_90_days",
                        "cnt_recharges_with_amt_0_50_in_60_days",
                        "cnt_recharges_with_amt_0_50_in_90_days",
                        "cnt_recharges_with_amt_0_50_lifetime",
                        "cnt_recharges_with_amt_100_200",
                        "cnt_recharges_with_amt_100_200_in_30_60_days",
                        "cnt_recharges_with_amt_100_200_in_30_days",
                        "cnt_recharges_with_amt_100_200_in_60_90_days",
                        "cnt_recharges_with_amt_100_200_in_60_days",
                        "cnt_recharges_with_amt_100_200_in_90_days",
                        "cnt_recharges_with_amt_100_200_lifetime",
                        "cnt_recharges_with_amt_50_100",
                        "cnt_recharges_with_amt_50_100_in_30_60_days",
                        "cnt_recharges_with_amt_50_100_in_30_days",
                        "cnt_recharges_with_amt_50_100_in_60_90_days",
                        "cnt_recharges_with_amt_50_100_in_60_days",
                        "cnt_recharges_with_amt_50_100_in_90_days",
                        "cnt_recharges_with_amt_50_100_lifetime",
                        "cnt_recharges_with_amt_more_than_200",
                        "cnt_recharges_with_amt_more_than_200_in_30_60_days",
                        "cnt_recharges_with_amt_more_than_200_in_30_days",
                        "cnt_recharges_with_amt_more_than_200_in_60_90_days",
                        "cnt_recharges_with_amt_more_than_200_in_60_days",
                        "cnt_recharges_with_amt_more_than_200_in_90_days",
                        "cnt_recharges_with_amt_more_than_200_lifetime",
                        "cnt_util_bills_180_days",
                        "cnt_util_bills_30_days",
                        "cnt_util_bills_360_days",
                        "cnt_util_bills_90_days",
                        "cnt_util_bills_lifetime",
                        "cnt_utilities_paying_bills_for",
                        "flag_broadband_fixedline_utility",
                        "flag_dth_utility",
                        "flag_electricity_utility",
                        "flag_gas_utility",
                        "flag_home_service_utility",
                        "flag_missed_bb_landline_bill",
                        "flag_missed_elec_bill",
                        "flag_missed_postpaid_bill",
                        "flag_missed_postpaid_bill_30_60_days",
                        "flag_missed_postpaid_bill_30_days",
                        "flag_missed_postpaid_bill_60_90_days",
                        "flag_missed_utility_bill_payments",
                        "flag_missed_utility_bill_payments_30_60_days",
                        "flag_missed_utility_bill_payments_30_days",
                        "flag_missed_utility_bill_payments_60_90_days",
                        "flag_postpaid_on_mobile",
                        "flag_prepaid_on_mobile",
                        "flag_water_utility",
                        "ratio_amt_avg_monthly_postpaid_bill_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_postpaid_bill_30_days_to_90_days",
                        "ratio_amt_avg_monthly_recharge_30_days_to_60_120_days",
                        "ratio_amt_avg_monthly_recharge_30_days_to_90_days",
                        "sum_flag_utility",
                        "recharge_amount_m0",
                        "recharge_amount_m1",
                        "recharge_amount_m2",
                        "recharge_amount_m3",
                        "recharge_amount_m4",
                        "recharge_amount_m5",
                        "recharge_amount_m6",
                        "electricity_bill_amount_m0",
                        "electricity_bill_amount_m1",
                        "electricity_bill_amount_m2",
                        "electricity_bill_amount_m3",
                        "electricity_bill_amount_m4",
                        "electricity_bill_amount_m5",
                        "electricity_bill_amount_m6",
                        "cnt_missed_postpaid_bill_30_days",
                        "cnt_missed_electricity_bill_30_days",
                        "cnt_missed_gas_bill_30_days",
                        "cnt_missed_water_bill_30_days",
                        "cnt_missed_postpaid_bill_90_days",
                        "cnt_missed_electricity_bill_90_days",
                        "cnt_missed_gas_bill_90_days",
                        "cnt_missed_water_bill_90_days"
                    )
                );

                foreach ($OVERALL as $overKey => $overVal) {
                    if (isset($static_arr[$overKey])) {
                        $check_varible = $static_arr[$overKey];
                        $return_data .= '<div class="table-responsive">
							<table class="table table-bordered">       
								<tbody>
								<tr>                
									<th colspan="4" style="text-align:center;background:#ccc;"><b>' . $overKey . '</b></th>                                          
								</tr>';
                        $i = 0;
                        foreach ($overVal as $lastKey => $lastVal) {
                            if (!in_array($lastKey, $check_varible)) {
                                continue;
                            }
                            if ($i % 2 == 0) {
                                $return_data .= '<tr>                
									<th>' . strtoupper(str_replace('_', ' ', $lastKey)) . '</th>                
									<td>' . (($lastVal) ? $lastVal : '-') . '</td>';
                            } else {
                                $return_data .= '<th>' . strtoupper(str_replace('_', ' ', $lastKey)) . '</th>                
									<td>' . (($lastVal) ? $lastVal : '-') . '</td>            
								</tr>';
                            }
                            $i++;
                        }
                        $return_data .= '</tbody></table></div>';
                    }
                }

                /* 					
                  $return_data .= '<h4 style="font-size: 12px; font-weight:bold;">OVER ALL</h4>';
                  foreach($OVERALL as $overKey => $overVal) {
                  $return_data .= '<table class="table table-bordered table-striped"><thead><tr><th scope="col" style="text-align:center;">'.$overKey.'</th></tr></thead>';
                  foreach($overVal as $lastKey => $lastVal) {
                  $return_data .= '<tr><td>'.strtoupper(str_replace('_',' ',$lastKey)).'</td><td>'.(($lastVal) ? $lastVal : '-').'</td></tr>';
                  }
                  $return_data .= '</table>';
                  }

                  $return_data .= '<h4 style="font-size: 12px; font-weight:bold;">ENTITY</h4>';
                  foreach($ENTITY as $entryKey => $entryVal) {
                  $return_data .= '<table class="table table-bordered table-striped"><thead><tr><th scope="col" style="text-align:center;">'.$entryKey.'</th></tr></thead>';
                  foreach($entryVal as $lKey => $lVal) {
                  foreach($lVal as $key => $val) {
                  $return_data .= '<tr><td>'.strtoupper(str_replace('_',' ',$key)).'</td><td>'.(($val) ? $val : '-').'</td></tr>';
                  }
                  }
                  $return_data .= '</table>';
                  }
                 */
            }
        }
        echo json_encode($return_data);
    }

    public function analyse_bank_statement_api_call($leadID) {
        $lead_id = intval($this->encrypt->decode($leadID));
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "", "cart_return_doc_id" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        if (!empty($lead_id)) {

            $docs_details = $this->db->query('SELECT docs.docs_id, docs.lead_id, leads.status, leads.stage, leads.lead_status_id FROM `docs` INNER join leads ON leads.lead_id = docs.lead_id where docs.lead_id =' . $lead_id . ' AND docs.docs_master_id = 6 ORDER BY  docs.docs_id DESC LIMIT 1');
            if ($docs_details->num_rows() > 0) {
                $docs = $docs_details->row();
                $doc_id = $docs->docs_id;
                $status = $docs->status;
                $stage = $docs->stage;
                $lead_status_id = $docs->lead_status_id;

                $banking_analysis_api_type = 1;

                if (in_array($banking_analysis_api_type, array(1))) {

                    require_once (COMPONENT_PATH . "CommonComponent.php");

                    $CommonComponent = new CommonComponent();

                    $method_name = 'BANK_STATEMENT_UPLOAD';
                    $request_array = array();
                    $request_array['api_type'] = $banking_analysis_api_type;
                    $request_array['status'] = $status;
                    $request_array['stage'] = $stage;
                    $request_array['lead_status_id'] = $lead_status_id;
                    $request_array['doc_id'] = $doc_id;

                    $api_response = $CommonComponent->call_payday_bank_analysis($method_name, $lead_id, $request_array);
                    

                    if (!empty($api_response['status']) && ($api_response['status'] == 1) && !empty($api_response['return_doc_id'])) {
                        $responseArray['success_msg'] = "Bank statement updated successfully. Please wait for a moment...";
                        $responseArray['cart_return_doc_id'] = $api_response['return_doc_id'];
//                            $api_response_download = bank_analysis_doc_download_api($api_response['return_doc_id'], $request_array);
//                            
//                            if (!empty($api_response_download['status']) && ($api_response_download['status'] == 1) && !empty($api_response_download['return_doc_id'])) {
//                                
//                                $responseArray['success_msg'] = "Data download completed. Please wait for a moment...";
//                            } else if (!empty($api_response_download['error_msg'])) {
//                                $responseArray['error_msg'] = "Data download failed.. " . $api_response_download['error_msg'];
//                            } else {
//                                $responseArray['error_msg'] = "Data download failed... " . $api_response_download['error_msg'];
//                            }
                    } else if (!empty($api_response['error_msg'])) {
                        $responseArray['error_msg'] = "Banking validation failed.. " . $api_response['error_msg'];
                    } else {
                        $responseArray['error_msg'] = "Banking validation failed... " . $api_response['error_msg'];
                    }
                } else {
                    $responseArray['error_msg'] = "Somethig went wrong. Please check at your end.";
                }
            } else {
                $responseArray['error_msg'] = "Bank statement document not found. Please Try Again.";
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }

        echo json_encode($responseArray);
    }

    public function analyse_bank_statement_download_api_call($lead_id) {
        $lead_id = intval($this->encrypt->decode($lead_id));
        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "", "cart_return_doc_id" => "");

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        if (!empty($lead_id)) {
            $docs_details = $this->db->query('SELECT docs.docs_id, docs.lead_id, docs.docs_novel_return_id, leads.status, leads.stage, leads.lead_status_id FROM `docs` INNER join leads ON leads.lead_id = docs.lead_id where docs.lead_id =' . $lead_id . ' AND docs.docs_master_id = 6 ORDER BY  docs.docs_id DESC LIMIT 1');

            if ($docs_details->num_rows() > 0) {
                $docs = $docs_details->row();
                $doc_id = $docs->docs_id;
                $status = $docs->status;
                $stage = $docs->stage;
                $lead_status_id = $docs->lead_status_id;
                $docs_novel_return_id = $docs->docs_novel_return_id;

                $banking_analysis_api_type = 1;
                if (in_array($banking_analysis_api_type, array(1))) {
                    require_once (COMPONENT_PATH . "CommonComponent.php");

                    $CommonComponent = new CommonComponent();
                    $method_name = 'BANK_STATEMENT_DOWNLOAD';
                    $request_array = array();
                    $request_array['api_type'] = $banking_analysis_api_type;
                    $request_array['status'] = $status;
                    $request_array['stage'] = $stage;
                    $request_array['lead_status_id'] = $lead_status_id;
                    $request_array['doc_id'] = $docs_novel_return_id;

                    $api_response_download = $CommonComponent->call_payday_bank_analysis($method_name, $lead_id, $request_array);

                    if (!empty($api_response_download['status']) && ($api_response_download['status'] == 1) && !empty($api_response_download['return_doc_id'])) {

                        $responseArray['success_msg'] = "Data download completed. Please wait for a moment...";
                    } else if (!empty($api_response_download['error_msg'])) {
                        $responseArray['error_msg'] = "Data download failed.. " . $api_response_download['error_msg'];
                    } else {
                        $responseArray['error_msg'] = "Data download failed... " . $api_response_download['error_msg'];
                    }
                } else {
                    $responseArray['error_msg'] = "Somethig went wrong. Please check at your end.";
                }
            } else {
                $responseArray['error_msg'] = "Document not found. Please Try Again.";
            }
        } else {
            $responseArray['error_msg'] = "Invalid request. Please Try Again.";
        }

        echo json_encode($responseArray);
    }

    public function Click_to_call($lead_id) {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['err'] = "Session Expired";
            echo json_encode($json);
        } else {

            $lead_id = intval($this->encrypt->decode($_POST['lead_id']));
            $call_type = $_POST['call_type'];
            $profile_type = $_POST['profile_type'];

            $this->load->helper('integration/payday_smartping_call_api_helper');
            $method_name = '';

            if ($profile_type == 1) {
                $method_name = 'LEAD_CAT_SANCTION';
            } else if ($profile_type >= 2) {
                $method_name = 'SMARTPING_COLLECTION_CALL';
            }

            $request_array['call_type'] = $call_type;
            $request_array['profile_type'] = $profile_type;

            if ($profile_type == 4) {
                $request_array['not_contact_flow'] = 1;
            }

            $return_array = payday_call_management_api_call($method_name, $lead_id, $request_array);

            if ($return_array['status'] == 1) {
                $json['msg'] = "Call has been assigned to " . $return_array['process_name'];
            } else {
                $json['err'] = $return_array['error_msg'];
            }

            echo json_encode($json);
        }
    }

    public function dialer_data_upload() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['err'] = "Session Expired";
            echo json_encode($json);
        } else {
            $this->load->helper('integration/payday_runo_call_api_helper');

            $request_array = array();
            $request_array['call_type'] = 1;
            $request_array['profile_type'] = 2;
            $request_array['lead_list'] = $this->input->post('checkList');

            $result = payday_call_management_api_call("SMARTPING_BULK_UPLOAD", 0, $request_array);

            if ($result['status'] == 1) {
                $json['msg'] = "Data Synced";
                echo json_encode($json);
            } else {
                $json['err'] = $result['message'];
                echo json_encode($json);
            }
        }
    }

    public function email_verification_api_response_call($leadID) {
        $lead_id = intval($this->encrypt->decode($leadID));
        $responseArray = array("status" => 0);
        $return_data = "";
        $conditions = array();
        $data_error = 0;

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $responseArray['errSession'] = "Session Expired. try again.";
            echo json_encode($responseArray);
        }

        try {

            $conditions['EVL.ev_lead_id'] = $lead_id;
            $conditions['EVL.ev_method_id'] = 2;
            $conditions['EVL.ev_active'] = 1;
            $conditions['EVL.ev_api_status_id'] = 1;
            $conditions['EVL.ev_deleted'] = 0;

            $this->db->select("*");
            $this->db->from('api_email_verification_logs EVL');
            $this->db->where($conditions);
            $temp_data = $this->db->order_by('EVL.ev_id', 'DESC')->get();

            if (empty($temp_data->num_rows())) {
                throw new Exception("Office email does not verify using Signzy Api.");
            }

            $verified_email_json = $temp_data->row_array();

            if (empty($verified_email_json['ev_response'])) {
                throw new Exception("Office Email verification response data not found.");
            }

            $email_response_array = json_decode($verified_email_json['ev_response'], true);

            $is_valid_email = $email_response_array['result']['validEmail']; // true or false

            if ($is_valid_email == false) {
                throw new Exception("Invalid Official email.");
            }

            $result_email = $email_response_array['result'];
            $personal_details = $email_response_array['result']['personalDetails']; //array
            $company_details = $email_response_array['result']['companyDetails']; // array
            $company_domain_details = $email_response_array['result']['domainDetails']; // array


            $return_data = '<div style="overflow-y: scroll;">';
            $return_data = '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Basic&nbsp;DETAILS&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Official Email</th>';
            $return_data .= '<td class="whitespace">' . (($result_email['emailId']) ? $result_email['emailId'] : '-') . '</td>';
            $return_data .= '<th class="whitespace">Status</th>';
            $return_data .= '<td class="whitespace">' . (($is_valid_email == true) ? "SUCCESS" : 'FAILED') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Email Account</th>';
            $return_data .= '<td class="whitespace">' . (($result_email['account']) ? strtoupper($result_email['account']) : '-') . '</td>';
            $return_data .= '<th class="whitespace">Domain</th>';
            $return_data .= '<td class="whitespace">' . (($result_email['domain']) ? strtoupper($result_email['domain']) : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">SMTP Provider</th>';
            $return_data .= '<td class="whitespace">' . (($result_email['smtpProvider']) ? $result_email['smtpProvider'] : '-') . '</td>';
            $return_data .= '<th class="whitespace">MX Record</th>';
            $return_data .= '<td class="whitespace">' . (($result_email['mxRecord']) ? $result_email['mxRecord'] : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">User Name</th>';
            $return_data .= '<td class="whitespace">' . (($personal_details['name']) ? strtoupper($personal_details['name']) : '-') . '</td>';
            $return_data .= '<th class="whitespace">Employment</th>';
            $return_data .= '<td class="whitespace">' . (($personal_details['employment']) ? strtoupper($personal_details['employment']) : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Company Name</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['name']) ? strtoupper($company_details['name']) : '-') . '</td>';
            $return_data .= '<th class="whitespace">Legal Name</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['legalName']) ? strtoupper($company_details['legalName']) : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Social&nbsp;Media&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';
            foreach ($personal_details['socialMediaHandles'] as $key => $values) {
                $return_data .= '<th class="whitespace">' . ucwords($key) . '</th>';
            }
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            foreach ($personal_details['socialMediaHandles'] as $key => $values) {
                $return_data .= '<td class="whitespace" >' . (($values) ? strtoupper($values) : '-') . '</td>';
            }
            $return_data .= '</tr>';
            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Company&nbsp;Details&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Company Name</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['name']) ? strtoupper($company_details['name']) : '-') . '</td>';
            $return_data .= '<th class="whitespace">Legal Name</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['legalName']) ? strtoupper($company_details['legalName']) : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Company Foundation Year</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['foundedYear']) ? $company_details['foundedYear'] : '-') . '</td>';
            $return_data .= '<th class="whitespace">Company Location</th>';
            $return_data .= '<td class="whitespace">' . (($company_details['location']) ? strtoupper($company_details['location']) : '-') . '</td>';
            $return_data .= '</tr>';

            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Company&nbsp;Contact&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Contacts</th>';

            foreach ($company_details['phoneNumbers'] as $values) {
                $return_data .= '<td class="whitespace"><a href="tel:' . $values . '">' . (!empty($values) ? $values : "-") . '</a></td>';
            }
            $return_data .= '</tr>';
            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Company&nbsp;Emails&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';
            $return_data .= '<th class="whitespace">Emails</th>';

            foreach ($company_details['emailAddresses'] as $values) {
                $return_data .= '<td class="whitespace"><a href="mailto:' . $values . '">' . (!empty($values) ? strtoupper($values) : "-") . '</a></td>';
            }
            $return_data .= '</tr>';
            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Company&nbsp;Category&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';

            foreach ($company_details['category'] as $key => $values) {
                $return_data .= '<th class="whitespace">' . ucwords($key) . '</th>';
            }
            $return_data .= '</tr>';
            $return_data .= '<tr>';

            foreach ($company_details['category'] as $key => $values) {
                $return_data .= '<td class="whitespace">' . (!empty($values) ? strtoupper($values) : "-") . '</td>';
            }
            $return_data .= '</tr>';
            $return_data .= '</table></div>';

            $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
            $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Domain&nbsp;Details&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

            $return_data .= '<tr>';

            foreach ($company_domain_details as $key => $values) {
                if (in_array($key, ['registrar', 'nameservers'])) {
                    continue;
                }
                $return_data .= '<th class="whitespace">' . ucwords($key) . '</th>';
            }
            $return_data .= '</tr>';
            $return_data .= '<tr>';

            foreach ($company_domain_details as $key => $values) {
                if (in_array($key, ['registrar', 'nameservers'])) {
                    continue;
                }
                $return_data .= '<td class="whitespace">' . (!empty($values) ? strtoupper($values) : "-") . '</td>';
            }
            $return_data .= '</tr>';
            $return_data .= '</table></div></div>';

            $responseArray['status'] = 1;
            $responseArray['data'] = $return_data;
        } catch (Exception $ex) {
            $responseArray['message'] = $ex->getMessage();
        }

        echo json_encode($responseArray);
    }
}

?>
