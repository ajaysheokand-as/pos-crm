<?php

$white_list_internal_urls = array("sanction-esign-request", "sanction-esign-response", "aadhaar-veri-request", "aadhaar-veri-response", "sanction-esign-consent", "digitap-aadhaar-veri-request");
$white_list_internal_params = array("refstr", "lead_id");

$blacklisted_urls = array("login", "img-sys");

// $file = FCPATH . "common_component/includes/functions.inc.php";
// if (file_exists($file)) {
//     require_once ($file);
// }
//  $to="akash.kushwaha@bharatloan.com";
//  $sub="Test email";
//  $msg="Testing the mail";
//  common_send_email($to,$sub,$msg);
//$_GET = array("xxx"=>"yyyy", "zzz"=>"1' AND 1=1");
//$_POST = array("kkk"=>array("my ))", "mmm'"));
//$_SESSION = array();

$request_uri = urldecode($_SERVER['REQUEST_URI']);
$request_http_user_agent = urldecode($_SERVER["HTTP_USER_AGENT"]);
$request_http_referer = urldecode($_SERVER["HTTP_REFERER"]);
//$request_uri = "";

$uri_str = "URI: $request_uri";
$session_str = "SESSION: " . json_encode($_SESSION);
$post_str = "POST: " . json_encode($_POST);
$get_str = "GET: " . json_encode($_GET);
$error_str_postfix = " || <p><b> <BR>$uri_str <BR>$session_str <BR>$post_str <BR>$get_str";

$error_instances = 0;
$error_string = "";
$api_error_string = "";
$api_error_instances = 0;

$sql_threat_score = 0;
$sql_threat_1 = array(" and ", " or ", "=", "(", ")");
$sql_threat_5 = array("select ", " from ", " where ", " having ", " table", "sleep", " union ");
$sql_threat_10 = array("database", "substr", "ascii", "()", "length(");

function isValidEmail($email) {
    // Regular expression for basic email validation
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    // Check if the email matches the pattern
    if (preg_match($pattern, $email)) {
        return true;
    } else {
        return false;
    }
}

foreach ($blacklisted_urls as $blacklisted_url) {
    if (strpos($request_uri, $blacklisted_url) !== false) {
        header("Location: /");
        exit;
    }
}

foreach ($white_list_internal_urls as $url) {
    if (strpos($request_uri, $url) !== false) {
        foreach ($_REQUEST as $req_key => $req_value) {
            if (in_array($req_key, $white_list_internal_params)) {
                unset($sql_threat_1[2]);
                $string_print .= "<br/>Step 3";
                break;
            }
        }
    }
}

/// File extension list
$banned_file_extension_list = array(".sql", ".php", ".txt", ".htaccess", ".htpass", ".bash", ".xml", ".log", "passwd", ".conf", ".sh", ".properties", ".jar", ".java", ".war");

