<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_Model extends CI_Model {

    private $t_role = 'tbl_user_role';

    public function getUser($user_id) {
        return $query = $this->db->select('u.*')->where('u.user_id', $user_id)->from('users u')->get();
    }

    public function getUserOtp($user_id) {
        return $query = $this->db->select('lot_otp_verify_flag')->from('leads_otp_trans')->where('lot_user_id', $user_id)->order_by('lot_id', 'desc')->get();
    }


    public function user_authentication($conditions) {
        $result = array('status' => 0, 'user_data' => array());

        $conditions['U.user_status_id'] = 1;
        $conditions['U.user_active'] = 1;
        $conditions['U.user_deleted'] = 0;
        $conditions['UR.user_role_active'] = 1;
        $conditions['UR.user_role_deleted'] = 0;

        $select = 'U.user_id, U.user_allocation_type_id, U.user_is_loanwalle, U.company_id, U.product_id, UR.user_role_id,RM.role_type_id, RM.role_type_id as role_id, RM.role_type_labels as labels, U.name, U.email, U.mobile, U.user_last_login_datetime';
        $select .= ', RM.role_type_heading as heading';
        $select .= ', U.user_total_login_count, U.user_logins_failed_count';

        $this->db->select($select);
        $this->db->from('users U');
        $this->db->join('user_roles UR', 'UR.user_role_user_id = U.user_id', 'inner');
        $this->db->join('master_role_type RM', 'UR.user_role_type_id = RM.role_type_id', 'inner');
        $this->db->where($conditions);
        $sql = $this->db->get();

        if (!empty($sql->num_rows())) {
            $row = $sql->row_array();

            $this->load->model('UMS/UMS_Model', 'umsModel');

            $user_branch_array = array();
            $user_state_array = array();

            if (in_array($row['role_type_id'], array(7))) {
                $branch_list = $this->umsModel->getUmsUserMappedBranchList($row['user_role_id'], 3, false); // 3 => branch

                if (!empty($branch_list['status'])) {
                    foreach ($branch_list['branch_list'] as $branch) {
                        $user_branch_array[] = $branch['m_branch_id'];
                    }
                }
            }

            if (in_array($row['role_type_id'], array(8))) {
                $state_list = $this->umsModel->getUmsStateList($row['user_role_id'], 2, false); // 2 => state

                if (!empty($state_list['status'])) {
                    foreach ($state_list['state_list'] as $state) {
                        $user_state_array[] = $state['m_state_id'];
                    }
                }
            }

            $result['user_data']['user_id'] = $row['user_id'];
            $result['user_data']['role_id'] = $row['role_id']; // role_type_id
            $result['user_data']['user_role_id'] = $row['user_role_id']; // user_roles primary key
            $result['user_data']['company_id'] = $row['company_id'];
            $result['user_data']['product_id'] = $row['product_id'];
            $result['user_data']['name'] = $row['name'];
            $result['user_data']['user_allocation_type_id'] = $row['user_allocation_type_id'];
            $result['user_data']['user_is_loanwalle'] = $row['user_is_loanwalle'];
            $result['user_data']['email'] = $row['email'];
            $result['user_data']['mobile'] = $row['mobile'];
            $result['user_data']['labels'] = $row['labels'];
            $result['user_data']['user_branch'] = $user_branch_array;
            $result['user_data']['user_state'] = $user_state_array;
            $result['user_data']['user_total_login_count'] = $row['user_total_login_count'];
            $result['user_data']['user_logins_failed_count'] = $row['user_logins_failed_count'];
            $result['user_data']['user_last_login_datetime'] = $row['user_last_login_datetime'];
            $result['status'] = 1;
        }
        return $result;
    }

    // public function getUserDetails()
    // {
    // 	$role = "";
    // 	if($_SESSION['isUserSession']['role'] == "Senction Head"){
    // 		$role = "Sanction & Telecaller";
    // 	}
    // 	$user_id = $_SESSION['isUserSession']['user_id'];
    // 	$agentDetails = $this->db->select('company_id')->where('user_id', $user_id)->get('users')->row();
    //           $company_id = $agentDetails->company_id;
    //           $stateList = $this->db->select('tb_states.id, tb_states.state')
    //               ->from('tb_states')->get()->result();
    //           foreach($stateList as $state){
    //            $this->db->select('users.user_id, users.created_by, users.name, users.email, users.mobile, users.password,
    //                    users.role, users.branch, users.center, users.status, users.created_on, tb_states.state')
    //           // 	->where('users.created_by', $user_id)
    //           // 	->where('users.company_id', $company_id)
    //                ->from('users')
    //                ->join('tb_states', 'users.branch = tb_states.id');
    //            return $query = $this->db->order_by('users.user_id', 'desc')->get();
    //        }
    // }

    public function getUserRole() {
        return $this->db->select('role.role_id, role.name')->from($this->t_role . ' role')->order_by('role.role_id', 'asc')->get();
    }

    public function getUserBranch() {
        return $this->db->select('tb_states.id, tb_states.state')->from('tb_states')->order_by('tb_states.id', 'asc')->get();
    }

    public function getUserDetailById($user_id) {
        $stateList = $this->db->select('tb_states.id, tb_states.state')->from('tb_states')->get()->row();

        $this->db->select('users.user_id, users.name, users.email, users.mobile, users.password, users.role, users.branch, users.center, users.status, users.created_on')
            ->where('users.user_id', $user_id)
            ->from('users');
        return $query = $this->db->order_by('users.user_id', 'desc')->get();
    }

    public function getUserloginList($user_id) {

        $result = array('status' => 0, 'user_data' => array());

        $conditions['U.user_status_id'] = 1;
        $conditions['U.user_active'] = 1;
        $conditions['U.user_deleted'] = 0;
        $conditions['UR.user_role_active'] = 1;
        $conditions['UR.user_role_deleted'] = 0;

        $select = 'U.user_id, U.company_id, U.product_id, UR.user_role_id,UA.ual_user_id, UA.ual_type_id as role_id,  U.name, U.email, U.user_last_login_datetime';
        $select .= ', UA.ual_ip';
        $select .= ', U.user_total_login_count, U.user_logins_failed_count';

        $this->db->select($select);
        $this->db->from('users U');
        $this->db->join('user_roles UR', 'UR.user_role_user_id = U.user_id', 'inner');
        $this->db->join('user_activity_log UA', 'U.user_id = UA.ual_user_id', 'inner');
        $this->db->where($user_id);
        $tempDetails = $this->db->get();
        foreach ($tempDetails->result_array() as $user_data) {
            $user_data['ual_user_id'] . ' ' . $user_data['ual_ip'];
        }
        return $user_data;
    }

    public function getUserProfileById($user_id) {
        $result = array('status' => 0, 'user_data' => array());

        $select = 'u.user_id, u.name, u.email, u.mobile, u.password, u.dob, u.gender, u.marital_status, u.father_name, u.role, ';
        $select .= ' u.branch, u.center, u.created_on, ';
        $select .= ' UR.user_role_id, MRT.role_type_heading, MRT.role_type_name as role, ';
        $select .= ' IF(UR.user_role_active = 1 AND UR.user_role_deleted = 0, "Active", "Inactive") AS status, ';

        $this->db->select($select);

        if (!empty($user_id)) {
            $this->db->where('u.user_id', $user_id);
        }

        $this->db->from('users u');
        $this->db->join('user_roles UR', 'UR.user_role_type_id =' . $_SESSION['isUserSession']['role_id']);
        $this->db->join('master_role_type MRT', 'MRT.role_type_id = UR.user_role_type_id');

        $query = $this->db->order_by('u.user_id', 'desc')->get();

        if (!empty($query->num_rows())) {
            $result['user_data']['userDetails'] = $query->row_array();
            $result['status'] = 1;
        }

        return $result;
    }

    public function adminUpdateUser($user_id, $data) {
        $result = $this->db->where('user_id', $user_id)->update('users', $data);
        if ($result == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function addBankDetails($data) {
        //print_r($data); exit;
        $query = $this->db->insert('tbl_bank_details', $data);
        print_r($query);
        exit;
    }

    public function insertUserActivity($user_id, $role_id, $activity_type = 1) {

        $user_activity_log = array();
        $user_activity_log["ual_url"] = "";
        $user_activity_log["ual_platform"] = $this->agent->platform();
        $user_activity_log["ual_browser"] = $this->agent->browser() . ' ' . $this->agent->version();
        $user_activity_log["ual_agent"] = $this->agent->agent_string();
        $user_activity_log["ual_ip"] = $this->input->ip_address();
        $user_activity_log["ual_datetime"] = date("Y-m-d H:i:s");
        $user_activity_log["ual_user_id"] = $user_id;
        $user_activity_log["ual_role_id"] = $role_id;
        $user_activity_log["ual_type_id"] = $activity_type;
        $user_activity_log["ual_source_type"] = 1;

        $this->db->insert('user_activity_log', $user_activity_log);
    }

    public function getUserDetailsByEmail($email) {

        if (empty($email)) {
            return [];
        }

        $result = array('status' => 0, 'user_data' => array());

        $select = '*';

        $this->db->select($select);

        $this->db->where(['email' => $email, 'user_status_id' => 1]);

        $this->db->from('users');

        $query = $this->db->order_by('user_id', 'DESC')->get();

        if (!empty($query->num_rows())) {
            $result['user_data'] = $query->row_array();
            $result['status'] = 1;
        }

        return $result;
    }
}
