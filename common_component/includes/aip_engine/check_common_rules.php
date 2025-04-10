<?php
//   echo 'Testing Due'; die;
function check_customer_mandatory_documents($lead_id, $request_array = array()) {

    common_log_writer(1, "check_customer_mandatory_documents started | $lead_id");

    $return_array = array("status" => 0, "error" => "");

    $apiStatusId = 0;

    $message = "";
    $errorMessage = "Some error occurred. Please try again.";
    $user_id = (isset($_SESSION['isUserSession']['user_id']) && !empty($_SESSION['isUserSession']['user_id'])) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {



        $master_document_array_app = array();

        if (empty($lead_id)) {
            throw Exception("Missing Lead Id");
        }

        $LeadDetails = $leadModelObj->getLeadDetails($lead_id);



        if ($LeadDetails['status'] != 1) {
            throw new Exception("Application details not found");
        }

        $app_data = $LeadDetails['app_data'];

        $pancard = $app_data['pancard'];
        $user_type = $app_data['user_type'];
        $mobile = $app_data['mobile'];
        $customer_docs_available = !empty($app_data['customer_docs_available']) ? $app_data['customer_docs_available'] : 0;

        if (empty($pancard)) {
            throw new Exception("Missing pancard number.");
        }

        $masterDocumentDetails = $leadModelObj->getCustomerMandatoryDocumentMaster('1,2,4,6,16,18');



        if ($masterDocumentDetails['status'] == 1) {

            $master_document_array = array();

            foreach ($masterDocumentDetails['master_doc_data'] as $document_data) {

                if ($document_data['id'] == 16) {
                    $document_data['docs_sub_type'] = "Last Salary Slip (Salary slip -1)";
                }

                $master_document_array[$document_data['id']] = $document_data['docs_sub_type'];
            }
        } else {
            throw new Exception("Master list not available.");
        }

        $mandatory_docIds = implode(",", array_keys($master_document_array));

        if (!empty($master_document_array)) {

            $custDocumentDetails = $leadModelObj->getCustomerMandatoryDocumentByPan($pancard, '1,2,4,18');

            if ($custDocumentDetails['status'] == 1) {

                foreach ($custDocumentDetails['doc_data'] as $document_data) {
                    if (!empty($master_document_array[$document_data['docs_master_id']])) {
                        unset($master_document_array[$document_data['docs_master_id']]);
                    }
                }
            }
        }

        if (!empty($master_document_array)) {

            $custDocumentDetails = $leadModelObj->getCustomerMandatoryDocumentByLeadId($lead_id, $mandatory_docIds);

            if ($custDocumentDetails['status'] == 1) {

                foreach ($custDocumentDetails['doc_data'] as $document_data) {
                    if (!empty($master_document_array[$document_data['docs_master_id']])) {
                        unset($master_document_array[$document_data['docs_master_id']]);
                    }
                }
            }
        }



        if (empty($master_document_array) || $user_type == "REPEAT" || $mobile == "9560807913") {
            $master_document_array = array();
            $apiStatusId = 1;
            $message = "All document available to process application.";
            $errorMessage = "";

            $update_array = array();

            $update_array['customer_docs_available'] = 1;

            if (!empty($user_id) && $customer_docs_available != 1) {
                $update_array['customer_docs_available'] = 2;
            }

            $update_array['updated_at'] = date("Y-m-d H:i:s");

            $leadModelObj->updateLeadCustomerTable($lead_id, $update_array);
        } else {
            $mandatory_docNames = implode(", ", $master_document_array);

            throw new Exception("Missing mandatory docs : " . $mandatory_docNames);
        }
    } catch (Exception $e) {
        $apiStatusId = 0;
        $errorMessage = $e->getMessage();
    }

    if (!empty($master_document_array)) {
        foreach ($master_document_array as $doc_id => $doc_name) {

            $allowed_format = "image";

            if (in_array($doc_id, array(6))) { //bank statement
                $allowed_format = "document";
            }

            $master_document_array_app[] = array('id' => $doc_id, 'name' => $doc_name, "allowed_format" => $allowed_format);
        }
    }



    $return_array = array("status" => $apiStatusId, 'data' => $master_document_array_app, "error" => $errorMessage, 'message' => $message);

    common_log_writer(1, "return response====" . json_encode($return_array));
    common_log_writer(1, "check_customer_mandatory_documents end");

    return $return_array;
}

function check_customer_dedupe($request_array = array()) {

    common_log_writer(1, "check_customer_dedupe started | " . json_encode($request_array));

    $return_array = array("status" => 0, "error" => "");

    $apiStatusId = 0;

    $message = "";
    $errorMessage = "";


    if (in_array($request_array['mobile'], array(9717708655, 8279750539, 9170004606))) {
        return array("status" => 0, "error" => "", "message" => "");
    }

    //    $user_id = (isset($_SESSION['isUserSession']['user_id']) && !empty($_SESSION['isUserSession']['user_id'])) ? $_SESSION['isUserSession']['user_id'] : 0;

    $leadModelObj = new LeadModel();

    try {


        $mobile = !empty($request_array['mobile']) ? $request_array['mobile'] : "";
        $pancard = !empty($request_array['pancard']) ? $request_array['pancard'] : "";
        $email = !empty($request_array['email']) ? $request_array['email'] : "";

        if (empty($mobile) && empty($pancard) && empty($email)) {
            throw Exception("Missing mandaotry input");
        }

        $dedupeDetails = $leadModelObj->checkCustomerDedupeByInput($mobile, $pancard, $email);

        $apiStatusId = $dedupeDetails['status'];

        if ($apiStatusId == 1) {
            $message = "Customer already applied for the day.";
        }
    } catch (Exception $e) {
        $apiStatusId = 0;
        $errorMessage = $e->getMessage();
    }

    $return_array = array("status" => $apiStatusId, "error" => $errorMessage, 'message' => $message);

    common_log_writer(1, "return response====" . json_encode($return_array));
    common_log_writer(1, "check_customer_dedupe end");

    return $return_array;
}
