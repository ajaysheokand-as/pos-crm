
<form id="insertApplication" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
    <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $this->encrypt->encode($leadDetails->lead_id); ?>" />
    <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
    <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
    <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
    <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
    <div class="alert alert-danger" role="alert" style="display: none" id="application_errors">        
    </div>
    <div class="col-md-6">
        <label class="labelField">Borrower Type</label>
        <input type="text" class="form-control inputField" id="borrower_type" name="borrower_type" autocomplete="off" value="NEW" readonly>
    </div>

    <div class="col-md-6">
        <label class="labelField">PAN&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" id="pancard" name="pancard" onchange="validatePanNumber(this)" autocomplete="off" placeholder="PAN" required="required" maxlength="10" <?= ($leadDetails->pancard_verified_status == 1 ? 'readonly=readonly' : '') ?>>
    </div>

    <div class="col-md-6">
        <label class="labelField" class="labelField">Loan Applied&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" id="loan_applied" name="loan_applied" autocomplete="off"  placeholder="Loan Amount" required="required" maxlength="10">
    </div>

    <div class="col-md-6">
        <label class="labelField" class="labelField">Loan Tenure&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" id="loan_tenure" name="loan_tenure" onchange="tenure(this)" autocomplete="off" placeholder="Loan Tenure" required="required" maxlength="2">
    </div>

    <div class="col-md-6">
        <label class="labelField">Loan Purpose&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="loan_purpose" name="loan_purpose" autocomplete="off" required="required">
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
        <input type="text" class="form-control inputField" id="first_name" name="first_name" autocomplete="off" required="required" maxlength="40" <?= ($leadDetails->pancard_verified_status == 1 ? 'readonly=readonly' : '') ?>>
    </div>

    <div class="col-md-6">
        <label class="labelField">Middle Name</label>
        <input type="text" class="form-control inputField" id="middle_name" name="middle_name" autocomplete="off" maxlength="40" <?= ($leadDetails->pancard_verified_status == 1 ? 'readonly=readonly' : '') ?>>
    </div>

    <div class="col-md-6">
        <label class="labelField">Surname</label>
        <input type="text"  class="form-control inputField" id="sur_name" name="sur_name" autocomplete="off"  maxlength="40" <?= ($leadDetails->pancard_verified_status == 1 ? 'readonly=readonly' : '') ?>>
    </div>

    <div class="col-md-6">
        <label class="labelField">Gender&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="gender" name="gender" autocomplete="off" required="required">
            <option value="">SELECT</option>
            <option value="MALE">MALE</option>
            <option value="FEMALE">FEMALE</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">DOB&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" min="1971-01-01" id="dob" name="dob" autocomplete="off" required="required" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
        <span id="pan_msg" style="color: red;"></span>
    </div>

    <div class="col-md-6">
        <label class="labelField">Income Type&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="income_type" name="income_type" autocomplete="off" required="required">
            <option value="">SELECT</option>
            <option value="1">SALARIED</option>
            <option value="2">SELF-EMPLOYED</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">Salary Mode&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="salary_mode" name="salary_mode" autocomplete="off" required="required">
            <option value="">SELECT</option>
            <option value="BANK">BANK</option>
            <option value="CASH">CASH</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">Salary&nbsp; <strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" id="monthly_income" name="monthly_income" onchange="monthlyIncome(this)" autocomplete="off" value="<?= !empty($leadDetails->monthly_income) ? round($leadDetails->monthly_income) : !empty($leadDetails->monthly_salary_amount) ? $leadDetails->monthly_salary_amount : '' ?>" required="required">
    </div>

    <div class="col-md-6">
        <label class="labelField">Religion&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="religion1" name="religion" onchange="religion(this, 1)" autocomplete="off" required="required">
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">Promocode</label>
        <input type="text" class="form-control inputField" id="promocode" name="promocode" autocomplete="off" disabled="">
    </div>

    <div class="col-md-6">
        <label class="labelField">Obligations&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="text" class="form-control inputField" id="obligations" name="obligations" autocomplete="off" required="required" maxlength="10">
    </div>

    <div class="col-md-6">
        <label class="labelField">Mobile&nbsp;<strong class="required_Fields">*</strong> </label>
        <input type="text" class="form-control inputField" id="mobile" name="mobile" autocomplete="off" required="required" maxlength="10">
    </div>

    <div class="col-md-6">
        <label class="labelField">Mobile Alternate</label>
        <input type="text" class="form-control inputField" id="alternate_mobile" name="alternate_mobile" autocomplete="off" maxlength="10">
    </div>

    <div class="col-md-6">
        <label class="labelField">Email (Personal)&nbsp;<strong class="required_Fields">*</strong></label>
        <input  type="text" class="form-control inputField" id="email" name="email" onchange="IsEmail(this)" autocomplete="off" required="required" maxlength="60">
    </div>

    <div class="col-md-6">
        <label class="labelField">Email (Office)</label>
        <input type="text" class="form-control inputField" id="alternate_email" name="alternate_email" onchange="IsEmail(this)" autocomplete="off">
    </div>

    <div class="col-md-6">
        <label class="labelField">State&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="state10" name="state" onchange="state1(this, 10)" autocomplete="off" required="required">
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">City&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="city10" name="city" onchange="city1(this, 10)" autocomplete="off" required="required">
        </select>
    </div>


    <div class="col-md-6">
        <label class="labelField">Pincode&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="pincode10" name="pincode" autocomplete="off" required="required">
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">Aadhaar Number (Last 4 digit only)&nbsp;<strong class="required_Fields">*</strong></label>
        <input type="number" class="form-control inputField" id="aadhar" name="aadhar" autocomplete="off" maxlength="4" required="required">
    </div>
   
     <div class="col-md-6">
        <label class="labelField">Qualification&nbsp;</label>
        <select class="form-control inputField" id="Qualification1" name="customer_qualification_id" autocomplete="off">
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="labelField">Marital Status&nbsp;<strong class="required_Fields">*</strong></label>
        <select class="form-control inputField" id="MaritalStatus1" name="customer_marital_status_id" onchange="showFieldMandotry(this.value);" autocomplete="off" required="required">
        </select>
    </div>

    <div class="col-md-6">
        <label class="labelField">Spouse Name&nbsp;<span id="mendory_id"></span></label>
        <input type="text" class="form-control inputField" id="customer_spouse_name" name="customer_spouse_name" autocomplete="off" >
    </div>
    <div class="col-md-6">
        <label class="labelField">Spouse Occupation&nbsp;<span id="mendory_id1"></span></label>
        <select class="form-control inputField" id="SpouseOccupation1" name="customer_spouse_occupation_id" autocomplete="off" >
        </select>
    </div>
    <div class="col-md-6">
        <label class="labelField">Reloan Remark&nbsp;<span id="reloan_remark"></span></label>
        <input type="text" class="form-control inputField" id="reloan_remark" name="reloan_remark" autocomplete="off">
    </div>
   


</form>
<?php if (agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(2, 3))) { ?>
    <div class="col-md-12" style="margin: 10px;">
        <button id="saveApplication" class="btn btn-success lead-sanction-button">Save </button> 
    </div>
<?php } ?>
