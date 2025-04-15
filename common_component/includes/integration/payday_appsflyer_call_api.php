<?php

$blackListedPartners = array("ORGANIC", "kinstaagenin699");

function payday_appsflyer_campaign_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    $responseArray = array("status" => 0, "error_msg" => "");

    $method_aray = array(
        "PULL_ARD_ORGANIC" => 1,
        "PULL_IOS_ORGANIC" => 2,
        "PULL_ARD_NON_ORGANIC" => 3,
        "PULL_IOS_NON_ORGANIC" => 4,
        "EVENT_PUSH_CALL" => 5
    );

    $method_id = $method_aray[$method_name];
    if ($method_id == 1) {
        $responseArray = pull_appsflyer_android_organic_data($method_id, $request_array);
    } elseif ($method_id == 2) {
        $responseArray = pull_appsflyer_ios_organic_data($method_id, $request_array);
    } else if ($method_id == 3) {
        $responseArray = pull_appsflyer_android_non_organic_data($method_id, $request_array);
    } elseif ($method_id == 4) {
        $responseArray = pull_appsflyer_ios_non_organic_data($method_id, $request_array);
    } elseif ($method_id == 5) {
        $responseArray = appsflyer_push_event_api($method_id, $lead_id, $request_array);
    } else {
        $responseArray = "Unknown method called";
    }

    return $responseArray;
}

