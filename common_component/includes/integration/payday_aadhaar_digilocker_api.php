
<?php

use Aws\EndpointV2\Rule\ErrorRule;
use Prophecy\Exception\Prediction\NoCallsException;
use SebastianBergmann\Exporter\Exporter;

function aadhaar_digilocker_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(5, "Aadhaar DIGILOCKER started | $lead_id | $method_name");
    //   print_r("testing");die;

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "DIGILOCKER_CREATE_URL" => 1,
        "DIGILOCKER_GET_DETAILS" => 2,
        "DIGILOCKER_GET_FILES" => 3,
        "DIGILOCKER_GET_EAADHAAR" => 4,
        "DIGILOCKER_PULL_DOCS" => 5,
        "DIGITAP_EKYC_CREATE_OTP" => 6,
        "DIGITAP_EKYC_SUCCESS" => 7,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = digilocker_create_url_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = digilocker_get_details_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 3) {
        $responseArray = digilocker_get_file_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 4) {
        $responseArray = digilocker_get_eaadhaar_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 6) {
        $responseArray = digitap_ekyc_create_otp_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 7) {
        $responseArray = digitap_ekyc_create_otp_api_success($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(5, "Aadhaar DIGILOCKER API end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function digilocker_create_url_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(5, "digilocker_create_url_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "DIGILOCKER";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $aadhaar_no_last_4_digit = "";
    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $digilocker_request_id = "";
    $digilocker_url = "";

    $lw_redirect_url = COMP_CRM_URL . 'aadhaar-veri-response?refstr=' . $request_array['lead_id'];
    $lw_callback_url = "";

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

        $aadhaar_no_last_4_digit = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";

        //$aadhaar_no_last_4_digit = substr($aadhaar_no, 8, 4);


        if (empty($aadhaar_no_last_4_digit)) {
            throw new Exception("Missing aadhaar number last 4 digit.");
        }

        if (!empty($app_data['customer_digital_ekyc_flag']) && $app_data['customer_digital_ekyc_flag'] == 1) {
            throw new Exception("E-KYC already done.");
        }

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 1);

        if ($digilockerDetails['status'] == 1) {

            $digilocker_log_data = $digilockerDetails['digilocker_log_data'];

            $tempApiResponseData = json_decode($digilocker_log_data['ekyc_response'], true);

            if (!empty($tempApiResponseData)) {

                $tempApiResponseData = common_trim_data_array($tempApiResponseData);

                if (!empty($tempApiResponseData)) {

                    if (isset($tempApiResponseData['result']) && !empty($tempApiResponseData['result'])) {

                        $tempApiResponseData = $tempApiResponseData['result'];

                        if (!empty($tempApiResponseData['requestId']) && !empty($tempApiResponseData['url'])) {
                            $response_array['status'] = 1;
                            $response_array['data'] = $tempApiResponseData;
                            $response_array['digilocker_url'] = $tempApiResponseData['url'];
                            $response_array['digilocker_request_id'] = $tempApiResponseData['requestId'];
                            return $response_array;
                        }
                    }
                }
            }
        }


        // $token_return_array = signzy_token_api_call(1, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        // $apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);

        // $apiRequestJson = '{
        //                     "task" : "url",
        //                     "essentials": {
        //                         "signup": false,
        //                         "redirectUrl": "' . $lw_redirect_url . '",
        //                         "redirectTime": "2",
        //                         "callbackUrl": "' . $lw_callback_url . '"
        //                     }
        //                   }';

        // $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        // if ($debug) {
        //     echo "<br/><br/>=======Request JSON=========<br/><br/>";
        //     echo $apiRequestJson;
        // }


        // $apiHeaders = array(
        //     "content-type: application/json",
        //     "accept-language: en-US,en;q=0.8",
        //     "accept: */*",
        //     "Authorization: $token_string"
        // );

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        // $curl = curl_init($apiUrl);
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        // $apiResponseJson = curl_exec($curl);

        $validity_period = 86400; // 24 hours
        $expiry_timestamp = time() + $validity_period;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker/createUrl',
            CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker-v2/createUrl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "signup": true,
            "redirectUrl": "https://crm.tejasloan.com/aadhaar-veri-response?refstr=' . $lead_id . '",
            "redirectTime": "1",
            "callbackUrl": "https://crm.tejasloan.com/aadhaar-veri-response?refstr=' . $lead_id . '",
            "customerId": "<customer ID>",
            "successRedirectUrl": "https://crm.tejasloan.com/aadhaar-veri-response?refstr=' . $lead_id . '",
            "successRedirectTime": "5",
            "failureRedirectUrl": "https://www.signzy.com/",
            "failureRedirectTime": "5",
            "logoVisible": "true",
            "logo": "https://rise.barclays/content/dam/thinkrise-com/images/rise-stories/Signzy-16_9.full.high_quality.jpg",
            "supportEmailVisible": "true",
            "supportEmail": "support@signzy.com",
            "docType": [
                "PANCR",
                "ADHAR"
            ],
            "purpose": "kyc",
            "getScope": true,
            "consentValidTill": ' . $expiry_timestamp . ',
            "showLoaderState": true,
            "internalId": "<Internal ID>",
            "companyName": "Signzy",
            "favIcon": "https://rise.barclays/content/dam/thinkrise-com/images/rise-stories/Signzy-16_9.full.high_quality.jpg"
        }',
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
            ),
        ));


        $apiResponseJson = curl_exec($curl);

        //curl_close($curl);
        // echo $apiResponseJson;die;


        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {

                    if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                        $apiResponseData = $apiResponseData['result'];

                        if (!empty($apiResponseData['requestId']) && !empty($apiResponseData['url'])) {
                            $apiStatusId = 1;
                            $digilocker_request_id = $apiResponseData['requestId'];
                            $digilocker_url = $apiResponseData['url'];
                        } else {
                            throw new ErrorException("Digilocker url does not received from API.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        $tmp_error_msg = "Some error occurred. Please try again.";
                        throw new ErrorException($tmp_error_msg);
                    }
                } else {
                    throw new ErrorException("Some error occurred. Please try again..");
                }
            } else {
                throw new ErrorException("Empty response from eSign Doc API.");
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

    if ($apiStatusId == 1) {
        $lead_remarks = "Digilocker REQUEST API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";
        $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_ekyc_request_ip' => $_SERVER['REMOTE_ADDR'], 'customer_ekyc_request_initiated_on' => date("Y-m-d H:i:s")]);
    } else {
        $lead_remarks = "Digilocker REQUEST API CALL (Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ekyc_provider"] = 1;
    $insertApiLog["ekyc_method_id"] = $method_id;
    $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
    $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
    $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
    $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no_last_4_digit;
    $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ekyc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ekyc_user_id"] = $user_id;
    $insertApiLog["ekyc_return_url"] = $digilocker_url;
    $insertApiLog["ekyc_return_request_id"] = $digilocker_request_id;

    $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['digilocker_url'] = $digilocker_url;
    $response_array['digilocker_request_id'] = $digilocker_request_id;
    $response_array['errors'] = !empty($errorMessage) ? "Digilocker Request Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function digilocker_get_details_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(5, "digilocker_get_details_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "DIGILOCKER";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $aadhaar_no_last_4_digit = "";
    $eaadhaar_available_flag = "N";

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
        $customer_dob = !empty($app_data['dob']) ? date("d/m/Y", strtotime($app_data['dob'])) : "";

        $aadhaar_no = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";

        if (!empty($app_data['customer_digital_ekyc_flag']) && $app_data['customer_digital_ekyc_flag'] == 1) {
            throw new Exception("E-KYC already done.");
        }


        // $token_return_array = signzy_token_api_call(1, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        // $apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 1);

        if ($digilockerDetails['status'] != 1) {
            throw new Exception("Digilocker Request details not found");
        }

        $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";

        $digilocker_request_id = $digilocker_log_data['ekyc_return_request_id'];

        $aadhaar_no_last_4_digit = $digilocker_log_data['ekyc_aadhaar_no'];

        if ($aadhaar_no_last_4_digit != $aadhaar_no) {
            throw new Exception("Aadhaar number does not mateched for initiated request.");
        }


        // $apiRequestJson = '{
        //                         "task" : "getDetails",
        //                         "essentials" : {
        //                             "requestId" : "' . $digilocker_request_id . '"
        //                         }
        //                   }';

        // $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        // if ($debug) {
        //     echo "<br/><br/>=======Request JSON=========<br/><br/>";
        //     echo $apiRequestJson;
        // }


        // $apiHeaders = array(
        //     "content-type: application/json",
        //     "accept-language: en-US,en;q=0.8",
        //     "accept: */*",
        //     "Authorization: $token_string"
        // );

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        // $curl = curl_init($apiUrl);
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker/getDetails',
            CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker-v2/getDetails',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "requestId": "' . $digilocker_request_id . '"
        }',
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
            ),
        ));



        $apiResponseJson = curl_exec($curl);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {

                    if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                        $apiResponseData = $apiResponseData['result'];
                        $apiUserDetailsData = $apiResponseData['userDetails'];

                        if (!empty($apiUserDetailsData['digilockerid'])) {

                            $apiStatusId = 1;

                            if (!empty($apiUserDetailsData['eaadhaar']) && $apiUserDetailsData['eaadhaar'] == "Y") {
                                $eaadhaar_available_flag = "Y";
                            }
                        } else {
                            throw new ErrorException("Aadhaar details does not received from API.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        $tmp_error_msg = "Some error occurred. Please try again.";
                        throw new ErrorException($tmp_error_msg);
                    }
                } else {
                    throw new ErrorException("Please check raw response for error details");
                }
            } else {
                throw new ErrorException("Empty response from eSign Request API");
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

    if ($apiStatusId == 1) {
        $lead_remarks = "Digilocker GetDetails API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";
        if ($eaadhaar_available_flag == "Y") {
            $lead_remarks .= "<br>E-Aadhaar available : YES";
        } else {
            $lead_remarks .= "<br>E-Aadhaar available : NO";
        }
    } else {
        $lead_remarks = "Digilocker GetDetails API CALL(Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ekyc_provider"] = 1;
    $insertApiLog["ekyc_method_id"] = $method_id;
    $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
    $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
    $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
    $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no_last_4_digit;
    $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ekyc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ekyc_user_id"] = $user_id;
    $insertApiLog["ekyc_eaadhaar_available_flag"] = $eaadhaar_available_flag;
    $insertApiLog["ekyc_return_request_id"] = $digilocker_request_id;

    $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    //    $response_array['nsdl_url'] = $aadhaar_nsdl_url;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Digilocker GetDetails Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    if ($apiStatusId == 1 && $eaadhaar_available_flag == "Y") {
        $temp_eadhaar_return = digilocker_get_eaadhaar_api_call(4, $lead_id, $request_array);

        if ($temp_eadhaar_return['status'] == 5) {
            $response_array['aadhaar_photo'] = $temp_eadhaar_return['aadhaar_photo'];

            $temp_aadhaar_files_return = digilocker_get_file_api_call(3, $lead_id, $request_array);
            if ($temp_aadhaar_files_return['status'] == 1) {
                $response_array['digilocker_files'] = $temp_aadhaar_files_return['digilocker_files'];
                $response_array['fetch_document_array'] = $temp_aadhaar_files_return['fetch_document_array'];
            }
        }
    }


    return $response_array;
}

