<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Rejection_Model extends CI_Model {

    private $table = 'tbl_rejection_master';

    public function getRejectionReasonMaster($where) {
        return $this->db->select('*')->where($where)->from($this->table)->get();
    }

    public function send_rejection_mail($lead_id, $lead_data, $to_email) {
        $subject = BRAND_NAME . " | Application Status : Rejected";

        $message = '<p>Dear Customer,</p>';
        $message .= '<p>Greetings of the day.</p>';
        $message .= '<p>Thank you for your recent loan application with ' . BRAND_NAME . '.</p>';
        $message .= '<p>Your request for a loan was carefully considered, and we regret that we are unable to approve your application at this time.</p>';
        $message .= '<p>Please note that this rejection does not reflect upon your financial status or spending pattern alone but the decision is made as a result of multiple checks on multiple parameters pre-defined for any such application.</p>';
        $message .= '<p>Request you to re-apply after some time in the future for a re-assessment of your application.</p>';
        $message .= '<br/>';
        $message .= '<p>Best Regards</p>';
        $message .= '<p>Team ' . BRAND_NAME . '</p>';
        $message .= '<p>(' . COMPANY_NAME . ')</p>';

        $result = lw_send_email($to_email, $subject, $message);
        return $result;
    }

}

?>
