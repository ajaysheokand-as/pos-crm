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
          background: #000;
          border-radius: 10px;
      }

</style>
<?php
$umsActionUrl = 'ums/add-user-role';

if (!empty($enc_user_id)) {
    $umsActionUrl = "ums/add-user-role/" . $enc_user_id;
}

if ($update_flag == true && !empty($enc_user_role_id)) {
    $umsActionUrl = "ums/edit-role/" . $enc_user_role_id;
}

//traceObject($user_data);
?>
<!-- section start -->

<section class="ums">
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
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
                                                    <?php if ($udpate_flag == true) { ?>
                                                        <h4>Update Role</h4>
                                                    <?php } else { ?>
                                                        <h4>Add Role</h4>
                                                    <?php } ?>
                                                    &nbsp;<a href="<?= base_url('ums/view-user/' . $enc_user_id) ?>" class="button-add btn btn-ifo">BACK</a>

                                                </div>

                                                <div class="widget-container">

                                                    <div class=" widget-block">

                                                        <div class="row">
                                                            <?php if (!empty($this->session->flashdata('success_msg'))) { ?>
                                                                <div class="alert alert-success">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('success_msg'); ?>
                                                                </div>
                                                            <?php } else if (!empty($this->session->flashdata('errors_msg'))) { ?>
                                                                <div class="alert alert-danger">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('errors_msg'); ?>
                                                                </div>
                                                            <?php } if (!in_array($user_data["user_status_id"], [1])) { ?>
                                                                <div class="alert alert-danger">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= 'Inactive User.'; ?>
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
                                                        <?php if (in_array($user_data["user_status_id"], [1])) { ?>
                                                            <form id="formData" method="post" enctype="multipart/form-data" action="<?= base_url($umsActionUrl) ?>">
                                                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                                <br/>

                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <label><span class="span">*</span> Role Type</label>
                                                                        <select class="form-control" name="user_role_type_id" id="user_role_type_id" <?php if (!empty($user_data['user_role_type_id'])) { ?> disabled <?php } ?>>
                                                                            <option value="">Select</option>
                                                                            <?php
                                                                            if (!empty($master_role_type)) {
                                                                                foreach ($master_role_type as $role_type_id => $role_type_value) {
                                                                                    ?>
                                                                                    <option  <?= (!empty($user_data["user_role_type_id"]) && $role_type_id == $user_data["user_role_type_id"]) ? 'selected="selected"' : "" ?> value="<?= $role_type_id ?>"><?= $role_type_value ?></option>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3" id="user_role_branch_id_row">
                                                                        <label><span style="color : red">*</span>&nbsp;Branch</label>
                                                                        <select class="form-control" style="height: 100px" name="user_role_branch_id[]" id="user_role_branch_id" multiple>
                                                                            <option value="">Select</option>
                                                                            <?php
                                                                            if (!empty($master_branch)) {

                                                                                foreach ($master_branch as $branch_id => $branch_name) {
                                                                                    ?>
                                                                                    <option value="<?= $branch_id ?>" <?php
                                                                                    ?> ><?= $branch_name ?>  </option>
                                                                                            <?php
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3"  id="user_role_agency_id_row">
                                                                        <label><span class="span">*</span> Agency  </label>
                                                                        <select class="form-control"  name="user_role_agency_id" id="user_role_agency_id">
                                                                            <option value="">Select</option>
                                                                            <?php
                                                                            if (!empty($master_collection_agency)) {
                                                                                foreach ($master_collection_agency as $agency_id => $agency_name) {
                                                                                    ?>
                                                                                    <option  value="<?= $agency_id ?>">
                                                                                        <?= $agency_name ?>
                                                                                    </option>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3"  id="user_role_scm_id_row">
                                                                        <label><span class="span">*</span> SCM</label>
                                                                        <select class="form-control"  name="user_role_scm_id" id="user_role_scm_id">
                                                                            <option value="">Select</option>
                                                                            <?php
                                                                            if (!empty($get_scm)) {
                                                                                foreach ($get_scm as $scm_id => $scm_name) {
                                                                                    ?>
                                                                                    <option  value="<?= $scm_id ?>">
                                                                                        <?= $scm_name ?>
                                                                                    </option>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3"  id="user_role_state_id_row">
                                                                        <label><span class="span">*</span> State</label>
                                                                        <select class="form-control" style="height: 100px;" name="user_role_state_id[]" id="user_role_state_id" multiple>
                                                                            <option value="">Select</option>
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
                                                                    <div class="col-md-3"  id="user_role_supervisor_role_id_row">
                                                                        <label><span class="span">*</span> Credit Head  </label>
                                                                        <select class="form-control"  name="user_role_supervisor_role_id" id="user_role_supervisor_role_id">
                                                                            <option value="">Select</option>
                                                                            <?php
                                                                            if (!empty($getMappedCreditHead)) {
                                                                                foreach ($getMappedCreditHead as $value) {
                                                                                    ?>
                                                                                    <option  value="<?= $value['user_role_id'] ?>">
                                                                                        <?= $value['name'] ?>
                                                                                    </option>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <button type="submit" class="button-add btn btn-ifo" id="adminSaveUser">Save</button>
                                                                        <a class="button-add btn btn-ifo" href="<?= base_url('ums') ?>" role="button">Cancel</a>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        <?php } ?>

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
</section/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        $('#user_export_id, #user_role_state_id, #user_role_branch_id, #user_role_supervisor_role_id_row,#user_role_supervisor_role_cid_row').multiselect({
            nonSelectedText: 'Select',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
        });
    });
</script>


<script>
    $(document).ready(function () {
        $("#user_role_state_id_row, #user_role_branch_id_row, #user_role_scm_id_row, #user_role_supervisor_role_id_row, #user_role_supervisor_role_cid_row, #user_role_agency_id_row").hide();

        $('#user_role_type_id').on('change', function () {
            var user_role_type_id = $(this).val();

            if (user_role_type_id == 2) {
                $("#user_role_supervisor_role_id_row").show();
            } else {
                $("#user_role_supervisor_role_id_row").hide();
            }

            if (user_role_type_id == 3) {
                $("#user_role_supervisor_role_cid_row").show();
            } else {
                $("#user_role_supervisor_role_cid_row").hide();
            }

            if (user_role_type_id == 8) {
                $("#user_role_state_id_row").show();
            } else {
                $("#user_role_state_id_row").hide();
            }

            if (user_role_type_id == 7) {
                $("#user_role_branch_id_row").show();
            } else {
                $("#user_role_branch_id_row").hide();
            }

            if (user_role_type_id == 19) {
                $("#user_role_branch_id_row").show();
                $("#user_role_agency_id_row").show();
                $("#user_role_agency_id_row").prop("required", true);
            } else {
                $("#user_role_branch_id_row").hide();
                $("#user_role_agency_id_row").hide();
            }

            if (user_role_type_id == 13) {
                $("#user_role_scm_id_row").show();
            } else {
                $("#user_role_scm_id_row").hide();
            }

        });
    });
</script>
