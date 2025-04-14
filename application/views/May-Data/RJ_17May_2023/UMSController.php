<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

class UMSController extends CI_Controller {

    var $javascript_files = "";

    public function __construct() {
        parent::__construct();
        $this->load->model('UMS/UMS_Model', 'umsModel');
        $this->load->model('Task_Model', 'Tasks');
        $this->javascript_files .= "<script type='text/javascript'>var app_base_url='" . base_url() . "';</script>";
        // $this->javascript_files .= "<script type='text/javascript' src='" . base_url("public/js/jsLibrary.js?v=1") . "'></script>";
        $this->javascript_files .= "<script type='text/javascript' src='" . base_url("public/js/ums/ums_process.js?v=1") . "'></script>";
        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $conditions = array();
        if (!empty($_POST['filter_role'])) {
            $conditions["users.role_id"] = intval($_POST['filter_role']);
        }
        if (!empty($_POST['filter_input'])) {
            $conditions["(users.name LIKE '%" . $_POST['filter_input'] . "%' OR users.email LIKE '" . $_POST['filter_input'] . "%' OR users.mobile = '" . $_POST['filter_input'] . "')"] = null;
        }
        //traceObject($conditions);
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->umsModel->umsUserListCount($conditions);
        $config["per_page"] = 10;
        $config["uri_segment"] = 2;
        $config['full_tag_open'] = '<div class="pagging text-right"><nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav></div>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '<span aria-hidden="true"></span></span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';

        $this->pagination->initialize($config);
        $data['pageURL'] = $url;
        $data['userDetails'] = $this->umsModel->umsUserList($config["per_page"], $page, $conditions);
        $data["links"] = $this->pagination->create_links();
        $data["javascript_files"] = $this->javascript_files;
        $data["master_role"] = $this->umsModel->getRoleList();
        $data['master_user_status'] = $this->umsModel->getUserStatus();
        $this->load->view('UMS/index', $data);
    }

