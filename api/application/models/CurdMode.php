<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CurdMode extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Kolkata");
    }

    public function globel_inset($table, $data) {
        return $this->db->insert($table, $data);
        //echo $this->db->last_query(); 
    }

    public function globel_update($table, $data, $upd_id, $colm) {
        $this->db->where($colm, $upd_id);
        $res = $this->db->update($table, $data);
        //echo $this->db->last_query(); //die;
        return $res;
    }

    public function check_validationToken($token) {
        $currentdate = date('Y-m-d H:i:s');

        $selquery = "SELECT mlt_id,mlt_user_id,mlt_valid_datetime,mlt_token_valid_time FROM mobileapp_login_trans where mlt_token='$token' and mlt_valid_datetime>='$currentdate'  ";
        $query = $this->db->query($selquery);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    public function globel_select($query) {
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

}
