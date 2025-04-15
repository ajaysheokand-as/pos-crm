<?php $this->load->view("Tasks/main_js.php"); ?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OTP Verification</title>
        <link rel="icon" type="image/*" href="<?= base_url('public'); ?>/images/18-BK_kixu8fav.png">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/bootstrap.min.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/bootstrap.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/font-awesome.min.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/style.css">
        <script src="<?= base_url('public/front'); ?>/js/jquery.3.5.1.min.js"></script>
        <script>
            $(document).ready(function () {
                $("a.close").click(function () {
                    $("#myDiv").hide();
                });

            });
        </script>
        <style>
            body {

                background-image: url('<?= base_url('public/front'); ?>/../images/login_background_img.jpg');
                background-position: center center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-size: cover;
                background-color: #464646;
            }

            .form {
                background: #fff;
                border: 1px solid #fff;
                padding: 20px;
                margin: 18%;
                box-shadow: 0px 0px 5px gray;
                border-radius: 0px 50px;
            }

            input[type="text"],
            input[type="password"] {
                height: 45px;
                border-top: 0;
                border-left: 0;
                border-right: 0;
                border-radius: 0;
                text-align: center;
            }

            button[id="userSigin"] {
                width: 41%;
                margin-left: 9%;
                height: 45px;
                border-top: 0;
                border-left: 0;
                border-right: 0;
                border-radius: 0;
                text-align: center;
                background-color: #0d7ec0;
                color: #fff;
                float:left;
                margin-right: 10px;

            }
            #error_message{
                display:none;
            }
            a#resendOtp{
               
                padding-top: 10px;
                height: 45px;
                border-top: 0;
                border-left: 0;
                border-right: 0;
                border-radius: 0;
                text-align: center;
                background-color: #0d7ec0;
                color: #fff;
                padding-top: 12px;
               
                text-decoration: none;
               

            }
            a#Verify_otp {
                
                padding-top: 10px;
                height: 45px;
                border-top: 0;
                border-left: 0;
                border-right: 0;
                border-radius: 0;
                text-align: center;
                background-color: #0d7ec0;
                color: #fff;
                padding-top: 12px;
               
                text-decoration: none;
            }
            button[id="userSigin"]:hover {
                background-color: #005d86;
                color: #fff;
            }

            h1 {
                color: #0d7ec0;
                font-size: 20px;
            }

            p {
                margin-bottom: 40px;
            }
            .form-group.verify_reset {
              height: 38px;
              margin-left: 40px;
              margin-right: 40px;

              }
              #otpSnd1 {
               width: 48%;
                float: left;
                border-right: 3px solid #fff;
              }
              #otpSnd{
               width: 48%;
                float: left;
                border-left: 10px solid #fff;

              }

            @media all and (max-width: 320px),
            (max-width: 375px),
            (max-width: 384px),
            (max-width: 414px),
            (max-device-width: 450px),
            (max-device-width: 480px),
            (max-device-width: 540px),
            (max-device-width: 590px),
            (max-device-width: 620px),
            (max-device-width: 680px) {
                .form {
                    background: #fff;
                    border: 1px solid #fff;
                    padding: 20px;
                    margin: 0%;
                    box-shadow: 0px 0px 5px gray;
                    border-radius: 0px 50px;
                    margin-top: 30%;
                }
            }
        </style>
    </head>

    <body>
        <?php
        $csrf = array(
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        );
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="form">

                        <form method="post" id="formVerifyData" autocomplete="off" onsubmit="return false;">

                            <input type="hidden" name="otp_flag" value="1" />

                            <p class="text-center">
                                <img class="img-rounded" src="<?= LMS_BRAND_LOGO ?>" alt="brand-logo">
                            </p>
                            <p class="text-center mb-4">
                            </p>
                            <?php if ($this->session->flashdata('msg') != '') { ?>
                                <p style="text-align:center" id="myDiv" class="alert alert-success alert-dismissible">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close" >&times;</a>
                                    <strong><?= $this->session->flashdata('msg'); ?></strong>
                                </p>
                                <?php
                            }
                            if ($this->session->flashdata('err') != '') {
                                ?>
                                <p id="error_message" style="text-align:center" class="alert alert-danger alert-dismissible">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close" >&times;</a>
                                    <strong><?= $this->session->flashdata('err'); ?></strong>
                                </p>
                            <?php } ?>
                             
                            <p id="error_message" style="text-align:center" class="alert alert-danger alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close" >&times;</a>
                                <strong></strong>
                            </p>

                            <div class="form-group">
                                <input type="text" name="otp" class="form-control" placeholder="OTP" title="OTP" minimum="4" maxlength="4" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                            return false;" required autocomplete="off">
                            </div>


                            <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />

                            <input type="hidden" name="user_id" value="<?= $user_id; ?>" />

                            <!-- </div> -->

                        </form>
                        <div class="form-group verify_reset">
                            <div id="otpsnd1">
                                <a class="form-control" id="verify_otp" href="#" title="Verify OTP">Verify OTP</a>
                            </div>
                            <div id="otpsnd">
                                <a href="<?= base_url("otpResend") ?>" class="form-control" id="resendOtp" title="Resend OTP">Resend OTP</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>

<script type="text/javascript">
  var count = 0;
    $("#verify_otp").on("click", function () {
      
        var FormData = $("#formVerifyData").serialize();
        $.ajax({
            url: '<?= base_url("otpLogin") ?>',
            type: 'POST',
            data: FormData,
            dataType: "json",
            beforeSend: function () {
                $("#verify_otp").html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Verifying...').prop('disabled', true);
            },
            success: function (response) {
                   
                console.log(response);                   
                if (response['status'] == 1) {
                    window.location.href = "<?= base_url("dashboard") ?>";
                    
                } else if (response['status'] == 0) {
                   count += 1;
                    if(count< 4) {       
                       $("#error_message").show();
                       $("#error_message").text(response['message']+ ":" + (4-count));
                         $("#error_message").fadeOut(3000);
                       $("#myDiv").hide();
                     }else {
                        alert("Your account has been locked. Forget your password, then try again.")
                        window.location.href = "<?= base_url("logout") ?>";
                     }
                }
               
            },
            complete: function () {
                $("#verify_otp").html("Verify").prop('disabled', false);
            }
        });
    });
  
  
</script>