function digilocker_get_eaadhaar_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(5, "digilocker_get_eaadhaar_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "DIGILOCKER";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $aadhaar_no_last_4_digit = "";
    $aadhaar_no = "";
    $customer_dob = "";

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
        $customer_dob = !empty($app_data['dob']) ? date("d/m/Y", strtotime($app_data['dob'])) : "";

        $aadhaar_no = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";
        $customer_id = !empty($app_data['customer_id']) ? trim($app_data['customer_id']) : "";
        $pancard = !empty($app_data['pancard']) ? trim($app_data['pancard']) : "";
        $mobile = !empty($app_data['mobile']) ? trim($app_data['mobile']) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 2);

        if ($digilockerDetails['status'] != 1) {
            throw new Exception("Digilocker Request details not found");
        }

        $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";

        $digilocker_request_id = $digilocker_log_data['ekyc_return_request_id'];

        $eaadhaar_available_flag = $digilocker_log_data['ekyc_eaadhaar_available_flag'];

        $aadhaar_no_last_4_digit = $digilocker_log_data['ekyc_aadhaar_no'];

        if ($aadhaar_no_last_4_digit != $aadhaar_no) {
            throw new Exception("Aadhaar number does not mateched for initiated request.");
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");


        $curl = curl_init();

        curl_setopt_array($curl, array(
            // CURLOPT_URL => "https://api.signzy.app/api/v3/digilocker/geteaadhaar",
            CURLOPT_URL => "https://api.signzy.app/api/v3/digilocker-v2/geteAadhaar",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "requestId": "' . $digilocker_request_id . '",
            "extraDigitalCertificateParams" : true
        }',
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
            ),
        ));

        $apiResponseJson = curl_exec($curl);


        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {

                    if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                        $apiResponseData = $apiResponseData['result'];

                        if (!empty($apiResponseData['uid']) && !empty($apiResponseData['dob'])) {
                            $apiStatusId = 1;
                        } else {
                            throw new ErrorException("E-Aadhaar data does not received from API.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        $tmp_error_msg = "Some error occurred. Please try again.";
                        throw new ErrorException($tmp_error_msg);
                    }
                } else {
                    throw new ErrorException("Please check raw response for error details");
                }
            } else {
                throw new ErrorException("Empty response from eSign Request API");
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

    if ($apiStatusId == 1) {
        $lead_remarks = "Digilocker E-Aadhaar API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";
        if (substr($apiResponseData['uid'], 8, 4) == $aadhaar_no && $apiResponseData['dob'] == $customer_dob) {
            $apiStatusId = 5;
            $lead_remarks .= "<br/>Result : Aadhaar last four digit and dob match";

            if (!empty($apiResponseData['photo'])) {
                if (COMP_DOC_S3_FLAG == true) {
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = base64_encode(file_get_contents($apiResponseData['photo']));
                    $upload_file['ext'] = 'jpeg';

                    $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($upload_status['status'] == 1) {
                        $file_write_flag = true;
                        $generated_file_name = $upload_status['file_name'];
                    }
                } else {
                    $generated_file_name = "DIGILOCKER_PHOTO_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";
                    $file_write_flag = file_put_contents(COMP_DOC_PATH . $generated_file_name, file_get_contents($apiResponseData['photo']));
                }

                if ($file_write_flag) {

                    $document_insert_array = array();
                    $document_insert_array["lead_id"] = $lead_id;
                    $document_insert_array["customer_id"] = $customer_id;
                    $document_insert_array["pancard"] = $pancard;
                    $document_insert_array["mobile"] = $mobile;
                    $document_insert_array["file"] = $generated_file_name;
                    $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                    $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                    $document_insert_array["upload_by"] = $user_id;
                    $document_insert_array["docs_master_id"] = 19;
                    $document_insert_array["docs_type"] = "DIGILOCKER";
                    $document_insert_array["sub_docs_type"] = "AADHAAR PHOTO";

                    $leadModelObj->insertTable("docs", $document_insert_array);
                }
            }

            $aadhaar_complete_address = !empty($apiResponseData['address']) ? $apiResponseData['address'] : "";
            $aadhaar_city = !empty($apiResponseData['splitAddress']['city'][0]) ? $apiResponseData['splitAddress']['city'][0] : "";
            $aadhaar_district = !empty($apiResponseData['splitAddress']['district'][0]) ? $apiResponseData['splitAddress']['district'][0] : "";
            $aadhaar_addressLine = !empty($apiResponseData['splitAddress']['addressLine']) ? $apiResponseData['splitAddress']['addressLine'] : "";
            $aadhaar_addressLandmark = !empty($apiResponseData['splitAddress']['landMark']) ? $apiResponseData['splitAddress']['landMark'] : $aadhaar_district;
            $aadhaar_pincode = !empty($apiResponseData['splitAddress']['pincode']) ? $apiResponseData['splitAddress']['pincode'] : "";

            $pincodeDetails = $leadModelObj->getCityStateByPincode($aadhaar_pincode);

            $m_state_id = "";
            $m_state_name = "";
            $m_city_id = "";
            $m_city_name = "";

            if ($pincodeDetails['status'] == 1) {
                $m_state_id = $pincodeDetails["pincode_data"]["m_state_id"];
                $m_state_name = $pincodeDetails["pincode_data"]["m_state_name"];
                $m_city_id = $pincodeDetails["pincode_data"]["m_city_id"];
                $m_city_name = $pincodeDetails["pincode_data"]["m_city_name"];
            }

            $aadhaar_array = [
                'aa_current_house' => $aadhaar_addressLine,
                'aa_current_locality' => $aadhaar_city,
                'aa_current_landmark' => $aadhaar_addressLandmark,
                'aa_current_eaadhaar_address' => $aadhaar_complete_address,
                'aa_current_state' => $m_state_name,
                'aa_current_state_id' => $m_state_id,
                'aa_current_city' => $m_city_name,
                'aa_current_city_id' => $m_city_id,
                'aa_cr_residence_pincode' => $aadhaar_pincode,
                'aa_current_district' => $aadhaar_district,
                'customer_digital_ekyc_flag' => 1,
                'customer_digital_ekyc_done_on' => date("Y-m-d H:i:s")
            ];

            $leadModelObj->updateLeadCustomerTable($lead_id, $aadhaar_array);
        } else if (substr($apiResponseData['uid'], 8, 4) != $aadhaar_no) {
            $apiStatusId = 7;
            $lead_remarks .= "<br/>Result : Aadhaar number does not matched with input aadhaar.[" . $apiResponseData['uid'] . "]";
        } else if ($apiResponseData['dob'] != $customer_dob) {
            $apiStatusId = 6;
            $lead_remarks .= "<br/>Result : Aadhaar dob does not matched with input aadhaar.[" . $apiResponseData['dob'] . "]";
        } else {
            $apiStatusId = 8;
            $lead_remarks .= "<br/>Result : Aadhaar number last four digit and dob not matched. [" . $apiResponseData['uid'] . " - " . $apiResponseData['dob'] . "]";
        }
    } else {
        $lead_remarks = "Digilocker E-Aadhaar API CALL(Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }



    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ekyc_provider"] = 1;
    $insertApiLog["ekyc_method_id"] = $method_id;
    $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
    $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
    $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
    $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no_last_4_digit;
    $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ekyc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ekyc_user_id"] = $user_id;
    $insertApiLog["ekyc_return_request_id"] = $digilocker_request_id;

    $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Digilocker E-Aadhaar Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;
    $response_array['aadhaar_photo'] = $apiResponseData['photo'];
    return $response_array;
}

function digilocker_get_file_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(5, "digilocker_get_file_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "DIGILOCKER";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $aadhaar_no_last_4_digit = "";
    $digilocker_request_id = "";
    $api_return_files = array();
    $fetch_document_array = array();

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

        $aadhaar_no = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";
        $customer_id = !empty($app_data['customer_id']) ? trim($app_data['customer_id']) : "";
        $pancard = !empty($app_data['pancard']) ? trim($app_data['pancard']) : "";
        $mobile = !empty($app_data['mobile']) ? trim($app_data['mobile']) : "";

        // $aadhaar_no = substr($aadhaar_no, 8, 4);

        // $token_return_array = signzy_token_api_call(1, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        // $apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 2);
        if ($digilockerDetails['status'] != 1) {
            throw new Exception("Digilocker Request details not found");
        }

        $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";


        $digilocker_request_id = $digilocker_log_data['ekyc_return_request_id'];

        $aadhaar_no_last_4_digit = $digilocker_log_data['ekyc_aadhaar_no'];

        if ($aadhaar_no_last_4_digit != $aadhaar_no) {
            throw new Exception("Aadhaar number does not mateched for initiated request.");
        }

        $digilocker_response = $digilocker_log_data["ekyc_response"];

        if (!empty($digilocker_response)) {

            $digilocker_response = json_decode($digilocker_response, true);

            $temp_array = $digilocker_response['result']['files'];

            $digilocker_document_ids = array();

            foreach ($temp_array as $doc_array) {

                if (in_array($doc_array['doctype'], array('ADHAR', 'DRVLC', 'PANCR'))) { //,'UNCRD'
                    if (empty($fetch_document_array[$doc_array['doctype']])) {
                        $fetch_document_array[$doc_array['doctype']] = $doc_array['id'];
                        $digilocker_document_ids[] =  $doc_array['id'];
                    }
                }
            }
        }

        // $digilocker_document_ids = rtrim($digilocker_document_ids, ",");

        if (empty($digilocker_document_ids)) {
            throw new Exception("No document received to fetch digilocker docs.");
        }

        $apiRequestData = array(
            "requestId" =>  $digilocker_request_id,
            "fileIds" => $digilocker_document_ids
        );

        $apiRequestJson = preg_replace("!\s+!", " ", json_encode($apiRequestData));

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }


        // $apiHeaders = array(
        //     "content-type: application/json",
        //     "accept-language: en-US,en;q=0.8",
        //     "accept: */*",
        //     "Authorization: $token_string"
        // );

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        // $curl = curl_init($apiUrl);
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);


        $curl = curl_init();

        curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker/getFiles',
            CURLOPT_URL => 'https://api.signzy.app/api/v3/digilocker-v2/getFiles',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
            ),
        ));

        $apiResponseJson = curl_exec($curl);


        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {

                    if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                        $api_return_files = $apiResponseData['result']['files'];

                        if (!empty($api_return_files)) {
                            $apiStatusId = 1;
                        } else {
                            throw new ErrorException("Document does not received from API.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        $tmp_error_msg = "Some error occurred. Please try again.";
                        throw new ErrorException($tmp_error_msg);
                    }
                } else {
                    throw new ErrorException("Please check raw response for error details");
                }
            } else {
                throw new ErrorException("Empty response from eSign Request API");
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
    $insertApiLog["ekyc_provider"] = 1;
    $insertApiLog["ekyc_method_id"] = $method_id;
    $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
    $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
    $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
    $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no_last_4_digit;
    $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ekyc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ekyc_user_id"] = $user_id;
    $insertApiLog["ekyc_return_request_id"] = $digilocker_request_id;

    $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);

    if ($apiStatusId == 1) {
        $lead_remarks = "Digilocker GetFiles API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";
    } else {
        $lead_remarks = "Digilocker GetFiles API CALL(Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    if ($apiStatusId == 1) {
        $lead_remarks = "Digilocker GetFiles API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";

        foreach ($api_return_files as $document_array) {

            if (!empty($fetch_document_array[$document_array['doctype']])) {

                if (!empty($document_array["id"]) && $document_array["id"] == $fetch_document_array[$document_array['doctype']] && !empty($document_array["file"]['pdf'])) {

                    if (COMP_DOC_S3_FLAG == true) {
                        require_once(COMP_PATH . '/CommonComponent.php');

                        $CommonComponent = new CommonComponent();

                        $upload_file = array();
                        $upload_file['flag'] = 1;
                        $upload_file['file'] = base64_encode(file_get_contents($document_array["file"]['pdf']));
                        $upload_file['ext'] = 'pdf';

                        $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                        if ($upload_status['status'] == 1) {
                            $file_write_flag = true;
                            $generated_file_name = $upload_status['file_name'];
                        }
                    } else {
                        $generated_file_name = "DIGILOCKER_" . $document_array['doctype'] . "_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".pdf";

                        $file_write_flag = file_put_contents(COMP_DOC_PATH . $generated_file_name, file_get_contents($document_array["file"]['pdf']));
                    }

                    if ($file_write_flag) {

                        $document_insert_array = array();
                        $document_insert_array["lead_id"] = $lead_id;
                        $document_insert_array["customer_id"] = $customer_id;
                        $document_insert_array["pancard"] = $pancard;
                        $document_insert_array["mobile"] = $mobile;
                        $document_insert_array["file"] = $generated_file_name;
                        $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                        $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                        $document_insert_array["upload_by"] = $user_id;

                        if ($document_array['doctype'] == "ADHAR") {
                            $document_insert_array["docs_master_id"] = 20;
                            $document_insert_array["docs_type"] = "DIGILOCKER";
                            $document_insert_array["sub_docs_type"] = "AADHAAR CARD";
                        } else if ($document_array['doctype'] == "PANCR") {
                            $document_insert_array["docs_master_id"] = 21;
                            $document_insert_array["docs_type"] = "DIGILOCKER";
                            $document_insert_array["sub_docs_type"] = "PAN CARD";
                        } else if ($document_array['doctype'] == "DRVLC") {
                            $document_insert_array["docs_master_id"] = 23;
                            $document_insert_array["docs_type"] = "DIGILOCKER";
                            $document_insert_array["sub_docs_type"] = "DRIVING LICENCE";
                        }

                        $leadModelObj->insertTable("docs", $document_insert_array);
                    }
                }
            }
        }
    } else {
        $lead_remarks = "Digilocker GetFiles API CALL(Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Digilocker GetFiles Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;
    $response_array['digilocker_files'] = $api_return_files;
    $response_array['fetch_document_array'] = $fetch_document_array;
    return $response_array;
}

