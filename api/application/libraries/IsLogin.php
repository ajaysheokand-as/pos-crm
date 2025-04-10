<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class IsLogin
{
    // public function __construct()
    // {
    //     parent::__construct();
    //     // The session class is available now because
    //     // we called the parent constructor, where it is already loaded.

    //     // Get the current logged in user (however your app does it)
    //     $user_id = $this->session->userdata('user_id');

    //     // You might want to validate that the user exists here

    //     // If you only want to update in intervals, check the last_activity.
    //     // You may need to load the date helper, or simply use time() instead.
    //     $time_since = now() - $this->session->userdata('last_activity');
    //     $interval = 300;

    //     // Do nothing if last activity is recent
    //     if ($time_since < $interval) return;

    //     // Update database
    //     $updated = $this->db
    //           ->set('last_activity', now())
    //           ->where('id', $user_id)
    //           ->update('users');

    //     // Log errors if you please
    //     $updated or log_message('error', 'Failed to update last activity.');
    // }
    
    public function index()
    {   
        if(!empty($_SESSION['isUserSession']['user_id']) && $_SESSION['isUserSession']['email'] == NULL) 
        {
            $this->session->set_flashdata('err', "Session Expired, Try once more.");
            return redirect(base_url(), 'refresh');
        } else {
            // echo "Er. Vinay Kumar checking is user login or not.";
        }
    }  
}