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

    require_once (COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $source = "";
    $tempid = "";
    $message = "";
    $template_id = "";
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
    $repayment_link = "https://www.bharatloan.com/repay-loan";
    //$repayment_link = 'repay-loan';
    $type = "SMS_API";

    $api_sub_type = "NORMAL";

    if(in_array($sms_type_id,[16])){
        $sms_type = 'PROMOTIONAL';
    }

    $hardcode_response = false;

    $debug = !empty($_REQUEST['bltest']) ? 1 : 0;
//        $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {


        $apiConfig = integration_config($type, $api_sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($debug == 1) {
            echo "<pre>";
            print_r($request_array);
            exit;
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $mobile = $request_array['mobile'];
        $cust_name = (isset($request_array['name']) && !empty($request_array['name'])) ? $request_array['name'] : "Customer";
        $reference_no = (isset($request_array['refrence_no']) && !empty($request_array['refrence_no'])) ? $request_array['refrence_no'] : "";
        $otp = (isset($request_array['otp']) && !empty($request_array['otp'])) ? $request_array['otp'] : "";
        $executive_name = (isset($request_array['executive_name']) && !empty($request_array['executive_name'])) ? $request_array['executive_name'] : "Team Bharat Loan";
        $executive_mobile = (isset($request_array['executive_mobile']) && !empty($request_array['executive_mobile'])) ? $request_array['executive_mobile'] : "";
        $loan_amount = (isset($request_array['loan_amount']) && !empty($request_array['loan_amount'])) ? $request_array['loan_amount'] : "";
        $cust_bank_account_no = (isset($request_array['cust_bank_account_no']) && !empty($request_array['cust_bank_account_no'])) ? $request_array['cust_bank_account_no'] : "";
        $loan_no = (isset($request_array['loan_no']) && !empty($request_array['loan_no'])) ? $request_array['loan_no'] : "";
        $repayment_amount = (isset($request_array['repayment_amount']) && !empty($request_array['repayment_amount'])) ? $request_array['repayment_amount'] : "";
        $repayment_date = (isset($request_array['repayment_date']) && !empty($request_array['repayment_date'])) ? $request_array['repayment_date'] : "";
        $pending_days = (isset($request_array['pending_days']) && !empty($request_array['pending_days'])) ? $request_array['pending_days'] : "";
        $esign_link = (isset($request_array['esign_link']) && !empty($request_array['esign_link'])) ? $request_array['esign_link'] : ""; 
        $ekyc_link = (isset($request_array['ekyc_link']) && !empty($request_array['ekyc_link'])) ? $request_array['ekyc_link'] : "";
        
        if (empty($mobile)) {
            throw new Exception('Mobile number is black.');
        }

//        if ($sms_type_id == 1) {//OTP
//            if (empty($cust_name) || empty($otp)) {
//                throw new Exception('Customer Name or otp is blank.');
//            }
//
//            $template_id = "1207167522658871633";
//            $source = "BLAPPL";
//            $input_message = "Dear $cust_name,\n$otp is your mobile verification code.\nPlease don't share it with anyone - Bharat Loan (DEVMUNI FINANCE)";
//        }
        if ($sms_type_id == 1) {//Mobile OTP Sms New
            if (empty($otp) || empty($cust_name)) {
                throw new Exception('Customer Name or OTP is blank.');
            }
            $template_id = "1207168077556341426";
            $source = "BLAPPL";
            $input_message = "Dear ".$cust_name.", ".$otp." is your mobile verification code. Please don't share it with anyone. Bharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 2) {//LEAD Thank You
            if (empty($cust_name) || empty($reference_no)) {
                throw new Exception('Customer Name or Reference no. is blank.');
            }

            $template_id = "1207167522788634000";
            $source = "BLAPPL";
            $input_message = "Dear $cust_name,\nYour loan application $reference_no is successfully submitted.\nWe will get back to you soon. - Bharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 3) {//Exectuive Connect
            if (empty($cust_name) || empty($executive_name) || empty($executive_mobile)) {
                throw new Exception('Customer Name or Executive Details is blank.');
            }

            $template_id = "1207167522910404872";
            $source = "BLAPPL";
            $input_message = "Hi $cust_name,\nI have received your loan application for a Personal Loan.\nPlease be ready with the necessary documents. I will connect with you soon.\nRegards,\n$executive_name\n+91-$executive_mobile\nBharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 4) {//Lead Reject
            $template_id = "1207167542541360683";
            $source = "BLDECL";
            $input_message = "We regret to inform you that your loan application has been declined due to our internal policy, and we have made no determination about your credibility.\nBharat Loan (Devmuni Finance)";
        } else if ($sms_type_id == 5) {//Loan Dibsursed
            if (empty($cust_name) || empty($loan_amount) || empty($cust_bank_account_no)) {
                throw new Exception('Customer Name or Loan Amount or Customer Bank Account is blank.');
            }

            if (empty($loan_no) || empty($repayment_amount) || empty($repayment_date)) {
                throw new Exception('Loan number or Repayment Amount and Date is blank.');
            }
            $template_id = "1207167542607178020";
            $source = "BLDISB";
            $input_message = "Dear $cust_name,\n\nCongratulations! Rs.$loan_amount/- is credited to your account no. $cust_bank_account_no against your Loan No. $loan_no.\nPlease ensure to pay your repayment amount Rs.$repayment_amount/- on repayment date $repayment_date.\nRepayment Link - $repayment_link\nBharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 6) {//Loan Repayment Reminder Old
            if (empty($loan_no) || empty($repayment_date)) {
                throw new Exception('Loan number or Repayment Date is blank.');
            }
            $template_id = "1207167542525870191";
            $source = "BLCOLX";
            $input_message = "Your Loan No. " . $loan_no . " is due for repayment on " . $repayment_date . ". Please make your payment by clicking on the link $repayment_link Bharat Loan (DEVMUNI FINANCE)";
            //echo $input_message;
            //die();
        } else if ($sms_type_id == 7) {//Lead Apply
            if (empty($cust_name)) {
                throw new Exception('Customer Name is blank.');
            }

            $template_id = "1207167707187394006";
            $source = "BLAPPL";
            $input_message = "Dear " . $cust_name . ", We are trying to reach you but didn't connect with you. Please visit us at " . COMP_WEBSITE_URL . ".  T&C Apply. Bharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 9) {//Loan Repayment Reminder New
            if (empty($repayment_amount) || empty($cust_name) || empty($repayment_date)) {
                throw new Exception('Loan number or Repayment Date is blank.');
            }
            $template_id = "1207167973030405803";
            $source = "BLCOLX";
            $input_message = "Hello " . $cust_name . ",  This is a friendly reminder that your loan payment of " . $repayment_amount . " is due on " . $repayment_date . ".  Please ensure that the payment is made on time to avoid any late fees or penalties. Thank you for choosing Bharat Loan (DEVMUNI FINANCE) as your lending partner.";
        } else if ($sms_type_id == 10) {//Loan Repayment Reminder New 2
            if (empty($loan_no) || empty($repayment_link) || empty($pending_days)) {
                throw new Exception('Loan no or Repayment Link or Pending Days is blank.');
            }
            $template_id = "1207167972987364510";
            $source = "BLCOLX";
            $input_message = "Dear Customer, Repayment Reminder Day ".$pending_days."!!! Please do the timely repayment of your Loan No. ".$loan_no." using the url ".$repayment_link.". Bharat Loan (DEVMUNI FINANCE)";
        }  else if ($sms_type_id == 12) {//Customer eSign SMS
            if (empty($esign_link) || empty($cust_name)) {
                throw new Exception('Customer Name or eSign Link is blank.');
            }
            $template_id = "1207168121850887984";
            $source = "BLCDIT";
            $input_message = "Dear ".$cust_name.", Congratulation!!! We have sanction your loan application for the disbursal. Please read the sanction letter carefully and do the eSign using Aadhaar. eSign - ".$esign_link." Bharat Loan (DEVMUNI FINANCE)";
        }  else if ($sms_type_id == 13) {//Customer eKYC SMS
            if (empty($ekyc_link) || empty($cust_name)) {
                throw new Exception('Customer Name or eKYC Link is blank.');
            }
            $template_id = "1207168121821844132";
            $source = "BLCDIT";
            $input_message = "Dear ".$cust_name.", Congratulation!!! We have process your loan application to the next step. Please complete the eKyc using the below link. eKyc - ".$ekyc_link." Bharat Loan (DEVMUNI FINANCE)";
        } else if ($sms_type_id == 14) {//Collection Pending SMS
            if (empty($cust_name) || empty($loan_no) || empty($repayment_link)) {
                throw new Exception('Customer Name or Loan no or Repayment Link is blank.');
            }
            $template_id = "1207167973066040871";
            $source = "BLCOLX";
            $input_message = "Hi ".$cust_name.",  We noticed that your Loan No. ".$loan_no." repayment is overdue.  Please make your payment as soon as possible to avoid any further fees or penalties.  Repayment Link ".$repayment_link.". Bharat Loan (DEVMUNI FINANCE)";
        }   else if ($sms_type_id == 15) {//Mobile OTP Sms New
            if (empty($otp) || empty($cust_name)) {
                throw new Exception('Customer Name or OTP is blank.');
            }
        
            $template_id = "1207168077556341426";
            $source = "BLCOLX";
            $input_message = "Dear ".$cust_name.", ".$otp." is your mobile verification code. Please don't share it with anyone. Bharat Loan (DEVMUNI FINANCE)";
        }  
        if ($sms_type_id == 16) {//Mobile OTP Sms New
            if (empty($otp) || empty($cust_name)) {
                throw new Exception('Customer Name or OTP is blank.');
            }
            $template_id = "1207168258773055016";
            $source = "BLCOLX";
            $input_message = "Dear ".$cust_name.", Did you know that you may be eligible for a loan? Our loan options offer \n competitive rates and flexible repayment terms to help you meet your financial \n goals. 
            \n Applying is quick and easy! Just visit our website \n.
            Bharat Loan (DEVMUNI FINANCE) ";
        }
        else if ($sms_type_id == 17) {//Mobile OTP Sms New
            if (empty($otp) || empty($cust_name)) {
                throw new Exception('Customer Name or OTP is blank.');
         }
         $template_id = "1207168258761210222";
         $source = "BLCOLX";
         $input_message = "Dear ".$cust_name.", We hope this message finds you well. Did you know that we offer affordable \n and flexible loan options to help you achieve your financial goals? Whether \n you're looking to fund a home renovation, consolidate debt, or make a major \n purchase, our loan options can help you get there. 
         \n Apply today and get approved in just a few simple steps. \n 
         Visit our website.\n
         Bharat Loan (DEVMUNI FINANCE)";
        }
        
        $apiUrl = $apiConfig["ApiUrl"];
        $sms_username = urlencode($apiConfig["username"]);
        $sms_password = urlencode($apiConfig["password"]);
        $sms_type = $apiConfig["type"];
        $dlr = $apiConfig["dlr"];
        $sms_entityid = $apiConfig["entityid"];
        $message = urlencode($input_message);

        $apiData = "username=$sms_username&password=$sms_password&type=$sms_type&dlr=$dlr&destination=$mobile&source=$source&message=$message&entityid=$sms_entityid&tempid=$template_id";

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $apiData
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

            $apiResponseData = explode(":", $apiResponseJson);

            if (!empty($apiResponseData)) {

                $apiResponseData = $apiResponseData[1];

                if (!empty($apiResponseData)) {
                    $apiStatusId = 1;
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
    $insertApiLog['sms_provider'] = 1;
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

    if ($debug) {
        $response_array['request_json'] = $apiData;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $apiResponseJson;
}

?>
