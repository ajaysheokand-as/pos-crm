<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CronCallController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronCall_Model', 'CallModel');
        $this->load->model('CronJobs/CronEmailer_Model', 'EmailModel');
    }

    public function voiceBlastReminder5_2Day() {

        $loan_number_array = array();
        $mobile_number_array = array();

        $cron_name = 'reminder_5to2_days';

        if (true) {

            $tempDetails = $this->CallModel->getAllRepaymentReminderEmails(2, 5);

            $start_datetime = date("d-m-Y H:i:s");

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['mobile'])) {

                        $mobile_no = $customer_data['mobile'];
                        $loan_no = $customer_data['loan_no'];
                        $loan_number_array[] = $loan_no;
                        $mobile_number_array[] = $mobile_no;
                    }
                }

                if (!empty($mobile_number_array)) {
                    $return_array = $this->reminderCallScheduleApi($cron_name, 1, $mobile_number_array);
                    $return_status = "";
                    if ($return_array['status'] == 1) {
                        $return_status = "voice blast sent successfully";
                    } else {
                        $return_status = $return_array['errors'];
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-VOICE BLAST $cron_name REMINDER CALL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = $return_status;
                $email_data['message'] .= "<br/><br/>Toal Calls : " . count($mobile_number_array);
                $email_data['message'] .= "<br/><br/>LOAN NO : ";
                $email_data['message'] .= implode(", ", $loan_number_array);

                lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);

                echo "Toal Calls :" . count($mobile_number_array);
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-VOICE BLAST $cron_name REMINDER CALL - start time - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);
            echo "Unauthorized";
        }
    }

    public function voiceBlastReminder1_0Day() {
        $loan_number_array = array();
        $mobile_number_array = array();

        $cron_name = 'reminder_1to0_days';

        $return_array = $this->reminderCallScheduleApi($cron_name, 1, 1);
        if (true) {

            $tempDetails = $this->CallModel->getAllRepaymentReminderEmails(0, 1);

            $start_datetime = date("d-m-Y H:i:s");

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['mobile'])) {

                        $mobile_no = $customer_data['mobile'];
                        $loan_no = $customer_data['loan_no'];
                        $loan_number_array[] = $loan_no;
                        $mobile_number_array[] = $mobile_no;
                    }
                }

                if (!empty($mobile_number_array)) {
                    $return_array = $this->reminderCallScheduleApi($cron_name, 1, $mobile_number_array);
                    $return_status = "";
                    if ($return_array['status'] == 1) {
                        $return_status = "voice blast sent successfully";
                    } else {
                        $return_status = $return_array['errors'];
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-VOICE BLAST $cron_name REMINDER CALL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = $return_status;
                $email_data['message'] .= "<br/><br/>Toal Calls : " . count($mobile_number_array);
                $email_data['message'] .= "<br/><br/>LOAN NO : ";
                $email_data['message'] .= implode(", ", $loan_number_array);

                lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);

                echo "Toal Calls :" . count($mobile_number_array);
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-VOICE BLAST $cron_name REMINDER CALL - start time - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);
            echo "Unauthorized";
        }
    }

    public function voiceBlastOutstanding1_10Day() {
        $loan_number_array = array();
        $mobile_number_array = array();

        $cron_name = 'outstanding_1to10_days';

        if (true) {

            $tempDetails = $this->CallModel->getAllDefaulterCollectionApps(1, 10);

            $start_datetime = date("d-m-Y H:i:s");

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['mobile'])) {

                        $mobile_no = $customer_data['mobile'];
                        $loan_no = $customer_data['loan_no'];
                        $loan_number_array[] = $loan_no;
                        $mobile_number_array[] = $mobile_no;
                    }
                }

                if (!empty($mobile_number_array)) {
                    $return_array = $this->reminderCallScheduleApi($cron_name, 2, $mobile_number_array);
                    $return_status = "";
                    if ($return_array['status'] == 1) {
                        $return_status = "voice blast sent successfully";
                    } else {
                        $return_status = $return_array['errors'];
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-VOICE BLAST $cron_name Outstanding CALL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = $return_status;
                $email_data['message'] .= "<br/><br/>Toal Calls : " . count($mobile_number_array);
                $email_data['message'] .= "<br/><br/>LOAN NO : ";
                $email_data['message'] .= implode(", ", $loan_number_array);

                lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);

                echo "Toal Calls :" . count($mobile_number_array);
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-VOICE BLAST $cron_name Outstanding CALL - start time - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            lw_send_email($email_data['email'], $email_data['subject'], $email_data['message'], "");
            echo "Unauthorized";
        }
    }

    public function voiceBlastDefaulter11_20Day() {
        $loan_number_array = array();
        $mobile_number_array = array();

        $cron_name = 'defaulter_11to20_days';

        if (true) {

            $tempDetails = $this->CallModel->getAllDefaulterCollectionApps(11, 20);

            $start_datetime = date("d-m-Y H:i:s");

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['mobile'])) {

                        $mobile_no = $customer_data['mobile'];
                        $loan_no = $customer_data['loan_no'];
                        $loan_number_array[] = $loan_no;
                        $mobile_number_array[] = $mobile_no;
                    }
                }

                if (!empty($mobile_number_array)) {
                    $return_array = $this->reminderCallScheduleApi($cron_name, 3, $mobile_number_array);
                    $return_status = "";
                    if ($return_array['status'] == 1) {
                        $return_status = "voice blast sent successfully";
                    } else {
                        $return_status = $return_array['errors'];
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-VOICE BLAST $cron_name Defaulter CALL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = $return_status;
                $email_data['message'] .= "<br/><br/>Toal Calls : " . count($mobile_number_array);
                $email_data['message'] .= "<br/><br/>LOAN NO : ";
                $email_data['message'] .= implode(", ", $loan_number_array);

                lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);

                echo "Toal Calls :" . count($mobile_number_array);
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-VOICE BLAST $cron_name Defaulter CALL - start time - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            lw_send_email($email_data['email'], $email_data['subject'], $email_data['message']);
//            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function reminderCallScheduleApi($cron_name, $call_type, $mobile_numbder_array) {

        $mobile_numbder_array = array(9170004606);

        $campaign_array = array(1 => 684515, 2 => 635987, 3 => 635988);
        $accountSid = "vrindafinlease1";
        $apiKey = "44cf86fd0a57d3ba8f8bf12165cc28a9cdc81cccf66489d8";
        $apiToken = "72bc2b32c7e679769f1a7d73bd98a1d4f785b9d86e7fed0d";
        $authrization = base64_encode($apiKey . ":" . $apiToken);

        $apiStatusId = 0;
        $apiRequestJson = "";
        $apiResponseJson = "";
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $apiResponseDateTime = "";
        $errorMessage = "";
        $curlError = "";
        $campagin_name = "";
        $hardcode_response = false;

        $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
        try {

            $apiUrl = "https://api.exotel.com/v2/accounts/" . $accountSid . "/campaigns";

            if (empty($call_type)) {
                throw new Exception("Missing Call Type");
            }

            if (empty($cron_name)) {
                throw new Exception("Missing cron name");
            }

            if (empty($mobile_numbder_array)) {
                throw new Exception("Missing mobile numbers");
            }

            if (!in_array($call_type, array(1, 2, 3))) {
                throw new Exception("Call Type out of range");
            }



            $caller_id = "+918069453391";
            $campaign_id = $campaign_array[$call_type];

            if ($call_type == 1) {
                $campagin_name = "cron_" . $cron_name . "_" . date("Y-m-d H:i:s");
            } else if ($call_type == 2) {
                $campagin_name = "cron_" . $cron_name . "_" . date("Y-m-d H:i:s");
            } else if ($call_type == 3) {
                $campagin_name = "cron_" . $cron_name . "_" . date("Y-m-d H:i:s");
            }

            $mobile_number_string = '"' . implode('","', $mobile_numbder_array) . '"';

            $apiRequestJson = '{
                                "campaigns": [
                                    {
                                        "caller_id": "' . $caller_id . '",
                                        "url": "http://my.exotel.in/exoml/start_voice/' . $campaign_id . '",
                                        "from": [' . $mobile_number_string . '],
                                        "name":"' . $campagin_name . '",
                                        "type":"trans",
                                        "retries": {
                                            "number_of_retries": 2,
                                            "interval_mins": 15,
                                            "mechanism": "Exponential",
                                            "on_status": ["busy", "no-answer", "failed"]
                                        },
                                        "schedule": {
                                            "end_at": "' . date("Y-m-d") . 'T20:00:00+05:30"
                                        }      
                                    }
                                ]
                            }';

            $apiHeaders = array(
                "Authorization: Basic " . $authrization,
                "Content-Type:application/json",
            );

            $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

            if ($debug == 1) {
                echo "<br/><br/> =======Header Plain======<br/><br/>" . json_encode($apiHeaders);
                echo "<br/><br/> =======Request Plain======<br/><br/>" . $apiRequestJson;
            }

            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $apiResponseJson = curl_exec($curl);

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

                    if (!empty($apiResponseData['response'])) {

                        if ($apiResponseData['response'][0]['status'] == 'success') {
                            $apiStatusId = 1;
                        } else {
                            $temp_error = !empty($apiResponseData['response'][0]['error_data']) ? $apiResponseData['response'][0]['error_data'] : "Some error occurred. Please try again.";
                            throw new ErrorException($temp_error);
                        }
                    } else {
                        $temp_error = "Some error occurred. Please try again..";
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
        $insertApiLog["call_campaign_name"] = $campagin_name;
        $insertApiLog["call_campaign_method_id"] = $call_type;
        $insertApiLog["call_campaign_status_id"] = $apiStatusId;
        $insertApiLog["call_campaign_request"] = $apiRequestJson;
        $insertApiLog["call_campaign_response"] = $apiResponseJson;
        $insertApiLog["call_campaign_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["call_campaign_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["call_campaign_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $this->CallModel->calllog_insert($insertApiLog);

        $returnResponseData = array();
        $returnResponseData['status'] = $apiStatusId;
        $returnResponseData['errors'] = !empty($errorMessage) ? $errorMessage : "";

        if ($debug == 1) {
            $returnResponseData['raw_request'] = $apiRequestJson;
            $returnResponseData['raw_response'] = $apiResponseJson;
        }

        return $returnResponseData;
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 0, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@tejasloan.com";

            if ($email_type_id == 10) {
                $from_email = "info@tejasloan.com";
                $provider_name = "MAILGUN";
            } else if ($email_type_id == 12) {
                $from_email = "info@tejasloan.com";
                $provider_name = "MAILGUN";
            }

//
            if (in_array($email_type_id, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 11))) {
                $provider_name = "MAILGUN";

                $template = "";
                if ($email_type_id == 1) {
                    $template = "birthdayemailer";
                } else if (in_array($email_type_id, array(2))) {
                    $template = "festiveofferforcloseloan";
                } else if (in_array($email_type_id, array(3, 4))) {
                    $template = "festiveoffernewcustomer";
                } else if (in_array($email_type_id, array(5))) {
                    $template = "loanwallefreshloanjan2022";
                } else if (in_array($email_type_id, array(6))) {
                    $template = "freshloanloharijan22reject";
                } else if (in_array($email_type_id, array(7))) {
                    $template = "republicday26012022";
                } else if (in_array($email_type_id, array(8))) {
                    $template = "repayloanemailer";
                } else if (in_array($email_type_id, array(9))) {
                    $template = "valentineweekspecial";
                } else if (in_array($email_type_id, array(11))) {
                    $template = "happyholi";
                }

                $apiUrl = "https://api.mailgun.net/v3/loanwalle.com/messages";

                $request_array = array(
                    "from" => $from_email,
                    "to" => $to_email,
                    "subject" => $subject,
                    "template" => $template
                );

                $apiHeaders = array(
                    "Authorization: Basic " . base64_encode("api:ada7804cae9740db5c62abd5b2ae5d62-8ed21946-b133e0ab"),
                    "Content-Type:multipart/form-data",
                );
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request_array);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                $response = curl_exec($curl);

                $return_array = json_decode($response, true);

                if ($return_array['message'] == "Queued. Thank you.") {
                    $status = 1;
                } else {
                    $status = 2;
                    $error = $return_array['message'];
                }
            } else {
                $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
                $status = $return_array['status'];
                $error = $return_array['error'];
            }

            $insert_log_array = array();
            $insert_log_array['email_provider'] = $provider_name;
            $insert_log_array['email_type_id'] = $email_type_id;
            $insert_log_array['email_address'] = $email;
            $insert_log_array['email_content'] = addslashes($message);
            $insert_log_array['email_api_status_id'] = $status;
            $insert_log_array['email_errors'] = $error;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            $this->EmailModel->emaillog_insert($insert_log_array);

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }

}
