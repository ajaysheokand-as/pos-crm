<?php
    defined('BASEPATH') OR exit('No direct script access allowed');  
    class Blocker extends CI_Controller
    {
        function __construct(){
            // Define allowed origins
                // Get the comma-separated origins from env
            // $origins_env = false;
            $origins_env = getenv('ALLOWED_ORIGINS');
            // log_message(print_r("error"),$_SERVER['HTTP_ORIGIN']."blocker_message");
            // Convert to array if present, else use default
            $allowed_origins = $origins_env
                ? array_map('trim', explode(',', $origins_env))
                : [
                    'http://localhost:3000',
                    'https://paisaonsalary.com'
                ];

            // Check if the incoming Origin is in the allowed list
            if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
                header("Access-Control-Allow-Credentials: true");
            }
            // Always set these
                header( "Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, X-API-KEY, Authorization");
            // header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            // Preflight handling
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }
        }

        public function isLogin() 
        {
            $this->CI = & get_instance();
            if(!isset($_SESSION)){
                return redirect(base_url());
            }
        }
        /**
         * This function used to block the every request except allowed ip address
         */

        public function requestBlocker()
        {
            $ip = $_SERVER["REMOTE_ADDR"];
            
            if($ip == "49.248.51.230")
            {
                $currentPath = $_SERVER['PHP_SELF']; 
                $pathInfo = pathinfo($currentPath); 
                $hostName = $_SERVER['HTTP_HOST']; 

                $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
                $url = $protocol.'://'.$hostName.$pathInfo['dirname']."/";
                $base_url = $url. "public/front/images/access_denied.jpg";

                $access_denied_image = str_replace("index.php/", "", $base_url);
                echo "<div style='margin-left : 35%; margin-top : 10%;'><img src='".$access_denied_image."' width='300' height='250'><br>";
                echo "Sorry! You can not access this page. Please take permission from Admin"; die;
            }
        }
    }
?>