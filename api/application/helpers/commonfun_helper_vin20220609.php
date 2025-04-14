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
        echo("<pre>");
        print_r($expression);
        echo("</pre>");
    }

}

if (!function_exists('trim_data_array')) {

    function trim_data_array($inputstring) {
        if (!is_array($inputstring)) {
            $inputstring = trim($inputstring);
            $inputstring = addslashes($inputstring);
            $inputstring = ($inputstring == 'null') ? $inputstring : "";
            $inputstring = ($inputstring == 'NULL') ? $inputstring : "";
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
            $first_name = substr($full_name, 0, (strpos($full_name, " ") !== false) ? strpos($full_name, " ") : strlen($full_name));
            $full_name = trim(str_replace($first_name, "", $full_name));
            $last_name = !empty($full_name) ? substr($full_name, (strrpos($full_name, " ", -1) !== false) ? strrpos($full_name, " ", -1) : 0, strlen($full_name)) : "";
            $last_name = trim($last_name);
            $full_name = trim(str_replace($last_name, "", $full_name));
            $middle_name = trim($full_name);
        }
        return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
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

if (!function_exists('lw_send_email')) {


    function lw_send_email($to_email, $subject, $message, $bcc_email = "", $from_email = "") {
        $status = 0;
        $error = "";
        $active_id = 2;

        if (empty($to_email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            if (empty($from_email)) {
                $from_email = "info@loanwalle.com";
            }

            $ci = & get_instance();
            if ($active_id == 1) {

                $config = array();
                $config['protocol'] = "smtp";
                $config['smtp_host'] = "smtp.mailgun.org";
                $config['smtp_user'] = "info@loanwalle.com";
                $config['smtp_pass'] = "825f8086472a5b63ee5f4a1f5b82959a-8ed21946-4e331879";
                $config['smtp_port'] = 587;
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

                $ci->email->to($to_email);

                $ci->email->subject($subject);

                $ci->email->message($message);

                if ($ci->email->send()) {
                    $status = 1;
                } else {
                    $error = "Some error occurred";
                }
            } else if ($active_id == 2) {

                if (empty($from_email)) {
                    $from_email = "info@loanwalle.com";
                }

                $apiUrl = "https://api.mailgun.net/v3/loanwalle.com/messages";

                $request_array = array(
                    "from" => $from_email,
                    "to" => $to_email,
                    "subject" => $subject,
                    "html" => $message
                );

                if (!empty($bcc_email)) {
                    $request_array["bcc"] = $bcc_email;
                }

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
                    $error = $return_array['message'];
                }
            } else {

                if (empty($from_email)) {
                    $from_email = "info@loanwalle.com";
                }

                $domain = "fintechcloud.in";

                $ip = gethostbyname($domain);

                $request = [
                    'serverIP' => $ip,
                    'to' => $to_email,
                    'from' => $from_email,
                    'subject' => $subject,
                    'message' => $message
                ];

                if (!empty($cc_email)) {
                    $request['cc'] = $cc_email;
                }
                if (!empty($bcc_email)) {
                    $request['bcc'] = $bcc_email;
                }

                $url = 'https://www.loanwalle.com/DisbursalController/restSentMail/';

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $request
                ));

                $response = curl_exec($curl);
                if ($response == "sent") {
                    $status = 1;
                } else {
                    $error = "Some error occurred";
                }
            }
        }

        $return_array = array("status" => $status, "error" => $error);

        return $return_array;
    }

}
