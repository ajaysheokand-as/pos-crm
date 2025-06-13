<?php

date_default_timezone_set("Asia/Calcutta");

$reques_params = array (

    'AGGR_ID' => 'BULK0025',

    'AGGR_NAME' => 'STAYVISTA',

    'CORP_ID' => 'SESPRODUCT',

    'USER_ID' => '389018',

    'URN' => 'SR230928486',

    'UNIQUE_ID'=> 'BUCKS001',

    'AGOTP' => '824169',

    'FILE_NAME' => '5fa96a80ad2f11ed9de76f8a608c5a3e',

    'FILE_CONTENT' => "RkhSfDN8MjAvMDIvMjAyM3x0ZXN0fDN8SU5SfDAwMDQwNTAwMTI1N3wwMDExXgpNRFJ8MDAwNDA1MDAxMjU3fDAwMTF8cHJhY2hpY2lifDN8SU5SfHRlc3RwYXl8SUNJQzAwMDAwMTF8V0lCXgpNQ1d8MDQxMTAxNTE4MjQwfDAwMTF8cmVudXwyfElOUnx0ZXN0cGF5fElDSUMwMDAwMDExfFdJQl4KTUNXfDA0MTEwMTUxODI0MHwwMDExfHJlbnV8MXxJTlJ8dGVzdHBheXxJQ0lDMDAwMDAxMXxXSUJe",

    'FILE_DESCRIPTION' => '5fa96a80ad2f11ed9de76f8a608c5a3e',

);
 
$apostData = json_encode($reques_params);

print_r("<<========apostData=========>>");

print_r($apostData);

$sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

$fp=fopen("/public1.txt","r");

$pub_key_string=fread($fp,8192);

openssl_get_publickey($pub_key_string);

openssl_public_encrypt($sessionKey,$encryptedKey,$pub_key_string); // RSA

$iv = 1234567890123456; //str_repeat("\0", 16);

$encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES

$request = [

    "requestId"=> "req_".time(),

    "encryptedKey"=> base64_encode($encryptedKey),

    "iv"=> base64_encode($iv),

    "encryptedData"=> base64_encode($encryptedData),

    "oaepHashingAlgorithm"=> "NONE",

    "service"=> "",

    "clientInfo"=> "",

    "optionalParam"=> ""

];
 
print_r("<<========request=========>>");

print_r($request);

$apostData = json_encode($request);

print_r("<<========apostData=========>>");

print_r($apostData);

$httpUrl = "https://apibankingonesandbox.icicibank.com/api/v1/cibbulkpayment/bulkPayment";

print_r("<<========httpUrl=========>>");

print_r($httpUrl);

$headers = array(

    "cache-control: no-cache",

    "accept: application/json",

    "content-type: application/json",

    "apikey: JMzH2oBzcMjb76vMmjhsXiV5UxiGJGo4",

    "x-priority:1000"

);

print_r("<<========headers=========>>");

print_r($headers);

$acurl = curl_init();

curl_setopt_array($acurl, array(

    CURLOPT_URL => $httpUrl,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_ENCODING => "",

    CURLOPT_MAXREDIRS => 10,

    CURLOPT_TIMEOUT => 300,

    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

    CURLOPT_CUSTOMREQUEST => "POST",

    CURLOPT_POSTFIELDS => $apostData,

    CURLOPT_HTTPHEADER => $headers,

));

$aresponse = curl_exec($acurl);

print_r("<<========aresponse=========>>");

print_r($aresponse);

$aerr = curl_error($acurl);

$httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);

print_r("<<========httpcode=========>>");

print_r($httpcode);

if ($aerr) {

    echo "cURL Error #:" . $aerr;

} else {

    $fp= fopen("prod_private.pem","r");

    $priv_key=fread($fp,8192);

    fclose($fp);

    $res = openssl_get_privatekey($priv_key, "");

    $data = json_decode($aresponse);

    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);

    $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);

    $newsource = substr($encData, 16);

    $output = json_decode($newsource);

    print_r("<<========output=========>>");

	print_r($output);

}
 