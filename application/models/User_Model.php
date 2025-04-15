<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class User_Model extends CI_Model {    
		private $table = 'users';    
		public function login($data) {        
			if (!empty($this->db->where($data)->get($this->table)->result_array())) {            
				return $this->db->where($data)->get($this->table)->result_array();        
			} else {            
				return false;        
			}    
		}    

		public function index($conditions = null, $limit, $start = null) {        
			$this->db->select('lmsUser.*,lmsUser.branch as state_ids,lmsUser.center as city_ids,roleMaster.name as role_name');        
			$this->db->from("users AS lmsUser");
			//        $this->db->join('tbl_state ST', 'ST.state_id = lmsUser.branch', 'left');        
			$this->db->join('users_role roleMaster', 'roleMaster.role_id = lmsUser.role_id', 'inner');        
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
			$this->db->where('lmsUser.user_active', 1);        
			$this->db->where('lmsUser.user_deleted', 0);        
			return $this->db->order_by('lmsUser.user_id', 'desc')->get();
		}    

		public function countLeads($conditions) {        
			$this->db->select("lmsUser.user_id");        
			if (!empty($conditions)) {            
				foreach ($conditions as $cond_index => $val) {                
					if (!empty($val)) {                    
						$this->db->where($cond_index, $val);                
					} else {                    
						$this->db->where($cond_index);                
					}            
				}        
			}        
			$this->db->where('lmsUser.user_active', 1);        
			$this->db->where('lmsUser.user_deleted', 0);        
			$this->db->from('users AS lmsUser')->get()->num_rows();    
		}    

		public function select($conditions, $data = null) {        
			return $this->db->select($data)->where($conditions)->from($this->table)->get();    
		}    

		public function insert($data) {        
			return $this->db->insert($this->table, $data);    
		}    

		public function update($conditions, $data) {        
			return $this->db->where($conditions)->update($this->table, $data);    
		}    

		public function delete($conditions) {        
			return $this->db->where($conditions)->delete($this->table);    
		}

		public function getUser($user_id) {        
			return $this->db->select('u.*')->where('u.user_id', $user_id)->from('users as u')->get();    
		}    

		public function updateUser($user_id, $data) {        
			return $this->db->where('users.user_id', $user_id)->update('users', $data);    
		}

		public function getStateList() {        
			$state_array = array();        
			$tempDetails = $this->db->select('state_id,state as state_name')->from('tbl_state')->get();        
			foreach ($tempDetails->result_array() as $temp_data) {            
				$state_array[$temp_data['state_id']] = $temp_data['state_name'];        
			}        
			return $state_array;    
		}    

		public function getCityList() {        
			$city_array = array();        
			$tempDetails = $this->db->select('id as city_id,city as city_name')->from('tbl_city')->get();        
			foreach ($tempDetails->result_array() as $temp_data) {            
				$city_array[$temp_data['city_id']] = $temp_data['city_name'];        
			}        
			return $city_array;    
		}    

		public function getRoleList() {        
			$role_array = array();        
			$tempDetails = $this->db->select('role_id,name')->from('users_role')->get();        
			foreach ($tempDetails->result_array() as $temp_data) {            
				$role_array[$temp_data['role_id']] = $temp_data['name'];        
			}        
			return $role_array;    
		}
	}