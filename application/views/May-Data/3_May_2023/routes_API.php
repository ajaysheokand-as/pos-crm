<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/* * ***********************Website******************************************************** */

$route['UserVerificationNew']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppSaveRegistration';
$route['OTPVerifyNew']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppOtpVerify';
$route['resendOtp']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppResendOTP';
$route['SaveCustomer']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppSaveCustomer';
$route['SaveLeadApp']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppApplyLoan';
$route['userresidencedetails']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppCustomerResidenceDetails';
$route['saveOfficeAddress']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppSaveOfficeDetails';
$route['saverefrencedetails']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppSaveReferenceDetails';
$route['savebankdetils']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppSaveBankDetails';
$route['uploadDocuments']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppUploadDocuments';
$route['thankyou']['post'] = 'Api/LMSSanctionApp/LoginController/thankyou';

$route['getCityName']['post'] = 'Api/LMSSanctionApp/LoginController/getCityName';
$route['refrenceDetails']['post'] = 'Api/LMSSanctionApp/LoginController/getReferenceMasterList';
$route['getallifsc']['post'] = 'Api/LMSSanctionApp/LoginController/getIFSCMasterList';
$route['getAllRecordsFromMobile']['post'] = 'Api/LMSSanctionApp/LoginController/qdeAppAllRecordsFromMobile';
$route['masterAPI']['post'] = 'Api/LMSSanctionApp/MasterController/masterAPI';
$route['getState']['post'] = 'Api/LMSSanctionApp/LoginController/GetState';
$route['getCity']['post'] = 'Api/LMSSanctionApp/LoginController/GetCity';
$route['getUserType']['post'] = 'Api/LMSSanctionApp/LoginController/getUserType';
$route['residenceType']['post'] = 'Api/LMSSanctionApp/LoginController/residenceType';
$route['getPupposeOfLoan']['post'] = 'Api/LMSSanctionApp/LoginController/getPupposeOfLoan';
$route['getbankdetails']['post'] = 'Api/LMSSanctionApp/LoginController/getbankdetails';

/* * ******************************************************************************* */



/* * **********************Android App V1 URLs*************************************** */

