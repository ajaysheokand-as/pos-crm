<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collection_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        define("ip", $this->input->ip_address());
    }

    public $visit_type = array(1 => 'Residence', 2 => 'Office');

    public function get_list_collection_followup($lead_id) {

        $result = array('error' => '', 'success' => '', 'data' => array());

        $select = 'LCF.lcf_id, LCF.lcf_lead_id, MFS.m_followup_status_heading, MFT.m_followup_type_id, MFT.m_followup_type_heading, MFT.m_followup_type_icons, LCF.lcf_remarks, LCF.lcf_next_schedule_datetime, ';
        $select .= ' LCF.lcf_fe_upload_selfie, LCF.lcf_fe_upload_location, LCF.total_distance_covered, ';
        $select .= ' LCF.lcf_created_on, U.name as followup_username ';

        $this->db->select($select);

        $this->db->from('loan_collection_followup LCF');
        $this->db->join('master_followup_status MFS', 'MFS.m_followup_status_id = LCF.lcf_status_id AND MFS.m_followup_status_active = 1 AND MFS.m_followup_status_deleted = 0', "LEFT");
        $this->db->join('master_followup_type MFT', 'MFT.m_followup_type_id = LCF.lcf_type_id AND MFT.m_followup_type_active = 1 AND MFT.m_followup_type_deleted = 0', "LEFT");
        $this->db->join('users U', 'U.user_id = LCF.lcf_user_id', 'left');

        $this->db->where(['LCF.lcf_lead_id' => $lead_id, 'LCF.lcf_active' => 1, 'LCF.lcf_deleted' => 0]);

        $followup_array = $this->db->order_by('LCF.lcf_id', 'DESC')->get();

        $data = '<div class="table-responsive">
		    <table class="table table-hover table-striped table-bordered">
                  	<thead>
                        <tr class="table-primary">
                            <th class="whitespace">Followup&nbsp;ID</th>
                            <th class="whitespace">Followup&nbsp;By</th>
                            <th class="whitespace">Followup&nbsp;Initiated&nbsp;On</th>
                            <th class="whitespace">Followup&nbsp;Type</th>
                            <th class="whitespace">Followup&nbsp;Status</th>
                            <th class="whitespace">Followup&nbsp;Remarks</th>
                            <th class="whitespace">Followup&nbsp;Schedule&nbsp;On</th>
                            <th class="whitespace">CFE&nbsp;Selfie&nbsp;With&nbsp;Customer</th>
                            <th class="whitespace">Selfie&nbsp;at&nbsp;the&nbsp;Location</th>
                            <th class="whitespace">Total&nbsp;Distance&nbsp;Covered</th>
                        </tr>
                  	</thead>';
        if (!empty($followup_array->num_rows())) {
            foreach ($followup_array->result() as $colum) {

                $data .= '<tbody>
                        <tr>
                            <td class="whitespace">' . (($colum->lcf_id) ? strtoupper(intval($colum->lcf_id)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->followup_username) ? strtoupper(strval($colum->followup_username)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->lcf_created_on) ? date("d-m-Y H:i:s", strtotime($colum->lcf_created_on)) : '-') . '</td>                           
                            <td class="whitespace">' . (($colum->m_followup_type_icons) ? '<i class="' . $colum->m_followup_type_icons . '" aria-hidden="true" title="' . strval($colum->m_followup_type_heading) . '"></i>' : "-") . '</td>
                            ';

                if (in_array($colum->m_followup_type_id, [2])) {
                    $status_heading = '<td class="whitespace">Sent SMS</td>';
                } else if (in_array($colum->m_followup_type_id, [4])) {
                    $status_heading = '<td class="whitespace">Sent Email</td>';
                } else if (in_array($colum->m_followup_type_id, [1])) {
                    $status_heading = '<td class="whitespace">' . (($colum->m_followup_status_heading) ? strtoupper(strval($colum->m_followup_status_heading)) : "-") . '</td>';
                } else {
                    $status_heading = '<td class="whitespace">Field Visit</td>';
                }

                $data .= $status_heading;
                $data .= '
                            <td class="whitespace">
                                <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                    <i>' . strtoupper(htmlspecialchars($colum->lcf_remarks)) . '</i>
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace">' . (($colum->lcf_next_schedule_datetime) ? date("d-m-Y H:i:s", strtotime($colum->lcf_next_schedule_datetime)) : '-') . '</td>
                            ';
                $empty_td = '<td class="whitespace">-</td>';
                if (!empty($colum->lcf_fe_upload_selfie)) {
//                    $image_upload_selfie = base_url("upload/") . $colum->lcf_fe_upload_selfie;
//                    $data .= '<td class="whitespace"><button name="' . $image_upload_selfie . '" onclick="viewDocs(this.name)"><i class="fa fa-file-image-o"></i></button></td>';
                    $data .= '<td class="whitespace"><a class="btn btn-control" target="_blank" href="' . base_url('view-document-file/' . intval($colum->lcf_id) . '/5') . '" title="' . $colum->lcf_id . '"><i class="fa fa-file-image-o"></i></a></td>';
                } else {
                    $data .= $empty_td;
                }
                if (!empty($colum->lcf_fe_upload_location)) {
//                    $image_upload_location = base_url("upload/") . $colum->lcf_fe_upload_location;
//                    $data .= '<td class="whitespace"><button name="' . $image_upload_location . '" onclick="viewDocs(this.name)"><i class="fa fa-file-image-o"></i></button></td>';
                    $data .= '<td class="whitespace"><a class="btn btn-control" target="_blank" href="' . base_url('view-document-file/' . intval($colum->lcf_id) . '/6') . '" title="' . htmlspecialchars($colum->lcf_id) . '"><i class="fa fa-file-image-o"></i></a></td>';
                } else {
                    $data .= $empty_td;
                }
                $data .= '<td class="whitespace">' . (($colum->total_distance_covered) ? strval($colum->total_distance_covered) . "&nbsp;Km" : "-") . '</td>
                        </tr>';
            }
        } else {
            $data .= '<tbody><tr><td colspan="16" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }

        $result['data'] = $data;

        return $result;
    }

    public function lists_master_followup_type($conditions = null) {

        $result = array();

        $select = 'm_followup_type_id as type_id, m_followup_type_heading as type_heading, m_followup_type_icons as type_icons';

        $this->db->select($select);
        $this->db->from('master_followup_type');

        $conditions['m_followup_type_active'] = 1;
        $conditions['m_followup_type_deleted'] = 0;

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $followup_array = $this->db->order_by('m_followup_type_id', 'asc')->get();

        if (!empty($followup_array->num_rows())) {
            $result = $followup_array;
        }

        return $result;
    }

    public function lists_master_followup_status($conditions = null) {

        $result = array();

        $select = 'm_followup_status_id as status_id, m_followup_status_heading as status_heading ';

        $this->db->select($select);
        $this->db->from('master_followup_status');

        $conditions['m_followup_status_active'] = 1;
        $conditions['m_followup_status_deleted'] = 0;

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $followup_array = $this->db->order_by('m_followup_status_id', 'asc')->get();

        if (!empty($followup_array->num_rows())) {
            $result = $followup_array;
        }

        return $result;
    }

    public function scm_user_lists($conditions = array()) {

        $result = array('err' => '', 'status' => 0, 'data' => array());
        $lead_customer = $this->db->select("LC.state_id as residence_state_id")->from('lead_customer LC')->where("LC.customer_lead_id", $conditions['LD.lead_id'])->get()->row_array();
        $customer_employment = $this->db->select("CE.state_id as office_state_id")->from('customer_employment CE')->where("CE.lead_id", $conditions['LD.lead_id'])->get()->row_array();
//            echo "<pre>"; print_r($customer_employment); exit;

        $select = 'LD.lead_id, ';
        $select .= ' URL.user_rl_location_id, SCM_USER.user_id as scm_user_id, SCM_USER.name as scm_user_name';

        $this->db->select($select);
        $this->db->from('leads LD');
        $this->db->join('user_roles UR', 'UR.user_role_type_id = 8 AND UR.user_role_active = 1 AND UR.user_role_deleted = 0', 'left');
        $this->db->join('user_role_locations URL', 'URL.user_rl_role_id = UR.user_role_id AND URL.user_rl_active = 1 AND URL.user_rl_deleted = 0');

        if ($conditions['visit_type_id'] == 1) {
            $conditions['URL.user_rl_location_id'] = $lead_customer['residence_state_id'];
            $this->db->join('lead_customer LC', 'LC.state_id = URL.user_rl_location_id AND LC.customer_active = 1 AND LC.customer_deleted = 0');
        }

        if ($conditions['visit_type_id'] == 2) {
            $conditions['URL.user_rl_location_id'] = $customer_employment['office_state_id'];
            $this->db->join('customer_employment CE', 'CE.state_id = URL.user_rl_location_id AND CE.emp_active = 1 AND CE.emp_deleted = 0');
        }

        $this->db->join('users SCM_USER', 'SCM_USER.user_id = UR.user_role_user_id AND SCM_USER.user_active=1 AND SCM_USER.user_deleted=0 AND SCM_USER.user_status_id=1');

        if (!empty($conditions)) {
            unset($conditions['visit_type_id']);
            $this->db->where($conditions);
        }

        $this->db->distinct();
        $user_array = $this->db->get();

        if (!empty($user_array->num_rows())) {
            $result['data'] = $user_array->result_array();
            $result['status'] = 1;
        } else {
            $result['err'] = "SCM not mapped.";
        }

//            echo $this->db->last_query(); exit;

        return $result;
    }

    public function cfe_user_lists($conditions = array()) {

        $result_array = array('status' => 0);

        $conditions_get_sms_role['UR.user_role_user_id'] = $_SESSION['isUserSession']['user_id'];
        $conditions_get_sms_role['UR.user_role_type_id'] = 8;

        $query = $this->db->select("UR.user_role_id")->from("user_roles UR")->where($conditions_get_sms_role)->get();

        if (!empty($query->num_rows())) {
            $scm_roles = $query->row_array();
            $scm_role_id = $scm_roles['user_role_id'];

            if (in_array(agent, ['CO2'])) {
                $conditions['UR.user_role_supervisor_role_id'] = $scm_role_id;
            }
            $conditions['UR.user_role_type_id'] = 13;
            $conditions['UR.user_role_active'] = 1;
            $conditions['UR.user_role_deleted'] = 0;
            $conditions['U.user_status_id'] = 1;

            $select = "UR.user_role_user_id as cfe_user_id, UR.user_role_id, U.name as cfe_user_name ";

            $this->db->select($select);
            $this->db->from('user_roles UR');
            $this->db->join('users U', "U.user_id = UR.user_role_user_id");
            $this->db->where($conditions);

            if (ENVIRONMENT == 'production') {
                $this->db->where_not_in('UR.user_role_user_id', [185, 142, 222, 66]);
            }

            $temp_data = $this->db->get();

            if (!empty($temp_data->num_rows())) {
                $result_array['data'] = $temp_data->result_array();
                $result_array['status'] = 1;
            } else {
                $result_array['err'] = "CFE not mapped.";
            }
        }

        return $result_array;
    }

    public function get_list_collection_visit($lead_id) {
        $result = array('error' => '', 'success' => '', 'data' => array());

        $select = '  LCV.col_visit_id, LCV.col_lead_id, ';
        $select .= ' LCV.col_visit_address_type, ';
        $select .= ' LCV.col_fe_rtoh_return_datetime, LCV.col_fe_rtoh_return_type, LCV.col_fe_rtoh_total_distance_covered, LCV.col_fe_visit_trip_status_id, ';
        $select .= ' LCV.col_fe_rtoh_remarks, LCV.col_fe_rtoh_upload_selfie, LCV.col_fe_visit_approval_status, visit_approved_user.name as visit_approved_user_name, LCV.col_fe_visit_approval_datetime, ';
        $select .= ' LCV.col_visit_requested_datetime, co_xecutive.name as co_excutive_name, LCV.col_visit_requested_by_remarks, ';
        $select .= ' LCV.col_visit_allocate_on, LCV.col_visit_scm_id, scm_user.name as scm_user_name, LCV.col_visit_scm_remarks, ';
        $select .= ' LCV.col_visit_field_status_id, LCV.col_visit_field_schedule_datetime, LCV.col_visit_field_remarks, LCV.col_visit_field_datetime, ';
        $select .= ' LCV.col_fe_visit_trip_start_datetime, LCV.col_fe_visit_trip_start_latitude, LCV.col_fe_visit_trip_start_longitude,';
        $select .= ' LCV.col_fe_visit_end_datetime, LCV.col_fe_visit_end_latitude, LCV.col_fe_visit_end_longitude,';
        $select .= ' LCV.col_fe_visit_total_distance_covered,';

        $select .= ' rm_user.name as rm_user_name ';

        $this->db->select($select);

        $this->db->from('loan_collection_visit  LCV');
        $this->db->join('users co_xecutive', 'co_xecutive.user_id = LCV.col_visit_requested_by', 'left');
        $this->db->join('users scm_user', 'scm_user.user_id = LCV.col_visit_scm_id', 'left');
        $this->db->join('users rm_user', 'rm_user.user_id = LCV.col_visit_allocated_to', 'left');
        $this->db->join('users visit_approved_user', 'visit_approved_user.user_id = LCV.col_fe_visit_approval_user_id', 'left');

        $this->db->where(['LCV.col_lead_id' => $lead_id, "LCV.col_visit_active" => 1, "LCV.col_visit_deleted" => 0]);

        $visit_array = $this->db->order_by('LCV.col_visit_id', 'DESC')->get();

        $data = '<div class="table-responsive">
		    <table class="table table-hover table-striped table-bordered">
                  	<thead>
                        <tr class="table-primary">
                            <th class="whitespace">Visit&nbsp;ID</th>
                            <th class="whitespace">Visit&nbsp;Type</th>
                            <th class="whitespace">Visit&nbsp;Requested&nbsp;By</th>
                            <th class="whitespace">Visit&nbsp;Requested&nbsp;On</th>
                            <th class="whitespace">Visit&nbsp;Requested&nbsp;Remarks</th>
                            <th class="whitespace">SCM&nbsp;Name</th>
                            <th class="whitespace">SCM&nbsp;Remarks</th>
                            <th class="whitespace">Visit&nbsp;Status</th>
                            <th class="whitespace">CFE&nbsp;Allocated&nbsp;On</th>
                            <th class="whitespace">CFE&nbsp;Name</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;Current&nbsp;Status</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;Next&nbsp;Schedule&nbsp;On</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;On</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;Started&nbsp;At</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;Ended&nbsp;At</th>
                            <th class="whitespace">CFE&nbsp;Visit&nbsp;Coverd&nbsp;KM.</th>
                            <th class="whitespace">CFE&nbsp;Remarks</th>
                            <th class="whitespace">CFE&nbsp;Return&nbsp;From&nbsp;Visit&nbsp;On</th>
                            <th class="whitespace">CFE&nbsp;Return&nbsp;From&nbsp;Visit&nbsp;Type</th>
                            <th class="whitespace">CFE&nbsp;Return&nbsp;Coverd&nbsp;KM.</th>
                            <th class="whitespace">CFE&nbsp;Return&nbsp;Visit&nbsp;Remarks</th>
                            <th class="whitespace">CFE&nbsp;Return&nbsp;Visit&nbsp;Selfie</th>
                            <th class="whitespace">Visit&nbsp;Conveyance&nbsp;Status</th>
                            <th class="whitespace">Visit&nbsp;Conveyance&nbsp;Action&nbsp;By</th>
                            <th class="whitespace">Visit&nbsp;Conveyance&nbsp;Action&nbsp;On</th>
                            <th class="whitespace">Action</th>
                        </tr>
                  	</thead>';
        if (!empty($visit_array->num_rows())) {
            foreach ($visit_array->result() as $colum) {
                $editBtnActive = "";
                $editBtn = "NA";
                $col_visit_field_status = "-";
                $is_visit_completed = "";
                $col_fe_visit_approval_status = "-";
                $return_from_visit_address_type = "-";
                if ($colum->col_visit_field_status_id == 1) {
                    $col_visit_field_status = "PENDING";
                } else if ($colum->col_visit_field_status_id == 2) {
                    $col_visit_field_status = "ASSIGNED";
                } else if ($colum->col_visit_field_status_id == 3) {
                    $col_visit_field_status = "Cancel";
                } else if ($colum->col_visit_field_status_id == 4) {
                    $col_visit_field_status = "HOLD";
                } else if ($colum->col_visit_field_status_id == 5) {
                    $col_visit_field_status = "COMPLETED";

                    if (empty($colum->col_fe_visit_approval_status)) {
                        if ((agent == 'CO2' && $colum->col_visit_scm_id == user_id) || agent == 'CO3') {
                            $editBtn = "";
                            $is_visit_completed = '<input type="radio" name="is_visit_completed" value="1">&nbsp;APPROVE&nbsp;';
                            $is_visit_completed .= '<input type="radio" name="is_visit_completed" value="2">&nbsp;REJECT&nbsp;';
                            $is_visit_completed .= '<button class="btn btn-primary" onclick="is_visit_completed(' . $colum->col_visit_id . ')">Submit</button>';
                        }
                    }
                }

                if (empty($colum->col_fe_visit_trip_status_id)) {
                    $col_fe_visit_trip_status = "-";
                } else if ($colum->col_fe_visit_trip_status_id == 1) {
                    $col_fe_visit_trip_status = "CFE Visit Started";
                } else if ($colum->col_fe_visit_trip_status_id == 2) {
                    $col_fe_visit_trip_status = "CFE Visit self cancel";
                } else if ($colum->col_fe_visit_trip_status_id == 3) {
                    $col_fe_visit_trip_status = "CFE reached on visit location.";
                } else if ($colum->col_fe_visit_trip_status_id == 4) {
                    $col_fe_visit_trip_status = "CFE Visit completed.";
                }

                if ($colum->col_visit_field_status_id == 5 && empty($colum->col_fe_visit_approval_status)) {
                    $col_fe_visit_approval_status = "PENDING";
                } else if ($colum->col_fe_visit_approval_status == 1) {
                    $col_fe_visit_approval_status = "APPROVED";
                } else if ($colum->col_fe_visit_approval_status == 2) {
                    $col_fe_visit_approval_status = "REJECT";
                }

                if ($colum->col_visit_address_type == 1) {
                    $col_visit_address_type = "RESIDENCE";
                } elseif ($colum->col_visit_address_type == 2) {
                    $col_visit_address_type = "OFFICE";
                }

                if ($colum->col_fe_rtoh_return_type == 1) {
                    $return_from_visit_address_type = "HOME";
                } elseif ($colum->col_fe_rtoh_return_type == 2) {
                    $return_from_visit_address_type = "OFFICE";
                }

                if (in_array(agent, ["CO1"]) && in_array($colum->col_visit_field_status_id, [1, 2, 3, 4, 5])) {
                    $editBtnActive = " disabled='disabled'";
                } elseif (in_array(agent, ["CO2"]) && in_array($colum->col_visit_field_status_id, [2])) { // completed    
                    $editBtnActive = "";
                } elseif (in_array(agent, ["CO2"]) && in_array($colum->col_visit_field_status_id, [3, 5])) { // completed    
                    $editBtnActive = " disabled='disabled' ";
                } elseif (in_array(agent, ["CFE1"]) && in_array($colum->col_visit_field_status_id, [1, 3, 4, 5])) { // 2 => assign
                    $editBtnActive = " disabled='disabled' ";
                }

                $input = [
                    'col_visit_id' => $this->encrypt->encode($colum->col_visit_id),
                    'col_visit_type' => htmlspecialchars($colum->col_visit_address_type),
                    'col_visit_address_type' => htmlspecialchars($colum->col_visit_address_type)
                ];
                $input = json_encode($input);
                if (in_array(agent, ["CO2"]) && (in_array(user_id, [$colum->col_visit_scm_id]) && (in_array($colum->col_visit_field_status_id, [2, 1])))) { // , "CFE1"
                    $editBtn = "<div id='btnEditAssignCollection'><a class='btn btn-control btn-primary' " . $editBtnActive . " onclick='editsCollectionVisit(" . $input . ")'><i class='fa fa-pencil'></i></a></div>";
                }

                $data .= '<tbody>
                        <tr>
                            <td class="whitespace">' . (($colum->col_visit_id) ? strtoupper(htmlspecialchars($colum->col_visit_id)) : '-') . '</td>
                            <td class="whitespace">' . (($col_visit_address_type) ? strtoupper(htmlspecialchars($col_visit_address_type)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->co_excutive_name) ? strtoupper(htmlspecialchars($colum->co_excutive_name)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->col_visit_requested_datetime) ? date("d-m-Y H:i:s", strtotime(htmlspecialchars($colum->col_visit_requested_datetime))) : '-') . '</td>
                            <td class="whitespace">
                                <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                    <i>' . strtoupper($colum->col_visit_requested_by_remarks) . '</i>
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace">' . (($colum->scm_user_name) ? strtoupper(htmlspecialchars($colum->scm_user_name)) : '-') . '</th>';

                if (!empty($colum->col_visit_scm_remarks)) {
                    $data .= '<td class="whitespace">
                                        <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                            <i>' . strtoupper(htmlspecialchars($colum->col_visit_scm_remarks)) . '</i>
                                            </span>
                                        </div>
                                    </td>';
                } else {
                    $data .= ' <td class="whitespace">-</td>';
                }

                $data .= '<td class="whitespace">' . (($col_visit_field_status) ? strtoupper($col_visit_field_status) : '-') . '</td>
                            <td class="whitespace">' . (($colum->col_visit_allocate_on) ? date("d-m-Y H:i:s", strtotime($colum->col_visit_allocate_on)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->rm_user_name) ? strtoupper(htmlspecialchars($colum->rm_user_name)) : '-') . '</td>
                            <td class="whitespace">' . (($col_fe_visit_trip_status) ? strtoupper($col_fe_visit_trip_status) : '-') . '</td>
                            <td class="whitespace">' . (($colum->col_visit_field_schedule_datetime) ? date("d-m-Y H:i:s", strtotime($colum->col_visit_field_schedule_datetime)) : '-') . '</td>
                            <td class="whitespace">' . (($colum->col_visit_field_datetime) ? date("d-m-Y H:i:s", strtotime($colum->col_visit_field_datetime)) : '-') . '</td>';
                $data .= '<td class="whitespace">' . (($colum->col_fe_visit_trip_start_datetime) ? date("d-m-Y H:i:s", strtotime($colum->col_fe_visit_trip_start_datetime)) . ' | <a href="https://www.latlong.net/c/?lat=' . $colum->col_fe_visit_trip_start_latitude . '&long=' . $colum->col_fe_visit_trip_start_longitude . '" target="_blank">Start Location</a>' : '-') . '</td>';
                $data .= '<td class="whitespace">' . (($colum->col_fe_visit_end_datetime) ? date("d-m-Y H:i:s", strtotime($colum->col_fe_visit_end_datetime)) . ' | <a href="https://www.latlong.net/c/?lat=' . $colum->col_fe_visit_end_latitude . '&long=' . $colum->col_fe_visit_end_longitude . '" target="_blank">End Location</a>' : '-') . '</td>';
                $data .= '<td class="whitespace">' . (($colum->col_fe_visit_total_distance_covered) ? htmlspecialchars($colum->col_fe_visit_total_distance_covered) : '-') . '</td>';

                if (!empty($colum->col_visit_field_remarks)) {
                    $data .= '<td class="whitespace">
                                    <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                        <i>' . (($colum->col_visit_field_remarks) ? strtoupper($colum->col_visit_field_remarks) : "-") . '</i>
                                        </span>
                                    </div>
                                </td>';
                } else {
                    $data .= ' <td class="whitespace">-</td>';
                }

                $data .= '
                            <td class="whitespace">' . (($colum->col_fe_rtoh_return_datetime) ? date("d-m-Y H:i:s", strtotime($colum->col_fe_rtoh_return_datetime)) : '-') . '</td>
                            <td class="whitespace">' . (($return_from_visit_address_type) ? strtoupper($return_from_visit_address_type) : '-') . '</td>
                            <td class="whitespace">' . (($colum->col_fe_rtoh_total_distance_covered) ? htmlspecialchars($colum->col_fe_rtoh_total_distance_covered) . "&nbsp;Km" : '-') . '</td>
                            ';
                if (!empty($colum->col_fe_rtoh_remarks)) {
                    $data .= '<td class="whitespace">
                                    <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                        <i>' . (($colum->col_fe_rtoh_remarks) ? strtoupper(htmlspecialchars($colum->col_fe_rtoh_remarks)) : "-") . '</i>
                                        </span>
                                    </div>
                                </td>';
                } else {
                    $data .= ' <td class="whitespace">-</td>';
                }

                $empty_td = '<td class="whitespace">-</td>';
                if (!empty($colum->col_fe_rtoh_upload_selfie)) {
//                    $image_upload_selfie = base_url("upload/") . $colum->col_fe_rtoh_upload_selfie;
//                    $data .= '<td class="whitespace"><button name="' . $image_upload_selfie . '" onclick="viewDocs(this.name)"><i class="fa fa-file-image-o"></i></button></td>';
                    $data .= '<td class="whitespace"><a class="btn btn-control" target="_blank" href="' . base_url('view-document-file/' . $this->encrypt->encode($colum->col_visit_id) . '/7') . '" title="' . $colum->col_visit_id . '"><i class="fa fa-file-image-o"></i></a></button></td>';
                } else {
                    $data .= $empty_td;
                }
                $data .= '<td class="whitespace">' . $col_fe_visit_approval_status . '</td>
                            <td class="whitespace">' . ($colum->visit_approved_user_name ? htmlspecialchars($colum->visit_approved_user_name) : "-") . '</td>
                            <td class="whitespace">' . ($colum->col_fe_visit_approval_datetime ? date("d-m-Y H:i:s", strtotime($colum->col_fe_visit_approval_datetime)) : "-") . '</td>
                            ';
                $data .= '<td class="whitespace">' . $editBtn . $is_visit_completed . '</td>
                        </tr>';
            }
        } else {
            $data .= '<tbody><tr><td colspan="16" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }

        $result['data'] = $data;

        return $result;
    }

    public function get_sms_template_lists() {

        $result_array = array("status" => 0);

        $select = 'm_st_id, m_st_type_id, m_st_template_id, m_st_template_source, m_st_description, m_st_content';

        $this->db->select($select);
        $this->db->from('master_sms_template');

        $conditions['m_st_type_id'] = 1;
        $conditions['m_st_active'] = 1;
        $conditions['m_st_deleted'] = 0;

        $this->db->where($conditions);

        $data_array = $this->db->order_by('m_st_id', 'asc')->get();

        if (!empty($data_array->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data']['list_sms_template'] = $data_array->result_array();
        }

        return $result_array;
    }

    public function get_template_lists($followup_type_id) {
        $result_array = array("status" => 0);

        if ($followup_type_id == 2) { // master_sms_template
            $sms_template_list = $this->get_sms_template_lists();
            if (!empty($sms_template_list['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['list_sms_template'] = $sms_template_list['data']['list_sms_template'];
            }
        } else if ($followup_type_id == 3) { // master_whatsapp_template
            $sms_template_list = $this->get_whatsapp_template_lists($followup_type_id);
            if (!empty($sms_template_list['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['list_whatsapp_template'] = $sms_template_list['data']['list_whatsapp_template'];
            }
        } else if ($followup_type_id == 4) { // master_email_template
            $sms_template_list = $this->get_email_template_lists();
//                echo '<pre>'; print_r($sms_template_list); exit;

            if (!empty($sms_template_list['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['list_email_template'] = $sms_template_list['data']['list_email_template'];
            }
        }
        return $result_array;
    }

    public function get_email_template_lists($email_template_id = 0) {
        $result_array = array("status" => 0);

        $select = 'm_et_id, m_et_type_id, m_et_title as email_subject, m_et_description, m_et_content as email_body';

        $this->db->select($select);
        $this->db->from('master_email_template');

        $conditions['m_et_type_id'] = 1;
        $conditions['m_et_active'] = 1;
        $conditions['m_et_deleted'] = 0;

        if (!empty($email_template_id)) {
            $conditions['m_et_id'] = $email_template_id;
        }

        $this->db->where($conditions);

        $data_array = $this->db->order_by('m_et_id', 'asc')->get();

        if (!empty($data_array->num_rows())) {
            $result_array['status'] = 1;
            if (!empty($email_template_id)) {
                $row = $data_array->row_array();

                $data['email_template_id'] = ($row['m_et_id']) ? $row['m_et_id'] : "0";
                $data['email_subject'] = ($row['email_subject']) ? $row['email_subject'] : "";
                $data['email_body'] = ($row['email_body']) ? $row['email_body'] : "NA";

                $result_array['data']['list_email_template'] = $data;
            } else {
                $result_array['data']['list_email_template'] = $data_array->result_array();
            }
        }

        return $result_array;
    }

    /**
     * Make a method get_template_content
     * <p>Returns <code>array</code> with template content details.</p>
     * @param integer $followup_type_id <p>m_followup_type_id from master_followup_type.</p>
     * @param integer $followup_template_id <p>m_st_id from master_sms_template.</p>
     * @param integer $lead_id <p>lead_id from leads.</p>
     * @return Array <p>Returns the template content.</p>
     * @author Er. Vinay Kumar
     */
    public function get_template_content($followup_type_id, $followup_template_id, $lead_id = 0) {
        $result_array = array("status" => 0);
        if ($followup_type_id == 2) { // SEND SMS
            $sms_content = $this->get_sms_template_content($followup_template_id, $lead_id);

            if (!empty($sms_content['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['sms_content'] = $sms_content['data']['sms_content'];
            }
        } else if ($followup_type_id == 3) { // SEND WHATSAPP
            $sms_content = $this->get_sms_template_content($followup_template_id, $lead_id);
            if (!empty($sms_content['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['whatsapp_content'] = $sms_content['data']['sms_content'];
            }
        } else if ($followup_type_id == 4) { // SEND EMAIL
            $email_content = $this->get_email_template_content($followup_template_id, $lead_id);
//            echo "email_content : "; print_r($email_content); exit;
            if (!empty($email_content['status'])) {
                $result_array['status'] = 1;
                $result_array['data']['email_content'] = $email_content['data']['email_content'];
            }
        }

        return $result_array;
    }

    public function get_sms_template_content($sms_template_id, $lead_id = 0) {

        $result_array = array("status" => 0);

        $select = 'm_st_id, m_st_type_id, m_st_template_id, m_st_template_source, m_st_description, m_st_content';

        $this->db->select($select);
        $this->db->from('master_sms_template');

        $conditions['m_st_type_id'] = 1;
        $conditions['m_st_active'] = 1;
        $conditions['m_st_deleted'] = 0;

        if (!empty($sms_template_id)) {
            $conditions['m_st_id'] = $sms_template_id;
        }

        $this->db->where($conditions);

        $data_array = $this->db->get();

        if (!empty($data_array->num_rows())) {
            $result_array['status'] = 1;
            $raw_content = $data_array->row_array();

            $sms_template = $raw_content['m_st_content'];

            $query = $this->db->select("LC.customer_lead_id, LC.gender, LC.first_name, LC.middle_name, LC.sur_name, LC.mobile, CAM.roi, CAM.disbursal_date, CAM.repayment_date, CAM.loan_recommended, L.loan_no")
                            ->from('loan L')
                            ->join('lead_customer LC', 'LC.customer_lead_id=L.lead_id', 'INNER')
                            ->join('credit_analysis_memo CAM', 'CAM.lead_id=L.lead_id', 'INNER')
                            ->where("L.lead_id = '$lead_id' AND L.loan_active = 1 AND CAM.cam_active = 1")
                            ->get()->row();

            $name = $query->first_name;

            if (!empty($query->middle_name)) {
                $name .= " " . $query->middle_name;
            }

            if (!empty($query->sur_name)) {
                $name .= " " . $query->sur_name;
            }

            $name = ucwords($name);

            $loan_no = $query->loan_no;

            if ($sms_template_id == 1) {
                $sms_template = str_replace("{#CUSTOMER_NAME#}", $name, $sms_template);
                $sms_template = str_replace("{#LOAN_NO#}", $loan_no, $sms_template);
            }

            $result_array['data']['sms_content'] = $sms_template;
        }
        return $result_array;
    }

    public function get_email_template_content($followup_template_id, $lead_id) {
        $result_array = array("status" => 0);
        $email_template_id = $followup_template_id;

        $email_data = $this->get_email_template_lists($email_template_id);
//            echo "email_data : <pre>"; print_r($email_data); exit;

        if (!empty($email_data['status'])) {

            $email_template = $email_data['data']['list_email_template'];

            $loan_no = "NFPL01";
            $customer_name = "Manish";

            if ($email_template_id == 1) {
                $repay_amount = "1000";
                $repay_date = "18-04-2022 10:10:10";
                $payment_link = WEBSITE;

                $email_body = $email_template["email_body"];

                $email_body = str_replace("{#LOAN_NO#}", $loan_no, $email_body);
                $email_body = str_replace("{#CUSTOMER_NAME#}", $customer_name, $email_body);
                $email_body = str_replace("{#REPAY_AMOUNT#}", $repay_amount, $email_body);
                $email_body = str_replace("{#REPAY_DATE#}", $repay_date, $email_body);
                $email_body = str_replace("{#PAYMENT_LINK#}", $payment_link, $email_body);
            }

            $data['email_subject'] = $email_template["email_subject"];
            $data['email_body'] = $email_body;

            $result_array['status'] = 1;
            $result_array['data']['email_content'] = $data;
        }
        return $result_array;
    }

    public function get_lead_details($lead_id) {
        $result_array = array("status" => 0);
        $conditions['LD.lead_id'] = $lead_id;

        $select = "LC.first_name, LC.middle_name, LC.sur_name, LC.gender, LC.email, LC.alternate_email, ";
        $select .= " LC.mobile, LC.alternate_mobile, ";

        $this->db->select($select);
        $this->db->from("leads LD");
        $this->db->join("lead_customer LC", "LC.customer_lead_id = LD.lead_id");

        $this->db->where($conditions);
        $lead_details = $this->db->get();

        if (!empty($lead_details->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data'] = $lead_details->row_array();
        }
        return $result_array;
    }

    public function send_collection_followup_email($email_data = array()) {

        $lead_id = $email_data['lead_id'];
        $lead_details = $this->get_lead_details($lead_id);

        if (!empty($lead_details['status'])) {
            $leads = $lead_details['data'];
            $full_name = $leads['first_name'] . ' ' . $leads['middle_name'] . ' ' . $leads['sur_name'];
            $first_name = $leads['first_name'];
            $gender = $leads['gender'];
            $email = $leads['email'];
            $alternate_email = $leads['alternate_email'];

            $followup_type_id = $email_data['followup_type_id'];
            $email_template_id = $email_data['email_template_id'];

            $email_subject = $email_data['email_subject'];
            $email_cc_user = $email_data['email_cc_user']; // 1=> Yes, 2=> No
            $email_body = $email_data['email_body'];

            $email_cc = "";
            $message = "Template Id : " . $email_template_id . " <br>";
            $message .= "Email Subject : " . $email_subject . " <br>";

            if ($email_data['email_cc_user'] == 1) { // if cc_user is 1 then required to add cc email
                $email_cc = TECH_EMAIL;
            }

            if (!empty($email)) {
                $email_send = lw_send_email($email, $email_subject, $email_body, $bcc_email = "", $email_cc, $from_email = "");
                $message .= " Email sent on Personal Email" . " <br>";

                $collection_followup_email_data_log['lel_lead_id'] = $lead_id;
                $collection_followup_email_data_log['lel_email_type_id'] = $email_template_id;
                $collection_followup_email_data_log['lel_email_address'] = $email;
                $collection_followup_email_data_log['lel_email_content'] = $email_body;
                $collection_followup_email_data_log['lel_api_status_id'] = (($email_send['status'] == 1) ? 1 : 2);
                $collection_followup_email_data_log['lel_created_on'] = date('Y-m-d H:i:s');
                $collection_followup_email_data_log['lel_active'] = 1;
                $collection_followup_email_data_log['lel_deleted'] = 0;

                $this->db->insert('lead_email_logs', $collection_followup_email_data_log);
            }

            if (!empty($alternate_email)) {
                $email_send = lw_send_email($alternate_email, $email_subject, $email_body, $bcc_email = "", $email_cc, $from_email = "");
                $message .= " Email sent on Office Email";

                $collection_followup_email_data_log['lel_lead_id'] = $lead_id;
                $collection_followup_email_data_log['lel_email_type_id'] = $email_template_id;
                $collection_followup_email_data_log['lel_email_address'] = $alternate_email;
                $collection_followup_email_data_log['lel_email_content'] = $email_body;
                $collection_followup_email_data_log['lel_api_status_id'] = (($email_send['status'] == 1) ? 1 : 2);
                $collection_followup_email_data_log['lel_created_on'] = date('Y-m-d H:i:s');
                $collection_followup_email_data_log['lel_active'] = 1;
                $collection_followup_email_data_log['lel_deleted'] = 0;

                $this->db->insert('lead_email_logs', $collection_followup_email_data_log);
            }

            if (!empty($email_send['status'])) {

                $collection_followup_email_data['lcf_lead_id'] = $lead_id;
                $collection_followup_email_data['lcf_type_id'] = $followup_type_id;
                $collection_followup_email_data['lcf_remarks'] = $message;
                $collection_followup_email_data['lcf_user_id'] = $_SESSION['isUserSession']['user_id'];
                $collection_followup_email_data['lcf_created_on'] = date('Y-m-d H:i:s');
                $collection_followup_email_data['lcf_active'] = 1;
                $collection_followup_email_data['lcf_deleted'] = 0;

                $this->db->insert('loan_collection_followup', $collection_followup_email_data);

                $result_array['status'] = 1;
                $result_array['msg'] = $message;
            } else {
                $result_array['error'] = "Failed send successfully";
            }
        }

        return $result_array;
    }

    public function send_collection_followup_sms($sms_data = array()) {

        $lead_id = $sms_data['lead_id'];
        $lead_details = $this->get_lead_details($lead_id);

        $sms_primary_id = $sms_data['sms_primary_id'];

        if (!empty($lead_details['status'])) {

            $leads = $lead_details['data'];
            $full_name = $leads['first_name'] . ' ' . $leads['middle_name'] . ' ' . $leads['sur_name'];
            $first_name = $leads['first_name'];
            $gender = $leads['gender'];
            $mobile = $leads['mobile'];
            $alternate_mobile = $leads['alternate_mobile'];

            $followup_type_id = $sms_data['followup_type_id']; // 2=>sms
            $sms_template_content = $sms_data['sms_template_content'];

            $message = "Template Id : " . $sms_primary_id . " <br>";

            if (!empty($mobile)) {
                $sms_send = lw_send_sms($lead_id, $mobile, $sms_template_content, $sms_primary_id);
                if (!empty($sms_send['status'])) {
                    $message .= " SMS sent on personal mobile.<br>";
                } else {
                    $message .= " Failed to send sms to personal mobile.<br>";
                }
            }

            if (!empty($alternate_mobile)) {
                $sms_send = lw_send_sms($lead_id, $alternate_mobile, $sms_template_content, $sms_primary_id);
                if (!empty($sms_send['status'])) {
                    $message .= " SMS sent on alternate Mobile.<br>";
                } else {
                    $message .= " Failed to send sms to alternate mobile.<br>";
                }
            }

            if (!empty($sms_send['status'])) {

                $collection_followup_sms_data['lcf_lead_id'] = $lead_id;
                $collection_followup_sms_data['lcf_type_id'] = $followup_type_id;
                $collection_followup_sms_data['lcf_remarks'] = $message;
                $collection_followup_sms_data['lcf_user_id'] = $_SESSION['isUserSession']['user_id'];
                $collection_followup_sms_data['lcf_created_on'] = date('Y-m-d H:i:s');
                $collection_followup_sms_data['lcf_active'] = 1;
                $collection_followup_sms_data['lcf_deleted'] = 0;

                $this->db->insert('loan_collection_followup', $collection_followup_sms_data);

                $result_array['status'] = 1;
                $result_array['msg'] = $message;
            } else {
                $result_array['error'] = "Failed send successfully.";
            }
        }

        return $result_array;
    }

    public function get_scm_rm_roles() {
        $user_role_id = $_SESSION['isUserSession']['user_role_id'];

        $sql = "SELECT UR.user_role_id, UR.user_role_user_id, UR.user_role_type_id, U.user_id, U.name FROM user_roles UR INNER JOIN users U on (U.user_id=UR.user_role_user_id) ";
        $sql .= " WHERE UR.user_role_type_id=13 AND UR.user_role_supervisor_role_id=$user_role_id";
        $res = $this->db->query($sql);
        if ($res->num_rows() > 0) {
            $rm = $res->result_array();
        }
        return $rm;
    }

    public function is_already_visit_running($lead_id, $visit_type_id) {

        $result_array = array('status' => 0);

        $conditions_visit['LCV.col_lead_id'] = $lead_id;
        $conditions_visit['LCV.col_visit_address_type'] = $visit_type_id;
        $conditions_visit['LCV.col_visit_scm_id !='] = "";
        $conditions_visit['LCV.col_visit_active'] = 1;
        $conditions_visit['LCV.col_visit_deleted'] = 0;

        $this->db->select("LCV.col_visit_id, CFE.name as visit_allocated_to");
        $this->db->from("loan_collection_visit  LCV");
        $this->db->join("users CFE", "CFE.user_id = LCV.col_visit_allocated_to", "LEFT");

        $this->db->where($conditions_visit);

        if (in_array(agent, ['CO1'])) {
            $this->db->where_in("LCV.col_visit_field_status_id", [1, 2, 4]);
        } else if (in_array(agent, ['CO2'])) {
            $this->db->where_in("LCV.col_visit_field_status_id", [2, 4]);
        }

        $followup_array = $this->db->get();

        if (!empty($followup_array->num_rows())) {
            $row = $followup_array->row_array();
            $result_array['status'] = 1;
            $result_array['data']['running_visit']['visit_allocated_to'] = $row['visit_allocated_to'];
        }

        return $result_array;
    }

    public function get_visit_location($lead_id, $visit_id) {
        $result_array = array("status" => 0);

        $select = "MS.status_name as status ";

        if ($visit_id == 1) {
            $select .= " , LC.state_id, MSR.m_state_name as state_name, MCR.m_city_name as city_name";
        }
        if ($visit_id == 2) {
            $select .= " , CE.state_id, MSO.m_state_name as state_name, MCO.m_city_name as city_name";
        }

        $this->db->select($select);
        $this->db->from('leads LD');

        $this->db->join('master_status MS', "MS.status_id = LD.lead_status_id");

        if ($visit_id == 1) {
            $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id");
            $this->db->join('master_state MSR', "MSR.m_state_id = LC.state_id");
            $this->db->join('master_city MCR', "MCR.m_city_id = LC.city_id");

            $this->db->where("LC.customer_lead_id", $lead_id);
        }

        if ($visit_id == 2) {
            $this->db->join('customer_employment CE', "CE.lead_id = LD.lead_id");
            $this->db->join('master_state MSO', "MSO.m_state_id = CE.state_id");
            $this->db->join('master_city MCO', "MCO.m_city_id = CE.city_id");

            $this->db->where("CE.lead_id", $lead_id);
        }

        $temp_data = $this->db->get();

        if (!empty($temp_data->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data'] = $temp_data->row_array();
        }

        return $result_array;
    }

    public function send_email_for_visit($conditions = array()) {
        $result_array = array("status" => 0);

        $email_sent_status = $this->send_email_ce_request_visit($conditions);

        if (!empty($email_sent_status['status'])) {
            $result_array['status'] = 1;
        } else {
            $result_array['error'] = $email_sent_status['error'];
        }

        return $result_array;
    }

    public function send_email_ce_request_visit($email_data) {
        $result_array = array('status' => 0);

        $lead_id = $email_data['lead_id'];
        $visit_type_id = $email_data['visit_type_id'];
        $loan_no = $email_data['loan_no'];
        $total_due_amount = $email_data['total_due_amount'];
        $today_date = date("d-m-Y H:i:s");

        $users = $this->get_user_email_details($email_data);

        if (!empty($users['status'])) {
            $message = '';
            $subject = '';
            $from_agent_email = $users['data']['from_agent_email'];
            $from_agent_name = $users['data']['from_agent_name'];
            $to_agent_email = $users['data']['to_agent_email'];
            $to_agent_name = $users['data']['to_agent_name'];

            $lead_details = $this->get_lead_details($lead_id);
            $visit_location_details = $this->get_visit_location($lead_id, $visit_type_id);

            $state_name = $visit_location_details['data']['state_name'];
            $city_name = $visit_location_details['data']['city_name'];
            $status = $visit_location_details['data']['status'];
            $customer_name = $lead_details['data']['first_name'] . " " . $lead_details['data']['middle_name'] . " " . $lead_details['data']['sur_name'];
            $visit_type = $this->visit_type[$visit_type_id];

            if (in_array(agent, ['CO1'])) {
                $subject = 'Collection Visit Request';
                $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <title>Email Request Visit</title>
                            </head>
                            <body>
                            <table width="491" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #0463a3; background:#fff; font-family:Arial, Helvetica, sans-serif;border-radius: 5px; padding-bottom:15px;">
                            <tr>
                            <td width="489" align="center" style="background:#0463a3; padding:15px;"><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . COLLECTION_BRAND_LOGO . '" alt="LW" width="218" height="52"/></a></td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_EXE_BANNER . '" alt="Collection-Executive-banner" width="491" height="128"/></td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="15" /></td>
                            </tr>
                            <tr>
                              <td style="padding:10px 15px; font-size:18px; color:#0463a3;"><strong>Dear ' . $to_agent_name . ',</strong></td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px; line-height:25px; font-size:17px;">You have received a request from <b>' . $from_agent_name . '</b> for RM visit against the following details on the <b>' . $visit_type . '</b> on <b>' . $today_date . '</b></td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px; line-height:25px;"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px;"><table width="100%" border="0" cellpadding="2" cellspacing="1" bgcolor="#dddddd" style="font-size:14px;">
                                
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan No.</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $loan_no . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan Status.</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $status . '</td>
                                </tr>
                                <tr>
                                  <td width="31%" align="left" bgcolor="#0463a3" style="padding:15px; color:#fff;"><strong>Customer Name</strong></td>
                                  <td width="69%" bgcolor="#FFFFFF" style="padding:15px;">' . $customer_name . '</td>
                                </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>State</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $state_name . '</td>
                                </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>City</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $city_name . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Due Amount</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="15" height="15" style="position: relative;
                                margin-bottom: -1px;">' . $total_due_amount . '</td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="border-top:solid 1px #ddd; padding-top:15px;"><a href="' . APPLE_STORE_LINK . '" target="_blank"><img src="' . APPLE_STORE_ICON . '" alt="aap-sore" width="108" height="30" /></a> <a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="30" height="30" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="instagram" width="30" height="30" /></a><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="30" height="30" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" alt="twitter" width="30" height="30" /></a><a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="30" height="30" /></a>&nbsp;<a href="' . ANDROID_STORE_LINK . '" target="_blank"><img src="' . ANDROID_STORE_ICON . '" alt="google-play" width="108" height="30" /></a></td>
                            </tr>
                            <tr>
                              <td align="center"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="5" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="font-size:14px; font-weight:bold;"> <a href="tel:' . REGISTED_MOBILE . '" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_PHONE_ICON . '" alt="phone" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/>' . REGISTED_MOBILE . '</a> <a href="' . WEBSITE_URL . '" target="_blank" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_WEB_ICON . '" alt="web" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . WEBSITE . '</a> <a href="mailto:' . INFO_EMAIL . '" style="text-decoration:blink; color:#0463a3;"> <img src="' . COLLECTION_EMAIL_ICON . '" alt="email" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . INFO_EMAIL . '</a> </td>
                            </tr>
                            </table>
                            </body>
                            </html>
                        ';
            } else if (in_array(agent, ['CO2'])) {
                $subject = 'Collection Visit Assign';
                $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <title>Customer Relationship Management</title>
                            </head>
                            <body>
                            <table width="491" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #0463a3; background:#fff; font-family:Arial, Helvetica, sans-serif;border-radius: 5px; padding-bottom:15px;">
                            <tr>
                            <td width="489" align="center" style="background:#0463a3; padding:15px;"><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . COLLECTION_BRAND_LOGO . '" alt="LW" width="218" height="52"/></a></td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_ROAD_BANNER . '" alt="Collection-Executive-banner" width="491" height="128"/></td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="15" /></td>
                            </tr>
                            <tr>
                              <td style="padding:10px 15px; font-size:18px; color:#0463a3;"><strong>Dear ' . $to_agent_name . ',</strong></td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px; line-height:25px; font-size:17px;">You have been assigned a ' . $visit_type . ' visit 
                            against the following details on ' . $today_date . '</td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px; line-height:25px;"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                            </tr>
                            <tr>
                              <td style="padding:0px 15px;"><table width="100%" border="0" cellpadding="2" cellspacing="1" bgcolor="#dddddd" style="font-size:14px;">
                                
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan No.</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $loan_no . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan Status.</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $status . '</td>
                                  </tr>
                                  <tr>
                                  <td width="31%" align="left" bgcolor="#0463a3" style="padding:15px; color:#fff;"><strong>Customer Name</strong></td>
                                  <td width="69%" bgcolor="#FFFFFF" style="padding:15px;">' . $customer_name . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>State</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $state_name . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>City</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;">' . $city_name . '</td>
                                  </tr>
                                <tr>
                                  <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Due Amount</strong></span></td>
                                  <td bgcolor="#FFFFFF" style="padding:15px;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="15" height="15" style="    position: relative;
                                margin-bottom: -1px;">' . $total_due_amount . '</td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <tr>
                            <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="border-top:solid 1px #ddd; padding-top:15px;"><a href="' . APPLE_STORE_LINK . '" target="_blank"><img src="' . APPLE_STORE_ICON . '" alt="aap-sore" width="108" height="30" /></a> <a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="30" height="30" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="instagram" width="30" height="30" /></a><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="30" height="30" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" alt="twitter" width="30" height="30" /></a><a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="30" height="30" /></a>&nbsp;<a href="' . ANDROID_STORE_LINK . '" target="_blank"><img src="' . ANDROID_STORE_ICON . '" alt="google-play" width="108" height="30" /></a></td>
                            </tr>
                            <tr>
                              <td align="center"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="5" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="font-size:14px; font-weight:bold;"> <a href="tel:' . REGISTED_MOBILE . '" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_PHONE_ICON . '" alt="phone" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/>' . REGISTED_MOBILE . '</a> <a href="' . WEBSITE_URL . '" target="_blank" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_WEB_ICON . '" alt="web" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . WEBSITE . '</a> <a href="mailto:' . INFO_EMAIL . '" style="text-decoration:blink; color:#0463a3;"> <img src="' . COLLECTION_EMAIL_ICON . '" alt="email" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . INFO_EMAIL . '</a> </td>
                            </tr>
                            </table>
                            </body>
                            </html>
                        ';
            }

            $email_sent_status = lw_send_email($to_agent_email, $subject, $message, "", "", $from_agent_email, "");

            if (!empty($email_sent_status['status'])) {
                $result_array['status'] = 1;
            } else {
                $result_array['error'] = $email_sent_status['error'];
            }
        }

        return $result_array;
    }

    public function get_user_email_details($conditions = array()) {
        $result_array = array("status" => 0, "message" => "User details not found.");

        $from_email = "";
        $user_id = 0;

        if (in_array(agent, ['CO1'])) {
            $from_email = $_SESSION['isUserSession']['email'];
            $user_id = $conditions['scm_user_id'];
        } else if (in_array(agent, ['CO2'])) {
            $from_email = $_SESSION['isUserSession']['email'];
            $user_id = $conditions['rm_user_id'];
        }

        $conditions_user['U.user_id'] = $user_id;
        $conditions_user['U.user_status_id'] = 1;
        $conditions_user['U.user_active'] = 1;
        $conditions_user['U.user_deleted'] = 0;

        $this->db->select("U.name as agent_name, U.email");
        $this->db->from("users U");
        $this->db->where($conditions_user);
        $temp_data = $this->db->get();

        if (!empty($temp_data->num_rows())) {
            unset($result_array['message']);

            $users = $temp_data->row_array();

            $result_array['status'] = 1;
            $result_array['data']['from_agent_email'] = $from_email;
            $result_array['data']['from_agent_name'] = $_SESSION['isUserSession']['name'];
            $result_array['data']['to_agent_email'] = $users['email'];
            $result_array['data']['to_agent_name'] = $users['agent_name'];
        }

        return $result_array;
    }

    public function get_master_payment_mode($mpm_id = null) {
        $result_array = array('status' => 0);
        $data_array = array();

        $select = 'mpm.mpm_id, mpm.mpm_name, mpm.mpm_heading ';
        $this->db->select($select);
        $this->db->from("master_payment_mode mpm");

        if (!empty($mpm_id)) {
            $conditions['mpm_id'] = $mpm_id;
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($mpm_id)) {
            $result_array['status'] = 1;
            $columns = $tempDetails->row_array();
            $data['payment_mode_id'] = $columns['mpm_id'];
            $data['payment_mode_name'] = $columns['mpm_name'];
            $data['payment_mode_heading'] = $columns['mpm_heading'];

            $data_array[] = $data;
        } else {
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $columns) {
                $data['payment_mode_id'] = $columns['mpm_id'];
                $data['payment_mode_name'] = $columns['mpm_name'];
                $data['payment_mode_heading'] = $columns['mpm_heading'];

                $data_array[] = $data;
            }
        }

        $result_array['data']['payment_mode_list'] = $data_array;
        return $result_array;
    }
}

?>
