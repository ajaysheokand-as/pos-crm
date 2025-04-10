<section class="parent_wrapper">
    <?php
    $this->load->view('Layouts/header');
    ?>
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
                width: 270px;
            }
        </style>
        <?php
        if (!empty($enc_user_role_id) && $user_data['user_role_type_id'] == 13) {
            $umsActionUrl = "ums/edit-role-scm/" . $enc_user_role_id;
        } else {
            $umsActionUrl = "ums/edit-role/" . $enc_user_role_id;
        }
        ?>



        <?php $getMappedRoleLevel = $user_data['user_role_level']; ?>


        <head>

            <link rel="stylesheet" href="<?= base_url('public/css/selectbox_min.css') ?>" type="text/css">
        </head>
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
                                                            &nbsp;<a href="<?= base_url('ums') ?>" class="button-add btn btn-ifo">BACK</a>

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
                                                                    <?php }
                                                                    if (!in_array($user_data["user_status_id"], [1])) { ?>
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
                                                                                            <th>Login Username</th>
                                                                                            <td><?= display_data($user_data["user_name"]); ?></td>
                                                                                            <th>Name</th>
                                                                                            <td><?= display_data($user_data["name"]); ?></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <th>Dialer ID</th>
                                                                                            <td><?= display_data($user_data["user_dialer_id"]); ?></td>
                                                                                            <th>Email</th>
                                                                                            <td><a href="<?= !empty($user_data["email"]) ? 'mailto:' . $user_data["email"] : "javascript:void(0)" ?>"><i class="fa fa-envelope"></i></a>&nbsp;<?= display_data($user_data["email"]) ?></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <th>Status</th>
                                                                                            <td><?= display_data($master_user_status[$user_data["user_status_id"]]); ?></td>
                                                                                            <th>Mobile</th>
                                                                                            <td><a href="<?= !empty($user_data["mobile"]) ? 'tel:' . $user_data["mobile"] : "" ?>"><i class="fa fa-phone"></i></a>&nbsp;<?= display_data($user_data["mobile"]); ?></td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <?php if (in_array($user_data["user_status_id"], [1])) { ?>
                                                                    <form id="formData" action="<?= base_url($umsActionUrl) ?>" method="post" enctype="multipart/form-data">
                                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                                        <br />

                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <label><span class="span">*</span> Role Types</label>
                                                                                <select class="form-control" name="user_role_type_id" id="user_role_type_id" <?php if (!empty($user_data['user_role_type_id'])) { ?> disabled <?php } ?>>
                                                                                    <!--<option value="">Select</option>-->
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

                                                                            <?php if (!empty($user_data['user_role_type_id'])) { ?>
                                                                                <div class="col-md-6">
                                                                                    <label><span class="span">&nbsp;</span> User Status</label>
                                                                                    <select class="form-control" name="user_role_active" id="user_role_active">
                                                                                        <option value="1" <?php if ($user_data["user_role_active"] == 1) echo "selected"; ?>>Active</option>
                                                                                        <option value="0" <?php if ($user_data["user_role_active"] == 0) echo "selected"; ?>>Inactive</option>
                                                                                    </select>
                                                                                </div>
                                                                            <?php } ?>
                                                                        </div>
                                                                        <br />
                                                                        <div class="row">
                                                                            <?php
                                                                            $role_id = $user_data['user_role_type_id'];

                                                                            if (!empty($user_data['user_role_type_id']) && !in_array($role_id, array(2, 3, 13, 15, 17, 18))) {
                                                                            ?>
                                                                                <div class="col-md-3" id=""> <!<!-- div_state_id -->
                                                                                        <label>Export</label>
                                                                                        <select class="select" style="height: 100px" name="user_export_id[]" id="user_export_id" multiple data-mdb-filter="true" placeholder="Select">
                                                                                            <!--<option value="">Select</option>-->
                                                                                            <?php
                                                                                            if (!empty($export_master_list)) {
                                                                                                $export_permission_export_id = [];
                                                                                                foreach ($export_user_permission_list['export_list'] as $export) {
                                                                                                    $export_permission_export_id[] = $export["export_permission_export_id"];
                                                                                                }

                                                                                                foreach ($export_master_list as $m_export_id => $m_export_name) {
                                                                                            ?>
                                                                                                    <option value="<?= $m_export_id ?>" <?php
                                                                                                                                        if (in_array($m_export_id, $export_permission_export_id)) {
                                                                                                                                            echo "selected";
                                                                                                                                        }
                                                                                                                                        ?>>
                                                                                                        <?= $m_export_name ?> </option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                </div>
                                                                                <div class="col-md-3" id="">
                                                                                    <label>Report</label>
                                                                                    <select class="select" style="height: 100px" name="user_mis_id[]" id="user_mis_id" multiple data-mdb-filter="true" placeholder="Select">
                                                                                        <!--<option value="">Select</option>-->
                                                                                        <?php
                                                                                        if (!empty($mis_master_list)) {
                                                                                            $mis_permission_mis_id = [];
                                                                                            foreach ($mis_user_permission_list['mis_list'] as $mis) {
                                                                                                $mis_permission_mis_id[] = $mis["mis_permission_mis_id"];
                                                                                            }

                                                                                            foreach ($mis_master_list as $m_mis_id => $m_mis_name) {
                                                                                        ?>
                                                                                                <option value="<?= $m_mis_id ?>" <?php
                                                                                                                                    if (in_array($m_mis_id, $mis_permission_mis_id)) {
                                                                                                                                        echo "selected";
                                                                                                                                    }
                                                                                                                                    ?>>
                                                                                                    <?= $m_mis_name ?> </option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && $user_data['user_role_type_id'] == 8) { ?>

                                                                                <div class="col-md-3" id="div_state_id">
                                                                                    <label><span style="color : red">*</span>&nbsp;State</label>
                                                                                    <select class="form-control" required style="height: 100px" name="user_role_state_id[]" id="user_role_state_id" multiple>
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_state)) {
                                                                                            $user_rl_location_id = [];
                                                                                            foreach ($state_list['state_list'] as $state) {
                                                                                                $user_rl_location_id[] = $state["user_rl_location_id"];
                                                                                            }

                                                                                            foreach ($master_state as $state_id => $state_name) {
                                                                                        ?>
                                                                                                <option value="<?= $state_id ?>" <?php
                                                                                                                                    if (in_array($state_id, $user_rl_location_id)) {
                                                                                                                                        echo "selected";
                                                                                                                                    }
                                                                                                                                    ?>><?= $state_name ?> </option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && in_array($user_data['user_role_type_id'], [7])) { //2, 3,
                                                                            ?>
                                                                                <div class="col-md-3">
                                                                                    <label><span style="color : red">*</span>&nbsp;Branch</label>
                                                                                    <select class="form-control" style="height: 100px" name="user_role_branch_id[]" id="user_role_branch_id" multiple>
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_branch)) {
                                                                                            $user_rl_location_branch_id = [];
                                                                                            foreach ($branch_list['branch_list'] as $branch) {
                                                                                                $user_rl_location_branch_id[] = $branch["user_rl_location_id"];
                                                                                            }

                                                                                            foreach ($master_branch as $branch_id => $branch_name) {
                                                                                        ?>
                                                                                                <option value="<?= $branch_id ?>" <?php
                                                                                                                                    if (in_array($branch_id, $user_rl_location_branch_id)) {
                                                                                                                                        echo "selected";
                                                                                                                                    }
                                                                                                                                    ?>><?= $branch_name ?> </option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>

                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && $user_data['user_role_type_id'] == 3) { ?>
                                                                                <div class="row" id="user_role_supervisor_role_id">
                                                                                    <div class="col-md-6" id="div_role_level_id">
                                                                                        <label><span class="span">*</span>User Role Level</label>
                                                                                        <select class="form-control" name="user_role_level[]" id="user_role_level" multiple data-mdb-filter="true" required>
                                                                                            <option value="" <?= $getMappedRoleLevel == 0 ? "selected" : "" ?>>select</option>
                                                                                            <option value="L1" <?= $getMappedRoleLevel == "L1" ? "selected" : "" ?>>L1</option>
                                                                                            <option value="L2" <?= $getMappedRoleLevel == "L2" ? "selected" : "" ?>>L2</option>
                                                                                            <option value="L3" <?= $getMappedRoleLevel == "L3" ? "selected" : "" ?>>L3</option>
                                                                                            <option value="L4" <?= $getMappedRoleLevel == "L4" ? "selected" : "" ?>>L4</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="col-md-6" id="div_screener">

                                                                                        <label><span class="span">*</span>Reporting</label>
                                                                                        <select class="form-control" name="reporting_id" id="reporting_id" required>
                                                                                            <option value="">Select</option>
                                                                                            <?php
                                                                                            if (!empty($getCreditHead)) {
                                                                                                foreach ($getCreditHead as $value) {
                                                                                            ?>
                                                                                                    <option value="<?= $value['user_role_id'] ?>" <?php
                                                                                                                                                    if ($getMappedCreditHead['user_role_id'] == $value['user_role_id']) {
                                                                                                                                                        echo "selected";
                                                                                                                                                    }
                                                                                                                                                    ?>><?= $value['name'] ?></option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>
                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && $user_data['user_role_type_id'] == 2) { ?>

                                                                                <div class="row" id="user_role_supervisor_role_id">
                                                                                    <div class="col-md-6" id="div_role_level_id">
                                                                                        <label><span class="span">*</span>User Role Level</label>
                                                                                        <select class="form-control" name="user_role_level" id="user_role_level" required>
                                                                                            <option value="" <?= $getMappedRoleLevel == 0 ? "selected" : "" ?>>select</option>
                                                                                            <option value="L1" <?= $getMappedRoleLevel == "L1" ? "selected" : "" ?>>L1</option>
                                                                                            <option value="L2" <?= $getMappedRoleLevel == "L2" ? "selected" : "" ?>>L2</option>
                                                                                            <option value="L3" <?= $getMappedRoleLevel == "L3" ? "selected" : "" ?>>L3</option>
                                                                                            <option value="L4" <?= $getMappedRoleLevel == "L4" ? "selected" : "" ?>>L4</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="col-md-6" id="div_screener_id">

                                                                                        <label><span class="span">*</span>Reporting</label>
                                                                                        <select class="form-control" name="reporting_id" id="reporting_id" required>
                                                                                            <option value="">Select</option>
                                                                                            <?php
                                                                                            if (!empty($getCreditHead)) {
                                                                                                foreach ($getCreditHead as $value) {
                                                                                            ?>
                                                                                                    <option value="<?= $value['user_role_id'] ?>" <?php
                                                                                                                                                    if ($getMappedCreditHead['user_role_id'] == $value['user_role_id']) {
                                                                                                                                                        echo "selected";
                                                                                                                                                    }
                                                                                                                                                    ?>><?= $value['name'] ?></option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>

                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && $user_data['user_role_type_id'] == 4) { ?>

                                                                                <div class="row" id="user_role_supervisor_role_id">
                                                                                    <div class="col-md-6" id="div_role_level_id">
                                                                                        <label><span class="span">*</span>User Role Level</label>

                                                                                        <select class="form-control" name="user_role_level" id="user_role_level" required>
                                                                                            <option value="" <?= $getMappedRoleLevel == 0 ? "selected" : "" ?>>select</option>
                                                                                            <option value="L1" <?= $getMappedRoleLevel == "L1" ? "selected" : "" ?>>L1</option>
                                                                                            <option value="L2" <?= $getMappedRoleLevel == "L2" ? "selected" : "" ?>>L2</option>
                                                                                            <option value="L3" <?= $getMappedRoleLevel == "L3" ? "selected" : "" ?>>L3</option>
                                                                                            <option value="L4" <?= $getMappedRoleLevel == "L4" ? "selected" : "" ?>>L4</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="col-md-6" id="div_credit_head_id">

                                                                                        <label>Reporting</label>
                                                                                        <select class="form-control" name="reporting_id" id="reporting_id">
                                                                                            <option value="0">Select</option>
                                                                                            <?php
                                                                                            if (!empty($getCreditHead)) {
                                                                                                foreach ($getCreditHead as $value) {
                                                                                            ?>
                                                                                                    <option value="<?= $value['user_role_id'] ?>" <?php
                                                                                                                                                    if ($getMappedCreditHead['user_role_id'] == $value['user_role_id']) {
                                                                                                                                                        echo "selected";
                                                                                                                                                    }
                                                                                                                                                    ?>><?= $value['name'] ?></option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>

                                                                                    </div>
                                                                                </div>

                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && in_array($user_data['user_role_type_id'], [13])) { ?>
                                                                                <div class="row" id="user_role_scm_id_row">
                                                                                    <div class="col-md-6" id="div_scm_id">
                                                                                        <label><span class="span">*</span> SCM</label>
                                                                                        <select class="form-control" name="user_role_scm_id" id="user_role_scm_id">
                                                                                            <option value="">Select</option>
                                                                                            <?php if (!empty($get_scm)) { ?>
                                                                                                <?php foreach ($get_scm as $scm_id => $scm_name) : ?>
                                                                                                    <option value="<?= $scm_id ?>" <?php
                                                                                                                                    if ($getMappedSCM['user_role_id'] == $scm_id) {
                                                                                                                                        echo "selected";
                                                                                                                                    }
                                                                                                                                    ?>><?= $scm_name; ?></option>
                                                                                                <?php endforeach; ?>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            <?php }
                                                                            if (!empty($user_data['user_role_type_id']) && in_array($user_data['user_role_type_id'], [19])) { ?>
                                                                                <div class="row" id="user_role_agency_id_row">
                                                                                    <div class="col-md-3" id="div_agency_id">
                                                                                        <label> Agency</label>
                                                                                        <select class="form-control" name="user_role_agency_id" id="user_role_agency_id" required>
                                                                                            <option value="">Select</option>
                                                                                            <?php
                                                                                            if (!empty($master_collection_agency)) {
                                                                                                foreach ($master_collection_agency as $agency_id => $agency_name) {
                                                                                            ?>
                                                                                                    <option value="<?= $agency_id ?>
                                                                                              " <?php
                                                                                                    if ($agency_id == $user_role_agency_id) {
                                                                                                        echo "selected";
                                                                                                    }
                                                                                                ?>>
                                                                                                        <?= $agency_name ?>
                                                                                                    </option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            <?php } ?>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <button class="button-add btn btn-ifo" id="adminSaveUser">Update</button>
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
</section>
<script src="<?= base_url(); ?>public/js/selectbox_style.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#user_role_state_id, #user_role_branch_id').multiselect({
            nonSelectedText: 'Select all',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
        });
    });
</script>
