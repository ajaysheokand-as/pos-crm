<?php

function integration_config($api_type = "", $api_sub_type = "") {

    $envSet = COMP_ENVIRONMENT;

    $config_arr = array();

    switch ($api_type) {

        case "CRIF_CALL":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "CRIF";
            $config_arr['UserName'] = "";
            $config_arr['UserPassword'] = "";
            //$config_arr['RPMiddleWareUrl'] = "";
            // $config_arr['ApiUserId'] = "kasar_cpu_prd@kasarcredit.com";
            // $config_arr['ApiPassword'] = "E2ACF08723F5EBBCC6AE42383D1BEA844EEFA571";
            // $config_arr['ApiMemberId'] = "NBF0005465";
            // $config_arr['ApiSubMemberId'] = "KASAR CREDIT N CAPITAL PRIVATE LIMI";

            $config_arr['ApiUserId'] = "agrim_cpu_prd@agrimfincap.in";
            $config_arr['ApiPassword'] = "6E261285001C87D1BC9C9A6B033A474034DCC5A2";
            $config_arr['ApiMemberId'] = "NBF0005339";
            $config_arr['ApiSubMemberId'] = "AGRIM FINCAP PRIVATE LIMITED";

            // $config_arr['RPMiddleWareUrl'] = "";
            // if ($envSet == "production") {
            //     $config_arr['RPMiddleWareUrl'] = "";
            //     $config_arr['ApiUserId'] = "crif1_cpu_prd@devmunileasing.in";
            //     $config_arr['ApiPassword'] = "B9D6969BC49F199B632782F859502509F88ACA1D";
            //     $config_arr['ApiMemberId'] = "NBF0004900";
            //     $config_arr['ApiSubMemberId'] = "KASAR CREDIT N CAPITAL PRIVATE LIMIT";
            // }

            if ($api_sub_type == "REQUEST_JSON") {
                if ($envSet == "development") {
                    // $config_arr['ApiUrl'] = "https://hub.crifhighmark.com/Inquiry/doGet.service/requestResponseSync";
                    $config_arr['ApiUrl'] = "https://hub.crifhighmark.com/Inquiry/doGet.service/requestResponseSync";
                } else if ($envSet == "production") {
                    // $config_arr['ApiUrl'] = "https://hub.crifhighmark.com/Inquiry/doGet.service/requestResponseSync";
                    $config_arr['ApiUrl'] = "https://hub.crifhighmark.com/Inquiry/doGet.serviceJson/CIRProServiceSynchJson";
                }
            }
            break;

            case "SIGNZY_API":

                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "signzy";
                $config_arr['UserName'] = "";
                $config_arr['UserPassword'] = "";
                $config_arr['RPMiddleWareUrl'] = "";
                // $config_arr['ApiUserId'] = "info@salarywalle.com";
                // $config_arr['ApiPassword'] = "A1UrUUHE3ezATzr2C3u0";
                $config_arr['ApiUserId'] = "amit@agrimfin.com";
                $config_arr['ApiPassword'] = "97j0rJp7CcqkzwWCwqhY";
                $config_arr['RPMiddleWareUrl'] = "";
                // $envSet = "production";
                if ($envSet == "production") {
                    $config_arr['RPMiddleWareUrl'] = "";
                    // $config_arr['ApiUserId'] = "info@salarywalle.com";
                    // $config_arr['ApiPassword'] = "A1UrUUHE3ezATzr2C3u0";
                    $config_arr['ApiUserId'] = "amit@agrimfin.com";
                    $config_arr['ApiPassword'] = "97j0rJp7CcqkzwWCwqhY";
                    // $config_arr['ApiKey'] = "ScTTTviEmhU1EPT79VM6QV9NUHImPkBm";
                    $config_arr['ApiKey'] = "n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey";
                }
    
                if ($api_sub_type == "GET_TOKEN") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/login";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/login";
                    }
                } else if ($api_sub_type == "GET_IDENTITIY_OBJECT") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/<patron-id>/identities";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/<patron-id>/identities";
                    }
                } else if ($api_sub_type == "PAN_FETCH") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetchV2";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetchV2";
                    }
                } else if ($api_sub_type == "PAN_OCR") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetch";
                        $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/pan/extractions";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetch";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/extractions";
                    }
                } else if ($api_sub_type == "AADHAAR_OCR") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/aadhaar/extraction";
                        $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/aadhaar/extraction";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/snoops";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/aadhaar/extraction";
                    }
                } else if ($api_sub_type == "AADHAAR_MASK") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/aadhaar/extraction-masking";
                        $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/aadhaar/maskers";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/snoops";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/aadhaar/maskers";
                    }
                } else if ($api_sub_type == "GET_ESIGN_TOKEN") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://esign-preproduction.signzy.tech/api/customers/login";
                    } else if ($envSet == "production") {
                        $config_arr['ApiUrl'] = "https://esign.signzy.tech/api/customers/login";
                    }
                } else if ($api_sub_type == "UPLOAD_ESIGN_DOCUMENT") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://persist.signzy.tech/api/base64";
                        $config_arr['ApiUrl'] = "https://preproduction-persist.signzy.tech/api/base64";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://persist.signzy.tech/api/base64";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/persist/base64";
                    }
                } else if ($api_sub_type == "AADHAAR_ESIGN") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://esign.signzy.tech/api/customers/customerid/aadhaaresigns";
                        $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/contract/initiate";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://esign.signzy.tech/api/customers/customerid/aadhaaresigns";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/contract/initiate";
                    }
                } else if ($api_sub_type == "DOWNLOAD_ESIGN_DOCUMENT") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://esign-preproduction.signzy.tech/api/callbacks";
                        $config_arr['ApiUrl'] = "https://api-preproduction.signzy.app/api/v3/contract/pullData";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://esign.signzy.tech/api/callbacks";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/contract/pullData";
                    }
                } else if ($api_sub_type == "DIGILOCKER") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/digilockers";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/digilocker-v2/createUrl";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/digilockers";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/digilocker-v2/createUrl";
                    }
                } else if ($api_sub_type == "OFFICE_EMAIL_VERIFICATION") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/emailverificationsv2";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/email/verificationV2";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/emailverificationsv2";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/email/verificationV2";
                    }
                } else if ($api_sub_type == "BANK_ACCOUNT_VERIFICATION") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/bankaccountverification/bankaccountverifications";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/bankaccountverifications";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/bankaccountverification/bankaccountverifications";
                    }
                } else if ($api_sub_type == "PERSONAL_EMAIL_VERIFICATION") {
                    if ($envSet == "development") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/emailvalidations";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/email/verificationV2";
                    } else if ($envSet == "production") {
                        // $config_arr['ApiUrl'] = "https://signzy.tech/api/v2/patrons/customerid/emailvalidations";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/email/verificationV2";
                    }
                } else if ($api_sub_type == "PAN_FETCHV3") {
                    if ($envSet == "development") {
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetchV2";
                    } else if ($envSet == "production") {
                        // $config_arr['Token'] = "ScTTTviEmhU1EPT79VM6QV9NUHImPkBm";
                        $config_arr['Token'] = "n2SCMAhKmpqyDLzqd0B944ifZ4BbTZey";
                        $config_arr['ApiUrl'] = "https://api.signzy.app/api/v3/pan/fetch";
                    }
                }
                break;

        case "REPAY_API":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "ICICI_EAZYPAY";
            $config_arr['RPMiddleWareUrl'] = "http://loanwallefintech.in:8096/middleware/service/";
            $config_arr['ApiKey'] = 2408430522005001;
            $config_arr['MerchantId'] = 242204;
            $config_arr['ApiUrl'] = "https://eazypay.icicibank.com/EazyPG?merchantid=" . $config_arr['MerchantId'];
            $config_arr['Paymode'] = 9;
            $config_arr['ReferenceNo'] = mt_rand();
            $config_arr['ReturnURL'] = "https://www.bharatloan.com/loan/status1";

            if ($envSet == "production") {
                $config_arr['ApiKey'] = 2408430522005001;
                $config_arr['MerchantId'] = 242204;
                $config_arr['ApiUrl'] = "https://eazypay.icicibank.com/EazyPG?merchantid=" . $config_arr['MerchantId'];
                $config_arr['Paymode'] = 9;
                $config_arr['ReferenceNo'] = date("YmdHis") . rand(1000, 9999);
                $config_arr['ReturnURL'] = "https://www.bharatloan.com/loan/status1";
                $config_arr['RPMiddleWareUrl'] = "http://localhost:8096/middleware/service/";
            }
            break;

        case "SMS_API":
            $config_arr['Status'] = 0;
            // $config_arr['Provider'] = "";
            // $config_arr['username'] = "";
            // $config_arr['password'] = "";
            // $config_arr['type'] = 0;
            // $config_arr['PE_ID'] = "";
            // $config_arr['dlr'] = '';
            // $config_arr['ApiUrl'] = "";

            // if ($api_sub_type == "SMS_WHISTLE") {
            //     $config_arr['Status'] = 1;
            //     $config_arr['Provider'] = "Whistle";
            //     $config_arr['username'] = "Kasarcre";
            //     $config_arr['password'] = "8f424c59d1XX";
            //     $config_arr['type'] = 0;
            //     $config_arr['PE_ID'] = "1707171353065890872";
            //     $config_arr['dlr'] = 1;
            //     if ($envSet == "development") {
            //         $config_arr['ApiUrl'] = "http://sms.whistle.mobi/sendsms.jsp?";
            //     } else if ($envSet == "production") {
            //         $config_arr['ApiUrl'] = "http://sms.whistle.mobi/sendsms.jsp?";
            //     }
            // } else if ($api_sub_type == "SMS_VAPIO") {
            //     $config_arr['Status'] = 1;
            //     $config_arr['Provider'] = "Vapio";
            //     $config_arr['username'] = "kasar";
            //     $config_arr['password'] = "kasar@123";
            //     $config_arr['type'] = 0;
            //     $config_arr['PE_ID'] = "1701171300552626064";
            //     $config_arr['dlr'] = 1;
            //     $config_arr['ApiUrl'] = "https://vapio.in/api.php?";
            // }
            if ($api_sub_type == "SMS_NIBUS") {
                $config_arr['Status'] = 1;
                $config_arr['Provider'] = "Nimbus";
                $config_arr['user'] = "namanfinlenet";
                $config_arr['authkey'] = "92JPficG1956";
                // $config_arr['type'] = 0;
                $config_arr['entityid'] = "1201159134511282286";
                $config_arr['rpt'] = 1;
                $config_arr['ApiUrl'] = "http://nimbusit.net/api/pushsms?";
            }

            break;

        case "WHATSAPP_API":
            $config_arr['Status'] = 1;
            if ($api_sub_type == "WHATSAPP_API_AISENSY") {
                $config_arr['Provider'] = "Aisensy";
                $config_arr['ApiUrl'] = "https://backend.api-wa.co/campaign/smartping/api";
                $config_arr['apiKey'] = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY2ZWU3ZDQ4YjJlNjRjMGI3ODU2NDJiMiIsIm5hbWUiOiIgS2FzYXIgQ3JlZGl0ICYgQ2FwaXRhbCBQcml2YXRlIExpbWl0ZWQiLCJhcHBOYW1lIjoiQWlTZW5zeSIsImNsaWVudElkIjoiNjZlZTdkNDdiMmU2NGMwYjc4NTY0MmFjIiwiYWN0aXZlUGxhbiI6IkJBU0lDX01PTlRITFkiLCJpYXQiOjE3Mjg0MjYwNzN9.o8V9UxBkTrJiQiqpvXtFdRw5aq85x5Fuu8ez2cosbi8";
            } else if ($api_sub_type == "WHATSAPP_API_WHISTLE") {
                $config_arr['Provider'] = "Whistle";
                $config_arr['ApiUrl'] = "https://partnersv1.pinbot.ai/v3/491310100730713/messages";
                $config_arr['apiKey'] = "3fb59eb7-a31e-11ef-bb5a-02c8a5e042bd";
            } else if ($api_sub_type == "WHATSAPP_API_YELLOW_AI") {
                $config_arr['Provider'] = "YELLOW AI";
                $config_arr['ApiKey'] = "9AU_qAFo49uNsgSxb73tnrmrWfxBdltswgvaRYso";
                $config_arr['ApiUrl'] = "https://app.yellowmessenger.com/api/engagements/notifications/v2/push?bot=x1679053104873";
            }
            break;

        case "URL_SHORTENER_API":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "TINYURL";
            $config_arr['username'] = "";
            $config_arr['password'] = "";

            if ($api_sub_type == "TINYURL") {
                $config_arr['ApiUrl'] = "https://api.tinyurl.com/create";
                $config_arr['ApiToken'] = "M6sW9v62kXh3JzcbHitDCtIZKyB50ClvF2wUj7TuoUKzOg9up1A3gRIn4DAn";
            }
            break;

        case "RUNO_CALL_CRM":
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

        case "BANK_ANALYSIS":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "NOVEL PATTERN";
            // $config_arr['UserName'] = "prod@salarytime";
            $config_arr['UserName'] = "prod@speedo";
            // $config_arr['UserPassword'] = "on_time@prod321";
            $config_arr['UserPassword'] = "sPeed@prod_987";
            $config_arr['RPMiddleWareUrl'] = "";
            $config_arr['ApiUserId'] = "";
            $config_arr['ApiPassword'] = "";

            // $config_arr['ApiToken'] = "API://ofbJDI/UjZYuUhxFOTYLsoxhi5Jy2OKz22hiKICrt2/88/NrejmPFWsdg4yYy1IC";
            $config_arr['ApiToken'] = "API://ZA86NPP9OxWtsZag8sLwkymwnmMuk6XxEJ1LmZCT4GEY/7glhJhunUJvkL6/FlkD";

            if ($envSet == "production") {
                // $config_arr['ApiToken'] = "API://ofbJDI/UjZYuUhxFOTYLsoxhi5Jy2OKz22hiKICrt2/88/NrejmPFWsdg4yYy1IC"; // New key
                $config_arr['ApiToken'] = "API://ZA86NPP9OxWtsZag8sLwkymwnmMuk6XxEJ1LmZCT4GEY/7glhJhunUJvkL6/FlkD"; // New key
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

        // case "S3_BUCKET":
        //     $config_arr['Status'] = 1;
        //     $config_arr['use_ssl'] = '1';
        //     $config_arr['verify_peer'] = '1';
        //     $config_arr['version'] = 'latest';
        //     $config_arr['region'] = 'ap-south-1';
        //     $config_arr['access_key'] = 'AKIAXEVXYIVMRIOWP3MO';
        //     $config_arr['secret_key'] = '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';
        //     $config_arr['bucket_name'] = 'sot-documents';
        //     $config_arr['folder_name'] = 'upload';
        //     $config_arr['s3_url'] = 's3://sot-documents/upload/';
        //     $config_arr['access_key_envname'] = 'S3_KEY';
        //     $config_arr['secret_key_envname'] = 'S3_SECRET';
        //     $config_arr['get_from_enviroment'] = '';
        //     break;

        case "S3_BUCKET":
            $config_arr['Status'] = 1;
            $config_arr['use_ssl'] = '1';
            $config_arr['verify_peer'] = '1';
            $config_arr['version'] = 'latest';
            $config_arr['region'] = 'ap-south-1';
            $config_arr['access_key'] = 'AKIASE5KQZXHCBU65I6O';
            $config_arr['secret_key'] = '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';
            $config_arr['bucket_name'] = 'tejasloanbucket';
            $config_arr['folder_name'] = 'upload';
            $config_arr['s3_url'] = 's3://tejasloanbucket/upload/';
            $config_arr['access_key_envname'] = 'S3_KEY';
            $config_arr['secret_key_envname'] = 'S3_SECRET';
            $config_arr['get_from_enviroment'] = '';
            break;

        case "ADJUST":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "Adjust";
            $config_arr['UserName'] = "";
            $config_arr['UserPassword'] = "";
            $config_arr['RPMiddleWareUrl'] = "";
            $config_arr['ApiUserId'] = "";
            $config_arr['ApiPassword'] = "";

            $config_arr['ApiAccessToken'] = "xisxgv7g36bwCSn6ov1c";
            $config_arr['AppToken'] = "3jnaver2v328";

            if ($api_sub_type == "INSPECT_DEVICE") {
                $config_arr['ApiUrl'] = "https://api.adjust.com/device_service/api/v1/inspect_device?advertising_id=input_adid&app_token=" . $config_arr['AppToken'];
            }
            break;

        case "DIGITAP_API":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "DIGITAP API";
            $config_arr['UserName'] = "";
            $config_arr['UserPassword'] = "";
            $config_arr['RPMiddleWareUrl'] = "";
            $config_arr['ApiUserId'] = "";
            $config_arr['ApiPassword'] = "";

            // $config_arr['ApiToken'] = "Basic MTExMzI0MzM6SWdUMXNZckJiQUlleVRJTUR6OEdtRU1zVXRrUEhmQ2s=";
            $config_arr['ApiToken'] = "MTE2MzU3MjY6eFh2OTZlNGdoOW9OdHlxbXRLcmw1NFdTalFXOHVjQkQ=";

            // if ($envSet == "production") {
            //     $config_arr['ApiToken'] = "";
            // }

            if ($api_sub_type == "PAN_EXTENSION") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://svcdemo.digitap.work/validation/kyc/v1/pan_details";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://svc.digitap.ai/validation/kyc/v1/pan_details";
                }
            } else if ($api_sub_type == "DIGITAP_EKYC_CREATE_OTP") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://svc.digitap.ai/ent/v3/kyc/intiate-kyc-auto";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://svc.digitap.ai/ent/v3/kyc/intiate-kyc-auto";
                }
            } else if ($api_sub_type == "DIGITAP_EKYC_SUCCESS") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://svc.digitap.ai/ent/v3/kyc/submit-otp";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://svc.digitap.ai/ent/v3/kyc/submit-otp";
                }
            } else if ($api_sub_type == "DIGITAP_PAN_OCR") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/ocr/v1/pan";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/ocr/v1/pan";
                }
            } else if ($api_sub_type == "DIGITAP_AADHAAR_OCR") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/ocr/v1/aadhaar";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/ocr/v1/aadhaar";
                }
            } else if ($api_sub_type == "BANK_DIGITAP_ACCOUNT_VERIFICATION") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/penny-drop/v2/check-valid";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/penny-drop/v2/check-valid";
                }
            } else if ($api_sub_type == "UPLOAD_ESIGN_DOCUMENT_DIGITAP") {
                if ($envSet == "development") {
                    // $config_arr['ApiUrl'] = "https://api.digitap.ai/ent/v1/generate-esign";
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/clickwrap/v1/intiate";
                } else if ($envSet == "production") {
                    // $config_arr['ApiUrl'] = "https://api.digitap.ai/ent/v1/generate-esign";
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/clickwrap/v1/intiate";
                }
            } else if ($api_sub_type == "DOWNLOAD_ESIGN_DOCUMENT_DIGITAP") {
                if ($envSet == "development") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/clickwrap/v1/get-doc-url";
                    // $config_arr['ApiUrl'] = "https://api.digitap.ai/ent/v1/get-esign-doc";
                } else if ($envSet == "production") {
                    $config_arr['ApiUrl'] = "https://api.digitap.ai/clickwrap/v1/get-doc-url";
                    // $config_arr['ApiUrl'] = "https://api.digitap.ai/ent/v1/get-esign-doc";
                }
            }
            break;

        case "APPS_FLYER":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "APPS_FLYER";
            $config_arr['ApiKey'] = "eyJhbGciOiJBMjU2S1ciLCJjdHkiOiJKV1QiLCJlbmMiOiJBMjU2R0NNIiwidHlwIjoiSldUIiwiemlwIjoiREVGIn0.-070iBFz-9ab7TTuM9KWRvJqFX3-N_KDiilK-9iObs35q0ZYLseK3g.nucOvN6MMuaEP75E.vwkAn0jfVmM2Q-f6LX1G7WbxmuxjssGYii3L-ojJ8j2bWgNxB63Q4fLLhv6FQ6C5EZfEnOMugg1B9DRAczJ9fwT9_E-7fdeu51FMKYnBY2mlNO6Yugf0gPDOy8Y9IHl2Jv-cWx8A3bThypfNrwXDXlItqzfqF1sZkM33xaReqK9oohMSvD41i_xnyaSksZiLPgT3x8hFsfTgpgp9AFmYx82VuQTqpTNTdFdx4p9vYJsVwyHAneYbrZcak9KZIsS4RDnkM9Q80n09m7UNIyjc0xHtMk1jDcWmt9G2Y7YSelUXLjwTlC3S69DLIXpu5IuRoZWpnS-xB48NdSBZVmZGSk6BxK-x.cbk6P3Btmk8luIgxJbq7Ew";

            if ($api_sub_type == "PULL_NON_ORGANIC") {
                $config_arr['ApiUrl'] = "https://hq1.appsflyer.com/api/raw-data/export/app/app_id/in_app_events_report/v5?maximum_rows=200000&";
            } elseif ($api_sub_type == "PULL_ORGANIC") {
                $config_arr['ApiUrl'] = "https://hq1.appsflyer.com/api/raw-data/export/app/app_id/organic_in_app_events_report/v5?maximum_rows=200000&";
            } elseif ($api_sub_type == "EVENT_PUSH_CALL") {
                $config_arr['ApiKey'] = "cd8a7d40-484a-455c-9aeb-3d683387d557";
                $config_arr['ApiUrl'] = "https://api3.appsflyer.com/inappevent/app_id";
            }
            break;
        case "UPI_API":
            $config_arr['Status'] = 1;
            $config_arr['Provider'] = "ICICI_BANK";
            $config_arr['CurrencyCode'] = "INR";
            $config_arr['MerchantId'] = 8146216;
            $config_arr['SubMerchantId'] = 8146216;
            $config_arr['TerminalId'] = 6012;
            $config_arr['StaticVal'] = 8192;

            if ($api_sub_type == "IP_REQUEST") {
                $config_arr['ApiUrl'] = "https://api.ipify.org/";
                $config_arr['ApiKey'] = "fb7cc67c-1980-883b-b928-0308e571b1cc";
            } else if ($api_sub_type == "QRCODE_REQUEST") {
                $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/MerchantAPI/UPI/v0/QR3/8146216";
            } else if ($api_sub_type == "CHECK_STATUS") {
                $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/MerchantAPI/UPI/v0/TransactionStatus3/8146216";
            } else if ($api_sub_type == "COLLECTPAY_REQUEST") {
                $config_arr['ApiUrl'] = "https://apibankingone.icicibank.com/api/MerchantAPI/UPI/v0/CollectPay3/8146216";
            }
            break;
        default:
            $config_arr['Status'] = 0;
            $config_arr['ErrorInfo'] = "LW : Invalid config value passed";
            break;
    }


    return $config_arr;
}

