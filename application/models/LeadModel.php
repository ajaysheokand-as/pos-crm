<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LeadModel extends CI_Model {

    // Method to update lead details
    public function update_lead($lead_id, $data) {
        // Prepare the data for update
        $arr_data_update_ekyc = [
            'ekyc_active' => isset($data['ekyc_active']) ? $data['ekyc_active'] : '0', // Default to '0' if not provided
            'ekyc_deleted' => isset($data['ekyc_deleted']) ? $data['ekyc_deleted'] : '1', // Default to '1' if not provided
            'updated_on' => date('Y-m-d H:i:s') // Current timestamp
        ];

        // Perform the update
        $this->db->where('ekyc_lead_id', $lead_id);
        return $this->db->update('api_ekyc_logs', $arr_data_update_ekyc);
    }

    // Method to retrieve a lead by ID
    public function get_lead($lead_id) {
        $this->db->where('ekyc_lead_id', $lead_id);
        $query = $this->db->get('api_ekyc_logs');

        return $query->num_rows() > 0 ? $query->row() : null; 
    }
    
        // Method to update lead details
    public function update_esign($lead_id, $data) {
        // Prepare the data for update
        $arr_data_update_esign = [
            'esign_active' => isset($data['esign_active']) ? $data['esign_active'] : '0', // Default to '0' if not provided
            'esign_deleted' => isset($data['esign_deleted']) ? $data['esign_deleted'] : '1', // Default to '1' if not provided
            'updated_on' => date('Y-m-d H:i:s') // Current timestamp
        ];

        // Perform the update
        $this->db->where('esign_lead_id', $lead_id);
       
        return $this->db->update('api_esign_logs', $arr_data_update_esign);
    }
    
        // Method to retrieve a lead by ID
    public function get_esign($lead_id) {
        $this->db->where('esign_lead_id', $lead_id);
        $query = $this->db->get('api_esign_logs');

        return $query->num_rows() > 0 ? $query->row() : null; 
    }
    
    public function insert_pincode($data) {
    return $this->db->insert('master_pincode', $data); 
}
    
    
    
    
}
?>
