<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Use SSL
|--------------------------------------------------------------------------
|
| Run this over HTTP or HTTPS. HTTPS (SSL) is more secure but can cause problems
| on incorrectly configured servers.
|
*/

$config['use_ssl'] = TRUE;

/*
|--------------------------------------------------------------------------
| Verify Peer
|--------------------------------------------------------------------------
|
| Enable verification of the HTTPS (SSL) certificate against the local CA
| certificate store.
|
*/

$config['verify_peer'] = TRUE;

/*
|--------------------------------------------------------------------------
| Access Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 access key.
|
*/

// $config['access_key'] = 'AKIAXEVXYIVMRIOWP3MO';
$config['access_key'] = 'AKIAV72EG5TGUPEWVYJC';

/*
|--------------------------------------------------------------------------
| Secret Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 Secret Key.
|
*/
// $config['secret_key'] = 'awlWUgbMYfWJFRe4ZqdHnrIcmZfMJuOorHT4ZXiS';
$config['secret_key'] = getenv('AWS_SECRET_KEY');

/*
|--------------------------------------------------------------------------
| Bucket Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Name.
|
*/

// $config['bucket_name'] = 'sot-documents';
$config['bucket_name'] = getenv('S3_BUCKET_NAME');

/*
|--------------------------------------------------------------------------
| Bucket Folder Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Folder Name.
|
*/

$config['folder_name'] = getenv( 'S3_BUCKET_FOLDER');

/*
|--------------------------------------------------------------------------
| Bucket Folder Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Folder Name.
|
*/

// $config['s3_url'] = 's3://sot-documents/upload/';
$config['s3_url'] = 's3://'.$config['bucket_name']."/".$config['folder_name']."/";

/*
|--------------------------------------------------------------------------
| Use Enviroment?
|--------------------------------------------------------------------------
|
| Get Settings from enviroment instead of this file?
| Used as best-practice on Heroku
|
*/

$config['get_from_enviroment'] = FALSE;

/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|
*/
$config['access_key_envname'] = 'S3_KEY';

/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|
*/
$config['secret_key_envname'] = 'S3_SECRET';

/*
|--------------------------------------------------------------------------
| If get from enviroment, do so and overwrite fixed vars above
|--------------------------------------------------------------------------
|
*/

if ($config['get_from_enviroment']) {
	$config['access_key'] = getenv($config['access_key_envname']);
	$config['secret_key'] = getenv($config['secret_key_envname']);
}
