<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('traceObjectSelf')) {

    function traceObjectSelf($object_passed, $die = false) {
        if (in_array($_SERVER["REMOTE_ADDR"], array("183.83.210.174"))) {
            traceObject($object_passed);
            if ($die) {
                die;
            }
        }
    }
}

if (!function_exists('traceObject')) {

    function traceObject(&$expression) {
        echo ("<pre>");
        print_r($expression);
        echo ("</pre>");
    }
}

if (!function_exists('trim_data_array')) {


    function trim_data_array($inputstring) {
        if (!is_array($inputstring)) {
            $inputstring = trim($inputstring);
            $inputstring = addslashes($inputstring);
            $inputstring = preg_replace("!\s+!", " ", $inputstring);
            $inputstring = str_replace("Ã¢â‚¬â€œ", " ", $inputstring);
            $inputstring = str_replace("ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“", " ", $inputstring);
            $inputstring = preg_replace("!\s+!", " ", $inputstring);
            return $inputstring;
        }
        return array_map('trim_data_array', $inputstring);
    }
}
if (!function_exists('common_parse_full_name')) {

    function common_parse_full_name($full_name = "") {
        $first_name = $middle_name = $last_name = "";

        if (!empty($full_name)) {
            $full_name = preg_replace("!\s+!", " ", $full_name);

            $name_array = explode(" ", $full_name);

            $first_name = $name_array[0];

            for ($i = 1; $i < (count($name_array) - 1); $i++) {
                $middle_name .= " " . $name_array[$i];
            }

            $middle_name = trim($middle_name);
            $last_name = (count($name_array) != 1 && isset($name_array[count($name_array) - 1])) ? $name_array[count($name_array) - 1] : "";
        }
        return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
    }
}

if (!function_exists('inscriptionNumber')) {
    function inscriptionNumber($ccNum, $leadStatus = 0) {
        if (in_array($_SESSION['isUserSession']['labels'], ["CO1", "CR2"]) || !in_array($leadStatus, array(14, 16, 17, 18, 19))) {
            return $ccNum;
        }
        return str_replace(range(0, 9), "*", substr($ccNum, 0, -4)) . substr($ccNum, -4);
    }
}

if (!function_exists('maskEmail')) {
    function maskEmail($email) {
        if (in_array($_SESSION['isUserSession']['labels'], ["CO1", "CR2"])) {
            return $email;
        }
        list($localPart, $domain) = explode('@', $email);
        $maskedLocalPart = substr($localPart, 0, 2) . str_repeat('*', max(strlen($localPart) - 2, 0)) . substr($localPart, -1);
        return $maskedLocalPart . '@' . $domain;
    }
}


if (!function_exists('ConvertXmlToJson')) {

    function ConvertXmlToJson($xmlString) {
        $return_val = true;
        $error_msg = "";
        $jsonString = "";
        try {

            $xmlString = str_replace(array("\n", "\r", "\t"), '', $xmlString);
            if (empty($xmlString)) {
                throw new Exception("XML not in correct format.#2");
            }

            $xmlString = simplexml_load_string($xmlString);

            if ($xmlString === false) {
                throw new Exception("XML not in correct format.#3");
            }

            $jsonString = json_encode($xmlString);

            if (json_last_error() > 0) {
                throw new Exception("XML not in correct format.#4 | " . json_last_error_msg());
            }
        } catch (Exception $ex) {
            $return_val = false;
            $error_msg = $ex->getMessage();
        }
        return array($return_val, $error_msg, $jsonString);
    }
}
if (!function_exists('ConvertXmlToArray')) {

    function ConvertXmlToArray($xmlString) {
        $return_val = true;
        $error_msg = "";
        $jsonArray = array();

        try {

            $jsonString = ConvertXmlToJson($xmlString);

            if ($jsonString[0] == false) {
                $return_val = false;
                $error_msg = $jsonString[1];
            } else {
                $jsonArray = json_decode($jsonString[2], true);

                if (json_last_error() > 0) {
                    throw new Exception("XML not in correct format.#5 | " . json_last_error_msg());
                }
            }
        } catch (Exception $ex) {
            $return_val = false;
            $error_msg = $ex->getMessage();
        }

        return array($return_val, $error_msg, $jsonArray);
    }
}

