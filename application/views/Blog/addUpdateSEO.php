<?php 
$this->load->view('Layouts/header');
if($update_flag == true && !empty($ws_id)) {
    $seoActionUrl = "seo/edit-seo/".$this->encrypt->encode($ws_id);
}else{
    $seoActionUrl = "seo/add-seo";
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
                                                    <h4> <a href="<?=base_url('seo-list'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a>  &nbsp;::
                                                    <?php if ($update_flag == true) { ?>
                                                        Update SEO
                                                    <?php } else { ?>
                                                        Add SEO
                                                    <?php } ?>
                                                    
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
                                                        <form id="formData" method="post" enctype="multipart/form-data" action="<?=base_url($seoActionUrl)?>">
                                                            <input type="hidden" name="ws_id" value="<?= !empty($seo_data["ws_id"]) ? $seo_data["ws_id"] : "" ?>" />
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="ws_title_id"><span class="span">*</span> Title</label>
                                                                    <input type="text" class="form-control" name="ws_title" id="ws_title_id" value="<?= !empty($seo_data["ws_title"]) ? $seo_data["ws_title"] : "" ?>" required/>
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="ws_slug_id"><span class="span">*</span> Full URL</label>
                                                                    <input type="url" class="form-control" name="ws_slug" id="ws_slug_id" value="<?= !empty($seo_data["full_url"]) ? $seo_data["full_url"] : "" ?>" required/>
                                                                </div>                                                           
                                                                                                                                
                                                            </div> 
                                                            <p>&nbsp;</p>                                                       
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                    <label for="ws_publish_status_id"><span class="span">*</span> Blog Status</label>
                                                                    <select class="form-control" name="ws_publish_status" id="ws_publish_status_id" required onchange="showMandotry(this.value);">
                                                                        <option value="">Select</option>
                                                                        <option value="0"<?php if(isset($seo_data["ws_publish_status"]) && $seo_data["ws_publish_status"] == '0'){echo 'selected';} ?>>Draft</option>
                                                                        <option value="1"<?php if(isset($seo_data["ws_publish_status"]) && $seo_data["ws_publish_status"] == '1'){echo 'selected';} ?>>Publish</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6" style="margin-top:10px">
                                                                    <label for="ws_publish_date_id"> Publish Date</label>
                                                                    
                                                                    <input type="text" class="form-control salaryDate1" name="ws_publish_date" id="ws_publish_date_id" value="<?= !empty($seo_data["ws_publish_date"]) ? $seo_data["ws_publish_date"] : "" ?>" placeholder="Please enter date." required <?php if(isset($seo_data["ws_publish_status"]) && $seo_data["ws_publish_status"] == '0'){echo 'disabled'.' '.'readonly';} ?>/>
                                                                </div>
                                                               
                                                            </div>
                                                            <p>&nbsp;</p>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="ws_seo_title">Meta Title</label>
                                                                    <input type="text" class="form-control" name="ws_seo_title" id="ws_seo_title_id" value="<?= !empty($seo_data["ws_seo_title"]) ? $seo_data["ws_seo_title"] : "" ?>"/>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="ws_seo_keyword_id">Meta Keywords</label>
                                                                    <input type="text" class="form-control" name="ws_seo_keyword" id="ws_seo_keyword_id" value="<?= !empty($seo_data["ws_seo_keyword"]) ? $seo_data["ws_seo_keyword"] : "" ?>"/>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row" style="margin:0px;">
                                                                <div class="col-md-12">
                                                                    <br>
                                                                    <label for="ws_seo_description">Meta Description</label>
                                                                    <textarea class="form-control" name="ws_seo_description" id="ws_seo_description_id"/><?= !empty($seo_data["ws_seo_description"]) ? $seo_data["ws_seo_description"] : "" ?></textarea>
                                                                </div>                                                                
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12" style="margin:15px;">
                                                                  
                                                                    <button type="submit" class="button-add btn btn-ifo" id="adminSaveseo">Save</button>
                                                                    <a class="button-add btn btn-ifo" href="<?= base_url('seo-list') ?>" role="button">Cancel</a>
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
<?php $this->load->view('Layouts/footer'); ?>
<script type="text/javascript">
$(function () {
    $('.salaryDate1').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startDate: '-1m',
        endDate: '+1d'
    });
});
function showMandotry(str) {
    if (str == '1') {
        //$('#ws_publish_status_id').attr('required', 'true');
        $('#ws_publish_date_id').removeAttr('disabled');
        $('#ws_publish_date_id').removeAttr('readonly');
        //$('.showMaritalStatus').css('display', 'block');
    } else {
        //$('#ws_publish_status_id').removeAttr('required');
        $('#ws_publish_date_id').attr('disabled', true);
	$('#ws_publish_date_id').attr('readonly', true);
        //$('#customer_spouse_occupation_id').prop('disabled', true);
        //$('.showMaritalStatus').css('display', 'none');
        $('#ws_publish_date_id').val('');
    }
}
</script>
<?php $this->load->view('Support/support_js'); ?>
