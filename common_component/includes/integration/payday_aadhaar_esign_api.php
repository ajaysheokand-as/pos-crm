
<?php

function aadhaar_esign_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(4, "Aadhaar Esign started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "UPLOAD_ESIGN_FILE" => 1,
        "AADHAAR_ESIGN" => 2,
        "DOWNLOAD_ESIGN_FILE" => 3,
        "UPLOAD_ESIGN_FILE_DIGITAP" => 4,
        "SIGNIN_ESIGN_FILE_DIGITAP" => 5,
        "DOWNLOAD_ESIGN_FILE_DIGITAP" => 6
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = esign_document_upload_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = esign_aadhaar_request_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 3) {
        $responseArray = esign_aadhaar_download_api_call($method_id, $lead_id, $request_array);
    } elseif ($method_id == 4) {
        $responseArray = esign_document_upload_digitap_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 5) {
        $responseArray = call_signin_api($method_id, $lead_id, $request_array);
    } else if ($method_id == 6) {
        $responseArray = esign_aadhaar_download_digitap_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(4, "Aadhaar Esign API end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function esign_document_upload_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(4, "esign_document_upload_api_call started | $lead_id");

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
    $sub_type = "UPLOAD_ESIGN_DOCUMENT";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $aadhar_no_last_4_digit = "";
    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $directURL = "";
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

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $aadhar_no_last_4_digit = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";
        $dob = !empty($app_data['dob']) ? date("Y", strtotime($app_data['dob'])) : "";
        $gender = !empty($app_data['gender']) ? $app_data['gender'] : "MALE";
        $email = !empty($app_data['email']) ? $app_data['email'] : "";

        //        $aadhar_no_last_4_digit = substr($aadhar_no, 8, 4);

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $mobile = !empty($app_data['mobile']) ? trim(strtoupper($app_data['mobile'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        if (empty($aadhar_no_last_4_digit)) {
            throw new Exception("Missing aadhaar number last 4 digit.");
        }

        // if (!empty($user_id)) {
        //     throw new Exception("Only customer are allowed to do eSign.");
        // }

        $camDetails = $leadModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] != 1) {
            throw new Exception("Sanction details not found");
        }

        $cam_data = !empty($camDetails['cam_data']) ? $camDetails['cam_data'] : "";

        if (!empty($cam_data['cam_sanction_letter_esgin_file_name'])) {
            throw new Exception("Sanction Letter already eSigned.");
        }

        if (empty($cam_data['cam_sanction_letter_file_name'])) {
            throw new Exception("Sanction Letter file name does not exist.");
        }


        $eSignRequestDetails = $leadModelObj->getEsignApiLog($lead_id, 2);

        if ($eSignRequestDetails['status'] == 1) {

            $eSignRequestDetails = $eSignRequestDetails['esign_log_data'];

            $tempApiResponseData = json_decode($eSignRequestDetails['esign_response'], true);

            if (!empty($tempApiResponseData)) {

                $tempApiResponseData = common_trim_data_array($tempApiResponseData);

                if (!empty($tempApiResponseData)) {

                    if (isset($tempApiResponseData['result']) && !empty($tempApiResponseData['result'])) {

                        $tempApiResponseData = $tempApiResponseData['result'];

                        if (!empty($tempApiResponseData['url']) && !empty($tempApiResponseData['url'])) {
                            $response_array['status'] = 1;
                            $response_array['nsdl_url'] = $tempApiResponseData['url'];
                            return $response_array;
                        }
                    }
                }
            }
        }

        $cam_esign_count = !empty($cam_data['cam_sanction_letter_esgin_count']) ? $cam_data['cam_sanction_letter_esgin_count'] : 0;

        if ($cam_esign_count > 3) {
            throw new Exception("You have reached to maximum eSign request. Please contact to Sanction Executive.");
        }

        $sanction_letter_file_name = $cam_data['cam_sanction_letter_file_name'];

        //        $sanction_letter_file_path = COMP_DOC_PATH . $sanction_letter_file_name;
        //
        //        if (!file_exists($sanction_letter_file_path)) {
        //            throw new Exception("Sanction Letter PDF file does not found.");
        //        }

        // $token_return_array = signzy_token_api_call(3, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        $base64String_file = COMP_DOC_PATH . $sanction_letter_file_name;

        $base64String = base64_encode(file_get_contents(COMP_DOC_URL . $sanction_letter_file_name));

        // $apiRequestJson = '{
        //                     "base64String":"' . $base64String . '",
        //                     "mimetype":"application/pdf",
        //                     "ttl":"2 day"
        //                 }';

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

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

        // $tmp_apiRequestJson = str_replace($base64String_file, $base64String, $apiRequestJson);

        $apiRequestDateTime = date("Y-m-d H:i:s");

        // $curl = curl_init($apiUrl);
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $tmp_apiRequestJson);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.signzy.app/api/v3/contract/initiate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "pdf": "' . $base64String . '",
            "contractName": "Esign Letter",
            "contractExecuterName": "Signzy",
            "successRedirectUrl": "https://crm.tejasloan.com/sanction-esign-response?lead_id=' . $lead_id . '",
            "failureRedirectUrl": "https://tejasloan.com/",
            "contractTtl": 86400,
            "eSignProvider": "eMudhra",
            "nameMatchThreshold": "0.50",
            "allowSignerGenderMatch": true,
            "allowSignerYOBMatch": true,
            "allowUidLastFourDigitsMatch": true,
            "emudhraCustomization": {
                "logoURL": "",
                "headerColour": "",
                "buttonColour": "",
                "maskedAadhaarField": "0",
                "secondaryButtonColour": "",
                "pageBackgroundColour": "",
                "pageTextColour": "",
                "footerBackgroundColour": "",
                "footerTextColour": "",
                "successTextColour": "",
                "errorTextColour": "",
                "errorBackgroundColour": "",
                "linkTextColour": "",
                "infoIconColour": "",
                "textFieldBorderColour": ""
            },
            "signerdetail": [
                {
                    "signerName": "' . $customer_full_name . '",
                    "signerMobile": "' . $mobile . '",
                    "signerEmail": "' . $email . '",
                    "signerGender": "' . $gender . '",
                    "uidLastFourDigits": "' . $aadhar_no_last_4_digit . '",
                    "signerYearOfBirth": "' . $dob . '",
                    "signatureType": "AADHAARESIGN-OTP",

                    "signatures": [
                        {
                            "pageNo": [
                                "All"
                            ],
                            "signaturePosition": [
                                "BottomLeft"
                            ]
                        }
                    ]
                }
            ],
            "workflow": true,
            "isParallel": false,
            "redirectTime": 5,
            "locationCaptureMethod": "ip",
            "initiationEmailSubject": "Please sign the document received on your email",
            "customerMailList": [
                "' . $email . '",
                "info@tejasloan.com"
            ],

            "emailPdfCustomNameFormat": "SIGNERNAME"
        }',
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                // 'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Authorization: 20YobI629zAm00OqQL3CVsbsJCYDAJu1',
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

                    if (!empty($apiResponseData['customerId'])) {

                        if (!empty($apiResponseData['signerdetail'][0]['workflowUrl'])) {
                            $apiStatusId = 1;
                            $directURL = $apiResponseData['signerdetail'][0]['workflowUrl'];
                        } else {
                            throw new ErrorException("Uploaded document details does not received from API.");
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
        $lead_remarks = "eSign Doc API CALL(Success) | Aadhaar : $aadhar_no_last_4_digit";
    } else {
        $lead_remarks = "eSign Doc API CALL (Failed) | Aadhaar : $aadhar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["esign_provider"] = 1;
    $insertApiLog["esign_method_id"] = 1;
    $insertApiLog["esign_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["esign_api_status_id"] = $apiStatusId;
    $insertApiLog["esign_request"] = addslashes($apiRequestJson);
    $insertApiLog["esign_response"] = addslashes($apiResponseJson);
    $insertApiLog["esign_aadhaar_no"] = $aadhar_no_last_4_digit;
    $insertApiLog["esign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["esign_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["esign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["esign_user_id"] = $user_id;
    $insertApiLog["esign_return_url"] = $directURL;

    $leadModelObj->insertTable("api_esign_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "eSign Doc Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;
    $response_array['nsdl_url'] = $directURL;

    // if ($apiStatusId == 1) {
    //     $response_array = esign_aadhaar_request_api_call(2, $lead_id, $request_array);
    // }

    return $response_array;
}

function esign_aadhaar_request_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(4, "esign_aadhaar_request_api_call started | $lead_id");

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
    $sub_type = "AADHAAR_ESIGN";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";

    $lw_redirect_url = COMP_CRM_URL . 'sanction-esign-response?refstr=' . $request_array['lead_id'];
    $lw_callback_url = "";

    $aadhaar_nsdl_url = "";
    $aadhar_no_last_4_digit = "";

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

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        $customer_full_name = str_replace("\'", ' ', $customer_full_name);
        $customer_full_name = str_replace('\"', ' ', $customer_full_name);

        $camDetails = $leadModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] != 1) {
            throw new Exception("Sanction details not found");
        }

        $cam_data = !empty($camDetails['cam_data']) ? $camDetails['cam_data'] : "";

        $cam_esign_count = !empty($cam_data['cam_sanction_letter_esgin_count']) ? $cam_data['cam_sanction_letter_esgin_count'] : 0;

        if ($cam_esign_count > 3) {
            throw new Exception("You have reached to maximum eSign request. Please contact to Sanction Executive.");
        }

        // $token_return_array = signzy_token_api_call(3, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        // $apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);

        $eSignDetails = $leadModelObj->getEsignApiLog($lead_id, 1);

        if ($eSignDetails['status'] != 1) {
            throw new Exception("Document Upload API details not found");
        }


        $esign_log_data = !empty($eSignDetails['esign_log_data']) ? $eSignDetails['esign_log_data'] : "";

        $directURL = $esign_log_data['esign_return_url'];
        $aadhar_no_last_4_digit = $esign_log_data['esign_aadhaar_no'];

        if (empty($directURL)) {
            throw new Exception("eSign Document Upload URL details not found.");
        }


        // $apiRequestJson = '{
        //                     "task": "url",
        //                     "dob": "' . $customer_dob . '",
        //                     "callbackUrl": "' . $lw_callback_url . '",
        //                     "redirectUrl": "' . $lw_redirect_url . '",
        //                     "eventCallbackUrl": "",
        //                     "eventCallbackHeaders": "",
        //                     "redirectTime": "2",
        //                     "inputFile": "' . $directURL . '",
        //                     "name": "' . $customer_full_name . '",
        //                     "multiPages": "true",
        //                     "signaturePosition": "BOTTOM-RIGHT",
        //                     "pageNo": "1",
        //                     "signatureType": "aadhaaresign",
        //                     "xCoordinate": "10",
        //                     "yCoordinate": "10",
        //                     "height": "250",
        //                     "width": "150",
        //                     "esignTtl": "",
        //                     "logoUrl": "https://www.loan24seven.com/public/images/loanwalle-logo.gif"
        //                 }';

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

                        if (!empty($apiResponseData['url']) && !empty($apiResponseData['url'])) {
                            $apiStatusId = 1;
                            $aadhaar_nsdl_url = $apiResponseData['url'];
                        } else {
                            throw new ErrorException("NSDL URL does not received from API.");
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
        $lead_remarks = "E-Sign Request API CALL(Success) | Aadhaar : $aadhar_no_last_4_digit";
        $leadModelObj->updateCAMTable($lead_id, ['cam_sanction_letter_esgin_type_id' => 1, 'cam_sanction_letter_ip_address' => $_SERVER['REMOTE_ADDR'], 'cam_sanction_letter_esgin_count' => ($cam_esign_count + 1)]);
    } else {
        $lead_remarks = "E-Sign Request API (Failed) | Aadhaar : $aadhar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["esign_provider"] = 1;
    $insertApiLog["esign_method_id"] = 2;
    $insertApiLog["esign_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["esign_api_status_id"] = $apiStatusId;
    $insertApiLog["esign_request"] = addslashes($apiRequestJson);
    $insertApiLog["esign_response"] = addslashes($apiResponseJson);
    $insertApiLog["esign_aadhaar_no"] = $aadhar_no_last_4_digit;
    $insertApiLog["esign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["esign_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["esign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["esign_user_id"] = $user_id;
    $insertApiLog["esign_return_url"] = $aadhaar_nsdl_url;

    $leadModelObj->insertTable("api_esign_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['nsdl_url'] = $aadhaar_nsdl_url;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "eSign Request Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function esign_aadhaar_download_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(4, "esign_aadhaar_download_api_call started | $lead_id" . " | " . $_SERVER['REQUEST_URI']);

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $apiCustomerData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "DOWNLOAD_ESIGN_DOCUMENT";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $token_string = "";

    $customer_dob_year = 0;
    $aadhaar_esign_document_url = "";
    $aadhar_no_last_4_digit = "";
    $sanction_letter_file_name = "";

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
        $token_string = $apiConfig["ApiKey"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";
        $customer_dob_year = !empty($app_data['dob']) ? date("Y", strtotime($app_data['dob'])) : "";

        $camDetails = $leadModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] != 1) {
            throw new Exception("Sanction details not found");
        }

        $cam_data = !empty($camDetails['cam_data']) ? $camDetails['cam_data'] : "";

        if (!empty($cam_data['cam_sanction_letter_esgin_file_name'])) {
            throw new Exception("eSign already done.");
        }

        $sanction_letter_file_name = $cam_data['cam_sanction_letter_file_name'];

        // $token_return_array = signzy_token_api_call(3, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }



        $eSignDetails = $leadModelObj->getEsignApiLog($lead_id, 1);

        if ($eSignDetails['status'] != 1) {
            throw new Exception("eSign Request API details not found");
        }

        $esign_log_data = !empty($eSignDetails['esign_log_data']) ? $eSignDetails['esign_log_data'] : "";

        $eSignRequestResponse = json_decode($esign_log_data["esign_response"], true);
        // $eSignRequestResponseToken = $eSignRequestResponse['result']['token'];

        $aadhar_no_last_4_digit = $esign_log_data['esign_aadhaar_no'];
        $esign_contract_id = $eSignRequestResponse['contractId'];
        // if (empty($eSignRequestResponseToken)) {
        //     throw new Exception("eSign document download token does not found.");
        // }

        if ($debug) {
            echo "<br/><br/>=======aadhaar no=========<br/><br/>";
            echo $aadhar_no_last_4_digit;
        }

        if (empty($esign_contract_id)) {
            throw new Exception("eSign document download contract id does not found.");
        }

        // $apiRequestJson = '{
        //                     "customerId": "' . $token_return_user_id . '",
        //                     "token": "' . $eSignRequestResponseToken . '"
        //                 }';

        if (!empty($request_array['contractId'])) {
            $esign_contract_id = $request_array['contractId'];
        }

        $apiRequestJson = '{
                            "contractId": "' . $esign_contract_id . '"
                        }';

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }


        $apiHeaders = array(
            "content-type: application/json",
            "accept-language: en-US,en;q=0.8",
            "accept: */*",
            "Authorization: $token_string"
        );

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

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

                    $apiCustomerData = !empty($apiResponseData) ? $apiResponseData : "";

                    if (!empty($apiCustomerData['isCompleted']) && $apiCustomerData['isCompleted'] == true) {

                        if (!empty($apiResponseData['finalSignedContract'])) {
                            $apiStatusId = 1;
                            $aadhaar_esign_document_url = $apiResponseData['finalSignedContract'];
                            $auditCertificateUrl_url = $apiResponseData['auditCertificateUrl'];
                        } else {
                            throw new ErrorException("eSigned file does not received from API.");
                        }
                    } else if (isset($apiResponseData['message']) && !empty($apiResponseData['message'])) {
                        throw new ErrorException($apiResponseData['message']);
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
        $apiCustomerData = $apiCustomerData['signerdetail'][0];
        $lead_remarks = "E-Sign Download API CALL(Success) | Aadhaar : $aadhar_no_last_4_digit";

        if (trim($apiCustomerData['uidLastFourDigits']) == $aadhar_no_last_4_digit && trim($apiCustomerData['signerYearOfBirth']) == $customer_dob_year) {

            $lead_remarks .= "<br/>Result : Aadhaar last four digit and dob year match";

            if (!empty($aadhaar_esign_document_url)) {
                $apiStatusId = 1;

                // $leadModelObj->updateCAMTable($lead_id, ['cam_sanction_letter_esgin_file_name' => $eSignFileName, 'cam_sanction_letter_esgin_on' => $apiResponseDateTime]);

                if (COMP_DOC_S3_FLAG == true) { // S3 Upload eSign Document
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = base64_encode(file_get_contents($aadhaar_esign_document_url));
                    $upload_file['ext'] = 'pdf';

                    $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($upload_status['status'] == 1) {
                        $file_write_flag = true;
                        $eSignFileName = $upload_status['file_name'];
                    }
                } else {
                    $eSignFileName = "eSign_Sanction_letter" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";
                    $file_write_flag = file_put_contents(COMP_DOC_PATH . $eSignFileName, file_get_contents($aadhaar_esign_document_url));
                }

                if (COMP_DOC_S3_FLAG == true) { // Upload to S3 eSignAuditFileName
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = base64_encode(file_get_contents($auditCertificateUrl_url));
                    $upload_file['ext'] = 'pdf';

                    $audit_upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($audit_upload_status['status'] == 1) {
                        $file_write_flag = true;
                        $eSignAuditFileName = $audit_upload_status['file_name'];
                    }
                } else {
                    $eSignAuditFileName = "eSign_Sanction_letter" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";
                    $file_write_flag = file_put_contents(COMP_DOC_PATH . $eSignAuditFileName, file_get_contents($auditCertificateUrl_url));
                }

                if ($file_write_flag) {
                    $updateCam = array();
                    $updateCam['cam_sanction_letter_esgin_file_name'] = $eSignFileName;
                    $updateCam['cam_esgin_audit_trail_file_name'] = $eSignAuditFileName;
                    $updateCam['cam_sanction_letter_esgin_on'] = $apiResponseDateTime;
                    $updateCam['cam_sanction_letter_ip_address'] = $_SERVER['REMOTE_ADDR'];
                    $updateCam['updated_at'] = date("Y-m-d H:i:s");

                    $leadModelObj->updateCAMTable($lead_id, $updateCam);
                    $apiStatusId = 5;
                    $lead_remarks .= "<br/>eSign File Status : success";
                }
            } else {
                $apiStatusId = 6;
                $errorMessage = "eSign file does not generated.";
                $lead_remarks .= "<br/>eSign File Status : failed";
                $lead_remarks .= "<br/>" . $errorMessage;
            }
        } else if (trim($apiCustomerData['uidLastFourDigits']) != $aadhar_no_last_4_digit) {
            $apiStatusId = 7;
            $errorMessage = "Aadhaar last 4 digit does not matched with customer given aadhaar.[" . $apiCustomerData['uidLastFourDigits'] . "]";
            $lead_remarks .= "<br/>eSign File Status : failed";
            $lead_remarks .= "<br/>" . $errorMessage;
        } else if (trim($apiCustomerData['signerYearOfBirth']) != $customer_dob_year) {
            $apiStatusId = 8;
            $errorMessage = "Aadhaar DOB year does not matched with customer given DOB.[" . $apiCustomerData['signerYearOfBirth'] . "]";
            $lead_remarks .= "<br/>eSign File Status : failed";
            $lead_remarks .= "<br/>" . $errorMessage;
        }
    } else {
        $lead_remarks = "E-Sign Download API (Failed) | Aadhaar : $aadhar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["esign_provider"] = 1;
    $insertApiLog["esign_method_id"] = 3;
    $insertApiLog["esign_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["esign_api_status_id"] = $apiStatusId;
    $insertApiLog["esign_request"] = addslashes($apiRequestJson);
    $insertApiLog["esign_response"] = addslashes($apiResponseJson);
    $insertApiLog["esign_aadhaar_no"] = $aadhar_no_last_4_digit;
    $insertApiLog["esign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["esign_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["esign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["esign_user_id"] = $user_id;
    $insertApiLog["esign_return_url"] = $aadhaar_esign_document_url;

    $leadModelObj->insertTable("api_esign_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['esigned_file_url'] = $aadhaar_esign_document_url;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "eSign Request Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function esign_document_upload_digitap_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(4, "esign_document_upload_digitap_api_call started | $lead_id");

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
    $sub_type = "UPLOAD_ESIGN_DOCUMENT_DIGITAP";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $aadhar_no_last_4_digit = "";
    $lead_status_id = 0;

    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";
    $redirect_url = COMP_CRM_URL . 'sanction-esign-response';


    // $esignUrl = "https://sdk.digitap.ai/e-sign/templateesignprocess.html?docId=return_docId&redirect_url=" . $redirect_url;
    $esignUrl = "https://sdk.digitap.ai/clickwrap/clickwrap.html?txnid=entTransactionId";
    // $esignUrl = "https://api.digitap.ai/clickwrap/v1/send/sign-in-link";
    // $esignUrl = COMP_CRM_URL."sign-in-link/$lead_id";

    $uploadURL = "";
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

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        // print_r($app_data);
        // exit;

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $aadhar_no_last_4_digit = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";
        $email = !empty($app_data['email']) ? $app_data['email'] : "";

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $mobile = !empty($app_data['mobile']) ? trim(strtoupper($app_data['mobile'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";
        $location = !empty($app_data['aa_current_locality']) ? $app_data['aa_current_locality'] : "";

        if (empty($aadhar_no_last_4_digit)) {
            throw new Exception("Missing aadhaar number last 4 digit.");
        }

        $camDetails = $leadModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] != 1) {
            throw new Exception("Sanction details not found");
        }

        $cam_data = !empty($camDetails['cam_data']) ? $camDetails['cam_data'] : "";
        $cam_sanction_letter_file_name = !empty($cam_data['cam_sanction_letter_file_name']) ? $cam_data['cam_sanction_letter_file_name'] : "";
        if (!empty($cam_data['cam_sanction_letter_esgin_file_name'])) {
            throw new Exception("Sanction Letter already eSigned.");
        }

        if (empty($cam_data['cam_sanction_letter_file_name'])) {
            throw new Exception("Sanction Letter file name does not exist.");
        }

        $cam_esign_count = !empty($cam_data['cam_sanction_letter_esgin_count']) ? $cam_data['cam_sanction_letter_esgin_count'] : 0;

        if ($cam_esign_count > 3) {
            throw new Exception("You have reached to maximum eSign request. Please contact to Sanction Executive.");
        }

        $sanction_letter_file_name = $cam_data['cam_sanction_letter_file_name'];

        $fileUrl = COMP_DOC_URL . $sanction_letter_file_name;

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }
        
        $curl = curl_init();
        // $requestData = array(
        //     "uniqueId" => "sanction_letter_" . $lead_id . "_" . time(),
        //     "signers" => array(
        //         array(
        //             "email" => $email,
        //             "location" => $location,
        //             "mobile" => $mobile,
        //             "name" => $customer_full_name
        //         )
        //     ),
        //     "reason" => "Loan agreement",
        //     "templateId" => "ESIG35625073",
        //     "fileName" => $cam_sanction_letter_file_name,
        //     "multiSignerDocId" => $aadhar_no_last_4_digit
        // );
        $requestData = array(
            "docClassId" => "EI358OTPESIG29101",//"sanction_letter_" . $lead_id . "_" . time(),// "EI358OTPESIG58568",
            "reason" => "Loan agreement",
            "signersInfo" => array(
                array(
                    "email" => $email,
                    "location" => $location,
                    "mobile" => $mobile,
                    // "name" => $customer_full_name,
                    "fname" => $first_name,
                    "lname" => $sur_name,
                    "signerType" => "signer1"
                )
            ),
        );

        $apiRequestJson = json_encode($requestData);
        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);
        // print_r($apiRequestJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Request JSON======<br/><br/>" . $apiRequestJson;
        }

        $apiHeaders = array(
            // 'authorization: ' . ltrim($apiToken, 'Basic '),
            'ent_authorization: ' . $apiToken,
            'Content-Type: application/json'
        );

        // print_r($apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $apiHeaders,
        ));
        $apiResponseJson = curl_exec($curl);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }
        // print_r($apiResponseJson);
        // exit;

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

                    if (!empty($apiResponseData)) {
                        

                        // if (!empty($apiResponseData['model']['url']) && !empty($apiResponseData['model']['docId']) && $apiResponseData['code'] == "200") {
                        //     $uploadURL = $apiResponseData['model']['url'];
                        //     $docId = $apiResponseData['model']['docId'];
                        //     $response_upload = esign_upload_sanction_letter_digitap_api_call($fileUrl, $uploadURL);

                        //     if ($response_upload['status'] == 1) {
                        //         $apiStatusId = 1;
                        //         $esignUrl = str_replace("return_docId", $docId, $esignUrl);
                        //     } else {
                        //         throw new ErrorException("Uploaded document details does not received from API[2].");
                        //     }
                        if (!empty($apiResponseData['model']['uploadUrl']) && !empty($apiResponseData['model']['docTransactionId']) && !empty($apiResponseData['model']['entTransactionId']) && $apiResponseData['code'] == "200") {
                            $uploadURL = $apiResponseData['model']['uploadUrl'];
                            $docId = $apiResponseData['model']['docTransactionId'];
                            $entTransactionId = $apiResponseData['model']['entTransactionId'];
                            $response_upload = esign_upload_sanction_letter_digitap_api_call($fileUrl, $uploadURL);
    
                            if ($response_upload['status'] == 1) {
                                $apiStatusId = 1;
                                // $esignUrl = str_replace("return_docId", $docId, $esignUrl);
                                // $esignUrl = str_replace("entTransactionId", $docId, $esignUrl);
                                $esignUrl = str_replace("entTransactionId", $entTransactionId, $esignUrl);

                            } else {
                                throw new ErrorException("Uploaded document details does not received from API[2].");
                            }
                        } else {
                            throw new ErrorException("Uploaded document details does not received from API[1].");
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
        $lead_remarks = "DigiTap eSign Doc API CALL(Success) | Aadhaar : $aadhar_no_last_4_digit";
    } else {
        $lead_remarks = "DigiTap eSign Doc API CALL (Failed) | Aadhaar : $aadhar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["esign_provider"] = 2;
    $insertApiLog["esign_method_id"] = 1;
    $insertApiLog["esign_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["esign_aadhaar_no"] = $aadhar_no_last_4_digit;
    $insertApiLog["esign_request"] = addslashes($apiRequestJson);
    $insertApiLog["esign_response"] = addslashes($apiResponseJson);
    $insertApiLog["esign_api_status_id"] = $apiStatusId;
    $insertApiLog["esign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["esign_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["esign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["esign_user_id"] = $user_id;
    $insertApiLog["esign_return_url"] = $docId;

    $leadModelObj->insertTable("api_esign_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "eSign Doc Error : " . $errorMessage : "";
    $response_array['request_json'] = $requestData;
    $response_array['response_json'] = $apiResponseJson;
    $response_array['nsdl_url'] = $esignUrl;

    return $response_array;
}

function esign_upload_sanction_letter_digitap_api_call($fileUrl, $apiUrl) {

    $debug = 0;
    $response_array = array("status" => 0, "errors" => "");

    try {

        if (empty($fileUrl)) {
            throw new Exception("File URL does not exist.");
        }

        if ($debug == 1) {
            echo "<br/><br/> =======API URL======<br/><br/>" . $apiUrl;
        }

        if (empty($apiUrl)) {
            throw new Exception("API URL does not exist.");
        }
        
        if ($debug == 1) {
            echo "<br/><br/> =======File URL======<br/><br/>" . $fileUrl;
        }

        $fileContent = file_get_contents($fileUrl);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $array(''=> new CURLFILE($fileContent))
            // CURLOPT_HTTPHEADER => array(
            //     'Content-Type: application/pdf', // Ensure correct MIME type
            //     'Content-Length: ' . strlen($fileContent),
            // ),
        ));

        $apiResponseJson = curl_exec($curl);


        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $httpCode;
        }

        if (curl_errno($curl)) {
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($httpCode) && $httpCode == 200) {
                $apiStatusId = 1;
            } else {
                throw new ErrorException("Please check raw response for error details");
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

    $response_array['status'] = $apiStatusId;
    $response_array['errors'] = !empty($errorMessage) ? "Upload error : " . $errorMessage : "";

    return $response_array;
}

function call_signin_api($method_id, $lead_id, $request_array) {
    error_reporting(E_ALL);
               ini_set('display_errors', 1); 
    $response_array = array("status" => 0, "errors" => "");

$lead_id = (int) $lead_id; // Sanitize input to prevent SQL injection
$leadModelObj = new LeadModel();
$esignLogResult = $leadModelObj->getesignLogResult($lead_id);
$esignLogRow = $esignLogResult['items'][0];
$esign_response = json_decode($esignLogRow['esign_response'], true);
$esign_response_model = $esign_response['model'];
$docTransactionId = $esign_response_model['docTransactionId'];
// $esignLogRow[];

$apiResponseData = array(
    "docTransactionId" => $docTransactionId,
    "sendNotification" => true
);

$apiRequestJson = json_encode($apiResponseData);
$apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

// if ($debug == 1) {
//     echo "<br/><br/> =======Request JSON======<br/><br/>" . $apiRequestJson;
// }

$apiHeaders = array(
    // 'ent_authorization: ' . ltrim($apiToken, 'Basic '),
    'ent_authorization: MTE2MzU3MjY6eFh2OTZlNGdoOW9OdHlxbXRLcmw1NFdTalFXOHVjQkQ=',
    // 'authorization: ' . ltrim($apiToken, 'Basic '),
    'Content-Type: application/json'
);

// if ($debug) {
//     echo "<br/><br/>=======Request Header=========<br/><br/>";
//     echo json_encode($apiHeaders);
// }

$apiUrl = "https://api.digitap.ai/clickwrap/v1/send/sign-in-link";

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

curl_close($curl);

// if ($debug == 1) {
//     echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
// }

// print_r($apiResponseJson);
// exit;

$apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
$apiResponseDateTime = date("Y-m-d H:i:s");
// if (isset($curl)) {
//     curl_close($curl);
// }

$apiResponseData = json_decode($apiResponseJson, true);

if (!empty($apiResponseData)) {

    $apiResponseData = common_trim_data_array($apiResponseData);

    if (!empty($apiResponseData)) {

        if ($apiResponseData['code'] == "200") {
            $apiStatusId = 1;
            // echo $apiResponseData["model"]["msg"];
            // $this->load->library('encrypt');
            // echo $enc_lead_id = $this->encrypt->encode($lead_id);
            // exit;

// echo '
// <script>
//     setTimeout(function() {
//         window.location.href = "' . COMP_CRM_URL . 'sanction-esign-response?lead_id=' . $lead_id . '";
//     }, 100);
// </script>';
            // redirect('ApiCallBackController/eSignSanctionLetterResponse');
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

function esign_aadhaar_download_digitap_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(4, "esign_aadhaar_download_digitap_api_call started | $lead_id" . " | " . $_SERVER['REQUEST_URI']);

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $apiCustomerData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "DIGITAP_API";
    $sub_type = "DOWNLOAD_ESIGN_DOCUMENT_DIGITAP";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $lead_status_id = 0;

    $apiToken = "";

    $customer_dob_year = 0;
    $aadhaar_esign_document_url = "";
    $aadhar_no_last_4_digit = "";

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

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";
        $customer_dob_year = !empty($app_data['dob']) ? date("Y", strtotime($app_data['dob'])) : "";
        $aadhar_no_last_4_digit = !empty($app_data['aadhar_no']) ? trim($app_data['aadhar_no']) : "";

        if (empty($aadhar_no_last_4_digit)) {
            throw new Exception("Missing aadhaar number last 4 digit.");
        }

        $camDetails = $leadModelObj->getCAMDetails($lead_id);

        if ($camDetails['status'] != 1) {
            throw new Exception("Sanction details not found");
        }

        $cam_data = !empty($camDetails['cam_data']) ? $camDetails['cam_data'] : "";

        if (!empty($cam_data['cam_sanction_letter_esgin_file_name'])) {
            throw new Exception("eSign already done.");
        }

        // $sanction_letter_file_name = $cam_data['cam_sanction_letter_file_name'];

        $eSignDetails = $leadModelObj->getEsignApiLog($lead_id, 1, 2);

        if ($eSignDetails['status'] != 1) {
            throw new Exception("eSign Request API details not found");
        }

        $esign_log_data = !empty($eSignDetails['esign_log_data']) ? $eSignDetails['esign_log_data'] : "";

        $esign_contract_id = $esign_log_data["esign_return_url"];

        if ($esign_contract_id == "") {
            throw new Exception("eSign document download contract id does not found.");
        }

        if ($debug) {
            echo "<br/><br/>=======aadhaar no=========<br/><br/>";
            echo $esign_contract_id;
        }

        // $apiResponseData = array(
        //     "docId" => $esign_contract_id
        // );
        $apiResponseData = array(
            "transactionId" => $esign_contract_id
        );

        $apiRequestJson = json_encode($apiResponseData);
        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Request JSON======<br/><br/>" . $apiRequestJson;
        }

        $apiHeaders = array(
            // 'ent_authorization: ' . ltrim($apiToken, 'Basic '),
            'ent_authorization:'. $apiToken,
            // 'authorization: ' . ltrim($apiToken, 'Basic '),
            'Content-Type: application/json'
        );

        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            echo json_encode($apiHeaders);
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

        curl_close($curl);

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!$hardcode_response && curl_errno($curl)) {
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

                    if ($apiResponseData['code'] == "200" && !empty($apiResponseData['model']['url'])) {
                        $apiStatusId = 1;
                        $aadhaar_esign_document_url = $apiResponseData['model']['url'];
                        $apiCustomerData = $apiResponseData['model']['signers'][0];
                    } else if (isset($apiResponseData['message']) && !empty($apiResponseData['message'])) {
                        throw new ErrorException($apiResponseData['message']);
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

        $lead_remarks = "DigiTap E-Sign Download API CALL(Success) | Aadhaar : $aadhar_no_last_4_digit";

        if (trim(substr($apiCustomerData['aadhaarSuffix'], 8, 4)) == $aadhar_no_last_4_digit && trim($apiCustomerData['dob']) == $customer_dob_year) {

            $lead_remarks .= "<br/>Result : Aadhaar last four digit and dob year match";

            if (!empty($aadhaar_esign_document_url)) {
                $apiStatusId = 1;

                if (COMP_DOC_S3_FLAG == true) { // S3 Upload eSign Document
                    require_once(COMP_PATH . '/CommonComponent.php');

                    $CommonComponent = new CommonComponent();

                    $upload_file = array();
                    $upload_file['flag'] = 1;
                    $upload_file['file'] = base64_encode(file_get_contents($aadhaar_esign_document_url));
                    $upload_file['ext'] = 'pdf';

                    $upload_status = $CommonComponent->upload_document($lead_id, $upload_file);

                    if ($upload_status['status'] == 1) {
                        $file_write_flag = true;
                        $eSignFileName = $upload_status['file_name'];
                    }
                } else {
                    $eSignFileName = "eSign_Sanction_letter" . $lead_id . "_" . date("YmdHis") . "_" . rand(1000, 9999) . ".jpeg";
                    $file_write_flag = file_put_contents(COMP_DOC_URL . $eSignFileName, file_get_contents($aadhaar_esign_document_url));
                }

                if ($file_write_flag) {
                    $updateCam = array();
                    $updateCam['cam_sanction_letter_esgin_file_name'] = $eSignFileName;
                    $updateCam['cam_sanction_letter_esgin_on'] = $apiResponseDateTime;
                    $updateCam['cam_sanction_letter_ip_address'] = $_SERVER['REMOTE_ADDR'];
                    $updateCam['updated_at'] = date("Y-m-d H:i:s");

                    $leadModelObj->updateCAMTable($lead_id, $updateCam);
                    $apiStatusId = 5;
                    $lead_remarks .= "<br/>eSign File Status : success";
                }
            } else {
                $apiStatusId = 6;
                $errorMessage = "eSign file does not generated.";
                $lead_remarks .= "<br/>eSign File Status : failed";
                $lead_remarks .= "<br/>" . $errorMessage;
            }
        } else if (trim(substr($apiCustomerData['aadhaarSuffix'], 8, 4)) != $aadhar_no_last_4_digit) {
            $apiStatusId = 7;
            $errorMessage = "Aadhaar last 4 digit does not matched with customer given aadhaar.[" . $apiCustomerData['aadhaarSuffix'] . "]";
            $lead_remarks .= "<br/>eSign File Status : failed";
            $lead_remarks .= "<br/>" . $errorMessage;
        } else if (trim($apiCustomerData['dob']) != $customer_dob_year) {
            $apiStatusId = 8;
            $errorMessage = "Aadhaar DOB year does not matched with customer given DOB.[" . $apiCustomerData['dob'] . "]";
            $lead_remarks .= "<br/>eSign File Status : failed";
            $lead_remarks .= "<br/>" . $errorMessage;
        }
    } else {
        $lead_remarks = "DigiTap E-Sign Download API (Failed) | Aadhaar : $aadhar_no_last_4_digit | Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["esign_provider"] = 2;
    $insertApiLog["esign_method_id"] = 3;
    $insertApiLog["esign_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["esign_api_status_id"] = $apiStatusId;
    $insertApiLog["esign_request"] = addslashes($apiRequestJson);
    $insertApiLog["esign_response"] = addslashes($apiResponseJson);
    $insertApiLog["esign_aadhaar_no"] = $aadhar_no_last_4_digit;
    $insertApiLog["esign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["esign_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["esign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["esign_user_id"] = $user_id;
    $insertApiLog["esign_return_url"] = $aadhaar_esign_document_url;

    $leadModelObj->insertTable("api_esign_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['esigned_file_url'] = $aadhaar_esign_document_url;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "eSign Request Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}
?>
