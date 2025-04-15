<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


if (!function_exists('test_method')) {




    if (!function_exists('getVerificationdata')) {

        function getVerificationdata($table, $id) {
            $ci = & get_instance();
            $ci->load->database();
            // echo "SELECT *   FROM $table where lead_id='$id'   ";
            // $query = $ci->db->query("SELECT *   FROM $table where lead_id='$id' ");
            $sql = "SELECT $table.*, lead_customer.email_verified_status as personal_email_isVerified , lead_customer.alternate_email_verified_status as office_email_isVerified, scm_residence_user.name as scm_fi_res_name, scm_office_user.name as scm_fi_office_user, rm_residence_user.name as rm_fi_res_name, rm_office_user.name as rm_fi_office_user ";
            $sql .= " From $table";
            $sql .= " LEFT JOIN leads ON $table.lead_id = leads.lead_id";
            $sql .= " LEFT JOIN lead_customer ON lead_customer.customer_lead_id = leads.lead_id";
            $sql .= " LEFT JOIN users scm_residence_user ON scm_residence_user.user_id = leads.lead_fi_scm_residence_assign_user_id";
            $sql .= " LEFT JOIN users scm_office_user ON scm_office_user.user_id = leads.lead_fi_scm_office_assign_user_id";
            $sql .= " LEFT JOIN users rm_residence_user ON rm_residence_user.user_id = $table.residece_cpv_allocated_to";
            $sql .= " LEFT JOIN users rm_office_user ON rm_office_user.user_id = $table.office_cpv_allocated_to";
            $sql .= " where $table.lead_id='$id' ";

            $query = $ci->db->query($sql);
            // echo "======". $str = $ci->db->last_query(); die;
            //  echo "reult---".  $query->num_rows() ;
//echo "=====".$query->num_rows(); //die;
            //$query = $ci->db->get();
            //echo "---->".$query->num_rows();

            $data = array();
            if ($query !== FALSE && $query->num_rows() > 0) {
                $data = $query->row_array();
            }

            return $data;

            // die;
            //if($query->num_rows() > 0){
            //  return $query->result_array();
            //  }else{
            //  return "No";
            // }
        }

    }


    if (!function_exists('getUserData')) {

        function getUserData($table, $id, $colmn) {
            $ci = & get_instance();
            $ci->load->database();

            $query = $ci->db->query("SELECT tbl_cam.borrower_name ,tbl_cam.middle_name , tbl_cam.surname, tbl_cam.gender, tbl_cam.dob,users.name as screenername,users.user_id as screenerid,tbl_cam.pancard,tbl_cam.mobile,tbl_cam.alternate_no,tbl_cam.email,tbl_cam.alternateEmail  FROM $table inner join users on users.user_id=$table.usr_created_by where $table.$colmn='$id'   ");

            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return "No";
            }
        }

    }

    //function to get lead_id from table_cam
    if (!function_exists('getLeadIdstatus')) {

        function getLeadIdstatus($table, $id) {
            $ci = & get_instance();
            $ci->load->database();

            $query = $ci->db->query("SELECT count(*) as total from $table where lead_id='$id'  ");

            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    if ($row['total'] != '0') {
                        return '1';
                    } else {
                        return '0';
                    }
                }
            } else {
                return "0";
            }
        }

    }



    //getrefrenceData 
    if (!function_exists('getrefrenceData')) {

        function getrefrenceData($table, $lead_id) {
            $ci = & get_instance();
            $ci->load->database();

            $query = $ci->db->query("SELECT lcr.lcr_id,lcr.lcr_name,lcr.lcr_mobile,mlt.mrt_name,lcr.lcr_relationType FROM $table lcr left join master_relation_type mlt on lcr.lcr_relationType=mlt.mrt_id where lcr.lcr_lead_id='$lead_id' and (lcr.lcr_active=1 && lcr.lcr_deleted=0 ) order by lcr.lcr_id");

            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return "No";
            }
        }

    }

    if (!function_exists('getrelationTypes')) {

        function getrelationTypes($table, $data) {
            $ci = & get_instance();
            $ci->load->database();
            $query = $ci->db->query("SELECT $data from $table");

            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return "0";
            }
        }

    }


    //function to get lead_id from table_cam
    if (!function_exists('getCouts')) {

        function getCouts($table, $where) {
            $ci = & get_instance();
            $ci->load->database();

            $query = $ci->db->query("SELECT count(*) as total from $table $where");

            $row = $query->row_array();

            if (!empty($row['total'])) {
                return $row['total'];
            } else {
                return "0";
            }
        }

    }

    //getuserCOllData
    if (!function_exists('getuserCOllData')) {

        function getuserCOllData($table, $id) {
            $ci = & get_instance();
            $ci->load->database();
            //echo "SELECT count(*) as total from $table $where  ";          
            $query = $ci->db->query("SELECT name,user_id as id from $table where user_scm_id='$id'  ");

            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return "0";
            }
        }

    }
}
?>
