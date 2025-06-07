<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_loan_disbursement_call')) {

    function payday_loan_disbursement_call($lead_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");
        $transRefNoCreateFlag = true; //create new payment request

        $ci = &get_instance();
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing

        $disbursementTransArray = $ci->IntegrationModel->getDisbursementTransLogs($lead_id); //check the previous transactions of disbursement
        
        if (!empty($disbursementTransArray)) {
            //Trans ref no will be same for pending status.
            if (in_array($disbursementTransArray['disb_trans_status_id'], array(1, 2, 4))) { //initiated, pending and hold
                $transRefNoCreateFlag = false; //not need to create new payment request.
                $tranRefNo = $disbursementTransArray["disb_trans_reference_no"]; //previous transaction no
                $disb_trans_id = $disbursementTransArray["disb_trans_id"]; //previous transaction pk id

                if ($disbursementTransArray["disb_trans_payment_mode_id"] == 1 && $request_array['payment_mode_id'] == 2) {
                    $responseArray['status'] = 4;
                    $responseArray['error_msg'] = "Disbursement not allowed as transaction initiated as Online";
                    return $responseArray;
                } else {
                    $responseArray['status'] = 4;
                    $responseArray['error_msg'] = "Disbursement not allowed as transaction initiated.";
                    return $responseArray;
                }
            } else if (in_array($disbursementTransArray['disb_trans_status_id'], array(3))) { //failed
                $transRefNoCreateFlag = true;
            } else if (in_array($disbursementTransArray['disb_trans_status_id'], array(5))) {
                $responseArray['status'] = 4;
                $responseArray['error_msg'] = "Disbursement already done to this application.";
                return $responseArray;
            }
        }

        if ($transRefNoCreateFlag) { //create the disbursement request
            $tranRefNo = "TLUAT" . date("YmdHis") . rand(100, 999);

            if ($envSet == 'production') {
                $tranRefNo = "TLPRD" . date("YmdHis") . rand(100, 999);
            }
            $disb_trans_array = array();
            $disb_trans_array["disb_trans_lead_id"] = $lead_id;
            $disb_trans_array["disb_trans_reference_no"] = $tranRefNo;
            $disb_trans_array["disb_trans_bank_id"] = $request_array['bank_id'];
            $disb_trans_array["disb_trans_payment_mode_id"] = $request_array['payment_mode_id']; //1=>Online,2=>Offline
            $disb_trans_array["disb_trans_payment_type_id"] = $request_array['payment_type_id']; //1=>IMPS,2=>NEFT
            $disb_trans_array["disb_trans_status_id"] = 1; //1=>initiated,2=>pending,3=>failed,4=>hold,5=>completed
            $disb_trans_array["disb_trans_created_by"] = $user_id;
            $disb_trans_array["disb_trans_created_on"] = date("Y-m-d H:i:s");

            if ($request_array['payment_mode_id'] == 2) {
                $disb_trans_array["disb_trans_status_id"] = 5;
            }

            $disb_trans_id = $ci->IntegrationModel->insert("lead_disbursement_trans_log", $disb_trans_array);
        }

        $request_array['tran_ref_no'] = $tranRefNo;

        if ($request_array['payment_mode_id'] == 1) {
            if ($request_array['bank_id'] == 1 && $request_array['payment_type_id'] == 1) {
                $request_array['disb_trans_id'] = $disb_trans_id;
                $responseArray = icici_disburse_loan_amount_api($lead_id, $request_array);
            } else {
                $responseArray["status"] = 4;
                $responseArray["error_msg"] = "Bank is not available for online mode.";
            }
        } else {
            $responseArray["status"] = 1;
        }


        return $responseArray;
    }
}


