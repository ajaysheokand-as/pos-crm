<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes in the
  | controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */

$route['default_controller'] = 'LoginController/login';
$route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;
$route['translate_uri_dashes'] = TRUE;

///////////////// Admin Login /////////////////

$route['dashboard'] = 'LoginController/dashboard';
$route['login'] = 'LoginController/login';

$route['home/(:any)'] = 'LoginController/home/$1';
$route['islogin'] = 'LoginController/islogin';
$route['logout'] = 'LoginController/logout';
$route['myProfile'] = 'LoginController/myProfile';
$route['editProfile/(:num)'] = 'LoginController/editProfile/$1';
$route['updateProfile/(:num)'] = 'LoginController/updateProfile/$1';
$route['changePassword'] = 'LoginController/changePassword';
$route['generatePassword'] = 'LoginController/generatePassword';
$route['GetMACAddress'] = 'LoginController/GetMAC';

$route['changePassword'] = 'LoginController/changePassword';
$route['forgetPassword'] = 'LoginController/forgetPassword';
$route['verifyUser'] = 'LoginController/verifyUser';
$route['verifyotp'] = 'LoginController/verifyOtp';
$route['updatePassword'] = 'LoginController/updatePassword';
$route['leadAllocation'] = 'LoginController/leadAllocation';
$route['targetAllocation'] = 'LoginController/targetAllocation';
/////////////////////////// State City /////////////////////////////

$route['getState'] = 'TaskController/getState';
$route['getReligion'] = 'TaskController/getReligion';
$route['getCity/(:num)'] = 'TaskController/getCity/$1';
$route['getPincode/(:num)'] = 'TaskController/getPincode/$1';
$route['apiPincode/(:num)'] = 'TaskController/apiPincode/$1';

/////////////////////////// Senction task /////////////////////////////

