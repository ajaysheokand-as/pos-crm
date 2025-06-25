<?php

defined('BASEPATH') or exit('No direct script access allowed');

class AAController extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Leadmod', 'Leads');
		$this->load->model('Task_Model', 'Tasks');

		date_default_timezone_set('Asia/Kolkata');
		$timestamp = date("Y-m-d H:i:s");

		$login = new IsLogin();
		$login->index();
	}

	public function getAAconsentAllLog($leadId) {
		$lead_id = intval($this->encrypt->decode($leadId));
		$methodId = $this->input->get('methodId') ? intval($this->input->get('methodId')) : null;

		$return_data = $this->Tasks->getAAconsentAllLog($lead_id, $methodId, ['aa_id,aa_method_id,aa_request_datetime,aa_sessionId,aa_api_status_id,aa_status_message,aa_consentHandleId,aa_consentId']);

		if (empty($return_data[0])) {
			$data = array('status' => false, 'message' => 'Consent request not found.');
			echo json_encode($data);
			die;
		} else {
			$data = array('status' => true, 'message' => 'Consent request found.', 'data' => $return_data);
			echo json_encode($data);
			die;
		}
	}

	public function consentRequest($leadId) {
		$lead_id = intval($this->encrypt->decode($leadId));

		$return_data = $this->db->select('mobile,first_name,lead_id,email,status,stage,lead_status_id')->where('lead_id', $lead_id)->from('leads')->get()->row();
		if (!isset($return_data) && empty($return_data)) {
			$data = array('status' => false, 'message' => 'Lead detail is not found.');
			echo json_encode($data);
			die;
		} else {
			$user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : "0";
			$enc_lead_id = $this->encrypt->encode($lead_id);
			$this->load->helper('aa_api_curl');
			$mobile = $return_data->mobile;
			$email = $return_data->email;
			$name = $return_data->first_name;

			$aa_request_datetime = date("Y-m-d H:i:s");
			$requestArray = array(
				"mobileNumber" => $mobile,
				"consentDescription" => "CONSENT FOR BANK STATEMENT",
				"consentArtifactName" => "BANK_STATEMENT_PERIODIC",
				"redirectUrl" => "https://paisaonsalary.com/account-consent-thank-you?refstr=" . $enc_lead_id
			);
			$json_request = json_encode($requestArray);
			$endUrl = 'accountAggregator/consent-request-plus';
			$response = sendCurl_request($json_request, $endUrl);
			/* $response = '{"result":{"encryptedRequest":"ohA0BMmD-fLAvdUkCZZgXAOp3rZpRm1D2HYbv9bBQFTCNvW2yIl-rFajq1rrZxydRxxN1iTLrg5W2Ttr57EsOnZKLWS8fYl-kBa_EQheont9B4YgUdgiiDmjQEEb0bGgGqJGgS-vu59xBvzMgclp8zXc2--PYKMhmVk6fgrlmy6ccz5cBRR3MNrAPw_tPRl0A_awjv9PCZH34fbKSd7nULG_WqcOUPAzmhWF_oCsx1Y=","requestDate":"190820241908190","encryptedFiuId":"V1BFeFlRQVVD","consentHandle":"71822cb1-27ba-49fb-b4c5-431d78fd522b","url":"https://reactjssdk.finvu.in/?ecreq=ohA0BMmD-fLAvdUkCZZgXAOp3rZpRm1D2HYbv9bBQFTCNvW2yIl-rFajq1rrZxydRxxN1iTLrg5W2Ttr57EsOnZKLWS8fYl-kBa_EQheont9B4YgUdgiiDmjQEEb0bGgGqJGgS-vu59xBvzMgclp8zXc2--PYKMhmVk6fgrlmy6ccz5cBRR3MNrAPw_tPRl0A_awjv9PCZH34fbKSd7nULG_WqcOUPAzmhWF_oCsx1Y=&reqdate=190820241908190&fi=V1BFeFlRQVVD"}}'; */
			$resArr = json_decode($response, true);
			$apiStatus = isset($resArr['result']['url']) ? 1 : 2;
			$consentHandle = isset($resArr['result']['consentHandle']) ? $resArr['result']['consentHandle'] : '';
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 1,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandle,
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");

			if (isset($resArr['result']) && !empty($resArr['result']) && isset($resArr['result']['url'])) {
				$url = $resArr['result']['url'];

				require_once(COMPONENT_PATH . 'CommonComponent.php');
				$CommonComponent = new CommonComponent();
				$res = $CommonComponent->call_url_shortener_api($url, $lead_id);
				$account_aggregator_register_url = $res['short_url'];
				//$account_aggregator_register_url = 'https://tinyurl.com/yu99pv5n';
				$mailResposne = $this->sendConsentRequest_url($name, $account_aggregator_register_url, $email, $mobile);

				if ($mailResposne['status']) {
					$lead_followup = [
						'lead_id' => $lead_id,
						'user_id' => $user_id,
						'status' => $return_data->status,
						'stage' => $return_data->stage,
						'lead_followup_status_id' => $return_data->lead_status_id,
						'remarks' => $mailResposne['message'],
						'created_on' => date("Y-m-d H:i:s")
					];
					$this->Tasks->insert($lead_followup, "lead_followup");
				}
				$resData['status'] = true;
				$resData['message'] = 'Account Aggregator request sent successfully';
				$resData['consentHandleId'] = $consentHandle;
				echo json_encode($resData);
				die;
			} else {
				$resData['status'] = false;
				$resData['message'] = 'Error: Please try again after sometime.';
				$resData['consentHandleId'] = $consentHandle;
				echo json_encode($resData);
				die;
			}
		}
	}

	public function consentRequestStatus($leadId, $internalCall = false) {
		$lead_id = intval($this->encrypt->decode($leadId));

		$return_data = $this->Tasks->getAAconsentLog($lead_id, 1, ['a.mobile', 'b.aa_consentHandleId']);
		if (empty($return_data)) {
			$data = array('status' => false, 'message' => 'Consent request not found.');
			echo json_encode($data);
			die;
		}
		$this->load->helper('aa_api_curl');
		$mobile = $return_data->mobile;
		$consentHandleId = $return_data->aa_consentHandleId;

		$aa_request_datetime = date("Y-m-d H:i:s");
		$requestArray = array(
			"mobileNumber" => $mobile,
			"consentHandleId" => $consentHandleId
		);
		$json_request = json_encode($requestArray);
		$endUrl = 'accountAggregator/consent-status';
		$response = sendCurl_request($json_request, $endUrl);
		//$response = '{"result":{"consentStatus":"ACCEPTED","consentId":"8a3e9070-7000-46c4-a858-a92468e1e00f"}}';

		$resArr = json_decode($response, true);
		$apiStatus = isset($resArr['result']) ? 1 : 2;
		$consentId = isset($resArr['result']['consentId']) ? $resArr['result']['consentId'] : null;
		if (isset($consentId) && !empty($consentId)) {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 2,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_status_message' => 'ACCEPTED',
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");

			$this->db->where('aa_consentHandleId', $consentHandleId)->where('aa_lead_id', $lead_id)->where('aa_method_id', 1)->update('api_account_aggregator_logs', ['aa_status_message' => 'ACCEPTED']);

			$resData['status'] = true;
			$resData['message'] = 'Request accepted.';
			$resData['data']['consentHandleId'] = $consentHandleId;
			$resData['data']['consentId'] = $consentId;
			//echo json_encode($resData); die;
		} else {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => addslashes($response),
				'aa_method_id' => 2,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_status_message' => 'REJECTED',
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = false;
			$resData['message'] = 'Request not accepted.';
			$resData['data']['consentHandleId'] = $consentHandleId;
			//echo json_encode($resData); die;
		}
		if ($internalCall) {
			return $resData;
		} else {
			echo json_encode($resData);
			die;
		}
	}

	public function fiRequest($leadId) {
		$lead_id = intval($this->encrypt->decode($leadId));

		$dateFrom = $this->input->get('dateFrom');
		$dateTo = $this->input->get('dateTo');
		if (empty($dateFrom) || empty($dateTo)) {
			$data = array('status' => false, 'message' => 'dateTimeRange is not allowed to be empty.');
			echo json_encode($data);
			die;
		}
		$dateFrom = date("Y-m-d", strtotime($dateFrom));
		$dateTo = date("Y-m-d", strtotime($dateTo));

		$return_data = $this->Tasks->getAAconsentLog($lead_id, 2, ['a.mobile', 'b.aa_consentHandleId', 'b.aa_consentId']);

		if (empty($return_data)) {
			$data = array('status' => false, 'message' => 'Consent request not found.');
			echo json_encode($data);
			die;
		}
		if (empty($return_data->aa_consentId)) {
			$consentStatus = $this->consentRequestStatus($leadId, true);
			if (isset($consentStatus['status']) && $consentStatus['status'] == true && !empty($consentStatus['consentId'])) {
				$consentId = $consentStatus['consentId'];
			} else {
				$data = array('status' => false, 'message' => 'Consent request not accepted form customer side.');
				echo json_encode($data);
				die;
			}
		} else {
			$consentId = $return_data->aa_consentId;
		}
		$this->load->helper('aa_api_curl');
		$mobile = $return_data->mobile;
		$consentHandleId = $return_data->aa_consentHandleId;
		$dateTimeRF = new DateTime($dateFrom . ' 00:00:59');
		$dateTimeRangeFrom	= $dateTimeRF->format('Y-m-d\TH:i:s.vO');
		$dateTimeTF = new DateTime($dateTo . ' 23:59:59');
		$dateTimeRangeTo	= $dateTimeTF->format('Y-m-d\TH:i:s.vO');
		$aa_request_datetime = date("Y-m-d H:i:s");

		$requestArray = array(
			"customerId" => $mobile,
			"consentHandleId" => $consentHandleId,
			"consentId" => $consentId,
			"dateTimeRangeFrom" => $dateTimeRangeFrom,
			"dateTimeRangeTo" => $dateTimeRangeTo
		);
		$json_request = json_encode($requestArray);
		$endUrl = 'accountAggregator/FI-request';
		$response = sendCurl_request($json_request, $endUrl);
		//$response = '{"result": {"ver": "2.0.0","timestamp": "2024-08-20T17:15:33.334+00:00","txnid": "10d7f210-3717-4d64-938e-6d7e3d076215","consentId": "8a3e9070-7000-46c4-a858-a92468e1e00f","sessionId": "7e11e2ad-af5a-4968-a52f-7dd843385b58","consentHandleId": null} }';

		$resArr = json_decode($response, true);
		$apiStatus = isset($resArr['result']) ? 1 : 2;
		$sessionId = isset($resArr['result']['sessionId']) ? $resArr['result']['sessionId'] : null;
		if (isset($sessionId) && !empty($sessionId)) {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 3,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = true;
			$resData['message'] = 'Request accepted.';
			echo json_encode($resData);
			die;
		} else {

			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 3,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");

			$resData['status'] = false;
			$resData['message'] = ($resArr['error']['message']) ? $resArr['error']['message'] : 'Request not accepted.';
			$resData['resArr'] = $resArr;
			echo json_encode($resData);
			die;
		}
	}

	public function fiRequestStatus($leadId) {
		$lead_id = intval($this->encrypt->decode($leadId));

		$return_data = $this->Tasks->getAAconsentLog($lead_id, 3, ['a.mobile', 'b.aa_consentHandleId', 'b.aa_consentId', 'b.aa_sessionId']);

		if (empty($return_data)) {
			$data = array('status' => false, 'message' => 'Consent request not found.');
			echo json_encode($data);
			die;
		}

		$this->load->helper('aa_api_curl');
		$mobile = $return_data->mobile;
		$consentHandleId = $return_data->aa_consentHandleId;
		$consentId = $return_data->aa_consentId;
		$sessionId = $return_data->aa_sessionId;
		$aa_request_datetime = date("Y-m-d H:i:s");
		$requestArray = array(
			"customerId" => $mobile,
			"consentHandleId" => $consentHandleId,
			"consentId" => $consentId,
			"sessionId" => $sessionId
		);
		$json_request = json_encode($requestArray);
		$endUrl = 'accountAggregator/FI-request-status';
		$response = sendCurl_request($json_request, $endUrl);
		//$response = '{"result":{"fiRequestStatus":"READY"}}';

		$resArr = json_decode($response, true);
		$apiStatus = isset($resArr['result']) ? 1 : 2;
		$fiRequestStatus = isset($resArr['result']['fiRequestStatus']) ? $resArr['result']['fiRequestStatus'] : null;
		if (isset($fiRequestStatus) && $fiRequestStatus == "READY") {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 4,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_status_message' => $fiRequestStatus,
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = true;
			$resData['message'] = 'Financial Information is Ready.';
			echo json_encode($resData);
			die;
		} else {
			$resData['status'] = false;
			$resData['message'] = ($resArr['error']['message']) ? $resArr['error']['message'] : 'Not Ready.';
			$resData['resArr'] = $resArr;
			echo json_encode($resData);
			die;
		}
	}

	public function fiFetchData($leadId) {
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		$lead_id = intval($this->encrypt->decode($leadId));

		$returnData = $this->Tasks->getAAconsentLog($lead_id, 5, ['b.*']);
		if (!empty($returnData)) {
			$aa_response = stripslashes($returnData->aa_response);
			$aa_response = str_replace('\\', " - ", $aa_response);
			$reportData = json_decode($aa_response, true);
			$resData['status'] = true;
			$resData['message'] = 'Financial Information is Ready.';
			$resData['data'] = $reportData;
			echo $this->createBankStatement_from_fiData($reportData);
			die;
			//echo json_encode($resData); die;

			//$res = json_decode($nobel_response_data_json,true);
			//print_r($nobel_response_data_json); die;
		}

		$return_data = $this->Tasks->getAAconsentLog($lead_id, 3, ['a.mobile', 'b.aa_consentHandleId', 'b.aa_consentId', 'b.aa_sessionId']);

		if (empty($return_data)) {
			$data = array('status' => false, 'message' => 'Consent request not found.');
			echo json_encode($data);
			die;
		}
		$this->load->helper('aa_api_curl');
		$mobile = $return_data->mobile;
		$consentHandleId = $return_data->aa_consentHandleId;
		$consentId = $return_data->aa_consentId;
		$sessionId = $return_data->aa_sessionId;
		$aa_request_datetime = date("Y-m-d H:i:s");
		$requestArray = array(
			"outputFormat" => "json",
			"consentHandleId" => $consentHandleId,
			"sessionId" => $sessionId
		);
		$json_request = json_encode($requestArray);
		$endUrl = 'accountAggregator/FI-fetch-data';
		$response = sendCurl_request($json_request, $endUrl);

		$response = stripslashes($response);
		$response = str_replace('\\', " - ", $response);
		$resArr = json_decode($response, true);
		$apiStatus = isset($resArr['result']) ? 1 : 2;
		if (isset($resArr['result']['body']) && !empty($resArr['result']['body'])) {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => addslashes($response),
				'aa_method_id' => 5,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_status_message' => 'Report Ready',
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = true;
			$resData['message'] = 'Financial Information is Ready.';
			$resData['data'] = $response;

			echo $this->createBankStatement_from_fiData($resArr);
			die;
			//echo json_encode($resData); die;
		} else {
			echo ($resArr['error']['message']) ? $resArr['error']['message'] : 'Not Ready.';
			die;
			//$resData['status'] = false;
			//$resData['message'] = ($resArr['error']['message']) ? $resArr['error']['message'] : 'Not Ready.';
			//$resData['resArr'] = $resArr;
			//echo json_encode($resData); die;
		}
	}

	public function analyticsReport($leadId) {
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		$lead_id = intval($this->encrypt->decode($leadId));

		$returnData = $this->Tasks->getAAconsentLog($lead_id, 6, ['b.*']);

		if (!empty($returnData)) {
			$reportData = json_decode($returnData->aa_response, true);
			$resData['status'] = true;
			$resData['message'] = 'Bank Analytics Report is Ready.';
			//$resData['data'] = $reportData['result']['data'];
			$resData['data'] = array("pdf" => $reportData['result']['pdf'], "excel" => $reportData['result']['excel']);
			echo json_encode($resData);
			die;
			//print_r($returnData); die;
		}

		$return_data = $this->Tasks->getAAconsentLog($lead_id, 5, ['a.mobile', 'b.aa_consentHandleId', 'b.aa_consentId', 'b.aa_sessionId', 'b.aa_response']);

		if (empty($return_data)) {
			$data = array('status' => false, 'message' => 'Consent request not found..');
			echo json_encode($data);
			die;
		}
		$reportData =  stripslashes($return_data->aa_response);;
		$reportData = json_decode($reportData, true);
		$linkRefNo = ($reportData['result']['body'][0]['fiObjects'][0]['linkedAccRef']) ? $reportData['result']['body'][0]['fiObjects'][0]['linkedAccRef'] : '';

		$this->load->helper('aa_api_curl');
		$mobile = $return_data->mobile;
		$consentHandleId = $return_data->aa_consentHandleId;
		$consentId = $return_data->aa_consentId;
		$sessionId = $return_data->aa_sessionId;
		$aa_request_datetime = date("Y-m-d H:i:s");
		$requestArray = array(
			"consentHandleId" => $consentHandleId,
			"sessionId" => $sessionId,
			"linkRefNo" => $linkRefNo,
			"pdf" => true,
			"excel" => true,
		);
		$json_request = json_encode($requestArray);
		$endUrl = 'accountAggregator/analytics-report';
		$response = sendCurl_request($json_request, $endUrl);
		//$response = '{"result":{"fiRequestStatus":"READY"}}';

		$resArr = json_decode($response, true);
		$apiStatus = isset($resArr['result']) ? 1 : 2;
		if (isset($resArr['result']['data']) && !empty($resArr['result']['data'])) {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 6,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_status_message' => 'Analytics Report Ready',
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = true;
			$resData['message'] = 'Analytics Report is Ready.';
			$resData['data'] = array("pdf" => $resArr['result']['pdf'], "excel" => $resArr['result']['excel']);
			echo json_encode($resData);
			die;
		} else {
			$apiAAlogs = [
				'aa_lead_id' => $lead_id,
				'aa_request' => $json_request,
				'aa_response' => $response,
				'aa_method_id' => 6,
				'aa_api_status_id' => $apiStatus,
				'aa_consentHandleId' => $consentHandleId,
				'aa_consentId' => $consentId,
				'aa_sessionId' => $sessionId,
				'aa_status_message' => 'Analytics Report Ready',
				'aa_request_datetime' => $aa_request_datetime,
				'aa_response_datetime' => date("Y-m-d H:i:s")
			];
			$aa_log_id = $this->Tasks->insert($apiAAlogs, "api_account_aggregator_logs");
			$resData['status'] = false;
			$resData['message'] = ($resArr['error']['message']) ? $resArr['error']['message'] : 'Not Ready.';
			$resData['resArr'] = $resArr;
			echo json_encode($resData);
			die;
		}
	}

	private function sendConsentRequest_url($customer_name, $account_aggregator_register_url, $email, $mobile) {
		$to = $email;
		$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
					<title>Account Aggregator</title>
				</head>
				<body style="padding: 0; margin: 0;">
					<table width="600" align="center" cellspacing="0" cellpadding="0" style="border: 1px solid #bebaba; background: url(images/bg_image.jpg);background-size: cover;">
						<tbody>
							<tr>
								<td align="center">
									<table style="text-align: center;"  bgcolor="" cellspacing="0"  style="border: 1px solid #bebaba;" border="0" width="600" cellpadding="0">
										<tbody>
											<tr>
												<td style="line-height: 0; padding-top: 0;">
													<a href="#/" target="_blank">
														<img src="' . WEBSITE_URL . 'public/images/banner_account_aggregator.jpg" alt="" width="600">
													</a>
												</td>
											</tr>
										</tbody>
									</table>
									<table style="text-align: center;"  bgcolor="" cellspacing="0"  style="background: url(images/bg_image.jpg);" border="0" width="600" cellpadding="0">
										<tbody>
											<tr>
												<td colspan="3" style="border-radius: 11px; color: #000; font-size: 13px;line-height: 18px; text-align: left;padding: 26px;">

													<span width="300" cellpadding="0">
														Dear ' . ucwords($customer_name) . ',
														<br/><br/>
														We thank you for showing interest in Paisa On Salary Instant personal loan.
														<br/><br/>Your application process is pending a crucial step, which involves obtaining your consent to access your salary bank account for retrieving the most recent bank statement.
														<br />
														In order to process your loan application further, please give your consent on our Account Aggregator portal to share your bank statement securely.
														<br /><br/>
														To facilitate the continued processing of your loan application, we kindly request your consent to securely share your bank statement through our Account Aggregator portal.
													</span>
												</td>
											</tr>
											<tr>
												<td colspan="3" style="border-radius: 11px; color: #000; font-size: 13px;line-height: 18px; text-align: center;padding: 10px;">
													<span width="300" cellpadding="0"><a href="' . $account_aggregator_register_url . '" style="border-radius: 20px;background-color: #df2b4d;border: none;color: #fff;font-size: 13px;font-weight: 600;padding: 5px 19px;margin: 2%;letter-spacing: 1px;text-decoration:none">Fetch Salary Account Bank Statement</a></span>
													<br />
													<br />
													<span width="300" cellpadding="0">If you are not able to click on the above button, then please copy and paste this URL ' . $account_aggregator_register_url . ' in the browser to proceed.</span>
												</td>
											</tr>
										</tbody>
									</table>
									<tr>
										<td colspan="3" style="border-radius: 11px; color: #000; font-size: 11px;
											line-height: 35px; text-align: center;">
											<b style="background-color: #000062;padding: 10px 10px 7px 10px;font-weight: 100;border-radius: 20px;">
												<a style="font-size: 11px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman;">  <img alt="Mobile: " src="' . PHONE_ICON . '"> ' . REGISTED_MOBILE . '</a> &nbsp;
												<a style="font-size: 11px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman">  <img alt="Webiste: " src="' . WEB_ICON . '"> ' . WEBSITE_URL . ' </a> &nbsp;
												<a style="font-size: 11px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman">  <img alt="Email: " src="' . EMAIL_ICON . '"> ' . INFO_EMAIL . '</a>
											</b><br/>
										</td>
									</tr>

								</td>
							</tr>
						</tbody>
					</table>
				</body>
			</html>';

		require_once(COMPONENT_PATH . 'includes/functions.inc.php');

		$return_array = common_send_email($to, BRAND_NAME . '  | CONSENT FOR BANK STATEMENT : ' . $customer_name, $message);

		if ($return_array['status'] == 1) {
			$lead_remark = "Account Aggregator email sent successfully.";
			$status = "true";
		} else {
			$lead_remark = "Account Aggregator email sending failed.";
			$status = "false";
		}

		return array('status' => $status, 'message' => $lead_remark);
	}


	function createBankStatement_from_fiData($data) {
		// Parse JSON data
		if (!$data || !isset($data['result']['body'][0]['fiObjects'][0])) {
			echo "Invalid or missing data in JSON";
			return;
		}

		$fiObject = $data['result']['body'][0]['fiObjects'][0];
		$transactions = $fiObject['Transactions']['Transaction'];
		$summary = $fiObject['Summary'];
		$profile = $fiObject['Profile']['Holders']['Holder'];
		$maskedAccNumber = $fiObject['maskedAccNumber'];

		// Prepare view data
		$accountHolder = $profile['name'] ?? 'N/A';
		$accountNumber = $maskedAccNumber ?? 'N/A';
		$currentBalance = $summary['currentBalance'] ?? 0;
		$startDate = $fiObject['Transactions']['startDate'] ?? 'N/A';
		$endDate = $fiObject['Transactions']['endDate'] ?? 'N/A';

		// Process and group transactions
		$groupedTransactions = [];
		$monthlyBalances = [];
		$allTransactions = [];
		$totalCredits = 0;
		$totalDebits = 0;
		$transactionCount = 0;

		foreach ($transactions as $t) {
			$date = explode('T', $t['transactionTimestamp'])[0];
			$year = substr($date, 0, 4);
			$month = substr($date, 0, 7);
			$amount = floatval($t['amount']);
			$type = strtolower($t['type']);
			$balance = floatval($t['currentBalance']);

			$transaction = [
				'date' => $date,
				'description' => $t['narration'] ?? 'N/A',
				'amount' => $amount,
				'type' => $type,
				'balance' => $balance,
			];

			$groupedTransactions[$year][$month][] = $transaction;
			$allTransactions[] = $transaction;
			$monthlyBalances[$month] = $balance;

			if ($type == 'credit') {
				$totalCredits += $amount;
			} elseif ($type == 'debit') {
				$totalDebits += $amount;
			}

			$transactionCount++;
		}

		$netChange = $totalCredits - $totalDebits;
		$avgTransaction = $transactionCount > 0 ? ($totalCredits + $totalDebits) / $transactionCount : 0;

		// Sort transactions by date (newest first)
		usort($allTransactions, function ($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});

		// Prepare transaction HTML
		$transactionHtml = '';
		foreach ($allTransactions as $t) {
			$transactionHtml .= '<tr>
            <td>' . $t['date'] . '</td>
            <td>' . $t['description'] . '</td>
            <td class="' . $t['type'] . '">₹' . number_format($t['amount'], 2) . '</td>
            <td>' . ucfirst($t['type']) . '</td>
            <td>₹' . number_format($t['balance'], 2) . '</td>
        </tr>';
		}

		// Prepare chart data
		$chartLabels = array_keys($monthlyBalances);
		$chartData = array_values($monthlyBalances);

		// Generate HTML
		$html = '<!DOCTYPE html>
					<html lang="en">
						<head>
							<meta charset="UTF-8">
							<meta name="viewport" content="width=device-width, initial-scale=1.0">
							<title><?php echo $accountHolder; ?> Bank Statement Dashboard</title>
							<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
							<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
							<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
							<style>
							body {
								font-family: Arial, sans-serif;
								line-height: 1.6;
								color: #333;
								max-width: 1200px;
								margin: 0 auto;
								padding: 20px;
								background-color: #f0f4f8;
							}

							.dashboard-header {
								background-color: #2c3e50;
								color: white;
								padding: 3px 20px;
								border-radius: 8px;
								margin-bottom: 20px;
							}

							.dashboard-header h1 {
								margin: 0;
							}

							.account-info {
								display: flex;
								justify-content: space-between;
								flex-wrap: wrap;
								margin-top: 10px;
							}

							.account-info div {
								flex: 1;
								min-width: 200px;
								margin: 5px;
							}

							.summary-cards {
								display: flex;
								justify-content: space-between;
								margin-bottom: 20px;
							}

							.summary-card {
								background-color: white;
								padding: 15px;
								border-radius: 8px;
								box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
								flex: 1;
								margin: 0 10px;
								text-align: center;
							}

							.summary-card h3 {
								margin-top: 0;
								color: #2c3e50;
							}

							.summary-card p {
								font-size: 1.5em;
								font-weight: bold;
								margin: 10px 0;
							}

							.dashboard-content {
								display: flex;
								flex-wrap: wrap;
								gap: 20px;
							}

							.chart-container,
							.transactions-container {
								flex: 1;
								min-width: 300px;
								background-color: white;
								border-radius: 8px;
								box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
								padding: 20px;
							}

							.chart-container {
								height: 400px;
							}

							.transactions-container {
								height: 600px;
								overflow-y: auto;
								position: relative;
							}

							.transaction-table {
								width: 100%;
								border-collapse: separate;
								border-spacing: 0;
							}

							.transaction-table thead {
								position: sticky;
								top: -20px;
								z-index: 1;
							}

							.transaction-table th {
								background-color: #f8f9fa;
								border-bottom: 2px solid #ddd;
							}

							.transaction-table th,
							.transaction-table td {
								padding: 12px;
								text-align: left;
							}

							.transaction-table tbody tr {
								border-bottom: 1px solid #ddd;
							}

							.transaction-table tbody tr:last-child {
								border-bottom: none;
							}

							.transaction-table tbody::before {
								display: block;
								height: 2px;
								background-color: #ddd;
								position: sticky;
								top: 43px; /* Adjust this value to match your th height */
								z-index: 1;
							}

							.credit {
								color: green;
							}

							.debit {
								color: red;
							}

							@media (max-width: 768px) {
								.summary-cards,
								.dashboard-content {
									flex-direction: column;
								}

								.summary-card,
								.chart-container,
								.transactions-container {
									margin-bottom: 20px;
								}
							}
						</style>
						</head>
					<body>
						<div class="dashboard-header">
							<h2>Bank Statement</h2>
							<div class="account-info">
								<div><strong>Account Holder:</strong> ' . htmlspecialchars($accountHolder) . '</div>
								<div><strong>Account Number:</strong> ' . htmlspecialchars($accountNumber) . '</div>
								<div><strong>Current Balance:</strong> ₹' . number_format($currentBalance, 2) . '</div>
								<div><strong>Statement Period:</strong> ' . $startDate . ' to ' . $endDate . '</div>
							</div>
						</div>

						<div class="summary-cards">
							<div class="summary-card">
								<h3>Total Credits</h3>
								<p>₹' . number_format($totalCredits, 2) . '</p>
							</div>
							<div class="summary-card">
								<h3>Total Debits</h3>
								<p>₹' . number_format($totalDebits, 2) . '</p>
							</div>
							<div class="summary-card">
								<h3>Net Change</h3>
								<p>₹' . number_format($netChange, 2) . '</p>
							</div>
							<div class="summary-card">
								<h3>Avg. Transaction</h3>
								<p>₹' . number_format($avgTransaction, 2) . '</p>
							</div>
						</div>

						<div class="dashboard-content">
							<div class="transactions-container">
								<table class="transaction-table">
									<thead>
										<tr>
											<th>Date</th>
											<th>Description</th>
											<th>Amount</th>
											<th>Type</th>
											<th>Balance</th>
										</tr>
									</thead>
									<tbody>
										' . $transactionHtml . '
									</tbody>
								</table>
							</div>
						</div>

					</body>
					</html>';

		return $html;
	}

	public function __destruct() {
		$this->db->close();
	}
}
