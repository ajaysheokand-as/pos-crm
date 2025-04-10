
<?php

function address_match_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(11, "Address Match started | $lead_id | $method_name");

    $responseArray = array("status" => 0, "errors" => "");
    
    $opertion_array = array(
        "ADDRESS_MATCH" => 1,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = address_match_validation_api_call($method_id, $lead_id, $request_array);
    } else {
        $responseArray["errors"] = "invalid opertation called";
    }

    common_log_writer(11, "Address Match end | $lead_id | $method_name | " . json_encode($responseArray));
    return $responseArray;
}

function address_match_validation_api_call($method_id, $lead_id = 0, $request_array = array()) {
   
    common_log_writer(11, "address_match_validation_api_call started | $lead_id");
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
    
    $type = "SIGNZY_API";
    $sub_type = "ADDRESS_MATCH";

    $hardcode_response = false;
    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    $debug = 1;

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;
    $leadModelObj = new LeadModel();
    $token_string = "";

    $address1 = "";
    $address2 = "";
    $address_match = "";
    
    $match_flag = array(1,2,3,4,5);
    
    $AddressSameMatch = "";
    $lead_status_id = 0;

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
            throw new Exception("Missing lead id.");
        }

        $LeadDetails = $leadModelObj->getLeadFullDetails($lead_id);

        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = !empty($LeadDetails['app_data']) ? $LeadDetails['app_data'] : "";

        $lead_status_id = !empty($app_data['lead_status_id']) ? $app_data['lead_status_id'] : "";
        $AddressSameMatch = !empty($app_data['aa_same_as_current_address']) ? $app_data['aa_same_as_current_address'] : "";
        
        if($match_flag[0] ==1){
        $address1 = $app_data['current_house'] . ", " . $app_data['current_locality'] . ", " . $app_data['current_landmark'] . ", " . $app_data['city'] . ", " . $app_data['state'] . ", " . $app_data['cr_residence_pincode'];
        $address2 = $app_data['aa_current_house'] . ', ' . $app_data['aa_current_locality'] . ', ' . $app_data['aa_current_landmark'] . ', ' . $app_data['aa_city'] . ', ' . $app_data['aa_state'] . ', ' . $app_data['aa_cr_residence_pincode'];
        }else if($match_flag[1] ==2){
        $cibil_details = $leadModelObj->getCibilJsonData($lead_id);  
            if ($cibil_details['status'] == 1) {
            $cibil_address1 = $cibil_details['cibil_data']['REQUEST']['ADDRESSES']['ADDRESS'][0];
            $cibil_address2 = $cibil_details['cibil_data']['REQUEST']['ADDRESSES']['ADDRESS'][1];
            } 
        }else if($match_flag[2] ==3){
        $customer_employment = $leadModelObj->getCustomerEmploymentDetails($lead_id); 
            if ($customer_employment['status'] == 1) {
                $empOfficeAddress = $customer_employment['emp_data']['office_address'].', '.$customer_employment['emp_data']['emp_locality'].', '.$customer_employment['emp_data']['emp_lankmark'].', '.$customer_employment['emp_data']['m_state_name'].', '.$customer_employment['emp_data']['m_city_name'].', '.$customer_employment['emp_data']['emp_pincode'];    
            }
        }
       
        if (empty($address1)) {
            throw new Exception("Missing address match");
        }

        $token_return_array = signzy_token_api_call(1, $lead_id, $request_array);
        
        if ($token_return_array['status'] == 1) {
            $token_string = $token_return_array['token'];
            $token_return_user_id = $token_return_array['token_user_id'];
        } else {
            throw new Exception($token_return_array['errors']);
        }
        
        $token_string = "0t4Xycq1jMVLvoXgbs47iE4z6oCAV4bZBXSqB3n00U9JW9t1mVznl0R7BDQt5iKE";
        $token_return_user_id = "64c8dea00871ca0734439a04";

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('customerid', $token_return_user_id, $apiConfig["ApiUrl"]);
  
        if ($debug) {
            echo "<br/><br/>=======Request JSON=========<br/><br/>";
            echo $apiUrl;
        }
       
        if($match_flag[0] ==1){
     
            $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address1 . '",
                                    "address2": "' . $address2 . '"
                                }
                            }
                        }';
       
            }else if($match_flag[1] ==2){
                $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address1 . '",
                                    "cibil_address1": "' . $cibil_address1 . '"
                                }
                            }
                        }';
       
            
            }else if($match_flag[2] ==3){
             
                $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address1 . '",
                                    "emp_office_address": "' . $empOfficeAddress . '"
                                }
                            }
                        }';
            
            }else if($match_flag[3] ==4){
                $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address2 . '",
                                    "cibil_address1": "' . $cibil_address1 . '"
                                }
                            }
                        }';
            }else if($match_flag[4] ==5){
                $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address2 . '",
                                    "emp_office_address": "' . $empOfficeAddress . '"
                                }
                            }
                        }';
            }else{
                $apiRequestJson = '{
                            "task": "addressMatch",
                            "essentials": address1_vs_address2_match_Result{
                                "addressBlock": {
                                    "address1": "' . $address1 . '",
                                    "noMatch": "' . $address2 . '"
                                }
                            }
                        }';
            }
          
        print_r($apiRequestJson);die;
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 200);
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
            } 
            else {
            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);
            
            
            $score = $apiResponseData['result']['address1_vs_address2_match_Result']['addressMatchJaroWinklerScore']; 
       
            if (!empty($apiResponseData)) {
                
                            if ($AddressSameMatch == "YES") { 
                                $apiStatusId = 1;
                                $leadModelObj->insertApplicationLog($lead_id, $lead_status_id);
                                $insertApiLog = array();
                                $insertApiLog["am_provider_id"] = 2; //SIGNZY
                                $insertApiLog["am_method_id"] = $match_flag;
                                $insertApiLog["am_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
                                $insertApiLog["am_api_status_id"] = $$apiStatusIdapiStatusId;
                                $insertApiLog["am_match_status"] = 1;
                                $insertApiLog["am_request"] = addslashes($apiRequestJson);
                                $insertApiLog["am_response"] = addslashes($apiResponseJson);
                                $insertApiLog["am_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
                                $insertApiLog["am_request_datetime"] = $apiRequestDateTime;
                                $insertApiLog["am_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
                                $insertApiLog["am_user_id"] = $user_id;
                                $insertApiLog["am_score"] = $score;
                                
                
                                $response_array['status'] = $apiStatusId;
                                $response_array['data'] = $apiResponseData;
                                $response_array['re_current_house'] = $address1;
                                $response_array['aa_current_house'] = $address2;

                                $response_array['errors'] = !empty($errorMessage) ? "Address Match Error : " . $errorMessage : "";
                                if ($debug) {
                                    $response_array['request_json'] = $apiRequestJson;
                                    $response_array['response_json'] = $apiResponseJson;
                                }
                            } else {       
                                $insertApiLog = array();
                                $insertApiLog["am_provider_id"] = 2; //SIGNZY
                                $insertApiLog["am_method_id"] = $method_id;
                                $insertApiLog["am_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
                                $insertApiLog["am_api_status_id"] = $apiStatusId;
                                $insertApiLog["am_match_status"] = 2;
                                $insertApiLog["am_request"] = addslashes($apiRequestJson);
                                $insertApiLog["am_response"] = addslashes($apiResponseJson);
                                $insertApiLog["am_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
                                $insertApiLog["am_request_datetime"] = $apiRequestDateTime;
                                $insertApiLog["am_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
                                $insertApiLog["am_user_id"] = $user_id;
                                $insertApiLog["am_score"] = $score;
                                
                                $response_array['status'] = $apiStatusId;
                                $response_array['data'] = $apiResponseData;
                                $response_array['re_current_house'] = $address1;
                                $response_array['aa_current_house'] = $address2;

                                $response_array['errors'] = !empty($errorMessage) ? "Address Match Error : " . $errorMessage : "";
                                if ($debug) {
                                    $response_array['request_json'] = $apiRequestJson;
                                    $response_array['response_json'] = $apiResponseJson;
                                }
                            }
                            $leadModelObj->insertTable("api_address_match_logs", $insertApiLog);
                          } else {
                            throw new ErrorException("Address response does not received from api.");
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
  
    return $response_array;
}

?>
