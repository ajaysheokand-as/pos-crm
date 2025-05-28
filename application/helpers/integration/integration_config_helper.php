<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('integration_config')) {

    function integration_config($api_type = "", $api_sub_type = "") {


        $envSet = ENVIRONMENT;

        $config_arr = array();

        switch ($api_type) {

            CASE "ICICI_DISBURSEMENT_CALL" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "ICICI BANK";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";
                // $config_arr['ApiKey'] = "u8zdY9yriHGToh466AyQeLzK1VO0DdAn";
                // $config_arr['ApiPassCode'] = "447c4524c9074b8c97e3a3c40ca7458d";
                // $config_arr['ApiBCID'] = "IBCKer00055";
                $config_arr['ApiKey'] = "ujEjRsMHJWURm9xfAmGyDwuvacEI2cTu";
                $config_arr['ApiPassCode'] = "447c4524c9074b8c97e3a3c40ca7458d";
                $config_arr['ApiBCID'] = "IBCKer00055";

                $config_arr['RPMiddleWareUrl'] = "http://loanwallefintech.in:8096/middleware/service/";

                if ($envSet == "production") {
                    $config_arr['ApiKey'] = "7b9d4611-f7cd-4b83-9a94-3d6C000029";
                    $config_arr['ApiPassCode'] = "1fb6fa09ebaa4f2b941a7e005cc184e2";
                    $config_arr['ApiBCID'] = "IBCNAM00529";
                    $config_arr['RPMiddleWareUrl'] = "http://localhost:8096/middleware/service/";
                }

                if ($api_sub_type == "DO_DISBURSEMENT") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/v1/composite-payment";
                    }
////                } else if ($api_sub_type == "TRANSACTION_INQUIRY_IMPS") {
//                    if ($envSet == "development") {
//                        $config_arr['ApiUrl'] = "https://apigwuat.icicibank.com:8443/api/v1/imps/tran-status";
//                    } else if ($envSet == "production") {
////                    $config_arr['ApiUrl'] = "https://api.icicibank.com:8443/api/v1/imps/tran-status";
//                    }
                } else if ($api_sub_type == "TRANSACTION_STATUS") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://apibankingonesandbox.icicibank.com/api/v1/composite-status";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/v1/composite-status";
                    }
                } else if ($api_sub_type == "TRANSACTION_INCREMENTAL_STATUS_NEFT") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://apibankingonesandbox.icicibank.com/api/v1/CIBNEFTStatus";
                    } else if ($envSet == "production") {
                   $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/v1/CIBNEFTStatus";
                    }
                } else if ($api_sub_type == "BENEFICIARY_REGISTRATION") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://apigwuat.icicibank.com:8443/compPay/CIB/v1/BeneAddition";
                    } else if ($envSet == "production") {
                   $config_arr['ApiUrl'] = "https://api.icicibank.com:8443/api/compPay/CIB/v1/BeneAddition";
                    }
                } else if ($api_sub_type == "CIB_REGISTRATION") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://apigwuat.icicibank.com:8443/api/compPay/CIB/v1/Registration";
                    } else if ($envSet == "production") {
                   $config_arr['ApiUrl'] = "https://api.icicibank.com:8443/api/compPay/CIB/v1/Registration";
                    }
                }
                break;

            CASE "QUICK_DIALER" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "CUBE SOFTWARE";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";

                $config_arr['RPMiddleWareUrl'] = "";

                if ($envSet == "production") {
                    $config_arr['RPMiddleWareUrl'] = "";
                }

                if ($api_sub_type == "SAVE_CALL") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://raphsody.in/QuickCallRaphsody/Click2Call58.php";
                    } else if ($envSet == "production") {
//                        $config_arr['ApiUrl'] = "https://raphsody.in/QuickCallRaphsody/Click2Call58.php";
                    }
                }
                break;

            CASE "BANK_ANALYSIS" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "NOVEL PATTERN";
                $config_arr['UserName'] = "prod@bharat";
                $config_arr['UserPassword'] = "bharatL@prod876";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";

                // $config_arr['ApiToken'] = "API://Bdiz+uqvavZsaAAmfsepuRmlEOb3QIedSUvAqBvh1uSauh+/2ynoOmEARBZaXGR0";
                $config_arr['ApiToken'] = "API://2usi98zZoXygumUjqT8tzdfY16GwQi8Hdi6f9Euxz+91hbClpM5IdhK9KBc1pjLb";

                if ($envSet == "production") {
                    // $config_arr['ApiToken'] = "API://+zRrr3yyzbayo2Q4478Ji+y4YdY9MmlNPOKJx8WVP15Qb2bgwxFqrn7K0LgWIly1"; // New key
                    $config_arr['ApiToken'] = "API://2usi98zZoXygumUjqT8tzdfY16GwQi8Hdi6f9Euxz+91hbClpM5IdhK9KBc1pjLb"; // New key
                }

                if ($api_sub_type == "UPLOAD_DOC") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://cartbi.com/api/upload";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://cartbi.com/api/upload";
                    }
                } else if ($api_sub_type == "DOWNLOAD_DOC") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://cartbi.com/api/downloadFile";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://cartbi.com/api/downloadFile";
                    }
                }
                break;

            CASE "EMAIL_VALIDATION" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "MAILGUN";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";

                if ($api_sub_type == "MAILGUN_EMAIL_VALIDATE") {

                    $config_arr['Provider'] = "MAILGUN";

                    $config_arr['ApiToken'] = "583dcf87b30cba9fd08643270c0b9fca-f2340574-bbc27f08";

                    if ($envSet == "production") {
                        $config_arr['ApiToken'] = "583dcf87b30cba9fd08643270c0b9fca-f2340574-bbc27f08";
                    }


                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://api.mailgun.net/v4/address/validate";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://api.mailgun.net/v4/address/validate";
                    }
                } elseif ($api_sub_type == 'SENDGRID_EMAIL_VALIDATE') {

                    $config_arr['Provider'] = "SENDGRID";

                    $config_arr['ApiToken'] = "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";

                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://api.sendgrid.com/v3/validations/email";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://api.sendgrid.com/v3/validations/email";
                    }
                }
                break;

            CASE "BANK_ACCOUNT_VERIFICATION" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "NU PAY";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";

                if ($api_sub_type == "NUPAY_AUTH_TOKEN") {
                    $config_arr['ApiKey'] = "YzE2YTUzMjNmYTQ3NTUiMGQ5NTgzYzM0ZmQzNTZU";
                    $config_arr['ApiUrl'] = "https://nupaybiz.com/Auth/token";
                } else if ($api_sub_type == "NUPAY_PENNY_DROP") {
                    $config_arr['ApiKey'] = "YzE2YTUzMjNmYTQ3NTUiMGQ5NTgzYzM0ZmQzNTZU";
                    $config_arr['ApiUrl'] = "https://nupaybiz.com/api/Validate/getAccountVerificationWithIFSC";
                }
                break;

            CASE "RUNO_CALL_CRM" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "RUNO SOFTWARE";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";
                $config_arr['ApiKey'] = "MWJrNDVvd29xM3k3MzFhMWc=";
                $config_arr['RPMiddleWareUrl'] = "";

                if ($api_sub_type == "CALL_ALLOCATION") {
                    $config_arr['ApiUrl'] = "https://api.runo.in/v1/crm/allocation";
                }
                break;
            CASE "SMARTPING_CALL_CRM" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "SMARTPING SOFTWARE";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                $config_arr['ApiUserId'] = "";
                $config_arr['ApiPassword'] = "";
                $config_arr['ApiKey'] = "KeONrVD4uglIRiv2gwsglcm6hOjiyV3j9R6BMKGg11ZTjvWA1o";
                $config_arr['LocationId'] = "ccdd646f-5bfc-44e0-93af-fedf3562b625";
                $config_arr['RPMiddleWareUrl'] = "";

                if ($api_sub_type == "CLICK_TO_CALL") {
                    $config_arr['ApiUrl'] = "https://smartdevmuni.vispl.in/cc/api/v1/click-to-call";
                } elseif ($api_sub_type == "BULK_UPLOAD") {
                    $config_arr['ApiUrl'] = "https://smartdevmuni.vispl.in/cc/api/v1/contacts";
                }
                break;
            CASE "FB_CALL_CAMPAIGN" :
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "FACEBOOK";
                $config_arr['PageID'] = 105632195732824;
                $config_arr['PageAccessToken'] = "EABSGZAvyHNKEBAHuGTfmAgx8Emc87nTOiCzsfjmiUJr4EJphNO5DUD9VAMWZApJ3iP4pVWneZA7obIdGtTLJs1gtlQ4zThZAQhX9u3HYtaEw3UCsiGM1nhKZBXer9FoeIKj3XYcMHzZB9U3sLpRf8M1nhjMN7AdzAunLZAZCnGvoVydvD4nOWJZAAy3Kn7MRJwZCCR2jPjQbxwTAZDZD";
                $config_arr['RPMiddleWareUrl'] = "";

                if ($api_sub_type == "CALL_PAGE") {
                    $config_arr['ApiUrl'] = "https://graph.facebook.com/v16.0/";
                } elseif ($api_sub_type == "CALL_FORM") {
                    $config_arr['ApiUrl'] = "https://graph.facebook.com/v16.0/";
                }
                break;
            default :
                $config_arr['Status'] = 0;
                $config_arr['ErrorInfo'] = "LW : Invalid config value passed";
                break;
        }


        return $config_arr;
    }

}

