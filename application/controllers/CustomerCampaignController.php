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
                    $customerData['cp_first_name'] = $formData['first_name'];
                    $customerData['cp_sur_name'] = $formData['last_name'];
                    $customerData['cp_mobile'] = $formData['mobile_number'];
                    $customerData['cp_pancard'] = $formData['pan_number'];
                    $customerData['cp_monthly_income'] = $formData['salary'];
                    $customerData['cp_personal_email'] = !empty($formData['email']) ? $formData['email'] : '';
                    $customerData['cp_employment_type'] = $formData['employment_type'];
                    $this->Tasks->insert($customerData, 'customer_profile');
                    $response = ['success' => true, 'message' => 'Customer saved successfully!'];
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
                'rules' => 'required|alpha|trim'
            ],
            [
                'field' => 'pan_number',
                'label' => 'PAN Number',
                'rules' => 'required|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/]'
            ],
            [
                'field' => 'mobile_number',
                'label' => 'Mobile Number',
                'rules' => 'required|regex_match[/^[6-9][0-9]{9}$/]'
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'valid_email|trim' 
            ],
            [
                'field' => 'salary',
                'label' => 'Monthly Salary',
                'rules' => 'required|numeric|greater_than[0]'
            ],
            [
                'field' => 'employment_type',
                'label' => 'Employment Type',
                'rules' => 'required|in_list[Private Job, Government Service, Armed Forces/Police, Lawyer, Journalist, Self-Employed]'
            ]
        ];
        $this->form_validation->set_rules($validationRules);
    }
}