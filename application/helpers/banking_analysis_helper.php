<?php

if (!function_exists('get_Banking_Analysis_Response_Data')) {

    function get_Banking_Analysis_Response_Data($lead_id) {
        $ci = &get_instance();
        $ci->load->database();

        $responseArray = array("errSession" => "", "success_msg" => 0, "error_msg" => "", "data" => "");

        $nobel_response_data = $ci->db->query('SELECT docs.lead_id as docs_lead_id, api_banking_cart_log.cart_log_id as cart_log_id, api_banking_cart_log.cart_return_novel_doc_id as log_nobel_return_id, api_banking_cart_log.cart_response as nobel_response_data
                FROM `docs`
                INNER join api_banking_cart_log ON api_banking_cart_log.cart_lead_id = docs.lead_id
                where docs.lead_id =' . $lead_id . ' AND docs.docs_master_id = 6 AND docs.docs_active = 1 AND docs.docs_deleted = 0 AND api_banking_cart_log.cart_method_id = 2 AND api_banking_cart_log.cart_api_status_id IN (1,2) AND api_banking_cart_log.cart_active = 1 AND api_banking_cart_log.cart_deleted = 0
                ORDER BY api_banking_cart_log.cart_log_id DESC LIMIT 1');


        if ($nobel_response_data->num_rows() > 0) {

            $api_data = $nobel_response_data->row();

            $nobel_response_data_json = stripslashes($api_data->nobel_response_data);
            $nobel_response_data_json = str_replace('\\', " - ", $nobel_response_data_json);

            $response_data_array = json_decode($nobel_response_data_json, true);

            if ($response_data_array['status'] == 'Submitted') {
                $responseArray['success_msg'] = "Transaction is processed but Final Summary output is not available for View.";
            } else if ($response_data_array['status'] == 'In Progress') {
                $responseArray['success_msg'] = "Transaction is uploaded and is In process. Not available for view at this stage.";
            } else if ($response_data_array['status'] == 'Deleted') {
                $responseArray['success_msg'] = "Transaction was deleted";
            } else if (in_array(strtolower($response_data_array['status']), ['downloaded', 'processed'])) { // && strpos(strtolower($response_data_array['message']), 'fraud') === false
                foreach ($response_data_array['data'] as $bank_accounts) {
                    // if ($bank_accounts['accountType'] == "Saving") {
                    $account_details = $bank_accounts;
                    $cam_details = $account_details['camAnalysisData'];
                    $cam_details_monthly_wise = $account_details['camAnalysisData']['camAnalysisMonthly'];
                    $cheque_Bounces = $account_details['chequeBounces'];
                    $emi = $account_details['emi'];
                    $salary = $account_details['salary'];
                    $fraudIndicators = $account_details['fraudIndicators'];
                    $fraudScore = $account_details['fraudScore'];
                    /*  } else {
                        continue;
                    } */
                }
                $return_data = '';
                /*                 * ********************************** Fraud Indicators ************************************************* */
                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                $return_data .= '<tr><td colspan="' . count($fraudIndicators[0]) . '">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Fraud Indicators &nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i> <div style="color:red;display: inline-block;">(<strong>Message:</strong> ' . @$response_data_array['message'] . ') --- Fraud Score: ' . @$fraudScore . ' </div></h4>';
                $return_data .= '</td></tr>';
                $return_data .= '<tr>';
                $return_data .= '<th>S.NO</th>';
                foreach ($fraudIndicators[0] as $key => $values) {
                    if ($key != 'transactions') {
                        $return_data .= '<th>' . ucwords($key) . '</th>';
                    }
                }
                $return_data .= '</tr>';
                $iFi = 0;
                foreach ($fraudIndicators as $fi_type) {
                    $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                    $return_data .= '<tr>';
                    $return_data .= '<td>' . ($iFi + 1) . '</td>';
                    foreach ($fi_type as $key => $value) {
                        if ($key != 'transactions') {
                            $return_data .= '<td>' . $value . '</td>';
                        } else if (!empty($fraudIndicators[$iFi][$key]) && $key == 'transactions') {
                            $return_data .= '<tr><div class="table-responsive"><table class="table table-bordered table-striped">';

                            $return_data .= '<tr>';
                            foreach ($fraudIndicators[$iFi][$key][0] as $key => $values) {
                                $return_data .= '<th>' . ucwords($key) . '</th>';
                            }
                            $return_data .= '</tr>';
                            if (!empty($fraudIndicators[$iFi]['transactions'])) {
                                foreach ($fraudIndicators[$iFi]['transactions'] as $fi_transactions) {
                                    $return_data .= '<tr>';
                                    foreach ($fi_transactions as $key_fi_tr => $val_fi_tr) {
                                        $return_data .= '<td class="whitespace">' . (($key_fi_tr) ? $key_fi_tr : '-') . '</td>';
                                    }
                                    $return_data .= '</tr>';
                                }
                            }
                            $return_data .= '</table></div></tr>';
                        }
                    }
                    $return_data .= '</tr></table></div>';
                    $iFi++;
                }
                $return_data .= '</table></div>';

                /*                 * ********************************** Fraud Indicators  End ************************************************* */
                /*                 * ************************************* ACCOUNT DETAILS ************************************************* */

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">ACCOUNT&nbsp;DETAILS [' . $api_data->log_nobel_return_id . ']&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';
                $return_data .= '<tr>
                        <th class="whitespace">Bank&nbsp;Name</th>
                        <th class="whitespace">Bank&nbsp;Full&nbsp;Name</th>
                        <th class="whitespace">Account&nbsp;Number</th>
                        <th class="whitespace">Account&nbsp;Holder&nbsp;Name</th>
                        <th class="whitespace">IfscCode</th>
                        <th class="whitespace">Account&nbsp;Type</th>
                        <th class="whitespace">Product&nbsp;Type</th>
                        <th class="whitespace">Period&nbsp;Start</th>
                        <th class="whitespace">Period&nbsp;End</th>
                        <th class="whitespace">Address</th>
                        <th class="whitespace">Email</th>
                        <th class="whitespace">PAN</th>
                        <th class="whitespace">Document&nbsp;Type</th>
                    </tr>';
                $return_data .= '<tr>
                        <td class="whitespace">' . (($account_details['bankName']) ? $account_details['bankName'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['bankFullName']) ? $account_details['bankFullName'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['accountNumber']) ? $account_details['accountNumber'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['accountName']) ? $account_details['accountName'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['ifscCode']) ? $account_details['ifscCode'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['accountType']) ? $account_details['accountType'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['productType']) ? $account_details['productType'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['periodStart']) ? $account_details['periodStart'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['periodEnd']) ? $account_details['periodEnd'] : '-') . '</td>
                        <td class="whitespace">
                            ' . (($account_details['address']) ? substr($account_details['address'], 0, 15) . '... <div class="tooltip" style="user-select: auto;">
                                <i class="fa fa-info-circle" style="user-select: auto;"></i>
                                <span class="tooltiptext" style="user-select: auto;">' . $account_details['address'] . '</span>
                            </div>' : '-') . '
                        </td>
                        <td class="whitespace">' . (($account_details['email']) ? $account_details['email'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['panNumber']) ? $account_details['panNumber'] : '-') . '</td>
                        <td class="whitespace">' . (($account_details['documentType']) ? $account_details['documentType'] : '-') . '</td>
                    </tr>';

                $return_data .= '</table></div>';

                /*                 * ************************************* CAM ANALYSIS END ************************************************* */
                /*                 * ************************************* CAM ANALYSIS ************************************************* */

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">CAM&nbsp;ANALYSIS&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';
                $return_data .= '
                    <tr>
                        <th>OFCC&nbsp;Limit</th>
                        <td>' . round($cam_details['odCcLimit']) . '</td>
                        <th>Inward&nbsp;Return&nbsp;Count</th>
                        <td>' . round($cam_details['inwardReturnCount']) . '</td>
                    </tr>

                    <tr>
                        <th>Out&nbsp;Ward&nbsp;Return&nbsp;Count</th>
                        <td>' . round($cam_details['outwardReturnCount']) . '</td>
                        <th>Inward&nbsp;Return&nbsp;Amount</th>
                        <td>' . round($cam_details['inwardReturnAmount']) . '</td>
                    </tr>

                    <tr>
                        <th>Out&nbsp;Ward&nbsp;Return&nbsp;Amount</th>
                        <td>' . round($cam_details['outwardReturnAmount']) . '</td>
                        <th>Total&nbsp;Net&nbsp;Credits</th>
                        <td>' . round($cam_details['totalNetCredits']) . '</td>
                    </tr>

                    <tr>
                        <th>Average&nbsp;Balance</th>
                        <td>' . round($cam_details['averageBalance']) . '</td>
                        <th>Custom&nbsp;Average&nbsp;Balance</th>
                        <td>' . round($cam_details['customAverageBalance']) . '</td>
                    </tr>

                    <tr>
                        <th>Custom&nbsp;Average&nbsp;Balance&nbsp;Last&nbsp;Three&nbsp;Month</th>
                        <td>' . round($cam_details['customAverageBalanceLastThreeMonth']) . '</td>
                        <th>Average&nbsp;Balance&nbsp;Last&nbsp;Three&nbsp;Month</th>
                        <td>' . round($cam_details['averageBalanceLastThreeMonth']) . '</td>
                    </tr>

                    <tr>
                        <th>Average&nbsp;Balance&nbsp;Last&nbsp;Six&nbsp;Month</th>
                        <td>' . round($cam_details['averageBalanceLastSixMonth']) . '</td>
                        <th>Average&nbsp;Balance&nbsp;Last&nbsp;Twelve&nbsp;Month</th>
                        <td>' . round($cam_details['averageBalanceLastTwelveMonth']) . '</td>
                    </tr>
                    ';
                $return_data .= '</table></div>';

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';

                $return_data .= '<tr>';
                $return_data .= '<th class="whitespace">S.NO</th>';
                foreach ($cam_details_monthly_wise[0] as $key => $values) {
                    $return_data .= '<th class="whitespace">' . ucwords($key) . '</th>';
                }
                $return_data .= '</tr>';

                $return_data .= '<tr>';
                $i = 0;
                foreach ($cam_details_monthly_wise as $index) {
                    $return_data .= '<tr class="table-default">';
                    $return_data .= '<td class="whitespace">' . ($i + 1) . '</td>';
                    foreach ($index as $key => $values) {

                        if ($key == 'customDayBalances') {

                            if (!empty($cam_details_monthly_wise[$i]['customDayBalances'])) {
                                $return_data .= '<td class="whitespace"><table class="table-bordered table-striped"><tr>';
                                foreach ($cam_details_monthly_wise[$i]['customDayBalances'] as $key_day => $key_balance) {
                                    $return_data .= '<td>' . $key_day . " day : " . round($key_balance) . '</td>';
                                }
                                $return_data .= '</tr></table></td>';
                            } else {
                                $return_data .= '<td>-</td>';
                            }
                        } else {
                            $return_data .= '<td class="whitespace">' . $values . '</td>';
                        }
                    }
                    $return_data .= '</tr>';
                    $i++;
                }
                $return_data .= '</tr>';
                $return_data .= '</table></div>';

                /*                 * ********************************** CAM ANALYSIS End ************************************************* */
                /*                 * ********************************** EMI ************************************************* */

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">EMI&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

                $return_data .= '<tr>';
                $return_data .= '<th>S.NO</th>';
                foreach ($emi[0] as $key => $values) {
                    if ($key != 'transactions') {
                        $return_data .= '<th>' . ucwords($key) . '</th>';
                    }
                }

                $ii = 0;
                foreach ($emi as $emi_months) {
                    $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                    $return_data .= '<tr>';
                    $return_data .= '<td>' . ($ii + 1) . '</td>';
                    foreach ($emi_months as $key => $value) {
                        if ($key != 'transactions') {
                            $return_data .= '<td>' . $value . '</td>';
                        } else if (!empty($emi[$ii][$key]) && $key == 'transactions') {
                            $return_data .= '<tr><div class="table-responsive"><table class="table table-bordered table-striped">';

                            $return_data .= '<tr>';
                            foreach ($emi[$ii][$key][0] as $key => $values) {
                                $return_data .= '<th>' . ucwords($key) . '</th>';
                            }
                            $return_data .= '</tr>';

                            foreach ($emi[$ii]['transactions'] as $key_transactions) {
                                $return_data .= '<tr>';
                                foreach ($key_transactions as $key_day => $key_balance) {
                                    if ($key_day == 'transactionDate') {
                                        $return_data .= '<td class="whitespace">' . (date('d-m-Y', substr($key_balance, 0, -3))) . '</td>';
                                    } else {
                                        $return_data .= '<td class="whitespace">' . (($key_balance) ? $key_balance : '-') . '</td>';
                                    }
                                }
                                $return_data .= '</tr>';
                            }
                            $return_data .= '</table></div></tr>';
                        }
                    }
                    $return_data .= '</tr></table></div>';
                    $ii++;
                }
                $return_data .= '</table></div>';

                /*                 * ********************************** EMI End ************************************************* */


                /*                 * ********************************** SALARY ************************************************* */

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">SALARY&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';

                $return_data .= '<tr>';
                $return_data .= '<th>S.NO</th>';
                foreach ($salary[0] as $key => $values) {
                    if ($key != 'transactions') {
                        $return_data .= '<th>' . ucwords($key) . '</th>';
                    }
                }

                $ii = 0;
                foreach ($salary as $salary_months) {
                    $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin: 0;">';
                    $return_data .= '<tr>';
                    $return_data .= '<td>' . ($ii + 1) . '</td>';
                    foreach ($salary_months as $key => $value) {
                        if ($key != 'transactions') {
                            $return_data .= '<td>' . $value . '</td>';
                        } else if (!empty($salary[$ii][$key]) && $key == 'transactions') {
                            $return_data .= '<tr><div class="table-responsive"><table class="table table-bordered table-striped">';

                            $return_data .= '<tr>';
                            foreach ($salary[$ii][$key][0] as $key => $values) {
                                $return_data .= '<th>' . ucwords($key) . '</th>';
                            }
                            $return_data .= '</tr>';

                            foreach ($salary[$ii]['transactions'] as $key_salary_transactions) {
                                $return_data .= '<tr>';
                                foreach ($key_salary_transactions as $key_day => $key_balance) {
                                    if ($key_day == 'transactionDate') {
                                        $return_data .= '<td class="whitespace">' . (date('d-m-Y', substr($key_balance, 0, -3))) . '</td>';
                                    } else {
                                        $return_data .= '<td class="whitespace">' . (($key_balance) ? $key_balance : '-') . '</td>';
                                    }
                                }
                                $return_data .= '</tr>';
                            }
                            $return_data .= '</table></div></tr>';
                        }
                    }
                    $return_data .= '</tr></table></div>';
                    $ii++;
                }
                $return_data .= '</table></div>';

                /*                 * ********************************** SALARY End ************************************************* */
                /*                 * ********************************** Cheque Bounce ************************************************* */

                $return_data .= '<div class="table-responsive"><table class="table table-bordered table-striped">';
                $return_data .= '<h4 class="footer-support" style="color: #1e87c6; font-size: 12px;padding-left: 10px;font-weight: bold;">Cheque&nbsp;Bounce&nbsp;<i class="fa fa-angle-double-down" style="user-select: auto;"></i></h4>';
                $i = 0;
                foreach ($cheque_Bounces as $cheque_months) {
                    foreach ($cheque_months as $key => $value) {
                        $return_data .= '<tr>';
                        if ($key == 'month') {
                            $return_data .= '<th>' . $value . '</th>';
                        } else if (!empty($cheque_Bounces[$i]['transactions']) && $key == 'transactions') {
                            $return_data .= '<td><span>Transection</span><table class="table-bordered table-striped"><tr>';
                            foreach ($cheque_Bounces[$i]['transactions'] as $key_day => $key_balance) {
                                $return_data .= '<td>' . $key_day . " : " . round($key_balance) . '</td>';
                            }
                            $return_data .= '</tr></table></td>';
                        } else {
                            $return_data .= '<td>&nbsp;&nbsp;&nbsp;-</td>';
                        }
                        $return_data .= '</tr>';
                    }
                    $i++;
                }
                $return_data .= '</table></div>';

                /*                 * ********************************** Cheque Bounce End ************************************************* */

                $responseArray['data'] = $return_data;

                /*                 * ******************Check Salary Details in CAM ************************************** */
                $salary = array_reverse($salary);

                $update_monthly_salary_count = 1;

                $iii = 0;

                $cam_salary_data = $ci->db->query('SELECT salary_credit1_date, salary_credit1_amount, salary_credit2_date, salary_credit2_amount, salary_credit3_date, salary_credit3_amount
                FROM `credit_analysis_memo` where lead_id=' . $lead_id);

                $cam_salary_data = $cam_salary_data->row();

                $salary_credit1_date = (empty($cam_salary_data->salary_credit1_date) || $cam_salary_data->salary_credit1_date == 0) ? 0 : $cam_salary_data->salary_credit1_date;
                $salary_credit2_date = (empty($cam_salary_data->salary_credit2_date) || $cam_salary_data->salary_credit2_date == 0) ? 0 : $cam_salary_data->salary_credit2_date;
                $salary_credit3_date = (empty($cam_salary_data->salary_credit3_date) || $cam_salary_data->salary_credit3_date == 0) ? 0 : $cam_salary_data->salary_credit3_date;
                $salary_credit1_amount = (empty($cam_salary_data->salary_credit1_amount) || $cam_salary_data->salary_credit1_amount == 0) ? 0 : $cam_salary_data->salary_credit1_amount;
                $salary_credit2_amount = (empty($cam_salary_data->salary_credit2_amount) || $cam_salary_data->salary_credit2_amount == 0) ? 0 : $cam_salary_data->salary_credit2_amount;
                $salary_credit3_amount = (empty($cam_salary_data->salary_credit3_amount) || $cam_salary_data->salary_credit3_amount == 0) ? 0 : $cam_salary_data->salary_credit3_amount;

                foreach ($salary as $salary_months) {
                    foreach ($salary_months as $key => $value) {
                        if ($update_monthly_salary_count <= 3) {
                            if ($key == 'transactions') {
                                foreach ($salary[$iii]['transactions'] as $key_salary_transactions) {
                                    foreach ($key_salary_transactions as $key_day => $key_balance) {
                                        if ($update_monthly_salary_count == 1) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit1_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit1_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        } else if ($update_monthly_salary_count == 2) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit2_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit2_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        } else if ($update_monthly_salary_count == 3) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit3_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit3_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                    }
                    $update_monthly_salary_count++;
                    $iii++;
                }

                $update_data = array();
                //}
                if ($salary_credit1_date == 0) {
                    $update_data['salary_credit1_date'] = $response_data['salary_credit1_date'];
                } else {
                    $update_data['salary_credit1_date'] = $salary_credit1_date;
                }
                if ($salary_credit2_date == 0) {
                    $update_data['salary_credit2_date'] = $response_data['salary_credit2_date'];
                } else {
                    $update_data['salary_credit2_date'] = $salary_credit2_date;
                }
                if ($salary_credit3_date == 0) {
                    $update_data['salary_credit3_date'] = $response_data['salary_credit3_date'];
                } else {
                    $update_data['salary_credit3_date'] = $salary_credit3_date;
                }
                if ($salary_credit1_amount == 0) {
                    $update_data['salary_credit1_amount'] = $response_data['salary_credit1_amount'];
                } else {
                    $update_data['salary_credit1_amount'] = $salary_credit1_amount;
                }
                if ($salary_credit2_amount == 0) {
                    $update_data['salary_credit2_amount'] = $response_data['salary_credit2_amount'];
                } else {
                    $update_data['salary_credit2_amount'] = $salary_credit2_amount;
                }
                if ($salary_credit3_amount == 0) {
                    $update_data['salary_credit3_amount'] = $response_data['salary_credit3_amount'];
                } else {
                    $update_data['salary_credit3_amount'] = $salary_credit3_amount;
                }

                $update_data['median_salary'] = round(($update_data['salary_credit1_amount'] + $update_data['salary_credit2_amount'] + $update_data['salary_credit3_amount']) / 3);

                if (!empty($update_data)) {
                    $ci->db->update("credit_analysis_memo", $update_data, array("lead_id" => $lead_id));
                }

                /*                 * ******************Check Salary Details in CAM End************************************** */
            } else if (in_array(strtolower($response_data_array['status']), ['rejected'])) {
                $responseArray['error_msg'] = $response_data_array['message'];
            } else if (strpos(strtolower($response_data_array['message']), 'fraud') !== false) {
                $responseArray['error_msg'] = $response_data_array['message'];
            }

            // echo "response_data_array : <pre>"; print_r($response_data_array); exit;
        } else {
            $responseArray['error_msg'] = "Document not found. Please Try Again.";
        }

        return $responseArray;
    }
}
