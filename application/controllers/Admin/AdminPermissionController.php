<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class AdminPermissionController extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
            $this->load->model('Task_Model');
            $this->load->model('Admin_Model');
            $this->load->model('User_Model', 'user');

	    	$login = new IsLogin();
	    	$login->index();
		}

		public function index()
		{
			$limit = 10;
	        $data['users'] = $this->user->index($limit, "'user_id', 'desc'"); // limit, order_by
	        // echo "<pre>";print_r($data['users']->result()); exit;
	        // $data['exportDataType'] = $this->db->select('tbl_filter_menu.filter_id, tbl_filter_menu.name, tbl_filter_menu.status')
	        //             ->from('tbl_filter_menu')
	        //             ->get()
	        //             ->result();
	        $this->load->view('Admin/Permissions/index', $data);
		}
		
		public function userPermission($user_id)
		{
		    $where = ['company_id' => company_id, 'product_id' => product_id, 'is_active'=>1];
            $data['menu_list'] = $this->db->select('id, menu_name')->where($where)->from('ftc_menu_master')->get();
		    $this->load->view('Admin/Permissions/userPermission', $data);
		}

	    public function adminPermission()
	    {
	        $data['userDetails'] = $this->db->select('users.user_id, users.name, users.role')
	                    ->from('users')
	                    ->get()
	                    ->result();
	                    
	        // $data['exportDataType'] = $this->db->select('tbl_filter_menu.filter_id, tbl_filter_menu.name, tbl_filter_menu.status')
	        //             ->from('tbl_filter_menu')
	        //             ->get()
	        //             ->result();
	        $this->load->view('Admin/Permissions/index', $data);
	    }

	    public function getExportType($currentChecked)
	    { 
	        $data = $this->db->select('SM.sub_menu_id, SM.filter_id, SM.name, SM.status')->where('SM.filter_id', $currentChecked)->from('tbl_filter_sub_menu as SM')->get()->result();
	        echo json_encode($data);
	    }

	    public function permissionExportData()
	    {
	        $user_id = intval($_POST['user_id']);
	        $permission_user = strval($_POST['permission_user']);
	        $filterID = strval($_POST['filterID']);
	        
	        if(!empty($user_id)){
	            $query = $this->db->select('u.permission_export_Data')->where('u.user_id', $user_id)->from('users u')->get()->row();
	            $pxData = $query->permission_export_Data;
	            $permissionID = $permission_user;
	            
	            if($permission_user == 1) {
    	            $str = strtok($pxData, "=>");
    	            $permissionID = $permission_user ."=>". implode(",", $filterID);
	            }
	            $update = [
	                'permission_export_Data' => $permissionID
	                ];
                echo "<pre>"; print_r($update); exit;
	                
	            $this->db->where('user_id', $user_id)->update('users', $update);
    	        $data = $this->db->select('SM.sub_menu_id, SM.filter_id, SM.name, SM.status')->where('SM.filter_id', $currentChecked)->from('tbl_filter_sub_menu as SM')->get()->result();
    	        echo json_encode($data);
	        }
	    }

	    public function permissionGetUsers()
	    {
	        $data['userDetails'] = $this->db->select('users.user_id, users.name, users.role, users.permission_add_user')
	                    ->where('company_id', $_SESSION['isUserSession']['company_id'])->from('users')->get()->result();
	        echo json_encode($data);
	    }

	    public function allowPermissionToAddUser()
	    {
	    	if ($this->input->server('REQUEST_METHOD') == 'POST') 
	        {
	            $user_id = intval($this->input->post('user_id'));
	            $permission_user = strval($this->input->post('allowUser'));
	            
                $data = ["permission_add_user" => $permission_user];
	            
                $result = $this->db->where('user_id', $user_id)->update('users', $data);
                if($permission_user == 0){
                    $json['msg'] = $permission_user;
                }else{
                    $json['msg'] = $permission_user;
                }
                echo json_encode($json);
	        }
	    }
	    
	    public function dashboardMenuPermission($user_id)
	    {
	    	if ($this->input->server('REQUEST_METHOD') == 'POST') 
	        {
	            $selectedItems = strval($this->input->post('checkList'));
	            if($selectedItems == ""){
		            $where = ['user_id'=>$user_id, 'is_active' => 1];
                    $json['menuPermitted'] = $this->db->select('*')->where($where)->from('ftc_menu_permission')->get()->result_array();
                    $menuMaster = $this->db->select('*')->from('ftc_menu_master')->get();
                    $json['menuMaster'] = $menuMaster->result();
                    echo json_encode($json);
	            }else{
    	            foreach($selectedItems as $item)
    	            {
    	                $data = [
    	                    'menu_id'       => $item,
    	                    'company_id'    => company_id,
    	                    'product_id'    => product_id,
    	                    'user_id'       => $user_id,
                        ];
                        $result = $this->db->select('*')->where($data)->from('ftc_menu_permission')->get();
                        
                        if($result->num_rows() === 0){
                            $arr = array_merge($data, ['is_active' => 1]);
    	                    $this->db->insert('ftc_menu_permission', $arr);
    	                    $json['msg'] = "Permission Allocated.";
                        }else{
    	                    $json['msg'] = "Already Allocated.";
                        }
    	            }
                    echo json_encode($json);
	            }
	        }else{
	            $json['err'] = "Server Found Invalid request.";
                echo json_encode($json);
	        }
	    }
		
		public function importData() {

			if ($this->input->server('REQUEST_METHOD') == 'POST') {
				
				if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
					$file_name = $_FILES['import_file']['name'];
					$file_tmp = $_FILES['import_file']['tmp_name'];

					$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
					$extensions = array("csv");

					if (in_array($file_ext, $extensions)) {
						$csvFile = fopen($file_tmp, 'r');
						$i = 0;
						while (($line = fgetcsv($csvFile)) !== FALSE) {

							$lead_id = $line[0];
							$user_id = $line[1];

							$updateLead = array(
								'lead_collection_executive_assign_user_id' => $user_id,
								'lead_collection_executive_assign_datetime' => date('Y-m-d H:i:s')
							);

							$this->db->where('lead_id', $lead_id)->update('leads', $updateLead);
							$i++;
						}

						fclose($csvFile);

						$response = array(
							'status' => 'success',
							'message' => 'Data imported successfully. Updated : ' . ($i - 1)
						);
					} else {
						$response = array(
							'status' => 'error',
							'message' => 'Invalid file format. Please upload a CSV file.'
						);
					}
				} else {
					$response = array(
						'status' => 'error',
						'message' => 'No file was uploaded or an error occurred during upload.'
					);
				}
			} else {
				$response = array(
					'status' => 'error',
					'message' => 'Invalid request method.'
				);
			}

			echo json_encode($response);
		}


	}

?>
