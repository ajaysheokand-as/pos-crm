<?php 
$this->load->view('Layouts/header');
if($update_flag == true && !empty($mbp_id)) {
    $blacklistedPincodeActionUrl = "support/edit-blacklisted-pincode/".$this->encrypt->encode($mbp_id);
}else{
    $blacklistedPincodeActionUrl = "support/add-blacklisted-pincode";
}
?>
<section class="ums">

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

                                    <div class="col-md-10 div-right-sidebar">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>
                                                    <?php if ($update_flag == true) { ?>
                                                        Update Blacklisted Pincode
                                                    <?php } else { ?>
                                                        Add Blacklisted Pincode
                                                    <?php } ?>
                                                    :: <a href="<?=base_url('support/sysytem-blacklisted-pincode'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a>
                                                    </h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <div class="row">
                                                            <?php if (!empty($this->session->flashdata('success_msg'))) { ?>
                                                                <div class="alert alert-success alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('success_msg'); ?>
                                                                </div>
                                                            <?php } else if (!empty($this->session->flashdata('errors_msg'))) { ?>
                                                                <div class="alert alert-danger alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('errors_msg'); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <form id="formData" method="post" enctype="multipart/form-data" action="<?=base_url($blacklistedPincodeActionUrl)?>">
                                                            <input type="hidden" name="mbp_id" value="<?= !empty($blacklisted_pincode_data["mbp_id"]) ? $blacklisted_pincode_data["mbp_id"] : "" ?>" />
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="mbp_pincode"><span class="span">*</span>Pincode</label>
                                                                    <input type="text" maxlength="6" class="form-control" name="mbp_pincode" id="mbp_pincode" value="<?= !empty($blacklisted_pincode_data["mbp_pincode"]) ? $blacklisted_pincode_data["mbp_pincode"] : $_POST["mbp_pincode"] ?>" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;" required/>
                                                                </div>                                                                                                                 
                                                            </div> 
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="submit" class="button-add btn btn-ifo" id="adminSaveseo">Save</button>
                                                                    <a class="button-add btn btn-ifo" href="<?= base_url('support/sysytem-blacklisted-pincode') ?>" role="button">Cancel</a>
                                                                </div>                                                                                                                      
                                                            </div> 															
                                                        </form>
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
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Support/support_js'); ?>
