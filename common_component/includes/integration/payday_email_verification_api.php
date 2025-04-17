
<?php

function email_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "Email Verification started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
//        "PERSONAL_EMAIL_VALIDATE" => 1,
        "SIGNZY_EMAIL_VALIDATE" => 1,
        "OFFICE_EMAIL_VALIDATE" => 2,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = signzy_email_verification_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = office_email_verification_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(6, "Email Verification end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function office_email_verification_api_call($method_id, $lead_id = 0, $request_array = array()) {
    

    common_log_writer(6, "office_email_verification_api_call started | $lead_id");

    require_once (COMP_PATH . '/includes/integration/integration_config.php');

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
    $sub_type = "OFFICE_EMAIL_VERIFICATION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";

    $email_address = "";
    $email_validate_status = "";
    $alternate_email_verified_status = "";

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
        
        //print_r($LeadDetails);die;
        

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $email_address = !empty($app_data['alternate_email']) ? trim($app_data['alternate_email']) : "";

        $email_address_status = !empty($app_data['alternate_email_verified_status']) ? trim($app_data['alternate_email_verified_status']) : "";

        if (empty($email_address)) {
            throw new Exception("Missing office email address");
        }

        if ($email_address_status == "YES") {
            throw new Exception("Office email already verified.");
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
        //                     "essentials": {
        //                         "emailId":"' . $email_address . '"
        //                     }
        //                   }';

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
            //   CURLOPT_URL => 'https://signzy.tech/api/v2/patrons/661df0abcb57aa00230ea02e/emailverificationsv2',
              CURLOPT_URL => 'https://api.signzy.app/api/v3/email/verificationV2',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
                "emailId": "'.$email_address.'"
            }',
              CURLOPT_HTTPHEADER => array(
                // 'Authorization: ObUb9uyt6VFVli8bFSw1BFiXMifComc43Djj8uflfXUjhfYd3uF9YgSIMQjEgmCo',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
              ),
            ));
            
            $apiResponseJson = curl_exec($curl);
            
            //curl_close($curl);
            //print_R($apiResponseJson);

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

                        if (!empty($apiResponseData['validEmail'])) {

                            $apiStatusId = 1;

                            if ($apiResponseData['validEmail'] == "true") {
                                $alternate_email_verified_status = "YES";
                                $email_validate_status = 1;
                            } else {
                                $email_validate_status = 2;
                                $alternate_email_verified_status = "NO";
                            }
                        } else {
                            throw new ErrorException("Email response does not received from api.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        throw new ErrorException("Some error occurred. Please try again.");
                    }
                } else {
                    throw new ErrorException("Office Email verification : API Response empty.");
                }
            } else {
                throw new ErrorException("Office Email verification : API Response empty..");
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
        $lead_remarks = "Office Email Verification API CALL(Success) <br/> Office Email : $email_address | Result : " . $alternate_email_verified_status;

        if ($email_validate_status == 1) {
            $leadModelObj->updateLeadCustomerTable($lead_id, ['alternate_email_verified_status' => "YES", 'alternate_email_verified_on' => date("Y-m-d H:i:s")]);
        }
    } else {
        $lead_remarks = "Office Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ev_provider_id"] = 2; //SIGNZY
    $insertApiLog["ev_method_id"] = $method_id;
    $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ev_email"] = $email_address;
    $insertApiLog["ev_email_validate_status"] = $email_validate_status;
    $insertApiLog["ev_api_status_id"] = $apiStatusId;
    $insertApiLog["ev_request"] = addslashes($apiRequestJson);
    $insertApiLog["ev_response"] = addslashes($apiResponseJson);
    $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ev_user_id"] = $user_id;

    $leadModelObj->insertTable("api_email_verification_logs", $insertApiLog);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['email'] = $email_address;
    $response_array['email_validate_status'] = $email_validate_status;
    $response_array['errors'] = !empty($errorMessage) ? "Office Email Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function persional_email_verification_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "persional_email_verification_api_call started | $lead_id");

    require_once (COMP_PATH . '/includes/integration/integration_config.php');

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
    $email_type = $request_array['email_type'];

    $type = "SIGNZY_API";
    $sub_type = "PERSONAL_EMAIL_VERIFICATION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";

    $email_address = "";
    $email_validate_status = "";
    $alternate_email_verified_status = "";

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

        if ($email_type == 1) {
            $email_address = !empty($app_data['email']) ? trim($app_data['email']) : "";
        } else if ($email_type == 2) {
            $email_address = !empty($app_data['alternate_email']) ? trim($app_data['alternate_email']) : "";
        }

        if ($email_type == 1) {
            $email_address_status = !empty($app_data['email_verified_status']) ? trim($app_data['email_verified_status']) : "";
        } elseif ($email_type == 2) {
            $email_address_status = !empty($app_data['alternate_email_verified_status']) ? trim($app_data['alternate_email_verified_status']) : "";
        }

        if (empty($email_address)) {
            throw new Exception("Missing email address");
        }

        if ($email_address_status == "YES") {
            throw new Exception("Email already verified.");
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
        //                     "essentials": {
        //                         "emailId":"' . $email_address . '"
        //                     }
        //                   }';

        // $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

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
        
        $curl = curl_init();
            
            curl_setopt_array($curl, array(
            //   CURLOPT_URL => 'https://signzy.tech/api/v2/patrons/661df0abcb57aa00230ea02e/emailverificationsv2',
              CURLOPT_URL => 'https://api.signzy.app/api/v3/email/verificationV2',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
                "emailId": "'.$email_address.'"
            }',
              CURLOPT_HTTPHEADER => array(
                // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                'Content-Type: application/json'
              ),
            ));
            
            $apiResponseJson = curl_exec($curl);
            
        
       // echo $apiResponseJson; die;

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

                        $apiResponseData = $apiResponseData['result']['emailverifyData'];

                        if (!empty($apiResponseData['status'])) {

                            $apiStatusId = 1;

                            if (in_array($apiResponseData['status'], ["valid", "do_not_mail"])) {
                                $alternate_email_verified_status = "YES";
                                $email_validate_status = 1;
                            } else {
                                $email_validate_status = 2;
                                $alternate_email_verified_status = "NO";
                            }
                        } else {
                            throw new ErrorException("Email response does not received from api.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        throw new ErrorException("Some error occurred. Please try again.");
                    }
                } else {
                    throw new ErrorException("Email verification : API Response empty.");
                }
            } else {
                throw new ErrorException("Email verification : API Response empty..");
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
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Success) <br/> Personal Email : $email_address | Result : " . $alternate_email_verified_status;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Success) <br/> Official Email : $email_address | Result : " . $alternate_email_verified_status;
        }

        if ($email_type == 1) {
            if ($email_validate_status == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['email_verified_status' => "YES", 'email_verified_on' => date("Y-m-d H:i:s")]);
            }
        } elseif ($email_type == 2) {
            if ($email_validate_status == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['alternate_email_verified_status' => "YES", 'alternate_email_verified_on' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
        }
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ev_provider_id"] = 2; //SIGNZY
    $insertApiLog["ev_method_id"] = $method_id;
    $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ev_email"] = $email_address;
    $insertApiLog["ev_email_validate_status"] = $email_validate_status;
    $insertApiLog["ev_api_status_id"] = $apiStatusId;
    $insertApiLog["ev_request"] = addslashes($apiRequestJson);
    $insertApiLog["ev_response"] = addslashes($apiResponseJson);
    $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ev_user_id"] = $user_id;

    $leadModelObj->insertTable("api_email_verification_logs", $insertApiLog);
    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['email'] = $email_address;
    $response_array['email_validate_status'] = $email_validate_status;
    $response_array['errors'] = !empty($errorMessage) ? "Personal Email Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function sendgrid_email_validation_api($lead_id = 0, $request_array = array()) {

    $envSet = ENVIRONMENT;

    require_once (COMP_PATH . '/includes/integration/integration_config.php');
    $leadModelObj = new LeadModel();

    $apiStatusId = 0;
    $emailValidateStatus = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $email_validate_status = 0;
    $alternate_email_verified_status = "NO";

    $type = "SENDGRID_API";
    $sub_type = "SENDGRID_EMAIL_VALIDATE";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
//    $debug = 1;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing
    $email_type = !empty($request_array['email_type']) ? $request_array['email_type'] : "";
    $email_type = 2;

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
            throw new Exception("Missing Lead Id.");
        }

        if (empty($email_type)) {
            throw new Exception("Missing Email Type");
        }

        if (!in_array($email_type, array(1, 2))) {
            throw new Exception("Email Type out of range");
        }

        $appDataReturnArr = $leadModelObj->getLeadFullDetails($lead_id);

//            $appDataReturnArr = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        if ($appDataReturnArr['status'] === 1) {

            $applicationDetails = $appDataReturnArr['app_data'];
            $customer_seq_id = $applicationDetails["customer_seq_id"];
            if ($email_type == 1) {
                $input_email = $applicationDetails["email"];
                $input_email_status = trim(strtoupper($applicationDetails["email_verified_status"]));
            } else if ($email_type == 2) {
                $input_email = $applicationDetails["alternate_email"];
                $input_email_status = trim(strtoupper($applicationDetails["alternate_email_verified_status"]));
            }
        } else {
            throw new Exception("Application details does not exist.");
        }

        $lead_status_id = !empty($applicationDetails['lead_status_id']) ? $applicationDetails['lead_status_id'] : "";

        if ($email_type == 1 && empty($input_email)) {
            throw new Exception("Personal email does not exist.");
        } else if ($email_type == 2 && empty($input_email)) {
            throw new Exception("Office email does not exist.");
        } else if ($email_type == 1 && $input_email_status == "YES") {
            throw new Exception("Personal email already verified.");
        } else if ($email_type == 2 && $input_email_status == "YES") {
            throw new Exception("Office email already verified.");
        }

        $input_request_array = array("email" => $input_email);

        $apiRequestJson = json_encode($input_request_array);
        

        // $apiToken = "Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm";
        $apiToken = "Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey";
        $apiHeaders = array('Accept: application/json', 'Content-Type: application/json', $apiToken
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
        }

        if ($hardcode_response && $envSet == 'development') {
//                $apiResponseJson = '{"address":"info@tejasloan.com","is_disposable_address":false,"is_role_address":false,"reason":[],"result":"deliverable","risk":"low"}';
        } else {

            // $apiUrl = 'https://signzy.tech/api/v2/patrons/661df0abcb57aa00230ea02e/emailverificationsv2';
            $apiUrl = 'https://api.signzy.app/api/v3/email/verificationV2';
            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $apiResponseJson = curl_exec($curl);
           
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");
        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
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

                $apiResponseData = trim_data_array($apiResponseData);

                if (!empty($apiResponseData['result'])) {
                    $apiStatusId = 1;

                    if (in_array($apiResponseData['result']['verdict'], array("Valid", "Risky"))) {
                        $emailValidateStatus = 1;
                        $alternate_email_verified_status = "YES";
                    } elseif ($apiResponseData['result']['verdict'] == 'Invalid') {
                        $emailValidateStatus = 2;
                        $errorMessage = 'Invalid email id.';
                    } else {
                        $emailValidateStatus = 2;
                        $errorMessage = json_encode($apiResponseData['reason']);
                    }
                } elseif (!empty($apiResponseData['errors'])) {
                    $temp_error = !empty($apiResponseData['errors']['message']) ? $apiResponseData['errors']['message'] : "Some error occurred. Please try again.";
                    throw new ErrorException($temp_error);
                } else {
                    $temp_error = !empty($apiResponseData['message']) ? $apiResponseData['message'] : "Some error occurred. Please try again.";
                    throw new ErrorException($temp_error);
                }
            } else {
                throw new ErrorException("Invalid api response..");
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
    $insertApiLog["ev_user_id"] = $user_id;
    $insertApiLog["ev_provider_id"] = 3;
    $insertApiLog["ev_method_id"] = $email_type;
    $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ev_email"] = $input_email;
    $insertApiLog["ev_api_status_id"] = $apiStatusId;
    $insertApiLog["ev_email_validate_status"] = ($emailValidateStatus == 1) ? 1 : 2;
    $insertApiLog["ev_request"] = addslashes($apiRequestJson);
    $insertApiLog["ev_response"] = addslashes($apiResponseJson);
    $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : $errorMessage;
    $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

    $leadModelObj->insertTable("api_email_verification_logs", $insertApiLog);

    if ($apiStatusId == 1) {
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Success) <br/> Personal Email : $input_email | Result : " . $alternate_email_verified_status;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Success) <br/> Official Email : $input_email | Result : " . $alternate_email_verified_status;
        }

        if ($email_type == 1) {
            if ($emailValidateStatus == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['email_verified_status' => "YES", 'email_verified_on' => date("Y-m-d H:i:s")]);
            }
        } elseif ($email_type == 2) {
            if ($emailValidateStatus == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['alternate_email_verified_status' => "YES", 'alternate_email_verified_on' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
        }
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['email'] = $input_email;
    $returnResponseData['email_validate_status'] = $emailValidateStatus;
    $returnResponseData['email_validate'] = ($emailValidateStatus == 1 ? "YES" : "NO");
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

function signzy_email_verification_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(7, "signzy_email_verification_api_call started | $lead_id");

    require_once (COMP_PATH . '/includes/integration/integration_config.php');

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
    $email_type = $request_array['email_type'];

    $type = "SIGNZY_API";
    $sub_type = "PERSONAL_EMAIL_VERIFICATION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";

    $email_address = "";
    $email_validate_status = "";
    $alternate_email_verified_status = "";

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
        
        // print_r($LeadDetails);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        if ($email_type == 1) {
            $email_address = !empty($app_data['email']) ? trim($app_data['email']) : "";
        } else if ($email_type == 2) {
            $email_address = !empty($app_data['alternate_email']) ? trim($app_data['alternate_email']) : "";
        }

        if ($email_type == 1) {
            $email_address_status = !empty($app_data['email_verified_status']) ? trim($app_data['email_verified_status']) : "";
        } elseif ($email_type == 2) {
            $email_address_status = !empty($app_data['alternate_email_verified_status']) ? trim($app_data['alternate_email_verified_status']) : "";
        }

        if (empty($email_address)) {
            throw new Exception("Missing email address");
        }

        if ($email_address_status == "YES") {
            throw new Exception("Email already verified.");
        }

        // $token_return_array = signzy_token_api_call(1, $lead_id, $request_array);

        // if ($token_return_array['status'] == 1) {
        //     $token_string = $token_return_array['token'];
        //     $token_return_user_id = $token_return_array['token_user_id'];
        // } else {
        //     throw new Exception($token_return_array['errors']);
        // }

        //$apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);

        // $apiRequestJson = '{
        //                     "essentials": {
        //                         "emailId":"' . $email_address . '"
        //                     }
        //                   }';

       // $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

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

        // $apiResponseJson = curl_exec($curl);
        
        $curl = curl_init();
        
              curl_setopt_array($curl, array(
            //   CURLOPT_URL => 'https://signzy.tech/api/v2/patrons/customerid/emailverificationsv2',
              CURLOPT_URL => 'https://api.signzy.app/api/v3/email/verificationV2',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
                "emailId": "'.$email_address.'"
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
            // print_r($apiResponseData);
            // exit; // Exit

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData)) {

                    if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                        // $apiResponseData = $apiResponseData['result']['emailverifyData'];
                        

                        if (!empty($apiResponseData['result']['status'])) {

                            $apiStatusId = 1;

                            // if (in_array($apiResponseData['status'], ["valid", "do_not_mail"])) {
                            if ($apiResponseData['result']['status'] =  "valid") {
                                $alternate_email_verified_status = "YES";
                                $email_validate_status = 1;
                            } else {
                                $email_validate_status = 2;
                                $alternate_email_verified_status = "NO";
                            }
                        } else {
                            throw new ErrorException("Email response does not received from api.");
                        }
                    } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                        throw new ErrorException($apiResponseData['error']['message']);
                    } else {
                        throw new ErrorException("Some error occurred. Please try again.");
                    }
                } else {
                    throw new ErrorException("Email verification : API Response empty.");
                }
            } else {
                throw new ErrorException("Email verification : API Response empty..");
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
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Success) <br/> Personal Email : $email_address | Result : " . $alternate_email_verified_status;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Success) <br/> Official Email : $email_address | Result : " . $alternate_email_verified_status;
        }

        if ($email_type == 1) {
            if ($email_validate_status == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['email_verified_status' => "YES", 'email_verified_on' => date("Y-m-d H:i:s")]);
            }
        } elseif ($email_type == 2) {
            if ($email_validate_status == 1) {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['alternate_email_verified_status' => "YES", 'alternate_email_verified_on' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        if ($email_type == 1) {
            $lead_remarks = "Personal Email Verification API CALL(Failed) <br/> Personal Email : $email_address | Error : " . $errorMessage;
        } elseif ($email_type == 2) {
            $lead_remarks = "Official Email Verification API CALL(Failed) <br/> Office Email : $email_address | Error : " . $errorMessage;
        }
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["ev_provider_id"] = 2; //SIGNZY
    $insertApiLog["ev_method_id"] = $email_type;
    $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["ev_email"] = $email_address;
    $insertApiLog["ev_email_validate_status"] = $email_validate_status;
    $insertApiLog["ev_api_status_id"] = $apiStatusId;
    $insertApiLog["ev_request"] = addslashes($apiRequestJson);
    $insertApiLog["ev_response"] = addslashes($apiResponseJson);
    $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["ev_user_id"] = $user_id;

    $leadModelObj->insertTable("api_email_verification_logs", $insertApiLog);
    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['email'] = $email_address;
    $response_array['email_validate_status'] = $email_validate_status;
    $response_array['errors'] = !empty($errorMessage) ? "Email Error : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

?>
