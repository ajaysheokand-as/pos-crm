<section class="parent_wrapper">
<?php
$this->load->view('Layouts/header');
include('inner_layout.php');
?>
    <div class="container-fluid">

        <div class="taskPageSize taskPageSizeDashboard" style="margin-top:30px">

            <div class="row">

                <div class="col-md-12">

                    <div class="page-container list-menu-view">

                        <div class="page-content">

                            <div class="main-container">

                                <div class="container-fluid">

                                    <div class="col-sm-12 div-right-sidebar">
                                          <div class="row">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>
                                                   Add Pincode
                                                    :: <a href="<?=base_url('support/pincode-list'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a>
                                                    </h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                       
                                                       <?php //echo form_open('support/addnewpincode'); ?>
                                                    
                                                           <form id="formData" method="post" enctype="multipart/form-data" action="">
                                                       
                                                          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="m_pincode_value"><span class="span">*</span>Pincode</label>
                                                                    <input type="text" maxlength="6" class="form-control" name="m_pincode_value" id="m_pincode_value" value="" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" required/>
                                                                </div>  
                                                                
                                                                
                                                                
                                                                 <div class="col-md-6">
                                                                    <label for="m_pincode_value"><span class="span">*</span>City</label>
                                                                   
                                                            
                                                            
                                                             <select class="form-control" name="m_pincode_city_id" id="m_pincode_city_id">
                    <option value="">Select City</option>
                    <?php foreach($cities as $city): ?>
                        <option value="<?php echo $city->m_city_id; ?>"><?php echo $city->m_city_name; ?></option>
                    <?php endforeach; ?>
                </select>
                                                            
                                                            
                                                            
                                                                </div> 
                                                                
                                                                
                                                            
                                                                
                                                                
                                                                
                                                            </div> 
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                   
                                                                    
                                                                     <button type="button" id="add_link" class="button-add btn add_pincode_number">Add Pincode</button>
                                                                    <a class="button-add btn btn-ifo" href="<?= base_url('support/pincode-list') ?>" role="button">Cancel</a>
                                                                </div>                                                                                                                      
                                                            </div> 															
                                                      <?php echo form_close(); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
<?php $this->load->view('Support/supportekyc'); ?>
</section>
</section>
