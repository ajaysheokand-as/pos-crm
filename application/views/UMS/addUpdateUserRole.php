<?php $this->load->view('Layouts/header') ?>

<?php
$umsActionUrl = 'ums/add-role';
if (!empty($enc_user_id)) {
    $umsActionUrl = "ums/add-role/" . $enc_user_id;
}

if ($update_flag == true && !empty($enc_role_id)) {
    $umsActionUrl = "ums/edit-role/" . $enc_role_id;
}
//traceObject($user_data);
?>
<!-- section start -->

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
                                                    <?php if ($udpate_flag == true) { ?>
                                                        <h4>Update Role</h4>
                                                    <?php } else { ?>
                                                        <h4>Add Role</h4>
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

                                                        <div class="row">
                                                            <div role="tabpanel" class="tab-pane fade in active" id="userSaction">
                                                                <div id="userSaction">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover table-striped table-bordered">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th>Login Username</th><td><?= display_data($user_data["user_name"]); ?></td>
                                                                                    <th>Name</th><td><?= display_data($user_data["name"]); ?></td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <th>Dialer ID</th><td><?= display_data($user_data["user_dialer_id"]); ?></td>
                                                                                    <th>Email</th><td><a href="<?= !empty($user_data["email"]) ? 'mailto:' . $user_data["email"] : "javascript:void(0)" ?>"><i class="fa fa-envelope"></i></a>&nbsp;<?= display_data($user_data["email"]) ?></td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <th>Status</th><td><?= display_data($master_user_status[$user_data["user_status_id"]]); ?></td>
                                                                                    <th>Mobile</th><td><a href="<?= !empty($user_data["mobile"]) ? 'tel:' . $user_data["mobile"] : "" ?>"><i class="fa fa-phone"></i></a>&nbsp;<?= display_data($user_data["mobile"]); ?></td>
                                                                                </tr>

                                                                            </tbody></table>
                                                                    </div>

                                                                </div>



                                                            </div>
                                                        </div>
                                                        <form id="formData" method="post" enctype="multipart/form-data" action="<?= base_url($umsActionUrl) ?>">

                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />



                                                            <div class="row">

                                                                <div class="col-md-6">

                                                                    <label><span class="span">*</span> Role Type</label>

                                                                    <select class="form-control" name="user_role_type_id" id="user_role_type_id">
                                                                        <option value="">Select</option>
                                                                        <?php
                                                                        if (!empty($master_role_type)) {
                                                                            foreach ($master_role_type as $role_type_id => $role_type_value) {
                                                                                ?>
                                                                                <option <?= (!empty($user_data["user_role_type_id"]) && $role_type_id == $user_data["user_role_type_id"]) ? 'selected="selected"' : "" ?> value="<?= $role_type_id ?>"><?= $role_type_value ?></option>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>

                                                                    </select>

                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6" id="div_state_id">

                                                                    <label><span class="span">*</span> State</label>

                                                                    <select class="form-control" style="height: 100px" name="user_role_state_id" id="user_role_state_id" multiple>
                                                                        <!--<option value="">Select</option>-->
                                                                        <?php
                                                                        if (!empty($master_state)) {
                                                                            foreach ($master_state as $state_id => $state_name) {
                                                                                ?>
                                                                                <option <?= (!empty($user_data["user_role_type_id"]) && $state_id == $user_data["user_role_type_id"]) ? 'selected="selected"' : "" ?> value="<?= $state_id ?>"><?= $state_name ?></option>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>

                                                                    </select>

                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6" id="div_city_id">

                                                                    <label><span class="span">*</span> City</label>

                                                                    <select class="form-control" style="height: 100px" name="user_role_city_id" id="user_role_city_id" multiple>

                                                                        <?php
                                                                        if (!empty($master_city)) {
                                                                            foreach ($master_city as $city_id => $city_name) {
                                                                                ?>
                                                                                <option <?= (!empty($user_data["user_role_type_id"]) && $city_id == $user_data["user_role_type_id"]) ? 'selected="selected"' : "" ?> value="<?= $city_id ?>"><?= $city_name ?></option>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>

                                                                    </select>

                                                                </div>

                                                            </div>


                                                            <div class="row">

                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span> Dialer ID</label>

                                                                    <input type="text" class="form-control" name="user_dialer_id" id="user_dialer_id" value="<?= !empty($user_data["user_dialer_id"]) ? $user_data["user_dialer_id"] : "" ?>" />

                                                                </div>
                                                                <div class="col-md-6">

                                                                    <label><span class="span">&nbsp;</span> User Status</label>
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
