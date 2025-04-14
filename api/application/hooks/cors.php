<?php
// Add CORS headers
// function add_cors_headers() {
//     header("Access-Control-Allow-Origin: *"); // Allow all origins, or restrict to specific domain
//     header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Allowed HTTP methods
//     header("Access-Control-Allow-Headers: Content-Type, Authorization, Auth, Authtoken"); // Allow custom headers, including 'auth'

//     // Handle OPTIONS request
//     if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//         header('HTTP/1.1 200 OK');
//         exit();
//     }
// }


// Add CORS headers to the current request
function add_cors_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Auth, Authtoken");
    
    // Handle Preflight Requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

