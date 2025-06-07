<section class="parent_wrapper">
<?php $this->load->view('Layouts/header') ?>

<section class="right-side">
    
    <style>

    .parent_wrapper {
        width: 100%;
        height: 100vh;
        display: flex;
    }
    
    .parent_wrapper .right-side {
        width: calc(100% - 234px);
        position: absolute;
        left: 234px;
        top: 0;
        min-height: 100vh;
    }
    
    .parent_wrapper .right-side .logo_container {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        max-height: 90px;
        padding: 30px 20px;
    }
    
      .parent_wrapper .right-side .logo_container a img {
          margin-right: 20px;
          width: 100px;
      }

</style>
<?php
$umsActionUrl = 'ums/add-user';
if ($update_flag == true && !empty($enc_user_id)) {
    $umsActionUrl = "ums/edit-user/" . $enc_user_id;
}
//traceObject($user_data);
?>
<!-- section start -->
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
<section class="ums">


    <div class="container-fluid">

        <div class="taskPageSize taskPageSizeDashboard">

            <div class="row">

                <div class="col-md-12">

                    <div class="page-container list-menu-view">

                        <div class="page-content">

                            <div class="main-container">

                                <div class="container-fluid">

                                    <div class="drop-me">
                                        <?php $this->load->view('Layouts/leftsidebar') ?>
                                    </div>

                                    <div class="col-md-12 div-right-sidebar">

                                        <div class="login-formmea">

                                            <div class="box-widget widget-module">

                                                <div class="widget-head clearfix">

                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <?php if ($update_flag == true) { ?>
                                                        <h4>Update User</h4>
                                                    <?php } else { ?>
                                                        <h4>Add User</h4>
                                                    <?php } ?>

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


                                                        <form id="formData" method="post" enctype="multipart/form-data" action="<?= base_url($umsActionUrl) ?>">

                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

                                                            <div class="row">
                                                                <div class="col-md-6">

                                                                    <label><span class="span">*</span> Name</label>

                                                                    <input type="text" class="form-control" name="name" id="user_name" value="<?= !empty($user_data["name"]) ? $user_data["name"] : "" ?>" required/>

                                                                </div>
                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span>Dialer Campaign Id</label>

                                                                    <input type="text" class="form-control" name="user_dialer_campaign_id" id="user_dialer_campaign_id" value="<?= !empty($user_data["user_dialer_campaign_id"]) ? $user_data["user_dialer_campaign_id"] : "" ?>"/>

                                                                </div>
                                                            </div>

                                                            <div class="row">

                                                                <div class="col-md-6">

                                                                    <label><span class="span">*</span> Email</label>

                                                                    <input type="email" class="form-control" name="email" id="email" onchange="IsEmail(this)" value="<?= !empty($user_data["email"]) ? $user_data["email"] : "" ?>" required/>

                                                                </div>

                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span>Dialer Queue Id</label>

                                                                    <input type="text" class="form-control" name="user_dialer_queue_id" id="user_dialer_queue_id" value="<?= !empty($user_data["user_dialer_queue_id"]) ? $user_data["user_dialer_queue_id"] : "" ?>"/>

                                                                </div>
                                                            </div>

                                                            <div class="row">

                                                                <div class="col-md-6">

                                                                    <label><span class="span">*</span> Mobile</label>

                                                                    <input type="text" class="form-control" name="mobile" id="mobile" value="<?= !empty($user_data["mobile"]) ? $user_data["mobile"] : "" ?>" required/>

                                                                </div>

                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span> Dialer ID</label>

                                                                    <input type="text" class="form-control" name="user_dialer_id" id="user_dialer_id" value="<?= !empty($user_data["user_dialer_id"]) ? $user_data["user_dialer_id"] : "" ?>" />

                                                                </div>
                                                            </div>


                                                            <div class="row">


                                                                <div class="col-md-6">

                                                                    <label><span class="span">*</span> User Status</label>
                                                                    <select class="form-control" name="user_status_id" id="user_status_id">
                                                                        <option value="">Select</option>
                                                                        <?php
                                                                        if (!empty($master_user_status)) {
                                                                            foreach ($master_user_status as $status_key => $status_value) {
                                                                                ?>
                                                                                <option <?= (!empty($user_data["user_status_id"]) && $status_key == $user_data["user_status_id"]) ? 'selected="selected"' : "" ?> value="<?= $status_key ?>"><?= $status_value ?></option>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>

                                                                    </select>


                                                                </div>
                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span>Dialer List Id</label>

                                                                    <input type="text" class="form-control" name="user_dialer_call_list_id" id="user_dialer_call_list_id" value="<?= !empty($user_data["user_dialer_call_list_id"]) ? $user_data["user_dialer_call_list_id"] : "" ?>"/>

                                                                </div>
                                                            </div>
                                                            <div class="row">

                                                                <div class="col-md-12">

                                                                    <button type="submit" class="button-add btn btn-ifo" id="adminSaveUser">Save</button>

                                                                    <a class="button-add btn btn-ifo" href="<?= base_url('ums') ?>" role="button">Cancel</a>

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
</section>
</section>
