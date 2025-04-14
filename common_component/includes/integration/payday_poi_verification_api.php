
<?php

function poi_verification_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(3, "POI VERIFICATION API started | $lead_id");

    $responseArray = array("status" => 0, "errors" => "");

    $opertion_array = array(
        "GET_PAN_VERFICATION" => 1,
        "GET_PAN_VERFICATION_V3" => 2,
        "DIGITAP_PANEXTENSION" => 3,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = pan_verifcaition_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = pan_verifcaition_v3_api_call($method_id, $lead_id, $request_array);
    } else if ($method_id == 3) {
        $responseArray = pan_verifcaition_digitap_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(3, "POI VERIFICATION API end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function pan_verifcaition_api_call($method_id, $lead_id = 0, $request_array = array()) {

    common_log_writer(3, "pan_verifcaition_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $pan_valid_status = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $father_name = "";

    $type = "SIGNZY_API";
    $sub_type = "PAN_FETCH";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    $pan_no = "";
    $lead_status_id = 0;
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";
    $item_id_string = "";
    $access_token_string = "";

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

        if (empty($lead_id)) {
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

        $pan_no = !empty($app_data['pancard']) ? trim(strtoupper($app_data['pancard'])) : "";

        $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
        $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
        $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

        $customer_full_name = $first_name;
        $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
        $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

        if (empty($pan_no)) {
            throw new Exception("Missing pancard number.");
        }



        $panLogData = $leadModelObj->getPanValidateLastApiLog($lead_id);

        if ($panLogData['status'] == 1) {

            if (!empty($panLogData['pan_log_data'])) {

                if ($panLogData['pan_log_data']['poi_veri_proof_no'] == $pan_no) {
                    $api_call_flag = false;
                    $apiResponseJson = $panLogData['pan_log_data']['poi_veri_response'];
                }
            }
        }
        if ($api_call_flag) {


            //$token_return_array = signzy_token_api_call(1, $lead_id, $request_array);


            // if ($token_return_array['status'] == 1) {
            //     $token_string = $token_return_array['token'];
            //     $request_array['token_user_id'] = $token_return_array['token_user_id'];
            //     $request_array['token'] = $token_return_array['token'];
            // } else {
            //     throw new Exception($token_return_array['errors']);
            // }

            // $identity_return_array = signzy_identity_object_api_call('individualPan', $lead_id, $request_array);

            // if ($identity_return_array['status'] == 1) {
            //     $item_id_string = $identity_return_array['item_id_string'];
            //     $access_token_string = $identity_return_array['access_token_string'];
            // } else {
            //     throw new Exception($identity_return_array['errors']);
            // }


            // $apiRequestJson = '{
            //                 "service":"Identity",
            //                 "itemId":"' . $item_id_string . '",
            //                 "task":"fetch",
            //                 "accessToken":"' . $access_token_string . '",
            //                 "essentials":{
            //                     "number":"' . $pan_no . '"
            //                   }
            //             }';

            // $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            // if ($debug) {
            //     echo "<br/><br/>=======Request JSON=========<br/><br/>";
            //     echo $apiRequestJson;
            // }


            // $apiHeaders = array(
            //     "content-type: application/json",
            //     "accept-language: en-US,en;q=0.8",
            //     "accept: */*",
            //     "Authorization: $token_string"
            // );

            // if ($debug) {
            //     echo "<br/><br/>=======Request Header=========<br/><br/>";
            //     echo json_encode($apiHeaders);
            // }

            $apiRequestDateTime = date("Y-m-d H:i:s");

            // $curl = curl_init($apiUrl);
            // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            // curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            // curl_setopt($curl, CURLOPT_POST, true);
            // curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
            // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            // curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            // $apiResponseJson = curl_exec($curl);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.signzy.app/api/v3/pan/fetchV2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                     "number": "' . $pan_no . '"
                }',
                CURLOPT_HTTPHEADER => array(
                    // 'Authorization: ScTTTviEmhU1EPT79VM6QV9NUHImPkBm',
                    'Authorization: n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey',
                    'Content-Type: application/json'
                ),
            ));

            $apiResponseJson = curl_exec($curl);


            //  PRINT_R($apiResponseJson);die;


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

                //  print_r($apiResponseJson); die;

                if (!empty($apiResponseData)) {

                    $apiResponseData = common_trim_data_array($apiResponseData);

                    if (!empty($apiResponseData)) {
                        //     echo '<pre>';
                        //   print_r($apiResponseData); die;

                        $apiResponseData = $apiResponseData['result'];

                        if (!empty($apiResponseData['number']) && !empty($apiResponseData['name'])) {
                            $apiStatusId = 1;
                        } else {
                            throw new ErrorException("PAN details does not received from API.");
                        }
                    } else {
                        throw new ErrorException("Please check raw response for error details");
                    }
                } else {
                    throw new ErrorException("Empty response from CRIF API");
                }
            }
        } else {
            $apiStatusId = 1;
            $apiResponseData = json_decode($apiResponseJson, true);
            $apiResponseData = common_trim_data_array($apiResponseData);
            $apiResponseData = $apiResponseData['response']['result'];
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

    $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 0, 'pancard_verified_on' => NULL, 'father_name' => '', 'updated_at' => date("Y-m-d H:i:s")]);

    if ($apiStatusId == 1) {

        $lead_remarks = "PAN VERIFICATION API CALL(Success) | PAN NO : $pan_no | Customer Name : " . $customer_full_name;
        $lead_remarks .= "<br>NSDL FETCH DETAILS";
        $lead_remarks .= "<br>Name : " . $apiResponseData['name'] . " | Father Name : " . $apiResponseData['fatherName'];

        $father_name = trim(strtoupper($apiResponseData['fatherName']));

        $pan_name_array = common_parse_name($apiResponseData['name']);

        $pan_valid_status = 1;

        if ($first_name != trim(strtoupper($pan_name_array['first_name']))) {
            $pan_valid_status = 2;
        }

        if ($middle_name != trim(strtoupper($pan_name_array['middle_name']))) {
            $pan_valid_status = 2;
        }

        if ($sur_name != trim(strtoupper($pan_name_array['last_name']))) {
            $pan_valid_status = 2;
        }


        if ($pan_valid_status == 2) {
            $lead_remarks .= "<br>Result : Name Matched with PAN Details";
        } else {
            $lead_remarks .= "<br>Result : Name does not matched with PAN Details";
        }


        if ($pan_valid_status == 1) {
            $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 1, 'pancard_verified_on' => date("Y-m-d H:i:s"), 'father_name' => $father_name, 'updated_at' => date("Y-m-d H:i:s")]);
        }
    } else {
        $lead_remarks = "PAN VERIFICATION API CALL(Failed) | PAN NO : $pan_no | Error : " . $errorMessage;
    }


    $leadModelObj->insertApplicationLog($lead_id, $lead_status_id, $lead_remarks);

    if ($api_call_flag) {
        $insertApiLog = array();
        $insertApiLog["poi_veri_provider"] = 1;
        $insertApiLog["poi_veri_method_id"] = $method_id;
        $insertApiLog["poi_veri_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["poi_veri_api_status_id"] = $apiStatusId;
        $insertApiLog["poi_veri_request"] = addslashes($apiRequestJson);
        $insertApiLog["poi_veri_response"] = addslashes($apiResponseJson);
        $insertApiLog["poi_veri_proof_no"] = $pan_no;
        $insertApiLog["poi_veri_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["poi_veri_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["poi_veri_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["poi_veri_user_id"] = $user_id;
        $insertApiLog["poi_veri_father_name"] = $father_name;
        $leadModelObj->insertTable("api_poi_verification_logs", $insertApiLog);
    }
    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['pan_valid_status'] = $pan_valid_status;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "PAN API Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function pan_verifcaition_v3_api_call($method_id, $lead_id = 0, $request_array = array()) {
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    common_log_writer(3, "pan_verifcaition_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $pan_valid_status = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $father_name = "";

    $type = "SIGNZY_API";
    $sub_type = "PAN_FETCHV3";

    $hardcode_response = false;

    //    if ($envSet == 'development') {
    //        $hardcode_response = true;
    //    }

    $debug = !empty($_REQUEST['test']) ? 1 : 0;

    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    $profile_flag = !empty($request_array['profile_flag']) ? $request_array['profile_flag'] : 0;
    $profile_id = !empty($request_array['profile_id']) ? $request_array['profile_id'] : 0;
    $dual_pancard = !empty($request_array['dual_pancard']) ? $request_array['dual_pancard'] : "";
    $profile_stage_id = !empty($request_array['profile_journey_stage_id']) ? $request_array['profile_journey_stage_id'] : 0;
    $leadModelObj = new LeadModel();

    $pan_no = "";
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";
    $item_id_string = "";
    $access_token_string = "";
    $reference_type = 0;

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($debug == 1) {
            print_r($envSet);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $token_string = $apiConfig["Token"];

        if ($profile_flag == 1) {
            $reference_id = $profile_id;
            $reference_type = 1;

            $profileDetails = $leadModelObj->getProfileFullDetails($profile_id);

            if ($profileDetails['status'] != 1) {
                throw new Exception("Application details not found");
            }

            $app_data = $profileDetails['app_data'];

            $reference_status_id = !empty($profile_stage_id) ? $profile_stage_id : (!empty($app_data['cp_journey_stage']) ? $app_data['cp_journey_stage'] : "");

            $pan_no = !empty($app_data['cp_pancard']) ? trim(strtoupper($app_data['cp_pancard'])) : "";

            $first_name = !empty($app_data['cp_first_name']) ? trim(strtoupper($app_data['cp_first_name'])) : "";
            $middle_name = !empty($app_data['cp_middle_name']) ? trim(strtoupper($app_data['cp_middle_name'])) : "";
            $sur_name = !empty($app_data['cp_sur_name']) ? trim(strtoupper($app_data['cp_sur_name'])) : "";

            $customer_full_name = $first_name;
            $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
            $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

            if (empty($pan_no)) {
                throw new Exception("Missing pancard number.");
            }
        } else {
            $reference_id = $lead_id;
            $reference_type = 0;

            if (empty($lead_id)) {
                throw new Exception("Missing lead id.");
            }

            $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

            if ($LeadDetails['status'] != 1) {
                throw new Exception("Application details not found");
            }

            $app_data = $LeadDetails['app_data'];

            $reference_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

            if (!empty($dual_pancard)) {
                $pan_no = $dual_pancard;
            } else {
                $pan_no = !empty($app_data['pancard']) ? trim(strtoupper($app_data['pancard'])) : "";
            }

            $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
            $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
            $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

            $customer_full_name = $first_name;
            $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
            $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

            if (empty($pan_no)) {
                throw new Exception("Missing pancard number.");
            }
        }


        $panLogData = $leadModelObj->getPanValidateLastApiLog($reference_id, $reference_type);

        if ($panLogData['status'] == 1) {

            if (!empty($panLogData['pan_log_data'])) {

                if ($panLogData['pan_log_data']['poi_veri_proof_no'] == $pan_no) {
                    $api_call_flag = false;
                    $apiResponseJson = $panLogData['pan_log_data']['poi_veri_response'];
                }
            }
        }

        if ($api_call_flag) {

            $apiRequestJson = '{
                                "panNumber":"' . $pan_no . '"
                            }';

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            if ($debug) {
                echo "<br/><br/>=======Request JSON=========<br/><br/>";
                echo $apiRequestJson;
            }


            $apiHeaders = array(
                "content-type: application/json",
                "accept-language: en-US,en;q=0.8",
                "accept: */*",
                "Authorization: $token_string"
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

                        if (isset($apiResponseData['result']) && !empty($apiResponseData['result'])) {

                            $apiResponseData = $apiResponseData['result'];

                            if (!empty($apiResponseData['number']) && !empty($apiResponseData['name'])) {
                                $apiStatusId = 1;
                            } else {
                                throw new ErrorException("PAN details does not received from API.");
                            }
                        } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                            throw new ErrorException($apiResponseData['error']['message']);
                        } else {
                            $tmp_error_msg = "Some error occurred. Please try again.";
                            throw new ErrorException($tmp_error_msg);
                        }
                    } else {
                        throw new ErrorException("Please check raw response for error details");
                    }
                } else {
                    throw new ErrorException("Empty response from PAN API");
                }
            }
        } else {
            $apiStatusId = 1;
            $apiResponseData = json_decode($apiResponseJson, true);
            $apiResponseData = common_trim_data_array($apiResponseData);
            $apiResponseData = $apiResponseData['result'];
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

    if (empty($dual_pancard)) {
        if ($reference_type == 1) {
            $leadModelObj->updateCustomerProfileTable($profile_id, ['cp_pancard_verified_status' => 0, 'cp_father_name' => '', 'cp_updated_at' => date("Y-m-d H:i:s")]);
        } else {
            $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 0, 'pancard_verified_on' => NULL, 'father_name' => '', 'updated_at' => date("Y-m-d H:i:s")]);
        }
    }

    if ($apiStatusId == 1) {

        if (!empty($dual_pancard)) {
            $lead_remarks = "Dual PAN VERIFICATION API CALL(Success) | PAN NO : $pan_no | Customer Name : " . $customer_full_name;
        } else {
            $lead_remarks = "PAN VERIFICATION API CALL(Success) | PAN NO : $pan_no | Customer Name : " . $customer_full_name;
        }

        $lead_remarks .= "<br>NSDL FETCH DETAILS";
        $lead_remarks .= "<br>Name : " . $apiResponseData['name'] . " | Father Name : " . $apiResponseData['fatherName'];

        $father_name = trim(strtoupper($apiResponseData['fatherName']));

        $pan_name_array = common_parse_name($apiResponseData['name']);

        $pan_valid_status = 1;

        if ($reference_type != 1) {


            if ($first_name != trim(strtoupper($pan_name_array['first_name']))) {
                $pan_valid_status = 2;
            }

            if ($middle_name != trim(strtoupper($pan_name_array['middle_name']))) {
                $pan_valid_status = 2;
            }

            if ($sur_name != trim(strtoupper($pan_name_array['last_name']))) {
                $pan_valid_status = 2;
            }


            if ($pan_valid_status == 1) {
                $lead_remarks .= "<br>Result : Name Matched with PAN Details";
            } else {
                $lead_remarks .= "<br>Result : Name does not matched with PAN Details";
            }
        }

        if ($pan_valid_status == 1 && empty($dual_pancard)) {

            if ($reference_type == 1) {
                $update_array['cp_pancard_verified_status'] = 1;
                $update_array['cp_pancard_verified_on'] = date("Y-m-d H:i:s");
                $update_array['cp_father_name'] = $father_name;
                $update_array['cp_first_name'] = $pan_name_array['first_name'];
                $update_array['cp_middle_name'] = $pan_name_array['middle_name'];
                $update_array['cp_sur_name'] = $pan_name_array['last_name'];
                $update_array['cp_updated_at'] = date("Y-m-d H:i:s");

                $leadModelObj->updateCustomerProfileTable($profile_id, $update_array);
            } else {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 1, 'pancard_verified_on' => date("Y-m-d H:i:s"), 'father_name' => $father_name, 'updated_at' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        if (!empty($dual_pancard)) {
            $lead_remarks = "Dual PAN VERIFICATION API CALL(Failed) | PAN NO : $pan_no | Error : " . $errorMessage;
        } else {
            $lead_remarks = "PAN VERIFICATION API CALL(Failed) | PAN NO : $pan_no | Error : " . $errorMessage;
        }
    }

    if ($reference_type == 1) {
        $leadModelObj->insertProfileFollowupLog($profile_id, $reference_status_id, $lead_remarks);
    } else {
        $leadModelObj->insertApplicationLog($lead_id, $reference_status_id, $lead_remarks);
    }

    if ($api_call_flag) {
        $insertApiLog = array();
        $insertApiLog["poi_veri_provider"] = 1;
        $insertApiLog["poi_veri_method_id"] = $method_id;
        $insertApiLog["poi_veri_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["poi_veri_profile_id"] = !empty($profile_id) ? $profile_id : NULL;
        $insertApiLog["poi_veri_api_status_id"] = $apiStatusId;
        $insertApiLog["poi_veri_request"] = addslashes($apiRequestJson);
        $insertApiLog["poi_veri_response"] = addslashes($apiResponseJson);
        $insertApiLog["poi_veri_proof_no"] = $pan_no;
        $insertApiLog["poi_veri_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["poi_veri_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["poi_veri_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["poi_veri_user_id"] = $user_id;
        $insertApiLog["poi_veri_father_name"] = $father_name;

        if (!empty($dual_pancard)) {
            $insertApiLog["poi_other_pan_veri_flag"] = 1;
        }
        $leadModelObj->insertTable("api_poi_verification_logs", $insertApiLog);
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['pan_valid_status'] = $pan_valid_status;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "PAN API Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}

function pan_verifcaition_digitap_api_call($method_id, $lead_id = 0, $request_array = array()) {
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    common_log_writer(3, "pan_verifcaition_api_call started | $lead_id");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $response_array = array("status" => 0, "errors" => "");

    $envSet = COMP_ENVIRONMENT;
    $api_call_flag = true;
    $apiStatusId = 0;
    $pan_valid_status = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $apiResponseData = "";
    $errorMessage = "";
    $curlError = "";
    $father_name = "";

    $type = "DIGITAP_API";
    $sub_type = "PAN_EXTENSION";

    $hardcode_response = false;

    $debug = !empty($_REQUEST['test']) ? 1 : 0;
    if ($envSet == 'development') {
        $debug = 1;
        // $hardcode_response = true;
    }


    $applicationDetails = array();

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    $profile_flag = !empty($request_array['profile_flag']) ? $request_array['profile_flag'] : 0;
    $profile_id = !empty($request_array['profile_id']) ? $request_array['profile_id'] : 0;
    $dual_pancard = !empty($request_array['dual_pancard']) ? $request_array['dual_pancard'] : "";
    $profile_stage_id = !empty($request_array['profile_journey_stage_id']) ? $request_array['profile_journey_stage_id'] : 0;
    $leadModelObj = new LeadModel();

    $pan_no = "";
    $first_name = "";
    $middle_name = "";
    $sur_name = "";
    $customer_full_name = "";
    $token_string = "";
    $item_id_string = "";
    $access_token_string = "";
    $reference_type = 0;

    try {

        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($debug == 1) {
            print_r($envSet);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $token_string = $apiConfig["ApiToken"];

        if ($profile_flag == 1) {
            $reference_id = $profile_id;
            $reference_type = 1;

            $profileDetails = $leadModelObj->getProfileFullDetails($profile_id);

            if ($profileDetails['status'] != 1) {
                throw new Exception("Application details not found");
            }

            $app_data = $profileDetails['app_data'];

            $reference_status_id = !empty($profile_stage_id) ? $profile_stage_id : (!empty($app_data['cp_journey_stage']) ? $app_data['cp_journey_stage'] : "");

            $pan_no = !empty($app_data['cp_pancard']) ? trim(strtoupper($app_data['cp_pancard'])) : "";

            $first_name = !empty($app_data['cp_first_name']) ? trim(strtoupper($app_data['cp_first_name'])) : "";
            $middle_name = !empty($app_data['cp_middle_name']) ? trim(strtoupper($app_data['cp_middle_name'])) : "";
            $sur_name = !empty($app_data['cp_sur_name']) ? trim(strtoupper($app_data['cp_sur_name'])) : "";

            $customer_full_name = $first_name;
            $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
            $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

            if (empty($pan_no)) {
                throw new Exception("Missing pancard number.");
            }
        } else {
            $reference_id = $lead_id;
            $reference_type = 0;

            if (empty($lead_id)) {
                throw new Exception("Missing lead id.");
            }

            $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

            if ($LeadDetails['status'] != 1) {
                throw new Exception("Application details not found");
            }

            $app_data = $LeadDetails['app_data'];

            $reference_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";

            if (!empty($dual_pancard)) {
                $pan_no = $dual_pancard;
            } else {
                $pan_no = !empty($app_data['pancard']) ? trim(strtoupper($app_data['pancard'])) : "";
            }

            $first_name = !empty($app_data['first_name']) ? trim(strtoupper($app_data['first_name'])) : "";
            $middle_name = !empty($app_data['middle_name']) ? trim(strtoupper($app_data['middle_name'])) : "";
            $sur_name = !empty($app_data['sur_name']) ? trim(strtoupper($app_data['sur_name'])) : "";

            $customer_full_name = $first_name;
            $customer_full_name .= !empty($middle_name) ? " " . $middle_name : "";
            $customer_full_name .= !empty($sur_name) ? " " . $sur_name : "";

            if (empty($pan_no)) {
                throw new Exception("Missing pancard number.");
            }
        }

        $panLogData = $leadModelObj->getPanValidateLastApiLog($reference_id, $reference_type, array("method_id" => 3));

        if ($panLogData['status'] == 1) {

            if (!empty($panLogData['pan_log_data'])) {

                if ($panLogData['pan_log_data']['poi_veri_proof_no'] == $pan_no) {
                    $api_call_flag = false;
                    $apiResponseJson = $panLogData['pan_log_data']['poi_veri_response'];
                }
            }
        }

        if ($api_call_flag) {

            $apiRequestJson = '{
                                "pan": "' . $pan_no . '",
                                "client_ref_num": "' . $reference_id . '"
                            }';

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            if ($debug) {
                echo "<br/><br/>=======Request JSON=========<br/><br/>";
                echo $apiRequestJson;
            }


            $apiHeaders = array(
                "Authorization: $token_string",
                'Content-Type: application/json'
            );

            if ($debug) {
                echo "<br/><br/>=======Request Header=========<br/><br/>";
                echo json_encode($apiHeaders);
            }

            $apiRequestDateTime = date("Y-m-d H:i:s");

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
                CURLOPT_POSTFIELDS => $apiRequestJson,
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

                    $apiResponseData = common_trim_data_array($apiResponseData);

                    if (!empty($apiResponseData)) {

                        if (isset($apiResponseData) && !empty($apiResponseData)) {

                            if (!empty($apiResponseData) && $apiResponseData['http_response_code'] == 200 && $apiResponseData['result']['fullname'] != "") {
                                $apiResponseData = $apiResponseData['result'];
                                $apiStatusId = 1;
                                $dob = "";
                                if (!empty($apiResponseData['dob'])) {
                                    $dob = date("Y-m-d", strtotime($apiResponseData['dob']));
                                }
                                if ($dob == "1970-01-01") {
                                    $dob = "";
                                }

                                $reWriteResponseData = array(
                                    "result" => array(
                                        "name" => $apiResponseData['fullname'],
                                        "number" => $apiResponseData['pan'],
                                        "typeOfHolder" =>  $apiResponseData['pan_type'],
                                        "isIndividual" => true,
                                        "isValid" => true,
                                        "firstName" => $apiResponseData['first_name'],
                                        "middleName" => $apiResponseData['middle_name'],
                                        "lastName" => $apiResponseData['last_name'],
                                        "fatherName" => $apiResponseData['father_name'] ?? "",
                                        "gender" => $apiResponseData['gender'],
                                        "aadhaar_number" => $apiResponseData['aadhaar_number'],
                                        "aadhaar_linked" => $apiResponseData['aadhaar_linked'],
                                        "dob" => $dob,
                                        "title" => "",
                                        "panStatus" => "VALID",
                                        "panStatusCode" => "E",
                                        "aadhaarSeedingStatus" => "Successful"
                                    )
                                );
                                $apiResponseJson = "";
                                $apiResponseJson = json_encode($reWriteResponseData);

                                $apiResponseData = $reWriteResponseData['result'];

                                if ($debug == 1) {
                                    echo "<br/><br/> =======Re Write Response======<br/><br/>" . $apiResponseJson;
                                }
                            } else {
                                throw new ErrorException("PAN details does not received from API.");
                            }
                        } else if (isset($apiResponseData['error']['message']) && !empty($apiResponseData['error']['message'])) {
                            throw new ErrorException($apiResponseData['error']['message']);
                        } else {
                            $tmp_error_msg = "Some error occurred. Please try again.";
                            throw new ErrorException($tmp_error_msg);
                        }
                    } else {
                        throw new ErrorException("Please check raw response for error details");
                    }
                } else {
                    throw new ErrorException("Empty response from PAN API");
                }
            }
        } else {
            $apiStatusId = 1;
            $apiResponseData = json_decode($apiResponseJson, true);
            $apiResponseData = common_trim_data_array($apiResponseData);
            $apiResponseData = $apiResponseData['result'];
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

    if (empty($dual_pancard)) {
        if ($reference_type == 1) {
            $leadModelObj->updateCustomerProfileTable($profile_id, ['cp_pancard_verified_status' => 0, 'cp_father_name' => '', 'cp_updated_at' => date("Y-m-d H:i:s")]);
        } else {
            $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 0, 'pancard_verified_on' => NULL, 'father_name' => '', 'updated_at' => date("Y-m-d H:i:s")]);
        }
    }

    if ($apiStatusId == 1) {

        if (!empty($dual_pancard)) {
            $lead_remarks = "Dual PAN VERIFICATION API CALL(Success) | PAN NO : $pan_no | Customer Name : " . $customer_full_name;
        } else {
            $lead_remarks = "PAN VERIFICATION API CALL(Success) | PAN NO : $pan_no | Customer Name : " . $customer_full_name;
        }

        $lead_remarks .= "<br>NSDL FETCH DETAILS";
        $lead_remarks .= "<br>Name : " . $apiResponseData['name'] . " | Father Name : " . $apiResponseData['fatherName'];

        $father_name = trim(strtoupper($apiResponseData['fatherName']));

        $pan_name_array = common_parse_name($apiResponseData['name']);

        $pan_valid_status = 1;

        if ($reference_type != 1) {


            if ($first_name != trim(strtoupper($pan_name_array['first_name']))) {
                $pan_valid_status = 2;
            }

            if ($middle_name != trim(strtoupper($pan_name_array['middle_name']))) {
                $pan_valid_status = 2;
            }

            if ($sur_name != trim(strtoupper($pan_name_array['last_name']))) {
                $pan_valid_status = 2;
            }


            if ($pan_valid_status == 1) {
                $lead_remarks .= "<br>Result : Name Matched with PAN Details";
            } else {
                $lead_remarks .= "<br>Result : Name does not matched with PAN Details";
            }
        }

        if ($pan_valid_status == 1 && empty($dual_pancard)) {

            if ($reference_type == 1) {
                $update_array['cp_pancard_verified_status'] = 1;
                $update_array['cp_pancard_verified_on'] = date("Y-m-d H:i:s");
                $update_array['cp_father_name'] = $father_name;
                // $update_array['cp_dob'] = $apiResponseData['dob'];
                $update_array['cp_first_name'] = $pan_name_array['first_name'];
                $update_array['cp_middle_name'] = $pan_name_array['middle_name'];
                $update_array['cp_sur_name'] = $pan_name_array['last_name'];
                $update_array['cp_updated_at'] = date("Y-m-d H:i:s");

                $leadModelObj->updateCustomerProfileTable($profile_id, $update_array);
            } else {
                $leadModelObj->updateLeadCustomerTable($lead_id, ['pancard_verified_status' => 1, 'pancard_verified_on' => date("Y-m-d H:i:s"), 'father_name' => $father_name, 'updated_at' => date("Y-m-d H:i:s")]);
            }
        }
    } else {
        if (!empty($dual_pancard)) {
            $lead_remarks = "Dual PAN VERIFICATION API CALL(Failed) | PAN NO : $pan_no | Error : " . $errorMessage;
        } else {
            $lead_remarks = "PAN VERIFICATION API CALL(Failed) | PAN NO : $pan_no | Error : " . $errorMessage;
        }
    }

    if ($reference_type == 1) {
        $leadModelObj->insertProfileFollowupLog($profile_id, $reference_status_id, $lead_remarks);
    } else {
        $leadModelObj->insertApplicationLog($lead_id, $reference_status_id, $lead_remarks);
    }

    if ($api_call_flag) {
        $insertApiLog = array();
        $insertApiLog["poi_veri_provider"] = 2;
        $insertApiLog["poi_veri_method_id"] = $method_id;
        $insertApiLog["poi_veri_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["poi_veri_profile_id"] = !empty($profile_id) ? $profile_id : NULL;
        $insertApiLog["poi_veri_api_status_id"] = $apiStatusId;
        $insertApiLog["poi_veri_request"] = addslashes($apiRequestJson);
        $insertApiLog["poi_veri_response"] = addslashes($apiResponseJson);
        $insertApiLog["poi_veri_proof_no"] = $pan_no;
        $insertApiLog["poi_veri_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["poi_veri_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["poi_veri_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["poi_veri_user_id"] = $user_id;
        $insertApiLog["poi_veri_father_name"] = $father_name;

        if (!empty($dual_pancard)) {
            $insertApiLog["poi_other_pan_veri_flag"] = 1;
        }
        $leadModelObj->insertTable("api_poi_verification_logs", $insertApiLog);
    }

    //Preparing response array
    $response_array['status'] = $apiStatusId;
    $response_array['pan_valid_status'] = $pan_valid_status;
    $response_array['data'] = $apiResponseData;
    $response_array['errors'] = !empty($errorMessage) ? "PAN API Error : " . $errorMessage : "";
    $response_array['request_json'] = $apiRequestJson;
    $response_array['response_json'] = $apiResponseJson;

    return $response_array;
}
?>
