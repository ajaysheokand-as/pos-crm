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
                                                    <h4>Search loan no? </h4>
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

                                                        <form id="loanNodata" autocomplete="off" action="<?=base_url('support/searchBlackList'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="bl_loan_no" id="bl_loan_no" required="" value="<?php if($referenceInfo['bl_loan_no']!=''){echo $referenceInfo['bl_loan_no'];}else{ echo $_POST['bl_loan_no'];} ?>" placeholder="Please enter loan no.*">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <button type="submit" id="search_loan_no" class="button btn">Search Loan No</button>
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
                                                    <h4>Black List</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped table-bordered table-hover" id="domainTable" style="border:1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace data-fixed-columns"><b>S.No.</b></th>                              
                                                                            <th class="whitespace"><b>Name</b></th>
                                                                            <th class="whitespace"><b>Mobile</b></th>
                                                                            <th class="whitespace"><b>PAN No.</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if(count($leadInfo)>0){ $i=1; foreach($leadInfo as $key => $value) { ?>
                                                                        <tr>                                                                            
                                                                            <td class="whitespace data-fixed-columns"><?=$i?></td>                             
                                                                            <td class="whitespace"><?=$value['bl_customer_first_name']?></td>
                                                                            <td class="whitespace"><?=$value['bl_customer_mobile']?></td>
                                                                            <td class="whitespace"><?=$value['bl_customer_pancard']?></td>
                                                                            <td class="whitespace">
                                                                            <?php if($value['bl_active']==1){?>
                                                                                <span class="black_class" title="block"><i class="fa fa-unlock" aria-hidden="true" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></span>
                                                                            <?php }else{?>
                                                                                <a class="black_class" href="javascript:void();" title="unblack" onclick="blockUpdate('<?=$value['bl_id']?>','<?=$value['bl_active']?>','<?=$value['bl_deleted']?>');"><i class="fa fa-lock" aria-hidden="true" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a>
                                                                            <?php } ?>
                                                                            </td>                                                                       
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
                                        <?php }if(!empty($referenceInfo)){ ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Reference Details :: <a href="<?=base_url('support/searchReferenceId'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a></h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                        <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php if(isset($referenceInfo['lcr_lead_id']) && $referenceInfo['lcr_lead_id']!=''){echo $referenceInfo['lcr_lead_id'];}?>" />
                                                            <input type="hidden" name="lcr_id" id="lcr_id" value="<?php if(isset($referenceInfo['lcr_id']) && $referenceInfo['lcr_id']!=''){echo $referenceInfo['lcr_id'];}?>" />
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label for="lcr_name_id">Name <span class="span" style="color:red;">*</span></label>                  
                                                                    <input type="text" required class="form-control" name="lcr_name" id="lcr_name_id" value="<?php if(isset($referenceInfo['lcr_name']) && $referenceInfo['lcr_name']!=''){echo $referenceInfo['lcr_name'];}else{echo $_POST['lcr_name'];} ?>">
                                                                </div> 
                                                                <div class="col-md-4">
                                                                    <label for="lcr_mobile_id">Mobile <span class="span" style="color:red;">*</span></label>                    
                                                                    <input type="text" class="form-control" name="lcr_mobile" id="lcr_mobile_id" value="<?php if(isset($referenceInfo['lcr_mobile']) && $referenceInfo['lcr_mobile']!=''){echo $referenceInfo['lcr_mobile'];}else{echo $_POST['lcr_mobile'];} ?>" maxlength="10" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" required>
                                                                </div>                                                                                                               
                                                                <div class="col-md-4">
                                                                    <label for="lcr_relationType_id">Relation Type <span class="span" style="color:red;">*</span></label>                                                 
                                                                    <select class="form-control" name="lcr_relationType" required id="lcr_relationType_id">
                                                                        <option value="">Select Relation Type</option>
                                                                        <?php if(count($relation_list)>0){foreach($relation_list as $key => $relationVal) {?>
                                                                         <option value="<?=$relationVal['mrt_id']?>"<?php if(isset($referenceInfo['lcr_relationType']) && $referenceInfo['lcr_relationType']==$relationVal['mrt_id']){echo 'Selected="Selected"';} ?>><?=$relationVal['mrt_name']?></option>   
                                                                        <?php }} ?>
                                                                    </select>
                                                                </div> 
                                                                <p>&nbsp</p>
                                                                <div class="col-md-6"> 
                                                                    <label for="lead_followup_remark">Lead Followup Remark </label>                                       
                                                                    <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark" style="width:100% !important;height:50px !important;" autocomplete="off" placeholder="Please enter lead followup remark."></textarea>
                                                                </div>                                                                                                          
                                                            </div>
                                                            <div class="row">                                                                
                                                                <div class="col-md-6">
                                                                    <button type="button" class="button-add btn update_reference_details">Update Reference Details</button>
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

