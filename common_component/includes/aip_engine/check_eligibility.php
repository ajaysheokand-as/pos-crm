<?php

function check_customer_eligibility($lead_id) {
    common_log_writer(1, "check_customer_eligibility started | $lead_id");
    $return_array = array("status" => 0, "error" => "");

    $apiStatusId = 0;

    $errorMessage = "";
    $state_data = "";
    $city_data = "";
    $black_listed_data = "";
    $rejected_listed_data = "";
    $duplicate_listed_data = "";
    $employment_data = "";
    $data_array = array();

    $leadModelObj = new LeadModel();

    try {

        if (empty($lead_id)) {
            throw Exception("Missing Lead Id");
        }

        $LeadDetails = $leadModelObj->getLeadDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        $state_id = $app_data['state_id'];
        $city_id = $app_data['city_id'];
        $dob = $app_data['dob'];
        $user_type = $app_data['user_type'];
        $lead_status_id = $app_data['lead_status_id'];
        $monthly_salary_amount = $app_data['monthly_salary_amount'];
        $pancard = $app_data['pancard'];

        $stateDetails = $leadModelObj->getStateDetails($state_id);

        if ($stateDetails['status'] == 1) {
            $state_data = $stateDetails['state_data'];
        }

        $cityDetails = $leadModelObj->getCityDetails($city_id);

        if ($cityDetails['status'] == 1) {
            $city_data = $cityDetails['city_data'];
        }


        $customerEmploymentDetails = $leadModelObj->getCustomerEmploymentDetails($lead_id);
        if ($customerEmploymentDetails['status'] == 1) {
            $employment_data = $customerEmploymentDetails['emp_data'];
        }

        $blackListedDetails = $leadModelObj->checkBlackListedCustomer($lead_id);
        if ($blackListedDetails['status'] == 1) {
            $black_listed_data = $blackListedDetails['message'];
        }

        $settlementDetails = $leadModelObj->GetSettlementDetails($pancard);
        if ($settlementDetails['status'] == 1) {
            $settlementData = $settlementDetails['settlement_data'];
        }

        $dpdDetails = $leadModelObj->getDPDDetails($pancard);
        if ($dpdDetails['status'] == 1) {
            $dpdData = $dpdDetails['dpd_data'];
        }

        //if Rejection happend in last three month
        $rejectedDetails = $leadModelObj->checkCustomerRejected($lead_id);
        if ($rejectedDetails['status'] == 1) {
            $rejected_listed_data = $rejectedDetails['message'];
        }

        //if Repeat customer
        //        $repeatCustomerDetails = $leadModelObj->checkRepeatCustomer($lead_id);
        //        if ($repeatCustomerDetails['status'] == 1) {
        //            $repeat_listed_data = $repeatCustomerDetails['message'];
        //        }
        //Duplicate lead
        $duplicateDetails = $leadModelObj->checkCustomerDedupe($lead_id);

        if ($duplicateDetails['status'] == 1) {
            $duplicate_listed_data = $duplicateDetails['message'];
        }

        //Age rule 21-55 allowed
        $current_date = strtotime(date("Y-m-d"));
        $age = ($current_date - strtotime($dob));
        $age = intval(($age / (60 * 60 * 24 * 365.25))); //21-55 allow
        //Eligibility rules checking start
        $eligibility_status = true;
        $eligibility_remark = "Eligibility Rules";

        $dob_flag = 0; //1=>pass,2=>failed
        $city_flag = 0;
        $state_flag = 0;
        $cust_blacklisted_flag = 0;
        $cust_reject_flag = 0;
        $cust_repeat_flag = 0;
        $cust_income_flag = 0;
        $cust_emp_type_flag = 0;
        $cust_duplicate_flag = 0;

        if (!empty($employment_data['income_type']) && $employment_data['income_type'] == 1) {
            $eligibility_remark .= "<br>Employment Type : Salaried | Status : Pass";
            $cust_emp_type_flag = 1;
        } else if (!empty($employment_data['income_type']) && $employment_data['income_type'] == 2) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>Employment Type : Self-Employed | Status : Fail";
            $cust_emp_type_flag = 2;
            $rejection_id = 8;
        } else {
            $eligibility_remark .= "<br>Employment Type : Not available | Status : NA";
        }

        if (!empty($monthly_salary_amount) && $monthly_salary_amount >= 35000) {
            $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $monthly_salary_amount . " | Status : Pass";
            $cust_income_flag = 1;
        } else {
            $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $monthly_salary_amount . " | Status : Fail";
            $eligibility_status = false;
            $cust_income_flag = 2;
            $rejection_id = 14;
        }
        // else if (!empty($monthly_salary_amount) && $monthly_salary_amount <= 25000  && $monthly_salary_amount > 0) {
        //     $eligibility_status = false;
        //     $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $monthly_salary_amount . " | Status : Fail";
        //     $cust_income_flag = 2;
        //     $rejection_id = 14;
        // }

        // if ($user_type == "NEW" && !empty($employment_data['monthly_income']) && $employment_data['monthly_income'] >= 25000) {
        //     $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $employment_data['monthly_income'] . " | Status : Pass";
        //     $cust_income_flag = 1;
        // } else if ($user_type == "NEW" && !empty($employment_data['monthly_income']) && $employment_data['monthly_income'] < 25000) {
        //     $eligibility_status = false;
        //     $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $employment_data['monthly_income'] . " | Status : Fail";
        //     $cust_income_flag = 2;
        // } else if ($user_type == "REPEAT" && !empty($employment_data['monthly_income']) && $employment_data['monthly_income'] >= 15000) {
        //     $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $employment_data['monthly_income'] . " | Status : Pass";
        //     $cust_income_flag = 1;
        // } else if ($user_type == "REPEAT" && !empty($employment_data['monthly_income']) && $employment_data['monthly_income'] < 15000) {
        //     $eligibility_status = false;
        //     $eligibility_remark .= "<br>Monthly Salary - $user_type : " . $employment_data['monthly_income'] . " | Status : Fail";
        //     $cust_income_flag = 2;
        // } else {
        //     $eligibility_remark .= "<br>Monthly Salary : Not available | Status : NA";
        // }

        if (!empty($app_data['dob']) && ($app_data['dob'] != '0000-00-00') && !empty($age) && ($age >= 21 && $age <= 55)) {
            $eligibility_remark .= "<br>DOB : " . $app_data['dob'] . "| Age : $age | Status : Pass";
            $dob_flag = 1;
        } else if (!empty($app_data['dob']) && ($app_data['dob'] != '0000-00-00') && !empty($age) && ($age < 21 || $age > 55)) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>DOB : " . $app_data['dob'] . "| Age : $age | Status : Fail";
            $dob_flag = 2;
            $rejection_id = 54;
        } else {
            $eligibility_remark .= "<br>DOB : Not available | Status : NA";
        }

        if (!empty($state_data) && $state_data['m_state_is_sourcing'] == 1) {
            $eligibility_remark .= "<br>State : " . $state_data['m_state_name'] . " | Status : Pass";
            $state_flag = 1;
        } else if (!empty($state_data)) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>State : " . $state_data['m_state_name'] . " | Status : Fail";
            $state_flag = 2;
            $rejection_id = 2;
        } else {
            $eligibility_remark .= "<br>State : Not available | Status : NA";
        }

        if (!empty($city_data) && $city_data['m_city_is_sourcing'] == 1) {
            $eligibility_remark .= "<br>City : " . $city_data['m_city_name'] . " | Status : Pass";
            $city_flag = 1;
        } else if (!empty($city_data)) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>City : " . $city_data['m_city_name'] . " | Status : Fail";
            $city_flag = 2;
            $rejection_id = 2;
        } else {
            $eligibility_remark .= "<br>City : Not available | Status : NA";
        }

        if (!empty($black_listed_data)) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>Blacklisted : " . $black_listed_data;
            $cust_blacklisted_flag = 2;
            $rejection_id = 41;
        } else {
            $cust_blacklisted_flag = 1;
            $eligibility_remark .= "<br>Blacklisted : Not available | Status : Pass";
        }


        if (!empty($rejected_listed_data)) {
            $eligibility_status = false;
            $eligibility_remark .= "<br>Rejected Customer : " . $rejected_listed_data;
            $cust_reject_flag = 2;
            $rejection_id = 1;
        } else {
            $cust_reject_flag = 1;
            $eligibility_remark .= "<br>Rejected Customer : Not available | Status : Pass";
        }

        if (!empty($duplicate_listed_data) && COMP_ENVIRONMENT == 'production') {
            $eligibility_status = false;
            $eligibility_remark .= "<br>Duplicate Customer : " . $duplicate_listed_data;
            $cust_duplicate_flag = 2;
            $rejection_id = 1;
        } else {
            $cust_duplicate_flag = 1;
            $eligibility_remark .= "<br>Duplicate Customer : Not available | Status : Pass";
        }

        //        if (!empty($repeat_listed_data)) {
        //            $eligibility_status = false;
        //            $eligibility_remark .= "<br>Repeat Customer : " . $repeat_listed_data;
        //            $cust_repeat_flag = 2;
        //        } else {
        //            $cust_repeat_flag = 1;
        //            $eligibility_remark .= "<br>Repeat Customer : Not available | Status : Pass";
        //        }

        if ($settlementData['lead_id'] > 0) {
            $eligibility_status = false;
            $loan_ative_flag = 2;
            $eligibility_remark .= "<br>Loan Active : Loan is Settled - " . $settlementData['loan_no'] . " | Status : Fail";
            $rejection_id = 3;
        } else {
            $loan_ative_flag = 1;
            $eligibility_remark .= "<br>Loan Active : Loan is not active | Status : Pass";
        }

        if ($dpdData['DPD'] > 15) {
            $eligibility_status = false;
            $loan_ative_flag = 2;
            $eligibility_remark .= "<br>Loan DPD : " . $dpdData['DPD'] . " | Loan No : " . $dpdData['loan_no'] . " | Status : Fail";
            $rejection_id = 35;
        }

        $data_array['cust_data'] = $app_data;
        $data_array['state_data'] = $state_data;
        $data_array['city_data'] = $city_data;
        $data_array['age'] = $age;
        $data_array['black_listed_data'] = $black_listed_data;
        $data_array['rejected_listed_data'] = $rejected_listed_data;
        //        $data_array['repeat_listed_data'] = $repeat_listed_data;
        $data_array['duplicate_listed_data'] = $duplicate_listed_data;
        $data_array['employment_data'] = $employment_data;

        if ($eligibility_status == false) {
            $lead_status_id = 8;

            $update_array = array();
            $update_array['status'] = 'SYSTEM-REJECT';
            $update_array['stage'] = 'S8';
            $update_array['lead_status_id'] = $lead_status_id;
            $update_array['updated_on'] = date("Y-m-d H:i:s");
            $update_array['lead_rejected_reason_id'] = $rejection_id;
            $update_array['lead_rejected_datetime'] = date("Y-m-d H:i:s");

            $leadModelObj->updateLeadTable($lead_id, $update_array);
            $apiStatusId = 2;
            $errorMessage = "Eligibility Failed";
            $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $eligibility_remark);

            $eligibility_insert_array = array();
            $eligibility_insert_array['lerr_lead_id'] = $lead_id;
            $eligibility_insert_array['lerr_dob_flag'] = $dob_flag;
            $eligibility_insert_array['lerr_city_flag'] = $city_flag;
            $eligibility_insert_array['lerr_state_flag'] = $state_flag;
            $eligibility_insert_array['lerr_cust_blacklisted_flag'] = $cust_blacklisted_flag;
            $eligibility_insert_array['lerr_cust_reject_flag'] = $cust_reject_flag;
            $eligibility_insert_array['lerr_cust_repeat_flag'] = $cust_repeat_flag;
            $eligibility_insert_array['lerr_cust_income_flag'] = $cust_income_flag;
            $eligibility_insert_array['lerr_cust_duplicate_flag'] = $cust_duplicate_flag;
            $eligibility_insert_array['lerr_emp_type_flag'] = $cust_emp_type_flag;
            $eligibility_insert_array['lerr_loan_flag'] = $loan_ative_flag;
            $eligibility_insert_array['lerr_created_on'] = date("Y-m-d H:i:s");

            $leadModelObj->insertTable('lead_eligibility_rules_result', $eligibility_insert_array);

            $email_status = send_eligibility_failed_email($lead_id);
        } else {
            $apiStatusId = 1;
        }
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    $return_array = array("status" => $apiStatusId, "error" => $errorMessage, "check_data" => $data_array, "eligibility_remark" => $eligibility_remark, 'email_status' => $email_status);
    common_log_writer(1, "return lead_id = $lead_id | response====" . json_encode($return_array));
    common_log_writer(1, "check_customer_eligibility end");
    return $return_array;
}

