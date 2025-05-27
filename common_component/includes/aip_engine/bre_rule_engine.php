<?php

function bre_quote_engine($lead_id, $request_array = array()) {
    common_log_writer(6, "bre_quote_engine started | $lead_id");

    $return_array = array("status" => 0, "error" => "", "max_loan_amount" => 0, "min_loan_amount" => 0, "min_loan_tenure" => 7,  "max_loan_tenure" => 0, "interest_rate" => 0, "processing_fee" => 0);

    $max_loan_amount = 0;
    $max_loan_tenure = 0;
    $return_status_id = 0;
    $errorMessage = "";

    $breRuleModelObj = new BreRuleModel();

    try {

        if (empty($lead_id)) {
            throw Exception("Missing Lead Id");
        }

        $LeadDetails = $breRuleModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        $customerEmploymentDetails = $breRuleModelObj->getCustomerEmploymentDetails($lead_id);
        if ($customerEmploymentDetails['status'] == 1) {
            $employment_data = $customerEmploymentDetails['emp_data'];
        }

        $customer_obligations = $app_data['obligations'];
        $customer_office_email = $app_data['alternate_email'];
        $customer_residence_type = $app_data['current_residence_type'];
        $current_city_id = $app_data['city_id'];
        $current_city_category = "";
        $current_city_is_sourcing = "";
        $current_city_name = "";

        $customer_monthly_income = $employment_data['monthly_income'];

        $cityDetails = $breRuleModelObj->getCityDetails($current_city_id);

        if ($cityDetails['status'] == 1) {
            $city_data = $cityDetails['city_data'];
            $current_city_name = trim($city_data['m_city_name']);
            $current_city_category = trim($city_data['m_city_category']);
            $current_city_is_sourcing = $city_data['m_city_is_sourcing'];
        } else {
            throw Exception("Missing city.");
        }

        $camDetails = $breRuleModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] == 1) {
            $cam_data = $camDetails['cam_data'];
            $next_pay_date = $cam_data["next_pay_date"];
        }

        $monthly_salary = !empty($cam_data['cam_appraised_monthly_income']) ? $cam_data['cam_appraised_monthly_income'] : $customer_monthly_income;

        $obligations = $customer_obligations;

        $foir_percent = 0;

        if (($current_city_category == 'A') && $monthly_salary >= 50000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.7;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.75;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.8;
            }
        } else if (($current_city_category == 'A') && $monthly_salary >= 30000 && $monthly_salary < 50000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.6;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.65;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.7;
            }
        } else if (($current_city_category == 'A') && $monthly_salary >= 15000 && $monthly_salary <= 30000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.5;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.55;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.6;
            }
        } else if (($current_city_category == 'A') && $monthly_salary < 15000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0;
            }
        } else if (($current_city_category == 'B') && $monthly_salary >= 50000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.75;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.8;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.85;
            }
        } else if (($current_city_category == 'B') && $monthly_salary >= 30000 && $monthly_salary < 50000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.65;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.7;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.75;
            }
        } else if (($current_city_category == 'B') && $monthly_salary >= 15000 && $monthly_salary < 30000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.55;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.6;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.65;
            }
        } else if (($current_city_category == 'B') && $monthly_salary >= 10000 && $monthly_salary < 15000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0.45;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.50;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0.55;
            }
        } else if (($current_city_category == 'B') && $monthly_salary < 10000) {
            if (($customer_office_email == "" || $customer_office_email == null) && ($customer_residence_type == "" || $customer_residence_type == null)) {
                $foir_percent = 0;
            } else if (($customer_office_email != "" || $customer_office_email != null) || ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0;
            } else if (($customer_office_email != "" || $customer_office_email != null) && ($customer_residence_type != "" || $customer_residence_type != null)) {
                $foir_percent = 0;
            }
        }

        $eligible_foir_percentage = number_format($foir_percent * 100, 2);
        $max_loan_amount = round((($monthly_salary - $obligations) * $foir_percent), 0);
        $min_loan_amount = 5000;

        $return_status_id = 1;
    } catch (Exception $e) {
        $return_status_id = 4;
        $errorMessage = $e->getMessage();
    }

    $return_array = array("status" => $return_status_id, "error" => $errorMessage, "max_loan_amount" => $max_loan_amount, "min_loan_amount" => $min_loan_amount, "min_loan_tenure" => 7,  "max_loan_tenure" => 40, "interest_rate" => 0.75, "processing_fee" => 15, "eligible_foir_percentage" => $eligible_foir_percentage);

    return $return_array;
}