if (!function_exists('is_mobile')) {

    function is_mobile($mob, $country_code = 0) {
        if ($country_code == 971) {
            return preg_match("/^[0,5,{0,5}]+[0-9]{7}$/", $mob);
        } else if ($country_code == 0) {
            return preg_match("/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1}){0,1}\d{10,12}$/", $mob);
        } else {
            return preg_match("/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1}){0,1}\d{10,12}$/", $mob);
        }
    }
}

if (!function_exists('display_data')) {

    function display_data($data, $type = 0) {

        $display_data = "-";

        if (!empty($data)) {
            $display_data = $data;
        }

        return $display_data;
    }
}

if (!function_exists('display_date_format')) {

    function display_date_format($data, $type = 0) {

        $display_data = "-";

        if (!empty($data) && !strpos($data, '0000-00-00')) {

            $display_data = date("d-m-Y H:i", strtotime($data));

            if ($type == 1) {
                $display_data = date("d-m-Y H:i:s", strtotime($data));
            }
            if ($type == 2) {
                $display_data = date("d-m-Y", strtotime($data));
            }
        }

        return $display_data;
    }
}
if (!function_exists('getIpAddress')) {

    function getIpAddress() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }
}

if (!function_exists('strongPassword')) {


    function strongPassword($pwd, $username) {
        $return_array = array(true, "");
        $return_val = true;
        $error = "";
        if (strlen($pwd) < 8) {
            $error .= "New password too short (min 8 chars). | ";
            $return_val = false;
        } else if (strlen($pwd) > 25) {
            $error .= "New password too long (max 25 chars). | ";
            $return_val = false;
        }

        if (!preg_match("#[0-9]+#", $pwd) && !preg_match("#\W+#", $pwd)) {
            $error .= "New password must include at least one number or one symbol. | ";
            $return_val = false;
        }

        if (!preg_match("#[a-z]+#", $pwd) && !preg_match("#[A-Z]+#", $pwd)) {
            $error .= "New password must include at least one letter. | ";
            $return_val = false;
        }

        $alphabets_lower = "abcdefghijklmnopqrstuvwxyz";
        $alphabets_upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "123456789";
        for ($i = 0; $i < strlen($alphabets_lower) - 4; $i++) {
            $tmp_sub_str = substr($alphabets_lower, $i, 4);
            if (strpos($pwd, $tmp_sub_str) >= 0 && strpos($pwd, $tmp_sub_str) !== false) {
                $error .= "Do not include 4 or more small identical characters like abcd. | ";
                $return_val = false;
            }
        }

        for ($i = 0; $i < strlen($alphabets_upper) - 4; $i++) {
            $tmp_sub_str = substr($alphabets_upper, $i, 4);
            if (strpos($pwd, $tmp_sub_str) >= 0 && strpos($pwd, $tmp_sub_str) !== false) {
                $error .= "Do not include 4 or more capital identical characters like ABCD. | ";
                $return_val = false;
            }
        }

        for ($i = 0; $i < strlen($numbers) - 4; $i++) {
            $tmp_sub_str = substr($numbers, $i, 4);
            if (strpos($pwd, $tmp_sub_str) >= 0 && strpos($pwd, $tmp_sub_str) !== false) {
                $error .= "Do not include 4 or more identical characters like 1234. | ";
                $return_val = false;
            }
        }

        if (preg_match('/(.)\\1{2}/', $pwd)) {
            $error .= "Do not include 3 same characters consecutively like xxx or 111. | ";
            $return_val = false;
        }

        if (strpos($pwd, $username) >= 0 && strpos($pwd, $username) !== false) {
            $error .= "Do not include username in password. | ";
            $return_val = false;
        }

        if (strpos($pwd, " ") >= 0 && strpos($pwd, " ") !== false) {
            $error .= "Do not include spaces in password. | ";
            $return_val = false;
        }

        $return_array[0] = $return_val;
        $return_array[1] = rtrim($error, " | ");

        return $return_array;
    }
}

function unformatMoney($str) {
    return preg_replace("/[,\s]/", "", preg_replace("/^[0]{1,20}/", "", $str));
}

