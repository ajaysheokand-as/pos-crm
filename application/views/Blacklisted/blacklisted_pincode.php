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
                                    <div class="col-md-10 div-right-sidebar">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <span class="inner-page-tag">Blacklisted Pincode List</span>
                                                    <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('support/sysytem-blacklisted-pincode') ?>">
                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="filter_input" value="<?= !empty($_POST['filter_input']) ? $_POST['filter_input'] : '' ?>" placeholder="Enter search keywords."/>
                                                        </div>
                                                        
                                                        <button type="submit" class="btn btn-primary">Search</button> <button  type="button" onclick="location.href = '<?= base_url('support/sysytem-blacklisted-pincode') ?>'" class="btn btn-outline-light">Reset</button>
                                                        <a class="btn btn-primary" href="<?= base_url('support/add-blacklisted-pincode') ?>" role="button">Add Pincode</a>
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
                                                                            <th class="whitespace"><b>Pincode</b></th>
                                                                            <th class="whitespace"><b>Created Date</b></th>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $i = 1;
                                                                        if (!empty($blacklistedPincodeDetails)) {
                                                                            $pagination_links = $links;
                                                                            //$this->load->helper('text');
                                                                            foreach($blacklistedPincodeDetails as $val) {
                                                                                ?>
                                                                                <tr class="table-default" id="id_<?=$i?>">
                                                                                    <td class="whitespace"><?=$i?></td> 
                                                                                    <td class="whitespace"><?=$val["mbp_pincode"]?></td>
                                                                                    <td class="whitespace"><?= !empty($val["mbp_created_on"]) ? display_date_format($val["mbp_created_on"]) : "-" ?></td>
                                                                                    <td class="whitespace">
                                                                                        <a class="btn btn-primary btn-sm" title="Edit" href="<?= base_url('support/edit-blacklisted-pincode/' . $this->encrypt->encode($val["mbp_id"])) ?>"><i class="fa fa-pencil-square-o"></i></a>&nbsp;<a class="btn btn-primary btn-sm" title="Delete" href="javascript:void();" onclick="blacklistedPincodeDelete('<?=$this->encrypt->encode($val["mbp_id"]) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Support/support_js'); ?>
