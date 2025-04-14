<?php $this->load->view('Layouts/header') ?>
<?php
$uri = $this->uri->segment(1);
$stage = $this->uri->segment(2);

//if (!empty($collver) && $collver == 'collectionuserlist') {
//    $userlist = getuserCOllData('user_roles', $_SESSION['isUserSession']['user_id']);
//}
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
                                                                            <input type="text" class="form-control" id="slid" name="slid" autocomplete="off" value="<?= !empty($search_input_array['slid']) ? $search_input_array['slid'] : ''; ?>"/>
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
                                                                                        <option <?= (!empty($search_input_array['sdsid']) && $search_input_array['sdsid'] == $data_source_id) ? 'selected="selected"' : ''; ?> value="<?= $data_source_id ?>"><?= $data_source_name ?></option>
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
                                                                                        <option <?= (!empty($search_input_array['ssid']) && $search_input_array['ssid'] == $data_state_id) ? 'selected="selected"' : ''; ?> value="<?= $data_state_id ?>"><?= $data_state_name ?></option>
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
                                                                                        <option <?= (!empty($search_input_array['scid']) && $search_input_array['scid'] == $data_city_id) ? 'selected="selected"' : ''; ?> value="<?= $data_city_id ?>"><?= $data_city_name ?></option>
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
                                                                                        <option <?= (!empty($search_input_array['sbid']) && $search_input_array['sbid'] == $data_branch_id) ? 'selected="selected"' : ''; ?> value="<?= $data_branch_id ?>"><?= $data_branch_name ?></option>
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
                                                                            <input type="text" class="form-control" id="sfn" name="sfn" autocomplete="off" value="<?= !empty($search_input_array['sfn']) ? $search_input_array['sfn'] : ''; ?>"/>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2 col-sm-3">
                                                                        <label>Mobile No</label>
                                                                        <div class="form-group">
                                                                            <input type="text" class="form-control" id="smno" name="smno" autocomplete="off" value="<?= !empty($search_input_array['smno']) ? $search_input_array['smno'] : ''; ?>"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 col-sm-3">
                                                                        <label>Email ID</label>
                                                                        <div class="form-group">
                                                                            <input type="text" class="form-control" id="semail" name="semail" autocomplete="off" value="<?= !empty($search_input_array['semail']) ? $search_input_array['semail'] : ''; ?>"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 col-sm-3">
                                                                        <label>PAN</label>
                                                                        <div class="form-group">
                                                                            <input type="text" class="form-control" id="span" name="span" autocomplete="off" value="<?= !empty($search_input_array['span']) ? $search_input_array['span'] : ''; ?>"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 col-sm-3">
                                                                        <label>Loan No</label>
                                                                        <div class="form-group">
                                                                            <input type="text" class="form-control" id="sln" name="sln" autocomplete="off" value="<?= !empty($search_input_array['sln']) ? $search_input_array['sln'] : ''; ?>"/>
                                                                        </div>
                                                                    </div>
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
                                                        <div class="search-desktop">    
                                                            <button class="btn btn-default inner-page-box" onclick="searchdatalist()"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</button>
                                                        </div>
                                                    </div>

                                                    <div class="widget-container">
                                                        <div class=" widget-block">
                                                            <div class="row">
                                                                <div class="table-responsive">
                                                                    <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                                                    <table class="table table-striped table-bordered table-hover" id="domainTable"  style="border: 1px solid #dde2eb">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="whitespace"><b>Action</b></th>
                                                                                <th class="whitespace data-fixed-columns"><b>Lead ID</b></th>
                                                                                <th class="whitespace"><b>Feedback&nbsp;On</b></th>
                                                                                <th class="whitespace"><b>Source</b></th>
                                                                                <th class="whitespace"><b>Name</b></th>
                                                                                <th class="whitespace"><b>State</b></th>
                                                                                <th class="whitespace"><b>City</b></th>
                                                                                <th class="whitespace"><b>Mobile</b></th>
                                                                                <th class="whitespace"><b>PAN</b></th>
                                                                                <th class="whitespace"><b>User Type</b></th>
                                                                                <th class="whitespace"><b>Feedback&nbsp;Remarks</b></th>
                                                                                <th class="whitespace"><b>Status</b></th>
                                                                                <th class="whitespace"><b>CIF No.</b></th>
                                                                                <th class="whitespace"><b>Loan No.</b></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            if ($totalcount > 0) {
                                                                                $sn = 1;
                                                                                foreach ($leadDetails->result() as $row) :
                                                                                    ?>
                                                                                    <tr>

        <!--                                                                                            <td class="whitespace data-fixed-columns">
                                                                                                        <a href="" id="viewFeedback" data-toggle="modal" data-target="#feedbackModal">
                                                                                                            <span class="glyphicon glyphicon-edit" style="font-size: 20px;" onclick="get_customer_feedback('<?= $this->encrypt->encode($row->lead_id) ?>')"></span>
                                                                                                        </a>
                                                                                                    </td>-->

                                                                                        <td class="whitespace data-fixed-columns">
                                                                                            <a href="<?= base_url('view-customer-feedback/' . $this->encrypt->encode($row->lead_id)) ?>">
                                                                                                <span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span>
                                                                                            </a>
                                                                                        </td>

                                                                                        <td class="whitespace data-fixed-columns"><?= $row->lead_id ?></td>
                                                                                        <td class="whitespace"><?= date('d-m-Y H:i', strtotime($row->created_on)) ?></td>
                                                                                        <td class="whitespace"><?= ($row->source) ? strtoupper($row->source) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->cust_full_name) ? strtoupper($row->cust_full_name) : "-" ?></td>
                                                                                        <td class="whitespace"><?= ($row->m_state_name) ? strtoupper($row->m_state_name) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->m_city_name) ? strtoupper($row->m_city_name) : "-" ?></td>
                                                                                        <td class="whitespace"><?= ($row->mobile) ? $row->mobile : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->pancard) ? strtoupper($row->pancard) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->user_type) ? strtoupper($row->user_type) : '-' ?></td>
                                                                                        <td class="whitespace">
                                                                                            <?php if (!empty($row->remarks)) { ?>
                                                                                                <div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext"><?= ($row->remarks) ? strtoupper($row->remarks) : '-' ?></span></div>
                                                                                            <?php
                                                                                            } else {
                                                                                                echo "-";
                                                                                            }
                                                                                            ?>
                                                                                        </td>
                                                                                        <td class="whitespace"><?= ($row->status) ? strtoupper($row->status) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->customer_id) ? strtoupper($row->customer_id) : "-" ?></td> 
                                                                                        <td class="whitespace"><?= ($row->loan_no) ? strtoupper($row->loan_no) : "-" ?></td>
                                                                                    </tr>
                                                                                    <?php
                                                                                endforeach;
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
<!-- Modal -->
<div class="modal" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" style="padding: 5px;">Feedback Response</h5><hr>
                <!--                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                    <i class="material-icons">&times;</i>
                                </button>-->
            </div>
            <div class="modal-body">
                <div id="customer_feedback"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $('#txt_searchall').keyup(function () {
            var search = $(this).val().toUpperCase();
            $('table tbody tr').hide();
            var len = $('table tbody tr:not(.notfound) td:contains("' + search + '")').length;
            if (len > 0) {
                $('table tbody tr:not(.notfound) td:contains("' + search + '")').each(function () {
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
