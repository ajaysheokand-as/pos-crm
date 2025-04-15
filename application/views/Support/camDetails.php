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

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchCamId'); ?>" method="POST" enctype="multipart/form-data">
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
                                                    <h4>Update CAM Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($leadInfo['lead_id']) && $leadInfo['lead_id']!=''){echo $leadInfo['lead_id'];}?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit1_date_id">Salary Date 1<span class="span" style="color:red;">*</span></label>      
                                                                    <input type="text" required class="form-control salaryDate1" readonly name="salary_credit1_date" id="salary_credit1_date_id" value="<?php if(isset($leadInfo['salary_credit1_date']) && $leadInfo['salary_credit1_date']!=''){echo $leadInfo['salary_credit1_date'];}else{echo $_POST['salary_credit1_date'];} ?>">
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit1_amount_id">Salary Amount 1 (Rs.)<span class="span" style="color:red;">*</span></label>                                                          
                                                                    <input type="text" class="form-control" required name="salary_credit1_amount" id="salary_credit1_amount_id" value="<?php if(isset($leadInfo['salary_credit1_amount']) && $leadInfo['salary_credit1_amount']!=''){echo $leadInfo['salary_credit1_amount'];}else{echo $_POST['salary_credit1_amount'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div> 
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit2_date_id">Salary Date 2</label>      
                                                                    <input type="text" class="form-control salaryDate2" readonly name="salary_credit2_date" id="salary_credit2_date_id" value="<?php if(isset($leadInfo['salary_credit2_date']) && $leadInfo['salary_credit2_date']!=''){echo $leadInfo['salary_credit2_date'];}else{echo $_POST['salary_credit2_date'];} ?>">
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit2_amount_id">Salary Amount 2 (Rs.)</label>                                   
                                                                    <input type="text" class="form-control" name="salary_credit2_amount" id="salary_credit2_amount_id" value="<?php if(isset($leadInfo['salary_credit2_amount']) && $leadInfo['salary_credit2_amount']!=''){echo $leadInfo['salary_credit2_amount'];}else{echo $_POST['salary_credit2_amount'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit3_date_id">Salary Date 3</label>      
                                                                    <input type="text" class="form-control salaryDate3" readonly name="salary_credit3_date" id="salary_credit3_date_id" value="<?php if(isset($leadInfo['salary_credit3_date']) && $leadInfo['salary_credit3_date']!=''){echo $leadInfo['salary_credit3_date'];}else{echo $_POST['salary_credit3_date'];} ?>">
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="salary_credit3_amount_id">Salary Amount 3 (Rs.)</label>                                  
                                                                    <input type="text" class="form-control" name="salary_credit3_amount" id="salary_credit3_amount_id" value="<?php if(isset($leadInfo['salary_credit3_amount']) && $leadInfo['salary_credit3_amount']!=''){echo $leadInfo['salary_credit3_amount'];}else{echo $_POST['salary_credit3_amount'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6">
                                                                    <label for="next_pay_date_id">Next Pay Date<span class="span" style="color:red;">*</span></label>      
                                                                    <input type="text" class="form-control nextSalaryDate" required readonly name="next_pay_date" id="next_pay_date_id" value="<?php if(isset($leadInfo['next_pay_date']) && $leadInfo['next_pay_date']!=''){echo $leadInfo['next_pay_date'];}else{echo $_POST['next_pay_date'];} ?>">
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="median_salary_id">Avg. Salary (Rs.)<span class="span" style="color:red;">*</span></label>       
                                                                    <input type="text" class="form-control" required readonly name="median_salary" id="median_salary_id" value="<?php if(isset($leadInfo['median_salary']) && $leadInfo['median_salary']!=''){echo $leadInfo['median_salary'];}else{echo $_POST['median_salary'];} ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div>
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6"> 
                                                                    <label for="cam_remark">CAM Remark </label>                                       
                                                                    <textarea class="form-control" name="remark" id="cam_remark_id" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead cam remark."><?php if(isset($leadInfo['remark']) && $leadInfo['remark']!=''){echo $leadInfo['remark'];}else{echo $_POST['remark'];} ?></textarea>
                                                                </div> 
                                                                <div class="col-md-6"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark </label>                                       
                                                                    <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>                                                                
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="button" class="button-add btn update_cam_details">Update CAM Details</button>
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
