<?php

defined('BASEPATH') or exit('No direct script access allowed');

class NewsController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('News/News_Model', 'newsModel');
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
        if (!empty($_POST['filter_input'])) {
            $conditions["(website_news.news_title LIKE '%" . strval($_POST['filter_input']) . "%' OR website_news.news_short_description LIKE '" . strval($_POST['filter_input']) . "%')"] = null;
        }

        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $this->newsModel->newsListCount($conditions);
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

        $data['newsDetails'] = $this->newsModel->newsList($config["per_page"], $page, $conditions);
        $data["links"] = $this->pagination->create_links();
        $data["javascript_files"] = $this->javascript_files;
        $this->load->view('News/index', $data);
    }

    public function newsDelete() {
        $news_id = $_POST['news_id'];
        $check_data = $this->db->select('*')->from('website_news')->where('news_active', 1)->where('news_id', $news_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['news_id' => $news_id])->update('website_news', ['news_active' => 0, 'news_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('News/index');
        }
    }

    public function addNews() {
        $view_data = array();
        $news_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;
        $news_id = 0;

        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $news_data["news_title"]             = $this->input->post('news_title');
            $news_data["news_publish_date"]      = $this->input->post('news_publish_date');
            $news_data["news_publish_status"]    = $this->input->post('news_publish_status');
            $news_data["news_short_description"] = $this->input->post('news_short_description');
            $news_data["news_long_description"]  = $this->input->post('news_long_description');
            $news_data["news_seo_title"]         = $this->input->post('news_seo_title');
            $news_data["news_seo_keyword"]       = $this->input->post('news_seo_keyword');
            $news_data["news_seo_description"]   = $this->input->post('news_seo_description');
            $this->form_validation->set_rules('news_title', 'Title', 'required');
            if ($this->form_validation->run() == FALSE) {
                $errors_msg = validation_errors();
            } else {
                if ($this->newsModel->getChecknewsTitle($news_data["news_title"])) {
                    $errors_msg = "Title is already exist. Please enter new title";
                } else {
                    $insert_news_array = array();
                    $insert_news_array["news_title"]             = $news_data["news_title"];
                    $insert_news_array["news_slug"]              = $this->slugify($news_data["news_title"]);
                    $insert_news_array["news_publish_date"]      = $news_data["news_publish_date"];
                    $insert_news_array["news_publish_status"]    = $news_data["news_publish_status"];
                    $insert_news_array["news_short_description"] = $news_data["news_short_description"];
                    $insert_news_array["news_long_description"]  = $news_data["news_long_description"];
                    $insert_news_array["news_seo_title"]         = $news_data["news_seo_title"];
                    $insert_news_array["news_seo_keyword"]       = $news_data["news_seo_keyword"];
                    $insert_news_array["news_seo_description"]   = $news_data["news_seo_description"];
                    $insert_news_array["news_publish_by"]        = $_SESSION['isUserSession']['user_id'];
                    $insert_news_array["news_created_on"]        = date("Y-m-d H:i:s");
                    $insert_news_array["news_updated_on"]        = date("Y-m-d H:i:s");

                    require_once(COMPONENT_PATH . "CommonComponent.php");
                    $CommonComponent = new CommonComponent();
                    $request_array['flag'] = 0;
                    $request_array['new_file_name'] = '';
                    $request_array['bucket_name'] = 'paisaonsalarybucket';

                    if (!empty($_FILES['news_thumb_image_url']['name'])) {
                        $request_array['file'] = $_FILES['news_thumb_image_url'];
                        $request_array['new_file_name'] = 'thumbnail';
                        $result_array = $CommonComponent->upload_document(0, $request_array);
                        $insert_news_array["news_thumb_image_url"] = $result_array['file_name'];
                    }

                    if (!empty($_FILES['news_banner_image_url']['name'])) {
                        $request_array['file'] = $_FILES['news_banner_image_url'];
                        $request_array['new_file_name'] = 'banner';
                        $result_array = $CommonComponent->upload_document(0, $request_array);
                        $insert_news_array["news_banner_image_url"] = $result_array['file_name'];
                    }

                    $news_id = $this->db->insert('website_news', $insert_news_array);
                    if (!empty($news_id)) {
                        $success_msg = "news has been added successfully.";
                        $this->session->set_flashdata('success_msg', $success_msg);
                        $enc_news_id = $this->encrypt->encode($news_id);
                        return redirect(base_url('news'), 'refresh');
                    } else {
                        $errors_msg = "Some error occurred during creation of news. Please try again.";
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
        $view_data["news_data"] = $news_data;
        $this->load->view('News/addUpdateNews', $view_data);
    }

    public function editNews($news_id = "") {
        $view_data = array();
        $news_data = array();
        $success_msg = "";
        $errors_msg = "";
        $update_flag = false;

        if (!empty($news_id)) {
            $return_array = $this->newsModel->getNewsDetails($news_id);
            if ($return_array['status'] == 1) {
                $update_flag = true;
                $news_id = $return_array['news_data']['news_id'];
                $news_data = $return_array['news_data'];
                if ($this->input->server('REQUEST_METHOD') == 'POST') {
                    $news_id                             = $this->input->post('news_id');
                    $news_data["news_title"]             = $this->input->post('news_title');
                    $news_data["news_publish_date"]      = $this->input->post('news_publish_date');
                    $news_data["news_publish_status"]    = $this->input->post('news_publish_status');
                    $news_data["news_short_description"] = $this->input->post('news_short_description');
                    $news_data["news_long_description"]  = $this->input->post('news_long_description');
                    $news_data["news_seo_title"]         = $this->input->post('news_seo_title');
                    $news_data["news_seo_keyword"]       = $this->input->post('news_seo_keyword');
                    $news_data["news_seo_description"]   = $this->input->post('news_seo_description');

                    $this->form_validation->set_rules('news_title', 'Title', 'required');
                    if ($this->form_validation->run() == FALSE) {
                        $errors_msg = validation_errors();
                    } else {
                        if ($this->newsModel->getChecknewsTitle($news_data["news_title"], $news_id)) {
                            $errors_msg = "Title already exist. Please try with different title.";
                        } else {
                            $update_news_array = array();
                            $update_news_array["news_title"]             = $news_data["news_title"];
                            $update_news_array["news_slug"]              = $this->slugify($news_data["news_title"]);
                            $update_news_array["news_publish_date"]      = $news_data["news_publish_date"];
                            $update_news_array["news_publish_status"]    = $news_data["news_publish_status"];
                            $update_news_array["news_short_description"] = $news_data["news_short_description"];
                            $update_news_array["news_long_description"]  = htmlentities($news_data["news_long_description"]);
                            $update_news_array["news_seo_title"]         = $news_data["news_seo_title"];
                            $update_news_array["news_seo_keyword"]       = $news_data["news_seo_keyword"];
                            $update_news_array["news_seo_description"]   = $news_data["news_seo_description"];
                            $update_news_array["news_updated_on"]        = date("Y-m-d H:i:s");

                            require_once(COMPONENT_PATH . "CommonComponent.php");
                            $CommonComponent = new CommonComponent();
                            $request_array['flag'] = 0;
                            $request_array['new_file_name'] = '';
                            $request_array['bucket_name'] = 'paisaonsalarybucket';

                            if (!empty($_FILES['news_thumb_image_url']['name'])) {
                                $request_array['file'] = $_FILES['news_thumb_image_url'];
                                $request_array['new_file_name'] = 'thumbnail';
                                $result_array = $CommonComponent->upload_document(0, $request_array);
                                $update_news_array["news_thumb_image_url"] = $result_array['file_name'];
                            } else {
                                $update_news_array["news_thumb_image_url"] = $this->input->post('old_thumb_image');
                            }

                            if (!empty($_FILES['news_banner_image_url']['name'])) {
                                $request_array['file'] = $_FILES['news_banner_image_url'];
                                $request_array['new_file_name'] = 'banner';
                                $result_array = $CommonComponent->upload_document(0, $request_array);
                                $update_news_array["news_banner_image_url"] = $result_array['file_name'];
                            } else {
                                $update_news_array["news_banner_image_url"] = $this->input->post('old_banner_image');
                            }
                            $return_update_flag = $this->db->where(['news_id' => $news_id])->update('website_news', $update_news_array);
                            if ($return_update_flag) {
                                //$news_id = $this->encrypt->encode($news_id);
                                $success_msg = "News has been updated successfully.";
                                $this->session->set_flashdata('success_msg', $success_msg);
                                return redirect(base_url('news/edit-news/' . $news_id), 'refresh');
                            } else {
                                $errors_msg = "Some error occurred during updation of user. Please try again.";
                            }
                        }
                    }
                }
            } else {
                $errors_msg = "news details not found.";
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
        $view_data["news_data"] = $news_data;
        $view_data["update_flag"] = $update_flag;
        if ($update_flag) {
            $view_data["news_id"] = $news_id;
        }
        $this->load->view('News/addUpdateNews', $view_data);
    }
}
