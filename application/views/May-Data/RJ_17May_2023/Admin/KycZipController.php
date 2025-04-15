<?php

defined('BASEPATH') or exit('No direct script access allowed');

class KycZipController extends CI_Controller {

    private $master_fy_folder = [1 => 'kyc_fin_202104_202203/', 2 => 'kyc_fin_202204_202303/'];

    public function __construct() {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');
        $this->load->library('zip');
        $this->load->model('Task_Model', 'Tasks');

        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        if (agent == 'LD1') {
            $this->load->view('KycZip/index');
        } else {
            echo "INVALID ACCESS";
            die;
        }
    }

    public function download_loandocs_zip() {

        if (agent == 'LD1') {

            $loan_no = strtoupper(trim($_POST['loan_no']));

            $fy_year = intval($_POST['fy_year']);

            try {

                if (empty($loan_no)) {
                    throw new Exception("Please enter the loan number.");
                }

                if (empty($this->master_fy_folder[$fy_year])) {
                    throw new Exception("Please select the financial year.");
                }

                $document_path = LOANS_KYC_DOCS;
                $document_path .= $this->master_fy_folder[$fy_year];
                $document_path .= $loan_no . "/";

                if (!is_dir($document_path)) {
                    throw new Exception("KYC Document not avialble");
                }

                $read_dir_files = scandir($document_path);

                unset($read_dir_files[0], $read_dir_files[1]);

                foreach ($read_dir_files as $file_name) {
                    $this->zip->read_file($document_path . $file_name);
                }

                // Download
                $filename = $loan_no . ".zip";

                //$this->sent_zip_email($loan_no);

                $this->zip->download($filename);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $this->session->set_flashdata('error', $errorMessage);
                return redirect(base_url('loan-kyc-docs'), 'refresh');
            }
        } else {
            echo "INVALID ACCESS";
            die;
        }
    }

    private function sent_zip_email($loan_no) {
        $user_name = $_SESSION['isUserSession']['name'];
        $user_email = $_SESSION['isUserSession']['email'];

        $email_subject = "FINTECH LOAN KYC DOWNLOAD | " . $user_name . "| TIME : " . date("d-m-Y H:i:s");

        $email_message = "<table width='650' border='1' cellspacing='0' cellpadding='0' style='border:1px solid #000'>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top' colspan='2'><strong>&nbsp;Dear $user_name,</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;URL</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . base_url() . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Login User</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;$user_email</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;LOAN NO</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;$loan_no</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;IP</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->input->ip_address() . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Platform</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->platform() . "</strong></td>
                    	          </tr>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Browser & Version</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->browser() . ' ' . $this->agent->version() . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Agent String</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->agent_string() . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Last Activity</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . date('d-m-Y H:i:s') . "</strong></td>
                    	          </tr>
                    
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top' colspan='3'><strong>&nbsp;If you have any query regarding login.<br>Contact us on email - tech.team@loanwalle.com</strong></td>
                    	          </tr>
                    	        </table>";

        lw_send_email(TO_KYC_DOCS_ZIP_DOWNLOAD_EMAIL, $email_subject, $email_message);
    }

}
