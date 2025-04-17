<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronMiscellaneousController extends CI_Controller {

    private $notification_mail = "info@tejasloan.com";

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronMiscellaneous_Model', 'MiscellaneousModel');
    }

    public function kycLoanDocs() {
        $start_datetime = date("d-m-Y H:i:s");
        $time_close = intval(date("Hi"));

        if ($time_close > 1743) {
            echo "here";
            die;
        }

        $doc_path = '/home/fintechcloud/public_html/';
        $get_doc_path = '/home/fintechcloud/public_html/upload/';

        $kyc_counter['kyc_success'] = array();

        $request_data = array();

        $total_loans = 0;

        if (true) {

            if (!is_dir($doc_path . 'kyc1/')) {
                mkdir($doc_path . 'kyc1/', 0777, TRUE);
            }

            //            $request_data['start_date'] = $start_date;
            //            $request_data['end_date'] = $end_date;
            //            $tempLoanData = $this->MiscellaneousModel->get_customer_loan($request_data);
            $tempLoanData = $this->MiscellaneousModel->get_customer_loan();
            //traceObject($tempLoanData);
            $counter = 0;
            if (!empty($tempLoanData['status'])) {

                $document_push_array = array();

                $total_loans = count($tempLoanData['loan']);

                foreach ($tempLoanData['loan'] as $row) {

                    $loan_no = $row['loan_no'];

                    if (empty($loan_no)) {
                        continue;
                    }
                    $document_push_array[$loan_no] = array();

                    $request_data['lead_id'] = $row['lead_id'];
                    $request_data['loan_no'] = $row['loan_no'];
                    $request_data['pancard'] = $row['pancard'];

                    $tempLoanKycDocs = $this->MiscellaneousModel->get_loans_kyc_documents($request_data);

                    if (!is_dir($doc_path . 'kyc1/' . $loan_no . '/')) {
                        mkdir($doc_path . 'kyc1/' . $loan_no . '/', 0777, TRUE);
                    }

                    if (!empty($tempLoanKycDocs['status'])) {

                        $update_kyc_loan_flag = 0;

                        foreach ($tempLoanKycDocs['docs'] as $docs) {
                            $docs_sub_type = preg_replace("!\s+!", "", $docs['sub_docs_type']);
                            $docs_sub_type = trim($docs_sub_type);
                            $docs_sub_type = str_replace(array(" ", "-", ":", "(", ")", ".", "'"), '_', $docs_sub_type);
                            $docs_sub_type = strtoupper($docs_sub_type);

                            if (empty($document_push_array[$loan_no][$docs_sub_type]) && !empty($docs['file'])) {

                                $ext = pathinfo($docs['file'], PATHINFO_EXTENSION);

                                $document_name = $docs_sub_type . '.' . $ext;

                                $image_upload_dir = $doc_path . 'kyc1/' . $loan_no . '/' . $document_name;

                                if (file_exists($get_doc_path . $docs['file'])) {

                                    $flag = file_put_contents($image_upload_dir, file_get_contents($get_doc_path . $docs['file']));

                                    if ($flag) {
                                        $document_push_array[$loan_no][$docs_sub_type] = $docs['docs_id'];
                                        $kyc_counter['kyc_success'][$loan_no][$docs_sub_type] = $document_name;
                                        $update_kyc_loan_flag = 1;
                                    }
                                }
                            }
                        }

                        if ($update_kyc_loan_flag == 1) {
                            $counter++;
                            $this->MiscellaneousModel->update('test_kyc_loan', ['kyc_loan_no' => $loan_no], ['kyc_loan_done' => 1, 'kyc_loan_done_datetime' => date("Y-m-d H:i:s")]);
                        }
                    }
                }
            }
        }

        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-KYC DOCS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        $message = "total loans : $total_loans | kyc_success = " . $counter;

        lw_send_email($email, $subject, $message);
    }

    public function aadhaarMasked() {

        $lead_remarks = '';

        $time_close = intval(date("Hi"));

        if ($time_close > 1742) {
            echo "here";
            die;
        }

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $disbursal_start_date = "2022-05-25";
        $disbursal_end_date = "2022-06-15";

        $tempDetails = $this->MiscellaneousModel->get_loan_list($disbursal_start_date, $disbursal_end_date);

        $start_datetime = date("d-m-Y H:i:s");

        $masked_counter = array('masked_success' => 0, 'masked_failed' => 0);

        $api_call_doc_ids = array();

        if (!empty($tempDetails)) {

            foreach ($tempDetails['loan'] as $customer_data) {

                if (empty($api_call_doc_ids[$customer_data['docs_id']]) && !empty($customer_data['lead_id']) && !empty($customer_data['docs_id']) && !empty($customer_data['docs_master_id'])) {

                    $api_call_doc_ids[$customer_data['docs_id']] = $customer_data['docs_id'];

                    $masked_aadhaar_return = $CommonComponent->call_aadhaar_masked_api($customer_data['lead_id'], $customer_data['docs_master_id'], $customer_data['docs_id']);

                    if ($masked_aadhaar_return['status'] == 5) {
                        $masked_counter['masked_success']++;
                    } else {
                        $masked_counter['masked_failed']++;
                    }
                }
            }
        }


        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-AADHAAR MASKED - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        $message = "masked_success=" . $masked_counter['masked_success'] . " | masked_failed=" . $masked_counter['masked_failed'];

        lw_send_email($email, $subject, $message);
    }

    public function aadhaarMaskedAllCases() {

        $get_file_data = '';

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $cron_name = "aadhaarMaskedAllCases";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        // $tempDetails = $this->MiscellaneousModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        // if (!empty($tempDetails['status'])) {
        //     echo "Already Cron in prcoess";
        //     die;
        // }

        // $cron_insert_id = $this->MiscellaneousModel->insert_cron_logs($cron_name);

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $tempDetails = $this->MiscellaneousModel->get_aadhaar_docs();

        $start_datetime = date("d-m-Y H:i:s");

        $masked_counter = array('masked_success' => 0, 'masked_failed' => 0);

        $api_call_doc_ids = array();

        echo "<pre>";

        if (!empty($tempDetails['status'])) {

            foreach ($tempDetails['docs'] as $customer_data) {
                if (empty($api_call_doc_ids[$customer_data['docs_id']]) && !empty($customer_data['lead_id']) && !empty($customer_data['docs_id']) && !empty($customer_data['docs_master_id'])) {

                    $api_call_doc_ids[$customer_data['docs_id']] = $customer_data['docs_id'];

                    $masked_aadhaar_return = $CommonComponent->call_aadhaar_masked_api($customer_data['lead_id'], $customer_data['docs_master_id'], $customer_data['docs_id']);

                    if ($masked_aadhaar_return['status'] == 5) {

                        $lead_remarks = "AADHAAR MASKED API CALL(Success) | AADHAAR NO : " . $masked_aadhaar_return['aadhaar_no'] . " | Customer Name : " . $masked_aadhaar_return['customer_full_name'];

                        $lead_data = $this->MiscellaneousModel->select(['lead_id' => $customer_data['lead_id']], "lead_id, lead_status_id", 'leads');

                        if ($lead_data->num_rows() > 0) {
                            $lead_data = $lead_data->row_array();
                            $lead_id = $lead_data['lead_id'];
                            $lead_status_id = $lead_data['lead_status_id'];
                        }

                        if (!empty($masked_aadhaar_return['aadhaar_masked_url'])) {
                            if (!empty($masked_aadhaar_return['aadhaar_docs_data'])) {

                                $aadhaar_doc_data = $masked_aadhaar_return['aadhaar_docs_data'];

                                $file_basename = basename($masked_aadhaar_return['aadhaar_masked_url']);

                                $file_name = "MASK_" . rand(1000000, 9999999) . $file_basename;
                            }
                            $get_file_data = file_get_contents($masked_aadhaar_return['aadhaar_masked_url']);

                            if (!empty($get_file_data)) {

                                $doc_flag = file_put_contents(UPLOAD_PATH . $file_name, $get_file_data);

                                $tmp_file_ext = pathinfo(UPLOAD_PATH . $file_name, PATHINFO_EXTENSION);

                                $upload_file = uploadDocument(UPLOAD_PATH . $file_name, $lead_id, 2, $tmp_file_ext);

                                if ($upload_file['status'] == 1) {

                                    print_r($upload_file);

                                    $docs_update_array = array();
                                    $docs_update_array['file'] = $upload_file['file_name'];
                                    $docs_update_array['docs_aadhaar_masked'] = 1;

                                    $this->MiscellaneousModel->update('docs', ['lead_id' => $lead_id, 'docs_id' => $customer_data['docs_id']], $docs_update_array);

                                    $lead_remarks .= "<br>Result : Aadhaar masked image stored.";
                                    unlink(UPLOAD_PATH . $file_name);
                                } else {

                                    $lead_remarks .= "<br>Result : Aadhaar masked image not stored.";
                                }
                            } else {

                                $lead_remarks .= "<br>Result : Aadhaar masked image not fetched.";
                            }
                        } else {
                            $lead_remarks .= "<br>Result : Aadhaar masked image URL not fetched.";
                        }
                        $user_id = 0;

                        if (isset($_SESSION['isUserSession']['user_id']) && !empty($_SESSION['isUserSession']['user_id'])) {
                            $user_id = $_SESSION['isUserSession']['user_id'];
                        }

                        $insert_log_array = array();
                        $insert_log_array['lead_id'] = $lead_id;
                        $insert_log_array['user_id'] = $user_id;
                        $insert_log_array['lead_followup_status_id'] = $lead_status_id;
                        $insert_log_array['remarks'] = addslashes($lead_remarks);
                        $insert_log_array['created_on'] = date("Y-m-d H:i:s");
                        $this->MiscellaneousModel->insert($insert_log_array, 'lead_followup');
                        $masked_counter['masked_success']++;
                    } else {
                        $masked_counter['masked_failed']++;
                    }
                }
            }
        }

        // if (!empty($cron_insert_id)) {
        //     $this->MiscellaneousModel->update_cron_logs($cron_insert_id, $masked_counter['masked_success'], $masked_counter['masked_failed']);
        // }

        // $email = "shubham.agrawal@loanwalle.com";
        // $subject = "PROD-$cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        // $message = "masked_success=" . $masked_counter['masked_success'] . " | masked_failed=" . $masked_counter['masked_failed'];

        //lw_send_email($email, $subject, $message);
    }

    public function renameDocs() {

        $start_datetime = date("d-m-Y H:i:s");

        $time_close = intval(date("Hi"));

        if ($time_close > 1741) {
            echo "here";
            die;
        }

        $final_doc_path = '/home/fintechcloud/public_html/upload/';
        $get_doc_path = '/home/fintechcloud/public_html/advlms/upload/';

        $tempLoanData = $this->MiscellaneousModel->get_all_documents();

        $counter = 0;

        if (!empty($tempLoanData['status'])) {

            $total_loans = count($tempLoanData['docs']);

            foreach ($tempLoanData['docs'] as $row) {

                $docs_id = $row['docs_id'];
                $lead_id = $row['lead_id'];
                $file_name = $row['file'];
                $docs_rename_status = $row['docs_rename_status'];

                if (!empty($docs_rename_status)) {
                    continue;
                }

                if (!empty($file_name)) {

                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);

                    $document_name = $lead_id . '_' . $docs_id . '_adv_' . date("YmodHis") . '_' . rand(1000, 9999) . '.' . strtolower($ext);

                    $image_upload_dir = $final_doc_path . $document_name;

                    if (file_exists($get_doc_path . $file_name)) {

                        $flag = file_put_contents($image_upload_dir, file_get_contents($get_doc_path . $file_name));

                        if ($flag) {

                            $counter++;
                            $this->MiscellaneousModel->update('docs', ['docs_id' => $docs_id], ['docs_rename_status' => 1, 'file' => $document_name]);
                        }
                    }
                }
            }
        }

        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-Advance Salary KYC DOCS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        echo $message = "total loans : $total_loans | counter = " . $counter;

        lw_send_email($email, $subject, $message);
        die("Done");
    }

    public function renameCollectionDocs() {

        $start_datetime = date("d-m-Y H:i:s");

        $time_close = intval(date("Hi"));

        if ($time_close > 1805) {
            echo "here";
            die;
        }

        $final_doc_path = '/home/fintechcloud/public_html/upload/';
        $get_doc_path = '/home/fintechcloud/public_html/advlms/upload/';

        $tempLoanData = $this->MiscellaneousModel->get_all_collection_documents();

        $counter = 0;

        if (!empty($tempLoanData['status'])) {

            $total_loans = count($tempLoanData['colldocs']);

            foreach ($tempLoanData['colldocs'] as $row) {

                $docs_id = $row['id'];
                $lead_id = $row['lead_id'];
                $file_name = $row['docs'];
                $docs_rename_status = $row['collection_docs_rename_status'];
                //
                if (!empty($docs_rename_status)) {
                    continue;
                }

                if (!empty($file_name)) {

                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);

                    $document_name = $lead_id . '_' . $docs_id . '_adv_' . date("YmdHis") . '_' . rand(1000, 9999) . '.' . strtolower($ext);

                    $image_upload_dir = $final_doc_path . $document_name;

                    if (file_exists($get_doc_path . $file_name)) {

                        $flag = file_put_contents($image_upload_dir, file_get_contents($get_doc_path . $file_name));

                        if ($flag) {

                            $counter++;
                            $this->MiscellaneousModel->update('collection', ['id' => $docs_id], ['collection_docs_rename_status' => 1, 'docs' => $document_name]);
                        }
                    }
                }
            }
        }

        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-Advance Collection KYC DOCS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        echo $message = "total loans : $total_loans | counter = " . $counter;

        lw_send_email($email, $subject, $message);
        die("Done");
    }

    public function getCollectionDocs() {

        $start_datetime = date("d-m-Y H:i:s");

        $time_close = intval(date("Hi"));

        if ($time_close > 1505) {
            echo "here";
            die;
        }

        $final_doc_path = '/home/fintechcloud/public_html/collex_docs/';
        $get_doc_path = '/home/fintechcloud/public_html/upload/';

        $tempLoanData = $this->MiscellaneousModel->get_all_collection_documents();

        $counter = 0;

        if (!empty($tempLoanData['status'])) {

            $total_loans = count($tempLoanData['colldocs']);

            foreach ($tempLoanData['colldocs'] as $row) {

                $docs_id = $row['id'];
                $lead_id = $row['lead_id'];
                $file_name = $row['docs'];
                $loan_no = $row['loan_no'];

                if (!empty($file_name)) {

                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);

                    $document_name = $loan_no . "_" . $lead_id . '_' . $docs_id . '.' . strtolower($ext);

                    $image_upload_dir = $final_doc_path . $document_name;

                    if (file_exists($get_doc_path . $file_name)) {

                        $flag = file_put_contents($image_upload_dir, file_get_contents($get_doc_path . $file_name));

                        if ($flag) {

                            $counter++;
                            $this->MiscellaneousModel->update('collection', ['id' => $docs_id], ['collection_type' => 1]);
                        }
                    }
                }
            }
        }

        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-Collection PAYMENT DOCS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        echo $message = "total loans : $total_loans | counter = " . $counter;

        lw_send_email($email, $subject, $message);
        die("Done");
    }

    public function updateFatherName() {

        $start_datetime = date("d-m-Y H:i:s");

        $time_close = intval(date("Hi"));

        if ($time_close > 1355) {
            echo "here";
            die;
        }

        $tempDetails = $this->db->query('SELECT poi_veri_id, poi_veri_lead_id, poi_veri_response, poi_veri_proof_no FROM  api_poi_verification_logs WHERE poi_veri_method_id=1 AND poi_veri_api_status_id=1 AND poi_veri_active=1');

        $total_loans = 0;
        $counter = 0;

        if (!empty($tempDetails->num_rows())) {

            $panDetails = $tempDetails->result_array();

            foreach ($panDetails as $pan_data) {

                $total_loans++;
                $lead_id = $pan_data['poi_veri_lead_id'];
                $poi_veri_id = $pan_data['poi_veri_id'];
                $panResponse = json_decode($pan_data['poi_veri_response'], true);
                $father_name = "";

                if (!empty($panResponse)) {

                    $father_name = strtoupper(trim($panResponse['response']['result']['fatherName']));

                    if (!empty($father_name)) {
                        $counter++;
                        $this->MiscellaneousModel->update('lead_customer', ['customer_lead_id' => $lead_id], ['father_name' => $father_name]);
                        $this->MiscellaneousModel->update('api_poi_verification_logs', ['poi_veri_id' => $poi_veri_id], ['poi_veri_father_name' => $father_name]);
                    }
                }
            }
        }

        $email = "shubham.agrawal@loanwalle.com";
        $subject = "PROD-Father name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
        echo $message = "total loans : $total_loans | counter = " . $counter;

        lw_send_email($email, $subject, $message);
        die("Done");
    }

    public function cibilLogToS3Bucket() {
        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT C.cibil_id, C.lead_id, C.cibil_file, C.s3_flag ";
        $sql .= " FROM tbl_cibil C ";
        $sql .= " WHERE C.s3_flag = 0 AND cibil_file IS NOT NULL";
        $sql .= " ORDER BY C.cibil_id ASC LIMIT 10000";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {

                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $uploadingStartTimestamp = microtime(true);
                $cibil_id = $cibil_data["cibil_id"];
                $lead_id = $cibil_data["lead_id"];
                $html_file = $cibil_data["cibil_file"];

                $apiStatusid = 0;
                $errorMessage = '';

                // Prepare file paths and names
                $filePaths = [
                    'html' => $this->prepareFile($html_file, "tbl_cibil_html", $cibil_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content'])) {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'cibil_file' => $uploadedFiles['html'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('tbl_cibil', ['cibil_id' => $cibil_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'cibil_id' => $cibil_id,
                    'lead_id' => $lead_id,
                    'html' => $uploadedFiles['html'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "CIBIL Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        // echo $message;
    }

    public function apiCibilLogToS3Bucket() {

        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT CL.cibil_id, CL.lead_id, CL.api1_request, CL.api1_response, CL.cibil_file, CL.s3_flag ";
        $sql .= " FROM tbl_cibil_log CL INNER JOIN leads LD ON (CL.lead_id = LD.lead_id) ";
        $sql .= " WHERE CL.s3_flag = 0 AND LD.lead_status_id IN (14, 16, 17, 18, 19, 8, 9)";
        $sql .= " ORDER BY CL.cibil_id ASC LIMIT 2000";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {

                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $apiStatusid = 0;
                $errorMessage = '';
                $uploadingStartTimestamp = microtime(true);
                $cibil_id = $cibil_data["cibil_id"];
                $lead_id = $cibil_data["lead_id"];
                $cibil_request = $cibil_data["api1_request"];
                $cibil_response = $cibil_data["api1_response"];
                $html_file = $cibil_data["cibil_file"];

                // Prepare file paths and names
                $filePaths = [
                    'request' => $this->prepareFile($cibil_request, "tbl_cibil_log_request", $cibil_id, $lead_id),
                    'response' => $this->prepareFile($cibil_response, "tbl_cibil_log_response", $cibil_id, $lead_id),
                    'html' => $this->prepareFile($html_file, "tbl_cibil_log_html", $cibil_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content'])) {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'api1_request' => $uploadedFiles['request'],
                        'api1_response' => $uploadedFiles['response'],
                        'cibil_file' => $uploadedFiles['html'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('tbl_cibil_log', ['cibil_id' => $cibil_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'cibil_id' => $cibil_id,
                    'lead_id' => $lead_id,
                    'request' => $uploadedFiles['request'],
                    'response' => $uploadedFiles['response'],
                    'html' => $uploadedFiles['html'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "API CIBIL Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        // echo $message;
    }

    public function apiBankingCartLogToS3Bucket() {
        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT BL.cart_log_id, BL.cart_lead_id, BL.cart_response, BL.s3_flag ";
        $sql .= " FROM api_banking_cart_log BL INNER JOIN leads LD ON (BL.cart_lead_id = LD.lead_id) ";
        $sql .= " WHERE BL.s3_flag = 0 AND LD.lead_status_id IN (7, 8, 9, 14, 16, 17, 18, 19) AND BL.cart_response != '' ";
        $sql .= " ORDER BY BL.cart_log_id ASC LIMIT 15000 ";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {

                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $apiStatusid = 0;
                $errorMessage = '';
                $uploadingStartTimestamp = microtime(true);
                $cart_log_id = $cibil_data["cart_log_id"];
                $lead_id = $cibil_data["cart_lead_id"];
                $json_data = $cibil_data["cart_response"];

                // Prepare file paths and names
                $filePaths = [
                    'json' => $this->prepareFile($json_data, "api_banking_cart_log_json", $cart_log_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content'])) {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'cart_response' => $uploadedFiles['json'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('api_banking_cart_log', ['cart_log_id' => $cart_log_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'cart_log_id' => $cart_log_id,
                    'lead_id' => $lead_id,
                    'json' => $uploadedFiles['json'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "Banking Cart Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        // echo $message;
    }

    public function apiEsignLogsToS3Bucket() {
        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT AE.esign_id, AE.esign_lead_id, AE.esign_response, AE.s3_flag ";
        $sql .= " FROM api_esign_logs AE INNER JOIN leads LD ON (AE.esign_lead_id = LD.lead_id) ";
        $sql .= " WHERE AE.s3_flag = 0 AND LD.lead_status_id IN (7, 8, 9, 14, 16, 17, 18, 19) AND AE.esign_response != '' ";
        $sql .= " ORDER BY AE.esign_id ASC LIMIT 10000 ";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {

                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $apiStatusid = 0;
                $errorMessage = '';
                $uploadingStartTimestamp = microtime(true);
                $esign_id = $cibil_data["esign_id"];
                $lead_id = $cibil_data["esign_lead_id"];
                $json_data = $cibil_data["esign_response"];

                // Prepare file paths and names
                $filePaths = [
                    'json' => $this->prepareFile($json_data, "api_esign_logs_json", $esign_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content'])) {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'esign_response' => $uploadedFiles['json'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('api_esign_logs', ['esign_id' => $esign_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'esign_id' => $esign_id,
                    'lead_id' => $lead_id,
                    'json' => $uploadedFiles['json'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "ESIGN Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        // echo $message;
    }

    public function apiEycLogsToS3Bucket() {
        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT AE.ekyc_id, AE.ekyc_lead_id, AE.ekyc_request, AE.ekyc_response, AE.s3_flag ";
        $sql .= " FROM api_ekyc_logs AE INNER JOIN leads LD ON (AE.ekyc_lead_id = LD.lead_id) ";
        $sql .= " WHERE AE.s3_flag = 0 AND LD.lead_status_id NOT IN (12, 13, 25, 30, 35, 37) AND AE.ekyc_response != '' ";
        $sql .= "  ORDER BY AE.ekyc_id ASC LIMIT 10000 ";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {
                $filePaths = [];
                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $apiStatusid = 0;
                $errorMessage = '';
                $uploadingStartTimestamp = microtime(true);
                $ekyc_id = $cibil_data["ekyc_id"];
                $lead_id = $cibil_data["ekyc_lead_id"];
                $request_json_data = $cibil_data["ekyc_request"];
                $response_json_data = $cibil_data["ekyc_response"];

                // Prepare file paths and names
                $filePaths = [
                    'request_json' => $this->prepareFile($request_json_data, "api_ekyc_logs_request_json", $ekyc_id, $lead_id),
                    'response_json' => $this->prepareFile($response_json_data, "api_ekyc_logs_response_json", $ekyc_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content']) && $type != 'request_json') {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'ekyc_request' => $uploadedFiles['request_json'],
                        'ekyc_response' => $uploadedFiles['response_json'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('api_ekyc_logs', ['ekyc_id' => $ekyc_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'ekyc_id' => $ekyc_id,
                    'lead_id' => $lead_id,
                    'request_json' => $uploadedFiles['request_json'],
                    'response_json' => $uploadedFiles['response_json'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "EKYC Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        // echo $message;
    }

    public function apiPoiOCRLogsToS3Bucket() {
        $return_array = array();
        $start_datetime = date("d-m-Y H:i:s");
        $i = 0;

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $sql = "SELECT POI.poi_ocr_id, POI.poi_ocr_lead_id, POI.poi_ocr_request, POI.poi_ocr_response, POI.s3_flag ";
        $sql .= " FROM api_poi_ocr_logs POI INNER JOIN leads LD ON (POI.poi_ocr_lead_id = LD.lead_id) ";
        $sql .= " WHERE POI.s3_flag = 0 AND LD.lead_status_id IN(7, 8, 9, 14, 15, 16, 17, 18, 19) AND POI.poi_ocr_response != '' ";
        $sql .= " ORDER BY POI.poi_ocr_id ASC LIMIT 10000 ";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $cibilLogs = $tempDetails->result_array();

            foreach ($cibilLogs as $cibil_data) {
                $filePaths = [];
                if ($cibil_data["s3_flag"] == 1) {
                    continue;
                }

                $apiStatusid = 0;
                $errorMessage = '';
                $uploadingStartTimestamp = microtime(true);
                $poi_ocr_id = $cibil_data["poi_ocr_id"];
                $lead_id = $cibil_data["poi_ocr_lead_id"];
                $request_json_data = $cibil_data["poi_ocr_request"];
                $response_json_data = $cibil_data["poi_ocr_response"];

                // Prepare file paths and names
                $filePaths = [
                    'request_json' => $this->prepareFile($request_json_data, "api_poi_ocr_logs_request_json", $poi_ocr_id, $lead_id),
                    'response_json' => $this->prepareFile($response_json_data, "api_poi_ocr_logs_response_json", $poi_ocr_id, $lead_id)
                ];

                try {
                    // Process each file: create, upload, and return file names
                    $uploadedFiles = [];
                    foreach ($filePaths as $type => $path) {
                        if (empty($path['file_content']) && $type != 'request_json') {
                            throw new Exception("File content is empty for type: $type");
                        }

                        $request_array['flag'] = 1;
                        $request_array['file'] = $path['file_content'];
                        $request_array['new_file_name'] = $path['file_name'];
                        $request_array['ext'] = "txt";

                        $uploadReturn = $CommonComponent->upload_document(0, $request_array);
                        $uploadedFiles[$type] = $uploadReturn['file_name'];
                    }

                    // Update database log
                    $updateLog = [
                        'poi_ocr_request' => $uploadedFiles['request_json'],
                        'poi_ocr_response' => $uploadedFiles['response_json'],
                        's3_flag' => 1
                    ];

                    $flag = $this->MiscellaneousModel->update('api_poi_ocr_logs', ['poi_ocr_id' => $poi_ocr_id], $updateLog);
                    if ($flag) {
                        $apiStatusid = 1;
                    }
                } catch (Exception $e) {
                    $apiStatusid = 4;
                    $errorMessage = $e->getMessage();
                }

                // Add result to the return array
                $return_array[$i] = [
                    'poi_ocr_id' => $poi_ocr_id,
                    'lead_id' => $lead_id,
                    'request_json' => $uploadedFiles['request_json'],
                    'response_json' => $uploadedFiles['response_json'],
                    'status' => $apiStatusid,
                    'error_message' => $errorMessage,
                    'time_taken' => round(microtime(true) - $uploadingStartTimestamp, 2)
                ];
                $i++;
            }
        }

        // Ensure that return_array is returned, even when no data is found
        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($return_array);
        $message .= "<br/><br/>";
        $message .= json_encode($return_array);
        $subject = "OCR Logs To S3 Bucket | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");
        echo $message;
    }

    private function prepareFile($fileContent, $prefix, $cibil_id, $lead_id) {
        $fileName = "{$prefix}_{$cibil_id}_{$lead_id}";
        return [
            'file_name' => $fileName,
            'file_content' => base64_encode($fileContent)
        ];
    }
}