function pull_appsflyer_ios_organic_data($method_id, $request_array = array()) {

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $fromDate = date("Y-m-d");
    $toDate = date("Y-m-d");

    $type = "APPS_FLYER";
    $sub_type = "PULL_ORGANIC";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['rptest']) ? 1 : 0;
    $debug = 1;

    $leadModelObj = new LeadModel();

    $apiResponseData = array();

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (!empty($request_array['from_date'])) {
            $fromDate = $request_array['from_date'];
        }

        if (!empty($request_array['to_date'])) {
            $toDate = $request_array['to_date'];
        }

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('app_id', "id6465577336", $apiConfig["ApiUrl"]);
        $apiToken = $apiConfig["ApiKey"];

        $apiUrl .= "from=" . date("Y-m-d", strtotime($fromDate)) . "&to=" . date("Y-m-d", strtotime($toDate));

        if (!empty($request_array['event_name'])) {
            $apiUrl .= "&event_name=" . $request_array['event_name'];
        }

        $apiHeaders = array(
            "accept: text/csv",
            "Authorization: Bearer " . $apiToken
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======URL Plain======<br/><br/>" . $apiUrl;
        }

        if ($hardcode_response) {

            $apiResponseRaw = 'Attributed Touch Type,Attributed Touch Time,Install Time,Event Time,Event Name,Event Value,Event Revenue,Event Revenue Currency,Event Revenue USD,Event Source,Is Receipt Validated,Partner,Media Source,Channel,Keywords,Campaign,Campaign ID,Adset,Adset ID,Ad,Ad ID,Ad Type,Site ID,Sub Site ID,Sub Param 1,Sub Param 2,Sub Param 3,Sub Param 4,Sub Param 5,Cost Model,Cost Value,Cost Currency,Contributor 1 Partner,Contributor 1 Media Source,Contributor 1 Campaign,Contributor 1 Touch Type,Contributor 1 Touch Time,Contributor 2 Partner,Contributor 2 Media Source,Contributor 2 Campaign,Contributor 2 Touch Type,Contributor 2 Touch Time,Contributor 3 Partner,Contributor 3 Media Source,Contributor 3 Campaign,Contributor 3 Touch Type,Contributor 3 Touch Time,Region,Country Code,State,City,Postal Code,DMA,IP,WIFI,Operator,Carrier,Language,AppsFlyer ID,Advertising ID,IDFA,Android ID,Customer User ID,IMEI,IDFV,Platform,Device Type,OS Version,App Version,SDK Version,App ID,App Name,Bundle ID,Is Retargeting,Retargeting Conversion Type,Attribution Lookback,Reengagement Window,Is Primary Attribution,User Agent,HTTP Referrer,Original URL
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,dashboard_visit,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,login_otp_verified,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:01:54,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:54,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:48,loan_eligibility_total,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:47,loan_eligibility_failed,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:41,registration_preview,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:38,dashboard_registration_click,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,login_otp_verified,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:22,login_otp_send,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:10,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,';
        } else {

            $curl = curl_init($apiUrl);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30000000);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);

            $apiResponseRaw = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseRaw;
        }

        if (!$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseRaw)) {

                if (!empty($apiResponseRaw)) {

                    if (!isset($apiResponseRaw['error']) && empty($apiResponseRaw['error'])) {
                        $apiStatusId = 1;
                        $lines = explode("\n", trim($apiResponseRaw));
                        $headersData = str_getcsv(array_shift($lines));
                        foreach ($lines as $line) {
                            $row = str_getcsv($line);
                            $apiResponseData[] = array_combine($headersData, $row);
                        }
                    } else if (isset($apiResponseRaw['error']) && $apiResponseRaw['STATUS'] == "CL003") {
                        throw new ErrorException(json_encode($apiResponseRaw['error']));
                    }
                } else {
                    $temp_error = !empty(json_encode($apiResponseRaw['error'])) ? json_encode($apiResponseRaw['error']) : "Some error occurred. Please try again.";
                    throw new ErrorException(json_encode($temp_error));
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
    $insertApiLog["ad_method_id"] = $method_id;
    $insertApiLog["ad_request"] = $apiRequestJson;
    $insertApiLog["ad_response"] = json_encode($apiResponseData);
    $insertApiLog["ad_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ad_response_datetime"] = $apiResponseDateTime;
    $insertApiLog["ad_api_status_id"] = $apiStatusId;
    $insertApiLog["ad_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ad_created_on"] = date("Y-m-d H:i:s");

    $log_insert_id = $leadModelObj->insertTable("api_adjust_logs ", $insertApiLog);

    foreach ($apiResponseData as $value) {

        $utm_source = $value['Media Source'];
        $utm_medium = $value['Channel'];
        $utm_campaign = $value['Campaign'];
        $profile_id = $value['Customer User ID'];
        $appsflyer_id = $value['AppsFlyer ID'];
        $af_prt = $value['Partner'];
        $af_siteid = $value['Site ID'];

        if (strpos(strtoupper($utm_source), 'INTELLECTADS') !== false || strpos(strtoupper($af_prt), 'INTELLECTADS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "INTELLECTADS";
        } else if (strpos(strtoupper($utm_source), 'AFFINITY') !== false || strpos(strtoupper($af_prt), 'AFFINITY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "AFFINITY";
        } else if (strpos(strtoupper($utm_source), 'VALULEAFAFF') !== false || strpos(strtoupper($af_prt), 'VALUELEAF') !== false) {
            $utm_source = "VALUELEAF";
            $utm_medium = $af_siteid;
        } else if (strpos(strtoupper($utm_source), 'ADCANOPUS') !== false || strpos(strtoupper($af_prt), 'ADCANOPUS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADCANOPUS";
        } else if (strpos(strtoupper($utm_source), 'ADSPLAY') !== false || strpos(strtoupper($af_prt), 'ADSPLAY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSPLY";
        } else if (strpos(strtoupper($utm_source), 'ADSREVERB') !== false || strpos(strtoupper($af_prt), 'ADSREVERB') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSREVERB";
        } else if (strpos(strtoupper($utm_source), 'TATADZ') !== false || strpos(strtoupper($af_prt), 'TATADZ') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "TATADZ";
        } else if (strpos(strtoupper($utm_source), 'QUICKADSMEDIA') !== false || strpos(strtoupper($af_prt), 'QUICKADSMEDIA') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "QUICKADSMEDIA";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGITAL') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGITAL') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'ROYALMOBI') !== false || strpos(strtoupper($af_prt), 'ROYALMOBI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ROYALMOBI";
        } else if (strpos(strtoupper($utm_source), '3DOT14') !== false || strpos(strtoupper($af_prt), '3DOT14') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "3DOT14";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGI') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'CLICK2CAGENCY') !== false || strpos(strtoupper($af_prt), 'CLICK2CAGENCY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "CLICK2COMMISION";
        } else if (strpos(strtoupper($utm_source), 'ADZGAMMAME') !== false || strpos(strtoupper($af_prt), 'ADZGAMMAME') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADZEALOUS";
        } else if (strpos(strtoupper($utm_source), 'FACEBOOK') !== false || strpos(strtoupper($af_prt), 'FACEBOOK') !== false || strpos(strtoupper($utm_source), 'RESTRICTED') !== false) {
            $utm_source = "FACEBOOK";
        } else if (strpos(strtoupper($utm_source), 'GOOGLE') !== false || strpos(strtoupper($af_prt), 'GOOGLE') !== false) {
            $utm_source = "GOOGLE";
        } else if (strpos(strtoupper($utm_source), 'ORGANIC') !== false) {
            $utm_source = "ORGANIC";
        } else {
            $utm_source = "ORGANIC";
        }

        $update_flag = 0;

        if (!empty($profile_id) && !empty($appsflyer_id) && !empty($utm_source)) {

            $data_array = array(
                'cp_utm_source' => strtoupper($utm_source),
                'cp_utm_medium' => $utm_medium,
                'cp_utm_campaign' => strtoupper($utm_campaign),
                'cp_utm_term' => $af_siteid,
                'cp_adjust_adid' => $appsflyer_id
            );

            $update_flag = $leadModelObj->updateTable('customer_profile', $data_array, ' cp_id=' . $profile_id);
        }
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $log_insert_id;
    $returnResponseData['update_flag'] = $update_flag;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }

    return $returnResponseData;
}

function pull_appsflyer_android_organic_data($method_id, $request_array = array()) {

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $fromDate = date("Y-m-d");
    $toDate = date("Y-m-d");

    $type = "APPS_FLYER";
    $sub_type = "PULL_ORGANIC";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['rptest']) ? 1 : 0;
    $debug = 1;

    $leadModelObj = new LeadModel();

    $apiResponseData = array();

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (!empty($request_array['from_date'])) {
            $fromDate = $request_array['from_date'];
        }

        if (!empty($request_array['to_date'])) {
            $toDate = $request_array['to_date'];
        }

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('app_id', "com.vrindafinlease.rupee112", $apiConfig["ApiUrl"]);
        $apiToken = $apiConfig["ApiKey"];

        $apiUrl .= "from=" . date("Y-m-d", strtotime($fromDate)) . "&to=" . date("Y-m-d", strtotime($toDate));

        if (!empty($request_array['event_name'])) {
            $apiUrl .= "&event_name=" . $request_array['event_name'];
        }

        $apiHeaders[] = "Authorization: Bearer " . $apiToken;
        $apiHeaders[] = "Content-Type: text/csv";

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======URL Plain======<br/><br/>" . $apiUrl;
        }

        if ($hardcode_response) {

            $apiResponseRaw = 'Attributed Touch Type,Attributed Touch Time,Install Time,Event Time,Event Name,Event Value,Event Revenue,Event Revenue Currency,Event Revenue USD,Event Source,Is Receipt Validated,Partner,Media Source,Channel,Keywords,Campaign,Campaign ID,Adset,Adset ID,Ad,Ad ID,Ad Type,Site ID,Sub Site ID,Sub Param 1,Sub Param 2,Sub Param 3,Sub Param 4,Sub Param 5,Cost Model,Cost Value,Cost Currency,Contributor 1 Partner,Contributor 1 Media Source,Contributor 1 Campaign,Contributor 1 Touch Type,Contributor 1 Touch Time,Contributor 2 Partner,Contributor 2 Media Source,Contributor 2 Campaign,Contributor 2 Touch Type,Contributor 2 Touch Time,Contributor 3 Partner,Contributor 3 Media Source,Contributor 3 Campaign,Contributor 3 Touch Type,Contributor 3 Touch Time,Region,Country Code,State,City,Postal Code,DMA,IP,WIFI,Operator,Carrier,Language,AppsFlyer ID,Advertising ID,IDFA,Android ID,Customer User ID,IMEI,IDFV,Platform,Device Type,OS Version,App Version,SDK Version,App ID,App Name,Bundle ID,Is Retargeting,Retargeting Conversion Type,Attribution Lookback,Reengagement Window,Is Primary Attribution,User Agent,HTTP Referrer,Original URL
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,dashboard_visit,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,login_otp_verified,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:01:54,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:54,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:48,loan_eligibility_total,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:47,loan_eligibility_failed,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:41,registration_preview,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:38,dashboard_registration_click,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,login_otp_verified,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:22,login_otp_send,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:10,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,';
        } else {

            $curl = curl_init($apiUrl);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30000000);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);

            $apiResponseRaw = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseRaw;
        }

        if (!$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseRaw)) {

                if (!empty($apiResponseRaw)) {

                    if (!isset($apiResponseRaw['error']) && empty($apiResponseRaw['error'])) {
                        $apiStatusId = 1;
                        $lines = explode("\n", trim($apiResponseRaw));
                        $headersData = str_getcsv(array_shift($lines));
                        foreach ($lines as $line) {
                            $row = str_getcsv($line);
                            $apiResponseData[] = array_combine($headersData, $row);
                        }
                    } else if (isset($apiResponseRaw['error']) && $apiResponseRaw['STATUS'] == "CL003") {
                        throw new ErrorException(json_encode($apiResponseRaw['error']));
                    }
                } else {
                    $temp_error = !empty(json_encode($apiResponseRaw['error'])) ? json_encode($apiResponseRaw['error']) : "Some error occurred. Please try again.";
                    throw new ErrorException(json_encode($temp_error));
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
    $insertApiLog["ad_request"] = $apiRequestJson;
    $insertApiLog["ad_method_id"] = $method_id;
    $insertApiLog["ad_response"] = json_encode($apiResponseData);
    $insertApiLog["ad_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ad_response_datetime"] = $apiResponseDateTime;
    $insertApiLog["ad_api_status_id"] = $apiStatusId;
    $insertApiLog["ad_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ad_created_on"] = date("Y-m-d H:i:s");

    $log_insert_id = $leadModelObj->insertTable("api_adjust_logs ", $insertApiLog);

    foreach ($apiResponseData as $value) {

        $utm_source = $value['Media Source'];
        $utm_medium = $value['Channel'];
        $utm_campaign = $value['Campaign'];
        $profile_id = $value['Customer User ID'];
        $appsflyer_id = $value['AppsFlyer ID'];
        $af_prt = $value['Partner'];
        $af_siteid = $value['Site ID'];

        if (strpos(strtoupper($utm_source), 'INTELLECTADS') !== false || strpos(strtoupper($af_prt), 'INTELLECTADS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "INTELLECTADS";
        } else if (strpos(strtoupper($utm_source), 'AFFINITY') !== false || strpos(strtoupper($af_prt), 'AFFINITY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "AFFINITY";
        } else if (strpos(strtoupper($utm_source), 'VALULEAFAFF') !== false || strpos(strtoupper($af_prt), 'VALUELEAF') !== false) {
            $utm_source = "VALUELEAF";
            $utm_medium = $af_siteid;
        } else if (strpos(strtoupper($utm_source), 'ADCANOPUS') !== false || strpos(strtoupper($af_prt), 'ADCANOPUS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADCANOPUS";
        } else if (strpos(strtoupper($utm_source), 'ADSPLAY') !== false || strpos(strtoupper($af_prt), 'ADSPLAY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSPLY";
        } else if (strpos(strtoupper($utm_source), 'ADSREVERB') !== false || strpos(strtoupper($af_prt), 'ADSREVERB') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSREVERB";
        } else if (strpos(strtoupper($utm_source), 'TATADZ') !== false || strpos(strtoupper($af_prt), 'TATADZ') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "TATADZ";
        } else if (strpos(strtoupper($utm_source), 'QUICKADSMEDIA') !== false || strpos(strtoupper($af_prt), 'QUICKADSMEDIA') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "QUICKADSMEDIA";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGITAL') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGITAL') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'ROYALMOBI') !== false || strpos(strtoupper($af_prt), 'ROYALMOBI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ROYALMOBI";
        } else if (strpos(strtoupper($utm_source), '3DOT14') !== false || strpos(strtoupper($af_prt), '3DOT14') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "3DOT14";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGI') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'CLICK2CAGENCY') !== false || strpos(strtoupper($af_prt), 'CLICK2CAGENCY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "CLICK2COMMISION";
        } else if (strpos(strtoupper($utm_source), 'ADZGAMMAME') !== false || strpos(strtoupper($af_prt), 'ADZGAMMAME') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADZEALOUS";
        } else if (strpos(strtoupper($utm_source), 'FACEBOOK') !== false || strpos(strtoupper($af_prt), 'FACEBOOK') !== false || strpos(strtoupper($utm_source), 'RESTRICTED') !== false) {
            $utm_source = "FACEBOOK";
        } else if (strpos(strtoupper($utm_source), 'GOOGLE') !== false || strpos(strtoupper($af_prt), 'GOOGLE') !== false) {
            $utm_source = "GOOGLE";
        } else if (strpos(strtoupper($utm_source), 'ORGANIC') !== false) {
            $utm_source = "ORGANIC";
        } else {
            $utm_source = "ORGANIC";
        }

        $update_flag = 0;

        if (!empty($profile_id) && !empty($appsflyer_id) && !empty($utm_source)) {

            $data_array = array(
                'cp_utm_source' => strtoupper($utm_source),
                'cp_utm_medium' => $utm_medium,
                'cp_utm_campaign' => strtoupper($utm_campaign),
                'cp_utm_term' => $af_siteid,
                'cp_adjust_adid' => $appsflyer_id
            );

            $update_flag = $leadModelObj->updateTable('customer_profile', $data_array, ' cp_id=' . $profile_id);
        }
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $log_insert_id;
    $returnResponseData['update_flag'] = $update_flag;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }

    return $returnResponseData;
}

function pull_appsflyer_ios_non_organic_data($method_id, $request_array = array()) {

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $fromDate = date("Y-m-d");
    $toDate = date("Y-m-d");

    $type = "APPS_FLYER";
    $sub_type = "PULL_NON_ORGANIC";

    $hardcode_response = true;

    $debug = !empty($_REQUEST['rptest']) ? 1 : 0;
    $debug = 1;

    $leadModelObj = new LeadModel();

    $apiResponseData = array();

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (!empty($request_array['from_date'])) {
            $fromDate = $request_array['from_date'];
        }

        if (!empty($request_array['to_date'])) {
            $toDate = $request_array['to_date'];
        }

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('app_id', "id6465577336", $apiConfig["ApiUrl"]);
        $apiToken = $apiConfig["ApiKey"];

        $apiUrl .= "from=" . date("Y-m-d", strtotime($fromDate)) . "&to=" . date("Y-m-d", strtotime($toDate));

        if (!empty($request_array['event_name'])) {
            $apiUrl .= "&event_name=" . $request_array['event_name'];
        }

        $apiHeaders[] = "Authorization: Bearer " . $apiToken;
        $apiHeaders[] = "Content-Type: text/csv";

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======URL Plain======<br/><br/>" . $apiUrl;
        }

        if ($hardcode_response) {

            $apiResponseRaw = 'Attributed Touch Type,Attributed Touch Time,Install Time,Event Time,Event Name,Event Value,Event Revenue,Event Revenue Currency,Event Revenue USD,Event Source,Is Receipt Validated,Partner,Media Source,Channel,Keywords,Campaign,Campaign ID,Adset,Adset ID,Ad,Ad ID,Ad Type,Site ID,Sub Site ID,Sub Param 1,Sub Param 2,Sub Param 3,Sub Param 4,Sub Param 5,Cost Model,Cost Value,Cost Currency,Contributor 1 Partner,Contributor 1 Media Source,Contributor 1 Campaign,Contributor 1 Touch Type,Contributor 1 Touch Time,Contributor 2 Partner,Contributor 2 Media Source,Contributor 2 Campaign,Contributor 2 Touch Type,Contributor 2 Touch Time,Contributor 3 Partner,Contributor 3 Media Source,Contributor 3 Campaign,Contributor 3 Touch Type,Contributor 3 Touch Time,Region,Country Code,State,City,Postal Code,DMA,IP,WIFI,Operator,Carrier,Language,AppsFlyer ID,Advertising ID,IDFA,Android ID,Customer User ID,IMEI,IDFV,Platform,Device Type,OS Version,App Version,SDK Version,App ID,App Name,Bundle ID,Is Retargeting,Retargeting Conversion Type,Attribution Lookback,Reengagement Window,Is Primary Attribution,User Agent,HTTP Referrer,Original URL
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,dashboard_visit,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,login_otp_verified,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:01:54,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:54,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:48,loan_eligibility_total,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:47,loan_eligibility_failed,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:41,registration_preview,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:38,dashboard_registration_click,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,login_otp_verified,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:22,login_otp_send,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:10,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,';
        } else {

            $curl = curl_init($apiUrl);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30000000);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);

            $apiResponseRaw = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseRaw;
        }

        if (!$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseRaw)) {

                if (!empty($apiResponseRaw)) {

                    if (!isset($apiResponseRaw['error']) && empty($apiResponseRaw['error'])) {
                        $apiStatusId = 1;
                        $lines = explode("\n", trim($apiResponseRaw));
                        $headersData = str_getcsv(array_shift($lines));
                        foreach ($lines as $line) {
                            $row = str_getcsv($line);
                            $apiResponseData[] = array_combine($headersData, $row);
                        }
                    } else if (isset($apiResponseRaw['error']) && $apiResponseRaw['STATUS'] == "CL003") {
                        throw new ErrorException(json_encode($apiResponseRaw['error']));
                    }
                } else {
                    $temp_error = !empty(json_encode($apiResponseRaw['error'])) ? json_encode($apiResponseRaw['error']) : "Some error occurred. Please try again.";
                    throw new ErrorException(json_encode($temp_error));
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
    $insertApiLog["ad_request"] = $apiRequestJson;
    $insertApiLog["ad_method_id"] = $method_id;
    $insertApiLog["ad_response"] = json_encode($apiResponseData);
    $insertApiLog["ad_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ad_response_datetime"] = $apiResponseDateTime;
    $insertApiLog["ad_api_status_id"] = $apiStatusId;
    $insertApiLog["ad_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ad_created_on"] = date("Y-m-d H:i:s");

    $log_insert_id = $leadModelObj->insertTable("api_adjust_logs ", $insertApiLog);

    foreach ($apiResponseData as $value) {

        $utm_source = $value['Media Source'];
        $utm_medium = $value['Channel'];
        $utm_campaign = $value['Campaign'];
        $profile_id = $value['Customer User ID'];
        $appsflyer_id = $value['AppsFlyer ID'];
        $af_prt = $value['Partner'];
        $af_siteid = $value['Site ID'];

        if (strpos(strtoupper($utm_source), 'INTELLECTADS') !== false || strpos(strtoupper($af_prt), 'INTELLECTADS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "INTELLECTADS";
        } else if (strpos(strtoupper($utm_source), 'AFFINITY') !== false || strpos(strtoupper($af_prt), 'AFFINITY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "AFFINITY";
        } else if (strpos(strtoupper($utm_source), 'VALULEAFAFF') !== false || strpos(strtoupper($af_prt), 'VALUELEAF') !== false) {
            $utm_source = "VALUELEAF";
            $utm_medium = $af_siteid;
        } else if (strpos(strtoupper($utm_source), 'ADCANOPUS') !== false || strpos(strtoupper($af_prt), 'ADCANOPUS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADCANOPUS";
        } else if (strpos(strtoupper($utm_source), 'ADSPLAY') !== false || strpos(strtoupper($af_prt), 'ADSPLAY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSPLY";
        } else if (strpos(strtoupper($utm_source), 'ADSREVERB') !== false || strpos(strtoupper($af_prt), 'ADSREVERB') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSREVERB";
        } else if (strpos(strtoupper($utm_source), 'TATADZ') !== false || strpos(strtoupper($af_prt), 'TATADZ') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "TATADZ";
        } else if (strpos(strtoupper($utm_source), 'QUICKADSMEDIA') !== false || strpos(strtoupper($af_prt), 'QUICKADSMEDIA') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "QUICKADSMEDIA";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGITAL') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGITAL') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'ROYALMOBI') !== false || strpos(strtoupper($af_prt), 'ROYALMOBI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ROYALMOBI";
        } else if (strpos(strtoupper($utm_source), '3DOT14') !== false || strpos(strtoupper($af_prt), '3DOT14') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "3DOT14";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGI') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'CLICK2CAGENCY') !== false || strpos(strtoupper($af_prt), 'CLICK2CAGENCY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "CLICK2COMMISION";
        } else if (strpos(strtoupper($utm_source), 'ADZGAMMAME') !== false || strpos(strtoupper($af_prt), 'ADZGAMMAME') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADZEALOUS";
        } else if (strpos(strtoupper($utm_source), 'FACEBOOK') !== false || strpos(strtoupper($af_prt), 'FACEBOOK') !== false || strpos(strtoupper($utm_source), 'RESTRICTED') !== false) {
            $utm_source = "FACEBOOK";
        } else if (strpos(strtoupper($utm_source), 'GOOGLE') !== false || strpos(strtoupper($af_prt), 'GOOGLE') !== false) {
            $utm_source = "GOOGLE";
        } else if (strpos(strtoupper($utm_source), 'ORGANIC') !== false) {
            $utm_source = "ORGANIC";
        } else {
            $utm_source = "ORGANIC";
        }

        $update_flag = 0;

        if (!empty($profile_id) && !empty($appsflyer_id) && !empty($utm_source)) {

            $data_array = array(
                'cp_utm_source' => strtoupper($utm_source),
                'cp_utm_medium' => $utm_medium,
                'cp_utm_campaign' => strtoupper($utm_campaign),
                'cp_utm_term' => $af_siteid,
                'cp_adjust_adid' => $appsflyer_id
            );

            $update_flag = $leadModelObj->updateTable('customer_profile', $data_array, ' cp_id=' . $profile_id);
        }
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $log_insert_id;
    $returnResponseData['update_flag'] = $update_flag;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }

    return $returnResponseData;
}

function pull_appsflyer_android_non_organic_data($method_id, $request_array = array()) {

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";
    $fromDate = date("Y-m-d");
    $toDate = date("Y-m-d");

    $type = "APPS_FLYER";
    $sub_type = "PULL_NON_ORGANIC";

    $hardcode_response = true;

    $debug = !empty($_REQUEST['rptest']) ? 1 : 0;
    $debug = 1;

    $leadModelObj = new LeadModel();

    $apiResponseData = array();

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if (!empty($request_array['from_date'])) {
            $fromDate = $request_array['from_date'];
        }

        if (!empty($request_array['to_date'])) {
            $toDate = $request_array['to_date'];
        }

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('app_id', "com.vrindafinlease.rupee112", $apiConfig["ApiUrl"]);
        $apiToken = $apiConfig["ApiKey"];

        $apiUrl .= "from=" . date("Y-m-d", strtotime($fromDate)) . "&to=" . date("Y-m-d", strtotime($toDate));

        if (!empty($request_array['event_name'])) {
            $apiUrl .= "&event_name=" . $request_array['event_name'];
        }


        $apiHeaders[] = "Authorization: Bearer " . $apiToken;
        $apiHeaders[] = "Content-Type: text/csv";

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======URL Plain======<br/><br/>" . $apiUrl;
        }

        if ($hardcode_response) {

            $apiResponseRaw = 'Attributed Touch Type,Attributed Touch Time,Install Time,Event Time,Event Name,Event Value,Event Revenue,Event Revenue Currency,Event Revenue USD,Event Source,Is Receipt Validated,Partner,Media Source,Channel,Keywords,Campaign,Campaign ID,Adset,Adset ID,Ad,Ad ID,Ad Type,Site ID,Sub Site ID,Sub Param 1,Sub Param 2,Sub Param 3,Sub Param 4,Sub Param 5,Cost Model,Cost Value,Cost Currency,Contributor 1 Partner,Contributor 1 Media Source,Contributor 1 Campaign,Contributor 1 Touch Type,Contributor 1 Touch Time,Contributor 2 Partner,Contributor 2 Media Source,Contributor 2 Campaign,Contributor 2 Touch Type,Contributor 2 Touch Time,Contributor 3 Partner,Contributor 3 Media Source,Contributor 3 Campaign,Contributor 3 Touch Type,Contributor 3 Touch Time,Region,Country Code,State,City,Postal Code,DMA,IP,WIFI,Operator,Carrier,Language,AppsFlyer ID,Advertising ID,IDFA,Android ID,Customer User ID,IMEI,IDFV,Platform,Device Type,OS Version,App Version,SDK Version,App ID,App Name,Bundle ID,Is Retargeting,Retargeting Conversion Type,Attribution Lookback,Reengagement Window,Is Primary Attribution,User Agent,HTTP Referrer,Original URL
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,dashboard_visit,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:02:12,login_otp_verified,null,,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,727513,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                click,2024-02-04 13:28:46,2024-02-27 07:01:52,2024-02-27 07:01:54,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,googleadwords_int,ACI_Display,,IOS_Rupee_6_Dec,20833007721,Emergency Loan,160159411084,,,ClickToDownload,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,HR,Garhkhera,121004,None,103.237.174.76,true,,,,1707053358481-2071216,,0E867D8D-2086-4AEA-9C25-7FB15849DA3E,,,,2A07AC12-E1F6-4832-8BEF-903C16D74E46,ios,,16.7.1,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,30d,,true,DigitalJourney/15022024 CFNetwork/1410.0.3 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:54,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:48,loan_eligibility_total,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:47,loan_eligibility_failed,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:41,registration_preview,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:38,dashboard_registration_click,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,dashboard_visit,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:32,login_otp_verified,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:22,login_otp_send,null,,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,722740,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,
                ,,2024-02-27 06:14:08,2024-02-27 06:14:10,splash_old_api_version,"{""Version"":""11""}",,USD,,SDK,,,restricted,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,AS,IN,PB,Dhudike,142053,None,106.211.90.62,false,,,,1709013912369-7733291,,21B431E8-0365-477F-86B1-CB529E09BA3D,,,,773329CC-B168-4AC0-8105-49909F5E1674,ios,,16.7.5,1.9,v6.12.3,id6465577336,Rupee112,com.vrinda.rupee112,false,,,,true,DigitalJourney/15022024 CFNetwork/1410.1 Darwin/22.6.0,,';
        } else {

            $curl = curl_init($apiUrl);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30000000);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);

            $apiResponseRaw = curl_exec($curl);
        }

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseRaw;
        }

        if (!$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseRaw)) {

                if (!empty($apiResponseRaw)) {

                    if (!isset($apiResponseRaw['error']) && empty($apiResponseRaw['error'])) {
                        $apiStatusId = 1;
                        $lines = explode("\n", trim($apiResponseRaw));
                        $headersData = str_getcsv(array_shift($lines));
                        foreach ($lines as $line) {
                            $row = str_getcsv($line);
                            $apiResponseData[] = array_combine($headersData, $row);
                        }
                    } else if (isset($apiResponseRaw['error']) && $apiResponseRaw['STATUS'] == "CL003") {
                        throw new ErrorException(json_encode($apiResponseRaw['error']));
                    }
                } else {
                    $temp_error = !empty(json_encode($apiResponseRaw['error'])) ? json_encode($apiResponseRaw['error']) : "Some error occurred. Please try again.";
                    throw new ErrorException(json_encode($temp_error));
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
    $insertApiLog["ad_request"] = $apiRequestJson;
    $insertApiLog["ad_method_id"] = $method_id;
    $insertApiLog["ad_response"] = json_encode($apiResponseData);
    $insertApiLog["ad_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["ad_response_datetime"] = $apiResponseDateTime;
    $insertApiLog["ad_api_status_id"] = $apiStatusId;
    $insertApiLog["ad_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["ad_created_on"] = date("Y-m-d H:i:s");

    $log_insert_id = $leadModelObj->insertTable("api_adjust_logs ", $insertApiLog);

    foreach ($apiResponseData as $value) {

        $utm_source = $value['Media Source'];
        $utm_medium = $value['Channel'];
        $utm_campaign = $value['Campaign'];
        $profile_id = $value['Customer User ID'];
        $appsflyer_id = $value['AppsFlyer ID'];
        $af_prt = $value['Partner'];
        $af_siteid = $value['Site ID'];

        if (strpos(strtoupper($utm_source), 'INTELLECTADS') !== false || strpos(strtoupper($af_prt), 'INTELLECTADS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "INTELLECTADS";
        } else if (strpos(strtoupper($utm_source), 'AFFINITY') !== false || strpos(strtoupper($af_prt), 'AFFINITY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "AFFINITY";
        } else if (strpos(strtoupper($utm_source), 'VALULEAFAFF') !== false || strpos(strtoupper($af_prt), 'VALUELEAF') !== false) {
            $utm_source = "VALUELEAF";
            $utm_medium = $af_siteid;
        } else if (strpos(strtoupper($utm_source), 'ADCANOPUS') !== false || strpos(strtoupper($af_prt), 'ADCANOPUS') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADCANOPUS";
        } else if (strpos(strtoupper($utm_source), 'ADSPLAY') !== false || strpos(strtoupper($af_prt), 'ADSPLAY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSPLY";
        } else if (strpos(strtoupper($utm_source), 'ADSREVERB') !== false || strpos(strtoupper($af_prt), 'ADSREVERB') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADSREVERB";
        } else if (strpos(strtoupper($utm_source), 'TATADZ') !== false || strpos(strtoupper($af_prt), 'TATADZ') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "TATADZ";
        } else if (strpos(strtoupper($utm_source), 'QUICKADSMEDIA') !== false || strpos(strtoupper($af_prt), 'QUICKADSMEDIA') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "QUICKADSMEDIA";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGITAL') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGITAL') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'ROYALMOBI') !== false || strpos(strtoupper($af_prt), 'ROYALMOBI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ROYALMOBI";
        } else if (strpos(strtoupper($utm_source), '3DOT14') !== false || strpos(strtoupper($af_prt), '3DOT14') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "3DOT14";
        } else if (strpos(strtoupper($utm_source), 'XPLOREDIGI') !== false || strpos(strtoupper($af_prt), 'XPLOREDIGI') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "XPLOREDIGITAL";
        } else if (strpos(strtoupper($utm_source), 'CLICK2CAGENCY') !== false || strpos(strtoupper($af_prt), 'CLICK2CAGENCY') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "CLICK2COMMISION";
        } else if (strpos(strtoupper($utm_source), 'ADZGAMMAME') !== false || strpos(strtoupper($af_prt), 'ADZGAMMAME') !== false) {
            $utm_medium = $utm_source;
            $utm_source = "ADZEALOUS";
        } else if (strpos(strtoupper($utm_source), 'FACEBOOK') !== false || strpos(strtoupper($af_prt), 'FACEBOOK') !== false || strpos(strtoupper($utm_source), 'RESTRICTED') !== false) {
            $utm_source = "FACEBOOK";
        } else if (strpos(strtoupper($utm_source), 'GOOGLE') !== false || strpos(strtoupper($af_prt), 'GOOGLE') !== false) {
            $utm_source = "GOOGLE";
        } else if (strpos(strtoupper($utm_source), 'ORGANIC') !== false) {
            $utm_source = "ORGANIC";
        } else {
            $utm_source = "ORGANIC";
        }

        $update_flag = 0;

        if (!empty($profile_id) && !empty($appsflyer_id) && !empty($utm_source)) {

            $data_array = array(
                'cp_utm_source' => strtoupper($utm_source),
                'cp_utm_medium' => $utm_medium,
                'cp_utm_campaign' => strtoupper($utm_campaign),
                'cp_utm_term' => $af_siteid,
                'cp_adjust_adid' => $appsflyer_id
            );

            $update_flag = $leadModelObj->updateTable('customer_profile', $data_array, ' cp_id=' . $profile_id);
        }
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $log_insert_id;
    $returnResponseData['update_flag'] = $update_flag;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['parse_response'] = $apiResponseData;
    }

    return $returnResponseData;
}

function appsflyer_push_event_api($method_id, $lead_id, $request_array = array()) {

    //    error_reporting(E_ALL);
    //    ini_set('display_errors', 1);

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseString = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "APPS_FLYER";
    $sub_type = "EVENT_PUSH_CALL";
    $app_id = "";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['rptest']) ? 1 : 0;

    $leadModelObj = new LeadModel();

    $apiResponseData = array();

    $event_name = "";

    $event_type_id = !empty($request_array['event_type_id']) ? $request_array['event_type_id'] : "";

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
            throw new Exception("lead id cannot be blank");
        }


        $appDataReturnArr = $leadModelObj->getLeadFullDetails($lead_id);

        if ($appDataReturnArr['status'] == 1) {
            $applicationDetails = $appDataReturnArr['app_data'];
        } else {
            throw new Exception("Lead details not found.");
        }

        if ($applicationDetails['user_type'] == 'REPEAT') {
            throw new Exception("User type is repeat.");
        }

        $data_source_id = $applicationDetails['lead_data_source_id'];
        $appsflyer_id = $applicationDetails["customer_adjust_adid"];
        $profile_id = $applicationDetails["lead_customer_profile_id"];
        $utm_source = $applicationDetails["utm_source"];
        $utm_medium = $applicationDetails["utm_medium"];
        $utm_campaign = $applicationDetails["utm_campaign"];
        $utm_term = $applicationDetails["utm_term"];
        $lead_status_id = $applicationDetails["lead_status_id"];

        if (empty($utm_source)) {
            throw new Exception("utm source is not found.");
        }

        // if (in_array($utm_source, $blackListedPartners)) {
        //     throw new Exception("Blacklisted partner found.");
        // }

        if (empty($appsflyer_id)) {
            throw new Exception("appsflyer id is not found.");
        }

        if ($data_source_id == 33) {
            $app_id = COMP_ANDROID_STORE_ID;
        } elseif ($data_source_id == 34) {
            $app_id = COMP_APPLE_STORE_ID;
        } else {

            $appsFlyerLog = $leadModelObj->getAppsFlyerLogs($appsflyer_id);

            if ($appsFlyerLog['status'] == 1) {
                $appsFlyer = $appsFlyerLog['data'];
            } else {
                throw new Exception("Appsflyer log is not found. | " . $appsflyer_id);
            }

            $acaf_platform_name = trim(strtolower($appsFlyer['acaf_platform_name']));

            if (!empty($acaf_platform_name) && $acaf_platform_name == "android") {
                $app_id = COMP_ANDROID_STORE_ID;
            } elseif (!empty($acaf_platform_name) && $acaf_platform_name == "ios") {
                $app_id = COMP_APPLE_STORE_ID;
            } else {
                throw new Exception("Appsflyer platform is not found. | " . $appsflyer_id);
            }
        }

        if ($event_type_id == 1 && in_array($lead_status_id, array(41, 42))) {
            $event_name = "eligibility_success";
        } else if ($event_type_id == 2 && in_array($lead_status_id, array(8))) {
            $event_name = "eligibility_failed";
        } else if ($event_type_id == 3 && in_array($lead_status_id, array(4, 5))) {
            $event_name = "application_submit";
        } else if ($event_type_id == 4 && in_array($lead_status_id, array(14))) {
            $event_name = "loan_disbursed";
        } else {
            throw new Exception("Wrong event passed with lead status. | " . $appsflyer_id . " | " . $lead_status_id . " | " . $event_type_id);
        }

        $appDataReturnArr = $leadModelObj->getAppsFlyerPushEventLogs($appsflyer_id, $event_name, $lead_id);

        if ($appDataReturnArr['status'] == 1 && !empty($appDataReturnArr['count'])) {
            throw new Exception("Event already pushed.  " . $appsflyer_id . " | Event Name : " . $event_name . " | Log ID : " . $appDataReturnArr['id']);
        }

        $apiRequestArray = array(
            "appsflyer_id" => $appsflyer_id,
            "eventName" => $event_name,
            "eventValue" => "{\"lead_id\":\"$lead_id\", \"event_type_id\":\"$event_type_id\"}",
            "ip" => $_SERVER['REMOTE_ADDR'],
            "customer_user_id" => $profile_id
        );

        $apiRequestJson = json_encode($apiRequestArray);

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('app_id', $app_id, $apiConfig["ApiUrl"]);
        $apiToken = $apiConfig["ApiKey"];

        $apiHeaders = array(
            "authentication: " . $apiToken,
            "accept: application/json",
            "content-type: application/json"
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
            echo "<br/><br/> =======URL Plain======<br/><br/>" . $apiUrl;
            echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
        }

        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $apiResponseString = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> $httpcode=======Response Plain ======<br/><br/>" . $apiResponseString;
        }

        if (!$hardcode_response && curl_errno($curl)) { // CURL Error
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            if (!empty($apiResponseString)) {

                if ($httpcode == 200 && trim(strtolower($apiResponseString)) == "ok") {
                    $apiStatusId = 1;
                } else {
                    throw new ErrorException($httpcode . " : " . $apiResponseString);
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
    $insertApiLog["aape_lead_id"] = $lead_id;
    $insertApiLog["aape_profile_id"] = $profile_id;
    $insertApiLog["aape_api_status_id"] = $apiStatusId;
    $insertApiLog["aape_appsflyer_id"] = $appsflyer_id;
    $insertApiLog["aape_app_id"] = $app_id;
    $insertApiLog["aape_event_name"] = $event_name;
    $insertApiLog["aape_data_source_id"] = $data_source_id;
    $insertApiLog["aape_utm_source"] = $utm_source;
    $insertApiLog["aape_utm_medium"] = $utm_medium;
    $insertApiLog["aape_utm_campaign"] = $utm_campaign;
    $insertApiLog["aape_utm_term"] = $utm_term;
    $insertApiLog["aape_request"] = $apiRequestJson;
    $insertApiLog["aape_response"] = json_encode($apiResponseString);
    $insertApiLog["aape_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["aape_response_datetime"] = $apiResponseDateTime;
    $insertApiLog["aape_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);

    if ($apiStatusId == 1) {
        $log_insert_id = $leadModelObj->insertTable("api_appsflyer_push_events ", $insertApiLog);
        $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, "Appflyer Event Pushback | " . $event_name . " | log_id-" . $log_insert_id);
    }

    $returnResponseData = array();
    $returnResponseData['status'] = $apiStatusId;
    $returnResponseData['log_id'] = $log_insert_id;
    $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $returnResponseData['raw_request'] = $apiRequestJson;
        $returnResponseData['parse_response'] = $apiResponseString;
    }

    return $returnResponseData;
}
