<?php

use Exception;

defined('BASEPATH') OR exit('No direct script access allowed');

class ShiftCibilController extends CI_Controller {

    private $context;

    public function __construct()
    {
        parent::__construct();
        require_once(COMP_PATH . "/classes/model/DatabaseConn.class.php");
        $this->context = new DatabaseConn();
        $this->load->model('Task_Model', 'Tasks');
    }


    public function shiftToS3()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $bankStatementDetails = $this->Tasks->select(['cart_lead_id' => '88454788'], 'cart_api_status_id,cart_errors', 'api_banking_cart_log');
        $bankStatementDetails = $bankStatementDetails->result();
        $bankStatementDetails = end($bankStatementDetails);
        echo '<pre>';
        print_r($bankStatementDetails->cart_api_status_id);
        die('test');
        // try {
        //     $sqlQuery = " SELECT * FROM `tbl_cibil` WHERE `s3_flag` IS NULL AND `cibil_file` != ''  LIMIT 20";
        //     $tempDetails = $this->context->query($sqlQuery);
        //    if (!empty($tempDetails['items'])) {
        //         $cibilData = $tempDetails['items'];
        //         require_once(COMPONENT_PATH . 'CommonComponent.php');
        //         require_once(COMP_PATH . '/includes/integration/integration_config.php');
        //         $commonComponent = new CommonComponent();
        //         foreach ($cibilData as $cibilDatum) {
        //             $leadId =  $cibilDatum['lead_id'];
        //             $cibilId = $cibilDatum['cibil_id'];
        //             $fileName = $leadId.'.html';
        //             $request_array['folderName'] = 'cibil';
        //             $request_array['fileName'] = $fileName;
        //             $request_array['htmlContent'] = $cibilDatum['cibil_file'];
        //             $request_array['upload_type'] = 'cibil';
        //             $s3Response = $commonComponent->upload_document($leadId, $request_array);
        //             $updateCibilQuery = "UPDATE `tbl_cibil` SET `s3_flag` = 1, `cibil_file` = '$fileName' WHERE `cibil_id` = $cibilId";
        //             $this->context->query($updateCibilQuery);
        //             $updateCibilLogQuery = "UPDATE `tbl_cibil_log` SET `s3_flag` = 1, `cibil_file` = '$fileName' WHERE `lead_id` = $leadId";
        //             $this->context->query($updateCibilLogQuery);
        //             echo '<pre>';
        //             echo 'Lead Id:' . $leadId . ', FileName:' . $fileName;
        //             echo '</pre>';
        //         }
        //         return [
        //             'success' => true,
        //             'message' => 'Data uploaded successfully!'
        //         ];
        //    }
        //    echo 'No Data found!';
        //    return [
        //     'success' => true,
        //     'message' => 'No Data found to upload!'
        // ];
        // } catch (Exception $exception) {
        //     echo 'Error' . $exception->getMessage();
        //     return [
        //         'success' => false,
        //         'message' => $exception->getMessage()
        //     ];
        // }
    }
}