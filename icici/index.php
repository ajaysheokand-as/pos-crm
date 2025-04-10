<?php

date_default_timezone_set("Asia/Calcutta");



$reques_params = [
    "mobile" => "6930142245",
    "device-id" => "7591128759112875911287591128",
    "account-provider" => "74",
    "payee-va" => "off@upi",
    "payer-va" => "KASAR1@icici",
    "amount" => 1.00,
    "pre-approved" => "P",
    "use-default-acc" => "D",
    "default-debit" => "N",
    "default-credit" => "N",
    "payee-name" => "KASAR",
    "mcc" => "6012",
    "merchant-type" => "API_INDIVIDUAL",
    "txn-type" => "merchantToPersonPay",
    "channel-code" => "MICICI",
    "remarks" => "none",
    "seq-no" => "ICI848484927456315620",
    "profile-id" => "254595054"
];

// $reques_params = [
//     "date" => "22/05/2024",
//     "recon360" => "N",
//     "seq-no" => "ICI848484998745613112321",
//     "channel-code" => "MICICI",
//     "ori-seq-no" => "ICI84848492274563112321",
//     "mobile" => "7988000014",
//     "profile-id" => "2996304",
//     "device-id" => "40043sdfsd8dfgdf400438400438"
// ];

$headers = array(
    "cache-control: no-cache",
    "accept: application/json",
    "content-type: application/json",
    "apikey: GxWaczAJKJvYgeUWRlfFTHF8pcTmtrvA",
    "x-priority: 1000"
);





// $reques_params = [
//     "localTxnDtTime" => "20240522162130",
//     "beneAccNo" => "123456041",
//     "beneIFSC" => "NPCI0000001",
//     "amount" => "1.00",
//     "tranRefNo" => "ttrrrrestingranstt",
//     "paymentRef" => "FTTransferP2A",
//     "senderName" => "Pratik Mundhe",
//     "mobile" => "9999988888",
//     "retailerCode" => "rcode",
//     "passCode" => "447c4524c9074b8c97e3a3c40ca7458d",
//     "bcID" => "IBCKer00055"
// ];

// rtrestingranst
// trrrrestingranstt

// $reques_params = [
//     "transRefNo" => "trrrrestingranstt",
//     "date" => "22/05/2024",
//     "recon360" => "N",
//     "passCode" => "447c4524c9074b8c97e3a3c40ca7458d",
//     "bcID" => "IBCKer00055"
// ];

// $headers = array(
//     "cache-control: no-cache",
//     "accept: application/json",
//     "content-type: application/json",
//     "apikey: 7X3NfvmJDqFpefEhhGBWfgI7FoC31CAQ",
//     "x-priority: 0100"
// );

// $reques_params = [
//     "localTxnDtTime" => "20240613153630",
//     "beneAccNo" => "343902120003262",
//     "beneIFSC" => "UBIN0534391",
//     "amount" => "1.00",
//     "tranRefNo" => "testingtranst",
//     "paymentRef" => "FTTransferP2A",
//     "senderName" => "Kasar Credit",
//     "mobile" => "9999988888",
//     "retailerCode" => "rcode",
//     "passCode" => "dec27836c64b460086bb2a782c525668",
//     "bcID" => "IBCKAS01640"
// ];
// $headers = array(
//     "cache-control: no-cache",
//     "accept: application/json",
//     "content-type: application/json",
//     "apikey: GxWaczAJKJvYgeUWRlfFTHF8pcTmtrvA",
//     "x-priority: 0100"
// );






// $reques_params = [
//     "tranRefNo"=> "2024555401100162301",
//     "amount"=> "1",
//     "senderAcctNo"=> "071805004842",
//     "beneAccNo"=> "343902120003262",
//     "beneName"=> "Urvashi",
//     "beneIFSC"=> "UBIN0534391",
//     "narration1"=> "NEFT transaction",
//     "narration2"=> "PritamG",
//     "crpId"=> "KASAR1632024K",
//     "crpUsr"=> "ANKITMOD",
//     "aggrId"=> "MESCOMP0113",
//     "aggrName"=> "KASAR",
//     "urn"=> "SR254269641",
//     "txnType"=> "RGS",
//     "WORKFLOW_REQD"=> "Y"
// ];