if (!function_exists('icici_disburse_loan_amount_api')) {


    function icici_disburse_loan_amount_api($lead_id, $request_array) {

        $api_disburse_bypass_mobile = array(); //"9560807913"

        // $envSet = $envSet;
        $envSet = 'UAT';

        $ci = &get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $esbApiRequestJson = "";
        $esbApiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $parseResponseData = "";
        $type = "ICICI_DISBURSEMENT_CALL";
        $sub_type = "DO_DISBURSEMENT";
        $product_id = 1;

        $hardcode_response = false;

        $debug = !empty($_REQUEST['sltest']) ? 0 : 0;

        $applicationDetails = array();

        $master_payment_mode = array(1 => "IMPS", 2 => "NEFT");

        $beneAccNo = "";
        $beneIFSC = "";
        $beneName = "";
        $loan_account_number = "";
        $loan_amount = "";
        $bank_reference_no = "";
        $payment_reference_no = "";
        $loan_id = "";

        $trans_type = !empty($request_array['payment_type_id']) ? $request_array['payment_type_id'] : "";
        $tranRefNo = !empty($request_array['tran_ref_no']) ? $request_array['tran_ref_no'] : "";
        $disb_trans_id = !empty($request_array['disb_trans_id']) ? $request_array['disb_trans_id'] : "";
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999";

        try {

            // $apiUrl = "https://apibankingone.icicibank.com/api/v1/composite-payment";
            // $apiKey = "GxWaczAJKJvYgeUWRlfFTHF8pcTmtrvA";
            // $apiPassCode = "dec27836c64b460086bb2a782c525668";
            // $apiBCID = "IBCKAS01640";

            $apiUrl = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment";
            $apiKey = "ujEjRsMHJWURm9xfAmGyDwuvacEI2cTu";
            $apiPassCode = "447c4524c9074b8c97e3a3c40ca7458d";
            $apiBCID = "IBCKer00055";

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            if (empty($trans_type)) {
                throw new Exception("Missing payment mode type.");
            }

            if (empty($master_payment_mode[$trans_type])) {
                throw new Exception("Invalid payment mode type.");
            }

            if (!in_array($user_id, array(17, 34, 30, 40, 103, 115, 117, 91))) {
                throw new Exception("Un-Authorized access of disbursement api... " . $user_id);
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];

                if (empty($applicationDetails)) {
                    throw new Exception("Application details cannot be empty.");
                } else {
                    $lead_status_id = $applicationDetails["lead_status_id"];
                    /*                     if (in_array($applicationDetails['mobile'], $api_disburse_bypass_mobile) && $envSet == 'development') {
//$hardcode_response = true;
                    } */
                }
            }

            $camDataReturnArr = $ci->IntegrationModel->getLeadCAMDetails($lead_id);
            if ($camDataReturnArr['status'] === 1) {
                $camDetails = $camDataReturnArr['cam_data'];
                if (empty($camDetails)) {
                    throw new Exception("CAM details cannot be empty.");
                } else {
                    $loan_amount = !empty($camDetails["loan_recommended"]) ? $camDetails["loan_recommended"] : "";
                    $net_disbursal_amount = !empty($camDetails["net_disbursal_amount"]) ? $camDetails["net_disbursal_amount"] : "";
                }
            }

            $loanDataReturnArr = $ci->IntegrationModel->getLeadLoanDetails($lead_id);
            if ($loanDataReturnArr['status'] === 1) {
                $loanDetails = $loanDataReturnArr['loan_data'];
                if (empty($loanDetails)) {
                    throw new Exception("Loan details cannot be empty.");
                } else {
                    $loan_id = !empty($loanDetails["loan_id"]) ? $loanDetails["loan_id"] : "";

                    $loan_account_number = !empty($loanDetails["loan_no"]) ? $loanDetails["loan_no"] : "";
                }
            }

            $bankingDataReturnArr = $ci->IntegrationModel->getCustomerAccountDetails($lead_id);
            /* echo "<pre>";
print_r($bankingDataReturnArr);  */
            if ($bankingDataReturnArr['status'] === 1) {
                $bankingDetails = $bankingDataReturnArr['banking_data'];
                if (empty($bankingDetails)) {
                    throw new Exception("Customer banking details not found.");
                } else {
                    $beneName = !empty($bankingDetails["beneficiary_name"]) ? $bankingDetails["beneficiary_name"] : "";
                    $beneAccNo = !empty($bankingDetails["account"]) ? $bankingDetails["account"] : "";
                    $beneIFSC = !empty($bankingDetails["ifsc_code"]) ? $bankingDetails["ifsc_code"] : "";
                }
            } else {
                throw new Exception("Please verify the customer banking details.");
            }

            if (empty($loan_account_number)) {
                throw new Exception("Please generate loan account number for loan disbursement.");
            }

            if (empty($net_disbursal_amount)) {
                throw new Exception("Loan amount cannot be zero or blank.");
            }

            // if ($loan_amount < 4000) {
            //     throw new Exception("Loan amount cannot be lesser then Rs. 4000.");
            // }

            if ($loan_amount > 150000) {
                //            if ($loan_amount > 500000) {
                throw new Exception("Loan amount cannot be greater then Rs. 150000.");
            }

            if (empty($lead_status_id) || !in_array($lead_status_id, array(13))) {
                throw new Exception("Application has been moved to next steps.");
            }

            $tmp_disburse_status_arr = array();

            if ((isset($loanDetails['loan_disbursement_trans_status_id']) && in_array($loanDetails['loan_disbursement_trans_status_id'], [1, 2, 3, 4])) || !empty($loanDetails['disburse_refrence_no'])) {
                throw new Exception("Loan amount has been already disbursed.");
            }

            $beneName = isset($bankingDetails['beneficiary_name']) ? $bankingDetails['beneficiary_name'] : '';

            if ($trans_type == 1) {

                $beneName_first_name = trim(substr($beneName, 0, 15));
                $localTxnDtTime = date("YmdHis");

                $senderName = "Agrim Fincap Pvt. Ltd.";
                $sender_mobile = "9999988888";
                $retailerCode = "rcode";

                $payment_reference_no = "IMPS/ICICI/" . $loan_account_number . "/" . $beneName_first_name . "/" . $net_disbursal_amount;
                $passCode = $apiPassCode;
                $bcID = $apiBCID;
                $x_priority = "0100";

                //$net_disbursal_amount = 1;
                $apiRequestJson = '{
                            "localTxnDtTime":"' . $localTxnDtTime . '",
                            "beneAccNo":"' . $beneAccNo . '",
                            "beneIFSC":"' . $beneIFSC . '",
                            "amount":"' . $net_disbursal_amount . '",
                            "tranRefNo":"' . $tranRefNo . '",
                            "paymentRef":"' . $payment_reference_no . '",
                            "senderName":"' . $senderName . '",
                            "mobile":"' . $sender_mobile . '",
                            "retailerCode":"' . $retailerCode . '",
                            "passCode":"' . $passCode . '",
                            "bcID":"' . $bcID . '"
                        }';
            }

            // print_r($apiRequestJson);
            // exit();

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);
            $apiRequestArray = json_decode($apiRequestJson, true);

            if ($debug == 1) {

                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            $esbApiRequestJson = icici_request_encrypt($apiRequestArray);

            $encryptedKey = "";
            $encryptedData = "";

            $apiHeaders = array(
                "cache-control: no-cache",
                "accept: application/json",
                "content-type: application/json",
                "apikey: $apiKey",
                "x-priority: $x_priority"
            );
            

            if ($hardcode_response && $envSet == 'development') {
                //$apiResponseJson = '{"ActCode":"0","Response":"Transaction Successful","BankRRN":"200701023783","BeneName":"BENE CUSTOMER NAME","success":true,"TransRefNo":"LWUAT20220107013948999"}';
            } else {

                $acurl = curl_init();
                curl_setopt_array($acurl, array(
                    CURLOPT_URL => $apiUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $esbApiRequestJson,
                    CURLOPT_HTTPHEADER => $apiHeaders,
                ));

                $esbApiResponseJson = curl_exec($acurl);
                if ($debug == 1) {
                    echo "<br/><br/> =======Response Encrypted ======<br/><br/>" . $esbApiResponseJson;
                }
            }

            // print_r($esbApiResponseJson);
            // exit();
            

            //$esbApiResponseJson = '{ "requestId": "req_1718273189", "service": "UPI", "encryptedKey": "E77UZMShl5oIR2WbUk7TrGLOQQCfgcLQUaUt0/lfi6KxYMMrPZ4ldDvGO5XcpYqUyX1KB9pueX4UCa+T01jXlfT+vUOXpKwJZcuAdGjygrTnD/zMUU7dz7bkEf2PSofVJiOCbjVvGasgnvaY3e9b+JvIeIL87R/Lu1lab5Yh8wnBezaFl7R00TVkCvgkEyDlfk9a95eHbfxnDbaafHU4ZVEVIUWz1Y0JK1MOr5LYdWxaBTn7L9IOMvC7Kg/f3L3OaAKZE+g1dIWyOzl4xmxV1I67oDqxh6sl7KIGQJq4/K34jXo3a9KyqHHgoQkBQACLWeFwlQbhDd8rC+TEZNrh5QWjXvcL2yofW3xTT+nkytM7tF+Z8Zo1yTHFBxY7OMA1+8E631k8ECFKZBNsBIPJebwCEemdBo33c1kGIXvvyz6gVValqChQfEB3r4SMXk8EMiEHZS3PrVUW6g7DOnyz8jlU7RRoZt71nNLIB9+F10X0AknO028Ugt8Bo8kH/zreDUA/qGCuYRRmm+Q0dLalAtJV7G1nIGPA9G29t9JXf8xFoLMaxAF25XF8HftYcbMaprGxSpkGH+R9Bv8ZlCW6ABliUaKowxboqp9IurO90nRFFMnY5Xv0Oww2HMirvY+qfG/eOQ3ZgGBRyMSxaa8C8w8ASuc8F4Ct9fSU0WMMz9o=", "oaepHashingAlgorithm": "NONE", "iv": "", "encryptedData": "D0wt4TBva8sAJ3pFC9TFNkMbuqZ7P2kdvZ5h3fOP+Tnj9XI6xeYDsRlAHORhQjiYNgVlUJQksYaAuXwEj5/8eoLm73+Z/4eAk2DWSz9TqnusFnTOXnVVtk6OO+gtE9Vrj8ZFO+zVMTkbYFUQjInI52G0/Xiqw+2roOcbEVQOpEFYrQ5wdO9PN5TwKe6DDRexX9Ow/XJmQA1wNPGgvlZlSg==", "clientInfo": "", "optionalParam": "" }';

            $apiResponseJson = icici_response_decrypt($esbApiResponseJson);

            // print_r($apiResponseJson);
            // exit;

            $apiResponseDateTime = date("Y-m-d H:i:s");

            if (curl_errno($acurl) && !$hardcode_response) {
                $curlError = "(" . curl_errno($acurl) . ") " . curl_error($acurl) . " to url " . $apiUrl;
                curl_close($acurl);
                throw new RuntimeException("Something went wrong. Please try after sometime.");
            } else {

                if (isset($acurl)) {
                    curl_close($acurl);
                }


                $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

                if ($debug == 1) {
                    echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
                }
                //$apiResponseJson = '{ "ActCode": "14", "Response": "Duplicate transaction", "BankRRN": "318620188540", "BeneName": "", "success": true, "TransRefNo": "Testing01" }';
                $apiResponseData = json_decode($apiResponseJson, true);
                //print_r($apiResponseJson); die;
                if (!empty($apiResponseData)) {

                    if ($trans_type == 1 && isset($apiResponseData['ActCode']) && $apiResponseData['ActCode'] == 0 && isset($apiResponseData['success']) && $apiResponseData['success'] == true) {
                        if (!empty($apiResponseData['BankRRN'])) {
                            $apiStatusId = 1;
                            $bank_reference_no = $apiResponseData['BankRRN'];
                        } else {
                            throw new ErrorException("BankRRN is not available in IMPS response.");
                        }
                    }
                    /* else if ($trans_type == 2 && isset($apiResponseData['RESPONSE']) && $apiResponseData['RESPONSE'] == "SUCCESS") {
                        if (!empty($apiResponseData['URN']) && !empty($apiResponseData["UTR"])) {
                            $apiStatusId = 1;
                            $bank_reference_no = $apiResponseData['URN'] . " | " . $apiResponseData["UNIQUEID"] . " | " . $apiResponseData["UTR"];
                        } else {
                            throw new ErrorException("URN or UTR is not available in NEFT response.");
                        }
                    } */ else {

                        if (!empty($apiResponseData['ActCodeDesc'])) {
                            $tmp_error = $apiResponseData['ActCodeDesc'];
                        } else if (!empty($apiResponseData['MESSAGE'])) {
                            $tmp_error = $apiResponseData['MESSAGE'];
                        } else if (!empty($apiResponseData['Response'])) {
                            $tmp_error = $apiResponseData['Response'];
                        } else if (!empty($apiResponseData['description'])) {
                            $tmp_error = $apiResponseData['description'];
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
            $retrigger_call = ($retrigger_call == 0) ? 0 : 0;
        } catch (Exception $e) {
            $apiStatusId = 4;
            $errorMessage = $e->getMessage();
        }

        $insertApiLog = array();
        $insertApiLog["disburse_user_id"] = $user_id;
        $insertApiLog["disburse_lan_no"] = $loan_account_number;
        $insertApiLog["disburse_bank_id"] = 1;
        $insertApiLog["disburse_method_id"] = 1;
        $insertApiLog["disburse_trans_refno"] = $tranRefNo;
        $insertApiLog["disburse_trans_type_id"] = $trans_type;
        $insertApiLog["disburse_lead_id"] = $lead_id;
        $insertApiLog["disburse_beneficiary_account_no"] = !empty($beneAccNo) ? $beneAccNo : "";
        $insertApiLog["disburse_beneficiary_ifsc_code"] = !empty($beneIFSC) ? $beneIFSC : "";
        $insertApiLog["disburse_beneficiary_name"] = !empty($beneName) ? $beneName : "";
        $insertApiLog["disburse_api_status_id"] = $apiStatusId;
        $insertApiLog["disburse_payment_reference_no"] = $payment_reference_no;
        $insertApiLog["disburse_bank_reference_no"] = $bank_reference_no;
        $insertApiLog["disburse_request"] = addslashes($apiRequestJson);
        $insertApiLog["disburse_response"] = addslashes($apiResponseJson);
        $insertApiLog["disburse_encrypted_request"] = addslashes($esbApiRequestJson);
        $insertApiLog["disburse_encrypted_response"] = addslashes($esbApiResponseJson);
        $insertApiLog["disburse_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["disburse_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["disburse_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $return_log_id = $ci->IntegrationModel->insert("api_disburse_logs", $insertApiLog);

        if ($apiStatusId == 1) {
            $call_description = "ICICI DISBURSEMENT API(Success) <br> LAN : $loan_account_number <br> Customer Account No : $beneAccNo <br>  TransRefNo : $tranRefNo <br> BankRefNo : $bank_reference_no";
        } else {
            $call_description = "ICICI DISBURSEMENT API(Fail) <br> Customer Account No : $beneAccNo <br> TransRefNo : $tranRefNo <br> Error : " . $errorMessage;
        }

        if ($applicationDetails["lead_status_id"] > 0) {
            /* $lead_followup = [
                'lead_id' => $lead_id,
                'user_id' => $user_id,
                'status' => $applicationDetails["status"],
                'stage' => $applicationDetails["stage"],
                'lead_followup_status_id' => $applicationDetails["lead_status_id"],
                'remarks' => addslashes($call_description),
                'created_on' => date("Y-m-d H:i:s")
            ];
            $ci->IntegrationModel->insert("lead_followup", $lead_followup); */
        }



        if (!empty($loan_id)) {
            $ci->IntegrationModel->update("loan", ["loan_id" => $loan_id], array("loan_disbursement_trans_log_id" => $return_log_id, "loan_disbursement_trans_status_id" => $apiStatusId, "loan_disbursement_trans_status_datetime" => date("Y-m-d H:i:s"), "disburse_refrence_no" => $bank_reference_no));
        }

        if ($apiStatusId == 1 && !empty($disb_trans_id)) {
            $ci->IntegrationModel->update("lead_disbursement_trans_log", array("disb_trans_id" => $disb_trans_id), array("disb_trans_status_id" => 5, "disb_trans_updated_by" => $user_id, "disb_trans_updated_on" => date("Y-m-d H:i:s")));
        } else if (in_array($apiStatusId, array(2, 3)) && !empty($disb_trans_id)) {
            $ci->IntegrationModel->update("lead_disbursement_trans_log", array("disb_trans_id" => $disb_trans_id), array("disb_trans_status_id" => 2, "disb_trans_updated_by" => $user_id, "disb_trans_updated_on" => date("Y-m-d H:i:s")));
        }


        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['payment_reference'] = "";

        $returnResponseData['payment_reference'] = $payment_reference_no;

        if (!empty($bank_reference_no)) {
            $returnResponseData['payment_reference'] .= "/" . $bank_reference_no;
        }

        $returnResponseData['bank_reference'] = $bank_reference_no;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? "ICICI DISBURSEMENT API Error : " . $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["disburse_errors"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }

        return $returnResponseData;
    }
}

if (!function_exists('icici_request_encrypt')) {
    function icici_request_encrypt($data) {
        $apostData = json_encode($data);
        $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
        $file_path = APPPATH . 'prod_public.txt';
        $fp = fopen($file_path, "r"); // bank certificate
        $pub_key_string = fread($fp, 8192);
        openssl_get_publickey($pub_key_string);
        openssl_public_encrypt($sessionKey, $encryptedKey, $pub_key_string); // RSA
        $iv = 1234567890123456;
        $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES

        $request = [
            "requestId" => $data['tranRefNo'],
            "encryptedKey" => base64_encode($encryptedKey),
            "iv" => base64_encode($iv),
            "encryptedData" => base64_encode($encryptedData),
            "oaepHashingAlgorithm" => "NONE",
            "service" => "",
            "clientInfo" => "",
            "optionalParam" => ""
        ];
        return json_encode($request);
    }
}

if (!function_exists('icici_response_decrypt')) {
    // function icici_response_decrypt($aresponse) {
    //     $file_path = APPPATH . 'prod_private.pem';
    //     $fp = fopen($file_path, "r"); // your private key
    //     $priv_key = fread($fp, 8192);
    //     fclose($fp);
    //     $res = openssl_get_privatekey($priv_key, "");
    //     $data = json_decode($aresponse);
    //     openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
    //     $encData = openssl_decrypt(base64_decode($data->encryptedData), "aes-128-cbc", $key, OPENSSL_PKCS1_PADDING);
    //     $newsource = substr($encData, 16);
    //     return $newsource;
    // }
    function icici_response_decrypt($aresponse) {
        $file_path = APPPATH . 'prod_private.pem';
        
        if (!file_exists($file_path)) {
            die("Error: Private key file not found.");
        }
        $fp = fopen($file_path, "r"); // your private key
        $priv_key = fread($fp, 8192);
        fclose($fp);
    
        // Read private key
        // $priv_key = file_get_contents($file_path);
        
        // Get private key resource
        // $res = openssl_pkey_get_private($priv_key, $passphrase);
        // $res = openssl_get_privatekey($priv_key, "");
        $res = openssl_get_privatekey($priv_key);
    
        if (!$res) {
            die("Failed to load private key: " . openssl_error_string());
        } else {
            print_r($res);
        }
    
        // Decode JSON response
        $data = json_decode($aresponse);
        if (!$data || empty($data->encryptedKey) || empty($data->encryptedData)) {
            die("Error: Invalid response format.");
        }
    
        // Decrypt AES key using private RSA key
        if (!openssl_private_decrypt(base64_decode($data->encryptedKey), $aes_key, $res)) {
            die("Error: RSA decryption failed.");
        }
    
        // Extract IV (first 16 bytes)
        $enc_data = base64_decode($data->encryptedData);
        $iv = substr($enc_data, 0, 16);
        $ciphertext = substr($enc_data, 16);
    
        // Decrypt AES-128-CBC encrypted data
        $decrypted = openssl_decrypt($ciphertext, "aes-128-cbc", $aes_key, OPENSSL_RAW_DATA, $iv);
    
        if ($decrypted === false) {
            die("Error: AES decryption failed.");
        }
    
        return $decrypted;
    }
    
}

if (!function_exists('icici_disburse_loan_status_api')) {
    function icici_disburse_loan_status_api($lead_id, $reference_id) {
        try {
            $ci = &get_instance();
            $ci->load->helper('integration/integration_config');
            $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

            $disbursementTransArray = $ci->db->select("*")->from("lead_disbursement_trans_log")->where(['disb_trans_lead_id' => $lead_id, 'disb_trans_active' => 1, 'disb_trans_deleted' => 0, 'disb_trans_reference_no' => $reference_id])->order_by('disb_trans_id', 'desc')->get()->row_array();

            if (empty($disbursementTransArray)) {
                throw new Exception("Disbursement Transaction not found.");
            }
            $reference_no = $disbursementTransArray['disb_trans_reference_no'];
            $loan_disbursement_trans_status_datetime = $disbursementTransArray['disb_trans_created_on'];
            $disb_trans_payment_mode_id = $disbursementTransArray['disb_trans_payment_mode_id'];
            $disb_trans_payment_type_id = $disbursementTransArray['disb_trans_payment_type_id'];
            $disb_trans_status_id = $disbursementTransArray['disb_trans_status_id'];
            if ($reference_id != $reference_no) {
                throw new Exception("Invailed Disbursement reference no.");
            }
            if ($reference_id != $reference_no) {
                throw new Exception("Invailed Disbursement reference no.");
            }
            if ($disb_trans_payment_mode_id == 2) {
                throw new Exception("Transction Mode is Offline.");
            }
            // $apiUrl = "https://apibankingone.icicibank.com/api/v1/composite-status";
            // $apiKey = "GxWaczAJKJvYgeUWRlfFTHF8pcTmtrvA";
            // $apiPassCode = "dec27836c64b460086bb2a782c525668";
            // $apiBCID = "IBCKAS01640";
            $apiUrl = "https://apibankingonesandbox.icicibank.com/api/v1/composite-status";
            $apiKey = "ujEjRsMHJWURm9xfAmGyDwuvacEI2cTu";
            $apiPassCode = "447c4524c9074b8c97e3a3c40ca7458d";
            $apiBCID = "IBCKer00055";
            $x_priority = "0100";

            $trans_date = date("m/d/Y", strtotime($loan_disbursement_trans_status_datetime));
            $apiRequestJson = '{
				"transRefNo": "' . $reference_id . '",
				"passCode": "' . $apiPassCode . '",
				"bcID": "' . $apiBCID . '",
				"recon360": "N",
				"date": "' . $trans_date . '"
			}';
            $apiRequestArray = json_decode($apiRequestJson, true);
            $esbApiRequestJson = icici_request_encrypt($apiRequestArray);

            $apiHeaders = array(
                "cache-control: no-cache",
                "accept: application/json",
                "content-type: application/json",
                "apikey: $apiKey",
                "x-priority: $x_priority"
            );
            $acurl = curl_init();
            curl_setopt_array($acurl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $esbApiRequestJson,
                CURLOPT_HTTPHEADER => $apiHeaders,
            ));
            $esbApiResponseJson = curl_exec($acurl);
            if (curl_errno($acurl)) {
                $curlError = "(" . curl_errno($acurl) . ") " . curl_error($acurl) . " to URL " . $apiUrl;
                curl_close($acurl);
                throw new RuntimeException("Curl Error: " . $curlError);
            }
            curl_close($acurl);

            $apiResponseJson = icici_response_decrypt($esbApiResponseJson);
            $apiResponseData = json_decode($apiResponseJson, true);
            return $apiResponseData;
        } catch (ErrorException $le) {
            // Log or handle specific ErrorException
            $apiStatusId = 2;
            $errorMessage = $le->getMessage();
        } catch (RuntimeException $re) {
            // Log or handle specific RuntimeException
            $apiStatusId = 3;
            $errorMessage = $re->getMessage();
            $retrigger_call = ($retrigger_call == 0) ? 0 : 0;
        } catch (Exception $e) {
            // Log or handle generic Exception
            $apiStatusId = 4;
            $errorMessage = $e->getMessage();
        }

        return $responseData;
    }
}
