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

                                                        <form id="leadIddata" autocomplete="off" action="<?=base_url('support/searchTransactionId'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required="" value="<?php if($transInfo['disb_trans_lead_id']!=''){echo $transInfo['disb_trans_lead_id'];}else{ echo $_POST['lead_id'];} ?>" placeholder="Please enter lead id*" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
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
                                                    <h4>Update Transaction Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped table-bordered table-hover" id="domainTable" style="border:1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace data-fixed-columns"><b>S.No.</b></th>                              
                                                                            <th class="whitespace"><b>Trans Reference No.</b></th>
                                                                            <th class="whitespace"><b>Trans Bank</b></th>
                                                                            <th class="whitespace"><b>Payment Mode</b></th>
                                                                            <th class="whitespace"><b>Trans Payment Type</b></th>
                                                                            <th class="whitespace"><b>Trans Status</b></th>
                                                                            <th class="whitespace"><b>Created Date</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if(count($leadInfo)>0){ $i=1; foreach($leadInfo as $key => $value) { ?>
                                                                        <tr id="id_<?=$i?>">                                                                            
                                                                            <td class="whitespace data-fixed-columns"><?=$i?></td>                             
                                                                            <td class="whitespace"><?=$value['disb_trans_reference_no']?></td>
                                                                            <td class="whitespace"><?=$value['disb_bank_name']?></td>
                                                                            <td class="whitespace"><?php if($value['disb_trans_payment_mode_id']=='1'){echo 'Online';}else{echo 'Offline';}?></td>
                                                                            <td class="whitespace"><?php if($value['disb_trans_payment_type_id']=='1'){echo 'IMPS';}else{echo 'NEFT';}?></td>
                                                                            <td class="whitespace"><?php if($value['disb_trans_status_id']=='1'){echo 'Initiated';}else if($value['disb_trans_status_id']=='2'){echo 'Pending';}else if($value['disb_trans_status_id']=='3'){echo 'Failed';}else if($value['disb_trans_status_id']=='4'){echo 'Hold';}else{echo 'Completed';}?></td>
                                                                            <td class="whitespace"><?php if($value['disb_trans_created_on']!=''){echo $value['disb_trans_created_on'];}?></td>
                                                                            <td class="whitespace"><a href="<?= base_url('getTransactionId/'.$this->encrypt->encode($value['disb_trans_id'])); ?>"><span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span></a><a class="transaction_delete_class" title="Delete" href="javascript:void();" onclick="transactionDelete('<?=$this->encrypt->encode($value['disb_trans_id']) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a></td>                                                                       
                                                                        </tr> 
                                                                        <?php $i++;}} else { ?>
                                                                           <tr><td colspan="5" class="whitespace" style="color:red;">Data not found.</td></tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php }if(!empty($transInfo)){ ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Transaction Details :: <a href="<?=base_url('support/searchTransactionId'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a></h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($transInfo['disb_trans_lead_id']) && $transInfo['disb_trans_lead_id']!=''){echo $transInfo['disb_trans_lead_id'];}?>" />
                                                            <input type="hidden" name="disb_trans_id" id="disb_trans_id" value="<?php if(isset($transInfo['disb_trans_id']) && $transInfo['disb_trans_id']!=''){echo $transInfo['disb_trans_id'];}?>" />
                                                            <div class="row">                                                                
                                                                <div class="col-md-4">
                                                                    <label for="disb_trans_reference_no_id">Trans Reference No. <span class="span" style="color:red;">*</span></label>                  
                                                                    <input type="text" disabled="disabled" required class="form-control" name="disb_trans_reference_no" id="disb_trans_reference_no_id" value="<?php if(isset($transInfo['disb_trans_reference_no']) && $transInfo['disb_trans_reference_no']!=''){echo $transInfo['disb_trans_reference_no'];}else{echo $_POST['disb_trans_reference_no'];} ?>">
                                                                </div> 
                                                                <div class="col-md-4">
                                                                    <label for="loan_no_id">Loan No. <span class="span" style="color:red;">*</span></label>                  
                                                                    <input type="text" disabled="disabled" required class="form-control" name="loan_no" id="loan_no_id" value="<?php if(isset($transInfo['loan_no']) && $transInfo['loan_no']!=''){echo $transInfo['loan_no'];}else{echo $_POST['loan_no'];} ?>">
                                                                </div> 
                                                                <div class="col-md-4">
                                                                    <label for="disb_trans_bank_id">Trans Bank <span class="span" style="color:red;">*</span></label>                    
                                                                    <input type="text" disabled="disabled" class="form-control" name="disb_trans_bank_id" id="disb_trans_bank_id" value="<?php if(isset($transInfo['disb_bank_name']) && $transInfo['disb_bank_name']!=''){echo $transInfo['disb_bank_name'];}else{echo $_POST['disb_trans_bank_id'];} ?>">
                                                                </div>
                                                            </div>
                                                            <p>&nbsp</p>
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <label for="disburse_refrence_no_id">Disburse Reference No</label>                    
                                                                    <input type="text" class="form-control" name="disburse_refrence_no" required id="disburse_refrence_no_id" value="<?php if(isset($transInfo['disburse_refrence_no']) && $transInfo['disburse_refrence_no']!=''){echo $transInfo['disburse_refrence_no'];}else{echo $_POST['disburse_refrence_no'];} ?>">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="disb_trans_payment_mode_id">Payment Mode <span class="span" style="color:red;">*</span></label>                    
                                                                    <select class="form-control" name="disb_trans_payment_mode_id" required id="disb_trans_payment_mode_id">
                                                                        <option value="">Select Status</option>                                                                        
                                                                        <option value="1"<?php if($transInfo['loan_disbursement_payment_mode_id']=='1'){echo 'Selected="Selected"';} ?>>Online</option> 
                                                                        <option value="2"<?php if($transInfo['loan_disbursement_payment_mode_id']=='2'){echo 'Selected="Selected"';} ?>>Offline</option>
                                                                    </select>                                                                    
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="loan_disbursement_payment_type_id">Trans Payment Type <span class="span" style="color:red;">*</span></label>                    
                                                                    <select class="form-control" name="loan_disbursement_payment_type_id" required id="loan_disbursement_payment_type_id">
                                                                        <option value="">Select Status</option>                                                                        
                                                                        <option value="1"<?php if($transInfo['loan_disbursement_payment_type_id']=='1'){echo 'Selected="Selected"';} ?>>IMPS</option> 
                                                                        <option value="2"<?php if($transInfo['loan_disbursement_payment_type_id']=='2'){echo 'Selected="Selected"';} ?>>NEFT</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="disb_trans_status_id">Trans Status <span class="span" style="color:red;">*</span></label>                                                 
                                                                    <select class="form-control" name="disb_trans_status_id" required id="disb_trans_status_id">
                                                                        <option value="">Select Status</option>                                                                        
                                                                        <option value="1"<?php if($transInfo['disb_trans_status_id']=='1'){echo 'Selected="Selected"';} ?>>Initiated</option> 
                                                                        <option value="2"<?php if($transInfo['disb_trans_status_id']=='2'){echo 'Selected="Selected"';} ?>>Pending</option>
                                                                        <option value="3"<?php if($transInfo['disb_trans_status_id']=='3'){echo 'Selected="Selected"';} ?>>Failed</option>
                                                                        <option value="4"<?php if($transInfo['disb_trans_status_id']=='4'){echo 'Selected="Selected"';} ?>>Hold</option>
                                                                        <option value="5"<?php if($transInfo['disb_trans_status_id']=='5'){echo 'Selected="Selected"';} ?>>Completed</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <p>&nbsp</p>
                                                            <div class="row">                                                                                                                                
                                                                <div class="col-md-6"> 
                                                                    <label for="remarks_id">Remarks</label>                                      
                                                                    <textarea class="form-control" name="remarks" id="remarks_id" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter remarks."><?php if(isset($transInfo['remarks']) && $transInfo['remarks']!=''){echo $transInfo['remarks'];}else{echo $_POST['remarks'];} ?></textarea>
                                                                </div>                                                            
                                                                <div class="col-md-6"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark </label>                                       
                                                                    <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>                                                    
                                                            </div>
                                                            <div class="row">                                                                
                                                                <div class="col-md-6">
                                                                    <button type="button" class="button-add btn update_transaction_failed_details">Update Transaction Details</button>
                                                                </div>
                                                            </div>

                                                        </form>                                                          
                                                        </div>                                                        
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

