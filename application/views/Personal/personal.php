<?php

$getData = 'mrt_id,mrt_name';
$getrelationTypes = getrelationTypes('master_relation_type', $getData);
$refrenceData = getrefrenceData('lead_customer_references', $leadDetails->lead_id);

?>
<?php if (in_array(agent, ["CR2", "CA", "SA"]) && in_array($leadDetails->stage, ["S5", "S6", "S11"]) && empty($leadDetails->lead_stp_flag) && ($leadDetails->customer_bre_run_flag == 0)) { ?>
    <form id="insertPersonal" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
        <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $leadDetails->lead_id; ?>" />
        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
        <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
        <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
        <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

        <div class="col-md-6">
            <label class="labelField" class="labelField">First Name &nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="p_first_name" name="first_name" autocomplete="off" <?= (($leadDetails->pancard_verified_status == 1) ? 'readonly=readonly' : '') ?>>
        </div>

        <div class="col-md-6">
            <label class="labelField">Middle Name</label>
            <input type="text" class="form-control inputField" id="p_middle_name" name="middle_name" autocomplete="off" <?= (($leadDetails->pancard_verified_status == 1) ? 'readonly=readonly' : '') ?>>
        </div>

        <div class="col-md-6">
            <label class="labelField">Surname</label>
            <input type="text" class="form-control inputField" id="p_sur_name" name="sur_name" autocomplete="off" <?= (($leadDetails->pancard_verified_status == 1) ? 'readonly=readonly' : '') ?>>
        </div>

        <div class="col-md-6">
            <label class="labelField">Gender &nbsp;<strong class="required_Fields">*</strong></label>
            <select class="form-control inputField" id="p_gender" name="gender" autocomplete="off">
                <option value="MALE">MALE</option>
                <option value="FEMALE">FEMALE</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="labelField">DOB&nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="p_dob" name="dob" autocomplete="off" <?= (($leadDetails->customer_digital_ekyc_flag == 1) ? 'readonly=readonly' : '') ?>>
            <span id="pan_msg" style="color: red;"></span>
        </div>

        <div class="col-md-6">
            <label class="labelField">PAN&nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="p_pancard" name="pancard" onchange="validatePanNumber(this)" autocomplete="off" <?= (($leadDetails->pancard_verified_status == 1) ? 'readonly=readonly' : '') ?>>
        </div>

        <div class="col-md-6">
            <label class="labelField">Mobile&nbsp;<strong class="required_Fields">*</strong> </label>
            <input type="text" class="form-control inputField" id="p_mobile" name="mobile" autocomplete="off">
        </div>

        <div class="col-md-6">
            <label class="labelField">Mobile Alternate </label>
            <input type="text" class="form-control inputField" id="p_alternate_mobile" name="alternate_mobile" autocomplete="off">
        </div>

        <div class="col-md-6">
            <label class="labelField">Email (Personal) &nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="p_email" name="email" onchange="IsEmail(this)" autocomplete="off">
        </div>

        <div class="col-md-6">
            <label class="labelField">Email (Office)</label>
            <input type="text" class="form-control inputField" id="p_alternate_email" name="alternate_email" onchange="IsEmail(this)" autocomplete="off" <?= (($leadDetails->alternate_email_verified_status == "YES") ? 'readonly=readonly' : '') ?>>
        </div>

        <div class="col-md-6">
            <label class="labelField">Screened By </label>
            <input type="text" name="screenedBy" class="form-control inputField" id="screenedBy" autocomplete="off" readonly>
        </div>

        <div class="col-md-6">
            <label class="labelField">Screened On</label>
            <input type="text" class="form-control inputField" id="screenedOn" name="screenedOn" autocomplete="off" readonly>
        </div>
        <div class="col-md-6">
            <label class="labelField">Marital Status &nbsp;<strong class="required_Fields">*</strong></label>
            <select class="form-control inputField" id="p_marital_status" name="customer_marital_status_id" autocomplete="off" required="required">
                <option value="">Select</option>
                <option value="1">Single</option>
                <option value="2">Married</option>
                <option value="3">Divorced</option>
                <option value="4">Separated</option>
                <option value="5">Widowed</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="labelField">Religion</label>
            <select class="form-control inputField" id="p_religion" name="customer_religion_id" autocomplete="off" required="required">
                <option value="">Select</option>
                <option value="1">Hindu</option>
                <option value="2">Muslim</option>
                <option value="3">Christian</option>
                <option value="4">Sikh</option>
                <option value="5">Buddhist</option>
                <option value="6">Jain</option>
                <option value="7">Other</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="labelField">Aadhaar Number (Last 4 digit only)<span class="required_Fields">*</span></label>
            <input type="text" class="form-control inputField" id="aadhar_no" name="aadhar_no" autocomplete="off">
        </div>
    </form>
    <?php if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) { ?>
        <div class="col-md-12" style="margin: 10px;">
            <button id="savePersonal" class="btn btn-success lead-sanction-button">Save </button>
        </div>
    <?php } ?>
