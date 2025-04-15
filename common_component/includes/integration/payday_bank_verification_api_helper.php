<?php

function bank_account_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    $responseArray = array("status" => 0, "error_msg" => "");

    // $opertion_array = array(
    //     "BANK_ACCOUNT_VERIFICATION" => 1,
    //     "BANK_DIGITAP_ACCOUNT_VERIFICATION" => 2,
    // );

    // $method_id = $opertion_array[$method_name];

    // $method_id = (date('d') % 2) > 0 ? 2 : 1;
    $method_id = 1;

    // if ($method_id == 1) {
    //     $responseArray = signzy_bank_account_verification_api($lead_id, $request_array);
    // } else if ($method_id == 2) {
    //     $responseArray = digitap_bank_account_verification_api($lead_id, $request_array);
    // } else {
    //     $responseArray["error_msg"] = "invalid opertation called";
    // }

    if ($method_id == 1) {
        $responseArray = signzy_bank_account_verification_api($lead_id, $request_array);
    } else {
        $responseArray["error_msg"] = "invalid opertation called";
    }

    return $responseArray;
}

function signzy_bank_account_verification_api($lead_id, $request_array = array()) {

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $apiKey = "";

    $type = "SIGNZY_API";
    $sub_type = "BANK_ACCOUNT_VERIFICATION";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = [];

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL; //for testing

    $cust_banking_id = !empty($request_array['cust_banking_id']) ? $request_array['cust_banking_id'] : "";

    $beneAccNo = "";
    $beneIFSC = "";
    $beneName = "";

    $leadModelObj = new LeadModel();

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiKey = $apiConfig["ApiKey"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        if (empty($cust_banking_id)) {
            throw new Exception("Missing customer banking id.");
        }

        $appDataReturnArr = $leadModelObj->getLeadDetails($lead_id);

        if ($appDataReturnArr['status'] !== 1) {
            throw new Exception("Application details cannot be empty..");
        }

        if (empty($appDataReturnArr['app_data'])) {
            throw new Exception("Application details cannot be empty.");
        }

        $applicationDetails = $appDataReturnArr['app_data'];

        $bankingDataReturnArr = $leadModelObj->getCustomerBankAccountDetails($lead_id, $cust_banking_id);

        if ($bankingDataReturnArr['status'] !== 1) {
            throw new Exception("Customer banking details not found..");
        }

        if (empty($bankingDataReturnArr['banking_data'])) {
            throw new Exception("Customer banking details not found.");
        }

        $bankingDetails = $bankingDataReturnArr['banking_data'];

        if ($bankingDetails['account_status_id'] == 1) {
            throw new Exception("Customer banking already verified.");
        }

        $beneName = !empty($bankingDetails["beneficiary_name"]) ? $bankingDetails["beneficiary_name"] : "";
        $beneAccNo = !empty($bankingDetails["account"]) ? $bankingDetails["account"] : "";
        $beneIFSC = !empty($bankingDetails["ifsc_code"]) ? $bankingDetails["ifsc_code"] : "";
        $beneMobile = !empty($applicationDetails["mobile"]) ? $applicationDetails["mobile"] : "";
        $beneEmail = !empty($applicationDetails["email"]) ? $applicationDetails["email"] : "";

        if (empty($beneName)) {
            throw new Exception("Missing beneficiary name.");
        }

        if (empty($beneAccNo)) {
            throw new Exception("Missing beneficiary account number.");
        }

        if (empty($beneIFSC)) {
            throw new Exception("Missing beneficiary ifsc code.");
        }

        $apiRequestData = array(
            "beneficiaryAccount" => $beneAccNo,
            "beneficiaryName" => $beneName,
            "beneficiaryIFSC" => $beneIFSC,
            "nameFuzzy" => "true",
            "beneficiaryMobile" => $beneMobile,
            "email" => $beneEmail
        );

        $apiRequestJson = json_encode($apiRequestData);
        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        $apiHeaders = array(
            'Authorization: ' . $apiKey,
            'Content-Type: application/json'
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Request Header======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiHeaders,
        ));

        $apiResponseJson = curl_exec($curl);
        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        if (curl_errno($curl) && !$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

            if ($debug == 1) {
                echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (isset($apiResponseData['result']['active']) && $apiResponseData['result']['active'] == 'yes' && trim($apiResponseData['result']['bankTransfer']['response']) == 'Transaction Successful') {
                    $apiStatusId = 1;
                    $apiNameMatch = $apiResponseData['result']['nameMatch'];
                    $apiNameMatchScore = $apiResponseData['result']['nameMatchScore'];
                    $apiBeneName = $apiResponseData['result']['bankTransfer']['beneName'];
                } else if (isset($apiResponseData['result']['reason']) && !empty($apiResponseData['result']['reason'])) {
                    throw new ErrorException($apiResponseData['result']['reason']);
                } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                    throw new ErrorException($apiResponseData['error']['message']);
                } else {
                    throw new ErrorException("Some error occurred. Please try again.");
                }
            } else {
                throw new ErrorException("Some error occurred. Please try again..");
            }
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

    $insertApiLog = array();
    $insertApiLog["bav_user_id"] = $user_id;
    $insertApiLog["bav_provider_id"] = 2;
    $insertApiLog["bav_method_id"] = 2;
    $insertApiLog["bav_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["bav_cust_banking_id"] = !empty($cust_banking_id) ? $cust_banking_id : NULL;
    $insertApiLog["bav_api_status_id"] = $apiStatusId;
    $insertApiLog["bav_request"] = addslashes($apiRequestJson);
    $insertApiLog["bav_response"] = addslashes($apiResponseJson);
    $insertApiLog["bav_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["bav_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["bav_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

    $return_log_id = $leadModelObj->insertTable("api_bank_account_verification_logs", $insertApiLog);

    if (!empty($applicationDetails["lead_status_id"]) && $applicationDetails["lead_status_id"] > 0) {

        if ($apiStatusId == 1) {
            $call_description = "Signzy Penny Drop API(Success) <br> Account Number : $beneAccNo";
            $call_description .= " <br> Valid : " . $apiResponseData['result']['active'];
            //            $call_description .= " <br> NameMatch : " . $apiNameMatch;
            //            $call_description .= " <br> NameMatchScore : " . $apiNameMatchScore;
            $call_description .= " <br> Return BeneName : " . $apiBeneName;

            $leadModelObj->updateTable('customer_banking', ['beneficiary_name' => $apiBeneName], ' id=' . $cust_banking_id);
        } else {
            $call_description = "Signzy Penny Drop API(Fail) <br> Account Number : $beneAccNo <br> Error : $errorMessage";
        }

        $leadModelObj->insertApplicationLog($lead_id, $applicationDetails["lead_status_id"], $call_description);
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $return_log_id;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['actual_error'] = $insertApiLog["bav_errors"];
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['raw_response'] = $apiResponseJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }


    return $returnResponseData;
}

function digitap_bank_account_verification_api($lead_id, $request_array = array()) {


    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "DIGITAP_API";
    $sub_type = "BANK_DIGITAP_ACCOUNT_VERIFICATION";



    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['sottest']) ? 1 : 0;

    $applicationDetails = [];

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL; //for testing
    $cust_banking_id = !empty($request_array['cust_banking_id']) ? $request_array['cust_banking_id'] : "";

    $beneAccNo = "";
    $beneIFSC = "";
    $beneName = "";

    $leadModelObj = new LeadModel();

    $apiResponseArray = array(
        "result" => array(
            "active" => "",
            "reason" => "",
            "nameMatch" => "",
            "mobileMatch" => "",
            "signzyReferenceId" => "",
            "auditTrail" => array(
                "nature" => "",
                "value" => "",
                "timestamp" => ""
            ),
            "nameMatchScore" => "",
            "bankTransfer" => array(
                "response" => "",
                "bankRRN" => "",
                "beneName" => "",
                "beneMMID" => "",
                "beneMobile" => "",
                "beneIFSC" => ""
            )
        ),
        "digitap_data" => array()
    );

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiKey = str_replace('Basic ', '', $apiConfig["ApiToken"]);

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        if (empty($cust_banking_id)) {
            throw new Exception("Missing customer banking id.");
        }

        $appDataReturnArr = $leadModelObj->getLeadDetails($lead_id);

        if ($appDataReturnArr['status'] !== 1) {
            throw new Exception("Application details cannot be empty..");
        }

        if (empty($appDataReturnArr['app_data'])) {
            throw new Exception("Application details cannot be empty.");
        }

        $applicationDetails = $appDataReturnArr['app_data'];

        $bankingDataReturnArr = $leadModelObj->getCustomerBankAccountDetails($lead_id, $cust_banking_id);

        if ($bankingDataReturnArr['status'] !== 1) {
            throw new Exception("Customer banking details not found..");
        }

        $bankingDetails = $bankingDataReturnArr['banking_data'];

        if ($bankingDetails['account_status_id'] == 1) {
            throw new Exception("Customer banking already verified.");
        }

        $beneName = !empty($bankingDetails["beneficiary_name"]) ? $bankingDetails["beneficiary_name"] : "";
        $beneAccNo = !empty($bankingDetails["account"]) ? $bankingDetails["account"] : "";
        $beneIFSC = !empty($bankingDetails["ifsc_code"]) ? $bankingDetails["ifsc_code"] : "";

        if (empty($beneName)) {
            throw new Exception("Missing beneficiary name.");
        }

        if (empty($beneAccNo)) {
            throw new Exception("Missing beneficiary account number.");
        }

        if (empty($beneIFSC)) {
            throw new Exception("Missing beneficiary ifsc code.");
        }

        $apiRequestData = array(
            "ifsc" => $beneIFSC,
            "accNo" => $beneAccNo,
            "benificiaryName" => $beneName,
            "address" => "",
            "clientRefNum" => ""
        );

        $apiRequestJson = json_encode($apiRequestData);
        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        $apiHeaders = array(
            "Authorization: " . $apiKey,
            "content-type: application/json"
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Request Header======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiHeaders,
        ));

        $apiResponseJson = curl_exec($curl);
        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        if (curl_errno($curl) && !$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (isset($apiResponseData['code']) && ($apiResponseData['code'] == "200" && $apiResponseData['model']['status'] == 'SUCCESS')) {
                    $apiStatusId = 1;
                    $apiResponseArray['digitap_data'] = $apiResponseData;
                    $apiResponseData = $apiResponseData['model'];

                    $apiResponseArray['result']['active'] = $apiResponseData['status'] == 'SUCCESS' ? 'yes' : 'no';
                    $apiResponseArray['result']['reason'] = $apiResponseData['status'] == 'SUCCESS' ?  strtolower($apiResponseData['status']) : '';
                    $apiResponseArray['result']['nameMatch'] = $apiResponseData['isNameMatch'] == true ? 'yes' : 'no';
                    $apiResponseArray['result']['signzyReferenceId'] = $apiResponseData['clientRefNum'];

                    $apiResponseArray['result']['nameMatchScore'] = 1;
                    $apiResponseArray['result']['bankTransfer']['response'] = $apiResponseData['status'] == 'SUCCESS' ? 'Transaction Successful' : 'Transaction Failed';
                    $apiResponseArray['result']['bankTransfer']['bankRRN'] = $apiResponseData['rrn'];
                    $apiResponseArray['result']['bankTransfer']['beneName'] = $apiResponseData['beneficiaryName'];
                    $apiResponseArray['result']['bankTransfer']['beneIFSC'] = $beneIFSC;

                    $apiBeneName = $apiResponseData['beneficiaryName'];

                    $apiResponseJson = "";
                    $apiResponseJson = json_encode($apiResponseArray);
                } else if ($apiResponseData['model']['status'] == 'FAILED' && $apiResponseData["code"] == '200') {
                    throw new Exception($apiResponseData['model']['desc']);
                } else if (!empty($apiResponseData['message']) && $apiResponseData["code"] == '400') {
                    throw new Exception($apiResponseData['message']);
                } else if (!empty($apiResponseData['message']) && $apiResponseData["code"] == '500') {
                    throw new Exception($apiResponseData['msg']);
                } else {
                    $tmp_error_msg = "Some error occurred. Please try again1.";
                    throw new ErrorException($tmp_error_msg);
                }
            } else {
                throw new ErrorException("Some error occurred. Please try again..");
            }
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

    $insertApiLog = array();
    $insertApiLog["bav_user_id"] = $user_id;
    $insertApiLog["bav_provider_id"] = 3;
    $insertApiLog["bav_method_id"] = 2;
    $insertApiLog["bav_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["bav_cust_banking_id"] = !empty($cust_banking_id) ? $cust_banking_id : NULL;
    $insertApiLog["bav_api_status_id"] = $apiStatusId;
    $insertApiLog["bav_request"] = addslashes($apiRequestJson);
    $insertApiLog["bav_response"] = addslashes($apiResponseJson);
    $insertApiLog["bav_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["bav_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["bav_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

    $return_log_id = $leadModelObj->insertTable("api_bank_account_verification_logs", $insertApiLog);

    if (!empty($applicationDetails["lead_status_id"]) && $applicationDetails["lead_status_id"] > 0) {

        if ($apiStatusId == 1) {
            $call_description = "DigiTap Penny Drop API(Success) <br> Account Number : $beneAccNo";
            $call_description .= " <br> Valid : " . $apiResponseData['result']['active'];
            //            $call_description .= " <br> NameMatch : " . $apiNameMatch;
            //            $call_description .= " <br> NameMatchScore : " . $apiNameMatchScore;
            $call_description .= " <br> Return BeneName : " . $apiBeneName;

            $leadModelObj->updateTable('customer_banking', ['beneficiary_name' => $apiBeneName], ' id=' . $cust_banking_id);
        } else {
            $call_description = "DigiTap Penny Drop API(Fail) <br> Account Number : $beneAccNo <br> Error : $errorMessage";
        }

        $leadModelObj->insertApplicationLog($lead_id, $applicationDetails["lead_status_id"], $call_description);
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $return_log_id;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['actual_error'] = $insertApiLog["bav_errors"];
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['raw_response'] = $apiResponseJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }


    return $returnResponseData;
}
