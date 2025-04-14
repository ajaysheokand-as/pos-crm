<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! function_exists('test_method'))
{
    

   //function to get lead_id from table_cam
   if ( ! function_exists('getLeadIdstatus')){
      function getLeadIdstatus($table,$id,$column){
          $ci =& get_instance();
          $ci->load->database();
                          // echo "SELECT count(*) as total from $table where $column='$id'  ";
          $query = $ci->db->query("SELECT count(*) as total from $table where $column='$id'  "); 
          
            if($query->num_rows() > 0){
            foreach ($query->result_array() as $row)  {
               if($row['total']!='0')
               {
                  return '1';
               }
               else {
                  return '0';
               }
               
              }
            }else{
             return "0";
          }
      }
   }

   //get single data from db
   if ( ! function_exists('getcustId')){
      function getcustId($table,$column,$id,$getval){
          $ci =& get_instance();
          $ci->load->database();
          $id=strtoupper($id);
          //'master_state','m_state_id',$sql['cif_residence_state_id'],'m_state_name')
        // echo  "============= SELECT $getval from $table where $column='$id' ";          
          $query = $ci->db->query("SELECT $getval from $table where $column='$id'  "); 
          
            if($query->num_rows() > 0){
            foreach ($query->result_array() as $row)  {
            //echo "<pre>";print_r($row);
             return $row[$getval];
               
               
              } 
            }else{
             return "0";
          }
      }
   }



// funtion for dynamic query
   if ( ! function_exists('getnumrowsData')){
      function getnumrowsData($selectdata, $table,$where){
          $ci =& get_instance();
          $ci->load->database();
          //  echo "SELECT $selectdata from $table $where  ";
          $query = $ci->db->query("SELECT $selectdata from $table $where  "); 
            if($query->num_rows() > 0){
          
             return  $query->result_array();
               
               
           }
           else
           {
            return 0;
           }
      }
   }







}

?>