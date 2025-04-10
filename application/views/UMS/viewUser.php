<section class="parent_wrapper">
<?php
$this->load->view('Layouts/header')
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
          width: 165px;
      }

</style>
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
                                                    <h4>View User</h4>

                                                    &nbsp;<button class="button-add btn btn-ifo" onclick="location.href = '<?= base_url('ums/edit-user/' . $enc_user_id) ?>'" role="button">Edit User Info</button>
                                                    <button class="button-add btn btn-ifo" onclick="location.href = '<?= base_url('ums') ?>'" role="button">Back</button>

                                                </div>

                                                <div class="widget-container">

                                                    <div class=" widget-block">
                                                        <div class="row">

                                                            <?php if (!empty($this->session->flashdata('success_msg'))) { ?>
                                                                <div class="alert alert-success" style="background: green; color: #fff;">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('success_msg'); ?>
                                                                </div>
                                                            <?php } else if (!empty($this->session->flashdata('errors_msg'))) { ?>
                                                                <div class="alert alert-danger" style="background: red; color: #fff;">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('errors_msg'); ?>
                                                                </div>
                                                            <?php } else if (!empty($this->session->flashdata('success_msg_role'))) { ?>
                                                                <div class="alert alert-success" style="background: green; color: #fff;">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('success_msg_role'); ?>
                                                                </div>
                                                            <?php }
                                                            ?>
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

                                                                                <tr>
                                                                                    <th>Created By</th>
                                                                                    <td><?= display_data($user_data["created_by_name"]) ?></td>
                                                                                    <th>Updated By</th>
                                                                                    <td><?= display_data($user_data["updated_by_name"]) ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Created On</th>
                                                                                    <td><?= display_date_format($user_data["created_on"]) ?></td>
                                                                                    <th>Updated On</th>
                                                                                    <td><?= display_date_format($user_data["updated_on"]) ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <button class="button-add btn btn-ifo" onclick="location.href = '<?= base_url('ums/add-role/' . $enc_user_id) ?>'" role="button">Add Role</button>
                                                            </div>
                                                        </div>
                                                        <div class="row">&nbsp;</div>

                                                        <div class="row">
                                                            <div role="tabpanel" class="tab-pane fade in active" id="userSaction">
                                                                <div id="userSaction">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover table-striped table-bordered">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th>S.No</th>
                                                                                    <th>Role</th>
                                                                                    <th>Status</th>
                                                                                    <th>Locations</th>
                                                                                    <th>Credit/Credit Head</th>
                                                                                    <th>Mapped SCM</th>
                                                                                    <th>Mapped CFE</th>
                                                                                    <th>Created On</th>
                                                                                    <th>Updated On</th>
                                                                                    <th>Action</th>
                                                                                </tr>
                                                                                <?php
                                                                                if (!empty($user_role_data)) {
                                                                                    $i = 1;
                                                                                    foreach ($user_role_data as $role_data) {
                                                                                        $getMappedSCM = $this->umsModel->getSCMSelectedvalue($role_data['user_role_id']);
                                                                                        $getMappedcredit = $this->umsModel->getcreditSelectedvalue($role_data['user_role_id']);
                                                                                        $cfe_list = $this->umsModel->getMapped_FCE_with_SCM($role_data['user_role_id']);

                                                                                        $branch_list = $this->umsModel->getUmsUserMappedBranchList($role_data['user_role_id'], 3, false); // 3 => branch
                                                                                        $state_list = $this->umsModel->getUmsStateList($role_data['user_role_id'], 2, false); // 3 => branch
                                                                                        ?>
                                                                                        <tr>
                                                                                            <!--<td><?= $i++; ?></td>-->
                                                                                            <td><?= $role_data['user_role_id']; ?></td>
                                                                                            <td><?= display_data($role_data["role_type_name"]) ?></td>
                                                                                            <td><?= (($role_data['user_role_active'] == 1) ? "Active" : "Inactive") ?></td>

                                                                                            <td class="whitespace">
                                                                                                <?php
                                                                                                if (!empty($branch_list['branch_list'])) {
                                                                                                    echo "Branch";
                                                                                                } else if (!empty($state_list['state_list'])) {
                                                                                                    echo "State&nbsp;";
                                                                                                } else {
                                                                                                    echo "-";
                                                                                                }
                                                                                                ?>&nbsp;
                                                                                                <?php if (!empty($branch_list['branch_list']) || !empty($state_list['state_list'])) { ?>
                                                                                                    <div class="tooltip" style="user-select: auto;">
                                                                                                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                                                                                                        <span class="tooltiptext" style="user-select: auto;">
                                                                                                            <i style="user-select: auto;">
                                                                                                                <?php
                                                                                                                if (!empty($branch_list['branch_list'])) {
                                                                                                                    foreach ($branch_list['branch_list'] as $role) {
                                                                                                                        $branch_id = $role[''];
                                                                                                                        echo $role_name = ((str_word_count($role['m_branch_name']) >= 1) ? ($role['m_branch_name'] . ', ') : $role['m_branch_name']);
                                                                                                                    }
                                                                                                                } else if (!empty($state_list['state_list'])) {
                                                                                                                    foreach ($state_list['state_list'] as $role) {
                                                                                                                        $branch_id = $role[''];
                                                                                                                        echo $role_name = ((str_word_count($role['m_state_name']) >= 1) ? ($role['m_state_name'] . ', ') : $role['m_state_name']);
                                                                                                                    }
                                                                                                                } else {
                                                                                                                    echo "-";
                                                                                                                }
                                                                                                                ?>
                                                                                                            </i>
                                                                                                        </span>
                                                                                                    </div>
                                                                                                <?php } ?>
                                                                                            </td>
                                                                                            <td><?= ($getMappedcredit['name'] ? $getMappedcredit['name'] : "-"); ?></td>
                                                                                            <td><?= ($getMappedSCM['name'] ? $getMappedSCM['name'] : "-"); ?></td>
                                                                                            <td>
                                                                                                <?php
                                                                                                if (!empty($cfe_list)) {
                                                                                                    foreach ($cfe_list as $cfe => $value) {
                                                                                                        echo $value['name'] . ", ";
                                                                                                    }
                                                                                                } else {
                                                                                                    echo "-";
                                                                                                }
                                                                                                ?>
                                                                                            </td>


                                                                                            <td><?= display_date_format($role_data["user_role_created_on"]) ?></td>
                                                                                            <td><?= display_data($role_data["user_role_updated_on"]) ?></td>
                                                                                            <td>
                                                                                                <a 
                                                                                                <?php
                                                                                                //if ($role_data["user_role_active"] == 0) {
                                                                                                //echo ' disabled=disabled ';
                                                                                                //}
                                                                                                ?>
                                                                                                    id="editbutton" class="btn btn-primary btn-sm" href="<?= base_url('ums/edit-user-role/' . $this->encrypt->encode($role_data["user_role_id"])) ?>"><i class="fa fa-pencil-square-o"></i></a>


                                                                                                <?php
                                                                                                //if ($role_data['user_role_active'] == 1) {
                                                                                                //echo "Active";
                                                                                                // } else {
                                                                                                //echo "Inactive";
                                                                                                // } 
                                                                                                ?>
                                                                                                <!--</a>-->
                                                                                            </td>
                                                                                        </tr>
                                                                                        <?php
                                                                                    }
                                                                                } else {
                                                                                    ?>
                                                                                    <tr><td colspan="5" >No role found.</td></tr>
                                                                                <?php } ?>

                                                                            </tbody>
                                                                        </table>
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
            </div>
        </div>
    </div>


</section>

<?php $this->load->view('Layouts/footer') ?>
</section>
</section>



