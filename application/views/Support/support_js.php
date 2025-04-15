<script>
    function showCity(state_id) {
        $.ajax({
            url: '<?= base_url("getCity/") ?>' + state_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            success: function (response) {
                //console.log(response);
                $("#city_id").html('<option value="">Select City</option>');
                $.each(response.city, function (index, myarr) {
                    var s = "";
                    if (city_id == myarr.m_city_id) {
                        s = "Selected";
                    }
                    $("#city_id").append('<option value="' + myarr.m_city_id + '" ' + s + '>' + myarr.m_city_name + '</option>');
                });
            }
        });
    }

    function showPincode(city_id) {
        $.ajax({
            url: '<?= base_url("getPincode/") ?>' + city_id,
            type: 'POST',
            dataType: "json",
            data: {csrf_token},
            success: function (response) {
                //console.log(response);
                $(".residence_pincode_cls").html('<option value="">Select Pincode</option>');
                $.each(response.pincode, function (index, myarr) {
                    $(".residence_pincode_cls").append('<option value="' + myarr.m_pincode_value + '">' + myarr.m_pincode_value + '</option>');
                });
            }
        });
    }

    function emailEabledAndDisabled() {
        if ($('#check_email_id').prop("checked") == true) {
            $("#email_id").removeAttr("disabled");
        } else {
            $("#email_id").attr("disabled", true);
        }
    }

    function alternateEmailEabledAndDisabled() {
        if ($('#check_alternate_email_id').prop("checked") == true) {
            $("#alternate_email_id").removeAttr("disabled");
        } else {
            $("#alternate_email_id").attr("disabled", true);
        }
    }

    function mobileEabledAndDisabled() {
        if ($('#check_mobile_id').prop("checked") == true) {
            $("#mobile_id").removeAttr("disabled");
        } else {
            $("#mobile_id").attr("disabled", true);
        }
    }

    function alternateMobileEabledAndDisabled() {
        if ($('#check_alternate_mobile_id').prop("checked") == true) {
            $("#alternate_mobile_id").removeAttr("disabled");
        } else {
            $("#alternate_mobile_id").attr("disabled", true);
        }
    }

    $(function () {
        $('.update_personal_details').click(function () {
            var check_email = $('#check_email_id').prop("checked");
            var check_alternate_email = $('#check_alternate_email_id').prop("checked");
            var check_mobile = $('#check_mobile_id').prop("checked");
            var check_alternate_mobile = $('#check_alternate_mobile_id').prop("checked");
            var lead_id = $('#lead_id').val();
            var email = $('#email_id').val();
            var alternate_email = $('#alternate_email_id').val();
            var mobile = $('#mobile_id').val();
            var alternate_mobile = $('#alternate_mobile_id').val();
            var loan_amount = $('#loan_amount_id').val();
            var pancard = $('#pancard_id').val();
            var gender = $('#gender_id').val();
            var dob = $('#dob_id').val();
            var religion_id = $('#customer_religion_id').val();
            var marital_status_id = $('#customer_marital_status_id').val();
            var spouse_name = $('#customer_spouse_name_id').val();
            var spouse_occupation_id = $('#customer_spouse_occupation_id').val();
            var qualification = $('#qualification_id').val();
            var current_house = $('#current_house_id').val();
            var current_locality = $('#current_locality_id').val();
            var current_landmark = $('#current_landmark_id').val();
            var current_state = $('#current_state_id').val();
            var current_city = $('#city_id').val();
            var residence_pincode = $('#residence_pincode_id').val();
            var source = $('#source_id').val();
            var utm_source = $('#utm_source_id').val();
            var utm_campaign = $('#utm_campaign_id').val();
            var lead_data_source = $('#lead_data_source_id').val();
            var lead_stp_flag = $('#lead_stp_flag_id').val();
            var lead_screener_assign_user = $('#lead_screener_assign_user_id').val();
            var stage = $('#stage_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (email == "") {
                catchError("Personal email cannot be empty");
                return false;
            }
            if (mobile == "") {
                catchError("Personal mobile cannot be empty");
                return false;
            }
            if (loan_amount == "") {
                catchError("loan amount cannot be empty");
                return false;
            }
            if (pancard == "") {
                catchError("Pancard number cannot be empty");
                return false;
            }
            if (gender == "") {
                catchError("Gender cannot be empty");
                return false;
            }
            if (dob == "") {
                catchError("DOB cannot be empty");
                return false;
            }
            if (religion_id == "") {
                catchError("Religion cannot be empty");
                return false;
            }
            if (marital_status_id == "") {
                catchError("Marital status cannot be empty");
                return false;
            }
            if (current_house == "") {
                catchError("Current house cannot be empty");
                return false;
            }
            if (current_locality == "") {
                catchError("Current locality cannot be empty");
                return false;
            }
            if (current_state == "") {
                catchError("State cannot be empty");
                return false;
            }
            if (current_city == "") {
                catchError("City cannot be empty");
                return false;
            }
            if (residence_pincode == "") {
                catchError("Pincode cannot be empty");
                return false;
            }
            if (lead_data_source == "") {
                catchError("Lead Source cannot be empty");
                return false;
            }
            if (lead_stp_flag == "") {
                catchError("Lead Flag cannot be empty");
                return false;
            }
            if (stage == "") {
                catchError("Stage cannot be empty");
                return false;
            }
            if (lead_screener_assign_user == "") {
                catchError("Users cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/savePersonalDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {lead_screener_assign_user: lead_screener_assign_user, check_email: check_email, check_alternate_email: check_alternate_email, check_mobile: check_mobile, check_alternate_mobile: check_alternate_mobile, lead_id: lead_id, email: email, alternate_email: alternate_email, mobile: mobile, alternate_mobile: alternate_mobile, loan_amount: loan_amount, pancard: pancard, gender: gender, dob: dob, religion_id: religion_id, marital_status_id: marital_status_id, spouse_name: spouse_name, spouse_occupation_id: spouse_occupation_id, qualification: qualification, current_house: current_house, current_locality: current_locality, current_landmark: current_landmark, current_state: current_state, current_city: current_city, residence_pincode: residence_pincode, source: source, utm_source: utm_source, utm_campaign: utm_campaign, lead_data_source: lead_data_source, lead_stp_flag: lead_stp_flag, stage: stage, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_personal_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_personal_details').html('Update Personal Details').removeClass('disabled');
                        catchSuccess(data.msg);
                        //console.log(data.msg);
                        //window.location.reload();
                    }
                },
                complete: function () {
                     $('.update_personal_details').html('Update Personal Details').removeClass('disabled');
                }
            });
        });

        $('.update_employment_details').click(function () {
            var lead_id = $('#lead_id').val();
            var employer_name = $('#employer_name_id').val();
            var emp_email = $('#emp_email_id').val();
            var emp_house = $('#emp_house_id').val();
            var emp_street = $('#emp_street_id').val();
            var emp_landmark = $('#emp_landmark_id').val();
            var state = $('#state_id').val();
            var city = $('#city_id').val();
            var emp_pincode = $('#emp_pincode_id').val();
            var emp_residence_since = $('#emp_residence_since_id').val();
            var emp_designation = $('#emp_designation_id').val();
            var emp_department = $('#emp_department_id').val();
            var emp_occupation_id = $('#emp_occupation_id').val();
            var emp_employer_type = $('#emp_employer_type_id').val();
            var salary_mode = $('#salary_mode_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (employer_name == "") {
                catchError("Name cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/saveEmploymentDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {employer_name: employer_name, emp_email: emp_email, emp_house: emp_house, emp_occupation_id: emp_occupation_id, emp_street: emp_street, emp_street: emp_street, emp_landmark: emp_landmark, state: state, city: city, emp_pincode: emp_pincode, emp_residence_since: emp_residence_since, emp_designation: emp_designation, emp_department: emp_department, emp_employer_type: emp_employer_type, salary_mode: salary_mode, lead_id: lead_id, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_employment_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_employment_details').html('Update Employment Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_employment_details').html('Update Employment Details').removeClass('disabled');
                }
            });
        });

        $('.update_transaction_failed_details').click(function () {
            var disb_trans_id = $('#disb_trans_id').val();
            var lead_id = $('#lead_id').val();
            var disburse_refrence_no = $('#disburse_refrence_no_id').val();
            var disb_trans_payment_mode = $('#disb_trans_payment_mode_id').val();
            var loan_disbursement_payment_type = $('#loan_disbursement_payment_type_id').val();
            var disb_trans_status = $('#disb_trans_status_id').val();
            var remarks = $('#remarks_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (disb_trans_payment_mode == "") {
                catchError("Payment Mode cannot be empty");
                return false;
            }
            if (loan_disbursement_payment_type == "") {
                catchError("Trans Payment Type cannot be empty");
                return false;
            }
            if (disb_trans_status == "") {
                catchError("Trans Status cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/updateTransactionDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {disb_trans_id: disb_trans_id, lead_id: lead_id, disburse_refrence_no: disburse_refrence_no, disb_trans_payment_mode: disb_trans_payment_mode, loan_disbursement_payment_type: loan_disbursement_payment_type, disb_trans_status: disb_trans_status, remarks: remarks, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_transaction_failed_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_transaction_failed_details').html('Update Reference Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_transaction_failed_details').html('Update Reference Details').removeClass('disabled');
                }
            });
        });

        $('.update_reference_details').click(function () {
            var lcr_id = $('#lcr_id').val();
            var lead_id = $('#lead_id').val();
            var lcr_name = $('#lcr_name_id').val();
            var lcr_mobile = $('#lcr_mobile_id').val();
            var lcr_relationType = $('#lcr_relationType_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (lcr_name == "") {
                catchError("Name cannot be empty");
                return false;
            }
            if (lcr_mobile == "") {
                catchError("Mobile cannot be empty");
                return false;
            }
            if (lcr_relationType == "") {
                catchError("Relation type cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/saveReferenceDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {lcr_id: lcr_id, lead_id: lead_id, lcr_name: lcr_name, lcr_mobile: lcr_mobile, lcr_relationType: lcr_relationType, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_reference_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_reference_details').html('Update Reference Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_reference_details').html('Update Reference Details').removeClass('disabled');
                }
            });
        });

        $('.update_docs_details').click(function () {
            var lead_id = $('#lead_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (lead_followup_remark == "") {
                catchError("Remark cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/updateDocsDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {lead_id: lead_id,lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_docs_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_docs_details').html('Update Docs Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_docs_details').html('Update Docs Details').removeClass('disabled');
                }
            });
        });

        $('.update_cam_details').click(function () {
            var lead_id = $('#lead_id').val();
            var salary_credit1_date = $('#salary_credit1_date_id').val();
            var salary_credit1_amount = $('#salary_credit1_amount_id').val();
            var salary_credit2_date = $('#salary_credit2_date_id').val();
            var salary_credit2_amount = $('#salary_credit2_amount_id').val();
            var salary_credit3_date = $('#salary_credit3_date_id').val();
            var salary_credit3_amount = $('#salary_credit3_amount_id').val();
            var next_pay_date = $('#next_pay_date_id').val();
            var median_salary = $('#median_salary_id').val();
            var remark = $('#cam_remark_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (salary_credit1_date == "") {
                catchError("Salary date cannot be empty");
                return false;
            }
            if (salary_credit1_amount == "") {
                catchError("Salary amount cannot be empty");
                return false;
            }
            if (next_pay_date == "") {
                catchError("Next pay date cannot be empty");
                return false;
            }
            if (median_salary == "") {
                catchError("Avg. salary cannot be empty");
                return false;
            }
            $.ajax({
                url: '<?= base_url("support/updateCAMDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {lead_id: lead_id, salary_credit1_date: salary_credit1_date, salary_credit1_amount: salary_credit1_amount, salary_credit2_date: salary_credit2_date, salary_credit2_amount: salary_credit2_amount, salary_credit3_date: salary_credit3_date, salary_credit3_amount: salary_credit3_amount, next_pay_date: next_pay_date, median_salary: median_salary, remark: remark, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_cam_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_cam_details').html('Update CAM Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_cam_details').html('Update CAM Details').removeClass('disabled');
                }
            });
        });
        
        

        $('.update_bank_details').click(function () {
            var id = $('#id').val();
            var lead_id = $('#lead_iD').val();
            var bank_name = $('#bank_name_id').val();
            var ifsc_code = $('#ifsc_code_id').val();
            var account_status = $('#account_status_id').val();
            var beneficiary_name = $('#beneficiary_name_id').val();
            var account = $('#account_id').val();
            var confirm_account = $('#confirmaccount_id').val();
            var account_type = $('#account_type_id').val();
            var branch = $('#branch_id').val();
            var lead_followup_remark = $('#lead_followup_remark').val();
           
            if (lead_id == "") {
                catchError("Lead ID cannot be empty");
                return false;
            }
            if (bank_name == "") {
                catchError("Bank name cannot be empty");
                return false;
            }
            if (ifsc_code == "") {
                catchError("IFSC Code cannot be empty");
                return false;
            }
            if (beneficiary_name == "") {
                catchError("Beneficiary name cannot be empty");
                return false;
            }
            if (account == "") {
                catchError("Account cannot be empty");
                return false;
            }
            if (confirm_account == "") {
              catchError("Confirm Account cannot be empty");
              return false;
            }
            if (account_type == "") {
                catchError("Account Type cannot be empty");
                return false;
            }
            if (branch == "") {
                catchError("Branch cannot be empty");
                return false;
            }
            
            
           if (account !== confirm_account) {
               catchError("Account and Confirm Account do not match");
              return false;
            }
            
            $.ajax({
                url: '<?= base_url("support/updateBankDetails") ?>',
                type: 'POST',
                dataType: "json",
                data: {id: id, lead_id:lead_id, bank_name: bank_name, ifsc_code: ifsc_code, account_status: account_status, beneficiary_name: beneficiary_name, account: account, confirm_account: confirm_account,  account_type: account_type, branch: branch, lead_followup_remark: lead_followup_remark, csrf_token},
                beforeSend: function () {
                    $('.update_bank_details').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
                },
                success: function (data) {
                    if (data.err) {
                        catchError(data.err);
                    } else {
                        $('.update_bank_details').html('Update Docs Details').removeClass('disabled');
                        catchSuccess(data.msg);
                    }
                },
                complete: function () {
                     $('.update_bank_details').html('Update Docs Details').removeClass('disabled');
                }
            });
        });
    });


    function blockUpdate(bl_id, bl_active, bl_deleted) {
        if (bl_id == "") {
            catchError("ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("support/blockUpdate") ?>',
            type: 'POST',
            dataType: "json",
            data: {bl_id: bl_id, bl_active: bl_active, bl_deleted: bl_deleted, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    catchSuccess(data.msg);
                    window.location.reload();
                }
            },
        });
    }

    function blogDelete(wb_id, id) {
        if (wb_id == "") {
            catchError("Blog ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("blogDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {wb_id: wb_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }

    function seoDelete(ws_id, id) {
        if (ws_id == "") {
            catchError("SEO ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("seoDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {ws_id: ws_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }
    
	function blacklistedPincodeDelete(mbp_id,id) {
        if (mbp_id == "") {
            catchError("Pincode id cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("blacklistedPincodeDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {mbp_id: mbp_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }
	
    function docsDelete(docs_id, id) {
        if (docs_id == "") {
            catchError("DOCS ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("support/docsDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {docs_id: docs_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }

    function referenceDelete(ref_id, id) {
        if (ref_id == "") {
            catchError("Reference ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("support/referenceDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {ref_id: ref_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }

    function transactionDelete(disb_trans_id, id) {
        if (disb_trans_id == "") {
            catchError("Trans ID cannot be empty");
            return false;
        }
        $.ajax({
            url: '<?= base_url("support/transactionDelete") ?>',
            type: 'POST',
            dataType: "json",
            data: {disb_trans_id: disb_trans_id, csrf_token},
            success: function (data) {
                if (data.err) {
                    catchError(data.err);
                } else {
                    $('#' + id).hide();
                    catchSuccess(data.msg);
                }
            },
        });
    }

    $(function () {
        $('.salaryDate1').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            startDate: '-1m',
            endDate: '-0m'
        });
    });

    $(function () {
        $('.salaryDate2').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            startDate: '-2m',
            endDate: '-1m'
        });
    });

    $(function () {
        $('.salaryDate3').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            startDate: '-3m',
            endDate: '-2m'
        });
    });

    $(function () {
        $('.nextSalaryDate').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            startDate: '+0d'
        });
    });

    $(function () {
        $('.residing_since_date, .employed_since_current_date').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true,
            startDate: '-15y',
            endDate: '-2y'
        });
    });

    $(function () {
        $('.dob_class').datepicker({
            format: 'dd-mm-yyyy',
            defaultDate: "01-01-1990",
            todayHighlight: true,
            autoclose: true,
            //startView: 2,
            startDate: '-55y',
            endDate: '-21y'
        });
    });
</script>
