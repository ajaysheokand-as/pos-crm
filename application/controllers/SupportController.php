<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SupportController extends CI_Controller {

    var $user_access = [1, 91, 106, 65];
    var $allow_status_id = [2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 25, 30, 35, 37];

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Support_Model', 'support');
        define('created_on', date('Y-m-d H:i:s'));
        set_time_limit(300);
        date_default_timezone_set('Asia/Kolkata');
        ini_set('max_execution_time', 3600);
        ini_set("memory_limit", "1024M");
        $login = new IsLogin();
        $login->index();
    }

    public function eKYCReset() {
        $lead_id = $_GET['lead_id'];
        $agent = $_SESSION['isUserSession']['labels'];
        $user_id = $_SESSION['isUserSession']['user_id'];

        $data = [
            'ekyc_active' => 0,
            'ekyc_deleted' => 1
        ];

        if (empty($lead_id)) {
            echo "Lead_id Missing.!!!";
            exit;
        }

        if ($agent == 'CA' || $agent == 'ST'  || in_array($user_id, $this->user_access)) {
            $this->db->where('ekyc_lead_id', $lead_id)->update('api_ekyc_logs', $data);
            echo "eKYC Reset Successfully.!!!";
        } else {
            echo "Access Denied.!!!";
        }
    }

    public function eSignReset() {
        $lead_id = $_GET['lead_id'];
        $agent = $_SESSION['isUserSession']['labels'];
        $user_id = $_SESSION['isUserSession']['user_id'];

        if (empty($lead_id)) {
            echo "Lead_id Missing.!!!";
            exit;
        }

        if ($agent == 'CA' || $agent == 'ST' || in_array($user_id, $this->user_access)) {
            $data = [
                'esign_active' => 0,
                'esign_deleted' => 1
            ];

            if (empty($lead_id)) {
                echo "Lead_id Missing.!!!";
                exit;
            }


            $this->db->where('esign_lead_id', $lead_id)->update('api_esign_logs', $data);
            echo "eSign Reset Successfully.!!!";
        } else {
            echo "Access Denied.!!!";
        }
    }

    public function resetEkycEsignLinks() {
        $this->load->view('Support/resetLinks');
    }

    public function personalDetails() {
        $this->load->view('Support/personalDetails');
    }

    public function employmentDetails() {
        $this->load->view('Support/employmentDetails');
    }

    public function blackList() {
        $this->load->view('Support/blackList');
    }

    public function referenceDetails() {
        $this->load->view('Support/referenceDetails');
    }

    public function transactionFailedDetails() {
        $this->load->view('Support/transactionFailedDetails');
    }

    public function docsDetails() {
        $this->load->view('Support/docsDetails');
    }

    public function camDetails() {
        $this->load->view('Support/camDetails');
    }

    public function bankDetails() {
        $this->load->view('Support/bankDetails');
    }

    public function searchBankId() {

        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('CB.*')->from('customer_banking CB')->where('CB.lead_id', $lead_id)->where_not_in('beneficiary_name', '')->order_by('id', 'DESC')->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['bank_list'] = $search_data->row_array();
            // print_R($data['leadInfo']); die;
            $data['leadInfo'] = $search_data->result_array();
            $this->load->view('Support/bankDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record not found.');
            $this->load->view('Support/bankDetails');
        }
    }


    public function searchResetLeadId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 1);
        $search_data = $this->db->select('LD.*,LC.*')->from('leads LD')->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id ', 'INNER')->where('LD.lead_id', $lead_id)->get();
        $search_data = $this->db->select('LD.*, LC.*')
            ->from('leads LD')
            ->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER')
            ->where('LD.lead_id', $lead_id)
            ->get();

        if ($search_data->num_rows() > 0) {

            $data['status'] = 1;
            $data['leadInfo'] = $search_data->row_array();
            $data['religion_list'] = $this->db->select('religion_id,religion_name')->from('master_religion')->where('religion_active', 1)->get()->result_array();
            $data['marital_status_list'] = $this->db->select('m_marital_status_id,m_marital_status_name')->from('master_marital_status')->where('m_marital_status_active', 1)->get()->result_array();
            $data['occupation_list'] = $this->db->select('m_occupation_id,m_occupation_name')->from('master_occupation')->where('m_occupation_active', 1)->get()->result_array();
            $state_id = $data['leadInfo']['current_state'];
            $city_id = $data['leadInfo']['current_city'];
            $data['state_list'] = $this->db->select('m_state_id,m_state_name')->from('master_state')->where('m_state_active', 1)->get()->result_array();
            $data['city_list'] = $this->db->select('m_city_id,m_city_name')->from('master_city')->where('m_city_state_id', $state_id)->where('m_city_active', 1)->get()->result_array();
            $data['pincode_list'] = $this->db->select('m_pincode_id,m_pincode_value')->from('master_pincode')->where('m_pincode_city_id', $city_id)->where('m_pincode_active', 1)->get()->result_array();
            $data['lead_source'] = $this->db->select('data_source_id,data_source_name,data_source_code')->from('master_data_source')->where('data_source_active', 1)->get()->result_array();
            $data['lead_status'] = $this->db->select('status_id,status_name,status_stage,status_order')->from('master_status')->where('status_active', 1)->get()->result_array();
            $data['qualification_list'] = $this->db->select('m_qualification_id,m_qualification_name')->from('master_qualification')->where('m_qualification_active', 1)->get()->result_array();
            $data['api_ekyc_log'] = $this->db->select('*')->from('lead_followup')->where('lead_id', $lead_id)->where('lead_followup_active', 1)->order_by('id', 'DESC')->limit(5)->get()->result_array();
            $data['api_ekyc_log'] = $this->db->select('lead_followup.*, users.name')->from('lead_followup')->join('users', 'users.user_id = lead_followup.user_id', 'inner')->where('lead_followup.lead_id', $lead_id)
                ->where('lead_followup.lead_followup_active', 1)->order_by('lead_followup.id', 'DESC')->limit(5)->get()->result_array();
            $data['user_list'] = $this->db->select('user_id,name')->from('users')->where('user_active', 1)->get()->result_array();
            $this->load->view('Support/resetLinks', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/resetLinks');
        }
    }

    public function updateekycLink() {
        $lead_id = $this->input->post('lead_id');
        $ekyc_active = $this->input->post('ekyc_active');
        $ekyc_deleted = $this->input->post('ekyc_deleted');

        // Check if the record exists
        $search_data = $this->db->select('*')->from('api_ekyc_logs')->where('ekyc_active', 1)->where('ekyc_lead_id', $lead_id)->get();

        if ($search_data->num_rows() > 0) {
            // Corrected from $bl_id to $lead_id
            $response = $this->db->where(['ekyc_lead_id' => $lead_id])->support->updateBankData('api_ekyc_logs', [
                'ekyc_active' => $ekyc_active,
                'ekyc_deleted' => $ekyc_deleted
            ]);

            if ($response) {
                $json['msg'] = 'Successfully updated.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/blackList');
        }
    }


    public function update_esign() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_id', 'Lead Id', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo json_encode(['err' => validation_errors()]);
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $lead_id = $this->input->post('lead_id');
                $data = [
                    'esign_active' => 0,
                    'esign_deleted' => 1,
                    'updated_on' => date('Y-m-d H:i:s')
                ];
                $search_data = $this->db->select('*')->from('api_esign_logs')->where('esign_lead_id ', $lead_id)->get();


                //   $search_data = $this->db->last_query();
                //   echo $search_data; die;

                if ($search_data->num_rows() > 0) {
                    $response = $this->lead->update_esign($lead_id, $data);

                    if ($response) {
                        $json['msg'] = 'Successfully updated.';
                    } else {
                        $json['err'] = 'Update failed. Please try again.';
                    }
                } else {
                    $json['err'] = 'Lead not found.';
                }
                echo json_encode($json);
            }
        } else {
            echo json_encode(['err' => 'Session expired. Please log in first.']);
            $this->login->index();
        }
    }

    public function searchLeadId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('LD.*, LC.*')
            ->from('leads LD')
            ->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER')
            ->where('LD.lead_id', $lead_id)
            ->where_in('LD.lead_status_id', array(5, 12))
            ->get();

        if ($search_data->num_rows() > 0) {

            $data['status'] = 1;
            $data['leadInfo'] = $search_data->row_array();
            $data['religion_list'] = $this->db->select('religion_id,religion_name')->from('master_religion')->where('religion_active', 1)->get()->result_array();
            $data['marital_status_list'] = $this->db->select('m_marital_status_id,m_marital_status_name')->from('master_marital_status')->where('m_marital_status_active', 1)->get()->result_array();
            $data['occupation_list'] = $this->db->select('m_occupation_id,m_occupation_name')->from('master_occupation')->where('m_occupation_active', 1)->get()->result_array();
            $state_id = $data['leadInfo']['current_state'];
            $city_id = $data['leadInfo']['current_city'];
            $data['state_list'] = $this->db->select('m_state_id,m_state_name')->from('master_state')->where('m_state_active', 1)->get()->result_array();
            $data['city_list'] = $this->db->select('m_city_id,m_city_name')->from('master_city')->where('m_city_state_id', $state_id)->where('m_city_active', 1)->get()->result_array();
            $data['pincode_list'] = $this->db->select('m_pincode_id,m_pincode_value')->from('master_pincode')->where('m_pincode_city_id', $city_id)->where('m_pincode_active', 1)->get()->result_array();
            $data['lead_source'] = $this->db->select('data_source_id,data_source_name,data_source_code')->from('master_data_source')->where('data_source_active', 1)->get()->result_array();
            $data['lead_status'] = $this->db->select('status_id,status_name,status_stage,status_order')->from('master_status')->where('status_active', 1)->get()->result_array();
            $data['qualification_list'] = $this->db->select('m_qualification_id,m_qualification_name')->from('master_qualification')->where('m_qualification_active', 1)->get()->result_array();
            $data['user_list'] = $this->db->select('user_id,name')->from('users')->where('user_active', 1)->get()->result_array();

            $this->load->view('Support/personalDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/personalDetails');
        }
    }

    public function searchCamId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('CAM.*')->from('credit_analysis_memo CAM')->where('CAM.lead_id', $lead_id)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->row_array();
            $this->load->view('Support/camDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record not found.');
            $this->load->view('Support/camDetails');
        }
    }

    public function searchEmploymentId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('*')->from('customer_employment')->where('lead_id', $lead_id)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->row_array();
            $state_id = $data['leadInfo']['state_id'];
            $data['state_list'] = $this->db->select('m_state_id,m_state_name')->from('master_state')->where('m_state_active', 1)->get()->result_array();
            $data['city_list'] = $this->db->select('m_city_id,m_city_name')->from('master_city')->where('m_city_state_id', $state_id)->where('m_city_active', 1)->get()->result_array();
            $data['designation_list'] = $this->db->select('m_designation_id,m_designation_name')->from('master_designation')->where('m_designation_active', 1)->get()->result_array();
            $data['department_list'] = $this->db->select('department_id,department_name')->from('master_department')->where('department_active', 1)->get()->result_array();
            $data['occupation_list'] = $this->db->select('m_occupation_id,m_occupation_name')->from('master_occupation')->where('m_occupation_active', 1)->get()->result_array();
            $data['company_type_list'] = $this->db->select('m_company_type_id,m_company_type_name')->from('master_company_type')->where('m_company_type_active', 1)->get()->result_array();
            $data['salary_mode_list'] = $this->db->select('m_salary_mode_id,m_salary_mode_name')->from('master_salary_mode')->where('m_salary_mode_active', 1)->get()->result_array();
            $this->load->view('Support/employmentDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/employmentDetails');
        }
    }

    public function searchBlackList() {
        $bl_loan_no = trim($_POST['bl_loan_no']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('*')->from('customer_black_list')->where('bl_loan_no', $bl_loan_no)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->result_array();
            $this->load->view('Support/blackList', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/blackList');
        }
    }

    public function searchReferenceId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('LR.*,MRT.mrt_name')->from('lead_customer_references LR')->join('master_relation_type MRT', 'MRT.mrt_id = LR.lcr_relationType ', 'LEFT')->where('LR.lcr_active', 1)->where('LR.lcr_lead_id', $lead_id)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->result_array();
            $this->load->view('Support/referenceDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/referenceDetails');
        }
    }

    public function searchTransactionId() {
        $lead_id = trim($_POST['lead_id']);
        $return_array = array('status' => 0);
        $search_data = $this->db->select('LN.*,LDTL.*,MDB.disb_bank_name')->from('loan LN')->join('lead_disbursement_trans_log LDTL', 'LDTL.disb_trans_lead_id = LN.lead_id', 'INNER')->join('master_disbursement_banks MDB', 'MDB.disb_bank_id = LDTL.disb_trans_bank_id ', 'LEFT')->where('LDTL.disb_trans_active', 1)->where('LN.lead_id', $lead_id)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->result_array();
            $this->load->view('Support/transactionFailedDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/transactionFailedDetails');
        }
    }

    public function getBankDetailId($leadid) {
        $lead_id = $this->encrypt->decode($leadid);
        $data['leadInfo'] = $this->db->select('*')->from('customer_banking')->where('lead_id', $lead_id)->where('account_status_id', 1)->get()->row_array();
        $data['bank_type_list'] = $this->db->select('m_bank_type_id,m_bank_type_name')->from('master_bank_type')->where('m_bank_type_active', 1)->get()->result_array();
        $this->load->view('Support/bankDetails', $data);
    }

    public function getReferenceId($ref_id) {
        $refId = $this->encrypt->decode($ref_id);
        $data['referenceInfo'] = $this->db->select('*')->from('lead_customer_references')->where('lcr_id', $refId)->get()->row_array();
        $data['relation_list'] = $this->db->select('mrt_id,mrt_name')->from('master_relation_type')->where('mrt_active', 1)->get()->result_array();
        $this->load->view('Support/referenceDetails', $data);
    }

    public function getTransactionId($disb_trans_id) {
        $disb_trans_id = $this->encrypt->decode($disb_trans_id);
        $data['transInfo'] = $this->db->select('LN.*,LDTL.*,MDB.disb_bank_name')->from('lead_disbursement_trans_log LDTL')->join('loan LN', 'LDTL.disb_trans_lead_id = LN.lead_id', 'INNER')->join('master_disbursement_banks MDB', 'MDB.disb_bank_id = LDTL.disb_trans_bank_id ', 'LEFT')->where('disb_trans_id', $disb_trans_id)->get()->row_array();
        //$data['relation_list'] = $this->db->select('mrt_id,mrt_name')->from('master_relation_type')->where('mrt_active',1)->get()->result_array();
        $this->load->view('Support/transactionFailedDetails', $data);
    }

    public function searchDocsId() {
        $lead_id = trim($_POST['lead_id']);

        $return_array = array('status' => 0);
        $search_data = $this->db->select('*')->from('docs')->where('lead_id', $lead_id)->get();
        if ($search_data->num_rows() > 0) {
            $data['status'] = 1;
            $data['leadInfo'] = $search_data->result_array();
            $this->load->view('Support/docsDetails', $data);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/docsDetails');
        }
    }


    public function blockUpdate() {
        $bl_id = $_POST['bl_id'];
        if (isset($_POST['bl_active']) && $_POST['bl_active'] == 1) {
            $bl_active = 0;
        } else {
            $bl_active = 1;
        }

        if (isset($_POST['bl_deleted']) && $_POST['bl_deleted'] == 1) {
            $bl_deleted = 0;
        } else {
            $bl_deleted = 1;
        }

        $check_data = $this->db->select('*')->from('customer_black_list')->where('bl_id', $bl_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['bl_id' => $bl_id])->update('customer_black_list', ['bl_active' => $bl_active, 'bl_deleted' => $bl_deleted]);
            if ($response) {
                $json['msg'] = 'Successfully updated.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/blackList');
        }
    }

    public function docsDelete() {
        $docs_id = $this->encrypt->decode($_POST['docs_id']);
        $check_data = $this->db->select('*')->from('docs')->where('docs_active', 1)->where('docs_id', $docs_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['docs_id' => $docs_id])->update('docs', ['docs_active' => 0, 'docs_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/docsDetails');
        }
    }

    public function referenceDelete() {
        $ref_id = $this->encrypt->decode($_POST['ref_id']);
        $check_data = $this->db->select('*')->from('lead_customer_references')->where('lcr_active', 1)->where('lcr_id', $ref_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['lcr_id' => $ref_id])->update('lead_customer_references', ['lcr_active' => 0, 'lcr_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/referenceDetails');
        }
    }

    public function transactionDelete() {
        $disb_trans_id = $this->encrypt->decode($_POST['disb_trans_id']);
        $check_data = $this->db->select('*')->from('lead_disbursement_trans_log')->where('disb_trans_active', 1)->where('disb_trans_id', $disb_trans_id)->get();
        if ($check_data->num_rows() > 0) {
            $response = $this->db->where(['disb_trans_id' => $disb_trans_id])->update('lead_disbursement_trans_log', ['disb_trans_active' => 0, 'disb_trans_deleted' => 1]);
            if ($response) {
                $json['msg'] = 'Successfully deleted.';
            } else {
                $json['err'] = 'Not updated.';
            }
            echo json_encode($json);
        } else {
            $this->session->set_flashdata('error', 'Record Not found.');
            $this->load->view('Support/transactionFailedDetails');
        }
    }

    public function updatePersonalDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $check_email = $this->input->post('check_email');
                $check_alternate_email = $this->input->post('check_alternate_email');
                $check_mobile = $this->input->post('check_mobile');
                $check_alternate_mobile = $this->input->post('check_alternate_mobile');
                $lead_id = $this->input->post('lead_id');
                $email = $this->input->post('email');
                $alternate_email = $this->input->post('alternate_email');
                $mobile = $this->input->post('mobile');
                $alternate_mobile = $this->input->post('alternate_mobile');
                $loan_amount = $this->input->post('loan_amount');
                $pancard = $this->input->post('pancard');
                $gender = $this->input->post('gender');
                $dob = $this->input->post('dob');
                $religion_id = $this->input->post('religion_id');
                $marital_status_id = $this->input->post('marital_status_id');
                $qualification = $this->input->post('qualification');
                $spouse_name = $this->input->post('spouse_name');
                $spouse_occupation_id = $this->input->post('spouse_occupation_id');
                $current_house = $this->input->post('current_house');
                $current_locality = $this->input->post('current_locality');
                $current_landmark = $this->input->post('current_landmark');
                $current_state = $this->input->post('current_state');
                $current_city = $this->input->post('current_city');
                $residence_pincode = $this->input->post('residence_pincode');
                $lead_data_source = $this->input->post('lead_data_source');
                $source = $this->input->post('source');
                $utm_source = $this->input->post('utm_source');
                $utm_campaign = $this->input->post('utm_campaign');
                $lead_stp_flag = $this->input->post('lead_stp_flag');

                if ($lead_data_source == '1') {
                    $source = 'Website BL';
                    $utm_source = 'Website BL';
                    $utm_campaign = 'bharatloan.com';
                    $lead_stp_flag = '0';
                }

                $lead_screener_assign_user = $this->input->post('lead_screener_assign_user');
                $stage = $this->input->post('stage');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                //echo $check_email.'@'.$check_alternate_email.'@'.$check_mobile.'@'.$check_alternate_mobile;die;
                $data = [
                    'user_id' => $user_id,
                    'check_email' => $check_email,
                    'check_alternate_email' => $check_alternate_email,
                    'check_mobile' => $check_mobile,
                    'check_alternate_mobile' => $check_alternate_mobile,
                    'email' => $email,
                    'alternate_email' => $alternate_email,
                    'mobile' => $mobile,
                    'alternate_mobile' => $alternate_mobile,
                    'loan_amount' => $loan_amount,
                    'pancard' => $pancard,
                    'gender' => $gender,
                    'dob' => $dob,
                    'religion_id' => $religion_id,
                    'marital_status_id' => $marital_status_id,
                    'qualification' => $qualification,
                    'spouse_name' => $spouse_name,
                    'spouse_occupation_id' => $spouse_occupation_id,
                    'current_house' => $current_house,
                    'current_locality' => $current_locality,
                    'current_landmark' => $current_landmark,
                    'current_state' => $current_state,
                    'current_city' => $current_city,
                    'residence_pincode' => $residence_pincode,
                    'source' => $source,
                    'utm_source' => $utm_source,
                    'utm_campaign' => $utm_campaign,
                    'lead_data_source' => $lead_data_source,
                    'lead_stp_flag' => $lead_stp_flag,
                    'lead_screener_assign_user' => $lead_screener_assign_user,
                    'stage' => $stage,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];

                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();
                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updatePersonalData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }

    public function updateTransactionDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('disb_trans_payment_mode', 'Payment Mode', 'required');
            $this->form_validation->set_rules('loan_disbursement_payment_type', 'Trans Payment Type', 'required');
            $this->form_validation->set_rules('disb_trans_status', 'Trans Status', 'required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $disb_trans_id = $this->input->post('disb_trans_id');
                $lead_id = $this->input->post('lead_id');
                $disburse_refrence_no = $this->input->post('disburse_refrence_no');
                $disb_trans_payment_mode = $this->input->post('disb_trans_payment_mode');
                $loan_disbursement_payment_type = $this->input->post('loan_disbursement_payment_type');
                $disb_trans_status = $this->input->post('disb_trans_status');
                $remarks = $this->input->post('remarks');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    'disb_trans_id' => $disb_trans_id,
                    'disburse_refrence_no' => $disburse_refrence_no,
                    'disb_trans_payment_mode' => $disb_trans_payment_mode,
                    'loan_disbursement_payment_type' => $loan_disbursement_payment_type,
                    'disb_trans_status' => $disb_trans_status,
                    'remarks' => $remarks,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];

                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->where('lead_status_id', 13)->get();
                if ($search_data->num_rows() > 0) {
                    $response = $this->support->updateTransactionData($data);
                    if ($response) {
                        $json['msg'] = 'Successfully updated.';
                    } else {
                        $json['err'] = 'Not updated.';
                    }
                } else {
                    $json['err'] = 'This case is not in Disburse Pending!';
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }

    public function updateEmploymentDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('employer_name', 'Name', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $lead_id = $this->input->post('lead_id');
                $employer_name = $this->input->post('employer_name');
                $emp_email = $this->input->post('emp_email');
                $emp_house = $this->input->post('emp_house');
                $emp_street = $this->input->post('emp_street');
                $emp_landmark = $this->input->post('emp_landmark');
                $state = $this->input->post('state');
                $city = $this->input->post('city');
                $emp_pincode = $this->input->post('emp_pincode');
                $emp_residence_since = $this->input->post('emp_residence_since');
                $emp_designation = $this->input->post('emp_designation');
                $emp_department = $this->input->post('emp_department');
                $emp_occupation_id = $this->input->post('emp_occupation_id');
                $emp_employer_type = $this->input->post('emp_employer_type');
                $salary_mode = $this->input->post('salary_mode');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    'employer_name' => $employer_name,
                    'emp_email' => $emp_email,
                    'emp_house' => $emp_house,
                    'emp_street' => $emp_street,
                    'emp_landmark' => $emp_landmark,
                    'state' => $state,
                    'city' => $city,
                    'emp_pincode' => $emp_pincode,
                    'emp_residence_since' => $emp_residence_since,
                    'emp_designation' => $emp_designation,
                    'emp_department' => $emp_department,
                    'emp_occupation_id' => $emp_occupation_id,
                    'emp_employer_type' => $emp_employer_type,
                    'salary_mode' => $salary_mode,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];
                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();
                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updateEmploymentData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }

    public function updateReferenceDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lcr_name', 'Name', 'trim|required');
            $this->form_validation->set_rules('lcr_mobile', 'Mobile', 'trim|required');
            $this->form_validation->set_rules('lcr_relationType', 'Relation Type', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $lcr_id = $this->input->post('lcr_id');
                $lead_id = $this->input->post('lead_id');
                $lcr_name = $this->input->post('lcr_name');
                $lcr_mobile = $this->input->post('lcr_mobile');
                $lcr_relationType = $this->input->post('lcr_relationType');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    'lcr_id' => $lcr_id,
                    'lcr_name' => $lcr_name,
                    'lcr_mobile' => $lcr_mobile,
                    'lcr_relationType' => $lcr_relationType,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];
                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();
                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updateReferenceData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }

    public function updateBankDetail() {

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
            $this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $bank_name = $this->input->post('bank_name');
                $id = $this->input->post('id');
                $lead_id = $this->input->post('lead_id');
                $ifsc_code = $this->input->post('ifsc_code');
                $beneficiary_name = $this->input->post('beneficiary_name');
                $account_status = $this->input->post('account_status');
                $account = $this->input->post('account');
                $confirm_account = $this->input->post('confirm_account');
                $account_type = $this->input->post('account_type');
                $branch = $this->input->post('branch');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    'bank_name' => $bank_name,
                    'ifsc_code' => $ifsc_code,
                    'beneficiary_name' => $beneficiary_name,
                    'account_status' => $account_status,
                    'account' => $account,
                    'confirm_account' => $confirm_account,
                    'account_type' => $account_type,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'id' => $id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];
                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();

                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updateBankData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }


    public function updateDocsDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('lead_followup_remark', 'Remark', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $lead_id = $this->input->post('lead_id');
                //$pancard = $this->input->post('pancard');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    //'pancard' => $pancard,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];
                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();
                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updateDocsData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }

    public function updateCAMDetail() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('salary_credit1_date', 'Salary Date 1', 'trim|required');
            $this->form_validation->set_rules('salary_credit1_amount', 'Salary Amount 1', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $user_id = $_SESSION['isUserSession']['user_id'];
                $lead_id = $this->input->post('lead_id');
                $salary_credit1_date = $this->input->post('salary_credit1_date');
                $salary_credit1_amount = $this->input->post('salary_credit1_amount');
                $salary_credit2_date = $this->input->post('salary_credit2_date');
                $salary_credit2_amount = $this->input->post('salary_credit2_amount');
                $salary_credit3_date = $this->input->post('salary_credit3_date');
                $salary_credit3_amount = $this->input->post('salary_credit3_amount');
                $next_pay_date = $this->input->post('next_pay_date');
                $median_salary = $this->input->post('median_salary');
                $remark = $this->input->post('remark');
                $lead_followup_remark = $this->input->post('lead_followup_remark');
                $data = [
                    'user_id' => $user_id,
                    'salary_credit1_date' => $salary_credit1_date,
                    'salary_credit1_amount' => $salary_credit1_amount,
                    'salary_credit2_date' => $salary_credit2_date,
                    'salary_credit2_amount' => $salary_credit2_amount,
                    'salary_credit3_date' => $salary_credit3_date,
                    'salary_credit3_amount' => $salary_credit3_amount,
                    'next_pay_date' => $next_pay_date,
                    'median_salary' => $median_salary,
                    'remark' => $remark,
                    'lead_followup_remark' => $lead_followup_remark,
                    'updated_by' => $user_id,
                    'lead_id' => $lead_id,
                    'updated_on' => timestamp
                ];
                $search_data = $this->db->select('*')->from('leads')->where('lead_active', 1)->where('lead_id', $lead_id)->get();
                if ($search_data->num_rows() > 0) {
                    $leadDetail = $search_data->row_array();
                    if (in_array($leadDetail['lead_status_id'], $this->allow_status_id)) {
                        $response = $this->support->updateCAMData($data);
                        if ($response) {
                            $json['msg'] = 'Successfully updated.';
                        } else {
                            $json['err'] = 'Not updated.';
                        }
                    } else {
                        $json['err'] = 'This case is not update,Because this is Disburse!';
                    }
                }
                echo json_encode($json);
            }
        } else {
            echo "Session Expired. Please login first.";
            $login->index();
        }
    }


    public function pincodeList() {
        $this->load->library('pagination');

        // Base URL for pagination
        $url = base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3);

        $conditions = "MP.m_pincode_active=1";
        $total_count = $this->Tasks->masterPincodeCount($conditions); // Ensure this function returns the correct total count

        $config = array();
        $config["base_url"] = $url . '?'; // Ensure the base URL ends with '?' for query string pagination
        $config["total_rows"] = $total_count;
        $config["per_page"] = 20; // Set the number of records per page
        $config["uri_segment"] = 3;
        $config["page_query_string"] = TRUE; // Enable query string pagination
        $config['query_string_segment'] = 'per_page'; // Use 'per_page' as the query parameter
        $config['full_tag_open'] = '<div class="pagging"><nav><ul class="pagination">';
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

        // Initialize pagination
        $this->pagination->initialize($config);

        // Calculate the offset for the query
        $page = !empty($_GET['per_page']) ? intval($_GET['per_page']) : 0;
        // Fetch city data with limit and offset
        $city_data = $this->db->distinct()
            ->select('MP.*, MC.m_city_name, MC.m_city_id')
            ->from('master_pincode MP')
            ->join('master_city MC', 'MP.m_pincode_city_id = MC.m_city_id', 'INNER')
            ->where($conditions)
            ->group_by('MP.m_pincode_city_id')
            ->limit($config["per_page"], $page)
            ->get()
            ->result_array();

        $data['city_data'] = !empty($city_data) ? $city_data : []; // Ensure it's always an array
        $data['pageURL'] = $url;
        $data['links'] = $this->pagination->create_links();
        $data['totalcount'] = $total_count;

        // Load the view with the data array
        $this->load->view('Support/pincodeList', $data);
    }


    public function addPincode() {
        $data['cities'] = $this->support->get_cities();
        // $this->load->view('address_form', $data);
        $this->load->view('Support/addPincode', $data);
    }

    public function Searchpincode() {

        $city_data1 = $this->db->distinct()
            ->select('MP.*, MC.m_city_name, MC.m_city_id')
            ->from('master_pincode MP')
            ->join('master_city MC', 'MP.m_pincode_city_id = MC.m_city_id', 'INNER') // Changed to INNER JOIN
            ->where('MP.m_pincode_active', 1)
            ->group_by('MP.m_pincode_city_id')
            ->get()
            ->result_array();
        $data['city_data1'] = $city_data1;
        $pincode = $this->input->post('master_pincode');
        $city_name = $this->input->post('m_city_name');
        $return_array = array('status' => 0);
        $this->db->select('mc.m_city_name, mp.m_pincode_value');
        $this->db->from('master_city mc');
        $this->db->join('master_pincode mp', 'mc.m_city_id = mp.m_pincode_city_id');
        if (!empty($pincode)) {
            $this->db->where('mp.m_pincode_value', $pincode);
        }
        if (!empty($city_name)) {
            $this->db->where('mc.m_city_id', $city_name);
        }

        $query = $this->db->get();
        $this->db->last_query();
        if ($query->num_rows() > 0) {
            $data['pincode_details'] = $query->result();
            $this->load->view('Support/pincodeDetail', $data, $data1);
        } else {

            $this->load->view('Support/pincodeDetail', $data, $data1);
        }
    }
}
