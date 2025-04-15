<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CompanyHolidayController extends CI_Controller {

    public function __construct() {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('Task_Model', 'Tasks');
        $login = new IsLogin();
        $login->index();
    }

    public function index() {

        $current_date = date('Y-m-d');
        $this->load->library("pagination");
        $url = (base_url("addHolidayDetails") . "/" . $this->uri->segment(2));

        $sql = "SELECT company_holiday.ch_id, company_holiday.ch_holiday_date, company_holiday.ch_holiday_name, U.name, company_holiday.ch_created_datetime ";
        $sql .= "FROM company_holiday LEFT JOIN users U ON(U.user_id=company_holiday.ch_created_by) WHERE company_holiday.ch_active = 1 AND company_holiday.ch_deleted = 0 AND company_holiday.ch_holiday_date >= '$current_date' ORDER BY company_holiday.ch_holiday_date ASC ";

        $result = $this->db->query($sql);
        $page_number = !empty($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 0;

        $config = array();
        $config["base_url"] = $url;

        $config['page_query_string'] = TRUE;
        $config["total_rows"] = $result->num_rows();
        $config["per_page"] = 10;
        $config["uri_segment"] = 2;
        $config['full_tag_open'] = '<div class="pagging text-right"><nav><ul class="pagination">';
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

        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();

        if (empty($page_number)) {
            $page_number = 1;
            $sql .= "LIMIT 10;";
        } else {
            $page_number = $page_number;
            $sql .= "LIMIT 10, $page_number;";
        }

        $limited_result = $this->db->query($sql);
        $data['holiday_data'] = $limited_result->result_array();

        $this->load->view('Admin/addHolidayClender', $data);
    }

    public function saveHolidayDetails() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (agent == 'CA')) {

            $holiday_name = strval(trim($_POST['holiday_name']));
            if (!empty($holiday_name) && strlen($holiday_name) <= 100) {
                $holiday_date = date('Y-m-d', strtotime($_POST['holiday_date']));

                if (!empty($holiday_date)) {
                    $q = "SELECT ch_id FROM company_holiday where ch_holiday_date = '$holiday_date' AND ch_active = 1";
                    $query = $this->db->query($q);
                    if ($query->num_rows() > 0) {
                        $this->session->set_flashdata('err', 'Holiday Details Already exists.');
                        return redirect('addHolidayDetails', 'refresh');
                    }
                }

                $user_id = $_SESSION['isUserSession']['user_id'];

                $insertData['ch_holiday_date'] = $holiday_date;
                $insertData['ch_holiday_name'] = $holiday_name;
                $insertData['ch_created_by'] = !empty($user_id) ? $user_id : '';
                $insertData['ch_created_datetime'] = date("Y-m-d H:i:s");

                $this->db->insert('company_holiday', $insertData);
                $this->session->set_flashdata('message', 'Holiday Details Added Successfully!');

                return redirect(base_url('addHolidayDetails'), 'refresh');
            } else {
                $this->session->set_flashdata('err', 'Holiday Name cannot be empty.');
                return redirect(base_url('addHolidayDetails'), 'refresh');
            }
        }
    }

    public function deleteHolidayDetails($id) {

        if (!empty($id) && (agent == 'CA')) {

            $user_id = $_SESSION['isUserSession']['user_id'];

            $insertData['ch_active'] = 0;
            $insertData['ch_deleted'] = 1;
            $insertData['ch_deleted_by'] = !empty($user_id) ? $user_id : '';
            $insertData['ch_deleted_datetime'] = date("Y-m-d H:i:s");

            $this->db->where('ch_id', $id)->update('company_holiday', $insertData);
            $this->session->set_flashdata('message', 'Holiday Details Deleted Successfully!');

            return redirect(base_url('addHolidayDetails'), 'refresh');
        } else {
            $this->session->set_flashdata('err', 'Permission denied.');
            return redirect(base_url('addHolidayDetails'), 'refresh');
        }
    }

}

?>
