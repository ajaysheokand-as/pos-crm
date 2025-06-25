<?php
$input_data = file_get_contents("php://input");
$curl = curl_init();

$input = json_decode($input_data,true);
$pan = $input['pancard'];

$otp = $input['otp'];

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://lms.paisaonsalary.in/api/Api/CustomerDetails/verifyOtp',
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
//echo json_encode($data);

if(isset($data['amount']) && $data['amount'] > 0){
    $curl1 = curl_init();
$rp_amount = round(($data['amount'] * 100),2);
$loan_no = $res['data']['repayment_data']['loan_no'];


    curl_setopt_array($curl1, array(
      CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "amount": '.$rp_amount.',
        "currency": "INR",
        "receipt": "'.$loan_no.'",
        "notes": {
            "orderid": "'.$loan_no.'"
        },
        "partial_payment": true,
        "first_payment_min_amount": 100000
    }',
      CURLOPT_HTTPHEADER => array(
        'Authorization:  Basic cnpwX2xpdmVfNU9ieGljUGpyZjJseFM6WkdnOUJuejg3ZjVGZFpjZUFTT3pOS0Qw',
        'Content-Type:  application/json'
      ),
    ));
    
    $response1 = curl_exec($curl1);
    
    curl_close($curl1);
    $ress = json_decode($response1,true);
    $data['Status'] = 1;
    $data['order_id'] = isset($ress['id']) ? $ress['id'] : null;
    $data['rzp_amount'] = $rp_amount;
 echo json_encode($data);   
}
else {
    
}

