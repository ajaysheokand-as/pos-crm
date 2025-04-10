<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ErrorController extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function error404() {

        if (session_id()) {
            session_destroy();
        }

        $this->load->view('errors/html/error_404', ['heading' => "Invalid Access", 'message' => "Opps!!! You are requesting for invalid page."]);
    }

    public function error403() {
        $this->load->view('errors/html/error_404', ['heading' => "Forbidden Access", 'message' => "Opps!!! You are requesting for invalid page."]);
    }

    public function error500() {
        $this->load->view('errors/index');
    }
}
