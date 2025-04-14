<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BlacklistedPincode_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    } 
	
    public function getBlacklistedPincodeDetails($blacklist_pincode_id) {
        $status = 0;
        $blacklisted_pincode_data = array();
        $tempDetails = $this->db->select('*')->from('master_blacklist_pincode')->where(["mbp_id"=>$blacklist_pincode_id])->get();
        if (!empty($tempDetails->num_rows())) {
            $blacklisted_pincode_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "blacklisted_pincode_data" => $blacklisted_pincode_data);
    }
    
    public function checkBlacklistedPincode($pincode,$blacklist_pincode_id=0) {
        $return_val = false;
        $condition = array();
        $condition["LOWER(mbp_pincode)"] = trim(strtolower($pincode));
        if(!empty($blacklist_pincode_id)) {
           $condition["mbp_id!="] = $blacklist_pincode_id;		   
        }
		$condition["mbp_active="] = 1;
		$condition["mbp_deleted="] = 0;
        $tempDetails = $this->db->select('mbp_pincode')->from('master_blacklist_pincode')->where($condition)->get();
        if($tempDetails->num_rows()) {
            $return_val = true;
        }
        return $return_val;
    }

    public function blacklistedPincodeList($limit, $start = null, $conditions = array()) {
        $this->db->select('*');
        $this->db->from("master_blacklist_pincode");
        $this->db->distinct();
        $this->db->limit($limit,$start);
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('mbp_active',1);
        $this->db->where('mbp_deleted',0);
        $return = $this->db->order_by('mbp_id','desc')->get()->result_array();
        return $return;
    }

    public function blacklistedPincodeCount($conditions) {
        $this->db->select("mbp_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('mbp_active', 1);
        $this->db->where('mbp_deleted', 0);
        return $this->db->from('master_blacklist_pincode')->get()->num_rows();
    }
}
?>
