<?php
$this->load->view('Layouts/header');
if ($update_flag == true && !empty($wp_id)) {
    $blogActionUrl = "blog/edit-blog/" . $this->encrypt->encode($wp_id);
} else {
    $blogActionUrl = "blog/add-blog";
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
                                                            Update Blog
                                                        <?php } else { ?>
                                                            Add Blog
                                                        <?php } ?>
                                                        :: <a href="<?= base_url('blog'); ?>"><b><i title="Back" class="fa fa-arrow-left" aria-hidden="true"></i></b></a>
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
                                                        <form id="formData" method="post" enctype="multipart/form-data" action="<?= base_url($blogActionUrl) ?>">
                                                            <input type="hidden" name="wb_id" value="<?= !empty($blog_data["wb_id"]) ? $blog_data["wb_id"] : "" ?>" />
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="wb_title_id"><span class="span">*</span> Title</label>
                                                                    <input type="text" class="form-control" name="wb_title" id="wb_title_id" value="<?= !empty($blog_data["wb_title"]) ? $blog_data["wb_title"] : "" ?>" required />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="wb_category_id"><span class="span">*</span> Category</label>
                                                                    <select class="form-control" name="wb_category_id" id="wb_category_id">
                                                                        <option value="">Select</option>
                                                                        <?php
                                                                        if (!empty($blog_category)) {
                                                                            foreach ($blog_category as $blog_categoryVal) {
                                                                        ?>
                                                                                <option <?= (!empty($blog_data["wb_category_id"]) && $blog_data["wb_category_id"] == $blog_categoryVal["wb_blog_category_id"]) ? 'selected="selected"' : "" ?> value="<?= $blog_categoryVal["wb_blog_category_id"] ?>"><?= $blog_categoryVal["wb_blog_category_name"] ?></option>
                                                                        <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="wb_publish_date_id"> Publish Date</label>
                                                                    <input type="date" class="form-control" name="wb_publish_date" id="wb_publish_date_id" value="<?= !empty($blog_data["wb_publish_date"]) ? $blog_data["wb_publish_date"] : "" ?>" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="wb_publish_status_id"><span class="span">*</span> Publish Status</label>
                                                                    <select class="form-control" name="wb_publish_status" id="wb_publish_status_id">
                                                                        <option value="">Select</option>
                                                                        <option value="0" <?= (isset($blog_data["wb_publish_status"]) && $blog_data["wb_publish_status"] == '0') ? 'selected="selected"' : "" ?>>Draft</option>
                                                                        <option value="1" <?= (isset($blog_data["wb_publish_status"]) && $blog_data["wb_publish_status"] == '1') ? 'selected="selected"' : "" ?>>Publish</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label for="wb_short_description_id"> Short Description</label>
                                                                    <textarea class="form-control" name="wb_short_description" id="wb_short_description_id" /><?= !empty($blog_data["wb_short_description"]) ? $blog_data["wb_short_description"] : "" ?></textarea>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <label for="wb_long_description_id"> Long Description</label>
                                                                    <textarea class="form-control" name="wb_long_description" id="wb_long_description_id" /><?= !empty($blog_data["wb_long_description"]) ? $blog_data["wb_long_description"] : "" ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="wb_thumb_image_url_id"> Thumb Image</label>
                                                                    <input type="file" class="form-control" name="wb_thumb_image_url" id="wb_thumb_image_url_id" />
                                                                    <?php if (isset($blog_data["wb_thumb_image_url"]) && $blog_data["wb_thumb_image_url"] != '') { ?>
                                                                        <input type="hidden" name="old_thumb_image" id="old_thumb_image_id" value="<?= $blog_data["wb_thumb_image_url"] ?>" />
                                                                        <img width="100" height="50" src="<?= WEBSITE_DOCUMENT_BASE_URL . $blog_data["wb_thumb_image_url"] ?>">
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="wb_banner_image_url_id"> Banner Image</label>
                                                                    <input type="file" class="form-control" name="wb_banner_image_url" id="wb_banner_image_url_id" />
                                                                    <?php if (isset($blog_data["wb_banner_image_url"]) && $blog_data["wb_banner_image_url"] != '') { ?>
                                                                        <input type="hidden" name="old_banner_image" id="old_banner_image_id" value="<?= $blog_data["wb_banner_image_url"] ?>" />
                                                                        <img width="100" height="50" src="<?= WEBSITE_DOCUMENT_BASE_URL . $blog_data["wb_banner_image_url"] ?>">
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="wb_seo_title">Meta Title</label>
                                                                    <input type="text" class="form-control" name="wb_seo_title" id="wb_seo_title_id" value="<?= !empty($blog_data["wb_seo_title"]) ? $blog_data["wb_seo_title"] : "" ?>" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="wb_seo_keyword_id">Meta Keywords</label>
                                                                    <input type="text" class="form-control" name="wb_seo_keyword" id="wb_seo_keyword_id" value="<?= !empty($blog_data["wb_seo_keyword"]) ? $blog_data["wb_seo_keyword"] : "" ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="wb_seo_description">Short Description</label>
                                                                    <textarea class="form-control" name="wb_seo_description" id="wb_seo_description_id" /><?= !empty($blog_data["wb_seo_description"]) ? $blog_data["wb_seo_description"] : "" ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="submit" class="button-add btn btn-ifo" id="adminSaveBlog">Save</button>
                                                                    <a class="button-add btn btn-ifo" href="<?= base_url('blog') ?>" role="button">Cancel</a>
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
<script src="https://cdn.tiny.cloud/1/7gpgmm03ljh9hkyzq8w4bbadzvqr7kai6b46zzoksn8p6q7e/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#wb_long_description_id',
        plugins: [
            // Core editing features
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            // Your account includes a free trial of TinyMCE premium features
            // Try the most popular premium features until Dec 17, 2024:
            'checklist', 'mediaembed', 'casechange', 'export', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'editimage', 'advtemplate', 'ai', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown',
            // Early access to document converters
            'importword', 'exportword', 'exportpdf'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [{
                value: 'First.Name',
                title: 'First Name'
            },
            {
                value: 'Email',
                title: 'Email'
            },
        ],
        ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    });
</script>
<?php $this->load->view('Support/support_js'); ?>
