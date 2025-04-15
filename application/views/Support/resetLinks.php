<section class="parent_wrapper">
<?php $this->load->view('Layouts/header'); 
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
                                    <div class="col-md-2 drop-me">
                                        <?php $this->load->view('Layouts/leftsidebar') ?>
                                    </div>
                                    <div class="col-md-12 div-right-sidebar">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                 <div class="col-md-12 div-right-sidebar">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
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

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchResetLeadId'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required="" value="<?php if(isset($_POST['lead_id']) && $_POST['lead_id']!=''){echo $_POST['lead_id'];} ?>" placeholder="Please enter lead id*" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
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
                                        <?php
                                        //echo '<pre>';print_r($leadInfo);die;
                                        if(!empty($status)){
                                        ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Update Personal Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <?php if(isset($leadInfo['lead_status_id'])){ ?>
                                                    <div class=" widget-block">
                                                    <form autocomplete="off" action="" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <input type="hidden" name="lead_id" id="lead_id" value="<?php echo isset($leadInfo['lead_id']) ? $leadInfo['lead_id'] : ''; ?>" />
                                                            <input type="hidden" id="ekyc_active" name="ekyc_active" value="0" />
                                                            <input type="hidden" id="ekyc_deleted" name="ekyc_deleted" value="1" />
                                                        
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p style="font-size:16px"><strong>Name:</strong> <?php echo htmlspecialchars($leadInfo['first_name']); ?></p>
                                                                    <p style="font-size:16px"><strong>Personal Email:</strong> <?php echo htmlspecialchars($leadInfo['email']); ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p style="font-size:16px"><strong>Mobile:</strong> <?php echo htmlspecialchars($leadInfo['mobile']); ?></p>
                                                                    <p style="font-size:16px"><strong>Lead Status:</strong> <?php echo htmlspecialchars($leadInfo['status']); ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <?php if(($eKyc_done['customer_digital_ekyc_flag'])==1){ ?>
                                                                    <p style="font-size:16px"><strong>eKyc Status:</strong> Yes</p>
                                                                    <?php } else { ?>
                                                                    <p style="font-size:16px"><strong>eKyc Status:</strong> No</p>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        
                                                            <h3>Application Logs History</h3>
                                                            
                                                            <table class="table dt-table1 table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace"><b>SR.ID</b></th>
                                                                            <th class="whitespace"><b>Lead ID</b></th>
                                                                            <th class="whitespace"><b>User Name</b></th>
                                                                            <th class="whitespace"><b>Remarks</b></th>
                                                                            <th class="whitespace"><b>Status</b></th>
                                                                            <th class="whitespace"><b>Log Date</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    
                                                                    
                                                                       <tbody>
                                                                   
                                                                        <?php foreach ($api_ekyc_log as $api_ekyc_row) { ?>
                                                                                <tr class="table-default" id="id_<?=$i?>">
                                                                                    <td class="whitespace"><?= $api_ekyc_row['id'] ?></td> 
                                                                                    <td class="whitespace"><?= $api_ekyc_row['lead_id'] ?></td>
                                                                                    <td class="whitespace"><?= $api_ekyc_row['name'] ?></td>
                                                                                    <td class="whitespace"><?= $api_ekyc_row['remarks'] ?></td>   
                                                                                    <td class="whitespace"><?= ($api_ekyc_row['status']) ? ($api_ekyc_row['status']) : "-" ?></td> 
                                                                                    <td class="whitespace"><?= !empty($api_ekyc_row["created_on"]) ? display_date_format($api_ekyc_row["created_on"]) : "-" ?></td> 
                                                                                    
                                                                                </tr>
                                                                        
                                                                        <?php } ?>
                                                                    
                                                                        
                                                                    </tbody>
                                                                    
                                                                </table>
                                                        
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="button" id="update_ekyc_link" class="button-add btn update_ekyc_link">Reset E-KYC</button>
                                                                    
                                                                    <button type="button" id="update_esign_link" class="button-add btn update_esign_link">Reset E-SIGN</button>
                                                                    
                                                                </div>
                                                            </div>
                                                        </form>
        
        
                                                            </div>
                                                            <?php } else { ?>
                                                            <p style="color:red">Lead data is Wrong</p>
                                                            <?php } ?>
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
                              
                            <!--Footer Start Here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->load->view('Layouts/footer'); ?>
<?php $this->load->view('Support/supportekyc'); ?>
</section>
</section>

