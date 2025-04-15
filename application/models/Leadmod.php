<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Leadmod extends CI_Model{



	

	public function globel_inset($table,$data)

	{

		return $this->db->insert($table,$data);

		//echo $this->db->last_query(); 

	}



	public function globel_update($table, $data, $upd_id, $colm)

	{

		$this->db->where($colm,$upd_id);

		$res = $this->db->update($table,$data);

	  //echo $this->db->last_query(); //die;

		return $res;

	}

public function selectdata($table, $data)

	{
  
		 $query = $this->db->query("SELECT count(*) as val from $table where lead_id IN ($data)  "); 
          
            if($query->num_rows() > 0){
            foreach ($query->result_array() as $row)  {
             return $row['val'];
               
               
              }
            }else{
             return "0";
          }

	}




}