<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// $getVerificationdata = getVerificationdata('tbl_verification', $leadDetails->lead_id);
// $verification_status = array(1 => "PENDING", 2 => "POSITIVE", 3 => "NEGATIVE");
// $relation_type = array(0 => "PENDING", 1 => "PARENTS", 2 => "RELATIVE", 3 => "FRIENDS", 4 => "COLLEAGUE", 5 => "OTHER");
// $is_field_insvestigation = 'disabled';
// if ($leadDetails->stage == "S5" || $leadDetails->stage == "S6" || $leadDetails->stage == "S11") {
//     $is_field_insvestigation = '';
// }
?>
<!--<div id="verification_details"></div>-->
<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#BANKANALYSIS" onclick="getDataBankingAnalysis('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">BANKING ANALYSIS&nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->

<!--<div id="BANKANALYSIS" class="collapse"> -->
<!--    <div id="div_bank_statement_analysis">-->
<!--        <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) || agent == "CA") && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && empty($leadDetails->lead_stp_flag)) { ?>-->
<!--            <button class="btn btn-info" id="analyse_bank_statement" onclick="analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Analyse Bank Statement</button>-->
<!--        <?php } ?>-->
<!--    </div>-->
<!--    <div id="viewBankingAnalysisApiData"></div>-->
<!--</div>-->

<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#BANKACCOUNT" onclick="getDataBankingAccountAggregator('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">BANK ACCOUNT AGGREGATOR&nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="BANKACCOUNT" class="collapse">   -->
<!--    <div id="viewBankAccountAgrregator"></div>-->
<!--</div>-->

<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FINBOXDEVICE"  onclick="getFinBoxDevice('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">FinBox Device &nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="FINBOXDEVICE">-->
<!--    <div id="viewFinBoxDevice"> -->
<!--    </div>-->
<!--</div>-->
<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FINBOXBANKINGDEVICE"  onclick="getFinBoxBankingDeviceData('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">FinBox Banking Device &nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="FINBOXBANKINGDEVICE" class="collapse">-->
<!--    <div id="div_finbox_bank_statement_analysis">-->
<!--        <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) || agent == "CA") && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && empty($leadDetails->lead_stp_flag)) { ?>-->
<!--            <button class="btn btn-info" id="finbox_analyse_bank_statement" onclick="finbox_analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Finbox Bank Statement</button>-->
<!--        <?php } ?>-->
<!--    </div>      -->
<!--    <div id="viewFinBoxBankingDeviceData"> -->
<!--    </div>-->
<!--</div>-->
<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#SMSANALYZER" onclick="setDataSMSAnalyzer('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">SMS Analyzer&nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="SMSANALYZER" class="collapse"> -->
<!--    <div id="div_sms_analizer">-->
<!--        <?php if (agent == "CA") { ?>-->
<!--            <button class="btn btn-info" id="sms_analyzer" onclick="getDataSMSAnalyzer('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">SMS Analyzer</button>-->
<!--        <?php } ?>-->
<!--    </div>-->
<!--    <div id="viewSMSAnalyzerApiData"></div>-->
<!--</div>-->


