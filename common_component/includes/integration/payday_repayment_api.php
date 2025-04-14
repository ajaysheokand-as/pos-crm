<?php

function payday_repayment_api($method_name = "", $lead_id = 0, $repayment_amount = 0, $request_array = array()) {

    common_log_writer(6, "Repayment api started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GENERATE_EAZYPAY_ENCRYPTED_URL" => 1
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = payday_generate_repay_encrypted_url($method_id, $lead_id, $repayment_amount, $request_array);
    } else {
        $responseArray["errors"] = "Invalid opertation called.";
    }

    common_log_writer(6, "Repayment Api end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function payday_generate_repay_encrypted_url($method_id, $lead_id = 0, $repayment_amount, $request_array = array()) {

    common_log_writer(6, "Repayment encrypt url started | $lead_id");

    require_once (COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $leadModelObj = new LeadModel();
    $envSet = COMP_ENVIRONMENT;
    $refrence_no = 0;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $apiResponseData_array = array();
    $errorMessage = "";
    $curlError = "";

    $type = "REPAY_API";
    $sub_type = "";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";

    $lead_status_id = 0;

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        if (!in_array($lead_status_id, [14, 19])) {
            throw new Exception("Failed to generate eazypay link. Only Applicable for -  Disbursed and Part Payment cases.");
        }

        $RepaymentDetails = $leadModelObj->getLoanRepaymentDetails($lead_id);

        if ($RepaymentDetails['status'] != 1) {
            throw new Exception("Repayment details not found");
        }

        $repay = $RepaymentDetails['repayment_data'];

        $actual_repay_amount = $repay['total_due_amount'];

        $repayment_amount = intval($repayment_amount);

        if (empty($repayment_amount) || ($repayment_amount <= 1 && $repayment_amount > $actual_repay_amount)) {
            throw new Exception("Please enter the valid repayment amount. Total Due Amount is : " . $actual_repay_amount);
        }

        $name = $app_data['first_name'] . " " . $app_data['middle_name'] . " " . $app_data['sur_name'];
        $email = $app_data['email'];
        $mobile = $app_data['mobile'];
        $pancard = $app_data['pancard'];

        $loan_no = $repay['loan_no'];
        $disbursal_date = date('d/M/Y', strtotime($repay['disbursal_date']));
        $repayment_date = date('d/M/Y', strtotime($repay['repayment_date']));
        $loan_amount = $repay['loan_recommended'];
        $pgamount = $actual_repay_amount;
        $amount = $repayment_amount;
        $submergent_id = $lead_id;

        $encKey = $apiConfig['ApiKey'];
        $paymode = $apiConfig['Paymode'];
        $refrence_no = $apiConfig['ReferenceNo'];
        $return_url = $apiConfig['ReturnURL']; // . "?userRefId=" . $user_id;

        $RPMiddleWareUrl = $apiConfig['RPMiddleWareUrl'];

        $pgamount = 0;
        $loan_amount = 0;
        $transaction_amount = $pgamount + $amount + $loan_amount;
        $plaintext = array();
        $plaintext[] = $refrence_no . '|' . $lead_id . '|' . $pgamount . '|' . $mobile . '|' . $loan_no . '|' . $name . '|' . $email . '|' . $amount . '|' . $disbursal_date . '|' . $repayment_date . '|' . $loan_amount;
        $plaintext[] = $pancard;
        $plaintext[] = $return_url;
        $plaintext[] = $refrence_no;
        $plaintext[] = $submergent_id;
//            $plaintext[] = $amount;
        $plaintext[] = $transaction_amount;
        $plaintext[] = $paymode;

        $plainText = implode("|||", $plaintext);

        $middleware_response = MiddlewareApiReqEncrypt($RPMiddleWareUrl, "LWCOMMON", "EASYPAYICICENCDEC", $plainText, $encKey, $lead_id);

        if (!empty($middleware_response['status']) && ($middleware_response['status'] == 1)) {
            $decoded_output_data = $middleware_response['output_data'];
            $output_data_array = explode('|||', $decoded_output_data);

            $apiResponseData_array = $output_data_array;

            $encrypted_url = $apiConfig['ApiUrl'];

            $encrypted_url .= '&mandatory fields=' . $output_data_array[0];
            $encrypted_url .= '&optional fields=' . $output_data_array[1];
            $encrypted_url .= '&returnurl=' . $output_data_array[2];
            $encrypted_url .= '&Reference No=' . $output_data_array[3];
            $encrypted_url .= '&submerchantid=' . $output_data_array[4];
            $encrypted_url .= '&transaction amount=' . $output_data_array[5];
            $encrypted_url .= '&paymode=' . $output_data_array[6];

            $apiStatusId = 1;
            $apiResponseData = str_replace(" ", "%20", $encrypted_url);
        } else {
            throw new Exception($middleware_response['errors']);
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    $insert_repay_log_data = array();

    $insert_repay_log_data['repayment_product_id'] = 1;
    $insert_repay_log_data['repayment_provider_id'] = 1;
    $insert_repay_log_data['repayment_method_id'] = 1;
    $insert_repay_log_data['repayment_lead_id'] = $lead_id;
    $insert_repay_log_data['repayment_trans_no'] = $refrence_no;
    $insert_repay_log_data['repayment_api_status_id'] = 1;
    $insert_repay_log_data['repayment_request'] = $apiResponseData;
    $insert_repay_log_data['repayment_source_id'] = 2; // via link generated by executive
    $insert_repay_log_data['repayment_user_id'] = $user_id;
    $insert_repay_log_data['repayment_errors'] = !empty($errorMessage) ? $errorMessage : null;
    $insert_repay_log_data['repayment_request_datetime'] = date('Y-m-d H:i:s');

    $leadModelObj->insertTable('api_repayment_logs', $insert_repay_log_data);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? $errorMessage : "";

    return $response_array;
}

?>
