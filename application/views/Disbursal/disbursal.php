
<div id="ViewDisbursalDetails"></div>
<?php if ((agent == "DS2" && $leadDetails->stage == "S13")) { ?>
    <div class="footer-support" id="div1disbursalBank">
        <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse"  data-toggle="collapse" data-target="#disbursalBank">Disbursal Bank&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
    <div id="disbursalBank"><!-- collapse -->
        <div class="form-group " >
            <form id="disbursalPayableDetails" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" class="form-control" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id); ?>" readonly>
                <input type="hidden" class="form-control" name="company_id" id="company_id" value="<?= company_id ?>" readonly>
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
                <input type="hidden" class="form-control" name="product_id" id="product_id" value="<?= product_id ?>" readonly>
                <input type="hidden" class="form-control" name="user_id" id="user_id" value="<?= user_id ?>" readonly>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="labelField">Payable Account&nbsp;<strong class="required_Fields">*</strong></label>
                            <select class="form-control inputField" name="payableAccount" id="payableAccount" required autocomplete="off">
                                <option value="">Select</option>
                                <?php
                                if (!empty($master_disbursement_bank_list)) {

                                    foreach ($master_disbursement_bank_list as $bank_id => $bank_list) {
                                        ?>
                                        <option value="<?= $bank_id ?>"><?= $bank_list['disb_bank_name'] . " / " . $bank_list['disb_bank_account_no'] ?></option>
                                        <?php
                                    }
                                }
                                ?>

                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="labelField">Net Disbursal Amount (Rs)&nbsp;<strong class="required_Fields">*</strong></label>
                            <input type="text" class="form-control inputField" name="payable_amount" id="payable_amount" readonly required>
                        </div>

                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="labelField">Payment Mode&nbsp;<strong class="required_Fields">*</strong></label>
                            <select class="form-control inputField" style="width:100%;" name="payment_mode" id="payment_mode" required>
                                <option value="">Select</option>
                                <option value="1">Online</option>
                                <option value="2">Offline</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="labelField">Channel&nbsp;<strong class="required_Fields">*</strong></label>
                            <select class="form-control inputField" style="width:100%;" name="channel" id="channel" required>
                                <option value="">Select</option>
                                <option value="1">IMPS</option>
                                <option value="2">NEFT</option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="form-group">

                    <div class="row">
                        <div class="col-md-6">
                            <label class="labelField">Disbursal Date&nbsp;<strong class="required_Fields">*</strong></label>
                            <input type="text" class="form-control inputField" name="disbursal_date" id="disbursal_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="labelField">Remarks&nbsp;<strong class="required_Fields">*</strong></label>
                            <input type="text" class="form-control inputField" name="disbursal_remarks" id="disbursal_remarks" required/>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="form-group" id="divbtnDisburse" style="float:left; width:100%; margin-bottom: 0px;">
            <div calss="row" style="border-top: solid 1px #ddd;text-align: center; padding-top : 20px; padding-bottom: 20px; background: #f3f3f3;">
                <div calss="col-md-12 text-center">
                    <button class="btn btn-primary" id="allowDisbursalToBank" style="text-align: center; padding-left: 50px; padding-right: 50px; font-weight: bold;">Disburse</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

	<?php if ((in_array(agent, ["CA","DS2"])) && ($leadDetails->lead_status_id == 14)) { ?>
                    
                       <div class="text-center">           
                                        <br/><button type="button" class="btn btn-info" onclick="sendDisbursalNotice('<?= $leadDetails->lead_id ?>')">Send Disbursal Letter On Email</button>
                        </div>
                    <?php } ?>

<?php if ((agent == "DS2" && $leadDetails->stage == "S13")) { ?>
    <div class="footer-support" id="div1UpdateReferenceNo">
        <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#divUpdateReferenceNo">Update Reference&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
    <div id="divUpdateReferenceNo" class="collapse">
        <div class="form-group">
            <form id="formUpdateReferenceNo" method="post" enctype="multipart/form-data">
                <input type="hidden" class="form-control" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id); ?>" readonly>
                <input type="hidden" class="form-control" name="company_id" id="company_id" value="<?= company_id ?>" readonly>
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
                <input type="hidden" class="form-control" name="product_id" id="product_id" value="<?= product_id ?>" readonly>
                <input type="hidden" class="form-control" name="user_id" id="user_id" value="<?= user_id ?>" readonly>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

                <div class="col-md-6">
                    <label class="labelField1">Reference no&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="text" class="form-control inputField1" name="loan_reference_no" id="loan_reference_no" required>
                </div>

                <div class="col-md-6">
                    <label class="labelField1">Screenshot&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="file" class="form-control inputField" id="file" name="file_name" accept=".png, .jpg, .jpeg" autocomplete="off" required>
                </div>

                <div class="form-group" style="float:left; width:100%; margin-bottom: 0px;margin-top: 15px;">
                    <div calss="row" style="border-top: solid 1px #ddd;text-align: center; padding-top : 20px; padding-bottom: 20px; background: #f3f3f3;">
                        <div calss="col-md-12 text-center">
                            <button class="btn btn-primary" id="updateReferenceNo" style="text-align: center; font-weight: bold;">Update Reference</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

