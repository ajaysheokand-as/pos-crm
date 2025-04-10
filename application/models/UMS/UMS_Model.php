<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class UMS_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function select($table, $conditions, $data = null) {
        return $this->db->select($data)->where($conditions)->from($table)->get();
    }

    public function select_result_array($table, $conditions, $data = null) {
        return $this->db->select($data)->where($conditions)->from($table)->get()->result_array();
    }

    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($table, $conditions, $data, $in_key = "", $in_values = "") {
        $this->db->where($conditions);

        if (!empty($in_key) && !empty($in_values)) {
            $this->db->where_in($in_key, $in_values);
        }
        $this->db->update($table, $data);
        return ($this->db->affected_rows() > 0);
    }

    public function umsUserList($limit, $start = null, $conditions = array()) {

        $this->db->select('*');
        $this->db->from("users");
        $this->db->distinct();
        $this->db->limit($limit, $start);

        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }

        $this->db->where('user_active', 1);
        $this->db->where('user_deleted', 0);

        $return = $this->db->order_by('user_id', 'desc')->get()->result_array();
        return $return;
    }

    public function umsUserListCount($conditions) {
        $this->db->select("user_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('user_active', 1);
        $this->db->where('user_deleted', 0);
        return $this->db->from('users')->get()->num_rows();
    }

    public function getRoleList() {
        $role_array = array();
        $tempDetails = $this->db->select('role_type_id,role_type_name')->from('master_role_type')->where(['role_type_active' => 1])->get();
        foreach ($tempDetails->result_array() as $temp_data) {
            $role_array[$temp_data['role_type_id']] = $temp_data['role_type_name'];
        }
        return $role_array;
    }

    public function getUserStatus() {
        $user_status_array = array(1 => "Active", 2 => "Inactive", 3 => "Closed", 4 => "Blocked");

        return $user_status_array;
    }

    public function getCheckLoginUsername($login_user_name, $user_id = 0) {
        $return_val = false;
        $condition = array();
        $condition["LOWER(email)"] = trim(strtolower($login_user_name));

        if (!empty($user_id)) {
            $condition["user_id!="] = $user_id;
        }

        $tempDetails = $this->db->select('email')->from('users')->where($condition)->get();

        if ($tempDetails->num_rows()) {
            $return_val = true;
        }
        return $return_val;
    }

    public function getUmsUserDetails($user_id) {
        $status = 0;
        $user_data = array();
        $tempDetails = $this->db->select('*')->from('users')->where(["user_id" => $user_id])->get();
        if (!empty($tempDetails->num_rows())) {
            $user_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "user_data" => $user_data);
    }

    public function getUmsUserRoleDetails($user_role_id) {
        $status = 0;
        $user_data = array();

        $tempDetails = $this->db->select('*')->where('UR.user_role_id', $user_role_id)->from('user_roles UR')->join('users U', 'UR.user_role_user_id = U.user_id')->get();

        if (!empty($tempDetails->num_rows())) {
            $user_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "user_role_data" => $user_data);
    }

    public function getUmsUserMiniDetails($user_id) {
        $status = 0;
        $user_data = array();
        $tempDetails = $this->db->select('user_name,name')->from('users')->where(["user_id" => $user_id])->get();
        if (!empty($tempDetails->num_rows())) {
            $user_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "user_data" => $user_data);
    }

    public function checkExistingRoleType($roletypeID, $user_id) {
        $conditions = array(
            "user_role_type_id" => $roletypeID,
            "user_role_user_id" => $user_id,
        );
        $this->db->select('user_role_active,user_role_user_id,user_role_type_id, user_role_deleted, user_role_supervisor_role_id');
        $this->db->from('user_roles');
        $tempDetails = $this->db->where($conditions)->get();
        if ($tempDetails->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getUmsRoleList($user_id) {
        $status = 0;
        $role_list = array();
        $conditions = array(
            "user_id" => $user_id,
            "user_active" => 1,
            "user_deleted" => 0,
        );

        $this->db->select('user_role_id,user_role_active, user_role_deleted, role_type_name, user_role_supervisor_role_id, user_role_created_on, user_role_updated_on');
        $this->db->from('users');
        $this->db->join('user_roles', 'user_role_user_id = user_id');
        $this->db->join('master_role_type', 'role_type_id = user_role_type_id');
        $this->db->distinct();
        $tempDetails = $this->db->where($conditions)->get();
        
        if (!empty($tempDetails->num_rows())) {
            $role_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "role_list" => $role_list);
    }

    public function getUmsStateList($user_role_id, $user_rl_location_type_id, $all_record = false) {
        $status = 0;
        $state_list = array();

        $conditions["user_rl_role_id"] = $user_role_id;
        $conditions["user_rl_location_type_id"] = $user_rl_location_type_id;
        if ($all_record == false) {
            $conditions["user_rl_active"] = 1;
            $conditions["user_rl_deleted"] = 0;
        }

        $this->db->select('user_rl_location_id,user_rl_active,user_rl_deleted, m_state_id, m_state_name');
        $this->db->from('user_role_locations');
        $this->db->join('master_state', 'user_rl_location_id = m_state_id AND user_rl_location_type_id=' . $user_rl_location_type_id);
        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails->num_rows())) {
            $state_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "state_list" => $state_list);
    }

    public function getUmsUserMappedBranchList($user_role_id, $user_rl_location_type_id, $all_record = false) {
        $status = 0;
        $branch_list = array();
        $conditions = array(
            "user_rl_role_id" => $user_role_id,
            "user_rl_location_type_id" => $user_rl_location_type_id, // 1 => city, 2 =>state, 3 => branch
        );

        if ($all_record == false) {
            $conditions["user_rl_active"] = 1;
            $conditions["user_rl_deleted"] = 0;
        }

        $this->db->select('user_rl_location_id,user_rl_active,user_rl_deleted, m_branch_id, m_branch_name');
        $this->db->from('user_role_locations');
        $this->db->join('master_branch', 'user_rl_location_id = m_branch_id AND user_rl_location_type_id=' . $user_rl_location_type_id);

        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails->num_rows())) {
            $branch_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "branch_list" => $branch_list);
    }

    public function getUmsExportPermissionList($user_role_id, $all_record = false) {
        $status = 0;
        $export_list = array();
        $conditions = array(
            "export_permission_user_role_id" => $user_role_id,
        );
        if ($all_record == false) {
            $conditions["export_permission_active"] = 1;
            $conditions["export_permission_deleted"] = 0;
        }
        $this->db->select('export_permission_id, export_permission_export_id, export_permission_active, export_permission_deleted');
        $this->db->from('user_export_permission');
        $this->db->join('master_export', 'export_permission_export_id = m_export_id');
        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails->num_rows())) {
            $export_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "export_list" => $export_list);
    }

    public function getUmsMasterBranchList() {
        $status = 0;
        $export_list = array();
        $conditions["export_permission_active"] = 1;
        $conditions["export_permission_deleted"] = 0;
        $this->db->select('export_permission_id, export_permission_export_id, export_permission_active, export_permission_deleted');
        $this->db->from('user_export_permission');
        $this->db->join('master_export', 'export_permission_export_id = m_export_id');
        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails->num_rows())) {
            $export_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "export_list" => $export_list);
    }

    public function getUmsMasterExportList($user_id, $all_record = false) {
        $status = 0;
        $master_export_list = array();
        $conditions = array(
            "export_permission_user_id" => $user_id,
        );
        if ($all_record == false) {
            $conditions["export_permission_active"] = 1;
            $conditions["export_permission_deleted"] = 0;
        }
        $this->db->select('export_permission_export_id, export_permission_active, export_permission_deleted, m_export_name');
        $this->db->from('user_export_permission');
        $this->db->join('master_export', 'm_export_id = export_permission_export_id');
        $tempDetails = $this->db->where($conditions)->get();
        if (!empty($tempDetails->num_rows())) {
            $master_export_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "export_list" => $master_export_list);
    }

    public function getStateList() {
        $state_array = array();
        $tempDetails = $this->db->select('m_state_id,m_state_name')->from('master_state')->order_by('m_state_name', 'ASC')->get();
        foreach ($tempDetails->result_array() as $temp_data) {
            $state_array[$temp_data['m_state_id']] = $temp_data['m_state_name'];
        }
        return $state_array;
    }

    public function getSCM($user_id = null) {
        $SCM_List = array();
        $conditions['user_role_type_id'] = 8;
        $conditions['user_role_active'] = 1;
        $conditions['user_role_deleted'] = 0;

        if (!empty($user_id)) {
            $conditions['user_role_user_id !='] = $user_id;
        }

        $this->db->select('user_role_user_id, user_role_id, user_id, name');
        $this->db->from('user_roles');
        $this->db->join('users', 'user_id = user_role_user_id');

        $tempDetails = $this->db->where($conditions)->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $SCM_List[$temp_data['user_role_id']] = $temp_data['name'];
        }

        return $SCM_List;
    }

    //Credit Head Function

    public function getCreditHeadList($user_id) {
        $Sanction_head = $this->db->query("SELECT user_id, user_is_loanwalle FROM `users` WHERE user_is_loanwalle=1 AND user_id=$user_id")->row_array();

        $credit_list = array();
        $conditions['UR.user_role_type_id'] = 4;
        $conditions['U.is_Active'] = 1;
        $conditions['U.user_status_id'] = 1;
        $conditions['UR.user_role_active'] = 1;

        if (!empty($user_id) && $Sanction_head['user_is_loanwalle'] == 0) {
            $conditions['UR.user_role_user_id !='] = $user_id;
        }

        $this->db->select('UR.user_role_user_id, UR.user_role_id, UR.user_role_type_id, U.user_id, U.name');
        $this->db->from('user_roles UR');
        $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
        $this->db->order_by('U.name');

        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails) && $tempDetails->num_rows() > 0) {
            $credit_list = $tempDetails->result_array();
        }
        return $credit_list;
    }

    public function getMappedCreditHead($user_role_id) {

        $query = $this->db->query("SELECT user_role_supervisor_role_id FROM user_roles WHERE user_role_id=$user_role_id")->row_array();

        if (!empty($query)) {

            $credit_role_id = $query['user_role_supervisor_role_id'];
            $conditions['UR.user_role_id'] = $credit_role_id;

            $this->db->select('UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, UR.user_role_supervisor_role_id, U.user_id, U.name');
            $this->db->from('user_roles UR');
            $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
            $this->db->where($conditions);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row_array();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getMappedRoleLevel($user_role_id) {

        $query = $this->db->query("SELECT user_role_supervisor_role_id, user_role_level FROM user_roles WHERE user_role_id=$user_role_id")->row_array();

        if (!empty($query)) {

            $user_role_level = $query['user_role_level'];

            if (!empty($user_role_level)) {
                return $user_role_level;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getCreditList($user_id) {
//        echo $user_id; exit;
        $credit_list = array();
        $conditions['UR.user_role_type_id'] = 3;
        $conditions['U.is_Active'] = 1;
        $conditions['U.user_status_id'] = 1;
        $conditions['UR.user_role_active'] = 1;

//        if (!empty($user_id)) {
//            $conditions['UR.user_role_user_id !='] = $user_id;
//        }

        $this->db->select('UR.user_role_user_id, UR.user_role_id, UR.user_role_type_id, U.user_id, U.name');
        $this->db->from('user_roles UR');
        $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
        $this->db->order_by('U.name');
        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails) && $tempDetails->num_rows() > 0) {
            $credit_list = $tempDetails->result_array();
        }

        return $credit_list;
    }

    public function getMappedCredit($user_role_id) {

        $query = $this->db->query("SELECT user_role_supervisor_role_id FROM user_roles WHERE user_role_id=$user_role_id AND user_role_type_id=2")->row_array();

        if (!empty($query)) {

            $credit_role_id = $query['user_role_supervisor_role_id'];
            $conditions['UR.user_role_id'] = $credit_role_id;

            $this->db->select('UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, UR.user_role_supervisor_role_id, U.user_id, U.name');
            $this->db->from('user_roles UR');
            $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
            $this->db->where($conditions);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row_array();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getcreditSelectedvalue($user_role_id) {
        $query = $this->db->select('user_role_supervisor_role_id')->where('user_role_id', $user_role_id)->where_in('user_role_type_id', [2, 3, 4])->from('user_roles')->get();

        if ($query->num_rows() > 0) {
            $result = $query->row();
            $credit_role_id = $result->user_role_supervisor_role_id;
            $conditions['UR.user_role_id'] = $credit_role_id;

            $this->db->select(' UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, UR.user_role_supervisor_role_id, U.user_id, U.name');
            $this->db->from('user_roles UR');
            $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
            $this->db->where($conditions);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row_array();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getSCMByRole($user_role_id) {
        $where_in = ['user_role_type_id' => [7, 13]];
        $query = $this->db->select('user_role_user_id')->where_in($where_in)->where('user_role_id', $user_role_id)->from('user_roles')->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $user_role_user_id = $result->user_role_user_id;

            $conditions = array(
                "user_role_user_id !=" => $user_role_user_id,
            );
            $this->db->select('user_role_user_id,user_role_id,name');
            $this->db->from('user_roles');
            $this->db->join('users', 'user_id = user_role_user_id');
            $this->db->where_in($where_in);
            $this->db->where($conditions);

            $this->db->distinct();

            $tempDetails = $this->db->get();
            foreach ($tempDetails->result_array() as $temp_data) {
                $SCM_List[$temp_data['user_role_id']] = $temp_data['name'];
            }
            return $SCM_List;
        } else {
            return 0;
        }
    }

    public function getMappedSCMtoFCE($user_id) {
        $query = $this->db->select('user_role_supervisor_role_id,user_role_id')->where('user_role_user_id', $user_id)->from('user_roles')->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $user_role_supervisor_role_id = $result->user_role_supervisor_role_id;

            $query2 = $this->db->select('name,user_role_user_id')->where('user_role_id', $user_role_supervisor_role_id)->from('user_roles')->join('users', 'user_id = user_role_user_id')->get();
            if ($query2->num_rows() > 0) {
                $result2 = $query2->row();
                return $result2->name;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getMapped_FCE_with_SCM($user_role_id) {
        $query = $this->db->select('user_role_user_id')->where('user_role_id', $user_role_id)->where_in('user_role_type_id', [8])->from('user_roles')->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $scm_user_id = $result->user_role_user_id;
            $conditions['UR.user_role_supervisor_role_id'] = $user_role_id;

            $this->db->select(' UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, U.user_id, U.name');
            $this->db->from('user_roles UR');
            $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
            $this->db->where($conditions);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getSCMSelectedvalue($user_role_id) {
        $query = $this->db->select('user_role_supervisor_role_id')->where('user_role_id', $user_role_id)->where_in('user_role_type_id', [13])->from('user_roles')->get();

        if ($query->num_rows() > 0) {
            $result = $query->row();
            $scm_role_id = $result->user_role_supervisor_role_id;
            $conditions['UR.user_role_id'] = $scm_role_id;

            $this->db->select(' UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, UR.user_role_supervisor_role_id, U.user_id, U.name');
            $this->db->from('user_roles UR');
            $this->db->join('users U', 'U.user_id = UR.user_role_user_id');
            $this->db->where($conditions);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row_array();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getUmsSuperVisorList($user_id) {

        $fce_array = array();

        $query = $this->db->select('user_role_supervisor_role_id, user_role_id')->where('user_role_user_id', $user_id)->from('user_roles')->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $user_role_supervisor_role_id = $result->user_role_supervisor_role_id;

            $query2 = $this->db->select('name,user_role_user_id')->where('user_role_supervisor_role_id', $user_role_supervisor_role_id)->from('user_roles')->join('users', 'user_id = user_role_user_id')->get();

            foreach ($query2->result_array() as $temp_data) {
                $fce_array[$temp_data['user_role_user_id']] = $temp_data['name'];
            }
            return $fce_array;
        }
    }

    public function getUmsRoleIDByType($user_id, $role_type_id = null) {

        $this->db->select('user_role_id')->where('user_role_user_id', $user_id);
        if (!empty($role_type_id)) {
            $this->db->where('user_role_type_id', $role_type_id); // 8
        }
        $query = $this->db->from('user_roles')->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $user_role_id = $result->user_role_id;
            if (!empty($user_role_id)) {
                return $user_role_id;
            } else {
                return 0;
            }
        }
    }

    public function getCityList() {
        $city_array = array();

        $tempDetails = $this->db->select('m_city_id,m_city_name')->from('master_city')->order_by('m_city_name', 'ASC')->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $city_array[$temp_data['m_city_id']] = $temp_data['m_city_name'];
        }
        return $city_array;
    }

    public function getMasterBranchList() {
        $city_array = array();
        $conditions_branch['m_branch_active'] = 1;
        $conditions_branch['m_branch_deleted'] = 0;

        $this->db->select('m_branch_id, m_branch_name');
        $this->db->from('master_branch');
        $this->db->where($conditions_branch);
        $this->db->order_by('m_branch_name', 'ASC');
        $tempDetails = $this->db->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $city_array[$temp_data['m_branch_id']] = $temp_data['m_branch_name'];
        }
        return $city_array;
    }

    public function getMasterCollectionAgencyList() {
      $agency_array = array();
      $conditions_agency['m_collection_agency_active'] = 1;
      $conditions_agency['m_collection_agency_deleted'] = 0;

      $this->db->select('m_collection_agency_id');
      $this->db->select('m_collection_agency_name');
      $this->db->from('master_collection_agency');
      $this->db->where($conditions_agency);
      $this->db->order_by('m_collection_agency_name', 'ASC');
      $tempDetails = $this->db->get();

      foreach($tempDetails->result_array() as $temp_data) {
        $agency_array[$temp_data['m_collection_agency_id']] = $temp_data['m_collection_agency_name'];
      }
      return $agency_array;
    }

    public function getExportMasterList() {
        $export_list_array = array();

        $tempDetails = $this->db->select('m_export_id,m_export_name, m_export_heading')
                ->where(['m_export_active' => 1, 'm_export_deleted' => 0])
                ->from('master_export')
                ->order_by('m_export_name', 'ASC')
                ->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $export_list_array[$temp_data['m_export_id']] = $temp_data['m_export_name'];
        }
        return $export_list_array;
    }

    public function getMISMasterList() {
        $export_list_array = array();

        $tempDetails = $this->db->select('m_report_id,m_report_name, m_report_heading')
                ->where(['m_report_active' => 1, 'm_report_deleted' => 0])
                ->from('master_mis_report')
                ->order_by('m_report_name', 'ASC')
                ->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $export_list_array[$temp_data['m_report_id']] = $temp_data['m_report_name'];
        }
        return $export_list_array;
    }

    public function getUmsMISPermissionList($user_role_id, $all_record = false) {
        $status = 0;
        $export_list = array();
        $conditions = array(
            "mis_permission_user_role_id" => $user_role_id,
        );
        if ($all_record == false) {
            $conditions["mis_permission_active"] = 1;
            $conditions["mis_permission_deleted"] = 0;
        }
        $this->db->select('mis_permission_id, mis_permission_mis_id, mis_permission_active, mis_permission_deleted');
        $this->db->from('user_mis_permission');
        $this->db->join('master_mis_report', 'mis_permission_mis_id = m_report_id');
        $tempDetails = $this->db->where($conditions)->get();

        if (!empty($tempDetails->num_rows())) {
            $export_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "mis_list" => $export_list);
    }
}

?>