<?php } else { ?>
    <div id="ViewPersonalDetails"></div>
<?php } ?>
<!------ end for varification section ----------------------->

<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" onclick="getResidenceDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-toggle="collapse" data-target="#RESIDENCE1">RESIDENCE&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div>

<!------ table for  RESIDENCE section ----------------------->

<div id="RESIDENCE1" class="collapse">
    <?php if (in_array(agent, ["CR2", "CA", "SA"]) && in_array($leadDetails->stage, ["S5", "S6", "S11"]) && empty($leadDetails->lead_stp_flag) && ($leadDetails->customer_bre_run_flag == 0)) { ?>
        <form id="insertResidence" class="form-inline" method="post" enctype="multipart/form-data" style="padding: 20px;">
            <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $this->encrypt->encode($leadDetails->lead_id); ?>" />
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
            <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
            <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
            <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField" style="width: 100% !important;font-size: 15px !important;margin: 0px 0px 20px 0px;">Please fill current address details.</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Line 1 <span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="hfBulNo1" name="hfBulNo1" autocomplete="off" style="width: 76% !important;">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Line 2 <span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="lcss1" name="lcss1" autocomplete="off" style="width: 76% !important;">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Landmark </label>
                    <input type="text" class="form-control inputField" id="lankmark1" name="lankmark1" autocomplete="off" style="width: 76% !important;">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">State&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="state1" name="state1" onchange="state(this, 1)" autocomplete="off">
                        <option value="">Select</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">City&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="city1" name="city1" onchange="city(this, 1)" autocomplete="off">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">Pincode&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="pincode1" name="pincode1" autocomplete="off">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">Present Residence Type<span class="required_Fields">*</span></label>
                    <select class="form-control inputField" id="presentResidenceType" name="presentResidenceType" autocomplete="off">
                        <option value="">Select</option>
                        <option value="OWNED">OWNED</option>
                        <option value="RENTED">RENTED</option>
                        <option value="SHARED">SHARED</option>
                        <option value="PARENTAL">PARENTAL</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Residing Since</label>
                    <input type="text" class="form-control inputField" id="residenceSince" name="residenceSince" autocomplete="off">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField" style="width: 100% !important;font-size: 15px !important;margin:20px 0px;">Please fill aadhaar address details.</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">Aadhaar Number (Last 4 digit only)<span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="res_aadhar" name="res_aadhar" autocomplete="off" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                </div>
                <div class="col-md-6">
                    <label class="labelField" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'style="color:green;"' : '') ?>>Resent E-KYC email</label>
                    <input type="checkbox" name="resent_ekyc_email" class="form-control inputField" id="resent_ekyc_email" value="<?= $this->encrypt->encode($leadDetails->lead_id); ?>" style="width: 14px !important;" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'disabled=disabled' : '') ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField" style="width: 220px !important;">Is aadhaar address same as above?</label>
                    <input type="checkbox" name="addharAddressSameasAbove" class="form-control inputField" id="addharAddressSameasAbove" value="YES" style="width: 14px !important;" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'disabled=disabled' : '') ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Line 1 <span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="hfBulNo2" name="hfBulNo2" autocomplete="off" style="width: 76% !important;" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Line 2 <span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="lcss2" name="lcss2" autocomplete="off" style="width: 76% !important;" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="labelField">Address Landmark </label>
                    <input type="text" class="form-control inputField" id="landmark2" name="landmark2" autocomplete="off" style="width: 76% !important;" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">State&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="state2" name="state2" onchange="state(this, 2)" autocomplete="off" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                        <option value="">Select</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">City&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="city2" name="city2" onchange="city(this, 2)" autocomplete="off" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                        <option value="">Select</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="labelField">Pincode&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="pincode2" name="pincode2" autocomplete="off" <?= ($leadDetails->customer_digital_ekyc_flag == 1 ? 'readonly=readonly' : '') ?>>
                        <option value="">Select</option>
                    </select>
                </div>
            </div>

        </form>
        <?php if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) { ?>
            <div class="row" style="padding: 20px;">
                <div class="col-md-6">
                    <label colspan='4' style="text-align: center;">
                        <button id="saveResidence" class="btn btn-success lead-sanction-button">Save </button> </label>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div id="viewResidenceDetails"></div>
    <?php } ?>
</div>

<!-- end section for labele residence section ----------------->