<?php
$getVerificationdata = getVerificationdata('tbl_verification', $leadDetails->lead_id);
$verification_status = array(1 => "PENDING", 2 => "POSITIVE", 3 => "NEGATIVE");
$relation_type = array(0 => "PENDING", 1 => "PARENTS", 2 => "RELATIVE", 3 => "FRIENDS", 4 => "COLLEAGUE", 5 => "OTHER");
$is_field_insvestigation = 'disabled';
if ($leadDetails->stage == "S5" || $leadDetails->stage == "S6" || $leadDetails->stage == "S11") {
    $is_field_insvestigation = '';
}
?>
<div id="verification_details"></div>
<?php /*
  <div class="footer-support"><h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#RESIDENCE">FIELD VERIFICATION - RESIDENCE&nbsp;<i class="fa fa-angle-double-down"></i></button></h2></div>
  <div id="RESIDENCE" class="collapse"> <!------ table for  RESIDENCE section ----------------------->
  <div class="table-responsive">
  <table class="table table-hover table-striped table-bordered">
  <tr>
  <th>Initiated On</th>
  <td colspan='3'><?= (!empty($getVerificationdata['residence_initiated_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['residence_initiated_on'])) : '-'); ?></td>
  </tr>
  <tr>
  <th>Met with</th>
  <td><?= !empty($getVerificationdata['met_with']) ? $getVerificationdata['met_with'] : '-'; ?></td>
  <th>Relation</th>
  <td><?= !empty($relation_type[$getVerificationdata['relation']]) ? $relation_type[$getVerificationdata['relation']] : '-'; ?></td>
  </tr>
  <tr>
  <tr>
  <th>Residence Type </th>
  <td><?= !empty($getVerificationdata['residence_type']) ? $getVerificationdata['residence_type'] : '-'; ?></td>
  <th>House Type</th>
  <td><?= !empty($getVerificationdata['office_residence_house_type']) ? $getVerificationdata['office_residence_house_type'] : '-'; ?></td>
  </tr>
  <tr>
  <tr>
  <th>Ease of Identification</th>
  <td><?= !empty($getVerificationdata['office_residence_ease_of_identification']) ? $getVerificationdata['office_residence_ease_of_identification'] : '-'; ?></td>
  <th>Locality</th>
  <td><?= !empty($getVerificationdata['office_residence_locality']) ? $getVerificationdata['office_residence_locality'] : '-'; ?></td>
  </tr>
  <tr>
  <tr>
  <th>Residing since</th>
  <td><?= !empty($getVerificationdata['office_residence_residing_since']) ? $getVerificationdata['office_residence_residing_since'] : '-'; ?></td>
  <th>Total members in family</th>
  <td><?= !empty($getVerificationdata['office_residence_total_members_in_family']) ? $getVerificationdata['office_residence_total_members_in_family'] : '-'; ?></td>
  </tr>
  <tr>
  <tr>
  <th>Earning members in family</th>
  <td><?= !empty($getVerificationdata['office_residence_earn_ng_members_in_family']) ? $getVerificationdata['office_residence_earn_ng_members_in_family'] : '-'; ?></td>
  <th>Living standard</th>
  <td><?= !empty($getVerificationdata['office_residence_living_standard']) ? $getVerificationdata['office_residence_living_standard'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Neighbour check</th>
  <td><?= !empty($getVerificationdata['office_residence_neighbour_check']) ? $getVerificationdata['office_residence_neighbour_check'] : '-'; ?></td>
  <th>Geo-cordinates</th>
  <td><?= !empty($getVerificationdata['office_residence_geo_cordinates']) ? '-' : '-'; ?></td>
  </tr>
  <tr>
  <th>Visit On</th>
  <td><?= !empty($getVerificationdata['office_residence_visit_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_residence_visit_on'])) : '-'; ?></td>
  <th>Remarks</th>
  <td><?= !empty($getVerificationdata['office_residence_remarks']) ? $getVerificationdata['office_residence_remarks'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Document verified</th>
  <td><?= !empty($getVerificationdata['office_residence_document_verified']) ? $getVerificationdata['office_residence_document_verified'] : '-'; ?></td>
  <th>Photo of Residence</th>
  <td><?= !empty($getVerificationdata['office_residence_photo']) ? "YES" : '-'; ?></td>
  </tr>
  <tr>
  <th>Received On</th>
  <td><?= !empty($getVerificationdata['received_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['received_on'])) : '-'; ?></td>
  <th> Report Status</th>
  <td><?= !empty($verification_status[$getVerificationdata['office_residence_status']]) ? $verification_status[$getVerificationdata['office_residence_status']] : '-'; ?></td>
  </tr>
  </table>
  </div><!-- end section for the residence section ----------------->
  </div>

  <div class="footer-support">
  <h2 class="footer-support">
  <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#OFFICE">FIELD VERIFICATION - OFFICE&nbsp;<i class="fa fa-angle-double-down"></i></button>
  </h2>
  </div>

  <div id="OFFICE" class="collapse">
  <!------ table for  OFFICE section ----------------------->
  <div class="table-responsive">
  <table class="table table-hover table-striped table-bordered">
  <tr>
  <th>Initiated On</th>
  <td><?= (!empty($getVerificationdata['office_initiated_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_initiated_on'])) : '-'); ?></td>
  <th>Received On</th>
  <td><?= (!empty($getVerificationdata['office_received_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_received_on'])) : '-'); ?></td>
  </tr>
  <tr>
  <th>Met with</th>
  <td><?= !empty($getVerificationdata['office_met_with']) ? $getVerificationdata['office_met_with'] : '-'; ?></td>
  <th>Relation</th>
  <td><?= !empty($getVerificationdata['office_relation']) ? $getVerificationdata['office_relation'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Entry Allowed </th>
  <td><?= !empty($getVerificationdata['office_entry_allowed']) ? $getVerificationdata['office_entry_allowed'] : '-'; ?></td>
  <th>Employer Name</th>
  <td><?= !empty($getVerificationdata['office_employer_name']) ? $getVerificationdata['office_employer_name'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Company Signboard sighted</th>
  <td><?= !empty($getVerificationdata['office_company_signboard_sighted']) ? $getVerificationdata['office_company_signboard_sighted'] : '-'; ?></td>
  <th>Locality</th>
  <td><?= !empty($getVerificationdata['office_locality']) ? $getVerificationdata['office_locality'] : '-'; ?></td>
  </tr>
  <tr>
  <th>No. of staff sighted</th>
  <td><?= !empty($getVerificationdata['office_no_of_staff_sighted']) ? $getVerificationdata['office_no_of_staff_sighted'] : '-'; ?></td>
  <th>Employee strength</th>
  <td><?= !empty($getVerificationdata['office_employee_strength']) ? $getVerificationdata['office_employee_strength'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Employed since</th>
  <td><?= !empty($getVerificationdata['office_employed_since']) ? date('m-Y', strtotime($getVerificationdata['office_employed_since'])) : '-'; ?></td>
  <th>Geo-cordinates</th>
  <td><?= !empty($getVerificationdata['office_geo_cordinates']) ? '-' : '-'; ?></td>
  </tr>
  <tr>
  <th>Visit On</th>
  <td><?= !empty($getVerificationdata['office_visit_on']) ? date('d-m-Y H:i:s', strtotime($getVerificationdata['office_visit_on'])) : '-'; ?></td>
  <th>Remarks</th>
  <td><?= !empty($getVerificationdata['office_remarks']) ? $getVerificationdata['office_remarks'] : '-'; ?></td>
  </tr>
  <tr>
  <th>Document verified</th>
  <td><?= !empty($getVerificationdata['office_document_verified']) ? $getVerificationdata['office_document_verified'] : '-'; ?></td>
  <th>Photo of Office</th>
  <td><?= !empty($getVerificationdata['office_photo_of_office']) ? "YES" : '-'; ?></td>
  </tr>
  <tr>
  <th>Report Status</th>
  <td colspan="3"><?= !empty($verification_status[$getVerificationdata['office_report_status']]) ? $verification_status[$getVerificationdata['office_report_status']] : '-'; ?></td>
  </tr>
  </table>
  </div><!----- end section for the OFFICE section ----------------->
  </div>
 */ ?>

