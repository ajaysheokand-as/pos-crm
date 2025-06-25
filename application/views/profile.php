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
        <!-- section start -->

        <section>
            <div class="logo_container">
                <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo" > <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
            </div>
            <div class="container-fluid">

                <div class="taskPageSize taskPageSizeDashboard  default-page-height">

                    <div class="row">

                        <div class="col-md-12">

                            <div class="page-container list-menu-view">

                                <div class="page-content">

                                    <div class="main-container">

                                        <div class="container-fluid">

                                            <div class="col-md-12">

                                                <div class="login-formmea">

                                                    <div class="box-widget widget-module">

                                                        <div class="widget-head clearfix">

                                                            <span class="h-icon"><i class="fa fa-th"></i></span>

                                                            <h4>User Profile</h4>

                                                        </div>

                                                        <?php
                                                        if ($this->session->flashdata('msg') != '') {

                                                            echo '<div class="alert alert-success alert-dismissible">

                                                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>

                                                              <strong>' . $this->session->flashdata('msg') . '</strong>

                                                            </div>';
                                                        }

                                                        if ($this->session->flashdata('err') != '') {

                                                            echo '<div class="alert alert-danger alert-dismissible">

                                                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>

                                                              <strong>' . $this->session->flashdata('err') . '</strong>

                                                            </div>';
                                                        }
                                                        ?>



                                                        <div class="widget-container">

                                                            <div class=" widget-block">

                                                                <div class="row">

                                                                    <table class="table-striped table-bordered table-responsive table-hover" style="width: 100%; border-collapse: collapse;">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>User ID</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $_SESSION['isUserSession']['user_id']; ?></td>
                                                                            </tr>

                                                                            <?php if ($add_ifsc_flag == 1) { ?>
                                                                                <tr>

                                                                                    <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>Add IFSC Code</b></th>

                                                                                    <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><a href="<?= base_url('addBankDetails'); ?>">Click Here</a></td>

                                                                                </tr>
                                                                            <?php } ?>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>User Status</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $userDetails['status']; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>User Role</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $userDetails['role']; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>Name</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $userDetails['name']; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>Email</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $userDetails['email']; ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>Mobile</b></th>
                                                                                <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= inscriptionNumber($userDetails['mobile']); ?></td>
                                                                            </tr>
                                                                            <?php if (in_array(agent, ["CO3", "CA"])) { ?>
                                                                                <tr>
                                                                                    <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>Upload File</b></th>
                                                                                    <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;">
                                                                                        <form id="fileUploadForm" style="display: flex; align-items: center; width: 100%;">
                                                                                            <input type="file" name="import_file" id="import_file" class="form-control" required style="flex-grow: 1; margin-right: 10px;">
                                                                                            <button type="submit" class="btn btn-success" style="white-space: nowrap;">Upload</button>
                                                                                        </form>
                                                                                        <div id="uploadStatus" style="margin-top: 10px;"></div>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php } ?>
                                                                            <?php if (!empty($userDetails['center'])) { ?>
                                                                                <tr>
                                                                                    <th style="width: 50%; padding: 10px; border: 1px solid #dde2eb; background-color: #f8f9fa; font-weight: bold; text-align: right;"><b>User Center</b></th>
                                                                                    <td style="width: 50%; padding: 10px; border: 1px solid #dde2eb;"><?= $userDetails['center']; ?></td>
                                                                                </tr>
                                                                            <?php } ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <?php if (in_array(agent, ["CR1", "CR2", "CO1", "CO2", "CO4"])) {
                                                                ?>
                                                                    <div class="row">
                                                                        <table class="table table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb; margin-top: 10px;">
                                                                            <tbody>

                                                                                <form id="insert_lead_allocation" method="post" enctype="multipart/form-data">
                                                                                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />

                                                                                    <tr>
                                                                                        <td colspan="2" style="text-align: center;"><b>Lead Allocation</b></td>
                                                                                    </tr>
                                                                                    <?php if (agent == "CR1" || agent ==  "CR2") { ?>
                                                                                        <tr>
                                                                                            <th style="text-align: center;"><b>Allocation Status</b></th>

                                                                                            <td>
                                                                                                <label><input type="radio" name="user_status" value="1" <?= ($allocation_data['ula_user_status'] == 1) ? "checked" : ''; ?> required>&nbsp;&nbsp;ACTIVE&nbsp;&nbsp;</label>
                                                                                                <label><input type="radio" name="user_status" value="2" <?= ($allocation_data['ula_user_status'] == 2) ? "checked" : ''; ?> required>&nbsp;&nbsp;IN-ACTIVE</label>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <th style="text-align: center;"><b>User Type</b></th>
                                                                                            <td>
                                                                                                <label><input type="radio" name="user_type" value="1" <?= ($allocation_data['ula_user_case_type'] == 1) ? "checked" : ''; ?> required>&nbsp;&nbsp;FRESH&nbsp;&nbsp;</label>
                                                                                                <label><input type="radio" name="user_type" value="2" <?= ($allocation_data['ula_user_case_type'] == 2) ? "checked" : ''; ?> required>&nbsp;&nbsp;REPEAT</label>
                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td colspan="2" style="text-align: center;">
                                                                                                <button class="btn btn-success lead-sanction-button" id="lead_allocation">SAVE</button>
                                                                                                <div id="error_lead_allocation" class="error-me"></div>
                                                                                                <div id="lead_allocation_message" class="error-me"></div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php } elseif (in_array(agent, ["CO1", "CO2", "CO4"])) { ?>
                                                                                        <tr>
                                                                                            <th style="text-align: center;"><b>Allocation Status</b></th>

                                                                                            <td>
                                                                                                <label><input type="radio" name="user_status" value="1" <?= ($allocation_data['uca_user_status'] == 1) ? "checked" : ''; ?> required>&nbsp;&nbsp;ACTIVE&nbsp;&nbsp;</label>
                                                                                                <label><input type="radio" name="user_status" value="2" <?= ($allocation_data['uca_user_status'] == 2) ? "checked" : ''; ?> required>&nbsp;&nbsp;IN-ACTIVE</label>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <?php if (agent == "CO4") { ?>
                                                                                            <input type="text" name="uca_collection_type" value="1" hidden>
                                                                                            <tr>
                                                                                                <th style="text-align: center;"><b>Loan Category</b></th>
                                                                                                <td>
                                                                                                    <select class="form-control" name="uca_loan_amount_type_id">
                                                                                                        <option value="0">SELECT</option>
                                                                                                        <option value="1" <?= ($allocation_data['uca_loan_amount_type_id'] == 1) ? "selected" : ''; ?>>Upto 20K</option>
                                                                                                        <option value="2" <?= ($allocation_data['uca_loan_amount_type_id'] == 2) ? "selected" : ''; ?>>20K to 50K</option>
                                                                                                        <option value="3" <?= ($allocation_data['uca_loan_amount_type_id'] == 3) ? "selected" : ''; ?>>50K Above</option>
                                                                                                    </select>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th style="text-align: center;"><b>Bucket DPD</b></th>
                                                                                                <td>
                                                                                                    <select class="form-control" name="uca_loan_dpd_categories_id">
                                                                                                        <option value="0">SELECT</option>
                                                                                                        <option value="1" <?= ($allocation_data['uca_loan_dpd_categories_id'] == 1) ? "selected" : ''; ?>>-5 to 10 DPD</option>
                                                                                                        <option value="2" <?= ($allocation_data['uca_loan_dpd_categories_id'] == 2) ? "selected" : ''; ?>>11 DPD to 40 DPD</option>
                                                                                                    </select>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php } elseif (agent == "CO1") { ?>
                                                                                            <input type="text" name="uca_collection_type" value="2" hidden>
                                                                                            <tr>
                                                                                                <th style="text-align: center;"><b>Loan Category</b></th>
                                                                                                <td>
                                                                                                    <select class="form-control" name="uca_loan_amount_type_id">
                                                                                                        <option value="0">SELECT</option>
                                                                                                        <option value="1" <?= ($allocation_data['uca_loan_amount_type_id'] == 1) ? "selected" : ''; ?>>Upto 20K</option>
                                                                                                        <option value="2" <?= ($allocation_data['uca_loan_amount_type_id'] == 2) ? "selected" : ''; ?>>20K to 50K</option>
                                                                                                        <option value="3" <?= ($allocation_data['uca_loan_amount_type_id'] == 3) ? "selected" : ''; ?>>50K Above</option>
                                                                                                    </select>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th style="text-align: center;"><b>Bucket DPD</b></th>
                                                                                                <td>
                                                                                                    <select class="form-control" name="uca_loan_dpd_categories_id">
                                                                                                        <option value="0">SELECT</option>
                                                                                                        <option value="3" <?= ($allocation_data['uca_loan_dpd_categories_id'] == 3) ? "selected" : ''; ?>>41 DPD to 60 DPD</option>
                                                                                                        <option value="4" <?= ($allocation_data['uca_loan_dpd_categories_id'] == 4) ? "selected" : ''; ?>>60 DPD and Above</option>
                                                                                                    </select>
                                                                                                </td>
                                                                                            </tr>

                                                                                        <?php } ?>

                                                                                        <tr>
                                                                                            <td colspan="2" style="text-align: center;">
                                                                                                <button class="btn btn-success lead-sanction-button" id="collection_allocation">SAVE</button>
                                                                                                <div id="error_collection_allocation" class="error-me"></div>
                                                                                                <div id="collection_allocation_message" class="error-me"></div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php } ?>

                                                                                </form>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php } ?>

                                                                <?php if (in_array(agent, ["CR1", "CO1"])) { //screener role , collection exectuive role
                                                                ?>
                                                                    <div class="row">
                                                                        <?php if ($target_flag == 0) { ?>
                                                                            <table class="table table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb; margin-top: 10px; margin-bottom: 10px;" autocomplete="off">
                                                                                <tbody>

                                                                                    <form id="insert_target_allocation" method="post" enctype="multipart/form-data" onsubmit="return false;">
                                                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                                                        <tr>
                                                                                            <td colspan="2" style="text-align: center;"><b>Monthly Target Allocation</b></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th style="text-align: center;"><b><?= (in_array(agent, ["CR1"])) ? 'Total Cases' : 'Follow Ups'; ?></b></th>

                                                                                            <?php if (in_array(agent, ['CR1'])) { ?>
                                                                                                <td id="no_of_cases">
                                                                                                    <input type="text" id="no_of_cases_val" name="no_of_cases" maxlength="3" required="" />
                                                                                                    <div id="errorno_of_cases" class="error-me"></div>
                                                                                                </td>
                                                                                            <?php } elseif (in_array(agent, ['CO1'])) { ?>
                                                                                                <td id="no_of_cases">
                                                                                                    <input type="text" id="no_of_cases_val" name="no_of_cases" maxlength="5" required="" />
                                                                                                    <div id="errorno_of_cases" class="error-me"></div>
                                                                                                </td>
                                                                                            <?php } ?>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th style="text-align: center;"><b><?= (in_array(agent, ["CR1"])) ? 'Disbursal Amount' : 'Collection Amount'; ?></b></th>

                                                                                            <?php if ($target_data['uta_user_target_amount'] <= 0) { ?>
                                                                                                <td id="target_amount">
                                                                                                    <input type="text" id="target_amount_val" name="target_amount" maxlength="8" required="" />
                                                                                                    <div id="errortarget_amount" class="error-me"></div>
                                                                                                </td>
                                                                                            <?php } ?>

                                                                                        </tr>

                                                                                        <?php if ($target_flag == 0) { ?>
                                                                                            <tr>
                                                                                                <td colspan="2" style="text-align: center;">
                                                                                                    <button class="btn btn-success lead-sanction-button" id="target_allocation">SAVE</button>
                                                                                                    <div id="error_target_allocation" class="error-me"></div>
                                                                                                    <div id="target_allocation_message" class="error-me"></div>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php } ?>
                                                                                    </form>
                                                                                </tbody>
                                                                            </table>
                                                                        <?php } ?>
                                                                        <?php
                                                                        if (in_array(agent, ["CR1"])) {
                                                                            $count_header = 'Cases';
                                                                            $amount_header = 'Amount';
                                                                        } else {
                                                                            $count_header = 'FollowUps';
                                                                            $amount_header = 'Collection';
                                                                        }
                                                                        ?>
                                                                        <div class="col-md-12" style="overflow-x: auto;padding: 0px; margin-bottom: 10px; margin-top: 10px;">
                                                                            <table border="0" cellpadding="2" cellspacing="1" bgcolor="#dddddd" style="font-size: 12px" class="table table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb;" autocomplete="off">

                                                                                <tr>
                                                                                    <td colspan="7" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center; font-size: 16px; font-weight: bold;">Allocation&nbsp;History</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td width="16%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Name</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Target&nbsp;<?= $count_header ?></strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Achieve&nbsp;<?= $count_header ?></strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Target&nbsp;<?= $amount_header ?></strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Achieve&nbsp;<?= $amount_header ?></strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Achieve&nbsp;Cases(%)</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Achieve&nbsp;Amount(%)</strong></td>
                                                                                </tr>
                                                                                <?php
                                                                                if (!empty($target_history)) {

                                                                                    foreach ($target_history as $value) {

                                                                                        if (in_array(agent, ["CR1"])) {
                                                                                            $target_count = $value['uta_user_target_cases'];
                                                                                            $achieve_count = $value['uta_user_achieve_cases'];
                                                                                        } else {
                                                                                            $target_count = $value['uta_user_target_followups'];
                                                                                            $achieve_count = $value['uta_user_achieve_followups'];
                                                                                        }
                                                                                ?>

                                                                                        <tr>
                                                                                            <td align="center" bgcolor="#FFFFFF"><strong><?= $value['month']; ?></strong></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $target_count; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $achieve_count; ?></td>

                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['uta_user_target_amount']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['uta_user_achieve_amount']; ?></td>

                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= number_format(($achieve_count / $target_count) * 100, 2); ?>%</td>

                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= number_format(($value['uta_user_achieve_amount'] / $value['uta_user_target_amount']) * 100, 2); ?>%</td>
                                                                                        </tr>

                                                                                    <?php
                                                                                    }
                                                                                } else {
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td colspan="7" style="text-align:center;color:red;">No Record Found...</td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                            </table>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>


                                                                <?php if (in_array(agent, ['CR1'])) { ?>
                                                                    <div class="row">
                                                                        <div class="col-md-12" style="overflow-x: auto;padding: 0px;">
                                                                            <table border="0" cellpadding="2" cellspacing="1" bgcolor="#dddddd" style="font-size: 12px" class="table table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb;" autocomplete="off">

                                                                                <tr>
                                                                                    <td colspan="8" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center; font-size: 16px; font-weight: bold;">Collection&nbsp;History</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td width="16%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Repay&nbsp;Month</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Total&nbsp;Cases</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Closed&nbsp;Cases</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Loan&nbsp;Amount</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Collected&nbsp;Amount</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>POS</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Default&nbsp;Cases(%)</strong></td>
                                                                                    <td width="12%" bgcolor="#00455E" style="padding: 10px; width:25%; color:#fff; text-align: center;"><strong>Default&nbsp;Amount(%)</strong></td>
                                                                                </tr>
                                                                                <?php
                                                                                if (!empty($collection_history)) {

                                                                                    foreach ($collection_history as $value) {
                                                                                ?>


                                                                                        <tr>
                                                                                            <td align="center" bgcolor="#FFFFFF"><strong><?= $value['month']; ?></strong></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['total_cases']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['closed_cases']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['principle_amount']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['total_rcvd']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= $value['principle_outstanding']; ?></td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= ($value['total_cases'] <= 0 ? 100 : (number_format((($value['total_cases'] - $value['closed_cases']) / $value['total_cases']) * 100, 2))); ?>%</td>
                                                                                            <td bgcolor="#FFFFFF" style="padding: 10px; text-align: center;"><?= ($value['principle_amount'] <= 0 ? 100 : number_format(($value['principle_outstanding'] / $value['principle_amount']) * 100, 2)); ?>%</td>
                                                                                        </tr>

                                                                                    <?php
                                                                                    }
                                                                                } else {
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td colspan="8" style="text-align:center;color:red;">Record Not Found...</td>
                                                                                    </tr>
                                                                                <?php } ?>
                                                                            </table>
                                                                            </table>
                                                                        </div>
                                                                    </div>

                                                                <?php } ?>


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
            </div>
        </section>
        <!-- footer -->
        <?php $this->load->view('Layouts/footer') ?>
    </section>
</section>
<script>
    $("#userRole, #restrectedBranchUser").select2({
        placeholder: "Select",
        allowClear: true
    });

    $('#roleTag').multiselect({ // #centerName
        columns: 1,
        placeholder: 'Select',
        search: true,
        selectAll: true,
        allowClear: true
    });
</script>
<script>
    $(document).ready(function() {
        $('#restrectedBranchUser').on('change', function() {
            var state_id = $(this).val();
            if (state_id) {
                $.ajax({

                    url: '<?= base_url("AdminController/getUserCenter"); ?>',

                    type: 'POST',

                    data: {
                        state_id: state_id
                    },

                    dataType: 'json',

                    cache: false,

                    success: function(response) {

                        $('#centerName').empty();

                        $.each(response, function(index, item) {

                            $('#centerName').append('<option value="' + item.city_id + '">' + item.city + '</option>').css('height', '100px');

                        });

                    }

                });

            } else {

                $('#restrectedBranchUser').html('<option value="">Select state first</option>');

            }

        });

    });
</script>

<script>
    function viewUserDetails(user_id) {

        $.ajax({

            url: '<?= base_url("getUserDetailById/") ?>' + user_id,

            type: 'POST',

            dataType: "json",

            async: false,

            success: function(response) {

                console.log(response);

                $('#exampleModalLongTitle').html('&nbsp;&nbsp; User ID # ' + response.user_id);

                var fullName = response.name.split(" ");

                var firstName = fullName[0];

                var lastName = fullName[1];

                $('#firstName').val(firstName);

                $('#lastName').val(lastName);

                $('#email').val(response.email);

                $('#mobile').val(response.mobile);

                $('#userRole').val(response.role);

            },

            error: function(XMLHttpRequest, textStatus, errorThrown) {

                $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);

                return false;

            }

        });

    }

    $("#lead_allocation").on('click', function(e) {
        e.preventDefault();
        var user_status = $('input[name="user_status"]:checked').val();
        var user_type = $('input[name="user_type"]:checked').val();

        //        console.log(user_status);
        //        console.log(user_type);
        if ((user_status == 1 || user_status == 2) && (user_type == 1 || user_type == 2)) {
            lead_allocation_submition();
        } else {
            $('#error_lead_allocation').html('Please choose mandatory fields.').show().css({
                'color': 'red'
            });
        }
    });

    $("#collection_allocation").on('click', function(e) {
        e.preventDefault();
        var user_status = $('input[name="user_status"]:checked').val();
        var case_type = $('input[name="case_type"]:checked').val();

        if (true) {
            collection_allocation_submition();
        } else {
            $('#error_lead_allocation').html('Please choose mandatory fields.').show().css({
                'color': 'red'
            });
        }
    });



    $("#target_allocation").on('click', function(e) {
        e.preventDefault();
        $('#error_target_allocation').empty()
        var target_cases = $('#no_of_cases_val').val();
        var target_amount = $('#target_amount_val').val();

        if (target_amount <= 0 || target_cases == '' || target_cases <= 0 || target_amount == '') {
            $('#error_target_allocation').html("Mandatory fields can't be blank or Zero.").show().css({
                'color': 'red'
            });
        } else {
            target_allocation_submition();
        }

    });

    function lead_allocation_submition() {

        var FormData = $("#insert_lead_allocation").serialize();
        $('#error_lead_allocation').html('');

        $.ajax({
            url: '<?php base_url(); ?>leadAllocation',
            type: 'POST',
            data: FormData,
            dataType: "json",
            beforeSend: function() {
                $("#cover").show();
                $('#lead_allocation').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response['status'] == 1) {
                    $('#lead_allocation_message').html('Lead Allocation Saved Successfully.').show().css({
                        'color': 'green '
                    });

                } else {

                    $('#error_lead_allocation').html(response['errormessage']).show().css({
                        'color': 'red'
                    });
                }
            },
            complete: function() {
                $('#lead_allocation').text('SAVE').prop('disabled', false);
                $("#cover").fadeOut(1750);
            }
        });
    }

    function collection_allocation_submition() {

        var FormData = $("#insert_lead_allocation").serialize();
        $('#error_collection_allocation').html('');

        $.ajax({
            url: '<?php base_url(); ?>collectionAllocation',
            type: 'POST',
            data: FormData,
            dataType: "json",
            beforeSend: function() {
                $("#cover").show();
                $('#collection_allocation').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response['status'] == 1) {
                    $('#collection_allocation_message').html('Collection Allocation Saved Successfully.').show().css({
                        'color': 'green '
                    });
                } else {
                    $('#error_collection_allocation').html(response['errormessage']).show().css({
                        'color': 'red'
                    });
                }
            },
            complete: function() {
                $('#collection_allocation').text('SAVE').prop('disabled', false);
                $("#cover").fadeOut(1750);
            }
        });
    }

    function target_allocation_submition() {


        var FormData = $("#insert_target_allocation").serialize();
        $('#error_target_allocation').html('');

        $.ajax({
            url: '<?= base_url() ?>targetAllocation',
            type: 'POST',
            data: FormData,
            dataType: "json",
            beforeSend: function() {
                $("#cover").show();
                $('#target_allocation').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response['status'] == 1) {

                    if (response['uta_type_id'] == 1) {
                        var uta_user_target_cases = response['uta_user_target_cases'];
                    } else if (response['uta_type_id'] == 2) {
                        var uta_user_target_cases = response['uta_user_target_followups'];
                    }

                    var uta_user_target_amount = response['uta_user_target_amount'];

                    $('#no_of_cases').html('<td>' + uta_user_target_cases + '</td>');
                    $('#target_amount').html('<td>' + uta_user_target_amount + '</td>');

                    $('#target_allocation_message').html('Target Saved Successfully.').show().css({
                        'color': 'green'
                    });

                    $('#target_allocation').hide();
                } else {

                    $('#error_target_allocation').html(response['errormessage']).show().css({
                        'color': 'red'
                    });
                }
            },
            complete: function() {
                $('#target_allocation').text('SAVE').prop('disabled', false);
                $("#cover").fadeOut(1750);
            }
        });

    };


    $("#target_amount").keypress(function(e) {
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            $('#errortarget_amount').html('Please enter digits only.').show().css({
                'color': 'red'
            });
            return false;
        }
    });

    $("#no_of_cases").keypress(function(e) {
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

            $('#errorno_of_cases').html('Please enter digits only.').show().css({
                'color': 'red'
            });
            return false;

        }
    });
</script>

<script>
    $(document).ready(function() {
        $('#fileUploadForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Add CSRF token to formData
            formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

            $.ajax({
                url: '<?= base_url("import-collection") ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#uploadStatus').html('<div class="alert alert-info">Uploading...</div>');
                },
                success: function(response) {
                    try {
                        var resp = JSON.parse(response);
                        if (resp.status === 'success') {
                            $('#uploadStatus').html('<div class="alert alert-success">' + resp.message + '</div>');
                        } else {
                            $('#uploadStatus').html('<div class="alert alert-danger">' + resp.message + '</div>');
                        }
                    } catch (e) {
                        $('#uploadStatus').html('<div class="alert alert-danger">An error occurred while processing the server response.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#uploadStatus').html('<div class="alert alert-danger">An error occurred: ' + error + '</div>');
                },
                complete: function() {
                    $('#fileUploadForm')[0].reset();
                }
            });
        });
    });
</script>
