<section class="parent_wrapper">
<?php $this->load->view('Layouts/header'); ?>


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
<!-- section start -->
    <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div>
<section>
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <?php if (agent == 'CA') { ?>
                                        <div class="drop-me">
                                            <?php $this->load->view('Layouts/leftsidebar') ?>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-12  pl0 mt20">
                                        <div class="login-formmea" style="margin-bottom: 10px;">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Search IFSC Code </h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">

                                                        <?php
                                                        if ($this->session->flashdata('error') != '') {
                                                            echo '<div class="alert alert-danger alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('error') . '</strong> 
                		                            </div>';
                                                        }
                                                        ?>

                                                        <form id="ifscdata" autocomplete="off" action="<?= base_url('searchIfscCode'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">

                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="ifsc" id="name">
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <button type="submit" id="searchifsc" class="button btn">Search IFSC</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Add Bank Details</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">

                                                        <?php
                                                        if ($this->session->flashdata('message') != '') {
                                                            echo '<div class="alert alert-success alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('message') . '</strong> 
                		                            </div>';
                                                        }
                                                        if ($this->session->flashdata('err') != '') {
                                                            echo '<div class="alert alert-danger alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('err') . '</strong> 
                		                            </div>';
                                                        }
                                                        ?>

                                                        <form autocomplete="off" action="<?= base_url('saveBankDetails'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span>Bank IFSC</label>
                                                                    <input type="text" class="form-control" name="ifsc" id="ifsc" value="" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span>Bank Name</label>
                                                                    <input type="text" class="form-control" name="name" id="name" value="" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span>Bank Branch</label>
                                                                    <input type="text" class="form-control" name="branch" id="branch" value="" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span> Bank state</label>
                                                                    <select type="text" class="form-control" name="state" id="state" required>
                                                                        <option value="">Select</option>

                                                                        <?php foreach ($state as $value) { ?>
                                                                            <option value="<?= $value['m_state_name'] ?>"><?= $value['m_state_name'] ?></option>

                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span> Bank District</label>
                                                                    <input type="text" class="form-control" name="district" id="district" value="" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span> Bank City</label>
                                                                    <input type="text" class="form-control" name="city" id="city" value="" required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span> Bank Address</label>
                                                                    <input type="text" class="form-control" name="address" id="address" value="" required>
                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="submit" class="button-add btn">ADD Bank Details</button>
                                                                </div>
                                                            </div>

                                                        </form>
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
</section>




<!-- footer -->
<?php $this->load->view('Layouts/footer') ?>
</section>
</section>

<!-- <script>
    // $("#searchifsc").click(function(e) {
    //     MIS(e);
    // });

    $("#searchifsc").on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url("searchIfscCode") ?>',
            type: 'POST',
            data: $('#ifscdata').serialize(),
            dataType: "json",
            beforeSend: function() {
                $("#cover").show();
                $(this).html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Processing...').addClass('disabled', true);
            },
            success: function(data) {
                if (data != "") {
                    alert("No Record Found");
                } else {
                $('#data').html(data);
                $('#sucess-message').html('ok').slideUp();
                }
            },

            complete: function() {
                $(this).html('Bank Analysis').removeClass('disabled');
                $("#cover").fadeOut(1750);
            }
        });
    });
</script> -->
