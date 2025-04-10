<?php

require_once(COMP_PATH . '/s3/vendor/autoload.php');

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\ObjectUploader;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function call_s3_bucket($method_name = "", $lead_id = 0, $request_array = array()) {
    common_log_writer(8, "call_to_s3_bucket started | $lead_id");

    $responseArray = array("status" => 0, "error_msg" => "");

    $opertion_array = array(
        "DOCUMENT_UPLOAD" => 1,
        "DOCUMENT_DOWNLOAD" => 2,
    );

    $method_id = $opertion_array[$method_name];

    if ($method_id == 1) {
        $responseArray = s3_document_upload_file($lead_id, $request_array);
    } else if ($method_id == 2) {
        $responseArray = s3_document_download_file($lead_id, $request_array);
    }

    common_log_writer(8, "call_to_s3_bucket end | $lead_id | $method_name | " . json_encode($responseArray));

    return $responseArray;
}

function s3_document_upload_file($lead_id, $request_array = array()) {
    $response_array = array("status" => 0, "errors" => "");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $apiResponseJson = "";
    $errorMessage = "";

    $type = "S3_BUCKET";
    $sub_type = "";

    $filename = "";

    $debug = 0;

    try {


        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiVersion = $apiConfig['version'];
        $apiRegion = $apiConfig["region"];
        $apiAccessKey = $apiConfig["access_key"];
        $apiSecretKey = $apiConfig["secret_key"];
        $apiBucket = $apiConfig["bucket_name"];
        $apiBucketFolder = $apiConfig["folder_name"];

        if (!empty($lead_id)) {
            $leadModelObj = new LeadModel();
            $appDataReturnArr = $leadModelObj->getLeadDetails($lead_id);
            if ($appDataReturnArr['status'] != 1) {
                throw new Exception("Lead does not exist.");
            }
            $new_file_name = $lead_id;
        } else {
            $new_file_name = $request_array['new_file_name'];
        }

        if (empty($request_array['file'])) {
            throw new Exception("Request file cannnot be empty.");
        }

        if (!empty($request_array['bucket_name'])) {
            $apiBucket = $request_array['bucket_name'];
        }

        $s3 = new S3Client([
            'version' => $apiVersion,
            'region' => $apiRegion,
            'credentials' => [
                'key' => $apiAccessKey,
                'secret' => $apiSecretKey,
            ],
        ]);

        // echo $request_array['file'];die;

        if ($request_array['flag'] == 1) {
            $base64String = $request_array['file'];
            $source = base64_decode($base64String);
            $extension = $request_array['ext'];
        } else {

            $request_file = $request_array['file'];

            $filename = $request_file['name'];

            $tmp_file_name = $request_file["tmp_name"];

            $file_Path = COMP_TEMP_DOC_PATH . $filename;

            $extension = strtolower(end(explode(".", $filename)));

            move_uploaded_file($tmp_file_name, $file_Path);

            $source = fopen($file_Path, 'rb');
        }

        $new_file_name = $new_file_name . '_lms_' . date('YmdHis') . rand(111, 999) . '.' . $extension;
        $key = $apiBucketFolder . "/" . $new_file_name;

        $uploader = new ObjectUploader(
            $s3,
            $apiBucket,
            $key,
            $source
        );
        do {
            try {

                $result = $uploader->upload();
                if ($result["@metadata"]["statusCode"] == '200') {
                    $apiResponseJson = json_encode($result);
                    $apiStatusId = 1;

                    if (!empty($file_Path) && !empty($filename) && file_exists($file_Path) && $request_array['flag'] != 1) {
                        unlink($file_Path);
                    }
                }
            } catch (MultipartUploadException $e) {
                rewind($source);
                $result = new MultipartUploader($s3, $source, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if ($debug == 1) {
            echo "<br/><br/> =======Response Plain ======<br/><br/>" . $apiResponseJson;
        }
    } catch (Aws\S3\Exception\S3Exception $e) {
        $apiStatusId = 5;
        $errorMessage = $e->getMessage();
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

    $response_array['status'] = $apiStatusId;

    if ($debug == 1) {
        $response_array['actual_error'] = $errorMessage;
        $response_array['raw_response'] = $apiResponseJson;
    }

    if ($apiStatusId == 1) {
        $response_array['file_name'] = $new_file_name;
        $response_array['msg'] = "File Uploaded Successfully";
    } else {
        $response_array['error_msg'] = !empty($errorMessage) ? $errorMessage : "";
    }

    return $response_array;
}

function s3_document_download_file($lead_id, $request_array = array()) {

    $response_array = array("status" => 0, "errors" => "");

    require_once(COMP_PATH . '/includes/integration/integration_config.php');

    $envSet = COMP_ENVIRONMENT;
    $apiStatusId = 0;
    $errorMessage = "";

    $type = "S3_BUCKET";
    $sub_type = "";

    $file_name = "";

    $debug = 0;

    try {
        $apiConfig = integration_config($type, $sub_type);
        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiVersion = $apiConfig['version'];
        $apiRegion = $apiConfig["region"];
        $apiAccessKey = $apiConfig["access_key"];
        $apiSecretKey = $apiConfig["secret_key"];
        $apiBucket = $apiConfig["bucket_name"];
        $apiBucketFolder = $apiConfig["folder_name"];

        if (empty($request_array['file'])) {
            throw new Exception("Requested file cannot be empty..");
        } else {
            $file_name = $request_array['file'];
        }

        $key = $apiBucketFolder . "/" . $file_name;
        $s3 = new S3Client([
            'version' => $apiVersion,
            'region' => $apiRegion,
            'credentials' => [
                'key' => $apiAccessKey,
                'secret' => $apiSecretKey,
            ],
        ]);
        $downloader = $s3->getObject([
            'Bucket' => $apiBucket,
            'Key' => $key,
        ]);

        if ($downloader['@metadata']['statusCode'] != 200) {
            $apiStatusId = 0;
        } else {
            $apiStatusId = 1;
            $documentBody = $downloader['Body'];
            $response_array['header_content_type'] = $downloader['ContentType'];
            $response_array['document_body'] = $documentBody;
        }

        $response_array['status'] = $apiStatusId;

        return $response_array;
    } catch (Exception $exception) {
        echo "Failed to download File with error: " . $exception->getMessage();
        exit("Please fix error with file downloading before continuing.");
    }
}
