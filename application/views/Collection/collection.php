<?php
$getVerificationdata = getVerificationdata('tbl_verification', $leadDetails->lead_id);
?>
<!------- table structure for varification form ----------->

<div class="table-responsive">

    <?php if (in_array(agent, ["CO1", "CO2", "CO3", "CR1", "CR2", "CR3", "CO4", "CC"]) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
        <div class="footer-support">
            <h2 class="footer-support">
                <button type="button" id="btnAddCollectionFollowup" class="btn btn-info collapse" onclick="get_collection_followup_master_lists()" data-toggle="collapse" data-target="#AddCollectionFollowup" style="width: 25% !important;">Add Collection Followup&nbsp;<i class="fa fa-angle-double-down"></i></button>
            </h2>
        </div>
    <?php } ?>

    <!------ Add lead collection followup ----------------------->

    <div id="AddCollectionFollowup" class="collapse"> 
        <div class="col-md-12 alert alert-dismissible alert-info" style="margin-bottom: 20px;">
            <div id="followup_type"></div>
        </div>
        <!--class="collapse"-->
        <div id="UpdateCollectionFollowup_1"> 

            <form id="addLeadCollectionFollowup" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <label class="labelField">Followup Status&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" id="collection_followup_status_id" name="collection_followup_status_id"></select>
                </div>

                <div class="col-md-6" style="margin-bottom: 15px;">
                    <label class="labelField">Next Followup Date Time</label>

                    <?php
                    $mindate = date("Y-m-d");
                    $mintime = date("h:i");
                    $min = $mindate . "T" . $mintime;
                    $maxdate = date("Y-m-d", strtotime("+5 Days"));
                    $maxtime = date("h:i");
                    $max = $maxdate . "T" . $maxtime;
                    ?>
                    <input type="datetime-local" class="form-control inputField" name="collection_next_schedule_date" id="collection_next_schedule_date" min="<?php echo $min ?>" max="<?php echo $max ?>" placeholder="Next Followup DateTime">
                </div>

                <div class="col-md-12">
                    <label class="labelField">Remarks&nbsp;<strong class="required_Fields">*</strong> </label>
                    <textarea class="form-control " rows="5" cols="111" maxlength="500" id="followup_remarks" name="followup_remarks" autocomplete="off"></textarea>
                    <p style="float:right"><span id="inputWordCount">0</span>/500</p>
                </div>
            </form>
            <div class="col-md-12" id="btnCollectionFollowup" style="margin: 10px;">
                <button id="saveCollectionFollowup" class="btn btn-success lead-sanction-button">Save</button> 
            </div>
        </div>

        <div id="UpdateCollectionFollowup_2"> 

            <form id="FormLeadCollectionFollowupSMS" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <label class="labelField">SMS Templates&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" style="width: 76% !important;" id="collection_followup_sms_primary_id" name="collection_followup_sms_primary_id" onchange="get_collection_followup_content(2, this, <?= $leadDetails->lead_id ?>)"></select>
                </div>

                <div class="col-md-12">
                    <label class="labelField">SMS Content&nbsp;<strong class="required_Fields">*</strong> </label>
                    <textarea class="form-control" rows="5" cols="111" id="collection_followup_sms_content" name="collection_followup_sms_content" readonly>NA</textarea>
                </div>
            </form>
            <div class="col-md-12" id="btnCollectionFollowup" style="margin: 10px;">
                <button id="saveCollectionFollowupSMS" class="btn btn-success lead-sanction-button">Send SMS</button> 
            </div>
        </div>

        <div id="UpdateCollectionFollowup_3"> 

            <form id="FormLeadCollectionFollowupWhatsapp" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <label class="labelField">Whatsapp Templates&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" style="width: 76% !important;" id="collection_followup_whatsapp_title" name="collection_followup_whatsapp_title" onchange="get_collection_followup_content(2, this, <?= $leadDetails->lead_id ?>)"></select>
                </div>

                <div class="col-md-12">
                    <label class="labelField">Whatsapp Content&nbsp;<strong class="required_Fields">*</strong> </label>
                    <textarea class="form-control" rows="5" cols="111" id="collection_followup_whatsapp_content" name="collection_followup_whatsapp_content" readonly>NA</textarea>
                </div>
            </form>
            <div class="col-md-12" id="btnCollectionFollowup" style="margin: 10px;">
                <button id="saveCollectionFollowupWhatsapp" class="btn btn-success lead-sanction-button">Send Whatsapp</button> 
            </div>
        </div>

        <div id="UpdateCollectionFollowup_4"> 

            <form id="FormLeadCollectionFollowupEmail" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
                <div class="col-md-12">
                    <label class="labelField">Email Templates&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" style="width: 76% !important;" id="c_followup_email_template_id" name="c_followup_email_template_id" onchange="get_collection_followup_content(4, this, <?= $leadDetails->lead_id ?>)"></select>
                </div>

                <div class="col-md-6">
                    <label class="labelField">Email Subject&nbsp;<strong class="required_Fields">*</strong> </label>
                    <input class="form-control inputField" name="email_subject" id="email_subject">
                </div>

                <div class="col-md-6">
                    <label class="labelField">Email CC&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" id="email_cc_user" name="email_cc_user">
                        <option value="">Default</option>
                        <option value="1">YES</option>
                        <option value="2">NO</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="labelField">Email Body&nbsp;<strong class="required_Fields">*</strong> </label>
                    <textarea class="form-control" rows="5" cols="111" id="email_body" name="email_body" readonly>NA</textarea>
                </div>

            </form>
            <div class="col-md-12" id="btnCollectionFollowup" style="margin: 10px;">
                <button id="saveCollectionFollowupEmail" class="btn btn-success lead-sanction-button">Send Email</button> 
            </div>
        </div>
    </div>

    <div id="summaryCollection"></div>
