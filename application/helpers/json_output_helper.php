<?php
if ( ! function_exists('json_output'))
{
    function json_output($statusHeader, $response)
    {
        $ci =& get_instance(); // Get the CodeIgniter instance
        $ci->output->set_content_type('application/json')
                   ->set_status_header($statusHeader)
                   ->set_output(json_encode($response));
    }
}