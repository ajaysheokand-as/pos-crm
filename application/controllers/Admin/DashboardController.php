<?php
defined('BASEPATH') or exit('No direct script access allowed');
class DashboardController extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('Task_Model');
		$this->load->model('Admin_Model');
		$this->load->model('State_Model', 'states');
		$this->load->model('User_Model', 'user');
		$this->load->model('UserRole_Model', 'role');
		$this->load->model('Company_Model', 'company');
		$this->load->model('Product_Model', 'product');
		$this->load->model('Dashboard_Model', 'dashboard');

		// $login = new IsLogin();
		// $login->index();
	}

	public function index() {
		$data['menus'] = $this->dashboard->index();
		$data['user'] = ['user_id' => $_SESSION['isUserSession']['user_id']];
		$data['company'] = $this->company->index();
		$data['product'] = $this->product->index([$conditions => company_id]);
		$this->load->view('Admin/Dashboard/index', $data);
	}

	public function save() {
		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
			$this->form_validation->set_rules('company_id', 'Company ID', 'trim|required');
			$this->form_validation->set_rules('product_id', 'Product ID', 'trim|required');
			$this->form_validation->set_rules('menu_name', 'Menu Name', 'trim|required');
			$this->form_validation->set_rules('route_link', 'Route Link', 'trim|required');
			$this->form_validation->set_rules('menu_order', 'Menu Order', 'trim|required');
			$this->form_validation->set_rules('icon', 'Icon', 'trim|required');
			$this->form_validation->set_rules('box_bg_color', 'Box Background Color', 'trim|required');
			$this->form_validation->set_rules('is_active', 'Is Active', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
				echo validation_errors();
			} else {
				$data = [
					'company_id' 	=> $this->input->post('company_id'),
					'product_id' 	=> $this->input->post('product_id'),
					'menu_name' 	=> $this->input->post('menu_name'),
					'route_link' 	=> $this->input->post('route_link'),
					'menu_order' 	=> $this->input->post('menu_order'),
					'icon' 			=> $this->input->post('icon'),
					'box_bg_color' 	=> $this->input->post('box_bg_color'),
					'is_active' 	=> $this->input->post('is_active'),
					'created_by' 	=> $this->input->post('user_id'),
					'created_on'	=> created_at
				];
				if ($this->dashboard->insert($data)) {
					echo 1;
				} else {
					echo 0;
				}
			}
		} else {
			echo "Session Expired. Please login first.";
			$this->islogin();
		}
	}

	public function edit($menu_id) {
		$id = $this->encrypt->decode($menu_id);
		$menus = $this->dashboard->select(['id' => $id]);
		$data['menu'] = $menus->row();
		$data['user'] = ['user_id' => $_SESSION['isUserSession']['user_id']];
		$data['company'] = $this->company->index();
		$data['product'] = $this->product->index([$conditions => company_id]);
		$this->load->view('Admin/Dashboard/edit', $data);
	}

	public function update($menu_id) {
		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
			$this->form_validation->set_rules('menu_id', 'User ID', 'trim|required');
			$this->form_validation->set_rules('company_id', 'Company ID', 'trim|required');
			$this->form_validation->set_rules('product_id', 'Product ID', 'trim|required');
			$this->form_validation->set_rules('menu_name', 'Menu Name', 'trim|required');
			$this->form_validation->set_rules('route_link', 'Route Link', 'trim|required');
			$this->form_validation->set_rules('menu_order', 'Menu Order', 'trim|required');
			$this->form_validation->set_rules('icon', 'Icon', 'trim|required');
			$this->form_validation->set_rules('box_bg_color', 'Box Background Color', 'trim|required');
			$this->form_validation->set_rules('is_active', 'Is Active', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
				echo validation_errors();
			} else {
				$menu_id = $this->input->post('menu_id');
				$data = [
					'company_id' 	=> $this->input->post('company_id'),
					'product_id' 	=> $this->input->post('product_id'),
					'menu_name' 	=> $this->input->post('menu_name'),
					'route_link' 	=> $this->input->post('route_link'),
					'menu_order' 	=> $this->input->post('menu_order'),
					'icon' 			=> $this->input->post('icon'),
					'box_bg_color' 	=> $this->input->post('box_bg_color'),
					'is_active' 	=> $this->input->post('is_active'),
					'updated_by' 	=> $this->input->post('user_id'),
					'updated_on'	=> created_at
				];
				if ($this->dashboard->update(['id' => $menu_id], $data)) {
					$this->session->set_flashdata('msg', 'Update successfully.');
					return redirect(base_url('adminViewDashboard'), 'refresh');
				} else {
					$this->session->set_flashdata('err', 'Failed to update record.');
					return redirect(base_url('adminEditDashboardMenu/' . $menu_id), 'refresh');
				}
			}
		} else {
			$this->session->set_flashdata('err', 'Failed to update record.');
			return redirect(base_url('adminEditDashboardMenu/' . $menu_id), 'refresh');
		}
	}

	public function adminTaskSetelment() {
		echo "Admin <pre>";
		print_r($_POST);
		exit;
	}

	public function sanction_dashboard_view() {
		if (!in_array($_SERVER['REMOTE_ADDR'], array("115.246.83.92"))) {
			die("NO ACCESS");
		}
		$this->load->view('MIS/dashboard');
	}

	public function sanction_dashboard_report() {

		$data = array('disbursal_executive_report' => '');
		if (false) { //!in_array($_SERVER['REMOTE_ADDR'], array("122.160.0.137", "125.63.105.251")

			$data['disbursal_executive_report'] = '<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Access Denied</title>
					<style>
						body {
							margin: 0;
							font-family: Arial, sans-serif;
							display: flex;
							align-items: center;
							justify-content: center;
							height: 100vh;
							background-color: #f8f9fa;
							color: #333;
						}
						.container {
							text-align: center;
						}
						.error-code {
							font-size: 100px;
							font-weight: bold;
							color: #dc3545;
						}
						.message {
							font-size: 24px;
							margin-top: 10px;
						}
						.description {
							font-size: 16px;
							color: #666;
							margin-top: 10px;
						}
						.home-link {
							display: inline-block;
							margin-top: 20px;
							padding: 10px 20px;
							font-size: 16px;
							color: #fff;
							background-color: #007bff;
							text-decoration: none;
							border-radius: 5px;
							transition: background-color 0.3s ease;
						}
						.home-link:hover {
							background-color: #0056b3;
						}
					</style>
				</head>
				<body>
					<div class="container">
						<div class="error-code">403</div>
						<div class="message">Access Denied</div>
						<div class="description">You don\'t have permission to access this page.</div>
						<a href="/" class="home-link">Go to Homepage</a>
					</div>
				</body>
				<script>
					setTimeout(() => {
						location.reload();
					}, 300000);
				</script>
				</html>';
		} else {
			$this->load->model('Report_Model', 'Report_Model');
			$fromDate = date("Y-m-d");
			$toDate = date("Y-m-d");
			$data['disbursal_executive_report'] = $this->Report_Model->DashboardDisbursalExecutiveWiseReport($fromDate, $toDate);
		}

		echo $data['disbursal_executive_report'];
	}
}
