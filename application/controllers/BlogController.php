<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BlogController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Blog/Blog_Model', 'blogModel');
        define('created_on', date('Y-m-d H:i:s'));
        set_time_limit(300);
        date_default_timezone_set('Asia/Kolkata');
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
        $login = new IsLogin();
        $login->index();
    }

    function slugify($string, $separator = '-') {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array('&' => 'and', "'" => '');
        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

    public function index() {
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $conditions = array();
        if (!empty($_POST['category_id'])) {
            $conditions["website_blog.wb_category_id"] = intval($_POST['category_id']);
        }
        if (!empty($_POST['filter_input'])) {
            $conditions["(website_blog.wb_title LIKE '%" . strval($_POST['filter_input']) . "%' OR website_blog.wb_short_description LIKE '" . strval($_POST['filter_input']) . "%')"] = null;
        }

        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->blogModel->blogListCount($conditions);
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

        $data['blogDetails'] = $this->blogModel->blogList($config["per_page"], $page, $conditions);
        $data["links"] = $this->pagination->create_links();
        $data["javascript_files"] = $this->javascript_files;

        $data["blog_category"] = $this->db->select('wb_blog_category_id,wb_blog_category_name,wb_blog_category_slug')->from('website_blog_category')->where('wb_blog_category_active', 1)->get()->result_array();
        $this->load->view('Blog/index', $data);
    }

    public function seoList() {
        $this->load->library("pagination");
        $url = (base_url() . $this->uri->segment(1));
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $conditions = array();
        if (!empty($_POST['filter_input'])) {
            $conditions["(website_seo.ws_title LIKE '%" . strval($_POST['filter_input']) . "%')"] = null;
        }

        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->blogModel->seoListCount($conditions);
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
        $data['seoDetails'] = $this->blogModel->seoList($config["per_page"], $page, $conditions);
        $data["links"] = $this->pagination->create_links();
        $data["javascript_files"] = $this->javascript_files;
        $this->load->view('Blog/seo_list', $data);
    }

    public function seoDelete() {
        $ws_id = $this->encrypt->decode($_POST['ws_id']);
        $check_data = $this->db->select('*')->from('website_seo')->where('ws_active', 1)->where('ws_id', $ws_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['ws_id' => $ws_id])->update('website_seo', ['ws_active' => 0, 'ws_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Blog/seo-list');
        }
    }

    public function blogDelete() {
        $wb_id = $this->encrypt->decode($_POST['wb_id']);
        $check_data = $this->db->select('*')->from('website_blog')->where('wb_active', 1)->where('wb_id', $wb_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['wb_id' => $wb_id])->update('website_blog', ['wb_active' => 0, 'wb_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Blog/index');
        }
    }

    public function addBlog() {
        $view_data = array();
        $blog_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $wb_id = 0;
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $blog_data["wb_title"]             = $this->input->post('wb_title');
            $blog_data["wb_category_id"]       = $this->input->post('wb_category_id');
            $blog_data["wb_publish_date"]      = $this->input->post('wb_publish_date');
            $blog_data["wb_publish_status"]    = $this->input->post('wb_publish_status');
            $blog_data["wb_short_description"] = $this->input->post('wb_short_description');
            $blog_data["wb_long_description"]  = $this->input->post('wb_long_description');
            $blog_data["wb_seo_title"]         = $this->input->post('wb_seo_title');
            $blog_data["wb_seo_keyword"]       = $this->input->post('wb_seo_keyword');
            $blog_data["wb_seo_description"]   = $this->input->post('wb_seo_description');
            $this->form_validation->set_rules('wb_title', 'Title', 'required');
            $this->form_validation->set_rules('wb_category_id', 'Category', 'required');
            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                if ($this->blogModel->getCheckBlogTitle($blog_data["wb_title"])) {
                    $errors_msg = "Title is already exist. Please enter new title";
                } else {
                    $insert_blog_array = array();
                    $insert_blog_array["wb_title"]             = $blog_data["wb_title"];
                    $insert_blog_array["wb_slug"]              = $this->slugify($blog_data["wb_title"]);
                    $insert_blog_array["wb_category_id"]       = $blog_data["wb_category_id"];
                    $insert_blog_array["wb_publish_date"]      = $blog_data["wb_publish_date"];
                    $insert_blog_array["wb_publish_status"]    = $blog_data["wb_publish_status"];
                    $insert_blog_array["wb_short_description"] = $blog_data["wb_short_description"];
                    $insert_blog_array["wb_long_description"]  = $blog_data["wb_long_description"];
                    $insert_blog_array["wb_seo_title"]         = $blog_data["wb_seo_title"];
                    $insert_blog_array["wb_seo_keyword"]       = $blog_data["wb_seo_keyword"];
                    $insert_blog_array["wb_seo_description"]   = $blog_data["wb_seo_description"];
                    $insert_blog_array["wb_publish_by"]        = $_SESSION['isUserSession']['user_id'];
                    $insert_blog_array["wb_created_on"]        = date("Y-m-d H:i:s");
                    $insert_blog_array["wb_updated_on"]        = date("Y-m-d H:i:s");

                    require_once(COMPONENT_PATH . "CommonComponent.php");
                    $CommonComponent = new CommonComponent();
                    $request_array['flag'] = 0;
                    $request_array['new_file_name'] = '';
                    // $request_array['bucket_name'] = 'sot-website';
                    $request_array['bucket_name'] = 'tejasloanbucket';

                    if (!empty($_FILES['wb_thumb_image_url']['name'])) {
                        $request_array['file'] = $_FILES['wb_thumb_image_url'];
                        $request_array['new_file_name'] = 'thumbnail';

                        $result_array = $CommonComponent->upload_document(0, $request_array);
                        $insert_blog_array["wb_thumb_image_url"] = $result_array['file_name'];
                    }

                    if (!empty($_FILES['wb_banner_image_url']['name'])) {
                        $request_array['file'] = $_FILES['wb_banner_image_url'];
                        $request_array['new_file_name'] = 'banner';
                        $result_array = $CommonComponent->upload_document(0, $request_array);
                        $insert_blog_array["wb_banner_image_url"] = $result_array['file_name'];
                    }

                    $blog_id = $this->db->insert('website_blog', $insert_blog_array);
                    if (!empty($blog_id)) {
                        $success_msg = "Blog has been added successfully.";
                        $this->session->set_flashdata('success_msg', $success_msg);
                        $enc_blog_id = $this->encrypt->encode($blog_id);
                        return redirect(base_url('blog'), 'refresh');
                    } else {
                        $errors_msg = "Some error occurred during creation of blog. Please try again.";
                    }
                }
            }
            if (!empty($errors_msg)) {
                $this->session->set_flashdata('errors_msg', $errors_msg);
            }
        }
        $view_data['blog_category'] = $this->db->select('wb_blog_category_id,wb_blog_category_name,wb_blog_category_slug')->from('website_blog_category')->where('wb_blog_category_active', 1)->get()->result_array();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["blog_data"] = $blog_data;
        $this->load->view('Blog/addUpdateBlog', $view_data);
    }

    public function addSEO() {
        $view_data = array();
        $seo_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $ws_id = 0;
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $seo_data["ws_id"]                = $this->input->post('ws_id');
            $seo_data["ws_title"]             = $this->input->post('ws_title');
            $seo_data["ws_slug"]              = $this->input->post('ws_slug');
            $seo_data["ws_publish_date"]      = $this->input->post('ws_publish_date');
            $seo_data["ws_publish_status"]    = $this->input->post('ws_publish_status');
            $seo_data["ws_seo_title"]         = $this->input->post('ws_seo_title');
            $seo_data["ws_seo_keyword"]       = $this->input->post('ws_seo_keyword');
            $seo_data["ws_seo_description"]   = $this->input->post('ws_seo_description');
            $slug = end(explode('/', rtrim($seo_data["ws_slug"], '/')));
            $this->form_validation->set_rules('ws_title', 'Title', 'required');
            $this->form_validation->set_rules('ws_slug', 'Full URL', 'required');
            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                if ($this->blogModel->getCheckSEOTitle($seo_data["ws_title"])) {
                    $errors_msg = "Title is already exist. Please enter new title";
                } else {
                    $insert_seo_array = array();
                    $insert_seo_array["ws_id"]                = $seo_data["ws_id"];
                    $insert_seo_array["ws_title"]             = $seo_data["ws_title"];
                    $insert_seo_array["ws_slug"]              = $slug;
                    $insert_seo_array["full_url"]             = $seo_data["ws_slug"];
                    $insert_seo_array["ws_publish_date"]      = $seo_data["ws_publish_date"];
                    $insert_seo_array["ws_publish_status"]    = $seo_data["ws_publish_status"];
                    $insert_seo_array["ws_seo_title"]         = $seo_data["ws_seo_title"];
                    $insert_seo_array["ws_seo_keyword"]       = $seo_data["ws_seo_keyword"];
                    $insert_seo_array["ws_seo_description"]   = $seo_data["ws_seo_description"];
                    $insert_seo_array["ws_publish_by"]        = $_SESSION['isUserSession']['user_id'];
                    $insert_seo_array["ws_created_on"]        = date("Y-m-d H:i:s");
                    $insert_seo_array["ws_updated_on"]        = date("Y-m-d H:i:s");

                    $seo_id = $this->db->insert('website_seo', $insert_seo_array);
                    if (!empty($seo_id)) {
                        $success_msg = "SEO has been added successfully.";
                        $this->session->set_flashdata('success_msg', $success_msg);
                        $enc_seo_id = $this->encrypt->encode($seo_id);
                        return redirect(base_url('seo-list'), 'refresh');
                    } else {
                        $errors_msg = "Some error occurred during creation of blog. Please try again.";
                    }
                }
            }
            if (!empty($errors_msg)) {
                $this->session->set_flashdata('errors_msg', $errors_msg);
            }
        }
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["update_flag"] = $update_flag;
        $view_data["seo_data"] = $seo_data;
        $this->load->view('Blog/addUpdateSEO', $view_data);
    }

    public function editSEO($seo_id = "") {
        $view_data = array();
        $seo_data = array();
        $success_msg = "";
        $errors_msg = "";
        $ws_id = 0;
        $update_flag = false;
        if (!empty($seo_id)) {
            $ws_id = intval($this->encrypt->decode($seo_id));
            $return_array = $this->blogModel->getSEODetails($ws_id);
            if ($return_array['status'] == 1) {
                $update_flag = true;
                $ws_id = $return_array['seo_data']['ws_id'];
                $seo_data = $return_array['seo_data'];
                if ($this->input->server('REQUEST_METHOD') == 'POST') {
                    $ws_id                             = $this->input->post('ws_id');
                    $seo_data1["ws_title"]             = $this->input->post('ws_title');
                    $seo_data1["ws_slug"]              = $this->input->post('ws_slug');
                    $seo_data1["ws_publish_date"]      = $this->input->post('ws_publish_date');
                    $seo_data1["ws_publish_status"]    = $this->input->post('ws_publish_status');
                    $seo_data1["wb_seo_title"]         = $this->input->post('ws_seo_title');
                    $seo_data1["wb_seo_keyword"]       = $this->input->post('wb_seo_keyword');
                    $seo_data1["wb_seo_description"]   = $this->input->post('wb_seo_description');
                    $slug = end(explode('/', rtrim($this->input->post('ws_slug'), '/')));
                    $this->form_validation->set_rules('ws_title', 'Title', 'required');
                    $this->form_validation->set_rules('ws_slug', 'Full URL', 'required');
                    if ($this->form_validation->run() == FALSE) {
                        $errors_msg = validation_errors();
                    } else {
                        if ($this->blogModel->getCheckSEOTitle($seo_data["ws_title"], $ws_id)) {
                            $errors_msg = "Title already exist. Please try with different title.";
                        } else {
                            $insert_seo_array = array();
                            $insert_seo_array["ws_title"]             = $this->input->post('ws_title');
                            $insert_seo_array["ws_slug"]              = $slug;
                            $insert_seo_array["full_url"]             = $this->input->post('ws_slug');
                            $insert_seo_array["ws_publish_date"]      = $this->input->post('ws_publish_date');
                            $insert_seo_array["ws_publish_status"]    = $this->input->post('ws_publish_status');
                            $insert_seo_array["ws_seo_title"]         = $this->input->post('ws_seo_title');
                            $insert_seo_array["ws_seo_keyword"]       = $this->input->post('ws_seo_keyword');
                            $insert_seo_array["ws_seo_description"]   = $this->input->post('ws_seo_description');
                            $insert_seo_array["ws_updated_on"]        = date("Y-m-d H:i:s");
                            $return_update_flag = $this->db->where(['ws_id' => $ws_id])->update('website_seo', $insert_seo_array);
                            if ($return_update_flag) {
                                $seo_id = $this->encrypt->encode($ws_id);
                                $success_msg = "SEO has been updated successfully.";
                                $this->session->set_flashdata('success_msg', $success_msg);
                                return redirect(base_url('seo/edit-seo/' . $seo_id), 'refresh');
                            } else {
                                $errors_msg = "Some error occurred during updation of user. Please try again.";
                            }
                        }
                    }
                }
            } else {
                $errors_msg = "SEO details not found.";
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
        $view_data["seo_data"] = $seo_data;
        $view_data["update_flag"] = $update_flag;
        if ($update_flag) {
            $view_data["ws_id"] = $ws_id;
        }
        $this->load->view('Blog/addUpdateSEO', $view_data);
    }

    public function editBlog($blog_id = "") {
        $view_data = array();
        $blog_data = array();
        $success_msg = "";
        $errors_msg = "";
        $wb_id = 0;
        $update_flag = false;
        if (!empty($blog_id)) {
            $wb_id = intval($this->encrypt->decode($blog_id));
            $return_array = $this->blogModel->getBlogDetails($wb_id);
            if ($return_array['status'] == 1) {
                $update_flag = true;
                $wb_id = $return_array['blog_data']['wb_id'];
                $blog_data = $return_array['blog_data'];
                if ($this->input->server('REQUEST_METHOD') == 'POST') {
                    $wb_id                             = $this->input->post('wb_id');
                    $blog_data["wb_title"]             = $this->input->post('wb_title');
                    $blog_data["wb_category_id"]       = $this->input->post('wb_category_id');
                    $blog_data["wb_publish_date"]      = $this->input->post('wb_publish_date');
                    $blog_data["wb_publish_status"]    = $this->input->post('wb_publish_status');
                    $blog_data["wb_short_description"] = $this->input->post('wb_short_description');
                    $blog_data["wb_long_description"]  = $this->input->post('wb_long_description');
                    $blog_data["wb_seo_title"]         = $this->input->post('wb_seo_title');
                    $blog_data["wb_seo_keyword"]       = $this->input->post('wb_seo_keyword');
                    $blog_data["wb_seo_description"]   = $this->input->post('wb_seo_description');
                    $this->form_validation->set_rules('wb_title', 'Title', 'required');
                    $this->form_validation->set_rules('wb_category_id', 'Category', 'required');
                    if ($this->form_validation->run() == FALSE) {
                        $errors_msg = validation_errors();
                    } else {
                        if ($this->blogModel->getCheckBlogTitle($blog_data["wb_title"], $wb_id)) {
                            $errors_msg = "Title already exist. Please try with different title.";
                        } else {
                            $update_blog_array = array();
                            $update_blog_array["wb_title"]             = $blog_data["wb_title"];
                            $update_blog_array["wb_slug"]              = $this->slugify($blog_data["wb_title"]);
                            $update_blog_array["wb_category_id"]       = $blog_data["wb_category_id"];
                            $update_blog_array["wb_publish_date"]      = $blog_data["wb_publish_date"];
                            $update_blog_array["wb_publish_status"]    = $blog_data["wb_publish_status"];
                            $update_blog_array["wb_short_description"] = $blog_data["wb_short_description"];
                            $update_blog_array["wb_long_description"]  = $blog_data["wb_long_description"];
                            $update_blog_array["wb_seo_title"]         = $blog_data["wb_seo_title"];
                            $update_blog_array["wb_seo_keyword"]       = $blog_data["wb_seo_keyword"];
                            $update_blog_array["wb_seo_description"]   = $blog_data["wb_seo_description"];
                            $update_blog_array["wb_updated_on"]        = date("Y-m-d H:i:s");
                            /*
                            $config['upload_path'] = 'blog/';
                            $config['allowed_types'] = 'pdf|jpg|png|jpeg';
                            $this->upload->initialize($config);
							*/
                            require_once(COMPONENT_PATH . "CommonComponent.php");
                            $CommonComponent = new CommonComponent();
                            $request_array['flag'] = 0;
                            $request_array['new_file_name'] = '';
                            $request_array['bucket_name'] = 'tejasloanbucket';

                            if (!empty($_FILES['wb_thumb_image_url']['name'])) {
                                $request_array['file'] = $_FILES['wb_thumb_image_url'];
                                $request_array['new_file_name'] = 'thumbnail';
                                $result_array = $CommonComponent->upload_document(0, $request_array);
                                $update_blog_array["wb_thumb_image_url"] = $result_array['file_name'];
                            } else {
                                $update_blog_array["wb_thumb_image_url"] = $this->input->post('old_thumb_image');
                            }

                            if (!empty($_FILES['wb_banner_image_url']['name'])) {
                                $request_array['file'] = $_FILES['wb_banner_image_url'];
                                $request_array['new_file_name'] = 'banner';
                                $result_array = $CommonComponent->upload_document(0, $request_array);
                                $update_blog_array["wb_banner_image_url"] = $result_array['file_name'];
                            } else {
                                $update_blog_array["wb_banner_image_url"] = $this->input->post('old_banner_image');
                            }
                            /*
                            if(!empty($_FILES['wb_thumb_image_url']['name'])) {
                                if(!$this->upload->do_upload('wb_thumb_image_url')) {
                                    $json['err'] = $this->upload->display_errors();
                                    echo json_encode($json);
                                }
                                else
                                {
                                  unlink('blog/'.$this->input->post('old_thumb_image'));
                                  $data = array('upload_data_thumb_image'=>$this->upload->data());
                                  $update_blog_array["wb_thumb_image_url"] = $data['upload_data_thumb_image']['file_name'];
                                }

                            } else{
                                  $update_blog_array["wb_thumb_image_url"] = $this->input->post('old_thumb_image');
                            }

                            if(!empty($_FILES['wb_banner_image_url']['name'])) {
                                if(!$this->upload->do_upload('wb_banner_image_url')) {
                                    $json['err'] = $this->upload->display_errors();
                                    echo json_encode($json);
                                }
                                else
                                {
                                  unlink('blog/'.$this->input->post('old_banner_image'));
                                  $data = array('upload_data_banner_image' => $this->upload->data());
                                  $update_blog_array["wb_banner_image_url"] = $data['upload_data_banner_image']['file_name'];
                                }
                            } else{
                                  $update_blog_array["wb_banner_image_url"] = $this->input->post('old_banner_image');
                            }
							*/
                            $return_update_flag = $this->db->where(['wb_id' => $wb_id])->update('website_blog', $update_blog_array);
                            if ($return_update_flag) {
                                $blog_id = $this->encrypt->encode($wb_id);
                                $success_msg = "Blog has been updated successfully.";
                                $this->session->set_flashdata('success_msg', $success_msg);
                                return redirect(base_url('blog/edit-blog/' . $blog_id), 'refresh');
                            } else {
                                $errors_msg = "Some error occurred during updation of user. Please try again.";
                            }
                        }
                    }
                }
            } else {
                $errors_msg = "Blog details not found.";
            }
        } else {
            $errors_msg = "Invalid Access..";
        }
        if (!empty($errors_msg)) {
            $this->session->set_flashdata('errors_msg', $errors_msg);
        }
        $view_data['blog_category'] = $this->db->select('wb_blog_category_id,wb_blog_category_name,wb_blog_category_slug')->from('website_blog_category')->where('wb_blog_category_active', 1)->get()->result_array();
        $view_data["javascript_files"] = $this->javascript_files;
        $view_data["errors_msg"] = $errors_msg;
        $view_data["success_msg"] = $success_msg;
        $view_data["blog_data"] = $blog_data;
        $view_data["update_flag"] = $update_flag;
        if ($update_flag) {
            $view_data["wp_id"] = $wb_id;
        }
        $this->load->view('Blog/addUpdateBlog', $view_data);
    }
}
