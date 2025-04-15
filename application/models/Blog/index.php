<?php 
$this->load->view('Layouts/header');
$uri = $this->uri->segment(1);
$pagination_links = "";
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
                                    <div class="col-md-9">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <span class="inner-page-tag">Blogs List</span>
                                                    <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('blog') ?>">
                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="filter_input" value="<?= !empty($_POST['filter_input']) ? $_POST['filter_input'] : '' ?>" placeholder="Enter search keywords."/>
                                                        </div>
                                                        <div class="form-group">
                                                            <select class="form-control" id="category_id" name="category_id">
                                                                <option value="">Select</option>
                                                                <?php foreach ($blog_category as $category_id => $category_name) { ?>
                                                                    <option <?= ((!empty($_POST['category_id']) && $_POST['category_id'] == $category_name['wb_blog_category_id']) ? 'selected="selected"' : '') ?>  value="<?= $category_name['wb_blog_category_id'] ?>"><?= $category_name['wb_blog_category_name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Search</button> <button  type="button" onclick="location.href = '<?= base_url('blog-list') ?>'" class="btn btn-outline-light">Reset</button>
                                                        <a class="btn btn-primary" href="<?= base_url('blog/add-blog') ?>" role="button">ADD BLOG</a>
                                                    </form>

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
                                                            <div class="scroll_on_x_axis">
                                                                <table class="table dt-table1 table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace"><b>#</b></th>
                                                                            <th class="whitespace"><b>Title</b></th>
                                                                            <th class="whitespace"><b>Image</b></th>
                                                                            <th class="whitespace"><b>Short Description</b></th>
                                                                            <th class="whitespace"><b>Created Date</b></th>
                                                                            <th class="whitespace"><b>Display Status</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $i = 1;
                                                                        if (!empty($blogDetails)) {
                                                                            $pagination_links = $links;
                                                                            $this->load->helper('text');
                                                                            foreach ($blogDetails as $blogData) {
                                                                                ?>
                                                                                <tr class="table-default" id="id_<?=$i?>">
                                                                                    <td class="whitespace"><?=$i?></td> 
                                                                                    <td class="whitespace"><?=$blogData["wb_title"]?></td>
                                                                                    <td class="whitespace"><img width="100" height="50" src="<?=WEBSITE_DOCUMENT_BASE_URL.$blogData["wb_banner_image_url"]?>"></td>
                                                                                    <td><?=word_limiter($blogData["wb_short_description"],10)?></td>
                                                                                    <td class="whitespace"><?= !empty($blogData["wb_created_on"]) ? display_date_format($blogData["wb_created_on"]) : "-" ?></td>
                                                                                    <td class="whitespace"><?= !empty($blogData["wb_publish_status"]) && $blogData["wb_publish_status"]=='1'?'Publish':'Draft'?></td> 
                                                                                    <td class="whitespace">
                                                                                        <a  class="btn btn-primary btn-sm" title="Edit" href="<?= base_url('blog/edit-blog/' . $this->encrypt->encode($blogData["wb_id"])) ?>"><i class="fa fa-pencil-square-o"></i></a>&nbsp;<a class="btn btn-primary btn-sm" title="Delete" href="javascript:void();" onclick="blogDelete('<?=$this->encrypt->encode($blogData["wb_id"]) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php
                                                                                $i++;
                                                                            }
                                                                        } else { ?>
                                                                                <tr class="table-default"><td colspan="7" style="color:red;">Data not available!</td></tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>

                                                            </div>
                                                            <?= $pagination_links; ?>
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
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Support/support_js'); ?>
