<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class SearchController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model');
        $this->load->model('Admin_Model');

        $login = new IsLogin();
        $login->index();
    }

    public function index() {
        $this->load->view('Search/index');
    }

    public function filter() {
        if (empty($_SESSION['isUserSession']['user_id'])) {
            $json['errSession'] = "Session Expired";
            echo json_encode($json);
        } 
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
     
            $lead_id = $this->input->post('lead_id');
            $lead_reference_no = $this->input->post('lead_reference_no');
            $loan_no = $this->input->post('loan_no');
            $pancard = $this->input->post('pancard');
            $name = $this->input->post('name');
            $mobile = $this->input->post('mobile');
            $application_no = $this->input->post('application_no');
            $aadhar = $this->input->post('aadhar');
            $cif = $this->input->post('cif');
            $email = $this->input->post('email');
        
        if (empty($lead_id) && empty($lead_reference_no) && empty($loan_no) && empty($pancard) && empty($name) && empty($mobile) && empty($application_no) && empty($aadhar) && empty($cif) && empty($email)) {
            $datatable = '<p style="text-align: left;color : red;">Please fill in at least one input field.</p>';
            echo json_encode($datatable);
            exit;
        }
        else {
            $lead_id = intval(trim($this->input->post('lead_id')));
            $lead_reference_no = strval(trim($this->input->post('lead_reference_no')));
            $loan_no = strval(trim($this->input->post('loan_no')));
            $pancard = strval(trim($this->input->post('pancard')));
            $name = strval(trim($this->input->post('name')));
            $mobile = intval(trim($this->input->post('mobile')));
            $application_no = strval(trim($this->input->post('application_no')));
            $aadhar = strval(trim($this->input->post('aadhar')));
            $cif = strval(trim($this->input->post('cif')));
            $email = strval(trim($this->input->post('email')));

            $querySearch = "SELECT DISTINCT LD.lead_id, LD.customer_id, LL.loan_no, LL.loan_status_id, C.first_name, C.middle_name, C.sur_name, C.email, C.mobile, C.pancard, CAM.loan_recommended as loan_amount_approved, LD.status as credit_status, LD.status, LD.lead_final_disbursed_date, CAM.created_at as credit_date, LD.created_on as lead_date, CT.m_city_name as city ";
            $querySearch .= " , LD.lead_black_list_flag,CAM.repayment_date";
            $querySearch .= " FROM leads LD ";
            $querySearch .= " LEFT JOIN lead_customer C ON C.customer_lead_id = LD.lead_id ";
            $querySearch .= " LEFT JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id ";
            $querySearch .= " LEFT JOIN loan LL ON LL.lead_id = LD.lead_id ";
            $querySearch .= " LEFT JOIN master_city CT ON CT.m_city_id = LD.city_id ";

            if (!empty($loan_no)) {
                $querySearch .= " where LD.loan_no ='" . $loan_no . "'";
            }

            if (!empty($lead_id)) {
                $querySearch .= " where LD.lead_id ='" . $lead_id . "'";
            }

            if (!empty($lead_reference_no)) {
                $querySearch .= " where LD.lead_reference_no ='" . $lead_reference_no . "'";
            }

            if (!empty($pancard)) {
                $querySearch .= " where C.pancard ='" . $pancard . "'";
            }

            if (!empty($email)) {
                $querySearch .= " where C.email ='" . $email . "'";
            }

            if (!empty($name)) {
                $querySearch .= " where LD.first_name LIKE'" . $name . "%'";
            }

            if (!empty($mobile)) {
                $querySearch .= " where C.mobile ='" . $mobile . "'";
            }

            if (!empty($application_no)) {
                $querySearch .= " where LD.application_no ='" . $application_no . "'";
            }

            if (!empty($aadhar)) {
                $querySearch .= " where C.aadhar ='" . $aadhar . "'";
            }

            if (!empty($cif)) {
                $querySearch .= " where LD.customer_id ='" . $cif . "'";
            }

            $querySearch .= " ORDER BY LD.created_on ASC";

            $query = $this->db->query($querySearch);

            if ($this->session->userdata['isUserSession']['role'] == 'Recovery' ||
                    $this->session->userdata['isUserSession']['role'] == 'MIS' ||
                    $this->session->userdata['isUserSession']['role'] == 'Admin') {
                $url = 'leads';
            } else {
                $url = 'leadDetails';
            }

            $action = '';
            if (agent != 'OL') {
                $action = '<th class="whitespace"><b>Action</b></th>';
            }
            $datatable = '<style>.widget-block {padding: 20px 0px;}</style><table class="table dt-table table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                            <thead>
                                <tr>
                                    <th class="whitespace"><b>#</b></th>
                                    ' . $action . '
                                    <th class="whitespace"><b>Lead&nbsp;ID</b></th>
                                    <th class="whitespace"><b>Customer&nbsp;ID</b></th>
                                    <th class="whitespace"><b>Loan&nbsp;No</b></th>
                                    <th class="whitespace"><b>Borrower&nbsp;Name</b></th>
                                    <th class="whitespace"><b>Email</b></th>
                                    <th class="whitespace"><b>Mobile</b></th>
                                    <th class="whitespace"><b>Pan</b></th>
                                    <th class="whitespace"><b>City</b></th>
                                    <th class="whitespace"><b>Loan&nbsp;Amount</b></th>
                                    <th class="whitespace"><b>Disbursed&nbsp;Date</b></th>
                                    <th class="whitespace"><b>Repayment Date</b></th>
                                    <th class="whitespace"><b>Blacklisted</b></th>
                                    <th class="whitespace"><b>Status</b></th>
                                    <th class="whitespace"><b>Apply&nbsp;Date</b></th>
                                   
                                </tr>
                            </thead>
                        <tbody>';
            $i = 1;
            if ($query->num_rows() > 0) {
                $editButton = '';
                foreach ($query->result() as $row) {
                    if (agent != 'OL') {
                        $editButton = '
                                <td class="whitespace">
                                    <a href="' . base_url("search/getleadDetails/" . $this->encrypt->encode($row->lead_id)) . '" class="" id="viewLeadsDetails"><span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span></a>
                                </td>';
                    }
                    $lead_id = $row->lead_id;
                    $customer_id = (($row->customer_id) ? $row->customer_id : '-');
                    $loan_no = (($row->loan_status_id == 14) ? $row->loan_no : '-');
                    $full_name = $row->first_name . ' ' . (($row->middle_name) ? $row->middle_name : '') . ' ' . (($row->sur_name) ? $row->sur_name : '');
                    $email = ((agent != 'OL') ? $row->email : str_pad(substr($row->email, -10), 15, 'X', STR_PAD_LEFT));
                    $mobile = ((agent != 'OL') ? $row->mobile : str_pad(substr($row->mobile, -4), 10, 'X', STR_PAD_LEFT));
                    $pancard = (($row->pancard) ? $row->pancard : '-');
                    $city = (($row->city) ? $row->city : '-');
                    $loan_amount_approved = (($row->loan_amount_approved) ? $row->loan_amount_approved : '-');
                    $lead_black_list_flag = (!empty($row->lead_black_list_flag) ? 'YES' : 'NO');
                      $credit_date = (($row->lead_final_disbursed_date) ? date('d-m-Y', strtotime($row->lead_final_disbursed_date)) : '-');
                    $repayment_date = ((!empty($row->repayment_date) && $row->repayment_date != '0000-00-00') ? date('d-m-Y', strtotime($row->repayment_date)) : '-');
                    $status = (($row->status) ? $row->status : '-');
                    $lead_date = (($row->lead_date) ? date('d-m-Y H:i:s', strtotime($row->lead_date)) : '-');
                  

                    $datatable .= '<tr>
                                <td class="whitespace">' . $i++ . '</td>
                                ' . $editButton . '
                                <td class="whitespace">' . $lead_id . '</td>
                                <td class="whitespace">' . $customer_id . '</td>
                                <td class="whitespace">' . $loan_no . '</td>
                                <td class="whitespace">' . $full_name . '</td>
                                <td class="whitespace">' . $email . '</td>
                                <td class="whitespace">' . inscriptionNumber($mobile) . '</td>
                                <td class="whitespace">' . $pancard . '</td>
                                <td class="whitespace">' . $city . '</td>
                                <td class="whitespace">' . $loan_amount_approved . '</td>
                                 <td class="whitespace">' . $credit_date . '</td>
                                <td class="whitespace">' . $repayment_date . '</td>
                                <td class="whitespace">' . $lead_black_list_flag . '</td>
                                <td class="whitespace">' . $status . '</td>
                                <td class="whitespace">' . $lead_date . '</td>
                               
                        </tr>';
                }
            } else {
                $datatable .= '<tr><td colspan="13" style="text-align: center;color : red;">No Record Found...</td></tr>';
            }
            $datatable .= '</tbody></table>';
            echo json_encode($datatable);
          }
        } else {
            $json['err'] = "Invalid Request";
            echo json_encode($json);
        }
    }

    public function exportData() {
        $data['filterMenu'] = $this->db->select('m.filter_id, m.sub_menu_id, m.name')->from('tbl_filter_sub_menu  m')->get();
        $this->load->view('Export/export', $data);
    }

}

?>
