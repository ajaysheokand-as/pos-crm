<?php

function common_log_writer($type, $data) {

    //    if (!empty($data) && (false || $type == 1)) {
    if (!empty($data) && false) {


        $mode = "a+";
        $file_name = 'common_log_' . date("YmdH") . ".log";

        if ($type == 1) {
            $file_name = 'eligibility_' . date("YmdH") . ".log";
        } else if ($type == 2) {
            $file_name = 'crif_' . date("YmdH") . ".log";
        } else if ($type == 3) {
            $file_name = 'signzy_' . date("YmdH") . ".log";
        } else if ($type == 4) {
            $file_name = 'esign_signzy_' . date("YmdH") . ".log";
        } else if ($type == 5) {
            $file_name = 'digilocker_signzy_' . date("YmdH") . ".log";
        } else if ($type == 6) {
            $file_name = 'bre_result_' . date("YmdH") . ".log";
        } else if ($type == 7) {
            $file_name = 'bank_analysis_' . date("YmdH") . ".log";
        }

        $error_log_file = fopen(COMP_PATH . "/logs/$file_name", $mode);

        fwrite($error_log_file, PHP_EOL . "---" . date("Y-m-d H:i:s") . "---" . $data . PHP_EOL);
        fclose($error_log_file);
    }
}

function common_extract_value_from_xml($str1, $str2, $xml) {
    $stringExist = strpos($str1, $xml);
    if ($stringExist == false && $stringExist != 0) {
        $raw_data_string[0] = '';
    } else {
        $raw_data_string = explode($str1, trim($xml));
        $raw_data_string = explode($str2, trim($raw_data_string[1]));
    }
    return $raw_data_string[0];
}

function common_trim_data_array($inputstring) {

    if (!is_array($inputstring)) {
        $inputstring = trim($inputstring);
        $inputstring = addslashes($inputstring);
        $inputstring = preg_replace("!\s+!", " ", $inputstring);
        $inputstring = str_replace("Ã¢â‚¬â€œ", " ", $inputstring);
        $inputstring = str_replace("ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“", " ", $inputstring);
        $inputstring = preg_replace("!\s+!", " ", $inputstring);
        return $inputstring;
    }

    return array_map('common_trim_data_array', $inputstring);
}

//function common_parse_name($full_name = "") {
//    $first_name = $middle_name = $last_name = "";
//    if (!empty($full_name)) {
//        $first_name = substr($full_name, 0, (strpos($full_name, " ") !== false) ? strpos($full_name, " ") : strlen($full_name));
//        $full_name = trim(str_replace($first_name, "", $full_name));
//        $last_name = !empty($full_name) ? substr($full_name, (strrpos($full_name, " ", -1) !== false) ? strrpos($full_name, " ", -1) : 0, strlen($full_name)) : "";
//        $last_name = trim($last_name);
//        $full_name = trim(str_replace($last_name, "", $full_name));
//        $middle_name = trim($full_name);
//    }
//    return array("first_name" => $first_name, "middle_name" => $middle_name, "last_name" => $last_name);
//}

