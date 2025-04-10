<?php
require_once('vendor/autoload.php');

require_once('s3.php');

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\ObjectUploader;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3 = new S3Client([
    'version' => 'latest',
    'region' => 'ap-south-1',
    'credentials' => [
        'key' => $config['access_key'],
        'secret' => $config['secret_key'],
    ],
        ]);

$bucket = 'bl-upload-files-dev';

$bucket_folder = "bl-upload-files-dev-folder/";

$s3_file_url = "https://devmunitech.com/s3/";

if (!empty($_FILES['upl_file'])) {

    $filename = $_FILES['upl_file']['name'];

    $tmp_file_name = $_FILES["upl_file"]["tmp_name"];

    $upload_path = __DIR__ . '/upload/';

    $download_path = $s3_file_url . 'download/';

    $file_Path = $upload_path . $filename;

    $ext = explode(".", $filename);

    $ext = strtolower(end($ext));

    move_uploaded_file($tmp_file_name, $file_Path);

    $new_file_name = "new_file_" . md5(rand(111111, 999999)) . "." . $ext;

    $key = $bucket_folder . $new_file_name;

    $source = fopen($file_Path, 'rb');

    try {

        $uploader = new ObjectUploader(
                $s3,
                $bucket,
                $key,
                $source,
        );
        do {
            try {

                $result = $uploader->upload();
                if ($result["@metadata"]["statusCode"] == '200') {
                    try {
                        $downloader = $s3->getObject([
                            'Bucket' => $bucket,
                            'Key' => $key,
                        ]);
                        
                        /***************** Uncomment the lines to download the file to your local system********************/
//                        header('Content-Description: File Transfer');
                        header("Content-Type: {$downloader['ContentType']}");
//                        header('Content-Disposition: attachment; filename=' . basename($key));
//                        header('Expires: 0');
//                        header('Cache-Control: must-revalidate');
//                        header('Pragma: public');
                        echo $downloader['Body'];
                    } catch (Exception $exception) {
                        echo "Failed to download $file_name from $bucket_name with error: " . $exception->getMessage();
                        exit("Please fix error with file downloading before continuing.");
                    }
                }

                echo "<br>Ended Here";
            } catch (MultipartUploadException $e) {
                rewind($source);
                $uploader = new MultipartUploader($s3Client, $source, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));
    } catch (Aws\S3\Exception\S3Exception $e) {

        echo "There was an error uploading file.\n";

        echo $e->getMessage();
    }
} else {
    ?>
    <html>
        <head>
            <title>Upload File</title>
        </head>
        <body>
            <form action="check_file.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="upl_file"><br><br>
                <input type="submit" value="Submit">
            </form>
        </body>
    </html>

    <?php
}

