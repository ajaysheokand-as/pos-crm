<?php

if (!function_exists('get_account_aggregator_bank_statement_data')) {

    function get_account_aggregator_bank_statement_data($enc_lead_id, $lead_id) {

        if (empty($lead_id) || empty($enc_lead_id)) {
            return null;
        }

        $CI = & get_instance();
        $CI->load->model('Task_Model', 'Tasks');

        $html = '';

        $count = 1;

        $responseArray = array("status" => 0, "data" => "", "message" => "");

        $get_account_aggregator_bank_statement_response_log = $CI->Tasks->getAccountAggregatorLogs($lead_id, 5);

        if ($get_account_aggregator_bank_statement_response_log['status'] == 1) {
            $responseArray['status'] = 1;
            $get_account_aggregator_bank_statement_data = json_decode($get_account_aggregator_bank_statement_response_log['account_aggregator_logs']['aa_response'], true);
            $bank_analysis_data = $get_account_aggregator_bank_statement_data['AA_Output']['bankStatementAnalysis']['bank_account'];
            $account_holder_data = $get_account_aggregator_bank_statement_data['AA_Output']['input_json']['AA_response']['data']['content']['data'];

            $html .= '<div class="panel-group" id="main-accordion">';
            if (!empty($bank_analysis_data)) {
                foreach ($bank_analysis_data as $bank_account) {
                    $account_no = str_replace("ACC_", "", $bank_account['account_id']);
                    $statement_start_date = $bank_account['start_date'];
                    $statement_end_date = $bank_account['end_date'];
                    $opening_balance = $bank_account['Account_Details']['Opening_Balance'];
                    $closing_balance = $bank_account['Account_Details']['Closing_Balance'];

//                    $startDate = date("Y/m/d", strtotime($statement_start_date));
//                    $endDate = date("Y/m/d", strtotime($statement_end_date));
                    list($preDay, $preMonth, $preYear) = explode('/', $statement_start_date);
                    list($currentDay, $currentMonth, $currentYear) = explode('/', $statement_end_date);
                    $startDate = date($preYear . "-" . $preMonth . "-" . $preDay);
                    $endDate = date($currentYear . "-" . $currentMonth . "-" . $currentDay);
//                    echo $currentYear."|".$currentMonth."|".$currentDay;
                    $numberOfMonths = (12 - $preMonth) + ($currentMonth) + 1 + (12 * ($currentYear - $preYear - 1));

                    $monthArray = array();
                    for ($i = 0; $i < $numberOfMonths; $i++) {
                        $monthArray[] = date("m/y", strtotime($startDate));
                        $startDate = date("Y-m-d", strtotime("+1 month", strtotime($startDate)));
                    }

                    $monthArray = array_reverse($monthArray);
//                    print_r($monthArray);

                    $eod_analysis = $bank_account['EOD_Analysis'];
                    $monthwise_top_10_transactions = $bank_account['Monthwise_Top_10_Transactions'];
                    $top_10_debit_transactions = $bank_account['Top_10_Debit_Transactions'];
                    $top_10_credit_transactions = $bank_account['Top_10_Credit_Transactions'];
                    $maximum_balance = $bank_account['max_balance'];
                    $minimum_balance = $bank_account['min_balance'];
                    $average_balance = $bank_account['average_balance'];
                    $cash_deposits = $bank_account['cash_deposits'];
                    $cash_withdrawals = $bank_account['cash_withdrawals'];
                    $summary_of_debit_and_credit = $bank_account['Summary_of_Debit_and_Credit'];
                    $total_debit = $bank_account['total_debit'];
                    $total_credit = $bank_account['total_credit'];
                    $total_loan_disbursal = $bank_account['total_loan_disbursal'];
                    $emi = $bank_account['emi'];
                    $total_credit_salary = $bank_account['total_credit_salary'];

                    $html .= '<div class="panel panel-default">
                                  <div class="panel-heading">
                                    <h4 class="panel-title">
                                      <a data-toggle="collapse" data-parent="#main-accordion" href="#account' . $count . '">' . $account_no . '&nbsp;<i class="fa fa-angle-double-right" style="user-select: auto;"></i></a>
                                    </h4>
                                  </div>
                                  <div id="account' . $count . '" class="panel-collapse collapse">';

                    //Customer Details

                    $html .= '<div class="table-responsive"><table class="table table-bordered table-striped">';

                    $html .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Customer&nbsp;Details&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';
                    $html .= '<tr>                
                        <th class="whitespace">Customer Name</th>                             
                        <th class="whitespace">DOB</th>       
                        <th class="whitespace">Mobile</th>   
                        <th class="whitespace">PAN Number</th>       
                        <th class="whitespace">Email</th>
                        <th class="whitespace">Address</th>
                        <th class="whitespace">Landline</th>
                        <th class="whitespace">Nominee</th>
                    </tr>';

                    if (!empty($account_holder_data)) {

                        foreach ($account_holder_data as $account_holder) {
                            $accountNo = $account_holder['maskedAccountNumber'];
                            if ($account_no == $accountNo) {
                                $customer_bank_details = $account_holder['Profile']['Holders']['Holder'];
                                foreach ($customer_bank_details as $customer_details) {
                                    $html .= '<tr>
                        <td class="whitespace">' . (($customer_details['name']) ? $customer_details['name'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['dob']) ? $customer_details['dob'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['mobile']) ? $customer_details['mobile'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['pan']) ? $customer_details['pan'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['email']) ? $customer_details['email'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['address']) ? $customer_details['address'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['landline']) ? $customer_details['landline'] : '-') . '</td>
                        <td class="whitespace">' . (($customer_details['nominee']) ? $customer_details['nominee'] : '-') . '</td>
                    </tr>';
                                }
                            }
                        }
                    }
                    $html .= '</table></div>';

                    //Account Details

                    $html .= '<div class="table-responsive"><table class="table table-bordered table-striped">';

                    $html .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">ACCOUNT&nbsp;DETAILS <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';
                    $html .= '<tr>
                        <th class="whitespace">Bank Name</th> 
                        <th class="whitespace">Account Number</th>                             
                        <th class="whitespace">IFSC Code</th>                             
                        <th class="whitespace">Branch Name</th>                             
                        <th class="whitespace">Account Type</th>                             
                        <th class="whitespace">MICR Code</th>                             
                        <th class="whitespace">Current Balance</th>                             
                        <th class="whitespace">Account Opening Date</th>                             
                        <th class="whitespace">Account Status</th>                             
                        <th class="whitespace">Bank Statement Start Date</th>       
                        <th class="whitespace">Bank Statement End Date</th>
                    </tr>';

                    if (!empty($account_holder_data)) {


                        foreach ($account_holder_data as $account_holder) {
                            $accountNo = $account_holder['maskedAccountNumber'];
                            if ($account_no == $accountNo) {
                                $bank_details = $account_holder['Summary'];
                                $html .= '<tr>
                        <td class="whitespace">' . (($account_holder['bank']) ? $account_holder['bank'] : '-') . '</td>
                        <td class="whitespace">' . (($accountNo) ? $accountNo : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['ifscCode']) ? $bank_details['ifscCode'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['branch']) ? $bank_details['branch'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['type']) ? $bank_details['type'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['micrCode']) ? $bank_details['micrCode'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['currentBalance']) ? $bank_details['currentBalance'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['openingDate']) ? $bank_details['openingDate'] : '-') . '</td>
                        <td class="whitespace">' . (($bank_details['status']) ? $bank_details['status'] : '-') . '</td>
                        <td class="whitespace">' . (($statement_start_date) ? $statement_start_date : '-') . '</td>
                        <td class="whitespace">' . (($statement_end_date) ? $statement_end_date : '-') . '</td>
                    </tr>';
                            }
                        }
                    }

                    $html .= '</table></div>';

                    //Salary Details
                    $html .= '<div class="table-responsive">
                            <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Salary Details<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                            <table class="table table-bordered table-striped">
                                <tr class="table-default">
                                    <th class="table-whitespace" style="width:auto !important">S.No.</th>
                                    <th class="table-whitespace">Month</th>
                                    <th class="table-whitespace">Total Salary Credits</th>
                                    <th class="table-whitespace">Total Bonus Credits</th>
                                    <th class="table-whitespace">Last Payment Date</th>
                                </tr>';

                    $sno = 1;

                    foreach ($total_credit_salary as $credit_salary) {
                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($credit_salary['month'])) . '</td>';
                        $html .= '<td>' . (!empty($credit_salary['total_salary_credits']) ? $credit_salary['total_salary_credits'] : 0) . '</td>';
                        $html .= '<td>' . (!empty($credit_salary['total_bonus_credits']) ? $credit_salary['total_bonus_credits'] : 0) . '</td>';
                        $html .= '<td>' . (!empty($credit_salary['lastPaymentDate']) ? date("d-m-Y", strtotime($credit_salary['lastPaymentDate'])) : '-') . '</td>';
                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    //Balances

                    $html .= '<div class="table-responsive">
                            <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Balances Details<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                            <table class="table table-bordered table-striped">
                            <tr class="table-default">
                            <th class="table-whitespace">S.No.</th>
                            <th class="table-whitespace">Month</th>
                            <th class="table-whitespace">Average Balance</th>
                            <th class="table-whitespace">Maximum Balance</th>
                            <th class="table-whitespace">Minimum Balance</th>
                            </tr>';

                    $sno = 1;
                    foreach ($monthArray as $month) {

                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($month)) . '</td>';

                        foreach ($average_balance as $avg_blnc) {
                            if ($avg_blnc['month'] == $month) {
                                $html .= '<td>' . (!empty($avg_blnc['average']) ? $avg_blnc['average'] : 0) . '</td>';
                            }
                        }

                        foreach ($maximum_balance as $max_balance) {
                            if ($max_balance['month'] == $month) {
                                $html .= '<td>' . (!empty($max_balance['max_balance']) ? $max_balance['max_balance'] : 0) . '</td>';
                            }
                        }

                        foreach ($minimum_balance as $min_balance) {
                            if ($min_balance['month'] == $month) {
                                $html .= '<td>' . (!empty($min_balance['min_balance']) ? $min_balance['min_balance'] : 0) . '</td>';
                            }
                        }

                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    // Cash Deposits and withdrawals
                    $html .= '<div class="table-responsive">
                            <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Cash Deposits and Withdrawals<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                            <table class="table table-bordered table-striped">
                            <tr class="table-default">
                            <th class="table-whitespace" style="width:auto !important">S.No.</th>
                            <th class="table-whitespace">Month</th>
                            <th class="table-whitespace">Cash Deposits</th>
                            <th class="table-whitespace">Cash Withdrawals</th>
                            </tr>';

                    $sno = 1;
                    foreach ($monthArray as $month) {

                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($month)) . '</td>';

                        foreach ($cash_deposits as $cd) {
                            if ($cd['month'] == $month) {
                                $html .= '<td>' . (!empty($cd['total_cash_deposit']) ? $cd['total_cash_deposit'] : 0) . '</td>';
                            }
                        }

                        foreach ($cash_withdrawals as $cw) {
                            if ($cw['month'] == $month) {
                                $html .= '<td>' . (!empty($cw['total_cash_withdrawals']) ? $cw['total_cash_withdrawals'] : 0) . '</td>';
                            }
                        }

                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    //Summary of Debit And Credit
                    /*
                    $html .= '<div class="table-responsive">
                                <h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Monthwise Summary of Debit and Credit<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>
                                <table class="table table-bordered table-striped">
                                    <tr class="table-default">
                                        <th class="table-whitespace" style="width:auto !important">S.No.</th>
                                        <th class="table-whitespace">Month</th>
                                        <th class="table-whitespace">Cash Deposit</th>
                                        <th class="table-whitespace">Cash Withdrawal</th>
                                        <th class="table-whitespace">Cheque Receipt</th>
                                        <th class="table-whitespace">Cheque Payment</th>
                                    </tr>
                                    <tr class="table-default">
                                        <th class="table-whitespace"></th>
                                        <th class="table-whitespace"></th>
                                        <th class="table-whitespace">
                                            <table class="table table-bordered table-striped">
                                                <tr class="table-default">
                                                    <th class="table-whitespace">Count</th>
                                                    <th class="table-whitespace">Amount</th>
                                                </tr>
                                            </table>
                                        </th>
                                        <th class="table-whitespace">
                                            <table class="table table-bordered table-striped">
                                                <tr class="table-default">
                                                    <th class="table-whitespace">Count</th>
                                                    <th class="table-whitespace">Amount</th>
                                                </tr>
                                            </table>
                                        </th>
                                        <th class="table-whitespace">
                                            <table class="table table-bordered table-striped">
                                                <tr class="table-default">
                                                    <th class="table-whitespace">Count</th>
                                                    <th class="table-whitespace">Amount</th>
                                                </tr>
                                            </table>
                                        </th>
                                        <th class="table-whitespace">
                                            <table class="table table-bordered table-striped">
                                                <tr class="table-default">
                                                    <th class="table-whitespace">Count</th>
                                                    <th class="table-whitespace">Amount</th>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>';

                    $sno = 1;
                    
                    foreach ($summary_of_debit_and_credit as $summary_data) {
                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($summary_data['month'])) . '</td>';
                        $html .= '<td>'
                                . '<table class="table table-bordered table-striped">'
                                . '<tr class="table-default">'
                                . '<td class="table-whitespace">' . (!empty($summary_data['CashDepositCount']) ? $summary_data['CashDepositCount'] : 0) . '</td>'
                                . '<td class="table-whitespace">' . (!empty($summary_data['CashDepositAmount']) ? $summary_data['CashDepositAmount'] : 0) . '</td>'
                                . '</tr>'
                                . '</table>'
                                . '</td>';
                        $html .= '<td>'
                                . '<table class="table table-bordered table-striped">'
                                . '<tr class="table-default">'
                                . '<td class="table-whitespace">' . (!empty($summary_data['CashWithdrawalCount']) ? $summary_data['CashWithdrawalCount'] : 0) . '</td>'
                                . '<td class="table-whitespace">' . (!empty($summary_data['CashWithdrawalAmount']) ? $summary_data['CashWithdrawalAmount'] : 0) . '</td>'
                                . '</tr>'
                                . '</table>'
                                . '</td>';
                        $html .= '<td>'
                                . '<table class="table table-bordered table-striped">'
                                . '<tr class="table-default">'
                                . '<td class="table-whitespace">' . (!empty($summary_data['ChequeReceiptCount']) ? $summary_data['ChequeReceiptCount'] : 0) . '</td>'
                                . '<td class="table-whitespace">' . (!empty($summary_data['ChequeReceiptAmount']) ? $summary_data['ChequeReceiptAmount'] : 0) . '</td>'
                                . '</tr>'
                                . '</table>'
                                . '</td>';
                        $html .= '<td>'
                                . '<table class="table table-bordered table-striped">'
                                . '<tr class="table-default">'
                                . '<td class="table-whitespace">' . (!empty($summary_data['ChequePaymentCount']) ? $summary_data['ChequePaymentCount'] : 0) . '</td>'
                                . '<td class="table-whitespace">' . (!empty($summary_data['ChequePaymentAmount']) ? $summary_data['ChequePaymentAmount'] : 0) . '</td>'
                                . '</tr>'
                                . '</table>'
                                . '</td>';
                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';
                    */
                    //Monthwise Debit Data
                    $html .= '<div class="table-responsive">'
                            . '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Total Debit<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>'
                            . '<table class="table table-bordered table-striped">'
                            . '<tr class="table-default">'
                            . '<th class="table-whitespace" style="width:auto !important">S.No.</th>'
                            . '<th class="table-whitespace">Month</th>'
                            . '<th class="table-whitespace">Count</th>'
                            . '<th class="table-whitespace">Amount</th>'
                            . '</tr>';
                    $sno = 1;
                    foreach ($total_debit as $debit) {
                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($debit['month'])) . '</td>';
                        $html .= '<td>' . (!empty($debit['count_debit_trx']) ? $debit['count_debit_trx'] : 0) . '</td>';
                        $html .= '<td>' . (!empty($debit['total_debit_amount']) ? $debit['total_debit_amount'] : 0) . '</td>';
                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    //Monthwise Credit Data
                    $html .= '<div class="table-responsive">'
                            . '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Total Credit<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>'
                            . '<table class="table table-bordered table-striped">'
                            . '<tr class="table-default">'
                            . '<th class="table-whitespace" style="width:auto !important">S.No.</th>'
                            . '<th class="table-whitespace">Month</th>'
                            . '<th class="table-whitespace">Count</th>'
                            . '<th class="table-whitespace">Amount</th>'
                            . '</tr>';
                    $sno = 1;
                    foreach ($total_credit as $credit) {
                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($credit['month'])) . '</td>';
                        $html .= '<td>' . (!empty($credit['count_credit_trx']) ? $credit['count_credit_trx'] : 0) . '</td>';
                        $html .= '<td>' . (!empty($credit['total_credit_amount']) ? $credit['total_credit_amount'] : 0) . '</td>';
                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    //Monthwise Loan Disbursal
                    $html .= '<div class="table-responsive">'
                            . '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Total Loan Disbursal<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>'
                            . '<table class="table table-bordered table-striped">'
                            . '<tr class="table-default">'
                            . '<th class="table-whitespace" style="width:auto !important">S.No.</th>'
                            . '<th class="table-whitespace">Month</th>'
                            . '<th class="table-whitespace">Count</th>'
                            . '<th class="table-whitespace">Amount</th>'
                            . '</tr>';
                    $sno = 1;
                    foreach ($total_loan_disbursal as $loan_disbursal) {
                        $html .= '<tr>';
                        $html .= '<td>' . $sno . '</td>';
                        $html .= '<td>' . date("F-Y", strtotime($loan_disbursal['month'])) . '</td>';
                        $html .= '<td>' . (!empty($loan_disbursal['count_loan_disbursed_trx']) ? $loan_disbursal['count_loan_disbursed_trx'] : 0) . '</td>';
                        $html .= '<td>' . (!empty($loan_disbursal['total_loan_disbursed']) ? $loan_disbursal['total_loan_disbursed'] : 0) . '</td>';
                        $html .= '</tr>';
                        $sno++;
                    }

                    $html .= '</table></div>';

                    //Monthwise EMI
                    $html .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">EMI Transactions <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

                    $html .= '<div class="panel-group" id="accordion">';

                    $sno = 1;
                    $snoo = 1;
                    foreach ($emi as $emis) {
                        $html .= '<div class="panel panel-default">
                                  <div class="panel-heading">
                                    <h4 class="panel-title">
                                      <a data-toggle="collapse" data-parent="#accordion" href="#emi' . $sno . '">' . date("F-Y", strtotime($emis['month'])) . '&nbsp;<i class="fa fa-angle-double-right" style="user-select: auto;"></i></a>
                                    </h4>
                                  </div>
                                  <div id="emi' . $sno . '" class="panel-collapse collapse">
                                     <div class="table-responsive">'
                                . '<table class="table table-bordered table-striped">'
                                . '<tr class="table-default">'
                                . '<th class="table-whitespace" style="width:auto !important">S.No.</th>'
                                . '<th class="table-whitespace">Date</th>'
                                . '<th class="table-whitespace">Type</th>'
                                . '<th class="table-whitespace">Reference no</th>'
                                . '<th class="table-whitespace">Description</th>'
                                . '<th class="table-whitespace">Balance Amount</th>'
                                . '<th class="table-whitespace">Withdrawal Amount</th>'
                                . '</tr>';
                        foreach ($emis['transactions'] as $transactions) {
                            if (!empty($transactions)) {
                                $html .= '<tr>';
                                $html .= '<td>' . $snoo . '</td>';
                                $html .= '<td>' . (!empty($transactions['date']) ? $transactions['date'] : '-') . '</td>';
                                $html .= '<td>' . (!empty($transactions['type']) ? $transactions['type'] : '-') . '</td>';
                                $html .= '<td>' . (!empty($transactions['particular']) ? $transactions['particular'] : '-') . '</td>';
                                $html .= '<td>' . (!empty($transactions['description']) ? $transactions['description'] : '-') . '</td>';
                                $html .= '<td>' . (!empty($transactions['balanceAmount']) ? $transactions['balanceAmount'] : 0) . '</td>';
                                $html .= '<td>' . (!empty($transactions['withdrawalAmount']) ? $transactions['withdrawalAmount'] : 0) . '</td>';
                                $html .= '</tr>';
                            } else {
                                $html .= '<tr>';
                                $html .= '<td style="text-align:center:color:red">Transactions Not Found</td>';
                                $html .= '</tr>';
                            }
                            $snoo++;
                        }
                        $html .= '</table></div>
                                  </div>
                                </div>
                            ';
                        $sno++;
                    }

                    $html .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Transactions Details <i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

                    $html .= '<div class="panel-group" id="transactions">';

                    $html .= '<div class="panel panel-default">
                                  <div class="panel-heading">
                                    <h4 class="panel-title">
                                      <a data-toggle="collapse" data-parent="#transactions" href="#transaction-acc">Transactions&nbsp;<i class="fa fa-angle-double-right" style="user-select: auto;"></i></a>
                                    </h4>
                                  </div>
                                  <div id="transaction-acc" class="panel-collapse collapse">
                                  <div class="table-responsive">'
                            . '<table class="table table-bordered table-striped">'
                            . '<tr class="table-default">'
                            . '<th class="table-whitespace" style="width:auto !important">S.No.</th>'
                            . '<th class="table-whitespace" style="width:auto !important">Transaction Date</th>'
                            . '<th class="table-whitespace">Transaction Mode</th>'
                            . '<th class="table-whitespace">Transaction</th>'
                            . '<th class="table-whitespace">Debit</th>'
                            . '<th class="table-whitespace">Credit</th>'
                            . '<th class="table-whitespace">Current Balance</th>'
                            . '</tr>';

                    $sno = 1;

                    foreach ($account_holder_data as $account_holder) {
                        $transactions_data = $account_holder['Transactions']['Transaction'];
                        foreach ($transactions_data as $transactions) {
                            $html .= '<tr>';
                            $html .= '<td style="width:15px">' . $sno . '</td>';
                            $html .= '<td>' . (!empty($transactions['transactionTimestamp']) ? $transactions['transactionTimestamp'] : '-') . '</td>';
                            $html .= '<td>' . (!empty($transactions['mode']) ? $transactions['mode'] : '-') . '</td>';
                            $html .= '<td>' . (!empty($transactions['narration']) ? $transactions['narration'] : '-') . '</td>';
                            $html .= '<td>' . ($transactions['type'] == "DEBIT" ? $transactions['amount'] : '-') . '</td>';
                            $html .= '<td>' . ($transactions['type'] == "CREDIT" ? $transactions['amount'] : '-') . '</td>';
                            $html .= '<td>' . (!empty($transactions['currentBalance']) ? $transactions['currentBalance'] : 0) . '</td>';
                            $html .= '</tr>';
                            $sno++;
                        }
                    }

                    $html .= '</div></div>';

                    $html .= '</div></div></div>';

                    $count++;
                }
            }

            $html .= '</div>';
            $responseArray['data'] = $html;
        } else {
            $get_account_aggregator_bank_statement_response_log = $CI->Tasks->getAccountAggregatorLogs($lead_id, 3);
            if ($get_account_aggregator_bank_statement_response_log['status'] == 1) {
                $get_account_aggregator_consent_status_data = json_decode($get_account_aggregator_bank_statement_response_log['account_aggregator_logs']['aa_response'], true);
                if (!empty($get_account_aggregator_consent_status_data['result']) && strtolower($get_account_aggregator_consent_status_data['Message']) == "success") {
                    $responseArray['status'] = 1;
                    if (strtoupper($get_account_aggregator_consent_status_data['data']['consentStatus']) == "PENDING") {
                        $html = '<button class="btn btn-info" id="verify_account_aggregator_consent" onclick="verify_account_aggregator_consent(\'' . $enc_lead_id . '\')">Verify Account Aggregator Consent</button>';
                        $responseArray['data'] = $html;
                        $responseArray['status'] = 1;
                    } else if (strtoupper($get_account_aggregator_consent_status_data['data']['consentStatus']) == "APPROVED") {
                        $html = '<button class="btn btn-info" id="fetch_aa_bank_statement" onclick="fetch_aa_bank_statement(\'' . $enc_lead_id . '\')">Fetch Bank Statement</button>';
                        $responseArray['data'] = $html;
                        $responseArray['status'] = 1;
                    } else if (strtoupper($get_account_aggregator_consent_status_data['data']['consentStatus']) == "REJECTED") {
//                        $responseArray['status'] = 1;
                        $html = 'Consent Status not available';
                        $responseArray['data'] = $html;
                    }
                } else {
                    $responseArray['message'] = "Consent Status not available";
                }
            } else {
                $responseArray['status'] = 1;
                $html = 'Consent Status not available';
                $responseArray['data'] = $html;
            }
        }

        return $responseArray;
    }

}