function digitap_ekyc_create_otp_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(8, "digitap_ekyc_create_otp_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");
    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "DIGITAP_API";
    $sub_type = "DIGITAP_EKYC_CREATE_OTP";

    $hardcode_response = false;

    $applicationDetails = array();

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();
    $aadhaar_no = "";
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

        $apiUrl = $apiConfig["ApiUrl"];
        $apiToken = $apiConfig["ApiToken"];

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiUrl);
            echo "<br/>";
            print_r($apiToken);
        }

        if (empty($apiUrl) || empty($apiToken)) {
            throw new Exception("API URL or Token not found.");
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] === 1) {
            $applicationDetails = $LeadDetails['app_data'];
        } else {
            throw new Exception("Lead Details does not exist.");
        }

        $aadhaar_no_last_4_digit = !empty($applicationDetails['aadhar_no']) ? $applicationDetails['aadhar_no'] : "";
        $unique_id = $request_array['lead_id'] . date("Ymdhis");
        $aadhaar_no = "";

        $aadhaar_no = $request_array['aadhaar_no'];

        if ($aadhaar_no_last_4_digit != substr($aadhaar_no, -4)) {
            throw new Exception("Aadhaar number does not mateched for initiated request. | " . $aadhaar_no_last_4_digit);
        }

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 7);
        if ($digilockerDetails['status'] == 1) {
            $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";
            $digilocker_log_data = !empty($digilocker_log_data['ekyc_response']) ? $digilocker_log_data['ekyc_response'] : "";

            $apiResponseData = json_decode($digilocker_log_data, true);
            $apiResponseData = common_trim_data_array($apiResponseData);

            if ($debug) {
                echo "<pre>";
                print_r($apiResponseData);
            }

            $apiResponseData = $apiResponseData['model'];
            $aadhaar_no_last_4_digit = substr($apiResponseData["maskedAdharNumber"], 8, 4);

            throw new Exception("E-KYC already done.");
        }

        $apiRequestData =  array(
            "uniqueId" => $unique_id,
            "uid" => $aadhaar_no
        );

        $apiRequestJson = preg_replace("!\s+!", " ", json_encode($apiRequestData));

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiHeaderData =  array(
            "Authorization:  " . str_replace("Basic ", "", $apiToken),
            'Content-Type: application/json'
        );

        if ($debug) {
            echo "<br/><br/>=======Header JSON=========<br/><br/>";
            print_r($apiHeaderData);
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
            CURLOPT_HTTPHEADER => $apiHeaderData,
        ));

        $apiResponseJson = curl_exec($curl);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData['msg']) && $apiResponseData['code'] == '200') {
                    $apiStatusId = 1;
                } else if (!empty($apiResponseData['msg']) && $apiResponseData["code"] == '400') {
                    throw new Exception($apiResponseData['msg']);
                } else {
                    $tmp_error_msg = "Some error occurred. Please try again1.";
                    throw new ErrorException($tmp_error_msg);
                }
            } else {
                throw new ErrorException("Empty response from Digitab API.");
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

    if ($apiStatusId == 1) {
        $lead_remarks = "Digitap REQUEST API CALL(Success) | Aadhaar : $aadhaar_no";
        $leadModelObj->updateLeadCustomerTable($lead_id, ['customer_ekyc_request_ip' => $_SERVER['REMOTE_ADDR'], 'customer_ekyc_request_initiated_on' => date("Y-m-d H:i:s")]);
    } else {
        $lead_remarks = "Digitap REQUEST API CALL (Failed) | Aadhaar : $aadhaar_no | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ekyc_provider"] = 2;
    $insertApiLog["ekyc_method_id"] = $method_id;
    $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : $lead_id;
    $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no;
    $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
    $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
    $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
    $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ekyc_request_datetime "] = $apiRequestDateTime;
    $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ekyc_user_id"] = $user_id;

    $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);

    if (isset($applicationDetails['lead_status_id']) && !empty($applicationDetails['lead_status_id'])) {

        $insert_digitab_api_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $applicationDetails['status'],
            'stage' => $applicationDetails['stage'],
            'lead_followup_status_id' => $applicationDetails['lead_status_id'],
            'created_on' => date("Y-m-d H:i:s"),
            'remarks' => (!empty($errorMessage) ? "Digitap Ekyc Initiateds failed : " . $errorMessage : "Digitap Ekyc Initiated !")
        ];

        $leadModelObj->insertTable("lead_followup", $insert_digitab_api_followup);
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['message'] = !empty($errorMessage) ? $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;
    return $response_array;
}

