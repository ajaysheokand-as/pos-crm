<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') or define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       https://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       https://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       https://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


defined('CSS_VERSION') or define('CSS_VERSION', 1.1); // highest automatically-assigned error code

// defined('ALL_FROM_EMAIL') or define('ALL_FROM_EMAIL', 'info@tejasloan.com');
defined('ALL_FROM_EMAIL') or define('ALL_FROM_EMAIL', 'info@tejasloan.com');

// defined('BCC_SANCTION_EMAIL') or define('BCC_SANCTION_EMAIL', 'info@tejasloan.com');
defined('BCC_SANCTION_EMAIL') or define('BCC_SANCTION_EMAIL', 'info@tejasloan.com');
defined('BCC_DISBURSAL_EMAIL') or define('BCC_DISBURSAL_EMAIL', '');
defined('BCC_NOC_EMAIL') or define('BCC_NOC_EMAIL', 'info@tejasloan.com');
defined('BCC_DISBURSAL_WAIVE_EMAIL') or define('BCC_DISBURSAL_WAIVE_EMAIL', 'info@tejasloan.com');

defined('CC_SANCTION_EMAIL') or define('CC_SANCTION_EMAIL', '');
defined('CC_DISBURSAL_EMAIL') or define('CC_DISBURSAL_EMAIL', '');
defined('CC_DISBURSAL_WAIVE_EMAIL') or define('CC_DISBURSAL_WAIVE_EMAIL', 'info@tejasloan.com');

defined('TO_KYC_DOCS_ZIP_DOWNLOAD_EMAIL') or define('TO_KYC_DOCS_ZIP_DOWNLOAD_EMAIL', 'info@tejasloan.com');

define("COMPONENT_PATH", "/var/www/html/tejasloan-backend/common_component/");
define("UPLOAD_PATH", "/var/www/html/tejasloan-backend/uploads/");
define("UPLOAD_LEGAL_PATH", "/var/www/html/tejasloan-backend/uploads/");
define("UPLOAD_SETTLEMENT_PATH", "/var/www/html/tejasloan-backend/uploads/");
define("UPLOAD_RECOVERY_PATH", "/var/www/html/tejasloan-backend/uploads/");
define("TEMP_UPLOAD_PATH", "/var/www/html/tejasloan-backend/temp_upload/");
define("UPLOAD_DISBURSAL_PATH", "/var/www/html/tejasloan-backend/uploads/");

define("LMS_DOC_S3_FLAG", true); //true=> Store in S3 bucket , false=> Physical store.


//define("LOANS_KYC_DOCS", "/kycdocs/loans/");

define("FEEDBACK_WEB_PATH", "https://tejasloan.com/customer-feedback/");

// ********** API URL DEFINE *****

// defined('SERVER_API_URL') or define('SERVER_API_URL', "https://api.sotcrm.com"); //SERVER API URL
defined('SERVER_API_URL') or define('SERVER_API_URL', "https://api.tejasloan.com"); //SERVER API URL

// ********** LMS DEFINED VARIABLE *****

// define("LMS_URL", "https://sotcrm.com/");
define("LMS_URL", "https://crm.tejasloan.com/");
define("WEBSITE_URL", "https://tejasloan.com/");
define("WEBSITE", "tejasloan.com");
define("WEBSITE_UTM_SOURCE", WEBSITE_URL . "apply-now?utm_source=");

// define("LMS_COMPANY_LOGO", LMS_URL . "public/front/img/company_logo.png");
define("LMS_COMPANY_LOGO", LMS_URL . "public/images/tejas-logo.svg");
define("LMS_KASAR_LETTERHEAD", LMS_URL . "public/images/Letter_Head_A4.png");
define("LMS_COMPANY_MIS_LOGO", LMS_URL . "public/images/tejas-logo.svg");
// define("LMS_BRAND_LOGO", LMS_URL . "public/front/img/brand_logo.jpg");
define("LMS_BRAND_LOGO", LMS_URL . "public/images/tejas-logo.svg");
define("BANK_STATEMENT_UPLOAD", "application/helpers/integration/");
define("COMPANY_NAME", "AMAN FINCAP LIMITED");
define("RBI_LICENCE_NUMBER", "14.01466");
define('CONTACT_PERSON', 'NITIN VAID');
define("REGISTED_ADDRESS", "D.K.Chopra Tower Plot no.2 , Basement -2 Near Yashodha Hospital Ghaziabad - UP - 201010");
define("REGISTED_MOBILE", "+91-9355562952");
define("BRAND_NAME", "TEJASLOAN");

