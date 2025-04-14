<?php
$input_data = file_get_contents("php://input");
$curl = curl_init();

$input = json_decode($input_data,true);
$pan = $input['pancard'];
print_r($pan);

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.tejasloan.com/Api/CustomerDetails/SendOtp',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{"panNumber" :"'.$pan.'"}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Auth: Y2M0Nzk0OGYwNmQyMjdmZTlhY2E1ZWQ1Nzk5YTZmMWE=',
    'Content-Type: application/json',
    'Cookie: ci_session=997e3d993dae9bbb2c439a790c4fff2ba71b3024'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
