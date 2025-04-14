<style>
    .button__password-visibility {
        font-weight: 550;
        padding: 3px 8px;
        color: #fff !important;
        position: absolute;
        top: 7px;
        background: #3468b3;
        border-radius: 4px;
        right: 21px;

    }

    /*    #password_visibility:focus {
        background-color: rgb(0 177 255 / 25%);
    }*/
</style>


<?php if (in_array(agent, ['CA', 'AH']) || (agent == 'CR1' && !empty($leadDetails->lead_screener_assign_user_id) && $leadDetails->lead_screener_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(2, 3, 30))) || (agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id && in_array($leadDetails->lead_status_id, array(5, 6, 11, 30)) && $leadDetails->customer_bre_run_flag == 0) || $leadDetails->lead_status_id < 14 || $leadDetails->lead_status_id == 30 || $leadDetails->lead_status_id == 25) { ?>
    <form id="formUserDocsData" method="post" enctype="multipart/form-data" style="float: left;width: 97%;margin:13px 13px 20px 0px;">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
        <input type="hidden" name="lead_id" id="lead_id" value="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
        <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['isUserSession']['user_id'] ?>">
        <input type="hidden" name="company_id" id="company_id" value="<?= $_SESSION['isUserSession']['company_id'] ?>">
        <input type="hidden" name="customer_id" id="customer_id" value="<?= $leadDetails->customer_id ?>">
        <div id="getDocId"></div>
        <input type="hidden" id="docuemnt_type" name="docuemnt_type" class="form-control" placeholder="Document Type" readonly="readonly" required>
        <div class="col-md-1" id="selectDocType"></div>
        <div class="col-md-3" id="selectDocType">
            <select class="form-control" name="document_name" id="document_name" required></select>
        </div>

        <div class="col-md-3" id="selectFileType">
            <input type="file" class="form-control" name="file_name" id="file_name" accept="image/*,.jpeg, .png, .jpg,.pdf,.mp4,.mkv" required>
        </div>

        <div class="col-md-3">
            <input type="password" class="form-control" name="password" id="password" placeholder="Password">
            <span id="password_visibility" class="button__password-visibility" onclick="password_visibility(this)" role="button">show</span>
        </div>

        <div class="col-md-2" id="btnDocsSave">
            <button class="btn btn-primary" id="btnSaveDocs">Upload</button>
        </div></br></br>
    </form>
<?php } ?>
