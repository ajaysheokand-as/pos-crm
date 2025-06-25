<section class="parent_wrapper">
<?php $this->load->view('Layouts/header') ?>
<section class="right-side">
    <style>

    .parent_wrapper {
        width: 100%;
        height: 100vh;
        display: flex;
    }
    
    .parent_wrapper .right-side {
        width: calc(100% - 234px);
        position: absolute;
        left: 234px;
        top: 0;
        min-height: 100vh;
    }
    
    .parent_wrapper .right-side .logo_container {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        max-height: 90px;
        padding: 30px 20px;
    }
    
      .parent_wrapper .right-side .logo_container a img {
          margin-right: 20px;
          width: 100px;
		  background: #000;
		  border-radius: 10px;
      }

</style>
<?php
// echo "<pre>"; print_r($leadDetails); 
?>
<span id="response" style="width: 100%;float: left;text-align: center;padding-top:-20%;"></span>
<section>
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
    <div class="width-my">
        <div class="container-fluid">
            <div class="taskPageSize taskPageSizeDashboard">
                <div class="alertMessage">
                    <div class="alert alert-dismissible alert-success msg">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Thanks!</strong>
                        <a href="#" class="alert-link">Add Successfully</a>
                    </div>
                    <div class="alert alert-dismissible alert-danger err">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Failed!</strong>
                        <a href="#" class="alert-link">Try Again.</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="padding: 0px !important;">
                        <div class="page-container list-menu-view">
                            <div class="page-content">
                                <div class="main-container">
                                    <div class="container">
                                        <div class="col-md-12">
                                            <div class="login-formmea">
                                                <div class="box-widget widget-module">
                                                    <div class="widget-head clearfix">
                                                        <span class="h-icon"><i class="fa fa-th"></i></span>
                                                        <span class="inner-page-tag">Enquiry </span> 
                                                    </div>

                                                    <div class="widget-container">
                                                        <div class=" widget-block">
                                                            <div class="row">
                                                            	<div class="col-md-12">
																	<form id="convertEnquiryToApplication" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
    																	<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
    																	<input type="hidden" name="cust_enquiry_id" id="cust_enquiry_id" value="<?php echo $leadDetails->cust_enquiry_id; ?>" />
                                                                        <input type="hidden" name="source_id" id="source_id" value="<?php echo $leadDetails->cust_enquiry_data_source_id; ?>" />
                                                                        <input type="hidden" name="geo_coordinates" id="geo_coordinates" value="<?php echo $leadDetails->cust_enquiry_geo_coordinates; ?>" />
                                                                        <input type="hidden" name="ip" id="ip" value="<?php echo $leadDetails->cust_enquiry_ip_address; ?>" />
    																	<div class="alert alert-danger" role="alert" style="display: none" id="application_errors">        
    																	</div>
    																	<div class="col-md-6">
        																	<label class="labelField">Borrower Type</label>
        																	<input type="text" class="form-control inputField" id="borrower_type" name="borrower_type" autocomplete="off" value="NEW" readonly>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">PAN&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="pancard" name="pancard" onchange="validatePanNumber(this)" autocomplete="off" placeholder="PAN">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField" class="labelField">Loan Applied&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="loan_applied" name="loan_applied" value="<?= $leadDetails->cust_enquiry_loan_amount ?>" autocomplete="off"  placeholder="Loan Amount">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField" class="labelField">Loan Tenure&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="loan_tenure" name="loan_tenure" onchange="tenure(this)" autocomplete="off" placeholder="Loan Tenure">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Loan Purpose&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="loan_purpose" name="loan_purpose" autocomplete="off">
            																	<option value="">SELECT</option>
            																	<option value="TRAVEL">TRAVEL</option>
            																	<option value="MEDICAL">MEDICAL</option>
            																	<option value="ACADEMICS">ACADEMICS</option>
            																	<option value="OBLIGATIONS">OBLIGATIONS</option>
            																	<option value="OCCASION">OCCASION</option>
            																	<option value="PURCHASE">PURCHASE</option>
        																	</select>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField" class="labelField">First Name &nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="first_name" name="first_name" value="<?= $leadDetails->cust_enquiry_name ?>" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Middle Name</label>
        																	<input type="text" class="form-control inputField" id="middle_name" name="middle_name" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Surname</label>
        																	<input type="text"  class="form-control inputField" id="sur_name" name="sur_name" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Gender&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="gender" name="gender" autocomplete="off">
            																	<option value="">SELECT</option>
            																	<option value="MALE">MALE</option>
            																	<option value="FEMALE">FEMALE</option>
        																	</select>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">DOB&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="dob" name="dob" autocomplete="off">
        																	<span id="pan_msg" style="color: red;"></span>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Salary Mode&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="salary_mode" name="salary_mode" autocomplete="off">
            																	<option value="">SELECT</option>
            																	<option value="BANK">BANK</option>
            																	<option value="CASH">CASH</option>
        																	</select>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Salary&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="monthly_income" name="monthly_income" onchange="monthlyIncome(this)" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Obligations&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input type="text" class="form-control inputField" id="obligations" name="obligations" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Mobile&nbsp;<strong class="required_Fields">*</strong> </label>
        																	<input type="text" class="form-control inputField" id="mobile" name="mobile" value="<?= $leadDetails->cust_enquiry_mobile ?>" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Mobile Alternate</label>
        																	<input type="text" class="form-control inputField" id="alternate_mobile" name="alternate_mobile" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Email (Personal)&nbsp;<strong class="required_Fields">*</strong></label>
        																	<input  type="text" class="form-control inputField" id="email" name="email" value="<?= $leadDetails->cust_enquiry_email ?>" onchange="IsEmail(this)" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Email (Office)</label>
        																	<input type="text" class="form-control inputField" id="alternate_email" name="alternate_email" onchange="IsEmail(this)" autocomplete="off">
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">State&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="state5" name="state" onchange="state1(this, 0)" autocomplete="off">
        																		<option value="">SELECT</option>
        																		<?php foreach($state as $row) { ?>
        																			<option value="<?= $row->m_state_id ?>"><?= $row->m_state_name ?></option>
        																		<?php } ?>
        																	</select>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">City&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="city0" name="city" value="<?= $leadDetails->cust_enquiry_city_name ?>" onchange="city1(this, 0)" autocomplete="off">
        																	</select>
    																	</div>

    																	<div class="col-md-6">
        																	<label class="labelField">Pincode&nbsp;<strong class="required_Fields">*</strong></label>
        																	<select class="form-control inputField" id="pincode0" name="pincode" autocomplete="off">
        																	</select>
    																	</div>
																	</form>
																	<div class="col-md-12" style="margin: 10px;">
    																	<button id="saveEnquiryToApplication" class="btn btn-success lead-sanction-button">Save </button> 
																	</div>
                                                            	</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    </div>
</section>
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Tasks/main_js') ?>
</section>
</section>

<script type="text/javascript">
  
    function state1(state_id, count) {
        state(state_id, count);
    }

    function city1(city_id, count) {
        city(city_id, count);
    }
</script>