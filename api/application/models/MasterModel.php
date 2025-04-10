<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MasterModel extends CI_Model {

    // select Query
    public function globel_select($query) {
        date_default_timezone_set("Asia/Kolkata");
        $currentdate = date('Y-m-d H:i:s');
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    // global select Query
    function getnumrowsData($selectdata, $table, $where) {

        $query = $this->db->query("SELECT $selectdata from $table $where  ");
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    //
    function getCityData($id) {
        $selectdata = 'm_city_id,m_city_name';
        $table = 'master_city';
        $where = array(
            'm_city_state_id' => $id,
            'm_city_active' => '1',
            'm_city_deleted' => '0'
        );
        return $query = $this->select($table, $where, $selectdata,'m_city_name','ASC');
    }

    function getAllCityData($sourceable = 0) {
        $selectdata = 'm_city_id as id,m_city_name as name,m_city_is_sourcing';
        $table = 'master_city';
        $where = array(
            'm_city_active' => '1',
            'm_city_deleted' => '0'
        );

        if ($sourceable == 1) {
            $where['m_city_is_sourcing!='] = 0;
        }

        $orderby_field = 'm_city_name';
        $order_by_type = 'asc';
        return $query = $this->select($table, $where, $selectdata, $orderby_field, $order_by_type);
    }

    function getAllDocumentFile($lead_id) {
        $selectdata = 'docs_id,docs_type,sub_docs_type,file,created_on';
        $table = 'docs';
        $where = array(
            'lead_id' => $lead_id,
            'upload_by' => NULL,
            'docs_active' => '1',
            'docs_deleted' => '0'
        );
        $orderby_field = 'docs_id';
        $order_by_type = 'DESC';
        return $query = $this->select($table,$where,$selectdata,$orderby_field,$order_by_type);
    }

    function getStateIdfromCityId($id) {


        $query = "SELECT ms.m_state_id as id,ms.m_state_name as name FROM master_city mc inner join master_state ms on mc.m_city_state_id=ms.m_state_id where mc.m_city_id='$id' ";

        $query = $this->db->query($query);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    function getAllDocument() {
        $selectdata = 'distinct(docs_type), docs_required';
        $table = 'docs_master';
        $where = array(
            'document_active' => '1',
            'document_deleted' => '0'
        );

        $orderby_field = 'docs_type';
        $order_by_type = 'asc';
        return $query = $this->select($table, $where, $selectdata, $orderby_field, $order_by_type);
    }

    function getPincode($id) {

        $selectdata = 'm_pincode_value  name';
        $table = 'master_pincode';
        $where = array(
            'm_pincode_city_id' => $id,
            'm_pincode_active' => '1',
            'm_pincode_deleted' => '0'
        );
        return $query = $this->select($table, $where, $selectdata);
    }

    function getStateData() {

        $table = 'master_state';
        $selectdata = 'm_state_id as id,m_state_name as name';
        $where = array(
            'm_state_active' => '1',
            'm_state_deleted' => '0'
        );

        return $query = $this->select($table, $where, $selectdata,'m_state_name','ASC');
    }

    function getDocumentList() {

        $table = 'docs_master';
        $selectdata = 'id as docs_id,docs_type,docs_sub_type';
        $where = array(
            'document_active' => '1',
            'document_deleted' => '0'
        );

        return $query = $this->select($table, $where, $selectdata, '');
    }

    // function select($table, $conditions, $data = null)
    // {
    //   return $this->db->select($data)->where($conditions)->from($table)->get()->result_array();
    // }

    function getIndustryData() {
        $selectdata = 'm_industry_id as id,m_industry_name as name';
        $where = array('m_industry_active'=>'1','m_industry_deleted'=>'0');
        return $query = $this->select('master_industry',$where,$selectdata);
    }
    function getMaritalStatusData() {
        $selectdata = 'm_marital_status_id as id,m_marital_status_name as name';
        $where = array('m_marital_status_active'=>'1','m_marital_status_deleted'=>'0');
        return $query = $this->select('master_marital_status',$where,$selectdata,'m_marital_status_name','ASC');
    }
    function getOccupationData() {
        $selectdata = 'm_occupation_id as id,m_occupation_name as name';
        $where = array('m_occupation_active'=>'1','m_occupation_deleted'=>'0');
        return $query = $this->select('master_occupation',$where,$selectdata,'m_occupation_name','ASC');
    }
    function getDesignationData() {
        $selectdata = 'm_designation_id as id,m_designation_name as name';
        $where = array('m_designation_active'=>'1','m_designation_deleted'=>'0');
        return $query = $this->select('master_designation',$where,$selectdata);
    }
    function getSalaryModeData() {
        $selectdata = 'm_salary_mode_id as id,m_salary_mode_name as name';
        $where = array('m_salary_mode_active'=>'1','m_salary_mode_deleted'=>'0');
        return $query = $this->select('master_salary_mode',$where,$selectdata);
    }
    function getQualificationData() {
        $selectdata = 'm_qualification_id as id,m_qualification_name as name';
        $where = array('m_qualification_active'=>'1','m_qualification_deleted'=>'0');
        return $query = $this->select('master_qualification',$where,$selectdata);
    }
    function getDocumentByIDData($docs_type) {
        $selectdata = 'docs_type, docs_sub_type,id';
        $where = array('docs_type'=>$docs_type);
        return $query = $this->select('docs_master',$where,$selectdata);
    }
    function getCompanyTypeData() {
        $selectdata = 'm_company_type_id as id,m_company_type_name as name';
        $where = array('m_company_type_active'=>'1','m_company_type_deleted'=>'0');
        return $query = $this->select('master_company_type',$where,$selectdata);
    }
    function getSidenceTypeData() {
        $selectdata = 'm_residence_type_id as id,m_residence_type_name as name';
        $where = array('m_residence_type_active'=>'1','m_residence_type_deleted'=>'0');
        return $query = $this->select('master_residence_type',$where,$selectdata);
    }
    function getBankTypeData() {
        $selectdata = 'm_bank_type_id as id,m_bank_type_name as name';
        $where = array('m_bank_type_active'=>'1','m_bank_type_deleted'=>'0');
        return $query = $this->select('master_bank_type',$where,$selectdata);
    }
    function getReligionData() {
        $selectdata = 'religion_id as id,religion_name as name';
        $where = array('religion_active'=>'1','religion_deleted'=>'0');
        return $query = $this->select('master_religion',$where,$selectdata);
    }
    function getDepartmentData() {
        $selectdata = 'department_id as id,department_name as name';
        $where = array('department_active'=>'1','department_deleted'=>'0');
        return $query = $this->select('master_department',$where,$selectdata);
    }
    function getRelationData() {
        $selectdata = 'mrt_id as id,mrt_name as name';
        $where = array('mrt_active'=>'1','mrt_deleted'=>'0');
        return $query = $this->select('master_relation_type',$where,$selectdata);
    }

    function getCurrentAddressProofData() {
        $selectdata = 'id,docs_type,docs_sub_type as name';
        $where = array('document_active'=>'1','document_deleted'=>'0','docs_type'=>'PRESENT ADDRESS PROOF');
        return $query = $this->select('docs_master',$where,$selectdata);
    }

    function getOthersKYCData() {
        $query = "SELECT id,docs_sub_type as name FROM docs_master where docs_type='VOTER ID' OR docs_type='PASSPORT' OR docs_type='DRIVING LICENCE'";
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return 0;
        }
    }

    function select($table, $conditions, $data = null, $orderby_field = null, $order_by_type = null) {
        if (empty($data)) {
            $data = "*";
        }
        $this->db->select($data);
        $this->db->where($conditions);
        $this->db->from($table);

        if(!empty($orderby_field) && !empty($order_by_type)) {
            $this->db->order_by($orderby_field, $order_by_type);
        }
        $reusult = $this->db->get()->result_array();
        return $reusult;
    }

    function getPurposeOfLoan() {
        $selectdata = 'enduse_id as id,enduse_name as name';
        $table = 'master_enduse';
        $where = array(
            'enduse_active' => '1',
            'enduse_deleted' => '0'
        );
        $orderby_field = 'enduse_name';
        $order_by_type = 'ASC';
        return $query = $this->select($table, $where, $selectdata, $orderby_field, $order_by_type);
    }

}
