<?php
$input_data = file_get_contents("php://input");
$curl = curl_init();

$input = json_decode($input_data,true);
$pan = $input['pancard'];

$otp = $input['otp'];

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.tejasloan.com/Api/CustomerDetails/verifyOtp',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "panNumber": "'.$pan.'",
    "otp":"'.$otp.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Auth: Y2M0Nzk0OGYwNmQyMjdmZTlhY2E1ZWQ1Nzk5YTZmMWE=',
    'Content-Type: application/json',
    'Cookie: ci_session=997e3d993dae9bbb2c439a790c4fff2ba71b3024'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$res = json_decode($response,true);
$data['Status'] = $res['Status'];
$data['Message'] = $res['Message'];
$data['amount'] = $res['data']['repayment_data']['total_due_amount'];
$data['res']  = $res;
echo json_encode($data);