if (!function_exists('lw_send_email')) {

    function lw_send_email($to_email, $subject, $message, $bcc_email = "", $cc_email = "", $from_email = "", $reply_to = "info@paisaonsalary.in", $attchement_path = "", $attachement_name = "", $file_name = "") {
        $status = 0;
        $error = "";
        $active_id = 5;

        if (empty($to_email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            if (empty($from_email)) {
                $from_email = "info@paisaonsalary.in";
            }

            $ci = &get_instance();
            if ($active_id == 1) {

                $config = array();
                $config['protocol'] = "smtp";
                $config['smtp_host'] = "smtp.zeptomail.in";
                $config['smtp_user'] = "emailapikey";
                $config['smtp_pass'] = "PHtE6r0FRri63WIm9hgE5vPtQ5H3PYop+b4yegIWtYsWAvMCH01dr94qlTHjr08jAfUWEvLIz95tsOue5uyGLGa+Mm1KWGqyqK3sx/VYSPOZsbq6x00fuFkddUzaVoHmdd9p0yzRudjbNA==";
                $config['smtp_port'] = 587;
                $config['smtp_crypto'] = 'tls';
                $config['mailtype'] = "html";
                $config['charset'] = "UTF-8";
                $config['priority'] = 1;
                $config['newline'] = "\r\n";
                $config['wordwrap'] = TRUE;

                $ci->load->library('email', $config);

                $ci->email->initialize($config);

                $ci->email->set_newline("\r\n");

                $ci->email->from($from_email);

                if (!empty($bcc_email)) {
                    $ci->email->bcc($bcc_email);
                }
                if (!empty($cc_email)) {
                    $ci->email->cc($cc_email);
                }

                $ci->email->to($to_email);

                $ci->email->subject($subject);

                $ci->email->message($message);

                if ($ci->email->send()) {
                    $status = 1;
                } else {
                    // $error = "Some error occurred";
                    $error = $ci->email->print_debugger(['headers']);
                }
            } else if ($active_id == 2) {

                if (empty($from_email)) {
                    $from_email = "info@paisaonsalary.in";
                }

                $apiUrl = "https://api.zeptomail.in/v1.1/email";

                $emailData = array(
                    "from" => [
                        "address" => $from_email,
                        "name" => "Paisa On Salary"
                    ],
                    "to" => [
                        ["email_address" => ["address" => $to_email]]
                    ],
                    "subject" => $subject,
                    "htmlbody" => $message
                );

                // Optional CC
                if (!empty($cc_email)) {
                    $emailData["cc"] = [
                        ["email_address" => ["address" => $cc_email]]
                    ];
                }

                // Optional BCC
                if (!empty($bcc_email)) {
                    $emailData["bcc"] = [
                        ["email_address" => ["address" => $bcc_email]]
                    ];
                }

                // Optional Reply-To
                if (!empty($reply_to)) {
                    $emailData["reply_to"] = [
                        "address" => $reply_to
                    ];
                }

                if (!empty($attchement_path) && !empty($attachement_name)) {
                    $file_path = $attchement_path . $attachement_name;
                
                    $attachment_content = base64_encode(file_get_contents($file_path));
                
                    $emailData["attachments"] = [
                        [
                            "name" => "sanction_letter.pdf",
                            "content" => $attachment_content,
                            "content_type" => "application/pdf"
                        ]
                    ];
                }

                $apiHeaders = array(
                    "Authorization:Zoho-enczapikey PHtE6r0FRri63WIm9hgE5vPtQ5H3PYop+b4yegIWtYsWAvMCH01dr94qlTHjr08jAfUWEvLIz95tsOue5uyGLGa+Mm1KWGqyqK3sx/VYSPOZsbq6x00fuFkddUzaVoHmdd9p0yzRudjbNA==",
                    "Content-Type: application/json",
                    "Accept: application/json",
                );

                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($emailData));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 2 = strict host verification

                $response = curl_exec($curl);
                $return_array = json_decode($response, true);

                if ($return_array['message'] == "OK") {
                    $status = 1;
                } else {
                    $error = $return_array['message'];
                }
            } else if ($active_id == 4) {

                $config = array();
                $config['protocol'] = "smtp";
                $config['smtp_host'] = "smtp.zeptomail.in";
                $config['smtp_user'] = "emailapikey";
                $config['smtp_pass'] = "PHtE6r0FRri63WIm9hgE5vPtQ5H3PYop+b4yegIWtYsWAvMCH01dr94qlTHjr08jAfUWEvLIz95tsOue5uyGLGa+Mm1KWGqyqK3sx/VYSPOZsbq6x00fuFkddUzaVoHmdd9p0yzRudjbNA==";
                $config['smtp_port'] = 587;
                $config['smtp_crypto'] = 'tls';
                $config['mailtype'] = "html";
                $config['charset'] = "UTF-8";
                $config['priority'] = 1;
                $config['newline'] = "\r\n";
                $config['wordwrap'] = TRUE;

                $ci->load->library('email', $config);

                $ci->email->initialize($config);

                $ci->email->set_newline("\r\n");

                $ci->email->from($from_email);

                if (!empty($bcc_email)) {
                    $ci->email->bcc($bcc_email);
                }
                if (!empty($cc_email)) {
                    $ci->email->cc($cc_email);
                }

                $ci->email->to($to_email);

                $ci->email->subject($subject);

                $ci->email->message($message);

                if ($ci->email->send()) {
                    $status = 1;
                } else {
                    $error = "Some error occurred";
                }
            } else if ($active_id == 5) {

                $apiUrl = "https://api.zeptomail.in/v1.1/email";

                $emailData = array(
                    "from" => [
                        "address" => $from_email,
                        "name" => "Salarywalle"
                    ],
                    "to" => [
                        ["email_address" => ["address" => $to_email]]
                    ],
                    "subject" => $subject,
                    "htmlbody" => $message
                );

                // Optional CC
                if (!empty($cc_email)) {
                    $cc_email = explode(",", $cc_email);
                    $emailData["cc"] = array_map(function ($email) {
                        return ["email_address" => ["address" => $email]];
                    }, $cc_email);
                }

                // Optional BCC
                if (!empty($bcc_email)) {
                    $bcc_email = explode(",", $bcc_email);
                    $emailData["cc"] = array_map(function ($email) {
                        return ["email_address" => ["address" => $email]];
                    }, $bcc_email);
                }

                // Optional Reply-To
                if (!empty($reply_to)) {
                    $emailData["reply_to"] = [
                        "address" => $reply_to
                    ];
                }

                if (!empty($attchement_path) && !empty($attachement_name)) {
                    $file_path = $attchement_path . $attachement_name;
                
                    $attachment_content = base64_encode(file_get_contents($file_path));
                
                    $emailData["attachments"] = [
                        [
                            "name" => "sanction_letter.pdf",
                            "content" => $attachment_content,
                            "content_type" => "application/pdf"
                        ]
                    ];
                }

                $apiHeaders = array(
                    "Authorization:Zoho-enczapikey PHtE6r0FRri63WIm9hgE5vPtQ5H3PYop+b4yegIWtYsWAvMCH01dr94qlTHjr08jAfUWEvLIz95tsOue5uyGLGa+Mm1KWGqyqK3sx/VYSPOZsbq6x00fuFkddUzaVoHmdd9p0yzRudjbNA==",
                    "Content-Type: application/json",
                    "Accept: application/json",
                );
                
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($emailData));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 2 = strict host verification

                $response = curl_exec($curl);
                $return_array = json_decode($response, true);

                if ($return_array['message'] == "OK") {
                    $status = 1;
                } else {
                    $error = $return_array['message'];
                }
            }
        }

        $return_array = array("status" => $status, "error" => $error);

        return $return_array;
    }

    if (!function_exists('lw_send_sms')) {

        //            function lw_send_sms($lead_id, $mobile, $sms_content, $m_sms_primary_id, $sms_template_id, $sms_source, $log_insert_flag = true) {
        function lw_send_sms($lead_id, $mobile, $sms_content, $m_sms_primary_id = 0, $log_insert_flag = true) {
            $return_array = array("status" => 0, "error" => "");
            $active_id = 1;
            $sms_data = array();

            $ci = &get_instance();
            $ci->load->database();

            if (!empty($m_sms_primary_id)) {
                $sql = "SELECT m_st_id as m_sms_primary_id, m_st_template_id, m_st_template_source, m_st_description, m_st_content";
                $sql .= " From master_sms_template";
                $sql .= " where m_st_id='$m_sms_primary_id' ";

                $query = $ci->db->query($sql);

                if (!empty($query->num_rows())) {
                    $row = $query->row_array();
                    $sms_data['sms_primary_id'] = $row['m_sms_primary_id'];
                    $sms_data['sms_temp_id'] = $row['m_st_template_id'];
                    $sms_data['sms_source'] = $row['m_st_template_source'];
                }
            }

            if (empty($mobile)) {
                $return_array['error'] = "Please check customer mobile when sent sms.";
            } else if (empty($sms_content)) {
                $return_array['error'] = "Please check sms content when sent sms.";
            } else if (empty($sms_data['sms_temp_id'])) {
                $return_array['error'] = "Please check sms template Id when sent sms.";
            } else if (empty($sms_data['sms_source'])) {
                $return_array['error'] = "Please check sms source when sent sms.";
            } else {
                if ($active_id == 1) {

                    $username = urlencode("namanfinl");
                    $password = urlencode("ASX1@#SD");
                    $type = 0;
                    $dlr = 1;
                    $destination = $mobile;
                    $source = $sms_data['sms_source'];
                    $message = urlencode($sms_content);
                    $entityid = 1201159134511282286;
                    $tempid = $sms_data['sms_temp_id'];

                    $data = "username=$username&password=$password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$entityid&tempid=$tempid";
                    $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";

                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $data
                    ));

                    $output = curl_exec($ch);

                    if ($output) {
                        unset($return_array['error']);
                        $return_array['status'] = 1;
                    } else {
                        $return_array['error'] = "Some error occurred";
                    }
                    curl_close($ch);

                    if ($log_insert_flag) {
                        $sms_data_log['lsl_lead_id'] = $lead_id;
                        $sms_data_log['lsl_sms_type_id'] = $sms_data['sms_primary_id'];
                        $sms_data_log['lsl_sms_mobile'] = $mobile;
                        $sms_data_log['lsl_sms_content'] = $sms_content;
                        $sms_data_log['lsl_api_status_id'] = $return_array['status'];
                        $sms_data_log['lsl_errors'] = (($return_array['error']) ? $return_array['error'] : "");
                        $sms_data_log['lsl_user_id'] = $_SESSION['isUserSession']['user_id'];
                        $sms_data_log['lsl_created_on'] = date('Y-m-d H:i:s');
                        $sms_data_log['lsl_active'] = 1;
                        $sms_data_log['lsl_deleted'] = 0;

                        $ci->db->insert('lead_sms_logs', $sms_data_log);
                    }
                }
            }
            return $return_array;
        }
    }
}