function bre_rule_engine($lead_id, $request_array = array()) {

    common_log_writer(6, "bre_rule_engine started | $lead_id");

    $return_array = array("status" => 0, "error" => "", "bre_decision_status_id" => 0, "bre_decision_status" => "");

    $master_bre_decision_status = [1 => "Approve", 2 => "Refer", 3 => 'Reject', 4 => "BRE Error", 5 => "Unkown Error"];

    $bre_decision_status_id = 0;

    $errorMessage = "";
    $city_data = "";
    $bre_rule_result_data = "";
    $black_listed_data = "";
    $customer_reference_data = "";
    $aadhaar_log_data = "";
    $pan_log_data = "";
    $cam_data = "";
    $active_loan_data = "";
    $aadhaar_data = "";
    $customer_aadhaar_dob = "";
    $customer_pancard_dob = "";
    $employment_data = "";
    $bank_account_data = "";
    $bank_analysis_data = "";
    $user_type = "";
    $pancard_ocr_data = "";

    $master_employment = [1 => "Salaried", 2 => "Self Employed"];

    $breRuleModelObj = new BreRuleModel();

    try {

        if (empty($lead_id)) {
            throw Exception("Missing Lead Id");
        }

        $LeadDetails = $breRuleModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $breRuleModelObj->updateTable("lead_bre_rule_result", ["lbrr_active" => 0, "lbrr_deleted" => 1, "lbrr_updated_on" => date("Y-m-d H:i:s")], " lbrr_lead_id=$lead_id AND lbrr_active=1");
        // print_r($breRuleModelObj); die;
        $app_data = $LeadDetails['app_data'];

        $customerEmploymentDetails = $breRuleModelObj->getCustomerEmploymentDetails($lead_id);
        if ($customerEmploymentDetails['status'] == 1) {
            $employment_data = $customerEmploymentDetails['emp_data'];
        }

        $aadhaarEkycDetails = $breRuleModelObj->getAadhaarEkycDetails($lead_id);
        if ($aadhaarEkycDetails['status'] == 1) {
            $aadhaar_data = json_decode($aadhaarEkycDetails['aadhaar_data']['ekyc_response'], true);
            if (!empty($aadhaar_data['result']['dob'])) {
                $aadhaar_dob = str_replace("/", "-", $aadhaar_data['result']['dob']);
                $customer_aadhaar_dob = date("Y-m-d", strtotime($aadhaar_dob));
            }
        }

        $panOcrDetails = $breRuleModelObj->getPanOCRResponse($lead_id);

        if ($panOcrDetails['status'] == 1) {
            $pancard_ocr_data = json_decode($panOcrDetails['pan_data']['poi_ocr_response'], true);
            if (!empty($pancard_ocr_data['response']['result']['dob'])) {
                $pancard_dob = str_replace("/", "-", $pancard_ocr_data['response']['result']['dob']);
                $customer_pancard_dob = date("Y-m-d", strtotime($pancard_dob));
            }
        }

        $bankAnalysisDetails = $breRuleModelObj->getBankAnalysisDetails($lead_id);

        if ($bankAnalysisDetails['status'] == 1) {

            $bank_analysis_data = $bankAnalysisDetails['bank_data'];
            //            common_log_writer(6, "$lead_id || cart_response => " . $bank_analysis_data['cart_response']);
            $bank_analysis_data = $bank_analysis_data['cart_response'];
            //$bank_analysis_data = str_replace('\"', '"', $bank_analysis_data);
            //$bank_analysis_data = str_replace("\'", " ", $bank_analysis_data);
            $bank_analysis_data = stripslashes($bank_analysis_data);
            $bank_analysis_data = str_replace('\\', " - ", $bank_analysis_data);

            $bank_analysis_data_temp = json_decode($bank_analysis_data, true);

            common_log_writer(6, "$lead_id || Step1 || " . json_last_error() . " =>" . json_last_error_msg());

            if (json_last_error() == 4) {
                $bank_analysis_data_temp = json_decode($bank_analysis_data, true, 512, JSON_INVALID_UTF8_IGNORE);
                common_log_writer(6, "$lead_id || Step2 || " . json_last_error() . " =>" . json_last_error_msg());
            }

            $bank_analysis_data = $bank_analysis_data_temp;

            $bank_analysis_account_no = trim($bank_analysis_data['data'][0]['accountNumber']);
            $bank_analysis_account_ifsc_code = strtoupper(trim($bank_analysis_data['data'][0]['ifscCode']));
            $bank_analysis_fraudScore = $bank_analysis_data['data'][0]['fraudScore'];
            $bank_analysis_average_balance = round(trim($bank_analysis_data['data'][0]['camAnalysisData']['averageBalanceLastSixMonth']));
        }

        $bankDetails = $breRuleModelObj->getCustomerBankAccountDetails($lead_id);

        if ($bankDetails['status'] == 1) {

            $banking_data = $bankDetails['banking_data'];

            $ifsc_code = trim($banking_data['ifsc_code']);
            $beneficiary_name = strtoupper(trim($banking_data['beneficiary_name']));
            $account = trim($banking_data['account']);
        }

        $bankAggregatorDetails = $breRuleModelObj->getAggregatorData($lead_id);

        if ($bankAggregatorDetails['status'] == 1) {

            $aggregatorjson = $bankAggregatorDetails['aggregator_data']['aa_response'];
            $aggregatorjson = stripslashes($aggregatorjson);
            $aggregatorData = json_decode($aggregatorjson, true);

            $aggregatorData = $aggregatorData['result']['body'][0];

            $aa_current_balance = $aggregatorData['fiObjects'][0]['Summary']['currentBalance'];
            $aa_ifscCode = $aggregatorData['fiObjects'][0]['Summary']['ifscCode'];

            $aa_name = $aggregatorData['fiObjects'][0]['Profile']['Holders']['Holder']['name'];
            $aa_fipName = strtoupper(trim($aggregatorData['fipName']));
        }

        $pennyDropDetails = $breRuleModelObj->getBankAccountVerificationDetails($lead_id);

        if ($pennyDropDetails['status'] == 1) {
            $bank_account_data = $pennyDropDetails['bank_acc_data'];
            $bank_account_data = str_replace('\"', '"', $bank_account_data['bav_response']);
            $bank_account_data = json_decode($bank_account_data, true, 512, JSON_INVALID_UTF8_IGNORE);
            $bank_account_request_db = $pennyDropDetails['bank_acc_data'];
            $bank_account_request_data = str_replace('\"', '"', $bank_account_request_db['bav_request']);
            $bank_account_request_data = json_decode($bank_account_request_data, true, 512, JSON_INVALID_UTF8_IGNORE);
            // $bank_account_number = trim($bank_account_data['essentials']['beneficiaryAccount']);
            $bank_account_number = trim($bank_account_request_data['beneficiaryAccount']);
            // $bank_account_ifsc_code = strtoupper(trim($bank_account_data['essentials']['beneficiaryIFSC']));
            $bank_account_ifsc_code = strtoupper(trim($bank_account_data['result']['bankTransfer']['beneIFSC']));
            $bank_account_status = strtoupper($bank_account_data['result']['active']);
            $bank_account_name_match_status = strtoupper($bank_account_data['result']['nameMatch']);
            $bank_account_name_match_score = strtoupper($bank_account_data['result']['nameMatchScore']);
            $bank_account_customer_name = strtoupper($bank_account_data['result']['bankTransfer']['beneName']);
        }

        $bureauDetails = $breRuleModelObj->getBureauDetails($lead_id);

        if ($bureauDetails['status'] == 1) {
            $bureau_data = $bureauDetails['bureau_data'];
            $bureau_score = $bureau_data['score'];
            $over_due_accounts = $bureau_data['over_due_accounts'];
            $pan_variation_count = $bureau_data['pan_variation_count'];
            $inquiries_last_6_months = $bureau_data['inquiries_last_6_months'];
            $variation_pan_cards = $bureau_data['variation_pan_cards'];
        }


        //  $blackListedDetails = $breRuleModelObj->checkBlackListedCustomer($lead_id);

        // if ($blackListedDetails['status'] == 1) {
        //     $black_listed_data = $blackListedDetails['message'];
        // }

        $customerReferenceDetails = $breRuleModelObj->getCustomerReferenceDetails($lead_id);

        if ($customerReferenceDetails['status'] == 1) {
            $customer_reference_data = $customerReferenceDetails['customer_reference_data'];
        }

        $aadhaarOCRDetails = $breRuleModelObj->getAadhaarOCRLastApiLog($lead_id);

        if ($aadhaarOCRDetails['status'] == 1) {
            $aadhaar_log_data = $aadhaarOCRDetails['aadhaar_log_data'];
        }

        $panVerifyDetails = $breRuleModelObj->getPanValidateLastApiLog($lead_id);

        if ($panVerifyDetails['status'] == 1) {
            $pan_log_data = $panVerifyDetails['pan_log_data'];
        }


        $customer_dob = $app_data['dob'];

        $customer_mobile = $app_data['mobile'];
        $user_type = $app_data['user_type'];
        $applied_loan_amount = $app_data['loan_amount'];
        $applied_loan_tenure = $app_data['tenure'];
        $current_residence_since = $app_data['current_residence_since'];
        $lead_data_source_id = $app_data['lead_data_source_id'];

        $mobile_verified_status = (trim(strtoupper($app_data['mobile_verified_status'])) == "YES") ? "YES" : "NO";

        $customer_personal_email = trim(strtoupper($app_data['email']));
        $customer_personal_email_verified_status = (trim(strtoupper($app_data['email_verified_status'])) == "YES") ? "YES" : "NO";

        $customer_office_email = trim(strtoupper($app_data['alternate_email']));
        $customer_office_email_verified_status = (trim(strtoupper($app_data['alternate_email_verified_status'])) == "YES") ? "YES" : "NO";

        $customer_aadhar_no = $app_data['aadhar_no'];
        $customer_digital_ekyc_flag = $app_data['customer_digital_ekyc_flag'];
        $customer_residence_pincode = $app_data['cr_residence_pincode'];
        $customer_aadhaar_pincode = $app_data['aa_cr_residence_pincode'];
        $aadhaar_ocr_verified_status = $app_data['aadhaar_ocr_verified_status'];

        $customer_pancard = $app_data['pancard'];
        $pancard_verified_status = $app_data['pancard_ocr_verified_status'];
        $pancard_ocr_verified_status = $app_data['pancard_ocr_verified_status'];

        $current_city_id = $app_data['city_id'];
        $current_city_category = "";
        $current_city_is_sourcing = "";
        $current_city_name = "";

        $customer_employment_since = !empty($employment_data['emp_residence_since']) ? $employment_data['emp_residence_since'] : "";
        $customer_employment_id = $employment_data['income_type'];
        $customer_salary_mode = trim($employment_data['salary_mode']);
        $customer_monthly_income = $employment_data['monthly_income'];
        $customer_linkedin_url = $employment_data['emp_linkedin_url'];
        $customer_linkedin_url_status = !empty($employment_data['emp_linkedin_url']) ? "Yes" : "No";

        $cityDetails = $breRuleModelObj->getCityDetails($current_city_id);

        // print_r($cityDetails);

        if ($cityDetails['status'] == 1) {
            $city_data = $cityDetails['city_data'];
            $current_city_name = trim($city_data['m_city_name']);
            $current_city_category = trim($city_data['m_city_category']);
            $current_city_is_sourcing = $city_data['m_city_is_sourcing'];
        }

        $camDetails = $breRuleModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] == 1) {
            $cam_data = $camDetails['cam_data'];
        }

        $activeLoanDetails = $breRuleModelObj->getActiveLoanByPancard($customer_pancard);

        if ($activeLoanDetails['status'] == 2) {
            $active_loan_data = $activeLoanDetails['lead_details'];
        }

        $recommend_loan_amount = $cam_data['loan_recommended'];
        $recommend_loan_tenure = $cam_data['tenure'];
        $eligible_loan = $cam_data['eligible_loan'];
        $customer_monthly_income = !empty($cam_data['cam_appraised_monthly_income']) ? $cam_data['cam_appraised_monthly_income'] : $customer_monthly_income;

        $current_date = strtotime(date("Y-m-d"));
        $customer_age_calc = ($current_date - strtotime($customer_dob));
        $customer_age = intval(($customer_age_calc / (60 * 60 * 24 * 365.25)));

        $dob_rule_id = 1;
        $dob_rule_name = "Age Criteria";
        $dob_rule_cutoff_value = ">=21 && <= 50";
        $dob_rule_actual_value = ['applicant_age' => $customer_age];
        $dob_rule_relevant_inputs = ['applicant_dob' => date("d-m-Y", strtotime($customer_dob)), 'calculated_on' => date("d-m-Y")];
        $dob_rule_system_decision_id = 0;
        $dob_rule_manual_decision_id = 0;

        if (!empty($customer_dob) && ($customer_dob != '0000-00-00')) {

            if ($customer_age >= 21 && $customer_age <= 50) {
                $dob_rule_system_decision_id = 1;
                $dob_rule_manual_decision_id = 1;
            } else if (($customer_age >= 20 && $customer_age < 21) || ($customer_age > 50 && $customer_age <= 60)) {
                $dob_rule_system_decision_id = 2;
                $dob_rule_manual_decision_id = 2;
            } else if (($customer_age < 20 || $customer_age > 60)) {
                $dob_rule_system_decision_id = 3;
                $dob_rule_manual_decision_id = 3;
            }
        }

        insertBreRuleResult($lead_id, $dob_rule_id, $dob_rule_name, $dob_rule_cutoff_value, $dob_rule_actual_value, $dob_rule_relevant_inputs, $dob_rule_system_decision_id, $dob_rule_manual_decision_id);

        $emp_type_rule_id = 2;
        $emp_type_rule_name = "Employment Type";
        $emp_type_rule_cutoff_value = "Salaried";
        $emp_type_rule_actual_value = ['applicant_employment' => $master_employment[$customer_employment_id]];
        $emp_type_rule_relevant_inputs = ['applicant_employment' => $master_employment[$customer_employment_id]];
        $emp_type_rule_system_decision_id = 0;
        $emp_type_rule_manual_decision_id = 0;

        if (!empty($customer_employment_id) && $customer_employment_id == 1) {
            $emp_type_rule_system_decision_id = 1;
            $emp_type_rule_manual_decision_id = 1;
        } else if (!empty($customer_employment_id) && $customer_employment_id == 2) {
            $emp_type_rule_system_decision_id = 3;
            $emp_type_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $emp_type_rule_id, $emp_type_rule_name, $emp_type_rule_cutoff_value, $emp_type_rule_actual_value, $emp_type_rule_relevant_inputs, $emp_type_rule_system_decision_id, $emp_type_rule_manual_decision_id);

        $salary_mode_rule_id = 3;
        $salary_mode_rule_name = "Salary Mode";
        $salary_mode_rule_cutoff_value = "Bank";
        $salary_mode_rule_actual_value = ['applicant_salary_mode' => $customer_salary_mode];
        $salary_mode_rule_relevant_inputs = ['applicant_salary_mode' => $customer_salary_mode];
        $salary_mode_rule_system_decision_id = 0;
        $salary_mode_rule_manual_decision_id = 0;

        if (!empty($customer_salary_mode) && $customer_salary_mode == "BANK") {
            $salary_mode_rule_system_decision_id = 1;
            $salary_mode_rule_manual_decision_id = 1;
        } else if (!empty($customer_salary_mode) && $customer_salary_mode == "CHEQUE") {
            $salary_mode_rule_system_decision_id = 2;
            $salary_mode_rule_manual_decision_id = 2;
        } else if (!empty($customer_salary_mode) && $customer_salary_mode == "CASH") {
            $salary_mode_rule_system_decision_id = 3;
            $salary_mode_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $salary_mode_rule_id, $salary_mode_rule_name, $salary_mode_rule_cutoff_value, $salary_mode_rule_actual_value, $salary_mode_rule_relevant_inputs, $salary_mode_rule_system_decision_id, $salary_mode_rule_manual_decision_id);

        $sourcing_city_rule_id = 4;
        $sourcing_city_rule_name = "Location Criteria";
        $sourcing_city_rule_cutoff_value = "City Sourcing => Yes";
        $sourcing_city_rule_actual_value = ['City Sourcing' => (($current_city_is_sourcing == 1) ? "Yes" : "No")];
        $sourcing_city_rule_relevant_inputs = ['current_city_sourcing' => (($current_city_is_sourcing == 1) ? "Yes" : "No"), "current_city_name" => $current_city_name];
        $sourcing_city_rule_system_decision_id = 0;
        $sourcing_city_rule_manual_decision_id = 0;

        if (!empty($current_city_is_sourcing) && $current_city_is_sourcing == 1) {
            $sourcing_city_rule_system_decision_id = 1;
            $sourcing_city_rule_manual_decision_id = 1;
        } else if (!empty($current_city_is_sourcing)) {
            $sourcing_city_rule_system_decision_id = 3;
            $sourcing_city_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $sourcing_city_rule_id, $sourcing_city_rule_name, $sourcing_city_rule_cutoff_value, $sourcing_city_rule_actual_value, $sourcing_city_rule_relevant_inputs, $sourcing_city_rule_system_decision_id, $sourcing_city_rule_manual_decision_id);

        $salary_with_category_rule_id = 5;
        $salary_with_category_rule_name = "Salary Criteria with City Category";
        $salary_with_category_rule_cutoff_value = ">=25000 & CITY CAT A";
        $salary_with_category_rule_actual_value = "";
        $salary_with_category_rule_relevant_inputs = ['customer_monthly_income' => $customer_monthly_income, "current_city_category" => $current_city_category, "current_city_name" => $current_city_name];
        $salary_with_category_rule_system_decision_id = 0;
        $salary_with_category_rule_manual_decision_id = 0;

        if (!empty($customer_monthly_income) && !empty($current_city_category)) {

            if ($customer_monthly_income >= 25000 && $current_city_category == "A") {
                $salary_with_category_rule_system_decision_id = 1;
                $salary_with_category_rule_manual_decision_id = 1;
                $salary_with_category_rule_actual_value = [0 => ">=25000 & CITY CAT A"];
            } else if ($customer_monthly_income >= 25000 && $current_city_category == "B") {
                $salary_with_category_rule_system_decision_id = 1;
                $salary_with_category_rule_manual_decision_id = 1;
                $salary_with_category_rule_actual_value = [0 => ">=25000 & CITY CAT B"];
            } else if ($customer_monthly_income >= 20000 && $customer_monthly_income < 25000 && $current_city_category == "B") {
                $salary_with_category_rule_system_decision_id = 2;
                $salary_with_category_rule_manual_decision_id = 2;
                $salary_with_category_rule_actual_value = [0 => "20000 >= & < 25000 & CITY CAT B"];
            } else if ($customer_monthly_income < 25000 && $current_city_category == "A") {
                $salary_with_category_rule_system_decision_id = 3;
                $salary_with_category_rule_manual_decision_id = 3;
                $salary_with_category_rule_actual_value = [0 => "< 25000 & CITY CAT A"];
            } else {
                $salary_with_category_rule_actual_value = [0 => "< 25000"];
                $salary_with_category_rule_system_decision_id = 3;
                $salary_with_category_rule_manual_decision_id = 3;
            }
        }

        insertBreRuleResult($lead_id, $salary_with_category_rule_id, $salary_with_category_rule_name, $salary_with_category_rule_cutoff_value, $salary_with_category_rule_actual_value, $salary_with_category_rule_relevant_inputs, $salary_with_category_rule_system_decision_id, $salary_with_category_rule_manual_decision_id);

        $mobile_otp_rule_id = 6;
        $mobile_otp_rule_name = "Customer Mobile OTP";
        $mobile_otp_rule_cutoff_value = "OTP Verify => Yes";
        $mobile_otp_rule_actual_value = ['OTP Verify' => (($mobile_verified_status == "YES") ? "Yes" : "No")];
        $mobile_otp_rule_relevant_inputs = ["customer_mobile" => $customer_mobile, 'mobile_verified_status' => (($mobile_verified_status == "YES") ? "Yes" : "No")];
        $mobile_otp_rule_system_decision_id = 0;
        $mobile_otp_rule_manual_decision_id = 0;

        if (!empty($mobile_verified_status) && $mobile_verified_status == "YES") {
            $mobile_otp_rule_system_decision_id = 1;
            $mobile_otp_rule_manual_decision_id = 1;
        } else if (!empty($mobile_verified_status)) {
            $mobile_otp_rule_system_decision_id = 2;
            $mobile_otp_rule_manual_decision_id = 2;
        }

        insertBreRuleResult($lead_id, $mobile_otp_rule_id, $mobile_otp_rule_name, $mobile_otp_rule_cutoff_value, $mobile_otp_rule_actual_value, $mobile_otp_rule_relevant_inputs, $mobile_otp_rule_system_decision_id, $mobile_otp_rule_manual_decision_id);

        $personal_email_verification_rule_id = 7;
        $personal_email_verification_rule_name = "Personal Email Verification";
        $personal_email_verification_rule_cutoff_value = "Personal Email Verify => Yes";
        $personal_email_verification_rule_actual_value = ['Personal Email Verify' => (($customer_personal_email_verified_status == "YES") ? "Yes" : "No")];
        $personal_email_verification_rule_relevant_inputs = ['customer_personal_email' => $customer_personal_email, "customer_personal_email_verifiy" => (($customer_personal_email_verified_status == "YES") ? "Yes" : "No")];
        $personal_email_verification_rule_system_decision_id = 0;
        $personal_email_verification_rule_manual_decision_id = 0;

        if (!empty($customer_personal_email) && $customer_personal_email_verified_status == "YES") {
            $personal_email_verification_rule_system_decision_id = 1;
            $personal_email_verification_rule_manual_decision_id = 1;
        } else if (!empty($customer_personal_email) && $customer_personal_email_verified_status == "NO") {
            $personal_email_verification_rule_system_decision_id = 3;
            $personal_email_verification_rule_manual_decision_id = 3;
        }

        // insertBreRuleResult($lead_id, $personal_email_verification_rule_id, $personal_email_verification_rule_name, $personal_email_verification_rule_cutoff_value, $personal_email_verification_rule_actual_value, $personal_email_verification_rule_relevant_inputs, $personal_email_verification_rule_system_decision_id, $personal_email_verification_rule_manual_decision_id);

        // $office_email_verification_rule_id = 26;
        // $office_email_verification_rule_name = "Office Email Verification";
        // $office_email_verification_rule_cutoff_value = "Office Email Verify => Yes";
        // $office_email_verification_rule_actual_value = ['Office Email Verify' => (($customer_office_email_verified_status == "YES") ? "Yes" : "No")];
        // $office_email_verification_rule_relevant_inputs = ["customer_office_email" => $customer_office_email, "customer_office_email_verify" => (($customer_office_email_verified_status == "YES") ? "Yes" : "No")];
        // $office_email_verification_rule_system_decision_id = 0;
        // $office_email_verification_rule_manual_decision_id = 0;

        // if (!empty($customer_office_email) && $customer_office_email_verified_status == "YES") {
        //     $office_email_verification_rule_system_decision_id = 1;
        //     $office_email_verification_rule_manual_decision_id = 1;
        // } else if (!empty($customer_office_email) && $customer_office_email_verified_status == "NO") {
        //     $office_email_verification_rule_system_decision_id = 2;
        //     $office_email_verification_rule_manual_decision_id = 2;
        // }

        insertBreRuleResult($lead_id, $office_email_verification_rule_id, $office_email_verification_rule_name, $office_email_verification_rule_cutoff_value, $office_email_verification_rule_actual_value, $office_email_verification_rule_relevant_inputs, $office_email_verification_rule_system_decision_id, $office_email_verification_rule_manual_decision_id);

        $aadhaar_verification_rule_id = 8;
        $aadhaar_verification_rule_name = "Aadhaar EKYC Verification";
        $aadhaar_verification_rule_cutoff_value = "Aadhaar EKYC Verify => Yes";
        $aadhaar_verification_rule_actual_value = ['Aadhaar EKYC Verify' => (($customer_digital_ekyc_flag == 1) ? "Yes" : "No")];
        $aadhaar_verification_rule_relevant_inputs = ["customer_aadhaar" => $customer_aadhar_no, "customer_digital_ekyc" => (($customer_digital_ekyc_flag == 1) ? "Yes" : "No")];
        $aadhaar_verification_rule_system_decision_id = 0;
        $aadhaar_verification_rule_manual_decision_id = 0;

        if (!empty($customer_aadhar_no) && $customer_digital_ekyc_flag == 1) {
            $aadhaar_verification_rule_system_decision_id = 1;
            $aadhaar_verification_rule_manual_decision_id = 1;
        } else if (!empty($customer_aadhar_no) && $customer_digital_ekyc_flag != 1) {
            $aadhaar_verification_rule_system_decision_id = 3;
            $aadhaar_verification_rule_manual_decision_id = 3;
        } else {
            $aadhaar_verification_rule_system_decision_id = 3;
            $aadhaar_verification_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $aadhaar_verification_rule_id, $aadhaar_verification_rule_name, $aadhaar_verification_rule_cutoff_value, $aadhaar_verification_rule_actual_value, $aadhaar_verification_rule_relevant_inputs, $aadhaar_verification_rule_system_decision_id, $aadhaar_verification_rule_manual_decision_id);

        $aadhaar_ocr_rule_id = 27;
        $aadhaar_ocr_rule_name = "Aadhaar OCR Verification";
        $aadhaar_ocr_rule_cutoff_value = "Aadhaar OCR Verify => Yes";
        $aadhaar_ocr_rule_actual_value = "";
        $aadhaar_ocr_rule_relevant_inputs = "";
        $aadhaar_ocr_rule_system_decision_id = 0;
        $aadhaar_ocr_rule_manual_decision_id = 0;

        if (!empty($aadhaar_log_data) && $aadhaar_ocr_verified_status == 1) {
            $aadhaar_ocr_rule_system_decision_id = 1;
            $aadhaar_ocr_rule_manual_decision_id = 1;
            $aadhaar_ocr_rule_actual_value = ['Aadhaar OCR Verify' => "Yes"];
            $aadhaar_ocr_rule_relevant_inputs = ["customer_aadhaar" => $customer_aadhar_no, "aadhaar_ocr_verify" => (($aadhaar_ocr_verified_status == 1) ? "Yes" : "No")];
        } else if (!empty($aadhaar_log_data) && $aadhaar_ocr_verified_status != 1) {
            $aadhaar_ocr_rule_system_decision_id = 3;
            $aadhaar_ocr_rule_manual_decision_id = 3;
            $aadhaar_ocr_rule_actual_value = ['Aadhaar OCR Verify' => "No"];
            $aadhaar_ocr_rule_relevant_inputs = ["customer_aadhaar" => $customer_aadhar_no, "aadhaar_ocr_verify" => (($aadhaar_ocr_verified_status == 1) ? "Yes" : "No")];
        } else if ($user_type != "REPEAT") {
            $aadhaar_ocr_rule_system_decision_id = 3;
            $aadhaar_ocr_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $aadhaar_ocr_rule_id, $aadhaar_ocr_rule_name, $aadhaar_ocr_rule_cutoff_value, $aadhaar_ocr_rule_actual_value, $aadhaar_ocr_rule_relevant_inputs, $aadhaar_ocr_rule_system_decision_id, $aadhaar_ocr_rule_manual_decision_id);

        $pan_verification_rule_id = 9;
        $pan_verification_rule_name = "PAN NSDL Verification";
        $pan_verification_rule_cutoff_value = "PAN Verify => Yes";
        $pan_verification_rule_actual_value = ['PAN Verify' => (($pancard_verified_status == 1) ? "Yes" : "No")];
        $pan_verification_rule_relevant_inputs = ["customer_pancard" => $customer_pancard, "pancard_ocr_verified_status" => (($pancard_verified_status == 1) ? "Yes" : "No")];
        $pan_verification_rule_system_decision_id = 0;
        $pan_verification_rule_manual_decision_id = 0;

        if (!empty($customer_pancard) && $pancard_verified_status == 1) {
            $pan_verification_rule_system_decision_id = 1;
            $pan_verification_rule_manual_decision_id = 1;
        } else if (!empty($customer_pancard) && $pancard_verified_status != 1) {
            $pan_verification_rule_system_decision_id = 3;
            $pan_verification_rule_manual_decision_id = 3;
        } else {
            $pan_verification_rule_system_decision_id = 3;
            $pan_verification_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $pan_verification_rule_id, $pan_verification_rule_name, $pan_verification_rule_cutoff_value, $pan_verification_rule_actual_value, $pan_verification_rule_relevant_inputs, $pan_verification_rule_system_decision_id, $pan_verification_rule_manual_decision_id);

        $pan_ocr_rule_id = 28;
        $pan_ocr_rule_name = "PAN OCR Verification";
        $pan_ocr_rule_cutoff_value = "PAN OCR Verify => Yes";
        $pan_ocr_rule_actual_value = "";
        $pan_ocr_rule_relevant_inputs = "";
        $pan_ocr_rule_system_decision_id = 0;
        $pan_ocr_rule_manual_decision_id = 0;

        if (!empty($pancard_ocr_data) && $pancard_ocr_verified_status == 1) {
            $pan_ocr_rule_system_decision_id = 1;
            $pan_ocr_rule_manual_decision_id = 1;
            $pan_ocr_rule_actual_value = ['PAN OCR Verify' => "Yes"];
            $pan_ocr_rule_relevant_inputs = ["customer_pancard" => $customer_pancard, "pancard_ocr_verify" => (($pancard_ocr_verified_status == 1) ? "Yes" : "No")];
        } else if (!empty($pancard_ocr_data) && $pancard_ocr_verified_status != 1) {
            $pan_ocr_rule_system_decision_id = 3;
            $pan_ocr_rule_manual_decision_id = 3;
            $pan_ocr_rule_actual_value = ['PAN OCR Verify' => "No"];
            $pan_ocr_rule_relevant_inputs = ["customer_pancard" => $customer_pancard, "pancard_ocr_verify" => (($pancard_ocr_verified_status == 1) ? "Yes" : "No")];
        } else if ($user_type != "REPEAT") {
            $pan_ocr_rule_system_decision_id = 3;
            $pan_ocr_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $pan_ocr_rule_id, $pan_ocr_rule_name, $pan_ocr_rule_cutoff_value, $pan_ocr_rule_actual_value, $pan_ocr_rule_relevant_inputs, $pan_ocr_rule_system_decision_id, $pan_ocr_rule_manual_decision_id);

        // $dob_match_rule_id = 10;
        // $dob_match_rule_name = "DOB Verification";
        // $dob_match_rule_cutoff_value = "PAN and AADHAAR DOB Matched => Yes";
        // $dob_match_rule_actual_value = "";
        // $dob_match_rule_relevant_inputs = "";
        // $dob_match_rule_system_decision_id = 0;
        // $dob_match_rule_manual_decision_id = 0;

        // if (!empty($customer_pancard_dob) && !empty($customer_aadhaar_dob) && $customer_dob == $customer_pancard_dob && $customer_dob == $customer_aadhaar_dob && $customer_pancard_dob == $customer_aadhaar_dob) {
        //     $dob_match_rule_system_decision_id = 1;
        //     $dob_match_rule_manual_decision_id = 1;
        //     $dob_match_rule_actual_value = ["PAN and AADHAAR DOB Matched" => "Yes"];
        //     $dob_match_rule_relevant_inputs = ["customer_dob" => date("d-m-Y", strtotime($customer_dob)), "customer_pancard_dob" => date("d-m-Y", strtotime($customer_pancard_dob)), "customer_aadhaar_dob" => date("d-m-Y", strtotime($customer_aadhaar_dob))];
        // }else if ($user_type != "REPEAT") {
        //     $dob_match_rule_system_decision_id = 3;
        //     $dob_match_rule_manual_decision_id = 3;
        // }

        insertBreRuleResult($lead_id, $dob_match_rule_id, $dob_match_rule_name, $dob_match_rule_cutoff_value, $dob_match_rule_actual_value, $dob_match_rule_relevant_inputs, $dob_match_rule_system_decision_id, $dob_match_rule_manual_decision_id);

        $loan_amount_rule_id = 15;
        $loan_amount_rule_name = "Min and Max loan amount";
        $loan_amount_rule_cutoff_value = ">=5,000 & <=1,00,000";
        $loan_amount_rule_actual_value = "";
        $loan_amount_rule_relevant_inputs = ["applied_loan_amount" => $applied_loan_amount, "recommend_loan_amount" => $recommend_loan_amount];
        $loan_amount_rule_system_decision_id = 0;
        $loan_amount_rule_manual_decision_id = 0;

        if (!empty($recommend_loan_amount) && $recommend_loan_amount >= 5000 && $recommend_loan_amount <= 100000) {
            $loan_amount_rule_system_decision_id = 1;
            $loan_amount_rule_manual_decision_id = 1;
            $loan_amount_rule_actual_value = [">=5,000 & <=1,00,000"];
        } else if (!empty($recommend_loan_amount) && $recommend_loan_amount > 100000 && $recommend_loan_amount <= 115000) {
            $loan_amount_rule_system_decision_id = 2;
            $loan_amount_rule_manual_decision_id = 2;
            $loan_amount_rule_actual_value = [">1,00,000 &  <=1,15,000"];
        } else if (!empty($recommend_loan_amount)) {
            $loan_amount_rule_system_decision_id = 3;
            $loan_amount_rule_manual_decision_id = 3;
            $loan_amount_rule_actual_value = ["<5,000 &  >1,15,000"];
        } else {
            $loan_amount_rule_system_decision_id = 3;
            $loan_amount_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $loan_amount_rule_id, $loan_amount_rule_name, $loan_amount_rule_cutoff_value, $loan_amount_rule_actual_value, $loan_amount_rule_relevant_inputs, $loan_amount_rule_system_decision_id, $loan_amount_rule_manual_decision_id);

        $loan_tenure_rule_id = 16;
        $loan_tenure_rule_name = "Min and Max Loan Tenure";
        $loan_tenure_rule_cutoff_value = ">=7 days to <=30 days";
        $loan_tenure_rule_actual_value = "";
        $loan_tenure_rule_relevant_inputs = ["applied_loan_tenure" => $applied_loan_tenure, "recommend_loan_tenure" => $recommend_loan_tenure];
        $loan_tenure_rule_system_decision_id = 0;
        $loan_tenure_rule_manual_decision_id = 0;

        if (!empty($recommend_loan_tenure) && $recommend_loan_tenure >= 7 && $recommend_loan_tenure <= 30) {
            $loan_tenure_rule_system_decision_id = 1;
            $loan_tenure_rule_manual_decision_id = 1;
            $loan_tenure_rule_actual_value = [">=7 days to <=30 days"];
        } else if (!empty($recommend_loan_tenure) && $recommend_loan_tenure > 30 && $recommend_loan_tenure <= 40) {
            $loan_tenure_rule_system_decision_id = 2;
            $loan_tenure_rule_manual_decision_id = 2;
            $loan_tenure_rule_actual_value = [">30 days to <=40 days"];
        } else if (!empty($recommend_loan_tenure) && $recommend_loan_tenure < 7) {
            $loan_tenure_rule_system_decision_id = 2;
            $loan_tenure_rule_manual_decision_id = 2;
            $loan_tenure_rule_actual_value = ["<7 days"];
        } else if (!empty($recommend_loan_tenure) && $recommend_loan_tenure > 40) {
            $loan_tenure_rule_system_decision_id = 3;
            $loan_tenure_rule_manual_decision_id = 3;
            $loan_tenure_rule_actual_value = [">40 days"];
        } else {
            $loan_tenure_rule_system_decision_id = 3;
            $loan_tenure_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $loan_tenure_rule_id, $loan_tenure_rule_name, $loan_tenure_rule_cutoff_value, $loan_tenure_rule_actual_value, $loan_tenure_rule_relevant_inputs, $loan_tenure_rule_system_decision_id, $loan_tenure_rule_manual_decision_id);

        $quote_engine_return = bre_quote_engine($lead_id);

        $quote_engine_return['max_loan_amount'] = $eligible_loan;

        $eligible_loan_amount_rule_id = 29;
        $eligible_loan_amount_rule_name = "Eligible Loan Amount";
        $eligible_loan_amount_rule_cutoff_value = "Eligible Loan Amount => " . $quote_engine_return['max_loan_amount'];
        $eligible_loan_amount_rule_actual_value = "";
        $eligible_loan_amount_rule_relevant_inputs = ["applied_loan_amount" => $applied_loan_amount, "recommend_loan_amount" => $recommend_loan_amount];
        $eligible_loan_amount_rule_system_decision_id = 0;
        $eligible_loan_amount_rule_manual_decision_id = 0;

        if (!empty($recommend_loan_amount) && $recommend_loan_amount <= $quote_engine_return['max_loan_amount']) {
            $eligible_loan_amount_rule_system_decision_id = 1;
            $eligible_loan_amount_rule_manual_decision_id = 1;
            $eligible_loan_amount_rule_actual_value = ["Recommend Loan Amount <= Eligible Loan Amount"];
        } else if (!empty($recommend_loan_amount)) {
            $eligible_loan_amount_rule_system_decision_id = 3;
            $eligible_loan_amount_rule_manual_decision_id = 3;
            $eligible_loan_amount_rule_actual_value = ["Recommend Loan Amount > Eligible Loan Amount"];
        } else {
            $eligible_loan_amount_rule_system_decision_id = 3;
            $eligible_loan_amount_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $eligible_loan_amount_rule_id, $eligible_loan_amount_rule_name, $eligible_loan_amount_rule_cutoff_value, $eligible_loan_amount_rule_actual_value, $eligible_loan_amount_rule_relevant_inputs, $eligible_loan_amount_rule_system_decision_id, $eligible_loan_amount_rule_manual_decision_id);

        $eligible_loan_tenure_rule_id = 30;
        $eligible_loan_tenure_rule_name = "Eligible Loan Tenure";
        $eligible_loan_tenure_rule_cutoff_value = "Eligible Loan Tenure => " . $quote_engine_return['max_loan_tenure'];
        $eligible_loan_tenure_rule_actual_value = "";
        $eligible_loan_tenure_rule_relevant_inputs = ["applied_loan_tenure" => $applied_loan_tenure, "recommend_loan_tenure" => $recommend_loan_tenure];
        $eligible_loan_tenure_rule_system_decision_id = 0;
        $eligible_loan_tenure_rule_manual_decision_id = 0;

        if (!empty($recommend_loan_tenure) && $recommend_loan_tenure <= $quote_engine_return['max_loan_tenure']) {
            $eligible_loan_tenure_rule_system_decision_id = 1;
            $eligible_loan_tenure_rule_manual_decision_id = 1;
            $eligible_loan_tenure_rule_actual_value = ["Recommend Loan Tenure <= Eligible Loan Tenure"];
        } else if (!empty($recommend_loan_tenure)) {
            $eligible_loan_tenure_rule_system_decision_id = 3;
            $eligible_loan_tenure_rule_manual_decision_id = 3;
            $eligible_loan_tenure_rule_actual_value = ["Recommend Loan Tenure > Eligible Loan Tenure"];
        } else {
            $eligible_loan_tenure_rule_system_decision_id = 3;
            $eligible_loan_tenure_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $eligible_loan_tenure_rule_id, $eligible_loan_tenure_rule_name, $eligible_loan_tenure_rule_cutoff_value, $eligible_loan_tenure_rule_actual_value, $eligible_loan_tenure_rule_relevant_inputs, $eligible_loan_tenure_rule_system_decision_id, $eligible_loan_tenure_rule_manual_decision_id);

        list($preYear, $preMonth, $preDay) = explode('-', $customer_employment_since);
        list($currentYear, $currentMonth, $currentDay) = explode('-', date("Y-m-d"));

        $customer_employment_since_month_counter = (12 - $preMonth) + ($currentMonth) + 1 + (12 * ($currentYear - $preYear - 1));

        $employment_since_rule_id = 17;
        $employment_since_rule_name = "Current Employment Experience";
        $employment_since_rule_cutoff_value = "< 12 Months & Net monthly income >30000";
        $employment_since_rule_actual_value = "";
        $employment_since_rule_relevant_inputs = ["customer_employment_since" => date("d-m-Y", strtotime($customer_employment_since)), "customer_employment_since_months" => $customer_employment_since_month_counter, "customer_monthly_income" => $customer_monthly_income];
        $employment_since_rule_system_decision_id = 0;
        $employment_since_rule_manual_decision_id = 0;

        if (!empty($customer_employment_since_month_counter) && $customer_employment_since_month_counter > 12) {
            $employment_since_rule_system_decision_id = 1;
            $employment_since_rule_manual_decision_id = 1;
            $employment_since_rule_actual_value = [">12 Months"];
        } else if (!empty($customer_employment_since_month_counter) && ($customer_employment_since_month_counter >= 6 && $customer_employment_since_month_counter < 12) && $customer_monthly_income > 30000) {
            $employment_since_rule_system_decision_id = 1;
            $employment_since_rule_manual_decision_id = 1;
            $employment_since_rule_actual_value = [">6 Months & Net monthly income >30000"];
        } else if (!empty($customer_employment_since_month_counter) && $customer_employment_since_month_counter > 6) {
            $employment_since_rule_system_decision_id = 2;
            $employment_since_rule_manual_decision_id = 2;
            $employment_since_rule_actual_value = [">6 Months"];
        } else if (!empty($customer_employment_since_month_counter)) {
            $employment_since_rule_system_decision_id = 2;
            $employment_since_rule_manual_decision_id = 2;
            $employment_since_rule_actual_value = ["<6 Months"];
        }

        insertBreRuleResult($lead_id, $employment_since_rule_id, $employment_since_rule_name, $employment_since_rule_cutoff_value, $employment_since_rule_actual_value, $employment_since_rule_relevant_inputs, $employment_since_rule_system_decision_id, $employment_since_rule_manual_decision_id);

        list($preYear, $preMonth, $preDay) = explode('-', $current_residence_since);

        $customer_residence_since_month_counter = (12 - $preMonth) + ($currentMonth) + 1 + (12 * ($currentYear - $preYear - 1));

        $residence_since_rule_id = 18;
        $residence_since_rule_name = "Current Residence Since";
        $residence_since_rule_cutoff_value = ">= 12 Months";
        $residence_since_rule_actual_value = "";
        $residence_since_rule_relevant_inputs = ["customer_residence_since" => date("d-m-Y", strtotime($current_residence_since)), "customer_residence_since_months" => $customer_residence_since_month_counter];
        $residence_since_rule_system_decision_id = 0;
        $residence_since_rule_manual_decision_id = 0;

        if (!empty($customer_residence_since_month_counter) && $customer_residence_since_month_counter >= 12) {
            $residence_since_rule_system_decision_id = 1;
            $residence_since_rule_manual_decision_id = 1;
            $residence_since_rule_actual_value = [">=12 Months"];
        } else if (!empty($customer_residence_since_month_counter) && ($customer_residence_since_month_counter >= 6 && $customer_residence_since_month_counter < 12)) {
            $residence_since_rule_system_decision_id = 1;
            $residence_since_rule_manual_decision_id = 1;
            $residence_since_rule_actual_value = [">=6 Months & <12 Months"];
        } else if (!empty($customer_employment_since_month_counter)) {
            $residence_since_rule_system_decision_id = 2;
            $residence_since_rule_manual_decision_id = 2;
            $residence_since_rule_actual_value = ["<6 Months"];
        }

        insertBreRuleResult($lead_id, $residence_since_rule_id, $residence_since_rule_name, $residence_since_rule_cutoff_value, $residence_since_rule_actual_value, $residence_since_rule_relevant_inputs, $residence_since_rule_system_decision_id, $residence_since_rule_manual_decision_id);

        $bank_doc_verification_rule_id = 11;
        $bank_doc_verification_rule_name = "Banking Document";
        $bank_doc_verification_rule_cutoff_value = "Bank Statement uploaded & Banking Analysis => Yes";
        $bank_doc_verification_rule_actual_value = ['Bank Statement uploaded & Banking Analysis' => (!empty($bank_analysis_data) ? "Yes" : "No")];
        $bank_doc_verification_rule_relevant_inputs = ["bank_statement_analysis" => (!empty($bank_analysis_data) ? "Yes" : "No")];
        $bank_doc_verification_rule_system_decision_id = 0;
        $bank_doc_verification_rule_manual_decision_id = 0;

        if (!empty($bank_analysis_data)) {
            $bank_doc_verification_rule_system_decision_id = 1;
            $bank_doc_verification_rule_manual_decision_id = 1;
        } else if (empty($bank_analysis_data)) {
            $bank_doc_verification_rule_system_decision_id = 3;
            $bank_doc_verification_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bank_doc_verification_rule_id, $bank_doc_verification_rule_name, $bank_doc_verification_rule_cutoff_value, $bank_doc_verification_rule_actual_value, $bank_doc_verification_rule_relevant_inputs, $bank_doc_verification_rule_system_decision_id, $bank_doc_verification_rule_manual_decision_id);

        $bank_acc_verification_rule_id = 12;
        $bank_acc_verification_rule_name = "Bank Account Verification";
        $bank_acc_verification_rule_cutoff_value = "Bank Account Verification => Yes & Name Match => Yes";
        $bank_acc_verification_rule_actual_value = "";
        $bank_acc_verification_rule_relevant_inputs = ["bank_account_verification" => $bank_account_status, "bank_account_name_match_status" => $bank_account_name_match_status, "bank_account_name_match_score" => $bank_account_name_match_score, "bank_account_customer_name" => $bank_account_customer_name];
        $bank_acc_verification_rule_system_decision_id = 0;
        $bank_acc_verification_rule_manual_decision_id = 0;

        if (!empty($bank_account_data) && $bank_account_status == "YES" && $bank_account_name_match_status == "YES") {
            $bank_acc_verification_rule_system_decision_id = 1;
            $bank_acc_verification_rule_manual_decision_id = 1;
            $bank_acc_verification_rule_actual_value = ['Bank Account Verification' => "Yes", "Name Match" => "Yes"];
        } else if (!empty($bank_account_data) && $bank_account_status == "YES" && $bank_account_name_match_status != "YES") {
            $bank_acc_verification_rule_system_decision_id = 2;
            $bank_acc_verification_rule_manual_decision_id = 2;
            $bank_acc_verification_rule_actual_value = ['Bank Account Verification' => "Yes", "Name Match" => "No"];
        } else if (!empty($bank_account_data) && !in_array($lead_data_source_id, array(32))) {
            $bank_acc_verification_rule_system_decision_id = 3;
            $bank_acc_verification_rule_manual_decision_id = 3;
            $bank_acc_verification_rule_actual_value = ['Bank Account Verification' => "No"];
        } else if (!in_array($lead_data_source_id, array(32))) {
            $bank_acc_verification_rule_system_decision_id = 3;
            $bank_acc_verification_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bank_acc_verification_rule_id, $bank_acc_verification_rule_name, $bank_acc_verification_rule_cutoff_value, $bank_acc_verification_rule_actual_value, $bank_acc_verification_rule_relevant_inputs, $bank_acc_verification_rule_system_decision_id, $bank_acc_verification_rule_manual_decision_id);

        $bank_statement_account_rule_id = 13;
        $bank_statement_account_rule_name = "Bank Statement & Bank Account Match (API)";
        $bank_statement_account_rule_cutoff_value = "Bank Statement & Bank Account Match => Yes";
        $bank_statement_account_rule_actual_value = "";
        $bank_statement_account_rule_relevant_inputs = ["bank_analysis_account_no" => $bank_analysis_account_no, "bank_analysis_ifsc_code" => $bank_analysis_account_ifsc_code, "bank_account_number" => $bank_account_number, "bank_account_ifsc_code" => $bank_account_ifsc_code];
        //$bank_statement_account_rule_relevant_inputs = ["bank_analysis_account_no" => $bank_analysis_account_no, "bank_analysis_ifsc_code" => $bank_analysis_account_ifsc_code];
        $bank_statement_account_rule_system_decision_id = 0;
        $bank_statement_account_rule_manual_decision_id = 0;

        if (!empty($bank_analysis_account_no) && !empty($bank_account_number) && substr($bank_analysis_account_no, -4, 4) == substr($bank_account_number, -4, 4)) {
            $bank_statement_account_rule_system_decision_id = 1;
            $bank_statement_account_rule_manual_decision_id = 1;
            $bank_statement_account_rule_actual_value = ['Bank Statement & Bank Account Match' => "Yes"];
        } else if (!empty($bank_account_data)) {
            $bank_statement_account_rule_system_decision_id = 2;
            $bank_statement_account_rule_manual_decision_id = 2;
            $bank_statement_account_rule_actual_value = ['Bank Statement & Bank Account Match' => "No"];
        } else if (!in_array($lead_data_source_id, array(32))) {
            $bank_statement_account_rule_system_decision_id = 3;
            $bank_statement_account_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bank_statement_account_rule_id, $bank_statement_account_rule_name, $bank_statement_account_rule_cutoff_value, $bank_statement_account_rule_actual_value, $bank_statement_account_rule_relevant_inputs, $bank_statement_account_rule_system_decision_id, $bank_statement_account_rule_manual_decision_id);

        $bank_statement_balance_rule_id = 19;
        $bank_statement_balance_rule_name = "Bank Statement Average Monthly Balance";
        $bank_statement_balance_rule_cutoff_value = ">=10,000";
        $bank_statement_balance_rule_actual_value = "";
        $bank_statement_balance_rule_relevant_inputs = ["bank_analysis_average_balance" => $bank_analysis_average_balance];
        $bank_statement_balance_rule_system_decision_id = 0;
        $bank_statement_balance_rule_manual_decision_id = 0;

        if (!empty($bank_analysis_data) && $bank_analysis_average_balance >= 5000) {
            $bank_statement_balance_rule_system_decision_id = 1;
            $bank_statement_balance_rule_manual_decision_id = 1;
            $bank_statement_balance_rule_actual_value = [">=5,000"];
        } else if (!empty($bank_analysis_data) && $bank_analysis_average_balance >= 2500 && $bank_analysis_average_balance < 5000) {
            $bank_statement_balance_rule_system_decision_id = 2;
            $bank_statement_balance_rule_manual_decision_id = 2;
            $bank_statement_balance_rule_actual_value = [">2,500 & <5,000"];
        } else if (!empty($bank_analysis_data) && $bank_analysis_average_balance < 2500) {
            $bank_statement_balance_rule_system_decision_id = 2;
            $bank_statement_balance_rule_manual_decision_id = 2;
            $bank_statement_balance_rule_actual_value = ["<2,500"];
        } else {
            $bank_statement_balance_rule_system_decision_id = 3;
            $bank_statement_balance_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bank_statement_balance_rule_id, $bank_statement_balance_rule_name, $bank_statement_balance_rule_cutoff_value, $bank_statement_balance_rule_actual_value, $bank_statement_balance_rule_relevant_inputs, $bank_statement_balance_rule_system_decision_id, $bank_statement_balance_rule_manual_decision_id);

        $bank_statement_fraudscore_rule_id = 31;
        $bank_statement_fraudscore_rule_name = "Bank Statement Fraud Score";
        $bank_statement_fraudscore_rule_cutoff_value = "Fraud Score <= 0";
        $bank_statement_fraudscore_rule_actual_value = "";
        $bank_statement_fraudscore_rule_relevant_inputs = ["bank_analysis_fraud_score" => $bank_analysis_fraudScore];
        $bank_statement_fraudscore_rule_system_decision_id = 0;
        $bank_statement_fraudscore_rule_manual_decision_id = 0;

        if (!empty($bank_analysis_data) && $bank_analysis_fraudScore <= 0) {
            $bank_statement_fraudscore_rule_system_decision_id = 1;
            $bank_statement_fraudscore_rule_manual_decision_id = 1;
            $bank_statement_fraudscore_rule_actual_value = ["Fraud Score <= 0"];
        } else if (!empty($bank_analysis_data) && $bank_analysis_fraudScore > 0) {
            $bank_statement_fraudscore_rule_system_decision_id = 2;
            $bank_statement_fraudscore_rule_manual_decision_id = 2;
            $bank_statement_fraudscore_rule_actual_value = ["Fraud Score > 0"];
        } else {
            $bank_statement_fraudscore_rule_system_decision_id = 3;
            $bank_statement_fraudscore_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bank_statement_fraudscore_rule_id, $bank_statement_fraudscore_rule_name, $bank_statement_fraudscore_rule_cutoff_value, $bank_statement_fraudscore_rule_actual_value, $bank_statement_fraudscore_rule_relevant_inputs, $bank_statement_fraudscore_rule_system_decision_id, $bank_statement_fraudscore_rule_manual_decision_id);

        $black_listed_rule_id = 25;
        $black_listed_rule_name = "Blacklisted Customer";
        $black_listed_rule_cutoff_value = "Customer Details Matched => No";
        $black_listed_rule_actual_value = ["Customer Details Matched" => !empty($black_listed_data) ? "Yes" : "No"];
        $black_listed_rule_relevant_inputs = ["black_list_response" => $black_listed_data];
        $black_listed_rule_system_decision_id = 0;
        $black_listed_rule_manual_decision_id = 0;

        if (!empty($black_listed_data)) {
            $black_listed_rule_system_decision_id = 2;
            $black_listed_rule_manual_decision_id = 2;
        } else if (empty($black_listed_data)) {
            $black_listed_rule_system_decision_id = 1;
            $black_listed_rule_manual_decision_id = 1;
        }

        insertBreRuleResult($lead_id, $black_listed_rule_id, $black_listed_rule_name, $black_listed_rule_cutoff_value, $black_listed_rule_actual_value, $black_listed_rule_relevant_inputs, $black_listed_rule_system_decision_id, $black_listed_rule_manual_decision_id);

        $active_loan_rule_id = 24;
        $active_loan_rule_name = "Active Loan";
        $active_loan_rule_cutoff_value = "Active Loan=>No";
        $active_loan_rule_actual_value = ["Active Loan" => !empty($active_loan_data) ? "Yes" : "No"];
        $active_loan_rule_relevant_inputs = ["customer_pancard" => $customer_pancard];
        $active_loan_rule_system_decision_id = 0;
        $active_loan_rule_manual_decision_id = 0;

        if (!empty($active_loan_data)) {
            $active_loan_rule_system_decision_id = 3;
            $active_loan_rule_manual_decision_id = 3;
        } else if (empty($active_loan_data)) {
            $active_loan_rule_system_decision_id = 1;
            $active_loan_rule_manual_decision_id = 1;
        }

        insertBreRuleResult($lead_id, $active_loan_rule_id, $active_loan_rule_name, $active_loan_rule_cutoff_value, $active_loan_rule_actual_value, $active_loan_rule_relevant_inputs, $active_loan_rule_system_decision_id, $active_loan_rule_manual_decision_id);

        $customer_pincode_rule_id = 22;
        $customer_pincode_rule_name = "Pincode Matching";
        $customer_pincode_rule_cutoff_value = "Residence Pincode == Aadhar Pincode";
        $customer_pincode_rule_actual_value = "";
        $customer_pincode_rule_relevant_inputs = ["customer_aadhaar_pincode" => $customer_aadhaar_pincode, "customer_residence_pincode" => $customer_residence_pincode];
        $customer_pincode_rule_system_decision_id = 0;
        $customer_pincode_rule_manual_decision_id = 0;

        if (!empty($customer_aadhaar_pincode) && $customer_aadhaar_pincode == $customer_residence_pincode) {
            $customer_pincode_rule_system_decision_id = 1;
            $customer_pincode_rule_manual_decision_id = 1;
            $customer_pincode_rule_actual_value = ["Residence Pincode == Aadhar Pincode"];
        } else if (empty($customer_aadhaar_pincode) || empty($customer_residence_pincode) || $customer_aadhaar_pincode != $customer_residence_pincode) {
            $customer_pincode_rule_system_decision_id = 2;
            $customer_pincode_rule_manual_decision_id = 2;
            $customer_pincode_rule_actual_value = ["Residence Pincode != Aadhar Pincode"];
        }

        insertBreRuleResult($lead_id, $customer_pincode_rule_id, $customer_pincode_rule_name, $customer_pincode_rule_cutoff_value, $customer_pincode_rule_actual_value, $customer_pincode_rule_relevant_inputs, $customer_pincode_rule_system_decision_id, $customer_pincode_rule_manual_decision_id);

        $customer_references_rule_id = 21;
        $customer_references_rule_name = "Customer Reference Available";
        $customer_references_rule_cutoff_value = "Reference Available => Yes";
        $customer_references_rule_actual_value = "";
        $customer_references_rule_relevant_inputs = ["customer_reference_data" => count($customer_reference_data)];
        $customer_references_rule_system_decision_id = 0;
        $customer_references_rule_manual_decision_id = 0;

        if (!empty($customer_reference_data) && count($customer_reference_data) >= 2) {
            $customer_references_rule_system_decision_id = 1;
            $customer_references_rule_manual_decision_id = 1;
            $customer_references_rule_actual_value = ["Reference Available => Yes"];
        } else if (empty($customer_reference_data) || count($customer_reference_data) < 2) {
            $customer_references_rule_system_decision_id = 3;
            $customer_references_rule_manual_decision_id = 3;
            $customer_references_rule_actual_value = ["Reference Available => No"];
        }

        insertBreRuleResult($lead_id, $customer_references_rule_id, $customer_references_rule_name, $customer_references_rule_cutoff_value, $customer_references_rule_actual_value, $customer_references_rule_relevant_inputs, $customer_references_rule_system_decision_id, $customer_references_rule_manual_decision_id);

        $bureau_score_rule_id = 32;
        $bureau_score_rule_name = "SCORE";
        $bureau_score_rule_cutoff_value = "Score >= 500";
        $bureau_score_rule_actual_value = "";
        $bureau_score_rule_relevant_inputs = ["bureau_score" => $bureau_score];
        $bureau_score_rule_system_decision_id = 0;
        $bureau_score_rule_manual_decision_id = 0;

        if (!empty($bureau_data) && $bureau_score >= 500) {
            $bureau_score_rule_system_decision_id = 1;
            $bureau_score_rule_manual_decision_id = 1;
            $bureau_score_rule_actual_value = ["Score >=500"];
        } else if (!empty($bureau_data) && $bureau_score < 500) {
            $bureau_score_rule_system_decision_id = 2;
            $bureau_score_rule_manual_decision_id = 2;
            $bureau_score_rule_actual_value = ["Score < 500"];
        } else {
            $bureau_score_rule_system_decision_id = 3;
            $bureau_score_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bureau_score_rule_id, $bureau_score_rule_name, $bureau_score_rule_cutoff_value, $bureau_score_rule_actual_value, $bureau_score_rule_relevant_inputs, $bureau_score_rule_system_decision_id, $bureau_score_rule_manual_decision_id);

        $bureau_account_overdue_rule_id = 33;
        $bureau_account_overdue_rule_name = "Overdue Accounts";
        $bureau_account_overdue_rule_cutoff_value = "Overdue Accounts == 0";
        $bureau_account_overdue_rule_actual_value = "";
        $bureau_account_overdue_rule_relevant_inputs = ["over_due_accounts" => $over_due_accounts];
        $bureau_account_overdue_rule_system_decision_id = 0;
        $bureau_account_overdue_rule_manual_decision_id = 0;

        if (!empty($bureau_data) && $over_due_accounts == 0) {
            $bureau_account_overdue_rule_system_decision_id = 1;
            $bureau_account_overdue_rule_manual_decision_id = 1;
            $bureau_account_overdue_rule_actual_value = ["Overdue Accounts == 0"];
        } else if (!empty($bureau_data) && $over_due_accounts > 0) {
            $bureau_account_overdue_rule_system_decision_id = 2;
            $bureau_account_overdue_rule_manual_decision_id = 2;
            $bureau_account_overdue_rule_actual_value = ["Overdue Accounts > 0"];
        } else {
            $bureau_account_overdue_rule_system_decision_id = 3;
            $bureau_account_overdue_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bureau_account_overdue_rule_id, $bureau_account_overdue_rule_name, $bureau_account_overdue_rule_cutoff_value, $bureau_account_overdue_rule_actual_value, $bureau_account_overdue_rule_relevant_inputs, $bureau_account_overdue_rule_system_decision_id, $bureau_account_overdue_rule_manual_decision_id);

        $bureau_id_variation_rule_id = 34;
        $bureau_id_variation_rule_name = "ID Variation (PAN)";
        $bureau_id_variation_rule_cutoff_value = "Variation <= 1";
        $bureau_id_variation_rule_actual_value = "";
        $bureau_id_variation_rule_relevant_inputs = ["pan_variation_count" => $pan_variation_count, "variation_pan_cards" => $variation_pan_cards];
        $bureau_id_variation_rule_system_decision_id = 0;
        $bureau_id_variation_rule_manual_decision_id = 0;

        if (!empty($bureau_data) && $pan_variation_count <= 1) {
            $bureau_id_variation_rule_system_decision_id = 1;
            $bureau_id_variation_rule_manual_decision_id = 1;
            $bureau_id_variation_rule_actual_value = ["Variation <= 1"];
        } else if (!empty($bureau_data) && $pan_variation_count > 1) {
            $bureau_id_variation_rule_system_decision_id = 2;
            $bureau_id_variation_rule_manual_decision_id = 2;
            $bureau_id_variation_rule_actual_value = ["Variation > 1"];
        } else {
            $bureau_id_variation_rule_system_decision_id = 3;
            $bureau_id_variation_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bureau_id_variation_rule_id, $bureau_id_variation_rule_name, $bureau_id_variation_rule_cutoff_value, $bureau_id_variation_rule_actual_value, $bureau_id_variation_rule_relevant_inputs, $bureau_id_variation_rule_system_decision_id, $bureau_id_variation_rule_manual_decision_id);

        $bureau_inquiry_rule_id = 36;
        $bureau_inquiry_rule_name = "Inquiries in last 30 days";
        $bureau_inquiry_rule_cutoff_value = "Inquiries <= 3";
        $bureau_inquiry_rule_actual_value = "";
        $bureau_inquiry_rule_relevant_inputs = ["inquiries_last_1_months" => $inquiries_last_6_months];
        $bureau_inquiry_rule_system_decision_id = 0;
        $bureau_inquiry_rule_manual_decision_id = 0;

        if (!empty($bureau_data) && $inquiries_last_6_months <= 3) {
            $bureau_inquiry_rule_system_decision_id = 1;
            $bureau_inquiry_rule_manual_decision_id = 1;
            $bureau_inquiry_rule_actual_value = ["Inquiries <= 3"];
        } else if (!empty($bureau_data) && $inquiries_last_6_months > 3) {
            $bureau_inquiry_rule_system_decision_id = 2;
            $bureau_inquiry_rule_manual_decision_id = 2;
            $bureau_inquiry_rule_actual_value = ["Inquiries > 3"];
        } else {
            $bureau_inquiry_rule_system_decision_id = 3;
            $bureau_inquiry_rule_manual_decision_id = 3;
        }

        insertBreRuleResult($lead_id, $bureau_inquiry_rule_id, $bureau_inquiry_rule_name, $bureau_inquiry_rule_cutoff_value, $bureau_inquiry_rule_actual_value, $bureau_inquiry_rule_relevant_inputs, $bureau_inquiry_rule_system_decision_id, $bureau_inquiry_rule_manual_decision_id);

        // $bureau_inquiry_rule_id = 39;
        // $bureau_inquiry_rule_name = "Account Aggregator";
        // $bureau_inquiry_rule_cutoff_value = "Account Details Matched";
        // $bureau_inquiry_rule_actual_value = "";
        // $bureau_inquiry_rule_relevant_inputs = ["name" => $aa_name, "account" => $account, "ifsc_code" => $aa_ifscCode, "bank_name" => $aa_fipName, "current_balance" => $aa_current_balance];
        // $bureau_inquiry_rule_system_decision_id = 0;
        // $bureau_inquiry_rule_manual_decision_id = 0;

        // if (!empty($bankAggregatorDetails) && !empty($aa_ifscCode) && !empty($aa_fipName) && !empty($aa_current_balance) && $aa_ifscCode == $ifsc_code && $aa_name == $beneficiary_name) {
        //     $bureau_inquiry_rule_system_decision_id = 1;
        //     $bureau_inquiry_rule_manual_decision_id = 1;
        //     $bureau_inquiry_rule_actual_value = ["Account details matched"];
        // } else if (!empty($bankAggregatorDetails) && $aa_ifscCode != $ifsc_code) {
        //     $bureau_inquiry_rule_system_decision_id = 2;
        //     $bureau_inquiry_rule_manual_decision_id = 2;
        //     $bureau_inquiry_rule_actual_value = ["IFSC Code does not match"];
        // } else if (!empty($bankAggregatorDetails) && $aa_fipName != $beneficiary_name) {
        //     $bureau_inquiry_rule_system_decision_id = 2;
        //     $bureau_inquiry_rule_manual_decision_id = 2;
        //     $bureau_inquiry_rule_actual_value = ["Beneficiary Name does not match"];
        // } else {
        //     $bureau_inquiry_rule_system_decision_id = 0;
        //     $bureau_inquiry_rule_manual_decision_id = 0;
        // }

        // insertBreRuleResult($lead_id, $bureau_inquiry_rule_id, $bureau_inquiry_rule_name, $bureau_inquiry_rule_cutoff_value, $bureau_inquiry_rule_actual_value, $bureau_inquiry_rule_relevant_inputs, $bureau_inquiry_rule_system_decision_id, $bureau_inquiry_rule_manual_decision_id);

        //RULE ENGINE FINAL OUTPUT


        $getBreRuleResult = $breRuleModelObj->getBreRuleResult($lead_id);
        //print_r($getBreRuleResult);
        if ($getBreRuleResult['status'] == 1) {
            $bre_rule_result_data = $getBreRuleResult['bre_rule_result'];
        } else {
            throw Exception("BRE does not run due to some technical error.");
        }

        $rule_counter = 0;
        $approve_rule_counter = 0;
        $refer_rule_counter = 0;
        $reject_rule_counter = 0;
        $not_available_rule_counter = 0;
        foreach ($bre_rule_result_data as $bre_rule_data) {

            if (!empty($bre_rule_data['lbrr_rule_system_decision_id'])) {
                $rule_counter++;
            }

            if ($bre_rule_data['lbrr_rule_system_decision_id'] == 1) {
                $approve_rule_counter++;
            } else if ($bre_rule_data['lbrr_rule_system_decision_id'] == 2) {
                $refer_rule_counter++;
            } else if ($bre_rule_data['lbrr_rule_system_decision_id'] == 3) {
                $reject_rule_counter++;
            } else {
                $not_available_rule_counter++;
            }
        }

        //print_r($rule_counter.'/'.$approve_rule_counter);

        if ($rule_counter > 0 && $rule_counter == $approve_rule_counter) {
            $bre_decision_status_id = 1;
        } else if ($rule_counter > 0 && $rule_counter == ($approve_rule_counter + $refer_rule_counter)) {
            $bre_decision_status_id = 2;
        } else if ($rule_counter > 0 && $reject_rule_counter > 0) {
            $bre_decision_status_id = 3;
        } else {
            $bre_decision_status_id = 5;
        }

        $breRuleModelObj->updateTable("lead_customer", ["customer_bre_run_flag" => 1, "customer_bre_run_datetime" => date("Y-m-d H:i:s")], " customer_lead_id=$lead_id");
    } catch (Exception $e) {
        $bre_decision_status_id = 4;
        $errorMessage = $e->getMessage();
    }

    $return_array = array(
        "bre_decision_status_id" => $bre_decision_status_id,
        "bre_decision_status" => $master_bre_decision_status[$bre_decision_status_id],
        "error" => $errorMessage
    );

    common_log_writer(6, "return lead_id = $lead_id | response====" . json_encode($return_array));
    common_log_writer(6, "bre_quote_engine end");
    return $return_array;
}

