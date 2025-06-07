<section class="parent_wrapper">
<?php
$csrf = array(
    'name' => $this->security->get_csrf_token_name(),
    'hash' => $this->security->get_csrf_hash()
);
?>
<?php $this->load->view('Layouts/header') ?>
<section class="right-side">
<head>
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

        .redalart-me {
            background: #fff;
            border: solid 1px #ddd;
            padding: 10px;
            text-align: left;
            color: #e30000;
        }

        /* #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 90.5% !important;
        } */

        /* #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        } */

        /* #customers tr:nth-child(even) {
            background-color: #f2f2f2;
        } */

        /* #customers tr:hover {
            background-color: #ddd;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #324ab2;
            color: white;
        }

        #customers tfoot {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #0c70ab;
            color: white;
        } */

        .my-td {
            padding: 3px 7px !important;

        }

        body {

            font-family: 'trebuchet MS', 'Lucida sans', Arial;
            font-size: 14px;
            color: #444;
        }

        table {
            border-collapse: collapse;
            width: auto;
            /* display: block; */
            overflow: auto;
        }

        .bordered {
            border: solid #ccc 1px;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            border-radius: 6px;
            -webkit-box-shadow: 0 1px 1px #ccc;
            -moz-box-shadow: 0 1px 1px #ccc;
            box-shadow: 0 1px 1px #ccc;
        }

        .bordered tr:hover {
            background: #fbf8e9;
            -o-transition: all 0.1s ease-in-out;
            -webkit-transition: all 0.1s ease-in-out;
            -moz-transition: all 0.1s ease-in-out;
            -ms-transition: all 0.1s ease-in-out;
            transition: all 0.1s ease-in-out;
        }

        .bordered td,
        .bordered th {
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
            padding: 7px;
            text-align: center;
        }

        .bordered th {
            background-color: #dce9f9;
            background-image: -webkit-gradient(linear, left top, left bottom, from(#ebf3fc), to(#dce9f9));
            background-image: -webkit-linear-gradient(top, #ebf3fc, #dce9f9);
            background-image: -moz-linear-gradient(top, #ebf3fc, #dce9f9);
            background-image: -ms-linear-gradient(top, #ebf3fc, #dce9f9);
            background-image: -o-linear-gradient(top, #ebf3fc, #dce9f9);
            background-image: linear-gradient(top, #ebf3fc, #dce9f9);
            -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, .8) inset;
            -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, .8) inset;
            box-shadow: 0 1px 0 rgba(255, 255, 255, .8) inset;
            border-top: none;

        }

        .bordered td:first-child,
        .bordered th:first-child {
            border-left: none;
        }

        .bordered th:first-child {
            -moz-border-radius: 6px 0 0 0;
            -webkit-border-radius: 6px 0 0 0;
            border-radius: 6px 0 0 0;
        }

        .bordered th:last-child {
            -moz-border-radius: 0 6px 0 0;
            -webkit-border-radius: 0 6px 0 0;
            border-radius: 0 6px 0 0;
        }

        .bordered th:only-child {
            -moz-border-radius: 6px 6px 0 0;
            -webkit-border-radius: 6px 6px 0 0;
            border-radius: 6px 6px 0 0;
        }

        .bordered tr:last-child td:first-child {
            -moz-border-radius: 0 0 0 6px;
            -webkit-border-radius: 0 0 0 6px;
            border-radius: 0 0 0 6px;
        }

        .bordered tr:last-child td:last-child {
            -moz-border-radius: 0 0 6px 0;
            -webkit-border-radius: 0 0 6px 0;
            border-radius: 0 0 6px 0;
        }


        .footer-tabels-text {
            color: #fff;
            background: #0363a3 !important;
            font-size: 14px;
            font-weight: bold;
        }

        .disbu {
            color: #0363a3;
            border: solid 1px #38a7f1 !important;
            border-bottom: none !important;
            border-right: none !important;
            font-weight: bold;
        }

        .no-of-case {
            color: #0363a3 !important;
            border: solid 1px #38a7f1 !important;
            border-right: none !important;
            border-radius: 0px !important;
            font-weight: bold;
            text-align:center !important;
        }

        .amounts {
            color: #0363a3 !important;
            border: solid 1px #38a7f1 !important;
            border-right: none !important;
            font-weight: bold;
        }

        .datess {
            color: #0363a3 !important;
            border: solid 1px #38a7f1 !important;
            font-weight: bold;
        }

        .disburse-green {
            background: #9acba9 !important;
            color: rgb(0, 0, 0) !important;
            border: solid 1px #38a7f1 !important;
            border-right: none !important;
            border-radius: 0px !important;
        }

        /* Fixed Headers */

        .fir-header {
            position: sticky;
            top: 7%;
            z-index: 4;
        }

        .sec-header {
            position: sticky;
            top: 10.5%;
            z-index: 4;
        }

        .thr-header {
            position: sticky;
            top: 14%;
            z-index: 4;
        }
        #report-wt{text-align:center;background:transparent;border:1px solid #ccc}
        #report-dt{text-align:center;background:#fbe4ad;}
        #report-dt{text-align:center;background:#fbe4ad;}
        #report-pink{text-align:center !important;background:pink;border:1px solid #000}
        #report-lg{background:#fff;height:60px;width:280px;margin:0 auto}
        #report-mn{text-align:center;background:#cc98c4 !important;border:1px solid #000;box-shadow:none}
        #report-red{background:red}
        #report-bl{background-color: #dce9f9;border:1px solid #000}
        #report-gr{background: #a2ca5b;border:1px solid #222}
        #report-yl{ text-align: center; background: #fbe4ad;color: #16b716;border:1px solid #000;}
        #text-bl{color:#0f80c4; font-weight:bold;}
        #text-rd{color:#ff0000; font-weight:bold;}
        #text-blk{color:#222; font-weight:bold;}

    </style>

</head>

<!-- section start -->
<section>
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
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <div class="col-md-12">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <div class="row">
                                                            <form id="Report" action="" method="POST" enctype="multipart/form-data">
                                                                <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                                                                <!-- <input type="hidden" name="report_type" id="report_type" /> -->
                                                                <div class="col-md-3">
                                                                    <label>MIS Report</label>
                                                                    <select class="form-control" name="report_id" id="report_id">
                                                                        <option value="">Select</option>
                                                                        <?php /* foreach ($masterExport as $row) { ?>
                                                                            <option value='<?= $row['m_report_id'] ?>'><?= $row['m_report_heading'] ?></option>
                                                                        <?php }; */?>
																		<?php foreach ($masterExport as $row) { 
																			if($row['permission'] == 1) { 
																		?>
                                                                            <option value='<?= $row["m_report_id"] ?>' ><?= $row["m_report_heading"] ?></option>
                                                                        <?php
																			}
																		};
																		?>
                                                                    </select>
                                                                </div>


                                                                <div class="col-md-2" style="display: none" id="month_year">
                                                                    <label id="month_year_label"></label>
                                                                    <input class="form-control" type="month" name="month_data" min="2020-03" value="">
                                                                </div>

                                                                <div class="col-md-2" id="f_date" style="display: none">
                                                                    <label id="from_date_label"></label>
                                                                    <input type="text" class="form-control" name="from_date" id="from_date" title="From Date" autocomplete="off">
                                                                </div>

                                                                <div class="col-md-2" id="t_date" style="display: none">
                                                                    <label id="to_date_label"></label>
                                                                    <input type="text" class="form-control" name="to_date" id="to_date" title="To Date" autocomplete="off">
                                                                </div>

                                                                <div class="col-md-2" id="financial_year" style="display: none">
                                                                    <label id="financial_year_label"></label>
                                                                    <select class="form-control" name="financial_year" id="report_id">
                                                                        <option value="">Select</option>
                                                                        <option value="01-04-2018">FY 2018-19</option>
                                                                        <option value="01-04-2019">FY 2019-20</option>
                                                                        <option value="01-04-2020">FY 2020-21</option>
                                                                        <option value="01-04-2021">FY 2021-22</option>
                                                                        <option value="01-04-2022">FY 2022-23</option>
                                                                    </select>
                                                                </div>
                                                                
                                                               
                                                                <div class="col-md-2" id="source_name" style="display: none">
                                                                    <label id="source_name_label"></label>
                                                                    <select class="form-control" name="source_name" id="report_id">
                                                                        <option value="">Select</option>
                                                                  
                                                                        <option value="Website BL">Website</option>
                                                                        <option value="Import">Import</option>
                                                                        <option value="Website Instant Loan">Website Instant Loan</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-2" style="width: 130px;">
                                                                    <button class="btn btn-info" id="generate_report" name="generate_report" style="background-color : #35b7c4;margin-top: 23px;"><i class="fa fa-book" aria-hidden="true"></i>&nbsp; Generate Report</button>
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top:30px; margin-bottom:30px;">
                                        <div id="reportData"></div>
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

</section>
<!-- footer -->
<?php $this->load->view('Layouts/footer') ?>
</section>
</section>
<script type="text/javascript">
    $('.search').click(function (e) {

        e.preventDefault();

        const filter = $('#search').serialize();

        console.log(filter);

        $.ajax({

            url: '<?= base_url("filter") ?>',

            type: 'POST',

            dataType: "json",

            data: filter,

            // async: false,

            beforeSend: function () {

                $('#btnSearch').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...').addClass('disabled', true);

            },

            success: function (response) {

                $(".searchResults").html(response);

                $('#search')[0].reset();

            },

            complete: function () {

                $('#btnSearch').html('Search').removeClass('disabled');

            },

        });
    });


    $("#generate_report").click(function (e) {
        MIS(e);
    });

    function MIS(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url("Report") ?>',
            type: 'POST',
            data: $('#Report').serialize(),
            dataType: "json",
            beforeSend: function () {
                $("#cover").show();
                $(this).html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...').addClass('disabled', true);
            },
            success: function (response) {
                $('#reportData').empty();
                if (response.err) {
                    catchError(response.err);
                } else {
                    // $('#report_id').val('');
                    $('#reportData').html(response['reportData']);
                }
            },
            complete: function () {
                $(this).html('Bank Analysis').removeClass('disabled');
                $("#cover").fadeOut(1750);
            }
        });
    }



    $(function () {
        $("#report_id").change(function () {
           
            var val = parseInt($(this).val());
            var showDiv = [4, 6, 11, 13, 14, 29, 9, 22, 23];

            if (showDiv.indexOf(val) != -1) {
                $("#month_year").show();
                $("#f_date").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                $("#month_wise").hide();
                $("#t_date").hide();
                if (val == 4) {
                    $("#month_year_label").text("Disbursal Month-Year")
                }
                if (val == 13) {
                    $("#month_year_label").text("Repayment Month-Year")
                }
                if (val == 14) {
                    $("#month_year_label").text("Disbursal Month-Year")
                }
                if (val == 29) {
                    $("#month_year_label").text("Repay Month-Year")
                }
                if (val == 9) {
                    $("#month_year_label").text("Repay Month-Year")
                }
                if (val == 22) {
                    $("#month_year_label").text("Repay Month-Year")
                }
                if (val == 23) {
                    $("#month_year_label").text("Repay Month-Year")
                }
                if (val == 24) {
                    $("#month_year_label").text("Repay Month-Year")
                }
              
            } else if (val == 7) {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                $("#f_date").hide();
                $("#t_date").hide();
                $("#month_wise").hide();
            } else if (val == 18) {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                
                $("#f_date").show();
                $("#t_date").hide();
                $("#month_wise").hide();
                $("#from_date_label").text("Lead Initiated Till Date");
            } else if (val == 19) {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                $("#f_date").show();
                $("#t_date").hide();
                $("#month_wise").hide();
                $("#from_date_label").text("Lead Initiated Till Date")
            } else if (val == 47) {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                $("#f_date").show();
                $("#t_date").hide();
                $("#month_wise").hide();
                $("#from_date_label").text("Due/Collection Date")
            } else if (val == 21 || val == 34 || val == 35 ) {
                $("#month_year").hide();
                $("#f_date").hide();
                $("#t_date").hide();
                $("#financial_year").show();
                $("#source_name").hide();
                $("#month_wise").hide();
                if (val == 34) {
                    $("#financial_year_label").text("Disbursal Financial Year")
                } else if (val == 21) {
                    $("#financial_year_label").text("Disbursal Financial Year")
                } else if (val == 35) {
                    $("#financial_year_label").text("Repayment Financial Year")
                }

            } else if (val == 63) {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#source_name").hide();
                $("#f_date").hide();
                $("#t_date").hide();
                $("#month_wise").hide();
            } else if (val == 66) {
                if (val == 66){
                    $("#from_date_label").text("From Date")
                    $("#to_date_label").text("To Date")
                    $('#source_name_label').text('Source Name')
                }
             
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#month_wise").hide();
                $("#source_name").show();
                $("#f_date").show();
                $("#t_date").show();
            }else if (val == 67) {
                if (val == 67){
                    $("#month_year_label").text("Lead Month Year")
                    $('#source_name_label').text('Source Name')
                } 
              $("#month_year").hide();
                $("#financial_year").hide();
                $("#month_year").show();
                $("#source_name").show();
                $("#f_date").hide();
                $("#t_date").hide();
            }else {
                $("#month_year").hide();
                $("#financial_year").hide();
                $("#month_wise").hide();
                $('#source_name').hide();
                $("#f_date").show();
                $("#t_date").show();
                if (val == 3) {
                    $("#from_date_label").text("Sanction From Date")
                    $("#to_date_label").text("Sanction To Date")
                }
                if (val == 8) {
                    $("#from_date_label").text("Lead Initiated From Date")
                    $("#to_date_label").text("Lead Initiated To Date")
                }
                if (val == 10 || val == 12) {
                    $("#from_date_label").text("Call Initiated From Date")
                    $("#to_date_label").text("Call Initiated To Date")
                }
                if (val == 15) {
                    $("#from_date_label").text("Disbursed From Date")
                    $("#to_date_label").text("Disbursed To Date")
                }
                if (val == 16) {
                    $("#from_date_label").text("Visit Initiated From Date")
                    $("#to_date_label").text("Visit Initiated To Date")
                }
                if (val == 17) {
                    $("#from_date_label").text("Allocated From Date")
                    $("#to_date_label").text("Allocated To Date")
                }
                if (val == 20) {
                    $("#from_date_label").text("Conveyance From Date")
                    $("#to_date_label").text("Conveyance To Date")
                }
                if (val == 25) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 26) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 27) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 28) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 30) {
                    $("#from_date_label").text("Repayment From Date")
                    $("#to_date_label").text("Repayment To Date")
                }
                if (val == 31) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 32) {
                    $("#from_date_label").text("Lead Entry From Date")
                    $("#to_date_label").text("Lead Entry To Date")
                }
                if (val == 33) {
                    $("#from_date_label").text("Disbursal From Date")
                    $("#to_date_label").text("Disbursal To Date")
                }
                if (val == 37) {
                    $("#from_date_label").text("Repayment From Date")
                    $("#to_date_label").text("Repayment To Date")
                }
               if (val == 54) {
                    $("#from_date_label").text("Collection From Date")
                    $("#to_date_label").text("Collection To Date")
                }
               if (val == 56) {
                    $("#from_date_label").text("Collection From Date")
                    $("#to_date_label").text("Collection To Date")
                }
               if (val == 69) {
                    $("#from_date_label").text("Pre-collection From Date")
                    $("#to_date_label").text("Pre-collection To Date")
                }
                 if (val == 70) {
                    $("#from_date_label").text("Disbursal From Date")
                    $("#to_date_label").text("Disbursal To Date")
                }
            }
        });
    });
</script>