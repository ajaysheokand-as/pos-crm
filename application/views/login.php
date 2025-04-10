<?php

    if(!empty($_SESSION['isUserSession']['user_id'])){ 
        $this->session->set_flashdata('err', "Session Expired, Try once more.");
        return redirect(base_url('dashboard'));
    } else { 


?>


<?php $logo = $this->db->where('company_id', 1)->get('logo')->row(); ?>

<!DOCTYPE html>


<html>

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Log In</title>
        <link rel="icon" type="image/*" href="<?= base_url('public'); ?>/images/18-BK_kixu8fav.png">
       
		<link rel="stylesheet preload" href="<?= base_url('public'); ?>/css/style.css">
		<script src="<?= base_url('public/front'); ?>/js/jquery.3.5.1.min.js"></script>
        <style>
        .container form .row {
           width: 100%;
           position: relative;
        }   
         .container form .row img {
            width: 15px;
            position: absolute;
            right: 5%;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .container a {
    color: #333;
    font-size: 13px;
    text-decoration: none;
    margin: 15px 0 10px;
}
        </style>

		

	</head>


	<body>



     
<div class="container" id="container">
        <div class="form-container sign-in">
            <!-- <div class="logo">
                <img src="final_logo.png">
            </div> -->
            <form method="post" action="<?= base_url($url);?>" id="formData" autocomplete="off">
			<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                 
							<?php  if($this->session->flashdata('msg')!=''){ ?>
				                <p class="alert alert-success alert-dismissible">
				                	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				                    <strong><?= $this->session->flashdata('msg'); ?></strong> 
				                </p>
				            <?php } if($this->session->flashdata('err')!=''){ ?>
				                <div class="alert alert-danger alert-dismissible">
				                	<a href="#" role='button' class="close close-btn" data-dismiss="alert" aria-label="close">&times;</a>
				                    <strong class='error-msg'><?= $this->session->flashdata('err'); ?></strong> 
				                </div>
				            <?php } ?> 
				            <h1>Sign In</h1>
                            <span>or use your Email and password</span>
                           <div class="row">
                              <input type="text" name="email" placeholder="Username" title="Username" required>
                           </div>
                          <div class="row">
                              <input type="password" id="password" name='password' title='Password' required placeholder="Password">
                              <img src="<?= base_url('public'); ?>/images/eye-slash.svg" alt="" id="eye">
                          </div>
				            
                
                
				<a href="<?= base_url('forgetPassword') ?>">→ Forget your password? ←</a>
            
                <button id="userSigin" title="User Sign in">SIGN IN</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-right">
                    <div class="logo">
                        <!-- <img src="<?= base_url('public'); ?>/images/final_logo.png"> -->
                        <img src="<?= base_url('public'); ?>/images/login-logo.png">
                    </div>
                    <h1>Welcome Back !</h1>
                    <p>Please log in using your credentials
                         to avoid the consequences of an account block.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        let eyeicon = document.getElementById('eye');
        let password = document.getElementById('password');

        eyeicon.onclick = function () {
            if(password.type == "password") {
                password.type = "text"
                eyeicon.src="<?= base_url('public'); ?>/images/eye-solid.svg"
            }else {
                password.type = "password";
                eyeicon.src="<?= base_url('public'); ?>/images/eye-slash.svg";
            }
        }
    </script>
	</body>
	<script src="<?= base_url('public/front'); ?>/js/jquery.3.5.1.min.js"></script>
		<script>
					$('.close-btn').click(function(){
					$(this).parent().fadeOut(); 
					});
					$('#show').click(function(){
                    if($("input#password").attr('type')=='password')
                    {
                        $("input#password").attr('type','text');
                        $("#show img").attr('src','<?= base_url("public/images/show_pass.png") ?>');
                    }
                    else
                    {
                        $("input#password").attr('type','password');
                        $("#show img").attr('src','<?= base_url("public/images/hide_pass.png") ?>');
                    }
                });
</script>

</html>


<?php } ?>