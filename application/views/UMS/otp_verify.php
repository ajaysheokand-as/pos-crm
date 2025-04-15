<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FORGOT PASSWORD</title>
        <link rel="icon" type="image/*" href="<?= base_url('public'); ?>/images/18-BK_kixu8fav.png">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/bootstrap.min.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/bootstrap.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/font-awesome.min.css">
        <link rel="stylesheet preload" href="<?= base_url('public/front'); ?>/css/style.css">
        <script src="<?= base_url('public/front'); ?>/js/jquery.3.5.1.min.js"></script>
        <style>
            body {
                /*background-image: url('<?= base_url('public/front'); ?>/../images/login_background_img.jpg');*/
                /*background-position: center center;*/
                /*background-repeat: no-repeat;*/
                /*background-attachment: fixed;*/
                /*background-size: cover;*/
                background: #8180e0;
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
                padding: 6px 12px;
                color: #555555;
                background-color: #ffffff;
                background-image: none;
                border: none;
                border-bottom: 1px solid #adadad;
                text-align: center;
            }
            
            .btn_container {
                width: 100%;
                display: flex;
                justify-content: center;
            }

            button[id="userSigin"] {
                padding: 4px 10px !important;
                background: #8180e0 !important;
                color: #fff !important;
                max-width: 200px;
                margin-top: 10px;
                border-radius: 4px !important;
                text-align: center !important;
                font-size: 16px !important;
                border: 1px solid #8180e0 !important;
            }

            button[id="userSigin"]:hover {
                background-color: transparent;
                color: #fff;
                border: 1px solid #8180e0;
            }

            h1 {
                color: #0d7ec0;
                font-size: 20px;
            }

            p {
                margin-bottom: 40px;
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
            .img-rounded {
                border-radius: 6px;
                width: 200px;
            }
            .close {
                position: absolute !important;
                right: 10px !important;
                top: 50% !important;
                transform: translateY(-50%);
            }
            
            .custom_center {
                display: flex;
                align-item: center;
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

                        <form method="post" action="<?= base_url('verifyotp'); ?>" id="formData" autocomplete="off">
                            <p class="text-center">
                                <img class="img-rounded" src="<?= LMS_COMPANY_LOGO ?>" alt="brand-logo">
                            </p>
                            <p class="text-center mb-4">
                                <!--<div class="titleSignin text-center"></div>-->
                            </p>
                            <?php if ($this->session->flashdata('msg') != '') { ?>
                                <p class="alert alert-success alert-dismissible custom_center">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <strong><?= $this->session->flashdata('msg'); ?></strong>
                                </p>
                                <?php
                            }
                            if ($this->session->flashdata('err') != '') {
                                ?>
                                <p class="alert alert-danger alert-dismissible">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    <strong>'<?= $this->session->flashdata('err'); ?></strong>
                                </p>
                            <?php } ?>
                            <!-- <div class="form-group"> -->
                                <!-- <p class="text-center">
                                    <span style="color : red; font-size : 20px;">*</span>Your Email
                                </p> -->
                            <div class="form-group">
                                <input type="text" name="otp" class="form-control" placeholder="OTP" title="OTP" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="New Password" title="New Password" required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" title="Confirm Password" required autocomplete="off">
                            </div>

                            <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                            <input type="hidden" name="user_id" value="<?= $user_id; ?>" />
                            <!-- </div> -->

                            <div class="form-group btn_container">
                                <button type="text" class="form-control" id="userSigin" title="Verify Email">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>
