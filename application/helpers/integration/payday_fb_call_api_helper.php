<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_fb_campaign_api_call')) {

    function payday_fb_campaign_api_call($method_name = "", $form_id = 0, $start_time = 60, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "GET_PAGE_FORM" => 1,
            "GET_FORM_DATA" => 2,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 1) {
            $responseArray = get_fb_page_forms_api($request_array);
        } elseif ($method_id == 2) {
            $responseArray = get_fb_forms_data_api($form_id, $start_time, $request_array);
        } else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

        return $responseArray;
    }

}



if (!function_exists('get_fb_page_forms_api')) {

    function get_fb_page_forms_api($request_array = array()) {

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiResponseData = array();
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $return_log_id = "";
        $requestUrl = "";

        $type = "FB_CALL_CAMPAIGN";
        $sub_type = "CALL_PAGE";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['bltest']) ? 1 : 0;

        try {

            $apiConfig = integration_config($type, $sub_type);

            if ($debug == 1) {
                echo "<pre>";
                print_r($apiConfig);
            }

            if ($apiConfig['Status'] != 1) {
                throw new Exception($apiConfig['ErrorInfo']);
            }

            $apiToken = $apiConfig["PageAccessToken"];
            $apiPageID = $apiConfig["PageID"];
            $apiUrl = $apiConfig["ApiUrl"];

            if (empty($apiToken) || empty($apiPageID)) {
                throw new Exception('API Token or Page ID is blank.');
            }

            $requestUrl = $apiUrl . $apiPageID . '/leadgen_forms?fields=leads_count,id,name,status&limit=200&access_token=' . $apiToken;

            $curl = curl_init($requestUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $apiResponseJson = curl_exec($curl);

            $apiResponseDateTime = date("Y-m-d H:i:s");
            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

            if ($debug == 1) {
                echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
            }

            if (curl_errno($curl) && !$hardcode_response) {
                $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
                curl_close($curl);
                throw new RuntimeException("Something went wrong. Please try after sometime.");
            } else {

                if (isset($curl)) {
                    curl_close($curl);
                }


                $apiResponseData = json_decode($apiResponseJson, true);

                if (!empty($apiResponseData)) {

                    $apiResponseData = trim_data_array($apiResponseData);

                    if (!empty($apiResponseData)) {

                        if (!empty($apiResponseData['data']) && isset($apiResponseData['data'])) {
                            $apiStatusId = 1;
                        } else {
                            $temp_error = !empty($apiResponseData['error']['message']) ? $apiResponseData['error']['message'] : "Some error occurred. Please try again..";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        $temp_error = "Some error occurred. Please try again.";
                        throw new ErrorException($temp_error);
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
        $insertApiLog["fb_campaign_method_id"] = 1; //Page Data
        $insertApiLog["fb_campaign_form_id"] = NULL; //Page Data
        $insertApiLog["fb_campaign_request"] = $requestUrl;
        $insertApiLog["fb_campaign_response"] = $apiResponseJson;
        $insertApiLog["fb_campaign_status_id"] = $apiStatusId;
        $insertApiLog["fb_campaign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["fb_campaign_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["fb_campaign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_fb_campaign_logs", $insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['lead_count'] = count($apiResponseData['data']);
        $returnResponseData['lead_data'] = !empty($apiResponseData['data']) ? $apiResponseData['data'] : "";
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['raw_request'] = $requestUrl;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }
        return $returnResponseData;
    }

}

if (!function_exists('get_fb_forms_data_api')) {

    function get_fb_forms_data_api($form_id, $start_time, $request_array = array()) {

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiResponseData = array();
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $return_log_id = "";
        $requestUrl = "";

        $type = "FB_CALL_CAMPAIGN";
        $sub_type = "CALL_FORM";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['bltest']) ? 1 : 0;
//            $debug = 1;

        try {

            $apiConfig = integration_config($type, $sub_type);

            if ($debug == 1) {
                echo "<pre>";
                print_r($apiConfig);
            }

            if ($apiConfig['Status'] != 1) {
                throw new Exception($apiConfig['ErrorInfo']);
            }

            if (empty($form_id)) {
                throw new Exception('Form ID is blank.');
            }

            $apiToken = $apiConfig["PageAccessToken"];
            $apiUrl = $apiConfig["ApiUrl"];

            if (empty($apiToken)) {
                throw new Exception('API Token is blank.');
            }

            $start_datetime = strtotime(date('Y-m-d H:00:00', (time() - (60 * $start_time))));
            $requestUrl = $apiUrl . $form_id . '/leads?filtering=[{"field":"time_created","operator":"GREATER_THAN_OR_EQUAL","value":' . $start_datetime . '}]&limit=500&access_token=' . $apiToken;

            $curl = curl_init($requestUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $apiResponseJson = curl_exec($curl);

            $apiResponseDateTime = date("Y-m-d H:i:s");
            $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

            if ($debug == 1) {
                echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
            }

            if (curl_errno($curl) && !$hardcode_response) {
                $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $apiUrl;
                curl_close($curl);
                throw new RuntimeException("Something went wrong. Please try after sometime.");
            } else {

                if (isset($curl)) {
                    curl_close($curl);
                }


                $apiResponseData = json_decode($apiResponseJson, true);

                if (!empty($apiResponseData)) {

                    $apiResponseData = trim_data_array($apiResponseData);

                    if (!empty($apiResponseData)) {

                        if (!empty($apiResponseData['data']) && isset($apiResponseData['data'])) {
                            $apiStatusId = 1;
                        } else if (empty($apiResponseData['data'])) {
                            throw new Exception('Leads not found.');
                        } else {
                            $temp_error = !empty($apiResponseData['error']['message']) ? $apiResponseData['error']['message'] : "Some error occurred. Please try again..";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        $temp_error = "Some error occurred. Please try again.";
                        throw new ErrorException($temp_error);
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
        $insertApiLog["fb_campaign_method_id"] = 2; //Form Data
        $insertApiLog["fb_campaign_form_id"] = NULL;
        $insertApiLog["fb_campaign_request"] = $requestUrl;
        $insertApiLog["fb_campaign_response"] = $apiResponseJson;
        $insertApiLog["fb_form_lead_count"] = count($apiResponseData['data']);
        $insertApiLog["fb_campaign_status_id"] = $apiStatusId;
        $insertApiLog["fb_campaign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["fb_campaign_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["fb_campaign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_fb_campaign_logs", $insertApiLog);

        $final_form_lead_array = array();

        if (!empty($apiResponseData['data']) && $apiStatusId == 1) {


            if (isset($apiResponseData['data']) && !empty($apiResponseData['data'])) {


                foreach ($apiResponseData['data'] as $form_lead_data) {

                    $final_key_value = array();

                    if (!empty($form_lead_data['field_data'])) {

                        foreach ($form_lead_data['field_data'] as $form_key_value) {
                            if (isset($form_key_value['name']) && !empty($form_key_value['name'])) {
                                $final_key_value[$form_key_value['name']] = (isset($form_key_value['values'][0]) && !empty($form_key_value['values'][0])) ? $form_key_value['values'][0] : "";
                            }
                        }
                    }
                    if (!empty($final_key_value)) {
                        $final_form_lead_array[] = $final_key_value;
                    }
                }
            }
        }

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['lead_count'] = count($final_form_lead_array);
        $returnResponseData['lead_data'] = $final_form_lead_array;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['raw_request'] = $requestUrl;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }
        return $returnResponseData;
    }

}


