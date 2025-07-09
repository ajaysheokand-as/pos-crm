<section class="parent_wrapper">
    <?php
    $this->load->view('Layouts/header');

    $url = $this->uri->segment(1);
    $hold_date = date('Y-m-d h:i:s', strtotime(timestamp . ' + 2 days'));
    ?>
    <section class="right-side">
        <style>
            .parent_wrapper {
                width: 100%;
                height: 100vh;
                display: flex;
            }

            .audit-success {
                background: green !important;
                border: 1px solid green !important;
            }

            .audit-success:hover {
                background: transparent !important;
                color: green !important;
                border: 1px solid green !important;
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
        <div class="width-my">
            <div class="logo_container">
                <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
            </div>
            <div class="container-fluid">
                <div class="taskPageSize taskPageSizeDashboard" style="height:auto !important;">
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
                    <div class="row default-page-height">
                        <div class="col-md-12">
                            <div class="tab" role="tabpanel">
                                <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>" readonly>
                                <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>" readonly>
                                <ul class="nav nav-tabs" role="tablist">

                                    <li role="presentation" class="borderList active"><a href="#LeadSaction" onclick="getLeadsDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="lead" role="tab" data-toggle="tab">
                                            <?= ((agent == "CR1" && in_array($leadDetails->stage, ["S1", "S2", "S3"])) ? "Lead" : "Application") ?></a></li>

                                    <?php if (agent == "CR1" && in_array($leadDetails->stage, ["S2", "S3"])) { ?>
                                        <li role="presentation" class="borderList"><a href="#ApplicationSaction" onclick="getApplicationDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="lead" role="tab" data-toggle="tab">Application</a></li>

                                    <?php }
                                    if (in_array(agent, ["AU", "AM", "AH", "CR1", "CR2", "CR3", "AC1", "AC2", "CA", "SA", "DS1", "DS2", "CO1", "CO2", "CO3", "CO4", "CC"]) || $url == "search") { ?>
                                        <li role="presentation" class="borderList"><a href="#DocumentSaction" aria-controls="Document" role="tab" data-toggle="tab">Documents</a></li>

                                    <?php }
                                    if (in_array(agent, ["AU", "AM", "AH", "CR2", "CR3", "AC1", "AC2", "CA", "SA", "DS1", "DS2", "CO1", "CO2", "CO3", "CO4", "CFE1"]) || $url == "search") { ?>

                                        <li role="personal" class="borderList"><a href="#PersonalDetailSaction" onclick="getPersonalDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="Personal" role="tab" data-toggle="tab">Personal</a></li>

                                        <li role="banking" class="borderList"><a href="#BankingDetailSaction" onclick="getCustomerBanking('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="Banking" role="tab" data-toggle="tab">Banking</a></li>

                                        <!--<li role="presentation" class="borderList"><a href="#Verification" aria-controls="Verification" role="tab" data-toggle="tab" >Verification</a></li>-->
                                        <li role="verification" class="borderList"><a href="#Verification" onclick="getVerificationDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="Verification" role="tab" data-toggle="tab">Verification</a></li>

                                        <li role="cam" class="borderList "><a href="#CAMSheetSaction" onclick="getCam('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="messages" role="tab" data-toggle="tab">CAM</a></li>

                                        <li role="bre" class="borderList"><a href="#BRESaction" aria-controls="messages" onclick="get_bre_rule_result()" role="tab" data-toggle="tab">BRE</a></li>

                                    <?php
                                    }

                                    if (in_array(agent, ["AU", "AM", "AH", "DS1", "DS2", "CR3", "AC1", "AC2", "CO1", "CO2", "CO3", "CO4", "CA", "SA", "CC"]) || $url == "search") {
                                    ?>
                                        <li role="disbursal" class="borderList"><a href="#DisbursalSaction" onclick="disbursalDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="messages" role="tab" data-toggle="tab">Disbursal</a></li>

                                    <?php }
                                    if ((in_array($leadDetails->stage, array("S14", "S16")) && agent == 'CR2' && $leadDetails->lead_credit_assign_user_id == user_id) || in_array(agent, ["AU", "AM", "AH", "AC1", "AC1", "AC2", "CO1", "CO2", "CO3", "CO4", "CFE1", "CA", "SA", "CO4", "CC"]) || $url == "search") { ?>
                                        <li role="collection" class="borderList"><a href="#CollectionSaction" onclick="getCollectionDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" aria-controls="messages" role="tab" data-toggle="tab">Collection</a></li>

                                        <li role="repayment" class="borderList"><a href="#RepaymentSaction" onclick="repaymentLoanDetails('<?= $leadDetails->lead_id ?>', '<?= user_id ?>')" aria-controls="messages" role="tab" data-toggle="tab">Repayment</a></li>
                                    <?php } ?>
                                    <!--<li role="audit" class="borderList"><a href="#AuditSaction" onclick="auditDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>','<?= user_id ?>','Audit')" aria-controls="messages" role="tab" data-toggle="tab">Audit</a></li>-->
                                </ul>
                                <hr>

                                <div class="tab-content tabs">
                                    <div role="tabpanel" class="tab-pane fade in active mw-1200" id="LeadSaction">
                                        <?php if ($leadDetails->lead_fi_residence_status_id == 1 || $leadDetails->lead_fi_office_status_id == 1) { ?>
                                            <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Application is under Field Investigation.
                                            </div>
                                        <?php } else if ($leadDetails->check_cibil_status == 0) { ?>
                                            <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please generate cibil. <br />
                                                Note * : If you will change any things in the customer details like - NAME, EMAIL, MOBILE, GENDER, DOB, PAN, STATE, CITY, PINCODE then it's required to regenerate cibil.
                                            </div>
                                        <?php
                                        } else if ($isAnotherLeadInprocess->num_rows() > 0) {
                                            $another_lead = $isAnotherLeadInprocess->row();
                                        ?>
                                            <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Already one application <?= $another_lead->lead_id ?> of same customer <?= $another_lead->first_name ?> with status - <?= $another_lead->status ?> is In process. </div>
                                        <?php } ?>

                                        <div id="LeadDetails">
                                            <?php $this->load->view('Tasks/leadsDetails'); ?>
                                        </div>

                                        <div class="footer-support">
                                            <h2 class="footer-support"><button type="button" id="internal_dedupe" class="btn btn-info collapse" data-toggle="collapse" data-target="#old_leads" onclick="viewOldHistory('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">INTERNAL DEDUPE&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
                                        </div>
                                        <div id="old_leads" class="collapse">
                                            <div id="oldTaskHistory"></div>
                                        </div>

                                        <div class="footer-support">
                                            <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#cibil_details" onclick="ViewCibilStatement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">CREDIT BUREAU&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
                                        </div>
                                        <div id="bankStatement"></div>

                                        <div id="cibil_details" class="collapse">
                                            <?php if (empty($leadDetails->lead_stp_flag) && empty($leadDetails->cibil) && $leadDetails->user_type == "NEW" && in_array($leadDetails->lead_status_id, array(1, 2, 3)) && ($leadDetails->lead_screener_assign_user_id == user_id && agent ==  'CR1' || agent == 'CR2'  || agent == 'CA') && empty($leadDetails->check_cibil_status)) { ?>
                                                <div id="btndivCheckCibil">
                                                    <div id="checkCustomerCibil" style="background:#fff !important;">
                                                        <button class="btn btn-primary" id="btnCheckCibil" onclick="checkCustomerCibil('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Fetch CRIF</button>
                                                    </div>
                                                </div>
                                            <?php } else if (empty($leadDetails->lead_stp_flag) && $leadDetails->lead_status_id < 13 && ($leadDetails->lead_credit_assign_user_id == user_id || agent == 'CR1' || agent == 'CR2' || agent == 'CR3' || agent == 'CA') && empty($leadDetails->check_cibil_status)) {
                                            ?>
                                                <div id="btndivCheckCibil">
                                                    <div id="checkCustomerCibil" style="background:#fff !important;">
                                                        <button class="btn btn-primary" id="btnCheckCibil" onclick="checkCustomerCibil('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Fetch CRIF</button>
                                                    </div>
                                                </div>
                                            <?php } else if (in_array($leadDetails->check_cibil_status, array(0, 1)) && in_array($leadDetails->lead_status_id, array(14, 19)) && in_array(agent, array("CO1", "CO2", "CO3", "CO4"))) {
                                            ?>
                                                <div id="btndivCheckCibil">
                                                    <div id="checkCustomerCibil" style="background:#fff !important;">
                                                        <button class="btn btn-primary" id="btnCheckCibil" onclick="checkCustomerCibil('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Fetch CRIF</button>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div id="cibilStatement"></div>
                                        </div>
                                        <div class="footer-support">
                                            <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#leadSanctionFollowupLogs" onclick="leadSanctionFollowupLogs('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction Followup Logs&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
                                        </div>
                                        <div id="leadSanctionFollowupLogs"></div>
                                        <div class="footer-support">
                                            <h2 class="footer-support"><button type="button" id="leadHistoryLogs" class="btn btn-info collapse" data-toggle="collapse" data-target="#leadLogs" onclick="leadHistoryLogs('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Application Log History&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
                                        </div>

                                        <div id="leadLogs"></div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="ApplicationSaction">
                                        <div>
                                            <?php $this->load->view('Tasks/application'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="DocumentSaction">
                                        <input type="hidden" name="leadIdForDocs" id="leadIdForDocs">
                                        <div id="documents" class="show">
                                            <div id="btndivUploadDocs">
                                                <?php if (in_array(agent, ['CA', 'AH']) || (in_array(agent, ["CR1", "CR2"]) && in_array($leadDetails->stage, ["S2", "S3", "S5", "S6", "S11", "S21"]) && $leadDetails->customer_bre_run_flag == 0) || $leadDetails->lead_status_id < 14 || $leadDetails->lead_status_id == 30 || $leadDetails->lead_status_id == 25) { ?>
                                                    <div style="background:#fff !important;">
                                                        <?php if (($leadDetails->lead_screener_assign_user_id == user_id && agent == "CR1" && in_array($leadDetails->stage, ["S2", "S3", "S21"])) || ($leadDetails->lead_credit_assign_user_id == user_id && agent == "CR2" && in_array($leadDetails->stage, ["S5", "S6", "S11"])) || in_array(agent, ["CA", "AH"]) || $leadDetails->lead_status_id < 14 || $leadDetails->lead_status_id == 30 || $leadDetails->lead_status_id == 25) { ?>

                                                            <p id="selectDocsTypes" style="text-transform:uppercase; margin-top:20px;padding-left: 10px;padding-bottom: 15px;">
                                                                <?php
                                                                $i = 1;
                                                                foreach ($docs_master->result() as $row) :
                                                                    if ($row->docs_type == 'DIGILOCKER') {
                                                                        continue;
                                                                    }
                                                                    if ($leadDetails->lead_stp_flag == 1 && in_array($row->docs_type, ['AADHAR', 'PAN'])) {
                                                                        continue;
                                                                    }
                                                                ?>
                                                                    <label class="radio-inline">
                                                                        <input type="radio" name="selectdocradio" id="selectdocradio<?= $i ?>" value="<?= $row->docs_type ?>">&nbsp;<?= $row->docs_type ?>&nbsp;<strong class="required_Fields"><?php
                                                                                                                                                                                                                                                if ($row->docs_required == 1) {
                                                                                                                                                                                                                                                    echo "*";
                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                ?></strong>
                                                                    </label>
                                                                <?php
                                                                    $i++;
                                                                endforeach;
                                                                ?>
                                                            </p>
                                                        <?php } ?>
                                                    </div>
                                                    <?php if (agent == 'CA' || agent == 'CR1' || agent == 'CR2') { ?>
                                                        <div style="background:#fff !important;">
                                                            <!--<p><b>Generate link for customer Documents</b>&nbsp;&nbsp;<input type="checkbox" name="email_send" id="email_send" value="1" onclick="setSendLink('<?= $leadDetails->lead_id ?>','<?= $leadDetails->customer_id ?>',1)">&nbsp;<label for="email_send"><b>Send Email</b></label>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="sms_send" id="sms_send" value="1" onclick="setSendLink('<?= $leadDetails->lead_id ?>','<?= $leadDetails->customer_id ?>',2)">&nbsp;<label for="sms_send"><b>Send SMS</b></label></p>-->
                                                        </div>
                                                    <?php } ?>
                                                    <div class="row" id="docsform">
                                                        <?php $this->load->view('Document/docs'); ?>
                                                    </div>
                                                <?php } //else {
                                                ?>
                                                <div class="footer-support">
                                                    <h2 class="footer-support" style="margin-top: 0px;">
                                                        <button type="button"  id="uploadedDocumentsID" class="btn btn-info collapse" onclick="getCustomerDocs('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', '<?= $leadDetails->customer_id ?>')" data-toggle="collapse" data-target="#Uploaded-Documents">Uploaded Documents&nbsp;<i class="fa fa-angle-double-down"></i></button>
                                                    </h2>
                                                </div>
                                                <div id="Uploaded-Documents" class="collapse" style="background: #fff !important;">
                                                    <div id="docsHistory"></div>
                                                </div>
                                                <?php //}
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="PersonalDetailSaction">
                                        <div style="border : solid 1px #ddd;margin-bottom: 20px; background: #fff;">
                                            <?php $this->load->view('Personal/personal'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="BankingDetailSaction">
                                        <div style="border : solid 1px #ddd;margin-bottom: 20px; background: #fff;">
                                            <?php $this->load->view('Disbursal/banking'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="Verification">
                                        <div id="divVerification">
                                            <?php $this->load->view('Verification/verification'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="CAMSheetSaction">
                                        <!-- <a class="btn btn-primary" href="#" id="urlViewCAM" target="_blank" title="View" style="width: 30px;height: 30px;padding: 5px 0px 0px 0px;"><i class="fa fa-eye"> </i>
                                </a>
                                <a class="btn btn-primary" href="#" id="urlDownloadCAM" style="width: 30px;height: 30px;padding: 5px 0px 0px 0px;"><i class="fa fa-download"></i>
                                </a> -->
                                        <div class="camBorder">
                                            <div id="divCamDetails" product_id="<?= product_id ?>" company_id="<?= company_id ?>">
                                                <?php
                                                if (company_id == 1 && product_id == 1) {
                                                    $this->load->view('CAM/camPayday');
                                                }
                                                if (company_id == 1 && product_id == 2) {
                                                    $this->load->view('CAM/camLAC');
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="DisbursalSaction">
                                        <div id="disbursal">
                                            <?php $this->load->view('Disbursal/disbursal_new'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="RepaymentSaction">
                                        <div id="repay">
                                            <?php $this->load->view('Collection/repayment'); ?>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="BRESaction">
                                        <div id="bre">
                                            <?php $this->load->view('Bre/bre'); ?>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade" id="CollectionSaction">
                                        <div id="collection">
                                            <?php $this->load->view('Collection/collection'); ?>
                                        </div>
                                    </div>


                                    <div role="tabpanel" class="tab-pane fade" id="AuditSaction">
                                        <div id="audit">
                                            <?php $this->load->view('Audit/audit'); ?>
                                        </div>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button disabled style="background: #fff;border: none;"></button>
                                    <input type="hidden" name="customer_id" id="customer_id" value="<?= $leadDetails->customer_id ?>">
                                    <input type="hidden" name="status" id="status" value="<?= $leadDetails->status ?>">
                                    <input type="hidden" name="stage" id="stage" value="<?= $leadDetails->stage ?>">

                                    <?php
                                    //                            if ((agent == "CR1" || (agent == "CR2" && ($leadDetails->stage != "S10" || $leadDetails->stage == "S11")) || agent == "CA" || agent == "SA") || (agent == "CR3" && $leadDetails->stage == "S10") || (agent == "DS1" && $leadDetails->stage == "S13")) {
                                    if (true) {
                                    ?>
                                        <div id="btndivReject1">
                                            <div calss="row" style="border-top: solid 1px #ddd;text-align: center; padding-top : 20px; padding-bottom: 20px; background: #f3f3f3; overflow: auto;">
                                                <div class="col-md-12 text-center">

                                                    <?php /* if (!empty($leadDetails->lead_status_id) && !in_array($leadDetails->lead_status_id, array(9, 12, 14, 15, 16, 17, 18, 19))) { ?>
                                              <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                              <?php } */ 
                                              ?>
<?php if((agent == "CA") && in_array($_SESSION['isUserSession']['user_id'],array(92,93))){?>
   <button class="btn btn-warning lead-transfer-fp" style="background : #0a5e90 !important;" onclick="getTransferFp('<?=$leadDetails->lead_id ?>')" ?>Transfer To Fp</button>
<?php } ?>
                                                    <?php if (agent == 'AU') { ?>
                                                        <button class="btn btn-success approval-button" style="display:none;" onclick="ApprovalLoan()">Approve</button>
                                                    <?php }
                                                    // if ((agent == 'CR1') && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(2, 3)) && ($leadDetails->user_is_loanwalle == 1)) { 
                                                        if ((agent == 'CR1') && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(2, 3))) { 
                                                        ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php } else if ((agent == 'CA') && !empty($leadDetails->lead_rejected_assign_user_id) && $leadDetails->lead_rejected_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(9)) && in_array($leadDetails->lead_rejected_reason_id, array(7, 31))) { ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php 
                                                    // } else if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && ($leadDetails->user_is_loanwalle == 1 || $leadDetails->user_type == 'REPEAT' || $leadDetails->user_type == 'UNPAID-REPEAT')) { 
                                                    } else if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && ($leadDetails->user_is_loanwalle == 1 || $leadDetails->user_type == 'NEW' || $leadDetails->user_type == 'REPEAT' || $leadDetails->user_type == 'UNPAID-REPEAT')) { 
                                                        ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php } else if (agent == 'CR3' && in_array($leadDetails->lead_status_id, array(10))) { ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php } else if (agent == 'DS1' || agent == 'DS2' && !empty($leadDetails->lead_disbursal_assign_user_id)  && in_array($leadDetails->lead_status_id, array(30, 35, 37))) { ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php } else if (agent == 'DS2' && in_array($leadDetails->lead_status_id, array(13)) && !in_array($leadDetails->loan_status_id, array(14))) { ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                        <button class="btn btn-success" id="btn_disburse_send_back" onclick="disburseSendBack('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Send Back</button>
                                                    <?php } else if (agent == 'CA' && !in_array($leadDetails->stage, array('S16', 'S14', 'S30', 'S9'))) { ?>
                                                        <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button>
                                                    <?php //} else if (in_array(agent, array('AH', 'AM')) && in_array($leadDetails->lead_status_id, array(45, 46))) { ?>

                                                        <?php //if ((agent == 'AH') && in_array($leadDetails->lead_status_id, array(46))) { ?>
                                                            <!-- <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button> -->
                                                        <?php //} else if (in_array($leadDetails->lead_status_id, array(45))) { ?>
                                                            <!-- <button class="btn btn-success lead-audit-hold-button" onclick="auditHoldLeadsRemark()">Audit Hold</button> -->
                                                        <?php //} ?>

                                                        <!-- <button class="btn btn-success" id="btn_audit_send_back" onclick="auditSendBackRemark('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Application Send Back</button> -->
                                                        <!-- <button class="btn btn-success" id="auditLeadRecommend" onclick="auditLeadRecommend('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Audit Recommend</button> -->
                                                    <?php //} else if ((agent == 'AH') && in_array($leadDetails->lead_status_id, array(44))) { ?>
                                                        <!-- <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button> -->
                                                        <!-- <button class="btn btn-success" id="btn_audit_send_back" onclick="auditSendBackRemark('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Send Back</button> -->
                                                        <!-- <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button> -->
                                                    <?php } ?>

                                                    <?php if (agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(2))) { ?>
                                                        <button class="btn btn-success lead-hold-button" onclick="holdLeadsRemark()">Hold</button>
                                                    <?php } else if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5))) { ?>
                                                        <button class="btn btn-success lead-hold-button" onclick="holdLeadsRemark()">Hold</button>
                                                    <?php } else if (agent == 'DS1' || agent == 'DS2' && !empty($leadDetails->lead_disbursal_assign_user_id)  && in_array($leadDetails->lead_status_id, array(13, 30, 35))) { ?>
                                                        <button class="btn btn-success lead-hold-button" onclick="holdLeadsRemark()">Hold</button>
                                                    <?php }  ?>

                                                    <?php if (agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && ($leadDetails->application_status == 1) && in_array($leadDetails->lead_status_id, array(2, 3))) { ?>
                                                        <button class="btn btn-success" id="LeadRecommend" onclick="leadRecommend('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Recommend</button>
                                                    <?php } else if (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 8, 11)) && ($camDetails->cam_status == 1 && $leadDetails->lead_fi_residence_status_id != 1 && $leadDetails->lead_fi_office_status_id != 1)) { ?>
                                                        <button class="btn btn-success" onclick="applicationRecommendation('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Recommend</button>
                                                    <?php } else if (agent == 'CR3' && in_array($leadDetails->lead_status_id, array(10))) { ?>
                                                        <button class="btn btn-success" id="btn_send_back" onclick="leadSendBack('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Send Back</button>
                                                    <?php } else if (agent == 'DS1' ||  agent == 'DS2' && !empty($leadDetails->lead_disbursal_assign_user_id)  && in_array($leadDetails->lead_status_id, array(30, 35, 37))) { ?>
                                                        <button class="btn btn-success" onclick="addRemarksToggle('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Recommend</button>
                                                    <?php }
                                                    if ((agent == 'DS1' || agent == 'DS2' || agent == 'CA') && in_array($leadDetails->lead_status_id, array(2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 21, 22, 26, 27, 28, 29, 30, 35, 37, 43, 44, 45, 46, 47))) {  ?>
                                                        <!--<button class="btn btn-success audit-success" onclick="preAuditToggle('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',1)" >Disbursed</button>-->
                                                        <button class="btn btn-primary" id="allowDisbursalToBank" style="text-align: center; padding-left: 50px; padding-right: 50px; font-weight: bold;">Disburse</button>
                                                    <?php }
                                                    if (agent == 'CO1' || agent == 'CO4') {  ?>
                                                        <button class="btn btn-success" style="background-color:#32bd8e !important;" onclick="postAuditToggle('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',2)">Send to Audit</button>
                                                    <?php }
                                                    if ((agent == 'CR2' && $leadDetails->user_type == 'REPEAT' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && ($camDetails->cam_status == 1 && $leadDetails->lead_fi_residence_status_id != 1 && $leadDetails->lead_fi_office_status_id != 1)) { ?>
                                                    <?php
                                                        if ($leadDetails->user_type == 'REPEAT' && $leadDetails->sanctionedAmount < 40000) {
                                                        ?>
                                                            <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button>
                                                        <?php }?>
                                                        <!-- <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button> -->
                                                    <?php } else if ((agent == 'CR3') && in_array($leadDetails->lead_status_id, array(10)) && ($camDetails->cam_status == 1 && $leadDetails->lead_fi_residence_status_id != 1 && $leadDetails->lead_fi_office_status_id != 1)) { ?>
                                                        <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button>
                                                        <?php
                                                        // echo '<pre>';print_r($leadDetails);
                                                        // if ($leadDetails->user_type == 'REPEAT' && $dpd < 11 && $leadDetails->sanctionedAmount < 40000) {
                                                        ?>
                                                            <!-- <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button> -->
                                                        <?php //} else { ?>
                                                            <!-- <button class="btn btn-primary" style="background : #0a5e90 !important;" id="auditNew" onclick="AuditFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Audit New</button> -->
                                                        <?php //} ?>
                                                    <?php //} else if ((agent == 'AH') && in_array($leadDetails->lead_status_id, array(47)) && ($camDetails->cam_status == 1 && $leadDetails->lead_fi_residence_status_id != 1 && $leadDetails->lead_fi_office_status_id != 1)) { ?>
                                                        <!-- <button class="btn btn-success reject-button" onclick="RejectedLoan()">Reject</button> -->
                                                        <!-- <button class="btn btn-success" id="btn_audit_send_back" onclick="auditSendBackRemark('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Application Send Back</button> -->
                                                        <!-- <button class="btn btn-primary" style="background : #0a5e90 !important;" onclick="sanctionFeedback('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Sanction</button> -->
                                                    <?php } ?>
                                                    <?php if (agent == 'CR1' && !empty($leadDetails->lead_rejected_assign_user_id) && $leadDetails->lead_rejected_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(9)) && in_array($leadDetails->lead_rejected_reason_id, array(7, 31)) && $leadDetails->lead_rejected_assign_counter <= NON_CONTACTABLE_ROTATE_COUNTER) { ?>
                                                        <button class="btn btn-success" id="rejectedLeadMoveToProcess">Move to In-Process</button>
                                                    <?php } ?>
                                                    <?php
                                                    if ((agent == 'AC2' || agent == 'CA') && in_array($leadDetails->lead_status_id, array(14))) { // && !in_array($leadDetails->loan_disbursement_trans_status_id, array(1))
                                                        $camp_disbursal_date = strtotime(date("Y-m-d", strtotime("+10 day", strtotime($leadDetails->lead_disbursal_approve_datetime))));
                                                        $camp_current_datetime = strtotime(date("Y-m-d"));
                                                        if (($camp_disbursal_date > $camp_current_datetime) || agent == 'CA') {
                                                    ?>
                                                            <button class="btn btn-warning  lead-sanction-button" style="background : #0a5e90 !important;" onclick="addRemarksToggle('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" ?>Waive OFF</button>
                                                    <?php
                                                        }
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                </div>

                                <div id="divExpendReason" class="marging-footer-verifa">
                                    <div style="margin-top: 15px">
                                        <input type="hidden" name="audit" id="audit_id" class="audit_id">
                                        <div class="col-md-3 text-center">
                                            <select class="js-select2 form-control inputField" name="resonForReject" id="resonForReject" autocomplete="off" style="float: right;width: 100% !important;height: 43px !important;">
                                            </select>
                                        </div>
                                        <div class="col-md-7 text-center">
                                            <input type="text" class="form-control inputField" style="width:100% !important;" name="reject_remark" id="reject_remark" autocomplete="off" placeholder="Enter Remarks">
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <button class="btn btn-primary" id="btnRejectApplication" onclick="ResonForRejectLoan()">Lead Reject</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="divExpendApproval" class="marging-footer-verifa" style="display:none;">
                                    <div style="margin-top: 15px">
                                        <input type="hidden" name="audit" id="audit_id" class="audit_id">
                                        <div class="col-md-7 text-center">
                                            <textarea class="form-control inputField" name="comment" id="comment_id" style="width:100% !important;height:100px !important;" autocomplete="off" placeholder="Enter Comment"></textarea>
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <button class="btn btn-primary" id="btnApprovalApplication" onclick="ResonForApprovalLoan()">Lead Approve</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="divExpendReason2" class="marging-footer-verifa">
                                    <div style="margin-top: 15px">
                                        <!-- <div class="col-md-3 text-left">&nbsp;</div> -->
                                        <div class="col-md-7 text-left">
                                            <input type="text" class="form-control inputField" name="remark" id="hold_remark" placeholder="Enter Remarks" style="width:100% !important;">
                                        </div>

                                        <div class="col-md-3 text-left">
                                            <?php
                                            $mindate = date("Y-m-d");
                                            $mintime = date("h:i");
                                            $min = $mindate . "T" . $mintime;
                                            $maxdate = date("Y-m-d", strtotime("+5 Days"));
                                            $maxtime = date("h:i");
                                            $max = $maxdate . "T" . $maxtime;
                                            ?>
                                            <input type="datetime-local" class="form-control inputField" name="holdDurationDate" id="holdDurationDate" placeholder="Enter Remarks" min="<?php echo $min ?>" max="<?php echo $max ?>" style="width:100% !important;">
                                        </div>

                                        <div class="col-md-2 text-left">
                                            <button class="btn btn-primary" id="btnRejectApplication" onclick="saveHoldleads('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Lead Hold</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="auditExpendReason2" class="marging-footer-verifa" style="display:none">
                                    <div style="margin-top: 15px">
                                        <div class="col-md-7 text-left">
                                            <input type="text" class="form-control inputField" name="remark" id="audit_hold_remark" placeholder="Enter Remarks" style="width:100% !important;">
                                        </div>

                                        <div class="col-md-3 text-left">
                                            <?php
                                            $mindate = date("Y-m-d");
                                            $mintime = date("h:i");
                                            $min = $mindate . "T" . $mintime;
                                            $maxdate = date("Y-m-d", strtotime("+5 Days"));
                                            $maxtime = date("h:i");
                                            $max = $maxdate . "T" . $maxtime;
                                            ?>
                                            <input type="datetime-local" class="form-control inputField" name="holdDurationDate" id="auditHoldDurationDate" placeholder="Enter Remarks" min="<?php echo $min ?>" max="<?php echo $max ?>" style="width:100% !important;">
                                        </div>

                                        <div class="col-md-2 text-left">
                                            <button class="btn btn-primary" id="btnAuditHold" onclick="saveAuditHoldleads('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Lead Hold</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div id="divExpendReason3" class="marging-footer-verifa">
                                <div style="margin-top: 15px">
                                    <div class="col-md-3 text-left" id="users_list" style="display:none"></div>
                                    <div class="col-md-9 text-left">
                                        <textarea class="form-control inputField" name="remark" id="own_remark" placeholder="Enter Remarks" style="width:100% !important;"></textarea>
                                    </div>
                                    <div class="col-md-2 text-left" id="btn_own_reason"></div>
                                </div>
                            </div>
                        <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <?php $this->load->view('Layouts/footer') ?>
        <div class="modal fade" id="bootstrap_data_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content clearfix">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Data Response</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="payday_model_body">No record found.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
    </section>
</section>
<script>
    var csrf_token = $("input[name=csrf_token]").val();
</script>