function common_parse_name($full_name = "") {
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

function common_send_email($to_email, $subject, $message, $bcc_email = "", $cc_email = "", $from_email = "", $reply_to = "", $attchement_path = "", $fileName = "", $file_move = "", $isTempUploadPath=true) {
    $status = 0;
    $error = "";
    $active_id = 6;
    // if (date("d") > 10 && date("d") <= 21) {
    //     $active_id = 5;
    // }

    if (empty($to_email) || empty($subject) || empty($message)) {
        $error = "Please check email id, subject and message when sent email";
    } else {

        // if (empty($from_email)) {
        //     $from_email = "info@tejasloan.com";
        // }
        if (empty($from_email)) {
            $from_email = INFO_EMAIL ?? "info@paisaonsalary.in";
        }



        $ci = &get_instance();
        if ($active_id == 0) {

            $config = array();
            $config['protocol'] = "smtp";
            $config['smtp_host'] = "smtp.zeptomail.in";
            $config['smtp_user'] = "emailapikey";
            $config['smtp_pass'] = getenv('ZEPTO_MAIL_PASS');
            $config['smtp_port'] = 587;
            $config['smtp_crypto'] = 'tls';
            $config['mailtype'] = "html";
            $config['charset'] = "UTF-8";
            $config['priority'] = 1;
            $config['newline'] = "\r\n";
            $config['wordwrap'] = TRUE;

            $ci->load->library('email', $config);

            $ci->email->initialize($config);
            $config['mailtype'] = "html";
            $config['charset'] = "UTF-8";
            $config['priority'] = 1;

            $ci->email->set_newline("\r\n");

            $ci->email->from($from_email);

            if (!empty($bcc_email)) {
                $ci->email->bcc($bcc_email);
            }


            // $list = array('info@tejasloan.com', 'info@tejasloan.com');
            $list = array(INFO_EMAIL ?? 'info@paisaonsalary.in', TECH_EMAIL ?? 'tech@paisaonsalary.in');

            $ci->email->cc($list);
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
                $from_email = "info@paisaonsalary.in";
            }

            $apiUrl = "https://api.zeptomail.in/v1.1/email";

            $emailData = array(
                "from" => [
                    "address" => $from_email,
                    "name" => "Paias On Salary"
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

            $apiHeaders = array(
                "Authorization:Zoho-enczapikey ".getenv('ZEPTO_MAIL_PASS'),
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
        } else if (in_array($active_id, [3, 5, 6])) {

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
                "Authorization:Zoho-enczapikey ".getenv('ZEPTO_MAIL_PASS'),
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
            $config['smtp_pass'] = getenv("ZEPTO_MAIL_PASS");
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
        }else if ($active_id == 7){
             $apiKey = getenv('MAIL_GUN_API_KEY');
                $domain = getenv('MAIL_GUN_DOMAIN');
                $url = "https://api.mailgun.net/v3/{$domain}/messages";

                $fields = [
                    'from'    => $from_email,
                    'to'      => $to_email,
                    'subject' => $subject,
                ];
            
                // Detect message type: HTML or plain text
                if (stripos($message, 'DOCTYPE html') !== false || stripos($message, '<html') !== false) {
                    $fields['html'] = $message;
                } else {
                    $fields['text'] = $message;
                }
            
                // Add CC if provided
                if (!empty($cc_email)) {
                    $fields['cc'] = $cc_email;
                }

            
                // Handle file attachment
                if (!empty($attachment_path)) {
                    if($isTempUploadPath === true){
                        $full_path = realpath(TEMP_UPLOAD_PATH . $attachment_path);
                    }else{
                        $full_path = realpath($attachment_path);
                    }
                    if ($full_path && file_exists($full_path)) {
                        $mime_type = mime_content_type($full_path) ?: 'application/octet-stream';
                        $fields['attachment'] = new CURLFile($full_path, $mime_type, $fileName ?? basename($full_path));
                    } else {
                        return ['success' => false, 'error' => 'Attachment file not found.'];
                    }
                }
            
                // Initialize and send cURL request
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_USERPWD        => "api:{$apiKey}",
                    CURLOPT_POSTFIELDS     => $fields,
                    CURLOPT_SAFE_UPLOAD    => true,
                ]);
            
                $response = curl_exec($curl);
            
                // Handle cURL errors
                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                    curl_close($curl);
                    return ['success' => false, 'error' => $error_msg];
                }
            
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
            
                $result = json_decode($response, true);
            
                if ($statusCode == 200 && isset($result['message']) && stripos($result['message'], "Queued") !== false) {
                    return ['success' => true, "status"=>1];
                } else {
                    $error = $result['message'] ?? 'Unknown error sending email.';
                    return ['success' => false, 'error' => $error];
                }
        }

            //  else if ($active_id == 6) {
        //     $curl = curl_init();
        //     if (strpos($message, 'DOCTYPE html') !== false) {
        //         $from_email = empty($from_email) ? "info@tejasloan.com" : $from_email;
        //         if (!empty($attchement_path)) {
        //             $apiRequestArray = array(
        //                 'from' => 'Tejas Loan '.$from_email,
        //                 'to' => $to_email,
        //                 'html' => $message,
        //                 'subject' => $subject,
        //                 'attachment' => new CURLFile($attchement_path),
        //                 'cc' => $cc_email
        //             );
        //         } else {
        //             $apiRequestArray = array(
        //                 'from' => 'Tejas Loan '.$from_email,
        //                 'to' => $to_email,
        //                 'html' => $message,
        //                 'subject' => $subject,
        //                 'cc' => $cc_email
        //             );
        //         }
        //     } else {
        //         if (!empty($attchement_path)) {
        //             $apiRequestArray = array(
        //                 'from' => 'Tejas Loan '.$from_email,
        //                 'to' => $to_email,
        //                 'text' => $message,
        //                 'subject' => $subject,
        //                 'attachment' => new CURLFile($attchement_path),
        //                 'cc' => $cc_email
        //             );
        //         } else {
        //             $apiRequestArray = array(
        //                 'from' => 'Tejas Loan '.$from_email,
        //                 'to' => $to_email,
        //                 'text' => $message,
        //                 'subject' => $subject,
        //                 'cc' => $cc_email
        //             );
        //         }
        //     }
        //     $url = "";

        //     // if (strpos(strtolower($to_email), 'tejasloan.com') !== false) {
        //     if (strpos(strtolower($to_email), 'info@tejasloan.com') !== false) {
        //         // $url = 'https://api.mailgun.net/v3/tejasloan.com/messages';
        //         $url = 'https://api.zeptomail.in/v1.1/email';
        //     // } else if (strpos(strtolower($cc_email), 'tejasloan.com') !== false) {
        //     } else if (strpos(strtolower($cc_email), 'tech@telasloan.com') !== false) {

        //         // $url = 'https://api.mailgun.net/v3/tejasloan.com/messages';
        //         $url = 'https://api.zeptomail.in/v1.1/email';
        //     } else {
        //         $url = 'https://api.zeptomail.in/v1.1/email';
        //     }
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => $url,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //         CURLOPT_POSTFIELDS => $apiRequestArray,
        //         CURLOPT_HTTPHEADER => array(
        //             "Authorization: Basic " . base64_encode("api:69cd62f6fad5577cd547b4b8c5760052-24bda9c7-ed7ced9c"),
        //             'Content-Type = multipart/form-data'
        //         ),
        //     ));

        //     $response = curl_exec($curl);

        //     curl_close($curl);
        //     // echo $response;

        //     if (!empty($response)) {
        //         $status = 1;
        //         $return_array = array("status" => $status, "error" => $error);
        //     } else {
        //         $return_array = json_decode($response, true);
        //         $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
        //     }
        // } else if ($active_id == 5) {

        //     // $apiUrl = "https://api.sendgrid.com/v3/mail/send";
        //     $apiUrl = "https://api.mailgun.net/v3/tejasloan.com/messages";

        //     $apiHeaders = array(
        //         "Authorization: Basic " . base64_encode("api:69cd62f6fad5577cd547b4b8c5760052-24bda9c7-ed7ced9c"),
        //         "Accept: application/json",
        //         "Content-Type: application/json",
        //     );

        //     $apiRequestArray = [];

        //     $send_email_array = [];

        //     // Add recipient email
        //     $send_email_array["to"] = [["email" => $to_email]];

        //     // Add CC emails
        //     if (!empty($cc_email)) {
        //         $cc_email = explode(",", $cc_email);

        //         $sent_cc_email = [];
        //         foreach ($cc_email as $email_data) {
        //             if (trim(strtolower($to_email)) == trim(strtolower($email_data))) {
        //                 continue;
        //             }
        //             $sent_cc_email[] = ["email" => trim($email_data)];
        //         }

        //         if (!empty($sent_cc_email)) {
        //             $send_email_array["cc"] = $sent_cc_email;
        //         }
        //     }

        //     // Add BCC emails
        //     if (!empty($bcc_email)) {
        //         $bcc_email = explode(",", $bcc_email);

        //         $sent_bcc_email = [];
        //         foreach ($bcc_email as $email_data) {
        //             if (trim(strtolower($to_email)) == trim(strtolower($email_data))) {
        //                 continue;
        //             }
        //             $sent_bcc_email[] = ["email" => trim($email_data)];
        //         }

        //         if (!empty($sent_bcc_email)) {
        //             $send_email_array["bcc"] = $sent_bcc_email;
        //         }
        //     }

        //     $apiRequestArray["personalizations"] = [$send_email_array];

        //     // Add sender details
        //     $apiRequestArray["from"] = ["email" => $from_email, "name" => "SalaryOnTime"];

        //     // Add reply-to email
        //     if (!empty($reply_to)) {
        //         $apiRequestArray["reply_to"] = ["email" => $reply_to];
        //     }

        //     // Add email subject
        //     $apiRequestArray["subject"] = $subject;

        //     // Add email content
        //     $apiRequestArray["content"] = [[
        //         "type" => "text/html",
        //         "value" => "$message"
        //     ]];

        //     // Add attachment if provided
        //     if (!empty($attchement_path)) {

        //         // Add attachment if provided
        //         $attachment_file = @file_get_contents($attchement_path);

        //         if ($attachment_file !== false) {
        //             $attachment_content = base64_encode($attachment_file);

        //             // Use a default MIME type
        //             $attachment_type = "application/octet-stream";

        //             // Extract filename from the S3 URL
        //             $attachment_filename = basename(parse_url($attchement_path, PHP_URL_PATH));

        //             $apiRequestArray['attachments'] = [
        //                 [
        //                     "content" => $attachment_content,
        //                     "type" => $attachment_type,
        //                     "filename" => empty($fileName) ? $attachment_filename : $fileName,
        //                     "disposition" => "attachment"
        //                 ]
        //             ];
        //         } else {
        //             echo "Failed to download the file from the S3 URL.";
        //             exit;
        //         }
        //     }

        //     // Encode the API request payload
        //     $apiResponseJson = json_encode($apiRequestArray);
        //     $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        //     // Initialize cURL
        //     $curl = curl_init($apiUrl);
        //     curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        //     curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        //     curl_setopt($curl, CURLOPT_POST, true);
        //     curl_setopt($curl, CURLOPT_POSTFIELDS, $apiResponseJson);
        //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        //     curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        //     // Execute the cURL request and handle the response
        //     $response = curl_exec($curl);

        //     if (empty($response)) {
        //         $status = 1;
        //         $return_array = array("status" => $status, "error" => $error);
        //         return $return_array;
        //     } else {
        //         $return_array = json_decode($response, true);
        //         $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
        //     }
        //     curl_close($curl);
        // }
    }

    $return_array = array("status" => $status, "error" => $error);

    return $return_array;
}

