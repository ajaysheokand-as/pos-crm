<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MyPincode_Model extends CI_Model {
    
        function __construct() {
        parent::__construct();
    }

       public function is_duplicate($m_pincode_valuel) {
        $this->db->select('m_pincode_id');
        $this->db->from('master_pincode'); 
        $this->db->where('m_pincode_value', $m_pincode_value);
        $query = $this->db->get();

      
        return $query->num_rows() > 0;
    }


   
    public function insert_data($data) {
        return $this->db->insert('master_pincode', $data); 
    }
    
    
}
?>
