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
                                                    <h4>
                                                    <?php if ($update_flag == true) { ?>
                                                        Update SEO
                                                    <?php } else { ?>
                                                        Add SEO
                                                    <?php } ?>
                                                    :: <a href="<?=base_url('seo-list'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a>
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
                                                                    <label for="ws_title_id"><span class="span">*</span>Page Name</label>
                                                                    <input type="text" class="form-control" name="ws_title" id="ws_title_id" value="<?= !empty($seo_data["ws_title"]) ? $seo_data["ws_title"] : "" ?>" required/>
                                                                </div> 
                                                                <div class="col-md-6">
                                                                    <label for="ws_slug_id"><span class="span">*</span> Full URL</label>
                                                                    <input type="url" class="form-control" name="ws_slug" id="ws_slug_id" value="<?= !empty($seo_data["ws_slug"]) ? $seo_data["ws_slug"] : "" ?>" required/>
                                                                </div>                                                           
                                                                                                                                
                                                            </div> 
                                                            <p>&nbsp;</p>                                                       
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="ws_publish_date_id"> Publish Date</label>
                                                                    <input type="date" class="form-control" name="ws_publish_date" id="ws_publish_date_id" value="<?= !empty($seo_data["ws_publish_date"]) ? $seo_data["ws_publish_date"] : "" ?>"/>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="ws_publish_status_id"><span class="span">*</span> Publish Status</label>
                                                                    <select class="form-control" name="ws_publish_status" id="ws_publish_status_id">
                                                                        <option value="">Select</option>
                                                                        <option value="0"<?= (!empty($seo_data["ws_publish_status"]) && $seo_data["ws_publish_status"] == '0') ? 'selected="selected"' : "" ?>>Draft</option>
                                                                        <option value="1"<?= (!empty($seo_data["ws_publish_status"]) && $seo_data["ws_publish_status"] == '1') ? 'selected="selected"' : "" ?>>Publish</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <p>&nbsp;</p>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="ws_seo_title">Page Title</label>
                                                                    <input type="text" class="form-control" name="ws_seo_title" id="ws_seo_title_id" value="<?= !empty($seo_data["ws_seo_title"]) ? $seo_data["ws_seo_title"] : "" ?>"/>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="ws_seo_keyword_id">Page Keywords</label>
                                                                    <input type="text" class="form-control" name="ws_seo_keyword" id="ws_seo_keyword_id" value="<?= !empty($seo_data["ws_seo_keyword"]) ? $seo_data["ws_seo_keyword"] : "" ?>"/>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="ws_seo_description">Page Description</label>
                                                                    <textarea class="form-control" name="ws_seo_description" id="ws_seo_description_id"/><?= !empty($seo_data["ws_seo_description"]) ? $seo_data["ws_seo_description"] : "" ?></textarea>
                                                                </div>                                                                
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
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
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Support/support_js'); ?>
