<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class DocsController extends CI_Controller {

    public $tbl_leads = 'leads';
    public $tbl_customer = 'lead_customer';
    public $tbl_docs = 'docs';

    public function __construct() {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');

        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Docs_Model', 'Docs');
    }

    public function error_page() {
        $this->load->view('errors/index');
    }

    public function index() {
        
    }

    public function viewUploadedDocument($document_id, $document_type = 0, $file_dwnl_flag = 1, $email_flag = 0) {

        $lead_id = 0;

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $this->session->set_flashdata('err', "Session Expired, Try once more.");
            return redirect(base_url("logout"));
        }

        if ($document_type == 1) {//docs table
            $docs_return_array = $this->Docs->getDocumentDetails($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                $lead_id = $documentDetails['lead_id'];

                if (!empty($documentDetails['file'])) {
                    if ($file_dwnl_flag = 0) {
                        downloadDocument($documentDetails['file'], 0);
                    } else {
                        echo downloadDocument($documentDetails['file'], 1);
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 2) {//collection table
            $docs_return_array = $this->Docs->getCollectionDocumentDetails($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['docs'])) {
                    if ($file_dwnl_flag = 0) {
                        downloadDocument($documentDetails['docs'], 0);
                    } else {
                        echo downloadDocument($documentDetails['docs'], 1);
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 3) {//esginsanction letter download
            $docs_return_array = $this->Docs->getSanctionLetterPdf($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['cam_sanction_letter_esgin_file_name'])) {
                    if ($file_dwnl_flag = 0) {
                        downloadDocument($documentDetails['cam_sanction_letter_esgin_file_name'], 0);
                    } else {
                        echo downloadDocument($documentDetails['cam_sanction_letter_esgin_file_name'], 1);
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 4) {//sanction letter download
            $docs_return_array = $this->Docs->getSanctionLetterPdf($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['cam_sanction_letter_file_name'])) {
                    if ($file_dwnl_flag = 0) {
                        downloadDocument($documentDetails['cam_sanction_letter_file_name'], 0);
                    } else {
                        echo downloadDocument($documentDetails['cam_sanction_letter_file_name'], 1);
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 5) {//collection followup docs - FE Selfie
            $docs_return_array = $this->Docs->getCollectionFollowupDocumentDetails($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['lcf_fe_upload_selfie'])) {
                    if ($file_dwnl_flag = 0) {
                        downloadDocument($documentDetails['lcf_fe_upload_selfie'], 0);
                    } else {
                        echo downloadDocument($documentDetails['lcf_fe_upload_selfie'], 1);
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 6) {//collection followup docs - fe upload_location
            $docs_return_array = $this->Docs->getCollectionFollowupDocumentDetails($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['lcf_fe_upload_location'])) {
                    $document_full_path = UPLOAD_PATH . $documentDetails['lcf_fe_upload_location'];

                    if (file_exists($document_full_path)) {
                        $this->downloadFile($document_full_path, $documentDetails['lcf_fe_upload_location'], $file_dwnl_flag);
                    } else {
                        echo "Document not found.";
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else if ($document_type == 7) {//collection followup docs - fe upload_location
            $docs_return_array = $this->Docs->getCollectionVisitDocumentDetails($document_id);

            if ($docs_return_array['status'] == 1) {

                $documentDetails = $docs_return_array['doc_data'];

                if (!empty($documentDetails['col_fe_rtoh_upload_selfie'])) {
                    $document_full_path = UPLOAD_PATH . $documentDetails['col_fe_rtoh_upload_selfie'];

                    if (file_exists($document_full_path)) {
                        $this->downloadFile($document_full_path, $documentDetails['col_fe_rtoh_upload_selfie'], $file_dwnl_flag);
                    } else {
                        echo "Document not found.";
                    }
                } else {
                    echo "File not found.";
                }
            } else {
                echo "Document not found";
            }
        } else {
            echo "Document type not found";
        }
    }

    public function donwloadUploadedDocument($document_id, $document_type = 0, $flag = 0, $email_flag = 0) {
        $this->viewUploadedDocument($document_id, $document_type, $flag, $email_flag);
    }

    public function directViewDocument($file_name) {

        //$document_full_path = UPLOAD_PATH . $file_name;
        //if (file_exists($document_full_path)) {
        echo downloadDocument($file_name, 1);
        //} else {
        //  echo "Document not found.";
        //}
    }

    private function downloadFile($filefullPath = '', $fileName = '', $fileShow = false, $email_flag = false, $lead_id = 0, $document_id = 0) {

        if (file_exists($filefullPath)) {

            $mime_type = $this->rp_mime_content_type($filefullPath);

            $allowed_file_types = array("image/jpeg", "image/jpg", "image/pjpg", "image/pjpeg", "image/gif", "image/png", "application/pdf", "image/tiff");
            if (in_array($mime_type, $allowed_file_types)) {
                header('Pragma: public');
                header('Cache-Control: max-age=120, public');
                header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 120));
                header("Content-type: $mime_type");
                if (!$fileShow) {
                    header("Content-Disposition: attachment;filename=" . $fileName);
                }
                header('Content-length: ' . filesize($filefullPath));
                readfile($filefullPath);
                flush();

                if ($fileShow == false) {
                    $this->saveDocumentDownloadLogs($lead_id, $document_id);
                }

                if ($email_flag == true && $fileShow == false) {
                    $this->send_email_download_document($lead_id, $document_id);
                }
            } else {
                die('File Not Found..');
            }
        } else {
            die('File Not Found');
        }
        exit;
    }

    private function rp_mime_content_type($file_full_path) {
        return get_mime_by_extension($file_full_path);
//        return trim(exec('file -b --mime-type ' . escapeshellarg($file_full_path)));
    }

    private function send_email_download_document($lead_id = 0, $document_id = 0) {
        $user_id = $_SESSION['isUserSession']['user_id'];
        $user_name = $_SESSION['isUserSession']['name'];
        $user_email = $_SESSION['isUserSession']['email'];

        $email_subject = BRAND_NAME . " FINTECH DOCUMENT DOWNLOAD | $user_name | DOWNLOAD TIME : " . date("d-m-Y H:i:s");

        $email_message = "<table width='650' border='1' cellspacing='0' cellpadding='0' style='border:1px solid #000'>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top' colspan='2'><strong>&nbsp;Dear Team,</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;URL</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . base_url() . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;NAME</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $user_name . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;EMAIL</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $user_email . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Action IP</strong></td>
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
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;LEAD ID</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $lead_id . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;DOCUMENT ID</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $document_id . "</strong></td>
                    	          </tr>
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;DOCUMENT DOWNLOAD</strong></td>
                    	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . date('d-m-Y H:i:s') . "</strong></td>
                    	          </tr>
                    
                    	          <tr bgcolor='#ededed'>
                    	            <td height='20' style='color:#000;' valign='top' colspan='3'><strong>&nbsp;If you have any query regarding login.<br>Contact us on email - tech.team@loanwalle.com</strong></td>
                    	          </tr>
                    	        </table>";

        //lw_send_email("meena.joshi@loanwalle.com", $email_subject, $email_message, 'shubham.agrawal@loanwalle.com,alok.nautiyal@loanwalle.com');
        lw_send_email(CTO_EMAIL, $email_subject, $email_message);
    }

    private function saveDocumentDownloadLogs($lead_id = 0, $document_id = 0) {
        $user_id = $_SESSION['isUserSession']['user_id'];
        $user_role_id = $_SESSION['isUserSession']['user_role_id'];

        $insert_ddl_array = array();

        $insert_ddl_array["ddl_lead_id"] = $lead_id;
        $insert_ddl_array["ddl_document_id"] = $document_id;
        $insert_ddl_array["ddl_user_id"] = $user_id;
        $insert_ddl_array["ddl_user_role_id"] = $user_role_id;
        $insert_ddl_array["ddl_user_platform"] = $this->agent->platform();
        $insert_ddl_array["ddl_user_browser"] = $this->agent->browser() . ' ' . $this->agent->version();
        $insert_ddl_array["ddl_user_agent"] = $this->agent->agent_string();
        $insert_ddl_array["ddl_user_ip"] = $this->input->ip_address();
        $insert_ddl_array["ddl_created_on"] = date("Y-m-d H:i:s");

        $this->Docs->insertDocumentDownloadLogs($insert_ddl_array);
    }

    public function uploadSanctionLetter() {

        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
            exit;
        }

        $enc_lead_id = $this->input->post('lead_id');
        $lead_id = intval($this->encrypt->decode($enc_lead_id));
        $user_id = intval($this->input->post('user_id'));

        if (!empty($lead_id)) {
            if (!empty($_FILES['file_name']['name'])) {

                $upload_return = uploadDocument($_FILES, $lead_id);

                if ($upload_return['status'] == 1) {

                    $file_name = $upload_return['file_name'];

                    $updateData = array(
                        'lead_id' => $lead_id,
                        'cam_sanction_letter_file_name' => $file_name,
                        'updated_by' => $user_id,
                        'updated_at' => date("Y-m-d H:i:s")
                    );
                    $this->db->where(['lead_id' => $lead_id])->update('credit_analysis_memo', $updateData);
                    $json['msg'] = 'Sanction Letter uploaded successfully.';
                    $this->Task->sendSanctionMail($enc_lead_id);
                    echo json_encode($json);
                } else {
                    $json['err'] = "Failed to upload. Try Again...";
                    echo json_encode($json);
                }
            } else {
                $json['err'] = "Failed to upload. Try Again.";
                echo json_encode($json);
            }
        } else {
            $json['err'] = "Failed to upload. Try Again." . $lead_id;
            echo json_encode($json);
        }
    }

    public function __destruct() {
        $this->db->close();
    }

}
