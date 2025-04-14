<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BankDetailsController extends CI_Controller {

    public function __construct() {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('Task_Model', 'Tasks');
        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $result = $this->db->select('m_state_name')
                ->from('master_state')
                ->order_by('m_state_name')
                ->get();

        $data['state'] = $result->result_array();

        $this->load->view('Banking/addBankDetails', $data);
    }

    public function saveBankDetails() {

        // if ($_SERVER['REQUEST_METHOD'] == 'POST' && (agent == 'CA' || agent == "CR3")) {

            $ifsc_code = strval(trim($_POST['ifsc']));
            if (!empty($ifsc_code)) {

                $master_bank = $this->db->select('bank_ifsc')->from('tbl_bank_details')->where('bank_ifsc', $ifsc_code)->get();

                if ($master_bank->num_rows() > 0) {
                    $this->session->set_flashdata('err', 'Bank IFSC Code Already Exist!');
                    return redirect(base_url('addBankDetails'), 'refresh');
                } else {
                    $_POST = trim_data_array($_POST);
                    $bank_name = strval($_POST['name']);
                    $ifsc = strtoupper($ifsc_code);
                    $branch = strval($_POST['branch']);
                    $address = strval($_POST['address']);
                    $city = strval($_POST['city']);
                    $district = strval($_POST['district']);
                    $state = strval($_POST['state']);
                    $updated_by = $_SESSION['isUserSession']['user_id'];
                    $ip = $_SERVER['REMOTE_ADDR'];

                    if (!empty($ifsc) && !empty($bank_name) && !empty($branch) && !empty($updated_by) && !empty($state) && !empty($district) && !empty($city) && !empty($address)) {

                        $insert_data = array(
                            'bank_name' => $bank_name,
                            'bank_ifsc' => $ifsc,
                            'bank_branch' => $branch,
                            'bank_address' => $address,
                            'bank_city' => $city,
                            'bank_district' => $district,
                            'bank_state' => $state,
                            'updated_by' => $updated_by,
                            'ip' => $ip,
                            'updated_at' => date('Y-m-d H:i:s')
                        );

                        $this->db->insert('tbl_bank_details', $insert_data);

                        $this->session->set_flashdata('message', 'IFSC code has been saved sucessfully!');
                        return redirect(base_url('addBankDetails'), 'refresh');
                    } else {
                        $this->session->set_flashdata('err', 'Missing mandatory fields.');
                        return redirect(base_url('addBankDetails'), 'refresh');
                    }
                }
            } else {
                $this->session->set_flashdata('err', 'IFSC Code cannot be empty.');
                return redirect(base_url('addBankDetails'), 'refresh');
            }
        // } else {
        //     $this->session->set_flashdata('err', 'Invalid access');
        //     return redirect(base_url('addBankDetails'), 'refresh');
        // }
    }

    public function searchIfscCode() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (agent == 'CA' || agent == 'CR3')) {

            $ifsc_code = strval(trim($_POST['ifsc']));
            if (!empty($ifsc_code)) {

                $master_bank = $this->db->select('*')->from('tbl_bank_details')->where('bank_ifsc', $ifsc_code)->get();

                if ($master_bank->num_rows() > 0) {
                    $this->session->set_flashdata('error', 'IFSC code already exist.');
                } else {
                    $this->session->set_flashdata('err', 'Please add the ifsc code details.');
                }
                return redirect('addBankDetails');
            } else {
                $this->session->set_flashdata('error', 'Missing IFSC Code.');
                return redirect('addBankDetails');
            }
        }
    }

}
