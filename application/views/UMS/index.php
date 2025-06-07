<section class="parent_wrapper">
<?php $this->load->view('Layouts/header') ?>
<?php
$uri = $this->uri->segment(1);
$pagination_links = "";
?>
<!-- section start -->
<section class="right-side">  
<style>
      .parent_wrapper .right-side .logo_container a img {
          margin-right: 0px;
          width: 100px !important;
      }
</style>

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
                                                    <span class="h-icon"><i class="fa-solid fa-table-columns"></i></span>
                                                    <span class="inner-page-tag">Users List</span>
                                                    <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('ums') ?>">
                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="filter_input" value="<?= !empty($_POST['filter_input']) ? $_POST['filter_input'] : '' ?>"/>
                                                        </div>
                                                        <div class="form-group">
                                                            <select class="form-control" id="filter_role" name="filter_role">
                                                                <option value="">Select</option>
                                                                <?php foreach ($master_role as $role_id => $role_name) { ?>
                                                                    <option <?= ((!empty($_POST['filter_role']) && $_POST['filter_role'] == $role_id) ? 'selected="selected"' : '') ?>  value="<?= $role_id ?>"><?= $role_name ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Search</button> <button  type="button" onclick="location.href = '<?= base_url('ums') ?>'" class="btn btn-outline-light">RESET</button>
                                                        <a class="btn btn-primary" href="<?= base_url('ums/add-user') ?>" role="button">ADD USER</a>
                                                    </form>

                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                      
                                                            <div class="scroll_on_x_axis">
                                                                <table class="table dt-table1 table-responsive table-hover">
                                                                    <thead>

                                                                        <tr>
                                                                            <th class="whitespace"><b>#</b></th>
                                                                            <!--<th class="whitespace"><b>Username</b></th>-->
                                                                            <th class="whitespace"><b>Name</b></th>
                                                                            <th class="whitespace"><b>Email</b></th>
                                                                            <th class="whitespace"><b>Mobile</b></th>
                                                                            <th class="whitespace"><b>Role</b></th>
                                                                            <th class="whitespace"><b>Status</b></th>
                                                                            <th class="whitespace"><b>Last Login</b></th>
                                                                            <th class="whitespace"><b>Created On</b></th>
                                                                            <!--<th class="whitespace"><b>Updated On</b></th>-->
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $i = 1;
                                                                        $role_name = "";
                                                                        if (!empty($userDetails)) {
                                                                            $pagination_links = $links;
                                                                            foreach ($userDetails as $userData) {
                                                                                $this->load->model('UMS/UMS_Model', 'umsModel');
                                                                                $roles = $this->umsModel->getUmsRoleList($userData["user_id"]);
                                                                                ?>
                                                                                <tr class="table-default">
                                                                                    <td class="whitespace"><?= $userData["user_id"] ?></td> 
                                                                                    <!--<td class="whitespace"><?= display_data($userData["user_name"]) ?></td>--> 

                                                                                    <td class="whitespace"><?= $userData["name"] ?></td>
                                                                                    <td class="whitespace"><?= $userData["email"] ?></td>
                                                                                    <td class="whitespace"><?= $userData["mobile"] ?></td>
                                                                                    <td class="whitespace">
                                                                                        <div class="tooltip" style="user-select: auto;">
                                                                                            <i class="fa fa-users" style="user-select: auto;"></i>
                                                                                            <span class="tooltiptext" style="user-select: auto;">
                                                                                                <i style="user-select: auto;">
                                                                                                    <?php
                                                                                                    foreach ($roles['role_list'] as $role) {
                                                                                                        echo $role_name = ((str_word_count($role['role_type_name']) > 1) ? (', ' . $role['role_type_name']) : $role['role_type_name']);
                                                                                                    }
                                                                                                    ?>
                                                                                                </i>
                                                                                            </span>
                                                                                        </div>
                                                                                    </td>

                                        <!--<td class="whitespace"><?= $userData["status"] ?></td>-->
                                                                                    <td class="whitespace"><?= display_data($master_user_status[$userData["user_status_id"]]) ?></td> 
                                                                                    <td class="whitespace"><?= !empty($userData["user_last_login_datetime"]) ? display_date_format($userData["user_last_login_datetime"]) : "-" ?></td>
                                                                                    <td class="whitespace"><?= !empty($userData["created_on"]) ? display_date_format($userData["created_on"]) : "-" ?></td>
                                                                                    <!--<td class="whitespace"><?= !empty($userData["updated_on"]) ? display_date_format($userData["updated_on"]) : "-" ?></td>-->

                                                                                    <td class="whitespace">
                                                                                        <a  class="btn btn-primary btn-sm" href="<?= base_url('ums/view-user/' . $this->encrypt->encode($userData["user_id"])) ?>"><i class="fa-solid fa-pen-nib"></i></a>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php
                                                                                $i++;
                                                                            }
                                                                        } else {
                                                                            ?>

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
</section>
</section>