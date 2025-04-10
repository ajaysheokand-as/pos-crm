<?php
require_once APPPATH . 'third_party/tcpdf/tcpdf.php';

class Pdf extends TCPDF
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createPDF($html, $filename, $download = true)
    {
        ob_clean(); // Clean the output buffer
        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=document.pdf");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header('Content-Type: text/html; charset=utf-8');

        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->SetFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Suuny Kumar');
        $this->SetTitle($filename);
        $this->SetHeaderData('', '', 'Salarywalle', 'PDF Generation');
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(TRUE, 10);
        $this->AddPage();
        $this->writeHTML($html, true, false, true, false, '');
        
        if ($download) {
            $this->Output($filename, 'D'); // Download
        } else {
            $this->Output($filename, 'F'); // Save to folder
        }
    }
}
?>
