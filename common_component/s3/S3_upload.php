<?php

/**
 * Amazon S3 Upload PHP class
 *
 * @version 0.1
 */
class S3_upload {

    function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->library('s3');

        $this->CI->config->load('s3', TRUE);
        $s3_config = $this->CI->config->item('s3');
        $this->bucket_name = $s3_config['bucket_name'];
        $this->folder_name = $s3_config['folder_name'];
        $this->s3_url = $s3_config['s3_url'];
    }

    function upload_file($file_path, $file_name, $file_type = 0) {

        $s3_file = $file_name;

        if ($file_type == 1) {

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $mime_type = $finfo->buffer($file_path);

            $saved = $this->CI->s3->putObjectString(
                $file_path,
                $this->bucket_name,
                $this->folder_name . "/" . $s3_file,
                S3::ACL_PRIVATE,
                array(),
                $mime_type
            );
        } else {

            $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file_path);
            $saved = $this->CI->s3->putObjectFile(
                $file_path,
                $this->bucket_name,
                $this->folder_name . "/" . $s3_file,
                S3::ACL_PRIVATE,
                array(),
                $mime_type
            );
        }


        if ($saved) {
            return true;
        } else {
            return false;
        }
    }

    function get_file($file_name, $flag = 0) {
        $objInfo = $this->CI->s3->getObjectInfo($this->bucket_name, $this->folder_name . "/" . $file_name);
        $obj = $this->CI->s3->getObject($this->bucket_name, $this->folder_name . "/" . $file_name);

        if ($flag == 1) {
            header('Content-type: ' . $objInfo['type']);
            return $obj->body;
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $object->ContentType);
            header('Content-Disposition: attachment; filename=' . $file_name);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            echo $obj->body;
        }
    }

    function get_file_path($file_name) {
        $objInfo = $this->CI->s3->getObjectInfo($this->bucket_name, $this->folder_name . "/" . $file_name);
        return $objInfo;
    }
}
