<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class AdminController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model');
        $this->load->model('State_Model');
        $this->load->model('User_Model');
        $this->load->model('UserRole_Model');
        $this->load->model('Company_Model');
        $this->load->model('Product_Model');

        $login = new IsLogin();
        $login->index();
    }

    public function index() {

        $this->load->library("pagination");

        $url = (base_url() . $this->uri->segment(1));
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;

        $conditions = array();
        
        if(!empty($_POST['filter_role'])){
            $conditions["lmsUser.role_id"]= intval($_POST['filter_role']);
        }
        
        if(!empty($_POST['filter_input'])){
            $conditions["(lmsUser.name LIKE '%".$_POST['filter_input']."%' OR lmsUser.email LIKE '".$_POST['filter_input']."%' OR lmsUser.mobile = '".$_POST['filter_input']."')"]=null;
        }

        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->User_Model->countLeads($conditions);
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

        $data['userDetails'] = $this->User_Model->index($conditions, $config["per_page"], $page);

        $data["links"] = $this->pagination->create_links();
        $data["master_state"] = $this->User_Model->getStateList();
        $data["master_city"] = $this->User_Model->getCityList();
        $data["master_role"] = $this->User_Model->getRoleList();
//        traceObject($data["master_state"]);
        $this->load->view('Admin/index', $data);
    }

    public function addUsers() {
        $user_id = ['user_id' => $_SESSION['isUserSession']['user_id']];
        $users = $this->User_Model->select($user_id);
        $data['user'] = $users->row();
        $data['company'] = $this->Company_Model->index();
        $data['product'] = $this->Product_Model->index([$conditions => company_id]);
        $data['userRole'] = $this->UserRole_Model->index();
        $data['states'] = $this->State_Model->index();
        $this->load->view('Admin/addUser', $data);
    }

    public function getUserCenter() {
        if (!empty($_POST['state_id'])) {
            $data = 'id, city';
            $conditions = ['state_id' => $_POST['state_id']];
            $cityList = $this->State_Model->getCity($conditions, $data);
            $cities = $cityList->result_array();
            echo json_encode($cities);
        }
    }

    public function adminSaveUser() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('firstName', 'First Name', 'trim|required');
            $this->form_validation->set_rules('lastName', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
            $this->form_validation->set_rules('userRole', 'User Role', 'trim|required');
            $this->form_validation->set_rules('restrectedBranchUser', 'Branch', 'trim|required');
            $this->form_validation->set_rules('centerName[]', 'Center Name', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = intval($this->input->post('user_id'));
                $company_id = intval($this->input->post('company_id'));
                $product_id = intval($this->input->post('product_id'));
                $firstName = strval($this->input->post('firstName'));
                $lastName = strval($this->input->post('lastName'));
                $fullName = $firstName . " " . $lastName;
                $email = strval($this->input->post('email'));
                $mobile = intval($this->input->post('mobile'));
                $role_id = intval($this->input->post('userRole'));

                $roles = $this->UserRole_Model->select(['role_id' => $role_id]);
                $rowRole = $roles->row();
                $restrectedBranchUser = strval($this->input->post('restrectedBranchUser'));
                $centerName = strval($this->input->post('centerName'));
                $centerName = implode(", ", $centerName);
                $password = ucfirst("loanwalle" . strtoupper(chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999)));
                $hash = MD5($password);

                $data = [
                    'company_id' => $company_id,
                    'product_id' => $product_id,
                    'name' => $fullName,
                    'email' => $email,
                    'password' => $hash,
                    'mobile' => $mobile,
                    'branch' => $restrectedBranchUser,
                    'center' => $centerName,
                    'role_id' => $role_id,
                    'labels' => $rowRole->labels,
                    'role' => $rowRole->heading,
                    'status' => "Active",
                    'ip' => ip,
                    'created_by' => $user_id,
                    'created_on' => timestamp
                ];
                if ($this->User_Model->insert($data)) {
                    echo 1;
                } else {
                    echo 0;
                }
            }
        } else {
            echo "Session Expired. Please login first.";
            $this->islogin();
        }
    }

    public function adminEditUser($user_id) {
        $data['userRole'] = $this->UserRole_Model->index();
        $data['states'] = $this->State_Model->index();
        $users = $this->User_Model->select(['user_id' => $user_id]);
        $data['user'] = $users->row();
        $data['company'] = $this->Company_Model->index();
        $data['product'] = $this->Product_Model->index([$conditions => company_id]);
        $this->load->view('Admin/editUser', $data);
    }

    public function adminUpdateUser() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('userId', 'User ID', 'trim|required');
            $this->form_validation->set_rules('company_id', 'Company ID', 'trim|required');
            $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required');
            $this->form_validation->set_rules('firstName', 'First Name', 'trim|required');
            $this->form_validation->set_rules('lastName', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
            $this->form_validation->set_rules('userRole', 'User Role', 'trim|required');
            $this->form_validation->set_rules('restrectedBranchUser', 'Branch', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo "All fields required";
            } else {
                $user_id = intval($this->input->post('userId'));
                $company_id = intval($this->input->post('company_id'));
                $product_id = intval($this->input->post('product_id'));
                $firstName = strval($this->input->post('firstName'));
                $lastName = strval($this->input->post('lastName'));
                $fullName = $firstName . " " . $lastName;
                $email = strval($this->input->post('email'));
                $mobile = intval($this->input->post('mobile'));
                $role_id = intval($this->input->post('userRole'));
                $restrectedBranchUser = strval($this->input->post('restrectedBranchUser'));
                $centerName = strval($this->input->post('centerName'));
                $status = strval($this->input->post('status'));

                $roles = $this->UserRole_Model->select(['role_id' => $role_id]);
                $rowRole = $roles->row();
                $data = array(
                    'company_id' => $company_id,
                    'product_id' => $product_id,
                    'name' => $fullName,
                    'email' => $email,
                    'mobile' => $mobile,
                    'branch' => $restrectedBranchUser,
                    'role_id' => $role_id,
                    // 'labels'		=> $rowRole->labels,	
                    // 'role'			=> $rowRole->heading,	
                    'status' => $status,
                    'ip' => ip,
                    'updated_on' => timestamp
                );
                // echo "<pre>"; print_r($data); exit;
                if (isset($centerName)) {
                    $data = array_merge($data, ['center' => implode(",", $centerName)]);
                }
                echo $result = $this->User_Model->update(['user_id' => $user_id], $data);
            }
        } else {
            echo "Session Expired. Please login first.";
        }
    }

    public function adminTaskSetelment() {
        echo "Admin <pre>";
        print_r($_POST);
        exit;
    }

}

?>