//FIRST CHECK FOR BASTARD CHARACTERS IN REQUEST_URI
$uri_key_banned_list = array("/..", "../", "<", ">", "'", "%", ".sql", ".php", ".txt", ".htaccess", ".htpass", ".bash", ".xml", ".log", "passwd", ".conf", ".sh", ".properties", ".jar", ".java", ".war", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");

foreach ($uri_key_banned_list as $barred_uri_str) {
    if (stripos($request_uri, $barred_uri_str) !== false) {
        $error_string .= " | $barred_uri_str within URI";
        $error_instances++;
    }
}

////// BASTARD CHARACTERS CHECK IN HTTP_USER_AGENT  ///////
$http_value_banned_list = array("/..", "../", "<", ">", "select", ".htaccess", ".htpass", "IN BOOLEAN MODE", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");

foreach ($http_value_banned_list as $barred_httpd_value_str) {
    if (stripos($request_http_user_agent, $barred_httpd_value_str) !== false) {
        $error_string .= " | $barred_httpd_value_str within HTTP_USER_AGENT VALUE $request_http_user_agent";
        $error_instances++;
    }

    if (stripos($request_http_referer, $barred_httpd_value_str) !== false) {
        $error_string .= " | $barred_httpd_value_str within HTTP_REFERER VALUE $request_http_referer";
        $error_instances++;
    }
}

// foreach ($sql_threat_2 as $sql_threat_2_str) {
//     $count_instances = substr_count(strtoupper($request_http_user_agent), strtoupper($sql_threat_2_str));
//     $count_instances += substr_count(strtoupper($request_http_referer), strtoupper($sql_threat_2_str));
//     $sql_threat_score += 2 * $count_instances;
//     echo("$sql_threat_2_str | 2 * $count_instances | $sql_threat_score \r\n");
// }

foreach ($sql_threat_5 as $sql_threat_5_str) {
    $count_instances = substr_count(strtoupper($request_http_user_agent), strtoupper($sql_threat_5_str));
    $count_instances += substr_count(strtoupper($request_http_referer), strtoupper($sql_threat_5_str));
    $sql_threat_score += 5 * $count_instances;
    //echo("$sql_threat_5_str | 5 * $count_instances | $sql_threat_score \r\n");
}

foreach ($sql_threat_10 as $sql_threat_10_str) {
    $count_instances = substr_count(strtoupper($request_http_user_agent), strtoupper($sql_threat_10_str));
    $count_instances = substr_count(strtoupper($request_http_referer), strtoupper($sql_threat_10_str));
    $sql_threat_score += 10 * $count_instances;
    //echo("$sql_threat_10_str | 10 * $count_instances | $sql_threat_score \r\n");
}

$get_key_banned_list = array("/..", "../", "<", ">", "'", "%", "(", ")", "=", "&", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");
$get_value_banned_list = array("/..", "../", "<", ">", "'", ";", "((", "))", "IN BOOLEAN MODE", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");

foreach ($white_list_internal_urls as $url) {
    if (strpos($request_uri, $url) !== false) {
        foreach ($_REQUEST as $req_key => $req_value) {
            if (in_array($req_key, $white_list_internal_params)) {
                unset($get_key_banned_list[8]);
                $string_print .= "<br/>Step 9";
                break;
            }
        }
    }
}

foreach ($_GET as $key => $value) {
    $value = urldecode($value);
    foreach ($get_key_banned_list as $barred_get_key_str) {
        if (stripos($key, $barred_get_key_str) !== false) {
            $error_string .= " | $barred_get_key_str within GET KEY $key";
            $error_instances++;
            break;
        }
    }

    foreach ($get_value_banned_list as $barred_get_value_str) {
        if (stripos($value, $barred_get_value_str) !== false) {
            $error_string .= " | $barred_get_value_str within GET VALUE $value for $key";
            $error_instances++;
            break;
        }
    }

    /// GET Key File extension check
    foreach ($banned_file_extension_list as $barred_file_extension_str) {
        if (stripos($key, $barred_file_extension_str) !== false) {
            $error_string .= " | $barred_file_extension_str within GET Key $key";
            $error_instances++;
            break;
        }
    }

    /// GET Value File extension check
    foreach ($banned_file_extension_list as $barred_file_extension_str) {
        if (stripos($value, $barred_file_extension_str) !== false) {
            if (!isValidEmail($value)) {
                $error_string .= " | $value within GET VALUE  $value";
                $error_instances++;
                break;
            }
        }
    }

    foreach ($sql_threat_1 as $sql_threat_1_str) {
        $count_instances = substr_count(strtoupper($value), strtoupper($sql_threat_1_str));
        $sql_threat_score += 1 * $count_instances;
    }

    foreach ($sql_threat_5 as $sql_threat_5_str) {
        $count_instances = substr_count(strtoupper($value), strtoupper($sql_threat_5_str));
        $sql_threat_score += 5 * $count_instances;
    }

    foreach ($sql_threat_10 as $sql_threat_10_str) {
        $count_instances = substr_count(strtoupper($value), strtoupper($sql_threat_10_str));
        $sql_threat_score += 10 * $count_instances;
    }
}


$post_key_banned_list = array("/..", "../", "<", ">", "'", "%", "(", ")", "=", "&", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");
$post_value_banned_list = array("/..", "../", "<", ">", "'", ";", "((", "))", "IN BOOLEAN MODE", "table_name", "information_schema", "table_schema", "ascii(", "database(", "substr(", "count(*", "sleep(");

foreach ($white_list_internal_urls as $url) {
    if (strpos($request_uri, $url) !== false) {
        foreach ($_REQUEST as $req_key => $req_value) {
            if (in_array($req_key, $white_list_internal_params)) {
                unset($post_key_banned_list[8]);
                break;
            }
        }
    }
}


foreach ($_POST as $key => $value) {
    foreach ($post_key_banned_list as $barred_post_key_str) {
        if (stripos($key, $barred_post_key_str) !== false) {
            $error_string .= " | $barred_post_key_str within POST KEY $key";
            $error_instances++;
        }
    }

    /// POST Key File extension check
    foreach ($banned_file_extension_list as $barred_file_extension_str) {
        if (stripos($key, $barred_file_extension_str) !== false) {
            $error_string .= " | $barred_file_extension_str within POST Key $key";
            $error_instances++;
            break;
        }
    }

    if (is_array($value)) {
        foreach ($value as $k => $val) {
            $val = urldecode($val);
            foreach ($post_value_banned_list as $barred_post_value_str) {
                if (stripos($val, $barred_post_value_str) !== false) {
                    $error_string .= " | $barred_post_value_str within POST ARRAY VALUE $val for $key";
                    $error_instances++;
                    break;
                }
            }

            /// POST Array value File extension check
            foreach ($banned_file_extension_list as $barred_file_extension_str) {
                if (stripos($val, $barred_file_extension_str) !== false) {
                    if (!isValidEmail($val)) {
                        $error_string .= " | $barred_file_extension_str within POST ARRAY VALUE $val for $key";
                        $error_instances++;
                        break;
                    }
                }
            }

            foreach ($sql_threat_1 as $sql_threat_1_str) {
                $sql_threat_score += 1 * substr_count(strtoupper($val), strtoupper($sql_threat_1_str));
            }

            foreach ($sql_threat_5 as $sql_threat_5_str) {
                $sql_threat_score += 5 * substr_count(strtoupper($val), strtoupper($sql_threat_5_str));
            }

            foreach ($sql_threat_10 as $sql_threat_10_str) {
                $sql_threat_score += 10 * substr_count(strtoupper($val), strtoupper($sql_threat_10_str));
            }
        }
    } else {
        $value = urldecode($value);
        foreach ($post_value_banned_list as $barred_post_value_str) {
            if (stripos($value, $barred_post_value_str) !== false) {
                $error_string .= " | $barred_post_value_str within POST VALUE $value for $key";
                $error_instances++;
                break;
            }
        }

        /// POST value File extension check
        foreach ($banned_file_extension_list as $barred_file_extension_str) {
            if (stripos($value, $barred_file_extension_str) !== false) {
                if (!isValidEmail($value)) {
                    $error_string .= " | $barred_file_extension_str within POST VALUE $value for $key";
                    $error_instances++;
                    break;
                }
            }
        }

        foreach ($sql_threat_1 as $sql_threat_1_str) {
            $sql_threat_score += 1 * substr_count(strtoupper($value), strtoupper($sql_threat_1_str));
        }

        foreach ($sql_threat_5 as $sql_threat_5_str) {
            $sql_threat_score += 5 * substr_count(strtoupper($value), strtoupper($sql_threat_5_str));
        }

        foreach ($sql_threat_10 as $sql_threat_10_str) {
            $sql_threat_score += 10 * substr_count(strtoupper($value), strtoupper($sql_threat_10_str));
        }
    }
}

if ($sql_threat_score > 21) {
    $error_string .= " | SQL THREAT VALUE = $sql_threat_score";
}

// echo("error_string: $error_string\r\n");
// echo("sql_threat_score: $sql_threat_score\r\n");
// echo ("error_instances: $error_instances\r\n");
// echo "sql_threat_score: $sql_threat_score\r\n";
// echo "error_string: $error_string\r\n";
// print_r(($error_instances > 0 || $sql_threat_score > 21) && true);

$ErrorLog = null;

$mode = "a+";
$file_name = FCPATH . 'error_logs/' . 'error_log_' . date("YmdH") . ".log";

if (($error_instances > 0 || $sql_threat_score > 21) && true) {

    $ErrorLog = fopen($file_name, $mode);

    $error_string_raw = $error_string;

    $error_string = $error_string . $error_str_postfix;
    $pretty_error = "<p><b>E_INFOSEC [INFOSEC]: </b>" . $error_string . "</p><p><i>AT: " . date("Y-M-d H:m:s U") . "</i></p>";
    $pretty_error .= "<p><small>PLATFORM: PHP " . PHP_VERSION . " " . PHP_OS . " REMOTE: " . (isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : "") . " [" . $_SERVER['REMOTE_ADDR'] . "]</small></p>";
    $pretty_error .= "<p>" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . (isset($_SESSION["isUserSession"]["user_id"]) ? (" LMS USER ID:" . $_SESSION["isUserSession"]["user_id"]) : "") . "</p>";
    if (isset($ErrorLog)) {
        fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
    }

    $SUB = "LMS : Suspected breach attempt";
    $to_array = array(0 => "ajay@salarayontime.com", 1 => "akash.kushwaha@salarayontime.com");
    $TOName = "Security Alerts";
    $post_str = "POST: " . json_encode($_POST);
    $get_str = "GET: " . json_encode($_GET);
    $pretty_error_2 = "<p><small>PLATFORM: PHP " . PHP_VERSION . " " . PHP_OS . " REMOTE: " . (isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : "") . " [" . $_SERVER['REMOTE_ADDR'] . "]</small></p>";
    $pretty_error_2 .= "<p>" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . (isset($_SESSION["isUserSession"]["user_id"]) ? (" LMS USER ID:" . $_SESSION["isUserSession"]["user_id"]) : "") . "</p>";
    $final_str = "<BR><BR>$error_string_raw | $post_str | $get_str | $pretty_error_2";
    $MSG = date('Y-m-d-H-i-s') . " | " . $_SERVER["REMOTE_ADDR"] . " | Type 1 | $final_str";
    // foreach ($to_array AS $TO) {
    //     common_send_email($TO, $SUB, $MSG);
    // }

    // header("Location: /404");
    // exit;
}
