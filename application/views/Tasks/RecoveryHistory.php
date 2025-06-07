<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
<section class="parent_wrapper">
<?php 
$this->load->view('Layouts/header');

$url = $this->uri->segment(1);
$hold_date = date('Y-m-d h:i:s', strtotime(timestamp . ' + 2 days'));
?>
<section class="right-side">
    <style>
    .parent_wrapper {
        width: 100%;
        height: 100vh;
        display: flex;
    }
    
    .audit-success {
        background: green !important;
        border: 1px solid green !important;
    }
    
    .audit-success:hover { 
        background: transparent !important;
        color: green !important;
        border: 1px solid green !important;
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
      }
    </style>
<div class="width-my">
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard" style="height:auto !important;">
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
            <div class="row default-page-height">
                <div class="col-md-12">
                    
					<div  id="RepaymentSaction">
						<div id="repay">
							<?php $this->load->view('Collection/repayment'); ?>
						</div>
					</div>
					<?php if(isset($leadDetails->lead_status_id) && !in_array($leadDetails->lead_status_id,[16,17,18])){ ?>
					<div >
						<div class="footer-support">
							<h2 class="footer-support">
								<button type="button" class="btn btn-danger btn-block" onclick="verifyPayment()" >Close Loan</button>
							</h2>
						</div>
					</div>
					<?php } ?>

                </div>
            </div>
        </div> 
    </div>
</div>
</div>

<?php  $this->load->view('Layouts/footer') ?>

<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script>
	function verifyPayment(){
		    $.confirm({
				title: 'Close Loan',
				content: '' +
				'<form action="" class="formName">' +
				'<div class="form-group">' +
				'<label>Change Loan Status</label>' +
				'<select class="name form-control" required >' +
				'<option value="">SELECT</option>' +
				'<option value="16">CLOSED</option>' +
				'<option value="17">SETTLED</option>' +
				'<option value="18">WRITEOFF</option>' +
				'</select>' +
				'</div>' +
				'</form>',
				buttons: {
					formSubmit: {
						text: 'Submit',
						btnClass: 'btn-blue',
						action: function () {
							var stid = this.$content.find('.name').val();
							if(!stid){							
								return false;								
							}
							$.ajax({
								url: '<?=base_url('loanClosingRequest/'. $this->encrypt->encode($leadDetails->lead_id))?>',
								type: 'POST',
								data: {status_id:stid, csrf_token},
								dataType: "json",
								beforeSend: function () {
									$("#cover").show();
								},
								success: function (response) {
									if (response.errSession) {
										window.location.href = "<?=base_url('/')?>";
									} else if (response.msg) {
										catchSuccess(response.msg);
										window.location.href = "<?=base_url('paymentHistory/'. $this->encrypt->encode($leadDetails->lead_id))?>";
										//get_bre_rule_result();
									} else {
										catchError(response.err);
									}
								},
								complete: function () {
									//$("#cover").fadeOut(1750);
								}
							});
							//return false;
						}
					},
					cancel: function () {
						//close
					},
				},
				onContentReady: function () {
					// bind to events
					var jc = this;
					this.$content.find('form').on('submit', function (e) {
						// if the user submits the form by pressing enter in the field.
						e.preventDefault();
						jc.$$formSubmit.trigger('click'); // reference the button and click it
					});
				}
			});
	}

</script>