function digitap_ekyc_create_otp_api_success($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(8, "digitap_ekyc_create_otp_api_success started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');
    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $otp = "";
    $aadhaar_no_last_4_digit = "";
    $return_dob = "";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['sottest']) ? 1 : 0;
    // $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $type = "DIGITAP_API";
    $sub_type = "DIGITAP_EKYC_SUCCESS";

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
        $apiToken = $apiConfig["ApiToken"];

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiUrl);
            echo "<br/>";
            print_r($apiToken);
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $leadModelObj = new LeadModel();

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] === 1) {
            $app_data = $LeadDetails['app_data'];
        } else {
            throw new Exception("Lead does not exist.");
        }

        $aadhaar_no = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";
        $customer_dob = !empty($app_data['dob']) ? trim($app_data['dob']) : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 7);
        if ($digilockerDetails['status'] == 1) {
            $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";
            $digilocker_log_data = !empty($digilocker_log_data['ekyc_response']) ? $digilocker_log_data['ekyc_response'] : "";

            $apiResponseData = json_decode($digilocker_log_data, true);
            $apiResponseData = common_trim_data_array($apiResponseData);

            if ($debug) {
                echo "<pre>";
                print_r($apiResponseData);
            }

            $apiResponseData = $apiResponseData['model'];
            $aadhaar_no_last_4_digit = substr($apiResponseData["maskedAdharNumber"], 8, 4);
            $return_dob = date("Y-m-d", strtotime($apiResponseData['dob']));

            throw new Error("E-KYC already done.");
        }

        $digilockerDetails = $leadModelObj->getDigilockerApiLog($lead_id, 6);
        if ($digilockerDetails['status'] != 1) {
            throw new Exception("Digilocker Request details not found");
        }

        $digilocker_log_data = !empty($digilockerDetails['digilocker_log_data']) ? $digilockerDetails['digilocker_log_data'] : "";

        $digitab_response = $digilocker_log_data['ekyc_response'];
        $digitab_decode  = json_decode($digitab_response, true);

        $aadhaar_no_last_4_digit = substr($digilocker_log_data['ekyc_aadhaar_no'], 8, 4);

        if ($aadhaar_no_last_4_digit != $aadhaar_no) {
            throw new Exception("Aadhaar number does not mateched for initiated request." . $aadhaar_no_last_4_digit);
        }

        $digitab_transaction_id = $digitab_decode["model"]["transactionId"];
        $digitab_fwdp = $digitab_decode["model"]["fwdp"];
        $digitab_codeverifier = $digitab_decode["model"]["codeVerifier"];
        $otp = $request_array['otp'];

        $apiRequestData = array(
            "transactionId" => $digitab_transaction_id,
            "fwdp" => $digitab_fwdp,
            "codeVerifier" => $digitab_codeverifier,
            "otp" => $otp,
            "shareCode" => "1234",
            "isSendPdf" => true
        );

        $apiRequestJson = preg_replace("!\s+!", " ", json_encode($apiRequestData));

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }

        $apiHeaderData =  array(
            "Authorization:  " . str_replace("Basic ", "", $apiToken),
            'Content-Type: application/json'
        );

        if ($debug) {
            echo "<br/><br/>=======Header JSON=========<br/><br/>";
            print_r($apiHeaderData);
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
            CURLOPT_HTTPHEADER => $apiHeaderData,
        ));

        $apiResponseJson = curl_exec($curl);
        curl_close($curl);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (isset($apiResponseData['msg']) && ($apiResponseData['code'] == "200")) {
                    $apiStatusId = 1;
                    $apiResponseData = $apiResponseData['model'];
                    $return_dob = date("Y-m-d", strtotime($apiResponseData['dob']));
                } else if (!empty($apiResponseData['msg']) && $apiResponseData["code"] == '400') {
                    throw new Exception($apiResponseData['msg']);
                } else if (!empty($apiResponseData['msg']) && $apiResponseData["code"] == '500') {
                    throw new Exception($apiResponseData['msg']);
                } else {
                    $tmp_error_msg = "Some error occurred. Please try again1.";
                    throw new ErrorException($tmp_error_msg);
                }
            } else {
                throw new ErrorException("Empty response from Digitab API.");
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
    } catch (Error $e) {
        $apiStatusId = 5;
        $errorMessage = $e->getMessage();
    }

    if ($apiStatusId != 5) {
        $insertApiLog = array();
        $insertApiLog["ekyc_provider"] = 2;
        $insertApiLog["ekyc_method_id"] = $method_id;
        $insertApiLog["ekyc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["ekyc_api_status_id"] = $apiStatusId;
        $insertApiLog["ekyc_request"] = addslashes($apiRequestJson);
        $insertApiLog["ekyc_response"] = addslashes($apiResponseJson);
        $insertApiLog["ekyc_aadhaar_no"] = $aadhaar_no;
        $insertApiLog["ekyc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["ekyc_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["ekyc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["ekyc_user_id"] = $user_id;

        $leadModelObj->insertTable("api_ekyc_logs", $insertApiLog);
    }

    if ($apiStatusId == 1 || $apiStatusId == 5) {

        $lead_remarks = "Digilocker API CALL(Success) | Aadhaar : $aadhaar_no_last_4_digit";

        if ($aadhaar_no_last_4_digit == $aadhaar_no && $return_dob == $customer_dob) {

            $apiStatusId = 1;
            $lead_remarks .= "<br/>Result : Aadhaar last four digit and dob match";

            if (!empty($apiResponseData['image'])) {
                if (COMP_DOC_S3_FLAG == true) {
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = $apiResponseData["image"];
                    $upload_file['ext'] = 'jpeg';

                    $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($upload_status['status'] == 1) {
                        $file_write_flag = true;
                        $generated_file_name = $upload_status['file_name'];
                    }
                } else {
                    $generated_file_name = "DIGILOCKER_PHOTO_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";
                    $file_write_flag = file_put_contents(COMP_DOC_PATH . $generated_file_name, file_get_contents($apiResponseData['photo']));
                }

                if ($file_write_flag) {
                    $document_insert_array = array();
                    $document_insert_array["lead_id"] = $lead_id;
                    $document_insert_array["customer_id"] = $customer_id;
                    $document_insert_array["pancard"] = $pancard;
                    $document_insert_array["mobile"] = $mobile;
                    $document_insert_array["file"] = $generated_file_name;
                    $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                    $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                    $document_insert_array["upload_by"] = $user_id;
                    $document_insert_array["docs_master_id"] = 19;
                    $document_insert_array["docs_type"] = "DIGILOCKER";
                    $document_insert_array["sub_docs_type"] = "AADHAAR PHOTO";

                    $leadModelObj->insertTable("docs", $document_insert_array);
                }
            }

            if (!empty($apiResponseData['pdfLink'])) {
                if (COMP_DOC_S3_FLAG == true) {
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = base64_encode(file_get_contents($apiResponseData["pdfLink"]));
                    $upload_file['ext'] = 'pdf';

                    $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($upload_status['status'] == 1) {
                        $file_write_flag_aadhaar = true;
                        $generated_file_name = $upload_status['file_name'];
                    }
                } else {
                    $generated_file_name = "DIGILOCKER_" . $document_array['doctype'] . "_" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".pdf";

                    $file_write_flag_aadhaar = file_put_contents(COMP_DOC_PATH . $generated_file_name, file_get_contents($document_array["file"]['pdf']));
                }

                if ($file_write_flag_aadhaar) {
                    unset($document_insert_array);
                    $document_insert_array = array();
                    $document_insert_array["lead_id"] = $lead_id;
                    $document_insert_array["customer_id"] = $customer_id;
                    $document_insert_array["pancard"] = $pancard;
                    $document_insert_array["mobile"] = $mobile;
                    $document_insert_array["file"] = $generated_file_name;
                    $document_insert_array["ip"] = $_SERVER["REMOTE_ADDR"];
                    $document_insert_array["created_on"] = date("Y-m-d H:i:s");
                    $document_insert_array["upload_by"] = $user_id;

                    $document_insert_array["docs_master_id"] = 20;
                    $document_insert_array["docs_type"] = "DIGILOCKER";
                    $document_insert_array["sub_docs_type"] = "AADHAAR CARD";

                    $leadModelObj->insertTable("docs", $document_insert_array);
                }
            }

            $aadhaar_complete_address = !empty($apiResponseData['address']) ? $apiResponseData['address'] : "";
            $aadhaar_city = !empty($apiResponseData['address']['loc']) ? $apiResponseData['address']['loc'] : $apiResponseData['address']['po'];
            $aadhaar_district = !empty($apiResponseData['address']['dist']) ? $apiResponseData['address']['dist'] : "";
            $aadhaar_addressLine = !empty($apiResponseData['address']['house']) ? $apiResponseData['address']['house'] . " " . $apiResponseData['address']['street'] : "";
            $aadhaar_addressLandmark = !empty($apiResponseData['address']['landmark']) ? $apiResponseData['address']['landmark'] : $aadhaar_district;
            $aadhaar_pincode = !empty($apiResponseData['address']['pc']) ? $apiResponseData['address']['pc'] : "";

            $aadhaar_addressLine = empty($aadhaar_addressLine) ? $aadhaar_city : $aadhaar_addressLine;

            $pincodeDetails = $leadModelObj->getCityStateByPincode($aadhaar_pincode);

            $m_state_id = "";
            $m_state_name = "";
            $m_city_id = "";
            $m_city_name = "";

            if ($pincodeDetails['status'] == 1) {
                $m_state_id = $pincodeDetails["pincode_data"]["m_state_id"];
                $m_state_name = $pincodeDetails["pincode_data"]["m_state_name"];
                $m_city_id = $pincodeDetails["pincode_data"]["m_city_id"];
                $m_city_name = $pincodeDetails["pincode_data"]["m_city_name"];
            }

            $aadhaar_array = [
                'aa_current_house' => $aadhaar_addressLine,
                'aa_current_locality' => $aadhaar_city,
                'aa_current_landmark' => $aadhaar_addressLandmark,
                'aa_current_eaadhaar_address' => $aadhaar_complete_address,
                'aa_current_state' => $m_state_name,
                'aa_current_state_id' => $m_state_id,
                'aa_current_city' => $m_city_name,
                'aa_current_city_id' => $m_city_id,
                'aa_cr_residence_pincode' => $aadhaar_pincode,
                'aa_current_district' => $aadhaar_district,
                'customer_digital_ekyc_flag' => 1,
                'customer_digital_ekyc_done_on' => date("Y-m-d H:i:s")
            ];

            $leadModelObj->updateLeadCustomerTable($lead_id, $aadhaar_array);
        } else if ($aadhaar_no_last_4_digit != $aadhaar_no) {
            $apiStatusId = 7;
            $lead_remarks .= "<br/>Result : Aadhaar number does not matched with input aadhaar.[" . $aadhaar_no_last_4_digit . "]";
        } else if ($return_dob != $customer_dob) {
            $apiStatusId = 6;
            $lead_remarks .= "<br/>Result : Aadhaar dob does not matched with input aadhaar.[" . $return_dob . "]";
        } else {
            $apiStatusId = 8;
            $lead_remarks .= "<br/>Result : Aadhaar number last four digit and dob not matched. [" . $aadhaar_no_last_4_digit . " - " . $return_dob . "]";
        }
    } else {
        $lead_remarks = "Digilocker E-Aadhaar API CALL(Failed) | Aadhaar : $aadhaar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['message'] = $errorMessage;

    return $response_array;
}

?>
