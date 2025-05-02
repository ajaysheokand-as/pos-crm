<?php

use Exception;

defined('BASEPATH') or exit('No direct script access allowed');

class CustomerCampaignController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
    }

    public function storeCustomerData()
    {
        try {
            if ($this->input->server('REQUEST_METHOD') == 'POST') {
                $this->setValidationRules();

                if ($this->form_validation->run() == FALSE) {
                    $response = ['success' => false, 'errors' => $this->form_validation->error_array()];
                } else {
                    $formData = $this->input->post();
                    $formData['lead_source'] = 'Campaign';
                    $this->Tasks->insert($formData, 'instant_loan_campaign');
                    $response = ['success' => true, 'message' => 'Customer saved successfully!', 'data' => $formData];
                }

            } else {
                $response = [
                    'success' => false,
                    'message' => $this->input->server('REQUEST_METHOD') . ' method is not supported!'
                ];
            }
        } catch (Exception $exception) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }

    /**
     * function to set the validtion rules
     *
     * @return void
     */
    public function setValidationRules()
    {
        $validationRules = [
            [
                'field' => 'first_name',
                'label' => 'first_name',
                'rules' => 'required|alpha|trim'
            ],
            [
                'field' => 'last_name',
                'label' => 'Last Name',
                'rules' => 'alpha|trim'
            ],
            [
                'field' => 'pan_number',
                'label' => 'PAN Number',
                'rules' => 'regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/]'
            ],
            [
                'field' => 'phone_number',
                'label' => 'Phone Number',
                'rules' => 'required|regex_match[/^[6-9][0-9]{9}$/]'
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'valid_email|trim' 
            ],
            [
                'field' => 'current_salary',
                'label' => 'Monthly Salary',
                'rules' => 'required'
            ],
            [
                'field' => 'employment_type',
                'label' => 'Employment Type',
                'rules' => 'required'
            ]
        ];
        $this->form_validation->set_rules($validationRules);
    }
}