function common_lead_thank_you_email($lead_id, $email, $name, $reference_no) {

    $return_array = array();

    if (empty($lead_id) || empty($email) || empty($name) || empty($reference_no)) {
        $return_array['Status'] = 0;
        $return_array['Message'] = 'Lead id required.';
        return $return_array;
    } else {

        $subject = 'Thank You. - Paisa On Salary';

        $html = '<!DOCTYPE html>
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>Thank You | Paisa On Salary</title>
                </head>

                <body>
                    <table width="400" border="0" align="center"
                        style="font-family:Arial, Helvetica, sans-serif; border:solid 1px #ddd; padding:10px; background:#f9f9f9;">
                        <tr>
                            <td width="775" align="center" style="background: #00455E;"><img
                                    src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/final_logo.png" width="40%"></td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <table width="418" border="0" style="text-align:center; padding:20px; background:#fff;">
                                    <tr>
                                        <td style="font-size:16px;"><img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/emailimages/thank-you.gif"
                                                width="auto" height="250" alt="thank-you"></td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:16px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td width="412" style="font-size:16px;">
                                            <h2 style="margin:0px; color: #278B65;">Thank You</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="412" style="font-size:16px;">
                                            <h2 style="margin:0px; color: #278B65;">Dear ' . $name . '</h2>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <p style="line-height:25px; margin:0px;">Thank you for showing interest in Paisa On Salary.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p style="line-height:25px; margin:0px;">We have received your loan application <strong
                                                    style="color: #278B65;">' . $reference_no . '</strong> successfully. Please note the
                                                same for future communication.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center">Follow Us On</td>
                        </tr>
                        <td align="center">
                            <!-- Facebook Icon -->
                            <a href="#" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/facebook.png" class="socil-t"
                                    alt="pos-facebook" style="width:30px;">
                            </a>
                            <!-- LinkedIn Icon -->
                            <a href="#" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/linkedin.png" class="socil-t"
                                    alt="pos-linkedin" style="width:30px;">
                            </a>
                            <!-- Instagram Icon -->
                            <a href="#" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/instagram.png" class="socil-t"
                                    alt="pos-instagram" style="width:30px;">
                            </a>
                            <!-- YouTube Icon -->
                            <a href="#" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/youtube.png" class="socil-t"
                                    alt="pos-youtube" style="width:30px;">
                            </a>
                            <!-- Twitter Icon -->
                            <a href="#" target="_blank"
                                style="margin: 0 10px; text-decoration: none;">
                                <img src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/twitter.png" class="socil-t"
                                    alt="pos-twitter" style="width:30px;">
                            </a>
                        </td>
                        </tr>
                        <tr>
                            <td align="center">For Latest Updates &amp; Offers</td>
                        </tr>
                    </table>
                </body>

                </html>';

        $email_status = common_send_email($email, $subject, $html);

        if ($email_status) {
            $return_array['email_status'] = $email_status;
            $return_array['Status'] = 1;
            $return_array['Message'] = 'Email sent successfully.';
        }

        return $return_array;
    }
}

if (!function_exists('uploadDocument')) {
    function uploadDocument() { //$file_obj, $lead_id = 0, $flag = 0, $ext = ''
        require_once(COMP_PATH . '/s3_bucket/S3_library.php');

        $s3_upload = new S3_upload();

        if ($flag == 1) {
            $extension = $ext;
        } else if ($flag == 2) {
            $extension = $ext;
        } else {
            $file_name = $file_obj["file_name"]['name'];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
        }

        $new_name = $lead_id . '_lms_' . date('YmdHis') . rand(111, 999) . '.' . $extension;

        if ($flag == 1) {
            $upload = $s3_conn->upload_file($file_obj, $new_name, $flag);
        } else if ($flag == 2) {
            $upload = $s3_conn->upload_file($file_obj, $new_name);
        } else {
            $upload = $s3_conn->upload_file($file_obj["file_name"]["tmp_name"], $new_name);
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

        $upload = $s3_conn->get_file($file_name, $flag);
        return $upload;
    }
}
