
<?php

function bureau_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(2, "CRIF API started | $lead_id");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GET_BUREAU_SCORE" => 1,
		"GET_BUREAU_JSON_SCORE" => 2
    );

    $method_id = $opertion_array[$method_name];
    if ($method_id == 1) {
        $responseArray = crif_bureau_api_call($lead_id,$request_array);
    }else if ($method_id == 2) {
        $responseArray = crif_bureau_json_api_call($lead_id,$request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    return $responseArray;
}

function crif_bureau_api_call($lead_id = 0, $request_array = array()) {

    ini_set('max_execution_time', 3600);
    ini_set("memory_limit", "1024M");

    common_log_writer(2, "crif_inquiry_agent_request started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $customer_id = "";
    $lead_status_id = "";
    $cibil_score = "";
    $cibil_html = "";

    $apiStatusId = 0;
    $apiRequestXml = "";
    $apiResponseXml = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "CRIF_CALL";
    $sub_type = "REQUEST_INIT";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $leadModelObj = new LeadModel();

    $REQ_VOL_TYP = "C01";
    $REQ_ACTN_TYP = "SUBMIT";
    $AUTH_FLG = "Y";
    $AUTH_TITLE = "USER";
    $RES_FRMT = "XML/HTML";
    $MEMBER_PRE_OVERRIDE = "N";
    $RES_FRMT_EMBD = "Y";
    $LOS_NAME = "abc";
    $LOS_VENDER = "cde";
    $LOS_VERSION = "1.0";
    $MFI_INDV = "true";
    $MFI_SCORE = "false";
    $MFI_GROUP = "true";

    $CONSUMER_INDV = "true";
    $CONSUMER_SCORE = "true";
    $IOI = "true";

    $INQUIRY_UNIQUE_REF_NO = "";
    $CREDT_RPT_ID = "345";
    $CREDT_REQ_TYP = "INDV";
    $CREDT_RPT_TRN_ID = "";
    $CREDT_INQ_PURPS_TYP = "ACCT-ORIG";
    $CREDT_INQ_PURPS_TYP_DESC = "A12";
    $CREDIT_INQUIRY_STAGE = "PRE-DISB";
    $KENDRA_ID = "1234";
    $BRANCH_ID = "3008";

    $APPLICATION_DATETIME = "";
    $LOAN_AMOUNT = "";
    $TEST_FLG = "";
    $NAME1 = $NAME2 = $NAME3 = $NAME4 = "";
    $DOB_DATE = $AGE_AS_ON = $AGE = "";

    $ID_TYPE_PAN = "N";
    $ID_TYPE_DL = "N";
    $ID_TYPE_PP = "N";
    $PAN_VALUE = "";
    $PP_VALUE = "";
    $DL_VALUE = "";

    $ADDR_TYPE_PERMANENT = "N";
    $PER_ADDR_VALUE = "";
    $PER_ADDR_CITY = "";
    $PER_ADDR_STATE = "";
    $PER_ADDR_PINCODE = "";

    $ADDR_TYPE_RESIDENCE = "N";
    $RES_ADDR_CITY = "";
    $RES_ADDR_STATE = "";
    $RES_ADDR_PINCODE = "";
    $RES_ADDR_VALUE = "";

    //RELATIONS
    $REL_TYPE_FATHER = "N";
    $REL_TYPE_MOTHER = "N";
    $REL_TYPE_SPOUSE = "N";

    $FATHER_VALUE = "";
    $MOTHER_VALUE = "";
    $SPOUSE_VALUE = "";

    //PHONES
    $PHONE_TYPE_MOBILE = "N";
    $PHONE_TYPE_COMPANY = "N";
    $MOBILE_VALUE = "";

    $EMAIL = "";
    $GENDER = "";

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

        $apiUserId = $apiConfig["ApiUserId"];
        $apiPassword = $apiConfig["ApiPassword"];
        $apiMemberId = $apiConfig["ApiMemberId"];
        $apiSubMemberId = $apiConfig["ApiSubMemberId"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];
        // print_r($app_data);
        // exit;
        $application_no = $app_data['application_no'];

        $customer_id = !empty($app_data['customer_id']) ? $app_data['customer_id'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $INQUIRY_UNIQUE_REF_NO = date("YmdHis") . $apiMemberId . rand(100, 999);

        $CREDT_RPT_TRN_ID = "BLUAT" . date("YmdHis") . rand(100, 999);

        if ($envSet == 'production') {
            $CREDT_RPT_TRN_ID = "BLPRD" . date("YmdHis") . rand(100, 999);
        }

        $APPLICATION_DATETIME = date('d-m-Y H:i:s');
        $TEST_FLG = ($envSet == 'production') ? 'HMLIVE' : 'HMTEST';
        $LOAN_AMOUNT = round($app_data['loan_amount'], 0);

        $NAME1 = $app_data['first_name'];

        if (!empty($app_data['middle_name'])) {
            $NAME2 = $app_data["middle_name"];
        }
        $NAME3 = !empty($app_data['sur_name']) ? $app_data['sur_name'] : "";

        $DOB_DATE = date('d-m-Y', strtotime($app_data['dob']));
        $AGE_AS_ON = date('d-m-Y');
        $AGE = (strtotime($AGE_AS_ON) - strtotime($DOB_DATE));
        $AGE = intval(($AGE / (60 * 60 * 24 * 365.25)));

        //IDS


        if (!empty($app_data['pancard'])) {
            $ID_TYPE_PAN = "Y";
            $PAN_VALUE = $app_data['pancard'];
        }


        if (!empty($app_data['mobile'])) {
            $PHONE_TYPE_MOBILE = "Y";
            $MOBILE_VALUE = $app_data['mobile'];
        }


        $EMAIL = $app_data['email'];

        $GENDER = ""; //G03
        if (!empty($app_data['gender'])) {
            $gender = strtoupper($app_data['gender']);

            if ($gender == "MALE") {
                $GENDER = "G01";
            } else if ($gender == "FEMALE") {
                $GENDER = "G02";
            }
        }

        //ADDRESSES
        //RESIDENCE ADDR
        if (!empty($app_data['current_house']) || in_array($lead_status_id, array(2, 3))) {
            $ADDR_TYPE_RESIDENCE = "Y";
            $RES_ADDR_VALUE = $app_data['current_house'];

            if (!empty($app_data['current_locality'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_locality'];
            }

            if (!empty($app_data['current_landmark'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_landmark'];
            }

            $res_city_id = $app_data['city_id'];

            $res_city_res = $leadModelObj->getCityDetails($res_city_id);

            if ($res_city_res['status'] == 1) {
                $res_city_details = $res_city_res['city_data'];
                $RES_ADDR_CITY = $res_city_details["m_city_name"];
            }

            $res_state_id = $app_data["state_id"];

            $res_state_res = $leadModelObj->getStateDetails($res_state_id);

            if ($res_state_res['status'] == 1) {
                $res_state_details = $res_state_res['state_data'];
                //                $RES_ADDR_STATE = $res_state_details["m_state_name"];
                $RES_ADDR_STATE = $res_state_details["m_state_code"];
            }

            $RES_ADDR_PINCODE = $app_data['cr_residence_pincode'];
            if (in_array($lead_status_id, array(2, 3))) {
                $RES_ADDR_VALUE = $RES_ADDR_CITY . " " . $res_state_details["m_state_name"];
            }

            $RES_ADDR_VALUE = trim($RES_ADDR_VALUE);
        }



        //PERMANENT ADDR
        if (!empty($app_data['aa_current_house'])) {
            $ADDR_TYPE_PERMANENT = "Y";

            $PER_ADDR_VALUE = $app_data['aa_current_house'];

            if (!empty($app_data['aa_current_locality'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_locality'];
            }
            if (!empty($app_data['aa_current_landmark'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_landmark'];
            }

            $perm_city_id = $app_data['aa_current_city_id'];

            $per_city_res = $leadModelObj->getCityDetails($perm_city_id);
            if ($per_city_res['status'] == 1) {
                $per_city_details = $per_city_res['city_data'];
                $PER_ADDR_CITY = $per_city_details["m_city_name"];
            }

            $perm_state_id = $app_data["aa_current_state_id"];

            $per_state_res = $leadModelObj->getStateDetails($perm_state_id);

            if ($per_state_res['status'] == 1) {
                $per_state_details = $per_state_res['state_data'];
                //                $PER_ADDR_STATE = $per_state_details["m_state_name"];
                $PER_ADDR_STATE = $per_state_details["m_state_code"];
            }

            $PER_ADDR_PINCODE = $app_data['aa_cr_residence_pincode'];
        }


        if (empty($NAME1) || $ID_TYPE_PAN != "Y" || $PHONE_TYPE_MOBILE != "Y" || empty($GENDER) || empty($RES_ADDR_VALUE) || empty($RES_ADDR_CITY) || empty($RES_ADDR_STATE) || empty($RES_ADDR_PINCODE)) {
            throw new Exception("Missing mandatory fields to call bureau api.");
        }


        $apiRequestXml = '<?xml version="1.0" encoding="UTF-8"?>
				<REQUEST-REQUEST-FILE>
                                    <HEADER-SEGMENT>
                                        <SUB-MBR-ID>' . $apiSubMemberId . '</SUB-MBR-ID>
                                        <INQ-DT-TM>' . $APPLICATION_DATETIME . '</INQ-DT-TM>
                                        <REQ-VOL-TYP>' . $REQ_VOL_TYP . '</REQ-VOL-TYP>
                                        <REQ-ACTN-TYP>' . $REQ_ACTN_TYP . '</REQ-ACTN-TYP>
                                        <TEST-FLG>' . $TEST_FLG . '</TEST-FLG>
                                        <AUTH-FLG>' . $AUTH_FLG . '</AUTH-FLG>
                                        <AUTH-TITLE>' . $AUTH_TITLE . '</AUTH-TITLE>
                                        <RES-FRMT>' . $RES_FRMT . '</RES-FRMT>
                                        <MEMBER-PRE-OVERRIDE>' . $MEMBER_PRE_OVERRIDE . '</MEMBER-PRE-OVERRIDE>
                                        <RES-FRMT-EMBD>' . $RES_FRMT_EMBD . '</RES-FRMT-EMBD>
                                        <LOS-NAME>' . $LOS_NAME . '</LOS-NAME>
                                        <LOS-VENDER>' . $LOS_VENDER . '</LOS-VENDER>
                                        <LOS-VERSION>' . $LOS_VERSION . '</LOS-VERSION>
                                        <MFI>
                                            <INDV>' . $MFI_INDV . '</INDV>
                                            <SCORE>' . $MFI_SCORE . '</SCORE>
                                            <GROUP>' . $MFI_GROUP . '</GROUP>
                                        </MFI>
                                        <CONSUMER>
                                            <INDV>' . $CONSUMER_INDV . '</INDV>
                                            <SCORE>' . $CONSUMER_SCORE . '</SCORE>
                                        </CONSUMER>
                                        <IOI>' . $IOI . '</IOI>
                                    </HEADER-SEGMENT>
                                    <INQUIRY>
                                        <APPLICANT-SEGMENT>
                                        <APPLICANT-ID>' . $application_no . '</APPLICANT-ID>
                                            <APPLICANT-NAME>
                                              <NAME1>' . $NAME1 . '</NAME1>
                                              <NAME2>' . $NAME2 . '</NAME2>
                                              <NAME3>' . $NAME3 . '</NAME3>
                                              <NAME4 />
                                              <NAME5 />
                                            </APPLICANT-NAME>
                                            <DOB>
                                              <DOB-DATE>' . $DOB_DATE . '</DOB-DATE>
                                              <AGE>' . $AGE . '</AGE>
                                              <AGE-AS-ON>' . $AGE_AS_ON . '</AGE-AS-ON>
                                            </DOB>
                                            <IDS>';
        if ($ID_TYPE_PAN == "Y") {
            $apiRequestXml .= '<ID>
                                                        <TYPE>ID07</TYPE>
                                                        <VALUE>' . $PAN_VALUE . '</VALUE>
                                                  </ID>';
        }

        $apiRequestXml .= '</IDS>
                                            <RELATIONS>';
        if ($REL_TYPE_FATHER == "Y") {
            $apiRequestXml .= '<RELATION>
                                                    <TYPE>K01</TYPE>
                                                    <VALUE>' . $FATHER_VALUE . '</VALUE>
                                              </RELATION>';
        }

        $apiRequestXml .= '</RELATIONS>
                                            <PHONES>';
        if ($PHONE_TYPE_MOBILE == "Y") {
            $apiRequestXml .= '<PHONE>
                                                    <TELE-NO-TYPE>P03</TELE-NO-TYPE>
                                                    <TELE-NO>' . $MOBILE_VALUE . '</TELE-NO>
                                              </PHONE>';
        }
        if ($PHONE_TYPE_COMPANY == "Y") {
            $apiRequestXml .= '<PHONE>
                                                    <TELE-NO-TYPE>P07</TELE-NO-TYPE>
                                                    <TELE-NO></TELE-NO>
                                            </PHONE>';
        }
        $apiRequestXml .= '</PHONES>';
        $apiRequestXml .= '<GENDER>' . $GENDER . '</GENDER>';
        $apiRequestXml .= '<EMAILS><EMAIL>' . $EMAIL . '</EMAIL></EMAILS>';
        $apiRequestXml .= '</APPLICANT-SEGMENT>';

        $apiRequestXml .= '<ADDRESS-SEGMENT>';

        if ($ADDR_TYPE_RESIDENCE == "Y") {
            $apiRequestXml .= '<ADDRESS>
                                                    <TYPE>D01</TYPE>
                                                    <ADDRESS-1>' . substr($RES_ADDR_VALUE, 0, 145) . '</ADDRESS-1>
                                                    <CITY>' . $RES_ADDR_CITY . '</CITY>
                                                    <STATE>' . $RES_ADDR_STATE . '</STATE>
                                                    <PIN>' . $RES_ADDR_PINCODE . '</PIN>
                                              </ADDRESS>';
        }
        if ($ADDR_TYPE_PERMANENT == "Y") {
            $apiRequestXml .= '<ADDRESS>
                                                    <TYPE>D04</TYPE>
                                                    <ADDRESS-1>' . substr($PER_ADDR_VALUE, 0, 145) . '</ADDRESS-1>
                                                    <CITY>' . $PER_ADDR_CITY . '</CITY>
                                                    <STATE>' . $PER_ADDR_STATE . '</STATE>
                                                    <PIN>' . $PER_ADDR_PINCODE . '</PIN>
                                              </ADDRESS>';
        }
        $apiRequestXml .= '</ADDRESS-SEGMENT>';
        $apiRequestXml .= '<APPLICATION-SEGMENT>
            <INQUIRY-UNIQUE-REF-NO>' . $INQUIRY_UNIQUE_REF_NO . '</INQUIRY-UNIQUE-REF-NO>
            <CREDT-RPT-ID>' . $CREDT_RPT_ID . '</CREDT-RPT-ID>
            <CREDT-REQ-TYP>' . $CREDT_REQ_TYP . '</CREDT-REQ-TYP>
            <CREDT-RPT-TRN-ID>' . $CREDT_RPT_TRN_ID . '</CREDT-RPT-TRN-ID>
            <CREDT-INQ-PURPS-TYP>' . $CREDT_INQ_PURPS_TYP . '</CREDT-INQ-PURPS-TYP>
            <CREDT-INQ-PURPS-TYP-DESC>' . $CREDT_INQ_PURPS_TYP_DESC . '</CREDT-INQ-PURPS-TYP-DESC>
            <CREDIT-INQUIRY-STAGE>' . $CREDIT_INQUIRY_STAGE . '</CREDIT-INQUIRY-STAGE>
            <CREDT-RPT-TRN-DT-TM>' . $APPLICATION_DATETIME . '</CREDT-RPT-TRN-DT-TM>
            <MBR-ID>' . $apiMemberId . '</MBR-ID>
            <KENDRA-ID>' . $KENDRA_ID . '</KENDRA-ID>
            <BRANCH-ID>' . $BRANCH_ID . '</BRANCH-ID>
            <LOS-APP-ID>' . $lead_id . '</LOS-APP-ID>
            <LOAN-AMOUNT>' . $LOAN_AMOUNT . '</LOAN-AMOUNT>';
        $apiRequestXml .= '</APPLICATION-SEGMENT>';
        $apiRequestXml .= '</INQUIRY>
				</REQUEST-REQUEST-FILE>';

        
        if ($debug) {
            echo "<br/><br/>=======Request XML=========<br/><br/>";
            echo $apiRequestXml;
        }

        // $apiHeaders = array();
        // $apiHeaders[] = "content-type: application/xml";
        // $apiHeaders[] = "requestXml: " . trim(preg_replace("/\s+/", " ", $apiRequestXml));
        // $apiHeaders[] = "userId: " . $apiUserId;
        // $apiHeaders[] = "password: " . $apiPassword;
        // $apiHeaders[] = "mbrid: " . $apiMemberId;
        // $apiHeaders[] = "productType: INDV";
        // $apiHeaders[] = "productVersion: 1.0";
        // $apiHeaders[] = "reqVolType: INDV";



        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            //            echo json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $cibil_details = $leadModelObj->getCibilData($lead_id);

        // 	 print_r($cibil_details); die;

        if ($cibil_details['status'] == 1) {
            $apiResponseXml = $cibil_details['cibil_data'][0]['response'];
        }

        $test = $apiRequestXml;
        
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://13.127.42.99:3000/api/convert',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $test, // Sending data in the body
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/xml',
                // 'requestXml: ' . $test . ''
            ),
        ));
        
        $apiResponseXml = curl_exec($curl);

        curl_close($curl);


        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response XML=========<br/><br/>";
            echo $apiResponseXml;
        }
        
        $apiResponseDateTime = date("Y-m-d H:i:s");

        // if (!$hardcode_response && curl_errno($curl)) { // CURL Error
        //     $curlError = curl_error($curl);
        //     curl_close($curl);
        //     throw new RuntimeException("Something went wrong. Please try after sometimes.");
        // } else {
        //     if (!$hardcode_response) {
        //         curl_close($curl);
        //     }

        if (!empty($apiResponseXml)) {

            $tempApiResponseXml = $apiResponseXml;
            // $cibil_html = common_extract_value_from_xml('<CONTENT><![CDATA[', ']]></CONTENT>', $tempApiResponseXml);
            // $cibil_h = base64_decode('<CONTENT>', $tempApiResponseXml);
            // $cibil_html = base64_decode($cibil_h);
            
            // echo $cibil_h;
            // exit();
            $apiResponseXml = str_replace($cibil_html, '', $apiResponseXml);
            $tempApiResponseXml = str_replace($cibil_html, '', $tempApiResponseXml);
            

            if (strpos(trim($tempApiResponseXml), '<STATUS>') !== false) {
                $temp_xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $tempApiResponseXml);
                $temp_xml = @simplexml_load_string($temp_xml);
                $temp_json = @json_encode($temp_xml);
                $temp_array = @json_decode($temp_json, true);
                $apiResponseJson = json_encode($temp_xml);
                if ($debug) {
                    echo "<br/><br/><br/><br/><br/><br/>=======Response Array=========<br/><br/>";
                    print_r($temp_array);
                }
                // print_r($temp_array);
                // exit;
                $cibil_printable_response = !empty($temp_array['CIR-REPORT-FILE']['PRINTABLE-REPORT']) ? $temp_array['CIR-REPORT-FILE']['PRINTABLE-REPORT'] : [];
                $cibil_html = base64_decode($cibil_printable_response['CONTENT']);
                // echo $cibil_html;
                // exit();
                
                $header_response_array = !empty($temp_array['CIR-REPORT-FILE']['HEADER-SEGMENT']) ? $temp_array['CIR-REPORT-FILE']['HEADER-SEGMENT'] : [];
$status_response_array = !empty($temp_array['CIR-REPORT-FILE']['REQUEST-STATUS']) ? $temp_array['CIR-REPORT-FILE']['REQUEST-STATUS'] : [];
$accounts_summary_response_array = !empty($temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']) ? $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY'] : [];
$alerts_response_array = !empty($temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ALERTS']) ? $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ALERTS'] : [];
$scores_response_array = !empty($temp_array['CIR-REPORT-FILE']['REPORT-DATA']['STANDARD-DATA']['SCORE']) ? $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['STANDARD-DATA']['SCORE'] : [];
$inquiry_history_response_array = !empty($temp_array['CIR-REPORT-FILE']['REPORT-DATA']['STANDARD-DATA']['INQUIRY-HISTORY']) ? $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['STANDARD-DATA']['INQUIRY-HISTORY'] : [];
$responses_response_array = !empty($temp_array['CIR-REPORT-FILE']['REPORT-DATA']) ? $temp_array['CIR-REPORT-FILE']['REPORT-DATA'] : [];
$indv_responses_response_array = !empty($temp_array['CIR-REPORT-FILE']['INDV-RESPONSES']) ? $temp_array['CIR-REPORT-FILE']['INDV-RESPONSES'] : [];
$grp_responses_response_array = !empty($temp_array['CIR-REPORT-FILE']['GRP-RESPONSES']) ? $temp_array['CIR-REPORT-FILE']['GRP-RESPONSES'] : [];

if (isset($status_response_array[0]['TYPE']) && !empty($status_response_array[0]['TYPE']) && $status_response_array[0]['TYPE'] == "CIR" && !empty($status_response_array[0]['STATUS']) && $status_response_array[0]['STATUS'] == "SUCCESS") {
    $apiStatusId = 1;
    if (isset($scores_response_array['NAME']) && !empty($scores_response_array['NAME']) && trim(strtoupper($scores_response_array['NAME'])) == "PERFORM CONSUMER 2.2") {
        $cibil_score = $scores_response_array['VALUE'];
    }
} else {
    $tmp_error_msg = "NO SCORE FOUND IN BUREAU. PLEASE CHECK THE REPORT.";
    throw new ErrorException($tmp_error_msg);
}
            } else {
                throw new ErrorException("Please check raw response for error details");
            }
        } else {
            throw new ErrorException("Empty response from CRIF API");
        }
        //       }
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

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['cibil_score'] = $cibil_score;
    $response_array['cibil_html'] = addslashes($cibil_html);
    $response_array['errors'] = $errorMessage;
    $response_array['request_xml'] = $apiRequestXml;
    $response_array['response_xml'] = $apiResponseXml;
    $response_array['response_json'] = $apiResponseJson;

    // print_r($response_array['cibil_html']);
    // exit();

    if (!empty($lead_id)) {
        if ($apiStatusId == 1) {
            $lead_remarks = "CRIF API CALL(Success) | Score : " . $cibil_score;
        } else {
            $lead_remarks = "CRIF API CALL(Failed) | Error : " . $errorMessage;
        }
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
    }

    $totalAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['NUMBER-OF-ACCOUNTS'];
    $overDueAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['OVERDUE-ACCOUNTS'];
    $overDueAmount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-AMT-OVERDUE'];
    $totalBalance = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-CURRENT-BALANCE'];
    $totalBalance = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-CURRENT-BALANCE'];
    $highCrSanAmt = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-SANCTIONED-AMT'];
    $activeAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['ACTIVE-ACCOUNTS'];
    $zeroBalance = $totalAccount - $activeAccount;

    
    if ($apiStatusId == 1) {

        $cibil_data = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'cibil_pancard' => $PAN_VALUE,
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'totalAccount' => $totalAccount,
            'totalBalance' => $totalBalance,
            'highCrSanAmt' => $highCrSanAmt,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance,
            // 'activeAccount' => $activeAccount,
            'cibil_file' => addslashes($cibil_html),
            'applicationId' => !empty($header_response_array['REPORT-ID']) ? $header_response_array['REPORT-ID'] : "",
            'cibil_created_by' => !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0,
            'created_at' => date("Y-m-d H:i:s"),
        ];

        // print_r($cibil_data);
        // exit();

        $leadModelObj->insertTable('tbl_cibil', $cibil_data);
    }

    if ($apiStatusId == 1 || $apiStatusId == 2) {
        $cibil_log = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'customer_name' => implode(" ", array($NAME1, $NAME2, $NAME3)),
            'customer_mobile' => $MOBILE_VALUE,
            'pancard' => $PAN_VALUE,
            'loan_amount' => $LOAN_AMOUNT,
            'dob' => $DOB_DATE,
            'gender' => $gender,
            'customer_email' => $EMAIL,
            'city' => $RES_ADDR_CITY,
            'state_id' => $res_state_id,
            'pincode' => $RES_ADDR_PINCODE,
            'totalAccount' => $totalAccount,
            'totalBalance' => $totalBalance,
            'highCrSanAmt' => $highCrSanAmt,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance,
            // 'activeAccount' => $activeAccount,
            'api1_request' => addslashes($apiRequestXml),
            'api1_response' => addslashes($apiResponseXml),
            //            'api2_response' => addslashes($apiResponseJson),
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'cibil_file' => addslashes($cibil_html),
            
            //            'applicationId' => !empty($header_response_array['REPORT-ID']) ? $header_response_array['REPORT-ID'] : "",
        ];
        // print_r($cibil_log);
        // exit();
        $leadModelObj->insertTable('tbl_cibil_log', $cibil_log);
    }

    if ($apiStatusId == 1 && isset($cibil_score)) {
        $leadModelObj->updateLeadTable($lead_id, ['check_cibil_status' => 1, 'cibil' => $cibil_score]);
    }

    return $response_array;
}

function crif_bureau_json_api_call($lead_id = 0, $request_array = array()) {
	ini_set('max_execution_time', 3600);
    ini_set("memory_limit", "1024M");

    common_log_writer(2, "crif_inquiry_json_agent_request started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "", "cibil_score" => "");

    //INIT VAR(s)

    $envSet = COMP_ENVIRONMENT;
    $customer_id = "";
    $lead_status_id = "";
    $cibil_score = "";
    $cibil_html = "";

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "CRIF_CALL";
    $sub_type = "REQUEST_JSON";

    $hardcode_response = false;
    //if($envSet == 'production') {
    //   $hardcode_response = true;
    //}

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";

    $leadModelObj = new LeadModel();   

    try {

        $apiConfig = integration_config($type,$sub_type);        
        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }
        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];

        $apiUserId = $apiConfig["ApiUserId"];
        $apiPassword = $apiConfig["ApiPassword"];
        $apiMemberId = $apiConfig["ApiMemberId"];
        $apiSubMemberId = $apiConfig["ApiSubMemberId"];

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];
		

        $customer_id = !empty($app_data['customer_id']) ? $app_data['customer_id'] : "";
        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $INQUIRY_UNIQUE_REF_NO = date("YmdHis") . $apiMemberId . rand(100, 999);

        $CREDT_RPT_TRN_ID = "BLUAT" . date("YmdHis") . rand(100, 999);

        if ($envSet == 'production') {
            $CREDT_RPT_TRN_ID = "BLPRD" . date("YmdHis") . rand(100, 999);
        }

        $APPLICATION_DATETIME = date('d-m-Y H:i:s');
        $TEST_FLG = ($envSet == 'production') ? 'HMLIVE' : 'HMTEST';
        $LOAN_AMOUNT = round($app_data['loan_amount'], 0);

        $NAME1 = $app_data['first_name'];

        if (!empty($app_data['middle_name'])) {
            $NAME2 = $app_data["middle_name"];
        }
        $NAME3 = !empty($app_data['sur_name']) ? $app_data['sur_name'] : "";	
		

        $DOB_DATE = date('d-m-Y', strtotime($app_data['dob']));
        $AGE_AS_ON = date('d-m-Y');
        $AGE = (strtotime($AGE_AS_ON) - strtotime($DOB_DATE));
        $AGE = intval(($AGE / (60 * 60 * 24 * 365.25)));
        
        //IDS

        if (!empty($app_data['pancard'])) {
            $ID_TYPE_PAN = "ID07";
            $PAN_VALUE = $app_data['pancard'];
        }

        if (!empty($app_data['mobile'])) {
            $PHONE_TYPE_MOBILE = "P04";
            $MOBILE_VALUE = $app_data['mobile'];
        }
        
		if (!empty($app_data['father_name'])) {
            $father_type = "K01";
            $father_name = $app_data['father_name'];
        }

        $EMAIL = $app_data['email'];

        $GENDER = "";
        if (!empty($app_data['gender'])) {
            $gender = strtoupper($app_data['gender']);

            if ($gender == "MALE") {
                $GENDER = "G01";
            } else if ($gender == "FEMALE") {
                $GENDER = "G02";
            }
        }

        //ADDRESSES
        //RESIDENCE ADDR
        if (!empty($app_data['current_house']) || in_array($lead_status_id, array(2, 3))) {
            $ADDR_TYPE_RESIDENCE = "Y";
            $RES_ADDR_VALUE = $app_data['current_house'];

            if (!empty($app_data['current_locality'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_locality'];
            }

            if (!empty($app_data['current_landmark'])) {
                $RES_ADDR_VALUE .= " " . $app_data['current_landmark'];
            }

            $res_city_id = $app_data['city_id'];

            $res_city_res = $leadModelObj->getCityDetails($res_city_id);

            if ($res_city_res['status'] == 1) {
                $res_city_details = $res_city_res['city_data'];
                $RES_ADDR_CITY = $res_city_details["m_city_name"];
            }

            $res_state_id = $app_data["state_id"];

            $res_state_res = $leadModelObj->getStateDetails($res_state_id);

            if ($res_state_res['status'] == 1) {
                $res_state_details = $res_state_res['state_data'];
                //$RES_ADDR_STATE = $res_state_details["m_state_name"];
                $RES_ADDR_STATE = $res_state_details["m_state_code"];
            }

            $RES_ADDR_PINCODE = $app_data['cr_residence_pincode'];
            if (in_array($lead_status_id, array(2, 3))) {
                $RES_ADDR_VALUE = $RES_ADDR_CITY . " " . $res_state_details["m_state_name"];
            }

            $RES_ADDR_VALUE = trim($RES_ADDR_VALUE);
        }

        //PERMANENT ADDR
        if (!empty($app_data['aa_current_house'])) {
            $ADDR_TYPE_PERMANENT = "Y";

            $PER_ADDR_VALUE = $app_data['aa_current_house'];

            if (!empty($app_data['aa_current_locality'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_locality'];
            }
            if (!empty($app_data['aa_current_landmark'])) {
                $PER_ADDR_VALUE .= " " . $app_data['aa_current_landmark'];
            }

            $perm_city_id = $app_data['aa_current_city_id'];

            $per_city_res = $leadModelObj->getCityDetails($perm_city_id);
            if ($per_city_res['status'] == 1) {
                $per_city_details = $per_city_res['city_data'];
                $PER_ADDR_CITY = $per_city_details["m_city_name"];
            }

            $perm_state_id = $app_data["aa_current_state_id"];

            $per_state_res = $leadModelObj->getStateDetails($perm_state_id);

            if ($per_state_res['status'] == 1) {
                $per_state_details = $per_state_res['state_data'];
                //                $PER_ADDR_STATE = $per_state_details["m_state_name"];
                $PER_ADDR_STATE = $per_state_details["m_state_code"];
            }

            $PER_ADDR_PINCODE = $app_data['aa_cr_residence_pincode'];
        }

        //echo $NAME1.'@'.$ID_TYPE_PAN.'@'.$PHONE_TYPE_MOBILE.'@'.$GENDER.'@'.$RES_ADDR_VALUE.'@'.$RES_ADDR_CITY.'@'.$RES_ADDR_STATE.'@'.$RES_ADDR_PINCODE;die;

        if (empty($NAME1) || empty($GENDER) || empty($RES_ADDR_VALUE) || empty($RES_ADDR_CITY) || empty($RES_ADDR_STATE) || empty($RES_ADDR_PINCODE)) {
            throw new Exception("Missing mandatory fields to call bureau api.");
        }

		$apiRequestJson = [
		   "REQUEST-FILE" => [
				 "HEADER-SEGMENT" => [
					"PRODUCT-TYPE" => "CIR PRO V2", 
					"PRODUCT-VER" => "2.0", 
					"USER-ID" => $apiUserId, 
					"USER-PWD" => $apiPassword, 
					"REQ-MBR" => $apiMemberId, 
					"INQ-DT-TM" => date("d-m-Y"), 
					"REQ-VOL-TYPE" => "C04", 
					"REQ-ACTN-TYPE" => "AT01", 
					"AUTH-FLG" => "Y", 
					"AUTH-TITLE" => "USER", 
					"RES-FRMT" => "HTML", 
					"MEMBER-PREF-OVERRIDE" => "N", 
					"RES-FRMT-EMBD" => "Y", 
					"LOS-NAME" => "INHOUSE", 
					"LOS-VENDOR" => "", 
					"LOS-VERSION" => "", 
					"REQ-SERVICES-TYPE" => "CIR" 
				 ], 
				 "INQUIRY" => [
					   "APPLICANT-SEGMENT"=> [
						  "APPLICANT-ID"=>"$lead_id", 
						  "FIRST-NAME"=>"$NAME1", 
						  "MIDDLE-NAME"=>"$NAME2", 
						  "LAST-NAME"=>"$NAME3",
                          "GENDER" => "$GENDER",
						  "DOB"=> [
							 "DOB-DT"=>"$DOB_DATE", 
							 "AGE"=>"$AGE", 
							 "AGE-AS-ON"=>"$AGE_AS_ON" 
						  ], 
						  "RELATIONS" => [
								[
								   "TYPE" => "$father_type", 
								   "VALUE" => "$father_name" 
								] 
							 ], 
						  "IDS" => [
									  [
										 "TYPE" => "$ID_TYPE_PAN", 
										 "VALUE" => "$PAN_VALUE" 
									  ] 
								   ], 
						  "ADDRESSES" => [
											[
											   "TYPE" => "D05", 
											   "ADDRESS-TEXT" => "$RES_ADDR_VALUE", 
											   "CITY" => "$RES_ADDR_CITY", 
											   "STATE" => "$RES_ADDR_STATE", 
											   "LOCALITY" => "", 
											   "PIN" => "$RES_ADDR_PINCODE", 
											   "COUNTRY" => "INDIA" 
											] 
										 ], 
						  "PHONES" => [
										  [
											 "TYPE" => "$PHONE_TYPE_MOBILE", 
											 "VALUE" => "$MOBILE_VALUE" 
										  ] 
									   ], 
						  "EMAILS" => [
											[
											   "EMAIL" =>"$EMAIL" 
											] 
										 ], 
						  "ACCOUNT-NUMBER" => "" 
					   ], 
					   "APPLICATION-SEGMENT" => [
						  "INQUIRY-UNIQUE-REF-NO" => "$INQUIRY_UNIQUE_REF_NO", 
						  "CREDIT-RPT-ID" => "$CREDT_RPT_TRN_ID", 
						  "CREDIT-RPT-TRN-DT-TM" => "$APPLICATION_DATETIME", 
						  "CREDIT-INQ-PURPS-TYPE" => "CP04", 
						  "CREDIT-INQUIRY-STAGE" => "PRE-DISB", 
						  "CLIENT-CONTRIBUTOR-ID" => "MFI00", 
						  "APPLICATION-ID" => "$lead_id", 
						  "BRANCH-ID" => "", 
						  "ACNT-OPEN-DT" => "", 
						  "LOAN-AMT" => "$LOAN_AMOUNT", 
						  "LTV" => "12", 
						  "TERM" => "24", 
						  "LOAN-TYPE" => "A01", 
						  "LOAN-TYPE-DESC" => "" 
					   ] 
					] 
			  ] 
		]; 
        
        if($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo json_encode($apiRequestJson);
        }
		
        $apiHeaders = array();
		$apiHeaders[] = 'userId:'.$apiUserId;
		$apiHeaders[] = 'password:'.$apiPassword;
		$apiHeaders[] = 'CUSTOMER-ID:'.$apiMemberId;
		$apiHeaders[] = 'PRODUCT-TYPE: CIR PRO V2';
		$apiHeaders[] = 'PRODUCT-VER: 2.0';
		$apiHeaders[] = 'REQ-VOL-TYPE: C04';
		$apiHeaders[] = 'Content-Type: application/json';
		$apiHeaders[] = 'Cookie: SESSIONID=O3k2YqvVFKfePW4GyXD7ejVOb95t49_0VsSJymFXoJwxmS3EGXKQ!-2033948751!1740374191061; JSESSIONID=O3k2YqvVFKfePW4GyXD7ejVOb95t49_0VsSJymFXoJwxmS3EGXKQ!-2033948751';
        
        if ($debug) {
            echo "<br/><br/>=======Request Header=========<br/><br/>";
            print_r($apiHeaders);
        }
        		
        $apiRequestDateTime = date("Y-m-d H:i:s");
        
        /*$cibil_details = $leadModelObj->getCibilData($lead_id);
        //echo '<pre>';print_r($cibil_details); die;
        if ($cibil_details['status'] == 1) {
            $apiResponseJson = $cibil_details['cibil_data'][0]['response'];
        }
        */	
		
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
		  CURLOPT_POSTFIELDS =>json_encode($apiRequestJson),
		  CURLOPT_HTTPHEADER => $apiHeaders,
		));
		
        $apiResponseJson = curl_exec($curl);        

        curl_close($curl);

        if ($debug) {
            echo "<br/><br/><br/><br/><br/><br/>=======Response XML=========<br/><br/>";
            echo $apiResponseJson;
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (!empty($apiResponseJson)) { 
            // $tempApiResponseJson = $apiResponseJson;
            // $extractFinalResponseJson = extractFinalResponse($tempApiResponseJson);
			// $jsonResponse = $extractFinalResponseJson['jsonResponse'];
			// $htmlResponse = $extractFinalResponseJson['htmlResponse'];			
			// $apiResponseData = json_decode($jsonResponse,true);		
			$apiResponseData = json_decode($apiResponseJson,true);		
            $CIR_REPORT_FILE = $apiResponseData['CIR-REPORT-FILE'];
            $REPORT_DATA = $CIR_REPORT_FILE['REPORT-DATA'];
            $REPORT_ID = $CIR_REPORT_FILE['HEADER-SEGMENT'];
            $STANDARD_DATA = $REPORT_DATA['STANDARD-DATA'];
			$cibil_score = !empty($STANDARD_DATA['SCORE'][0]['VALUE']) ? $STANDARD_DATA['SCORE'][0]['VALUE'] : "0";
            // print_r($htmlResponse);
            // exit();
			/*
			echo '<pre>';
			print_r($STANDARD_DATA); 
			die;
			*/
			// require_once(COMP_PATH . '/CommonComponent.php');
			// $CommonComponent = new CommonComponent();

			// $upload_file = array();
			// $upload_file['flag'] = 1;
			// $upload_file['file'] = $htmlResponse.'.html';
			// $upload_file['ext'] = 'html';

			// $upload_status = $CommonComponent->upload_document($lead_id,$upload_file);
			// echo '<pre>';print_r($upload_status);die;

			// if ($upload_status['status'] == 1) {
			// 	$cibil_html = $upload_status['file_name'];
			// }
            // $cibil_html = $htmlResponse;
            $cibil_html = $apiResponseData['CIR-REPORT-FILE']['PRINTABLE-REPORT']['CONTENT'];
			$apiStatusId = 1;
        } else {
            throw new ErrorException("Empty response from CRIF API");
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

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['cibil_score'] = $cibil_score;
    $response_array['cibil_html'] = $cibil_html;
    $response_array['errors'] = $errorMessage;
    $response_array['request_xml'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;
    $response_array['response_html'] = $cibil_html;

    // Convert JSON to an associative array
    $temp_array = json_decode($response_array['response_json'], true);

    if (!empty($lead_id)) {
        if ($apiStatusId == 1) {
            $lead_remarks = "CRIF API CALL(Success) | Score : " . $cibil_score;
        } else {
            $lead_remarks = "CRIF API CALL(Failed) | Error : " . $errorMessage;
        }
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);
    }
    
    $fileName = $lead_id.'.html';
    $request_array['folderName'] = 'cibil';
    $request_array['fileName'] = $fileName;
    $request_array['htmlContent'] = $cibil_html;
    $request_array['upload_type'] = 'cibil';

    // $foldername = $requestData['folderName'];
    // $fileName = $requestData['fileName'];
    // $path = $foldername . '/' . $fileName;
    // $htmlContent = $requestData['htmlContent'];
    $commonComponent = new CommonComponent();
    $s3Response = $commonComponent->upload_document($lead_id, $request_array);
    // print_r($s3Response);
    // die('yash');
    // $request_darray['file'] = $s3Response['file_name'];
    // $result_array = $commonComponent->download_document($lead_id, $request_darray);
    // // $content_type = $result_array['header_content_type'];
    // // header("Content-Type: {$content_type}");
    // // // echo $result_array['document_body'];
    // // $documentBody = $result_array['document_body'];

    // print_r($result_array);
    // die;

    $totalAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['NUMBER-OF-ACCOUNTS'];
    $overDueAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['OVERDUE-ACCOUNTS'];
    $overDueAmount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-AMT-OVERDUE'];
    $totalBalance = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-CURRENT-BALANCE'];
    $highCrSanAmt = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['TOTAL-SANCTIONED-AMT'];
    $activeAccount = $temp_array['CIR-REPORT-FILE']['REPORT-DATA']['ACCOUNTS-SUMMARY']['PRIMARY-ACCOUNTS-SUMMARY']['ACTIVE-ACCOUNTS'];
    $zeroBalance = $totalAccount - $activeAccount;

    if ($apiStatusId == 1) {

        $cibil_data = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'cibil_pancard' => $PAN_VALUE,
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'totalAccount' => $totalAccount,
            'totalBalance' => $totalBalance,
            'highCrSanAmt' => $highCrSanAmt,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance,
            'cibil_file' => $fileName,
            'applicationId' => $REPORT_ID['REPORT-ID'],
            'cibil_created_by' => !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0,
            'created_at' => date("Y-m-d H:i:s"),
            's3_flag' => 1
        ];

        $leadModelObj->insertTable('tbl_cibil', $cibil_data);
    }

    if ($apiStatusId == 1 || $apiStatusId == 2) {
        $cibil_log = [
            'cibil_bureau_type' => 2, //CRIF
            'lead_id' => $lead_id,
            'customer_id' => $customer_id,
            'customer_name' => implode(" ", array($NAME1, $NAME2, $NAME3)),
            'customer_mobile' => $MOBILE_VALUE,
            'pancard' => $PAN_VALUE,
            'loan_amount' => $LOAN_AMOUNT,
            'dob' => $DOB_DATE,
            'gender' => $gender,
            'customer_email' => $EMAIL,
            'city' => $RES_ADDR_CITY,
            'state_id' => $res_state_id,
            'pincode' => $RES_ADDR_PINCODE,
            'totalAccount' => $totalAccount,
            'totalBalance' => $totalBalance,
            'highCrSanAmt' => $highCrSanAmt,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance,
            'api1_request' => addslashes($apiRequestJson),
            'api1_response' => addslashes($jsonResponse),
            'memberCode' => $apiMemberId,
            'cibilScore' => $cibil_score,
            'cibil_file' => $fileName,//addslashes($cibil_html),
            's3_flag' => 1
        ];
        $leadModelObj->insertTable('tbl_cibil_log', $cibil_log);
    }

    if ($apiStatusId == 1 && isset($cibil_score)) {
        $leadModelObj->updateLeadTable($lead_id, ['check_cibil_status' => 1, 'cibil' => $cibil_score]);
    }

    return $response_array;
}

function extractFinalResponse($apiResponseJson) {		
		$extractedInfo = explode('}<!DOCTYPE', $apiResponseJson);
		$extractedInfo[1] = "<!DOCTYPE".$extractedInfo[1];
		return ["jsonResponse"=> $extractedInfo[0]."}","htmlResponse"=>$extractedInfo[1]];
	}

?>