function send_eligibility_failed_email($lead_id) {

    $return_array = array();

    $leadModelObj = new LeadModel();
    $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

    $email = $LeadDetails['app_data']['email'];

    if (empty($email)) {
        $return_array['Status'] = 0;
        $return_array['Message'] = 'Email id required.';
        return $return_array;
    } else {

        $subject = 'Eligibility Failed - Tejas Loan';

        $html = '<!DOCTYPE html>
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Eligibility Failed</title>
                    </head>

                    <body>
                        <table width="400" border="0" align="center" style="font-family:Arial, Helvetica, sans-serif; border:solid 1px #ddd; padding:10px; background:#f9f9f9;">
                            <tr>
                                <td width="775" align="center"><img src="'. EMAIL_BRAND_LOGO .'" width="30%" alt="Brand Logo"></td>
                            </tr>
                            <tr>
                                <td style="text-align:center;">
                                    <table width="418" border="0" style="text-align:center; padding:20px; background:#fff;">
                                        <tr>
                                            <td style="font-size:16px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="412" style="font-size:16px;">
                                                <h2 style="margin:0px; color:#116a97;">Eligibility Failed</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="line-height:25px; margin:0px;">
                                                    We are not able to process your loan application due to our internal policy and have made no determination about your credibility.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">&nbsp;</td>
                            </tr>
                        </table>
                    </body>
                </html>';

        $email_status = common_send_email($email, $subject, $html);

        if ($email_status) {
            $return_array['email_status'] = $email_status;
            $return_array['Status'] = 1;
            $return_array['Message'] = 'Email sent successfully.';
        }

        return $return_array;
    }
}
