<?php

class LeadModel extends BaseModel {

    private $white_list_mobile_no_rejection = array();

    public function __construct() {
        parent::__construct();
        $this->connectDatabase();
    }

    public function getLeadDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {

            $sql_query = "SELECT LD.lead_id, LD.city_id, LD.state_id, LD.monthly_salary_amount, LD.lead_status_id, LD.status, LD.stage, LD.user_type, LD.monthly_salary_amount";
            $sql_query .= ", LD.pancard, LD.loan_amount, C.dob, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email,C.customer_docs_available";
            $sql_query .= " FROM leads LD";
            $sql_query .= " INNER JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id)";
            $sql_query .= " WHERE LD.lead_id=$lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

            $tempDetails = $this->context->query($sql_query);


            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getLeadFullDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {

            $sql_query = "SELECT LD.lead_id, LD.user_type, LD.customer_id, LD.application_no, C.city_id, C.state_id, LD.lead_status_id, LD.status, LD.stage, LD.lead_data_source_id";
            $sql_query .= ", LD.pancard, LD.loan_amount, C.dob, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.email_verified_status, C.alternate_email,C.alternate_email_verified_status";
            $sql_query .= ", LD.lead_reference_no,C.gender, LD.lead_screener_assign_user_id, LD.lead_rejected_assign_user_id";
            $sql_query .= ", LD.cibil, LD.lead_credit_approve_datetime";
            $sql_query .= ", LD.utm_source, LD.utm_medium, LD.utm_campaign, LD.utm_term, LD.lead_customer_profile_id";
            $sql_query .= ", CAM.repayment_date, CAM.repayment_amount, CAM.loan_recommended, L.loan_no";
            $sql_query .= ", C.current_house, C.current_locality, C.current_landmark, C.cr_residence_pincode,C.current_residence_type";
            $sql_query .= ", C.aa_current_city_id, C.aa_current_state_id, C.aa_current_house, C.aa_current_locality, C.aa_current_landmark, C.aa_cr_residence_pincode";
            $sql_query .= ", C.aadhar_no, C.customer_digital_ekyc_flag, C.customer_adjust_adid, C.customer_adjust_gps_adid, C.customer_adjust_idfa";
            $sql_query .= ", MS.m_state_name as state, MC.m_city_name as city,MDS.data_source_name as source";
            $sql_query .= " FROM leads LD";
            $sql_query .= " LEFT JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id)";
            $sql_query .= " LEFT JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
            $sql_query .= " LEFT JOIN collection CO ON(LD.lead_id = CO.lead_id)";
            $sql_query .= " LEFT JOIN loan L ON(LD.lead_id = L.lead_id)";
            $sql_query .= " LEFT JOIN master_state MS ON(LD.state_id = MS.m_state_id)";
            $sql_query .= " LEFT JOIN master_city MC ON(LD.city_id = MC.m_city_id)";
            $sql_query .= " LEFT JOIN master_data_source MDS ON(LD.lead_data_source_id = MDS.data_source_id)";

            $sql_query .= " WHERE LD.lead_id=$lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

            $tempDetails = $this->context->query($sql_query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getStateDetails($state_id) {

        $return_array = array("status" => 0, "state_data" => array());

        if (!empty($state_id)) {

            $sql_query = "SELECT m_state_id,m_state_name,m_state_code,m_state_is_sourcing";
            $sql_query .= " FROM master_state";
            $sql_query .= " WHERE m_state_id=$state_id AND m_state_active=1 AND m_state_deleted=0";

            $tempDetails = $this->context->query($sql_query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["state_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getCityDetails($city_id) {

        $return_array = array("status" => 0, "city_data" => array());

        if (!empty($city_id)) {

            $sql_query = "SELECT m_city_id, m_city_name, m_city_is_sourcing, m_city_trial_sourcing";
            $sql_query .= " FROM master_city";
            $sql_query .= " WHERE m_city_id=$city_id AND m_city_active=1 AND m_city_deleted=0";

            $tempDetails = $this->context->query($sql_query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["city_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function checkBlackListedCustomer($lead_id) {

        $return_array = array("status" => 0, "message" => '');

        $sql = 'SELECT LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile,C.email, C.alternate_email';
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
        $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

        $leadDetails = $this->context->query($sql);

        if (!empty($leadDetails['items']) && count($leadDetails['items']) > 0) {

            $lead_data = $leadDetails['items'][0];

            $first_name = !empty($lead_data['first_name']) ? strtoupper($lead_data['first_name']) : "";

            $dob = !empty($lead_data['dob']) ? $lead_data['dob'] : "";

            $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

            $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

            if (in_array($mobile, $this->white_list_mobile_no_rejection)) {
                return $return_array;
            }

            $alternate_mobile = !empty($lead_data['alternate_mobile']) ? $lead_data['alternate_mobile'] : "";

            $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

            $alternate_email = !empty($lead_data['alternate_email']) ? strtoupper($lead_data['alternate_email']) : "";

            $sql = "SELECT * FROM customer_black_list";

            $where = "";

            if (!empty($first_name) && !empty($dob)) {
                $where .= "OR (bl_customer_first_name='$first_name' AND bl_customer_dob='$dob')";
            }

            if (!empty($pancard)) {
                $where .= "OR bl_customer_pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= "OR bl_customer_mobile='$mobile'";
                $where .= "OR bl_customer_alternate_mobile='$mobile'";
            }

            if (!empty($alternate_mobile)) {
                $where .= "OR bl_customer_mobile='$alternate_mobile'";
                $where .= "OR bl_customer_alternate_mobile='$alternate_mobile'";
            }


            if (!empty($email)) {
                $where .= "OR bl_customer_email='$email'";
                $where .= "OR bl_customer_alternate_email='$email'";
            }

            if (!empty($alternate_email)) {
                $where .= "OR bl_customer_email='$alternate_email'";
                $where .= "OR bl_customer_alternate_email='$alternate_email'";
            }

            $where = ltrim($where, 'OR ');

            $blacklistResult = $this->context->query($sql . " WHERE bl_active=1 AND bl_deleted=0 AND (" . $where . ") ORDER BY bl_id DESC");

            if (!empty($blacklistResult['items']) && count($blacklistResult['items']) > 0) {
                $black_list_data = $blacklistResult['items'][0];

                $error = "";

                if (!empty($black_list_data['bl_customer_first_name']) && !empty($black_list_data['bl_customer_dob']) && !empty($first_name) && !empty($dob) && $black_list_data['bl_customer_first_name'] == $first_name && $black_list_data['bl_customer_dob'] == $dob) {
                    $error .= ", First Name and DOB";
                }

                if (!empty($black_list_data['bl_customer_pancard']) && !empty($pancard) && $black_list_data['bl_customer_pancard'] == $pancard) {
                    $error .= ", PAN";
                }

                if (!empty($black_list_data['bl_customer_mobile']) && ((!empty($mobile) && $black_list_data['bl_customer_mobile'] == $mobile) || (!empty($alternate_mobile) && $black_list_data['bl_customer_mobile'] == $alternate_mobile))) {
                    $error .= ", Mobile";
                }

                if (!empty($black_list_data['bl_customer_alternate_mobile']) && ((!empty($mobile) && $black_list_data['bl_customer_alternate_mobile'] == $mobile) || (!empty($alternate_mobile) && $black_list_data['bl_customer_alternate_mobile'] == $alternate_mobile))) {
                    $error .= ", Alternate Mobile";
                }

                if (!empty($black_list_data['bl_customer_email']) && ((!empty($email) && $black_list_data['bl_customer_email'] == $email) || (!empty($alternate_email) && $black_list_data['bl_customer_email'] == $alternate_email))) {
                    $error .= ", Email";
                }

                if (!empty($black_list_data['bl_customer_alternate_email']) && ((!empty($email) && $black_list_data['bl_customer_alternate_email'] == $email) || (!empty($alternate_email) && $black_list_data['bl_customer_alternate_email'] == $alternate_email))) {
                    $error .= ", Alternate Email";
                }

                $error = ltrim($error, ', ');
                $return_array['status'] = 1;
                $return_array['message'] = "Customer Loan No - " . $black_list_data['bl_loan_no'] . " | Due to " . $error;
            }
        }

        return $return_array;
    }

    public function checkCustomerRejected($lead_id) {

        $return_array = array("status" => 0, "message" => '');

        $sql = 'SELECT LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email';
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
        $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

        $leadDetails = $this->context->query($sql);

        if (!empty($leadDetails['items']) && count($leadDetails['items']) > 0) {

            $lead_data = $leadDetails['items'][0];

            $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

            $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

            if (in_array($mobile, $this->white_list_mobile_no_rejection)) {
                return $return_array;
            }

            $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

            $sql = "SELECT lead_status_id,lead_rejected_reason_id FROM leads";

            $sql .= " WHERE lead_id!=$lead_id AND lead_active=1 AND lead_deleted=0 AND lead_entry_date >= DATE_SUB(CURRENT_DATE,INTERVAL 3 MONTH) AND ";

            $where = "";

            if (!empty($pancard)) {
                $where .= " OR pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= " OR mobile='$mobile'";
            }

            if (!empty($email)) {
                $where .= " OR email='$email'";
            }

            $where = ltrim($where, 'OR ');
            $where = "(" . $where . ")";

            $sql = $sql . " " . $where . " ORDER BY lead_id DESC";

            $customerResult = $this->context->query($sql);

            $counter_reject = 0;

            if (!empty($customerResult['items']) && count($customerResult['items']) > 0) {
                $customerResult = $customerResult['items'];

                foreach ($customerResult as $customer_data) {

                    if ($customer_data['lead_status_id'] == 9 && !in_array($customer_data['lead_rejected_reason_id'], array(1, 15, 29, 52))) {
                        $counter_reject++;
                    } else {
                        break;
                    }
                }
            }

            if ($counter_reject >= 5) {
                $return_array['status'] = 1;
                $return_array['message'] = "Customer applied more then 5 times and gets rejected.";
            }
        }

        return $return_array;
    }

    public function checkRepeatCustomer($lead_id) {

        return $return_array = array("status" => 0, "message" => '');

        $sql = 'SELECT LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email';
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
        $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

        $leadDetails = $this->context->query($sql);

        if (!empty($leadDetails['items']) && count($leadDetails['items']) > 0) {

            $lead_data = $leadDetails['items'][0];

            $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

            $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

            $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

            $sql = "SELECT COUNT(*) AS repeat_count FROM leads LD INNER JOIN loan L ON (LD.lead_id = L.lead_id)";

            $sql .= " WHERE LD.user_type='REPEAT' AND LD.lead_active=1 AND LD.lead_deleted=0 AND L.loan_status_id=14 AND ";

            $where = "";

            if (!empty($pancard)) {
                $where .= " OR LD.pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= " OR LD.mobile='$mobile'";
            }

            if (!empty($email)) {
                $where .= " OR LD.email='$email'";
            }

            $where = ltrim($where, 'OR ');
            $where = "(" . $where . ")";

            $sql = $sql . " " . $where . " ORDER BY LD.lead_id DESC";

            $customerResult = $this->context->query($sql);

            if (!empty($customerResult['items']) && count($customerResult['items']) > 0) {
                $customerResult = $customerResult['items'][0];

                if ($customerResult['repeat_count'] >= 5) {
                    $return_array['status'] = 1;
                    $return_array['message'] = "Customer applied more then 5 times as repeat customer";
                }
            }
        }

        return $return_array;
    }

    public function checkTokenValidity($method_id) {

        $tokenResult = $this->context->query("SELECT token_response_datetime, token_string, token_return_user_id FROM api_service_token_logs WHERE token_method_id=$method_id AND token_api_status_id=1 AND token_active=1 AND token_deleted=0 ORDER BY token_id DESC LIMIT 0,1");

        return $tokenResult;
    }

    public function getDocumentDetails($lead_id, $document_type_id = "", $document_id = "") {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($lead_id) && !empty($document_type_id)) {

            $sql = "SELECT D.docs_id, D.docs_master_id, D.file, D.docs_aadhaar_masked";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " INNER JOIN docs D  ON (D.lead_id = LD.lead_id)";
            $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";
            $sql .= " AND D.docs_master_id IN($document_type_id) AND D.docs_active=1 AND D.docs_deleted=0";

            if (!empty($document_id)) {
                $sql .= " AND D.docs_id = $document_id";
            }

            $sql .= " ORDER BY D.docs_id DESC";
            //            echo $sql;
            $documentResult = $this->context->query($sql);

            if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $documentResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getLeadDocumentDetails($lead_id, $doc_id) {

        $return_array = array("status" => 0, "docs_data" => array());

        if (!empty($lead_id) && !empty($doc_id)) {

            $query = "SELECT * FROM docs WHERE docs_id=$doc_id AND lead_id=$lead_id";

            $tempDetails = $this->context->query($query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["docs_data"] = $tempDetails['items'][0];
            }
        }
        return $return_array;
    }

    public function getLeadDocumentDetailsByNovelId($doc_id) {

        $return_array = array("status" => 0, "docs_data" => array());

        if (!empty($doc_id)) {

            $query = "SELECT * FROM docs WHERE docs_novel_return_id='" . $doc_id . "'";

            $tempDetails = $this->context->query($query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["docs_data"] = $tempDetails['items'][0];
            }
        }
        return $return_array;
    }

    public function getPanValidateLastApiLog($lead_id, $fetch_type = 0, $request_array = array()) {

        $return_array = array("status" => 0, "pan_log_data" => array());

        if (!empty($lead_id)) {
            $fetch_where = "";
            if ($fetch_type == 1) {
                $fetch_where = "poi_veri_profile_id =" . $lead_id;
                $method_id = 2;
            } else {
                $fetch_where = "poi_veri_lead_id =" . $lead_id;
                $method_id = 1;
            }

            if (!empty($request_array['method_id'])) {
                $method_id = $request_array['method_id'];
            }

            $sql = "SELECT poi_veri_proof_no,poi_veri_response";
            $sql .= " FROM api_poi_verification_logs";
            $sql .= " WHERE  $fetch_where AND poi_veri_active=1 AND poi_veri_deleted=0";
            $sql .= " AND poi_veri_method_id=$method_id AND poi_veri_api_status_id=1";
            $sql .= " ORDER BY poi_veri_id DESC";

            $panLogResult = $this->context->query($sql);

            if (!empty($panLogResult['items']) && count($panLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['pan_log_data'] = $panLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getPanOCRLastApiLog($lead_id) {

        $return_array = array("status" => 0, "pan_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT poi_ocr_proof_no, poi_ocr_doc_id_1,poi_ocr_response";
            $sql .= " FROM api_poi_ocr_logs";
            $sql .= " WHERE poi_ocr_lead_id = $lead_id AND poi_ocr_active=1 AND poi_ocr_deleted=0";
            $sql .= " AND poi_ocr_method_id=1 AND poi_ocr_api_status_id=1";
            $sql .= " ORDER BY poi_ocr_id DESC";

            $panLogResult = $this->context->query($sql);

            if (!empty($panLogResult['items']) && count($panLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['pan_log_data'] = $panLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getAadhaarOCRLastApiLog($lead_id) {

        $return_array = array("status" => 0, "aadhaar_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT poi_ocr_proof_no, poi_ocr_doc_id_1, poi_ocr_doc_id_2,poi_ocr_response";
            $sql .= " FROM api_poi_ocr_logs";
            $sql .= " WHERE poi_ocr_lead_id = $lead_id AND poi_ocr_active=1 AND poi_ocr_deleted=0";
            $sql .= " AND poi_ocr_method_id=2 AND poi_ocr_api_status_id=1";
            $sql .= " ORDER BY poi_ocr_id DESC";

            $aadhaarLogResult = $this->context->query($sql);

            if (!empty($aadhaarLogResult['items']) && count($aadhaarLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['aadhaar_log_data'] = $aadhaarLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getCAMDetails($lead_id) {

        $return_array = array("status" => 0, "cam_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT * ";
            $sql .= " FROM credit_analysis_memo";
            $sql .= " WHERE lead_id = $lead_id AND cam_active=1 AND cam_deleted=0";
            $sql .= " ORDER BY cam_id DESC";

            $camResult = $this->context->query($sql);

            if (!empty($camResult['items']) && count($camResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['cam_data'] = $camResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getEsignApiLog($lead_id, $method_id, $provider_id = 1) {

        $return_array = array("status" => 0, "esign_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT esign_aadhaar_no, esign_response, esign_api_status_id, esign_return_url";
            $sql .= " FROM api_esign_logs";
            $sql .= " WHERE esign_lead_id = $lead_id AND esign_active=1 AND esign_deleted=0";
            $sql .= " AND esign_method_id=$method_id AND esign_provider=$provider_id AND esign_api_status_id=1";
            $sql .= " ORDER BY esign_id DESC";

            $esignLogResult = $this->context->query($sql);

            if (!empty($esignLogResult['items']) && count($esignLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['esign_log_data'] = $esignLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getEsignApiLeadLog($lead_id, $method_id) {

        $return_array = array("status" => 0, "esign_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT esign_aadhaar_no, esign_response, esign_api_status_id, esign_return_url";
            $sql .= " FROM api_esign_logs";
            $sql .= " WHERE esign_lead_id = $lead_id AND esign_active=1 AND esign_deleted=0";
            $sql .= " AND esign_method_id=$method_id AND esign_api_status_id=1";
            $sql .= " ORDER BY esign_id DESC";

            $esignLogResult = $this->context->query($sql);

            if (!empty($esignLogResult['items']) && count($esignLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['esign_log_data'] = $esignLogResult['items'];
            }
        }

        return $return_array;
    }

    public function getDigilockerApiLog($lead_id, $method_id) {

        $return_array = array("status" => 0, "digilocker_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT ekyc_aadhaar_no, ekyc_response, ekyc_api_status_id, ekyc_return_url, ekyc_return_request_id,ekyc_eaadhaar_available_flag";
            $sql .= " FROM api_ekyc_logs";
            $sql .= " WHERE ekyc_lead_id = $lead_id AND ekyc_active=1 AND ekyc_deleted=0";
            $sql .= " AND ekyc_method_id = $method_id AND ekyc_api_status_id=1";
            $sql .= " ORDER BY ekyc_id DESC";

            $digilockerLogResult = $this->context->query($sql);

            if (!empty($digilockerLogResult['items']) && count($digilockerLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['digilocker_log_data'] = $digilockerLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getCityStateByPincode($pincode) {
        $return_array = array("status" => 0, "pincode_data" => array());

        $sql = "SELECT m_state_id, m_state_name, m_city_id, m_city_name";
        $sql .= " FROM  master_pincode ";
        $sql .= " INNER JOIN  master_city ON (m_city_id = m_pincode_city_id)";
        $sql .= " INNER JOIN  master_state  ON (m_state_id = m_city_state_id)";
        $sql .= " WHERE m_pincode_value = $pincode AND m_pincode_active=1 AND m_pincode_deleted=0";
        $sql .= " ORDER BY m_pincode_value DESC";

        $documentResult = $this->context->query($sql);

        if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
            $return_array['status'] = 1;
            $return_array['pincode_data'] = $documentResult['items'][0];
        }

        return $return_array;
    }

    public function getCustomerEmploymentDetails($lead_id) {
        $return_array = array("status" => 0, "emp_data" => array());

        $sql = "SELECT * ";
        $sql .= " FROM customer_employment";
        $sql .= " WHERE lead_id = $lead_id AND emp_active=1 AND emp_deleted=0";
        $sql .= " ORDER BY id DESC";

        $employmentDetails = $this->context->query($sql);

        if (!empty($employmentDetails['items']) && count($employmentDetails['items']) > 0) {
            $return_array['status'] = 1;
            $return_array['emp_data'] = $employmentDetails['items'][0];
        }

        return $return_array;
    }

    public function getLoanRepaymentDetails($lead_id) {

        $result_array = array("status" => 0, "repayment_data" => array());
        $update_loan_array = array();

        $data = array();
        $sql = "SELECT LD.lead_id, LD.user_type, LD.customer_id, LD.lead_status_id, MS.status_name as status, MS.status_stage as stage, LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, LD.lead_final_disbursed_date, ";
        $sql .= " CAM.cam_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, ";
        $sql .= " CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, CAM.disbursal_date";
        $sql .= " CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.cam_advance_interest_amount, CONCAT_WS(C.first_name, C.middle_name, C.sur_name) AS full_name ";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id = LD.lead_id)";
        $sql .= " INNER JOIN master_status MS ON(MS.status_id = LD.lead_status_id)";
        $sql .= " WHERE LD.lead_id=$lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

        $tempData = $this->context->query($sql);

        if (!empty($tempData['items']) && count($tempData['items']) > 0) {
            // $status = 1;
            $leadDetails = $tempData['items'][0];
            $lead_status_id = $leadDetails["lead_status_id"];
            //            $user_type = $leadDetails["user_type"];
            $status = $leadDetails["status"];
            //            $stage = $leadDetails["stage"];
            $loan_no = $leadDetails["loan_no"];
            $loan_recommended = ($leadDetails["loan_recommended"]) ? $leadDetails["loan_recommended"] : 0;
        }

        $disbursal_date = "-";
        $repayment_date = "-";
        $roi = 0;
        $penal_roi = 0;
        $tenure = 0;
        $ptenure = 0;
        $realIntrest = 0;
        $penaltyIntrest = 0;
        $recovered_interest_amount_deducted = 0;
        $advance_interest_amount_deducted = 0;
        $repayment_amount = 0;
        $total_repayment_amount = 0;
        $total_received_amount = 0;
        $total_due_amount = 0;

        $total_interest_amount = 0;
        //        $total_principle_amount = 0;
        //        $total_penalty_interest = 0;
        //discount was calculated on collection verify time.
        $principle_discount_amount = 0;
        $interest_discount_amount = 0;
        $penalty_discount_amount = 0;
        $total_discount_amount = 0;

        $sql_query = "SELECT LD.lead_id, LD.user_type, LD.customer_id, MS.status_name as status, MS.status_stage as stage, LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, LD.lead_final_disbursed_date, ";
        $sql_query .= " CAM.cam_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, ";
        $sql_query .= " CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, ";
        $sql_query .= " CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.cam_advance_interest_amount, ";
        $sql_query .= " L.loan_principle_discount_amount, L.loan_interest_discount_amount, L.loan_penalty_discount_amount, L.loan_total_discount_amount, ";
        $sql_query .= " L.loan_noc_settlement_letter, L.loan_noc_settled_letter_datetime, L.loan_noc_closed_letter_datetime, L.loan_noc_closing_letter ";
        $sql_query .= " FROM leads LD";
        $sql_query .= " INNER JOIN lead_customer C ON(LD.lead_id = C.customer_lead_id)";
        $sql_query .= " INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id = LD.lead_id)";
        $sql_query .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
        $sql_query .= " INNER JOIN master_status MS ON(MS.status_id = LD.lead_status_id)";
        $sql_query .= " WHERE LD.lead_id=$lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";
        $sql_query .= " AND L.loan_status_id = 14";

        $tempDetails = $this->context->query($sql_query);

        if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {

            $lead_details = $tempDetails['items'][0];

            $roi = !empty($lead_details["roi"]) ? $lead_details["roi"] : 0;
            $loan_noc_settlement_letter = !empty($lead_details["loan_noc_settlement_letter"]) ? $lead_details["loan_noc_settlement_letter"] : 0;
            $loan_settled_date = !empty($lead_details["loan_noc_settled_letter_datetime"]) ? date('d-m-Y H:i:s', strtotime($lead_details["loan_noc_settled_letter_datetime"])) : 0;
            $loan_closure_date = !empty($lead_details["loan_noc_closed_letter_datetime"]) ? date('d-m-Y H:i:s', strtotime($lead_details["loan_noc_closed_letter_datetime"])) : 0;
            $loan_noc_closing_letter = !empty($lead_details["loan_noc_closing_letter"]) ? $lead_details["loan_noc_closing_letter"] : 0;
            $penal_roi = $roi * 2;
            $disbursal_date = !empty($lead_details["lead_final_disbursed_date"]) ? date('d-m-Y', strtotime($lead_details["lead_final_disbursed_date"])) : '';
            $repayment_date = !empty($lead_details["repayment_date"]) ? date('d-m-Y', strtotime($lead_details["repayment_date"])) : '';
            $tenure = !empty($lead_details["tenure"]) ? $lead_details["tenure"] : 0;
            $repayment_amount = !empty($lead_details["repayment_amount"]) ? $lead_details["repayment_amount"] : 0;
            //            $processing_fee_percetage = !empty($lead_details["processing_fee_percent"]) ? $lead_details["processing_fee_percent"] : 0;
            $advance_interest_amount_deducted = !empty($lead_details["cam_advance_interest_amount"]) ? $lead_details["cam_advance_interest_amount"] : 0;
            $principle_discount_amount = !empty($lead_details["loan_principle_discount_amount"]) ? $lead_details["loan_principle_discount_amount"] : 0;
            $interest_discount_amount = !empty($lead_details["loan_interest_discount_amount"]) ? $lead_details["loan_interest_discount_amount"] : 0;
            $penalty_discount_amount = !empty($lead_details["loan_penalty_discount_amount"]) ? $lead_details["loan_penalty_discount_amount"] : 0;
            $total_discount_amount = !empty($lead_details["loan_total_discount_amount"]) ? $lead_details["loan_total_discount_amount"] : 0;

            $rtenure = 0;
            $ptenure = 0;

            $date_of_receive = strtotime(date('d-m-Y'));
            $date_of_receive_payment_verified = strtotime(date('d-m-Y'));
            $disbursal_date_to_time = strtotime($disbursal_date);
            $repayment_date_to_time = strtotime($repayment_date);

            $date_of_receive_flag = 0;

            //First get the date of received of settle or close case so that interest and panelty will be freez
            $tempDetails = $this->context->query("SELECT CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on FROM collection CO WHERE CO.lead_id=$lead_id AND CO.repayment_type=17 AND CO.payment_verification=1 AND CO.collection_active=1 AND CO.collection_deleted=0 ORDER BY CO.id ASC LIMIT 1");

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {

                $first_settle_data = $tempDetails['items'][0];

                if ($first_settle_data['repayment_type'] == 17 && !empty($first_settle_data["date_of_recived"]) && $first_settle_data["date_of_recived"] != '0000-00-00') {
                    $settle_date_of_receive = strtotime(date('d-m-Y', strtotime($first_settle_data["date_of_recived"])));
                    $settle_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_settle_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_settled_date'] = date('Y-m-d', strtotime($first_settle_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 1;
                    }
                }
            }

            $tempDetails = $this->context->query("SELECT CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on FROM collection CO WHERE CO.lead_id=$lead_id AND CO.repayment_type=16 AND CO.payment_verification=1 AND CO.collection_active=1 AND CO.collection_deleted=0 ORDER BY CO.id ASC LIMIT 1");

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $first_close_data = $tempDetails['items'][0];
                if ($first_close_data['repayment_type'] == 16 && !empty($first_close_data["date_of_recived"]) && $first_close_data["date_of_recived"] != '0000-00-00') {
                    $close_date_of_receive = strtotime(date('d-m-Y', strtotime($first_close_data["date_of_recived"])));
                    $close_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_close_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_closure_date'] = date('Y-m-d', strtotime($first_close_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 2;
                    }
                }
            }

            $tempDetails = $this->context->query("SELECT CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on FROM collection CO WHERE CO.lead_id=$lead_id AND CO.repayment_type=18 AND CO.payment_verification=1 AND CO.collection_active=1 AND CO.collection_deleted=0 ORDER BY CO.id ASC LIMIT 1");

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $first_writeoff_data = $tempDetails['items'][0];
                if ($first_writeoff_data['repayment_type'] == 18 && !empty($first_writeoff_data["date_of_recived"]) && $first_writeoff_data["date_of_recived"] != '0000-00-00') {
                    $writeoff_date_of_receive = strtotime(date('d-m-Y', strtotime($first_writeoff_data["date_of_recived"])));
                    $writeoff_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_writeoff_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_writeoff_date'] = date('Y-m-d', strtotime($first_writeoff_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 3;
                    }
                }
            }

            if ($date_of_receive_flag == 1) {
                $date_of_receive = $settle_date_of_receive;
                $date_of_receive_payment_verified = $settle_date_of_receive_payment_verified;
            } else if ($date_of_receive_flag == 3) {
                $date_of_receive = $writeoff_date_of_receive;
                $date_of_receive_payment_verified = $writeoff_date_of_receive_payment_verified;
            } else if ($date_of_receive_flag == 2) {
                $date_of_receive = $close_date_of_receive;
                $date_of_receive_payment_verified = $close_date_of_receive_payment_verified;
            }

            $tempDetails = $this->context->query("SELECT  SUM(CO.received_amount) as total_paid FROM collection CO WHERE CO.lead_id=$lead_id AND CO.payment_verification=1 AND CO.collection_active=1 AND CO.collection_deleted=0");

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $total_received_amount = !empty($tempDetails['items'][0]["total_paid"]) ? $tempDetails['items'][0]["total_paid"] : 0;
            }


            if ($date_of_receive <= $repayment_date_to_time) {
                $realdays = $date_of_receive - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            } else {
                $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            }

            if ($date_of_receive_payment_verified <= $repayment_date_to_time) {
                //                $realdays = $date_of_receive - $disbursal_date_to_time;
            } else {
                $endDate = $date_of_receive_payment_verified - $repayment_date_to_time;
                $oneDay = (60 * 60 * 24);
                $dateDays60 = ($oneDay * 60);

                if ($endDate <= $dateDays60) {
                    $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                    $rtenure = ($realdays / 60 / 60 / 24);
                    $paneldays = $date_of_receive_payment_verified - $repayment_date_to_time;
                    $ptenure = ($paneldays / 60 / 60 / 24);
                } else {
                    $ptenure = 60;
                }
            }

            $tenure = ($repayment_date_to_time - $disbursal_date_to_time) / (60 * 60 * 24);

            $interest_amount = round(($loan_recommended * $roi * $tenure) / 100);

            $realIntrest = round(($loan_recommended * $roi * $rtenure) / 100);

            $repayment_with_real_interest = $loan_recommended + $realIntrest;

            $total_interest_amount = $repayment_amount - $loan_recommended;

            if ($total_received_amount < $interest_amount) { // 700 < 1000
                $total_interest_amount_received = $total_received_amount;
                $total_interest_amount_pending = $interest_amount - ($total_interest_amount_received + $interest_discount_amount);
            } else if (($total_received_amount >= $interest_amount)) {
                $total_interest_amount_received = $interest_amount - $interest_discount_amount;
                $total_interest_amount_pending = 0;
            } else {
                $total_interest_amount_received = 0;
                $total_interest_amount_pending = $interest_amount;
            }

            if (($total_received_amount >= $interest_amount) && ($total_received_amount < $repayment_amount)) {
                $total_principle_amount_received = ($total_received_amount + $advance_interest_amount_deducted + $interest_discount_amount) - $interest_amount;
                $total_principle_amount_pending = $loan_recommended - $total_principle_amount_received - $principle_discount_amount;
            } else if (($total_received_amount >= $loan_recommended)) {
                $total_principle_amount_received = $loan_recommended - $principle_discount_amount;
                $total_principle_amount_pending = 0;
            } else {
                $total_principle_amount_received = 0;
                $total_principle_amount_pending = $loan_recommended - $principle_discount_amount;
            }


            if ($advance_interest_amount_deducted > 0) {
                $total_interest_amount = $advance_interest_amount_deducted;
                $total_interest_amount_received = $advance_interest_amount_deducted - $interest_discount_amount;
                $total_interest_amount_pending = 0;
                $total_received_amount = $total_received_amount + $interest_discount_amount + $total_interest_amount_received;
            }

            //if(!in_array($lead_status_id,[16,17,18])){
            $penaltyIntrest = ($loan_recommended * ($penal_roi) * $ptenure) / 100;
            //}
            $total_repayment_amount = ($repayment_amount + $penaltyIntrest + $advance_interest_amount_deducted);
            $total_due_amount = $total_repayment_amount - $total_received_amount - $total_discount_amount;
            if ($penaltyIntrest > 0) {
                if (($total_received_amount > $repayment_amount) && ($total_received_amount < $total_repayment_amount)) {
                    $total_penalty_interest_received = $total_received_amount - $repayment_amount - $advance_interest_amount_deducted;
                    $total_penalty_interest_pending = $penaltyIntrest - $total_penalty_interest_received - $penalty_discount_amount;
                } else if ($total_received_amount >= $total_repayment_amount) {
                    $total_penalty_interest_received = $penaltyIntrest - $penalty_discount_amount;
                    $total_penalty_interest_pending = 0;
                } else {
                    $total_penalty_interest_received = 0;
                    $total_penalty_interest_pending = $penaltyIntrest - $penalty_discount_amount;
                }
            } else {
                $total_penalty_interest_received = 0;
                $total_penalty_interest_pending = 0;
            }
            if ($status == 'CLOSED') {
                $total_due_amount = 0;
            }
        }

        $data['loan_no'] = $loan_no;
        $data['lead_id'] = $lead_id;
        $data['full_name'] = $leadDetails['full_name'];
        $data['lead_black_list_flag'] = !empty($lead_details["lead_black_list_flag"]) ? $lead_details["lead_black_list_flag"] : '';
        $data['status'] = $status;
        $data['disbursal_date'] = $disbursal_date;
        $data['repayment_date'] = $repayment_date;
        $data['repayment_interest_date'] = ($tenure > $rtenure) ? date("d-m-Y", $date_of_receive) : $repayment_date;
        $data['roi'] = round($roi, 2);
        $data['penal_roi'] = round($penal_roi, 2);
        $data['tenure'] = $tenure;
        $data['realdays'] = $rtenure;
        $data['penalty_days'] = $ptenure;
        $data['recovered_interest_amount_deducted'] = round($recovered_interest_amount_deducted, 0);
        $data['advance_interest_amount_deducted'] = round($advance_interest_amount_deducted, 0);
        $data['repayment_amount'] = round($repayment_amount, 0);

        $data['real_interest'] = round($realIntrest, 0);
        $data['repayment_with_real_interest'] = round($repayment_with_real_interest, 0);

        $data['total_interest_amount'] = round($total_interest_amount);
        $data['interest_discount_amount'] = round($interest_discount_amount, 0);
        $data['total_interest_amount_received'] = round($total_interest_amount_received, 0);
        $data['total_interest_amount_pending'] = round($total_interest_amount_pending, 0);

        $data['loan_recommended'] = round($loan_recommended, 0);
        $data['principle_discount_amount'] = round($principle_discount_amount, 0);
        $data['total_principle_amount_received'] = round($total_principle_amount_received, 0);
        $data['total_principle_amount_pending'] = round($total_principle_amount_pending, 0);

        $data['penalty_interest'] = round($penaltyIntrest, 0);
        $data['penalty_discount_amount'] = round($penalty_discount_amount, 0);
        $data['total_penalty_interest_received'] = round($total_penalty_interest_received, 0);
        $data['total_penalty_interest_pending'] = round($total_penalty_interest_pending, 0);

        $data['total_repayment_amount'] = round($total_repayment_amount, 0);
        $data['total_received_amount'] = round($total_received_amount, 0);
        $data['total_due_amount'] = round($total_due_amount, 0);
        $data['total_discount_amount'] = round($total_discount_amount, 0);
        $data['loan_noc_settled_letter_datetime'] = $loan_settled_date;
        $data['loan_noc_settlement_letter'] = $loan_noc_settlement_letter;
        $data['loan_noc_closed_letter_datetime'] = $loan_closure_date;
        $data['loan_noc_closing_letter'] = $loan_noc_closing_letter;

        if (!empty($loan_no) && !in_array($lead_status_id, [0])) {

            $update_loan_array['loan_principle_payable_amount'] = $loan_recommended;
            $update_loan_array['loan_interest_payable_amount'] = $total_interest_amount;
            $update_loan_array['loan_penalty_payable_amount'] = $penaltyIntrest;
            $update_loan_array['loan_principle_received_amount'] = $total_principle_amount_received;
            $update_loan_array['loan_interest_received_amount'] = $total_interest_amount_received;
            $update_loan_array['loan_penalty_received_amount'] = $total_penalty_interest_received;
            $update_loan_array['loan_principle_outstanding_amount'] = $total_principle_amount_pending;
            $update_loan_array['loan_interest_outstanding_amount'] = $total_interest_amount_pending;
            $update_loan_array['loan_penalty_outstanding_amount'] = $total_penalty_interest_pending;
            $update_loan_array['loan_total_payable_amount'] = $total_repayment_amount;
            $update_loan_array['loan_total_received_amount'] = $total_received_amount;
            $update_loan_array['loan_total_outstanding_amount'] = $total_due_amount;

            $this->updateLoanTable($lead_id, $update_loan_array);
        }

        $result_array = array("status" => 1, "repayment_data" => $data);

        return $result_array;
    }

    public function getCustomerMandatoryDocumentByLeadId($lead_id, $document_type_id = "", $document_id = "") {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($lead_id) && !empty($document_type_id)) {

            $sql = "SELECT D.docs_id, D.docs_master_id";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " INNER JOIN docs D  ON (D.lead_id = LD.lead_id)";
            $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";
            $sql .= " AND D.docs_master_id IN($document_type_id) AND D.docs_active=1 AND D.docs_deleted=0";

            if (!empty($document_id)) {
                $sql .= " AND D.docs_id = $document_id";
            }

            $sql .= " ORDER BY D.docs_id DESC";

            $documentResult = $this->context->query($sql);

            if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $documentResult['items'];
            }
        }

        return $return_array;
    }

    public function getCustomerMandatoryDocumentByPan($pancard, $document_type_id = "", $document_id = "") {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($pancard) && !empty($document_type_id)) {

            $sql = "SELECT D.docs_id, D.docs_master_id";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " INNER JOIN docs D  ON (D.pancard = LD.pancard)";
            $sql .= " WHERE LD.loan_no!='' AND LD.loan_no IS NOT NULL AND LD.pancard = '$pancard' AND LD.pancard IS NOT NULL AND LD.pancard!='' AND LD.lead_active=1 AND LD.lead_deleted=0";
            $sql .= " AND D.pancard = '$pancard' AND D.pancard IS NOT NULL AND D.pancard!=''";
            $sql .= " AND D.docs_master_id IN($document_type_id) AND D.docs_active=1 AND D.docs_deleted=0";

            if (!empty($document_id)) {
                $sql .= " AND D.docs_id = $document_id";
            }

            $sql .= " ORDER BY D.docs_id DESC";

            $documentResult = $this->context->query($sql);

            if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $documentResult['items'];
            }
        }

        return $return_array;
    }

    public function getCustomerMandatoryDocumentMaster($document_type_id = "") {

        $return_array = array("status" => 0, "master_doc_data" => array());

        $sql = "SELECT id, docs_sub_type";
        $sql .= " FROM docs_master";
        $sql .= " WHERE document_active=1 AND document_deleted=0";

        if (!empty($document_type_id)) {
            $sql .= " AND id IN($document_type_id)";
        }

        $documentResult = $this->context->query($sql);

        if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
            $return_array['status'] = 1;
            $return_array['master_doc_data'] = $documentResult['items'];
        }


        return $return_array;
    }

    public function get_customer_details_by_pancard($pancard) {
        $result_array = array('status' => 0);

        $query = " SELECT CPD.* ";
        $query .= " FROM customer_profile_details AS CPD ";
        $query .= " WHERE CPD.cpd_pancard = '$pancard' ";

        $temp_cpd_details = $this->context->query($query);

        if (!empty($temp_cpd_details['items']) && count($temp_cpd_details['items']) > 0) {
            $result_array['status'] = 1;
            $result_array['customer_profile_details'] = $temp_cpd_details['items'];
        }

        if (empty($result_array['status'])) {

            $where_in = '(14, 16, 17, 19)';

            $sql = "SELECT LD.lead_id, CIF.*, ";
            $sql .= " LC.pancard_verified_status, LC.pancard_verified_on, LC.email, LC.email_verified_status, LC.email_verified_on, LC.alternate_email, ";
            $sql .= " LC.alternate_email_verified_status, LC.alternate_email_verified_on, LC.mobile, LC.mobile_verified_status, LC.alternate_mobile, ";
            $sql .= ' CONCAT_WS(" ", LC.current_house, LC.current_locality) as current_residence_address, ';
            $sql .= ' LC.current_house, LC.current_locality, LC.current_landmark, LC.cr_residence_pincode, LC.current_state, LC.current_city, ';
            $sql .= " LC.aadhar_no, LC.customer_digital_ekyc_flag, LC.customer_digital_ekyc_done_on  ";
            $sql .= " FROM leads AS LD ";
            $sql .= " INNER JOIN cif_customer AS CIF ON LD.pancard = CIF.cif_pancard ";
            $sql .= " INNER JOIN lead_customer AS LC ON LC.customer_lead_id = LD.lead_id ";
            $sql .= " WHERE LD.pancard = '$pancard' ";
            $sql .= " AND LD.lead_status_id IN $where_in ";
            $sql .= " order by LD.lead_id DESC limit 1";

            $tempDetails = $this->context->query($sql);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $result_array['status'] = 2;
                $result_array['lead_details'] = $tempDetails['items'];
            }
        }

        return $result_array;
    }

    public function checkCustomerDedupe($lead_id) {

        $current_date = date("Y-m-d");

        $return_array = array("status" => 0, "message" => '');

        $sql = 'SELECT LD.lead_id, LD.pancard, LD.mobile, LD.email';
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
        $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

        $leadDetails = $this->context->query($sql);

        if (!empty($leadDetails['items']) && count($leadDetails['items']) > 0) {

            $lead_data = $leadDetails['items'][0];

            $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

            $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

            $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

            if (in_array($mobile, $this->white_list_mobile_no_rejection)) {
                return $return_array;
            }

            if (empty($pancard) && empty($email) && empty($mobile)) {
                return $return_array;
            }

            $sql = "SELECT lead_id FROM leads WHERE lead_id!=$lead_id AND lead_active=1 AND lead_deleted=0 AND lead_entry_date = '" . $current_date . "' AND ";

            $where = "";

            if (!empty($pancard)) {
                $where .= " OR pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= " OR mobile='$mobile'";
            }

            if (!empty($email)) {
                $where .= " OR email='$email'";
            }

            $where = ltrim($where, 'OR ');
            $where = "(" . $where . ")";

            $sql = $sql . " " . $where . " ORDER BY lead_id DESC";

            $customerResult = $this->context->query($sql);

            if (!empty($customerResult['items']) && count($customerResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['message'] = "Customer already applied for the day.";
            }
        }

        return $return_array;
    }

    public function checkCustomerDedupeByInput($mobile = '', $pancard = '', $email = '') {

        $return_array = array("status" => 0, "message" => '');

        if (!empty($mobile) || !empty($pancard) || !empty($email)) {


            if (in_array($mobile, $this->white_list_mobile_no_rejection)) {
                return $return_array;
            }

            $pancard = trim(strtoupper($pancard));
            $email = trim(strtoupper($email));

            $sql = "SELECT lead_id FROM leads WHERE lead_active=1 AND lead_deleted=0 AND lead_entry_date = '" . date("Y-m-d") . "' AND ";

            $where = "";

            if (!empty($pancard)) {
                $where .= " OR pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= " OR mobile='$mobile'";
            }

            if (!empty($email)) {
                $where .= " OR email='$email'";
            }

            $where = ltrim($where, 'OR ');
            $where = "(" . $where . ")";

            $sql = $sql . " " . $where . " ORDER BY lead_id DESC";

            $customerResult = $this->context->query($sql);
            //    echo json_encode($customerResult);

            if (!empty($customerResult['items']) && count($customerResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['message'] = "Customer already applied for the day.";
            }
        }

        return $return_array;
    }

    public function getCustomerBankAccountDetails($lead_id, $cust_bank_id = null) {

        $return_array = array("status" => 0, "banking_data" => array());

        $sql = "SELECT * FROM customer_banking WHERE customer_banking_active=1 AND customer_banking_deleted=0 AND lead_id=$lead_id";

        if (!empty($cust_bank_id)) {
            $sql .= " AND id=$cust_bank_id";
        }

        $sql .= " ORDER BY id DESC";

        $tempDetails = $this->context->query($sql);

        if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
            $return_array["status"] = 1;
            $return_array["banking_data"] = $tempDetails['items'][0];
        }

        return $return_array;
    }

    public function getUMSUserDetails($user_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($user_id)) {

            $sql = "SELECT * FROM users WHERE user_id=$user_id";

            $tempDetails = $this->context->query($sql);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails['items'][0];
            }
        }


        return $return_array;
    }

    public function getLoanApplicationDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            $sql = "SELECT LD.lead_id, LD.source , LD.status,LD.loan_no, LC.email, LD.mobile, LC.alternate_mobile, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) name, LD.lead_final_disbursed_date, ";
            $sql .= " CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard,";
            $sql .= " CAM.loan_recommended, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.roi,  MB.m_branch_name branch,";
            $sql .= " L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_outstanding_amount, L.loan_penalty_outstanding_amount, L.loan_recovery_status_id, L.loan_total_outstanding_amount";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer LC ON (LC.customer_lead_id = LD.lead_id)";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON (LD.lead_id = CAM.lead_id)";
            $sql .= " INNER JOIN loan L ON (LD.lead_id = L.lead_id)";
            $sql .= " LEFT JOIN master_state MS ON (LD.state_id = MS.m_state_id)";
            $sql .= " LEFT JOIN master_city MC ON (LD.city_id = MC.m_city_id)";
            $sql .= " LEFT JOIN master_branch MB ON (LD.lead_branch_id = MB.m_branch_id)";
            $sql .= " WHERE LD.lead_id=$lead_id";

            $tempDetails = $this->context->query($sql);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getFinboxApiLog($lead_id, $method_id) {

        $return_array = array("status" => 0, "finbox_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT finbox_dc_lead_id, finbox_dc_response, finbox_dc_method_id, finbox_dc_api_status_id";
            $sql .= " FROM api_finbox_device_connect_logs";
            $sql .= " WHERE finbox_dc_lead_id = $lead_id AND finbox_dc_active=1 AND finbox_dc_deleted=0";
            $sql .= " AND finbox_dc_method_id=$method_id AND finbox_dc_api_status_id=1";
            $sql .= " ORDER BY finbox_dc_id DESC";

            $esignLogResult = $this->context->query($sql);

            if (!empty($esignLogResult['items']) && count($esignLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['finbox_log_data'] = $esignLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getFinboxBureauConnectApiLog($lead_id, $method_id) {

        $return_array = array("status" => 0, "finbox_log_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT finbox_br_lead_id, finbox_br_response, finbox_br_method_id, finbox_br_api_status_id";
            $sql .= " FROM api_finbox_bureauconnect_logs";
            $sql .= " WHERE finbox_br_lead_id = $lead_id AND finbox_br_active=1 AND finbox_br_deleted=0";
            $sql .= " AND finbox_Br_method_id=$method_id AND finbox_br_api_status_id=1";
            $sql .= " ORDER BY finbox_br_id DESC";

            $esignLogResult = $this->context->query($sql);

            if (!empty($esignLogResult['items']) && count($esignLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['finbox_log_data'] = $esignLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getCibilDetails($lead_id) {
        $return_array = array("status" => 0, "cibil_data" => array());
        if (!empty($lead_id)) {

            $sql = "select lead_id,pancard,api1_response from tbl_cibil_log";
            $sql .= " where lead_id=" . $lead_id;

            $getCibilDetails = $this->context->query($sql);

            if (!empty($getCibilDetails['items']) && count($getCibilDetails['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['cibil_data'] = $getCibilDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getBankStatementDetails($lead_id) {
        $return_array = array("status" => 0, "docs_data" => array());

        $docs_details = $this->context->query('SELECT docs_id FROM `docs` where lead_id =' . $lead_id . ' AND docs_master_id = 6 ORDER BY  docs_id DESC LIMIT 1');

        if (!empty($docs_details['items']) && count($docs_details['items']) > 0) {
            $return_array['status'] = 1;
            $return_array['bank_data'] = $docs_details['items'][0];
        }
        //        if ($docs_details->num_rows() > 0) {
        //            $docs = $docs_details->row();
        //            $doc_id = $docs->docs_id;
        //        }

        $doc_id = $return_array['bank_data']['docs_id'];

        if (!empty($lead_id) && !empty($doc_id)) {

            $tempDetails = $this->context->query("SELECT * from docs where docs_id=" . $doc_id);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['docs_data'] = $tempDetails['items'][0];
            }

            //            if ($tempDetails->num_rows()) {
            //                $return_array["status"] = 1;
            //                $return_array["docs_data"] = $tempDetails->row_array();
            //            }
        }
        return $return_array;
    }

    public function getCibilData($lead_id = "") {

        $return_array = array("status" => 0, "cibil_data" => array());

        $sql = "SELECT lead_id, api1_response as response FROM tbl_cibil_log";
        $sql .= " ORDER BY RAND() LIMIT 1";

        $documentResult = $this->context->query($sql);

        if (!empty($documentResult['items']) && count($documentResult['items']) > 0) {
            $return_array['status'] = 1;
            $return_array['cibil_data'] = $documentResult['items'];
        }


        return $return_array;
    }

    public function getProfileFullDetails($profile_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($profile_id)) {

            $sql_query = "SELECT * FROM customer_profile WHERE cp_id=$profile_id AND cp_active=1 AND cp_deleted=0";

            $tempDetails = $this->context->query($sql_query);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function updateCustomerProfileTable($profile_id, $update_array) {

        if (empty($profile_id) || empty($update_array)) {
            return null;
        }

        return $this->context->update('customer_profile', $update_array, " cp_id=$profile_id");
    }

    public function insertProfileFollowupLog($profile_id, $profile_status_id, $remark) {

        if (empty($profile_id) || empty($profile_status_id) || empty($remark)) {
            return null;
        }

        $user_id = 0;

        $insert_log_array = array();
        $insert_log_array['profile_followup_profile_id'] = $profile_id;
        $insert_log_array['profile_followup_user_id'] = $user_id;
        $insert_log_array['profile_followup_status_id'] = $profile_status_id;
        $insert_log_array['profile_followup_remarks'] = addslashes($remark);
        $insert_log_array['profile_followup_created_on'] = date("Y-m-d H:i:s");

        return $this->context->insert('customer_profile_followup', $insert_log_array);
    }

    public function GetSettlementDetails($pancard) {

        $return_array = array("status" => 0, "settlement_data" => array());

        if (!empty($pancard)) {

            $sql = "SELECT lead_id, loan_no, lead_status_id FROM settlement WHERE lead_status_id IN(17, 18) AND pancard=$pancard AND lead_active=1";

            $settlementResult = $this->context->query($sql);

            if (!empty($settlementResult['items']) && count($settlementResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['settlement_data'] = $settlementResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getDPDDetails($pancard) {

        $return_array = array("status" => 0, "dpd_data" => array());

        if (!empty($pancard)) {

            $sql = "SELECT
                        LD.lead_id,
                        LD.loan_no,
                        LD.lead_status_id,
                        L.loan_closure_date,
                        L.loan_settled_date,
                        L.loan_writeoff_date,
                        CAM.repayment_date,
                        CASE
                            WHEN L.loan_closure_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, CAM.repayment_date, L.loan_closure_date)
                            WHEN L.loan_settled_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, CAM.repayment_date, L.loan_settled_date)
                            WHEN L.loan_writeoff_date IS NOT NULL THEN TIMESTAMPDIFF(DAY, CAM.repayment_date, L.loan_writeoff_date)
                            ELSE TIMESTAMPDIFF(DAY, CAM.repayment_date, NOW())
                        END AS DPD
                    FROM
                        leads LD
                    INNER JOIN
                        loan L
                        ON LD.lead_id = L.lead_id
                    INNER JOIN
                        credit_analysis_memo CAM
                        ON LD.lead_id = CAM.lead_id
                    WHERE
                        LD.lead_status_id IN (14, 16, 17, 18, 19)
                        AND LD.lead_active = 1
                        AND LD.pancard='$pancard'
                    ORDER BY
                        L.loan_id DESC
                    LIMIT 1";

            $dpdResult = $this->context->query($sql);

            if (!empty($dpdResult['items']) && count($dpdResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['dpd_data'] = $dpdResult['items'][0];
            }
        }

        return $return_array;
    }

    public function getAppsFlyerLogs($id) {

        $return_array = array("status" => 0, "data" => array());

        if (!empty($id)) {
            $sql = "SELECT * FROM api_callback_appsflyer WHERE acaf_active=1 AND acaf_deleted=0 AND acaf_appsflyer_id ='$id'";
            $sql .= " ORDER BY acaf_id DESC LIMIT 1";

            $tempDetails = $this->context->query($sql);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["data"] = $tempDetails['items'][0];
            }
        }

        return $return_array;
    }

    public function getAppsFlyerPushEventLogs($id, $event_name, $lead_id) {

        $return_array = array("status" => 0, "count" => 0);

        if (!empty($id)) {
            $sql = "SELECT aape_id FROM api_appsflyer_push_events WHERE aape_appsflyer_id='$id' AND aape_event_name='$event_name' AND aape_lead_id=$lead_id AND aape_api_status_id=1 AND aape_active=1 AND aape_deleted=0 ORDER BY aape_id DESC LIMIT 1";

            $tempDetails = $this->context->query($sql);

            if (!empty($tempDetails['items']) && count($tempDetails['items']) > 0) {
                $return_array["status"] = 1;
                $return_array["count"] = $tempDetails['count'];
                $return_array["id"] = $tempDetails['items'][0]['aape_id'];
            }
        }

        return $return_array;
    }

    public function getUPIApiLog($lead_id, $method_id = 1) {
        $return_array = array("status" => 0, "qrcode_tran_log_data" => array());
        if (!empty($lead_id)) {

            $sql = "SELECT au_transaction_id, au_request_datetime, au_response ";
            $sql .= " FROM api_upi_logs";
            $sql .= " WHERE au_lead_id = $lead_id AND au_active=1 AND au_deleted=0";
            $sql .= " AND au_method_id= $method_id AND au_status_id=1 AND au_status_check=0";
            $sql .= " ORDER BY au_id DESC LIMIT 1";

            $qrcodeLogResult = $this->context->query($sql);

            if (!empty($qrcodeLogResult['items']) && count($qrcodeLogResult['items']) > 0) {
                $return_array['status'] = 1;
                $return_array['upi_data'] = $qrcodeLogResult['items'][0];
            }
        }

        return $return_array;
    }

    public function UpdateUPIApiLog($table, $update_array, $where) {

        if (empty($table) || empty($update_array) || empty($where)) {
            return null;
        }

        $update_query = "UPDATE $table SET ";
        $update_query .= implode(", ", array_map(function ($v, $k) {
            return sprintf("%s='%s'", $k, $v);
        }, $update_array, array_keys($update_array)));

        $update_query .= " WHERE ";
        $update_query .= implode(" AND ", array_map(function ($v, $k) {
            return sprintf("%s='%s'", $k, $v);
        }, $where, array_keys($where)));

        return $this->context->query($update_query);
    }
}
