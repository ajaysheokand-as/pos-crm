<?php

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class CustomerCampaignApi extends REST_Controller
{

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
    }

    public function storeCustomerData_post()
    {
        try {
            if ($this->input->server('REQUEST_METHOD') == 'POST') {
                $this->setValidationRules();
                if ($this->form_validation->run() == FALSE) {
                    $response = ['success' => false, 'errors' => $this->form_validation->error_array()];
                    $responseCode = 400;
                } else {
                    $formData = $this->input->post();
                    $recaptchaCode = $formData['recaptcha_code'];
                    unset($formData['recaptcha_code']);
                    $secretKey = $this->googleCaptchSecretKey;
                    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaCode");
                    $responseData = json_decode($verifyResponse);
                    if (!$responseData->success || $responseData->score < 0.5) {
                        $responseCode = 400;
                        $response = ['success' => false, "message" => "reCAPTCHA verification failed. Please try again."];
                    } else {
                        $responseCode = 200;
                        $formData['lead_source'] = 'Campaign';
                        $formData['created_at'] = date('Y-m-d H:i:s');
                        $this->Tasks->insert($formData, 'instant_loan_campaign');
                        $response = ['success' => true, 'message' => 'Customer saved successfully!'];
                    }
                }

            } else {
                $responseCode = 404;
                $response = [
                    'success' => false,
                    'message' => $this->input->server('REQUEST_METHOD') . ' method is not supported!'
                ];
            }
        } catch (Exception $exception) {
            $responseCode = 401;
            $response = [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_status_header($responseCode)
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
                'label' => 'First Name',
                'rules' => 'required|alpha|trim'
            ],
            [
                'field' => 'last_name',
                'label' => 'Last Name',
                'rules' => 'alpha|trim'
            ],
            [
                'field' => 'phone_number',
                'label' => 'Mobile Number',
                'rules' => 'required|regex_match[/^[6-9][0-9]{9}$/]'
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'valid_email|trim' 
            ],
            [
                'field' => 'city',
                'label' => 'City',
                'rules' => 'required|alpha|trim' 
            ],
            [
                'field' => 'current_salary',
                'label' => 'Salary',
                'rules' => 'alpha_numeric|trim' 
            ],
        ];
        $this->form_validation->set_rules($validationRules);
    }
}