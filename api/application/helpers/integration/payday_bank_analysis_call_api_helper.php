<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('payday_bank_analysis_api_call')) {

    function payday_bank_analysis_api_call($method_name = "", $lead_id = 0, $doc_id = 0, $request_array = array()) {

        $responseArray = array("status" => 0, "error_msg" => "");

        $opertion_array = array(
            "BANK_STATEMENT_UPLOAD" => 1,
            "BANK_STATEMENT_DOWNLOAD" => 2,
        );

        $method_id = $opertion_array[$method_name];

        if ($method_id == 1) {
            $responseArray = bank_analysis_doc_upload_api($lead_id, $doc_id, $request_array);
        } else if ($method_id == 2) {
            $responseArray = bank_analysis_doc_download_api($doc_id, $request_array);
        } else {
            $responseArray["error_msg"] = "invalid opertation called";
        }

//        traceObject($responseArray);

        return $responseArray;
    }

}


if (!function_exists('bank_analysis_doc_upload_api')) {

    function bank_analysis_doc_upload_api($lead_id, $doc_id, $request_array = array()) {

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

        $type = "BANK_ANALYSIS";
        $sub_type = "UPLOAD_DOC";

        $hardcode_response = false;
        $document_path = FCPATH . 'upload/';
        $filename = "";
        $return_document_id = "";
        $direct_download = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing

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

            if (empty($doc_id)) {
                throw new Exception("Missing Doc Id.");
            }

            if (empty($user_id)) {
                throw new Exception("User session has been expired. Please login again.");
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];
            } else {
                throw new Exception("Lead does not exist.");
            }

            $docDataReturnArr = $ci->IntegrationModel->getLeadDocumentDetails($lead_id, $doc_id);

            if ($docDataReturnArr['status'] === 1) {
                $documentDetails = $docDataReturnArr['docs_data'];
                $filename = $documentDetails['file'];
                $return_document_id = $documentDetails['docs_novel_return_id'];
            } else {
                throw new Exception("Bank Statement Document does not exist with the application.");
            }

            if (empty($filename)) {
                throw new Exception("Document does not exist..");
            }

//            if (!in_array(strtoupper($documentDetails['docs_type']), ['BANK STATEMENT'])) {
//                throw new Exception("Document type must be Bank Statement.");
//            }
//
            if (!in_array($documentDetails['docs_master_id'], [6])) {
                throw new Exception("Document sub type must be Bank Statement.");
            }

            $filePath = $document_path . $filename;

            if (!file_exists($filePath)) {
                throw new Exception("Document does not exist on file location.");
            }

            if (!empty($return_document_id)) {
                $returnResponseData = bank_analysis_doc_download_api($return_document_id, $request_array);
                return $returnResponseData;
            }

            $pdf_password = (($documentDetails['pwd'] != null) ? trim($documentDetails['pwd']) : '');

            $cartFileObject = [
                "file" => new CURLFile($filePath, '', $filename),
                'metadata' => '{"password":"' . $pdf_password . '","bank":"","name":""}'
            ];

            $tempcartFileObject = $cartFileObject;
            $tempcartFileObject['file'] = $filePath;

            $apiRequestJson = json_encode($tempcartFileObject);

            if (empty($cartFileObject)) {
                throw new Exception("Unable to load document.");
            }

            $apiHeaders = [
                'content-type: multipart/form-data',
                'auth-token: ' . $apiToken
            ];

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
//                $apiResponseJson = '{"Click2Call":"Success", "Response":{"Record":"Inserted", "RecordId":"000017629560:Naman_UAT_Test:1"}}';
            } else {
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $cartFileObject);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 60);
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

                    if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("submitted", "processed", "in progress", "downloaded"))) {//Deleted need to ask from provider
                        if (!empty($apiResponseData['docId'])) {
                            $return_document_id = $apiResponseData['docId'];
                            $apiStatusId = 1;

                            if (in_array(strtolower($apiResponseData['status']), array("processed", "downloaded"))) {
                                $direct_download = true;
                            }
                        } else {
                            throw new ErrorException("Return document id is not available.");
                        }
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
        $insertApiLog["cart_method_id"] = 1;
        $insertApiLog["cart_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["cart_doc_id"] = !empty($doc_id) ? $doc_id : NULL;
        $insertApiLog["cart_api_status_id"] = $apiStatusId;
        $insertApiLog["cart_request"] = addslashes($apiRequestJson);
        $insertApiLog["cart_response"] = addslashes($apiResponseJson);
        $insertApiLog["cart_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["cart_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["cart_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["cart_return_novel_doc_id"] = $return_document_id;
        $insertApiLog["cart_user_id"] = $user_id;

        $return_log_id = $ci->IntegrationModel->insert("api_banking_cart_log", $insertApiLog);

        if (!empty($return_document_id)) {
            $ci->IntegrationModel->update('docs', ['docs_id' => $doc_id], ['docs_novel_return_id' => $return_document_id]);
        }

        if (!empty($applicationDetails['lead_status_id'])) {
            $insert_banking_api_followup = [
                'lead_id' => $lead_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $applicationDetails['status'],
                'stage' => $applicationDetails['stage'],
                'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                'created_on' => date("Y-m-d H:i:s"),
                'remarks' => (!empty($errorMessage) ? "Banking verification failed : " . $errorMessage : "API bank statement has been uploaded successfully.")
            ];

            $ci->IntegrationModel->insert("lead_followup", $insert_banking_api_followup);
        }
        
        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['return_doc_id'] = $return_document_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }

        if ($apiStatusId == 1 && !empty($return_document_id) && $direct_download == true) {
            if (in_array(strtolower($apiResponseData['status']), array("processed", "downloaded"))) {
                $returnResponseData = bank_analysis_doc_download_api($return_document_id, $request_array);
            }
        }

        return $returnResponseData;
    }

}

if (!function_exists('bank_analysis_doc_download_api')) {

    function bank_analysis_doc_download_api($docs_novel_return_id, $request_array = array()) {

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

        $type = "BANK_ANALYSIS";
        $sub_type = "DOWNLOAD_DOC";

        $hardcode_response = false;
        $return_document_id = "";
        $bankParseData = "";

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;

        $applicationDetails = array();

        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "9999"; //for testing

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

            if (empty($docs_novel_return_id)) {
                throw new Exception("Missing Novel Document Id.");
            }

            $docDataReturnArr = $ci->IntegrationModel->getLeadDocumentDetailsByNovelId($docs_novel_return_id);

            if ($docDataReturnArr['status'] === 1) {
                $documentDetails = $docDataReturnArr['docs_data'];
                $lead_id = $documentDetails['lead_id'];
                $doc_id = $documentDetails['docs_id'];
                $return_document_id = $docs_novel_return_id;
            } else {
                throw new Exception("Novel Document does not exist with the application.");
            }

            if (empty($lead_id)) {
                throw new Exception("Missing Lead Id.");
            }

            if (empty($doc_id)) {
                throw new Exception("Missing Doc Id.");
            }

            $appDataReturnArr = $ci->IntegrationModel->getLeadDetails($lead_id);

            if ($appDataReturnArr['status'] === 1) {
                $applicationDetails = $appDataReturnArr['app_data'];
            } else {
                throw new Exception("Lead does not exist.");
            }


            $apiHeaders = [
                'Content-Type: text/plain',
                'auth-token: ' . $apiToken
            ];

            $apiRequestJson = $docs_novel_return_id;

            if ($debug == 1) {
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            if ($hardcode_response && $envSet == 'development') {
//                $apiResponseJson = '{"Click2Call":"Success", "Response":{"Record":"Inserted", "RecordId":"000017629560:Naman_UAT_Test:1"}}';
            } else {
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 60);
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

                    if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("downloaded", "processed")) && strpos(strtolower($response_data_array['message']), 'fraud') === false) {
                        if (!empty($apiResponseData['data'])) {
                            $apiStatusId = 1;
                            $bankParseData = $apiResponseData['data'];
                        } else {
                            throw new ErrorException("Return data is empty.");
                        }
                    } else if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("submitted"))) {
                        if (!empty($apiResponseData['data'])) {
                            $apiStatusId = 1;
                            $bankParseData = $apiResponseData['data'];
                        } else {
                            throw new ErrorException("Return data is empty.");
                        }
                    } else if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("in progress"))) {
                        if (!empty($apiResponseData['message'])) {
                            throw new ErrorException($apiResponseData['message']);
                        } else {
                            throw new ErrorException("Document is still in progress.");
                        }
                    } else if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("rejected"))) {
                        if (!empty($apiResponseData['message'])) {
                            throw new ErrorException($apiResponseData['message']);
                        } else {
                            throw new ErrorException("Some error occurred. Please try again.");
                        }
                    } else if (strpos(strtolower($response_data_array['message']), 'fraud') !== false) {
                        throw new ErrorException($response_data_array['message']);
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
        $insertApiLog["cart_method_id"] = 2;
        $insertApiLog["cart_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
        $insertApiLog["cart_doc_id"] = !empty($doc_id) ? $doc_id : NULL;
        $insertApiLog["cart_api_status_id"] = $apiStatusId;
        $insertApiLog["cart_request"] = addslashes($apiRequestJson);
        $insertApiLog["cart_response"] = addslashes($apiResponseJson);
        $insertApiLog["cart_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["cart_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["cart_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
        $insertApiLog["cart_return_novel_doc_id"] = $return_document_id;
        $insertApiLog["cart_user_id"] = $user_id;

        $return_log_id = $ci->IntegrationModel->insert("api_banking_cart_log", $insertApiLog);

        if (!empty($applicationDetails['lead_status_id'])) {
            $insert_banking_api_followup = [
                'lead_id' => $lead_id,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'status' => $applicationDetails['status'],
                'stage' => $applicationDetails['stage'],
                'lead_followup_status_id' => $applicationDetails['lead_status_id'],
                'created_on' => date("Y-m-d H:i:s"),
                'remarks' => (!empty($errorMessage) ? "Banking download failed : " . $errorMessage : "API Banking cart data download successfully.")
            ];

            $ci->IntegrationModel->insert("lead_followup", $insert_banking_api_followup);
        }

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['log_id'] = $return_log_id;
        $returnResponseData['return_doc_id'] = $return_document_id;
        $returnResponseData['error_msg'] = !empty($errorMessage) ? $errorMessage : "";
        $returnResponseData['data'] = $bankParseData;

        if ($debug == 1) {
            $returnResponseData['actual_error'] = $insertApiLog["error_msg"];
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
            $returnResponseData['parse_response'] = $apiResponseData;
        }
        return $returnResponseData;
    }

}