$route['inProcess/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['screeninLeads/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['holdleads/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['enquires(/:any)?'] = 'TaskController/enquires';
$route['getEnquiryDetails/(:any)'] = 'TaskController/getEnquiryDetails/$1';
$route['convertEnquiryToApplication'] = 'TaskController/convertEnquiryToApplication';

$route['saveHoldleads/(:any)'] = 'TaskController/saveHoldleads/$1';
$route['getleadDetails/(:any)'] = 'TaskController/getLeadDetails/$1';
$route['search/getleadDetails/(:any)'] = 'TaskController/getLeadDetails/$1';
$route['rejectedLeadMoveToProcess'] = 'TaskController/rejectedLeadMoveToProcess';
$route['viewOldHistory/(:any)'] = 'TaskController/viewOldHistory/$1';
$route['viewleadLogs/(:any)'] = 'TaskController/getLeadHistoryLogs/$1';
$route['viewSanctionFollowupLogs/(:any)'] = 'TaskController/getSanctionFollowupLogs/$1';
$route['sanctionleads'] = 'TaskController/sanctionleads';
$route['generateLoanNo'] = 'TaskController/generateLoanNo';

$route['GetLeadTaskList/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['applicationinprocess/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['applicationRecommend/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['applicationSendBack/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['applicationHold/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['applicationrejected/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['sanctioned/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursalnew/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursalinprocess/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursesendback/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursewaiveoff/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['not-contactable/(:any)(/:any)?'] = 'TaskController/index/$1';

$route['disbursalhold/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursalPending/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['disbursed/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['collection/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['collection-pending/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['closure/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['preclosure/(:any)(/:any)?'] = 'TaskController/index/$1';

$route['recovery-pending/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['legal/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['settlement/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['write-off/(:any)(/:any)?'] = 'TaskController/index/$1';

$route['visitrequest/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['visitpending/(:any)(/:any)?'] = 'TaskController/index/$1';
$route['visitcompleted/(:any)(/:any)?'] = 'TaskController/index/$1';

///////////////////////// 403 or 404 not accessable ////////////////

$route['upload'] = 'TaskController/error_page';

////////////////////////////////////////////////////////////////////

$route['sanctionLetter/(:num)'] = 'TaskController/sanctionLetter/$1';
$route['followUp'] = 'TaskController/followUp';
$route['TaskList'] = 'TaskController/TaskList';
$route['initiateFiCPV'] = 'TaskController/initiateFiCPV';
// $route['rejectApproval'] = 'TaskController/rejectApproval';
$route['getDocumentSubType/(:any)'] = 'TaskController/getDocumentSubType/$1';
$route['getDocsUsingAjax/(:num)'] = 'TaskController/getDocsUsingAjax/$1';
$route['viewCustomerDocsById/(:num)'] = 'TaskController/viewCustomerDocsById/$1';
$route['UpdateCustomerDocs'] = 'TaskController/UpdateCustomerDocs';
$route['deleteCustomerDocsById/(:num)'] = 'TaskController/deleteCustomerDocsById/$1';

$route['oldUserHistory/(:num)'] = 'TaskController/oldUserHistory/$1';
$route['viewCustomerDocs/(:num)'] = 'TaskController/viewCustomerDocs/$1';
$route['downloadCustomerdocs/(:num)'] = 'TaskController/downloadCustomerdocs/$1';
$route['sendRequestToCustomerForUploadDocs/(:any)'] = 'TaskController/sendRequestToCustomerForUploadDocs/$1';
$route['saveCustomerDocs'] = 'TaskController/saveCustomerDocs';
$route['customer-upload-docs/(:any)'] = 'TaskController/saveCustomerDocs/$1';

$route['resonForDuplicateLeads'] = 'TaskController/resonForDuplicateLeads';
$route['allocateLeads'] = 'TaskController/allocateLeads';
$route['applicationHold'] = 'TaskController/applicationHold';

$route['saveApplication'] = 'LeadsController/saveApplication';
$route['saveVerification'] = 'LeadsController/add_action';
$route['insertPersonal1'] = 'LeadsController/savepersonal1'; //insertPersonal4
$route['insertPersonal4'] = 'LeadsController/insertPersonal4';
$route['insertPersonal3'] = 'LeadsController/insertPersonal3';

$route['saveapplocConfirmation'] = 'LeadsController/saveapplocConfirmation'; //saveFVCData
$route['saveFVCData'] = 'LeadsController/saveFVCData'; //saveFVCData



$route['duplicateTaskList'] = 'TaskController/duplicateTaskList';
$route['duplicateLeadDetails/(:num)'] = 'TaskController/duplicateLeadDetails/$1';

$route['getRejectionReasonMaster'] = 'RejectionController/getRejectionReasonMaster';
$route['resonForRejectLoan'] = 'RejectionController/resonForRejectLoan';
$route['getRejectionList'] = 'RejectionController/getRejectionList';
$route['rejectedTaskList'] = 'RejectionController/rejectedTaskList';
$route['rejectedLeadDetails/(:num)'] = 'RejectionController/rejectedLeadDetails/$1';

$route['RequestForApproveLoan'] = 'TaskController/RequestForApproveLoan';
$route['AddContactDetails/(:num)'] = 'TaskController/AddContactDetails/$1';
$route['taskApproveRequest'] = 'TaskController/taskRequestForApprove';

$route['LACLeadRecommendation'] = 'TaskController/LACLeadRecommendation';
$route['PaydayLeadRecommendation'] = 'TaskController/PaydayLeadRecommendation';

$route['leadRecommend'] = 'TaskController/leadRecommend';
$route['leadSendBack'] = 'TaskController/leadSendBack';
$route['disburseRecommend'] = 'TaskController/disburseRecommend';
$route['disbursalSendBack'] = 'TaskController/disbursalSendBack';
$route['leadDisbursed'] = 'TaskController/leadDisbursed';
$route['scmConfRequest'] = 'TaskController/scmConfRequest';
$route['disburseWaived'] = 'TaskController/disburseWaived';

/////////////////////// PERSONAL ///////////////////////////////////////////////////

$route['insertApplication'] = 'TaskController/insertApplication';
$route['insertPersonal'] = 'TaskController/insertPersonal';
$route['insertResidence'] = 'TaskController/insertResidence';
$route['insertEmployment'] = 'TaskController/insertEmployment';
$route['insertReference'] = 'TaskController/insertReference';
$route['updateReference'] = 'TaskController/updateReference';
$route['deleteData'] = 'TaskController/deleteData';

$route['getApplicationDetails/(:num)'] = 'TaskController/getApplicationDetails/$1';
$route['getPersonalDetails/(:num)'] = 'TaskController/getPersonalDetails/$1';
$route['getResidenceDetails/(:num)'] = 'TaskController/getResidenceDetails/$1';
$route['getEmploymentDetails/(:num)'] = 'TaskController/getEmploymentDetails/$1';
$route['getReferenceDetails/(:num)'] = 'TaskController/getReferenceDetails/$1';
// $route['saveCustomerPersonalDetails'] = 'TaskController/saveCustomerPersonalDetails';
$route['initiateResidenceCPV'] = 'TaskController/initiateResidenceCPV';

////////////////////// CAM ////////////////////////////////////////////////////////

$route['calculateAmount'] = 'CAMController/calculateAmount';
$route['checkLoanEligibility'] = 'CAMController/checkLoanEligibility';
$route['calculateMedian/(:any)'] = 'CAMController/calculateMedian/$1';
$route['averageSalary/(:any)'] = 'CAMController/averageSalary/$1';
$route['viewCAM/(:num)/(:any)'] = 'CAMController/viewCAM/$1/$2';
$route['downloadCAM/(:num)'] = 'CAMController/downloadCAM/$1';
$route['getCAMDetails/(:num)'] = 'CAMController/getCAMDetails/$1';
$route['saveLACCAMDetails'] = 'CAMController/saveLACCAMDetails';
$route['savePaydayCAMDetails'] = 'CAMController/savePaydayCAMDetails';
$route['headCAMApproved/(:num)'] = 'CAMController/headCAMApproved/$1';
$route['resentEkycEmail/(:num)'] = 'CAMController/resentEkycEmail/$1';

$route['saveCustomerEmployeeDetails/(:num)'] = 'TaskController/saveCustomerEmployeeDetails/$1';
$route['ShowCustomerEmploymentDetails/(:num)'] = 'TaskController/ShowCustomerEmploymentDetails/$1';

//$route['AddCreditDetails/(:num)'] = 'CreditController/AddCreditDetails/$1';
//$route['save-credit-details'] = 'CreditController/saveCreditDetails';
//$route['EditCreditDetails/(:num)'] = 'CreditController/EditCreditDetails/$1';
//$route['updateCreditDetails/(:num)'] = 'CreditController/updateCreditDetails/$1';
//$route['get_credit/(:num)'] = 'CreditController/get_credit/$1';
///////////////////////// Senction Head ////////////////////////////////////////////////////

$route['reCreditLoan'] = 'TaskController/reCreditLoan';
$route['ApproveSenctionLoan'] = 'TaskController/ApproveSenctionLoan';

///////////////////////// Search ///////////////////////////////////////////////////

$route['search'] = "SearchController/index";
$route['filter'] = "SearchController/filter";

$route['searchrunningloan'] = "SearchController/index";

///////////////////////// Disbursal leads ///////////////////////////////////////////////////
$route['getSanctionDetails'] = 'DisbursalController/getSanctionDetails';
$route['resendDisbursalMail'] = 'DisbursalController/resendDisbursalMail';
$route['getCustomerBanking'] = "DisbursalController/getCustomerBanking";
$route['getCustomerDisbBanking/(:any)'] = "DisbursalController/getCustomerDisbBanking/$1";
$route['getCustomerBankDetails'] = "DisbursalController/getCustomerBankDetails";
$route['getBankNameByIfscCode'] = "DisbursalController/getBankNameByIfscCode";
$route['saveDisbursalData'] = "DisbursalController/saveDisbursalData";
$route['addBeneficiary'] = "DisbursalController/addBeneficiary";
$route['verifyDisbursalBank'] = "DisbursalController/verifyDisbursalBank";

$route['allowDisbursalToBank'] = "DisbursalController/allowDisbursalToBank";
$route['UpdateDisburseReferenceNo'] = "DisbursalController/UpdateDisburseReferenceNo";

$route['PayAmountToCustomer/(:num)'] = "DisbursalController/PayAmountToCustomer/$1";
//$route['addBankDetails'] = "DisbursalController/addBankDetails";
$route['totalDisbursedLoan'] = "DisbursalController/totalDisbursedLoan";
$route['loanAgreementLetter/(:num)'] = "DisbursalController/loanAgreementLetter/$1";
$route['viewAgreementLetter/(:num)'] = "DisbursalController/viewAgreementLetter/$1";
$route['loanAgreementLetterResponse'] = "ApiCallBackController/loanAgreementLetterResponse";

$route['getBankDetails/(:num)'] = 'DisbursalController/getBankDetails/$1';
$route['updateBankDetails/(:num)'] = 'DisbursalController/updateBankDetails/$1';
$route['generateDisbursalLetter/(:num)'] = 'DisbursalController/printDisbursalLetter/$1';

// ---------------------- ThirdPartyAPIController --------------------------------

$route['analyse-bank-statement/(:any)'] = "ThirdPartyAPIController/analyse_bank_statement_api_call/$1";
$route['api-download-bank-statement/(:num)'] = "ThirdPartyAPIController/analyse_bank_statement_download_api_call/$1";
$route['get-Banking-Analysis-Data/(:any)'] = "VerificationController/get_Banking_Analysis_Data/$1";
$route['click-to-call/(:any)'] = 'ThirdPartyAPIController/Click_to_call/$1';
// -----------------------------------------------------------------------------------

$route['saveBankDetails'] = 'Admin/BankDetailsController/saveBankDetails';
$route['addBankDetails'] = 'Admin/BankDetailsController/index';
$route['searchIfscCode'] = 'Admin/BankDetailsController/searchIfscCode';

$route['saveHolidayDetails'] = 'Admin/CompanyHolidayController/saveHolidayDetails';
$route['addHolidayDetails'] = 'Admin/CompanyHolidayController/index';
$route['deleteHolidayDetails/(:num)'] = 'Admin/CompanyHolidayController/deleteHolidayDetails/$1';

///////////////////////// LWTestController ///////////////////////////////////////////////////

$route['Finbox/finbox-analysis-report/(:any)'] = "LWTestController/get_finbox_device/$1";
$route['Finbox/finbox-banking-device-data/(:any)'] = "LWTestController/get_finbox_banking_device_data/$1";
$route['finbox-analyse-bank-statement/(:any)'] = "LWTestController/finbox_bank_statement_api_call/$1";
$route['api-download-finbox-bank-statement/(:any)'] = "LWTestController/finbox_bank_statement_download_api_call/$1";

///////////////////////// Collection ///////////////////////////////////////////////////

$route['repaymentLoanDetails'] = 'CollectionController/repaymentLoanDetails';
$route['collectionHistory'] = 'CollectionController/collectionHistory';
$route['UpdatePayment'] = "CollectionController/UpdatePayment";
$route['viewCustomerPaidSlip/(:num)'] = 'CollectionController/viewCustomerPaidSlip/$1';
$route['editCoustomerPayment'] = "CollectionController/editCoustomerPayment";
$route['deleteCoustomerPayment'] = "CollectionController/deleteCoustomerPayment";

$route['send_settlement_loan_letter/(:any)'] = "CollectionController/send_settlement_letter/$1";
$route['send_closed_loan_letter/(:any)'] = "CollectionController/send_closed_letter/$1";
$route['get-collection-payment-verification'] = 'CollectionController/collection_payment_verification';
$route['get-scm-rm-details'] = 'CollectionController/get_scm_rm_details';
///////////////////////// MIS ///////////////////////////////////////////////////

$route['MIS'] = "CollectionController/MIS";
$route['getRecoveryData/(:num)'] = "CollectionController/getRecoveryData/$1";
$route['getPaymentVerification/(:any)'] = "CollectionController/getPaymentVerification/$1";
$route['verifyCustomerPayment'] = "CollectionController/verifyCustomerPayment";
$route['addToBlackList'] = "CollectionController/addToBlackList"; //BlackList CR on 2022-03-05

$route['getCollectionDetails/(:any)'] = 'CollectionController/get_list_loan_collection_followup/$1';
$route['get-list-followup-master-lists'] = 'CollectionController/get_collection_followup_master_lists';
$route['insert-lead-collection-followup'] = 'CollectionController/insert_loan_collection_followup';
$route['get-followup-template-lists'] = 'CollectionController/get_followup_template_lists';

$route['get-visit-request-lists/(:any)'] = 'CollectionController/get_visit_request_lists/$1';
$route['get-visit-request-user-lists/(:any)'] = 'CollectionController/get_visit_request_user_lists/$1';
$route['insert-request-for-collection-visit'] = 'CollectionController/insert_request_for_collection_visit';
$route['confirm-is-cfe-visit-completed'] = 'CollectionController/confirm_is_cfe_visit_completed';

$route['generateEazyPayRepaymentLink'] = 'CollectionController/generateEazyPayRepaymentLink';

///////////////////////// exportData - ImportData - MIS Report ///////////////////////////////////////////////////

$route['exportData'] = "ExportController/index";
$route['FilterExportReports'] = "ExportController/FilterExportReports";

$route['MIS-Report'] = "ReportsController/index";
$route['Report'] = "ReportsController/FilterMISReports";

$route['ViewImportData'] = 'Admin/ImportController/index';
$route['importData'] = 'Admin/ImportController/importData';
$route['sampleCSV'] = 'Admin/ImportController/sampleCSV';

///////////////////////// Customer Follow up ///////////////////////////////////////////////////

$route['CustomerFollowUp/(:num)'] = "CustomerFollowUpController/CustomerFollowUp/$1";

///////////////////////// Cart Integration /////////////////////////////////////////////////////////

$route['bankAnalysis'] = "CartController/bankAnalysis";
$route['bankAnalysistest'] = "CartController/bankAnalysistest";
$route['callback'] = "CartController/callback";
$route['bank'] = "CartController/index";
$route['ViewBankingAnalysis'] = "CartController/ViewBankingAnalysis";
$route['getBankAnalysis/(:num)'] = "CartController/getBankAnalysis/$1";

///////////////////////// Cibil api Integration /////////////////////////////////////////////////////////

$route['cibil'] = "CibilController/index";
$route['cibilStatement'] = "CibilController/ViewCivilStatement";
$route['viewCustomerCibilPDF/(:num)'] = "CibilController/viewCustomerCibilPDF/$1";
$route['downloadCibilPDF/(:num)'] = "CibilController/downloadCibilPDF/$1";
$route['viewDownloadCibilPDF/(:num)'] = "CibilController/viewDownloadCibilPDF/$1";
$route['downloadcibil/(:num)'] = "CibilController/downloadcibil/$1";

///////////////////////// Expport ///////////////////////////////////////////////////////////////

$route['Export/ExportData/(:any)']['get'] = "ExportController/ExportData/$1";
$route['Export/ExportDisbursalData'] = "ExportController/ExportDisbursalData";

$route['filterReportType'] = "ExportController/filterReportType";
$route['filterReportFilterType'] = "ExportController/filterReportFilterType";

$route['getReasonList'] = "TaskController/getReasonList";

///////////////////////// Migration ///////////////////////////////////////////////////////////////

$route['migrationData'] = "MigrationController/migrationData";
$route['import_Loan_data'] = "MigrationController/import_Loan_data";

/////////////////////////// Admin Permission //////////////////////////////////////////////////////////////////

$route['adminPermission'] = 'Admin/AdminPermissionController/index';
// $route['adminPermission'] = 'Admin/AdminPermissionController/adminPermission';
$route['userPermission/(:num)'] = 'Admin/AdminPermissionController/userPermission/$1';
$route['permissionExportData'] = 'Admin/AdminPermissionController/permissionExportData';
$route['permissionExportType'] = 'Admin/AdminPermissionController/permissionExportType';
$route['getExportType/(:num)'] = 'Admin/AdminPermissionController/getExportType/$1';
$route['permissionGetUsers'] = 'Admin/AdminPermissionController/permissionGetUsers';
$route['admin/dashboardMenuPermission/(:num)'] = 'Admin/AdminPermissionController/dashboardMenuPermission/$1';

$route['addCompanyDetails'] = 'CompanyController/addCompanyDetails';
$route['saveCompanyDetails'] = 'CompanyController/saveCompanyDetails';

///////////////// Super Admin Login ///////////////////

$route['portal'] = 'PortalController/login';
$route['loginPortal'] = 'PortalController/loginPortal';
$route['adminViewUser'] = 'AdminController/index';
$route['adminViewUser/(:num)'] = 'AdminController/index/$1';
$route['adminAddUser'] = 'AdminController/addUsers';
$route['adminSaveUser'] = 'AdminController/adminSaveUser';
$route['adminEditUser/(:num)'] = 'AdminController/adminEditUser/$1';
$route['adminUpdateUser'] = 'AdminController/adminUpdateUser';

$route['adminTaskSetelment'] = 'AdminController/adminTaskSetelment';

$route['adminViewDashboard'] = 'Admin/DashboardController/index';
$route['adminAddDashboardMenu'] = 'Admin/DashboardController/save';
$route['adminEditDashboardMenu/(:any)'] = 'Admin/DashboardController/edit/$1';
$route['adminUpdateDashboardMenu/(:any)'] = 'Admin/DashboardController/update/$1';

$route['exportFile'] = 'TaskController/exportFile';
/* * *********CronJob Setup on 2021-11-17************** */
$route['cronEmailer'] = 'CronJobs/CronEmailerController/birthdayemailer';
/* * ************************************************* */

//****************** Quick Call ********************// quickCallLeadId
$route['quickCallLeadId'] = 'TaskController/quickCallLeadId';
$route['residence-verification(/:any)?'] = 'TaskController/index/$1'; //saveVerification
$route['assignLeadToCollectionuser'] = 'VerificationController/assignLeadToCollectionuser';
$route['office-verification(/:any)?'] = 'TaskController/index/$1';
/* * ****************************************************** */

/* * *********************** Email verification api called ************************ */

$route['getVerificationDetails/(:any)'] = 'VerificationController/getVerificationDetails/$1';
$route['email-verification-api-call'] = 'VerificationController/email_verification_api_call';
$route['email-verification-api-response-call/(:any)'] = 'ThirdPartyAPIController/email_verification_api_response_call/$1';

/* * *****************UMS Design and Access controller ***************************** */
$route['ums'] = 'UMS/UMSController/index';
$route['ums/(:num)'] = 'UMS/UMSController/index';
$route['ums/view-user/(:any)'] = 'UMS/UMSController/umsViewUser/$1';
$route['ums/add-user'] = 'UMS/UMSController/umsAddUser';
$route['ums/edit-user/(:any)'] = 'UMS/UMSController/umsEditUser/$1';
$route['ums/edit-user-role/(:any)'] = 'UMS/UMSController/umsEditUserRole/$1';
$route['ums/edit-role/(:any)'] = 'UMS/UMSController/umsEditUsersRole/$1';
$route['ums/edit-role-scm/(:any)'] = 'UMS/UMSController/umsEditUsersRoleSCM/$1';
$route['ums/add-role/(:any)'] = 'UMS/UMSController/umsAddUserRole/$1';
$route['ums/add-user-role/(:any)'] = 'UMS/UMSController/umsAddUsersRole/$1';
$route['ums/update-user-role-status/(:any)'] = 'UMS/UMSController/updateUserRoleStatus/$1';

/* * ***************************Call Back URL for APIs******************************** */
$route['callbackba-novel-patterns'] = 'ApiCallBackController/callback_novel_bank_analysis';
$route['sanction-esign-request'] = 'ApiCallBackController/eSignSanctionLetterRequest';
$route['sanction-esign-consent'] = 'ApiCallBackController/eSignConsentForm';
$route['sanction-esign-response'] = 'ApiCallBackController/eSignSanctionLetterResponse';
$route['aadhaar-veri-request'] = 'ApiCallBackController/digilockerRequest';
$route['aadhaar-veri-response'] = 'ApiCallBackController/digilockerResponse';

/* * ******************************************************************************** */

$route['defaultLoginRole/(:num)'] = 'LoginController/defaultLoginRole/$1';

/* * **************Dcoument releted all work will be here*************** */
$route['view-document-file/(:any)/(:any)'] = 'DocsController/viewUploadedDocument/$1/$2';
$route['download-document-file/(:any)/(:any)'] = 'DocsController/donwloadUploadedDocument/$1/$2';
$route['direct-document-file/(:any)'] = 'DocsController/directViewDocument/$1';
/* * ************************************************************** */

/* * *********************** Customer Feedback **************************************** */

$route['customer-feedback(/:any)?'] = 'FeedbackController/index/$1';
$route['view-customer-feedback/(:any)'] = 'FeedbackController/view_customer_feedback/$1';
$route['get-customer-feedback'] = 'FeedbackController/get_customer_feedback';
/* * ************************************************************** */

/* * *******************Kyc Docs Download*************************************************** */
$route['loan-kyc-docs'] = 'Admin/KycZipController/index';
$route['loan-kyc-docs/(:any)'] = 'Admin/KycZipController/index';
$route['loan-kyc-download-zip'] = 'Admin/KycZipController/download_loandocs_zip';
/* * ************************************************************** */

/* * *******************Performance Popup****2023-03-20************************** */
$route['get-sanction-performance'] = 'PerformanceController/SanctionPerformancePopup';
/* * ************************************************************** */

/* * *********************BRE RULE ENGINE************************************** */
$route['call-bre-rule-engine'] = 'BreController/gernerateBreResult';
$route['get-bre-rule-result'] = 'BreController/getBreRuleResult';
$route['save-bre-manual-decision'] = 'BreController/saveBreManualDecision';
$route["bre-edit-application"] = 'BreController/breEditApplication';
/*****************************************************************/ 


