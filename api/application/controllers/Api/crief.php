<?php

if (isset($_SERVER['HTTP_REQUESTXML']) && !empty($_SERVER['HTTP_REQUESTXML'])) {
	$curl = curl_init();
	//file_put_contents('crif_request.txt',"\n\n Time: ".date("F j, Y, g:i a"). " - ". $_SERVER['HTTP_REQUESTXML'], FILE_APPEND);
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://hub.crifhighmark.com/Inquiry/doGet.service/requestResponseSync',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_HTTPHEADER => array(
			'mbrid: NBF0005465',
			'productType: INDV',
			'productVersion: 1.0',
			'Content-Type: application/xml',
			'UserId: kasar_cpu_prd@kasarcredit.com',
			'password: E2ACF08723F5EBBCC6AE42383D1BEA844EEFA571',
			'reqVolType: INDV',
			'requestXml: ' . $_SERVER['HTTP_REQUESTXML']
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//file_put_contents('crif_response.txt',"\n\n Time: ".date("F j, Y, g:i a"). " - ". $response, FILE_APPEND);
	//echo $response;
	if ($response === false) {
		$error_msg = curl_error($curl);
		$error_code = curl_errno($curl);
		echo "cURL Error: $error_msg (Error Code: $error_code)";
	} else {
		header('Content-Type: application/xml');
		echo $response;
	}
}
