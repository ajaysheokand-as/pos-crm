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
                                    <div class="col-sm-12">
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

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchEmploymentId'); ?>" method="POST" enctype="multipart/form-data">
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
                                        <?php if(!empty($status)){ ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Employment Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($leadInfo['lead_id']) && $leadInfo['lead_id']!=''){echo $leadInfo['lead_id'];}?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="employer_name_id">Name <span class="span" style="color:red;">*</span></label>                  
                                                                    <input type="text" required class="form-control" name="employer_name" id="employer_name_id" value="<?php if(isset($leadInfo['employer_name']) && $leadInfo['employer_name']!=''){echo $leadInfo['employer_name'];}else{echo $_POST['employer_name'];} ?>">
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="emp_email_id">Emp Email </label>                                                          
                                                                    <input type="email" class="form-control" name="emp_email" id="emp_email_id" value="<?php if(isset($leadInfo['emp_email']) && $leadInfo['emp_email']!=''){echo $leadInfo['emp_email'];}else{echo $_POST['emp_email'];} ?>">
                                                                </div> 
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="emp_house_id">Emp House </label>                                                          
                                                                    <input type="text" class="form-control" name="emp_house" id="emp_house_id" value="<?php if(isset($leadInfo['emp_house']) && $leadInfo['emp_house']!=''){echo $leadInfo['emp_house'];}else{echo $_POST['emp_house'];} ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="emp_street_id">Emp Street </label>                                                          
                                                                    <input type="text" class="form-control" name="emp_street" id="emp_street_id" value="<?php if(isset($leadInfo['emp_street']) && $leadInfo['emp_street']!=''){echo $leadInfo['emp_street'];}else{echo $_POST['emp_street'];} ?>">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="emp_landmark_id">Emp Landmark</label>                                                          
                                                                    <input type="text" class="form-control" name="emp_landmark" id="emp_landmark_id" value="<?php if(isset($leadInfo['emp_landmark']) && $leadInfo['emp_landmark']!=''){echo $leadInfo['emp_landmark'];}else{echo $_POST['emp_landmark'];} ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="state_id">State </label>                                                          
                                                                    <select class="form-control" name="state" id="state_id" onchange="return showCity(this.value);">
                                                                        <option value="">Select State</option>
                                                                        <?php if(count($state_list)>0){foreach($state_list as $key => $satetVal) {?>
                                                                         <option value="<?=$satetVal['m_state_id']?>"<?php if(isset($leadInfo['state_id']) && $leadInfo['state_id']==$satetVal['m_state_id']){echo 'Selected="Selected"';} ?>><?=$satetVal['m_state_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="city_id">City </label>                                                          
                                                                    <select class="form-control" name="city" id="city_id">
                                                                        <option value="">Select City</option>
                                                                        <?php if(count($city_list)>0){foreach($city_list as $key => $citytVal) {?>
                                                                         <option value="<?=$citytVal['m_city_id']?>"<?php if(isset($leadInfo['city_id']) && $leadInfo['city_id']==$citytVal['m_city_id']){echo 'Selected="Selected"';} ?>><?=$citytVal['m_city_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="emp_pincode_id">Emp Pincode </label>                                                          
                                                                    <input type="text" class="form-control" name="emp_pincode" id="emp_pincode_id" value="<?php if(isset($leadInfo['emp_pincode']) && $leadInfo['emp_pincode']!=''){echo $leadInfo['emp_pincode'];}else{echo $_POST['emp_pincode'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" maxlength="6">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="emp_residence_since_id">Residence Since </label>                                                          
                                                                    <input type="text" class="form-control employed_since_current_date" readonly name="emp_residence_since" id="emp_residence_since_id" value="<?php if(isset($leadInfo['emp_residence_since']) && $leadInfo['emp_residence_since']!=''){echo $leadInfo['emp_residence_since'];}else{echo $_POST['emp_residence_since'];} ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="emp_designation_id">Emp Designation </label> 
                                                                    <select class="form-control" name="emp_designation" id="emp_designation_id">
                                                                        <option value="">Select Designation</option>
                                                                        <?php if(count($designation_list)>0){foreach($designation_list as $key => $designationVal) {?>
                                                                         <option value="<?=$designationVal['m_designation_name']?>"<?php if(isset($leadInfo['emp_designation']) && $leadInfo['emp_designation']==$designationVal['m_designation_name']){echo 'Selected="Selected"';} ?>><?=$designationVal['m_designation_name']?></option>   
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="emp_department_id">Emp Department </label> 
                                                                    <select class="form-control" name="emp_department" id="emp_department_id">
                                                                        <option value="">Select Department</option>
                                                                        <?php if(count($department_list)>0){foreach($department_list as $key => $departmentVal) {?>
                                                                         <option value="<?=$departmentVal['department_name']?>"<?php if(isset($leadInfo['emp_department']) && $leadInfo['emp_department']==$departmentVal['department_name']){echo 'Selected="Selected"';} ?>><?=$departmentVal['department_name']?></option>   
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>                                                                
                                                                <div class="col-md-6">
                                                                    <label for="emp_employer_type_id">Emp Employer Type </label>                                                 
                                                                    <select class="form-control" name="emp_employer_type" id="emp_employer_type_id">
                                                                        <option value="">Select Employer Type</option>
                                                                        <?php if(count($company_type_list)>0){foreach($company_type_list as $key => $companyTypeVal) {?>
                                                                         <option value="<?=$companyTypeVal['m_company_type_name']?>"<?php if(isset($leadInfo['emp_employer_type']) && $leadInfo['emp_employer_type']==$companyTypeVal['m_company_type_name']){echo 'Selected="Selected"';} ?>><?=$companyTypeVal['m_company_type_name']?></option>   
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div> 
                                                                <p>&nbsp</p>
                                                                <div class="col-md-4">
                                                                    <label for="emp_occupation_id">Occupation </label>                         
                                                                    <select class="form-control" name="emp_occupation_id" id="emp_occupation_id">
                                                                        <option value="">Select Occupation</option>
                                                                        <?php if(count($occupation_list)>0){foreach($occupation_list as $key => $occupationVal) {?>
                                                                         <option value="<?=$occupationVal['m_occupation_id']?>"<?php if(isset($leadInfo['emp_occupation_id']) && $leadInfo['emp_occupation_id']==$occupationVal['m_occupation_id']){echo 'Selected="Selected"';} ?>><?=$occupationVal['m_occupation_name']?></option>  
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-4">
                                                                <label for="income_type">Income Type</label>
                                                                    <select class="form-control" name="income_type" id="income_type">
                                                                        <option value="">Select Income Type</option>
                                                                        <?php if(count($leadInfo) > 0) { 
                                                                            $incomeType = $leadInfo['income_type'];
                                                                            ?>
                                                                            <option value="1" <?php if($incomeType == '1') echo 'selected="selected"'; ?>>Salary</option>
                                                                            <option value="2" <?php if($incomeType == '2') echo 'selected="selected"'; ?>>Self Employed</option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="salary_mode_id">Emp Salary Mode </label>                                                 
                                                                    <select class="form-control" name="salary_mode" id="salary_mode_id">
                                                                        <option value="">Select Salary Mode</option>
                                                                        <?php if(count($salary_mode_list)>0){foreach($salary_mode_list as $key => $salaryModeVal) {?>
                                                                         <option value="<?=$salaryModeVal['m_salary_mode_name']?>"<?php if(isset($leadInfo['salary_mode']) && $leadInfo['salary_mode']==$salaryModeVal['m_salary_mode_name']){echo 'Selected="Selected"';} ?>><?=$salaryModeVal['m_salary_mode_name']?></option>   
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div> 
                                                                <p> &nbsp; </p>
                                                                <div class="col-md-12"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark </label>                                       
                                                                    <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>
                                                                                                                                
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="button" class="button-add btn update_employment_details">Update Employment Details</button>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->load->view('Layouts/footer'); ?>
<?php $this->load->view('Support/support_js'); ?>
</section>
</section>

