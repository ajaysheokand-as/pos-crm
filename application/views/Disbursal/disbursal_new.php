<div id="ViewDisbursalDetails"></div>
	<div class="footer-support" id="div1disbursalhistory">
        <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse"  data-toggle="collapse" data-target="#disbursalHistory">Disbursal History&nbsp;<i class="fa fa-angle-double-down"></i></button>
        </h2>
    </div>
	<div id="disbursalHistory" class="collapse in" aria-expanded="true" style=""> 
        <div >
			<div class="table-responsive">
				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr class="table-primary">
							<th class="whitespace">Lead ID</th>
							<th class="whitespace">Bank Ref.</th>
							<th class="whitespace">Trans. Ref.</th>
							<th class="whitespace">Disbursed&nbsp;On</th>
							<th class="whitespace">Disbursal Status</th>
							<th class="whitespace">Api Status</th>
							<th class="whitespace">Loan&nbsp;No</th>
							<th class="whitespace">Loan&nbsp;Amount</th>
							<th class="whitespace">Disbursed&nbsp;Amount</th>
							<th class="whitespace">Disbursed&nbsp;Message</th>
							<th class="whitespace">Tran. Type</th>
							<th class="whitespace">Tran. Mode</th>
							<th class="whitespace">Bene.&nbsp;Account</th>
							<th class="whitespace">Bene.&nbsp;IFCS</th>
							<th class="whitespace">Bene.&nbsp;Name</th>
							<th class="whitespace">Action</th>
						</tr>
					</thead>
					<tbody id="disbursalHistoryRow">
					</tbody>
				</table>
			</div>
		</div>
	</div>		
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
                    <button class="btn btn-primary" id="allowDisbursalToBank_new" style="text-align: center; padding-left: 50px; padding-right: 50px; font-weight: bold;">Disburse</button>
                </div>
            </div>
        </div>
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
<script>
function changePayoutStaus(e){
	var btn = e;
	var refId = $(btn).attr('data-refId');
	var leadId = $(btn).attr('data-leadId');
	$('#changeStatus').modal('show');
}
function getPayoutStatus(e){
	var btn = e;
	var refId = $(btn).attr('data-refId');
	var leadId = $(btn).attr('data-leadId');
	$('#data-table tbody').html('');
	$.ajax({
            url: '<?= base_url("disbursalToBankStatus") ?>',
            type: 'POST',
            data: {lead_id: leadId,ref_id: refId, csrf_token},
            success: function (response) {
                $('#myModal').modal('show');
	
				var jsonData = JSON.parse(response);
						
						// Get the table body element
						var tableBody = $('#data-table tbody');

						// Iterate over each key-value pair in the JSON object
						$.each(jsonData.ImpsResponse, function(key, value) {
							// Create a table row
							var tableRow = $('<tr></tr>');

							// Create and append the first cell (key)
							tableRow.append('<th>' + key + '</th>');

							// Create and append the second cell (value)
							tableRow.append('<td>' + value + '</td>');

							// Append the table row to the table body
							tableBody.append(tableRow);
						});
			}
		});
}
function viewLog(e){
	var btn = e;
	var json = $(btn).attr('data-log');
	$('#myModal').modal('show');
	$('#data-table tbody').html('');
	var jsonData = JSON.parse(json);
			$('#data-table tbody').html('');
            // Get the table body element
            var tableBody = $('#data-table tbody');

            // Iterate over each key-value pair in the JSON object
            $.each(jsonData, function(key, value) {
                // Create a table row
                var tableRow = $('<tr></tr>');

                // Create and append the first cell (key)
                tableRow.append('<th>' + key + '</th>');

                // Create and append the second cell (value)
                tableRow.append('<td>' + value + '</td>');

                // Append the table row to the table body
                tableBody.append(tableRow);
            });
}
</script>
<div class="modal fade bs-example-modal-sm" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content clearfix">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Data Response</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="data-table" border="1" class="table table-bordered">
					<thead>
						<tr>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-sm" id="changeStatus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document" style="margin: 2% 30% auto;">
        <div class="modal-content clearfix">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Change Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formUpdatePayoutStatus" method="post" >
                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" name="status" id="status" >
							<option value="success">Success</option>
							<option value="failed">Failed</option>
							<option value="pending">Pending</option>
						</select>
                    </div>

                    <!-- password input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label" for="v">Comment</label>
                        <input type="text" id="comment" name="status" class="form-control" />
                    </div>
					<br/>
                    <!-- Submit button -->
                    <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block">Update</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

