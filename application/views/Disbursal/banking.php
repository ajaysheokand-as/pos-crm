
<div id="disbursalBanking"></div>
<?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && $leadDetails->customer_bre_run_flag==0) || in_array(agent, array("DS1", "CA", "SA"))) && in_array($leadDetails->stage, array("S5", "S6", "S11", "S21", "S22", "S25")) && ($leadDetails->customer_bre_run_flag==0)) { ?>
    <div class="footer-support">
        <h2 class="footer-support">
            <button type="button" id="btnAddBank" class="btn btn-info collapse" data-toggle="collapse" data-target="#AddBank" style="width: 13% !important;">Add Banking&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
<?php } ?>

<!------ Add Banking section ----------------------->

<div id="AddBank" class="collapse"> 

    <form id="addBeneficiary" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
        <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $this->encrypt->encode($leadDetails->lead_id); ?>" />
        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
        <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
        <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
        <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
        <?php $customer_name = preg_replace("!\s+!", " ", $leadDetails->cust_full_name); ?>

        <div class="col-md-6">
            <label class="labelField">Bank A/C No.&nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="bankA_C_No" name="bankA_C_No" autocomplete="off">
        </div>

        <div class="col-md-6">
            <label class="labelField">Reconfirm Bank A/C No.&nbsp;<strong class="required_Fields">*</strong> </label>
            <input type="text" class="form-control inputField" id="confBankA_C_No" name="confBankA_C_No" onchange="customer_confirm_bank_ac_no(this)" autocomplete="off">
        </div>
        <div class="col-md-6">
            <label class="labelField" class="labelField">IFSC Code&nbsp;<strong class="required_Fields">*</strong></label>
            <select class="form-control inputField" id="customer_ifsc_code" name="customer_ifsc_code" autocomplete="off">
            </select>
        </div>
        <div class="col-md-6">
            <label class="labelField">Beneficiary Name&nbsp;<strong class="required_Fields">*</strong></label>
            <input type="text" class="form-control inputField" id="beneficiary_name" name="beneficiary_name" value="<?= $customer_name ?>" autocomplete="off">
        </div>



        <div class="col-md-6">
            <label class="labelField">Bank A/C Type&nbsp;<strong class="required_Fields">*</strong></label>
            <select class="form-control inputField" id="customer_bank_ac_type" name="customer_bank_ac_type" autocomplete="off">
                <option value="SAVINGS">SAVINGS</option>
                <option value="CURRENT">CURRENT</option>
                <option value="OVERDRAFT">OVERDRAFT</option>
                <option value="NRO SAVING SEAFARER">NRO SAVING SEAFARER</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="labelField">Bank Name</label>
            <input type="text" class="form-control inputField" id="customer_bank_name" name="customer_bank_name" autocomplete="off" readonly>
        </div>

        <div class="col-md-6">
            <label class="labelField">Branch Name</label>
            <input type="text"  class="form-control inputField" id="customer_bank_branch" name="customer_bank_branch" autocomplete="off" readonly>
        </div>
    </form>
    <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) || in_array(agent, array("DS1", "CA", "SA"))) && in_array($leadDetails->stage, array("S5", "S6", "S11", "S21", "S22", "S25"))) { ?>
        <div class="col-md-12" style="margin: 10px;">
            <button id="saveBeneficiary" class="btn btn-success lead-sanction-button">Save </button> 
        </div>
    <?php } ?>
</div>

<div id="viewBankingDetails"></div>

<?php if (in_array(agent, array("CR2", "DS1", "CA", "SA")) && in_array($leadDetails->stage, array("S5", "S6", "S11", "S21", "S22", "S25")) && ($leadDetails->customer_bre_run_flag==0)) { ?>
    <div class="footer-support">
        <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#confirmDisbursalBank">BANK ACCOUNT FOR DISBURSAL - VERIFICATION&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
<?php } ?>
<div id="confirmDisbursalBank" class="collapse"> 
    <form id="FormverifyDisbursalBank" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
        <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $this->encrypt->encode($leadDetails->lead_id); ?>" />
        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
        <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
        <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
        <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />


        <div class="col-md-6">
            <label class="labelField" class="labelField">Bank A/C No.&nbsp;<strong class="required_Fields">*</strong></label>
            <select class="form-control inputField" id="list_bank_AC_No" name="list_bank_AC_No" autocomplete="off">
            </select>
        </div>

        <div class="col-md-6">
            <label class="labelField">Account Verification Status&nbsp;<strong class="required_Fields">*</strong> </label>
            <select class="form-control inputField" id="bank_ac_verification" name="bank_ac_verification" autocomplete="off">
                <option value="">SELECT</option>
                <?php
                if (!empty($master_bank_account_status)) {
                    foreach ($master_bank_account_status as $account_status_key => $account_status_name) {
                        ?>
                        <option value="<?= $this->encrypt->encode($account_status_key) ?>"><?= $account_status_name ?></option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>

        <div class="col-md-12">
            <label class="labelField">Remark</label>
            <textarea class="form-control" id="remarks" name="remarks" autocomplete="off" style="width: 76%;"></textarea>
        </div>
    </form>
    <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11))) || in_array(agent, array("DS1", "CA", "SA"))) && in_array($leadDetails->stage, array("S5", "S6", "S11", "S21", "S22", "S25"))) { ?>
        <div class="col-md-12" style="margin: 10px;">
            <button id="verifyDisbursalBank" class="btn btn-success lead-sanction-button">Save </button> 
        </div>
    <?php } ?>
</div>

<?php if (false && in_array(agent, array("CR2", "DS1", "CA", "SA")) && in_array($leadDetails->stage, array("S5", "S6", "S11", "S21", "S22", "S25")) && $leadDetails->customer_bre_run_flag==0) { ?>
    <div class="footer-support">
        <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#CallBankingAPI">Analyse Banking&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
<?php } ?>

<div id="getBankingAnalysisData"></div>