if (!function_exists('MiddlewareApiReqEncrypt')) {


    function MiddlewareApiReqEncrypt($url, $Provider = "", $apiName = "", $data = "", $product_id = 1, $lead_id = 0) {

        $envSet = ENVIRONMENT;
        $ci = & get_instance();
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $curlError = "";
        $errorMessage = "";

        $return_array = array("status" => 0, "errors" => "Encryption: Something went wrong. Please contact to LW team.", "output_data" => "");

        $request_array = array(
            "environment" => "UAT",
            "plainText" => base64_encode($data),
            "requestFor" => "encrypt",
            "clientId" => $Provider,
            "apiName" => $apiName
        );

        $apiHeaders = array('Content-Type:application/json');

        if ($envSet == "production") {
            $request_array['environment'] = "PROD";
        }

        $apiRequestJson = json_encode($request_array);

        $return_array["raw_request"] = $apiRequestJson;
//        $input_data = "ew0KICAgICJyZXF1ZXN0SWQiOiAiIiwNCiAgICAic2VydmljZSI6ICIiLA0KICAgICJlbmNyeXB0ZWRLZXkiOiAib2xKTHMyaEpPS1p0S24yemF4MitHQnRhMUFUN3FGMHBaNk1YdTlPWTdPeVN3QXBlUERjV00zNDRNSGFEdVhxaEhWN3ZOemFCNW9nbmw5eE1hZmJwQkF4VW94UGRidFNGcHpXY0JpRWJBZDV5MlRmTVJWNUZxTUJ4dVlZb2x5MmVTR3plZzlDT2YzZVlPRVN6c2R5UTFaUm1NWUQ0ZytrYlhTcFZ0c2pvVVNmeE8zby9GdW02UWFnTWhXMmt1VDFsTm44RWF5M0srR1U0SVBibVY4cXNGM0xOSlhyVDhnQkdoYUJoN2k1WWZxMXp2SkhTOEtRR2NPSEYydEtlQnNaeHpNTUs3T3cwOHZSeTAxcVdBaVl3ZWs0SUhmTk9mdzJiK0RCSjhRQlVDRlRzTFJ0Qm83OVdiNFdFSWZVZU56clRnYjNybE9sdDFHbnIwSkFtVmtkRmNuUFpQY3MyUXF0ZDBGVkk5TENFb1ZPaXZrMGFscWxpNGxrMlhMWDJ2d3l6bTcwaVlDaldCcFlQVmIyaW52MCtoMXpraXBQQi9XVWRvSkMxSDJtSzZJMHovVmNwakxLdGdYclhVVWNkMzhiL0hUbG0xU3ZBcVRDamJoQ3hldkw2eHVDSUlKcmtyYWJqanExMTBBYTRULzkwSUEwd1YzbUpYcmdOKzJUMGh0S2QvR2JjMGZLRUJ0WUxFQ2daTVFVYSs3MUxDVnYyZVA3M05GR1lVcy9IY1I0VzJjR1lpdXBPUzJiakk3eDF0d2FQb1JGK1F0QXBMNXI3UVEyR1JoQ2tKRGs5aXVIVklGRVRRVjRxUUdIQ2Q4dFJ5Rzl4VFI5aWduMXdFVUM4UTZST2FvMVhXWkpoTXhhVDlGR20xQnVIeHlnWFF4RktUeUxjdWszVmVaZXF4NU09IiwNCiAgICAib2FlcEhhc2hpbmdBbGdvcml0aG0iOiAiTk9ORSIsDQogICAgIml2IjogIiIsDQogICAgImVuY3J5cHRlZERhdGEiOiAiMGlUUHdnbmlMRHJlNkxGYU5vVWs5dVBRSElkUTZ6ZXczT3lIemk3MnlkRXBCK2tEbXl0NEN4ek01Y3FDWkphTGx2YW4wTkp4eDYyRXk1T1JNWGR3K0hCSzkwNzM5aVo1TWFRcHFrL3FOci85eW9CS28xMG9sKzhad1BFODV6KzZ6Y2tDKzNnYUs2NUIrYU52cHRUL3JsQjRuOTBDWlBhNENlQnVPcjdmN3BRVEw0SHdXeE90UnhZVktuWStUT0NrOG12SU4wMnh1Ni82eWwraEVBUnEwbXFEekJzL1hKZHNxekFQM3VvMWp3RlFKa1V0QW1jQ2FLdFEyZ2xldzNacXV0Z0xaMzdocEZ5RVFJTVljN0lxWEtXNTN0M3FPc1JrOGtvUy8rMkZsdExaU2E1YUhyU3F1Y0d3d3BIMDMrbENncW5Pa1Z0Mkl3Tk96bi9kQjh1bGw2SEMxbWIvV2tmQ1E4dWk2Ym1Nb1pQRlNBeUd1MC9Pb0xuRHhJUHFnbWdiazA3LzVobmoycTZaRDRNWkNjQnUrYml3ZlAwZkFMZWl3czZVUlVmT1V2UjcyUW5GbktQUmVvck5nTmZFZjRzMiIsDQogICAgImNsaWVudEluZm8iOiAiIiwNCiAgICAib3B0aW9uYWxQYXJhbSI6ICIiDQp9";
//        $return_array = array("status" => 1, "errors" => "", "output_data" => base64_decode($input_data));
//        return $return_array;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
//        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        $status = curl_getinfo($curl);
//        echo json_encode($status, JSON_PRETTY_PRINT);
        $apiResponseJson = curl_exec($curl);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        $return_array["raw_response"] = $apiResponseJson;

        if (curl_errno($curl)) {

            $apiStatusId = 3;
            $curlError = "Encryption: (" . curl_errno($curl) . ") " . curl_error($curl) . " to url " . $url;
            $return_array["errors"] = $curlError; //"Some error occurred during java lang.e1. Please try again..";
            curl_close($curl);
        } else {
            curl_close($curl);

            $apiResponseData = json_decode($apiResponseJson, true);

            if ($apiResponseData["status"] == "true") {
                $apiStatusId = 1;
                $return_array["errors"] = "";
                $return_array["output_data"] = base64_decode($apiResponseData["response"]);
            } else {
                $apiStatusId = 2;
                $errorMessage = $apiResponseData['message'];
                $return_array["errors"] = "Some error occurred during java lang.e2. Please try again..";
            }
        }

        $insertApiLog = array();
        $insertApiLog["middleware_product_id"] = $product_id;
        $insertApiLog["middleware_method_id"] = 1;
        $insertApiLog["middleware_api_name"] = $apiName;
        $insertApiLog["middleware_lead_id"] = $lead_id;
        $insertApiLog["middleware_api_status_id"] = $apiStatusId;
        $insertApiLog["middleware_request"] = addslashes($apiRequestJson);
        $insertApiLog["middleware_response"] = addslashes($apiResponseJson);
        $insertApiLog["middleware_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["middleware_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["middleware_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $ci->IntegrationModel->insert("api_java_middleware_logs", $insertApiLog);

        $return_array["status"] = $apiStatusId;

        return $return_array;
    }

}

if (!function_exists('MiddlewareApiResDecrypt')) {


    function MiddlewareApiResDecrypt($url, $Provider = "", $apiName = "", $data = "", $product_id = 1, $lead_id = 0) {


        $envSet = ENVIRONMENT;
        $ci = & get_instance();
        $ci->load->model('Integration/Integration_Model', 'IntegrationModel');

        $apiStatusId = 0;
        $apiRequestDateTime = date("Y-m-d H:i:s");
        $curlError = "";
        $errorMessage = "";

        $return_array = array("status" => 0, "errors" => "Decryption: Something went wrong in decryption. Please contact to LW team.", "output_data" => "");

        $request_array = array("environment" => "UAT", "plainText" => base64_encode($data), "requestFor" => "decrypt", "clientId" => $Provider, "apiName" => $apiName);

        if ($envSet == "production") {
            $request_array['environment'] = "PROD";
        }

        $apiHeaders = array('Content-Type:application/json');
        $apiRequestJson = json_encode($request_array);

        $return_array["raw_request"] = $apiRequestJson;

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $apiResponseJson = curl_exec($curl);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        $return_array["raw_response"] = $apiResponseJson;

        if (curl_errno($curl)) {
            $curlError = "Decryption: (" . curl_errno($curl) . ") " . curl_error($curl);
            $return_array["errors"] = "Some error occurred during java lang.d1. Please try again..";
            $apiStatusId = 3;
            curl_close($curl);
        } else {
            curl_close($curl);
            $apiResponseData = json_decode($apiResponseJson, true);

            if ($apiResponseData["status"] == "true") {
                $apiStatusId = 1;
                $return_array["errors"] = "";
                $return_array["output_data"] = base64_decode($apiResponseData["response"]);
            } else {
                $apiStatusId = 2;
                $errorMessage = $apiResponseData['message'];
                $return_array["errors"] = "Some error occurred during java lang.d2. Please try again..";
            }
        }

        $insertApiLog = array();
        $insertApiLog["middleware_product_id"] = $product_id;
        $insertApiLog["middleware_method_id"] = 2;
        $insertApiLog["middleware_api_name"] = $apiName;
        $insertApiLog["middleware_lead_id"] = $lead_id;
        $insertApiLog["middleware_api_status_id"] = $apiStatusId;
        $insertApiLog["middleware_request"] = addslashes($apiRequestJson);
        $insertApiLog["middleware_response"] = addslashes($apiResponseJson);
        $insertApiLog["middleware_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
        $insertApiLog["middleware_request_datetime"] = $apiRequestDateTime;
        $insertApiLog["middleware_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

        $ci->IntegrationModel->insert("api_java_middleware_logs", $insertApiLog);

        $return_array["status"] = $apiStatusId;

        return $return_array;
    }

}
?>
