<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_quickcall_api_call')) {

    function payday_quickcall_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "LEAD_PUSH" => 1,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 1) {
            $responseArray = payday_save_call_api($lead_id, $request_array);
        } else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

        return $responseArray;
    }

}


if (!function_exists('payday_save_call_api')) {

    function payday_save_call_api($lead_id, $request_array) {

        $envSet = ENVIRONMENT;

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
        $parseResponseData = "";
        $type = "QUICK_DIALER";
        $sub_type = "SAVE_CALL";
        $product_id = 1;

        $hardcode_response = false;
        $api_quick_call_bypass_mobile = array();

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();
        $transactionId = "";
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "";
        $CampaignID = "";
        $AgentUserName = ""; //Kajal.Dubey
        $quick_call_type = !empty($request_array['quick_call_type']) ? $request_array['quick_call_type'] : 0;

        try {

            //API Config
            $apiConfig = integration_config($type, $sub_type);

            if ($debug == 1) {
                echo "<pre>";
                print_r($apiConfig);
            }

            if ($apiConfig['Status'] != 1) {
                throw new Exception($apiConfig['ErrorInfo']);
            }

            $apiUrl = $apiConfig["ApiUrl"];

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            if (empty($quick_call_type)) {
                throw new Exception("Missing quick call type.");
            }

            if (!isset($_SESSION['isUserSession']['user_id']) || empty($_SESSION['isUserSession']['user_id'])) {
                throw new Exception("User session has been expired. Please login again.");
            }

            $appDataReturnArr = $ci->IntegrationModel->getUMSUserDetails($user_id);
            if ($appDataReturnArr['status'] === 1) {
                $AgentUserName = $appDataReturnArr['app_data']['user_dialer_id'];
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];
                $cust_first_name = $applicationDetails["first_name"];
                $cust_mobile = $applicationDetails["mobile"];
                $lead_quick_call_id = $applicationDetails["lead_quick_call_id"];

                if (in_array($applicationDetails['mobile'], $api_quick_call_bypass_mobile) && $envSet == 'development') {
                    $hardcode_response = true;
                }
            } else {
                throw new Exception("Lead does not exist.");
            }

            if (empty($cust_mobile)) {
                throw new Exception("Mobile number does not exist.");
            }

            if (empty($AgentUserName)) {
                throw new Exception("User does not have the dialer user id.");
            }


            $Priority = 2; //0 for urgent call, 1 for call back, 2 for normal call
            $Duplicate = 2; //2 for fresh call, 1=>In this case duplicacy check will be done on the basis of campaignId, pnone number and remark.
            //Agent ID for Dialer CRM
            $RemarksSuffix = "";

            if ($envSet == "development") {
                $CampaignID = "Naman_UAT_Test";
            } else {
                if ($quick_call_type == 1) {
                    $CampaignID = "";
                }
            }

            $quickCallReturnArr = $ci->IntegrationModel->getQuickCallDetails($lead_id, $CampaignID);

            if ($quickCallReturnArr['status'] == 1) {//if call already set one time then will be reschedule
                $Duplicate = 1;
            }

            $api_url_parms = "?PhoneNo=$cust_mobile&Name=$cust_first_name&CampaignID=$CampaignID&Priority=$Priority&Duplicate=$Duplicate&Remark=$AgentUserName&RemarksSuffix=$RemarksSuffix&Field1=test&Field2=test";

            $api_url_parms = preg_replace("!\s+!", " ", $api_url_parms);

            $apiRequestJson = $apiUrl = $apiUrl . $api_url_parms;

            if ($debug == 1) {
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
                $apiResponseJson = '{"Click2Call":"Success", "Response":{"Record":"Inserted", "RecordId":"000017629560:Naman_UAT_Test:1"}}';
            } else {
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_POST, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                $apiResponseJson = curl_exec($curl);

                if ($debug == 1) {
                    echo "<br/><br/> =======API Response ======<br/><br/>" . $apiResponseJson;
                }
            }

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

                    if (isset($apiResponseData['Click2Call']) && strtolower($apiResponseData['Click2Call']) == "success") {

                        if (!empty($apiResponseData['Response']['RecordId'])) {

                            $parseResponseData = $apiResponseData['Response']['RecordId'];
                            $parseResponseData = explode(":", $parseResponseData);
                            if (!empty($parseResponseData[0])) {
                                $transactionId = $parseResponseData[0];
                                $apiStatusId = 1;
                            } else {
                                $temp_error = "RecordId is not available..";
                                throw new ErrorException($temp_error);
                            }
                        } else {
                            throw new ErrorException("RecordId is not available.");
                        }
                    } else if (isset($apiResponseData['Click2Call']) && strtolower($apiResponseData['Click2Call']) == "failed") {
                        $parseResponseData = $apiResponseData['Response'];
                        if (!empty($parseResponseData)) {
                            $tmp_error = $parseResponseData;
                        }

                        $tmp_error = !empty($tmp_error) ? $tmp_error : "Some error occurred. Please try again.";

                        throw new ErrorException($tmp_error);
                    } else {
                        throw new ErrorException("Some error occurred. Please try again..");
                    }
                } else {
                    throw new ErrorException("Some error occurred. Please try again...");
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
        $insertApiLog["quickcall_product_id"] = $product_id;
        $insertApiLog["quickcall_method_id"] = 1;
        $insertApiLog["quickcall_reschedule_flag"] = $Duplicate;
        $insertApiLog["quickcall_campaign_name"] = $CampaignID;
        $insertApiLog["quickcall_lead_id"] = $lead_id;
        $insertApiLog["quickcall_mobile"] = $cust_mobile;
        $insertApiLog["quickcall_api_status_id"] = $apiStatusId;
        $insertApiLog["quickcall_request"] = addslashes($apiRequestJson);
        $insertApiLog["quickcall_response"] = addslashes($apiResponseJson);
        $insertApiLog["quickcall_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["quickcall_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["quickcall_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["quickcall_return_call_id"] = !empty($transactionId) ? $transactionId : "";

        $return_log_id = $ci->IntegrationModel->insert("api_quickcall_logs", $insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? "Quick Call Error : " . $errorMessage : "";
        if ($apiStatusId == 1) {
            $returnResponseData['data'] = $parseResponseData;
        }
        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["disburse_errors"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }
        if ($apiStatusId == 1 && !empty($transactionId)) {
//            $ci->IntegrationModel->update("leads", array("lead_id" => $lead_id), array("lead_quick_call_id" => $transactionId));
        }


        return $returnResponseData;
    }

}
