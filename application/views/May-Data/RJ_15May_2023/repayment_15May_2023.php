<div id="loanStatus"></div>

<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" onclick="collectionHistory('<?= $leadDetails->lead_id ?>', '<?= $leadDetails->customer_id ?>', '<?= user_id ?>')" data-toggle="collapse" data-target="#listRecoveryHistory">Recovery History&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div>

<div id="listRecoveryHistory" class="collapse">
    <div id="recoveryHistory"></div>
</div>

<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" onclick="collection_payment_verification()" data-toggle="collapse" data-target="#addRecoveryPayment">NEW PAYMENT RECEIVED&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div>

<div id="addRecoveryPayment" class="collapse">
    <?php if ((agent == "CO1" || agent == "CO2" || agent == "AC1" || agent == "CA" || agent == "SA" || agent == "CR2" || agent == "CO4" || agent == "CC")) { ?>

        <?php if (($leadDetails->lead_status_id != 16) && ($leadDetails->lead_status_id != 40)) { ?>
            <form id="FormUpdatePayment" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $leadDetails->lead_id; ?>" />
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $leadDetails->customer_id; ?>" />
                <input type="hidden" name="user_id" id="user_id" value="<?= user_id ?>">
                <input type="hidden" name="company_id" id="company_id" value="<?= company_id ?>">
                <input type="hidden" name="product_id" id="product_id" value="<?= product_id ?>">
                <input type="hidden" name="loan_no" id="loan_no" value="<?= $leadDetails->loan_no ?>">
                <input type="hidden" name="recovery_id" id="recovery_id" value="">
                <input type="hidden" name="payment_verification" id="payment_verification" value="0">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

                <div class="col-md-6">
                    <label class="labelField">Payment Received&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="number" class="form-control inputField" id="received_amount" name="received_amount"  autocomplete="off"><!--onchange="receivedAmount(this)"-->
                </div>

                <div class="col-md-6">
                    <label class="labelField">Reference No.&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="text" class="form-control inputField" id="refrence_no" name="refrence_no" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="labelField">Payment Mode&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="collection_payment_mode" name="payment_mode">
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Repayment Type&nbsp;<strong class="required_Fields">*</strong></label>
                    <select class="form-control inputField" id="repayment_type" name="repayment_type" autocomplete="off">
                        <option value="">SELECT</option>
                        <?php foreach ($statusClosuer->result_array() as $row) { ?>
                            <option value="<?= $row['status_id'] ?>"><?= $row['status_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Discount&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="number" class="form-control inputField" id="discount" name="discount" value="0" autocomplete="off">
                    <!--  onchange="discountAmount(this)" -->
                </div>

                <div class="col-md-6">
                    <label class="labelField">Excess/ Refund&nbsp;<strong class="required_Fields">*</strong></label>
                    <input type="number" class="form-control inputField" id="refund" name="refund" value="0" autocomplete="off">
                    <!--  onchange="refundAmount(this)" -->
                </div>

                <?php if (in_array(agent, ['CO1', 'CO2', 'CR2', "CO4", "CC"])) { ?>
                    <div class="col-md-6" id="payment-screenshot">
                        <label class="labelField">Payment Screenshot<strong class="required_Fields">*</strong></label>
                        <input type="file" class="form-control" id="image" name="file_name" autocomplete="off" accept=".jpeg, .jpg, .png">
                    </div>
                    <div class="col-md-6" id='scm-rm' style='display: none'>
                        <label class="labelField">Collected by</label>
                        <select class="form-control inputField" id="collected_by" name="collected_by" autocomplete="off">
                            <option>SELECT</option>
                            <option value='<?= $_SESSION['isUserSession']['user_id'] ?>'>Self</option>
                        </select>
                    </div>
                    <div class="col-md-12" style="margin-top: 10px;margin-bottom:15px;">
                        <label class="labelField">SCM Remarks&nbsp;<strong class="required_Fields">*</strong></label>
                        <textarea class="form-control" id="scm_remarks" name="scm_remarks" autocomplete="off" cols="93" rows="1"></textarea>
                    </div>

                <?php } else if (agent == 'AC1') { ?>
                    <div class="col-md-12">
                        <label class="labelField">Upload Payment&nbsp;<strong class="required_Fields">*</strong></label>
                        <input type="text" class="form-control" id="date_of_recived" name="date_of_recived" autocomplete="off" placeholder="DD-MM-YYYY">
                    </div>

                    <div class="col-md-12" style="margin-top: 10px;">
                        <label class="labelField">Closure Remarks&nbsp;<strong class="required_Fields">*</strong></label>
                        <textarea class="form-control" id="ops_remarks" name="ops_remarks" autocomplete="off" cols="93" rows="1"></textarea>
                    </div>
                <?php } ?>
                <button class="btn btn-success" id="btnUpdatePayment" style="background : #22774e !important;">Upload Payment</button>
            </form>
        <?php } ?>
        <div calss="row" style="margin-top: 10px; border-top: solid 1px #ddd;text-align: center; padding-top : 20px; padding-bottom: 20px; background: #f3f3f3;">
            <div class="col-md-12 text-center" style="margin-top: 20px;">
                <?php if (in_array(agent, ['AC1'])) { ?>
                    <?php if ($leadDetails->lead_status_id == 17) { ?>
                        <button class="btn btn-success" id="btn_send_noc_settlement" onclick="send_NOC_for_settlement_letter('<?= $leadDetails->lead_id ?>')">Send Settlement Letter</button>
                    <?php } else if ($leadDetails->lead_status_id == 16) { ?>
                        <button class="btn btn-success" id="btn_send_noc_closed" onclick="send_NOC_for_closed_letter('<?= $leadDetails->lead_id ?>')">Send NOC for Closed Loan</button>
                    <?php } ?>
                    <?php if (in_array(agent, ['AC1']) && in_array($leadDetails->lead_status_id, [14, 19, 17, 18])) { // , 17, 18 ?>
                        <button class="btn btn-success reject-button" id="RejectPayment">Reject</button>
                        <button class="btn btn-success" id="UpdatePayment">Verify</button>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
