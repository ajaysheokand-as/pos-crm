<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('bank_account_verification_api_call')) {

    function bank_account_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "BANK_ACCOUNT_VERIFICATION" => 1,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 1) {
            $responseArray = nupay_bank_account_verification_api($lead_id, $request_array);
        } else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

        return $responseArray;
    }

}

if (!function_exists('nupay_bank_account_verifiation_token_api')) {

    function nupay_bank_account_verifiation_token_api($lead_id, $request_array = array()) {

        $envSet = ENVIRONMENT;

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;

        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";

        $type = "BANK_ACCOUNT_VERIFICATION";
        $sub_type = "NUPAY_AUTH_TOKEN";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

        $cust_banking_id = !empty($request_array['cust_banking_id']) ? $request_array['cust_banking_id'] : "";

        $token_string = "";

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


            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];

                if (empty($applicationDetails)) {
                    throw new Exception("Application details cannot be empty.");
                }
            }

            $tempDetails = $ci->IntegrationModel->getBankAccountVerifiationLastToken();

            if ($tempDetails['status'] === 1) {

                $tempDetails = $tempDetails['token_data'];

                $token_response_datetime = $tempDetails['bav_response_datetime'];

                $difference_in_minute = intval((strtotime(date("Y-m-d H:i:s")) - strtotime($token_response_datetime)) / 60);
                // api will be not called if last token fetched in last 23 Hour, token validity is 24 hours.
                if (!empty($tempDetails['bav_auth_token']) && !empty($token_response_datetime) && $difference_in_minute >= 0 && $difference_in_minute < 1380) {
                    $token_string = $tempDetails['bav_auth_token'];
                    $return_array = array();
                    $return_array['status'] = 1;
                    $return_array['token'] = $token_string;
                    return $return_array;
                }
            }


            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            $apiHeaders = array(
                "api-key: $apiKey",
                "Content-Type: multipart/form-data"
            );

            if ($debug == 1) {
                echo "<br/><br/> =======Request Header======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
                $apiResponseJson = '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwidGltZXN0YW1wIjoxNjQ2MTMwNzUyfQ.YeW2aPjAz67P4ai_c_TH1-nrj1NxF6vEY1Lz__Kc4C4"}';
            } else {
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                $apiResponseJson = curl_exec($curl);
            }

            $apiResponseDateTime = date("Y-m-d H:i:s");

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

                    $apiResponseData = trim_data_array($apiResponseData);

                    if (!empty($apiResponseData['token'])) {
                        $apiStatusId = 1;
                        $token_string = $apiResponseData['token'];
                    } else {
                        $tmp_error = !empty($apiResponseData['error']) ? $apiResponseData['error'] : "Some error occurred. Please try again.";
                        throw new ErrorException($tmp_error);
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
        $insertApiLog["bav_provider_id"] = 1;
        $insertApiLog["bav_method_id"] = 1;
        $insertApiLog["bav_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["bav_cust_banking_id"] = !empty($cust_banking_id) ? $cust_banking_id : NULL;
        $insertApiLog["bav_api_status_id"] = $apiStatusId;
        $insertApiLog["bav_auth_token"] = $token_string;
        $insertApiLog["bav_request"] = addslashes($apiRequestJson);
        $insertApiLog["bav_response"] = addslashes($apiResponseJson);
        $insertApiLog["bav_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["bav_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["bav_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_bank_account_verification_logs", $insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['token'] = $token_string;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }


        return $returnResponseData;
    }

}

if (!function_exists('nupay_bank_account_verification_api')) {

    function nupay_bank_account_verification_api($lead_id, $request_array = array()) {

        $envSet = ENVIRONMENT;

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";

        $type = "BANK_ACCOUNT_VERIFICATION";
        $sub_type = "NUPAY_PENNY_DROP";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : NULL; //for testing

        $cust_banking_id = !empty($request_array['cust_banking_id']) ? $request_array['cust_banking_id'] : "";

        $beneAccNo = "";
        $beneIFSC = "";
        $beneName = "";

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

            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];

                if (empty($applicationDetails)) {
                    throw new Exception("Application details cannot be empty.");
                }
            }

            $bankingDataReturnArr = $ci->IntegrationModel->getCustomerBankAccountDetails($lead_id, $cust_banking_id);

            if ($bankingDataReturnArr['status'] === 1) {

                $bankingDetails = $bankingDataReturnArr['banking_data'];

                if (empty($bankingDetails)) {
                    throw new Exception("Customer banking details not found.");
                } else if ($bankingDetails['account_status_id'] == 1) {
                    throw new Exception("Customer banking already verified.");
                } else {
                    $beneName = !empty($bankingDetails["beneficiary_name"]) ? $bankingDetails["beneficiary_name"] : "";
                    $beneAccNo = !empty($bankingDetails["account"]) ? $bankingDetails["account"] : "";
                    $beneIFSC = !empty($bankingDetails["ifsc_code"]) ? $bankingDetails["ifsc_code"] : "";
                }
            } else {
                throw new Exception("Please verify the customer banking details.");
            }

            if (empty($beneName)) {
                throw new Exception("Missing beneficiary name.");
            }

            if (empty($beneAccNo)) {
                throw new Exception("Missing beneficiary account number.");
            }

            if (empty($beneIFSC)) {
                throw new Exception("Missing beneficiary ifsc code.");
            }

            $token_return = nupay_bank_account_verifiation_token_api($lead_id, $request_array);

            if ($token_return['status'] != 1) {
                return $token_return;
            }

            $apiToken = $token_return['token'];

            $requestId = date("YmdHis") . rand(1000, 9999);

            $apiRequestArray = array(
                "reference_number" => $requestId,
                "ifsc_code" => $beneIFSC,
                "accounts" => $beneAccNo,
                "account_name" => $beneName,
            );

            $apiRequestJson = json_encode($apiRequestArray);

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            $apiHeaders = array(
                "api-key: $apiKey",
                "Content-Type: multipart/form-data",
                "Token: $apiToken"
            );

            if ($debug == 1) {
                echo "<br/><br/> =======Request Header======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
                $apiResponseJson = '{"StatusCode": "NP000","StatusDesc": "Name and account number successfully verified.[Hardcode]","BankResponse": "' . $beneName . '"}';
            } else {
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestArray);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                $apiResponseJson = curl_exec($curl);
            }

            $apiResponseDateTime = date("Y-m-d H:i:s");

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

                    $apiResponseData = trim_data_array($apiResponseData);

                    if (isset($apiResponseData['StatusCode']) && $apiResponseData['StatusCode'] == 'NP000') {
                        $apiStatusId = 1;
                    } else {
                        $tmp_error = "";

                        if (!empty($apiResponseData['StatusDesc'])) {
                            $tmp_error = $apiResponseData['StatusDesc'];
                        }

                        if (!empty($apiResponseData['BankResponse'])) {
                            $tmp_error .= " | BankResponse : " . $apiResponseData['BankResponse'];
                        }

                        $tmp_error = !empty($tmp_error) ? $tmp_error : "Some error occurred. Please try again.";

                        throw new ErrorException($tmp_error);
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
        $insertApiLog["bav_provider_id"] = 1;
        $insertApiLog["bav_method_id"] = 2;
        $insertApiLog["bav_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["bav_cust_banking_id"] = !empty($cust_banking_id) ? $cust_banking_id : NULL;
        $insertApiLog["bav_api_status_id"] = $apiStatusId;
        $insertApiLog["bav_request"] = addslashes($apiRequestJson);
        $insertApiLog["bav_response"] = addslashes($apiResponseJson);
        $insertApiLog["bav_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["bav_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["bav_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_bank_account_verification_logs", $insertApiLog);

        if (!empty($applicationDetails["lead_status_id"]) && $applicationDetails["lead_status_id"] > 0) {

            if ($apiStatusId == 1) {
                $call_description = "NUPAY Penny Drop API(Success) <br> Account Number : $beneAccNo <br> RESULT : " . $apiResponseData['StatusDesc'] . " <br> Customer Name : " . $apiResponseData['BankResponse'];
            } else {
                $call_description = "NUPAY Penny Drop API(Fail) <br> Account Number : $beneAccNo <br> Error : $errorMessage";
            }

            $lead_followup = [
                'lead_id' => $lead_id,
                'user_id' => $user_id,
                'status' => $applicationDetails["status"],
                'stage' => $applicationDetails["stage"],
                'lead_followup_status_id' => $applicationDetails["lead_status_id"],
                'remarks' => addslashes($call_description),
                'created_on' => date("Y-m-d H:i:s")
            ];

            $ci->IntegrationModel->insert("lead_followup", $lead_followup);
        }

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }


        return $returnResponseData;
    }

}


