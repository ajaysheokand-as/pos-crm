<?php
function payday_whatsapp_api($type_id = 0, $lead_id = 0, $request_array = array()) {

    $responseArray = array("status" => 0, "errors" => "");
    if (!empty($type_id)) {
        $responseArray = whatsapp_api_call($type_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "Type id is can not be blank.";
    }

    return $responseArray;
}

function whatsapp_api_call($templete_type_id, $lead_id = 0, $request_array = array()) {
    $response_array = array("status" => 0, "errors" => "");
    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $apiStatusId = 0;
    $apiResponseData = "";
    $apiRequestJson = "";
    $apiResponseJson = "";
    $errorMessage = "";
    $curlError = "";
    $requestArray = array();
    $type = "WHATSAPP_API";
    $api_sub_type = "WHATSAPP_API_WHISTLE";
    // $api_sub_type = "WHATSAPP_API_AISENSY";
    $debug = !empty($_REQUEST['sottest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {
        $apiConfig = integration_config($type, $api_sub_type);
        if ($debug) {
            echo "<pre>", print_r($apiConfig), "</pre>";
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        if ($debug) {
            echo "<pre>", print_r($request_array), "</pre>";
        }

        if (empty($lead_id)) {
            throw new Exception('Lead is blank.');
        }

        $template_name = $request_array['template_name'];
        if (empty($template_name)) {
            throw new Exception('Template name is blank.');
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);
        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'] ?? "";
        $first_name = strtoupper(trim($app_data['first_name'] ?? ""));
        $middle_name = strtoupper(trim($app_data['middle_name'] ?? ""));
        $sur_name = strtoupper(trim($app_data['sur_name'] ?? ""));
        $repayment_amount = trim($app_data['repayment_amount'] ?? "###");
        $repayment_date = strtoupper(trim($app_data['repayment_date'] ?? "###"));
        $loan_recommended = strtoupper(trim($app_data['loan_recommended'] ?? ""));
        $due_amount = $loan_recommended - $repayment_amount;
        $customer_full_name = $first_name . ($middle_name ? " $middle_name" : "") . ($sur_name ? " $sur_name" : "");
        $loan_no = trim($app_data['loan_no'] ?? "###");
        $mobile = $app_data['mobile'] ?? "";
        $reference_no = $app_data['lead_reference_no'] ?? "";

        if (empty($mobile)) {
            throw new Exception('Mobile number is blank.');
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiKey = $apiConfig["apiKey"];
        $Provider = $apiConfig["Provider"];

        $requestArray = array(
            "to" => "91" . $mobile,
            "type" => "template",
            "template" => array(
                "language" => array("code" => "en"),
                "name" => $template_name,
                "components" => array()
            ),
            "messaging_product" => "whatsapp"
        );

        $curl = curl_init();
        if ($Provider == 'Aisensy') {
            $apiResponseData = array(
                "apiKey" => $apiKey,
                "campaignName" => "Repayment_reminder",
                "destination" => "+91" . trim($mobile),
                "userName" => "info@salarywalle.com",
                "source" => "any",
                "templateParams" => array(
                    $customer_full_name,
                    $repayment_amount,
                    $repayment_date,
                    $loan_no
                ),
                "attributes" => new stdClass()
            );

            $apiRequestJson = json_encode($apiResponseData);
            $requestHeaders = array('Content-Type: application/json', 'Accept: application/json');
        } elseif ($Provider == 'Whistle') {
            $requestArray['template']['components'] = get_whistle_template_components($template_name, $first_name, $repayment_amount, $repayment_date, $loan_no);
            $apiRequestJson = json_encode($requestArray);
            $requestHeaders = array('apikey: ' . $apiKey, 'Content-Type: application/json');
        }

        if ($debug) {
            echo "======= Request =======<br/>$apiRequestJson<br/>================<br/>";
            echo "<br/>================  requestHeaders  ================<br/>", print_r($requestHeaders, true), "<br/>";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $apiRequestJson,
            CURLOPT_HTTPHEADER => $requestHeaders,
        ));

        $apiResponseJson = curl_exec($curl);

        if ($debug) {
            echo "<br/>================  apiResponseJson  ================<br/>$apiResponseJson<br/>";
        }

        if (curl_errno($curl)) {
            $curlError = curl_error($curl);
            curl_close($curl);
            if (curl_errno($curl) == CURLE_OPERATION_TIMEOUTED) {
                throw new RuntimeException("Request timed out. Please try again later. Error: $curlError");
            } else {
                throw new RuntimeException("Something went wrong. Please try after sometime. Error: $curlError");
            }
        } else {
            curl_close($curl);
            $apiResponseData = json_decode($apiResponseJson, true);
            $apiStatusId = handle_api_response($Provider, $apiResponseData, $apiResponseJson);
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

    $insertApiLog = array(
        'whatsapp_provider' => $Provider == 'Aisensy' ? 2 : 3,
        'whatsapp_type_id' => $templete_type_id,
        'whatsapp_mobile' => $mobile,
        'whatsapp_request' => $apiRequestJson,
        'whatsapp_response' => $apiResponseJson,
        'whatsapp_template_id' => $template_name,
        'whatsapp_api_status_id' => $apiStatusId,
        'whatsapp_lead_id' => $lead_id,
        'whatsapp_user_id' => $user_id,
        'whatsapp_errors' => $errorMessage,
        'whatsapp_created_on' => date("Y-m-d H:i:s")
    );

    $leadModelObj->insertTable("api_whatsapp_logs", $insertApiLog);

    $response_array['status'] = $apiStatusId;
    $response_array['mobile'] = $mobile;
    $response_array['errors'] = $errorMessage;

    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

function get_whistle_template_components($template_name, $first_name, $repayment_amount, $repayment_date, $loan_no) {
    $components = array();
    if ($template_name == 'repayment_reminder') {
        $components = array(
            array(
                "type" => "body",
                "parameters" => array(
                    array("type" => "text", "text" => $first_name),
                    array("type" => "text", "text" => $repayment_amount),
                    array("type" => "text", "text" => $repayment_date),
                    array("type" => "text", "text" => $loan_no)
                )
            )
        );
    } else if ($template_name == 'new_incomplete_file' || $template_name == 'complete_journey_ks3') {
        $components = array(
            array(
                "type" => "header",
                "parameters" => array(
                    array("type" => "image", "image" => array("link" => WHATSAPP_BASE_URL . 'whatsapp_loan_offer.jpg'))
                )
            )
        );
        if ($template_name == 'complete_journey_ks3') {
            $components[] = array(
                "type" => "body",
                "parameters" => array(array("type" => "text", "text" => $first_name))
            );
        }
    } else if ($template_name == 'reloan_approch' || $template_name == 'reloan_approch') {
        $components = array(
            array(
                "type" => "header",
                "parameters" => array(
                    array("type" => "image", "image" => array("link" => WHATSAPP_BASE_URL . 'reloan_approach.png'))
                )
            )
        );

        if ($template_name == 'reloan_approch') {
            $components[] = array(
                "type" => "body",
                "parameters" => array(array("type" => "text", "text" => $first_name))
            );
        }

        if ($template_name == 'reloan_approch') {
            $components[] = array(
                "type" => "button",
                "sub_type" => "url",
                "index" => 0,
                "parameters" => array(array("type" => "text", "text" => 'apply-now?utm_source=WhatsApp&utm_campaign=whatsapp' . date("d-m-Y") . '&utm_medium=whatsapp'))
            );
        }
    }
    return $components;
}

function handle_api_response($Provider, $apiResponseData, $apiResponseJson) {
    if ($Provider == 'Aisensy') {
        if (!empty($apiResponseJson) && $apiResponseJson == 'Success.') {
            return 1;
        } else if (!empty($apiResponseData['message'])) {
            throw new ErrorException($apiResponseData['message']);
        } else {
            throw new ErrorException("Some error occurred. Please try again.[2]");
        }
    } else if ($Provider == 'Whistle') {
        if (!empty($apiResponseData['messages']) && $apiResponseData['messages'][0]['message_status'] == 'accepted') {
            return 1;
        } else if (!empty($apiResponseData['error'])) {
            throw new ErrorException($apiResponseData['error']['message']);
        } else {
            throw new ErrorException("Some error occurred. Please try again.[2]");
        }
    }
    throw new ErrorException("Some error occurred. Please try again.[1]");
}
