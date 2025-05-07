<?php

defined('BASEPATH') or exit('No direct script access allowed');

class InstantLoan_Model extends CI_Model
{
    private $table = 'instant_loan_campaign';

    function __construct()  
    {
        parent::__construct();
    }

    public function insertData($data)
    {
        return $this->db->insert($this->table, $data);
    }
}