    public function umsViewUser($enc_user_id = "") {

        $view_data = array();
        $user_data = array();
        $user_role_data = array();
        $success_msg = "";
        $errors_msg = "";
        $user_id = 0;

        if (!empty($enc_user_id)) {

            $user_id = $this->encrypt->decode($enc_user_id);

            if (!empty($user_id)) {

                $return_array = $this->umsModel->getUmsUserDetails($user_id);
                if ($return_array['status'] == 1) {
                    $user_data = $return_array['user_data'];

                    $return_array = $this->umsModel->getUmsRoleList($user_id);

                    $RoleIdByType = $this->umsModel->getUmsRoleIDByType($user_id, null);
                    $supervisor_list = $this->umsModel->getUmsSuperVisorList($user_id);
                    if ($return_array['status'] == 1) {
                        $user_role_data = $return_array['role_list'];
                    }

                    $state_list = $this->umsModel->getUmsStateList($RoleIdByType, 2, true); // 2 => state

                    if (!empty($user_data['created_by'])) {
                        $return_array = $this->umsModel->getUmsUserMiniDetails($user_data['created_by']);
                        if ($return_array['status'] == 1) {
                            $user_data['created_by_name'] = $return_array['user_data']['name'];
                        }
                    }
                    if (!empty($user_data['updated_by'])) {
                        $return_array = $this->umsModel->getUmsUserMiniDetails($user_data['updated_by']);
                        if ($return_array['status'] == 1) {
                            $user_data['updated_by_name'] = $return_array['user_data']['name'];
                        }
                    }
                    $enc_user_id = $this->encrypt->encode($user_data['user_id']);
                } else {
                    $errors_msg = "User details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }

        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();

        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["user_data"] = $user_data;
        $view_data["user_role_data"] = $user_role_data;
        $view_data["state_list"] = $state_list;
        $view_data["ṣupervisor_list"] = $supervisor_list;
        $view_data["enc_user_id"] = $enc_user_id;

        $this->load->view('UMS/viewUser', $view_data);
    }

    public function umsAddUser() {

        $view_data = array();
        $user_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $user_id = 0;

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $user_data["name"] = $this->input->post('name');
            $user_data["email"] = $this->input->post('email');
            $user_data["mobile"] = $this->input->post('mobile');
            $user_data["user_name"] = $this->input->post('user_name');
            $user_data["user_dialer_id"] = $this->input->post('user_dialer_id');
            $user_data["user_status_id"] = $this->input->post('user_status_id');

            $this->form_validation->set_rules('name', 'User Name', 'required|regex_match[/^[A-Za-z ]+$/]|min_length[2]|max_length[100]');
            $this->form_validation->set_rules('email', 'Email', 'required|min_length[15]|max_length[150]|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric|exact_length[10]|is_unique[users.mobile]');
//            $this->form_validation->set_rules('user_name', 'Login User Name', 'required|min_length[10]|max_length[50]');
            $this->form_validation->set_rules('user_dialer_id', 'Dialer ID', 'trim');
            $this->form_validation->set_rules('user_status_id', 'User Status', 'required|numeric');

            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {

                if ($this->umsModel->getCheckLoginUsername($user_data["email"])) {
                    $errors_msg = "Email is already exist. Please enter new email";
                } else {

                    $name = $user_data["name"];
                    $email = $user_data["email"];
                    $mobile = $user_data["mobile"];
                    $loginUserName = $user_data["user_name"];
                    $dialerID = !empty($user_data["user_dialer_id"]) ? $user_data["user_dialer_id"] : NULL;

                    $insert_user_array = array();

                    $password = "lw@12345";

                    // $hash = hash('sha256', $password);
                    $hash = md5($password);
                    $insert_user_array["password"] = $hash;
                    $insert_user_array["created_by"] = $_SESSION['isUserSession']['user_id'];
                    $insert_user_array["created_on"] = date("Y-m-d H:i:s");
                    $insert_user_array["user_name"] = $loginUserName;
                    $insert_user_array["name"] = $name;
                    $insert_user_array["email"] = strtoupper($email);
                    $insert_user_array["mobile"] = $mobile;
                    $insert_user_array["user_dialer_id"] = !empty($dialerID) ? $dialerID : NULL;
                    $insert_user_array["ip"] = getIpAddress();
                    $insert_user_array["user_status_id"] = $user_data["user_status_id"];

                    $user_id = $this->umsModel->insert('users', $insert_user_array);

                    if (!empty($user_id)) {
                        $success_msg = "User has been added successfully.";

                        $this->session->set_flashdata('success_msg', $success_msg);

                        $enc_user_id = $this->encrypt->encode($user_id);
                        return redirect('ums/view-user/' . $enc_user_id, 'refresh');
                    } else {
                        $errors_msg = "Some error occurred during creation of user. Please try again.";
                    }
                }
            }

            if (!empty($errors_msg)) {
                $this->session->set_flashdata('errors_msg', $errors_msg);
            }
        }

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["user_data"] = $user_data;
        $this->load->view('UMS/addUpdateUser', $view_data);
    }

    public function umsEditUser($enc_user_id = "") {
        $view_data = array();
        $user_data = array();
        $success_msg = "";
        $errors_msg = "";
        $user_id = 0;
        $update_flag = false;

        if (!empty($enc_user_id)) {

            $user_id = $this->encrypt->decode($enc_user_id);

            $return_array = $this->umsModel->getUmsUserDetails($user_id);

            if ($return_array['status'] == 1) {
                $update_flag = true;
                $user_id = $return_array['user_data']['user_id'];
                $user_data = $return_array['user_data'];
                $enc_user_id = $this->encrypt->encode($user_id);
                if ($this->input->server('REQUEST_METHOD') == 'POST') {

                    $user_data["name"] = $this->input->post('name');
                    $user_data["email"] = $this->input->post('email');
                    $user_data["mobile"] = $this->input->post('mobile');
                    $user_data["user_name"] = $this->input->post('user_name');
                    $user_data["user_dialer_id"] = $this->input->post('user_dialer_id');
                    $user_data["user_status_id"] = $this->input->post('user_status_id');

                    $this->form_validation->set_rules('name', 'User Name', 'required|regex_match[/^[A-Za-z ]+$/]|min_length[2]|max_length[100]');
                    $this->form_validation->set_rules('email', 'Email', 'required|min_length[15]|max_length[150]|valid_email'); // |is_unique[users.email]
                    $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric|exact_length[10]'); // |is_unique[users.mobile]
//                    $this->form_validation->set_rules('user_name', 'Login User Name', 'required|min_length[10]|max_length[50]');
                    $this->form_validation->set_rules('user_dialer_id', 'Dialer ID', 'trim');
                    $this->form_validation->set_rules('user_status_id', 'User Status', 'required|numeric');

                    if ($this->form_validation->run() == FALSE) {
                        $errors_msg = validation_errors();
                    } else {

                        if ($this->umsModel->getCheckLoginUsername($user_data["user_name"], $user_id)) {
                            $errors_msg = "login user name already exist. Please try with different login user name.";
                        } else {

                            $fullName = $user_data["name"];
                            $email = $user_data["email"];
                            $mobile = $user_data["mobile"];
                            $loginUserName = $user_data["user_name"];
                            $dialerID = !empty($user_data["user_dialer_id"]) ? $user_data["user_dialer_id"] : NULL;

                            $insert_user_array = array();
                            $insert_user_array["updated_by"] = $_SESSION['isUserSession']['user_id'];
                            $insert_user_array["updated_on"] = date("Y-m-d H:i:s");
                            $insert_user_array["user_name"] = $loginUserName;
                            $insert_user_array["name"] = $fullName;
                            $insert_user_array["email"] = strtoupper($email);
                            $insert_user_array["mobile"] = $mobile;
                            $insert_user_array["user_dialer_id"] = !empty($dialerID) ? $dialerID : NULL;
                            $insert_user_array["ip"] = getIpAddress();
                            $insert_user_array["user_status_id"] = $user_data["user_status_id"];

                            $return_update_flag = $this->umsModel->update('users', ['user_id' => $user_id], $insert_user_array);
                            if ($return_update_flag) {
                                $enc_user_id = $this->encrypt->encode($user_id);

                                $success_msg = "User has been updated successfully.";

                                $this->session->set_flashdata('success_msg', $success_msg);

                                return redirect('ums/view-user/' . $enc_user_id, 'refresh');
                            } else {
                                $errors_msg = "Some error occurred during updation of user. Please try again.";
                            }
                        }
                    }
                }
            } else {
                $errors_msg = "User details not found.";
            }
        } else {
            $errors_msg = "Invalid Access..";
        }

        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["user_data"] = $user_data;
        $view_data["update_flag"] = $update_flag;
        if ($update_flag) {
            $view_data["enc_user_id"] = $enc_user_id;
        }
        $this->load->view('UMS/addUpdateUser', $view_data);
    }

    public function umsAddUserRole($enc_user_id) {

        $view_data = array();
        $user_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $user_id = 0;

        if (!empty($enc_user_id)) {
            $user_id = $this->encrypt->decode($enc_user_id);
            if (!empty($user_id)) {
                $return_array = $this->umsModel->getUmsUserDetails($user_id);
                if ($return_array['status'] == 1) {
                    $user_data = $return_array['user_data'];

                    $enc_user_id = $this->encrypt->encode($user_data['user_id']);
                } else {
                    $errors_msg = "User role details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();
        $view_data['master_state'] = $this->umsModel->getStateList();
        $view_data['get_scm'] = $this->umsModel->getSCM(); // $user_id
        $view_data['get_chi'] = $this->umsModel->getCHI(); 
        $view_data['master_city'] = $this->umsModel->getCityList();
        $view_data['master_branch'] = $this->umsModel->getMasterBranchList();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["user_data"] = $user_data;
        $view_data["enc_user_id"] = $enc_user_id;
        $this->load->view('UMS/AddUserRole', $view_data);
    }

    public function umsAddUsersRole($enc_user_id) {
        $user_role_locations_data = array();
        $view_data = array();
        $user_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $user_id = 0;

        if (!empty($enc_user_id)) {
            $user_id = $this->encrypt->decode($enc_user_id);
            if (!empty($user_id)) {
                $return_array = $this->umsModel->getUmsUserDetails($user_id);

                if ($return_array['status'] == 1) {
                    $user_data = $return_array['user_data'];
                    $enc_user_id = $this->encrypt->encode($user_data['user_id']);
                } else {
                    $errors_msg = "User details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }


        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $user_data["user_role_type_id"] = $this->input->post('user_role_type_id');
            $user_role_locations_data["user_role_state_id"] = $this->input->post('user_role_state_id');
            $scm_id = $this->input->post('user_role_scm_id');

            $this->form_validation->set_rules('user_role_type_id', 'User Role Type Id', 'required|numeric');
            $this->form_validation->set_rules('user_role_scm_id', 'User Role SCM Id', 'numeric|trim');

            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                $scmID = !empty($scm_id) ? $scm_id : NULL;
                $roletypeID = !empty($user_data["user_role_type_id"]) ? $user_data["user_role_type_id"] : NULL;
                $rolestateID = !empty($user_role_locations_data["user_role_state_id"]) ? $user_role_locations_data["user_role_state_id"] : NULL;

                $user_role_branch_id_array = $this->input->post('user_role_branch_id');

                $existing_role = $this->umsModel->checkExistingRoleType($roletypeID, $user_id);
                if ($existing_role == 1) {
                    $errors_msg_role_exist = "This User already assign this role type";
                    $this->session->set_flashdata('success_msg', $errors_msg_role_exist);
                    return redirect('ums/view-user/' . $enc_user_id, 'refresh');
                }

                $insert_user_array = array();
                $insert_user_role_locations = array();

                if (empty($user_data["user_role_state_id"])) {
                    $errors_msg = "Please Select state first";
                    $this->load->view('UMS/AddUserRole', $errors_msg);
                }

                if (!empty($roletypeID) && empty($scmID)) {
                    $conditions = ['user_id' => $user_id, 'role_id' => 0];
                    $users = $this->umsModel->select('users', $conditions, 'role_id');
                    if ($users->num_rows() > 0) {
                        $master_role_type = $this->umsModel->select('master_role_type', ['role_type_id' => $roletypeID], 'role_type_id, role_type_labels');
                        $roles = $master_role_type->row();
                        $this->umsModel->update('users', ['user_id' => $user_id], ['role_id' => $roletypeID, 'labels' => $roles->role_type_labels]);
                    }

                    $insert_user_array["user_role_type_id"] = $roletypeID;
                    $insert_user_array["user_role_user_id"] = $user_id;
                    $insert_user_array["user_role_created_on"] = date("Y-m-d H:i:s");
                    $insert_user_array["user_role_created_by"] = $_SESSION['isUserSession']['user_id'];
                    $insert_user_array["user_role_supervisor_role_id"] = $user_role_supervisor_role_id;
                    $user_role_id = $this->umsModel->insert('user_roles', $insert_user_array);
                }
                if (!empty($user_role_id)) {
                    if (!empty($user_role_branch_id_array)) {
                        $insert_data_array = $user_role_branch_id_array;
                        $location_type_id = 3; // branch
                    } else if (!empty($rolestateID)) {
                        $insert_data_array = $rolestateID;
                        $location_type_id = 2; //state
                    }
                    foreach ($insert_data_array as $key => $value) {
                        $insert_user_role_locations["user_rl_role_id"] = $user_role_id;
                        $insert_user_role_locations["user_rl_location_type_id"] = $location_type_id;
                        $insert_user_role_locations["user_rl_created_by"] = $_SESSION['isUserSession']['user_id'];
                        $insert_user_role_locations["user_rl_created_on"] = date("Y-m-d H:i:s");
                        $insert_user_role_locations['user_rl_location_id'] = $value;
                        $this->umsModel->insert('user_role_locations', $insert_user_role_locations);
                    }


                    $success_msg = "User role has been added successfully.";
                    $this->session->set_flashdata('success_msg', $success_msg);
                    $enc_user_id = $this->encrypt->encode($user_id);
                    return redirect('ums/view-user/' . $enc_user_id, 'refresh');
                }

                if (!empty($scmID) && !empty($roletypeID) && empty($user_role_id)) {
                    $insert_user_array["user_role_supervisor_role_id"] = $scmID;
                    $insert_user_array["user_role_type_id"] = $roletypeID;
                    $insert_user_array["user_role_user_id"] = $user_id;
                    $insert_user_array["user_role_created_on"] = date("Y-m-d H:i:s");
                    $insert_user_array["user_role_created_by"] = $_SESSION['isUserSession']['user_id'];
                    $user_role_id = $this->umsModel->insert('user_roles', $insert_user_array);
                    $success_msg = "User role has been added successfully.";
                    $this->session->set_flashdata('success_msg', $success_msg);
                    $enc_user_id = $this->encrypt->encode($user_id);
                    return redirect('ums/view-user/' . $enc_user_id, 'refresh');
                } else {
                    $errors_msg = "Some error occurred during creation of user role. Please try again.";
                }
            }
        }

        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();
        $view_data['master_state'] = $this->umsModel->getStateList();
        $view_data['master_city'] = $this->umsModel->getCityList();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["user_data"] = $user_data;
        $view_data["enc_user_id"] = $enc_user_id;
        $this->load->view('UMS/AddUserRole', $view_data);
    }

    public function umsEditUserRole($enc_user_id) {
        $view_data = array();
        $user_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = true;
        $user_id = 0;

        if (!empty($enc_user_id)) {
            $user_role_id = $this->encrypt->decode($enc_user_id);
            if (!empty($user_role_id)) {
                $return_array = $this->umsModel->getUmsUserRoleDetails($user_role_id);
                if ($return_array['status'] == 1) {
                    $user_role_data = $return_array['user_role_data'];
                    $enc_user_role_id = $this->encrypt->encode($user_role_data['user_role_id']);
                } else {
                    $errors_msg = "User details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }

        foreach ($user_role_data as $key => $value) {
            $user_rl_location_type_id = 2; // 2 => state
            $state_list = $this->umsModel->getUmsStateList($user_role_id, $user_rl_location_type_id, false);
        }

        $view_data['export_master_list'] = $this->umsModel->getExportMasterList();
        $view_data['export_user_permission_list'] = $this->umsModel->getUmsExportPermissionList($user_role_id, false);

        $view_data['mis_master_list'] = $this->umsModel->getMISMasterList();
        $view_data['mis_user_permission_list'] = $this->umsModel->getUmsMISPermissionList($user_role_id, false);

        $view_data['master_branch'] = $this->umsModel->getMasterBranchList();
        $view_data['branch_list'] = $this->umsModel->getUmsUserMappedBranchList($user_role_id, 3, false); // 3 => branch

        $view_data['master_role_type'] = $this->umsModel->getRoleList();
        $view_data['master_user_status'] = $this->umsModel->getUserStatus();
        $view_data['get_scm'] = $this->umsModel->getSCM($user_role_id);
        $view_data['get_chi'] = $this->umsModel->getCHI($user_role_id);
        $view_data['getMappedSCM'] = $this->umsModel->getSCMSelectedvalue($user_role_id);

//        $conditions['LD.lead_id'] = $lead_id;
//        $conditions['URL.user_rl_location_type_id'] = 2; // 1 => city, 2 =>state, 3 => branch
//        $conditions['visit_type_id'] = $visit_type_id;
//        $this->load->model('Collection_Model', 'Collection');
//        $scm_user_lists = $this->Collection->scm_user_lists($conditions);

        $view_data['selectSCMId'] = $this->umsModel->getSCMSelectedvalue($user_role_id);
        $view_data['master_state'] = $this->umsModel->getStateList();
        $view_data['master_city'] = $this->umsModel->getCityList();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["user_data"] = $user_role_data;
        $view_data["state_list"] = $state_list;
        $view_data["enc_user_role_id"] = $enc_user_role_id;
        $this->load->view('UMS/UpdateUserRole', $view_data);
    }

    public function user_location_permission($user_role_locations_data) {
        $return_data = array('success_msg' => "", 'errors_msg' => "", 'data' => array());

        $insert_user_role_locations = array();
        $update_user_role_locations = array();
        $user_role_location_state = array();

        $user_role_id = $user_role_locations_data['user_role_id'];
        $user_state_array = $user_role_locations_data['user_role_state_id'];

        $user_rl_location_type_id = 2;
        $state_list = $this->umsModel->getUmsStateList($user_role_id, $user_rl_location_type_id, true);

        if ($state_list['status'] == 1) {
            foreach ($state_list['state_list'] as $role_mapped_state) {
                $user_role_location_state[] = $role_mapped_state['user_rl_location_id'];
            }
        }

        $array_diff_master = array_diff($user_state_array, $user_role_location_state);

        if (!empty($array_diff_master)) {
            foreach ($array_diff_master as $state_id) {
                $insert_user_role_locations["user_rl_role_id"] = $user_role_locations_data['user_role_id'];
                $insert_user_role_locations["user_rl_location_type_id"] = $user_rl_location_type_id; //location type 2 is state
                $insert_user_role_locations['user_rl_location_id'] = $state_id;
                $insert_user_role_locations["user_rl_created_by"] = $_SESSION['isUserSession']['user_id'];
                $insert_user_role_locations["user_rl_created_on"] = date("Y-m-d H:i:s");
                $insert_user_role_locations["user_rl_updated_on"] = date("Y-m-d H:i:s");
                $user_rl_role_id = $this->umsModel->insert('user_role_locations', $insert_user_role_locations);

                $return_data['success_msg'] = "Added Successfully";
            }
        }

        if (!empty($user_state_array)) {
            $master_state_values = array_values($user_state_array);
            $this->umsModel->update('user_role_locations', ['user_rl_location_type_id' => $user_rl_location_type_id, 'user_rl_role_id' => $user_role_id], ['user_rl_active' => 1, 'user_rl_deleted' => 0], 'user_rl_location_id', $master_state_values);

            $return_data['success_msg'] = "Updated Successfully";
        }

        $array_diff_role_location_state = array_diff($user_role_location_state, $user_state_array);

        if (!empty($array_diff_role_location_state)) {

            $master_state_values = array_values($array_diff_role_location_state);

            $update_user_role_locations["user_rl_updated_by"] = $_SESSION['isUserSession']['user_id'];
            $update_user_role_locations["user_rl_updated_on"] = date("Y-m-d H:i:s");
            $update_user_role_locations['user_rl_active'] = 0;
            $update_user_role_locations['user_rl_deleted'] = 1;

            $return_update_flag = $this->umsModel->update('user_role_locations', ['user_rl_location_type_id' => $user_rl_location_type_id, 'user_rl_role_id' => $user_role_id], $update_user_role_locations, 'user_rl_location_id', $master_state_values);

            $return_data['success_msg'] = "Updated Successfully";
        }

        return $return_data;
    }

    public function user_branch_permission($branch_data_array) {
        $return_data = array('success_msg' => "", 'errors_msg' => "", 'data' => array());

        $insert_branch_data = array();
        $update_branch_data = array();
        $user_role_location_branch = array();

        if (empty($branch_data_array['user_id'])) {
            $return_data['errors_msg'] = "User Id not found to allow export data.";
        } else if (empty($branch_data_array['user_branch_array'])) {
            $return_data['errors_msg'] = "Please select list from export list.";
        } else {

            $user_id = $branch_data_array['user_id'];
            $user_role_id = $branch_data_array['user_role_id'];
            $user_branch_array = $branch_data_array['user_branch_array'];

            $user_rl_location_type_id = 3;
            $state_list = $this->umsModel->getUmsStateList($user_role_id, $user_rl_location_type_id, true);
            if ($state_list['status'] == 1) {
                foreach ($state_list['state_list'] as $role_mapped_state) {
                    $user_role_location_branch[] = $role_mapped_state['user_rl_location_id'];
                }
            }

            $array_diff_master = array_diff($user_branch_array, $user_role_location_branch);

            if (!empty($array_diff_master)) {
                foreach ($array_diff_master as $branch_id) {
                    $insert_branch_data["user_rl_role_id"] = $user_role_id;
                    $insert_branch_data["user_rl_location_type_id"] = $user_rl_location_type_id; //location type 3 is branch
                    $insert_branch_data['user_rl_location_id'] = $branch_id;
                    $insert_branch_data["user_rl_created_by"] = $_SESSION['isUserSession']['user_id'];
                    $insert_branch_data["user_rl_created_on"] = date("Y-m-d H:i:s");
                    $insert_branch_data["user_rl_updated_on"] = date("Y-m-d H:i:s");

                    $user_rl_role_id = $this->umsModel->insert('user_role_locations', $insert_branch_data);

                    $return_data['success_msg'] = "Added Successfully";
                }
            }

            if (!empty($user_branch_array)) {
                $master_state_values = array_values($user_branch_array);
                $this->umsModel->update('user_role_locations', ['user_rl_location_type_id' => $user_rl_location_type_id, 'user_rl_role_id' => $user_role_id], ['user_rl_active' => 1, 'user_rl_deleted' => 0], 'user_rl_location_id', $master_state_values);

                $return_data['success_msg'] = "Updated Successfully";
            }

            $array_diff_role_location_branch = array_diff($user_role_location_branch, $user_branch_array);

            if (!empty($array_diff_role_location_branch)) {

                $master_state_values = array_values($array_diff_role_location_branch);

                $update_branch_data["user_rl_updated_by"] = $_SESSION['isUserSession']['user_id'];
                $update_branch_data["user_rl_updated_on"] = date("Y-m-d H:i:s");
                $update_branch_data['user_rl_active'] = 0;
                $update_branch_data['user_rl_deleted'] = 1;

                $return_update_flag = $this->umsModel->update('user_role_locations', ['user_rl_location_type_id' => $user_rl_location_type_id, 'user_rl_role_id' => $user_role_id], $update_branch_data, 'user_rl_location_id', $master_state_values);

                $return_data['success_msg'] = "Updated Successfully";
            }
        }

        return $return_data;
    }

    public function user_export_permission($user_export_data_array) {
        $return_data = array('success_msg' => "", 'errors_msg' => "", 'data' => array());
        $user_role_export = array();

        if (empty($user_export_data_array['user_id'])) {
            $return_data['errors_msg'] = "User Id not found to allow export data.";
        } else if (empty($user_export_data_array['user_export_array'])) {
            $return_data['errors_msg'] = "Please select list from export list.";
        } else {

            $user_id = $user_export_data_array['user_id'];
            $user_role_id = $user_export_data_array['user_role_id'];
            $user_export_array = $user_export_data_array['user_export_array'];

            $master_export_list = $this->umsModel->getUmsExportPermissionList($user_role_id, true);
            if ($master_export_list['status'] == 1) {
                foreach ($master_export_list['export_list'] as $role_export) {
                    $user_role_export[] = $role_export['export_permission_export_id'];
                }
            }

            $array_diff_master_export = array_diff($user_export_array, $user_role_export);

            if (!empty($array_diff_master_export)) {

                foreach ($array_diff_master_export as $export_id) {
                    $insert_user_export['export_permission_export_id'] = $export_id;
                    $insert_user_export['export_permission_user_role_id'] = $user_role_id;
                    $insert_user_export['export_permission_user_id'] = $user_id;
                    $insert_user_export["export_permission_created_user_id"] = $_SESSION['isUserSession']['user_id'];
                    $insert_user_export["export_permission_created_at"] = date("Y-m-d H:i:s");
                    $insert_user_export['export_permission_active '] = 1;
                    $insert_user_export['export_permission_deleted '] = 0;
                    if (!empty($export_id)) {
                        $user_rl_role_id = $this->umsModel->insert('user_export_permission', $insert_user_export);
                    }

                    $return_data['success_msg'] = "Added Successfully";
                }
            }

            if (!empty($user_export_array)) {
                $master_export_values = array_values($user_export_array);
                $update_user_export_permission = array();
                $update_user_export_permission["export_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                $update_user_export_permission["export_permission_updated_at"] = date("Y-m-d H:i:s");
                $update_user_export_permission['export_permission_active'] = 1;
                $update_user_export_permission['export_permission_deleted'] = 0;
                $this->umsModel->update('user_export_permission', ['export_permission_user_role_id' => $user_role_id], $update_user_export_permission, 'export_permission_export_id', $master_export_values);

                $return_data['success_msg'] = "Updated Successfully";
            }

            $array_diff_role_export = array_diff($user_role_export, $user_export_array);

            if (!empty($array_diff_role_export)) {

                $master_export_values = array_values($array_diff_role_export);

                $update_user_export_permission = array();
                $update_user_export_permission["export_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                $update_user_export_permission["export_permission_updated_at"] = date("Y-m-d H:i:s");
                $update_user_export_permission['export_permission_active'] = 0;
                $update_user_export_permission['export_permission_deleted'] = 1;

                $return_update_flag = $this->umsModel->update('user_export_permission', ['export_permission_user_role_id' => $user_role_id], $update_user_export_permission, 'export_permission_export_id', $master_export_values);
                $return_data['success_msg'] = "Updated Successfully";
            }
        }

        return $return_data;
    }

    public function user_mis_permission($user_mis_data_array) {
        $return_data = array('success_msg' => "", 'errors_msg' => "", 'data' => array());
        $user_role_mis = array();

        if (empty($user_mis_data_array['user_id'])) {
            $return_data['errors_msg'] = "User Id not found to allow mis data.";
        } else if (empty($user_mis_data_array['user_mis_array'])) {
            $return_data['errors_msg'] = "Please select list from mis list.";
        } else {

            $user_id = $user_mis_data_array['user_id'];
            $user_role_id = $user_mis_data_array['user_role_id'];
            $user_mis_array = $user_mis_data_array['user_mis_array'];

            $master_mis_list = $this->umsModel->getUmsMISPermissionList($user_role_id, true);
            if ($master_mis_list['status'] == 1) {
                foreach ($master_mis_list['mis_list'] as $role_mis) {
                    $user_role_mis[] = $role_mis['mis_permission_mis_id'];
                }
            }

            $array_diff_master_mis = array_diff($user_mis_array, $user_role_mis);

            if (!empty($array_diff_master_mis)) {

                foreach ($array_diff_master_mis as $mis_id) {
                    $insert_user_mis['mis_permission_mis_id'] = $mis_id;
                    $insert_user_mis['mis_permission_user_role_id'] = $user_role_id;
                    $insert_user_mis['mis_permission_user_id'] = $user_id;
                    $insert_user_mis["mis_permission_created_user_id"] = $_SESSION['isUserSession']['user_id'];
                    $insert_user_mis["mis_permission_created_at"] = date("Y-m-d H:i:s");
                    $insert_user_mis['mis_permission_active '] = 1;
                    $insert_user_mis['mis_permission_deleted '] = 0;
                    if (!empty($mis_id)) {
                        $user_rl_role_id = $this->umsModel->insert('user_mis_permission', $insert_user_mis);
                    }
                }
                $return_data['success_msg'] = "Added Successfully";
            }

            if (!empty($user_mis_array)) {
                $master_mis_values = array_values($user_mis_array);
                $update_user_mis_permission = array();
                $update_user_mis_permission["mis_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                $update_user_mis_permission["mis_permission_updated_at"] = date("Y-m-d H:i:s");
                $update_user_mis_permission['mis_permission_active'] = 1;
                $update_user_mis_permission['mis_permission_deleted'] = 0;
                $this->umsModel->update('user_mis_permission', ['mis_permission_user_role_id' => $user_role_id], $update_user_mis_permission, 'mis_permission_mis_id', $master_mis_values);

                $return_data['success_msg'] = "Updated Successfully";
            }

            $array_diff_role_mis = array_diff($user_role_mis, $user_mis_array);

            if (!empty($array_diff_role_mis)) {

                $master_mis_values = array_values($array_diff_role_mis);

                $update_user_mis_permission = array();
                $update_user_mis_permission["mis_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                $update_user_mis_permission["mis_permission_updated_at"] = date("Y-m-d H:i:s");
                $update_user_mis_permission['mis_permission_active'] = 0;
                $update_user_mis_permission['mis_permission_deleted'] = 1;

                $return_update_flag = $this->umsModel->update('user_mis_permission', ['mis_permission_user_role_id' => $user_role_id], $update_user_mis_permission, 'mis_permission_mis_id', $master_mis_values);
                $return_data['success_msg'] = "Updated Successfully";
            }
        }

        return $return_data;
    }

    public function umsEditUsersRole($enc_user_role_id) {

        $view_data = array();
        $return_data = array();
        $user_permission_data = array();
        $location_data_array = array();
        $export_data_array = array();
        $mis_data_array = array();
        $branch_data_array = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = true;
        $user_id = 0;
        $active = 0;
        $deleted = 1;
        user_role_scm_id;
        user_role_supervisor_role_id;

        $user_role_id = $this->encrypt->decode($enc_user_role_id);
        if (!empty($enc_user_role_id)) {
            if (!empty($user_role_id)) {
                $return_array = $this->umsModel->getUmsUserRoleDetails($user_role_id);
                if ($return_array['status'] == 1) {
                    $user_role_data = $return_array['user_role_data'];
                    $enc_user_role_id = $this->encrypt->encode($user_role_data['user_role_id']);

                    $user_id = $user_role_data['user_role_user_id'];
                } else {
                    $errors_msg = "User role details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $user_role_state_id_array = $this->input->post('user_role_state_id');
            $user_role_active = $this->input->post('user_role_active');
            $user_export_id_array = $this->input->post('user_export_id');
            $user_mis_id_array = $this->input->post('user_mis_id');
            $user_role_branch_id_array = $this->input->post('user_role_branch_id');
            $user_role_scm_id = $this->input->post('user_role_scm_id');
            $user_role_supervisor_role_id = $this->input->post('user_role_supervisor_role_id');

            $user_permission_data['user_id'] = $user_id;
            $user_permission_data['user_role_id'] = $user_role_id;
            if ($user_role_active == 1) {
                $active = 1;
                $deleted = 0;
            }

            $conditions['user_role_id'] = $user_role_id;
            $user_role_type_id = $this->db->select('user_role_type_id')->where($conditions)->from('user_roles')->get()->row_array();

            $conditions['user_role_user_id'] = $user_id;
            $conditions['user_role_active'] = $active;
            $conditions['user_role_deleted'] = $deleted;

            $user_role_active_object = $this->db->select('user_role_type_id, user_role_active, user_role_deleted')->where($conditions)->from('user_roles')->get();
            $user_role_active_array = $user_role_active_object->row_array();

            if (isset($user_role_active) && $user_role_active_object->num_rows() == 0) {
                $update_user_role_active['user_role_active'] = $active;
                $update_user_role_active['user_role_deleted'] = $deleted;
                $update_user_role_active['user_role_updated_by'] = $_SESSION['isUserSession']['user_id'];
                $update_user_role_active['user_role_updated_on'] = date("Y-m-d H:i:s");
                $update_user_role_status = $this->umsModel->update('user_roles', ['user_role_id' => $user_role_id], $update_user_role_active);

                $status = (($user_role_active == 1) ? "Active" : "Inactive");
                $success_msg = "<b>" . $status . "</b> - User status updated successfully."; // Active or Inactive
            }

            if (!empty($user_role_scm_id)) {
                $update_user_role_active['user_role_supervisor_role_id'] = $user_role_scm_id;
                $update_user_role_status = $this->umsModel->update('user_roles', ['user_role_id' => $user_role_id], $update_user_role_active);
            }
            if (!empty($user_role_supervisor_role_id)) {
                $update_user_role_active['user_role_supervisor_role_id'] = $user_role_supervisor_role_id;
                $update_user_role_status = $this->umsModel->update('user_roles', ['user_role_id' => $user_role_id], $update_user_role_active);
            }

            if ($user_role_active == 1 && ($user_role_active_array['user_role_active'] == 1)) {

                if (!empty($user_role_state_id_array) && !empty($user_role_id)) { // && in_array($user_role_id, [8])
                    $location_data_array['user_id'] = $user_id;
                    $location_data_array['user_role_id'] = $user_role_id;
                    $location_data_array['user_role_state_id'] = $user_role_state_id_array;

                    $return_data = $this->user_location_permission($location_data_array);
                }

                if (!empty($user_export_id_array) && !empty($user_role_id)) {
                    $export_data_array['user_id'] = $user_id;
                    $export_data_array['user_role_id'] = $user_role_id;
                    $export_data_array['user_export_array'] = $user_export_id_array;

                    $return_data = $this->user_export_permission($export_data_array);
                } else if (empty($user_export_id_array) && !empty($user_role_id)) {
                    $update_user_export_permission = array();
                    $update_user_export_permission["export_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                    $update_user_export_permission["export_permission_updated_at"] = date("Y-m-d H:i:s");
                    $update_user_export_permission['export_permission_active'] = 0;
                    $update_user_export_permission['export_permission_deleted'] = 1;
                    $this->umsModel->update('user_export_permission', ['export_permission_user_role_id' => $user_role_id], $update_user_export_permission);
                }

                if (!empty($user_mis_id_array) && !empty($user_role_id)) {
                    $mis_data_array['user_id'] = $user_id;
                    $mis_data_array['user_role_id'] = $user_role_id;
                    $mis_data_array['user_mis_array'] = $user_mis_id_array;

                    $return_data = $this->user_mis_permission($mis_data_array);
                } else if (empty($user_mis_id_array) && !empty($user_role_id)) {
                    $update_user_mis_permission = array();
                    $update_user_mis_permission["mis_permission_updated_user_id"] = $_SESSION['isUserSession']['user_id'];
                    $update_user_mis_permission["mis_permission_updated_at"] = date("Y-m-d H:i:s");
                    $update_user_mis_permission['mis_permission_active'] = 0;
                    $update_user_mis_permission['mis_permission_deleted'] = 1;
                    $this->umsModel->update('user_mis_permission', ['mis_permission_user_role_id' => $user_role_id], $update_user_mis_permission);
                }

                if (!empty($user_role_branch_id_array) && !empty($user_role_id)) {
                    $branch_data_array['user_id'] = $user_id;
                    $branch_data_array['user_role_id'] = $user_role_id;
                    $branch_data_array['user_branch_array'] = $user_role_branch_id_array;

                    $return_data = $this->user_branch_permission($branch_data_array);
                }

                if ($return_data['success_msg']) {
                    $success_msg = $return_data['success_msg'];
                } else {
                    $errors_msg = $return_data['errors_msg'];
                }
            }
        }

        if (!empty($success_msg)) {
            $this->session->set_flashdata('success_msg', $success_msg);
        } else {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }

        return redirect(base_url('ums/edit-user-role/' . $this->encrypt->encode($user_role_id)), 'refresh');
    }

    public function umsEditUsersRoleSCM($enc_user_role_id) {
        $update_supervisor_role = array();
        $view_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = true;
        // $user_id = 0;
        $active = 0;
        $deleted = 1;

        if (!empty($enc_user_role_id)) {
            $user_role_id = $this->encrypt->decode($enc_user_role_id);
            if (!empty($user_role_id)) {
                $return_array = $this->umsModel->getUmsUserRoleDetails($user_role_id);
                if ($return_array['status'] == 1) {
                    $user_role_data = $return_array['user_role_data'];
                    $enc_user_role_id = $this->encrypt->encode($user_role_data['user_role_id']);
                    $user_id = $this->encrypt->encode($user_role_data['user_role_user_id']);
                } else {
                    $errors_msg = "User role details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('user_role_scm_id', 'User Role SCM Id', 'required|numeric');
            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                $scm_id = $this->input->post('user_role_scm_id');
                $user_role_active = $this->input->post('user_role_active');
                if ($user_role_active == 1) {
                    $active = 1;
                    $deleted = 0;
                }

                if (!empty($scm_id)) {
                    $update_supervisor_role = [
                        'user_role_active' => $active,
                        'user_role_deleted' => $deleted,
                        'user_role_supervisor_role_id' => $scm_id,
                        'user_role_updated_by' => $_SESSION['isUserSession']['user_id'],
                        'user_role_updated_on' => date("Y-m-d H:i:s"),
                    ];

                    $return_update_flag = $this->db->where('user_role_id', $user_role_id)->update('user_roles', $update_supervisor_role);
                    if (!empty($return_update_flag)) {
                        $success_msg = "SCM has been updated successfully.";
                        $this->session->set_flashdata('success_msg_role', $success_msg);
                        return redirect('ums/view-user/' . $user_id, 'refresh');
                    } else {
                        $errors_msg = "Some error occurred during to assign the state collection manager. Please try again.";
                    }

                    if (!empty($errors_msg)) {
                        $this->session->set_flashdata('errors_msg', $errors_msg);
                    }
                } else {
                    $errors_msg = "We do not found thus SCM id";
                }
            }
        }

        else {

            $view_data['master_role_type'] = $this->umsModel->getRoleList();
            $view_data['master_user_status'] = $this->umsModel->getUserStatus();
            $view_data['master_state'] = $this->umsModel->getStateList();
            $view_data['master_city'] = $this->umsModel->getCityList();
            $view_data["javascript_files"] = $this->javascript_files;
            $view_data["errors_msg"] = $errors_msg;
            $view_data["success_msg"] = $success_msg;
            $view_data["update_flag"] = $update_flag;
            $view_data["enc_user_role_id"] = $enc_user_role_id;
            $this->load->view('UMS/UpdateUserRole', $view_data);
        }
    }

    public function updateUserRoleStatus($enc_user_role_id) {
        $view_data = array();
        $user_role_locations_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = true;

        if (!empty($enc_user_role_id)) {
            $user_role_id = $this->encrypt->decode($enc_user_role_id);
            // echo $user_role_id; exit;
            if (!empty($user_role_id)) {
                $return_array = $this->umsModel->getUmsUserRoleDetails($user_role_id);
                if ($return_array['status'] == 1) {
                    $user_role_data = $return_array['user_role_data'];
                    $enc_user_role_id = $this->encrypt->encode($user_role_data['user_role_id']);
                    $user_id = $this->encrypt->encode($user_role_data['user_role_user_id']);
                } else {
                    $errors_msg = "User role details not found.";
                }
            } else {
                $errors_msg = "Invalid Access..";
            }
        } else {
            $errors_msg = "Invalid Access.";
        }


        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }

        if (isset($user_role_data['user_role_active'])) {
            if ($user_role_data['user_role_active'] == 1) {
                $active = 0;
                $deleted = 1;
            } else {
                $active = 1;
                $deleted = 0;
            }

            $update_user_role_active['user_role_active'] = $active;
            $update_user_role_active['user_role_deleted'] = $deleted;
            $update_user_role_active['user_role_updated_by'] = $_SESSION['isUserSession']['user_id'];
            $update_user_role_active['user_role_updated_on'] = date("Y-m-d H:i:s");
            $return_update_flag = $this->umsModel->update('user_roles', ['user_role_id' => $user_role_id], $update_user_role_active);
        }
        if (!empty($return_update_flag)) {
            $success_msg = "User Role Status updated.";
            $this->session->set_flashdata('success_msg', $success_msg);
            return redirect('ums/view-user/' . $user_id, 'refresh');
        } else {
            $errors_msg = "Some error occurred during to assign the state collection manager. Please try again.";
        }
    }

}

?>