function signzy_token_api_call($method_id, $lead_id = 0, $request_array = array(), $retrigger_call = 0) {
    common_log_writer(3, "signzy_token_api_call started | Lead Id : $lead_id | method id : $method_id");
    $envSet = COMP_ENVIRONMENT;

    //RP Variables
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "GET_TOKEN";

    if ($method_id == 3) {
        $sub_type = "GET_ESIGN_TOKEN";
    }

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    $hardcode_response = false;

    $token_string = "";
    $token_return_user_id = "";

    $leadModelObj = new LeadModel();

    try {

        //API Config
        $apiConfig = integration_config($type, $sub_type);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiUrl = $apiConfig["ApiUrl"];
        $apiUserId = $apiConfig["ApiUserId"];
        $apiPassword = $apiConfig["ApiPassword"];

        $tempDetails = $leadModelObj->checkTokenValidity($method_id); //for signzy

        if (count($tempDetails['items']) > 0) {

            $tempDetails = $tempDetails['items'][0];
            $token_response_datetime = $tempDetails['token_response_datetime'];

            $difference_in_minute = intval((strtotime(date("Y-m-d H:i:s")) - strtotime($token_response_datetime)) / 60);
            // api will be not called if last token fetched in last 45 min, token validity is 1 hours.
            if (!empty($tempDetails['token_string']) && !empty($token_response_datetime) && $difference_in_minute >= 0 && $difference_in_minute < 45) {
                $token_string = $tempDetails['token_string'];
                $token_return_user_id = $tempDetails['token_return_user_id'];
                $return_array = array();
                $return_array['status'] = 1;
                $return_array['token'] = $token_string;
                $return_array['token_user_id'] = $token_return_user_id;

                return $return_array;
            }
        }

        $apiRequestJson = '{"username": "' . $apiUserId . '","password": "' . $apiPassword . '"}';

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Request======" . $apiRequestJson;
        }

        $apiHeaders = array(
            "content-type: application/json",
            "accept-language: en-US,en;q=0.8",
            "accept: */*"
        );

        if ($hardcode_response) {
            $apiResponseJson = "";
        } else {

            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 25);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $apiResponseJson = curl_exec($curl);
        }

        if ($debug == 1) {
            echo "<br/><br/> =======Response======" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (curl_errno($curl) && !$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . "to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData['id'])) {
                    $apiStatusId = 1;
                    $token_string = $apiResponseData['id'];
                    $token_return_user_id = $apiResponseData['userId'];
                } else {
                    throw new ErrorException("accessToken was not received from Token API.");
                }
            } else {
                throw new ErrorException("Some error occurred. Please try again..");
            }
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
        $retrigger_call = ($retrigger_call == 0) ? 1 : 0;
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    $insertApiLog = array();
    $insertApiLog["token_provider"] = 1;
    $insertApiLog["token_method_id"] = $method_id; //1=> Id Proof Login, 3=> eSign Api Login
    $insertApiLog["token_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["token_api_status_id"] = $apiStatusId;
    $insertApiLog["token_request"] = addslashes($apiRequestJson);
    $insertApiLog["token_response"] = addslashes($apiResponseJson);
    $insertApiLog["token_string"] = $token_string;
    $insertApiLog["token_return_user_id"] = $token_return_user_id;
    $insertApiLog["token_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["token_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["token_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

    $leadModelObj->insertTable("api_service_token_logs", $insertApiLog);

    $return_array = array();
    $return_array['status'] = $apiStatusId;
    $return_array['token'] = $token_string;
    $return_array['token_user_id'] = $token_return_user_id;
    $return_array['errors'] = !empty($errorMessage) ? "Token Error:" . $errorMessage : "";

    return $return_array;
}

function signzy_identity_object_api_call($request_type, $lead_id = 0, $request_array = array()) {
    common_log_writer(3, "signzy_identity_object_api_call started | $lead_id");
    $envSet = COMP_ENVIRONMENT;

    //RP Variables
    $apiStatusId = 0;
    $apiRequestJson = "";
    $apiResponseJson = "";
    $apiRequestDateTime = date("Y-m-d H:i:s");
    $apiResponseDateTime = "";
    $errorMessage = "";
    $curlError = "";

    $type = "SIGNZY_API";
    $sub_type = "GET_IDENTITIY_OBJECT";

    $debug = !empty($_REQUEST['lwtest']) ? 1 : 0;
    $hardcode_response = false;

    $item_id_string = "";
    $accessToken_string = "ScTTTviEmhU1EPT79VM6QV9NUHImPkBm";
    $token_string = $request_array['token'];
    $token_return_user_id = $request_array['token_user_id'];

    $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

    $ocr_file_1 = !empty($request_array['ocr_file_1']) ? $request_array['ocr_file_1'] : "";
    $ocr_file_2 = !empty($request_array['ocr_file_2']) ? $request_array['ocr_file_2'] : "";

    $leadModelObj = new LeadModel();

    try {


        if (empty($token_string)) {
            throw new Exception("Token is missing while creating identity object.");
        }

        if (empty($token_return_user_id)) {
            throw new Exception("Token user id is missing while creating identity object.");
        }

        //API Config
        $apiConfig = integration_config($type, $sub_type);

        $apiUrl = $apiConfig["ApiUrl"] = str_replace('<patron-id>', $token_return_user_id, $apiConfig["ApiUrl"]);

        if ($debug == 1) {
            echo "<pre>";
            print_r($apiConfig);
        }

        if ($apiConfig['Status'] != 1) {
            throw new Exception($apiConfig['ErrorInfo']);
        }

        $apiRequestJsonArray = array();
        $apiRequestJsonArray["type"] = $request_type;
        $apiRequestJsonArray["email"] = "info@salarywalle.com";
        $apiRequestJsonArray["callbackUrl"] = "https://www.tejasloan.com/";
        $apiRequestJsonArray["images"] = [];

        if (!empty($ocr_file_1)) {
            $apiRequestJsonArray["images"][] = COMP_DOC_URL . $ocr_file_1;
        }

        if (!empty($ocr_file_2)) {
            $apiRequestJsonArray["images"][] = COMP_DOC_URL . $ocr_file_2;
        }


        $apiRequestJson = json_encode($apiRequestJsonArray);

        $apiRequestJson = preg_replace("!\s+!", " ", $apiRequestJson);

        if ($debug == 1) {
            echo "<br/><br/> =======Request======" . $apiRequestJson;
        }

        $apiHeaders = array(
            "Content-Type: application/json",
            "accept-language: en-US,en;q=0.8",
            "accept: */*",
            "authorization: $token_string"
        );

        if ($debug == 1) {
            echo "<br/><br/> =======Header======" . json_encode($apiHeaders);
        }

        if ($hardcode_response) {
            $apiResponseJson = "";
        } else {

            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $apiResponseJson = curl_exec($curl);
        }

        if ($debug == 1) {
            echo "<br/><br/> =======Response======" . $apiResponseJson;
        }

        $apiResponseJson = preg_replace("!\s+!", " ", $apiResponseJson);

        $apiResponseDateTime = date("Y-m-d H:i:s");

        if (curl_errno($curl) && !$hardcode_response) {
            $curlError = "(" . curl_errno($curl) . ") " . curl_error($curl) . "to url " . $apiUrl;
            curl_close($curl);
            throw new RuntimeException("Something went wrong. Please try after sometime.");
        } else {

            if (isset($curl)) {
                curl_close($curl);
            }

            $apiResponseData = json_decode($apiResponseJson, true);

            if (!empty($apiResponseData)) {

                $apiResponseData = common_trim_data_array($apiResponseData);

                if (!empty($apiResponseData['id']) && !empty($apiResponseData['accessToken'])) {
                    $apiStatusId = 1;
                    $item_id_string = $apiResponseData['id'];
                    $accessToken_string = $apiResponseData['accessToken'];
                } else {
                    throw new ErrorException("accessToken was not received from Token API.");
                }
            } else {
                throw new ErrorException("Some error occurred. Please try again..");
            }
        }
    } catch (ErrorException $le) {
        $apiStatusId = 2;
        $errorMessage = $le->getMessage();
    } catch (RuntimeException $re) {
        $apiStatusId = 3;
        $errorMessage = $re->getMessage();
        $retrigger_call = ($retrigger_call == 0) ? 1 : 0;
    } catch (Exception $e) {
        $apiStatusId = 4;
        $errorMessage = $e->getMessage();
    }

    $insertApiLog = array();
    $insertApiLog["token_provider"] = 1;
    $insertApiLog["token_method_id"] = 2;
    $insertApiLog["token_lead_id"] = !empty($lead_id) ? $lead_id : NULL;
    $insertApiLog["token_api_status_id"] = $apiStatusId;
    $insertApiLog["token_request"] = addslashes($apiRequestJson);
    $insertApiLog["token_response"] = addslashes($apiResponseJson);
    $insertApiLog["token_string"] = $item_id_string;
    $insertApiLog["token_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["token_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["token_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");
    $insertApiLog["token_user_id"] = $user_id;

    $leadModelObj->insertTable("api_service_token_logs", $insertApiLog);

    $return_array = array();
    $return_array['status'] = $apiStatusId;
    $return_array['item_id_string'] = $item_id_string;
    $return_array['access_token_string'] = $accessToken_string;
    $return_array['errors'] = !empty($errorMessage) ? "IdentityObj Error:" . $errorMessage : "";

    return $return_array;
}

function MiddlewareApiReqEncrypt($url, $Provider = "", $apiName = "", $data = "", $key = "", $lead_id = 0) {

    $envSet = COMP_ENVIRONMENT;
    $leadModelObj = new LeadModel();

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
        "encKey" => $key,
        "apiName" => $apiName
    );

    $apiHeaders = array('Content-Type:application/json');

    if ($envSet == "production") {
        $request_array['environment'] = "PROD";
    }

    $apiRequestJson = json_encode($request_array);

    $return_array["raw_request"] = $apiRequestJson;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $apiHeaders);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $apiRequestJson);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

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
    $insertApiLog["middleware_product_id"] = $key;
    $insertApiLog["middleware_method_id"] = 1;
    $insertApiLog["middleware_api_name"] = $apiName;
    $insertApiLog["middleware_lead_id"] = $lead_id;
    $insertApiLog["middleware_api_status_id"] = $apiStatusId;
    $insertApiLog["middleware_request"] = addslashes($apiRequestJson);
    $insertApiLog["middleware_response"] = addslashes($apiResponseJson);
    $insertApiLog["middleware_errors"] = ($apiStatusId == 3) ? addslashes($curlError) : addslashes($errorMessage);
    $insertApiLog["middleware_request_datetime"] = $apiRequestDateTime;
    $insertApiLog["middleware_response_datetime"] = !empty($apiResponseDateTime) ? $apiResponseDateTime : date("Y-m-d H:i:s");

    $leadModelObj->insertTable("api_java_middleware_logs", $insertApiLog);

    $return_array["status"] = $apiStatusId;

    return $return_array;
}
