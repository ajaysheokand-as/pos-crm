<?php


defined('BASEPATH') OR exit('No direct script access allowed');

header('Content-type: text/html; charset=UTF-8');

class CampaignController extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->model('CampaignModel', 'Campaign');
    }

    public function index()
    {
        $config['base_url'] = base_url('CampaignController/index');
        $config['total_rows'] = $this->Campaign->get_count(); // Total number of rows
        $config['per_page'] = 15; // Records per page
        $config['uri_segment'] = 3; // Page number segment in URL
    
        // Optional Bootstrap 4/5 pagination style
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';
    
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['campaignData'] = $this->Campaign->getAllData($config['per_page'], $page);
        $data['links'] = $this->pagination->create_links();
        $this->load->view('Campaign/leadList', $data);
    }
}