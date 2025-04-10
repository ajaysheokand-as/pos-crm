<?php

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class BlogsController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Instant_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
        define('updated_on', date('Y-m-d H:i:s'));
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
    }

    public $commonComponent = null;
    public $source_name = 'ORGANIC';
    public $data_source_id = 4;
    public $whitelisted_numbers = array(9170004606, 9289767308, 9717708655, 8279750539);

    public function blog_post() {
		header('Access-Control-Allow-Origin: *');
		header("Referrer-Policy: strict-origin-when-cross-origin");
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, auth');
        header('Content-Type: application/json');
		$response_array = array();
		$apiStatusData = array();
        $input_data = file_get_contents("php://input");
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data,true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token_auth = $this->_token();

        $token = $token_auth['token_android'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
			$start = $post['start'];
			$end   = $post['end'];
			$result = $this->db->query("select * from website_blog where wb_active='1' AND wb_deleted='0' ORDER BY wb_id DESC LIMIT $start,$end")->result_array();
			if(count($result) > 0){
			  foreach($result as $val){
                $apiStatusData['id']               = $val['wb_id'];
				$apiStatusData['title']               = $val['wb_title'];
                $apiStatusData['slug']                = $val['wb_slug'];
                $apiStatusData['short_description']   = $val['wb_short_description'];
                $apiStatusData['long_description']    = $val['wb_long_description'];
                $apiStatusData['seo_title']           = $val['wb_seo_title'];
                $apiStatusData['seo_keyword']         = $val['wb_seo_keyword'];
                $apiStatusData['seo_description']     = $val['wb_seo_description'];
                $apiStatusData['publish_date']        = $val['wb_publish_date'];
                $apiStatusData['created_date']        = $val['wb_created_on'];
                $apiStatusData['thumb_image_url']     = WEBSITE_DOCUMENT_BASE_URL.$val['wb_thumb_image_url'];
                $apiStatusData['banner_image_url']    = WEBSITE_DOCUMENT_BASE_URL.$val['wb_banner_image_url'];
                $response_array[] = $apiStatusData;
			  }
              $apiData = $response_array;
			  return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $apiData], REST_Controller::HTTP_OK));
			}else{
			  return json_encode($this->response(['Status' => 0, 'Message' => 'No data available.'], REST_Controller::HTTP_OK));
			}
        } else {
            return json_encode($this->response(['Status' => 4, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function news_post() {
		header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
		header("Referrer-Policy: strict-origin-when-cross-origin");
        header('Access-Control-Allow-Headers: Content-Type, Authorization, auth');
        header('Content-Type: application/json');
		$response_array = array();
		$apiStatusData = array();
        $input_data = file_get_contents("php://input");
        if($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token_auth = $this->_token();

        $token = $token_auth['token_android'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
			$start = $post['start'];
			$end   = $post['end'];
			$result = $this->db->query("select * from website_news where news_active='1' AND news_deleted='0' ORDER BY news_id DESC LIMIT $start,$end")->result_array();
			if(count($result) > 0){
			  foreach($result as $val){
				$apiStatusData['id']               = $val['news_id'];
				$apiStatusData['title']               = $val['news_title'];
                $apiStatusData['slug']                = $val['news_slug'];
                $apiStatusData['short_description']   = $val['news_short_description'];
                $apiStatusData['long_description']    = $val['news_long_description'];
                $apiStatusData['seo_title']           = $val['news_seo_title'];
                $apiStatusData['seo_keyword']         = $val['news_seo_keyword'];
                $apiStatusData['seo_description']     = $val['news_seo_description'];
                $apiStatusData['publish_date']        = $val['news_publish_date'];
                $apiStatusData['created_date']        = $val['news_created_on'];
                $apiStatusData['thumb_image_url']     = WEBSITE_DOCUMENT_BASE_URL.$val['news_thumb_image_url'];
                $apiStatusData['banner_image_url']    = WEBSITE_DOCUMENT_BASE_URL.$val['news_banner_image_url'];
                $response_array[] = $apiStatusData;
			  }
              $apiData = $response_array;
			  return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $apiData], REST_Controller::HTTP_OK));
			}else{
			  return json_encode($this->response(['Status' => 0, 'Message' => 'No data available.'], REST_Controller::HTTP_OK));
			}
        } else {
            return json_encode($this->response(['Status' => 4, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

	public function blogDetail_post() {
		header('Access-Control-Allow-Origin: *');
		header("Referrer-Policy: strict-origin-when-cross-origin");
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, auth');
        header('Content-Type: application/json');
		$response_array = array();
		$apiStatusData = array();
        $input_data = file_get_contents("php://input");
        if($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token_auth = $this->_token();

        $token = $token_auth['token_android'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
			$blog_id = $post['blog_id'];
			$result = $this->db->query("select * from website_blog where wb_active='1' AND wb_deleted='0' AND wb_id='".$blog_id."'")->result_array();
			if(count($result) > 0){
			  foreach($result as $val){
				$apiStatusData['title']               = $val['wb_title'];
                $apiStatusData['slug']                = $val['wb_slug'];
                $apiStatusData['short_description']   = $val['wb_short_description'];
                $apiStatusData['long_description']    = $val['wb_long_description'];
                $apiStatusData['seo_title']           = $val['wb_seo_title'];
                $apiStatusData['seo_keyword']         = $val['wb_seo_keyword'];
                $apiStatusData['seo_description']     = $val['wb_seo_description'];
                $apiStatusData['publish_date']        = $val['wb_publish_date'];
                $apiStatusData['created_date']        = $val['wb_created_on'];
                $apiStatusData['thumb_image_url']     = WEBSITE_DOCUMENT_BASE_URL.$val['wb_thumb_image_url'];
                $apiStatusData['banner_image_url']    = WEBSITE_DOCUMENT_BASE_URL.$val['wb_banner_image_url'];
                $response_array[] = $apiStatusData;
			  }
              $apiData = $response_array;
			  return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $apiData], REST_Controller::HTTP_OK));
			}else{
			  return json_encode($this->response(['Status' => 0, 'Message' => 'No data available.'], REST_Controller::HTTP_OK));
			}
        } else {
            return json_encode($this->response(['Status' => 4, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

    public function newsDetail_post() {
		header('Access-Control-Allow-Origin: *');
		header("Referrer-Policy: strict-origin-when-cross-origin");
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, auth');
        header('Content-Type: application/json');
		$response_array = array();
		$apiStatusData = array();
        $input_data = file_get_contents("php://input");
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        $headers = $this->input->request_headers();
        $token_auth = $this->_token();

        $token = $token_auth['token_android'];
        $header_validation = ($token == base64_decode($headers['Auth']));

        if($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
			$news_id = $post['news_id'];
			$result = $this->db->query("select * from website_news where news_active='1' AND news_deleted='0' AND news_id='".$news_id."'")->result_array();
			if(count($result) > 0){
			  foreach($result as $val){
				$apiStatusData['title']               = $val['news_title'];
                $apiStatusData['slug']                = $val['news_slug'];
                $apiStatusData['short_description']   = $val['news_short_description'];
                $apiStatusData['long_description']    = $val['news_long_description'];
                $apiStatusData['seo_title']           = $val['news_seo_title'];
                $apiStatusData['seo_keyword']         = $val['news_seo_keyword'];
                $apiStatusData['seo_description']     = $val['news_seo_description'];
                $apiStatusData['publish_date']        = $val['news_publish_date'];
                $apiStatusData['created_date']        = $val['news_created_on'];
                $apiStatusData['thumb_image_url']     = WEBSITE_DOCUMENT_BASE_URL.$val['news_thumb_image_url'];
                $apiStatusData['banner_image_url']    = WEBSITE_DOCUMENT_BASE_URL.$val['news_banner_image_url'];
                $response_array[] = $apiStatusData;
			  }
              $apiData = $response_array;
			  return json_encode($this->response(['Status' => 1, 'Message' => 'Success', 'data' => $apiData], REST_Controller::HTTP_OK));
			}else{
			  return json_encode($this->response(['Status' => 0, 'Message' => 'No data available.'], REST_Controller::HTTP_OK));
			}
        } else {
            return json_encode($this->response(['Status' => 4, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }
}
