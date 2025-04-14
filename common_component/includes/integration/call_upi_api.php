<?php

function call_upi_api($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(3, "UPI QR Code started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GET_QRCODE_REQUEST" => 1,
        "COLLECTPAY_REQUEST" => 2,
        "CHECK_STATUS" => 3
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = qrcode_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = collectpay_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 3) {
        $responseArray = check_status_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }
    common_log_writer(3, "UPI QR Code end | $lead_id | $method_name | " . json_encode($responseArray));
    return $responseArray;
}

function qrcode_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "qrcode_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "UPI_API";
    $sub_type = "QRCODE_REQUEST";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    if(!empty($request_array['user_id'])){
        $user_id = $request_array['user_id'];
    }
    $leadModelObj = new LeadModel();

    $pan_no = "";
    $lead_status_id = 0;
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $apiUrl = "";
    $merchantId = "";
    $terminalId = "";
    $staticVal = "";
    $transaction_id = "";
    $currencyCode = "";

    $requestAmount = $request_array['amount'] ?? 0;

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
        $merchantId = $apiConfig["MerchantId"];
        $terminalId = $apiConfig["TerminalId"];
        $staticVal = $apiConfig["StaticVal"];
        $currencyCode = $apiConfig["CurrencyCode"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        if (!in_array($lead_status_id, array(14, 19))) {
            throw new Exception("This case already closed/settled.");
        }

        $loanDetails = $leadModelObj->getLoanRepaymentDetails($lead_id);

        if ($loanDetails['status'] != 1) {
            throw new Exception("Repayment details not found");
        }

        $repayment_data = !empty($loanDetails['repayment_data']) ? $loanDetails['repayment_data'] : "";

        if ($requestAmount == 0) {
            $requestAmount = !empty($repayment_data['total_due_amount']) ? $repayment_data['total_due_amount'] : "";
        }

        $loan_no = !empty($repayment_data['loan_no']) ? $repayment_data['loan_no'] : "";

        if ($requestAmount == 0) {
            throw new Exception("Missing Outstanding amount.");
        }

        if (!empty($lead_id)) {
            $qrDetails = $leadModelObj->getUPIApiLog($lead_id, $method_id);

            if ($qrDetails['status'] == 1) {
                $tran_log_data = $qrDetails['upi_data'] ?? "";
                $transaction_id = $tran_log_data['au_transaction_id'] ?? "";
                $request_datetime = strtotime($tran_log_data['au_request_datetime'] ?? "");
            }

            if (time() <= strtotime("+5 minutes", $request_datetime)) { // 5 minute check
                $api_call_flag = false;
                $apiResponseJson = $tran_log_data['au_response'];
                $apiResponseData = json_decode($apiResponseJson, true);

                throw new Exception("QR Code already generated for this case.");
            }
        }

        if ($api_call_flag) {

            $transaction_id = $loan_no . '-' . $lead_id . '-' . time();
            $apiRequestJson = [
                "amount" => $requestAmount,
                "merchantId" => $merchantId,
                "terminalId" => $terminalId,
                "merchantTranId" => $transaction_id,
                "billNumber" => $lead_id . '-' . time(),
                "validityStartDateTime" => date("d/m/Y H:i:s", strtotime("+2 seconds")),
                "validityEndDateTime" => date("d/m/Y H:i:s", strtotime("+5 minutes")),
            ];

            $paramsJsonEncode = json_encode($apiRequestJson);

            $fp = fopen(COMP_PATH . '/includes/icici-key/servPub.txt', 'r');
            $pub_key_string = fread($fp, $staticVal);
            fclose($fp);
            openssl_get_publickey($pub_key_string);
            openssl_public_encrypt($paramsJsonEncode, $crypttext, $pub_key_string);
            $encRequestData = json_encode(base64_encode($crypttext));
            $encRequestData = str_replace('"', '', $encRequestData);

            if ($debug == 1) {
                echo "<br/><br/>=======Request String=========<br/><br/>";
                echo $encRequestData;
            }

            $apiHeaders = array(
                'Content-type: text/plain'
            );

            if ($debug == 1) {
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
                CURLOPT_POSTFIELDS => $encRequestData,
                CURLOPT_HTTPHEADER => $apiHeaders,
            ));

            $encResponseJson = curl_exec($curl);
            $apiResponseDateTime = date("Y-m-d H:i:s");

            if ($debug == 1) {
                echo "<br/><br/> =======Response======<br/><br/>" . $encResponseJson . "<br/><br/>";
            }

            curl_close($curl);
            if (!$api_call_flag && curl_errno($curl)) {
                $curlError = curl_error($curl);
                curl_close($curl);
                throw new RuntimeException("Something went wrong. Please try after sometimes.");
            } else {

                if (isset($curl)) {
                    curl_close($curl);
                }

                if (!empty($encResponseJson)) {
                    $fp = fopen(COMP_PATH . '/includes/icici-key/serv.txt', 'r');
                    $priv_key = fread($fp, $staticVal);
                    fclose($fp);

                    $res = openssl_get_privatekey($priv_key, "");
                    openssl_private_decrypt(base64_decode($encResponseJson), $newsource, $res);

                    $apiResponseData   = json_decode($newsource, true);
                    if (!empty($apiResponseData)) {

                        $success = $apiResponseData['success'];
                        $message = $apiResponseData['message'];
                        $response = $apiResponseData['response'];

                        if ($success == true) {

                            $merchantId = $apiResponseData['merchantId'];
                            $terminalId = $apiResponseData['terminalId'];
                            $refId = $apiResponseData['refId'];

                            $upiString = "upi://pay?pa=KASAR3@icici&pn=Kasar Credit&tr=$refId&am=$requestAmount&cu=$currencyCode&mc=" . $terminalId;

                            $apiResponseData['qrCodeUrl'] = "https://quickchart.io/qr?size=300&text=" . urlencode($upiString);

                            $apiStatusId = 1;
                            $apiResponseJson = json_encode($apiResponseData);
                        } else {
                            throw new RuntimeException($message . " | " . $response);
                        }
                    } else {
                        throw new ErrorException("QR Code : API Response empty.");
                    }
                } else {
                    throw new ErrorException("QR Code : API Response empty..");
                }
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
        $lead_remarks = "QRCode API CALL(Success)<br/>Customer Name : " . $customer_full_name . "<br/>Loan No : $loan_no";
        $lead_remarks .= "<br>QRCode FETCH DETAILS";
        $lead_remarks .= "<br>Message : " . $response . "<br/>RefID : " . $refId;
    } else {
        $lead_remarks = "QRCode API CALL(Failed)<br/>Loan No : $loan_no<br/>Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    if ($api_call_flag) {

        $insertApiLog = array();
        $insertApiLog["au_provider"] = 1;
        $insertApiLog["au_method_id"] = $method_id;
        $insertApiLog["au_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["au_transaction_id"] = !empty($transaction_id) ? $transaction_id : NULL;
        $insertApiLog["au_status_id"] = $apiStatusId;
        $insertApiLog["au_request"] = addslashes($paramsJsonEncode);
        $insertApiLog["au_response"] = addslashes($apiResponseJson);
        $insertApiLog["au_encrypt_request"] = addslashes($encRequestData);
        $insertApiLog["au_encrypt_response"] = addslashes($encResponseJson);
        $insertApiLog["au_requested_amount"] = !empty($requestAmount) ? $requestAmount : 0;
        $insertApiLog["au_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["au_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["au_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["au_user_id"] = $user_id;

        $leadModelObj->insertTable("api_upi_logs", $insertApiLog);
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['transaction_id'] = $transaction_id;
    $response_array['amount'] = $requestAmount;
    $response_array['qrCodeUrl'] = $apiResponseData['qrCodeUrl'] ?? "";
    $response_array['errors'] = !empty($errorMessage) ? "QRCode Error : " . $errorMessage : "";

    if ($debug == 1) {
        $response_array['request_json'] = $paramsJsonEncode;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function collectpay_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "collectpay_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "UPI_API";
    $sub_type = "COLLECTPAY_REQUEST";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    $customerVPA = !empty($request_array['customer_vpa']) ? $request_array['customer_vpa'] : "";
    if(!empty($request_array['user_id'])){
        $user_id = $request_array['user_id'];
    }
    $leadModelObj = new LeadModel();

    $pan_no = "";
    $lead_status_id = 0;
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $apiUrl = "";
    $merchantId = "";
    $terminalId = "";
    $staticVal = "";
    $transaction_id = "";

    $requestAmount = $request_array['amount'] ?? 0;

    try {

        if (empty($customerVPA)) {
            throw new Exception("Missing customer upi id.");
        }

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $merchantId = $apiConfig["MerchantId"];
        $subMerchantId = $apiConfig["SubMerchantId"];
        $terminalId = $apiConfig["TerminalId"];
        $staticVal = $apiConfig["StaticVal"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        if (!in_array($lead_status_id, array(14, 19))) {
            throw new Exception("This case already closed/settled.");
        }

        if (!empty($lead_id)) {
            $qrDetails = $leadModelObj->getUPIApiLog($lead_id, $method_id);

            if ($qrDetails['status'] == 1) {
                $tran_log_data = $qrDetails['upi_data'] ?? "";
                $transaction_id = $tran_log_data['au_transaction_id'] ?? "";
                $request_datetime = strtotime($tran_log_data['au_request_datetime'] ?? "");
            }

            if (time() - $request_datetime < 300) { // 5 minutes check
                $api_call_flag = false;
                $apiResponseJson = $tran_log_data['au_response'];
                $apiResponseData = json_decode($apiResponseJson, true);

                throw new Exception("Collect already generated for this case within the last 5 minutes.");
            }
        }

        $loanDetails = $leadModelObj->getLoanRepaymentDetails($lead_id);

        if ($loanDetails['status'] != 1) {
            throw new Exception("Repayment details not found");
        }

        $repayment_data = !empty($loanDetails['repayment_data']) ? $loanDetails['repayment_data'] : "";

        if ($requestAmount == 0) {
            $requestAmount = !empty($repayment_data['total_due_amount']) ? $repayment_data['total_due_amount'] : "";
        }

        $loan_no = !empty($repayment_data['loan_no']) ? $repayment_data['loan_no'] : "";

        if (empty($requestAmount)) {
            throw new Exception("Missing Outstanding amount.");
        }

        if ($api_call_flag) {

            $transaction_id = $loan_no . '-' . $lead_id . '-' . time();
            $apiRequestJson = array(
                "payerVa" => $customerVPA, //Payee VPA
                "amount" => $requestAmount,
                "note" => "collect-pay-request",
                "collectByDate" => date("d/m/Y H:i A", strtotime("+5 minutes")),
                "merchantId" => $merchantId,
                "merchantName" => "Kasar Cradit",
                "subMerchantId" => $subMerchantId,
                "subMerchantName" => "KASAR CREDIT AND CAPITAL PRIVATE LIMITED",
                "terminalId" => $terminalId,
                "merchantTranId" => $transaction_id,
                "billNumber" => $lead_id . '-' . time(),
            );

            $paramsJsonEncode = json_encode($apiRequestJson);

            if ($debug == 1) {
                echo "<br/><br/>=======Request String=========<br/><br/>";
                echo $paramsJsonEncode;
            }

            $fp = fopen(COMP_PATH . '/includes/icici-key/servPub.txt', 'r');
            $pub_key_string = fread($fp, $staticVal);
            fclose($fp);
            openssl_get_publickey($pub_key_string);
            openssl_public_encrypt($paramsJsonEncode, $crypttext, $pub_key_string);
            $encRequestData = json_encode(base64_encode($crypttext));
            $encRequestData = str_replace('"', '', $encRequestData);

            if ($debug == 1) {
                echo "<br/><br/>=======Request String=========<br/><br/>";
                echo $encRequestData;
            }

            $apiHeaders = array(
                'Content-type: text/plain'
            );

            if ($debug == 1) {
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
                CURLOPT_POSTFIELDS => $encRequestData,
                CURLOPT_HTTPHEADER => $apiHeaders,
            ));

            $encResponseJson = curl_exec($curl);
            $apiResponseDateTime = date("Y-m-d H:i:s");

            if ($debug == 1) {
                echo "<br/><br/> =======Response======<br/><br/>" . $encResponseJson;
            }

            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
            if (!$api_call_flag && curl_errno($curl)) {
                $curlError = curl_error($curl);
                curl_close($curl);
                throw new RuntimeException("Something went wrong. Please try after sometimes.");
            } else {

                if (isset($curl)) {
                    curl_close($curl);
                }

                if (!empty($encResponseJson)) {
                    $fp = fopen(COMP_PATH . '/includes/icici-key/serv.txt', 'r');
                    $priv_key = fread($fp, $staticVal);
                    fclose($fp);

                    $res = openssl_get_privatekey($priv_key, "");
                    openssl_private_decrypt(base64_decode($encResponseJson), $newsource, $res);

                    $apiResponseData   = json_decode($newsource, true);
                    if (!empty($apiResponseData)) {

                        $success = $apiResponseData['success']; //True
                        $message = $apiResponseData['message'];
                        $response = $apiResponseData['response'];

                        if ($success == "true") {
                            $requestAmount = $apiResponseData['Amount'];
                            $merchantId = $apiResponseData['merchantId'];
                            $subMerchantId = $apiResponseData['subMerchantId'];
                            $terminalId = $apiResponseData['terminalId'];
                            $bankRRN = $apiResponseData['BankRRN'];

                            $apiStatusId = 1;
                            $apiResponseJson = json_encode($apiResponseData);
                        } else {
                            throw new RuntimeException($message . " | " . $response);
                        }
                    } else {
                        throw new ErrorException("Collectpay : API Response empty.");
                    }
                } else {
                    throw new ErrorException("Collectpay : API Response empty..");
                }
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
        $lead_remarks = "Collect Pay API CALL(Success)<br/>Customer Name : " . $customer_full_name . "<br/>Loan No : $loan_no";
        $lead_remarks .= "<br>Collect Pay DETAILS";
        $lead_remarks .= "<br>Message : " . $response . "<br/>Bank RRN : " . $bankRRN . "<br/>Requested Amount : " . $requestAmount;
    } else {
        $lead_remarks = "Collectpay API CALL(Failed)<br/>Loan No : $loan_no<br/>Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    if ($api_call_flag) {
        $insertApiLog = array();
        $insertApiLog["au_provider"] = 1;
        $insertApiLog["au_method_id"] = $method_id;
        $insertApiLog["au_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["au_transaction_id"] = !empty($transaction_id) ? $transaction_id : NULL;
        $insertApiLog["au_status_id"] = $apiStatusId;
        $insertApiLog["au_request"] = addslashes($paramsJsonEncode);
        $insertApiLog["au_response"] = addslashes($apiResponseJson);
        $insertApiLog["au_encrypt_request"] = addslashes($encRequestData);
        $insertApiLog["au_encrypt_response"] = addslashes($encResponseJson);
        $insertApiLog["au_requested_amount"] = !empty($requestAmount) ? $requestAmount : 0;
        $insertApiLog["au_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["au_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["au_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["au_user_id"] = $user_id;

        $leadModelObj->insertTable("api_upi_logs", $insertApiLog);
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['transaction_id'] = $transaction_id;
    $response_array['amount'] = $requestAmount;
    $response_array['errors'] = !empty($errorMessage) ? "Collectpay Error : " . $errorMessage : "";

    if ($debug == 1) {
        $response_array['request_json'] = $paramsJsonEncode;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}


function check_status_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "check_status_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";

    $type = "UPI_API";
    $sub_type = "CHECK_STATUS";

    $debug = !empty($_REQUEST['test']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    $paymentModeId = !empty($request_array['payment_mode_id']) ? $request_array['payment_mode_id'] : 0;

    $leadModelObj = new LeadModel();

    $pan_no = "";
    $lead_status_id = 0;
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $apiUrl = "";
    $merchantId = "";
    $terminalId = "";
    $staticVal = "";
    $transaction_id = "";

    try {

        if (empty($paymentModeId)) {
            throw new Exception("Missing payment mode id.");
        }

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $merchantId = $apiConfig["MerchantId"];
        $subMerchantId = $apiConfig["SubMerchantId"];
        $terminalId = $apiConfig["TerminalId"];
        $staticVal = $apiConfig["StaticVal"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";
        $pan_no = !empty($app_data['pancard']) ? trim(strtoupper($app_data['pancard'])) : "";

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";
        $qrDetails = $leadModelObj->getUPIApiLog($lead_id, $paymentModeId);

        if ($qrDetails['status'] != 1) {
            throw new Exception("Transaction details not found");
        }

        $tran_log_data = !empty($qrDetails['upi_data']) ? $qrDetails['upi_data'] : "";
        $transaction_id = !empty($tran_log_data['au_transaction_id']) ? $tran_log_data['au_transaction_id'] : "";

        if (empty($transaction_id)) {
            throw new Exception("Missing transaction id.");
        }

        $apiRequestJson = [
            "merchantId" => $merchantId,
            "subMerchantId" => $subMerchantId,
            "terminalId" => $terminalId,
            "merchantTranId" => $transaction_id
        ];

        $paramsJsonEncode = json_encode($apiRequestJson);

        $fp = fopen(COMP_PATH . '/includes/icici-key/servPub.txt', 'r');
        $pub_key_string = fread($fp, $staticVal);
        fclose($fp);
        openssl_get_publickey($pub_key_string);
        openssl_public_encrypt($paramsJsonEncode, $crypttext, $pub_key_string);
        $encRequestData = json_encode(base64_encode($crypttext));
        $encRequestData = str_replace('"', '', $encRequestData);

        if ($debug == 1) {
            echo "<br/><br/>=======Request String=========<br/><br/>";
            echo $encRequestData;
        }

        $apiHeaders = array(
            'Content-type: text/plain'
        );

        if ($debug == 1) {
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
            CURLOPT_POSTFIELDS => $encRequestData,
            CURLOPT_HTTPHEADER => $apiHeaders,
        ));

        $encResponseJson = curl_exec($curl);
        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $encResponseJson;
        }

        curl_close($curl);
        if (!$api_call_flag && curl_errno($curl)) {
            $curlError = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometimes.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($encResponseJson)) {
                $fp = fopen(COMP_PATH . '/includes/icici-key/serv.txt', 'r');
                $priv_key = fread($fp, $staticVal);
                fclose($fp);

                $res = openssl_get_privatekey($priv_key, "");
                openssl_private_decrypt(base64_decode($encResponseJson), $newsource, $res);
                $apiResponseData = json_decode($newsource, true);

                if (!empty($apiResponseData)) {

                    $apiResponseJson = json_encode($apiResponseData);
                    $status = $apiResponseData['status'];
                    $message = $apiResponseData['message'];
                    $response = $apiResponseData['response'];
                    $success = $apiResponseData['success'];

                    if ($status == "SUCCESS" && $success == "true") {
                        $merchantId = $apiResponseData['merchantId'];
                        $subMerchantId = $apiResponseData['subMerchantId'];
                        $terminalId = $apiResponseData['terminalId'];
                        $originalBankRRN = $apiResponseData['OriginalBankRRN'];
                        $merchantTranId = $apiResponseData['merchantTranId'];
                        $paidAmount = $apiResponseData['Amount'];

                        $apiStatusId = 1;
                    } else {
                        throw new RuntimeException($message . " | Status : " . $status);
                    }
                } else {
                    throw new ErrorException("QR Code Transaction: API Response empty.");
                }
            } else {
                throw new ErrorException("QR Code Transaction: API Response empty..");
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
        $lead_remarks = "Transaction Status API CALL(Success)";
        $lead_remarks .= "<br>TRANSACTION DETAILS";
        $lead_remarks .= "<br>Message : " . $message . "<br/>Bank RRN : " . $originalBankRRN;
    } else {
        $lead_remarks = "Transaction Status API CALL(Failed)<br/>Error : " . $errorMessage;
    }

    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    $insertApiLog = array();
    $insertApiLog["au_provider"] = 1;
    $insertApiLog["au_method_id"] = $method_id;
    $insertApiLog["au_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["au_transaction_id"] = !empty($transaction_id) ? $transaction_id : NULL;
    $insertApiLog["au_status_id"] = $apiStatusId;
    $insertApiLog["au_request"] = addslashes($paramsJsonEncode);
    $insertApiLog["au_response"] = addslashes($apiResponseJson);
    $insertApiLog["au_encrypt_request"] = addslashes($encRequestData);
    $insertApiLog["au_encrypt_response"] = addslashes($encResponseJson);
    $insertApiLog["au_requested_amount"] = !empty($paidAmount) ? $paidAmount : 0;
    $insertApiLog["au_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["au_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["au_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["au_user_id"] = $user_id;

    $leadModelObj->insertTable("api_upi_logs", $insertApiLog);

    $condition = array(
        "au_lead_id" => $lead_id,
        "au_method_id" => $paymentModeId,
        "au_transaction_id" => $merchantTranId
    );

    $leadModelObj->UpdateUPIApiLog('api_upi_logs', array("au_status_check" => 1), $condition);

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "QRCode Transaction Error : " . $errorMessage : "";

    if ($debug == 1) {
        $response_array['request_json'] = $paramsJsonEncode;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}