<!------ table for  OFFICE section ----------------------->
<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" onclick="getEmploymentDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-toggle="collapse" data-target="#EMPLOYMENT">EMPLOYMENT&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div>

<div id="EMPLOYMENT" class="collapse">
    <?php if (empty($leadDetails->lead_stp_flag) && $leadDetails->alternate_email_verified_status == "YES") { ?>
        <div class="col-md-12" style="margin-bottom: 10px;">
            <span class="required_Fields"><i class="fa fa-info-circle"></i></span>&nbsp;
            <a class="btn btn-primary" data-toggle="modal" data-target="#bootstrap_data_model" id="get_email_verification_response_api">
                <span onclick="get_email_verification_response_api('<?= $this->encrypt->encode($leadDetails->lead_id); ?>')">Office Email Verification</span>
            </a>
        </div>
    <?php } ?>
    <?php if (in_array(agent, ["CR2", "CA", "SA"]) && in_array($leadDetails->stage, ["S5", "S6", "S11"]) && empty($leadDetails->lead_stp_flag) && ($leadDetails->customer_bre_run_flag == 0)) { ?>
        <form id="insertEmployment" class="form-inline" method="post">
            <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $leadDetails->lead_id; ?>" />
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
            <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
            <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
            <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

            <div class="col-md-12">
                <label class="labelField">Office/ Employer Name&nbsp;<span class="required_Fields">*</span></label>
                <input type="text" class="form-control inputField" id="officeEmpName" name="officeEmpName" autocomplete="off" style="width: 76% !important;">
            </div>

            <div class="col-md-12">
                <label class="labelField">Address Line 1&nbsp;<span class="required_Fields">*</span></label>
                <input type="text" class="form-control inputField" id="hfBulNo3" name="hfBulNo3" autocomplete="off" style="width: 76% !important;">
            </div>

            <div class="col-md-12">
                <label class="labelField">Address Line 2&nbsp;<span class="required_Fields">*</span></label>
                <input type="text" class="form-control inputField" id="lcss3" name="lcss3" autocomplete="off" style="width: 76% !important;">
            </div>

            <div class="col-md-12">
                <label class="labelField">Address Landmark</label>
                <input type="text" class="form-control inputField" id="lankmark3" name="lankmark3" autocomplete="off" style="width: 76% !important;">
            </div>

            <div class="col-md-6">
                <label class="labelField">State&nbsp;<strong class="required_Fields">*</strong></label>
                <select class="form-control inputField" id="state3" name="state3" onchange="state(this, 3)" autocomplete="off">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="labelField">City&nbsp;<strong class="required_Fields">*</strong></label>
                <select class="form-control inputField" id="city3" name="city3" onchange="city(this, 3)" autocomplete="off">
                    <option value="">Select</option>
                </select>
            </div>






            <div class="col-md-6">
                <label class="labelField">Pincode&nbsp;<strong class="required_Fields">*</strong></label>
                <select class="form-control inputField" id="pincode3" name="pincode3" autocomplete="off">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="labelField">Company Website</label>
                <input type="text" class="form-control inputField" id="website" name="website" autocomplete="off">
            </div>

            <div class="col-md-6">
                <label class="labelField">Employer Type&nbsp;<span class="required_Fields">*</span></label>
                <select class="form-control inputField" name="employeeType" autocomplete="off">
                    <option value="">Select Employer Type</option>
                    <option value="Salaried">Salaried</option>
                    <option value="Self-Employed">Self-Employed</option>
                </select>
                <!--<input type="text" class="form-control inputField" id="employeeType" name="employeeType" autocomplete="off" >-->
            </div>

            <div class="col-md-6">
                <label class="labelField">Industry </label>
                <input type="text" class="form-control inputField" id="industry" name="industry" autocomplete="off" disabled>
            </div>

            <div class="col-md-6">
                <label class="labelField">Sector</label>
                <input type="text" class="form-control inputField" id="sector" name="sector" autocomplete="off" disabled>
            </div>

            <div class="col-md-6">
                <label class="labelField">Department </label>
                <select class="form-control inputField" id="department" name="department" autocomplete="off">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="labelField">Designation </label>
                <input type="text" class="form-control inputField" id="designation" name="designation" autocomplete="off">
            </div>

            <div class="col-md-6">
                <label class="labelField">Employed Since&nbsp;<span class="required_Fields">*</span></label>
                <input type="text" class="form-control inputField" id="employedSince" name="employedSince" autocomplete="off">
            </div>

            <div class="col-md-6">
                <label class="labelField">Present Service Tenure</label>
                <input type="text" class="form-control inputField" id="presentServiceTenure" name="presentServiceTenure" autocomplete="off" readonly>
            </div>

            <div class="col-md-6">
                <label class="labelField">Work&nbsp;Mode&nbsp;</label>
                <label class="radio-inline"><input type="radio" id="emp_work_mode_id1" name="emp_work_mode" autocomplete="off" value="WFH">WFH</label>
                <label class="radio-inline"><input type="radio" id="emp_work_mode_id2" name="emp_work_mode" autocomplete="off" Value="WFO">WFO</label>
            </div>
            <!--<div class="col-md-6">-->
            <!--    <label class="labelField">Employee&nbsp;Occupation&nbsp;<span class="required_Fields" id="mendory_id1">*</span></label>-->
            <!--    <select class="form-control inputField" id="EmpOccupation" name="emp_occupation_id" utocomplete="off" >-->
            <!--        <option value="">Select</option>-->
            <!--    </select>-->

            <!--</div>-->
        </form>
        <?php if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) { ?>
            <div class="col-md-12">
                <label colspan='4' style="text-align: center;">
                    <button id="saveEmployment" class="btn btn-success lead-sanction-button">Save </button> </label>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div id="ViewEmploymentDetails"></div>
    <?php } ?>
