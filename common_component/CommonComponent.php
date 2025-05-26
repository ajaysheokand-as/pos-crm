<?php

// error_reporting(0);
// ini_set("display_errors", 0);
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '-1');
set_time_limit(0);

date_default_timezone_set('Asia/Calcutta');

// $xco_path = '/var/www/sot_lms/common_component';
$xco_path = '/var/www/html/tejasloan-backend/common_component';

define("COMP_PATH", $xco_path);
define("COMP_ENVIRONMENT", 'production'); // development
// define("COMP_DOC_URL", 'https://sotcrm.com/direct-document-file/'); //production
define("COMP_DOC_URL", 'https://crm.tejasloan.com/direct-document-file/'); //production
// define("COMP_CRM_URL", 'https://sotcrm.com/');
define("COMP_CRM_URL", 'https://crm.tejasloan.com/');
define("COMP_WEBSITE_URL", 'https://tejasloan.com/');
define("COMP_DOC_S3_FLAG", true); //true=> Store in S3 bucket , false=> Physical store.
// define("COMP_DOC_PATH", "/var/www/sot_lms/upload/");
define("COMP_DOC_PATH", "/var/www/html/tejasloan-backend/upload/");
// define("COMP_TEMP_DOC_PATH", "/var/www/sot_lms/temp_upload/");
define("COMP_TEMP_DOC_PATH", "/var/www/html/tejasloan-backend/temp_upload/");
define("COMP_APPLE_STORE_ID", "id6503283983");
define("COMP_ANDROID_STORE_ID", "com.speedoloan.speedoloan");
define("WHATSAPP_LOAN_OFFER", "https://sot-website.s3.ap-south-1.amazonaws.com/whatsapp/whatsapp_loan_offer.jpg");
define("WHATSAPP_BASE_URL", "https://tejasloanbucket.s3.ap-south-1.amazonaws.com/whatsapp/");

require_once(COMP_PATH . "/includes/functions.inc.php");
require_once(COMP_PATH . "/classes/model/BaseModel.class.php");
require_once(COMP_PATH . "/classes/model/LeadModel.class.php");
require_once(COMP_PATH . "/classes/model/BreRuleModel.class.php");

class CommonComponent {

    public function run_eligibility($lead_id) {


        require_once(COMP_PATH . '/includes/aip_engine/check_eligibility.php');

        $return_array = check_customer_eligibility($lead_id);

        return $return_array;
    }

    public function get_loan_repayment_details($lead_id) {

        $leadModelObj = new LeadModel();

        $return_array = $leadModelObj->getLoanRepaymentDetails($lead_id);

        return $return_array;
    }

    public function check_customer_mandatory_documents($lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/aip_engine/check_common_rules.php');

        $return_array = check_customer_mandatory_documents($lead_id, $request_array);

        return $return_array;
    }

    public function call_bureau_api($lead_id) {
        require_once(COMP_PATH . '/includes/integration/payday_crif_api.php');
        // $return_array = bureau_api_call('GET_BUREAU_SCORE', $lead_id, array());
        $return_array = bureau_api_call('GET_BUREAU_JSON_SCORE', $lead_id, array());
        return $return_array;
    }

    public function call_pan_verification_api($lead_id) {

        require_once(COMP_PATH . '/includes/integration/payday_poi_verification_api.php');

        $return_array = poi_verification_api_call('GET_PAN_VERFICATION', $lead_id, array());

        return $return_array;
    }

    public function call_pan_verification_api_call($method_name, $lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_poi_verification_api.php');
        $return_array = poi_verification_api_call($method_name, $lead_id, $request_array);
        return $return_array;
    }

    public function call_pan_ocr_api($lead_id) {

        require_once(COMP_PATH . '/includes/integration/payday_poi_ocr_api.php');

        $return_array = poi_ocr_api_call('GET_PAN_OCR', $lead_id, array());

        // $method_id = (date('d') % 2) > 0 ? 1 : 2;
        // if ($method_id == 1) {
            // $return_array = poi_ocr_api_call('GET_PAN_OCR_DIGITAP', $lead_id, array());
        // } else {
        //     $return_array = poi_ocr_api_call('GET_PAN_OCR', $lead_id, array());
        // }

        return $return_array;
    }

