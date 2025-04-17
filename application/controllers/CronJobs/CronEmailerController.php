<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronEmailerController extends CI_Controller {
    private $notification_mail = "info@tejasloan.com";
    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronEmailer_Model', 'EmailModel');
        require_once(COMPONENT_PATH . "CommonComponent.php");
    }

    public function index() {
    }

    public function birthdayemailer() {

        $cron_name = "birthdayemailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getTodayBirthdayCustomer();

        $email_counter = array('email_sent' => 0, 'email_failed' => 0);
        $start_datetime = date("d-m-Y H:i:s");
        if (!empty($tempDetails)) {
            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    $email_data['subject'] = "Happy Birthday, " . $customer_data['name'] . "! 🎉 A Special Day Just for You!";
                    $email_data['message'] = '<!DOCTYPE html>
                                            <html lang="en">
                                            <head>
                                                <meta charset="UTF-8">
                                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                                <title>Birthday Wishes</title>
                                            </head>
                                            <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #00274d, #4da8da); color: #333; line-height: 1.6;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(135deg, #00274d, #4da8da); padding: 20px 0;">
                                                    <tr>
                                                        <td align="center">
                                                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                                                                <!-- Greeting Section -->
                                                                <tr>
                                                                    <td style="text-align: center; background-color: #ffffff;">
                                                                        <img src="https://sot-website.s3.ap-south-1.amazonaws.com/emailer/birthday_wish.png" alt="Happy Birthday" style="width: 100%; max-width: 600px; height: auto; display: block;">
                                                                        <p style="margin: 10px 0; font-size: 14px; color: #777;">"A special day to celebrate you and everything you bring to the world!"</p>
                                                                    </td>
                                                                </tr>
                                                                <!-- Motivational Message -->
                                                                <tr>
                                                                    <td style="padding: 20px; text-align: center; color: #555;">
                                                                        <p style="font-size: 16px; margin: 10px 0;">
                                                                            "Cheers to another year of growth, happiness, and achieving your financial goals! Wishing you a fantastic birthday filled with joy and memorable moments – from all of us at <a href="https://tejasloan.com/apply-now?utm_source=EMAIL&utm_campaign=birthday_wish_' . date("d-m-Y") . '" target="_blank"><strong>SalaryOnTime</strong></a>."
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <!-- Footer Section -->
                                                                <tr>
                                                                    <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                                                                        <p style="font-size: 14px; color: #777; margin: 0 0 10px;">
                                                                            Follow us on:
                                                                        </p>
                                                                        <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                            <img src="' . FACEBOOK_ICON . '" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                        </a>
                                                                        <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                            <img src="' . TWITTER_ICON . '" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                        </a>
                                                                        <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                            <img src="' . LINKEDIN_ICON . '" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                        </a>
                                                                        <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                            <img src="' . INSTAGRAM_ICON . '" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                        </a>
                                                                        <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                            <img src="' . YOUTUBE_ICON . '" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </body>
                                            </html>';

                    // $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 1);
                    $return_array = common_send_email($email_data['email'], $email_data['subject'], $email_data['message'], "", "", "", "", "", "", "");

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                    break;
                }
            }
            $email_data = array();
            $email_data['subject'] = "PROD-Birthday Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $return_array = common_send_email($this->notification_mail, $email_data['subject'], $email_data['message'], "", "", "", "", "", "", "");

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function festiveOfferForCloseLoanEmailer() {
        echo "Rohit : Not allowed at this time";
        die;
        if (true) {

            $tempDetails = $this->EmailModel->getAllCloseLoanEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        $email_data['subject'] = "LOANWALLE.COM Offers - Festive Treats";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Loanwalle</title>
                            </head>
                            <body>

                                <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="background:#f2f0f1 url(' . FESTIVAL_BANNER . ') no-repeat top; font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" width="200" height="50" / style="margin-top:15px;"></a><a href="tel:' . REGISTED_MOBILE . '"><img src="' . FESTIVAL_OFFICIAL_NUMBER . '" alt="phone" width="176" height="40" style="float: right;                                                                                                                                                                                margin-top:10px;"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="left"><a href="#"><img src="' . FESTIVAL_LINE . '" width="9" height="700" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="25" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="https://bit.ly/3mqyywv" style="background: #0363a3;color: #fff;text-transform: uppercase;padding: 12px 50px;border-radius: 30px;font-size: 24px;text-decoration: blink;border: solid 2px #063c60;">Apply Now</a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="30" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="35" height="35" align="facebook" /></a><a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="35" height="35" align="linkdin" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" width="35" height="35" alt="twitter" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="insta" width="35" height="35" align="instagram"></a> <a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="35" height="35" align="you-tube"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><p style="font-size:10px; padding-right:15px; color:#8f8f8f;"><span style="color:#F00;">*</span>Terms &amp; Conditions Apply</p></td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 2);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Festive Existing Customer Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Festive Existing Customer - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function festiveOfferForOldCloseLoanEmailer() {
        echo "Rohit : Not allowed at this time";
        die;
        if (true) {

            $tempDetails = $this->EmailModel->getAllOldCloudEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        $email_data['subject'] = "LOANWALLE.COM Offers - Festive Treats";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Loanwalle</title>
                            </head>
                            <body>

                                <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="background:#f2f0f1 url(' . FESTIVAL_CLOSE_BANNER . ') no-repeat top; font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" width="200" height="50"  style="margin-top:15px;"/></a><a href="tel:' . REGISTED_MOBILE . '"><img src="' . FESTIVAL_OFFICIAL_NUMBER . '" alt="phone" width="176" height="40" style="float: right;margin-top:10px;"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="left"><a href="#"><img src="' . FESTIVAL_LINE . '" width="9" height="667" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="25" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="https://bit.ly/3z3brNx" style="background: #0363a3;color: #fff;text-transform: uppercase;padding: 12px 50px;border-radius: 30px;font-size: 20px;text-decoration: blink;border: solid 2px #063c60; margin-right:10px;">Apply Now</a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="30" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="35" height="35" align="facebook" /></a><a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="35" height="35" align="linkdin" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" width="35" height="35" alt="twitter" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="insta" width="35" height="35" align="instagram"></a> <a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="35" height="35" align="you-tube"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><p style="font-size:10px; padding-right:15px; color:#8f8f8f;"><span style="color:#F00;">*</span>Terms &amp; Conditions Apply</p></td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 4);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Festive Settlement Existing Customer Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Festive Settlement Existing Customer - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function festiveOfferForNewCustomerEmailer() {
        //        echo "Rohit : Not allowed at this time";
        //        die;
        if (true) {

            $tempDetails = $this->EmailModel->getAllNewCustomerEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        $email_data['subject'] = "LOANWALLE.COM Offers - Festive Treats";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Loanwalle</title>
                            </head>
                            <body>

                                <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="background:#f2f0f1 url(' . FESTIVAL_CLOSE_BANNER . ') no-repeat top; font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" width="200" height="50"  style="margin-top:15px;"/></a><a href="tel:' . REGISTED_MOBILE . '"><img src="' . FESTIVAL_OFFICIAL_NUMBER . '" alt="phone" width="176" height="40" style="float: right;margin-top:10px;"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="right"><a href="#"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="left"><a href="#"><img src="' . FESTIVAL_LINE . '" width="9" height="667" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="25" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="https://bit.ly/3z3brNx" style="background: #0363a3;color: #fff;text-transform: uppercase;padding: 12px 50px;border-radius: 30px;font-size: 20px;text-decoration: blink;border: solid 2px #063c60; margin-right:10px;">Apply Now</a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="#"><img src="' . FESTIVAL_LINE . '" width="46" height="30" /></a></td>
                                    </tr>
                                    <tr>
                                        <td align="center"><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="35" height="35" align="facebook" /></a><a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="35" height="35" align="linkdin" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" width="35" height="35" alt="twitter" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="insta" width="35" height="35" align="instagram"></a> <a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="35" height="35" align="you-tube"></a></td>
                                    </tr>
                                    <tr>
                                        <td align="right"><p style="font-size:10px; padding-right:15px; color:#8f8f8f;"><span style="color:#F00;">*</span>Terms &amp; Conditions Apply</p></td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 3);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Festive New Customer Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Festive New Customer - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function freshNotContactableCustomerEmailer() { //Normal Email Template
        $cron_name = "notcontactablecustomeremailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllNotContactCustomerEmails();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        $campaign_name = 'NCCUSTEMAIL' . date("Y");

        if (!empty($tempDetails)) {
            //$i=1;
            foreach ($tempDetails as $customer_data) {
                //  if($i==1){
                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //$email_data['email'] = 'info@tejasloan.com';
                    $email_data['subject'] = "tejasloan.COM Offers - Instant Personal Loan In Just 30 Minutes*";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                    <html xmlns="http://www.w3.org/1999/xhtml">
                                                        <head>
                                                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                            <title>tejasloan.com Offers - Instant Personal Loan In Just 30 Minutes*</title>
                                                        </head>

                                                        <body>
                                                            <table width="667" border="0" align="center" cellpadding="0" cellspacing="0" style="background:url(' . MARKETING_BACK . '); border: 1px solid #f8f9fa;background-position: center; background-size: cover;
                                                            cursor: pointer;border-radius: 10px;margin-top: 10px;box-shadow: 5px 5px 15px rgba(0,0,0,0.15);font-family:Arial, Helvetica, sans-serif;">

                                                                <tr>
                                                                    <td valign="top"><a href="' . WEBSITE_UTM_SOURCE . $campaign_name . '"><img src="' . MARKETING_BANNER2 . '" alt="loanwalle-email-marketing" width="678" height="520" /></a></td>
                                                                </tr>
                                                                <tr>
                                                                <td align="center">
                                                                     <a href="' . WEBSITE_UTM_SOURCE . $campaign_name . '" style=" text-transform: capitalize; color: #000; padding: 3px 0; margin: 15px 0;
                                                                     font-size: 33px; border-radius: 10px; font-family:  serif !important; border-radius: 10px;text-decoration:none;display:inline-block;">
                                                                            Get Cash in your bank account faster than bullet train Quick and Easy Personal Loan from
                                                                            </a>

                                                                </td>
                                                            </tr>
                                                            <tr>
                                <td align="center">
                                    <a href="' . WEBSITE_UTM_SOURCE . $campaign_name . '" style="text-transform: capitalize; color: #016492; padding: 0 0 7px 0;
                                    margin: 0; margin-bottom: 0; font-size: 36px; border-radius: 0; font-style: italic;font-weight: 900;text-decoration:none">
                                               Bharat Loan
</a>

                                </td>
                            </tr>

                            <tr style="">
                            <td colspan="3" style="background-color: transparent; border-radius: 11px; color: #000; font-size: 13px; font-family: arial;
                            line-height: 18px; text-align: center;">
                            <b style="font-size: 40px; line-height: 45px; font-family:  serif !important;">
                                Starting from <span> <img src="' . MARKETING_BANNER_RUPEE_ICON . '" alt="" style="vertical-align: middle;"></span>
                                <span style="font-size: 40px; line-height: 45px; font-family: serif !important;">5000*</span>
                            </b>
                            <p><span> <a href="' . WEBSITE_UTM_SOURCE . $campaign_name . '"><img src="' . MARKETING_BANNER_APPLY_BUTTON . '" alt="" style="vertical-align: middle;"></a></span></p>
                        </td>
                        </tr>
                                                                <tr>
                                                                    <td align="center" style="color:#000; font-size:14px; font-weight:600;"><a href="' . WEBSITE_URL . '" target="_blank" style="color:#000; text-decoration:blink;">' . WEBSITE_URL . '</a>  |  <a href="mailto:' . INFO_EMAIL . '" style="color:#000; text-decoration:blink;">' . INFO_EMAIL . '</a> | <a href="tel:' . REGISTED_MOBILE . '" style="color:#000; text-decoration:blink;">' . REGISTED_MOBILE . '</a> </td>
                                                                </tr>
                                                                <tr>
                                                                <td colspan="3" style="margin-top:5px;background-color: transparent; border-radius: 11px; text-align: center;">
                                                                    <div style="margin-top:5px">

                                                                        <a href="' . APPLE_STORE_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_APP_STORE_ICON . '" alt="Apple">
                                                                        </a>
                                                                        <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_FB_ICON . '" alt="Facebook">
                                                                        </a>
                                                                        <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_INSTAGRAM_ICON . '"
                                                                                alt="Instagram">
                                                                        </a>
                                                                        <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_TWITTER_ICON . '" alt="Twitter">
                                                                        </a>
                                                                        <a href="' . LINKEDIN . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_LINKEDIN_ICON . '"
                                                                                alt="LinkedIn">
                                                                        </a>
                                                                        <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_YOUTUBE_ICON . '" alt="Youtube">
                                                                        </a>
                                                                        <a href="' . ANDROID_STORE_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                            <img src="' . MARKETING_BANNER_PLAY_STORE_ICON . '" alt="Playstore">
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                            </table>
                                                        </body>
                                                    </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 20);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
                //}
            }
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Not Contactable Customer Emailer - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'] . " <br/> Campaign Name : " . $campaign_name;

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            lw_send_email('ajay.singh@loanwalle.com', $email_data['subject'], $email_data['message']);
            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function loanOutstandingCustomer1To60DaysEmailer() {

        $cron_name = "loanoutstandingcustomer1To60daysemailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllDefaulterCustomerApps(1, 60);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['loan_no'])) {

                    $lead_id = $customer_data['lead_id'];
                    //$customer_email = $customer_data['email'];
                    $customer_email = 'info@tejasloan.com';
                    $loan_no = $customer_data['loan_no'];
                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));

                    $loan_amount = $customer_data['loan_recommended'];
                    $repayment_amount = $customer_data['repayment_amount'];

                    $repay_date = strtotime($customer_data['repayment_date']);

                    $roi = $customer_data['roi'];

                    $date1 = strtotime(date('d-m-Y'));
                    $date2 = $repay_date;
                    $due_past_date = ($date1 - $date2) / (60 * 60 * 24);

                    $dpd = 0;

                    if ($due_past_date > 60) {
                        $dpd = 60;
                    } else if ($due_past_date <= 60 && $due_past_date >= 0) {
                        $dpd = $due_past_date;
                    }

                    $late_panel_int = (($loan_amount * ($roi * 2) * $dpd) / 100);
                    $total_due = $late_panel_int + $repayment_amount;

                    $collection = $this->db->select_sum('received_amount')->where(['lead_id' => $lead_id, 'payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->from('collection')->get();

                    $collection = $collection->row_array();

                    $final_amount = $total_due - $collection['received_amount'];

                    $final_amount = number_format($final_amount);

                    $email_data = array();
                    //$email_data['email'] = $customer_email;
                    $email_data['email'] = 'info@tejasloan.com';
                    $email_data['subject'] = "tejasloan.com | Loan Outstanding Delay $due_past_date days | $loan_no";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Loan Outstanding</title>
                                                    </head>

                                                    <body>
                                                        <table width="618" border="0" align="center" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif;">
                                                            <tr>
                                                                <td align="left" style="background:url(' . OUTSTANDING_DEBIT . ');"><table width="100%" border="0"><tr>
                                                                            <td valign="top"><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" width="150" alt="logo" style="padding-top:10px;padding-left:28px"></a></td>
                                        <td align = "right" valign = "top"><img src = "' . OUTSTANDING_LINE . '" alt = "line" width = "15" height = "404" /></td>
                                        </tr>
                                        </table></td>
                                        </tr>
                                        <tr>
                                        <td style = "background:url(' . OUTSTANDING_BACKGROUND . '); padding:20px; border:solid 1px #ddd;"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                                        <tr>
                                        <td align = "center"><span style = "background: #0463a3;padding: 12px 35px;color: #fff;font-weight: bold;font-size: 25px;border-radius: 50px;margin-top: -100px;position: relative;bottom:41px;">Hurry Up!!!Already ' . $due_past_date . ' days delayed</span></td>
                                        </tr>
                                        <tr>
                                        <td align = "center"><strong style = "color:#0463a3; font-size:20px; font-weight:bold; padding:15px 0px; line-height:70px;">Dear ' . $cust_full_name . '</strong></td>
                                        </tr>
                                        <tr>
                                        <td align = "left" style = "line-height:25px; margin-top:20px;">
                                        With reference to your loan due amount of <span style = "color:#0463a3; font-weight:bold;"><img src = "' . COLLECTION_INR_ICON . '" alt = "inr" width = "10" height = "12" />' . $final_amount . '/-</span> for a loan amount of <span style = "color:#0463a3; font-weight:bold;"><img src = "' . COLLECTION_INR_ICON . '" alt = "inr" width = "10" height = "12" />' . $loan_amount . '/-</span> from <span style = "color:#0463a3; font-weight:bold;">LOANWALLE</span>.<br/> Loan account number <span style = "color:#0463a3; font-weight:bold;">' . $loan_no . '</span> repayment has already been delayed by ' . $due_past_date . ' days!!!<br/>Please pay now asap and avail the best services from <span style = "color:#0463a3; font-weight:bold;">LOANWALLE</span>.<br/>Further delay will impact your bureau scores as well as your credit history for taking loans in the future.
                                        </td>
                                        </tr>
                                        <tr>
                                        <td align = "center" style = "line-height:25px; margin-top:20px; color:#0463a3;"><strong>Please ignore if already paid </strong></td>
                                        </tr>
                                        <tr>
                                        <td align = "center" style = "line-height:25px; margin-top:20px; color:#0463a3;">&nbsp;
                                        </td>
                                        </tr>
                                        <tr>
                                        <td align = "center"><a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "background: #0463a3;padding: 10px 30px;color: #fff;text-decoration: blink;border-radius: 50px;">Pay Now</a></td>
                                        </tr>
                                        <tr>
                                        <td align = "center" style = "line-height:25px; margin-top:20px; color:#0463a3;"><img src = "' . OUTSTANDING_LINE . '" alt = "line" width = "15" height = "27" /></td>
                                        </tr>
                                        <tr>
                                        <td align = "center" style = "line-height:25px; margin-top:20px; color:#0463a3;">
                                        <a href = "' . APPLE_STORE_LINK . '" target = "_blank"><img src = "' . APPLE_STORE_ICON . '" alt = "app_store" width = "86" height = "27" style = "position: relative;bottom: 5px;right: 5px;"/></a>
                                        <a href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank"><img src = "' . LINKEDIN_ICON . '" alt = "linkdin" width = "35" height = "35" /></a>
                                        <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "' . INSTAGRAM_ICON . '" alt = "instagram" width = "35" height = "35" /></a>
                                        <a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "' . FACEBOOK_ICON . '" alt = "facebook" width = "35" height = "35" /></a>
                                        <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "' . TWITTER_ICON . '" alt = "twitter" width = "35" height = "35" /></a>
                                        <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "' . YOUTUBE_ICON . '" alt = "youtube" width = "35" height = "35" /></a>
                                        <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "' . ANDROID_STORE_ICON . '" alt = "goolge_play" width = "86" height = "27" style = "position: relative;bottom: 5px;left:3px;"/></a>
                                        </td>
                                        </tr>
                                        </table>
                                        </td>
                                        </tr>
                                        <tr>
                                        <td><img src = "' . OUTSTANDING_FOOTER_LINK . '" alt = "footer" width = "618" height = "40" border = "0" usemap = "#Map" /></td>
                                        </tr>
                                        </table>

                                        <map name = "Map" id = "Map">
                                        <area shape = "rect" coords = "48,7,195,34" href = "tel:' . REGISTED_MOBILE . '" />
                                        <area shape = "rect" coords = "204,7,374,35" href = "' . WEBSITE_URL . '" target = "_blank" />
                                        <area shape = "rect" coords = "382,8,556,33" href = "mailto:' . INFO_EMAIL . '" />
                                        </map>
                                        </body>
                                        </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 21, '', 'delay@loanwalle.com');

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD - Loan Outstanding Customer 1To60 Days Emailer - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function loanOutstandingFY2122CustomerEmailer() {

        $cron_name = "loanoutstandingfy2122customeremailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllDefaulterCustomerByDate('2021-04-01', '2022-03-31');

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['loan_no'])) {

                    $lead_id = $customer_data['lead_id'];
                    $customer_email = $customer_data['email'];
                    $loan_no = $customer_data['loan_no'];
                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));

                    $loan_amount = $customer_data['loan_recommended'];
                    $repayment_amount = $customer_data['repayment_amount'];

                    $repay_date = strtotime($customer_data['repayment_date']);

                    $roi = $customer_data['roi'];

                    $date1 = strtotime(date('d-m-Y'));
                    $date2 = $repay_date;
                    $due_past_date = ($date1 - $date2) / (60 * 60 * 24);

                    $dpd = 0;

                    if ($due_past_date > 60) {
                        $dpd = 60;
                    } else if ($due_past_date <= 60 && $due_past_date >= 0) {
                        $dpd = $due_past_date;
                    }

                    $late_panel_int = (($loan_amount * ($roi * 2) * $dpd) / 100);
                    $total_due = $late_panel_int + $repayment_amount;

                    $collection = $this->db->select_sum('received_amount')->where(['lead_id' => $lead_id, 'payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->from('collection')->get();

                    $collection = $collection->row_array();

                    $final_amount = $total_due - $collection['received_amount'];

                    $final_amount = number_format($final_amount);

                    $email_data = array();
                    $email_data['email'] = $customer_email;
                    //                    $email_data['email'] = 'info@tejasloan.com';
                    $email_data['subject'] = "Loanwalle.com | Loan Outstanding | $loan_no";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Higher Outstanding</title>
                        </head>

                        <body>

                        <table style = "font-family:Arial, Helvetica, sans-serif; background:#202121; color:#fff; padding:0px;" width = "750" cellspacing = "0" cellpadding = "0" border = "0" align = "center">
                        <tbody><tr>
                        <td width = "366" valign = "top" height = "420"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/Higher_Outstanding.png" alt = "Higher Outstanding" width = "366" height = "420"></td>
                        <td width = "384" valign = "top"><table style = "padding:0px;" width = "100%" cellspacing = "0" cellpadding = "0" border = "0">
                        <tbody><tr>
                        <td colspan = "2" align = "right"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/lw_logo.png" alt = "logo" style = "padding-top:10px;margin-right: 5px" width = "196" height = "46"></a></td>
                        </tr>

                        <tr>
                        <td colspan = "2"><strong>Dear ' . $cust_full_name . '</strong></td>
                        </tr>
                        <tr>
                        <td colspan = "2"><span style = "font-size:14px; line-height:10px;"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/line.png" alt = "line" width = "15" height = "10"></span></td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px; padding-right:10px;">Overdue Of <strong><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/inr.png" alt = "inr" style = "position:relative; bottom:-2px; right:3px; left:0px; width:10px;">' . $final_amount . '/-</strong> on your Loan Account <strong>' . $loan_no . '</strong> is unpaid.</td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px; padding-right:10px;">You need to repay your loan outstanding amount in the below Bank Account to close your loan asap.</td>
                        </tr>

                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px;"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/line.png" alt = "line" width = "15" height = "10"/></td>
                        </tr>
                        <tr><td style = "color: #fff;" colspan = "0"><table style = "padding:10PX;border: solid 1px #ddd;width: 90%;background: #525353;border-radius: 3px;" width = "100%" cellspacing = "0" cellpadding = "0" border = "0">
                        <tbody>

                        <tr style = "padding: "><td style = "font-size:10px;line-height:15px;">Bank Name</td><td style = "font-size:10px;line-height:15px;">ICICI Bank Limited</td></tr>
                        <tr><td style = "font-size:10px;line-height:15px;">Company Name</td><td style = "font-size:10px;line-height:15px;">Aman Fincap Limited</td></tr>
                        <tr><td style = "font-size:10px;line-height:15px;">Account Number</td><td style = "font-size:10px;line-height:15px;">084305001370</td></tr>
                        <tr><td style = "font-size:10px;line-height:15px;">IFSC Code</td><td style = "font-size:10px;line-height:15px;">ICIC0000843</td></tr>
                        <tr><td style = "font-size:10px;line-height:15px;">Branch Name</td><td style = "font-size:10px;line-height:15px;">DWARKA SEC-6 New Delhi</td></tr>
                        <tr><td style = "font-size:10px;line-height:15px;">Account Type</td><td style = "font-size:10px;line-height:15px;">Current Account</td></tr>
                        </tbody>
                        </table>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px;"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/line.png" alt = "line" width = "15" height = "10"/></td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px; padding-right:10px;">Please pay now to get eligible for RE-LOAN.</td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px;"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/line.png" alt = "line" width = "15" height = "10"/></td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px;font-weight:bold; line-height:20px; padding-right:10px;">APPLY FOR RE-LOAN NOW - <a href = "https://www.loanwalle.in/?utm_source=EMAILFY2122DEF" style = "color: inherit;">Click Here</a>.</td>
                        </tr>
                        <tr>
                        <td colspan = "2" style = "font-size:14px; line-height:20px;"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/line.png" alt = "line" width = "15" height = "10"/></td>
                        </tr>
                        <tr>
                        <td colspan = "2" align = "center">
                        <a href = "' . APPLE_STORE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/app-store.png" alt = "app_store" style = "position: relative;width:60px;bottom: 4px;right:3px;"></a>
                        <a href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/linkdin.png" alt = "linkdin" width = "25" height = "25"></a>
                        <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/instagram.png" alt = "instagram" width = "25" height = "25"></a>
                        <a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/facebook.png" alt = "facebook" width = "25" height = "25"></a>
                        <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/twitter.png" alt = "twitter" width = "25" height = "25"></a>
                        <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/youtube.png" alt = "youtube" width = "25" height = "25"></a>
                        <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/google-play.png" alt = "goolge_play" style = "position: relative;bottom: 5px;width:60px;left:3px;"></a>
                        </td>
                        </tr>

                        <tr>
                        <td colspan = "2" align = "right"><img src = "https://www.loanwalle.com/public/emailimages/Higher_Outstanding/images/footer.png" alt = "footer" usemap = "#Map" width = "100%" border = "0"></td>
                        </tr>
                        </tbody></table></td>
                        </tr>
                        </tbody></table>

                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "4,4,113,26" href = "tel:9999999341" />
                        <area shape = "rect" coords = "120,7,249,26" href = "' . WEBSITE_URL . '" target = "_blank" />
                        <area shape = "rect" coords = "255,7,384,25" href = "mailto:' . INFO_EMAIL . '" />
                        </map>
                        </body>
                        </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 22, '', 'delay@loanwalle.com');

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD - Loan Outstanding FY 2021-22 Emailer - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function freshLoanLohariJan22RejectEmailer() {
        echo "Rohit : Not allowed at this time";
        die;

        if (true) {

            $tempDetails = $this->EmailModel->getAllNewCustomerEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        $email_data['subject'] = "LOANWALLE.COM Offers - Festive Special";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>LOANWALLE.COM Offers - Festive Special</title>
                        </head>

                        <body>
                        <table width = "667" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-marketing-back.png); font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                        <td><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-logo.png" width = "667" height = "67" border = "0" usemap = "#Map" /></td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-line.png" width = "10" height = "412" /></td>
                        </tr>
                        <tr>
                        <td align = "center"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-marketing-3.png" alt = "loanwalle-email-marketing" /></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;"><a href = "https://bit.ly/3Gqd0YV" style = "background: #fff;border-radius: 50px;padding: 10px 30px;color: #034369;font-weight: bold;text-decoration: blink;border: solid 2px #034369;">Apply Now</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-line.png" alt = "line" width = "25" height = "20" /></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:14px; font-weight:600;"><a href = "tel:+919999999330" style = "color:#fff; text-decoration:blink;">+91-9999999-330</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;">www.loanwalle.com</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . '</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:17px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-line.png" alt = "line" width = "25" height = "10" /></td>
                        </tr>
                        <tr>
                        <td><table width = "100%" border = "0">
                        <tr>
                        <td width = "36%" align = "right"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-hr.png" alt = "hr" width = "180" height = "1" /></td>
                        <td width = "31%" align = "center"><a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-facebook.png" alt = "facebbok" width = "30" height = "30" /></a> <a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-linkdin.png" alt = "linkdin" width = "30" height = "30" /></a> <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-twitter.png" alt = "twitter" width = "30" height = "30" /></a> <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-instagram.png" alt = "instagram" width = "30" height = "30" /></a> <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-you-tube.png" alt = "youtube" width = "30" height = "30" /></a></td>
                        <td width = "33%" align = "left"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-hr.png" alt = "hr" width = "180" height = "1" /></td>
                        </tr>
                        <tr>
                        <td colspan = "3" align = "right"><img src = "https://www.loanwalle.com/public/emailimages/makar-sankranti/images/loanwalle-email-marketing-tc-apply.png" alt = "loanwalle-email-marketing-tc-apply" width = "13" height = "60" style = "margin-top:-100px;"></td>
                        </tr>
                        </table></td>
                        </tr>

                        </table>

                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "7,4,161,54" href = "' . WEBSITE_URL . '" target = "_blank" />
                        </map>
                        </body>
                        </html>
                        ';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 6);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Lohari Festival special - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Lohari Festival special - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function freshLoan26Jan22NewCustEmailer() {
        echo "Rohit : Not allowed at this time";
        die;
        //        error_reporting(E_ALL);
        //        ini_set("display_errors", 1);
        $time_close = intval(date("Hi"));

        if ($time_close > 1155) {
            //            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllNewCustomerEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        //                        $email_data['email'] = "info@tejasloan.com";
                        $email_data['subject'] = "LOANWALLE.COM Celebrate - Republic Day 2022";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Loanwalle.com Celebrate Republic Day</title>
                        </head>

                        <body>
                        <table width = "100" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "font-family:Arial, Helvetica, sans-serif; color:#fff; border:solid 1px #ddd; font-size:13px; font-weight:bold;">
                        <tr>
                        <td colspan = "3"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/loanwalle-republic-day-2022-header.jpg" width = "667" height = "114" border = "0" usemap = "#Map" alt = "loanwalle-republic-day-2022-header" /></td>
                        </tr>
                        <tr>
                        <td colspan = "3"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/loanwalle-republic-day-2022-center.jpg" alt = "loanwalle-republic-day-2022-center" width = "667" height = "513" border = "0" usemap = "#Map2" /></td>
                        </tr>
                        <tr>
                        <td width = "404" align = "center" bgcolor = "#0463a3" style = "padding:10px 1px;"><a href = "tel:+91-9999999341" style = "color:#fff; text-decoration:blink;">' . REGISTED_MOBILE . '</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;">www.loanwalle.com</a></td>
                        <td width = "141" align = "center" bgcolor = "#0463a3"><a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/facebook.png" alt = "loanwalle-facebook" width = "25" height = "25" /></a> <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/twitter.png" alt = "loanwalle-twiiter" width = "25" height = "25" /></a> <a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/linkdin.png" alt = "loanwalle-linkdin" width = "25" height = "25" /></a> <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/instagram.png" alt = "loanwalle-instagram" width = "25" height = "25" /></a> <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/you-tube.png" alt = "loanwalle-youtube" width = "25" height = "25" /></a> </td>
                        <td width = "122" align = "center" bgcolor = "#0463a3"><a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/republic-day/images/loanwalle-republic-day-center-mobile-app.png" alt = "loanwalle-republic-day-center-mobile-app"/></a></td>
                        </tr>
                        </table>


                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "12,13,241,72" href = "' . WEBSITE_URL . '" target = "_blank" />
                        </map>

                        <map name = "Map2" id = "Map2">
                        <area shape = "rect" coords = "45,402,186,443" href = "https://bit.ly/3GS9VRB" target = "_blank" />
                        </map>
                        </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 7);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Republic Day 2022 - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Celebrate - Republic Day 2022 - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function repayLoanEmailerOffer() {

        $time_close = intval(date("Hi"));

        if ($time_close > 1537) {
            //            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllReplayLoanEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {
                    if (!empty($customer_data['user_email_id'])) {
                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        //                        $email_data['email'] = 'info@tejasloan.com';
                        $email_data['subject'] = "LOANWALLE.COM Limited Offer on Repay Loan";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Untitled Document</title>
                        </head>

                        <body>
                        <table width = "667" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:#fff url(https://www.loanwalle.com/public/emailimages/31-Jan/images/background.jpg) no-repeat top; font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "left"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/loanwalle-logo.png" alt = "loanwalle-logo" width = "234" height = "50" style = "padding-left:9px;"></a></td>
                        </tr>
                        <tr>
                        <td><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/line.png" alt = "line" width = "6" height = "350" /></td>
                        </tr>
                        <tr>
                        <td><p style = "padding: 0px 20px; text-align:right;font-size: 31px;line-height: 41px;font-weight: 500;">
                        Offer valid till
                        <span style = "background: #004aa1;color: #fff;padding: 0px 2px;border-radius: 3px; font-weight:bold;">31st Jan</span>, <br />
                        Re-Pay your loan now.<br />
                        <span style = "margin-right: 184px;font-size: 55px;line-height: 65px;">&</span><br />Avail the offer on next loan.</p></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "padding:0px 0px 20px 0px;"><a href = "' . LOAN_REPAY_LINK . '" style = "background: #004aa1;border: solid 1px #004aa1;border-radius: 20px;padding: 8px 20px;text-decoration: blink;font-weight: bold;font-size: 14px;color: #fff;margin-left: 217px;">Repay Loan</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "background:#004aa1; color:#fff; font-size:17px; line-height:30px;"><a href = "tel:+91-9999999341" style = "color:#fff; text-decoration:blink;">' . REGISTED_MOBILE . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;">www.loanwalle.com</a> | <a href = "info@' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink; ">' . INFO_EMAIL . '</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "padding:10px;"><a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/facebook.png" alt = "loanwalle-facebook" width = "30" height = "30" /></a> <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/twitter.png" alt = "loanwalle-twiiter" width = "30" height = "30" /></a> <a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/linkdin.png" alt = "loanwalle-linkdin" width = "30" height = "30" /></a> <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/instagram.png" alt = "loanwalle-instagram" width = "30" height = "30" /></a> <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/you-tube.png" alt = "loanwalle-youtube" width = "30" height = "30" /></a> <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/31-Jan/images/loanwalle-mobile-app.png" width = "105" height = "33" /></a></td>
                        </tr>
                        </table>
                        </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 8);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Repay Loan Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Repay Loan Email- " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function valentineWeekCustomerEmailer() {

        $time_close = intval(date("Hi"));

        if ($time_close > 1956) {
            //            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllValentineWeekEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {


                    if (!empty($customer_data['user_email_id'])) {

                        if (!filter_var($customer_data['user_email_id'], FILTER_VALIDATE_EMAIL)) {
                            continue;
                        }

                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        //                        $email_data['email'] = "info@tejasloan.com";
                        $email_data['subject'] = "Valentine’s Day ahead and Pocket in debt?";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>LOANWALLE.COM - Valentine Week Special</title>
                        </head>

                        <body>
                        <table width = "667" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-marketing-back.png); font-family:Arial, Helvetica, sans-serif; border:solid 2px #d40005;">
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-logo.png" width = "289" height = "60" /></a></td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-valentine.png" alt = "loanwalle-valentine" width = "574" height = "353" /></td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-be-valentine.png" alt = "loanwalle-be-valentine" width = "411" height = "40" /></td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-small-loan.png" alt = "loanwalle-small-loan" width = "620" height = "53" /></td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-get-instant.png" alt = "loanwalle-get-instant" width = "648" height = "55" /></td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "font-size:20px; font-weight:500;"><a href = "https://bit.ly/3svQd7Y" target = "_blank" style = "background: #fff;border-radius: 50px;padding: 10px 50px;color: #cb1222;font-size: 25px;font-weight: bold;text-decoration: blink;border: solid 2px #cb1222;">Apply Now</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "font-size:20px; font-weight:500;">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-line.png" alt = "line" width = "25" height = "20" /></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#cb1222; font-size:14px; font-weight:600;"><a href = "tel:+9999999341" style = "color:#cb1222; text-decoration:blink;">' . REGISTED_MOBILE . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#cb1222; text-decoration:blink;">www.loanwalle.com</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#cb1222; text-decoration:blink;">' . INFO_EMAIL . '</a></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#cb1222; font-size:14px; font-weight:600;">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:17px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-line.png" alt = "line2" width = "25" height = "10" /></td>
                        </tr>
                        <tr>
                        <td><table width = "100%" border = "0">
                        <tr>
                        <td width = "25%" align = "right"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-left.png" width = "165" height = "16" alt = "left" /></td>
                        <td width = "50%" align = "center"><a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-facebook.png" alt = "facebbok" width = "30" height = "30" /></a> <a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-linkdin.png" alt = "linkdin" width = "30" height = "30" /></a> <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-twitter.png" alt = "twitter" width = "30" height = "30" /></a> <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-instagram.png" alt = "instagram" width = "30" height = "30" /></a> <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-email-you-tube.png" alt = "youtube" width = "30" height = "30" /></a> <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-get-it-on.png" align = "mobile-app"></a></td>
                        <td width = "25%" align = "left"><img src = "https://www.loanwalle.com/public/emailimages/valentine/images/loanwalle-right.png" width = "165" height = "16" alt = "right"/></td>
                        </tr>
                        <tr>
                        <td colspan = "3" align = "right">&nbsp;
                        </td>
                        </tr>
                        </table></td>
                        </tr>
                        </table>
                        </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 9);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Valentine Week Special - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Valentine Week Special - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function happyHoliCustomerEmailer() {

        $time_close = intval(date("Hi"));

        if ($time_close > 2005) {
            //            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllCustomerEmail();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {
                foreach ($tempDetails as $customer_data) {


                    if (!empty($customer_data['user_email_id'])) {

                        if (!filter_var($customer_data['user_email_id'], FILTER_VALIDATE_EMAIL)) {
                            continue;
                        }

                        $email_data = array();
                        $email_data['email'] = $customer_data['user_email_id'];
                        //                        $email_data['email'] = "info@tejasloan.com";
                        $email_data['subject'] = "HAPPY HOLI | LOANWALLE.COM";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Happy Holi | Loanwalle</title>
                        </head>

                        <body>
                        <table width = "667" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-marketing-back.png); font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                        <td><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-logo.png" width = "667" height = "67" border = "0" usemap = "#Map" /></td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-line.png" width = "10" height = "550" /></td>
                        </tr>
                        <tr>
                        <td align = "center">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;">&nbsp;
                        </td>
                        </tr>

                        <tr>
                        <td align = "center" style = "color:#fff; font-size:15px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-line.png" alt = "line" width = "25" height = "40" /></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:14px; font-weight:600;"><span style = "background: #fff;
                                                                                                                                              border: solid 3px #0463a3;
                                                                                                                                              padding: 7px 20px;
                                                                                                                                              border-radius: 50px;
                                                                                                                                              font-size: 14px;"><a href = "tel:+9999999341" style = "color:#0463a3; text-decoration:blink;">' . REGISTED_MOBILE . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#0463a3; text-decoration:blink;">www.loanwalle.com</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#0463a3; text-decoration:blink;">' . INFO_EMAIL . '</a></span></td>
                        </tr>
                        <tr>
                        <td align = "center" style = "color:#fff; font-size:17px; font-weight:500;"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-line.png" alt = "line" width = "25" height = "10" /></td>
                        </tr>
                        <tr>
                        <td><table width = "100%" border = "0">
                        <tr>
                        <td width = "36%" align = "right"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-hr.png" alt = "hr" width = "150" height = "1" /></td>
                        <td width = "31%" align = "center"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/social.png" width = "340" height = "48" border = "0" usemap = "#Map2" /></td>
                        <td width = "33%" align = "left"><img src = "https://www.loanwalle.com/public/emailimages/holi/images/loanwalle-email-hr.png" alt = "hr" width = "150" height = "1" /></td>
                        </tr>
                        </table></td>
                        </tr>

                        </table>

                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "7,4,161,54" href = "' . WEBSITE_URL . '" target = "_blank" />
                        </map>

                        <map name = "Map2" id = "Map2">
                        <area shape = "rect" coords = "6,7,44,42" href = "' . LINKEDIN_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "128,9,165,43" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "47,5,84,43" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "169,6,204,44" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "88,6,124,43" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "207,5,331,42" href = "' . ANDROID_STORE_LINK . '" target = "_blank" />
                        </map>
                        </body>
                        </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 11);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }
                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Holi Special - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Holi Special - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function legalNoticeEmailer() {

        $time_close = intval(date("Hi"));

        if ($time_close > 1353) {
            //            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllLegaNoticeEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);
            $cc_email = "admin@preachlaw.com,preachlawcompany@gmail.com";
            //            $cc_email = "ajay@loanwalle.com";
            $bcc_email = "legal@loanwalle.com";

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {


                    if (!empty($customer_data['email'])) {

                        $lead_id = $customer_data['lead_id'];
                        $loan_no = $customer_data['loan_no'];
                        $cust_full_name = $customer_data['cust_full_name'];
                        $send_date = date('d-m-Y');
                        $reference_no = 'Notice/Naman/' . date("Ymd");
                        $lw_company_name = 'NAMAN FINLEASE PRIVATE LIMITED';
                        $loan_amount = $customer_data['recommended_amount'];
                        $repayment_amount = $customer_data['repayment_amount'];
                        $interest = $repayment_amount - $loan_amount;
                        $repay_date = strtotime($customer_data['repayment_date']);
                        $disbursal_date = date("d-m-Y", strtotime($customer_data['disbursal_date']));
                        $roi = $customer_data['roi'];

                        $date1 = strtotime(date('d-m-Y'));
                        $date2 = $repay_date;
                        $due_past_date = ($date1 - $date2) / (60 * 60 * 24);

                        $dpd = 0;
                        if ($due_past_date > 60) {
                            $dpd = 60;
                        } else if ($due_past_date <= 60 && $due_past_date >= 0) {
                            $dpd = $due_past_date;
                        }

                        $late_panel_int = (($loan_amount * ($roi * 2) * $dpd) / 100);
                        $total_due = $late_panel_int + $repayment_amount;

                        $collection = $this->db->select_sum('received_amount')->where(['lead_id' => $lead_id, 'payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->from('collection')->get();

                        $collection = $collection->row_array();

                        $final_amount = $total_due - $collection['received_amount'];

                        $loan_amount = number_format($loan_amount);
                        $interest = number_format($interest);
                        $late_panel_int = number_format($late_panel_int);
                        $total_due = number_format($total_due);
                        $received_amount = number_format($collection['received_amount']);
                        $final_amount = number_format($final_amount);

                        $email_data = array();
                        $to_email = $customer_data['email'];
                        //                        $to_email = 'info@tejasloan.com';

                        $email_subject = "LEGAL NOTICE : $cust_full_name";

                        $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">

                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Legal Notice : ' . $loan_no . '</title>
                        </head>

                        <body>

                        <table width = "763" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                        <td>
                        <table width = "763" border = "0" align = "center" style = "border:solid 1px #ddd; padding:10px; font-family:Arial, Helvetica, sans-serif; line-height:22px;">
                        <tr>
                        <td>
                        <table width = "100%" border = "0">
                        <tr>
                        <td colspan = "2" align = "center"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/preach-law-logo.png" alt = "preach-law-logo" width = "237" height = "128" /></td>
                        </tr>
                        <tr>
                        <td width = "52%" align = "left"><strong>Ref: ' . $reference_no . '</strong></td>
                        <td width = "48%" align = "right"><strong>Date: ' . $send_date . '</strong></td>
                        </tr>
                        <tr>
                        <td align = "left">&nbsp;
                        </td>
                        <td align = "right">
                        <!--<p align = "right"><strong>Delhi, India</strong></p>-->
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2" align = "right">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">To, </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">Mr/Mrs : <span style = "text-decoration:underline;">' . $cust_full_name . '</span></p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">Loan I’D : <span style = "text-decoration:underline;">' . $loan_no . '</span></p>
                        </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">Subject: Reminder notice for loan amount recovery.</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;"><strong>My Client:</strong> <strong>' . $lw_company_name . '</strong></p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">M/S ' . $lw_company_name . ', operating under the brand name of Loanwalle.com.</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">To whomsoever it may concern, </p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">On instructions and on behalf of my above-named client i.e., M/S ' . $lw_company_name . ', operating under the brand name of & ldquo;
                        Loanwalle.com&rdquo;
                        , having it & rsquo;
                        s registered head office at S-370, LGF, Panchsheel Park, New Delhi- 110017, I hereby serve upon you the following notice:-</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">You had approached my client for a short-term loan on ' . $disbursal_date . '.</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">Your repayment amount including the interest and other dues as on ' . $send_date . ' is <img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $final_amount . ', the particulars of which arementioned below:</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <table width = "100%" border = "0" cellpadding = "5" cellspacing = "1" bgcolor = "#ccc">
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><strong>PARTICULARS</strong></p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><strong>AMOUNT/DAYS</strong></p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Principal Loan</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $loan_amount . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Interest</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $interest . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Delay in Repayment</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">' . $dpd . ' Days</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Late Penalty Interest</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $late_panel_int . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Total Due</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $total_due . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Payment Received</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $received_amount . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td width = "197" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;">Final Total</p>
                        </td>
                        <td width = "188" valign = "top" bgcolor = "#FFFFFF">
                        <p style = "margin: 2px 0px;"><img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $final_amount . '</p>
                        </td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">As on ' . $send_date . ', the total amount due and payable by you to my client is <img src = "https://www.loanwalle.com/public/emailimages/preach-law/image/inr.png" alt = "inr" width = "13" height = "13" />' . $received_amount . '</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">You are hereby called upon to pay all the updated dues immediately in the absence of which my client will be compelled to initiate legal proceedings against you as per the law.</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p style = "margin: 2px 0px;">You are also advised to take note of the fact that any further delay in repayment will be duly updated by my client with all the credit bureaus which will severe disparagement to your further borrowing capacity from any bank or financial institution.</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">Copy of this notice has been retained in my office for further course of actions and recourse.
                        </p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">
                        <p align = "right"><strong>Yours truly, </strong></p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2" align = "right">
                        <p align = "right"><strong>Krishna Kumar Mishra</strong><br />
                        (Advocate&amp;
                        Attorney)<br />
                        PREACH LAW LLP</p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2" align = "center">
                        <p align = "center"><em>Note: This is system generated demand notice, hence rubber stamp and signature are not required</em></p>
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td colspan = "2" align = "center" style = "border-top:solid 2px #000;">
                        <p align = "center">PREACH LAW LLP<br />
                        Office: E-111-B, Nawada Housing Complex, Uttam Nagar, Delhi – 110059<br />
                        <a href = "mailto:admin@preachlaw.com" style = "color:#000; text-decoration:none !important;">admin@preachlaw.com</a> | T: 01146574455 | M:+91 9311664455 | <a href = "http://www.preachlaw.com/" target = "_blank" style = "color:#000; text-decoration:none !important;">www.preachlaw.com</a>
                        </p>
                        </td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td>
                        <p style = " line-height:22px; margin-bottom:5px;"><strong>Regards</strong><br />
                        <strong style = "color:#339;">Krishna Kumar Mishra, Founder Partner</strong><br />
                        <span style = "color:#339;">(Advocate, Consultant & amp;
                        IPR Attorney)</span>
                        </p>
                        </td>
                        </tr>
                        <tr>
                        <td><img src = "https://www.loanwalle.com/public/emailimages/law-firm/image/line.jpg" alt = "line" style = "margin-bottom:10px;" /></td>
                        </tr>
                        <tr>
                        <td style = "font-style:italic; line-height:25px;"><strong>PREACH LAW LLP</strong><br />
                        Reg. Office: B-34, S/F, Arjun Park, New Delhi – 110043<br />
                        E: <a href = "mailto:admin@preachlaw.com" style = "color:#000; text-decoration:underline;">admin@preachlaw.com</a> | W: <a href = "www.preachlaw.com" target = "_blank" style = "color:#000; text-decoration:underline;">www.preachlaw.com</a><br />
                        T: 011-46574455 | M: +91 9311664455 | 9311465113 </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        </table>
                        </body>

                        </html>';

                        $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, $bcc_email, 10, $cc_email);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                        $insert_log_array = array();
                        $insert_log_array['legal_email_provider'] = "MAILGUN";
                        $insert_log_array['legal_email_type_id'] = 1;
                        $insert_log_array['legal_email_lead_id'] = $lead_id;
                        $insert_log_array['legal_email_loan_no'] = $loan_no;
                        $insert_log_array['legal_email_loan_no'] = $loan_no;
                        $insert_log_array['legal_email_sent_to'] = $to_email;
                        $insert_log_array['legal_email_sent_cc'] = $cc_email;
                        $insert_log_array['legal_email_sent_bcc'] = $bcc_email;
                        $insert_log_array['legal_email_content'] = addslashes($email_message);
                        $insert_log_array['legal_email_api_status_id'] = $return_array['status'];
                        $insert_log_array['legal_email_errors'] = $return_array['error'];
                        $insert_log_array['legal_email_created_on'] = date("Y-m-d H:i:s");

                        $this->EmailModel->emaillog_leagal_insert($insert_log_array);
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-LEGAL NOTICE EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-LEGAL NOTICE EMAIL - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function repaymentReminderToCustomerEmail() {
        //        error_reporting(E_ALL);
        //        ini_set("display_errors", 1);
        //        $time_close = intval(date("Hi"));
        //        if ($time_close > 1353) {
        ////            $this->middlewareEmail("info@tejasloan.com", "CRON CALL EMAIL | freshLoan26Jan22NewCustEmailer", "cron was called again | ".date("Y-m-d H:i:s"), '', 99);
        //            die;
        //        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails();
            $start_datetime = date("d-m-Y H:i:s");
            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {


                    if (!empty($customer_data['email'])) {

                        $lead_id = $customer_data['lead_id'];

                        $loan_no = $customer_data['loan_no'];

                        $credit_manager_email = $customer_data['credit_manager_email'];
                        $credit_manager_name = $customer_data['credit_manager_name'];
                        $credit_manager_mobile = $customer_data['credit_manager_mobile'];

                        $cust_full_name = $customer_data['cust_full_name'];

                        $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                        //                        $sanction_amount = $customer_data['loan_recommended'];
                        //                        $roi = $customer_data['roi'];
                        //                        $dpd = 1;
                        //                        $late_repayment_interest_amount_perday = (($sanction_amount * ($roi * 2) * $dpd) / 100);

                        $email_data = array();
                        $to_email = $customer_data['email'];
                        //                        $to_email = 'info@tejasloan.com';

                        $email_subject = "REPAYMENT REMINDER : $cust_full_name";

                        $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">

                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Repayment Reminder</title>
                        </head>

                        <body>
                        <table width = "778" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "padding:10px 10px 2px 10px; border:solid 2px #0363a3; font-family:Arial, Helvetica, sans-serif;border-radius:3px;">
                        <tr>
                        <td align = "left" style = "background:url(' . REMINDER_HAND_SHAKE . ');">
                        <table width = "100%" border = "0" style = "height:300px; padding:30px 0px;">
                        <tr>
                        <td valign = "top"><img src = "' . EMAIL_BRAND_LOGO . '" width = "200" height = "50" style = "margin-top:-23px;"/></td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        <tr>
                        <td><img src = "' . COLLECTION_LINE . '" width = "34" height = "8" /></td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>

                        <tr>
                        <td>Dear <strong>' . $cust_full_name . ', </strong></td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td> Greetings of the day!!!We hope this email finds you and your family well.</td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td style = "line-height:25px;">Your Loan No: <strong>' . $loan_no . '</strong> is due for repayment on <strong>' . $repayment_date . '.</strong> Please repay your loan within time to save penalty interest.</td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td>Please pay on time to maintain a healthy credit score and get a personal loan whenever you require it.</td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td style = "line-height:25px;">For any queries or assistance, please keep in touch with <strong>' . $credit_manager_name . ' @ <a style = "text-decoration: none;" href = "tel:' . $credit_manager_mobile . '">' . $credit_manager_mobile . '</a></strong>.</td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td style = "text-align:center;"><a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "background: #006;border-radius: 3px;padding: 8px 30px;color: #fff;text-decoration: blink;font-weight: bold;">Repay Loan</a> </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td><span style = "color: rgb(246, 3, 3);font-size: 12px; float:right;"><strong>Note: Kindly ignore this email if already paid.</strong></span> </td>
                        </tr>
                        <tr>
                        <td>
                        <p style = " line-height:22px; margin-bottom:5px;font-size: 13px;"><strong>Regards</strong><br />
                        <strong>Team Loanwalle</strong><br/>
                        <span>Aman Fincap Limited</span><br/>
                        <span>S-370, Basement, Panchsheel Enclave</span><br/>
                        <span>New Delhi-110017</span>
                        </p>
                        </td>
                        </tr>
                        <tr>
                        <td>&nbsp;
                        </td>
                        </tr>

                        <tr>
                        <td colspan = "3" align = "center" bgcolor = "#0463A3" style = "padding:10px; color:#fff; font-size:14px; font-weight:bold;"><a href = "tel:+91-9999999341" style = "color:#fff; text-decoration:blink;">' . REGISTED_MOBILE . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;">www.loanwalle.com</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . '</a></td>
                        </tr>

                        <tr>
                        <td colspan = "3" align = "center" bgcolor = "#FFFFFF" style = "padding:2px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;">
                        <a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "' . LINKEDIN_ICON . '" width = "30" height = "30" /></a>
                        <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "' . INSTAGRAM_ICON . '" width = "30" height = "30" /></a>
                        <a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "' . FACEBOOK_ICON . '" width = "30" height = "30" /></a>
                        <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "' . TWITTER_ICON . '" width = "30" height = "30" /></a>
                        <a href = "' . YOUTUBE_LINK . '" target = "_blank"><img src = "' . YOUTUBE_ICON . '" width = "30" height = "30" /></a>
                        <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "' . ANDROID_STORE_ICON . '" width = "100" height = "30" /></a>
                        </td>
                        </tr>
                        </table>
                        </body>
                        </html>';

                        $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 12, "collection@loanwalle.com", $credit_manager_email);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-REPAYMENT REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT REMINDER EMAIL - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function repaymentReminder5Day() {

        $cron_name = "repaymentreminder5day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 5;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                    $email_data = array();
                    $to_email = $customer_data['email'];
                    //$to_email = 'ajay@salaryin.com';

                    $email_subject = "tejasloan.COM | Gentle Reminder : $cust_full_name";

                    $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Payment Reminder 5 Day</title>
                        </head>

                        <body>

                        <table width = "740" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(' . REMINDER_PAYMENT_REMINDER . ') no-repeat;">
                        <tr>
                        <td valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                        <tr>
                        <td width = "99%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src="' . BRAND_TRANSPARENT_LOGO . '" alt="logo-speedoloan" style="width: 156px;padding-left: 28px;padding-top: 10px;" /></a></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top"><img src = "' . REMINDER_5_DAY . '" alt = "day" width = "170" height = "170" style = "position: relative;top: -54px;margin: 0 auto;left: 42%;" /></td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "' . REMINDER_LINE . '" alt = "line" width = "10" height = "80" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:35px;"><strong>Dear ' . $cust_full_name . ', </strong></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;">This is a gentle reminder that payment of <em><strong style = "color:#0463a3;"><img src = "' . COLLECTION_INR_ICON . '" alt = "inr" width = "13" height = "17" />' . $repayment_amount . '</strong></em> against <br /> Loan Number <strong style = "color:#0463a3;">' . $loan_no . '</strong> is due on <strong style = "color:#0463a3;">' . $repayment_date . '</strong>.</td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "8" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><span style = "font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">To repay now, visit <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> <br />
                        or call us on <a href = "tel:' . REGISTED_MOBILE . '" style = "color:#0463a3">' . REGISTED_MOBILE . '</a></span></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "65" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_SOCIAL_LINK . '" width = "411" alt = "social-line" height = "39" border = "0" usemap = "#Map2Map" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "10" /><img src = "' . REMINDER_FOOTER . '" alt = "footer-line" width = "740" height = "44" border = "0" usemap = "#Map" />
                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "81,7,228,35" href = "tel:' . REGISTED_MOBILE . '" />
                        <area shape = "rect" coords = "273,8,445,35" href = "' . WEBSITE_URL . '" target = "_blank" />
                        <area shape = "rect" coords = "487,7,661,34" href = "mailto:' . INFO_EMAIL . '" />
                        </map></td>
                        </tr>
                        </table>
                        <map name = "Map2Map" id = "Map2Map">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map></td>
                        </tr>
                        <map name = "Map2" id = "Map2">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map>
                        </table>
                        </body>
                        </html>';

                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 13, "info@tejasloan.com", 'info@tejasloan.com');
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 13,  "", '');
                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminder4Day() {

        $cron_name = "repaymentreminder4day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 4;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                    $email_data = array();
                    //  $to_email = $customer_data['email'];
                    //$to_email = 'info@tejasloan.com';

                    $email_subject = "tejasloan.com | Gentle Reminder : $cust_full_name";

                    $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Payment Reminder 4 Day</title>
                        </head>

                        <body>

                        <table width = "740" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(' . REMINDER_PAYMENT_REMINDER . ') no-repeat;">
                        <tr>
                        <td valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                        <tr>
                        <td width = "99%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src="' . BRAND_TRANSPARENT_LOGO . '" alt="logo-speedoloan" style="width: 156px;padding-left: 28px;padding-top: 10px;" /></a></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top"><img src = "' . REMINDER_4_DAY . '" alt = "day" width = "170" height = "170" style = "position: relative;top: -54px;margin: 0 auto;left: 42%;" /></td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "' . REMINDER_LINE . '" alt = "line" width = "10" height = "80" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:35px;"><strong>Dear ' . $cust_full_name . ', </strong></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;">This is a gentle reminder that payment of <em><strong style = "color:#0463a3;"><img src = "' . COLLECTION_INR_ICON . '" alt = "inr" width = "13" height = "17" />' . $repayment_amount . '</strong></em> against <br /> Loan Number <strong style = "color:#0463a3;">' . $loan_no . '</strong> is due on <strong style = "color:#0463a3;">' . $repayment_date . '</strong>.</td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "8" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><span style = "font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">To repay now, visit <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> <br />
                        or call us on <a href = "tel:' . REGISTED_MOBILE . '" style = "color:#0463a3">' . REGISTED_MOBILE . '</a></span></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "65" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_SOCIAL_LINK . '" width = "411" alt = "social-line" height = "39" border = "0" usemap = "#Map2Map" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "10" /><img src = "' . REMINDER_FOOTER . '" alt = "footer-line" width = "740" height = "44" border = "0" usemap = "#Map" />
                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "81,7,228,35" href = "tel:' . REGISTED_MOBILE . '" />
                        <area shape = "rect" coords = "273,8,445,35" href = "' . WEBSITE_URL . '" target = "_blank" />
                        <area shape = "rect" coords = "487,7,661,34" href = "mailto:' . INFO_EMAIL . '" />
                        </map></td>
                        </tr>
                        </table>
                        <map name = "Map2Map" id = "Map2Map">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map></td>
                        </tr>
                        <map name = "Map2" id = "Map2">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map>
                        </table>
                        </body>
                        </html>';

                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 14,  "info@tejasloan.com", 'info@tejasloan.com');
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 14,  "", '');
                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminder3Day() {

        $cron_name = "repaymentreminder3day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 3;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                    $email_data = array();
                    $to_email = $customer_data['email'];
                    //$to_email = 'info@tejasloan.com';

                    $email_subject = "tejasloan.COM | Payment Reminder : $cust_full_name";

                    $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Payment Reminder 3 Day</title>
                        </head>

                        <body>

                        <table width = "740" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(' . REMINDER_PAYMENT_REMINDER . ') no-repeat;">
                        <tr>
                        <td valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                        <tr>
                        <td width = "99%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src="' . BRAND_TRANSPARENT_LOGO . '" alt="logo-speedoloan" style="width: 156px;padding-left: 28px;padding-top: 10px;" /></a></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top"><img src = "' . REMINDER_3_DAY . '" alt = "day" width = "170" height = "170" style = "position: relative;top: -54px;margin: 0 auto;left: 42%;" /></td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "' . REMINDER_LINE . '" alt = "line" width = "10" height = "80" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:35px;"><strong>Dear ' . $cust_full_name . ', </strong></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;">This is a gentle reminder that payment of <em><strong style = "color:#0463a3;"><img src = "' . COLLECTION_INR_ICON . '" alt = "inr" width = "13" height = "17" />' . $repayment_amount . '</strong></em> against <br /> Loan Number <strong style = "color:#0463a3;">' . $loan_no . '</strong> is due on <strong style = "color:#0463a3;">' . $repayment_date . '</strong>.</td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "8" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><span style = "font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">To repay now, visit <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> <br />
                        or call us on <a href = "tel:' . REGISTED_MOBILE . '" style = "color:#0463a3">' . REGISTED_MOBILE . '</a></span></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "65" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_SOCIAL_LINK . '" width = "411" alt = "social-line" height = "39" border = "0" usemap = "#Map2Map" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "10" /><img src = "' . REMINDER_FOOTER . '" alt = "footer-line" width = "740" height = "44" border = "0" usemap = "#Map" />
                        <map name = "Map" id = "Map">
                        <area shape = "rect" coords = "81,7,228,35" href = "tel:' . REGISTED_MOBILE . '" />
                        <area shape = "rect" coords = "273,8,445,35" href = "' . WEBSITE_URL . '" target = "_blank" />
                        <area shape = "rect" coords = "487,7,661,34" href = "mailto:' . INFO_EMAIL . '" />
                        </map></td>
                        </tr>
                        </table>
                        <map name = "Map2Map" id = "Map2Map">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map></td>
                        </tr>
                        <map name = "Map2" id = "Map2">
                        <area shape = "rect" coords = "-2,4,105,37" href = "' . APPLE_STORE_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "127,20,16" href = "' . LINKEDIN_LINK . '/?originalSubdomain=in" target = "_blank" />
                        <area shape = "circle" coords = "165,20,17" href = "' . INSTAGRAM_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "205,21,17" href = "' . FACEBOOK_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "244,19,18" href = "' . TWITTER_LINK . '" target = "_blank" />
                        <area shape = "circle" coords = "282,20,18" href = "' . YOUTUBE_LINK . '" target = "_blank" />
                        <area shape = "rect" coords = "306,5,413,36" href = "' . ANDROID_STORE_LINK . '" target = "_blank" alt = "social-line" />
                        </map>
                        </table>
                        </body>
                        </html>';

                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 15,  "info@tejasloan.com", 'info@tejasloan.com');
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 15,  "", '');
                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminder2Day() {

        $cron_name = "repaymentreminder2day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 2;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                    $email_data = array();
                    $to_email = $customer_data['email'];
                    //$to_email='info@tejasloan.com';
                    $email_subject = "tejasloan.COM | Don't Forget The Payment : $cust_full_name";

                    $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns = "http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                        <title>Payment Reminder 2 Day</title>
                        </head>

                        <body>

                        <table width = "740" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "background:url(' . REMINDER_PAYMENT_REMINDER . ') no-repeat;">
                        <tr>
                        <td valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                        <tr>
                        <td width = "99%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src="' . BRAND_TRANSPARENT_LOGO . '" alt="logo-speedoloan" style="width: 156px;padding-left: 28px;padding-top: 10px;" /></a></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top"><img src = "' . REMINDER_2_DAY . '" alt = "day" width = "170" height = "170" style = "position: relative;top: -54px;margin: 0 auto;left: 42%;" /></td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top">&nbsp;
                        </td>
                        </tr>
                        <tr>
                        <td valign = "top"><img src = "' . REMINDER_LINE . '" alt = "line" width = "10" height = "80" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:35px;"><strong>Dear ' . $cust_full_name . ', </strong></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src = "' . REMINDER_LINE . '" alt = "line" width = "26" height = "25" /></td>
                        </tr>
                        <tr>
                        <td align = "center" valign = "top" style = "font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;">Don\'t forget to make your upcoming payment of <em><strong style="color:#0463a3;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="13" height="17" />' . $repayment_amount . '</strong></em> against <br /> Loan Number <strong style="color:#0463a3;">' . $loan_no . '</strong> is due on <strong style="color:#0463a3;">' . $repayment_date . '</strong>.</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="8" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><span style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">For more details, visit <a href="' . LOAN_REPAY_LINK . '" target="_blank" style="color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> <br />
                                                                                or call us on <a href="tel:' . REGISTED_MOBILE . '" style="color:#0463a3">' . REGISTED_MOBILE . '</a></span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="65" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_SOCIAL_LINK . '" width="411" alt="social-line" height="39" border="0" usemap="#Map2Map" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="10" /><img src="' . REMINDER_FOOTER . '" alt="footer-line" width="740" height="44" border="0" usemap="#Map" />
                                                                            <map name="Map" id="Map">
                                                                                <area shape="rect" coords="81,7,228,35" href="tel:' . REGISTED_MOBILE . '" />
                                                                                <area shape="rect" coords="273,8,445,35" href="' . WEBSITE_URL . '" target="_blank" />
                                                                                <area shape="rect" coords="487,7,661,34" href="mailto:' . INFO_EMAIL . '" />
                                                                            </map></td>
                                                                    </tr>
                                                                </table>
                                                                <map name="Map2Map" id="Map2Map">
                                                                    <area shape="rect" coords="-2,4,105,37" href="' . APPLE_STORE_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="127,20,16" href="' . LINKEDIN_LINK . '/?originalSubdomain=in" target="_blank" />
                                                                    <area shape="circle" coords="165,20,17" href="' . INSTAGRAM_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="205,21,17" href="' . FACEBOOK_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="244,19,18" href="' . TWITTER_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="282,20,18" href="' . YOUTUBE_LINK . '" target="_blank" />
                                                                    <area shape="rect" coords="306,5,413,36" href="' . ANDROID_STORE_LINK . '" target="_blank" alt="social-line" />
                                                                </map></td>
                                                        </tr>
                                                        <map name="Map2" id="Map2">
                                                            <area shape="rect" coords="-2,4,105,37" href="' . APPLE_STORE_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="127,20,16" href="' . LINKEDIN_LINK . '/?originalSubdomain=in" target="_blank" />
                                                            <area shape="circle" coords="165,20,17" href="' . INSTAGRAM_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="205,21,17" href="' . FACEBOOK_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="244,19,18" href="' . TWITTER_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="282,20,18" href="' . YOUTUBE_LINK . '" target="_blank" />
                                                            <area shape="rect" coords="306,5,413,36" href="' . ANDROID_STORE_LINK . '" target="_blank" alt="social-line" />
                                                        </map>
                                                    </table>
                                                </body>
                                            </html>';

                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 16,  "info@tejasloan.com", 'info@tejasloan.com');
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 16,  "", '');
                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminder1Day() {

        $cron_name = "repaymentreminder1day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        //$tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 1;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));

                    $email_data = array();
                    $to_email = $customer_data['email'];
                    //$to_email = 'info@tejasloan.com';
                    $email_subject = "tejasloan.com | Don't Forget The Payment : $cust_full_name";

                    $email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                            <html xmlns="http://www.w3.org/1999/xhtml">
                                                <head>
                                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                    <title>Payment Reminder 1 Day</title>
                                                </head>

                                                <body>

                                                    <table width="740" border="0" align="center" cellpadding="0" cellspacing="0" style="background:url(' . REMINDER_PAYMENT_REMINDER . ') no-repeat;">
                                                        <tr>
                                                            <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td width="99%" valign="top"><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . BRAND_TRANSPARENT_LOGO . '" alt="logo-speedoloan" style="width: 156px;padding-left: 28px;padding-top: 10px;" /></a></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top"  style="text-align:center"><img src="' . REMINDER_1_DAY . '" alt="day" width="170" height="170" style="position: relative;top: -54px;margin: -70px auto 0 auto;left: 42%;" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td valign="top"><img src="' . REMINDER_LINE . '" alt="line" width="10" height="80" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:35px;"><strong>Dear ' . $cust_full_name . ',</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="25" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="25" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;">Don\'t forget to make your upcoming payment of <em><strong style="color:#0463a3;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="13" height="17" />' . $repayment_amount . '</strong></em> against <br /> Loan Number <strong style="color:#0463a3;">' . $loan_no . '</strong> is due on <strong style="color:#0463a3;">' . $repayment_date . '</strong>.</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="8" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><span style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">For more details, visit <a href="' . LOAN_REPAY_LINK . '" target="_blank" style="color:#0463a3"> ' . LOAN_REPAY_LINK . ' </a> <br />
                                                                                or call us on <a href="tel:' . REGISTED_MOBILE . '" style="color:#0463a3">' . REGISTED_MOBILE . '</a></span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="65" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_SOCIAL_LINK . '" width="411" alt="social-line" height="39" border="0" usemap="#Map2Map" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px;"><img src="' . REMINDER_LINE . '" alt="line" width="26" height="10" /><img src="' . REMINDER_FOOTER . '" alt="footer-line" width="740" height="44" border="0" usemap="#Map" />
                                                                            <map name="Map" id="Map">
                                                                                <area shape="rect" coords="81,7,228,35" href="tel:' . REGISTED_MOBILE . '" />
                                                                                <area shape="rect" coords="273,8,445,35" href="' . WEBSITE_URL . '" target="_blank" />
                                                                                <area shape="rect" coords="487,7,661,34" href="mailto:' . INFO_EMAIL . '" />
                                                                            </map></td>
                                                                    </tr>
                                                                </table>
                                                                <map name="Map2Map" id="Map2Map">
                                                                    <area shape="rect" coords="-2,4,105,37" href="' . APPLE_STORE_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="127,20,16" href="' . LINKEDIN_LINK . '/?originalSubdomain=in" target="_blank" />
                                                                    <area shape="circle" coords="165,20,17" href="' . INSTAGRAM_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="205,21,17" href="' . FACEBOOK_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="244,19,18" href="' . TWITTER_LINK . '" target="_blank" />
                                                                    <area shape="circle" coords="282,20,18" href="' . YOUTUBE_LINK . '" target="_blank" />
                                                                    <area shape="rect" coords="306,5,413,36" href="' . ANDROID_STORE_LINK . '" target="_blank" alt="social-line" />
                                                                </map></td>
                                                        </tr>
                                                        <map name="Map2" id="Map2">
                                                            <area shape="rect" coords="-2,4,105,37" href="' . APPLE_STORE_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="127,20,16" href="' . LINKEDIN_LINK . '/?originalSubdomain=in" target="_blank" />
                                                            <area shape="circle" coords="165,20,17" href="' . INSTAGRAM_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="205,21,17" href="' . FACEBOOK_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="244,19,18" href="' . TWITTER_LINK . '" target="_blank" />
                                                            <area shape="circle" coords="282,20,18" href="' . YOUTUBE_LINK . '" target="_blank" />
                                                            <area shape="rect" coords="306,5,413,36" href="' . ANDROID_STORE_LINK . '" target="_blank" alt="social-line" />
                                                        </map>
                                                    </table>
                                                </body>
                                            </html>';

                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 17,  "info@tejasloan.com", 'info@tejasloan.com');
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 17,  "", '');
                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminder0Day() {

        $cron_name = "repaymentreminder0day";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        //$tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $day_counter = 0;

        $tempDetails = $this->EmailModel->getAllRepaymentReminderEmails(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {


                if (!empty($customer_data['email'])) {

                    $lead_id = $customer_data['lead_id'];
                    $dpd = 1;

                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_amount = number_format($customer_data['repayment_amount']);
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));
                    if ($dpd == 0) {
                        $style = 'background: linear-gradient(90deg, #E53935, #FF6F00); color: #fff; padding: 20px; text-align: center;';
                        $repaymentWarning = '<p style="line-height: 1.8; margin: 10px 0;color: red !important;font-size: 12px;">Please note that today is your repayment date, and failure to make the payment by the end of the day will result in the addition of penalty interest to your outstanding amount.</p>';
                    } else {
                        $style = 'background: linear-gradient(90deg, #266624, #19BF02); color: #fff; padding: 20px; text-align: center;';
                    }


                    $email_data = array();
                    //$to_email = $customer_data['email'];
                    $to_email = 'info@tejasloan.com';
                    $email_subject = "Action Required: Your Payment is Due Tomorrow | " . BRAND_NAME . " : " . ucwords($cust_full_name) . " | Loan No: " . $loan_no;

                    $email_message = '<!DOCTYPE html>
                                        <html lang="en">
                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>Repayment Reminder</title>
                                            <style>
                                                @media (max-width: 600px) {
                                                    .header,
                                                    .content,
                                                    .footer {
                                                        padding: 15px;
                                                    }
                                                    h1 {
                                                        font-size: 20px;
                                                    }
                                                    p,
                                                    td {
                                                        font-size: 14px;
                                                    }
                                                    .btn {
                                                        font-size: 14px;
                                                        padding: 10px 20px;
                                                    }
                                                }
                                            </style>
                                        </head>
                                        <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; color: #333;">
                                            <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                                                <div style="' . $style . '">
                                                    <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" style="width: 30%;">
                                                </div>
                                                <div style="padding: 20px;">
                                                    <div style="text-align: center; margin-bottom: 20px;">
                                                        <img src="https://sot-website.s3.ap-south-1.amazonaws.com/emailer/payment_remider.png" alt="Payment Reminder Banner" style="width: 100%; max-width: 560px; border-radius: 8px;">
                                                    </div>
                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Dear <strong>' . htmlspecialchars(ucwords($cust_full_name)) . '</strong>,</p>

                                                    <p style="line-height: 1.8; margin: 10px 0;">This is a gentle reminder regarding your upcoming repayment for your loan with <strong>' . BRAND_NAME . '</strong>. Below are the details:</p>

                                                    <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Loan Account Number:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars($loan_no) . '</strong></td>
                                                    </tr>
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Due Date:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars($repayment_date) . '</strong></td>
                                                    </tr>
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Amount Due:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px; color: #E53935;"><strong>₹' . htmlspecialchars($repayment_amount) . '</strong></td>
                                                    </tr>
                                                    </table>

                                                    ' . $repaymentWarning . '

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px; text-align: center;"><strong>Scan the QR code below to make your payment easily:</strong></p>

                                                    <div style="text-align: center; margin: 20px 0;">
                                                        <img src="https://quickchart.io/qr?size=300&text=upi%3A%2F%2Fpay%3Fpa%3DKASAR3%40icici%26pn%3DKasar+Credit%26tr%3DEZV2025011512410420291896%26am%3D76390%26cu%3DINR%26mc%3D6012" alt="QR Code for Payment" style="max-width: 200px; border: 2px solid #ddd; border-radius: 8px;">
                                                    </div>

                                                    <p style="line-height: 1.8; margin: 10px 0;">Alternatively, you can use the link below to make your payment:</p>

                                                    <div style="text-align: center; margin: 20px 0;">
                                                        <a href="[Payment Link]" class="btn" style="display: inline-block; background: #4CAF50; color: #fff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px;">Make Payment</a>
                                                    </div>

                                                    <p style="line-height: 1.8; margin: 10px 0;">If you have already made the payment, please disregard this message. For any assistance, contact our support team at <a href="mailto:' . CARE_EMAIL . '" style="color: #4CAF50; text-decoration: none;">' . CARE_EMAIL . '</a> or call us at <strong><a href="tel:' . REGISTED_MOBILE . '" style="color: #4CAF50; text-decoration: none;">' . REGISTED_MOBILE . '</a></strong>.</p>

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Thank you for choosing ' . BRAND_NAME . '.</p>

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Best regards,<br><strong>The ' . BRAND_NAME . ' Team</strong></p>
                                                </div>
                                                <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px;">
                                                    <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                                    <p style="margin: 0;">
                                                        <a href="' . WEBSITE_URL . "privacypolicy" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                                        <a href="' . WEBSITE_URL . "termsandconditions" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                                        <a href="' . WEBSITE_URL . "contact" . '" target="_blank"  style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                                    </p>
                                                    <div style="margin-top: 15px;">
                                                        <tr>
                                                            <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                                                                <p style="font-size: 14px; color: #777; margin: 0 0 10px;">
                                                                    Follow us on:
                                                                </p>
                                                                <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                    <img src="' . FACEBOOK_ICON . '" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                </a>
                                                                <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                    <img src="' . TWITTER_ICON . '" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                </a>
                                                                <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                    <img src="' . LINKEDIN_ICON . '" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                </a>
                                                                <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                    <img src="' . INSTAGRAM_ICON . '" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                </a>
                                                                <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                                    <img src="' . YOUTUBE_ICON . '" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </div>
                                                </div>
                                            </div>
                                        </body>

                                        </html>';
                    echo $email_message;
                    die();
                    //$return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 18,  "info@tejasloan.com", 'info@tejasloan.com');
                    $return_array = $this->middlewareEmail($to_email, $email_subject, $email_message, "", 18,  "", '');

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function feedbackForCloseLoanEmailer() {

        $time_close = intval(date("Hi"));

        if ($time_close > 1441) {
            die;
        }

        if (true) {

            $tempDetails = $this->EmailModel->getAllCloseLoanForFeedbackEmails();

            $start_datetime = date("d-m-Y H:i:s");

            $email_counter = array('email_sent' => 0, 'email_failed' => 0);

            $email_sent_array = array();

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (empty($email_sent_array[$customer_data['user_email_id']]) && !empty($customer_data['user_email_id'])) {

                        $feedback_url = FEEDBACK_WEB_PATH . $this->encrypt->encode($customer_data['lead_id']);

                        $customer_name = $customer_data['first_name'];

                        $email_data = array();

                        $email_data['email'] = $customer_data['user_email_id'];
                        //                        $email_data['email'] = "info@tejasloan.com";
                        $email_data['subject'] = "LOANWALLE.COM | FEEDBACK FORM";
                        $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                    <html xmlns="http://www.w3.org/1999/xhtml">
                                                        <head>
                                                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                            <title>Customer Feedback</title>
                                                        </head>
                                                        <body>
                                                            <table width="550" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px 10px 2px 10px; border:solid 2px #0363a3; font-family:Arial, Helvetica, sans-serif;border-radius:3px;">
                                                                <tr>
                                                                    <td align="left"><table width="100%" border="0" style="height:270px; padding:8px 0px; background:url(https://www.loanwalle.com/public/emailimages/feedback/images/header2.jpg);">
                                                                            <tr>
                                                                                <td valign="top"><a href="https://www.loanwalle.com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/loanwalle-logo.gif" alt="loanwalle-logo" style="margin-top:-18px; width:229px;" /></a></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="8" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong style="color:#0463A3;">Dear ' . $customer_name . ',</strong></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="15" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Greetings from <span style="color:#0463A3; font-size:16px;"><strong>Loanwalle.com</strong></span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="15" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style="margin:0px;color: #000;line-height: 25px;border-radius: 3px;">Please take few minutes to give us feedback about our service by filling in this short Customer Feedback Form.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="8" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style="margin:0px;color: #000;line-height: 25px;border-radius: 3px;">We are interested in your honest opinion. Your survey responses will remain confidential and will only by viewed in aggregate with answers from other respondents.<br/>
                                                                        </p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="8" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" style="text-align:center;"><a href="' . $feedback_url . '" target="_blank" style="background:#0463a3;border-radius: 3px;padding: 8px 30px;color: #fff;text-decoration: blink;font-weight: bold;">Click Here</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="8" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="line-height:25px;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="left"><strong style="color:#0463A3; font-size:18px;">Thank you.<br />
                                                                        </strong></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="left"><strong style="color:#000; font-size:15px;">Customer Experience Team</strong> </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="left"><strong style="color:#0463A3; font-size:18px;"><em style="font-size:16px; font-style:normal;">Loanwalle.com </em></strong></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><img src="https://www.loanwalle.com/public/emailimages/feedback/images/line.png" alt="line" width="34" height="8" /></td>
                                                                </tr>

                                                                <tr>
                                                                    <td align="left" style="text-align:left; color: #000;line-height: 25px;">If you are unable to click on the above button. Please <a href="' . $feedback_url . '" target="_blank" style="color:#0463a3; text-decoration:underline;">click here</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3" align="center" bgcolor="#0463A3" style="padding: 7px 0px 7px 0px;color: #fff;font-size: 16px;border-radius: 3px;"><a href="tel:+91-9999999305" style="color:#fff; text-decoration:blink;"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/phone-icon.png" width="20" height="20" alt="phone"  style="position: relative;top: 4px;"/> +91-9999999-341</a> | <a href="https://www.loanwalle.com/" target="_blank" style="color:#fff; text-decoration:blink;"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/web-icon.png" width="20" height="20" alt="phone"  style="position: relative;top: 4px;"/> www.loanwalle.com</a> | <a href="mailto:info@loanwalle.com" style="color:#fff; text-decoration:blink;"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/email-icon.png" width="20" height="20" alt="phone"  style="position: relative;top: 4px;"/> info@loanwalle.com</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3" align="center" bgcolor="#FFFFFF" style="padding:10px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"><a href="https://in.linkedin.com/company/loan-walle-com" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/linkdin.png" alt="linkdin" width="30" height="30" /></a> <a href="https://www.instagram.com/loanwalle_com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/instagram.png" alt="instagram" width="30" height="30" /></a> <a href="https://www.facebook.com/loanwalleindia" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/facebook.png" alt="facebook" width="30" height="30" /></a> <a href="https://twitter.com/Loanwalle_com" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/twitter.png" alt="twitter" width="30" height="30" /></a> <a href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank"> <img src="https://www.loanwalle.com/public/emailimages/feedback/images/you-tube.png" alt="youtube" width="30" height="30" /><span style="padding:2px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"></span></a><span style="padding:2px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"><a href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/goolge-play.png" alt="google-play" width="100" height="30" /></a> <a href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/feedback/images/app-store.png" alt="app-store" width="100" height="30" /></a></span></td>
                                                                </tr>
                                                            </table>
                                                        </body>
                                                    </html>';

                        $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 19);

                        if ($return_array['status'] == 1) {
                            $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                            $email_sent_array[$customer_data['user_email_id']] = $customer_data['user_email_id'];
                        } else {
                            $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                        }
                    }
                }

                $email_data = array();
                $email_data['email'] = "info@tejasloan.com";
                $email_data['subject'] = "PROD-Festive Existing Customer Email Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-Festive Existing Customer - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function rakshabandhanEmailer() {

        $cron_name = "rakshabandhanemailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }
        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllLoanCustomer();

        $email_counter = array('email_sent' => 0, 'email_failed' => 0);
        $start_datetime = date("d-m-Y H:i:s");

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //                    $email_data['email'] = "Rohit.AGRAWAL@LOANWALLE.COM";
                    //                    $email_data['email'] = "sushil.kumar@loanwalle.com";
                    $email_data['subject'] = "Loanwalle.com Celebrate Raksha Bandhan";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Raksha Bandhan</title>
                                                    </head>
                                                    <body>

                                                        <table width="600" height="597" border="0" align="center" cellpadding="0" cellspacing="0" style="background:url(https://www.loanwalle.com/public/emailimages/festival/Raksha_Bandhan/images/raksha_bandhan_background.jpg);">
                                                            <tr>
                                                                <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">

                                                                        <tr>
                                                                            <td><a href="https://www.loanwalle.com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/festival/Raksha_Bandhan/images/logo.gif" width="200" height="55" alt="Loanwalle Logo" /></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><img src="https://www.loanwalle.com/public/emailimages/festival/Raksha_Bandhan/images/line_hr.png" width="14" height="440" alt="line_hr" /></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center"><img src="https://www.loanwalle.com/public/emailimages/festival/Raksha_Bandhan/images/footer-link.png" alt="Loanwalle Footer" width="425" height="68" border="0" usemap="#Map" /></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>

                                                        <map name="Map" id="Map">
                                                            <area shape="rect" coords="22,9,136,27" href="tel:+919999999341"/>
                                                            <area shape="rect" coords="154,10,272,27" href="https://www.loanwalle.com" target="_blank"/>
                                                            <area shape="rect" coords="292,10,409,27" href="mailto:info@loanwalle.com"/>
                                                            <area shape="circle" coords="221,54,10" href="https://www.facebook.com/loanwalleindia" target="_blank"/>
                                                            <area shape="circle" coords="242,53,10" href="https://twitter.com/Loanwalle_com" target="_blank"/>
                                                            <area shape="circle" coords="197,53,10" href="https://www.instagram.com/loanwalle_com/" target="_blank"/>
                                                            <area shape="circle" coords="266,54,11" href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank"/>
                                                            <area shape="circle" coords="174,54,11" href="https://in.linkedin.com/company/loan-walle-com" target="_blank"/>
                                                            <area shape="rect" coords="293,44,351,63" href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank"/>
                                                            <area shape="rect" coords="86,43,147,63" href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank"/>
                                                        </map>

                                                    </body>
                                                </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 23);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                    //                    die("Rohit");
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD- $cron_name Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function independanceDayEmailer() {

        $cron_name = "independancedayemailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllLoanCustomer();

        $email_counter = array('email_sent' => 0, 'email_failed' => 0);
        $start_datetime = date("d-m-Y H:i:s");

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //                    $email_data['email'] = "info@tejasloan.com";
                    //                    $email_data['email'] = "tech.team@loanwalle.com";
                    //                    $email_data['email'] = "sushil.kumar@loanwalle.com";
                    $email_data['subject'] = "Loanwalle.com | Happy Independence Day";

                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Happy Independence Day</title>
                                                    </head>

                                                    <body>
                                                        <table width="600" height="610" border="1" bordercolor="#0463a3" align="center" cellpadding="0" cellspacing="0" style="background:url(https://www.loanwalle.com/public/emailimages/festival/independence_day/images/independence_day_background.jpg);">
                                                            <tr>
                                                                <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">

                                                                        <tr>
                                                                            <td><a href="https://www.loanwalle.com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/festival/independence_day/images/logo.gif" width="200" height="50" /></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><img src="https://www.loanwalle.com/public/emailimages/festival/independence_day/images/line_hr.png" alt="line_hr" width="14" height="480" /></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center"><img src="https://www.loanwalle.com/public/emailimages/festival/independence_day/images/footer-link.png" alt="footer_link" width="425" height="68" border="0" usemap="#Map" /></td>
                                                                        </tr>
                                                                    </table></td>
                                                            </tr>
                                                        </table>
                                                        <map name="Map" id="Map">
                                                            <area shape="rect" coords="22,9,136,27" href="tel:+919999999-341"/>
                                                            <area shape="rect" coords="154,10,272,27" href="https://www.loanwalle.com" target="_blank"/>
                                                            <area shape="rect" coords="292,10,409,27" href="mailto:info@loanwalle.com"/>
                                                            <area shape="circle" coords="221,54,10" href="https://www.facebook.com/loanwalleindia" target="_blank" />
                                                            <area shape="circle" coords="242,53,10" href="https://twitter.com/Loanwalle_com" target="_blank" />
                                                            <area shape="circle" coords="197,53,10" href="https://www.instagram.com/loanwalle_com/" target="_blank" />
                                                            <area shape="circle" coords="266,54,11" href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank" />
                                                            <area shape="circle" coords="174,54,11" href="https://in.linkedin.com/company/loan-walle-com" target="_blank" />
                                                            <area shape="rect" coords="293,44,351,63" href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank" />
                                                            <area shape="rect" coords="86,43,147,63" href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank" />
                                                        </map>

                                                    </body>
                                                </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 24);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD- $cron_name Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function krishanjanmaastmiEmailer() {

        $cron_name = "krishanjanmaastmiemailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllLoanCustomer();

        $email_counter = array('email_sent' => 0, 'email_failed' => 0);
        $start_datetime = date("d-m-Y H:i:s");

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //                    $email_data['email'] = "info@tejasloan.com";
                    //                    $email_data['email'] = "tech.team@loanwalle.com";
                    //                    $email_data['email'] = "sushil.kumar@loanwalle.com";
                    $email_data['subject'] = "Loanwalle.com | Happy Krishna Janmashtami";

                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Krishna Janmashtami</title>
                                                    </head>
                                                    <body>

                                                        <table width="600" height="597" border="0" align="center" cellpadding="0" cellspacing="0" style="background:url(https://www.loanwalle.com/public/emailimages/festival/Janmashtami/images/Janmashtami_background.jpg);">
                                                            <tr>
                                                                <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">

                                                                        <tr>
                                                                            <td><a href="https://www.loanwalle.com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/festival/Janmashtami/images/logo.gif" width="200" height="50" alt="Janmashtami" /></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><img src="https://www.loanwalle.com/public/emailimages/festival/Janmashtami/images/line_hr.png" width="14" height="480" alt="line_hr" /></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center"><img src="https://www.loanwalle.com/public/emailimages/festival/Janmashtami/images/footer-link.png" alt="footer_link" width="425" height="62" border="0" usemap="#Map" /></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>

                                                        <map name="Map" id="Map">
                                                            <area shape="rect" coords="44,7,149,23" href="tel:+919999999341"/>
                                                            <area shape="rect" coords="151,7,263,25" href="https://www.loanwalle.com" target="_blank"/>
                                                            <area shape="rect" coords="266,8,383,25" href="mailto:info@loanwalle.com"/>
                                                            <area shape="circle" coords="219,43,10" href="https://www.facebook.com/loanwalleindia" target="_blank"/>
                                                            <area shape="circle" coords="238,43,10" href="https://twitter.com/Loanwalle_com" target="_blank"/>
                                                            <area shape="circle" coords="200,42,10" href="https://www.instagram.com/loanwalle_com/" target="_blank"/>
                                                            <area shape="circle" coords="258,44,11" href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank"/>
                                                            <area shape="circle" coords="179,42,11" href="https://in.linkedin.com/company/loan-walle-com" target="_blank"/>
                                                            <area shape="rect" coords="277,33,335,52" href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank"/>
                                                            <area shape="rect" coords="99,32,160,52" href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank"/>
                                                        </map>

                                                    </body>
                                                </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 25);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                    //                    die("Rohit");
                }
            }

            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD- $cron_name Counter - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repeatHighTicketSizeCustomerEmailer() { //Normal Email Template
        $cron_name = "repeathighticketsizecustomeremailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));
        //
        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);
        //
        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllHighTicketSizeCustomer();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        $campaign_name = 'REPEATCUSTEMAIL' . date("Ymd");

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //                    $email_data['email'] = 'info@tejasloan.com';

                    $email_data['subject'] = "LOANWALLE.COM Offers - Instant Personal Loan To Most Valued Customers";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Loanwalle.com Offers - Instant Personal Loan In Just 30 Minutes*</title>
                                                    </head>

                                                    <body>
                                                        <table width="667" border="0" align="center" cellpadding="0" cellspacing="0" style="background:url(https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-email-marketing-back.png); font-family:Arial, Helvetica, sans-serif;">
                                                            <tr>
                                                                <td><a href="https://www.loanwalle.com/" target="_blank"><img src="https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-logo.png" alt="loanwalle-logo" width="250" height="60" /></a></td>
                                                            </tr>
                                                            <tr>
                                                                <td valign="top"><img src="https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-email-marketing-2.png" alt="loanwalle-email-marketing" width="678" height="520" /></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center"><img src="https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-email-marketing-3.png" alt="loanwalle-email-marketing" width="678" height="103" /></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center" style="color:#fff; font-size:15px; font-weight:500;">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center" style="color:#fff; font-size:15px; font-weight:500;"><a href="https://www.loanwalle.com/apply-now?utm_source=' . $campaign_name . '" style="background: #fff;border-radius: 50px;padding: 10px 30px;color: #034369;font-weight: bold;text-decoration: blink;border: solid 2px #034369;">Apply Now</a></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center" style="color:#fff; font-size:15px; font-weight:500;"><img src="https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-email-line.png" alt="line" width="25" height="20" /></td>
                                                            </tr>

                                                            <tr>
                                                                <td align="center" style="color:#fff; font-size:17px; font-weight:500;"><img src="https://www.loanwalle.com/public/emailimages/personal-jan/images/loanwalle-email-line.png" alt="line" width="25" height="10" /></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="center"><img src="https://www.loanwalle.com/public/emailimages/festival/Janmashtami/images/footer-link.png" alt="footer_link" width="425" height="62" border="0" usemap="#Map" /></td>
                                                            </tr>

                                                        </table>
                                                        <map name="Map" id="Map">
                                                            <area shape="rect" coords="44,7,149,23" href="tel:+919999999341"/>
                                                            <area shape="rect" coords="151,7,263,25" href="https://www.loanwalle.com" target="_blank"/>
                                                            <area shape="rect" coords="266,8,383,25" href="mailto:info@loanwalle.com"/>
                                                            <area shape="circle" coords="219,43,10" href="https://www.facebook.com/loanwalleindia" target="_blank"/>
                                                            <area shape="circle" coords="238,43,10" href="https://twitter.com/Loanwalle_com" target="_blank"/>
                                                            <area shape="circle" coords="200,42,10" href="https://www.instagram.com/loanwalle_com/" target="_blank"/>
                                                            <area shape="circle" coords="258,44,11" href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank"/>
                                                            <area shape="circle" coords="179,42,11" href="https://in.linkedin.com/company/loan-walle-com" target="_blank"/>
                                                            <area shape="rect" coords="277,33,335,52" href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank"/>
                                                            <area shape="rect" coords="99,32,160,52" href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank"/>
                                                        </map>
                                                    </body>
                                                </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 26);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                    //                    die("Rohit");
                }
            }
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-$cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'] . " <br/> Campaing Name : " . $campaign_name;

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function diwaliCustomerEmailer() { //Normal Email Template
        $cron_name = "diwalicustomeremailer";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllDiwaliCustomer();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        $campaign_name = 'DIWALICUSTEMAIL' . date("Ym") . "22";

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['user_email_id'])) {
                    $email_data = array();
                    $email_data['email'] = $customer_data['user_email_id'];
                    //                    $email_data['email'] = 'info@tejasloan.com';

                    $email_data['subject'] = "Surprise is here! It’s for Diwali Celebration from LOANWALLE.COM";
                    $email_data['message'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                                <html xmlns="http://www.w3.org/1999/xhtml">
                                                    <head>
                                                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                        <title>Diwali Celebration 2022</title>
                                                    </head>

                                                    <body>
                                                        <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td><img src="https://www.loanwalle.com/public/emailimages/diwali-mailer/diwali-email.jpg" alt="diwali" width="700" height="1161" border="0" usemap="#Map" /></td>
                                                            </tr>
                                                        </table>

                                                        <map name="Map" id="Map">
                                                            <area shape="rect" coords="2,4,279,81" href="https://www.loanwalle.com/" target="_blank" />
                                                            <area shape="rect" coords="277,996,417,1033" href="https://www.loanwalle.com/apply-now?utm_source=' . $campaign_name . '" target="_blank" />
                                                            <area shape="rect" coords="129,1066,259,1086" href="tel:+919999999341" />
                                                            <area shape="rect" coords="280,1067,410,1088" href="https://www.loanwalle.com/" target="_blank" />
                                                            <area shape="rect" coords="428,1066,568,1085" href="mailto:info@loanwalle.com" />
                                                            <area shape="rect" coords="182,1097,253,1122" href="https://apps.apple.com/in/app/loanwalle-com/id1614454811" target="_blank" />
                                                            <area shape="rect" coords="440,1098,512,1123" href="https://play.google.com/store/apps/details?id=com.loanwalle.personalloan" target="_blank" />
                                                            <area shape="circle" coords="299,1110,10" href="https://in.linkedin.com/company/loan-walle-com" target="_blank" />
                                                            <area shape="circle" coords="323,1110,10" href="https://www.instagram.com/loanwalle_com/" target="_blank" />
                                                            <area shape="circle" coords="346,1111,10" href="https://www.facebook.com/loanwalleindia" target="_blank" />
                                                            <area shape="circle" coords="370,1110,9" href="https://twitter.com/Loanwalle_com" target="_blank" />
                                                            <area shape="circle" coords="393,1109,10" href="https://www.youtube.com/channel/UC0XGjHs-oPeZxa1sqeE_q_w?view_as=subscriber" target="_blank" />
                                                        </map>
                                                    </body>
                                                </html>';

                    $return_array = $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 27);

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                    //                    die("Rohit");
                }
            }
            $email_data = array();
            $email_data['email'] = "info@tejasloan.com";
            $email_data['subject'] = "PROD-$cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'] . " <br/> Campaing Name : " . $campaign_name;

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        //
        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 0, $cc_email = "", $reply_to = "") {
        echo $message;
        //die();
        $status = 0;
        $error = "";
        $provider_name = "";
        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@tejasloan.com";

            if ($email_type_id == 10) {
                $from_email = "info@tejasloan.com";
                $provider_name = "MAILGUN";
            } else if ($email_type_id == 12) {
                $from_email = "info@tejasloan.com";
                $provider_name = "MAILGUN";
            }

            //
            if (in_array($email_type_id, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 11))) {
                $provider_name = "MAILGUN";

                $template = "";
                if ($email_type_id == 1) {
                    $template = "birthdayemailer";
                } else if (in_array($email_type_id, array(2))) {
                    $template = "festiveofferforcloseloan";
                } else if (in_array($email_type_id, array(3, 4))) {
                    $template = "festiveoffernewcustomer";
                } else if (in_array($email_type_id, array(5))) {
                    $template = "loanwallefreshloanjan2022";
                } else if (in_array($email_type_id, array(6))) {
                    $template = "freshloanloharijan22reject";
                } else if (in_array($email_type_id, array(7))) {
                    $template = "republicday26012022";
                } else if (in_array($email_type_id, array(8))) {
                    $template = "repayloanemailer";
                } else if (in_array($email_type_id, array(9))) {
                    $template = "valentineweekspecial";
                } else if (in_array($email_type_id, array(11))) {
                    $template = "happyholi";
                }

                $apiUrl = "https://api.mailgun.net/v3/loanwalle.com/messages";

                $request_array = array(
                    "from" => $from_email,
                    "to" => $to_email,
                    "subject" => $subject,
                    "template" => $template
                );

                $apiHeaders = array(
                    "Authorization: Basic " . base64_encode("api:ada7804cae9740db5c62abd5b2ae5d62-8ed21946-b133e0ab"),
                    "Content-Type:multipart/form-data",
                );
                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request_array);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                $response = curl_exec($curl);

                $return_array = json_decode($response, true);

                if ($return_array['message'] == "Queued. Thank you.") {
                    $status = 1;
                } else {
                    $status = 2;
                    $error = $return_array['message'];
                }
            } else {
                $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
                $status = $return_array['status'];
                $error = $return_array['error'];
            }

            $insert_log_array = array();
            $insert_log_array['email_provider'] = $provider_name;
            $insert_log_array['email_type_id'] = $email_type_id;
            $insert_log_array['email_address'] = $email;
            $insert_log_array['email_content'] = addslashes($message);
            $insert_log_array['email_api_status_id'] = $status;
            $insert_log_array['email_errors'] = $error;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            $this->EmailModel->emaillog_insert($insert_log_array);

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }

    public function notificationSendMailAndWhatsapp() {

        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

        $cron_name = "notificationSendMailAndWhatsapp";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $start_datetime = date("d-m-Y H:i:s");

        $campaign_name = 'EMAIL' . date("Ymd");

        $whatsapp_template_name = 'reloan_approch';
        $api_provider_id = 'Whistle';
        $html_content = "";
        $subject = "Ready for Another Loan? Get It in Just 10 Minutes! 😊";

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();

        $tempDetails = $this->EmailModel->get_closed_loan_for_reloan();
        $loanDetails = $tempDetails['status'] == 1 ? $tempDetails['loan'] : [];

        if (!empty($loanDetails)) {
            foreach ($loanDetails as $i => $val_data) {

                $lead_id = $val_data['lead_id'];
                $email = $val_data['email'];
                $name = $val_data['first_name'];

                $request_array = [
                    "template_name" => $whatsapp_template_name,
                    "api_provider_id" => $api_provider_id
                ];

                $returnWhatsapp = $CommonComponent->call_whatsapp_api(1, $lead_id, $request_array);

                $html_content = '<!DOCTYPE html>
                                        <html lang="en">
                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>Quick Loan Offer</title>
                                            <style>
                                                @media only screen and (max-width: 600px) {
                                                    .container {
                                                        width: 100% !important;
                                                        padding: 10px !important;
                                                    }
                                                    .content {
                                                        padding: 10px !important;
                                                    }
                                                    .cta-button {
                                                        padding: 12px 20px !important;
                                                        font-size: 16px !important;
                                                    }
                                                }
                                            </style>
                                        </head>
                                        <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; color: #333;">

                                            <div class="container" style="width: 100%; max-width: 600px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                                <div style="text-align: center; padding: 20px; background-color: #007bff; color: #fff; border-radius: 8px 8px 0 0;">
                                                    <!-- Brand Logo -->
                                                    <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" alt="Brand Logo" style="max-width: 150px; height: auto;">
                                                    <h1 style="font-size: 24px; margin: 0;">Dear ' . $name . ',</h1>
                                                    <p style="margin: 5px;">Looking for a quick and hassle-free loan? We\'re here to help! 😊</p>
                                                </div>

                                                <div class="content" style="padding: 20px; font-size: 16px; line-height: 1.5;">
                                                    <p>With your trusted loan partner, getting a loan is easier than ever. Apply now and have the amount directly in your bank account in just 10 minutes!</p>

                                                    <h3 style="color: #007bff; font-size: 20px; margin-bottom: 15px;">Why choose us?</h3>
                                                    <ul>
                                                        <li style="margin: 10px 0; font-size: 16px;">✅ 100% Online Process - Apply anytime, anywhere.</li>
                                                        <li style="margin: 10px 0; font-size: 16px;">✅ Quick Loan Disbursal - Get funds in your account in just 10 minutes.</li>
                                                        <li style="margin: 10px 0; font-size: 16px;">✅ Direct Bank Transfer - No hassle, no delays.</li>
                                                        <li style="margin: 10px 0; font-size: 16px;">✅ No Collateral Required - Borrow with ease.</li>
                                                    </ul>

                                                    <div style="text-align: center; margin-top: 30px;">
                                                        <a href="' . WEBSITE_URL . 'apply-now?utm_source=EMAIL&utm_campaign=' . $campaign_name . '" style="padding: 12px 25px; background-color: #28a745; color: #fff; font-size: 18px; text-decoration: none; border-radius: 5px; display: inline-block; text-align: center;">👉 Apply Now</a>
                                                    </div>
                                                </div>

                                                <div style="text-align: center; font-size: 12px; color: #777; margin-top: 30px;">
                                                    <p>Your financial emergency is our priority.</p>
                                                </div>
                                            </div>

                                        </body>
                                        </html>';

                common_send_email($email, $subject, $html_content, "", "", "", "", "", "", "");
            }
        }

        $message = "<br/><br/>Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");
        $message .= "<br/><br/>Total Records: " . count($loanDetails);
        $message .= "<br/><br/>" . json_encode($loanDetails);
        $subject = "CRON Notification Closed Loan | Start Time: " . $start_datetime . " | End Time: " . date("d-m-Y H:i:s");

        common_send_email($this->notification_mail, $subject, $message, "", "", "", "", "", "", "");

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function repaymentReminderDay() {

        $cron_name = "repaymentreminderday";
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->EmailModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->EmailModel->insert_cron_logs($cron_name);

        $tempDetails = $this->EmailModel->getAllRepaymentReminderData();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['email'])) {
                    $lead_id = $customer_data['lead_id'];

                    $repayment_amount = $customer_data['total_due'];

                    $return_array = $CommonComponent->call_qrcode_api($lead_id, array('amount' => $repayment_amount));
                    $email_subject = "";
                    $reminder_link = '';
                    $style = '';
                    $repaymentWarning = '';

                    $encData = base64_encode(json_encode(array('lead_id' => $lead_id, 'amount' => $repayment_amount)));
                    $repaymentLink = "https://payments.tejasloan.com/?encId=" . $encData;

                    $qrCodeUrl = $return_array['qrCodeUrl'];
                    $dpd = $customer_data['dpd'];
                    $cust_full_name = ucwords(strtolower($customer_data['cust_full_name']));
                    $loan_no = $customer_data['loan_no'];
                    $repayment_date = date('d-m-Y', strtotime($customer_data['repayment_date']));
                    $repayment_amount = number_format($customer_data['total_due']);

                    if ($dpd == 0) {
                        $email_subject = "Urgent: Repayment Due Today - Avoid Penalty Charges | " . BRAND_NAME . " : " . ucwords($cust_full_name);
                        $reminder_link = 'last_payment_remider.png';
                        $style = 'background: linear-gradient(90deg, #E53935, #FF6F00); color: #fff; padding: 20px; text-align: center;';
                        $repaymentWarning = '<p style="line-height: 1.8; margin: 10px 0;color: red !important;font-size: 12px;">Please note that today is your repayment date, and failure to make the payment by the end of the day will result in the addition of penalty interest.</p>';
                    } else {
                        $email_subject = "Action Required: Your Payment is Due on " . $repayment_date . " | " . BRAND_NAME . " : " . ucwords($cust_full_name);
                        $reminder_link = 'payment_remider.png';
                        $style = 'background: linear-gradient(90deg, #266624, #19BF02); color: #fff; padding: 20px; text-align: center;';
                        $repaymentWarning = '<p style="line-height: 1.8; margin: 10px 0;color: green !important;font-size: 12px;">Please note that your upcoming payment is due soon. Ensure the payment is made on or before the due date to avoid any penalty interest or late fees.</p>';
                    }

                    $email_data = array();
                    $to_email = $customer_data['email'];

                    $email_message = '<!DOCTYPE html>
                                        <html lang="en">
                                        <head>
                                            <meta charset="UTF-8">
                                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>Repayment Reminder</title>
                                            <style>
                                                @media (max-width: 600px) {
                                                    .header,
                                                    .content,
                                                    .footer {
                                                        padding: 15px;
                                                    }
                                                    h1 {
                                                        font-size: 20px;
                                                    }
                                                    p,
                                                    td {
                                                        font-size: 14px;
                                                    }
                                                    .btn {
                                                        font-size: 14px;
                                                        padding: 10px 20px;
                                                    }
                                                }
                                            </style>
                                        </head>
                                        <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; color: #333;">
                                            <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                                                <div style="' . $style . '">
                                                    <img src="https://crm.tejasloan.com/public/images/18-BK_kixu8.png" style="width: 30%;">
                                                </div>
                                                <div style="padding: 20px;">
                                                    <div style="text-align: center; margin-bottom: 20px;">
                                                        <img src="https://tejasloanbucket.s3.ap-south-1.amazonaws.com/emailer/' . $reminder_link . '" alt="Payment Reminder Banner" style="width: 100%; max-width: 560px; border-radius: 8px;">
                                                    </div>
                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Dear <strong>' . htmlspecialchars(ucwords($cust_full_name)) . '</strong>,</p>

                                                    <p style="line-height: 1.8; margin: 10px 0;">This is a gentle reminder regarding your repayment for your loan with <strong>' . BRAND_NAME . '</strong>. Below are the details:</p>

                                                    <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Loan Account Number:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars($loan_no) . '</strong></td>
                                                    </tr>
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Due Date:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars($repayment_date) . '</strong></td>
                                                    </tr>
                                                    <tr>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Amount Due:</td>
                                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px; color: #E53935;"><strong>₹' . htmlspecialchars($repayment_amount) . '</strong></td>
                                                    </tr>
                                                    </table>

                                                    ' . $repaymentWarning . '

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px; text-align: center;"><strong>Scan the QR code below to make your payment easily:</strong></p>

                                                    <div style="text-align: center; margin: 20px 0;">
                                                        <img src="' . $qrCodeUrl . '" alt="QR Code for Payment" style="max-width: 200px; border: 2px solid #ddd; border-radius: 8px;">
                                                    </div>

                                                    <p style="text-align: center; line-height: 1.8; margin: 10px 0;">Alternatively, you can use the link below to make your payment:</p>

                                                    <div style="text-align: center; margin: 20px 0;">
                                                        <a href="' . $repaymentLink . '" class="btn" style="display: inline-block; background: #4CAF50; color: #fff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px;">Make Payment</a>
                                                    </div>

                                                    <p style="line-height: 1.8; margin: 10px 0;">If you have already made the payment, please disregard this message. For any assistance, contact our support team at <a href="mailto:' . CARE_EMAIL . '" style="color: #4CAF50; text-decoration: none;"><strong>' . CARE_EMAIL . '</strong></a> or call us at <strong><a href="tel:' . REGISTED_MOBILE . '" style="color: #4CAF50; text-decoration: none;">' . REGISTED_MOBILE . '</a></strong></p>

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Thank you for choosing us.</p>

                                                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Best regards,<br><strong>' . BRAND_NAME . '</strong></p>
                                                </div>
                                                <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px;">
                                                    <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                                    <p style="margin: 0;">
                                                        <a href="' . WEBSITE_URL . "privacypolicy" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                                        <a href="' . WEBSITE_URL . "termsandconditions" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                                        <a href="' . WEBSITE_URL . "contact" . '" target="_blank"  style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                                    </p>
                                                    <div style="text-align: center; margin: 20px 0;">
                                                        <p style="font-size: 14px; color: #777; margin: 10px;">Follow us on:</p>
                                                        <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                            <img src="' . FACEBOOK_ICON . '" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                        </a>
                                                        <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                            <img src="' . TWITTER_ICON . '" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                        </a>
                                                        <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                            <img src="' . LINKEDIN_ICON . '" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                        </a>
                                                        <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                            <img src="' . INSTAGRAM_ICON . '" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                        </a>
                                                        <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                            <img src="' . YOUTUBE_ICON . '" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </body>

                                        </html>';

                    common_send_email($to_email, $email_subject, $email_message, "", "", "info@tejasloan.com", "", "", "", "");

                    if ($return_array['status'] == 1) {
                        $email_counter['email_sent'] = $email_counter['email_sent'] + 1;
                    } else {
                        $email_counter['email_failed'] = $email_counter['email_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['subject'] = "PROD-REPAYMENT DAY REMINDER EMAIL - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];

            common_send_email($this->notification_mail, $email_data['subject'], $email_data['message'], "", "", "", "", "", "", "");

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->EmailModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }
}
