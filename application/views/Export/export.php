<section class="parent_wrapper">
<?php
$csrf = array(
    'name' => $this->security->get_csrf_token_name(),
    'hash' => $this->security->get_csrf_hash()
);
?>
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
      }

</style>
<head>
    <style>
        #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 96.5% !important;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        #customers tr:nth-child(even) {e
            background-color: #f2f2f2;
        }

        #customers tr:hover {
            background-color: #ddd;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #0c70ab;
            color: white;
        }

        #customers tfoot {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #0c70ab;
            color: white;
        }
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
                                                            <?php if ($this->session->flashdata('msg') != '') { ?>
                                                                <div class="alert alert-success alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <strong><?= $this->session->flashdata('msg'); ?></strong>
                                                                </div>
                                                                <?php
                                                            }
                                                            if ($this->session->flashdata('err') != '') {
                                                                ?>
                                                                <div class="alert alert-danger alert-dismissible">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <strong><?= $this->session->flashdata('err'); ?></strong>
                                                                </div>
                                                            <?php } ?>

                                                            <form id="ExoprtFormData" action="<?= base_url("FilterExportReports") ?>" method="POST" enctype="multipart/form-data">
                                                                <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
                                                                <input type="hidden" name="report_type" id="report_type" />
                                                                <div class="col-md-2">
                                                                    <label>Export Data</label>
                                                                    <select class="form-control" name="report_id" id="report_id">
                                                                        <option value="">Select</option>
                                                                        <?php foreach ($masterExport as $row) { 
																			if($row['permission'] == 1) { 
																		?>
                                                                            <option value='<?= $row["m_export_id"] ?>' ><?= $row["m_export_name"] ?></option>
                                                                        <?php
																			}
																		};
																		?>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <label>From Date</label>
                                                                    <input type="text" class="form-control" name="from_date" id="from_date" title="From Date" autocomplete="off">
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <label>To Date</label>
                                                                    <input type="text" class="form-control" name="to_date" id="to_date" title="To Date" autocomplete="off">
                                                                </div>

                                                                <div class="col-md-2" style="width: 130px;">
                                                                    <button class="btn btn-info" id="export_report" name="export_report" style="background-color : #35b7c4;margin-top: 23px;"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Export</button>
                                                                </div>
                                                            </form>
                                                            <!-- <div class="col-md-2">
                                                                <button class="btn btn-info" id="generate_report" name="generate_report" style="background-color : #35b7c4;margin-top: 23px;"><i class="fa fa-file-code-o" aria-hidden="true"></i>&nbsp;Report</button>
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div id="reportData"></div> -->
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
    // $("#from_date, #to_date").keypress(function myfunction(event) {
    //     var regex = new RegExp("^[0-9?=.*!@#$%^&*]+$");
    //     var key = String.fromCharCode(event.charCode ? event.which : event.charCode);
    //     if (!regex.test(key)) {
    //         event.preventDefault();
    //         return false;
    //     }
    //     return false;
    // });

    // $("#to_date").change(function() {
    //     var dateTo = $("#to_date").val();
    //     var dateFrom = $("#from_date").val();
    //     if (dateTo == "" && dateFrom == "") {
    //         $(".SearchForExport").prop('disabled', true);
    //     } else {
    //         $(".SearchForExport").prop('disabled', false);
    //     }
    // });

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

    $("#export_report").click(function (e) {
        $("#cover").show();
        $("#cover").fadeOut(1750);
    });

    $("#generate_report").click(function (e) {
        $('#report_type').val(1);
        MIS(e);
    });

    function MIS(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url("FilterExportReports") ?>',
            type: 'POST',
            data: $('#ExoprtFormData').serialize(),
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
                    $('#report_id').val('');
                    $('#reportData').html(response['reportData']);
                }
            },
            complete: function () {
                $(this).html('Bank Analysis').removeClass('disabled');
                $("#cover").fadeOut(1750);
            }
        });
    }
</script>
