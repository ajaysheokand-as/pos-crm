<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ForgetPasswordController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model');
        $this->load->model('Admin_Model');

        date_default_timezone_set('Asia/Kolkata');
        define("updated_at", date('Y-m-d H:i:s'));
    }

    public function forgetPassword() {
        $this->load->view('change_password');
    }

    public function forgetOldPassword() {
        // $this->load->view('createNewAfterForgetPassword');
        $this->load->view('change_new_password');
    }

    public function verifyotp() {
        echo "test";
    }

    public function verifyUser() {
//        error_reporting(E_ALL);
//        ini_set('display_errors', 1);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('err', validation_errors());
                return redirect(base_url('forgetPassword'), 'refresh');
            } else {
                $this->session->set_flashdata('err', "Work in progress for the same.");
                return redirect(base_url('forgetPassword'), 'refresh');
                $input_email = strval($this->input->post('email'));

                $conditions = [
                    'U.email' => $input_email,
                    'U.user_status_id' => 1,
                    'U.user_active' => 1,
                    'U.user_deleted' => 0,
                    'UR.user_role_active' => 1,
                    'UR.user_role_deleted' => 0,
                ];

                $select = 'U.user_id, U.mobile,U.user_last_login_datetime, U.company_id, U.product_id, U.name, U.email, RM.role_type_id as role_id, RM.role_type_name as role, RM.role_type_labels as labels';

                $sql = $this->db->select($select)
                        ->where($conditions)
                        ->from('users U')
                        ->join('user_roles UR', 'UR.user_role_user_id=U.user_id', 'inner')
                        ->join('master_role_type RM', 'UR.user_role_type_id=RM.role_type_id', 'inner')
                        ->get()
                        ->row();

                $user_id = $sql->user_id;
                $name = $sql->name;
                $email = $sql->email;
                $role = $sql->role;
                $otp = mt_rand(100000, 999999);
                $this->db->set('otp', $otp)->where('user_id', $user_id)->update('users');

                if ($input_email == $email) {
                    $sessionData = [
                        "user_id" => $user_id,
                        "name" => $name,
                        "email" => $email,
                        "role" => $role,
                    ];
                    $this->session->set_userdata('isUserSession', $sessionData);
                    $this->session->set_flashdata('msg', "OTP Sent To Registered mail Please Verify.");

                    $subject = BRAND_NAME . " FINTECH FORGET PASSWORD";
                    $message = "<!DOCTYPE html>
                        <html xmlns='http://www.w3.org/1999/xhtml'>
                            <head>
                                <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                                <title>Forget Password</title>
                            </head><table width='650' border='1' cellspacing='0' cellpadding='0' style='border:1px solid #000'>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top' colspan='2'><strong>&nbsp;Dear " . $name . ",</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;URL</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . LMS_URL . "</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Login User</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;$email</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;One Time Password</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $otp . "</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;User IP</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . ip . "</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Platform</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->platform() . "</strong></td>
                	          </tr>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Browser & Version</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->browser() . ' ' . $this->agent->version() . "</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Agent String</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . $this->agent->agent_string() . "</strong></td>
                	          </tr>
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;Password Change Activity</strong></td>
                	            <td height='20' style='color:#000;' valign='top'><strong>&nbsp;" . date('d-m-Y H:i:s', strtotime(updated_at)) . "</strong></td>
                	          </tr>
                
                	          <tr bgcolor='#ededed'>
                	            <td height='20' style='color:#000;' valign='top' colspan='3'><strong>&nbsp;If you have any query regarding " . LMS_URL . ".<br>Contact us on email - " . TECH_EMAIL . " (IT-Support)</strong></td>
                	          </tr>
                	        </table>";

                       require_once(COMPONENT_PATH . 'includes/functions.inc.php');
                       common_send_email($email, $subject, $message);

                    $this->load->view('otpverify');
                } else {
                    $this->session->set_flashdata('err', "Invalid Email, try once more.");
                    return redirect(base_url('forgetPassword'), 'refresh');
                }
            }
        }
    }

}

?>
