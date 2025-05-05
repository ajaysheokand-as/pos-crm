<?php


defined('BASEPATH') OR exit('No direct script access allowed');

header('Content-type: text/html; charset=UTF-8');

class CampaignController extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
    }

    public function index()
    {
        $data['campaignData'] = $this->Tasks->select([], '*', 'instant_loan_campaign');
        $this->load->view('Campaign/leadList', $data);
    }
}