define("TECH_EMAIL", "tech@tejasloan.com");
define("INFO_EMAIL", "tech@tejasloan.com");
define("CARE_EMAIL", "tech@tejasloan.com");
define("RECOVERY_EMAIL", "tech@tejasloan.com");
define("COLLECTION_EMAIL", "tech@tejasloan.com");
define("CTO_EMAIL", "tech@tejasloan.com");

// ********** TEMPLETE DEFINED VARIABLE *****

define("EMAIL_BRAND_LOGO", LMS_URL . "public/images/tejas-logo.svg");
define("DISBURSAL_LETTER_BANNER", LMS_URL . "public/emailimages/disbursal_banner.png");

define("SANCTION_LETTER_HEADER", LMS_URL . "public/emailimages/header.jpg");
define("SANCTION_LETTER_FOOTER", LMS_URL . "public/emailimages/footer.png");

define("GENERATE_SANCTION_LETTER_HEADER", LMS_URL . "public/emailimages/header.jpg");
define("GENERATE_SANCTION_LETTER_FOOTER", LMS_URL . "public/emailimages/footer.png");

define("EKYC_BRAND_LOGO", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/ekyc_brand_logo.gif");
define("EKYC_HEADER_BACK", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/header_back.jpg");
define("EKYC_LINES", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/line.png");
define("EKYC_IMAGES_1", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/1st.jpg");
define("EKYC_IMAGES_1_SHOW", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/image1.png");
define("EKYC_IMAGES_2", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/2nd.jpg");
define("EKYC_IMAGES_2_SHOW", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/image2.png");
define("EKYC_IMAGES_3", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/image3.png");
define("EKYC_IMAGES_3_SHOW", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/3rd.jpg");
define("EKYC_IMAGES_4", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/4th.jpg");
define("EKYC_IMAGES_4_SHOW", WEBSITE_URL . "Digilocker_eKyc/images/4th.jpg");

//******** Start Advocate Mail Constant *******************//

define("ADVOCATE_SIGN", LMS_URL . "public/images/sign-lg.jpg");
define("ADVOCATE_LOGO", LMS_URL . "public/images/advocate-lg.jpg");
define("New_HEADER_BG", LMS_URL . "public/images/New_HEADER_BG.jpg");
define("ADVOCATE_HEADER", LMS_URL . "public/images/LEGAL_HD.jpg");
define("ADVOCATE_MOBILE1", LMS_URL . "99101-52173");
define("ADVOCATE_MOBILE2", LMS_URL . "92898-77841");
define("ADVOCATE_MAIL", LMS_URL . "FAUJDARAJAY99@GMAIL.COM");
define("ADVOCATE_COMPANY_MAIL", LMS_URL . "info@tejasloan.com");

define("LOAN_REPAY_LINK", WEBSITE_URL . "repay-loan");
define("REPAYMENT_REPAY_LINK", WEBSITE_URL . "repay-loan-details");
define("AUTHORISED_SIGNATORY", WEBSITE_URL . "public/front/images/Authorised-Signatory.jpeg");

define("PRE_APPROVED_LINES", WEBSITE_URL . "emailimages/final-email-template/images/back-line.png");
define("PRE_APPROVED_BANNER", WEBSITE_URL . "emailimages/final-email-template/images/email-speedoloan.gif");
define("PRE_APPROVED_LINE_COLOR", WEBSITE_URL . "emailimages/final-email-template/images/line-color.png");
define("PRE_APPROVED_PHONE_ICON", WEBSITE_URL . "emailimages/final-email-template/images/phone-icon.png");
define("PRE_APPROVED_WEB_ICON", WEBSITE_URL . "emailimages/final-email-template/images/web-icon.png");
define("PRE_APPROVED_EMAIL_ICON", WEBSITE_URL . "emailimages/final-email-template/images/emil-icon.png");
define("PRE_APPROVED_ARROW_LEFT", WEBSITE_URL . "emailimages/final-email-template/images/arrow-left.png");
define("PRE_APPROVED_ARROW_RIGHT", WEBSITE_URL . "emailimages/final-email-template/images/arrow-right.png");

define("FEEDBACK_HEADER", WEBSITE_URL . "emailimages/feedback/images/header2.jpg");
define("FEEDBACK_LINE", WEBSITE_URL . "emailimages/feedback/images/line.png");
define("FEEDBACK_PHONE_ICON", WEBSITE_URL . "emailimages/feedback/images/phone-icon.png");
define("FEEDBACK_WEB_ICON", WEBSITE_URL . "emailimages/feedback/images/web-icon.png");
define("FEEDBACK_EMAIL_ICON", WEBSITE_URL . "emailimages/feedback/images/email-icon.png");

define("COLLECTION_BRAND_LOGO", WEBSITE_URL . "emailimages/collection/image/lw-logo.png");
define("COLLECTION_EXE_BANNER", WEBSITE_URL . "emailimages/collection/image/Collection-Executive-banner.jpg");
define("COLLECTION_LINE", WEBSITE_URL . "emailimages/collection/image/line.png");
define("COLLECTION_INR_ICON", WEBSITE_URL . "emailimages/collection/image/inr-icon.png");
define("COLLECTION_ROAD_BANNER", WEBSITE_URL . "emailimages/collection/image/CRM.jpg");
define("COLLECTION_PHONE_ICON", WEBSITE_URL . "emailimages/collection/image/phone-icon.png");
define("COLLECTION_EMAIL_ICON", WEBSITE_URL . "emailimages/collection/image/emil-icon.png");
define("COLLECTION_WEB_ICON", WEBSITE_URL . "emailimages/collection/image/web-icon.png");

// *********SOCIAL MEDIA LINK ********

define("ANDROID_STORE_LINK", "#");
define("APPLE_STORE_LINK", "#");
define("LINKEDIN_LINK", "#");
define("INSTAGRAM_LINK", "#");
define("FACEBOOK_LINK", "#");
define("YOUTUBE_LINK", "#");
define("TWITTER_LINK", "#");

// define("PHONE_ICON", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/phone-icon.png");
// define("WEB_ICON", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/web_icon.png");
// define("EMAIL_ICON", WEBSITE_URL . "emailimages/Digilocker_eKyc/images/emil_icon.png");

define("PHONE_ICON", LMS_URL . "public/new_images/images/phone-512.webp");
define("WEB_ICON", LMS_URL . "public/new_images/images/1006771.png");
define("EMAIL_ICON", LMS_URL . "public/new_images/images/email-icon-1024x1024-7l3hfh11.png");

// ******* CRON JOBS ********

define("BIRTHDAY_LINE", WEBSITE_URL . "emailimages/birthday/line.png");
define("BIRTHDAY_BIRTHDAY_PIC", WEBSITE_URL . "emailimages/birthday/email-design.jpg");

define("FESTIVAL_BANNER", WEBSITE_URL . "emailimages/festiv/image/offer.jpg");
define("FESTIVAL_CLOSE_BANNER", WEBSITE_URL . "emailimages/new-cust/image/b.jpg");
define("FESTIVAL_OFFICIAL_NUMBER", WEBSITE_URL . "emailimages/festiv/image/phone-icon.png");
define("FESTIVAL_LINE", WEBSITE_URL . "emailimages/festiv/image/line.png");

define("BLOG", WEBSITE_URL . "public/blog/");

// define("WEBSITE_DOCUMENT_BASE_URL", "https://sot-website.s3.ap-south-1.amazonaws.com/upload/");
define("WEBSITE_DOCUMENT_BASE_URL", "https://tejasloanbucket.s3.ap-south-1.amazonaws.com/upload/");


$xco_path = 'common_component';
define("COMP_PATH", $xco_path);

// Portel configuration settings
const PORTAL_NAME    = "tejasloan";
const PORTAL_DOMAIN  = PORTAL_NAME . ".com";

// NBFC details
const NBFC_LOGO      = 'namanfinlease.png';
const NBFC_NAME      = 'Aman Fincap Limited';
const NBFC_CIN       = 'Reg No.14.01466';
const NBFC_ADDRESS   = 'S-370, Panchsheel Park, New Delhi - 110017';
const NBFC_MOBILE    = '8282824633';
const NBFC_EMAIL     = 'info@tejasloan.com';
const NBFC_WEBSITE   = 'https://namanfinlease.com/';
const NBFC_AUTHORIZED_SIGN   = LMS_URL . 'emailimages/Authorised-Signatory.jpg';

const NBFC_NODEL_GRIEVANCE_REDRESSAL_OFFICER_NAME  = 'Sachin Sharma';
const NBFC_NODEL_GRIEVANCE_REDRESSAL_OFFICER_NUMBER = '8282824633';

// Portal redirection page url
const PORTAL_REDIRECTION_URL = [
  "PRE_APPROVED_CUSTOMER" => "pre-approved-customer",
  "REPAY_LOAN"            => "repay-now",
  "APPLY_NOW"             => "apply-now",
  "THANK_YOU"             => "thankyou",
  "CONTACT_US"            => "contact-us"
];
