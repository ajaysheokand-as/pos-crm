<?php
defined('BASEPATH') or exit('No direct script access allowed');
class BlacklistedPincodeController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('BlacklistedPincode_Model', 'blacklistedPincodeModel');
        define('created_on', date('Y-m-d H:i:s'));
        set_time_limit(300);
        date_default_timezone_set('Asia/Kolkata');
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
        $login = new IsLogin();
        $login->index();
    }
    
    public function index() { 
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $conditions = array();
        if(!empty($_POST['filter_input'])) {
            $conditions["(master_blacklist_pincode.mbp_pincode LIKE '%" . strval($_POST['filter_input']) . "%')"] = null;
        }
        
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->blacklistedPincodeModel->blacklistedPincodeCount($conditions);
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
        $data['blacklistedPincodeDetails'] = $this->blacklistedPincodeModel->blacklistedPincodeList($config["per_page"],$page,$conditions);
        $data["links"] = $this->pagination->create_links();
        $data["javascript_files"] = $this->javascript_files;        
        $this->load->view('Blacklisted/blacklisted_pincode', $data);
    }
    
    public function blacklistedPincodeDelete() {
        $mbp_id = $this->encrypt->decode($_POST['mbp_id']);
        $check_data = $this->db->select('*')->from('master_blacklist_pincode')->where('mbp_active',1)->where('mbp_id',$mbp_id)->get();       
        if($check_data->num_rows() > 0) {
            $response = $this->db->where(['mbp_id'=>$mbp_id])->update('master_blacklist_pincode',['mbp_active'=>0,'mbp_deleted'=>1]);
            if($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else { 
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Blacklisted/blacklisted_pincode');
        }
    }
    
    public function addBlacklistedPincode() {        
        $view_data = array();
        $blacklisted_pincode_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $mbp_id = 0;
        if($this->input->server('REQUEST_METHOD') == 'POST') { 
            $blacklisted_pincode_data["mbp_id"]        = $this->input->post('mbp_id');
            $blacklisted_pincode_data["mbp_pincode"]   = $this->input->post('mbp_pincode');
            $this->form_validation->set_rules('mbp_pincode','Pincode','required');
            if($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                if($this->blacklistedPincodeModel->checkBlacklistedPincode($blacklisted_pincode_data["mbp_pincode"])) {
                    $errors_msg = "Pincode is already exist. Please enter new pincode";
                } else {
                    $insert_blacklisted_pincode_array = array(); 
                    $insert_blacklisted_pincode_array["mbp_id"]          = $blacklisted_pincode_data["mbp_id"];
                    $insert_blacklisted_pincode_array["mbp_pincode"]     = $blacklisted_pincode_data["mbp_pincode"];
                    $insert_blacklisted_pincode_array["mbp_publish_by"]  = $_SESSION['isUserSession']['user_id'];
                    $insert_blacklisted_pincode_array["mbp_created_on"]  = date("Y-m-d H:i:s");
                    $insert_blacklisted_pincode_array["mbp_updated_on"]  = date("Y-m-d H:i:s");                
                    $mbp_id = $this->db->insert('master_blacklist_pincode',$insert_blacklisted_pincode_array);
                    if(!empty($mbp_id)) {
                        $success_msg = "Blacklisted pincode has been added successfully.";
                        $this->session->set_flashdata('success_msg',$success_msg);
                        $enc_mbp_id = $this->encrypt->encode($mbp_id);
                        return redirect(base_url('support/sysytem-blacklisted-pincode'),'refresh');
                    } else {
                        $errors_msg = "Some error occurred during creation of blacklisted pincode. Please try again.";
                    }
                }
            }
            if(!empty($errors_msg)) {
                $this->session->set_flashdata('errors_msg', $errors_msg);
            }
        }        
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["blacklisted_pincode_data"] = $blacklisted_pincode_data;
        $this->load->view('Blacklisted/addUpdateBlacklistedPincode',$view_data);
    }
    
    public function editBlacklistedPincode($mbp_id = "") {
        $view_data = array();
        $blacklisted_pincode_data = array();
        $success_msg = "";
        $errors_msg = "";	
        $update_flag = false;
        if(!empty($mbp_id)) {
            $mbp_id = intval($this->encrypt->decode($mbp_id));
            $return_array = $this->blacklistedPincodeModel->getBlacklistedPincodeDetails($mbp_id);
            if($return_array['status'] == 1) {
                $update_flag = true;
                $mbp_id = $return_array['blacklisted_pincode_data']['mbp_id'];
                $blacklisted_pincode_data = $return_array['blacklisted_pincode_data'];
                if($this->input->server('REQUEST_METHOD') == 'POST') {
                    $blacklisted_pincode_data["mbp_id"]        = $this->input->post('mbp_id');
                    $blacklisted_pincode_data["mbp_pincode"]   = $this->input->post('mbp_pincode');                    
                    $this->form_validation->set_rules('mbp_pincode','Pincode','required');                 
                    if($this->form_validation->run() == FALSE) {
                        $errors_msg = validation_errors();
                    } else {
                        if($this->blacklistedPincodeModel->checkBlacklistedPincode($blacklisted_pincode_data["mbp_pincode"],$mbp_id)) {
                            $errors_msg = "Pincode already exist. Please try with different pincode.";
                        } else {
                            $update_blacklisted_pincode_array                  = array(); 
                            $update_blacklisted_pincode_array["mbp_pincode"]   = $this->input->post('mbp_pincode'); 
                            $update_blacklisted_pincode_array["mbp_updated_on"] = date("Y-m-d H:i:s");                            
                            $return_update_flag = $this->db->where(['mbp_id'=>$mbp_id])->update('master_blacklist_pincode',$update_blacklisted_pincode_array);
							//echo $this->db->last_query();die;
                            if($return_update_flag) {
                                $mbp_id = $this->encrypt->encode($mbp_id);
                                $success_msg = "Blacklisted pincode has been updated successfully.";
                                $this->session->set_flashdata('success_msg', $success_msg);
                                return redirect(base_url('support/edit-blacklisted-pincode/'.$mbp_id), 'refresh');
                            } else {
                                $errors_msg = "Some error occurred during updation of pincode. Please try again.";
                            }
                        }
                    }
                }
            } else {
                $errors_msg = "Blacklisted pincode details not found.";
            }
        } else {
            $errors_msg = "Invalid Access..";
        }
        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["blacklisted_pincode_data"] = $blacklisted_pincode_data;
        $view_data["update_flag"] = $update_flag;
        if($update_flag) {
            $view_data["mbp_id"] = $mbp_id;
        }
        $this->load->view('Blacklisted/addUpdateBlacklistedPincode',$view_data); 
    }
}
