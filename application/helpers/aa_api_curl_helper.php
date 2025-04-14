<?php

if (!function_exists('sendCurl_request')) {

    function sendCurl_request($json_request, $endUrl) {
			
			//$url = 'https://api-preproduction.signzy.app/api/v3/' . $endUrl ;
			//$token = 'aT60LIVFoR3OckLNAuHzS9sj41k5PtM8';
			$url = 'https://api.signzy.app/api/v3/' . $endUrl ;
			$token = 'ScTTTviEmhU1EPT79VM6QV9NUHImPkBm';
			
			$curl = curl_init();
	
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>$json_request,
			  CURLOPT_HTTPHEADER => array(
				'Authorization: '.$token,
				'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);

        return $response;
    }

}