</div>

<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" onclick="getReferenceDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-toggle="collapse" data-target="#REFERENCES">REFERENCES&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
    <div id="REFERENCES" class="collapse">
        <?php if (in_array(agent, ["CR2", "CA", "SA"]) && in_array($leadDetails->stage, ["S5", "S6", "S11"]) && empty($leadDetails->lead_stp_flag) && ($leadDetails->customer_bre_run_flag == 0)) { ?>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;Note&nbsp;* : <i>Only two reference allowed.</i></p>
            <form id="insertReference" class="form-inline" method="post" enctype="multipart/form-data">
                <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $this->encrypt->encode($leadDetails->lead_id); ?>" />
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
                <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
                <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
                <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <div class="col-md-6">
                    <label class="labelField">Reference&nbsp;<span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="refrence1" name="refrence1" autocomplete="off">
                </div>


                <div class="col-md-6">
                    <label class="labelField">Relation&nbsp;<span class="required_Fields">*</span></label>
                    <select class="form-control inputField" id="relation1" name="relation1" autocomplete="off">
                        <option value="">Select</option>
                        <?php foreach ($getrelationTypes as $rdata) { ?>
                            <option value="<?php echo $rdata['mrt_id']; ?>"><?php echo $rdata['mrt_name']; ?></option>

                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Mobile&nbsp;<span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="refrence1mobile" name="refrence1mobile" autocomplete="off">
                </div>
                <?php if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) { ?>
                    <div class="col-md-12 text-left">
                        <label colspan='4' style="margin-bottom: 15px;margin-top: 10px;">
                            <button id="saveReference" type='button' class="btn btn-success lead-sanction-button">Save </button>
                        </label>
                    </div>
                <?php } ?>
            </form>

            <form id="updateReference" class="form-inline" method="post" enctype="multipart/form-data" style="display:none">
                <input type="hidden" name="upd_lead_id" id="upd_lead_id" value="" />
                <input type="hidden" name="upd_user_id" id="upd_user_id" value="<?= user_id ?>">

                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <div class="col-md-6">
                    <label class="labelField">Reference &nbsp;<span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="upd_refrence1" name="upd_refrence1" autocomplete="off">
                </div>


                <div class="col-md-6">
                    <label class="labelField">Relation&nbsp;<span class="required_Fields">*</span></label>
                    <select class="form-control inputField" id="upd_relation1" name="upd_relation1" autocomplete="off">
                        <option value="">Select</option>
                        <?php
                        $getData = 'mrt_id,mrt_name';
                        $getrelationTypes = getrelationTypes('master_relation_type', $getData);
                        foreach ($getrelationTypes as $rdata) {
                        ?>
                            <option value="<?php echo $rdata['mrt_id']; ?>"><?php echo $rdata['mrt_name']; ?></option>

                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Mobile&nbsp;<span class="required_Fields">*</span></label>
                    <input type="text" class="form-control inputField" id="upd_refrence1mobile" name="upd_refrence1mobile" autocomplete="off">
                </div>
                <?php if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) { ?>
                    <div class="col-md-12 text-left">
                        <label colspan='4' style="margin-bottom: 15px;margin-top: 10px;">
                            <button id="updateReferencebuton" type="button" class="btn btn-success lead-sanction-button">Update </button> </label>
                    </div>
                <?php } ?>
            </form>

        <?php } else { ?> <div id=""></div> <?php } ?>
        <div class="col-md-12">
            <div class="table-responsive">
                <div id="viewReferenceDetails"></div>
            </div>
        </div>
    </div>
</div>
