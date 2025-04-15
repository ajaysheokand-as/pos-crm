<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_call_management_api_call')) {

    function payday_call_management_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "LEAD_CAT_SANCTION" => 1,
            "SMARTPING_COLLECTION_CALL" => 3,
            "SMARTPING_BULK_UPLOAD" => 4,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 3) {
            $responseArray = smartping_payday_call_api($lead_id, $request_array);
        } else if ($method_id == 4) {
            $responseArray = smartping_payday_bulk_upload_api($lead_id, $request_array);
        } else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

        return $responseArray;
    }

}



if (!function_exists('smartping_payday_call_api')) {

    function smartping_payday_call_api($lead_id, $request_array = array()) {

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $cml_method_id = "";

        $type = "SMARTPING_CALL_CRM";
        $sub_type = "CLICK_TO_CALL";
        $campaign_id = "";
        $queue_id = "";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
//        $debug = 1;

        $applicationDetails = array();
        $input_mobile = "";
        $agent_mobile = "";
        $assignedTo = "";
        $method_id = 3;
        $mobile = "";

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : ""; //for testing
        $call_type = !empty($request_array['call_type']) ? $request_array['call_type'] : "";
//        $profile_type = !empty($request_array['profile_type']) ? $request_array['profile_type'] : "";

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
            $apiToken = $apiConfig["ApiKey"];
            $apilocationId = $apiConfig["LocationId"];

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            $appDataReturnArr = $ci->IntegrationModel->getLoanApplicationDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {

                $loanDetails = $appDataReturnArr['app_data'][0];
                $input_mobile = $loanDetails["mobile"];

                if ($call_type == 2) {
                    $input_mobile = $loanDetails["alternate_mobile"];
                }
            } else {
                throw new Exception("Application details does not exist.");
            }


            if (!empty($user_id)) {

                $crmUserReturnArr = $ci->IntegrationModel->getUMSUserDetails($user_id);

                if ($crmUserReturnArr['status'] == 1) {
                    $crmUserDetails = $crmUserReturnArr['app_data'];
                    $agent_mobile = $crmUserDetails['mobile'];
                    $campaign_id = $crmUserDetails['dialer_campaign_id'];
                    $queue_id = $crmUserDetails['dialer_queue_id'];
//                    $queue_id = $crmUserDetails['dialer_queue_id'];
                }
            }


            $name = !empty($loanDetails["name"]) ? $loanDetails["name"] : 'NA';
            $city = !empty($loanDetails["city"]) ? $loanDetails["city"] : '';
            $state = !empty($loanDetails["state"]) ? $loanDetails["state"] : '';
            $pincode = !empty($loanDetails["pincode"]) ? $loanDetails["pincode"] : '';
            $loan_amount = !empty($loanDetails["loan_recommended"]) ? $loanDetails["loan_recommended"] : 0;

            $apiRequestArray = array(
                "token" => $apiToken,
                "location_id" => $apilocationId,
                "phone_number" => $input_mobile,
                "agent_name" => "dev_user",
                "lead_id" => $lead_id,
                "loan_amount" => $loan_amount,
                "monthly_income" => "200",
                "user_type" => "NEW",
                "customer_name" => $name,
                "city_name" => $city,
                "state_name" => $state,
                "pincode" => $pincode,
                "campaign_id" => $campaign_id,
                "queue_id" => $queue_id
            );

            $apiRequestJson = json_encode($apiRequestArray);

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            $apiHeaders[] = "Content-Type: application/json";
            $apiHeaders[] = "Accept: application/json";

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
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

                        if (isset($apiResponseData['status']) && $apiResponseData['status'] == 1) {
                            $apiStatusId = 1;
                        } else {
                            $temp_error = !empty($apiResponseData['message']) ? $apiResponseData['message'] : "Some error occurred. Please try again..";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        $temp_error = !empty($apiResponseData['message']) ? $apiResponseData['message'] : "Some error occurred. Please try again.";
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
        $insertApiLog["cml_provider_id"] = 2;
        $insertApiLog["cml_user_id"] = $user_id;
        $insertApiLog["cml_method_id"] = $method_id; //Call Collection Allocation
        $insertApiLog["cml_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["cml_api_status_id"] = $apiStatusId;
        $insertApiLog["cml_mobile"] = $input_mobile;
        $insertApiLog["cml_request"] = $apiRequestJson;
        $insertApiLog["cml_response"] = $apiResponseJson;
        $insertApiLog["cml_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["cml_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["cml_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_call_management_logs", $insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['mobile'] = $input_mobile;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }

        return $returnResponseData;
    }

}


if (!function_exists('smartping_payday_bulk_upload_api')) {

    function smartping_payday_bulk_upload_api($lead_id, $request_array = array()) {

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $cml_method_id = "";

        $type = "SMARTPING_CALL_CRM";
        $sub_type = "BULK_UPLOAD";
        $campaign_id = "b57a656b-24ef-4da3-a9d5-474ac6b66cda";
        $queue_id = "f3be2d7a-7a0c-4c75-8046-7adceaf4cfa1";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
//        $debug = 1;

        $applicationDetails = array();
        $input_mobile = "";
        $agent_mobile = "";
        $assignedTo = "";
        $method_id = 3;
        $mobile = "";

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : ""; //for testing
        $call_type = !empty($request_array['call_type']) ? $request_array['call_type'] : "";
        $lead_list = !empty($request_array['lead_list']) ? $request_array['lead_list'] : "";
//        $profile_type = !empty($request_array['profile_type']) ? $request_array['profile_type'] : "";
        $lead_list = implode($lead_list, ',');

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
            $apiToken = $apiConfig["ApiKey"];
            $apilocationId = $apiConfig["LocationId"];

            if (empty($lead_list)) {
                throw new Exception("Missing Lead Id.");
            }


            $appDataReturnArr = $ci->IntegrationModel->getLoanApplicationDetails($lead_list);

            if ($appDataReturnArr['status'] === 1) {
                $loanDetails = $appDataReturnArr['app_data'];
            } else {
                throw new Exception("Application details does not exist.");
            }

            if (!empty($user_id)) {

                $crmUserReturnArr = $ci->IntegrationModel->getUMSUserDetails($user_id);

                if ($crmUserReturnArr['status'] == 1) {
                    $crmUserDetails = $crmUserReturnArr['app_data'];
                    $agent_mobile = $crmUserDetails['mobile'];
                    $campaign_id = $crmUserDetails['dialer_campaign_id'];
                    $call_list_id = $crmUserDetails['dialer_call_list_id'];
                }
            }


            foreach ($loanDetails as $key => $value) {

                $input_mobile = $value["mobile"];

                if ($call_type == 2) {
                    $input_mobile = $value["alternate_mobile"];
                }

                if (!is_mobile($input_mobile)) {
                    throw new Exception("Mobile number is not correct.[2]");
                }

                $lead_id = !empty($value["lead_id"]) ? $value["lead_id"] : 'NA';
                $name = !empty($value["name"]) ? $value["name"] : 'NA';
                $city = !empty($value["city"]) ? $value["city"] : '';
                $state = !empty($value["state"]) ? $value["state"] : '';
                $pincode = !empty($value["pincode"]) ? $value["pincode"] : '';
                $agent_mobile_number = $agent_mobile;
                $loan_amount = !empty($value["loan_recommended"]) ? $value["loan_recommended"] : 0;

                if (!empty($agent_mobile_number)) {
                    $assignedTo = "+91" . $agent_mobile_number;
                }


                $list_records[$key] = array(
                    "phone_number" => $input_mobile,
                    "first_name" => $name,
                    "last_name" => "",
                    "otherdata" => array(
                        "leadId" => $lead_id,
                        "loanAmount" => $loan_amount,
                        "monthlyIncome" => "20000",
                        "userType" => "REPEAT",
                        "customerName" => $name,
                        "cityName" => $city,
                        "stateName" => $state,
                        "pincode" => $pincode
                    )
                );
            }


            $apiRequestArray = array(
                "token" => $apiToken,
                "location_id" => $apilocationId,
                "duplicate_check" => false,
                "list_uuid" => $call_list_id,
                "call_broadcast_uuid" => $campaign_id,
                "list_records" => $list_records
            );

            $apiRequestJson = json_encode($apiRequestArray);

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            $apiHeaders[] = "Content-Type: application/json";
            $apiHeaders[] = "Accept: application/json";

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
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

                        if (isset($apiResponseData['status']) && $apiResponseData['status'] == 1) {
                            $apiStatusId = 1;
                        } else {
                            $temp_error = !empty($apiResponseData['message']) ? $apiResponseData['message'] : "Some error occurred. Please try again..";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        $temp_error = !empty($apiResponseData['message']) ? $apiResponseData['message'] : "Some error occurred. Please try again.";
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
        $insertApiLog["cml_provider_id"] = 2;
        $insertApiLog["cml_user_id"] = $user_id;
        $insertApiLog["cml_method_id"] = $method_id; //Call Bulk Collection Allocation
        $insertApiLog["cml_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["cml_api_status_id"] = $apiStatusId;
//        $insertApiLog["cml_mobile"] = $input_mobile;
        $insertApiLog["cml_request"] = $apiRequestJson;
        $insertApiLog["cml_response"] = $apiResponseJson;
        $insertApiLog["cml_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["cml_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["cml_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_call_management_logs", $insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['mobile'] = $input_mobile;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }

        return $returnResponseData;
    }

}
 