// $reques_params = [
//     "AGGRID" => "MESCOMP0113",
//     "CORPID" => "KASAR1632024K",
//     "USERID" => "ANKITMOD",
//     "URN" => "SR254269641",
//     "UNIQUEID" => "2024555401100162300"
// ];

// $headers = array(
//     "cache-control: no-cache",
//     "accept: application/json",
//     "content-type: application/json",
//     "apikey: GxWaczAJKJvYgeUWRlfFTHF8pcTmtrvA",
//     "x-priority: 0010"
// );



$apostData = json_encode($reques_params);
print_r("<<========apostData=========>>");
echo '</br>';
print_r($apostData);
echo '</br>';
$sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

$fp = fopen("prod_public.txt", "r"); // bank certificate
$pub_key_string = fread($fp, 8192);
//fclose($fp);
openssl_get_publickey($pub_key_string);
openssl_public_encrypt($sessionKey, $encryptedKey, $pub_key_string); // RSA

$iv = 1234567890123456; //str_repeat("\0", 16);

$encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES

$request = [
    "requestId" => "req_" . time(),
    "encryptedKey" => base64_encode($encryptedKey),
    "iv" => base64_encode($iv),
    "encryptedData" => base64_encode($encryptedData),
    "oaepHashingAlgorithm" => "NONE",
    "service" => "",
    "clientInfo" => "",
    "optionalParam" => ""
];

print_r("<<========request=========>>");
echo '</br>';
print_r($request);
echo '</br>';
// echo "Time: ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Session key: ".$sessionKey.PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Base64 Session key: ".base64_encode($sessionKey).PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Decrypted Request: ".$apostData.PHP_EOL.PHP_EOL; echo "<br/>";
// echo "encryptedKey: ".$request['encryptedKey'].PHP_EOL.PHP_EOL; echo "<br/>";
// echo "encryptedData: ".$request['encryptedData'].PHP_EOL.PHP_EOL; echo "<br/>";
// echo "iv: ".$request['iv'].PHP_EOL.PHP_EOL; echo "<br/>";

$apostData = json_encode($request);
print_r("<<========apostData=========>>");
echo '</br>';
print_r($apostData);
echo '</br>';
$httpUrl = "https://apibankingone.icicibank.com/api/v1/composite-payment";
// $httpUrl = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment";
// $httpUrl = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment_sv";
// $httpUrl = "https://apibankingonesandbox.icicibank.com/api/v1/composite-status";
print_r("<<========httpUrl=========>>");
echo '</br>';
print_r($httpUrl);
echo '</br>';
// $headers = array(
//     "cache-control: no-cache",
//     "accept: application/json",
//     "content-type: application/json",
//     "apikey: 7X3NfvmJDqFpefEhhGBWfgI7FoC31CAQ",
//     "x-priority:1000"
// );
print_r("<<========headers=========>>");
echo '</br>';
print_r($headers);
echo '</br>';
// $file = 'logFiles.txt';

// $log = "\n\n".'GUID - '.time()."================================================================\n";
// $log .= 'URL - '.$httpUrl."\n\n";
// $log .= 'HEADER - '.json_encode($headers)."\n\n";
// $log .= 'REQUEST - '.json_encode($reques_params)."\n\n";
// $log .= 'REQUEST ENCRYPTED - '.$apostData."\n\n";

// file_put_contents($file, $log, FILE_APPEND | LOCK_EX);



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
echo '</br>';
print_r($aresponse);
echo '</br>';
$aerr = curl_error($acurl);
$httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
print_r("<<========httpcode=========>>");
echo '</br>';
print_r($httpcode);
echo '</br>';

if ($aerr) {

    echo "cURL Error #:" . $aerr;
} else {

    $fp = fopen("prod_private.pem", "r"); // your private key
    $priv_key = fread($fp, 8192);
    fclose($fp);
    $res = openssl_get_privatekey($priv_key, "");
    $data = json_decode($aresponse);
    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
    $encData = openssl_decrypt(base64_decode($data->encryptedData), "aes-128-cbc", $key, OPENSSL_PKCS1_PADDING);
    $newsource = substr($encData, 16);

    // $log = "\n\n".'GUID - '."================================================================\n";
    // $log .= 'URL - '.$httpUrl."\n\n";
    // $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n";
    // $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n";

    // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

    $output = json_decode($newsource);
    print_r("<<========output=========>>");
    echo '</br>';
    print_r($output);
}