$route['checkAppVersionARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppVersionCheck';
$route['getOTPARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveRegistration';
$route['getOTPVerifyARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppOtpVerify';
$route['getResendOtpARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppResendOTP';
$route['savePersonalDetailsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveCustomer';
$route['applyLoanQuoteARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppApplyLoan';
$route['saveResidenceAddressARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveResidenceAddresss';
$route['saveOfficeAddressARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveOfficeDetails';
$route['saveReferenceDetailsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveReferenceDetails';
$route['saveBankDetailsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppSaveBankDetails';
$route['uploadDocumentsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppUploadDocuments';
$route['requiredUploadedDocsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppRequiredUploadedDocs';
$route['thankyouARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppThankYou';

$route['getCityNameARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppGetCityName';
$route['getReferenceMasterARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/getReferenceMasterList';
$route['getSearchIFSCCodeARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppGetIFSCMasterList';
$route['getCustomerDetailsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppGetLeadDetails';
$route['getMasterDataARD']['post'] = 'Api/ANDROIDAPP/MasterController/masterAPI';
$route['getStateARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/GetState';
$route['getCityARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/GetCity';
$route['getAllCityARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/getAllCity';
$route['getUserTypeARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/getUserType';
$route['residenceTypeARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/residenceType';
$route['getPupposeOfLoanARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/getPupposeOfLoan';
$route['getBankDetailsARD']['post'] = 'Api/ANDROIDAPP/Android1Controller/qdeAppGetBankDetails';

/* * ******************************************************************************* */

/* * **************************IOS LOANWALLE APP URLS Version 1****************************** */
//Version Check
$route['checkAppVersionIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppVersionCheck';
//master
$route['getCityNameIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppGetCityName';
$route['getSearchIFSCCodeIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppGetIFSCMasterList';
$route['getBankDetailsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppGetBankDetails';
$route['getMasterDataIOS']['post'] = 'Api/IOSAPP/IOSMasterController/qdeAppMasterAPI';
//app actions
$route['getCustomerDetailsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppGetLeadDetails';
$route['getOTPIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveRegistration';
$route['getResendOtpIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppResendOTP';
$route['getOTPVerifyIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppOtpVerify';
$route['savePersonalDetailsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveCustomer';
$route['applyLoanQuoteIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppApplyLoan';
$route['saveResidenceAddressIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveResidenceAddresss';
$route['saveOfficeAddressIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveOfficeDetails';
$route['saveBankDetailsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveBankDetails';
$route['saveReferenceDetailsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppSaveReferenceDetails';
$route['requiredUploadedDocsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppRequiredUploadedDocs';
$route['uploadDocumentsIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppUploadDocuments';
$route['thankYouIOS']['post'] = 'Api/IOSAPP/IOSQDE1Controller/qdeAppThankYou';

//**************************************  Collex APP API **************************************//

$route['collexAuth']['post'] = 'Api/CollectionApp/CollectionController/collexAuth';
$route['collexAuthLogout']['post'] = 'Api/CollectionApp/CollectionController/collexAuthLogout';
$route['collexAuthOtpVerification']['post'] = 'Api/CollectionApp/CollectionController/collexAuthOtpVerification';
$route['collexGetTotalCollection']['post'] = 'Api/CollectionApp/CollectionController/collexGetTotalCollection';
$route['collexGetLoanDetails']['post'] = 'Api/CollectionApp/CollectionController/collexGetLoanDetails';
$route['collexGetUserProfile']['post'] = 'Api/CollectionApp/CollectionController/collexGetUserProfile';
$route['collexGetVisitAndManagerDetails']['post'] = 'Api/CollectionApp/CollectionController/collexGetVisitAndManagerDetails';
$route['collexGetRepaymentDetails']['post'] = 'Api/CollectionApp/CollectionController/collexGetRepaymentDetails';
$route['collexFeStartEndVisit']['post'] = 'Api/CollectionApp/CollectionController/collexFeStartEndVisit';
$route['collexUpdateFollowupAndCollection']['post'] = 'Api/CollectionApp/CollectionController/collexUpdateFollowupAndCollection';
$route['collexReturnFromVisit']['post'] = 'Api/CollectionApp/CollectionController/collexReturnFromVisit';
$route['collexGetListPaymentMode']['post'] = 'Api/CollectionApp/CollectionController/collexGetListPaymentMode';
$route['collexGetListMasterStatus']['post'] = 'Api/CollectionApp/CollectionController/collexGetListMasterStatus';
$route['collexAppVersionCheck']['post'] = 'Api/CollectionApp/CollectionController/collexAppVersionCheck';

$route['test_api']['post'] = 'Api/CollectionApp/CollectionController/test_api';

//************************************** API FOR PRODUCTION APP **************************************// 
//$route['userRegistration']['post'] = 'Api/ProdApi/ProdController/userRegistration';
//$route['userVerificationProd']['post'] = 'Api/ProdApi/ProdController/userVerificationProd'; //
//$route['vinSaveTasks']['post'] = 'Api/ProdApi/ProdController/vinSaveTasks';
//
//$route['getStatepro']['post'] = 'Api/ProdApi/ProdController/getStatepro';
//$route['getCitypro']['post'] = 'Api/ProdApi/ProdController/getCitypro';

/* * ************Connector Exposed**************** */
$route['getQdeAppState']['get'] = 'Connector/QdeApi/getState';
$route['getQdeAppCity']['post'] = 'Connector/QdeApi/getCity';
$route['saveQdeApp']['post'] = 'Connector/QdeApi/saveQdeApp';
$route['otpVerifyQdeApp']['post'] = 'Connector/QdeApi/otpVerifyQdeApp';
/* * ****************************************** */

//**************************************  Loanwalle Feedback form API **************************************//

$route['get_customer_details']['post'] = 'Api/FeedbackApi/get_customer_details';
$route['save_customer_feedback']['post'] = 'Api/FeedbackApi/save_customer_feedback';

/* * ******************Chat Bot API URL**************** */

$route['saveQdeAppChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppSaveRegistration';
$route['getOTPVerifyChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppOtpVerify';
$route['getResendOtpChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppResendOTP';
$route['getReLoanChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppReLoanRequest';
$route['getLoanStatusChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppLoanStatus';
$route['getLeadStatusChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppLeadStatus';
$route['getMasterDataChatBot']['post'] = 'Api/ChatBot/ChatBotController/qdeAppMasterAPI';

/* * ****** RUNO Webhook *************** */
$route['runo_call_interaction']['post'] = 'Api/RUNOAPP/RunoAppController/webhookRunoAddInteraction';
/* * ****** RUNO Webhook *************** */

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
