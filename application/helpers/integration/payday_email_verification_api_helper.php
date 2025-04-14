<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_email_verification_api_call')) {

    function payday_email_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "EMAIL_VALIDATION" => 2,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 1) {
            $responseArray = mailgun_email_validation_api($lead_id, $request_array);
        } else if ($method_id == 2) {
            $responseArray = sendgrid_email_validation_api($lead_id, $request_array);
        } else if ($method_id == 3) {
            $responseArray = signzy_email_validation_api($lead_id, $request_array);
        }
        else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

//        traceObject($responseArray);

        return $responseArray;
    }

}


if (!function_exists('mailgun_email_validation_api')) {

    function mailgun_email_validation_api($lead_id, $request_array = array()) {

        $envSet = ENVIRONMENT;

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $emailValidateStatus = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";

        $type = "EMAIL_VALIDATION";
        $sub_type = "MAILGUN_EMAIL_VALIDATE";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing
        $email_type = !empty($request_array['email_type']) ? $request_array['email_type'] : "";

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
            $apiToken = $apiConfig["ApiToken"];

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            if (empty($email_type)) {
                throw new Exception("Missing Email Type");
            }

            if (!in_array($email_type, array(1, 2))) {
                throw new Exception("Email Type out of range");
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadFullDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {

                $applicationDetails = $appDataReturnArr['app_data'];
                $customer_seq_id = $applicationDetails["customer_seq_id"];
                if ($email_type == 1) {
                    $input_email = $applicationDetails["email"];
                    $input_email_status = trim(strtoupper($applicationDetails["email_verified_status"]));
                } else if ($email_type == 2) {
                    $input_email = $applicationDetails["alternate_email"];
                    $input_email_status = trim(strtoupper($applicationDetails["alternate_email_verified_status"]));
                }
            } else {
                throw new Exception("Application details does not exist.");
            }

            if ($email_type == 1 && empty($input_email)) {
                throw new Exception("Personal email does not exist.");
            } else if ($email_type == 2 && empty($input_email)) {
                throw new Exception("Office email does not exist.");
            } else if ($email_type == 1 && $input_email_status == "YES") {
                throw new Exception("Personal email already verified.");
            } else if ($email_type == 2 && $input_email_status == "YES") {
                throw new Exception("Office email already verified.");
            }

            $input_request_array = array("address" => $input_email);

            $apiRequestJson = json_encode($input_request_array);

            $apiHeaders = array(
//                "Authorization: Basic " . base64_encode("api:$apiToken"),
                "Content-Type:multipart/form-data",
            );

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
//                $apiResponseJson = '{"address":"SHUBHAMGINGER@gmail.com","is_disposable_address":false,"is_role_address":false,"reason":[],"result":"deliverable","risk":"low"}';
            } else {

                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($curl, CURLOPT_USERPWD, 'api_key:' . $apiToken);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $input_request_array);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                $apiResponseJson = curl_exec($curl);
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

                    if (!empty($apiResponseData['result'])) {
                        $apiStatusId = 1;

                        if ($apiResponseData['result'] == 'deliverable') {
                            $emailValidateStatus = 1;
                        } else {
                            $emailValidateStatus = 2;
                            $errorMessage = json_encode($apiResponseData['reason']);
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
        $insertApiLog["mailgun_user_id"] = $user_id;
        $insertApiLog["mailgun_method_id"] = 1;
        $insertApiLog["mailgun_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["mailgun_api_status_id"] = $apiStatusId;
        $insertApiLog["mailgun_api_request"] = addslashes($apiRequestJson);
        $insertApiLog["mailgun_api_response"] = addslashes($apiResponseJson);
        $insertApiLog["mailgun_api_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["mailgun_api_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["mailgun_api_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_mailgun_logs", $insertApiLog);

        $update_val = "NO";
        if ($apiStatusId == 1 && !empty($emailValidateStatus)) {
            $update_val = ($emailValidateStatus == 1) ? "YES" : "NO";
            $update_datetime = ($emailValidateStatus == 1) ? $apiRequestDateTime : NULL;
            if ($email_type == 1) {
                $ci->IntegrationModel->update('lead_customer', ['customer_seq_id' => $customer_seq_id], ["email_verified_status" => $update_val, "email_verified_on" => $update_datetime]);
            } else if ($email_type == 2) {
                $ci->IntegrationModel->update('lead_customer', ['customer_seq_id' => $customer_seq_id], ["alternate_email_verified_status" => $update_val, "alternate_email_verified_on" => $update_datetime]);
            }
        }


        if ($apiStatusId == 1) {
            $call_description = "EMAIL VALIDATION API(Success) | Email : $input_email | Vaild : " . $update_val;
        } else {
            $call_description = "EMAIL VALIDATION API(Fail) | Email : $input_email | Error : " . $errorMessage;
        }


        $lead_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $applicationDetails["status"],
            'stage' => $applicationDetails["stage"],
            'lead_followup_status_id' => $applicationDetails["lead_status_id"],
            'remarks' => addslashes($call_description),
            'created_on' => date("Y-m-d H:i:s")
        ];
        $ci->IntegrationModel->insert("lead_followup", $lead_followup);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['email'] = $input_email;
        $returnResponseData['email_validate'] = $update_val;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }


        return $returnResponseData;
    }

}

if (!function_exists('sendgrid_email_validation_api')) {

    function sendgrid_email_validation_api($lead_id, $request_array = array()) {

        $envSet = ENVIRONMENT;

        $ci = & get_instance();
        $ci->load->helper('integration/integration_config');
        $ci->load->helper('commonfun');
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $emailValidateStatus = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";

        $type = "EMAIL_VALIDATION";
        $sub_type = "SENDGRID_EMAIL_VALIDATE";

        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing
        $email_type = !empty($request_array['email_type']) ? $request_array['email_type'] : "";

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
            $apiToken = $apiConfig["ApiToken"];

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            if (empty($email_type)) {
                throw new Exception("Missing Email Type");
            }

            if (!in_array($email_type, array(1, 2))) {
                throw new Exception("Email Type out of range");
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadFullDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {

                $applicationDetails = $appDataReturnArr['app_data'];
                $customer_seq_id = $applicationDetails["customer_seq_id"];
                if ($email_type == 1) {
                    $input_email = $applicationDetails["email"];
                    $input_email_status = trim(strtoupper($applicationDetails["email_verified_status"]));
                } else if ($email_type == 2) {
                    $input_email = $applicationDetails["alternate_email"];
                    $input_email_status = trim(strtoupper($applicationDetails["alternate_email_verified_status"]));
                }
            } else {
                throw new Exception("Application details does not exist.");
            }

            if ($email_type == 1 && empty($input_email)) {
                throw new Exception("Personal email does not exist.");
            } else if ($email_type == 2 && empty($input_email)) {
                throw new Exception("Office email does not exist.");
            } else if ($email_type == 1 && $input_email_status == "YES") {
                throw new Exception("Personal email already verified.");
            } else if ($email_type == 2 && $input_email_status == "YES") {
                throw new Exception("Office email already verified.");
            }

            $input_request_array = array("email" => $input_email);

            $apiRequestJson = json_encode($input_request_array);

            $apiToken = "Authorization: Bearer " . $apiToken;
            $apiHeaders = array('Content-Type: application/json', $apiToken
            );

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
//                $apiResponseJson = '{"address":"SHUBHAMGINGER@gmail.com","is_disposable_address":false,"is_role_address":false,"reason":[],"result":"deliverable","risk":"low"}';
            } else {

                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                $apiResponseJson = curl_exec($curl);
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

                    if (!empty($apiResponseData['result'])) {
                        $apiStatusId = 1;

                        if ($apiResponseData['result']['verdict'] == 'Valid') {
                            $emailValidateStatus = 1;
                        } elseif ($apiResponseData['result']['verdict'] == 'Invalid') {
                            $emailValidateStatus = 2;
                            $errorMessage = 'Invalid email id.';
                        } else {
                            $emailValidateStatus = 2;
                            $errorMessage = json_encode($apiResponseData['reason']);
                        }
                    } elseif (!empty($apiResponseData['errors'])) {
                        $temp_error = !empty($apiResponseData['errors']['message']) ? $apiResponseData['errors']['message'] : "Some error occurred. Please try again.";
                        throw new ErrorException($temp_error);
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
        $insertApiLog["ev_user_id"] = $user_id;
        $insertApiLog["ev_provider_id"] = 3;
        $insertApiLog["ev_method_id"] = $email_type;
        $insertApiLog["ev_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["ev_email"] = $input_email;
        $insertApiLog["ev_api_status_id"] = $apiStatusId;
        $insertApiLog["ev_email_validate_status"] = ($emailValidateStatus == 1) ? 1 : 2;
        $insertApiLog["ev_request"] = addslashes($apiRequestJson);
        $insertApiLog["ev_response"] = addslashes($apiResponseJson);
        $insertApiLog["ev_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : $errorMessage;
        $insertApiLog["ev_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["ev_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $return_log_id = $ci->IntegrationModel->insert("api_email_verification_logs", $insertApiLog);

        $update_val = "NO";
        if ($apiStatusId == 1 && !empty($emailValidateStatus)) {
            $update_val = ($emailValidateStatus == 1) ? "YES" : "NO";
            $update_datetime = ($emailValidateStatus == 1) ? $apiRequestDateTime : NULL;
            if ($email_type == 1) {
                $ci->IntegrationModel->update('lead_customer', ['customer_seq_id' => $customer_seq_id], ["email_verified_status" => $update_val, "email_verified_on" => $update_datetime]);
            } else if ($email_type == 2) {
                $ci->IntegrationModel->update('lead_customer', ['customer_seq_id' => $customer_seq_id], ["alternate_email_verified_status" => $update_val, "alternate_email_verified_on" => $update_datetime]);
            }
        }

        if ($apiStatusId == 1) {
            $call_description = "EMAIL VALIDATION API(Success) | Email : $input_email | Vaild : " . $update_val;
        } else {
            $call_description = "EMAIL VALIDATION API(Fail) | Email : $input_email | Error : " . $errorMessage;
        }

        $lead_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $applicationDetails["status"],
            'stage' => $applicationDetails["stage"],
            'lead_followup_status_id' => $applicationDetails["lead_status_id"],
            'remarks' => addslashes($call_description),
            'created_on' => date("Y-m-d H:i:s")
        ];

        $ci->IntegrationModel->insert("lead_followup", $lead_followup);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['email'] = $input_email;
        $returnResponseData['email_validate'] = $update_val;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }


        return $returnResponseData;
    }


}