<section class="parent_wrapper">
<?php
$this->load->view('Layouts/header');
include('inner_layout.php');
?>
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <?php if (agent == 'CA') { ?>
                                        <div class="col-md-3 drop-me">
                                            <?php $this->load->view('Layouts/leftsidebar') ?>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-12">
                                        <div class="login-formmea" style="margin-bottom: 10px;">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Search lead id? </h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        
                                                        <?php
                                                        if ($this->session->flashdata('message') != '') {
                                                            echo '<div class="alert alert-success alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('message') . '</strong> 
                		                            </div>';
                                                        }
                                                        else if($this->session->flashdata('error') != '') {
                                                            echo '<div class="alert alert-danger alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('error') . '</strong> 
                		                            </div>';
                                                        }
                                                        ?>

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchLeadId'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required="" value="<?php if(isset($_POST['lead_id']) && $_POST['lead_id']!=''){echo $_POST['lead_id'];} ?>" placeholder="Please enter lead id*" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <button type="submit" id="search_lead_id" class="button btn">Search LEAD ID</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        //echo '<pre>';print_r($leadInfo);die;
                                        if(!empty($status)){
                                        ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Personal Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($leadInfo['lead_id']) && $leadInfo['lead_id']!=''){echo $leadInfo['lead_id'];}?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="check_email_id"><span class="span" style="color:red;">*</span>Personal Email </label>
                                                                    <input type="checkbox" name="check_email" id="check_email_id" onclick="emailEabledAndDisabled();">
                                                                    <input type="email" class="form-control" name="email" id="email_id" maxlength="50" value="<?php if(isset($leadInfo['email']) && $leadInfo['email']!=''){echo $leadInfo['email'];}else{echo $_POST['email'];} ?>" disabled required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="check_alternate_email_id">Alternate Email </label>
                                                                    <input type="checkbox" name="check_alternate_email" id="check_alternate_email_id" maxlength="50" onclick="alternateEmailEabledAndDisabled();">
                                                                    <input type="email" class="form-control" name="alternate_email" id="alternate_email_id" value="<?php if(isset($leadInfo['alternate_email']) && $leadInfo['alternate_email']!=''){echo $leadInfo['alternate_email'];}else{echo $_POST['alternate_email'];} ?>" disabled>
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="check_mobile_id"><span class="span" style="color:red;">*</span>Personal Mobile </label>
                                                                    <input type="checkbox" name="check_mobile" id="check_mobile_id" onclick="mobileEabledAndDisabled();">
                                                                    <input type="text" class="form-control" name="mobile" id="mobile_id" maxlength="10" value="<?php if(isset($leadInfo['mobile']) && $leadInfo['mobile']!=''){echo $leadInfo['mobile'];}else{echo $_POST['mobile'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" required disabled>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="check_alternate_mobile_id">Alternate Mobile </label>
                                                                    <input type="checkbox" name="check_alternate_mobile" id="check_alternate_mobile_id" onclick="alternateMobileEabledAndDisabled();">
                                                                    <input type="text" class="form-control" name="alternate_mobile" id="alternate_mobile_id" maxlength="10" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" value="<?php if(isset($leadInfo['alternate_mobile']) && $leadInfo['alternate_mobile']!=''){echo $leadInfo['alternate_mobile'];}else{echo $_POST['alternate_mobile'];} ?>" disabled>
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="loan_amount_id">Loan Amount <span class="span" style="color:red;">*</span></label>                                                             
                                                                    <input type="text" class="form-control" name="loan_amount" id="loan_amount_id" maxlength="15" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" value="<?php if(isset($leadInfo['loan_amount']) && $leadInfo['loan_amount']!=''){echo $leadInfo['loan_amount'];}else{echo $_POST['loan_amount'];} ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="pancard_id">Pancard Number <span class="span" style="color:red;">*</span></label>                
                                                                    <input type="text" class="form-control" required name="pancard" id="pancard_id" maxlength="12" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" value="<?php if(isset($leadInfo['pancard']) && $leadInfo['pancard']!=''){echo $leadInfo['pancard'];}else{echo $_POST['pancard'];} ?>">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="gender_id">Gender <span class="span" style="color:red;">*</span></label>                         
                                                                    <select class="form-control" name="gender" id="gender_id">
                                                                        <option value="">Select gender</option>
                                                                        <option value="MALE"<?php if(isset($leadInfo['gender']) && $leadInfo['gender']=='MALE'){echo 'Selected="Selected"';}?>>MALE</option>
                                                                        <option value="FEMALE"<?php if(isset($leadInfo['gender']) && $leadInfo['gender']=='FEMALE'){echo 'Selected="Selected"';}?>>FEMALE</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="dob_id">DOB <span class="span" style="color:red;">*</span></label>                         
                                                                    <input type="text" class="form-control dob_class" required name="dob" id="dob_id" readonly value="<?php if(isset($leadInfo['dob']) && $leadInfo['dob']!=''){echo date('d-m-Y',strtotime($leadInfo['dob']));}else{date('DD-MM-YYYY',strtotime($_POST['dob']));} ?>">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="customer_religion_id">Religion <span class="span" style="color:red;">*</span></label>                         
                                                                    <select class="form-control" name="customer_religion_id" id="customer_religion_id">
                                                                        <option value="">Select Religion</option>
                                                                        <?php if(count($religion_list)>0){foreach($religion_list as $key => $religionVal) {?>
                                                                         <option value="<?=$religionVal['religion_id']?>"<?php if(isset($leadInfo['customer_religion_id']) && $leadInfo['customer_religion_id']==$religionVal['religion_id']){echo 'Selected="Selected"';} ?>><?=$religionVal['religion_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="customer_marital_status_id">Marital Status <span class="span" style="color:red;">*</span></label>                         
                                                                    <select class="form-control" name="customer_marital_status_id" id="customer_marital_status_id">
                                                                        <option value="">Select Marital Status</option>
                                                                        <?php if(count($marital_status_list)>0){foreach($marital_status_list as $key => $maritalVal) {?>
                                                                         <option value="<?=$maritalVal['m_marital_status_id']?>"<?php if(isset($leadInfo['customer_marital_status_id']) && $leadInfo['customer_marital_status_id']==$maritalVal['m_marital_status_id']){echo 'Selected="Selected"';} ?>><?=$maritalVal['m_marital_status_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-4">
                                                                    <label for="customer_spouse_name_id">Spouse Name </label> 
                                                                    <input type="text" class="form-control" name="customer_spouse_name" maxlength="40" id="customer_spouse_name_id" value="<?php if(isset($leadInfo['customer_spouse_name']) && $leadInfo['customer_spouse_name']!=''){echo $leadInfo['customer_spouse_name'];}else{echo $_POST['customer_spouse_name'];} ?>">                                                                    
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="customer_spouse_occupation_id">Spouse Occupation </label>                         
                                                                    <select class="form-control" name="customer_spouse_occupation_id" id="customer_spouse_occupation_id">
                                                                        <option value="">Select Occupation</option>
                                                                        <?php if(count($occupation_list)>0){foreach($occupation_list as $key => $occupationVal) {?>
                                                                         <option value="<?=$occupationVal['m_occupation_id']?>"<?php if(isset($leadInfo['customer_spouse_occupation_id']) && $leadInfo['customer_spouse_occupation_id']==$occupationVal['m_occupation_id']){echo 'Selected="Selected"';} ?>><?=$occupationVal['m_occupation_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="qualification_id">Qualification </label> 
                                                                    <select class="form-control" name="qualification" id="qualification_id">
                                                                        <option value="">Select Qualification</option>
                                                                        <?php if(count($qualification_list)>0){foreach($qualification_list as $key => $qualificationVal) {?>
                                                                         <option value="<?=$qualificationVal['m_qualification_id']?>"<?php if(isset($leadInfo['customer_qualification_id']) && $leadInfo['customer_qualification_id']==$qualificationVal['m_qualification_id']){echo 'Selected="Selected"';} ?>><?=$qualificationVal['m_qualification_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                                                    
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="current_house_id">Current House <span class="span" style="color:red;">*</span></label> 
                                                                    <input type="text" class="form-control" name="current_house" id="current_house_id" value="<?php if(isset($leadInfo['current_house']) && $leadInfo['current_house']!=''){echo $leadInfo['current_house'];}else{echo $_POST['current_house'];} ?>">                                                                    
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="current_locality_id">Current Locality <span class="span" style="color:red;">*</span></label> 
                                                                    <input type="text" class="form-control" name="current_locality" id="current_locality_id" value="<?php if(isset($leadInfo['current_locality']) && $leadInfo['current_locality']!=''){echo $leadInfo['current_locality'];}else{echo $_POST['current_locality'];} ?>">                                                                    
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="current_landmark_id">Current Landmark </label> 
                                                                    <input type="text" class="form-control" name="current_landmark" id="current_landmark_id" value="<?php if(isset($leadInfo['current_landmark']) && $leadInfo['current_landmark']!=''){echo $leadInfo['current_landmark'];}else{echo $_POST['current_landmark'];} ?>">                                                                    
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="current_state_id">Current State <span class="span" style="color:red;">*</span></label> 
                                                                    <select class="form-control" name="current_state" id="current_state_id" onchange="return showCity(this.value);">
                                                                        <option value="">Select State</option>
                                                                        <?php if(count($state_list)>0){foreach($state_list as $key => $satetVal) {?>
                                                                         <option value="<?=$satetVal['m_state_id']?>"<?php if(isset($leadInfo['current_state']) && $leadInfo['current_state']==$satetVal['m_state_id']){echo 'Selected="Selected"';} ?>><?=$satetVal['m_state_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                                                    
                                                                </div>
                                                                <p>&nbsp</p>                                                                
                                                                <div class="col-md-6">
                                                                    <label for="current_city_id">Current City <span class="span" style="color:red;">*</span></label> 
                                                                    <select class="form-control" name="current_city" id="city_id" onchange="return showPincode(this.value);">
                                                                        <option value="">Select City</option>
                                                                        <?php if(count($city_list)>0){foreach($city_list as $key => $citytVal) {?>
                                                                         <option value="<?=$citytVal['m_city_id']?>"<?php if(isset($leadInfo['current_city']) && $leadInfo['current_city']==$citytVal['m_city_id']){echo 'Selected="Selected"';} ?>><?=$citytVal['m_city_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                            
                                                                </div>
                                                                
                                                                <div class="col-md-6">
                                                                    <label for="residence_pincode_id">Current Pincode <span class="span" style="color:red;">*</span></label> 
                                                                    <select class="form-control residence_pincode_cls" name="residence_pincode" id="residence_pincode_id">
                                                                        <option value="">Select Pincode</option>
                                                                        <?php if(count($pincode_list)>0){foreach($pincode_list as $key => $pincodeVal) {?>
                                                                         <option value="<?=$pincodeVal['m_pincode_value']?>"<?php if(isset($leadInfo['cr_residence_pincode']) && $leadInfo['cr_residence_pincode']==$pincodeVal['m_pincode_value']){echo 'Selected="Selected"';} ?>><?=$pincodeVal['m_pincode_value']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                                                                                     
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <p><b>Lead Source Change</b></p> 
                                                                
                                                                <!--div class="col-md-2">
                                                                    <label for="source_id">Source <span class="span" style="color:red;">*</span>(e.g.-Website SOT)</label--> 
                                                                    <input type="hidden" class="form-control" maxlength="20" name="source" id="source_id" value="<?php if(isset($leadInfo['source']) && $leadInfo['source']!=''){echo $leadInfo['source'];}else{echo $_POST['source'];} ?>">                                                                    
                                                                <!--/div>
                                                                <div class="col-md-2">
                                                                    <label for="utm_source_id">UTM Source<span class="span" style="color:red;">*</span>(e.g.-Website SOT)</label--> 
                                                                    <input type="hidden" class="form-control" maxlength="20" name="utm_source" id="utm_source_id" value="<?php if(isset($leadInfo['utm_source']) && $leadInfo['utm_source']!=''){echo $leadInfo['utm_source'];}else{echo $_POST['utm_source'];} ?>">                                                                    
                                                                <!--/div>
                                                                <div class="col-md-3">
                                                                    <label for="utm_campaign_id">UTM Campaign <span class="span" style="color:red;">*</span>(e.g.-SOT)</label--> 
                                                                    <input type="hidden" class="form-control" maxlength="20" name="utm_campaign" id="utm_campaign_id" value="<?php if(isset($leadInfo['utm_campaign']) && $leadInfo['utm_campaign']!=''){echo $leadInfo['utm_campaign'];}else{echo $_POST['utm_campaign'];} ?>">                                                                    
                                                                <!--/div>
                                                                <div class="col-md-2">
                                                                    <label for="lead_stp_flag_id">Lead Flag<span class="span" style="color:red;">*</span>(e.g.-0/1)</label--> 
                                                                    <input type="hidden" class="form-control" maxlength="1" name="lead_stp_flag" id="lead_stp_flag_id" value="<?php if(isset($leadInfo['lead_stp_flag']) && $leadInfo['lead_stp_flag']!=''){echo $leadInfo['lead_stp_flag'];}else{echo $_POST['lead_stp_flag'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">                                                                    
                                                                <!--/div-->
                                                                 
                                                                <div class="col-md-3">
                                                                    <label for="lead_data_source_id">Lead Source<span class="span" style="color:red;">*</span>(e.g.-Website SL)</label> 
                                                                    <select class="form-control" name="lead_data_source_id" id="lead_data_source_id">
                                                                        <option value="">Select Source</option>
                                                                        <?php if(count($lead_source)>0){foreach($lead_source as $key => $sourceVal) {?>
                                                                         <option value="<?=$sourceVal['data_source_id']?>"<?php if(isset($leadInfo['lead_data_source_id']) && $leadInfo['lead_data_source_id']==$sourceVal['data_source_id']){echo 'Selected="Selected"';} ?>><?=$sourceVal['data_source_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                   
                                                                </div> 
                                                                <br><br>
                                                                <p>&nbsp</p>
                                                                <p><b>Lead has been allocated manually</b></p>
                                                                <div class="col-md-4">
                                                                    <label for="lead_screener_assign_user_id">Users <span class="span" style="color:red;">*</span></label> 
                                                                    <select class="form-control" name="lead_screener_assign_user_id" required="" id="lead_screener_assign_user_id">
                                                                        <option value="">Select User</option>
                                                                        <?php if(count($user_list)>0){foreach($user_list as $key => $userVal) {?>
                                                                         <option value="<?=$userVal['user_id']?>"<?php if(isset($leadInfo['lead_screener_assign_user_id']) && $leadInfo['lead_screener_assign_user_id']==$userVal['user_id']){echo 'Selected="Selected"';} ?>><?=$userVal['name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                           
                                                                </div>                                                                
                                                                <div class="col-md-4">
                                                                    <label for="stage_id">Status <span class="span" style="color:red;">*</span></label> 
                                                                    <select class="form-control" name="stage" id="stage_id">
                                                                        <option value="">Select Stage</option>
                                                                        <?php if(count($lead_status)>0){foreach($lead_status as $key => $statusVal) {?>
                                                                         <option value="<?=$statusVal['status_stage']?>"<?php if(isset($leadInfo['stage']) && $leadInfo['stage']==$statusVal['status_stage']){echo 'Selected="Selected"';} ?>><?=$statusVal['status_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>                                           
                                                                </div> 
                                                                <p>&nbsp</p>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-12"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark </label>                                       
                                                                    <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>
                                                                
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="button" class="button-add btn update_personal_details">Update Personal Details</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                            <!--Footer Start Here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</section>
<?php $this->load->view('Layouts/footer'); ?>
<?php $this->load->view('Support/support_js'); ?>

</section>