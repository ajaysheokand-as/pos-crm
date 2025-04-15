<?php

function payday_adjust_api($method_name, $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "Adjust Send Start | $lead_id");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "INSPECT_DEVICE" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = adjust_inspect_device_api_call($lead_id, $request_array);
    } else {
        $responseArray["errors"] = "Type id is can not be blank.";
    }

    common_log_writer(6, "Adjust Send end | $lead_id | " . json_encode($responseArray));

    return $responseArray;
}

function adjust_inspect_device_api_call($lead_id = 0, $request_array = array()) {

    common_log_writer(6, "adjust_api_call started | $lead_id");

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

    $type = "ADJUST";
    $api_sub_type = "INSPECT_DEVICE";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['bltest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {

        $apiConfig = integration_config($type, $api_sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $ApiAccessToken = $apiConfig["ApiAccessToken"];
        $AppToken = $apiConfig["AppToken"];

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        // adjust unique id
        $customer_adjust_adid = $app_data['customer_adjust_adid'];
        $customer_adjust_gps_adid = $app_data['customer_adjust_gps_adid'];
        $customer_adjust_idfa = $app_data['customer_adjust_idfa'];
        $lead_data_source_id = $app_data['lead_data_source_id'];

        $advertising_id = "";
        if ($lead_data_source_id == 2) {
            $advertising_id = $customer_adjust_gps_adid;
        } else if ($lead_data_source_id == 24) {
            $advertising_id = $customer_adjust_idfa;
        }


        if (empty($advertising_id)) {
            throw new Exception("Missing the Adjust Platform Identifier.");
        }




        $apiUrl = str_replace("input_adid", $advertising_id, $apiUrl);

        $apiHeaders = array("Authorization: Bearer " . $ApiAccessToken);

        if ($debug == 1) {
            echo "<pre>";
            print_r("URL : " . $apiUrl);
        }

        if ($debug == 1) {
            echo "<br/><br/> =======Headers======<br/><br/>" . json_encode($apiHeaders);
        }

        $apiRequestDateTime = date("Y-m-d H:i:s");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $apiHeaders,
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

                if (!empty($apiResponseData['Adid']) && !empty($apiResponseData['TrackerName'])) {
                    $apiStatusId = 1;

                    $TrackerName = explode("::", $apiResponseData['TrackerName']);
                    $utm_source = $TrackerName[0];
                    $utm_campaign = $TrackerName[1];
                } elseif (!empty($apiResponseData['errors'])) {
                    throw new ErrorException($apiResponseData['errors'][0]);
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
    $insertApiLog['ad_api_status_id'] = $apiStatusId;
    $insertApiLog['ad_request'] = $apiUrl;
    $insertApiLog['ad_response'] = $apiResponseJson;
    $insertApiLog['ad_lead_id'] = $lead_id;
    $insertApiLog['ad_errors'] = $errorMessage;
    $insertApiLog['ad_request_datetime'] = $apiRequestDateTime;
    $insertApiLog['ad_response_datetime'] = $apiResponseDateTime;
    $insertApiLog['ad_created_on'] = date("Y-m-d H:i:s");

    $leadModelObj->insertTable("api_adjust_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['errors'] = $errorMessage;
    $response_array['utm_source'] = $utm_source;
    $response_array['utm_campaign'] = $utm_campaign;

    if ($debug) {
        $response_array['request_json'] = $apiUrl;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

?>
