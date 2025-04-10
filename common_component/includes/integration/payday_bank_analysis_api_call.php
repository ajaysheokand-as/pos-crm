<?php

require_once(COMP_PATH . '/s3/vendor/autoload.php');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function payday_bank_analysis_api_call($method_name = "", $lead_id = 0, $request_array = array()) {

    common_log_writer(7, "bank_analysis_api_call started | $lead_id");

    $responseArray = array("status" => 0, "error_msg" => "");

    $opertion_array = array(
        "BANK_STATEMENT_UPLOAD" => 1,
        "BANK_STATEMENT_DOWNLOAD" => 2,
    );

    $method_id = $opertion_array[$method_name];

    $doc_id = $request_array['doc_id'];

    if ($method_id == 1) {
        $responseArray = bank_analysis_doc_upload_api($lead_id, $doc_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = bank_analysis_doc_download_api($doc_id, $request_array);
    } else {
        $responseArray["error_msg"] = "invalid opertation called";
    }

    common_log_writer(7, "bank_analysis_api_call end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function bank_analysis_doc_upload_api($lead_id, $doc_id, $request_array = array()) {

    common_log_writer(7, "bank_analysisn_statement_upload_api_call started | $lead_id");

    $response_array = array("status" => 0, "errors" => "");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;
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
    $document_path = COMP_DOC_PATH;
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

        $leadModelObj = new LeadModel();

        $appDataReturnArr = $leadModelObj->getLeadDetails($lead_id);

        if ($appDataReturnArr['status'] === 1) {
            $applicationDetails = $appDataReturnArr['app_data'];
        } else {
            throw new Exception("Lead does not exist.");
        }

        $docDataReturnArr = $leadModelObj->getLeadDocumentDetails($lead_id, $doc_id);

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


        if (!in_array($documentDetails['docs_master_id'], [6])) {
            throw new Exception("Document sub type must be Bank Statement.");
        }

        if (COMP_DOC_S3_FLAG == true) {
            // $temp_file = file_put_contents(COMP_TEMP_DOC_PATH . $filename, file_get_contents(COMP_DOC_URL . $filename));
            // $filePath = COMP_TEMP_DOC_PATH . $filename;
            $filePath = getFileFromS3($filename);
        } else {
            $filePath = $document_path . $filename;
        }

        if (COMP_DOC_S3_FLAG != true && !file_exists($filePath)) {
            throw new Exception("Document does not exist on file location.");
        }

        if (!empty($return_document_id)) {
            $response_array = bank_analysis_doc_download_api($return_document_id, $request_array);
            return $response_array;
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

        if (COMP_DOC_S3_FLAG == true) {
            unlink($filePath);
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

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData['status']) && in_array(strtolower($apiResponseData['status']), array("submitted", "processed", "in progress", "downloaded"))) { //Deleted need to ask from provider
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

    $return_log_id = $leadModelObj->insertTable("api_banking_cart_log", $insertApiLog);

    if (!empty($return_document_id)) {
        $leadModelObj->updateTable('docs', ['docs_novel_return_id' => $return_document_id], " docs_id=$doc_id");
    }

    if (!empty($applicationDetails['lead_status_id'])) {
        $insert_banking_api_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $applicationDetails['status'],
            'stage' => $applicationDetails['stage'],
            'lead_followup_status_id' => $applicationDetails['lead_status_id'],
            'created_on' => date("Y-m-d H:i:s"),
            'remarks' => (!empty($errorMessage) ? "Banking verification failed : " . $errorMessage : "API bank statement has been uploaded successfully. (Cart DOC ID - " . $return_document_id . ")")
        ];

        $leadModelObj->insertTable("lead_followup", $insert_banking_api_followup);
    }


    $response_array['status'] = $apiStatusId;
    $response_array['log_id'] = $return_log_id;
    $response_array['return_doc_id'] = $return_document_id;
    $response_array['error_msg'] = !empty($errorMessage) ? $errorMessage : "";

    if ($debug == 1) {
        $response_array['actual_error'] = $insertApiLog["error_msg"];
        $response_array['raw_request'] = $apiRequestJson;
        $response_array['raw_response'] = $apiResponseJson;
        $response_array['parse_response'] = $apiResponseData;
    }

    if ($apiStatusId == 1 && !empty($return_document_id) && $direct_download == true) {
        if (in_array(strtolower($apiResponseData['status']), array("processed", "downloaded"))) {
            $response_array = bank_analysis_doc_download_api($return_document_id, $request_array);
        }
    }

    return $response_array;
}

function bank_analysis_doc_download_api($docs_novel_return_id, $request_array = array()) {

    common_log_writer(7, "bank_analysisn_statement_download_api_call started | $lead_id");

    $response_array = array("status" => 0, "errors" => "");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;
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


        $leadModelObj = new LeadModel();

        $docDataReturnArr = $leadModelObj->getLeadDocumentDetailsByNovelId($docs_novel_return_id);

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

        $appDataReturnArr = $leadModelObj->getLeadDetails($lead_id);

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

                $apiResponseData = common_trim_data_array($apiResponseData);

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

    $return_log_id = $leadModelObj->insertTable("api_banking_cart_log", $insertApiLog);

    if (!empty($applicationDetails['lead_status_id'])) {
        $insert_banking_api_followup = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $applicationDetails['status'],
            'stage' => $applicationDetails['stage'],
            'lead_followup_status_id' => $applicationDetails['lead_status_id'],
            'created_on' => date("Y-m-d H:i:s"),
            'remarks' => (!empty($errorMessage) ? "Banking download failed : " . $errorMessage : "API Banking cart data download successfully.")
        ];

        $leadModelObj->insertTable("lead_followup", $insert_banking_api_followup);
    }

    $response_array = array();
    $response_array['status'] = $apiStatusId;
    $response_array['log_id'] = $return_log_id;
    $response_array['return_doc_id'] = $return_document_id;
    $response_array['error_msg'] = !empty($errorMessage) ? $errorMessage : "";
    $response_array['data'] = $bankParseData;

    if ($debug == 1) {
        $response_array['actual_error'] = $insertApiLog["error_msg"];
        $response_array['raw_request'] = $apiRequestJson;
        $response_array['raw_response'] = $apiResponseJson;
        $response_array['parse_response'] = $apiResponseData;
    }

    return $response_array;
}

function getFileFromS3($filename) {
    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $type = "S3_BUCKET";
    $sub_type = "";

    try {
        $apiConfig = integration_config($type, $sub_type);
        
        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }
        
        $fileKey = $apiConfig['folder_name'] . '/' . $filename;
        $expiry = '+5 minutes'; // Set the expiration time for the URL

        $s3Client = new S3Client([
            'version' => $apiConfig['version'],
            'region'  => $apiConfig["region"],
            'credentials' => [
                'key'    => $apiConfig["access_key"],
                'secret' => $apiConfig["secret_key"],
            ],
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $apiConfig["bucket_name"],
            'Key'    => $fileKey,
        ]);

        $request = $s3Client->createPresignedRequest($cmd, $expiry);
        return (string) $request->getUri();
    } catch (AwsException $e) {
        throw new Exception($e->getMessage());
    }
}