</div>




<div class="footer-support">
    <h2 class="footer-support"><button type="button" class="btn btn-info collapse" onclick="get_Visit_Request_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-toggle="collapse" data-target="#Request_Field_Visit">Request Field Visit&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
</div>



<div id="Request_Field_Visit" class="collapse"> 

    <?php if (in_array(agent, ["CO1", "CO2", "CO3", "CFE1", "CO4"]) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
        <div class="footer-support">
            <h2 class="footer-support">
                <button type="button" id="btnAddCollectionVisit" class="btn btn-info collapse" data-toggle="collapse" data-target="#AddCollectionVisit" style="width: 25% !important;">Add Visit&nbsp;<i class="fa fa-angle-double-down"></i></button>
            </h2>
        </div>
    <?php } ?>
    <div id="AddCollectionVisit" class="collapse"> 
        <form id="FormRequestForCollectionVisit" class="form-inline" method="post" enctype="multipart/form-data" style="margin: 10px;">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
            <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>" readonly="">
            <input type="hidden" name="col_visit_id" id="col_visit_id" value="" readonly="">

            <?php if (in_array(agent, ['CO1', 'CO2', 'CO3', "CO4"]) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                <div class="col-md-12 alert alert-dismissible alert-info" style="margin-bottom: 15px;">

                    <label class="labelField">Visit Type&nbsp;<strong class="required_Fields">*</strong> </label>

                    <label class="radio-inline">
                        <input type="radio" name="visit_type_id" id="visit_type_id_1" onclick="get_Visit_Request_user_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', this)" value="1">Residence
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="visit_type_id" id="visit_type_id_2" onclick="get_Visit_Request_user_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', this)" value="2">Office
                    </label>
                </div>
            <?php } ?>

            <?php if (in_array(agent, ['CO1','CO4']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <label class="labelField">Visit Request To SCM&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" name="visit_scm_user_id" id="visit_scm_user_id"></select>
                </div>
            <?php } ?>

            <?php if (in_array(agent, ['CO2', 'CO3']) && in_array($leadDetails->lead_status_id, [14, 19])) { // , 'CFE1'  ?>
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <label class="labelField">Visit Status&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" name="visit_status_id" id="visit_status_id">
                        <option value=''>Select</option>
                        <?php if (in_array(agent, ['CO2', 'CO3'])) { ?>
                            <option value='2'>Assign</option>
                            <option value='3'>Cancel</option>
                        <?php } ?>
                        <option value='4'>Hold</option>
                        <!--<option value='5'>Completed</option>-->
                    </select>
                </div>
            <?php } ?>

            <?php if (in_array(agent, ['CO2', 'CO3']) && in_array($leadDetails->lead_status_id, [14, 19])) { ?>
                <div class="col-md-6" style="margin-bottom: 15px;" id="div_visit_assign_to">
                    <label class="labelField">Visit Assign To CFE&nbsp;<strong class="required_Fields">*</strong> </label>
                    <select class="form-control inputField" name="visit_rm_user_id" id="visit_rm_user_id" onchange="assignLeadtoCollection(this, '<?= base64_encode($row->lead_id); ?>', '<?php echo base64_encode($ftype); ?>')"></select>
                </div>
            <?php } ?>

            <div class="col-md-12" style="margin-bottom: 15px;">
                <label class="labelField">Remarks&nbsp;<strong class="required_Fields">*</strong> </label>
                <textarea class="form-control " rows="5" cols="111" maxlength="500" id="remarks" name="remarks" autocomplete="off"></textarea>
            </div>
        </form>

        <div class="col-md-12" style="margin: 10px;">
            <div id="btn_save_visit">
                <button id="saveRequestForCollectionVisit" class="btn btn-success lead-sanction-button">Save </button> 
            </div>
        </div>
    </div>

</div>
<div id="summaryCollectionVisit"></div>


<?php if (in_array(agent, ["CA", "CO3"])) { ?>

    <div class="footer-support">
        <h2 class="footer-support"><button type="button" class="btn btn-info collapse" id="CustomerLegalNotice" data-toggle="collapse" data-target="#LegalNotice">Legal Notice&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
      
    </div>

    <div>     
        <div id="LegalNotice" style="display: none;">           
             <div style="background:#fff !important;">
                
                <p>
                    <b>Send Legal Notice: </b><button type="button" class="btn btn-info" onclick="sendLegalNotice('<?= $leadDetails->lead_id ?>')">Send On Email</button>
                &nbsp;
                <?php if($leadDetails->legal_notice_letter) { ?>
                <button type="button" class="btn btn-danger" onclick="download_legal_notice_letter('<?=  $leadDetails->lead_id; ?>')" style="cursor:pointer;">Download</button>
            <?php } ?>   
                </p>
            </div>   
        </div>
    </div>

    <div class="footer-support">
        <h2 class="footer-support"><button type="button" class="btn btn-info collapse" id="CustomerSettlementNotice" data-toggle="collapse" data-target="#SettlementNotice">Settlement Letter&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
      
    </div>

    <div>     
        <div id="SettlementNotice" style="display: none;">           
            
             <div style="background:#fff !important;">
                
                <p>
                    <b>Send Settlement Notice: </b><button type="button" class="btn btn-info" onclick="sendSettlementNotice('<?= $leadDetails->lead_id ?>')">Send On Email</button>
                &nbsp;
                <?php if($leadDetails->loan_noc_settlement_letter) { ?>
                <button type="button" class="btn btn-danger" onclick="download_Settlement_notice_letter('<?= $leadDetails->lead_id ?>')" style="cursor:pointer;">Download</button>
            <?php } ?>   
                </p>
            </div>   
        </div>
    </div>



<?php } ?>

<?php if (in_array(agent, ["AU"])) { ?>

    <div class="footer-support">
        <h2 class="footer-support"><button type="button" class="btn btn-info collapse" id="CustomerRecoveryNotice" data-toggle="collapse" data-target="#RecoveryNotice">Recovery Payment Notice&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
      
    </div>

    <div>     
        <div id="RecoveryNotice" style="display: none;">           
            
             <div style="background:#fff !important;">
                
                <p>
                <b>Send Recovery Notice: </b><button type="button" class="btn btn-info" onclick="send_NOC_for_recovery_letter('<?= ($leadDetails->lead_id) ?>')">Send On Email</button>
                
                &nbsp;
                
                </p>
            </div>   
        </div>
    </div>

<?php } ?>


<!--
<div class="footer-support">
    <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#ALRESIDENCE">APPROVED LOCATION CONFIRMATION&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
</div>

<div id="ALRESIDENCE" class="collapse"> 

    <div class="table-responsive">
        <form id="applocconf" method="post" >
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
            <input type="hidden" name="lead_id" id="lead_id" value="<?= $leadDetails->lead_id ?>">
            <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
            <input type="hidden" name="company_id" id="company_id" value="<?= $_SESSION['isUserSession']['company_id'] ?>">





            <table class="table table-hover table-striped table-bordered">
                <tr>
                    <th>Present Addres</th>
                    <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

                </tr>
                <tr>
                    <th>City*</th>
                    <td><?php
if (isset($getVerificationdata[0]['met_with']) == '' || isset($getVerificationdata[0]['met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>State</th>
                    <td><?php
if (isset($getVerificationdata[0]['relation']) == '' || isset($getVerificationdata[0]['relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>


                <tr>
                    <th>Pincode </th>
                    <td><?php
if (isset($getVerificationdata[0]['residence_type']) == '' || isset($getVerificationdata[0]['residence_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>PostOffice</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_residence_house_type']) == '' || isset($getVerificationdata[0]['fi_residence_house_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th>Present Residence Type</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_residence_ease_of_identification']) == '' || isset($getVerificationdata[0]['fi_residence_ease_of_identification']) == '-') {
    echo "NO";
} else {
    echo "-";
}
?></td>

                    <th>Residing Since</th> 
                    <td colspan="3"><input type="text" value="<?php
if (isset($getCollectiondata[0]['approvedLocCon_residenceSince']) != '' || isset($getCollectiondata[0]['approvedLocCon_residenceSince']) != '-') {
    echo strtoupper($getCollectiondata[0]['approvedLocCon_residenceSince']);
} else {
    echo "-";
}
?>" name="residence_since" id="residence_since" class="form-control"></td>


                </tr>

                <tr>
                    <th>SCM Remarks</th>
                    <td colspan="3"><input type="text" value="<?php
if (isset($getCollectiondata[0]['approvedLocCon_scmRemarks']) != '' || isset($getCollectiondata[0]['approvedLocCon_scmRemarks']) != '-') {
    echo strtoupper($getCollectiondata[0]['approvedLocCon_scmRemarks']);
} else {
    echo "YES";
}
?>" name="scm_remarks" id="scm_remarks" class="form-control"></td>

                </tr>

                <tr>

                    <th></th>
                    <td colspan=""><button class="btn btn-success lead-hold-button" type="button" onclick="rejectalc('1')">APPROVE</button></td>

                    <th><button type="button" class="btn btn-success reject-button " onclick="rejectalc('0')">REJECT</button></th>
                    <td colspan=""></td>

                </tr>



            </table>
        </form>
    </div>

</div>

<div class="footer-support">
    <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FVRESIDENCE">FIELD VERIFICATION - RESIDENCE&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
</div>
<div id="FVRESIDENCE" class="collapse"> 
    <div class="table-responsive">
        <form id="applocConfirmation" method="post" >
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
            <input type="hidden" name="lead_id" id="lead_id" value="<?= $leadDetails->lead_id ?>">
            <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
            <input type="hidden" name="company_id" id="company_id" value="<?= $_SESSION['isUserSession']['company_id'] ?>">


            <table class="table table-hover table-striped table-bordered ">
                <tr>
                    <th>Present Addres</th>
                    <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

                </tr>
                <tr>
                    <th>City*</th>
                    <td><?php
if (isset($getVerificationdata[0]['met_with']) == '' || isset($getVerificationdata[0]['met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>State</th>
                    <td><?php
if (isset($getVerificationdata[0]['relation']) == '' || isset($getVerificationdata[0]['relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>


                <tr>
                    <th>Pincode </th>
                    <td><?php
if (isset($getVerificationdata[0]['residence_type']) == '' || isset($getVerificationdata[0]['residence_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>PostOffice</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_residence_house_type']) == '' || isset($getVerificationdata[0]['fi_residence_house_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th>Present Residence Type</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_initiated_on']) == '' || isset($getVerificationdata[0]['fi_initiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Residing Since</th>
                    <td><input type="text"  value="<?php
if (isset($getCollectiondata[0]['fvr_fvr_residenceSince']) != '' || isset($getCollectiondata[0]['fvr_fvr_residenceSince']) != '-') {
    echo $getCollectiondata[0]['fvr_fvr_residenceSince'];
} else {
    echo "-";
}
?>" name="fvr_residenceSince" id="fvr_residenceSince" class="form-control"></td>
                </tr>
                <tr>
                    <th>Residence CPV Initiated On</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_met_with']) == '' || isset($getVerificationdata[0]['fi_met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Allocated To</th>
                    <td><select name="fvr_allocateTo" id="fvr_allocateTo" class="form-control">
                            <option value="1" <?php
if ($getCollectiondata[0]['fvr_allocatoTo'] == '1') {
    echo "selected";
}
?> >1</option>
                            <option value="2" <?php
if ($getCollectiondata[0]['fvr_allocatoTo'] == '2') {
    echo "selected";
}
?>>2</option>
                            <option value="3" <?php
if ($getCollectiondata[0]['fvr_allocatoTo'] == '3') {
    echo "selected";
}
?>>3</option>
                        </select></td>
                </tr>


                <tr>
                    <th>Allocated On </th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Report Status</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>


                <tr>

                    <th colspan="2"></th>
                    <td><button type="button" class="btn btn-success lead-sanction-button" id="savefvrData">Save</button></td>
                </tr>


            </table>
        </form>
    </div>

</div>-->
<!--
<div class="footer-support">
    <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FVOFFICE">FIELD VERIFICATION - OFFICE&nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
</div>
<div id="FVOFFICE" class="collapse"> 
    ---- table for  OFFICE section ---------------------

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <tr>
                <th>Office/ Employer Name*</th>
                <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

            </tr>
            <tr>
                <th>Office Address</th>
                <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

            </tr>
            <tr>
                <th>City*</th>
                <td><?php
if (isset($getVerificationdata[0]['met_with']) == '' || isset($getVerificationdata[0]['met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                <th>State</th>
                <td><?php
if (isset($getVerificationdata[0]['relation']) == '' || isset($getVerificationdata[0]['relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
            </tr>


            <tr>
                <th>Industry  </th>
                <td><?php
if (isset($getVerificationdata[0]['residence_type']) == '' || isset($getVerificationdata[0]['residence_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                <th>Sector</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_residence_house_type']) == '' || isset($getVerificationdata[0]['fi_residence_house_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
            </tr>

            <tr>
                <th>Department</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_initiated_on']) == '' || isset($getVerificationdata[0]['fi_initiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                <th>Designation </th>
                <td><?php
if (isset($getVerificationdata[0]['fi_received_on']) == '' || isset($getVerificationdata[0]['fi_received_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
            </tr>
            <tr>
                <th>Employed Since</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_met_with']) == '' || isset($getVerificationdata[0]['fi_met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                <th>Present Service Tenure</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_relation']) == '' || isset($getVerificationdata[0]['fi_relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
            </tr>


            <tr>
                <th>Office CPV Initiated On</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                <th>Allocate To</th>
                <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
            </tr>





        </table>
    </div> 
-->



<!--
    <div class="footer-support">
        <h2 class="footer-support"><button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#FVCOLL">FIELD VISIT - COLLECTION &nbsp;<i class="fa fa-angle-double-down"></i></button></h2>
    </div>
    <div id="FVCOLL" class="collapse"> 
        ---- table for  OFFICE section ---------------------

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <tr>
                    <th>Mobile</th>
                    <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

                </tr>
                <tr>
                    <th>Mobile Alternate</th>
                    <td colspan="3"><?php
if (isset($getVerificationdata[0]['initiiated_on']) == '' || isset($getVerificationdata[0]['initiiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>

                </tr>
                <tr>
                    <th>Email (Personal)</th>
                    <td><?php
if (isset($getVerificationdata[0]['met_with']) == '' || isset($getVerificationdata[0]['met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Email (Office)</th>
                    <td><?php
if (isset($getVerificationdata[0]['relation']) == '' || isset($getVerificationdata[0]['relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>


                <tr>
                    <th>Loan Amount  </th>
                    <td><?php
if (isset($getVerificationdata[0]['residence_type']) == '' || isset($getVerificationdata[0]['residence_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Tenure as on date</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_residence_house_type']) == '' || isset($getVerificationdata[0]['fi_residence_house_type']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th>ROI</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_initiated_on']) == '' || isset($getVerificationdata[0]['fi_initiated_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Interest as on date </th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_received_on']) == '' || isset($getVerificationdata[0]['fi_received_on']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>
                <tr>
                    <th>Disbursal Date</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_met_with']) == '' || isset($getVerificationdata[0]['fi_met_with']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Delay (days)</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_relation']) == '' || isset($getVerificationdata[0]['fi_relation']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>


                <tr>
                    <th>Repay Date</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Late Payment Interest as on date</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th>Repay Amount</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Total Payable (Rs)</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th>Penal ROI</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Total Received (Rs)</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>

                <tr>
                    <th></th>
                    <td></td>
                    <th>Total Due (Rs)</th>
                    <td></td>
                </tr>


                <tr>
                    <th>Allocate To</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_entry_allowed']) == '' || isset($getVerificationdata[0]['fi_entry_allowed']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                    <th>Allocated On</th>
                    <td><?php
if (isset($getVerificationdata[0]['fi_employer_name']) == '' || isset($getVerificationdata[0]['fi_employer_name']) == '-') {
    echo "NO";
} else {
    echo "YES";
}
?></td>
                </tr>





            </table>
        </div> 







    </div>-->







