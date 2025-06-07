<section class="parent_wrapper">
<?php $this->load->view('Layouts/header') ?>


<section class="right-side">
<!-- section start -->
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

   <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
<section class="ums">
    
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
                                    <div class="col-md-12  pl0 mt20 div-right-sidebar">
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Add Holiday Details</h4>
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

                                                        <form autocomplete="off" action="<?= base_url('saveHolidayDetails'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">


                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span>Holiday Date</label>
                                                                    <input type="text" class="form-control" name="holiday_date" id="holiday_date" value="" readonly required>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label><span class="span">*</span>Holiday Name</label>
                                                                    <input type="text" class="form-control" name="holiday_name" maxlength="100" onkeydown="return /[a-z,0-9 ]/i.test(event.key)" onblur="if (this.value == '') {
                                                                                this.value = '';
                                                                            }" onfocus="if (this.value == '') {
                                                                                        this.value = '';
                                                                                    }" required>
                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button type="submit" class="button-add btn">ADD Holiday Details</button>
                                                                </div>
                                                            </div>

                                                        </form>





                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($holiday_data)) { ?>
                                            <div class="widget-container">
                                                <div class=" widget-block">
                                                    <div class="row">
                                                        <div class="scroll_on_x_axis">
                                                            <table class="table dt-table1 table-striped table-bordered table-responsive table-hover" style="border: 1px solid #dde2eb">
                                                                <thead>

                                                                    <tr>
                                                                        <th class="whitespace"><b>#</b></th>
                                                                        <th class="whitespace"><b>Date</b></th>
                                                                        <th class="whitespace"><b>Name</b></th>
                                                                        <th class="whitespace"><b>Created By</b></th>
                                                                        <th class="whitespace"><b>Created On</b></th>
                                                                        <?php if (agent == 'CA') { ?>
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        <?php } ?>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
//                                                                    $i = 1;
                                                                    foreach ($holiday_data as $value) {
                                                                        ?>
                                                                        <tr class="table-default">
                                                                            <td class="whitespace"><?= $value['ch_id']; ?></td> 
                                                                            <td class="whitespace"><?= display_date_format($value['ch_holiday_date'],2); ?></td> 
                                                                            <td class="whitespace"><?= $value['ch_holiday_name']; ?></td> 
                                                                            <td class="whitespace"><?= $value['name']; ?></td> 
                                                                            <td class="whitespace"><?= display_date_format($value['ch_created_datetime'],1); ?></td> 
                                                                            <?php if (agent == 'CA') { ?>
                                                                                <td class="whitespace">
                                                                                    <a  class="btn-danger btn-sm" href="<?= base_url('deleteHolidayDetails/' . $value['ch_id']) ?>"><i class="fa fa-trash-o"></i></a>
                                                                                </td>
                                                                            <?php } ?>
                                                                        </tr>
                                                                        <?php
//                                                                        $i++;
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>

                                                        </div>

                                                        <?php echo $links; ?>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php } ?>

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

