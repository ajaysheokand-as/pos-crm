<section class="parent_wrapper">
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
        $this->load->view('Layouts/header');
        $uri = $this->uri->segment(1);
        $stage = $this->uri->segment(2);
        ?>
        <span id="response" style="width: 100%;float: left;text-align: center;padding-top:-20%;"></span>
        <section>
            <div class="width-my">
                <div class="container-fluid">
                    <div class="taskPageSize taskPageSizeDashboard">
                        <div class="alertMessage">
                            <div class="alert alert-dismissible alert-success msg">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Thanks!</strong>
                                <a href="#" class="alert-link">Add Successfully</a>
                            </div>
                            <div class="alert alert-dismissible alert-danger err">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Failed!</strong>
                                <a href="#" class="alert-link">Try Again.</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12" style="padding: 0px !important;">
                                <div class="page-container list-menu-view">
                                    <div class="page-content">
                                        <div class="main-container">
                                            <!--Search container-->
                                            <div class="container-fluid" id="divExpendSearch">
                                                <div class="col-md-12">
                                                    <div class="login-formmea">
                                                        <div class="box-widget widget-module">
                                                            <div class="widget-container">
                                                                <div class=" widget-block">

                                                                    <form method="POST" style="margin-top:8px;" action="<?= base_url($uri . '/' . $stage) ?>">
                                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                                        <div class="row">

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>Lead ID</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="slid" name="slid" autocomplete="off" value="<?= !empty($search_input_array['slid']) ? $search_input_array['slid'] : ''; ?>" />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <div class="form-group">
                                                                                    <label>Lead Source</label>
                                                                                    <select class="form-control" id="sdsid" name="sdsid">
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_data_source)) {
                                                                                            foreach ($master_data_source as $data_source_id => $data_source_name) {
                                                                                        ?>
                                                                                                <option <?= (!empty($search_input_array['sdsid']) && $search_input_array['sdsid'] == $data_source_id) ? 'selected="selected"' : ''; ?> value="<?= intval($data_source_id) ?>"><?= strval($data_source_name) ?></option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <div class="form-group">
                                                                                    <label>State</label>
                                                                                    <select class="form-control" id="ssid" name="ssid">
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_state)) {
                                                                                            foreach ($master_state as $data_state_id => $data_state_name) {
                                                                                        ?>
                                                                                                <option <?= (!empty($search_input_array['ssid']) && $search_input_array['ssid'] == $data_state_id) ? 'selected="selected"' : ''; ?> value="<?= intval($data_state_id) ?>"><?= strval($data_state_name) ?></option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <div class="form-group">
                                                                                    <label>City</label>
                                                                                    <select class="form-control" id="scid" name="scid">
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_city)) {

                                                                                            foreach ($master_city as $data_city_id => $data_city_name) {
                                                                                        ?>
                                                                                                <option <?= (!empty($search_input_array['scid']) && $search_input_array['scid'] == $data_city_id) ? 'selected="selected"' : ''; ?> value="<?= intval($data_city_id) ?>"><?= strval($data_city_name) ?></option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <div class="form-group">
                                                                                    <label>Branch</label>
                                                                                    <select class="form-control" id="sbid" name="sbid">
                                                                                        <option value="">Select</option>
                                                                                        <?php
                                                                                        if (!empty($master_branch)) {
                                                                                            foreach ($master_branch as $data_branch_id => $data_branch_name) {
                                                                                        ?>
                                                                                                <option <?= (!empty($search_input_array['sbid']) && $search_input_array['sbid'] == $data_branch_id) ? 'selected="selected"' : ''; ?> value="<?= intval($data_branch_id) ?>"><?= strval($data_branch_name) ?></option>
                                                                                        <?php
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <div class="form-group">
                                                                                    <label>User Type</label>
                                                                                    <select class="form-control" id="sut" name="sut">
                                                                                        <option value="">Select</option>
                                                                                        <option <?= (!empty($search_input_array['sut']) && $search_input_array['sut'] == "NEW") ? 'selected="selected"' : ''; ?> value="NEW">NEW</option>
                                                                                        <option <?= (!empty($search_input_array['sut']) && $search_input_array['sut'] == "REPEAT") ? 'selected="selected"' : ''; ?> value="REPEAT">REPEAT</option>

                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>From Date</label>
                                                                                <div class="form-group">
                                                                                    <input readonly="" type="text" class="form-control" name="sfd" id="sfd" autocomplete="off" value="<?= !empty($search_input_array['sfd']) ? date("d-m-Y", strtotime($search_input_array['sfd'])) : ''; ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>To Date</label>
                                                                                <div class="form-group">
                                                                                    <input readonly="" type="text" class="form-control" name="sed" id="sed" autocomplete="off" value="<?= !empty($search_input_array['sed']) ? date("d-m-Y", strtotime($search_input_array['sed'])) : ''; ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>First Name</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="sfn" name="sfn" autocomplete="off" value="<?= !empty($search_input_array['sfn']) ? $search_input_array['sfn'] : ''; ?>" />
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>Mobile No</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="smno" name="smno" autocomplete="off" value="<?= !empty($search_input_array['smno']) ? $search_input_array['smno'] : ''; ?>" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>Email ID</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="semail" name="semail" autocomplete="off" value="<?= !empty($search_input_array['semail']) ? $search_input_array['semail'] : ''; ?>" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>PAN</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="span" name="span" autocomplete="off" value="<?= !empty($search_input_array['span']) ? $search_input_array['span'] : ''; ?>" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-2 col-sm-3">
                                                                                <label>Loan No</label>
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" id="sln" name="sln" autocomplete="off" value="<?= !empty($search_input_array['sln']) ? $search_input_array['sln'] : ''; ?>" />
                                                                                </div>
                                                                            </div>
                                                                            <?php if ($stage == "S1") { ?>
                                                                                <div class="col-md-2 col-sm-3">
                                                                                    <div class="form-group">
                                                                                        <label>Docs Available?</label>
                                                                                        <select class="form-control" id="sdu" name="sdu">
                                                                                            <option value="">Select</option>
                                                                                            <option <?= (!empty($search_input_array['sdu']) && $search_input_array['sdu'] == "1") ? 'selected="selected"' : ''; ?> value="1">YES</option>
                                                                                            <option <?= (!empty($search_input_array['sdu']) && $search_input_array['sdu'] == "2") ? 'selected="selected"' : ''; ?> value="2">NO</option>
                                                                                            <option <?= (!empty($search_input_array['sdu']) && $search_input_array['sdu'] == "3") ? 'selected="selected"' : ''; ?> value="3">ALL</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            <?php } ?>
                                                                            <div class="col-md-12">
                                                                                <button type="submit" name="search" value="1" class="btn btn-primary">Search</button>
                                                                                <button type="reset" onclick="window.location.href = '<?= $pageURL ?>'" class="btn btn-primary">Reset</button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--Search container-->
                                            <div class="container-fluid">
                                                <div class="col-md-12">
                                                    <div class="login-formmea">
                                                        <div class="box-widget widget-module">
                                                            <div class="widget-head clearfix">
                                                                <span class="h-icon"><i class="fa fa-th"></i></span>
                                                                <span class="inner-page-tag">Leads </span>
                                                                <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                                <span class="counter inner-page-box"><?= $totalDisbursePendingAmount ?></span>

                                                                <?php if (((agent == 'AM') && $stage == "S31")) { ?>
                                                                    <a class="btn inner-page-box" id="pre_audit_allocate" style="background: #0d7ec0 !important;">Allocate</a>
                                                                <?php } ?>
                                                                <div class="search-desktop">
                                                                    <button class="btn btn-default inner-page-box" onclick="searchdatalist()"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</button>
                                                                </div>
                                                            </div>

                                                            <div class="widget-container">
                                                                <div class=" widget-block">
                                                                    <div class="row">
                                                                        <div class="table-responsive">
                                                                            <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                                                            <table class="table table-striped table-bordered table-hover" id="domainTable" style="border: 1px solid #dde2eb">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th class="whitespace data-fixed-columns"><b>Lead ID</b>
                                                                                        </th>
                                                                                        <th class="whitespace">
                                                                                            <b>Action
                                                                                                <?php if ((agent == 'AM') && $stage == "S31") { ?>
                                                                                                    </br> <input type="checkbox" name="selectAll" id="selectAllDomainList" title="Select All" />
                                                                                                <?php } ?>
                                                                                            </b>
                                                                                        </th>
                                                                                        <?php if ($collver == 'collectionuserlist') { ?>
                                                                                            <!--<th class="whitespace"><b>Assign To</b></th>-->
                                                                                        <?php } ?>

                                                                                        <?php if (in_array($stage, array("S14", "S16", 'S30'))) { ?>
                                                                                            <th class="whitespace"><b>Loan No.</b></th>
                                                                                            <th class="whitespace"><b>Repayment Date</b></th>
                                                                                        <?php } ?>
                                                                                        <?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                            <?php if ($uri == "preclosure") { ?>
                                                                                                <th class="whitespace"><b>Collection Amount</b></th>
                                                                                            <?php } ?>
                                                                                        <?php } ?>

                                                                                        <th class="whitespace"><b>Name</b></th>
                                                                                        <th class="whitespace"><b>State</b></th>
                                                                                        <th class="whitespace"><b>City</b></th>
                                                                                        <!-- <th class="whitespace"><b>Branch</b></th> -->
                                                                                        <th class="whitespace"><b>Mobile</b></th>
                                                                                        <!-- <th class="whitespace"><b>Email</b></th> -->
                                                                                        <th class="whitespace"><b>PAN</b></th>
                                                                                        <th class="whitespace"><b>User Type</b></th>
                                                                                        <?php if (in_array($stage, array("S3", "S6"))) { ?>
                                                                                            <th class="whitespace"><b>Hold&nbsp;On</b></th>
                                                                                        <?php } ?>


                                                                                        <?php if (in_array($stage, array("S2", "S3", "S4")) && (in_array(agent, array("CA", "CR3", "CR2")))) { ?>
                                                                                            <th class="whitespace"><b>Screener</b></th>
                                                                                        <?php } ?>

                                                                                        <th class="whitespace"><b>Sanction<br>Manager</b></th>

                                                                                        <!-- <th class="whitespace"><b>Sanctioned-On</b></th> -->
                                                                                        <th class="whitespace"><b>Recommend<br>Amount</b></th>
                                                                                        <th class="whitespace"><b>Status</b></th>

                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php
                                                                                    if ($totalcount > 0) {
                                                                                        $sn = 1;
                                                                                        foreach ($leadDetails->result() as $row) {
                                                                                            $row_class = '';
                                                                                            if ((in_array($stage, array("S1")) && $row->utm_source == "pre-approved-offeremail")) {
                                                                                                $row_class = 'class="info"';
                                                                                            } else if ((in_array($stage, array("S1")) && $row->customer_docs_available == 1)) {
                                                                                                $row_class = 'style="background-color:#c1ee70 !important"';
                                                                                            }

                                                                                            if ((in_array($stage, array("S4", "S5", "S6", "S11")) && $row->customer_digital_ekyc_flag == 1)) {
                                                                                                $row_class = 'class="success"';
                                                                                            }
                                                                                    ?>
                                                                                            <tr <?= $row_class ?>>

                                                                                                <td class="whitespace data-fixed-columns">
                                                                                                    <?php /* if ($uqickCall == 'button') { ?>
                                                                                              <input type="checkbox" name="quickCall_id[]" class="quickCall_id" id="quickCall_id" value="<?= $row->lead_id; ?>">
                                                                                              <?php } */ ?>

                                                                                                    <?= $row->lead_id ?>
                                                                                                </td>

                                                                                                <td class="whitespace">
                                                                                                    <?php if (agent == 'AM' && $stage == "S31") { ?>
                                                                                                        <input type="checkbox" name="duplicate_id[]" class="duplicate_id" id="duplicate_id" value="<?= intval($row->lead_id); ?>">&nbsp;</br>
                                                                                                        <input type="hidden" name="customer_id" id="customer_id" value="<?= strval($row->customer_id) ?>">
                                                                                                        <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
                                                                                                    <?php } else { ?>
                                                                                                        <a href="<?= base_url("getleadDetails_new/" . $this->encrypt->encode($row->lead_id)) ?>" class="" id="viewLeadsDetails">
                                                                                                            <span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span>
                                                                                                        </a>
                                                                                                    <?php } ?>
                                                                                                </td>

                                                                                                <?php if (in_array($stage, array("S14", "S16", 'S30'))) { ?>
                                                                                                    <td class="whitespace"><?= ($row->loan_no) ? strval(strtoupper($row->loan_no)) : "-" ?></td>
                                                                                                    <td class="whitespace"><?= ($row->repayment_date) ? date("d-m-Y", strtotime($row->repayment_date)) : "-" ?></td>
                                                                                                <?php } ?>
                                                                                                <?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                                    <?php if ($uri == "preclosure") { ?>
                                                                                                        <td class="whitespace"><?= (!empty($row->received_amount) ? number_format($row->received_amount) : '-') ?></td>
                                                                                                    <?php } ?>
                                                                                                <?php } ?>
                                                                                                <td class="whitespace"><?= ($row->cust_full_name) ? strval(strtoupper($row->cust_full_name)) : "-" ?></td>
                                                                                                <td class="whitespace"><?= ($row->m_state_name) ? strval(strtoupper($row->m_state_name)) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->m_city_name) ? strval(strtoupper($row->m_city_name)) : "-" ?></td>
                                                                                                <!-- <td class="whitespace"><?= (!empty($row->m_branch_name) ? strval($row->m_branch_name) : '-') ?></td> -->
                                                                                                <td class="whitespace"><?= ($row->mobile) ? inscriptionNumber(strval($row->mobile)) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->pancard) ? strval(strtoupper($row->pancard)) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->user_type) ? strval(strtoupper($row->user_type)) : '-' ?></td>

                                                                                                <?php if (in_array($stage, array("S3", "S6"))) { ?>
                                                                                                    <td class="whitespace"><?= (($row->scheduled_date) ? date('d-m-Y H:i', strtotime($row->scheduled_date)) : '-') ?></td>
                                                                                                <?php } ?>

                                                                                                <?php if (in_array($stage, array("S2", "S3", "S4")) && (in_array(agent, array("CA", "CR3", "CR2")))) { ?>
                                                                                                    <th class="whitespace"><?= !empty($row->screenedBy) ? strval($row->screenedBy) : "-" ?></th>
                                                                                                <?php } ?>

                                                                                                <th class="whitespace"><?= !empty($row->sanctionAssignTo) ? strval($row->sanctionAssignTo) : "-" ?></th>

                                                                                                <!-- <td class="whitespace"><?= (!empty($row->sanctionedOn) ? date('d-m-Y H:i', strtotime($row->sanctionedOn)) : '-') ?></td> -->
                                                                                                <td class="whitespace"><?= (!empty($row->sanctionedAmount) ? number_format($row->sanctionedAmount) : '-') ?></td>
                                                                                                <td class="whitespace"><?= ($row->status) ? strval(strtoupper($row->status)) : '-' ?></td>

                                                                                            </tr>
                                                                                        <?php
                                                                                        }
                                                                                    } else {
                                                                                        ?>
                                                                                        <tr>
                                                                                            <th colspan="15" class="whitespace data-fixed text-center"><b style="color: #b73232;">No Record Found...</b></th>
                                                                                        </tr>
                                                                                    <?php } ?>
                                                                                </tbody>
                                                                            </table>
                                                                            <?= $links; ?>
                                                                        </div>
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
            </div>
        </section>
        <?php $this->load->view('Layouts/footer') ?>
        <?php $this->load->view('Tasks/main_js.php') ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#txt_searchall').keyup(function() {
                    var search = $(this).val().toUpperCase();
                    $('table tbody tr').hide();
                    var len = $('table tbody tr:not(.notfound) td:contains("' + search + '")').length;
                    if (len > 0) {
                        $('table tbody tr:not(.notfound) td:contains("' + search + '")').each(function() {
                            $(this).closest('tr').show();
                            $('.price-counter').text(len);
                        });
                    } else {
                        $('.notfound').show();
                        $('.price-counter').text(len);
                    }
                });

            });
        </script>