<style>
    .download-links {
        display: flex;
    }

    .download-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #0066cc;
        transition: color 0.3s ease;
        margin-right: 30px;
        margin-left: 30px;
    }

    .download-link:hover {
        color: #004499;
    }

    .icon {
        font-size: 35px;
    }
</style>


<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#BANKANALYSIS" onclick="getDataBankingAnalysis('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">BANKING ANALYSIS&nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div>

<div id="BANKANALYSIS" class="collapse">
    <div id="div_bank_statement_analysis">
        <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) || agent == "CA") && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && empty($leadDetails->lead_stp_flag)) { ?>
            <button class="btn btn-info" id="analyse_bank_statement" onclick="analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Analyse Bank Statement</button>
        <?php } ?>
    </div>
    <div id="viewBankingAnalysisApiData"></div>
</div>



<!-- <div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" data-toggle="collapse" onclick="checkAACurrentStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-target="#ACCOUNTAGRIGATOR">ACCOUNT AGGREGATOR &nbsp;<i class="fa fa-angle-double-down"></i></button>
    </h2>
</div> -->

<div id="ACCOUNTAGRIGATOR" class="collapse">
    <div>
        <table class="table">
            <tbody>
                <tr id="consentRequest" style="display:none">
                    <th></th>
                    <th>
                        <a class="btn btn-primary" id="AAconsentRequestBtn" href="javascript:void(0)" onclick="sendAAconsentRequest('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Send Consent Request for Bank Statement </a>

                        <a class="btn btn-primary" style="display:none" id="AAconsentRequestStatusBtn" href="javascript:void(0)" onclick="AAconsentRequestStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Check Consent Request Status</a>

                        <button class="btn btn-primary btnLoading" style="display:none" type="button" disabled>
                            <span class="fa fa-spinner" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </th>
                    <th></th>
                </tr>
                <tr id="FI_request_form" style="display:none">
                    <td colspan="3">
                        <div class="row" style="padding: 20px;">
                            <div class="col-md-12 col-sm-12" style="padding-bottom: 0px;">
                                <h3>Financial Information request</h3>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label>From Date</label>
                                <div class="form-group">
                                    <input readonly="" type="text" class="form-control" name="sfd" id="sfd" autocomplete="off" value="">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label>From to</label>
                                <div class="form-group">
                                    <input readonly="" type="text" class="form-control" name="sed" id="sed" autocomplete="off" value="">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label style="opacity:0">Button</label>
                                <div class="form-group">
                                    <a class="btn btn-primary" href="javascript:void(0)" onclick="AA_FIRequest('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Submit</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr id="FI_request_status" style="display:none">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="AA_FIRequest_status('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Get Financial Information Status</a>
                    </td>
                </tr>
                <tr id="bankAnalysisBox" style="display:none">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="get_bankAnalysis('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Bank Statement Analysis</a>
                    </td>
                </tr>
                <tr id="bankStatementBox" style="display:none;background: #dfdffd;">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="get_bankStatement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Get Bank Statement</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>



<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FINBOXDEVICE"  onclick="getFinBoxDevice('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">FinBox Device &nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="FINBOXDEVICE">-->
<!--    <div id="viewFinBoxDevice"> -->
<!--    </div>-->
<!--</div>-->
<!--<div class="footer-support">-->
<!--    <h2 class="footer-support">-->
<!--        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FINBOXBANKINGDEVICE"  onclick="getFinBoxBankingDeviceData('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">FinBox Banking Device &nbsp;<i class="fa fa-angle-double-down"></i></button>-->
<!--    </h2>-->
<!--</div>-->
<!--<div id="FINBOXBANKINGDEVICE" class="collapse">-->
<!--    <div id="div_finbox_bank_statement_analysis">-->
<!--        <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) || agent == "CA") && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && empty($leadDetails->lead_stp_flag)) { ?>-->
<!--        <button class="btn btn-info" id="finbox_analyse_bank_statement" onclick="finbox_analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Finbox Bank Statement</button>-->
<!--         <?php } ?>-->
<!--     </div>      -->
<!--    <div id="viewFinBoxBankingDeviceData"> -->
<!--    </div>-->
<!--</div>-->
