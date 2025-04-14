<?php

defined('BASEPATH') OR exit('No direct script access allowed');

header('Content-type: text/html; charset=UTF-8');

class CartController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
    }

    public function index() {
        $this->load->view('Tasks/bank');
    }

    public function bankAnalysis() {
        if ($this->input->post('user_id') == "") {
            $json['errSession'] = "Session Expired.";
            echo json_encode($json);
        }
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|trim');
            $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|trim');
            if ($this->form_validation->run() == FALSE) {
                $json['err'] = validation_errors();
                echo json_encode($json);
            } else {
                $lead_id = intval($this->encrypt->decode($this->input->post('lead_id')));
                $customer_id = intval($this->input->post('customer_id'));
                $fetch = 'D.docs_id, D.lead_id, D.customer_id, D.docs_type, D.docs_type, D.file, D.pwd';
                $conditions = ['D.customer_id' => $customer_id, 'D.docs_type' => 'BANK STATEMENT'];
                $table1 = 'docs D';
                $table2 = 'customer C';
                $join2 = 'C.customer_id = D.customer_id';
                $order_by_key = 'D.docs_id';
                $order_by_val = 'desc';
                $docs = $this->Tasks->join_two_table_with_where_order_by($conditions, $fetch, $table1, $table2, $join2, $order_by_key, $order_by_val);
                $document = $docs->row();
                $filename = $document->file;
                $password = $document->pwd;

                $url = 'https://cartbi.com/api/upload';
                // $Auth_Token = "LIVE";
                $Auth_Token = "UAT";
                if ($Auth_Token == "UAT") {
                    define('api_token', 'API://IlJKyP5wUwzCvKQbb796ZSjOITkMSRN8rifQTMrNM1/NUUv8/tuaN6Lun6d1NG4S');
                } else {
                    define('api_token', 'API://9jwoyrhfdtDuDt0epG4VsisYdBHMsZMGC7IlUhwN8t1Qb2bgwxFqrn7K0LgWIly1');
                }

                // $cartJsonData = [ 'file' => new CURLFile (FCPATH.'public/BankAnalysis/'.$filename, '', $filename)];
                $filePath = FCPATH . 'upload/' . $filename;
                $cartJsonData = ['file' => new CURLFile($filePath, '', $filename)];
                $headers = [
                    'Content-Type: multipart/form-data',
                    'auth-token: ' . api_token
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $cartJsonData);

                $UploadResponse = curl_exec($ch);

                curl_close($ch);
                $response = json_decode($UploadResponse);
                // echo "<pre>"; print_r($response); exit;

                if (!empty($response->docId)) {
                    $docId = $response->docId;
                    $status = $response->status;
                    $message = $response->message;

                    // if($status == 'Submitted'){
                    //     $status = 'In Process';
                    // }
                    // $docId = "DOC00231319";
                    $urlCart_callback = 'https://cartbi.com/api/cart_callback';
                    $header3 = [
                    'Content-Type: text/plain',
                    'MediaType: APPLICATION_FORM_URLENCODED',
                    'MediaType: TEXT_PLAIN'
                    'auth-token: '. api_token
                    ];
                    $dataCalback = [
                        'docId' => $docId,
                        'requestId' => null,
                        'status' => $status,
                        'reportFileName' => $filename,
                        'endTime' => '',
                        'message' => $message,
                        'fileNo' => ''
                    ];

                    $ch2 = curl_init($urlCart_callback);
                    curl_setopt($ch2, CURLOPT_HTTPHEADER, $header3);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch2, CURLOPT_POSTFIELDS, $docId);

                    $downloadCartData = curl_exec($ch2);
                    curl_close($ch2);

                    $data = array(
                        'lead_id' => $lead_id,
                        'docId' => $docId,
                        'file' => $filename,
                        'password' => $password,
                        'cartJson' => json_encode($cartJsonData),
                        'cartJsonResponse' => $UploadResponse,
                        'status' => $status,
                        'ip' => ip,
                        'created_by' => $_SESSION['isUserSession']['user_id'],
                    );

                    $this->db->insert('tbl_cart', $data);

                    $json['msg'] = $docId;

                    ///////////////////////////// download cart data as csv ///////////////////////////////////////
                    // $docId = "DOC00231319";
                    $urlDownload = 'https://cartbi.com/api/downloadFile';
                    $header2 = [
                        'Content-Type: text/plain',
                        'auth-token: ' . api_token
                    ];

                    $ch2 = curl_init($urlDownload);
                    curl_setopt($ch2, CURLOPT_HTTPHEADER, $header2);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch2, CURLOPT_POSTFIELDS, $docId);

                    $downloadCartData = curl_exec($ch2);
                    curl_close($ch2);
                    $this->db->where('lead_id', $lead_id)->where('docId', $docId)->update('tbl_cart', ['downloadCartData' => $downloadCartData]);
                } else {
                    $json['err'] = "Failed to Upload Docs.";
                }
                echo json_encode($json);
            }
        }
    }

    public function ViewBankingAnalysis() {
        $json = '';
        $doc_id = $_POST['doc_id'];

        $query = $this->db->select('tbl_cart.downloadCartData')->where('docId', $doc_id)->get('tbl_cart')->row();

        $response = 1;
        if (empty($query->downloadCartData)) {
            $response = $this->Tasks->DownloadBankingAnalysis($doc_id); // 1
        }
        if ($response > 0) {
            $result = $this->Tasks->ViewBankingAnalysis($doc_id);
            // echo $result;exit;
            // $data       = $result->row_array();
            // $num_rows   = $result->num_rows();
            // if($num_rows > 0)
            // {   
            //     // foreach ($data as $key => $value){
            //     //     $json = $value['downloadCartData'];
            //     // }
            //     $json = $data['downloadCartData'];
            // } else{
            //     $json = 0;
            // }
            // echo json_encode($json);
            echo json_encode($result);
        }
    }

    public function getBankAnalysis($lead_id) {
        $lead_id = intval($this->encrypt->decode($lead_id));
        $data = $this->Tasks->bank_analiysis($lead_id);
        echo json_encode($data);
    }

    public function downloadCartFile($docId) {
        // $docId = "DOC00231319";
        $urlDownload = 'https://cartbi.com/api/downloadFile';
        $header2 = [
            'Content-Type: text/plain',
            'auth-token: ' . api_token
        ];

        $ch2 = curl_init($urlDownload);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $header2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $docId);

        $downloadCartData = curl_exec($ch2);
        curl_close($ch2);
        $this->db->where('lead_id', $lead_id)->where('docId', $docId)->update('tbl_cart', ['downloadCartData' => $downloadCartData]);
    }

}

?>
