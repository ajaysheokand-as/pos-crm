<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Forgot Password</title>
        <link rel="icon" type="image/*" href="<?= base_url('public'); ?>/images/18-BK_kixu8fav.png">
        <link rel="stylesheet preload" href="<?= base_url('public'); ?>/css/style.css">
     
    </head>

    <body>
        <?php
            $csrf = array(
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            );
        ?>

<div class="container" id="container">
				<div class="form-container sign-in">
					<!-- <div class="logo">
						<img src="final_logo.png">
					</div> -->
                    <form method="post" action="<?= base_url('verifyUser'); ?>" id="formData" autocomplete="off">
					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
						
                            <?php if ($this->session->flashdata('msg') != '') { ?>
                                    <p class="alert alert-success alert-dismissible">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                        <strong><?= $this->session->flashdata('msg'); ?></strong> 
                                    </p>
                                <?php } if ($this->session->flashdata('err') != '') { ?>
                                    <p class="alert alert-danger alert-dismissible">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                        <strong><?= $this->session->flashdata('err'); ?></strong> 
                                    </p>
                                <?php } ?>                
						<h1>FORGOT PASSWORD?</h1>
						<span>Please enter your email</span>
                                <!--</p>--> 
                                <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                                <input type="email" name="email"  placeholder="type here" title="Email" required>
						
                        <p><a href="<?= base_url() ?>">→ Back to login ←</a></p>
					
						<button type="submit"  id="userSigin" title="Get OTP">Get OTP</button>
					</form>
				</div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-right">
                    <div class="logo">
                        <!-- <img src="<?= base_url('public'); ?>/images/final_logo.png"> -->
                        <img src="<?= base_url('public'); ?>/images/18-BK_kixu8.png">
                    </div>
                    <h1>Welcome Back !</h1>
                    <p>Please logging through your credentials
                         to avoid the consequences of account block.</p>
                </div>
            </div>
        </div>
    </div>

    </body>

</html>