function insertBreRuleResult($lead_id, $rule_id, $rule_name, $rule_cutoff_value, $rule_actual_value, $rule_relevant_inputs, $rule_system_decision_id, $rule_manual_decision_id) {

    $breRuleModelObj = new BreRuleModel();

    $rule_result_insert_array = array();
    $rule_result_insert_array['lbrr_lead_id'] = $lead_id;
    $rule_result_insert_array['lbrr_rule_id'] = $rule_id;
    $rule_result_insert_array['lbrr_rule_name'] = $rule_name;
    $rule_result_insert_array['lbrr_rule_cutoff_value'] = !empty($rule_cutoff_value) ? $rule_cutoff_value : "";
    $rule_result_insert_array['lbrr_rule_actual_value'] = !empty($rule_actual_value) ? json_encode($rule_actual_value) : "";
    $rule_result_insert_array['lbrr_rule_relevant_inputs'] = !empty($rule_relevant_inputs) ? json_encode($rule_relevant_inputs) : "";
    $rule_result_insert_array['lbrr_rule_system_decision_id'] = $rule_system_decision_id;
    $rule_result_insert_array['lbrr_rule_manual_decision_id'] = $rule_manual_decision_id;
    $rule_result_insert_array['lbrr_created_on'] = date("Y-m-d H:i:s");

    $breRuleModelObj->insertTable('lead_bre_rule_result', $rule_result_insert_array);
}