    public function call_aadhaar_ocr_api($lead_id) {

        require_once(COMP_PATH . '/includes/integration/payday_poi_ocr_api.php');

        $return_array = poi_ocr_api_call('GET_AADHAAR_OCR', $lead_id, array());

        // $method_id = (date('d') % 2) > 0 ? 1 : 2;
        // if ($method_id == 1) {
            // $return_array = poi_ocr_api_call('GET_AADHAAR_OCR_DIGITAP', $lead_id, array());
        // } else {
        //     $return_array = poi_ocr_api_call('GET_AADHAAR_OCR', $lead_id, array());
        // }

        return $return_array;
    }

    public function call_aadhaar_masked_api($lead_id, $doc_type, $doc_id) {

        require_once(COMP_PATH . '/includes/integration/payday_poi_ocr_api.php');

        $return_array = poi_ocr_api_call('GET_MASKED_AADHAAR', $lead_id, array("doc_type_id" => $doc_type, "doc_id" => $doc_id));

        return $return_array;
    }

    public function call_sent_email($to_email, $subject, $message, $bcc_email = "", $cc_email = "", $from_email = "", $reply_to = "") {

        $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);

        return $return_array;
    }

    public function call_esign_api($lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_esign_api.php');

        // $return_array = aadhaar_esign_api_call('UPLOAD_ESIGN_FILE', $lead_id, $request_array);
        $return_array = aadhaar_esign_api_call('UPLOAD_ESIGN_FILE_DIGITAP', $lead_id, $request_array);

        return $return_array;
    }

    public function call_signin_api($lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_esign_api.php');

        // $return_array = aadhaar_esign_api_call('UPLOAD_ESIGN_FILE', $lead_id, $request_array);
        $return_array = aadhaar_esign_api_call('SIGNIN_ESIGN_FILE_DIGITAP', $lead_id, $request_array);

        return $return_array;
    }
    

    public function download_esign_document_api($lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_esign_api.php');

        if ($request_array['provider'] == 1) {
            $return_array = aadhaar_esign_api_call('DOWNLOAD_ESIGN_FILE', $lead_id, $request_array);
        } else if ($request_array['provider'] == 2) {
            $return_array = aadhaar_esign_api_call('DOWNLOAD_ESIGN_FILE_DIGITAP', $lead_id, $request_array);
        } else {
            $return_array = aadhaar_esign_api_call('DOWNLOAD_ESIGN_FILE', $lead_id, $request_array);
        }


        // $return_array = aadhaar_esign_api_call('DOWNLOAD_ESIGN_FILE_DIGITAP', $lead_id, $request_array);
        // $return_array = aadhaar_esign_api_call('DOWNLOAD_ESIGN_FILE_DIGITAP', $lead_id, $request_array);

        return $return_array;
    }

    public function call_aadhaar_verification_request_api($lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_digilocker_api.php');

        $return_array = aadhaar_digilocker_api_call('DIGILOCKER_CREATE_URL', $lead_id, $request_array);

        return $return_array;
    }

    public function call_aadhaar_verification_response_api($lead_id) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_digilocker_api.php');

        $return_array = aadhaar_digilocker_api_call('DIGILOCKER_GET_DETAILS', $lead_id, array());

        return $return_array;
    }

    public function call_office_email_verification_api($lead_id) {

        require_once(COMP_PATH . '/includes/integration/payday_email_verification_api.php');

        $return_array = email_verification_api_call('OFFICE_EMAIL_VALIDATE', $lead_id, array());
        return $return_array;
    }

    public function call_email_verification_api($lead_id, $request_array = array()) {


        require_once(COMP_PATH . '/includes/integration/payday_email_verification_api.php');

        $return_array = email_verification_api_call('SIGNZY_EMAIL_VALIDATE', $lead_id, $request_array);
        return $return_array;
    }

    public function call_whatsapp_api($templateId, $lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_whatsapp_api.php');
        $return_array = payday_whatsapp_api($templateId, $lead_id, $request_array);
        return $return_array;
    }

    public function check_customer_dedupe($request_array = array()) {

        require_once(COMP_PATH . '/includes/aip_engine/check_common_rules.php');

        $return_array = check_customer_dedupe($request_array);

        return $return_array;
    }

    public function payday_repayment_api($lead_id, $repayment_amount) {
        require_once(COMP_PATH . '/includes/integration/payday_repayment_api.php');

        $return_array = payday_repayment_api("GENERATE_EAZYPAY_ENCRYPTED_URL", $lead_id, $repayment_amount, array());

        return $return_array;
    }

    public function payday_bank_account_verification_api($lead_id, $request_array) {
        require_once(COMP_PATH . '/includes/integration/payday_bank_verification_api_helper.php');

        $return_array = bank_account_verification_api_call("BANK_ACCOUNT_VERIFICATION", $lead_id, $request_array);

        return $return_array;
    }

    public function sent_lead_thank_you_email($lead_id, $email, $name, $reference_no) {

        $return_array = common_lead_thank_you_email($lead_id, $email, $name, $reference_no);

        return $return_array;
    }

    public function payday_sms_api($type_id, $lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_sms_sent_api.php');

        $return_array = payday_sms_sent_api($type_id, $lead_id, $request_array);

        return $return_array;
    }

    public function payday_whatsapp_api($type_id, $lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_whatsapp_api.php');
        $return_array = payday_whatsapp_api($type_id, $lead_id, $request_array);
        return $return_array;
    }

    public function call_bre_quote_engine($lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/aip_engine/bre_rule_engine.php');

        $return_array = bre_quote_engine($lead_id, $request_array);

        return $return_array;
    }

    public function call_bre_rule_engine($lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/aip_engine/bre_rule_engine.php');

        $return_array = bre_rule_engine($lead_id, $request_array);

        return $return_array;
    }

    public function call_url_shortener_api($url = "", $lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_url_shortener_api.php');

        $return_array = payday_url_shortener_api('TINYURL', $url, $lead_id, $request_array);

        return $return_array;
    }

    public function call_runo_management_api($method_name = "", $lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_runo_call_api.php');

        $return_array = payday_call_management_api_call($method_name, $lead_id, $request_array);

        return $return_array;
    }

    public function call_payday_bank_analysis($method_name, $lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_bank_analysis_api_call.php');
        $return_array = payday_bank_analysis_api_call($method_name, $lead_id, $request_array);
        return $return_array;
    }

    public function call_payday_adjust_api($method_name, $lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_adjust_api.php');
        $return_array = payday_adjust_api($method_name, $lead_id, $request_array);
        return $return_array;
    }

    public function upload_document($lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/file_handling_with_s3.php');
        if (isset($request_array['upload_type']) && $request_array['upload_type'] == 'cibil') {
            $return_array = call_s3_bucket("DOCUMENT_UPLOAD_CIBIL", $lead_id, $request_array);
        } else {
            $return_array = call_s3_bucket("DOCUMENT_UPLOAD", $lead_id, $request_array);
        }
        return $return_array;
    }

    public function download_document($lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/file_handling_with_s3.php');
        $return_array = call_s3_bucket("DOCUMENT_DOWNLOAD", $lead_id, $request_array);
        return $return_array;
    }

    public function payday_appsflyer_campaign_api_call($method_name = "", $lead_id = 0, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/payday_appsflyer_call_api.php');

        $return_array = payday_appsflyer_campaign_api_call($method_name, $lead_id, $request_array);

        return $return_array;
    }

    public function call_digitap_ekyc_api_call($method_name, $lead_id, $request_array = array()) {

        require_once(COMP_PATH . '/includes/integration/payday_aadhaar_digilocker_api.php');
        $return_array = aadhaar_digilocker_api_call($method_name, $lead_id, $request_array);

        return $return_array;
    }

    public function call_qrcode_api($lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/call_upi_api.php');
        $return_array = call_upi_api('GET_QRCODE_REQUEST', $lead_id, $request_array);
        return $return_array;
    }

    public function call_collectpay_api($lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/call_upi_api.php');
        $return_array = call_upi_api('COLLECTPAY_REQUEST', $lead_id, $request_array);
        return $return_array;
    }

    public function call_check_upi_api($lead_id, $request_array = array()) {
        require_once(COMP_PATH . '/includes/integration/call_upi_api.php');
        $return_array = call_upi_api('CHECK_STATUS', $lead_id, $request_array);
        return $return_array;
    }
}
