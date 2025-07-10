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
$uri = $this->uri->segment(1);
$stage = $this->encrypt->decode($this->uri->segment(2));
$stage = $this->uri->segment(2);

//if (!empty($collver) && $collver == 'collectionuserlist') {
//    $userlist = getuserCOllData('user_roles', $_SESSION['isUserSession']['user_id']);
//}

 $leadDetail = $leadDetails->result();
    $rowCount = 0;
    foreach ($leadDetail as $row) {
        if (!empty($row->lead_final_disbursed_date)) {
            $rowCount++;
        }
    }
?>
<span id="response" style="width: 100%;float: left;text-align: center;padding-top:-20%;"></span>
<section>
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div>
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

                                                            <form method="POST" style="margin-top:8px;" action="<?= base_url($uri . '/' . $stage) ?><?= isset($_REQUEST['sOrderBy']) ? '?sOrderBy='.$_REQUEST['sOrderBy'] : ''; ?>">
                                                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                                <div class="row">

                                                                    <div class="col-md-2 col-sm-3">
                                                                        <label>Lead ID</label>
                                                                        <div class="form-group">
                                                                            <input type="text" class="form-control" id="slid" name="slid" autocomplete="off" value="<?= !empty($search_input_array['slid']) ? $search_input_array['slid'] : ''; ?>"/>
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
<!--
																	<div class="col-md-2 col-sm-3">
                                                                        <label>Loan FollowUp</label>
                                                                        <div class="form-group">
                                                                            <select class="form-control" id="lfid" name="lfid">
                                                                                <option value="">Select</option>
                                                                                <?php
                                                                                if (!empty($loan_followup)) {
                                                                                    foreach ($loan_followup as $followup_id => $followup_name) {
                                                                                        ?>
                                                                                        <option <?= (!empty($search_input_array['lfid']) && $search_input_array['lfid'] == $followup_id) ? 'selected="selected"' : ''; ?> value="<?= intval($followup_id) ?>"><?= strval($followup_name) ?></option>
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
-->
                                                                    <?php /* if ($stage == "S1") { ?>
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
                                                                    <?php } */?>

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
<!--
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
-->
                                                                    <div class="col-md-2 col-sm-3">
                                                                        <div class="form-group">
                                                                            <label>Type</label>
                                                                            <select class="form-control" id="sut" name="sut">
                                                                                <option value="">Select</option>
                                                                                <option <?= (!empty($search_input_array['sut']) && $search_input_array['sut'] == "NEW") ? 'selected="selected"' : ''; ?> value="NEW">NEW</option>
                                                                                <option <?= (!empty($search_input_array['sut']) && $search_input_array['sut'] == "REPEAT") ? 'selected="selected"' : ''; ?> value="REPEAT">REPEAT</option>

                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                     <div class="col-md-2 col-sm-3">
                                                                        <div class="form-group">
                                                                            <label>Screener By</label>
                                                                            <select class="form-control" id="scb" name="scb">
                                                                                <option value="">Select</option>
                                                                                <?php foreach($user_list as $userList => $data_user_list){ ?>
                                                                                <option <?= (!empty($search_input_array['scb'])) && $search_input_array['scb'] == $data_user_list  ? 'selected="selected"' : ''; ?> value="<?= $data_user_list; ?>"><?= $data_user_list; ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <?php  if ($stage == "S1") { ?>
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
                                                        <?php if ($stage == "S13") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                             <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>
                                                        <?php }  elseif ($stage == "S14") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                             <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>

                                                         <?php }  elseif ($stage == "S16") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>

                                                        <?php } elseif ($stage == "S20") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                             <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>
                                                        <?php } elseif ($stage == "S21") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                             <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>
                                                        <?php } elseif ($stage == "S12") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                              <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>
                                                        <?php }  elseif ($stage == "S10") { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                            <span class="counter inner-page-box"><?= $loan_recommended_total ?></span>
                                                             <?php if(!empty($totalcount)){ ?>
                                                             <span class="counter inner-page-box"><?= $loan_recommended_total/$totalcount ?></span>
                                                             <?php } else { ?>
                                                               <span class="counter inner-page-box">0</span>
                                                             <?php } ?>
                                                        <?php } else { ?>
                                                            <span class="counter inner-page-box"><?= $totalcount; ?></span>
                                                        <?php } ?>
                                                        <?php if ((agent == 'CR1' && $stage == "S1") || (agent == 'CR2' && $stage == "S4")) { ?>
                                                            <a  class="btn inner-page-box checkDuplicateItem" id="checkDuplicateItem" style="background: #0d7ec0 !important;">Duplicate</a>
                                                            <a  class="btn inner-page-box" id="allocate" style="background: #0d7ec0 !important;">Allocate</a>
                                                        <?php } else if ((agent == 'DS1' && $stage == "S20")) { ?>
                                                            <a  class="btn inner-page-box" id="allocate" style="background: #0d7ec0 !important;">Allocate</a>
                                                        <?php } else if (agent == "CO4") { ?>
                                                            <a  class="btn inner-page-box" id="sync_data" style="background: #0d7ec0 !important;">Sync Data</a>
                                                        <?php } if ($uqickCall == 'button' && false) { ?>
                                                            <a class="btn inner-page-box"  onclick="getLeadValue()" id="sdf" style="background: #0d7ec0 !important;">Quick Call</a>

                                                            <!-- <div class="tb_search">
                                                                <button class="btn btn-success" onclick="getLeadValue()">Quick Call</button>  </div> -->
                                                        <?php } ?>
                                                        <div class="search-desktop">
                                                            <button class="btn btn-default inner-page-box" onclick="searchdatalist()"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</button>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    $this->load->helper('url');
                                                    $full_url = current_url();
                                                    $qs = (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'].'&' : $_SERVER['QUERY_STRING'];
                                                    $full_url_with_query = str_replace('index.php/','',current_url() . '?' . $qs);
                                                    $salaryOrderBy = (isset($_GET['sOrderBy']) && $_GET['sOrderBy'] == 'asc') ? 'asc' : 'desc';

                                                    ?>
                                                    <div class="widget-container">
                                                        <div class=" widget-block">
                                                            <div class="row">
                                                                <div class="table-responsive">
                                                                    <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                                                    <table class="table table-hover" id="domainTable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="whitespace data-fixed-columns"><b>Lead ID</b></th>
																				<?php if (in_array(agent, array('CO1', 'CO3')) && $uri == "collection") { ?>
																				<th class="whitespace data-fixed-columns"><b>Status</b></th>
																				<?php } ?>
                                                                                <th class="whitespace"><b>Action<?php if (in_array($stage, array("S1", "S4", "S20", "S16"))) { ?>
                                                                                            </br> <input type="checkbox" name="selectAll" id="selectAllDomainList" title="Select All" />
                                                                                        <?php } ?></b></th>
                                                                                <?php //if ($collver == 'collectionuserlist') { ?>
                                                                                    <!--<th class="whitespace"><b>Assign To</b></th>-->
                                                                                <?php //} ?>
                                                                                <th class="whitespace"><b>Applied&nbsp;On</b></th>

																				<th class="whitespace"><b>UTM Source</b></th>
                                                                                <?php if (!in_array($stage, array("S1")) || agent == 'CA' || agent == 'AU') { ?>
                                                                                    <th class="whitespace"><b>Source</b></th>
                                                                                <?php } ?>
                                                                                <?php if (in_array($stage, array("S14", "S16", 'S30'))) { ?>
                                                                                    <th class="whitespace"><b>Loan No.</b></th>
                                                                                    <th class="whitespace"><b>Disbursed Date</b></th>
                                                                                    <th class="whitespace"><b>Repayment Date</b></th>
                                                                                    <th class="whitespace"><b>Disbursed Amount</b></th>
                                                                                    <th class="whitespace"><b>Repay Amount</b></th>
                                                                                <?php if(isset($stage) && $stage=="S16") {?>
                                                                                    <th class="whitespace"><b>Receive Amount</b></th>
                                                                                    <th class="whitespace"><b>Receive Date</b></th>
                                                                                <?php }} ?>
                                                                                <?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                    <?php if ($uri == "preclosure") { ?>
                                                                                        <th class="whitespace"><b>Collection Amount</b></th>
                                                                                    <?php } ?>
                                                                                <?php } ?>

                                                                                <th class="whitespace"><b>Name</b></th>
                                                                                <th class="whitespace"><b>State</b></th>
                                                                                <th class="whitespace"><b>City</b></th>
                                                                                <th class="whitespace"><b>Branch</b></th>
                                                                                <th class="whitespace"><a href="<?=$full_url_with_query."sOrderBy=".$salaryOrderBy?>"><b>Mon. Salary</b></a></th>
                                                                                <th class="whitespace"><b>Mobile</b></th>
                                                                                <!-- <th class="whitespace"><b>Email</b></th> -->
                                                                                <th class="whitespace"><b>PAN</b></th>
                                                                                <th class="whitespace"><b>Type</b></th>
                                                                                <!--<th class="whitespace"><b>Status</b></th>-->
                                                                                <?php if (in_array($stage, array("S3", "S6"))) { ?>
                                                                                    <th class="whitespace"><b>Hold&nbsp;On</b></th>
                                                                                <?php } ?>


                                                                                <?php if (in_array($stage, array("S2", "S3", "S4")) && (in_array(agent, array("CA", "CR3", "CR2")))) { ?>
                                                                                    <th class="whitespace"><b>Screener</b></th>
                                                                                <?php } ?>
                                                                                 <!-- Rohit Start Add Code to display Repayment Amount  -->
                                                                                 <?php if (in_array($stage, array("S13", "S14", "S20", "S21", "S22", "S25")) && (in_array(agent, array("DS1","DS2")))) { ?>
                                                                                            <th class="whitespace"><b>Repay Amount</b></th>
                                                                                            <th class="whitespace"><b>Repay Date</b></th>       
                                                                                <?php } ?>

                                                                                <!-- Rohit End Add Code to display Repayment Amount  -->

                                                                                <?php if (in_array($stage, array("S5", "S6", "S10", "S11", "S12", "S13", "S20", "S21", "S22", "S25")) && (in_array(agent, array("CA", "CR3", "DS1", "DS2","AU")))) { ?>
                                                                                    <th class="whitespace"><b>Manager</b></th>

                                                                                <?php } ?>

                                                                                <?php if (in_array($stage, array("S12", "S13", "S20", "S21", "S22", "S25")) && (in_array(agent, array("CA", "CR3", "DS1", "DS2")))) { ?>
                                                                                    <th class="whitespace"><b>Sanctioned-On</b></th>
                                                                                    <th class="whitespace"><b>Sanctioned-Amount</b></th>
                                                                                <?php } ?>

                                                                                <?php if (in_array($stage, array("S13", "S21", "S22", "S25")) && (in_array(agent, array("CA", "DS2")))) { ?>
                                                                                    <th class="whitespace"><b>Disbursal Manager</b></th>
                                                                                    <?php if (in_array($stage, array("S13")) && (in_array(agent, array("CA", "DS2")))) { ?>
                                                                                        <th class="whitespace"><b>Disbursal Recommend-On</b></th>
                                                                                    <?php } ?>
                                                                                <?php } ?>
                                                                                <?php if (in_array($stage, array("S16")) && (in_array(agent, array("CA", "AC1", "AC2"))) && ($uri == "preclosure")) { ?>
                                                                                    <th class="whitespace"><b>Payment Uploaded By</b></th>
                                                                                    <th class="whitespace"><b>Payment Uploaded On</b></th>
                                                                                <?php } ?>
                                                                                 <?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                    <?php if ($uri == "preclosure") { ?>
                                                                                         <th class="whitespace"><b>Action</b></th>
                                                                                    <?php } ?>
                                                                                <?php } ?>

                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php

                                                                            if ($totalcount > 0) {
                                                                                $sn = 1;
                                                                                foreach ($leadDetails->result() as $row) {
                                                                                    $doc_upload = $row_class = '';
                                                                                    if ((in_array($stage, array("S1")) && $row->utm_source == "pre-approved-offeremail")) {
                                                                                        $row_class = 'class="info"';
                                                                                    } else if ((in_array($stage, array("S1")) && $row->customer_docs_available == 1)) {
                                                                                        $doc_upload = '<i class="fa-solid fa-star" style="color:#e4d500"></i>';
                                                                                    }

                                                                                    if ((in_array($stage, array("S4", "S5", "S6", "S11")) && $row->customer_digital_ekyc_flag == 1)) {
                                                                                        $row_class = 'class="success"';
                                                                                    }
																					if(isset($row->monthly_salary_amount) && $row->monthly_salary_amount >= 50000 && $row->monthly_salary_amount < 75000){
																						$row_class = 'style="background-color:#cbe9ff !important"';
																					}
																					if(isset($row->monthly_salary_amount) && $row->monthly_salary_amount >= 75000 && $row->monthly_salary_amount < 100000){
																						$row_class = 'style="background-color:#c3c1ef !important"';
																					}
																					if(isset($row->monthly_salary_amount) && $row->monthly_salary_amount >= 100000 ){
																						$row_class = 'style="background-color:#fff2cb !important"';
																					}
                                                                                    ?>

                                                                                    <tr  <?= $row_class ?>>
																						<?php if (in_array(agent, array('CO1', 'CO3')) && $uri == "collection") { ?>
                                                                                                <td class="whitespace data-fixed-columns">
																									 <!--<a href="<?=base_url('search/getleadDetails/'.$this->encrypt->encode($row->lead_id))?>" target="_blank"><?= $row->lead_id ?></a>-->
																									 <a href="<?=base_url('getleadDetails/'.$this->encrypt->encode($row->lead_id))?>" target="_blank"><?= $row->lead_id ?></a>
																								</td>
																								<td class="whitespace data-fixed-columns">
																									 <?php echo $row->status; ?>
																								</td>
                                                                                        <?php } else { ?>
                                                                                        <td class="whitespace data-fixed-columns">
                                                                                            <?php /* if ($uqickCall == 'button') { ?>
                                                                                              <input type="checkbox" name="quickCall_id[]" class="quickCall_id" id="quickCall_id" value="<?= $row->lead_id; ?>">
                                                                                              <?php } */ ?>
                                                                                            <?=isset($doc_upload) ? $doc_upload . '' : ''?>
                                                                                            <?= $row->lead_id ?>
                                                                                        </td>
																						<?php } ?>

                                                                                        <td class="whitespace">
                                                                                            <?php echo "agent: ".agent." stage: ".$stage; ?>
                                                                                            <?php if (in_array($stage, array("S1", "S4", "S20"))) { ?>
                                                                                                <?php if (agent == 'CR1' || agent == 'CA') { //&& in_array(strtoupper($row->utm_source), array("REPEATNF", "LWREPEATDB")) && in_array(user_id, array(55, 121, 48, 138))?>
                                                                                                    <input type="checkbox" name="duplicate_id[]" class="duplicate_id" id="duplicate_id" value="<?= intval($row->lead_id); ?>">&nbsp;</br>
                                                                                                    <input type="hidden" name="customer_id" id="customer_id" value="<?= strval($row->customer_id) ?>">
                                                                                                    <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
                                                                                                <?php } else if (in_array($stage, array("S4", "S20"))) { ?>
                                                                                                    <input type="checkbox" name="duplicate_id[]" class="duplicate_id" id="duplicate_id" value="<?= intval($row->lead_id); ?>">&nbsp;</br>
                                                                                                    <input type="hidden" name="customer_id" id="customer_id" value="<?= strval($row->customer_id) ?>">
                                                                                                    <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
                                                                                                <?php } else if (in_array(agent, ["CO4", "CO1"])) { //&& in_array($stage, ["S16", "S14"]) ?>
                                                                                                    <input type="checkbox" name="duplicate_id[]" class="duplicate_id" id="duplicate_id" value="<?= intval($row->lead_id); ?>">&nbsp;</br>
                                                                                                    <input type="hidden" name="customer_id" id="customer_id" value="<?= strval($row->customer_id) ?>">
                                                                                                    <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
                                                                                                <?php } else { ?>
                                                                                                    <span style="font-size: 20px;">-</span>
                                                                                                <?php } ?>

                                                                                            <?php } else { ?>
                                                                                                <a href="<?= base_url("getleadDetails_new/" . $this->encrypt->encode($row->lead_id)) ?>" class="" id="viewLeadsDetails">
                                                                                                    <span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span>
                                                                                                </a>
                                                                                            <?php } ?>
                                                                                        </td>
                                                                                        <?php
                                                                                        /* if ($collver == 'collectionuserlist') {

                                                                                          if ($this->uri->segment(1) == 'office-verification') {
                                                                                          $ftype = 'office';
                                                                                          } else {
                                                                                          $ftype = 'residence';
                                                                                          } */
                                                                                        ?>

                                                                                        <?php //} ?>
                                                                                        <?php if (!in_array($stage, array("S1")) || agent == 'CA') { ?>
                                                                                            <td class="whitespace"><?= date('d-m-Y H:i', strtotime($row->created_on)) ?></td>
                                                                                        <?php } else { ?>
                                                                                            <td class="whitespace"><?= date('d-m-Y', strtotime($row->created_on)) ?></td>
                                                                                        <?php } ?>
																						<td class="whitespace"><?= (!empty($row->utm_source)) ? $row->utm_source : '-' ?></td>
                                                                                        <?php if (!in_array($stage, array("S1")) || agent == 'CA' || agent == 'AU') { ?>
                                                                                            <td class="whitespace"><?= (isset($master_data_source[$row->lead_data_source_id])) ? strval(($master_data_source[$row->lead_data_source_id])) : '-' ?></td>
                                                                                        <?php } ?>
                                                                                        <?php if (in_array($stage, array("S14", "S16", 'S30'))) { ?>
                                                                                            <td class="whitespace"><?= ($row->loan_no) ? strval(($row->loan_no)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->lead_final_disbursed_date) ? strval(($row->lead_final_disbursed_date)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->repayment_date) ? date("d-m-Y", strtotime($row->repayment_date)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->sanctionedAmount) ? strval(($row->sanctionedAmount)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->repayment_amount) ? strval(($row->repayment_amount)) : "-" ?></td>
                                                                                        <?php if(isset($stage) && $stage=="S16") {?>
																							<td class="whitespace"><?= ($row->received_amount) ? strval(($row->received_amount)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->date_of_recived) ? strval(($row->date_of_recived)) : "-" ?></td>
                                                                                        <?php } } ?>
                                                                                        <?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                            <?php if ($uri == "preclosure") { ?>
                                                                                                <td class="whitespace"><?= (!empty($row->received_amount) ? number_format($row->received_amount) : '-') ?></td>
                                                                                            <?php } ?>
                                                                                        <?php } ?>
                                                                                        <td class="whitespace"><?= ($row->cust_full_name) ? strval(($row->cust_full_name)) : "-" ?></td>
                                                                                        <td class="whitespace"><?= ($row->m_state_name) ? strval(($row->m_state_name)) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->m_city_name) ? strval(($row->m_city_name)) : "-" ?></td>
                                                                                        <td class="whitespace"><?= (!empty($row->m_branch_name) ? strval($row->m_branch_name) : '-') ?></td>
                                                                                        <td class="whitespace"><?= ($row->monthly_salary_amount) ? strval(($row->monthly_salary_amount)) : '-' ?></td>
                                                                                        <td class="whitespace"  onclick="copyText('mcopy<?=$row->lead_id?>')"><input type="hidden" id="mcopy<?=$row->lead_id?>" value="<?=$row->mobile ? $row->mobile : ''?>"  /><?= ($row->mobile) ? inscriptionNumber(strval($row->mobile)) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->pancard) ? strval(($row->pancard)) : '-' ?></td>
                                                                                        <td class="whitespace"><?= ($row->user_type) ? strval(($row->user_type)) : 'NEW' ?></td>

                                                                                        <!--<td class="whitespace"><?= ($row->status) ? strval(($row->status)) : '-' ?></td>-->
                                                                                        <!--<?php if (in_array($stage, array("S3", "S6"))) { ?>-->
                                                                                        <!--    <td class="whitespace"><?= (($row->scheduled_date) ? date('d-m-Y H:i', strtotime($row->scheduled_date)) : '-') ?></td>-->
                                                                                        <!--<?php } ?>-->

                                                                                        <?php if (in_array($stage, array("S2", "S3", "S4")) && (in_array(agent, array("CA", "CR3", "CR2", "AU")))) { ?>
                                                                                            <th class="whitespace"><?= !empty($row->screenedBy) ? strval($row->screenedBy) : "--" ?></th>
                                                                                        <?php } ?>

                                                                                        <!-- Rohit Start Add Code to display Repayment Amount  -->
                                                                                        <?php if (in_array($stage, array("S13", "S14", "S20", "S21", "S22", "S25")) && (in_array(agent, array("DS1","DS2")))) { ?>
                                                                                            <td class="whitespace"><?= ($row->repayment_amount) ? strval(($row->repayment_amount)) : "-" ?></td>
                                                                                            <td class="whitespace"><?= ($row->repayment_date) ? date("d-m-Y", strtotime($row->repayment_date)) : "-" ?></td>
                                                                                        <?php } ?>
                                                                                        <!-- Rohit End Add Code to display Repayment Amount  -->

                                                                                        <?php if (in_array($stage, array("S5", "S6", "S10", "S11", "S12", "S13", "S20", "S21", "S22", "S25")) && (in_array(agent, array("CA", "CR3", "DS1", "DS2","AU")))) { ?>
                                                                                            <th class="whitespace"><?= !empty($row->sanctionAssignTo) ? strval($row->sanctionAssignTo) : "-" ?></th>

                                                                                        <?php } ?>
                                                                                        <?php if (in_array($stage, array("S12", "S13", "S20", "S21", "S22", "S25")) && (in_array(agent, array("CA", "CR3", "DS1", "DS2")))) { ?>
                                                                                            <td class="whitespace"><?= (!empty($row->sanctionedOn) ? date('d-m-Y H:i', strtotime($row->sanctionedOn)) : '-') ?></td>
                                                                                            <td class="whitespace"><?= (!empty($row->sanctionedAmount) ? number_format($row->sanctionedAmount) : '-') ?></td>
                                                                                        <?php } ?>

                                                                                        <?php if (in_array($stage, array("S13", "S21", "S22", "S25")) && (in_array(agent, array("CA", "DS2")))) { ?>
                                                                                            <td class="whitespace"><?= (!empty($row->disbursalAssignTo) ? strval($row->disbursalAssignTo) : '-') ?></td>
                                                                                            <?php if (in_array($stage, array("S13")) && (in_array(agent, array("CA", "DS2")))) { ?>
                                                                                                <td class="whitespace"><?= (!empty($row->lead_disbursal_recommend_datetime) ? date('d-m-Y H:i', strtotime($row->lead_disbursal_recommend_datetime)) : '-') ?></td>
                                                                                            <?php } ?>
                                                                                        <?php } ?>

                                                                                        <?php if (in_array($stage, array("S16")) && (in_array(agent, array("CA", "AC1", "AC2"))) && ($uri == "preclosure")) { ?>
                                                                                            <td class="whitespace"><?= (!empty($row->collection_executive) ? strval($row->collection_executive) : '-') ?></td>
                                                                                            <td class="whitespace"><?= (!empty($row->payment_uploaded_on) ? date('d-m-Y H:i', strtotime($row->payment_uploaded_on)) : '-') ?></td>
                                                                                        <?php } ?>
																						<?php if (in_array(agent, array('AC1', 'AC2'))) { ?>
                                                                                            <?php if ($uri == "preclosure") { ?>
                                                                                                <td class="whitespace">
																									<a href="<?=base_url("paymentHistory/" . $this->encrypt->encode($row->lead_id))?>" class="btn btn-sm btn-success" >View Payments</a>
																								</td>
                                                                                            <?php } ?>
                                                                                        <?php } ?>

                                                                                    </tr>
                                                                                    <?php }

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
</section>
</section>
<script type="text/javascript">

function copyText(id) {
/*     // Get the text field
    var copyText = document.getElementById(id);

    // Select the text field
    copyText.select();
    //copyText.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    document.execCommand("copy");

    // Alert the copied text
    alert("Copied the text: " + copyText.value); */
	var textToCopy = document.getElementById(id).value;

    navigator.clipboard.writeText(textToCopy).then(function() {
        //alert("Copied the text: " + textToCopy);
    }).catch(function(error) {
        console.error("Failed to copy text: ", error);
    });
}
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
