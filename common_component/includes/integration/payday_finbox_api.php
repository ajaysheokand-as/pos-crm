
<?php

function finbox_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(7, "Finbox started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GENERATE_REPORT" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = get_finbox_data($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(7, "Finbox end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function get_finbox_data($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(7, "finbox_api_call started | $lead_id");

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
    $finbox_customer_id = "";
    $token_string = "";
    $hashing_key = "";
    $predictors_version = "";
    $token_string = "";
    $method_id = 0;

    $type = "FINBOX_API";
    $sub_type = "GENERAL_PREDICTORS";
//    $sub_type = "";

    $hardcode_response = false;

//    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {


        $apiConfig = integration_config($type, $sub_type);

        $token_string = $apiConfig['SERVER_API_KEY'];
        $hashing_key = $apiConfig['SERVER_HASH'];
        $predictors_version = $apiConfig['DC_PREDICTORS_VERSION'];

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($token_string) || empty($hashing_key) || empty($predictors_version)) {
            throw new Exception('Finbox Credentials not found.');
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadDetails($lead_id);
        $FinboxApiLog = $leadModelObj->getFinboxApiLog($lead_id, 2);

        if ($FinboxApiLog['status'] == 1) {
            $method_id = 2;
            throw new Exception('Finbox Data Already Exists.');
        }

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $finbox_customer_id = $app_data['pancard'];
        $loan_amount = ($app_data['loan_amount'] ? $app_data['loan_amount'] : "");

        if (empty($finbox_customer_id) || empty($hashing_key)) {
            throw new Exception("Missing Finbox Customer Id");
        }

        $finbox_salt = create_salt($finbox_customer_id, $hashing_key);

        if (empty($finbox_salt)) {
            throw new Exception('Salt creation Failed.');
        }

        if (empty($loan_amount)) {
            throw new Exception("Loan Amount Missing");
        }

        $apiHeaders = array(
            "content-type: application/json",
            "x-api-key: $token_string"
        );

        $apiRequestJson = '{
                            "customer_id": "' . $finbox_customer_id . '",
                            "version": ' . $predictors_version . ',
                            "salt": "' . $finbox_salt . '",
                            "metadata": {
                                "loan_type": "personal_loan",
                                "loan_amount": ' . $loan_amount . '
                            }}';

        $apiUrl = $apiConfig['ApiUrl'];

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }


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

                    if (isset($apiResponseData) && !empty($apiResponseData) && $apiResponseData['status'] != "error") {

                        $apiStatusId = 1;
                        if ($apiResponseData['status'] == "complete") {
                            $method_id = 2;
                        } elseif ($apiResponseData['status'] == "in_progress") {
                            $method_id = 1;
                        } elseif ($apiResponseData['status'] == "no_data") {

                            $errorMessage = $apiResponseData['message'];
                        } elseif ($apiResponseData['status'] == "not_found") {

                            $errorMessage = $apiResponseData['message'];
                        }
                    } else {
                        throw new ErrorException($apiResponseData['message']);
                    }
                } else {
                    throw new ErrorException("Finbox Device Connect : API Response empty.");
                }
            } else {
                throw new ErrorException("Finbox Device Connect : API Response empty..");
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
    $insertApiLog["finbox_dc_provider_id"] = 1; //Finbox
    $insertApiLog["finbox_dc_method_id"] = $method_id; // Device Connect
    $insertApiLog["finbox_dc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["finbox_dc_api_status_id"] = $apiStatusId;
    $insertApiLog["finbox_dc_request"] = addslashes($apiRequestJson);
    $insertApiLog["finbox_dc_response"] = addslashes($apiResponseJson);
    $insertApiLog["finbox_dc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["finbox_dc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["finbox_dc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["finbox_dc_user_id"] = $user_id;

    $leadModelObj->insertTable("api_finbox_device_connect_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Finbox Device Connect : " . $errorMessage : "";

    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function create_salt($customer_id, $server_hash) {
    $customer_hash = strtoupper(md5($customer_id));
    $intermediate_hash = $customer_hash . "" . $server_hash;
    $salt_encoded = openssl_digest($intermediate_hash, 'sha256', true);
    $salt = base64_encode($salt_encoded);
    return $salt;
}

function call_finbox_bureauconnect_api($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(7, "FinBox Bureau Connect Started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "FINBOX_BUREAU_CONNECT_REPORT" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = get_finbox_bureauconnect_data($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(7, "Finbox Bureauconnect Ended | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function get_finbox_bureauconnect_data($method_id, $lead_id = 0, $request_array = array()) {
    common_log_writer(7, "finbox_bureauconnect_api_call started | $lead_id");

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
    $finbox_customer_id = "";
    $token_string = "";
    $hashing_key = "";
    $predictors_version = "";
    $token_string = "";
    $method_id = 0;

    $type = "FINBOX_BUREAU_CONNECT_API";
//    $sub_type = "GENERAL_PREDICTORS";
    $sub_type = "";

    $hardcode_response = false;

//    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {


        $apiConfig = integration_config($type, $sub_type);

        $token_string = $apiConfig['SERVER_API_KEY'];
        //$hashing_key = $apiConfig['SERVER_HASH'];
        //$predictors_version = $apiConfig['DC_PREDICTORS_VERSION'];
        $source_type = $apiConfig['SOURCE_TYPE'];
        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($token_string) || empty($source_type)) {
            throw new Exception('API Key not found.');
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadDetails($lead_id);
//        $FinboxApiLog = $leadModelObj->getFinboxBureauConnectApiLog($lead_id, 2);
        $CibilDetails = $leadModelObj->getCibilDetails($lead_id);

//        if ($FinboxApiLog['status'] == 1) {
//            $method_id = 2;
//            throw new Exception('Finbox Data Already Exists.');
//        }

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $finbox_customer_id = $app_data['pancard'];

        if (empty($finbox_customer_id)) {
            throw new Exception("Missing Finbox Customer Id");
        }

        $tempApiResponseXml = !empty($CibilDetails['cibil_data']['api1_response']) ? $CibilDetails['cibil_data']['api1_response'] : "";
        //         echo '<pre>';
//        print_r($tempApiResponseXml['api1_response']);
//        die;
//        require_once(COMP_PATH . '/includes/functions.inc.php');

        $report_id = base64_encode(common_extract_value_from_xml('<REPORT-ID>', '</REPORT-ID>', $tempApiResponseXml));

        $created_at = common_extract_value_from_xml('<DATE-OF-ISSUE>', '</DATE-OF-ISSUE>', $tempApiResponseXml);

        $user_id = base64_encode($finbox_customer_id);

        $response = base64_encode($tempApiResponseXml);

//        echo "<pre>";
//        echo $report_id."<br>";
//        echo $created_at."<br>";
//        echo $user_id."<br>";
//        echo $response."<br>";
//        die;
//        echo $token_string;
//        die;

        $apiHeaders = array(
            "content-type: application/json",
            "x-api-key: $token_string"
        );

        $apiRequestJson = '{
                            "source_type": "' . $source_type . '",
                                "data": {
                                            "report_id": "' . $report_id . '",
                                            "user_id": "' . $user_id . '",
                                            "created_at": "' . $created_at . '",
                                            "response": "' . $response . '"
                                        }
                            }';

        $apiUrl = $apiConfig['ApiUrl'];

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }


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

                    if (isset($apiResponseData) && !empty($apiResponseData)) {

                        $apiStatusId = 1;
                        $method_id = 2;
                    } else {
                        throw new ErrorException($apiResponseData['message']);
                    }
                } else {
                    throw new ErrorException("Finbox BureauConnect : API Response empty.");
                }
            } else {
                throw new ErrorException("Finbox BureauConnect : API Response empty..");
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
    $insertApiLog["finbox_br_provider_id"] = 1; //Finbox
    $insertApiLog["finbox_br_method_id"] = $method_id; // Device Connect
    $insertApiLog["finbox_br_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["finbox_br_api_status_id"] = $apiStatusId;
    $insertApiLog["finbox_br_request"] = addslashes($apiRequestJson);
    $insertApiLog["finbox_br_response"] = addslashes($apiResponseJson);
    $insertApiLog["finbox_br_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["finbox_br_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["finbox_br_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["finbox_br_user_id"] = $user_id;

    $leadModelObj->insertTable("api_finbox_bureauconnect_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Finbox BureauConnect : " . $errorMessage : "";

    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function call_finbox_bank_connect_upload_api($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(7, "FinBox Bank Connect Started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "FINBOX_BANK_CONNECT_REPORT" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = get_finbox_bank_connect_upload_data($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(7, "Finbox Bank Connect Ended | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function get_finbox_bank_connect_upload_data($method_id, $lead_id = 0, $request_array = array()) {
    common_log_writer(7, "finbox_bank_connect_api_call started | $lead_id");

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
    $finbox_customer_id = "";
    $token_string = "";
    $hashing_key = "";
    $predictors_version = "";
    $token_string = "";
    $method_id = 0;

    $type = "FINBOX_BANK_CONNECT_API";

    $sub_type = "UPLOAD_API";

    $hardcode_response = false;

    $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $document_path = FCPATH . 'upload/';
    $filename = "";
    $return_document_id = "";

    try {


        $apiConfig = integration_config($type, $sub_type);

        $token_string = $apiConfig['SERVER_API_KEY'];
        $hashing_key = $apiConfig['SERVER_HASH'];
//        if ($debug == 1) {
//            echo "<pre>";
//            print_r($apiConfig);
//        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($token_string)) {
            throw new Exception('API Key not found.');
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $docDataReturnArr = $leadModelObj->getBankStatementDetails($lead_id);

        if ($docDataReturnArr['status'] === 1) {
            $documentDetails = $docDataReturnArr['docs_data'];
            $filename = $documentDetails['file'];
            $return_document_id = $documentDetails['docs_novel_return_id'];
        } else {
            throw new Exception("Bank Statement Document does not exist with the application.");
        }
        $filePath = $document_path . $filename;
        $pdf_password = (($documentDetails['pwd'] != null) ? trim($documentDetails['pwd']) : '');

        $cartFileObject = [
            "file" => new CURLFile($filePath, '', $filename),
            'metadata' => '{"password":"' . $pdf_password . '","bank":"","name":""}'
        ];

        $tempcartFileObject = $cartFileObject;
        $tempcartFileObject['file'] = $filePath;

        $apiRequestJson = json_encode($tempcartFileObject);

        if (empty($cartFileObject)) {
            throw new Exception("Unable to load document.");
        }

        $apiHeaders = [
            'content-type: multipart/form-data',
            'x-api-key: ' . $token_string
        ];

        $apiUrl = $apiConfig['ApiUrl'];

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

//        if ($debug) {
//            echo "<br/><br/>=======Request JSON=========<br/><br/>";
//            echo $apiRequestJson;
//        }
//
//
//        if ($debug) {
//            echo "<br/><br/>=======Request Header=========<br/><br/>";
//            echo json_encode($apiHeaders);
//        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $cartFileObject);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $apiResponseJson = curl_exec($curl);

//        print_r($apiResponseJson);
//        die;
//
//        if ($debug == 1) {
//            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
//        }

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

                    if (isset($apiResponseData) && !empty($apiResponseData)) {

                        $apiStatusId = 1;
                        $method_id = 1;
                    } else {
                        throw new ErrorException($apiResponseData['message']);
                    }
                } else {
                    throw new ErrorException("Finbox Bank Connect : API Response empty.");
                }
            } else {
                throw new ErrorException("Finbox Bank Connect : API Response empty..");
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
    $insertApiLog["finbox_bc_provider_id"] = 1; //Finbox
    $insertApiLog["finbox_bc_method_id"] = $method_id; // Device Connect
    $insertApiLog["finbox_bc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["finbox_bc_api_status_id"] = $apiStatusId;
    $insertApiLog["finbox_bc_request"] = addslashes($apiRequestJson);
    $insertApiLog["finbox_bc_response"] = addslashes($apiResponseJson);
    $insertApiLog["finbox_bc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["finbox_bc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["finbox_bc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["finbox_bc_user_id"] = $user_id;

    $leadModelObj->insertTable("api_finbox_bank_connect_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Finbox Bank Connect : " . $errorMessage : "";

    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function call_finbox_bank_connect_fetch_api($lead_id = 0, $request_array = array()) {
    common_log_writer(7, "FinBox Bank Connect Started | $lead_id");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        2 => "LIST_ACCOUNTS",
        3 => "IDENTITY",
        4 => "TRANSACTIONS",
        5 => "SALARY",
        6 => "RECURRING_TRANSACTIONS",
        7 => "LENDER_TRANSACTIONS",
        8 => "GET_EXPENSE_CATEGORIES",
        9 => "MONTHLY_ANALYSIS"
    //    10 => "XLSX_REPORT"
    );
    for ($i = 2; $i <= (count($opertion_array)+1); $i++) {
        $request_array['method_id']=$i;
        $request_array['entity_id']=$request_array['entity_id'];
        //echo $request_array['method_id']."<br>";
        $method_id = $opertion_array[$i];
        //echo $method_id."<br>";
        if ($request_array['method_id'] > 0) {
            $responseArray = get_finbox_bank_connect_fetch_data($method_id, $lead_id, $request_array);
        } else {
            $responseArray["errors"] = "invalid opertation called";
        }

        common_log_writer(7, "Finbox Bank Connect Ended | $lead_id | " . json_encode($responseArray));

        return $responseArray;
    }
}

function get_finbox_bank_connect_fetch_data($method_id, $lead_id = 0, $request_array = array()) {
    //echo $method_id."<br>";
    common_log_writer(7, "finbox_bank_connect_api_call started | $lead_id");

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
    $finbox_customer_id = "";
    $token_string = "";
    $hashing_key = "";
    $predictors_version = "";
    $token_string = "";
    
    $type = "FINBOX_BANK_CONNECT_API";

    $sub_type = $method_id;

    $hardcode_response = false;

    $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {
       
//echo "<br>".$sub_type." now<br>";
        $apiConfig = integration_config($type, $sub_type);
        //print_r($apiConfig['ApiUrl']);
        $token_string = $apiConfig['SERVER_API_KEY'];
        $hashing_key = $apiConfig['SERVER_HASH'];
        // if ($debug == 1) {
        //     echo "<pre>";
        //     print_r($apiConfig);
        // }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (empty($token_string)) {
            throw new Exception('API Key not found.');
        }

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        

        $apiHeaders = [
            'content-type: application/json',
            'x-api-key: ' . $token_string,
            'server-hash:'. $hashing_key
        ];

        $apiUrl = str_replace("<entity_id>",$request_array['entity_id'],$apiConfig['ApiUrl']);
        //echo $apiUrl."<br>";

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        // if ($debug) {
        //     echo "<br/><br/>=======Request JSON=========<br/><br/>";
        //     echo $apiRequestJson;
        // }


        // if ($debug) {
        //     echo "<br/><br/>=======Request Header=========<br/><br/>";
        //     echo json_encode($apiHeaders);
        // }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, false);
//        curl_setopt($curl, CURLOPT_POSTFIELDS);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $apiResponseJson = curl_exec($curl);

        // print_r($apiResponseJson);
        // die;
 
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

                    if (isset($apiResponseData) && !empty($apiResponseData)) {

                        $apiStatusId = 1;
                        $method_id = $request_array['method_id'];
                    } else {
                        throw new ErrorException($apiResponseData['message']);
                    }
                } else {
                    throw new ErrorException("Finbox Bank Connect : API Response empty.");
                }
            } else {
                throw new ErrorException("Finbox Bank Connect : API Response empty..");
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
    $insertApiLog["finbox_bc_provider_id"] = 1; //Finbox
    $insertApiLog["finbox_bc_method_id"] = $method_id; // Bank Connect
    $insertApiLog["finbox_bc_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["finbox_bc_api_status_id"] = $apiStatusId;
    $insertApiLog["finbox_bc_request"] = addslashes($apiRequestJson);
    $insertApiLog["finbox_bc_response"] = addslashes($apiResponseJson);
    $insertApiLog["finbox_bc_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["finbox_bc_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["finbox_bc_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["finbox_bc_user_id"] = $user_id;

    $leadModelObj->insertTable("api_finbox_bank_connect_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "Finbox Bank Connect : " . $errorMessage : "";

    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    //return $response_array;
}

?>
