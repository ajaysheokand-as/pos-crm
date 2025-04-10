<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    public function blogList($limit, $start = null, $conditions = array()) {
        $this->db->select('*');
        $this->db->from("website_blog");
        $this->db->distinct();
        $this->db->limit($limit,$start);
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('wb_active',1);
        $this->db->where('wb_deleted',0);
        $return = $this->db->order_by('wb_id','desc')->get()->result_array();
        return $return;
    }

    public function blogListCount($conditions) {
        $this->db->select("wb_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('wb_active', 1);
        $this->db->where('wb_deleted', 0);
        return $this->db->from('website_blog')->get()->num_rows();
    } 

    public function getCheckBlogTitle($title,$blog_id=0) {
        $return_val = false;
        $condition = array();
        $condition["LOWER(wb_title)"] = trim(strtolower($title));
        if(!empty($blog_id)) {
           $condition["wb_id!="] = $blog_id;
        }
        $tempDetails = $this->db->select('wb_title')->from('website_blog')->where($condition)->get();
        if($tempDetails->num_rows()) {
            $return_val = true;
        }
        return $return_val;
    }

    public function getBlogDetails($blog_id) {
        $status = 0;
        $blog_data = array();
        $tempDetails = $this->db->select('*')->from('website_blog')->where(["wb_id"=>$blog_id])->get();
        if (!empty($tempDetails->num_rows())) {
            $blog_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "blog_data" => $blog_data);
    }
    
    public function getSEODetails($seo_id) {
        $status = 0;
        $seo_data = array();
        $tempDetails = $this->db->select('*')->from('website_seo')->where(["ws_id"=>$seo_id])->get();
        if (!empty($tempDetails->num_rows())) {
            $seo_data = $tempDetails->row_array();
            $status = 1;
        }
        return array("status" => $status, "seo_data" => $seo_data);
    }
    
    public function getCheckSEOTitle($title,$seo_id=0) {
        $return_val = false;
        $condition = array();
        $condition["LOWER(ws_title)"] = trim(strtolower($title));
        if(!empty($seo_id)) {
           $condition["ws_id!="] = $seo_id;
        }
        $tempDetails = $this->db->select('ws_title')->from('website_seo')->where($condition)->get();
        if($tempDetails->num_rows()) {
            $return_val = true;
        }
        return $return_val;
    }

    public function seoList($limit, $start = null, $conditions = array()) {
        $this->db->select('*');
        $this->db->from("website_seo");
        $this->db->distinct();
        $this->db->limit($limit,$start);
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('ws_active',1);
        $this->db->where('ws_deleted',0);
        $return = $this->db->order_by('ws_id','desc')->get()->result_array();
        return $return;
    }

    public function seoListCount($conditions) {
        $this->db->select("ws_id");
        if (!empty($conditions)) {
            foreach ($conditions as $cond_index => $val) {
                if (!empty($val)) {
                    $this->db->where($cond_index, $val);
                } else {
                    $this->db->where($cond_index);
                }
            }
        }
        $this->db->where('ws_active', 1);
        $this->db->where('ws_deleted', 0);
        return $this->db->from('website_seo')->get()->num_rows();
    }
}
?>
