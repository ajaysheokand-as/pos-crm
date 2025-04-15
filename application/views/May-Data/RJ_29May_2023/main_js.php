
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
<script>
    var csrf_token = $("input[name=csrf_token]").val();
    $(".js-select2").select2({
        closeOnSelect: false,
        placeholder: "SELECT",
        allowClear: true,
        tags: true
    }).css("float", 'left');
</script>
<script>
    function apiPincode(pincode, count)
    {
        $.ajax({
            url: "<?= base_url('apiPincode/'); ?>" + $(pincode).val(),
            type: "GET",
            data: csrf_token,
            dataType: "json",
            success: function (response) {
                $("#state" + count).val(response[0].State.toUpperCase());
                $("#city" + count).empty().append('<option value="">Select</option>');
                $("#district" + count).empty().append('<option value="">Select</option>');
                $("#city" + count).append('<option value="' + response[0].Division + '">' + response[0].Division + '</option>');
                // $.each(response, function(index, myarr) { 
                //     $("#city"+count).append('<option value="'+ myarr.Division +'">'+ myarr.Division +'</option>');
                // });
                $.each(response, function (index, myarr) {
                    $("#district" + count).append('<option value="' + myarr.Name + '">' + myarr.Name + '</option>');
                });
            }
        });
    }

    function apiPincode(pincode, count)
    {
        $.ajax({
            url: "<?= base_url('apiPincode/'); ?>" + $(pincode).val(),
            type: "GET",
            data: csrf_token,
            dataType: "json",
            success: function (response) {
                $("#state" + count).val(response[0].State.toUpperCase());
                $("#city" + count).empty().append('<option value="">Select</option>');
                $("#district" + count).empty().append('<option value="">Select</option>');
                $("#city" + count).append('<option value="' + response[0].Division + '">' + response[0].Division + '</option>');
                // $.each(response, function(index, myarr) { 
                //     $("#city"+count).append('<option value="'+ myarr.Division +'">'+ myarr.Division +'</option>');
                // });
                $.each(response, function (index, myarr) {
                    $("#district" + count).append('<option value="' + myarr.Name + '">' + myarr.Name + '</option>');
                });
            }
        });
    }

    $(function () {
        $('#checkDuplicateItem').click(function () {
            var checkList = [];
            $('.duplicate_id:checked').each(function () {
                checkList.push($(this).val());
            });
            if (checkList.length > 0)
            {
                $.ajax({
                    url: '<?= base_url("resonForDuplicateLeads") ?>',
                    type: 'POST',
                    dataType: "json",
                    async: false,
                    data: {checkList: checkList, csrf_token},
                    beforeSend: function () {
                        $('#checkDuplicateItem').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                    },
                    success: function (response) {
                        if (response.err) {
                            catchError(response.err);
                        } else {
                            $('.duplicate_id,#selectAllDomainList').removeAttr('checked');
                            catchSuccess("Leads added in duplicate List.");
                            window.location.reload();
                        }
                    },
                    complete: function () {
                        $('#checkDuplicateItem').html('Duplicate').removeClass('disabled');
                    }
                });
            } else {
                catchError("Please select Leads to mark Duplicates .");
            }
        });
    });

    ////////////////////////////////////////// Allocate Leads ////////////////////////////////////////

    $(function () {
        $('#allocate').click(function () {
            var checkList = [];
            $('.duplicate_id:checked').each(function () {
                checkList.push($(this).val());
            });
            if (checkList.length > 0)
            {
                var user_id = $('#user_id').val();
                var customer_id = $('#customer_id').val();
                $.ajax({
                    url: '<?= base_url("allocateLeads") ?>',
                    type: 'POST',
                    dataType: "json",
                    data: {checkList: checkList, user_id: user_id, customer_id: customer_id, csrf_token},

                    beforeSend: function () {
                        $('#allocate').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                    },
                    success: function (response) {
                        if (response.err) {
                            catchError(response.err);
                        } else {
                            $('.duplicate_id,#selectAllDomainList').removeAttr('checked');
                            catchSuccess("Leads added in Your Bucket.");
                            window.location.reload();
                        }
                    },
                    complete: function () {
                        $('#allocate').html('Allocate').removeClass('disabled');
                    }
                });
            } else {
                $('#allocate').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                catchError("Please select Leads to Assign Yourself.");
                $('#allocate').html('Allocate').removeClass('disabled');
            }
        });
    });

    $(function () {
        $('#rejectedLeadMoveToProcess').click(function () {
            var lead_id = $('#lead_id').val();

            var customer_id = $('#customer_id').val();
            $.ajax({
                url: '<?= base_url("rejectedLeadMoveToProcess") ?>',
                type: 'POST',
                dataType: "json",
                data: {lead_id: lead_id, customer_id: customer_id, csrf_token},
                beforeSend: function () {
                    $('#rejectedLeadMoveToProcess').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');

                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        // window.location.reload();
                        window.location.href = "<?php echo base_url(); ?>/not-contactable/S9";

                    }
                },

            });

        });
    });




    $(function () {
        $('#reallocate').click(function () {
            var telecaller = $('#telecaller-name').val();
            var checkList = [];
            $('.duplicate_id:checked').each(function () {
                checkList.push($(this).val());
            });
            if (checkList.length > 0)
            {
                $.ajax({
                    url: '<?= base_url("reallocate") ?>',
                    type: 'POST',
                    dataType: "json",
                    data: {checkList: checkList, csrf_token},
                    success: function (response) {
                        if (response.err) {
                            catchError(response.err);
                        } else {
                            catchSuccess("Leads Reallocated Successfully.");
                            window.location.reload();
                        }
                    }
                });
            } else {
                catchError("Please select leads for Re-Allocate.");
            }
        });
    });

    //////////////////////////// get old loan History ////////////////////////////////////////////////

    function getLeadsDetails(lead_id)
    {
        window.location.href = "<?= base_url('getleadDetails/' . $this->encrypt->encode($leadDetails->lead_id)) ?>";
    }

    function getState(state_id, count)
    {
        $.ajax({
            url: '<?= base_url("getState") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#state" + count).empty();
                $("#state" + count).append('<option value="">Select</option>');
                $.each(response.state, function (index, myarr) {
                    var s = "";
                    if (state_id == myarr.m_state_id) {
                        s = "Selected";
                    }
                    $("#state" + count).append('<option value="' + myarr.m_state_id + '" ' + s + '>' + myarr.m_state_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getCity(city_id, state_id, count)
    {
        $.ajax({
            url: '<?= base_url("getCity/") ?>' + state_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#city" + count).empty();
                $("#city" + count).append('<option value="">Select</option>');
                $.each(response.city, function (index, myarr) {
                    var s = "";
                    if (city_id == myarr.m_city_id) {
                        s = "Selected";
                    }
                    $("#city" + count).append('<option value="' + myarr.m_city_id + '" ' + s + '>' + myarr.m_city_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getPincode(pincode, city_id, count)
    {
        $.ajax({
            url: '<?= base_url("getPincode/") ?>' + city_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#pincode" + count).empty();
                $("#pincode" + count).append('<option value="">Select</option>');
                $.each(response.pincode, function (index, myarr) {
                    var s = "";
                    if (pincode == myarr.m_pincode_value) {
                        s = "Selected";
                    }
                    $("#pincode" + count).append('<option value="' + myarr.m_pincode_value + '" ' + s + '>' + myarr.m_pincode_value + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getApplicationDetails(lead_id)
    {
        $.ajax({
            url: '<?= base_url("getApplicationDetails/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                var res = response['application'];
                $('#borrower_type').val((res.user_type) ? res.user_type : '');
                $('#pancard').val((res.pancard) ? res.pancard : '');
                $('#loan_applied').val((res.loan_amount) ? parseInt(res.loan_amount) : '');
                $('#loan_tenure').val((res.tenure) ? res.tenure : '');
                $('#loan_purpose').val((res.purpose) ? res.purpose : '');
                $('#first_name').val((res.first_name) ? res.first_name : '');
                $('#middle_name').val((res.middle_name) ? res.middle_name : '');
                $('#sur_name').val((res.sur_name) ? res.sur_name : '');
                $('#gender').val((res.gender) ? res.gender : '');
                $('#dob').val((res.dob) ? res.dob : '');
                $('#salary_mode').val((res.salary_mode) ? res.salary_mode : '');
                $('#monthly_income').val((res.monthly_income) ? parseInt(res.monthly_income) : '');
                $('#obligations').val((res.obligations) ? parseInt(res.obligations) : '');
                $('#mobile').val((res.mobile) ? res.mobile : '');
                $('#alternate_mobile').val((res.alternate_mobile != null && res.alternate_mobile != 0) ? res.alternate_mobile : '');
                $('#email').val((res.email) ? res.email : '');
                $('#alternate_email').val((res.alternate_email) ? res.alternate_email : '');
                $('#pincode0').val((res.pincode) ? res.pincode : '');
                $("#city0").empty().append('<option value="' + res.city_id + '">' + res.city_id + '</option>');
                $('#state0').val((res.state_id) ? res.state_id : '');
                $('#religion1').val((res.customer_religion_id) ? res.customer_religion_id : '');
                $('#income_type').val((res.income_type) ? parseInt(res.income_type) : '');
                $('#aadhar').val((res.aadhar_no) ? res.aadhar_no : '');
                $('#MaritalStatus1').val((res.customer_marital_status_id) ? res.customer_marital_status_id : '');
                $('#SpouseOccupation1').val((res.customer_spouse_occupation_id) ? res.customer_spouse_occupation_id : '');
                $('#customer_spouse_name').val((res.customer_spouse_name) ? res.customer_spouse_name : '');
                $('#Qualification1').val((res.customer_qualification_id) ? res.customer_qualification_id : '');


                getState(res.state_id, 10);
                getCity(res.city_id, res.state_id, 10);
                getPincode(res.pincode, res.city_id, 10);
                getReligion(res.customer_religion_id, 1);
                getMaritalStatus(res.customer_marital_status_id, 1);
                getSpouseOccupation(res.customer_spouse_occupation_id, 1);
                getQualification(res.customer_qualification_id, 1);
            },
            complete: function () {
                $("#cover").fadeOut(1750);
            }
        });
    }

    function viewOldHistory(lead_id)
    {
        $.ajax({
            url: '<?= base_url("viewOldHistory/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $('#oldTaskHistory').empty();
                $('#oldTaskHistory').html(response);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function ViewCibilStatement(lead_id)
    {
        $.ajax({
            url: '<?= base_url("cibilStatement"); ?>',
            type: 'POST',
            data: {lead_id: lead_id, csrf_token},
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $('#cibilStatement').html(response);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function checkCustomerCibil(lead_id)
    {
        autoCheckCustomerCibil(lead_id);
    }

    function autoCheckCustomerCibil(lead_id)
    {
        if (lead_id != '')
        {
            $.ajax({
                url: '<?= base_url("cibil") ?>',
                type: 'POST',
                data: {lead_id: lead_id, csrf_token},
                dataType: 'json',
                beforeSend: function () {
                    $('#checkCustomerCibil button').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (response) {
                    if (response.err) {
                        catchError(response.err);
                        ViewCibilStatement(lead_id);
                    } else {
                        ViewCibilStatement(lead_id);
                        catchSuccess(response.msg);
                        $("#btndivCheckCibil").hide();
                    }
                },
                complete: function () {
                    $('#checkCustomerCibil button').html('Check Cibil').removeClass('disabled');
                }
            });
        } else {
            catchError("No record found.");
        }
    }

    $('#divExpendReason').hide();

    function RejectedLoan()
    {
        $('#divExpendReason2').hide();
        $('#divExpendReason').toggle();

<?php if ($_SESSION['isUserSession']['role'] == "Disbursal") { ?>
            // $("#ResonBoxForRejectDisbursalLoan").html(prependFormDuplicateLead);
<?php } else { ?>
            // $("#ResonBoxForrejectLoan").html(prependFormDuplicateLead);
<?php } ?>

        $.ajax({
            url: '<?= base_url("getRejectionReasonMaster") ?>',
            type: 'POST',
            data: {csrf_token},
            dataType: 'json',
            beforeSend: function () {
                $('.reject-button').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
            },
            success: function (response) {
                $("#resonForReject").empty();
                $("#resonForReject").append('<option value="">Select Reason</option>');
                $.each(response.rejectionLists, function (index, myarr) {
                    $("#resonForReject").append('<option value="' + myarr.id + '">' + myarr.reason + '</option>');
                });
            },
            complete: function () {
                $('.reject-button').html('REJECT').removeClass('disabled');
            }
        });
    }

    function ResonForRejectLoan()
    {
        var user_id = $("#user_id").val();
        var lead_id = $("#lead_id").val();
        var customer_id = $("#customer_id").val();
        var reason_id = $("#resonForReject").val();

        if (lead_id == "") {
            catchError("Lead ID is required.");
        } else if (user_id == "") {
            catchError("Session Expired. Please re-login.");
        } else if (reason_id == "") {
            catchError("Reason is required.");
        } else {
            $.ajax({
                url: '<?= base_url("resonForRejectLoan") ?>',
                type: 'POST',
                data: {user_id: user_id, lead_id: lead_id, customer_id: customer_id, reason: reason_id, csrf_token},
                dataType: 'json',
                beforeSend: function () {
                    $("#btnRejectApplication").html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        $('#reson').empty();
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#btnRejectApplication").html('REJECT APPLICATION').removeClass('disabled');
                }
            });
        }
    }

    $("#divExpendReason2").hide();
    function holdLeadsRemark()
    {
        $("#divExpendReason").hide();
        $("#divExpendReason2").toggle();
    }

    $("#divExpendSearch").hide();
    function searchdatalist()
    {
        $("#divExpendSearch").toggle();
    }

    function saveHoldleads(lead_id)
    {
        var hold_remark = $("#hold_remark").val();
        var hold_date = $("#holdDurationDate").val();
        // var status = $("#status").val();
        // var stage = $("#stage").val();
        var user_id = $("#user_id").val();
        var customer_id = $("#customer_id").val();
        if (hold_remark == "") {
            catchError("Remarks is required.");
            return false;
        } else if (hold_date == "") {
            catchError("Date is required.");
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("saveHoldleads/") ?>' + lead_id,
                type: 'POST',
                data: {hold_remark: hold_remark, hold_date: hold_date, customer_id: customer_id, user_id: user_id, csrf_token},
                dataType: 'json',
                success: function (response) {
                    if (response.msg) {
                        $('#reson').empty();
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                }
            });
        }
    }

    //////////////////////////////////////////////////////////////// Document Section /////////////////////////////////////////////////////////////////////////////////

    function sendRequestToCustomerForUploadDocs(lead_id)
    {
        if (confirm("Are you sure to send request to the customer for upload docs!")) {
            $.ajax({
                url: '<?= base_url("sendRequestToCustomerForUploadDocs/") ?>' + lead_id,
                type: 'POST',
                dataType: "json",
                data: {csrf_token},
                async: false,
                success: function (response) {
                    if (response == "true") {
                        $(".msg").show().fadeOut(2000);
                        $(".msg a").html("Request Send Successfully.");
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);
                    return false;
                }
            });
        } else {
            catchSuccess("Network Error, Try Again");
        }
    }

    function viewCustomerDocs(docs_id) {
        $.ajax({
            url: '<?= base_url("viewCustomerDocs/") ?>' + docs_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            async: false,
            success: function (response) {
                // window.open(response, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=50,left=50,width=400,height=400");
                window.location.href = response;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);
                return false;
            }
        });
    }

/////////////////////////////////////////// Application Field ///////////////////////////////////////////

    function viewCustomerDocs(docs_id) {
        $.ajax({
            url: '<?= base_url("viewCustomerDocs/") ?>' + docs_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            async: false,
            success: function (response) {
                // window.open(response, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=50,left=50,width=400,height=400");
                window.location.href = response;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);
                return false;
            }
        });
    }

    function editCustomerDocs(docs_id)
    {
        $('#formUploadDocs').show();
        $.ajax({
            url: '<?= base_url("viewCustomerDocsById/") ?>' + docs_id, /*selectDocsTypes   editCustomerDocs*/
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                $('#getDocId').html('<input type="hidden" name="docs_id" id="docs_id" value="' + response.docs_id + '">');
                $("#btnSaveDocs").html("Update Docs");
                $("#docuemnt_type").val(response.docs);
                $("#document_name").val(response.type);
                $("#password").val(response.pwd);
            }
        });
    }

    function deleteCustomerDocs(docs_id)
    {
        var customer_id = $('#customer_id').val();
        $.ajax({
            url: '<?= base_url("deleteCustomerDocsById/") ?>' + docs_id, /*selectDocsTypes   editCustomerDocs*/
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                if (response['result'] == true) {
                    catchSuccess("Document Deleted Successfully.");
                    $('#formUserDocsData').trigger("reset");
                    $('#selectDocsTypes').trigger("reset");
                } else {
                    catchError("Process Failed, Try Again");
                }
                getDocs(response['lead_id'], customer_id);
            }

        });
    }

    function viewCustomerPaidSlip(docs_id)
    {
        $.ajax({
            url: '<?= base_url("viewCustomerPaidSlip/") ?>' + docs_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            async: false,
            success: function (response) {
                window.open(response, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=50,left=50,width=400,height=400");
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);
                return false;
            }
        });
    }

    function downloadCustomerdocs(docs_id)
    {
        $.ajax({
            url: '<?= base_url("downloadCustomerdocs/") ?>' + docs_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            async: false,
            success: function (response) {
                window.location.href = response;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#exampleModalLongTitle").html(textStatus + " : " + errorThrown);
                return false;
            }
        });
    }

    function getCustomerDocs(lead_id, customer_id)
    {
        getDocs(lead_id, customer_id);
    }

    function editsCoustomerPayment(input)
    {
        var json_data;
        if (input != '' && input != null) {
        }

        $('#recovery_id').val(input.id);
        $('#received_amount').val(input.received_amount);
        $('#refrence_no').val(input.refrence_no);
        $('#discount').val(input.discount);
        $('#refund').val((input.refund) ? input.refund : 0);
        // $('#date_of_recived').val(input.date_of_recived);
        $('#collection_payment_mode').val(input.payment_mode);
        $('#repayment_type').val(input.repayment_type);

        collection_payment_verification(input.payment_mode, input.payment_mode_id);
    }

    function getDocs(lead_id, customer_id)
    {
        $.ajax({
            url: '<?= base_url("getDocsUsingAjax/") ?>' + lead_id,
            type: 'POST',
            data: {customer_id: customer_id, csrf_token},
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $('#docsHistory').html(response);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    $('#divExpendReason3').hide();
    function sanctionFeedback(lead_id)
    {
        $('#btn_own_reason').html('<button class="btn btn-primary lead-sanction-button" style="background : #0a5e90 !important;" onclick="sanctionleads(&quot;' + lead_id + '&quot;)">Sanction</button>');
        $('#divExpendReason3').toggle();
    }

    function sanctionleads(lead_id)
    {
        var sanction_remarks = $("#own_remark").val();
        if (sanction_remarks == "") {
            catchError("Remarks is required.");
            return false;
        }

        if (lead_id == "") {
            catchError("Lead ID is required.");
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("sanctionleads") ?>',
                type: 'POST',
                data: {lead_id: lead_id, remarks: sanction_remarks, csrf_token},
                dataType: 'json',
                beforeSend: function () {
                    $('.lead-sanction-button').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        $('#reson').empty();
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('.lead-sanction-button').html('SANCTION').removeClass('disabled');

                }
            });
        }
    }

    $('#divExpendReason3').hide();
    function leadSendBack(lead_id)
    {
        $('#btn_own_reason').html('<button class="btn btn-primary" onclick="processLeadSendBack(&quot;' + lead_id + '&quot;)">Send Back</button>');
        $('#divExpendReason3').toggle();
    }
    function processLeadSendBack(lead_id)
    {
        var own_remark = $('#own_remark').val();
        if (lead_id == "") {
            catchError("Lead ID is required.");
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("leadSendBack") ?>',
                type: 'POST',
                data: {lead_id: lead_id, remark: own_remark, csrf_token},
                dataType: 'json',
                beforeSend: function () {
                    $('#btn_send_back').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        $('#reson').empty();
                        catchSuccess(response.msg);
                        window.location.href = '<?= base_url('applicationRecommend/') . $this->encrypt->encode('S10') ?>';
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#btn_send_back').html('Send Back').removeClass('disabled');

                }
            });
        }
    }

    $('#divExpendReason3').hide();
    function disburseSendBack(lead_id)
    {
        $('#btn_own_reason').html('<button class="btn btn-primary" id="btnRejectApplication" onclick="disbursalSendBack(&quot;' + lead_id + '&quot;)">Disbursal Send Back</button>');
        $('#divExpendReason3').toggle();
    }

    function disbursalSendBack(lead_id)
    {
        var own_remark = $('#own_remark').val();
        if (lead_id == "") {
            catchError("Lead ID is required.");
            return false;
        } else if (own_remark == "") {
            catchError("Remark is required.");
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("disbursalSendBack") ?>',
                type: 'POST',
                data: {lead_id: lead_id, remark: own_remark, csrf_token},
                dataType: 'json',
                beforeSend: function () {
                    $('#btn_disburse_send_back').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        $('#reson').empty();
                        catchSuccess(response.msg);
                        window.location.href = '<?= base_url('disbursalinprocess/S21') ?>';
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#btn_disburse_send_back').html('Send Back').removeClass('disabled');

                }
            });
        }
    }

    function resendAgreementLetter(lead_id, user_id)
    {
        if ($('#resendAgreementLetter').prop('checked'))
        {
            var resendAggLetter = "YES";
            $.ajax({
                url: '<?= base_url("resendDisbursalMail") ?>',
                type: 'POST',
                data: {lead_id: lead_id, user_id: user_id, csrf_token},
                dataType: "json",
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                }
            });
        } else {
            var resendAggLetter = "NO";
        }
    }

    function disbursalDetails(lead_id)
    {
        $('#div1disbursalBank, #disbursalBank, #div1UpdateReferenceNo, #divUpdateReferenceNo').show();
        $.ajax({
            url: '<?= base_url("getSanctionDetails") ?>',
            type: 'POST',
            data: {lead_id: lead_id, csrf_token},
            dataType: "json",
            success: function (response) {
                var res = response['camDetails'];

                $('#payable_amount').val(res.net_disbursal_amount);
                $('#disbursal_date').val(res.disbursal_date);
                var html = '<table class="table table-bordered table-striped"><tbody>';
                html += '<tr><th class="thbg">Loan No.</th><td>' + ((res.lead_status_id !== '' && res.lead_status_id >= 14) ? res.loan_no : "-") + '</td><th class="thbg">Customer Name</th><td>' + (res.first_name + ' ' + ' ' + res.middle_name + ' ' + res.sur_name) + '</td></tr>';

                html += '<tr><th class="thbg">Processed By</th><td>' + (res.screened_by) + '</td><th class="thbg">Processed On</th><td>' + (res.lead_screener_assign_datetime) + '</td></tr>';
                html += '<tr><th class="thbg">Dusbursal Executive</th><td>' + ((res.disbursal_manager) ? res.disbursal_manager : "-") + '</td><th class="thbg">Disbursal Recommended On</th><td colspan="4">' + ((res.disbursal_recommend) ? res.disbursal_recommend : '-') + '</td></tr>';
                html += '<tr><th class="thbg">Dusbursal Head</th><td>' + ((res.disbursal_head) ? res.disbursal_head : "-") + '</td><th class="thbg">Disbursed On</th><td colspan="4">' + ((res.disbursal_approve) ? res.disbursal_approve : '-') + '</td></tr>';
                html += '<tr><th class="thbg">Sanctioned By</th><td>' + ((res.sanctioned_by) ? res.sanctioned_by : res.credit_by) + '</td><th class="thbg">Sanctioned On</th><td>' + ((res.lead_credit_approve_datetime) ? res.lead_credit_approve_datetime : res.lead_credit_assign_datetime) + '</td></tr>';
                html += '<tr><th class="thbg">Loan Approved (Rs.)</th><td>' + ((res.loan_recommended) ? res.loan_recommended : "-") + '</td><th class="thbg">ROI % (p.d.) Approved</th><td>' + ((res.roi) ? res.roi : "-") + '</td></tr>';
//                html += '<tr><th class="thbg">Advance IRR Amount (Rs.)</th><td>' + ((res.cam_interest_amount) ? res.cam_interest_amount : "-") + '</td><th class="thbg"></th><td></td></tr>';
                html += '<tr><th class="thbg">Total Admin Fee (Rs.) Approved</th><td>' + ((res.admin_fee) ? res.admin_fee : "-") + '</td><th class="thbg">Tenure Approved</th><td>' + ((res.tenure) ? res.tenure : "-") + '</td></tr>';
                html += '<tr><th class="thbg">Sanction Email Sent On</th><td>' + ((res.agrementRequestedDate) ? res.agrementRequestedDate : '-') + '</td><th class="thbg">Sanction Email Sent To</th><td>' + ((res.email) ? res.email : '-') + '</td></tr>';
                html += '<tr><th class="thbg">Sanction Email Delivery status</th><td>' + ((res.loanAgreementRequest == 1) ? "SENT" : 'PENDING') + '</td><th class="thbg">Sanction Email Response status</th><td>' + ((res.loanAgreementResponse == 1) ? "ACCEPTED" : '-') + '</td></tr>';
                html += '<tr><th class="thbg">Sanction Email Response IP</th><td>' + ((res.agrementUserIP) ? res.agrementUserIP : "-") + '</td><th class="thbg">Acceptance Email</th><td>' + ((res.loanAgreementResponse == 1) ? res.email : '-') + '</td></tr>';

                if ((res.loan_status !== '') && ((res.loan_status == 'SANCTION') || (res.loan_status == 'DISBURSED-PENDING') || (res.loan_status != 'DISBURSED')))
                {
<?php if (agent == "DS1" || agent == "CR2" || agent == "CR3") { ?>
                        if (res.loan_status == 'DISBURSED-PENDING' || res.loan_status == 'SANCTION') {
                            html += '<tr><th class="thbg">Resend Sanction Email</th><td colspan="4"><input type="checkbox" name="resendAgreementLetter" id="resendAgreementLetter" onclick="resendAgreementLetter(' + lead_id + ', ' + user_id + ')"></td></tr>';
                              html += '<tr><th class="thbg">Dusbursal Executive</th><td>' + ((res.name) ? res.name : "-") + '</td><th class="thbg">Disbursal Head</th><td colspan="4">' + ((res.disburse_refrence_no) ? res.disburse_refrence_no : '-') + '</td></tr>';
                        }
<?php } else { ?>
                        html += '<tr><th class="thbg">Payable Account</th><td>' + ((res.company_account_no) ? res.company_account_no : "-") + '</td><th class="thbg">Channel</th><td>' + ((res.channel) ? res.channel : '-') + '</td></tr>';
                        html += '<tr><th class="thbg">MOP</th><td>' + ((res.mode_of_payment) ? res.mode_of_payment : "-") + '</td><th class="thbg">Disbursal Reference No.</th><td colspan="4">' + ((res.disburse_refrence_no) ? res.disburse_refrence_no : '-') + '</td></tr>';
<?php } ?>
                }
                if (res.loan_status == 'DISBURSED')
                {
                    html += '<tr><th class="thbg">Payable Account</th><td>' + ((res.company_account_no) ? res.company_account_no : "-") + '</td><th class="thbg">Channel</th><td>' + ((res.channel) ? res.channel : '-') + '</td></tr>';
                    html += '<tr><th class="thbg">MOP</th><td>' + ((res.mode_of_payment) ? res.mode_of_payment : "-") + '</td><th class="thbg">Disbursal Reference No.</th><td colspan="4">' + ((res.disburse_refrence_no) ? res.disburse_refrence_no : '-') + '</td></tr>';

                }
                html += '</tbody></table>';
                if ((res.loan_status !== '') && (res.loan_status == 'SANCTION'))
                {
                    $('#div1disbursalBank, #disbursalBank, #div1UpdateReferenceNo, #divUpdateReferenceNo').hide();
                } else if ((res.loan_status !== '') && (res.loan_status == 'DISBURSE-PENDING')) {
                    $('#resendAgreementLetter').prop('disabled', true);
                    $('#div1disbursalBank, #disbursalBank').show();
                    $('#div1UpdateReferenceNo, #divUpdateReferenceNo').hide();
                } else if ((res.loan_status == 'DISBURSED') && (res.disburse_refrence_no == null)) {
                    $('#resendAgreementLetter').prop('disabled', true);
                    $('#div1disbursalBank, #disbursalBank').hide();
                    $('#div1UpdateReferenceNo, #divUpdateReferenceNo').show();
                } else {
                    $('#div1disbursalBank, #disbursalBank, #div1UpdateReferenceNo, #divUpdateReferenceNo').hide();
                }
                $('#ViewDisbursalDetails').html(html);
            }
        });
    }

    function receivedAmount(amount)
    {
        var amount_val = parseInt($(amount).val());
        console.log(amount_val);
        var total_due_amount = parseInt($('#total_due_amount').val());
        console.log(total_due_amount);

        if (amount_val >= total_due_amount) {
            console.log(2);
            $('#received_amount').val(total_due_amount);
        } else if (amount_val >= 0) {
            console.log(3);
            $('#received_amount').val(amount_val);
        } else {
            console.log(4);
            $('#received_amount').val(total_due_amount);
        }
    }

    function discountAmount(amount)
    {
        var amount = $(amount).val();
        var total_due_amount = $('#total_due_amount').val();

        if (total_due_amount < amount) {
            $('#discount').val(0);
        } else if (amount <= 0) {
            $('#discount').val(0);
        } else {
            $('#discount').val(amount);
        }
    }

    function refundAmount(amount)
    {
        var amount = $(amount).val();
        var total_due_amount = $('#total_due_amount').val();

        if (total_due_amount < amount) {
            $('#refund').val(0);
        } else if (amount <= 0) {
            $('#refund').val(0);
        } else {
            $('#refund').val(amount);
        }
    }

    function repaymentLoanDetails(lead_id, customer_id, user_id)
    {
        $.ajax({
            url: '<?= base_url("repaymentLoanDetails") ?>',
            type: 'POST',
            data: {lead_id: lead_id, customer_id: customer_id, user_id: user_id, csrf_token},
            dataType: "json",
            success: function (response) {
                var diff_discount = ((parseInt(response.repayment_amount) > 0 && parseInt(response.total_due_amount) > 0) ? (parseInt(response.repayment_amount) - parseInt(response.repayment_with_real_interest)) : 0);

                var html = '<table class="table table-bordered table-striped"><tbody>';

                html += '<tr><th class="thbg">Loan No.</th><td>' + ((response.loan_no) ? response.loan_no : "-") + '</td><th>Status</th><td>' + ((response.status) ? response.status : '-') + '</td></tr>';
                html += '<tr><th>Loan Amount (Rs.)</th><td>' + ((response.loan_recommended) ? response.loan_recommended : '-') + '</td><th>Tenure as on ' + response.repayment_interest_date + ' (Days)</th><td>' + response.realdays + '</td></tr>';
                html += '<tr><th>ROI (%)</th><td>' + ((response.roi) ? response.roi : '-') + '</td><th>Interest as on ' + response.repayment_interest_date + ' (Rs.)</th><td>' + response.real_interest + '</td></tr>';
                html += '<tr><th>Disbursal Date</th><td>' + ((response.disbursal_date) ? response.disbursal_date : '-') + '</td><th title="Pre-Closure Discount : ' + ((diff_discount > 0) ? diff_discount : '0') + '">Repay Amount as on ' + response.repayment_interest_date + ' (Rs.)</th><td>' + response.repayment_with_real_interest + '</td></tr>';
                html += '<tr><th>Repay Date</th><td>' + ((response.repayment_date) ? response.repayment_date : '-') + '</td><th>Delay (Days)</th><td>' + ((response.penalty_days) ? response.penalty_days : 0) + '</td></tr>';
                html += '<tr><th>Tenure as on Repay Date (Days)</th><td>' + ((response.tenure) ? response.tenure : '-') + '</td><th>Penal ROI (%)</th><td>' + ((response.penal_roi) ? response.penal_roi : '-') + '</td></tr>';
                html += '<tr><th>Repay Amount (Rs.)</th><td>' + ((response.repayment_amount) ? response.repayment_amount : '-') + '</td><th>Penal Interest (Rs.)</th><td>' + ((response.penalty_interest) ? response.penalty_interest : '-') + '</td></tr>';

                html += '<tr></tr>';
                html += '<tbody></table>';

                html += '<table class="table table-bordered " style="background-color: #bbd2bb;"><tbody>';
                html += '<tr><th></th><th>&nbsp;Payable Amount</th><th>&nbsp;Received Amount</th><th>&nbsp;Discount Amount</th><th>&nbsp;Outstanding Amount</th></tr>';
                html += '<tr><th>&nbsp;Interest Amount</th><td>&nbsp;' + ((response.total_interest_amount) ? response.total_interest_amount : 0.00) + '</td><td>&nbsp;' + ((response.total_interest_amount_received) ? response.total_interest_amount_received : 0.00) + '</td><td>&nbsp;' + ((response.interest_discount_amount) ? response.interest_discount_amount : 0.00) + '</td><th>&nbsp;' + ((response.total_interest_amount_pending) ? response.total_interest_amount_pending : 0.00) + '</th></tr>';
                html += '<tr><th>&nbsp;Principle Amount</th><td>&nbsp;' + ((response.loan_recommended) ? response.loan_recommended : 0.00) + '</td><td>&nbsp;' + ((response.total_principle_amount_received) ? response.total_principle_amount_received : 0.00) + '</td><td>&nbsp;' + ((response.principle_discount_amount) ? response.principle_discount_amount : 0.00) + '</td><th>&nbsp;' + ((response.total_principle_amount_pending) ? response.total_principle_amount_pending : 0.00) + '</th></tr>';
                html += '<tr><th>&nbsp;Penalty Amount</th><td>&nbsp;' + ((response.penalty_interest) ? (response.penalty_interest) : 0.00) + '</td><td>&nbsp;' + ((response.total_penalty_interest_received) ? (response.total_penalty_interest_received) : 0.00) + '</td><td>&nbsp;' + ((response.penalty_discount_amount) ? response.penalty_discount_amount : 0.00) + '</td><th>&nbsp;' + ((response.total_penalty_interest_pending) ? (response.total_penalty_interest_pending) : 0.00) + '</th></tr>';
                html += '<tr><th>&nbsp;Grand Total</th><th>&nbsp;' + ((response.total_repayment_amount) ? (response.total_repayment_amount) : 0.00) + '</th><th>' + ((response.total_received_amount) ? response.total_received_amount : 0.00) + '</th><th>' + ((response.total_discount_amount) ? response.total_discount_amount : 0.00) + '</th><th>' + ((response.total_due_amount) ? response.total_due_amount : 0.00) + '</th></tr>';
                html += '<tbody></table>';

                html += '<table class="table table-bordered table-striped"><tbody>';

<?php if (in_array(agent, array("CO1", "CO2", "CO3", "CA", "SA"))) { ?>
                    //                    if (response.loan_no != '' && response.loan_no != null) {

                    html += '<tr><th class="thbg">Add to Blacklist</th><td colspan="4">';
                    if (response.lead_black_list_flag == 1) {
                        html += 'YES - Blacklisted';
                    } else {

                        html += '<input type="checkbox" name="viewBlackListBox" id="viewBlackListBox" onclick="viewBlackListBox()">';
                        html += '<div id="blackListDiv" style="display:none">';
                        html += '<select name="blackListReason" id="blackListReason" class="form-control" style="width: 250px; margin:10px 0px;"></select>';
                        html += '<textarea class="form-control" placeholder="Remark, Max 500 chars allowed." style="margin: 10px 0px;" rows="3" cols="6" maxlength="500" id="blackListReasonRemark" name="blackListReasonRemark" autocomplete="off" spellcheck="true"></textarea>';
                        html += '<input type="button" id="blackListReasonSave" class="btn btn-success" value="Save" onclick="addToBlackList(' + lead_id + ')"/>';
                        html += '</div>';
                    }

                    html += '</td></tr>';
                    //                    }
<?php } ?>

                html += '</thead></table>';
<?php if (in_array(agent, array("CR2", "CO1", "CO2", "CO3", "CA")) && in_array($leadDetails->lead_status_id, array(14, 19))) { ?>
                    html += '<table class="table table-bordered table-striped"><tbody>';
                    html += '<tr><th class="thbg">Generate Repayment Link</th><td colspan="4">';

                    html += '<input type="checkbox" name="viewGenerateRepayLinkBox" id="viewGenerateRepayLinkBox" onclick="viewGenerateRepayLinkBox()">';
                    html += '<div id="GenerateRepayLinkDiv" style="display:none">';
                    html += '<label for="Repayment Amount">Repayment Amount</label>';
                    html += '<input type="number" name="repay_loan_amount" id="repay_loan_amount" min="1" max="' + ((response.total_due_amount) ? response.total_due_amount : 0.00) + '" value="' + ((response.total_due_amount) ? response.total_due_amount : 0.00) + '" class="form-control" style="width: 250px; margin:10px 0px;">';
                    html += '<input type="button" id="generateRepayLinkSave" class="btn btn-success" value="Generate Repayment Link" onclick="generateRepayLink(' + lead_id + ')"/>';

                    html += '<p id="repay_encrypted_url_notes"></p>';
                    html += '<div id="repay_encrypted_url"></div>';
                    html += '</div>';
                    html += '</td></tr>';
                    html += '</thead></table>';
<?php } ?>
                $('#received_amount').val(response.total_due_amount);

                $('#loanStatus').html(html);
                $("#blackListReason").empty();
                $("#blackListReason").append('<option value="">Select Reason</option>');
                $.each(response.master_blacklist_reason, function (index, myarr) {
                    $("#blackListReason").append('<option value="' + myarr.id + '">' + myarr.reason + '</option>');
                });
            }
        });
    }

    function deleteCoustomerPayment(id, user_id)
    {
        $.ajax({
            url: '<?= base_url("deleteCoustomerPayment") ?>',
            type: 'POST',
            data: {id: id, user_id: user_id, csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    collectionHistory('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                } else {
                    catchError(response.err);
                }
            }
        });
    }

    function collectionHistory(lead_id)
    {
        $.ajax({
            url: '<?= base_url("collectionHistory") ?>',
            type: 'POST',
            data: {lead_id: lead_id, csrf_token},
            dataType: "json",
            success: function (response) {
                $('#recoveryHistory').html(response['recoveryData']);
            }
        });
    }

    function leadRecommend(lead_id)
    {
        $.ajax({
            url: '<?= base_url('leadRecommend') ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $('#LeadRecommend').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    localStorage.setItem('recommend', 1);
                    window.location.href = "<?= base_url("inProcess/") . $this->encrypt->encode('S2') ?>";
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#LeadRecommend').html('Recommend').prop('disabled', false);
            },
        });
    }

    function applicationRecommendation(lead_id)
    {

        $.ajax({
            url: '<?= base_url('PaydayLeadRecommendation') ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $('#LeadRecommend').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    localStorage.setItem('recommend', 1);
                    window.location.href = "<?= base_url('applicationinprocess/') . $this->encrypt->encode('S5') ?>";
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#LeadRecommend').html('Recommend').prop('disabled', false);
            },
        });
    }

    $('#divExpendReason3').hide();
    function addRemarksToggle(lead_id)
    {
<?php if (in_array($leadDetails->lead_status_id, array(30, 35, 37))) { ?>
            $('#btn_own_reason').html('<button class="btn btn-success" id="DisburseRecommend" onclick="disburseRecommend(&quot;' + lead_id + '&quot;)">Recommend</button>');
<?php } else if ((agent == "AC2" || agent == "CA") && in_array($leadDetails->lead_status_id, array(14))) { ?>
            $('#btn_own_reason').html('<button class="btn btn-success lead-sanction-button" id="DisburseWaived" onclick="disburseWaived(&quot;' + lead_id + '&quot;)">Waived</button>');
<?php } ?>
        $('#divExpendReason3').toggle();
    }

    function disburseRecommend(lead_id)
    {
        var customer_id = "<?= strval($leadDetails->customer_id) ?>";
        var own_remark = $("#own_remark").val();

        $.ajax({
            url: '<?= base_url('disburseRecommend') ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, customer_id: customer_id, remarks: own_remark, csrf_token},
            beforeSend: function () {
                $('#DisburseRecommend').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    window.location.href = "<?= base_url() ?>";
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#DisburseRecommend').html('Recommend').prop('disabled', false);
            },
        });
    }

    function disburseWaived(lead_id)
    {
        var customer_id = "<?= strval($leadDetails->customer_id) ?>";
        var own_remark = $("#own_remark").val();

        $.ajax({
            url: '<?= base_url('disburseWaived') ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, customer_id: customer_id, remarks: own_remark, csrf_token},
            beforeSend: function () {
                $('#DisburseWaived').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    window.location.href = "<?= base_url('disbursed/S14') ?>";
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#DisburseWaived').html('Waived').prop('disabled', false);
            },
        });
    }

    function getPersonalDetails(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getPersonalDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    var res = response['personalDetails1'];
                    $("#p_first_name").val((res.first_name) ? res.first_name : '');
                    $("#p_middle_name").val((res.middle_name) ? res.middle_name : '');
                    $("#p_sur_name").val((res.sur_name) ? res.sur_name : '');
                    $("#p_gender").val((res.gender) ? res.gender : '').prop("selected", "selected");
                    $("#p_dob").val((res.dob) ? res.dob : '');
                    $("#p_pancard").val((res.pancard) ? res.pancard : '');
                    $("#p_mobile").val((res.mobile) ? res.mobile : '');
                    $("#p_alternate_mobile").val((res.alternate_mobile != null && res.alternate_mobile != 0) ? res.alternate_mobile : '');
                    $("#p_email").val((res.email) ? res.email : '');
                    $("#p_alternate_email").val((res.alternate_email) ? res.alternate_email : '');
                    $("#screenedBy").val((res.screenedBy) ? res.screenedBy.toUpperCase() : '');
                    $("#screenedOn").val((res.screenedOn) ? res.screenedOn : '');


                    var html = '<table class="table table-bordered table-striped">';
                    html += '<tbody>';
                    html += '<tr><th>First&nbsp;Name</th><td>' + res.first_name + '</td><th>Middle&nbsp;Name</th><td>' + ((res.middle_name) ? res.middle_name : ' ') + '</td></tr>';
                    html += '<tr><th>Surname</th><td>' + ((res.sur_name) ? res.sur_name : '-') + '</td><th>Gender</th><td>' + ((res.gender) ? res.gender : '-') + '</td></tr>';
                    html += '<tr><th>DOB</th><td>' + ((res.dob) ? res.dob : '-') + '</td><th>PAN</th><td>' + ((res.pancard) ? res.pancard : '-') + '</td></tr>';
                    html += '<tr><th>Mobile</th><td>' + ((res.mobile) ? res.mobile : '-') + '</td><th>Alternate&nbsp;Mobile</th><td>' + ((res.alternate_mobile != null && res.alternate_mobile != 0) ? res.alternate_mobile : '-') + '</td></tr>';
                    html += '<tr><th>Email&nbsp;Personal</th><td>' + ((res.email) ? res.email : '-') + '</td><th>Email&nbsp;Office</th><td>' + ((res.alternate_email) ? res.alternate_email : '-') + '</td></tr>';
                    html += '<tr><th>Screened&nbsp;By</th><td>' + ((res.screenedBy) ? res.screenedBy : '-') + '</td><th>Screened&nbsp;On</th><td>' + ((res.screenedOn) ? res.screenedOn : '-') + '</td></tr>';
                    html += '</tbody>';
                    html += '</table>';

                    $('#ViewPersonalDetails').html(html);
                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function getResidenceDetails(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getResidenceDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    var res = response['residenceDetails'];

                    getState(res.state_id, 1);
                    getCity(res.city_id, res.state_id, 1);
                    getPincode(res.cr_residence_pincode, res.city_id, 1);

                    getState(res.aa_current_state_id, 2);
                    getCity(res.aa_current_city_id, res.aa_current_state_id, 2);
                    getPincode(res.aa_cr_residence_pincode, res.aa_current_city_id, 2);

//                    var current_city = res.current_city;

                    $("#hfBulNo1").val((res.current_house != '') ? res.current_house : '');
                    $("#lcss1").val((res.current_locality != '') ? res.current_locality : '');
                    $("#lankmark1").val((res.current_landmark != '') ? res.current_landmark : '');
                    $("#state1").val((res.state_id > 0) ? res.state_id : '');
                    $("#city1").val((res.city_id > 0) ? res.city_id : '');
                    $("#pincode1").val((res.cr_residence_pincode > 0) ? res.cr_residence_pincode : '');
//                    $("#district1").val((res.current_district != '') ? res.current_district : '');


                    //   $('#city1').append('<option value="' + res.current_city + '">' + current_city + '</option>');
//                    $('#city1').append($("<option></option>").attr("value", current_city).text(current_city));

                    $("#addharAddressSameasAbove").val((res.aa_same_as_current_address == '') ? 'NO' : res.aa_same_as_current_address);
                    if (res.aa_same_as_current_address == 'YES') {
                        $("#addharAddressSameasAbove").prop('checked', true);
                    } else {
                        $("#addharAddressSameasAbove").prop('checked', false);
                    }
                    //aa_current_landmark

                    $("#res_aadhar").val((res.aadhar_no != '') ? res.aadhar_no : '');
                    $("#hfBulNo2").val((res.aa_current_house != '') ? res.aa_current_house : '');
                    $("#lcss2").val((res.aa_current_locality != '') ? res.aa_current_locality : '');
                    $("#landmark2").val((res.aa_current_landmark != '') ? res.aa_current_landmark : '');

                    $("#state2").val((res.aa_current_state_id > 0) ? res.aa_current_state_id : '');
                    $("#city2").val((res.aa_current_city_id > 0) ? res.aa_current_city_id : '');

                    $("#pincode2").val((res.aa_cr_residence_pincode > 0) ? res.aa_cr_residence_pincode : '');
                    $("#district2").val((res.aa_current_district != '') ? res.aa_current_district : '');
                    $("#presentResidenceType").val((res.current_residence_type != '') ? res.current_residence_type : '');
                    $("#residenceSince").val((res.current_residence_since != '') ? res.current_residence_since : '');


                    var html = '<table class="table table-bordered table-striped"><tbody>';

                    html += '<tr><th>Address Line 1</th><td>' + ((res.current_house == '') ? '-' : res.current_house) + '</td><th>Address Line 2</th><td>' + ((res.current_locality == '') ? '-' : res.current_locality) + '</td></tr>';
                    html += '<tr><th>Address Landmark</th><td>' + ((res.current_landmark == '') ? '-' : res.current_landmark) + '</td><th>State</th><td>' + ((res.res_state == '') ? '-' : res.res_state) + '</td></tr>';
                    html += '<tr><th>City</th><td>' + ((res.res_city == '') ? '-' : res.res_city) + '</td><th>Pincode</th><td>' + ((res.cr_residence_pincode == '') ? '-' : res.cr_residence_pincode) + '</td></tr>';
                    html += '<tr><th>Present&nbsp;Residence&nbsp;Type</th><td>' + ((res.current_residence_type == '') ? '' : res.current_residence_type) + '</td><th>Residing&nbsp;Since</th><td>' + ((res.current_residence_since == '') ? '-' : res.current_residence_since) + '</td></tr>';
                    html += '<tr><th>Aadhaar (Last 4 digit)</th><td colspan="3">' + ((res.aadhar_no == '') ? '-' : res.aadhar_no) + '</td></tr>';
                    html += '<tr><th>Address Line 1</th><td>' + ((res.aa_current_house == '') ? '-' : res.aa_current_house) + '</td><th>Address Line 2</th><td>' + ((res.aa_current_locality == '') ? '-' : res.aa_current_locality) + '</td></tr>';
                    html += '<tr><th>Address Landmark</th><td>' + ((res.aa_current_landmark == '') ? '-' : res.aa_current_landmark) + '</td><th>State</th><td>' + ((res.aadhar_state == '') ? '-' : res.aadhar_state) + '</td></tr>';
                    html += '<tr><th>City</th><td>' + ((res.aadhar_city == '') ? '-' : res.aadhar_city) + '</td><th>Pincode</th><td>' + ((res.aa_cr_residence_pincode == '') ? '-' : res.aa_cr_residence_pincode) + '</td></tr>';
                    html += '</tbody></table>';
                    $('#viewResidenceDetails').html(html);




                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function getEmploymentDetails(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getEmploymentDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    $("#department").empty();
                    $("#department").append('<option value="">SELECT</option>');
                    $.each(response['department'], function (index, myarr) {
                        $('#department').append($("<option></option>").attr("value", myarr.department_name).text(myarr.department_name));
                    });

                    $("#EmpOccupation").empty();
                    $("#EmpOccupation").append('<option value="">SELECT</option>');
                    $.each(response['EmpOccupation'], function (index, myarr) {
                        $('#EmpOccupation').append($("<option></option>").attr("value", myarr.m_occupation_id).text(myarr.m_occupation_name));
                    });

                    var res = response['employmentDetails'];

                    getState(res.state_id, 3);
                    getCity(res.city_id, res.state_id, 3);
                    getPincode(res.emp_pincode, res.city_id, 3);

                    $("#officeEmpName").val((res.employer_name != '') ? res.employer_name : '');
                    $("#hfBulNo3").val((res.emp_house != '') ? res.emp_house : '');
                    $("#lcss3").val((res.emp_street != '') ? res.emp_street : '');
                    $("#lankmark3").val((res.emp_landmark != '') ? res.emp_landmark : '');
                    $("#state3").val((res.state_id > 0) ? res.state_id : '');
                    $("#city3").val((res.city_id > 0) ? res.city_id : '');

//                    $("#city3").append('<option value="' + res.emp_city + '">' + res.emp_city + '</option>');
                    $("#pincode3").val((res.emp_pincode > 0) ? res.emp_pincode : '');
                    $("#district3").val((res.emp_district != '') ? res.emp_district : '');
                    $("#website").val((res.emp_website != '') ? res.emp_website : '');
                    $("#employeeType").val((res.emp_employer_type != '') ? res.emp_employer_type : '');
                    $("#industry").val((res.industry != '') ? res.industry : '');
                    $("#sector").val((res.sector != '') ? res.sector : '');
                    $("#department").val((res.emp_department != '') ? res.emp_department : '');

                    if (res.emp_department != '') {
                        $("#department").append('<option value="' + res.emp_department + '">' + res.emp_department + '</option>');
                    }

                    $("#designation").val((res.emp_designation != '') ? res.emp_designation : '');
                    $("#employedSince").val((res.emp_residence_since != '') ? res.emp_residence_since : '');
                    $("#presentServiceTenure").val((res.presentServiceTenure != '') ? res.presentServiceTenure : '');
                    $("#EmpOccupation").val((res.emp_occupation_id != '') ? res.emp_occupation_id : '');

                    if (res.emp_work_mode == 'WFH') {
                        $("#emp_work_mode_id1").prop('checked', true);
                    } else if (res.emp_work_mode == 'WFO') {
                        $("#emp_work_mode_id2").prop('checked', true);
                    }

                    var html = '<table class="table table-bordered table-striped"><tbody>';
                    html += '<tr><th>Office/&nbsp;Employer&nbsp;Name</th><td>' + ((res.employer_name == null || res.employer_name == '') ? '-' : res.employer_name) + '</td><th>Address Line 1</th><td>' + ((res.emp_house == null || res.emp_house == '') ? '-' : res.emp_house) + '</td></tr>';
                    html += '<tr><th>Address Line 2</th><td>' + ((res.emp_street == null || res.emp_street == '') ? '-' : res.emp_street) + '</td><th>Address Landmark</th><td>' + ((res.emp_landmark == null || res.emp_landmark == '') ? '-' : res.emp_landmark) + '</td></tr>';
                    html += '<tr><th>State</th><td>' + ((res.state == null || res.state == '') ? '-' : res.state) + '</td><th>City</th><td>' + ((res.city == null || res.city == '') ? '-' : res.city) + '</td></tr>';
                    // <th>District</th><td>' + ((res.emp_district == null) ? '-' : res.emp_district) + '</td>
                    html += '<tr><th>Pincode</th><td colspan="3">' + ((res.emp_pincode == null || res.emp_pincode == '0') ? '-' : res.emp_pincode) + '</td></tr>';
                    html += '<tr><th>Website</th><td>' + ((res.emp_website == null || res.emp_website == '') ? '-' : res.emp_website) + '</td><th>Employer&nbsp;Type</th><td>' + ((res.emp_employer_type == null || res.emp_employer_type == '') ? '-' : res.emp_employer_type) + '</td></tr>';
                    html += '<tr><th>Industry</th><td>' + ((res.industry == null || res.industry == '') ? '-' : res.industry) + '</td><th>Sector</th><td>' + ((res.sector == null || res.sector == '') ? '-' : res.sector) + '</td></tr>';
                    html += '<tr><th>Department</th><td>' + ((res.emp_department == null || res.emp_department == '') ? '-' : res.emp_department) + '</td><th>Designation</th><td>' + ((res.emp_designation == null || res.emp_designation == '') ? '-' : res.emp_designation) + '</td></tr>';
                    html += '<tr><th>Employed&nbsp;Since</th><td>' + ((res.emp_residence_since == null || res.emp_residence_since == '') ? '-' : res.emp_residence_since) + '</td><th>Present&nbsp;Service&nbsp;Tenure</th><td>' + ((res.presentServiceTenure == null || res.presentServiceTenure == '') ? '-' : res.presentServiceTenure) + '</td></tr>';
                    html += '<tr><th>Emploayee Occupation</th><td>' + ((res.m_occupation_name == null || res.m_occupation_name == '') ? '-' : res.m_occupation_name) + '</td><th>Work&nbsp;Mode</th><td>' + ((res.emp_work_mode == null || res.emp_work_mode == '') ? '' : res.emp_work_mode) + '</td><</tr>';

                    html += '</tbody></table>';

                    $('#ViewEmploymentDetails').html(html);


                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function getReferenceDetails(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getReferenceDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    var i = 1;
                    var html = '<table class="table table-striped table-bordered table-hover"  style="border: 1px solid #dde2eb"><thead><tr><th class="whitespace data-fixed-columns"><b>Sno.</b></th><th class="whitespace data-fixed-columns"><b>Name</th><th class="whitespace"><b>Reference.</b></th><th class="whitespace"><b>Mobile.</b></th><?php if ((agent == "CR2" || agent == "CA" || agent == "SA") && ($leadDetails->stage == "S5" || $leadDetails->stage == "S6" || $leadDetails->stage == "S11")) { ?><th class="whitespace"><b>Action</b></th><?php } ?></tr></thead>';
                    html += '<tbody>';

                    if (response['refrence'] != "No") {
                        $.each(response['refrence'], function (index, myarr) {
                            html += '<tr id="remove ' + myarr.lcr_id + '">';
                            html += '<td class="whitespace">' + i + '</td>';
                            html += '<td class="whitespace">' + myarr.lcr_name + '</td>';
                            html += '<td class="whitespace">' + myarr.mrt_name + '</td>';
                            html += '<td class="whitespace">' + myarr.lcr_mobile + '</td>';
<?php if (in_array(agent, ["CR2", "CA", "SA"]) && in_array($leadDetails->stage, ["S5", "S6", "S11"])) { ?>
                                html += '<td class="whitespace"> <span onclick="updateReference(' + myarr.lcr_id + ', ' + myarr.lcr_relationType + ', ' + myarr.lcr_mobile + ', \'' + myarr.lcr_name + '\')" class="fa fa-pencil-square-o"></span> &nbsp;/ &nbsp; <span onclick="deleterefrence(' + myarr.lcr_id + ')" class="fa fa-trash"></span></td>';
<?php } ?>
                            html += '</tr>';
                            i++;
                        });
                    } else {
                        html += '<tr><td class="whitespace text-center" colspan="5" style="color : red;">No record Found</td></tr>';
                    }
                    html += '</tbody></table>';
                    $('#viewReferenceDetails').html(html);
                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function getCustomerBanking(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getCustomerBanking") ?>',
                type: 'POST',
                data: {lead_id: lead_id, csrf_token},
                dataType: "json",
                success: function (response) {
                    $('#disbursalBanking').html('');
                    if (response.disbursalBankCount > 0)
                    {
                        var res = response.disbursalBank;
                        var html1 = '<table class="table table-bordered table-striped"><tbody>';
                        html1 += '<tr><th>Beneficiary&nbsp;Name</th><td>' + res.beneficiary_name + '</td><th>Verified&nbsp;ON</th><td>' + res.updated_on + '</td></tr>';
                        html1 += '<tr><th>Bank&nbsp;A/C&nbsp;No.</th><td>' + res.account + '</td><th>IFSC&nbsp;Code</th><td>' + res.ifsc_code + '</td></tr>';
                        html1 += '<tr><th>Bank&nbsp;A/C&nbsp;Type</th><td>' + res.account_type + '</td><th>Bank&nbsp;Name</th><td>' + res.bank_name + '</td></tr>';
                        html1 += '<tr><th>Branch&nbsp;Name</th><td>' + res.branch + '</td><th>Verification&nbsp;Status</th><td style="color: green">' + res.account_status + '</td></tr>';
                        //html1 += '<tr><th>Verified&nbsp;ON</th><td colspan="3">' + res.updated_on + '</td></tr>';
                        html1 += '<tbody></table>';
                        $('#disbursalBanking').html(html1);
                    }

                    var html = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th class="whitespace">#</th><th class="whitespace">Lead&nbsp;ID</th><th class="whitespace">Beneficiary&nbsp;Name</th><th class="whitespace">Bank&nbsp;A/C&nbsp;No.</th><th class="whitespace">IFSC&nbsp;Code</th><th class="whitespace">Bank&nbsp;A/C&nbsp;Type</th><th class="whitespace">Bank&nbsp;Name</th><th class="whitespace">Branch&nbsp;Name</th><th class="whitespace">Active&nbsp;Account</th><th class="whitespace">Remark</th><th class="whitespace">Created&nbsp;ON</th><th class="whitespace">Updated&nbsp;ON</th></tr></thead><tbody>';
                    if (response.allDisbursalBankCount > 0)
                    {
                        var i = 1;
                        var html2 = "<option value=''>SELECT</option>";
                        $.each(response.allDisbursalBank, function (key, value) {
                            html += '<tr><td class="whitespace">' + i + '</td><td class="whitespace">' + value.lead_id + '</td><td class="whitespace">' + value.beneficiary_name + '</td><td class="whitespace">' + value.account + '</td><td class="whitespace">' + value.ifsc_code + '</td><td class="whitespace">' + value.account_type + '</td><td class="whitespace">' + value.bank_name + '</td><td class="whitespace">' + value.branch + '</td><td class="whitespace">' + ((value.account_status == null || value.account_status == '') ? '-' : value.account_status) + '</td><td class="whitespace">' + ((value.remark == null || value.remark == '') ? '-' : value.remark) + '</td><td class="whitespace">' + value.created_on + '</td><td class="whitespace">' + ((value.updated_on == null) ? '-' : value.updated_on) + '</td></tr>';
                            $('#list_bank_AC_No option').val(value.account);
                            html2 += ("<option value='" + value.id + "'>" + value.account + "</option>");
                            i++;
                        });
                        $('#list_bank_AC_No').html(html2);
                        html += '</tbody>';
                    } else {
                        html += '<tr><td colspan="11" class="text-danger text-center">No Record Found.</td></tbody>';
                    }
                    html += '</table></div>';
                    $('#viewBankingDetails').html(html);
                }
            });
        } else {
            catchError("Customer Id Not Found.");
        }
    }

    // function getListsOfCustBankAccount(customer_id)
    // {
    //     if(customer_id != "") {
    //         $.ajax({
    //             url : '<?= base_url("getListsOfCustBankAccount") ?>',
    //             type : 'POST',
    //             data : {customer_id : customer_id, csrf_token},
    //             dataType : "json",
    //             success : function(response){
    //                 var html = '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>#</th><th>Customer&nbsp;ID</th><th>Bank&nbsp;A/C&nbsp;No.</th><th>Reconfirm&nbsp;Bank&nbsp;A/C&nbsp;No.</th><th>IFSC&nbsp;Code</th><th>Bank&nbsp;A/C&nbsp;Type</th><th>Bank&nbsp;Name</th><th>Branch&nbsp;Name</th><th>Active&nbsp;Account</th><th>Remark</th><th>Created&nbsp;ON</th><th>Updated&nbsp;ON</th></tr></thead><tbody>';
    //                 if(response.allDisbursalBankCount > 0)
    //                 {
    //                     var i = 1;
    //                     $.each(response.allDisbursalBank, function(key, value){
    //                         html += '<tr><td>'+ i +'</td><td>'+ value.customer_id +'</td><td>'+ value.account +'</td><td>'+ value.confirm_account +'</td><td>'+ value.ifsc_code +'</td><td>'+ value.account_type +'</td><td>'+ value.bank_name +'</td><td>'+ value.branch +'</td><td>'+ value.account_status +'</td><td>'+ value.remark +'</td><td>'+ value.created_on +'</td><td>'+ value.updated_on +'</td></tr>';
    //                         i++;
    //                     }); 
    //                     html += '</tbody>';
    //                 }else{
    //                     html += '<tr><td colspan="11" class="text-danger text-center">No Record Found.</td></tbody>';
    //                 }
    //                 html += '</table></div>';
    //                 $('#viewBankingDetails').html(html);
    //             }
    //         });
    //     } else {
    //         catchError("Customer Id Not Found.");
    //     }
    // }

    function getCam(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getCAMDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
<?php if (company_id == 1 && product_id == 1) { ?>
                        getPaydayCAM(response);
<?php } if (company_id == 1 && product_id == 2) { ?>
                        getLACCAM(response);
<?php } ?>
                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function getLACCAM(response)
    {
        $('#userType').val(response['camDetails'].userType);
        $('#status').val(response['camDetails'].status);
        $('#cibil').val(response['camDetails'].cibil);
        $('#Active_CC').val(response['camDetails'].Active_CC);
        $('#cc_statementDate').val(response['camDetails'].cc_statementDate);
        $('#cc_paymentDueDate').val(response['camDetails'].cc_paymentDueDate);
        $('#customer_bank_name').val(response['camDetails'].customer_bank_name);
        // $('#account_type').val(response['camDetails'].account_type);
        $('#account_type').empty();
        var s = "";
        if (response['camDetails'].account_type == "AMEX") {
            s = 'selected';
            $('#account_type').html('<option value="' + response['camDetails'].account_type + '" ' + s + '>' + response['camDetails'].account_type + '</option>');
        } else {
            var accountTypeArr = ['MASTER', 'VISA'];
            $.each(accountTypeArr, function (index, arr) {
                s = "";
                if (response['camDetails'].account_type == arr) {
                    s = 'selected';
                }
                $('#account_type').append('<option value="' + arr + '" ' + s + '>' + arr + '</option>');
            });
        }

        $('#customer_account_no').val(response['camDetails'].customer_account_no);
        $('#customer_confirm_account_no').val(response['camDetails'].customer_confirm_account_no);
        $('#customer_name').val(response['camDetails'].customer_name);
        $('#cc_limit').val(response['camDetails'].cc_limit);
        $('#cc_outstanding').val(response['camDetails'].cc_outstanding);
        $('#max_eligibility').val(response['camDetails'].max_eligibility);

        if (response['camDetails'].cc_name_Match_borrower_name == "YES") {
            $('#cc_name_Match_borrower_name_YES').prop('checked', true);
            $('#cc_name_Match_borrower_name_NO').prop('checked', false);
        } else {
            $('#cc_name_Match_borrower_name_YES').prop('checked', false);
            $('#cc_name_Match_borrower_name_NO').prop('checked', true);
        }

        if (response['camDetails'].emiOnCard == "YES") {
            $('#emiOnCard_YES').prop('checked', true);
            $('#emiOnCard_NO').prop('checked', false);
        } else {
            $('#emiOnCard_YES').prop('checked', false);
            $('#emiOnCard_NO').prop('checked', true);
        }

        if (response['camDetails'].DPD30Plus == "YES") {
            $('#DPD30Plus_YES').prop('checked', true);
            $('#DPD30Plus_NO').prop('checked', false);
        } else {
            $('#DPD30Plus_YES').prop('checked', false);
            $('#DPD30Plus_NO').prop('checked', true);
        }

        if (response['camDetails'].cc_statementAddress == "YES") {
            $('#cc_statementAddress_YES').prop('checked', true);
            $('#cc_statementAddress_NO').prop('checked', false);
        } else {
            $('#cc_statementAddress_YES').prop('checked', false);
            $('#cc_statementAddress_NO').prop('checked', true);
        }

        if (response['camDetails'].last3monthDPD == "YES") {
            $('#last3monthDPD_YES').prop('checked', true);
            $('#last3monthDPD_NO').prop('checked', false);
            $('#divhigherDPDLast3month').show();
        } else {
            $('#divhigherDPDLast3month').hide();
            $('#last3monthDPD_YES').prop('checked', false);
            $('#last3monthDPD_NO').prop('checked', true);
        }

        $('#higherDPDLast3month').val(response['camDetails'].higherDPDLast3month);


        if (response['camDetails'].isDisburseBankAC == "YES") {
            $('#isDisburseBankAC').prop('checked', true);
            $('#customer_ifsc_code').html('<option value="' + response['camDetails'].bankIFSC_Code + '">' + response['camDetails'].bankIFSC_Code + '</option>');
            $('#bank_name').val(response['camDetails'].bank_name);
            $('#bank_branch').val(response['camDetails'].bank_branch);
            $('#bankA_C_No').val(response['camDetails'].bankA_C_No);
            $('#confBankA_C_No').val(response['camDetails'].confBankA_C_No);
            $('#bankHolder_name').val(response['camDetails'].bankHolder_name);
            $('#bank_account_type').val(response['camDetails'].bank_account_type);

            $('#disbursalBankDetails').show();
        } else {
            $('#disbursalBankDetails').hide();
            $('#isDisburseBankAC').prop('uncheck', false);
            $('#bankIFSC_Code', '#bank_name', '#bank_branch', '#bankA_C_No', '#confBankA_C_No', '#bankHolder_name', '#bank_account_type').val('');
        }
        $('#loan_applied').val(response['leadDetails'].loan_amount);
        $('#loan_recommended').val(Math.round(response['camDetails'].loan_recommended));
        $('#processing_fee').val(Math.round(response['camDetails'].processing_fee));
        $('#roi').val(response['camDetails'].roi);
        $('#adminFeeWithGST').val(Math.round(response['camDetails'].adminFeeWithGST));
        $('#net_disbursal_amount').val(Math.round(response['camDetails'].net_disbursal_amount));
        $('#disbursal_date').val(response['camDetails'].disbursal_date);
        $('#repayment_date').val(response['camDetails'].repayment_date);
        $('#tenure').val(response['camDetails'].tenure);
        $('#repayment_amount').val(Math.round(response['camDetails'].repayment_amount));
        $('#special_approval').val(response['camDetails'].special_approval);
        $('#deviationsApprovedBy').val(response['camDetails'].deviationsApprovedBy);
        $('#changeROI').val(response['camDetails'].changeROI);
        $('#changeFee').val(response['camDetails'].changeFee);
        $('#changeLoanAmount').val(response['camDetails'].changeLoanAmount);
        $('#changeTenure').val(response['camDetails'].changeTenure);
        $('#changeRTR').val(response['camDetails'].changeRTR);
        $('#remark').val(response['camDetails'].remark);
        var status = $('#status').val();

        var html = '<table class="table table-bordered table-striped">';
        html += '<tbody>';
        html += '<tr><th>User Type</th><td>' + response['camDetails'].userType + '</td><th>Status</th><td>' + response['camDetails'].status + '</td></tr>';
        html += '<tr><th>CIBIL Score</th><td>' + response['camDetails'].cibil + '</td><th>No of Active CC</th><td>' + response['camDetails'].Active_CC + '</td></tr>';
        html += '<tr><th>CC Bank</th><td>' + response['camDetails'].customer_bank_name.toUpperCase() + '</td><th>CC Type</th><td>' + response['camDetails'].account_type.toUpperCase() + '</td></tr>';
        html += '<tr><th>CC No.</th><td>' + response['camDetails'].customer_account_no + '</td><th>Confirm CC No.</th><td>' + response['camDetails'].customer_confirm_account_no + '</td></tr>';
        html += '<tr><th>CC Statement Date.</th><td>' + response['camDetails'].cc_statementDate + '</td><th>CC Payment Due Date.</th><td>' + response['camDetails'].cc_paymentDueDate + '</td></tr>';
        html += '<tr><th>CC Limit</th><td>' + response['camDetails'].cc_limit + '</td><th>CC Outstanding</th><td>' + response['camDetails'].cc_outstanding + '</td></tr>';
        html += '<tr><th>Name As on Card</th><td>' + response['camDetails'].customer_name + '</td><th>Max Eligibility</th><td>' + response['camDetails'].max_eligibility + '</td></tr>';
        html += '<tr><th>CC Name matches with Borrower Name ?</th><td colspan="3">' + response['camDetails'].cc_name_Match_borrower_name + '</td></tr>';
        html += '<tr><th>EMI on Card ?</th><td colspan="3">' + response['camDetails'].emiOnCard + '</td></tr>';
        html += '<tr><th>30+ DPD in last 3 mths in any CC ?</th><td colspan="3">' + response['camDetails'].DPD30Plus + '</td></tr>';
        html += '<tr><th>CC Statement Address same as Present address ?</th><td colspan="3">' + response['camDetails'].cc_statementAddress + '</td></tr>';
        html += '<tr><th>DPD On CC in Last 3 months</th><td colspan="3">' + response['camDetails'].last3monthDPD + '</td></tr>';
        // html += '<tr><th>Disburse to Bank Account ?</th><td colspan="3">'+ response['camDetails'].higherDPDLast3month +'</td></tr>';
        html += '<tr><th>Is Disburse to Bank Account ?</th><td colspan="3">' + response['camDetails'].isDisburseBankAC + '</td></tr>';
        html += '<tr><th>IFSC Code</th><td colspan="3">' + response['camDetails'].bankIFSC_Code + '</td></tr>';
        html += '<tr><th>Bank Name</th><td>' + response['camDetails'].bank_name + '</td><th>Bank Branch</th><td>' + response['camDetails'].bank_branch + '</td></tr>';
        html += '<tr><th>A/C No.</th><td>' + response['camDetails'].bankA_C_No + '</td><th>Confirm A/C No.</th><td>' + response['camDetails'].confBankA_C_No + '</td></tr>';
        html += '<tr><th>A/C Holder Name</th><td>' + response['camDetails'].bankHolder_name + '</td><th>Account Type</th><td>' + response['camDetails'].bank_account_type + '</td></tr>';
        html += '<tr><th>Loan Applied (Rs.)</th><td>' + response['camDetails'].loan_applied + '</td><th>Loan Recommended (Rs.)</th><td>' + response['camDetails'].loan_recommended + '</td></tr>';
        html += '<tr><th>Admin Fee (Rs.)</th><td>' + response['camDetails'].processing_fee + '</td><th>ROI (%)</th><td>' + response['camDetails'].roi + '</td></tr>';
        html += '<tr><th>Admin Fee with GST (18 %) (Rs.)</th><td>' + response['camDetails'].adminFeeWithGST + '</td><th>Net Disbursal Amount (Rs.)</th><td>' + response['camDetails'].net_disbursal_amount + '</td></tr>';
        html += '<tr><th>Disbursal Date</th><td>' + response['camDetails'].disbursal_date + '</td><th>Repayment Date</th><td>' + response['camDetails'].repayment_date + '</td></tr>';
        html += '<tr><th>Tenure (days)</th><td>' + response['camDetails'].tenure + '</td><th>Repayment Amount (Rs.)</th><td>' + response['camDetails'].repayment_amount + '</td></tr>';
        html += '<tr><th>Reference</th><td>' + response['camDetails'].special_approval + '</td><th>Deviations Approved By</th><td>' + response['camDetails'].deviationsApprovedBy + '</td></tr>';
        html += '<tr><th>Change in ROI : </th><td>' + response['camDetails'].changeROI + '</td><th>Change in Fees : </th><td>' + response['camDetails'].changeFee + '</td></tr>';
        html += '<tr><th>Higher Loan amount : </th><td>' + response['camDetails'].changeLoanAmount + '</td><th>Tenor more than norms : </th><td>' + response['camDetails'].changeTenure + '</td></tr>';
        html += '<tr><th>Note</th><td colspan="3">' + response['camDetails'].remark + '</td></tr>';

        html += '</tbody>';
        html += '</table>';
        $('#ViewCAMDetails').html(html);
    }

    function calculateMedianSalary()
    {
        calculateAmount();
        var s_cr1 = $('#salary_credit1_amount').val();
        var s_cr2 = $('#salary_credit2_amount').val();
        var s_cr3 = $('#salary_credit3_amount').val();

        if (s_cr1 != "" || s_cr2 != "" || s_cr3 != "")
        {
            var salaryAmt = s_cr1 + '-' + s_cr2 + '-' + s_cr3;
            $.ajax({
                url: "<?= base_url('averageSalary/'); ?>" + salaryAmt,
                type: "POST",
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    $('#median_salary').val(response['average_salary']);
                    $('#salary_variance').val(response['salary_variance']);
                }
            });
        }
    }

    function calculateSalaryOnTime()
    {
        var s_cr1 = $('#salary_credit1_date').val();
        var s_cr2 = $('#salary_credit2_date').val();
        var s_cr3 = $('#salary_credit3_date').val();

        if (s_cr1 != "" && s_cr2 != "" && s_cr3 != "")
        {
            const words1 = s_cr1.split('-');
            const words2 = s_cr2.split('-');
            const words3 = s_cr3.split('-');

            var date = words1[0] + '-' + words2[0] + '-' + words3[0];
            $.ajax({
                url: "<?= base_url('calculateMedian/'); ?>" + date,
                type: "POST",
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    $('#salary_on_time').val(response['salary_on_time']);
                    // $('#next_pay_date').val(response['next_pay_date']);
                }
            });
        }
    }

    function getPaydayCAM(response)
    {
        var res = response.getCamDetails;
        $('#ntc').val(((res.ntc != undefined) ? res.ntc : ((response.calculation.ntc != undefined) ? response.calculation.ntc : '')));
        $('#cibil').val(((res.cibil != null) ? res.cibil : ''));
        $('#run_other_pd_loan').val((res.run_other_pd_loan) ? res.run_other_pd_loan : "");
        $('#delay_other_loan_30_days').val((res.delay_other_loan_30_days) ? res.delay_other_loan_30_days : "");
        $('#job_stability').val(((res.job_stability) ? res.job_stability : ((response.calculation.job_stability != undefined) ? response.calculation.job_stability : '')));
        $('#city_category').val((res.city_category) ? res.city_category : "");
        // $('#salary_credit1').val((res.salary_credit1) ? res.salary_credit1 : "-");
        $('#salary_credit1_date').val((res.salary_credit1_date) ? res.salary_credit1_date : "");
        $('#salary_credit1_amount').val((res.salary_credit1_amount) ? res.salary_credit1_amount : '');
        // $('#salary_credit2').val((res.salary_credit2) ? res.salary_credit2 : "");
        $('#salary_credit2_date').val((res.salary_credit2_date) ? res.salary_credit2_date : "");
        $('#salary_credit2_amount').val((res.salary_credit2_amount) ? res.salary_credit2_amount : '');
        // $('#salary_credit3').val((res.salary_credit3) ? res.salary_credit3 : "");
        $('#salary_credit3_date').val((res.salary_credit3_date) ? res.salary_credit3_date : "");
        $('#salary_credit3_amount').val((res.salary_credit3_amount) ? res.salary_credit3_amount : '');
        $('#next_pay_date').val((res.next_pay_date) ? res.next_pay_date : "");
        $('#median_salary').val((res.median_salary) ? res.median_salary : 0);
        $('#salary_variance').val((res.salary_variance) ? res.salary_variance : "");
        $('#salary_on_time').val((res.salary_on_time) ? res.salary_on_time : "");
        $('#borrower_age').val(((res.borrower_age != undefined) ? res.borrower_age : ((response.calculation.borrower_age != undefined) ? response.calculation.borrower_age : '')));
        $('#end_use').val((res.end_use) ? res.end_use : res.purpose);
        $('#eligible_foir_percentage').val((res.eligible_foir_percentage) ? res.eligible_foir_percentage : "0");
        $('#eligible_loan').val((res.eligible_loan) ? res.eligible_loan : "0");
        $('#loan_recommended').val(Math.round(res.loan_recommended) ? Math.round(res.loan_recommended) : '<?= round($leadDetails->loan_amount) ?>');
        $('#final_foir_percentage').val((res.final_foir_percentage) ? res.final_foir_percentage : "0");
        $('#foir_enhanced_by').val((res.foir_enhanced_by) ? res.foir_enhanced_by : "0");
        $('#processing_fee_percent').val((res.processing_fee_percent) ? res.processing_fee_percent : "11");
        $('#admin_fee').val((res.admin_fee) ? res.admin_fee : "0");
        $('#disbursal_date').val((res.disbursal_date) ? res.disbursal_date : "<?= date('d-m-Y', strtotime(timestamp)) ?>");
        $('#repayment_date').val((res.repayment_date) ? res.repayment_date : "");
        $('#adminFeeWithGST').val((res.adminFeeWithGST) ? res.adminFeeWithGST : "0");
        $('#total_admin_fee').val((res.total_admin_fee) ? res.total_admin_fee : "0");
        $('#tenure').val((res.tenure) ? res.tenure : "0");
        $('#roi').val((res.roi) ? res.roi : "0");
        $('#net_disbursal_amount').val((res.net_disbursal_amount) ? res.net_disbursal_amount : "0");
        $('#repayment_amount').val((res.repayment_amount) ? res.repayment_amount : "0");
        $('#monthly_salary').val((res.cam_monthly_income > 0) ? res.cam_monthly_income : "0");

        $('#appraised_obligations').val((res.cam_obligations > 0) ? res.cam_obligations : "0");

        $('#panel_roi').val((res.roi * 2) ? res.roi * 2 : "1");
        $('#b2b_disbursal').val((res.b2b_disbursal) ? res.b2b_disbursal : "");
        $('#b2b_number').val((res.b2b_number) ? res.b2b_number : "");
        $('#risk_profile').val((res.cam_risk_profile) ? res.cam_risk_profile : "");
        $('#deviationsApprovedBy').val((res.deviationsApprovedBy) ? res.deviationsApprovedBy : "");
        $('#remark').val((res.remark) ? res.remark : "");

        var html = '<table class="table table-bordered table-striped">';
        html += '<tbody>';
        html += '<tr><th>CIBIL Score</th><td>' + ((res.cibil != null) ? res.cibil : '') + '</td><th>NTC</th><td>' + ((res.ntc) ? res.ntc : "-") + '</td></tr>';
        html += '<tr><th>Running other Payday loan</th><td>' + ((res.run_other_pd_loan) ? res.run_other_pd_loan : "-") + '</td><th>Delay in other loans in last 30 days</th><td>' + ((res.delay_other_loan_30_days) ? res.delay_other_loan_30_days : "-") + '</td></tr>';
        html += '<tr><th>Job stability</th><td>' + ((res.job_stability) ? res.job_stability : "-") + '</td><th>City category</th><td>' + ((res.city_category) ? res.city_category : "-") + '</td></tr>';
        // <td>' + ((res.salary_credit1) ? res.salary_credit1 : "-") + '</td>
        html += '<tr><th>Salary Credit Date</th><td>' + ((res.salary_credit1_date) ? res.salary_credit1_date : "-") + '</td><th>Salary Credit Amount (Rs.)</th><td>' + ((res.salary_credit1_amount) ? res.salary_credit1_amount : "-") + '</td></tr>';
        // <td>' + ((res.salary_credit2) ? res.salary_credit2 : "-") + '</td>
        html += '<tr><th>Salary Credit Date</th><td>' + ((res.salary_credit2_date) ? res.salary_credit2_date : "-") + '</td><th>Salary Credit Amount (Rs.)</th><td>' + ((res.salary_credit2_amount) ? res.salary_credit2_amount : "-") + '</td></tr>';
        // <td>' + ((res.salary_credit3) ? res.salary_credit3 : "-") + '</td>
        html += '<tr><th>Salary Credit Date</th><td>' + ((res.salary_credit3_date) ? res.salary_credit3_date : "-") + '</td><th>Salary Credit Amount (Rs.)</th><td>' + ((res.salary_credit3_amount) ? res.salary_credit3_amount : "-") + '</td></tr>';
        html += '<tr><th>Next Pay Date</th><td>' + ((res.next_pay_date) ? res.next_pay_date : "-") + '</td><th>Avg. Salary (Rs.)</th><td>' + ((res.median_salary) ? res.median_salary : "-") + '</td></tr>';
        html += '<tr><th>Salary Variance</th><td>' + ((res.salary_variance) ? res.salary_variance : "-") + '</td><th>Salary on Time</th><td>' + ((res.salary_on_time) ? res.salary_on_time : "-") + '</td></tr>';
        html += '<tr><th>Appraised Salary (Rs.)</th><td>' + res.cam_monthly_income + '</td><th>Appraised Obligations (Rs.)</th><td>' + res.cam_obligations + '</td></tr>';
        html += '<tr><th>Borrower Age (years)</th><td>' + ((res.borrower_age) ? res.borrower_age : "-") + '</td><th>End Use</th><td>' + ((res.end_use) ? res.end_use : "-") + '</td></tr>';
        html += '<tr><th>LW Score</th><td>-</td><th>Scheme</th><td>-</td></tr>';
        html += '<tr><th>Eligible FOIR (%)</th><td>' + ((res.eligible_foir_percentage) ? res.eligible_foir_percentage : "-") + '</td><th>Eligible Loan</th><td>' + ((res.eligible_loan) ? res.eligible_loan : "-") + '</td></tr>';
        html += '<tr><th>Loan Applied (Rs.)</th><td><?= ($leadDetails->loan_amount) ? round($leadDetails->loan_amount) : '-' ?></td><th>Loan Recommended (Rs.)</th><td>' + res.loan_recommended + '</td></tr>';
        html += '<tr><th>Final FOIR (%)</th><td>' + ((res.final_foir_percentage) ? res.final_foir_percentage : "-") + '</td><th>FOIR ENHANCED BY (%)</th><td>' + ((res.foir_enhanced_by) ? res.foir_enhanced_by : "-") + '</td></tr>';
        html += '<tr><th>Admin Fee (%)</th><td>' + ((res.processing_fee_percent) ? res.processing_fee_percent : "-") + '</td><th>ROI (%)</th><td>' + ((res.roi) ? res.roi : "-") + '</td></tr>';
        html += '<tr><th>Total Admin Fee (Rs.)</th><td>' + ((res.admin_fee) ? res.admin_fee : "-") + '</td><th>Disbursal Date</th><td>' + ((res.disbursal_date) ? res.disbursal_date : "-") + '</td></tr>';
        html += '<tr><th>GST @18.00 (%)</th><td>' + ((res.adminFeeWithGST) ? res.adminFeeWithGST : "-") + '</td><th>Repay Date</th><td>' + ((res.repayment_date) ? res.repayment_date : "-") + '</td></tr>';
        html += '<tr><th>Net Admin Fee (Rs.)</th><td>' + ((res.total_admin_fee) ? res.total_admin_fee : "-") + '</td><th>Tenure (days)</th><td>' + ((res.tenure) ? res.tenure : "-") + '</td></tr>';
        html += '<tr><th>Net Disb. Amount (Rs.)</th><td>' + ((res.net_disbursal_amount) ? res.net_disbursal_amount : "-") + '</td><th>Repay Amount (Rs.)</th><td>' + ((res.repayment_amount) ? res.repayment_amount : "-") + '</td></tr>';
        html += '<tr><th>Penal ROI</th><td>' + ((res.panel_roi) ? res.panel_roi : "-") + '</td><th>B2B Disbursal</th><td>' + ((res.b2b_disbursal) ? res.b2b_disbursal : "-") + '</td></tr>';
        html += '<tr><th>Risk Profile</th><td>' + ((res.cam_risk_profile) ? res.cam_risk_profile : "-") + '</td><th></th><td></td></tr>';
        html += '<tr><th>B2B NO.</th><td>' + ((res.b2b_number) ? res.b2b_number : "-") + '</td><th>Deviations</th><td>' + ((res.deviationsApprovedBy) ? res.deviationsApprovedBy : "-") + '</td></tr>';
        html += '<tr><th>Remark</th><td colspan="3">' + res.remark + '</td></tr>';

        html += '</tbody>';
        html += '</table>';
        $('#ViewCAMDetails').html(html);
//        checkLoanEligibility();
//        calculateAmount();
    }

    function bankingAnalysis(lead_id)
    {
        $.ajax({
            url: '<?= base_url("bankAnalysis") ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $('#btnBankingAnalysis').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...').addClass('disabled', true);
            },
            success: function (response) {
                if (response.err) {
                    catchError(response.err);
                } else {
                    $('#ViewBankingAnalysis').html(response);
                    catchSuccess(response.msg);
                }
            },
            complete: function () {
                $('#btnBankingAnalysis').html('Banking Analysis').removeClass('disabled');
            },
        });
    }

    /* Fin Box Device Created By Rohit */

    function finboxdevice(lead_id, customer_id, user_id)
    {
        $.ajax({
            url: '<?= base_url("finboxdevice") ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, customer_id: customer_id, user_id: user_id, csrf_token},
            beforeSend: function () {
                $('#btnBankingAnalysis').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...').addClass('disabled', true);
            },
            success: function (response) {
                if (response.err) {
                    catchError(response.err);
                } else {
                    $('#ViewFinBoxDevice').html(response);
                    catchSuccess(response.msg);
                }
            },
            complete: function () {
                $('#btnfinboxdevice').html('Fin Box Device').removeClass('disabled');
            },
        });
    }

    /* End Function */

    function state1(state_id, count)
    {
        state(state_id, count);
    }

    function getReligion(customer_religion_id, count)
    {
        $.ajax({
            url: '<?= base_url("getReligion") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#religion" + count).empty();
                $("#religion" + count).append('<option value="">Select</option>');
                $.each(response.religion, function (index, myarr) {
                    var s = "";
                    if (customer_religion_id == myarr.religion_id) {
                        s = "Selected";
                    }
                    $("#religion" + count).append('<option value="' + myarr.religion_id + '" ' + s + '>' + myarr.religion_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function state(state_id, count)
    {
        $("#city" + count, "#pincode" + count).empty();
        var state_id = $(state_id).val();
        if (state_id != '') {
            $.ajax({
                url: "<?= base_url('getCity/'); ?>" + state_id,
                type: "POST",
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    $("#city" + count).empty();
                    $("#city" + count).append('<option value="">Select</option>');
                    $.each(response.city, function (index, myarr) {
                        $("#city" + count).append('<option value="' + myarr.m_city_id + '">' + myarr.m_city_name + '</option>');
                    });
                }
            });
        } else {
            $('#city' + count).html('<option value="">Select City</option>');
        }
    }

    function city1(city_id, count)
    {
        city(city_id, count);
    }

    function city(city_id, count)
    {
        $("#pincode" + count).empty();
        var city_id = $(city_id).val();
        if (city_id != '') {
            $.ajax({
                url: "<?= base_url('getPincode/'); ?>" + city_id,
                type: "POST",
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    $("#pincode" + count).empty();
                    $("#pincode" + count).append('<option value="">Select</option>');
                    $.each(response.pincode, function (index, myarr) {
                        $("#pincode" + count).append('<option value="' + myarr.m_pincode_value + '">' + myarr.m_pincode_value + '</option>');
                    });
                }
            });
        } else {
            $('#pincode' + count).html('<option value="">Select Pincode</option>');
        }
    }

    function getMaritalStatus(customer_marital_status_id, count)
    {
        $.ajax({
            url: '<?= base_url("getMaritalStatus/") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {

                $("#MaritalStatus" + count).empty();
                $("#MaritalStatus" + count).append('<option value="">Select</option>');
                $.each(response.MaritalStatus, function (index, myarr) {
                    var s = "";
                    if (customer_marital_status_id == myarr.m_marital_status_id) {
                        s = "Selected";
                    }
                    $("#MaritalStatus" + count).append('<option value="' + myarr.m_marital_status_id + '" ' + s + '>' + myarr.m_marital_status_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function showFieldMandotry(str) {
        //alert(str);
        if (str == '2') {
            $('#customer_spouse_name').attr('required', 'true');
            $('#customer_spouse_name').removeAttr('readonly');
            $('#SpouseOccupation1').attr('required', 'true');
            $('#mendory_id').html('<strong class="required_Fields">*</strong>');
            $('#mendory_id1').html('<strong class="required_Fields">*</strong>');
            $('#SpouseOccupation1').removeAttr('disabled');
        } else {
            $('#mendory_id').html('');
            $('#customer_spouse_name').removeAttr('required');
            $('#customer_spouse_name').attr('readonly', true);
            $('#SpouseOccupation1').prop('disabled', true);
            $('#SpouseOccupation1').removeAttr('required');
            $('#customer_spouse_name').val('');
            $('#customer_spouse_occupation_id').val('');
        }
    }


    function getEmpOccupation(emp_occupation_id, count)
    {
        alert("OK");
        $.ajax({
            url: '<?= base_url("getEmpOccupation/") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#EmpOccupation" + count).empty();
                $("#EmpOccupation" + count).append('<option value="">Select</option>');
                $.each(response.EmpOccupation, function (index, myarr) {
                    var s = "";
                    if (emp_occupation_id == myarr.m_occupation_id) {
                        s = "Selected";
                    }
                    $("#EmpOccupation" + count).append('<option value="' + myarr.m_occupation_id + '" ' + s + '>' + myarr.m_occupation_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getSpouseOccupation(customer_spouse_occupation_id, count)
    {
        $.ajax({
            url: '<?= base_url("getSpouseOccupation/") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#SpouseOccupation" + count).empty();
                $("#SpouseOccupation" + count).append('<option value="">Select</option>');
                $.each(response.SpouseOccupation, function (index, myarr) {
                    var s = "";
                    if (customer_spouse_occupation_id == myarr.m_occupation_id) {
                        s = "Selected";
                    }
                    $("#SpouseOccupation" + count).append('<option value="' + myarr.m_occupation_id + '" ' + s + '>' + myarr.m_occupation_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getQualification(customer_qualification_id, count)
    {
        $.ajax({
            url: '<?= base_url("getQualification/") ?>',
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $("#Qualification" + count).empty();
                $("#Qualification" + count).append('<option value="">Select</option>');
                $.each(response.Qualification, function (index, myarr) {
                    var s = "";
                    if (customer_qualification_id == myarr.m_qualification_id) {
                        s = "Selected";
                    }
                    $("#Qualification" + count).append('<option value="' + myarr.m_qualification_id + '" ' + s + '>' + myarr.m_qualification_name + '</option>');
                });
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }


    $(document).ready(function () {
        $(document).ready(function () {
            $(".tab").click(function () {
                $(".tab").removeClass("active");
                // $(".tab").addClass("active"); // instead of this do the below 
                $(this).addClass("active");
            });
        });
        $('#addharAddressSameasAbove').click(function () {
            if ($('#addharAddressSameasAbove').prop('checked')) {
                $('#addharAddressSameasAbove').val("YES");
                var state1 = $("#state1").val();
                var city1 = $("#city1").val();
                var pincode1 = $("#pincode1").val();
                getCity(city1, state1, 2);
                getPincode(pincode1, city1, 2);

                $("#hfBulNo2").val($("#hfBulNo1").val());
                $("#lcss2").val($("#lcss1").val());
                $("#landmark2").val($("#lankmark1").val());
                $("#state2").val($("#state1").val());
                // $("#city2").val($("#city1").val());
                $("#city2").empty().append('<option value="' + $("#city1").val() + '">' + $("#city1").val() + '</option>');
                $("#pincode2").val($("#pincode1").val());
                $("#district2").val($("#district1").val());
            } else {
                $('#addharAddressSameasAbove').val("NO");
                $("#hfBulNo2").val('');
                $("#lcss2").val('');
                $("#landmark2").val('');
                $("#state2").val('');
                $("#city2").val('');
                $("#pincode2").val('');
                $("#district2").val('');
            }
        });

        $('#customer_ifsc_code').select2({
            placeholder: 'Select IFSC Code',
            minimumInputlength: 2,
            allowClear: true,
            ajax: {
                url: '<?= base_url('getCustomerBankDetails') ?>',
                data: {csrf_token},
                dataType: 'json',
                delay: 250,
                data: function (data) {
                    return {
                        searchTerm: data.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: $.map(response, function (item) {
                            return {
                                id: item.bank_ifsc,
                                text: item.bank_ifsc,
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $("#customer_ifsc_code").change(function () {
            var ifsc_code = $(this).val();
            $.ajax({
                url: '<?= base_url("getBankNameByIfscCode") ?>',
                type: 'POST',
                data: {ifsc_code: ifsc_code, csrf_token},
                dataType: "json",
                success: function (response) {
                    $('#customer_bank_name').val(response.bank_name);
                    $('#customer_bank_branch').val(response.bank_branch);
                }
            });
        });

        $('#state').change(function () {
            var state_id = $(this).val();
            state(state_id, '');
        });

        $('#city').change(function () {
            var city_id = $(this).val();
            city(city_id, '');
        });

        $('#aadhar').keyup(function () {
            // $(this).attr("maxLength", "14");
            $(this).attr("maxLength", "12");
            var value = $(this).val();
//             value = value.replace(/\D/g, "").split(/(?:([\d]{4}))/g).filter(s => s.length > 0).join(" ");
            value = value.replace(/\D/g, "");
            $(this).val(value);
        });

        $('#sameResidenceAddress').click(function () {
            var sameAddress = $(this).val();
            var residence_address = $("#residence_address").val();
            if ($(this).is(":checked")) {
                $('#office_address').val(residence_address);
            } else {
                $('#office_address').val('');
            }
        });

        var lengthCount = 0;
        $('#customer_account_no, #customer_confirm_account_no').keyup(function () {

            var account_type = $('#account_type').val();
            if (lengthCount == 0) {
                catchError('Please select CC Bank Name.');
                $(this).val('');
            } else if (account_type == "") {
                catchError('Please select CC Type.');
                $(this).val('');
            } else {
                if (lengthCount == 19) {
                    $(this).attr("maxLength", lengthCount);
                    var value = $(this).val();
                    value = value.replace(/\D/g, "").split(/(?:([\d]{4}))/g).filter(s => s.length > 0).join(" ");
                    $(this).val(value);
                } else {
                    $(this).attr("maxLength", lengthCount);
                    var value = $(this).val();
                    value = value.replace(/^(.{4})(.{6})(.{4})$/, "$1 $2 $3");
                    $(this).val(value);
                }
            }
        });

        $('#customer_bank_name').on('change', function () {
            lengthCount = 0;
            $('#customer_account_no, #customer_confirm_account_no').val('');
            var customer_bank_name = $(this).val();
            if (customer_bank_name == "American Express") {
                var account_type = $('#account_type').val();
                if (account_type != "AMEX") {
                    lengthCount = 17;
                    $('#account_type').html('<option value="AMEX">AMEX</option>');
                }
            } else {
                lengthCount = 19;
                $('#account_type').html('<option value="">Select</option><option value="Master">Master</option><option value="Visa">Visa</option>');
            }
            var disbursal_date = $('#disbursal_date').val();
            var roi = $('#roi').val();
            tenureAndRepaymentAmount(disbursal_date, repayment_date, roi);
        });

        $('#customer_name').on('change', function () {
            var customer_name = $(this).val();
            var borrower_name = $("#borrower_name").val();
            if (customer_name == borrower_name) {
                var account_type = $('#account_type').val();
                $('#cc_name_Match_borrower_name_YES').prop('checked', true);
                $('#cc_name_Match_borrower_name_NO').prop('unchecked', false);
                $('#thumb_cc_name_Match_borrower_name').html('<i class="fa fa-thumbs-o-up" style="color : green; font-size : 18px;"></i>');
            } else {
                $('#cc_name_Match_borrower_name_YES').prop('unchecked', false);
                $('#cc_name_Match_borrower_name_NO').prop('checked', true);
                $('#thumb_cc_name_Match_borrower_name').html('<i class="fa fa-thumbs-o-down" style="color : red; font-size : 18px;"></i>');
            }
            var disbursal_date = $('#disbursal_date').val();
            var roi = $('#roi').val();
            tenureAndRepaymentAmount(disbursal_date, repayment_date, roi);
        });

        $('#bankA_C_No, #confBankA_C_No').keyup(function () {
            // $(this).attr("maxLength", "19");
//            $(this).attr("maxLength", "16");
            $(this).attr("maxLength", "20"); // changed 20221004
            var value = $(this).val();
            // value = value.replace(/\D/g, "").split(/(?:([\d]{4}))/g).filter(s => s.length > 0).join(" ");
            $(this).val(value);
        });

        $('#resent_ekyc_email').click(function () {

            if ($('#resent_ekyc_email').prop('checked')) {
                var lead_id = $('#resent_ekyc_email').val();
                $.ajax({
                    url: "<?= base_url('resentEkycEmail/'); ?>" + lead_id,
                    type: "POST",
                    data: {csrf_token},
                    dataType: "json",
                    success: function (response) {
                        if (response.errSession) {
                            window.location.href = "<?= base_url() ?>";
                        } else if (response.msg) {
                            catchSuccess(response.msg);
                        } else {
                            catchError(response.err);
                        }
                    }
                });
            } else {

            }
        });

    });

    function initiateFiCPV(lead_id, initiateVisit)
    {
        var visit = "NO";
        var visit_type = "";
        if (!confirm("Are you sure to initiate verification")) {
            return false;
        }
        if (initiateVisit == 1) // residenceCPV
        {
            visit_type = 1;
            if ($('#residenceCPV').prop('checked')) {
                $('#residenceCPV').val("YES");
                visit = "YES";
            } else {
                $('#residenceCPV').val("NO");
            }
        }
        if (initiateVisit == 2) // officeCPV
        {
            visit_type = 2;
            if ($('#officeCPV').prop('checked')) {
                $('#officeCPV').val("YES");
                visit = "YES";
            } else {
                $('#officeCPV').val("NO");
            }
        }
        $.ajax({
            url: '<?= base_url("initiateFiCPV") ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, visit_type: visit_type, is_visit: visit, csrf_token},
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    // history.back(1);
                    window.location.reload();
                } else {
                    catchError(response.err);
                }
            }
        });
    }

    function checkLoanEligibility()
    {
        var camFormData = $('#FormSaveCAM').serialize();

        $.ajax({
            url: '<?= base_url("checkLoanEligibility") ?>',
            type: 'POST',
            dataType: "json",
            data: camFormData,
            success: function (response) {
                $('#eligible_foir_percentage').val(response.eligible_foir_percentage);
                $('#eligible_loan').val(response.eligible_loan);
            }
        });
    }

    function calculateAmount()
    {
        var loan_applied = $('#loan_applied').val();
        var loan_recommended = $('#loan_recommended').val();
        if (loan_recommended > loan_applied) {
            // $('#loan_recommended').val(loan_applied);
        }
        var roi = $('#roi').val();
        $('#apr_percentage').text((roi * 365).toFixed(2));
        var camFormData = $('#FormSaveCAM').serialize();
        $.ajax({
            url: '<?= base_url("calculateAmount") ?>',
            type: 'POST',
            dataType: "json",
            data: camFormData,
            success: function (response) {
                $('#tenure').val(response.tenure);
                $('#admin_fee').val(response.admin_fee);
                $('#repayment_amount').val(response.repayment_amount);
                $('#adminFeeWithGST').val(response.adminFeeWithGST);
                $('#total_admin_fee').val(response.total_admin_fee);
                $('#net_disbursal_amount').val(response.net_disbursal_amount);
                $('#final_foir_percentage').val(response.final_foir_percentage);
                $('#foir_enhanced_by').val(response.foir_enhanced_by);
            }
        });
    }

    function isAddressLine_1_or_2(residence_address_line1, residence_address_line2)
    {
        if ($("#isPresentAddress").is(":checked")) {
            $("#isPresentAddress").val('YES');
            $('#selectPresentAddress').hide();
            $("#present_address_line1").val(residence_address_line1);
            $("#present_address_line2").val(residence_address_line2);
        } else {
            $("#isPresentAddress").val('NO');
            $('#selectPresentAddress').show();
            $("#present_address_line1").val('');
            $("#present_address_line2").val('');
        }
    }

    function customer_confirm_bank_ac_no(acc_no2)
    {
        var acc1 = $("#bankA_C_No").val();
        var acc2 = $(acc_no2).val();

        if (acc1 == null || acc1 == 'undefined' || acc1 == '' || acc1 == 0) {
            $("#bankA_C_No, #confBankA_C_No").val('');
        }

        if (acc2 == null || acc2 == 'undefined' || acc2 == '' || acc2 == 0) {
            $("#bankA_C_No, #confBankA_C_No").val('');
        }

        if (acc1 === acc2) {
            $("#bankA_C_No, #confBankA_C_No").css('border-color', '#aaa');
            return true;
        } else {
            $("#bankA_C_No, #confBankA_C_No").val('').css('border-color', 'red');
            $("#bankA_C_No").focus();
        }
    }

    function scmConfRequest(lead_id, customer_id, user_id)
    {
        if ($('#scm_conf_request').prop('checked'))
        {
            $.ajax({
                url: '<?= base_url("scmConfRequest") ?>',
                type: 'POST',
                dataType: "json",
                data: {lead_id: lead_id, customer_id: customer_id, user_id: user_id, csrf_token},
                success: function (response) {
                    $('#eligible_foir_percentage').val(response.eligible_foir_percentage);
                    $('#final_foir_percentage').val(response.final_foir_percentage);
                    $('#eligible_loan').val(response.eligible_loan);
                }
            });
            //$('#scm_conf_init').val('<?= date('d-m-Y h:i:s', strtotime(timestamp)) ?>');
        } else {
            //$('#scm_conf_init').val('');
        }
    }

    function send_NOC_for_settlement_letter(lead_id)
    {
        $.ajax({
            url: '<?= base_url("send_settlement_loan_letter/") ?>' + lead_id,
            type: 'POST',
            data: {csrf_token},
            dataType: 'json',
            beforeSend: function () {
                $('#btn_send_noc_settlement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                console.log(response);
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    catchSuccess(response.msg);
                    window.location.reload();
                }
            },
            complete: function () {
                $('#btn_send_noc_settlement').html('Send Settlement Letter').prop('disabled', false);
            }
        });
    }

    function send_NOC_for_closed_letter(lead_id)
    {
        $.ajax({
            url: '<?= base_url("send_closed_loan_letter/") ?>' + lead_id,
            type: 'POST',
            data: {csrf_token},
            dataType: 'json',
            beforeSend: function () {
                $('#btn_send_noc_closed').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                console.log(response);
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    catchSuccess(response.msg);
                    window.location.reload();
                }
            },
            complete: function () {
                $('#btn_send_noc_closed').html('Send NOC for Closed Loan').prop('disabled', false);
            }
        });
    }
</script>

<script>
    $(document).ready(function () {
        $('#FormUpdatePayment').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?= base_url("UpdatePayment") ?>',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    $('#btnUpdatePayment, #UpdatePayment').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.err) {
                        catchError(response.err);
                    } else {
                        catchSuccess(response.msg);
                        window.location.href = "<?= base_url() ?>";
                        $("#FormUpdatePayment")[0].reset();
                    }
                },
                complete: function () {
                    $('#btnUpdatePayment, #UpdatePayment').html('Payment Received').prop('disabled', false);
                }
            });
        });

        $('#UpdatePayment').click(function (e) {
            if (confirm("Are you sure to verify payment!"))
            {
                e.preventDefault();
                $('#payment_verification').val(1);
                var FormData = $('#FormUpdatePayment').serialize();
                paymentVerification("#UpdatePayment", "Verify", FormData);
            }
        });
        $('#RejectPayment').click(function (e) {
            e.preventDefault();
            $('#payment_verification').val(2);
            var FormData = $('#FormUpdatePayment').serialize();
            paymentVerification("#RejectPayment", "Reject", FormData);
        });
    });

    function paymentVerification(btnID, buttonName, FormData)
    {
        $.ajax({
            url: '<?= base_url("UpdatePayment") ?>',
            type: 'POST',
            dataType: "json",
            data: FormData,
            beforeSend: function () {
                $(btnID).html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg) {
                    catchSuccess(response.msg);
                    history.back(1);
                    $("#FormUpdatePayment")[0].reset();
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $(btnID).html(buttonName).prop('disabled', false);
            }
        });
    }

    $(document).ready(function () {
        $('#docsform').hide();
        $('#btnFormSaveCAM').click(function () {

<?php if (company_id == 1 && product_id == 1) { ?>
                var url = 'savePaydayCAMDetails';
<?php } if (company_id == 1 && product_id == 2) { ?>
                var url = 'saveLACCAMDetails';
<?php } ?>
            $.ajax({
                url: '<?= base_url() ?>' + url,
                type: 'POST',
                dataType: "json",
                data: $('#FormSaveCAM').serialize(),
                beforeSend: function () {
                    $('#btnFormSaveCAM').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        catchSuccess(response.msg);
                        window.location.reload();
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#btnFormSaveCAM').html('Save').prop('disabled', false);
                },
            });
        });

        $('#btnCAM_Approve').on('click', function () {
            var lead_id = $('#lead_id').val();
            $.ajax({
                url: '<?= base_url("headCAMApproved/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                beforeSend: function () {
                    $('#btnCAM_Approve').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.notification) {
                        catchNotification(response.notification);
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#btnCAM_Approve').html('Sanction').prop('disabled', false);
                }
            });
        });

        $('#formUpdateReferenceNo').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?= base_url("UpdateDisburseReferenceNo") ?>',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false,
                dataType: 'json',
                beforeSend: function () {
                    $('#updateReferenceNo').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...');
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#updateReferenceNo').html('Update Reference');
                },
            });
        });

        $('#saveCustomerDetails').on('click', function () {
            var FormSaveCustomerDetails = $('#FormSaveCustomerDetails').serialize();
            $.ajax({
                url: '<?= base_url("saveCustomerPersonalDetails") ?>',
                type: 'POST',
                data: FormSaveCustomerDetails,
                dataType: "json",
                beforeSend: function () {
                    $('#saveCustomerDetails').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveCustomerDetails').html('Save').prop('disabled', false);
                },
            });
        });

        $('#selectDocsTypes').on('click', function () {
            var radioval = $("input[name='selectdocradio']:checked").val()
            $("#docuemnt_type").val(radioval);
            $('#docsform').show();

            const api_url = "<?= base_url('getDocumentSubType/') ?>" + radioval;
            var field = $('#document_name');
            showLoader(field);
            getDocumentSubType(api_url);
        });

        $('#formUserDocsData').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?= base_url("saveCustomerDocs") ?>',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false,
                async: false,
                dataType: 'json',
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {
                    getDocs($('#lead_id').val(), $('#customer_id').val());
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        if ($('#docs_id').val() != "") {
                            $('#docs_id').val('');
                        }
                        catchSuccess("Docs Save Successfully.");
                        $("#formUserDocsData")[0].reset();
                        $('input[name="selectdocradio"]').attr('checked', false);
                        $('#docsform').hide();
                    } else {
                        catchError('Failed to save Docs. Try Again.');
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        });
    });

    $(document).ready(function () {

        $("#insertVerification").on('submit', function (e) {
            e.preventDefault();


            if ($('#initiateMobileVerification').is(':checked'))
            {
                var initiateMobileVerification = 'YES';
            } else
            {
                var initiateMobileVerification = 'NO';
            }

            if ($('#residenceCPV').is(':checked'))
            {
                var residenceCPV = 'YES';
            } else
            {
                var residenceCPV = 'NO';
            }

            if ($('#officeEmailVerification').is(':checked'))
            {
                var officeEmailVerification = 'YES';
            } else
            {
                var officeEmailVerification = 'NO';
            }

            if ($('#officeCPV').is(':checked'))
            {
                var officeCPV = 'YES';
            } else
            {
                var officeCPV = 'NO';
            }

            //residenceCPVAllocatedTo

            //office_cpv_allocated_to
            var params = {
                PANverified: $("#PANverified").val(),
                BankStatementSVerified: $("#BankStatementSVerified").val(),
                enterOTPMobile: $("#enterOTPMobile").val(),
                lead_id: $("#lead_id").val(),
                initiateMobileVerification: initiateMobileVerification,
                residenceCPV: residenceCPV,
                officeEmailVerification: officeEmailVerification,
                officeCPV: officeCPV

            }

            $.post('<?= base_url("saveVerification"); ?>', {
                data: params, csrf_token
            }, function (data, status) {
                setTimeout(function () {
                    location.reload();
                }, 2000);
            });
        });
    });


    function rejectalc(val)
    {
        var csrf_token = $("input[name=csrf_token]").val();
        var params = {
            residence_since: $("#residence_since").val(),
            scm_remarks: $("#scm_remarks").val(),
            lead_id: $("#lead_id").val(),
            user_id: $("#user_id").val(),
            company_id: $("#company_id").val(),
            status: val

        }

        $.post('<?= base_url("saveapplocConfirmation"); ?>', {
            data: params, csrf_token
        }, function (data, status) {
            setTimeout(function () {
                location.reload();
            }, 2000);
        });

    }

    $("#savefvrData").click(function () {


        var csrf_token = $("input[name=csrf_token]").val();
        var params = {
            residence_since: $("#fvr_residenceSince").val(),
            fvr_allocateTo: $("#fvr_allocateTo").val(),
            lead_id: $("#lead_id").val(),
            user_id: $("#user_id").val(),
            company_id: $("#company_id").val(),

        }

        $.post('<?= base_url("saveFVCData"); ?>', {
            data: params, csrf_token
        }, function (data, status) {
            setTimeout(function () {
                location.reload();
            }, 2000);
        });

    });

    function getLeadValue()
    {
        if ($('input:checkbox').filter(':checked').length < 1)
        {
            alert("Please Check at least one Check Box");
        } else
        {
            $("#overlay").fadeIn(300);

            vale = new Array();
            $('input.quickCall_id:checkbox:checked').each(function () {
                vale.push($(this).val());
            });
            $.post('<?= base_url("quickCallLeadId"); ?>', {
                data: vale, csrf_token
            }, function (data, status) {
                $("#overlay").fadeOut(3000);
                setTimeout(function () {
                    location.reload();
                }, 3000);
            });
        }


    }

    function assignLeadtoCollection(userid, leadid, type)
    {
        var userid = userid.value;
        var lead_id = atob(leadid);
        var type = atob(type);

        var params = {
            user_id: userid,
            lead_id: lead_id,
            type: type,
        }

        $.post('<?= base_url("assignLeadToCollectionuser"); ?>', {
            data: params, csrf_token
        }, function (data, status) {
            setTimeout(function () {
                //  location.reload();
            }, 2000);
        });
    }

    $('#selectAllDomainList').click(function () {
        var checkedStatus = this.checked;
        $('.duplicate_id').prop('checked', checkedStatus);
    });

</script>
<script>
    async function getDocumentSubType(url) {
        const response = await fetch(url);
        var data = await response.json();
        var field = $('#document_name');

        if (response) {
            hideLoader(field);
        }

        field.empty();
        field.append("<option value=''>SELECT</option>");
        data.forEach(function (index) {
            field.append("<option value='" + index.id + "'>" + index.docs_sub_type + "</option>");
        });

    }

    function showLoader(field) {
        field.html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
    }
    function hideLoader(field) {
        field.prop('disabled', false);
    }

    $(document).ready(function () {
        $("#saveEnquiryToApplication").on('click', function (e) {
            var FormData = $("#convertEnquiryToApplication").serialize();
            $.ajax({
                url: '<?= base_url("convertEnquiryToApplication") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveEnquiryToApplication').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        history.back(1);
                        catchSuccess(response.msg);
                    } else {
                        $('#application_errors').html(response.err);
                        $('#application_errors').show();
                        $(window).scrollTop($('#application_errors').offset().top - 100);
                        $('#application_errors').fadeOut(9000);

                    }
                },
                complete: function () {
                    $('#saveEnquiryToApplication').html('Save').prop('disabled', false);
                },
            });
        });

        $("#saveApplication").on('click', function (e) {
            var FormData = $("#insertApplication").serialize();
            $.ajax({
                url: '<?= base_url("insertApplication") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveApplication').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        // history.back(1);
                        catchSuccess(response.msg);
                        window.location.reload();
                    } else {
                        $('#application_errors').html(response.err);
                        $('#application_errors').show();
                        $(window).scrollTop($('#application_errors').offset().top - 100);
                        $('#application_errors').fadeOut(9000);

                    }
                },
                complete: function () {
                    $('#saveApplication').html('Save').prop('disabled', false);
                },
            });
        });

        $("#savePersonal").on('click', function (e) {
            var FormData = $("#insertPersonal").serialize();
            $.ajax({
                url: '<?= base_url("insertPersonal") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#savePersonal').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        getPersonalDetails($('#lead_id').val());
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#savePersonal').html('Save').prop('disabled', false);
                },
            });
        });

        $("#saveResidence").on('click', function (e) {
            var FormData = $("#insertResidence").serialize();
            $.ajax({
                url: '<?= base_url("insertResidence") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveResidence').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        getResidenceDetails($('#lead_id').val());
                        catchSuccess(response.msg);
//                        window.location.reload();
                    } else if (response.err_branch_mapping) {
                        catchError(response.err_branch_mapping);
                        window.location.reload();
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveResidence').html('Save').prop('disabled', false);
                },
            });
        });

        $("#saveEmployment").on('click', function (e) {
            var FormData = $("#insertEmployment").serialize();
            $.ajax({
                url: '<?= base_url("insertEmployment") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveEmployment').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        getEmploymentDetails($('#lead_id').val());
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveEmployment').html('Save').prop('disabled', false);
                },
            });
        });

        $("#saveReference, #updateReference").dblclick(function (e) {
            window.location.reload();
        });

        $("#saveReference").on('click', function (e) {
            var FormData = $("#insertReference").serialize();
            $.ajax({
                url: '<?= base_url("insertReference") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveReference').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    getReferenceDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                    if (response.msg) {
                        $("#insertReference")[0].reset();
                        catchSuccess(response.msg);
                        // $("#insertReference").load(location.href + " #insertReference");
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveReference').html('Save').prop('disabled', false);
                },
            });
        });

        $("#saveBeneficiary").on('click', function (e) {
            var FormData = $("#addBeneficiary").serialize();
            $.ajax({
                url: '<?= base_url("addBeneficiary") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveBeneficiary').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        getCustomerBanking('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                        $("#addBeneficiary")[0].reset();
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveBeneficiary').html('Save').prop('disabled', false);
                },
            });
        });

        $("#verifyDisbursalBank").on('click', function (e) {
            var FormData = $("#FormverifyDisbursalBank").serialize();
            $.ajax({
                url: '<?= base_url("verifyDisbursalBank") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#verifyDisbursalBank').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.msg) {
                        getCustomerBanking('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                        $("#FormverifyDisbursalBank")[0].reset();
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#verifyDisbursalBank').html('Save').prop('disabled', false);
                },
            });
        });

        $("#allowDisbursalToBank").on('click', function (e) {
            var FormData = $("#disbursalPayableDetails").serialize();
            $.ajax({
                url: '<?= base_url("allowDisbursalToBank") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#allowDisbursalToBank').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        disbursalDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#allowDisbursalToBank').html('Save').prop('disabled', false);
                },
            });
        });

        $("#formUpdateReferenceNo").on('submit', function (e) {
            var FormData = $("#formUpdateReferenceNo").serialize();
            $.ajax({
                url: '<?= base_url("UpdateDisburseReferenceNo") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#updateReferenceNo').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        history.back(1);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#updateReferenceNo').html('Update Reference').prop('disabled', false);
                },
            });
        });



        $("#saveCollectionFollowup").click(function () {
            var FormData = $("#addLeadCollectionFollowup").serialize();
            var field_blank = "#addLeadCollectionFollowup #collection_followup_status_id, ";
            field_blank += " #addLeadCollectionFollowup #collection_next_schedule_date, ";
            field_blank += " #addLeadCollectionFollowup #followup_remarks ";

            insertCollectionFollowup("saveCollectionFollowup", "Save", FormData, field_blank);
        });

        $("#saveCollectionFollowupSMS").click(function () {
            var FormData = $("#FormLeadCollectionFollowupSMS").serialize();
            var field_blank = "#FormLeadCollectionFollowupSMS #collection_followup_sms_primary_id, ";
            field_blank += " #FormLeadCollectionFollowupSMS #collection_followup_sms_content ";

            insertCollectionFollowup("saveCollectionFollowupSMS", "Send SMS", FormData, field_blank);
        });

        $("#saveCollectionFollowupWhatsapp").click(function () {
            var FormData = $("#FormLeadCollectionFollowupWhatsapp").serialize();
            var field_blank = "#FormLeadCollectionFollowupWhatsapp #collection_followup_whatsapp_title, ";
            field_blank += " #FormLeadCollectionFollowupWhatsapp #collection_followup_whatsapp_content ";

            insertCollectionFollowup("saveCollectionFollowupWhatsapp", "Send Whatsapp", FormData, field_blank);
        });

        $("#saveCollectionFollowupEmail").click(function () {
            var FormData = $("#FormLeadCollectionFollowupEmail").serialize();
            var field_blank = "#FormLeadCollectionFollowupEmail #c_followup_email_template_id, ";
            field_blank += " #FormLeadCollectionFollowupEmail #email_subject, ";
            field_blank += " #FormLeadCollectionFollowupEmail #email_cc_user, ";
            field_blank += " #FormLeadCollectionFollowupEmail #email_body ";

            insertCollectionFollowup("saveCollectionFollowupEmail", "Send Email", FormData, field_blank);
        });

        $("#saveRequestForCollectionVisit").on('click', function () {
            var FormData = $("#FormRequestForCollectionVisit").serialize();
            $.ajax({
                url: '<?= base_url("insert-request-for-collection-visit") ?>',
                type: 'POST',
                data: FormData,
                dataType: "json",
                beforeSend: function () {
                    $('#saveRequestForCollectionVisit').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = '<?= base_url() ?>';
                    } else if (response.msg) {
                        $('#col_visit_id').empty();
                        $('#FormRequestForCollectionVisit')[0].reset();
                        get_Visit_Request_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $('#saveRequestForCollectionVisit').html('Save').prop('disabled', false);
                }
            });
        });
    });

    function updateReference(id, ref_type, mobile, name)
    {
        $('#insertReference').hide();
        $('#upd_lead_id').val(id);
        $('#refrence1, #upd_refrence1').val(name);
        $('#refrence1mobile, #upd_refrence1mobile').val(mobile);
        $('#relation1, #upd_relation1').val(ref_type);
        $('#updateReference').show();
    }

    //updateReferencebuton
    $("#updateReferencebuton").on('click', function (e) {
        var FormData = $("#updateReference").serialize();
        $.ajax({
            url: '<?= base_url("updateReference") ?>',
            type: 'POST',
            data: FormData,
            dataType: "json",
            beforeSend: function () {
                $('#saveReference').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                getReferenceDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                if (response.msg) {

                    // $("#insertReference").load(location.href + " #insertReference");
                    $('#insertReference')[0].reset();
                    $('#updateReference').hide();
                    $('#insertReference').show();
                    setInterval(function () {
                    }, 1000);
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#saveReference').html('Save').prop('disabled', false);
            },
        });
    });

    function deleterefrence(id)
    {

        var params = {
            lead_id: id,
            lcr_deleted: 1,

        }

        $.post('<?= base_url("deleteData"); ?>', {
            data: params, csrf_token
        }, function (data, status) {
            if (status == 'success')
            {
                catchSuccess('Successfully Deleted');
                $("#remove" + id).fadeOut("slow");
                getReferenceDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
            }

        });
    }


    function leadHistoryLogs(lead_id)
    {
        $.ajax({
            url: '<?= base_url("viewleadLogs/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                // $('#leadLogs').empty();
                $('#leadLogs').html(response);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function leadSanctionFollowupLogs(lead_id)
    {
        $.ajax({
            url: '<?= base_url("viewSanctionFollowupLogs/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $('#leadSanctionFollowupLogs').empty();
                $('#leadSanctionFollowupLogs').html(response);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getVerificationDetails(lead_id)
    {
        $.ajax({
            url: '<?= base_url("getVerificationDetails/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                $('#verification_details').html(response.data);
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }


    $('#formSanctionLetterData').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url("upload-sanction-letter") ?>',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            dataType: 'json',
            beforeSend: function () {
                $("#cover").show();
                $('#uploadLetterbutton').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {

                if (response.errSession) {
                    window.location.href = '<?= base_url() ?>';
                } else if (response.msg) {
                    catchSuccess("Sanction Letter uploaded Successfully.");
                    $("#formSanctionLetterData")[0].reset();
                } else {
                    catchError('Failed to upload Sanction Letter. Try Again.');
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750);
                $('#uploadLetterbutton').html('Upload').prop('disabled', false);
            }
        });
    });


    function email_verification_api_call(lead_id, email_verification_type)
    {
        var flag = "NO";
        if (!confirm("Are you sure to initiate verification")) {
            return false;
        }
        if (email_verification_type == 1) // Personal Email
        {
            if ($('#personalEmailVerification').prop('checked', true)) {
                $('#personalEmailVerification').val("YES");
                flag = "YES";
            } else {
                $('#personalEmailVerification').val("NO");
            }
        }
        if (email_verification_type == 2) // Official Email
        {
            if ($('#officeEmailVerification').prop('checked', true)) {
                $('#officeEmailVerification').val("YES");
                flag = "YES";
            } else {
                $('#officeEmailVerification').val("NO");
            }
        }
        $.ajax({
            url: '<?= base_url("email-verification-api-call") ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, email_verification_type: email_verification_type, flag: flag, csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    getVerificationDetails("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");
                    catchSuccess(response.success_msg);
                } else {
                    ((email_verification_type == 1) ? $('#personalEmailVerification').prop('checked', false) : '');
                    ((email_verification_type == 2) ? $('#officeEmailVerification').prop('checked', false) : '');
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function analyse_bank_statement(lead_id)
    {
        $.ajax({
            url: '<?= base_url("analyse-bank-statement/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $('#analyse_bank_statement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    catchSuccess(response.success_msg);
                    $('#api_download_bank_statement').html('Download Cart API Data').prop('disabled', true);
                    getDataBankingAnalysis("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");

                    var i = 30;
                    var timer = '';

                    (function timer() {
                        if (--i < 0)
                            return;
                        setTimeout(function () {
                            if (i == 0) {

                                $('#div_bank_statement_analysis').html('<button class="btn btn-info" id="api_download_bank_statement" onclick="api_download_bank_statement(&quot;' + lead_id + '&quot;)">Download Cart API Data</button>').prop('disabled', false);
                            } else {
                                $('#div_bank_statement_analysis').html('<div class="alert alert-success" role="alert">Please wait for a moment to call api download...<strong> ' + i + ' Secs </strong></div>').prop('disabled', true);
                                timer = i;
                                timer();
                            }
                        }, 1000);
                    })();
                } else {
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $('#analyse_bank_statement').html('Anasyse Bank Statement').prop('disabled', false);
            }
        });
    }


    function getFinBoxDevice(lead_id)
    {
        $.ajax({

            url: '<?= base_url("Finbox/finbox-analysis-report/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            success: function (data) {
                if (data.result) {
                    $('#viewFinBoxDevice').html(data.result);
                } else {
                    catchError(data.error_msg);
                }
            },
            complete: function () {

                $("#cover").fadeOut(1750)
            }
        });
    }

    function api_download_bank_statement(lead_id)
    {
        $.ajax({
            url: '<?= base_url("api-download-bank-statement/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $('#api_download_bank_statement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    catchSuccess(response.success_msg);
                    $('#api_download_bank_statement').html('Download Cart API Data').prop('disabled', true);
                    getDataBankingAnalysis("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");
                } else {
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $('#api_download_bank_statement').html('Download Cart API Data').prop('disabled', false);
            }
        });
    }

    function getDataBankingAnalysis(lead_id)
    {
        $.ajax({
            url: '<?= base_url("get-Banking-Analysis-Data/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    $('#viewBankingAnalysisApiData').html(response.success_msg);
                } else {
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750)
            }
        });
    }

    function getFinBoxBankingDeviceData(lead_id)
    {
        $.ajax({
            url: '<?= base_url("Finbox/finbox-banking-device-data/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},

            success: function (data) {
                if (data.result) {
                    $('#viewFinBoxBankingDeviceData').html(data.result);
                } else {
                    catchError(data.error_msg);
                }
            },
            complete: function () {

                $("#cover").fadeOut(1750)
            }
        });
    }

    function api_download_finbox_bank_statement(lead_id)
    {

        $.ajax({
            url: '<?= base_url("api-download-finbox-bank-statement/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $('#api_download_finbox_bank_statement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    catchSuccess(response.success_msg);
                    $('#api_download_finbox_bank_statement').html('Download Finbox API Data').prop('disabled', true);
                    getFinBoxBankingDeviceData("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");
                } else {

                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $('#api_download_finbox_bank_statement').html('Download Finbox API Data').prop('disabled', false);
            }
        });
    }
    function finbox_analyse_bank_statement(lead_id)
    {

        $.ajax({
            url: '<?= base_url("finbox-analyse-bank-statement/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $('#finbox_analyse_bank_statement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                //alert(response);
                if (response.errSession) {
                    //alert(response.errSession);
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    // alert(response.success_msg);
                    // alert(response.success_msg);
                    catchSuccess(response.success_msg);
                    $('#api_download_finbox_bank_statement').html('Download Finbox API Data').prop('disabled', true);
                    getFinBoxBankingDeviceData("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");

                    var i = 10;
                    var timer = '';
                    (function timer() {
                        if (--i < 0)
                            return;
                        setTimeout(function () {
                            if (i == 0) {
                                $('#div_finbox_bank_statement_analysis').html('<button class="btn btn-info" id="api_download_finbox_bank_statement" onclick="api_download_finbox_bank_statement(<?= $leadDetails->lead_id ?>)">Download Finbox API Data</button>').prop('disabled', false);
                            } else {
                                $('#div_finbox_bank_statement_analysis').html('<div class="alert alert-success" role="alert">Please wait for a moment to call api download...<strong> ' + i + ' Secs </strong></div>').prop('disabled', true);
                                timer = i;
                                timer();
                            }
                        }, 1000);
                    })();
                } else {
                    // alert(response.error_msg);
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                $('#finbox_analyse_bank_statement').html('Finbox Bank Statement').prop('disabled', false);
            }
        });
    }

    function getFinBoxBankingDeviceData(lead_id)
    {
        $.ajax({
            url: '<?= base_url("Finbox/finbox-banking-device-data/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},

            success: function (data) {
                if (data.result) {
                    $('#viewFinBoxBankingDeviceData').html(data.result);
                } else {
                    catchError(data.error_msg);
                }
            },
            complete: function () {

                $("#cover").fadeOut(1750)
            }
        });
    }

    function api_download_finbox_bank_statement(lead_id)
    {

        $.ajax({
            url: '<?= base_url("api-download-finbox-bank-statement/") ?>' + lead_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            beforeSend: function () {
                $('#api_download_finbox_bank_statement').html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {

                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.success_msg) {
                    catchSuccess(response.success_msg);
                    $('#api_download_finbox_bank_statement').html('Download Cart API Data').prop('disabled', true);
                    getFinBoxBankingDeviceData("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");
                } else {
                    catchError(response.error_msg);
                }
            },
            complete: function () {
                //   alert(lead_id);
                $('#api_download_finbox_bank_statement').html('Download Cart API Data').prop('disabled', false);
            }
        });
    }


    function viewBlackListBox() {
        var black_list_flag = $("#viewBlackListBox").prop('checked');

        if (black_list_flag == true) {
            $("#blackListDiv").show();
        } else {
            $("#blackListDiv").hide();
        }
    }

    function viewGenerateRepayLinkBox() {
        var flag = $("#viewGenerateRepayLinkBox").prop('checked');

        if (flag == true) {
            $("#GenerateRepayLinkDiv").show();
        } else {
            $("#GenerateRepayLinkDiv").hide();
        }
    }

    function addToBlackList(lead_id) {
        var black_list = confirm("Are you sure to black list this customer?");
        var blackListReason = $("#blackListReason").val();
        var blackListReasonRemark = $("#blackListReasonRemark").val();

        if (blackListReason == "") {
            catchError("Please select the blacklist reason.");
        } else if (blackListReasonRemark == "") {
            catchError("Please enter the blacklist remarks.");
        } else if (black_list) {
            $.ajax({
                url: '<?= base_url("addToBlackList") ?>',
                type: 'POST',
                data: {lead_id: lead_id, reason_id: blackListReason, remark: blackListReasonRemark, csrf_token},
                dataType: "json",
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        window.location.reload();
                    } else {
                        $('#viewBlackListBox').prop('checked', false);
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        } else {
            $('#viewBlackListBox').prop('checked', false);
        }
    }

    function generateRepayLink(lead_id) {
        var black_list = confirm("Are you sure to generate EazyPay link?");
        var repay_loan_amount = $("#repay_loan_amount").val();

        if (repay_loan_amount == "") {
            catchError("Please enter the valid repayment amount.");
        } else if (black_list) {
            $.ajax({
                url: '<?= base_url("generateEazyPayRepaymentLink") ?>',
                type: 'POST',
                data: {lead_id: lead_id, repay_loan_amount: repay_loan_amount, csrf_token},
                dataType: "json",
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {
                    $("#repay_encrypted_url, #repay_encrypted_url_notes, #encrypted_url").empty();
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        $('#repay_encrypted_url_notes').html('This link will expire in 5 minutes. <button style="margin-top : 5px;" onclick="copyToClipboard()">Click to copy Link</button>');
//                        $('#repay_encrypted_url').html("<a href='" + response.repay_encrypted_url + "' target='_blank'>" + response.repay_encrypted_url + "</a>");
                        $('#repay_encrypted_url').html("<textarea id='encrypted_url' readonly rows='10' cols='50' style='color:#0c70ab;'>" + response.repay_encrypted_url + "</textarea>");
                    } else {
                        $('#viewGenerateRepayLinkBox').prop('checked', false);
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        } else {
            $('#viewGenerateRepayLinkBox').prop('checked', false);
        }
    }

    function copyToClipboard() {
        var copyText = document.getElementById("encrypted_url");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.textContent);
        catchSuccess("Link Copied successfully.");
    }

    function getCollectionDetails(lead_id)
    {
        if (lead_id != "") {
            $.ajax({
                url: '<?= base_url("getCollectionDetails/") ?>' + lead_id,
                type: 'POST',
                data: {csrf_token},
                dataType: "json",
                success: function (response) {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.err) {
                        catchError(response.err);
                    } else if (response.msg) {
                        $('#summaryCollection').html(response.data);
                    }
                }
            });
        } else {
            catchError("Lead Id Not Found.");
        }
    }

    function get_collection_followup_master_lists()
    {
        $('#collection_followup_status_id').select2();
        $.ajax({
            url: '<?= base_url("get-list-followup-master-lists") ?>',
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    var i = 1;
                    $('#followup_type, #collection_followup_status_id').empty();
                    $('#followup_type').append('<label class="labelField">Followup Type&nbsp;<strong class="required_Fields">*</strong> </label>');
                    $.each(response['lists_master_followup_type'], function (index, myarr) { //  aria-expanded="true" data-toggle="collapse" data-target="#UpdateCollectionFollowup_'+ myarr.type_id +'"
                        $('#followup_type').append('<label class="radio-inline" title="' + myarr.type_heading + '"><input type="radio" name="collection_followup_type_id" id="collection_followup_type_id_' + myarr.type_id + '" value="' + myarr.type_id + '" onclick="get_collection_followup_type(' + myarr.type_id + ')">&nbsp;<i class="' + myarr.type_icons + '"></i>&nbsp;' + myarr.type_heading + '</label>');
                    });
                    $('#collection_followup_status_id').append('<option value="">Select</option>');
                    $.each(response['lists_master_followup_status'], function (index, myarr) {
                        $('#collection_followup_status_id').append('<option value="' + myarr.status_id + '">&nbsp;' + myarr.status_heading + '</option>');
                    });
                }
            }
        });
    }

    $("#UpdateCollectionFollowup_1, #UpdateCollectionFollowup_2, #UpdateCollectionFollowup_3, #UpdateCollectionFollowup_4").hide();
    function get_collection_followup_type(followup_type_id) {
        if (followup_type_id == 1) { // call
            $("#UpdateCollectionFollowup_1").show();
            $("#UpdateCollectionFollowup_2, #UpdateCollectionFollowup_3, #UpdateCollectionFollowup_4").hide();
            $('#collection_followup_status_id, #collection_next_schedule_date, #followup_remarks').val("");
        } else if (followup_type_id == 2) { // sms
            $("#UpdateCollectionFollowup_2").show();
            $("#UpdateCollectionFollowup_1, #UpdateCollectionFollowup_3, #UpdateCollectionFollowup_4").hide();
            $('#collection_followup_sms_primary_id, #collection_followup_sms_content').val("");
            $('#collection_followup_sms_content').val("NA");
        } else if (followup_type_id == 3) { // whatsapp
            $("#UpdateCollectionFollowup_3").show();
            $("#UpdateCollectionFollowup_1, #UpdateCollectionFollowup_2, #UpdateCollectionFollowup_4").hide();
            $('#collection_followup_whatsapp_title, #collection_followup_whatsapp_content').val("");
            $('#collection_followup_whatsapp_content').val("NA");
        } else if (followup_type_id == 4) { // email
            $("#UpdateCollectionFollowup_4").show();
            $("#UpdateCollectionFollowup_1, #UpdateCollectionFollowup_2, #UpdateCollectionFollowup_3").hide();
            $("#collection_followup_sms_content, #email_subject, #email_body").val("");
        }
        $.ajax({
            url: '<?= base_url("get-followup-template-lists") ?>',
            type: 'POST',
            data: {followup_type_id: followup_type_id, csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    $('#collection_followup_sms_primary_id, #c_followup_email_template_id').empty();
                    $('#collection_followup_sms_primary_id, #c_followup_email_template_id').append('<option value="">Select</option>');
                    if (followup_type_id == 2) { // sms
                        $.each(response['list_sms_template'], function (index, myarr) {
                            $('#collection_followup_sms_primary_id').append('<option value="' + myarr.m_st_id + '">&nbsp;' + myarr.m_st_description + '</option>');
                        });
                    } else if (followup_type_id == 3) { // whatsapp

                    } else if (followup_type_id == 4) { // email
                        $.each(response['list_email_template'], function (index, myarr) {
                            $('#c_followup_email_template_id').append('<option value="' + myarr.m_et_id + '">&nbsp;' + myarr.m_et_description + '</option>');
                        });
                    }

                }
            }
        });
    }

    function get_collection_followup_content(followup_type_id, template_id, lead_id) {
        var followup_template_id = $(template_id).val();
        $.ajax({
            url: '<?= base_url("get-followup-template-lists") ?>',
            type: 'POST',
            data: {followup_type_id: followup_type_id, followup_template_id: followup_template_id, lead_id: lead_id, csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    $("#collection_followup_sms_content, #email_subject, #email_body").empty();
                    if (followup_type_id == 2) {
                        $("#collection_followup_sms_content").val(response['sms_content']);
                    } else if (followup_type_id == 3) {

                    } else if (followup_type_id == 4) {
                        $("#email_subject").val(response['email_content']['email_subject']);
                        $("#email_body").val(response['email_content']['email_body']);
                    }
                }
            }
        });
    }

    function get_Visit_Request_lists(lead_id)
    {
        $('#col_visit_id').val("");
        $('#btnAddCollectionVisit').text("Visit");
        $('#saveRequestForCollectionVisit').text("Save");
        $('#visit_type_id_1, #visit_type_id_2').prop("checked", false);
        $('#visit_scm_user_id').select2();
        $.ajax({
            url: '<?= base_url("get-visit-request-lists/") ?>' + lead_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.err) {
                        catchError(response.err);
                    } else if (response.msg) {
                        $('#summaryCollectionVisit').html(response.data);
                    }
                }
            }
        });
    }

    function get_Visit_Request_user_lists(lead_id, visit_type_id)
    {
        $('#FormRequestForCollectionVisit #visit_status_id, #FormRequestForCollectionVisit #visit_rm_user_id, #FormRequestForCollectionVisit #remarks').val('');
        $('#visit_scm_user_id').select2();
        var visit_type_id = $(visit_type_id).val();
        $('#visit_type_id_1, #visit_type_id_2').prop("checked", false);
        $('#visit_type_id_' + visit_type_id).prop("checked", true);

        $.ajax({
            url: '<?= base_url("get-visit-request-user-lists/") ?>' + lead_id,
            type: 'POST',
            data: {visit_type_id: visit_type_id, csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.err) {
                    catchError(response.err);
                } else {
                    $('#visit_scm_user_id, #visit_rm_user_id').empty();
                    $('#visit_scm_user_id, #visit_rm_user_id').append('<option value="">Select</option>');
                    $.each(response['scm_user_lists'], function (index, myarr) {
                        $('#visit_scm_user_id').append('<option value="' + myarr.scm_user_id + '">&nbsp;' + myarr.scm_user_name + '</option>');
                    });
                    $.each(response['cfe_user_lists'], function (index, myarr) {
                        $('#visit_rm_user_id').append('<option value="' + myarr.cfe_user_id + '">&nbsp;' + myarr.cfe_user_name + '</option>');
                    });
                }
            }
        });
    }

    function editsCollectionVisit(input)
    {
        $('#AddCollectionVisit').addClass("show");
        console.log(input.col_visit_address_type);

        if (input.col_visit_address_type == 1) {
            $('#FormRequestForCollectionVisit #visit_type_id_2').attr('disabled', true);
        } else if (input.col_visit_address_type == 2) {
            $('#FormRequestForCollectionVisit #visit_type_id_1').attr('disabled', true);
        }
        get_Visit_Request_user_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', input.col_visit_id);
        get_Visit_Request_lists('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
        var visit_btn_name = "Save";
        var tab_btn_name = "Visit";

        $('#col_visit_id').empty();
        $('#visit_type_id_1, #visit_type_id_2').prop("checked", false);
        var btn_edit_visit = "<a class='btn btn-control btn-primary' onclick='editsCollectionVisit(" + JSON.stringify(input) + ")'><i class='fa fa-pencil'></i></a>";

        if ($('#saveRequestForCollectionVisit').text() == "Save") {
            visit_btn_name = "Update";
            tab_btn_name = "Assign Visit";
            $('#col_visit_id').val(input.col_visit_id);
            $('#visit_type_id_' + input.col_visit_type).prop("checked", true);
            btn_edit_visit = "<a class='btn btn-control btn-primary' onclick='editsCollectionVisit(" + JSON.stringify(input) + ")'><i class='fa fa-refresh'></i></a>";
        }

        $('#btnAddCollectionVisit').text(tab_btn_name);
        $('#btnEditAssignCollection').html(btn_edit_visit);
        $('#saveRequestForCollectionVisit').text(visit_btn_name);
    }

    $(document).ready(function () {
        $('textarea').keyup(function () {
            var count = this.value.length;
            if (count > 500) {
                return false;
            } else {
                $('#inputWordCount').text(count);
            }
        });

        $('#visit_status_id').change(function () {
            var visit_status_id = $(this).val();
            $('#div_visit_assign_to').show();
            if (visit_status_id == 3) {
                $('#div_visit_assign_to').hide();
            }
        });

    });


    function Click_To_Call(lead_id, call_type, profile_type)
    {
        $.ajax({
            url: '<?= base_url("click-to-call/") ?>' + lead_id,
            type: 'POST',
            data: {lead_id: lead_id, call_type: call_type, profile_type: profile_type, csrf_token},
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = '<?= base_url() ?>';
                } else if (response.msg) {
                    catchSuccess(response.msg);
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750);
            }
        });
    }

    function viewDocs(docs) {
        window.open(docs, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=50,left=50,width=400,height=400");
    }

    function insertCollectionFollowup(btn_id, btn_name, FormData, field_blank) {
        var collection_followup_type_id = $("input[name='collection_followup_type_id']:checked").val();
        // debugger;
        $.ajax({
            url: '<?= base_url("insert-lead-collection-followup") ?>',
            type: 'POST',
            data: 'collection_followup_type_id=' + collection_followup_type_id + "&" + FormData, //csrf_token +"&"+ 
            dataType: "json",
            beforeSend: function () {
                $('#' + btn_id).html('<span class="spinner-border spinner-border-sm mr-2" role="status"></span>Processing...').prop('disabled', true);
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = '<?= base_url() ?>';
                } else if (response.msg) {
                    $(field_blank).val("");
                    get_collection_followup_master_lists();
                    getCollectionDetails('<?= $this->encrypt->encode($leadDetails->lead_id) ?>');
                    get_collection_followup_master_lists();
                    catchSuccess(response.msg);
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $('#' + btn_id).html(btn_name).prop('disabled', false);
            }
        });
    }

    function get_customer_feedback(lead_id)
    {
        $.ajax({
            url: "<?= base_url('get-customer-feedback'); ?>",
            type: "POST",
            data: {lead_id: lead_id, csrf_token},
            dataType: "json",
            success: function (response) {
                var i = 1;
                $("#customer_feedback").empty();
                $.each(response.feedback, function (index, myarr) {
                    var html = '<table class="table table-bordered table-hover table-striped">';
                    html += '<thead>';
                    html += '<tbody>';
                    html += '<tr><th><b>Question : ' + i + ' ' + myarr.question + '</b></th></tr>';
                    html += '<tr><td><b>Answer : </b>' + myarr.answer + '</td></tr>';
                    html += '</tbody></table>';

                    $("#customer_feedback").append(html);
                    i++;
                });
            }
        });
    }

    function collection_payment_verification(p_mode = "", payment_mode_id = "")
    {
        $.ajax({
            url: "<?= base_url('get-collection-payment-verification'); ?>",
            type: "POST",
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                $("#collection_payment_mode").empty();
                $("#collection_payment_mode").append('<option value="">Select</option>');
                $.each(response.master_payment_mode, function (index, myarr) {
                    var s = "";
                    if (payment_mode_id == myarr.payment_mode_id) {
                        s = "selected";
                    }

                    $("#collection_payment_mode").append('<option value="' + myarr.payment_mode_id + '" ' + s + '>' + myarr.payment_mode_heading + '</option>');
                });
            }
        });
        $.ajax({
            url: "<?= base_url('get-scm-rm-details'); ?>",
            type: "POST",
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.is_SCM == 1) {
                    $("#payment-screenshot").addClass('col-md-6');
                    $("#payment-screenshot").removeClass('col-md-12');
                    $("#scm-rm").show();
                    if (Object.keys(response.rmlist).length > 0) {

                        $.each(response.rmlist, function (index, arr) {
                            $("#collected_by").append('<option value="' + arr.user_role_user_id + '">' + arr.name + '</option>');
                        });
                    } else
                    {

                        $("#collected_by").html('<option value="' + <?= $_SESSION['isUserSession']['user_id'] ?> + '">Self</option>');
                    }
                } else {
                    $("#payment-screenshot").addClass('col-md-12');
                    $("#payment-screenshot").removeClass('col-md-6');
                }
            }
        });
    }

    function get_email_verification_response_api(lead_id) {
        if (lead_id == "") {
            catchError("Application no required.");
            return false;
        }
        $.ajax({
            url: '<?= base_url("email-verification-api-response-call/") ?>' + lead_id,
            type: 'POST',
            data: {csrf_token},
            dataType: "json",
            success: function (response) {
                if (response.errSession) {
                    window.location.href = '<?= base_url() ?>';
                } else if (response.status > 0) {
                    $('.payday_model_body').html(response.data);
                } else {
                    catchError(response.message);
                }
            }
        });
    }
    function is_visit_completed(visit_id) {
        //console.log("visit_id : " + visit_id);
        var flag = $("input[name='is_visit_completed']:checked").val();
        //console.log(flag);

        if (!confirm("Are you sure to take action for conveyance approval?")) {
            $('#is_visit_completed').prop('checked', false);
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("confirm-is-cfe-visit-completed") ?>',
                type: 'POST',
                dataType: "json",
                data: {visit_id: visit_id, flag: flag, csrf_token},
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {
                    //console.log(response);
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        get_Visit_Request_lists("<?= $this->encrypt->encode($leadDetails->lead_id) ?>");
                        catchSuccess(response.msg);
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        }
    }


    function call_bre_rule_engine() {

        if (!confirm("Are you sure to run the BRE?")) {
            return false;
        } else {
            $.ajax({
                url: '<?= base_url("call-bre-rule-engine") ?>',
                type: 'POST',
                dataType: "json",
                data: {enc_lead_id: "<?= $this->encrypt->encode($leadDetails->lead_id) ?>", csrf_token},
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {
                    console.log(response);
                    if (response.errSession) {
                        window.location.href = "<?= base_url() ?>";
                    } else if (response.msg) {
                        catchSuccess(response.msg);
                        window.location.reload();
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        }
    }

    function get_bre_rule_result() {

        $.ajax({
            url: '<?= base_url("get-bre-rule-result") ?>',
            type: 'POST',
            data: {enc_lead_id: "<?= $this->encrypt->encode($leadDetails->lead_id) ?>", csrf_token},
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {

                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.rule_result_flag == 1) {
                    $("#bre_rule_result_container").html(response.rule_result_html);
                } else {
                    catchError(response.err);
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750);
            }
        });

    }

    function call_bre_edit_application() {
        $.ajax({
            url: '<?= base_url("bre-edit-application") ?>',
            type: 'POST',
            data: {enc_lead_id: "<?= $this->encrypt->encode($leadDetails->lead_id) ?>", csrf_token},
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
            },
            success: function (response) {
                if (response.errSession) {
                    window.location.href = "<?= base_url() ?>";
                } else if (response.msg == 1) {
                    window.location.reload();
                }
            },
            complete: function () {
                $("#cover").fadeOut(1750);
            }
        });
    }

    function save_bre_rule_result_deviation(rule_id) {

        var deviation_decision = $("#deviation_action_" + rule_id).val();
        var deviation_remark = $("#deviation_remark_" + rule_id).val();

        if (rule_id !== '' && deviation_decision !== "" && deviation_remark !== "") {

            $.ajax({
                url: '<?= base_url("save-bre-manual-decision") ?>',
                type: 'POST',
                data: {enc_lead_id: "<?= $this->encrypt->encode($leadDetails->lead_id) ?>", trans_rule_id: rule_id, deviation_decision: deviation_decision, deviation_remark: deviation_remark, csrf_token},
                dataType: "json",
                beforeSend: function () {
                    $("#cover").show();
                },
                success: function (response) {

                    if (response.errSession) {

                        window.location.href = "<?= base_url() ?>";

                    } else if (response.rule_result_flag == 1) {
                        catchSuccess(response.msg);
                        get_bre_rule_result();
                    } else {
                        catchError(response.err);
                    }
                },
                complete: function () {
                    $("#cover").fadeOut(1750);
                }
            });
        } else {
            alert("Missing mandatory field.");
        }
    }

    function show_bre_deviation_box(id) {
        var select_value = $("#deviation_action_" + id).val();
        if (select_value !== '') {
            $("#deviation_remark_" + id).removeClass('hide');
            $("#deviation_btn_" + id).removeClass('hide');
        } else {
            $("#deviation_remark_" + id).addClass('hide');
            $("#deviation_btn_" + id).addClass('hide');
        }
    }

    function password_visibility() {

        if ($("#password").attr('type') == "password") {
            $("#password").attr("type", "text");
            $("#password_visibility").text('hide');
        } else {
            $("#password").attr("type", "password");
            $("#password_visibility").text('show');
        }

    }

    function getSanctionPerformancePopup() {

<?php if (in_array(agent, array("CR1", "CR2"))) { ?>

            $.ajax({
                url: '<?= base_url("get-sanction-performance") ?>',
                type: 'POST',
                dataType: "json",
                async: false,
                data: {csrf_token},
                success: function (response)
                {
                    //console.log(response);
                    $("#achievement_popup").html(response['popup_data']);
                    //$("#popupModal").modal("show");
                    $("#popupModal").css("opacity", 1);

                }
            });
<?php } ?>
    }
<?php if (in_array(agent, array("CR1", "CR2"))) { ?>
        $(window).load(function () {
            if (localStorage.getItem('recommend') == 1)
            {
                setTimeout(getSanctionPerformancePopup, 1000);
                localStorage.removeItem('recommend');
            } else
            {
                setTimeout(getSanctionPerformancePopup, 1800000);
            }
        });
<?php } ?>
</script>
<!--
<script>
    function showDiv() {
        document.getElementById("welcomeDiv").style.display = "block";
    }
</script>-->

<!--<script>
    function myFunction() {

        var btn = document.getElementById("myButton");
//to make it fancier
        if (btn.value == "Click To Open") {
            btn.value = "Click To Close";
            btn.innerHTML = "Click To Close";
        } else {
            btn.value = "Click To Open";
            btn.innerHTML = "Click To Open";
        }
//this is what you're looking for
        var x = document.getElementById("myDIV");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
</script>-->
<?php if (in_array(agent, array("CR1", "CR2"))) { ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                <!--<script src="<?= LMS_URL ?>public/pop_ups/pop_up2/js/jquery-ui.min.js"></script>-->  
    <script src="<?= LMS_URL ?>public/pop_ups/pop_up2/js/wow.min.js"></script>

    <script type="text/javascript">
    var divs = ["menu1", "menu2", "menu3", "Menu4"];
    var visibleDivId = null;
    function toggleVisibility(divId) {
        if (visibleDivId === divId) {
            div.style.display = "block";
        } else {
            visibleDivId = divId;
        }
        hideNonVisibleDivs();
    }
    function hideNonVisibleDivs() {
        var i, divId, div;
        for (i = 0; i < divs.length; i++) {
            divId = divs[i];
            div = document.getElementById(divId);
            if (visibleDivId === divId) {
                div.style.display = "block";
            } else {
                div.style.display = "none";
            }
        }
    }

    function showtabdetails() {
        $(".tab-btn").each(function () {
            if ($(this).is(":checked"))
            {

                var id = $(this).attr("id");
                console.log(id);
                $(".tab-details").hide();
                $("." + id).show();
            }
        });
    }
    $(".tab-btn").click(function () {
        var id = $(this).attr("id");
        console.log(id);
        $(".tab-details").hide();
        $("." + id).show();
    });
    function closeModal() {
        $("#popupModal").removeClass("show");
        $("#popupModal").css("display", "none");
    }

    function showPerformance() {
        $('#target').toggle('slow');
    }
    $('.toggle').click(function () {
        $('#target').toggle('slow');
    });
    </script>
<?php }
?>
