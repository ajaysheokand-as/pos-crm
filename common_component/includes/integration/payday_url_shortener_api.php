
<?php

function payday_url_shortener_api($method_name = "", $url = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "URL Shortener started | $url | $method_name");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "TINYURL" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = tinyurl_api_call(1, $url, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(6, "URL Shortener end | $url | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function tinyurl_api_call($method_id, $url = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(6, "tinyurl_api_call started | $url");

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

    $type = "URL_SHORTENER_API";
    $sub_type = "TINYURL";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $token_string = "";
    $short_url = "";

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }


        if (empty($url)) {
            throw new Exception("Missing url.");
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $token_string = $apiConfig["ApiToken"];

        $apiRequestJson = '{
                            "url": "' . $url . '",
                            "domain": "tinyurl.com",
                            "expires_at": "' . date("Y-m-d H:i:s", strtotime("+1 day", strtotime(date("Y-m-d H:i:s")))) . '"
                          }';

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiRequestJson;
        }


        $apiHeaders = array(
            "content-type: application/json",
            "accept: application/json",
            "accept: */*",
            "Authorization: Bearer $token_string"
        );

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

                    if (isset($apiResponseData['data']) && !empty($apiResponseData['data'])) {


                        if ($apiResponseData['code'] == 0) {

                            $apiResponseData = $apiResponseData['data'];

                            $apiStatusId = 1;

                            $url = $apiResponseData['url'];
                            $short_url = $apiResponseData['tiny_url'];
                        } else {
                            throw new ErrorException("URL details does not received from api.");
                        }
                    } else if (isset($apiResponseData['errors']) && !empty($apiResponseData['errors'])) {
                        throw new ErrorException($apiResponseData['errors'][0]);
                    } else {
                        throw new ErrorException("Some error occurred. Please try again.");
                    }
                } else {
                    throw new ErrorException("Short URL : API Response empty.");
                }
            } else {
                throw new ErrorException("Short URL : API Response empty..");
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
    $insertApiLog["us_provider_id"] = 1; //tinyurl
    $insertApiLog["us_method_id"] = $method_id;
    $insertApiLog["us_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["us_request"] = addslashes($apiRequestJson);
    $insertApiLog["us_response"] = addslashes($apiResponseJson);
    $insertApiLog["us_url"] = $url;
    $insertApiLog["us_short_url"] = $short_url;
    $insertApiLog["us_api_status_id"] = $apiStatusId;
    $insertApiLog["us_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["us_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["us_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["us_user_id"] = $user_id;

    $res = $leadModelObj->insertTable("api_url_shortener_logs", $insertApiLog);

    //Preparing response array

    $response_array['status'] = $apiStatusId;
    $response_array['data'] = $apiResponseData;
    $response_array['url'] = $url;
    $response_array['short_url'] = $short_url;
    $response_array['errors'] = !empty($errorMessage) ? "Short URL : " . $errorMessage : "";
    if ($debug) {
        $response_array['request_json'] = $apiRequestJson;
        $response_array['response_json'] = $apiResponseJson;
    }
    return $response_array;
}

?>