if (!function_exists('insertBharatDetails')) {

    function insertBharatDetails() {
        $ci = &get_instance();
        $db2 = $ci->load->database('milti_DB', TRUE);
        $query = $db2->query('select * from test;')->result_array();
        return $query;
    }
}

if (!function_exists('uploadDocument')) {

    function uploadDocument($file_obj, $lead_id = 0, $flag = 0, $ext = '') {
        $ci = &get_instance();

        $ci->load->library(array('S3_upload'));
        if ($flag == 1) {
            $extension = trim(strtolower($ext));
        } else if ($flag == 2) {
            $extension = trim(strtolower($ext));
        } else {
            $file_name = $file_obj["file_name"]['name'];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $extension = trim(strtolower($extension));
        }

        $new_name = $lead_id . '_lms_' . date('YmdHis') . rand(111, 999) . '.' . $extension;

        if ($flag == 1) {
            $upload = $ci->s3_upload->upload_file($file_obj, $new_name, $flag);
        } else if ($flag == 2) {
            $upload = $ci->s3_upload->upload_file($file_obj, $new_name);
        } else {
            $upload = $ci->s3_upload->upload_file($file_obj["file_name"]["tmp_name"], $new_name);
        }

        $return_status = 0;

        if ($upload) {
            $return_status = 1;
        }

        $return_array = ["status" => $return_status, "file_name" => $new_name];
        return $return_array;
    }
}


if (!function_exists('downloadDocument')) {

    function downloadDocument($file_name, $flag = 0) {
        $ci = &get_instance();

        $ci->load->library(array('S3_upload'));

        $upload = $ci->s3_upload->get_file($file_name, $flag);
        return $upload;
    }
}
