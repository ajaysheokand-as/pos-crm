<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CampaignModel extends CI_Model
{

    private $table = 'instant_loan_campaign';

    function __construct()  
    {
        parent::__construct();
    }

    /**
     * function to get all the data
     *
     * @param mixed $limit
     * @param mixed $offset
     * @return mixed
     */
    public function getAllData($limit, $offset)
    {
        return $this->db
                    ->order_by('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->get($this->table); // Return results directly
    }

    /**
     * function to get the total data count
     *
     * @return mixed
     */
    public function get_count()
    {
        return $this->db->count_all($this->table);
    }
}