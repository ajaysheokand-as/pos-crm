<?php

function payday_sms_sent_api($type_id = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "SMS Send started | $lead_id | $type_id");

    $responseArray = array("status" => 0, "errors" => "");

    if (!empty($type_id)) {
        $responseArray = routemobile_sms_sent_api_call($type_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "Type id is can not be blank.";
    }

    common_log_writer(6, "SMS Send end | $lead_id | $type_id | " . json_encode($responseArray));

    return $responseArray;
}

function routemobile_sms_sent_api_call($sms_type_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "sms_sent_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiResponseJson = "";
    $new_url_on_whistle = '';
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $source = "";
    $provider_id = 1;
    $tempid = "";
    $message = "";
    $template_id = 'Template_ID';
    $mobile = "";
    $cust_name = "";
    $executive_name = "";
    $executive_mobile = "";
    $otp = "";
    $reference_no = "";
    $loan_amount = "";
    $cust_bank_account_no = "";
    $loan_no = "";
    $repayment_amount = "";
    $repayment_date = "";
    // $repayment_link = "https://www.lms.sotcrm.com/repay-loan";
    $repayment_link = "https://tejasloan.com/repay-now";
    //$repayment_link = 'repay-loan';
    $type = "SMS_API";
    $api_sub_type = "SMS_NIBUS";

    // $active_id = (date('d') % 2) > 0 ? 1 : 2;

    // if ($active_id == 1) {
    //     $api_sub_type = "SMS_VAPIO";
    // } else {
        // $api_sub_type = "SMS_WHISTLE";
    // }

    // if (date("H") >= 2 && date("H") <= 14) {
    //     $api_sub_type = "SMS_VAPIO";
    // } else {
    //     $api_sub_type = "SMS_WHISTLE";
    // }

    // $senderId = array("KASARC", "KASARL", "KASRCL", "KASARI", "KCREDT");
    // $senderId = array("AGRIMC", "AGRIML", "AGRMCL", "AGRIMI", "ACREDT");
    $senderId = array("AGRMCL", "SPEDOL", "SPDOLN", "AGRMFC");


    $hardcode_response = false;
    $debug = !empty($_REQUEST['bltest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    // if ($user_id == 91) {
    //     $api_sub_type = "SMS_VAPIO";
    //     $debug = 1;
    // }

    try {
        $apiConfig = integration_config($type, $api_sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($debug == 1) {
            echo "<pre>";
            print_r($request_array);
        }

        $mobile = $request_array['mobile'];
        $cust_name = isset($request_array['name']) ? $request_array['name'] : "Customer";
        $reference_no = isset($request_array['refrence_no']) ? $request_array['refrence_no'] : "";
        $otp = isset($request_array['otp']) ? $request_array['otp'] : "";
        $executive_name = isset($request_array['executive_name']) ? $request_array['executive_name'] : "Team Kasar Credit & Capital";
        $executive_mobile = isset($request_array['executive_mobile']) ? $request_array['executive_mobile'] : "";
        $loan_amount = isset($request_array['loan_amount']) ? $request_array['loan_amount'] : "";
        $cust_bank_account_no = isset($request_array['cust_bank_account_no']) ? $request_array['cust_bank_account_no'] : "";
        $loan_no = isset($request_array['loan_no']) ? $request_array['loan_no'] : "";
        $repayment_amount = isset($request_array['repayment_amount']) ? $request_array['repayment_amount'] : "";
        $loan_recommended = isset($request_array['loan_recommended']) ? $request_array['loan_recommended'] : "";
        $received_amount = isset($request_array['received_amount']) ? $request_array['received_amount'] : "";
        $repayment_date = isset($request_array['repayment_date']) ? $request_array['repayment_date'] : "";
        $pending_days = isset($request_array['pending_days']) ? $request_array['pending_days'] : "";
        $esign_link = isset($request_array['esign_link']) ? $request_array['esign_link'] : "";
        $ekyc_link = isset($request_array['ekyc_link']) ? $request_array['ekyc_link'] : "";
        $repayment_link_generate = isset($request_array['repayment_link_generate']) ? $request_array['repayment_link_generate'] : "";
        $upload_doc_link = isset($request_array['upload_doc_link']) ? $request_array['upload_doc_link'] : "";

        
        if (empty($mobile)) {
            throw new Exception('Mobile number is blank.');
        }
        // echo $mobile;
        // exit();

        $template_id = '';
        $input_message = '';
        $headerId = "";

        if ($sms_type_id == 1) {
            if (empty($otp)) {
                throw new Exception('Customer Name or OTP is blank.');
            }
            $template_id = "1207174339585786518";
            $headerId = "NAMANF";
            // $input_message = "Dear customer, $otp is the OTP for your login at SalaryOnTime. In case you have not requested this, please contact us at info@speedoloan.com KASAR";
            $input_message = "Dear customer, $otp is the OTP for your login at Salarywalle. In case you have not requested this, please contact us at info@salarywalle.com salarywalle";

        } else if ($sms_type_id == 2) {

            if (empty($cust_name)) {
                throw new Exception('Customer Name is blank.');
            }

            $template_id = "1207174340952339153";
            $headerId = "AGRMCL";
            // $input_message = "Dear $cust_name, Amount of Rs $repayment_amount due on $repayment_date against LAN $loan_no. Please pay using Link https://bit.ly/4dmIpMG & save on interest amount. Ignore if paid. Kasar";
            $inputa_message = "Dear $cust_name, Amount of Rs $repayment_amount due on $repayment_date against LOAN $loan_no. Please pay using Link https://speedoloan.com/repay-now & save on interest amount. Ignore if paid. AGRIM";
            $input_message = "Dear Mr/Ms $cust_name, you have defaulted in repayment of your Loan No. $loan_no. Please pay immediately by clicking on the link https://www.tejasloan.com/repay-now in order to avoid appropriate legal proceedings for recovery of loan including all interest and expenses by the company - Team Salarywalle";
        } else if ($sms_type_id == 3) {
            if (empty($cust_name)) {
                throw new Exception('Customer Name is blank.');
            }
            $template_id = "1207174340944331119";
            $headerId = "SPDOLN";
            // $input_message = "You are just 2 steps away from getting your loan amount disbursed to your account. Kindly complete your journey by clicking on https://tinyurl.com/5n8pnj6p Team KASAR";
            $input_message = "You are just 2 steps away from getting your loan amount disbursed to your account. Kindly complete your journey by clicking on https://tejasloan.com/apply-now Team tejasloan";
        } else if (($sms_type_id == 4)) {
            if (empty($cust_name)) {
                throw new Exception('Invalid Lead.');
            }
            $template_id = "1207174339585786518";
            $headerId = "NAMANF";
            // $input_message = "Dear customer, 3456 is the OTP for your login at SalaryOnTime. In case you have not requested this, please contact us at info@speedoloan.com KASAR";
            $input_message = "Dear customer, 3456 is the OTP for your login at Salarywalle. In case you have not requested this, please contact us at info@salarywalle.com salarywalle";
        }
        // print_r($template_id);
        // exit();

        // $template_id = !empty($template_id) ? $template_id : "1707174116596446805";
        $apiUrl = $apiConfig["ApiUrl"];
        $sms_username = urlencode($apiConfig["user"]);
        $sms_password = urlencode($apiConfig["authkey"]);
        $entityid = urlencode($apiConfig["entityid"]);
        // $templateid = urlencode($apiConfig["templateid"]);
        // $sms_type = $apiConfig["type"];
        // $dlr = $apiConfig["dlr"];
        $Provider = $apiConfig["Provider"];
        $message = urlencode($input_message);
        $source = $headerId;

        // if ($headerId == 'KASARC') {
        //     if ($Provider == 'Whistle') {
        //         $apiData = "user=$sms_username&password=$sms_password&senderid=$headerId&mobiles=$mobile&sms=$message";
        //         $new_url_on_whistle = $apiUrl . $apiData;
        //     } else if ($Provider == 'Vapio') {
        //         $apiData = "username=$sms_username&apikey=3jhWMzQj9tmG&senderid=KASARC&route=TRANS&mobile=$mobile&text=$message&TID=$template_id&PEID=1701171300552626064&format=json";
        //     }
        // } else if ($headerId == 'KASRCL') {
        //     $apiData = "username=$sms_username&apikey=3jhWMzQj9tmG&senderid=KASRCL&route=TRANS&mobile=$mobile&text=$message&TID=$template_id&PEID=1701171300552626064&format=json";
        // } else if ($headerId == 'KASARI') {
        //     $apiData = "username=$sms_username&apikey=3jhWMzQj9tmG&senderid=KASARI&route=TRANS&mobile=$mobile&text=$message&TID=$template_id&PEID=1701171300552626064&format=json";
        // } else {
        //     throw new Exception('Invalid sender ID.');
        // }

        if ($headerId == 'NAMANF') {
            $apiData = "user=$sms_username&authkey=$sms_password&sender=$headerId&mobile=$mobile&text=$message&entityid=$entityid&templateid=$template_id&rpt=1";
            $new_url_sms = $apiUrl . $apiData;
        } else if ($headerId == 'AGRMCL') {
            $apiData = "user=$sms_username&authkey=$sms_password&sender=$headerId&mobile=$mobile&text=$message&entityid=$entityid&templateid=$template_id&rpt=1";
            $new_url_sms = $apiUrl . $apiData;
        } else if ($headerId == 'SPDOLN') {
            $apiData = "user=$sms_username&authkey=$sms_password&sender=$headerId&mobile=$mobile&text=$message&entityid=$entityid&templateid=$template_id&rpt=1";
            $new_url_sms = $apiUrl . $apiData;
        } else {
            throw new Exception('Invalid sender ID.');
        }

        $curl = curl_init();
        if ($Provider == 'Nimbus') {
            $provider_id = 2;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $new_url_sms,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
        } else {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $apiData
            ));
        }

        $apiResponseJson = curl_exec($curl);

        

        // Parse XML response to PHP array
        // if ($Provider == 'Nimbus') {
        //     $apiResponseArray = simplexml_load_string($apiResponseJson, "SimpleXMLElement", LIBXML_NOCDATA);
        //     $apiResponseJson = json_encode($apiResponseArray);
        // }

        if ($debug == 1) {
            echo "<br/><br/> =======Response======<br/><br/>" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        $apiResponseData = json_decode($apiResponseJson, true);
        if (!empty($apiResponseData)) {
            $apiStatusId = 1;
        } else {
            throw new ErrorException("Some error occurred. Please try again.");
        }


        // if (!$hardcode_response && curl_errno($curl)) { // CURL Error
        //     $curlError = curl_error($curl);
        //     curl_close($curl);
        //     throw new RuntimeException("Something went wrong. Please try after sometimes.");
        // } else {

            // if (isset($curl)) {
            //     curl_close($curl);
            // }

            // if ($Provider == 'Whistle') {
            //     $apiResponseData = json_decode($apiResponseJson, true);
            //     if (!empty($apiResponseData) && $apiResponseData['sms']['status'] == 'success') {
            //         $apiStatusId = 1;
            //     } else {
            //         throw new ErrorException("Some error occurred. Please try again.");
            //     }
            // } else if ($Provider == 'SMS_VAPIO') {
            //     $apiResponseData = json_decode($apiResponseJson, true);
            //     if (!empty($apiResponseData) && $apiResponseData['status'] == 'OK') {
            //         $apiStatusId = 1;
            //     } else {
            //         throw new ErrorException("Some error occurred. Please try again.");
            //     }
            // } else {
            //     $apiResponseData = explode(":", $apiResponseJson);

            //     if (!empty($apiResponseData)) {

            //         $apiResponseData = $apiResponseData[1];

            //         if (!empty($apiResponseData)) {
            //             $apiStatusId = 1;
            //         } else {
            //             throw new ErrorException("Some error occurred. Please try again.");
            //         }
            //     } else {
            //         throw new ErrorException("Some error occurred. Please try again..");
            //     }
            // }
            // print_r($apiStatusId);
            // exit();
            // if (!empty($apiResponseData) && $apiResponseData['status'] == 'OK') {
            //     $apiStatusId = 1;
            // } else {
            //     throw new ErrorException("Some error occurred. Please try again.");
            // }

            // if ($Provider == 'Nimbus') {
            //     $apiResponseData = json_decode($apiResponseJson, true);
            //     if (!empty($apiResponseData) && $apiResponseData['status'] == 'OK') {
            //         $apiStatusId = 1;
            //     } else {
            //         throw new ErrorException("Some error occurred. Please try again.");
            //     }
            // } else {
            //     $apiResponseData = explode(":", $apiResponseJson);

            //     if (!empty($apiResponseData)) {

            //         $apiResponseData = $apiResponseData[1];

            //         if (!empty($apiResponseData)) {
            //             $apiStatusId = 1;
            //         } else {
            //             throw new ErrorException("Some error occurred. Please try again.");
            //         }
            //     } else {
            //         throw new ErrorException("Some error occurred. Please try again..");
            //     }
            // }
        // }
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
    $insertApiLog['sms_provider'] = $provider_id;
    $insertApiLog['sms_type_id'] = $sms_type_id;
    $insertApiLog['sms_mobile'] = $mobile;
    $insertApiLog['sms_content'] = addslashes($input_message);
    $insertApiLog['sms_template_id'] = $template_id;
    $insertApiLog['sms_template_source'] = $source;
    $insertApiLog['sms_api_status_id'] = $apiStatusId;
    $insertApiLog['sms_lead_id'] = $lead_id;
    $insertApiLog['sms_user_id'] = $user_id;
    $insertApiLog['sms_errors'] = $errorMessage;
    $insertApiLog['sms_created_on'] = date("Y-m-d H:i:s");


    $leadModelObj->insertTable("api_sms_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['mobile'] = $mobile;
    $response_array['errors'] = $errorMessage;

    // print_r($response_array);
    // exit();

    if ($debug) {
        $response_array['request_json'] = $apiData;
        $response_array['response_json'] = $apiResponseJson;
    }

    return $response_array;
}
