<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MiscellaneousController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('email');
        $this->db2 = $this->load->database('second', TRUE); // TRUE returns the DB object

    }   
    
      public function transferToFp(){
        $user_ids = array(244, 245); // array of user ids from Fastpaise
        $new_user_id = $user_ids[array_rand($user_ids)];   
        $new_lead_id = 0;
        $pancard = "";
        $user_id = 0;

        $input_data = file_get_contents("php://input");
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        
        $lead_id = $_POST['lead_id'] ?? null;
        
        if (!$lead_id) {
            echo json_encode(["status" => 0, "err" => "lead_id is required"]);
            return;
        }

        // Step 1: Fetch lead
        $lead = $this->db->query('SELECT * FROM leads WHERE lead_id = ?', [$lead_id])->row();
        if (!$lead) {
            echo json_encode(["status" => 0, "err" => "Lead not found in DB1"]);
            return;
        }
        $leadData = (array) $lead;

        // Step 2: Check if lead already exists in DB2
        $pancard = $leadData['pancard'];
        $lead2 = $this->db2->query('SELECT * FROM leads WHERE pancard = ? AND lead_status_id NOT IN (16, 8, 9) ORDER BY lead_id DESC LIMIT 1', [$pancard])->row();

        if ($lead2) {
            echo json_encode(["status" => 0, "err" => "Lead already active in fastpaise", "lead_id" => $lead2->lead_id]);
            return;
        }

        //Step 3: Checking lead status
        $lead_status_id = $leadData['lead_status_id'];
        if(in_array($lead_status_id, [2, 3])){
            $user_id = $leadData['lead_screener_assign_user_id'];
        }else if(in_array($lead_status_id, [5, 6])){
            $user_id = $leadData['lead_credit_assign_user_id'];
        }else{
            $user_id = 0;
        }

        if($user_id == 0){
            echo json_encode(["status" => 0, "err" => "User not found in DB1"]);
            return;
        }

        // // Step 4: Fetch user id for DB2
        // $sql = "SELECT * FROM master_map_users WHERE s4s_user_id = $user_id";
        
        // $user = $this->db->query($sql)->row();

        // if (!$user) {
        //     echo json_encode(["status" => 0, "error" => "User is not mapped in DB1"]);
        //     return;
        // }
        // $new_user_id = $user->fp_user_id;

        // Step 5: Insert lead into DB2
        $leadData['lead_id'] = ""; // unset primary key
        $leadData['lead_reference_no'] = ""; // unset lead_reference_no
        if(in_array($lead_status_id, [2, 3])){
            $leadData['lead_screener_assign_user_id'] = $new_user_id;
            $leadData['lead_credit_assign_user_id'] = NULL;
            $leadData['lead_credit_assign_datetime'] = NULL;

        }else if(in_array($lead_status_id, [5, 6])){
            $leadData['lead_screener_assign_user_id'] = $new_user_id;
            $leadData['lead_credit_assign_user_id'] = $new_user_id;
        }
        $leadData['transfered_flag'] = 2;
        $insertLead = $this->insertIntoTable($leadData, 'leads');

        if (!$insertLead) {
            echo json_encode(["status" => 0, "err" => "Failed to insert leads into DB2"]);
            return;
        }

        //Step 6: Get new lead_id from DB2
        $newLead = $this->db2->query('SELECT lead_id FROM leads ORDER BY lead_id DESC LIMIT 1')->row();
        if (!$newLead || !$newLead->lead_id) {
            echo json_encode(["status" => 0, "err" => "Failed to fetch new lead_id from DB2"]);
            return;
        }

        // Saved new lead_id
        $new_lead_id = $newLead->lead_id;


        // Step 7: lead_customer
        $customer = $this->db->query('SELECT * FROM lead_customer WHERE customer_lead_id = ?', [$lead_id])->row();

        if ($customer) {
            $custData = (array) $customer;
            $custData['customer_lead_id'] = $new_lead_id;
            $custData['customer_seq_id'] = '';

            if (!$this->insertIntoTable($custData, 'lead_customer')) {
                echo json_encode(["status" => 0, "err" => "Failed to insert lead_customer into DB2"]);
                return;
            }
        }

        // Step 8: customer_employment
        $employment = $this->db->query('SELECT * FROM customer_employment WHERE lead_id = ?', [$lead_id])->row();
        if ($employment) {
            $empData = (array) $employment;
            $empData['lead_id'] = $new_lead_id;
            $empData['id'] = '';

            if (!$this->insertIntoTable($empData, 'customer_employment')) {
                echo json_encode(["status" => 0, "err" => "Failed to insert customer_employment into DB2"]);
                return;
            }
        }

        // Step 9: customer_banking
        $banking = $this->db->query('SELECT * FROM customer_banking WHERE lead_id = ?', [$lead_id])->row();
        if ($banking) {
            $bankData = (array) $banking;
            $bankData['lead_id'] = $new_lead_id;
            $bankData['id'] = '';
            if (!$this->insertIntoTable($bankData, 'customer_banking')) {
                echo json_encode(["status" => 0, "err" => "Failed to insert customer_banking into DB2"]);
                return;
            }
        }


       $sql = "UPDATE leads SET lead_status_id = 9 , status = 'REJECT', stage = 'S9',
       lead_rejected_reason_id = 62, lead_rejected_datetime = now()
        WHERE lead_id = ?";
       $res = $this->db->query($sql,[$lead_id]);
       
        echo json_encode(["status" => 1, "message" => "Success", "new_lead_id" => $new_lead_id]);
    }



    public function generateCifNumberForDb2(){

        $last_row = $this->db2->select('cif_id as customer_id')->from('cif_customer')->order_by('cif_id', 'desc')->limit(1)->get();
        $customer_id = strtoupper(substr("DUN", 0, 3));
        if (empty($last_row->num_rows())) {
            $customer_id .= "00000001";
        } else {
            $str = preg_replace('/\D/', '', ($last_row->row())->customer_id);
            $customer_id .= str_pad(($str + 1), 8, "0", STR_PAD_LEFT); // FTC00000004
        }

        return $customer_id;
    }

  public function insertIntoTable($data,$table_name,$db_type = 2){
        //insert data in test table
        // $data = (array) $result;
        // $data['lead_id'] = "";
        $table = $table_name;  // or whatever your table name is
        

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($data);

        $sql = "INSERT INTO `$table` (" . implode(',', $columns) . ")
                VALUES (" . implode(',', $placeholders) . ")";

        if($db_type == 1){
            $result = $this->db->query($sql, $values);
            return $result;
        }else if($db_type == 2){
            $result = $this->db2->query($sql, $values);
            return $result;
        }else if($db_type == 3){
            $result = $this->db3->query($sql, $values);

            return $result;
        }
        
    }

    public function generateLoanNoForDb2(){
        $q = $this->db2->select('L.loan_no')->where('L.loan_no !=', '')->from('loan L')->order_by('loan_id', 'desc')->limit(1)->get();
            $pre_loan = $q->row();

            $num1 = (int) filter_var($pre_loan->loan_no, FILTER_SANITIZE_NUMBER_INT);
            $num1 = $num1 + 1;

            $prefix_loan_no = "AFLDUN";

            $envSet = ENVIRONMENT;

            if ($envSet == "production") {
                $prefix_loan_no = "AFLDUN";
            }

            $loan_no = $prefix_loan_no . str_pad(($num1), 11, "0", STR_PAD_LEFT); //16 chars

            return $loan_no;    

    }

}
