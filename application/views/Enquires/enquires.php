<?php
$uri = $this->uri->segment(1);
$stage = $this->uri->segment(2);

// echo "<pre>". $totalcount; print_r($leadDetails->result()); exit;
?>
<section class="parent_wrapper">
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

<span id="response" style="width: 100%;float: left;text-align: center;padding-top:-20%;"></span>
<section>
        <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
    <div class="width-my">
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
                    <div class="col-md-12" style="padding: 0px !important;">
                        <div class="page-container list-menu-view">
                            <div class="page-content">
                                <div class="main-container">
                                    <div class="container-fluid">
                                        <div class="col-md-12">
                                            <div class="login-formmea">
                                                <div class="box-widget widget-module">
                                                    <div class="widget-head clearfix">
                                                        <span class="h-icon"><i class="fa fa-th"></i></span>
                                                        <span class="inner-page-tag">Enquires </span> 
                                                      
                                                            <span class="counter inner-page-box"><?= count($leadDetails); ?></span>
                                                     

                                                        <!--<div class="tb_search1" style="float:right; margin-right: 10px;">-->
                                                        <!--    <form method="POST" class="form-inline" style="margin-top:8px;" action="<?= base_url('enquires') ?>">-->
                                                        <!--        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />-->
                                                        <!--        <div class="form-group">-->
                                                        <!--            <select class="form-control" id="search_type" name="search_type" required="">-->
                                                        <!--                <option value="">Select</option>-->
                                                        <!--                <option <?= ($_POST['search_type'] == 1) ? 'selected' : "" ?> value="1">Mobile</option>-->
                                                        <!--                <option <?= ($_POST['search_type'] == 2) ? 'selected' : "" ?> value="2">Email</option>-->
                                                        <!--                <option <?= ($_POST['search_type'] == 3) ? 'selected' : "" ?> value="3">Enquire ID</option>-->
                                                        <!--            </select>-->
                                                        <!--        </div>-->
                                                        <!--        <div class="form-group">-->
                                                        <!--            <input type="text" class="form-control" name="search_input" autocomplete="off" placeholder="Search as" value="<?=!empty($_POST['search_input'])?$_POST['search_input']:''?>">-->
                                                        <!--        </div>-->

                                                        <!--        <button type="submit" class="btn btn-primary">Search</button>-->
                                                        <!--        <button type="reset" class="btn btn-primary" onclick="location.href='<?= base_url('enquires') ?>'">Reset</button>-->
                                                        <!--    </form>-->
                                                        <!--</div>-->
                                                    </div>
                                                </div>

                                                <div class="widget-container">
                                                    <div class=" widget-block">
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                                                <table class="table table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace data-fixed-columns"><b>Sr No.</b></th>
                                                                            <th class="whitespace"><b>Lead Id</b></th>
                                                                            <th class="whitespace"><b>Mobile</b></th>
                                                                            <th class="whitespace"><b>Source</b></th>
                                                                            <th class="whitespace"><b>UTM Source</b></th>
                                                                            <th class="whitespace"><b>Reason</b></th>
                                                                            <th class="whitespace"><b>Status</b></th>
                                                                            <th class="whitespace"><b>Apply&nbsp;On</b></th>

                                                                        </tr>
                                                                    </thead>
                                                                    <tbody> 
                                                                        <?php
																			$i = 1;
                                                                            foreach ($leadDetails as $row) :
                                                                                   
                                                                                ?>
                                                                                <tr>
                                                                                    <td class="whitespace data-fixed-columns">
                                                                                        <?= $i ?>
                                                                                    </td>
                                                                                    <td class="whitespace"><?= ($row['lead_id']) ? $row['lead_id'] : '-' ?></td>
                                                                                    <td class="whitespace"><?= ($row['mobile']) ? $row['mobile'] : '-' ?></td>
                                                                                    <td class="whitespace"><?= ($row['source']) ? $row['source'] : '-' ?></td>
                                                                                    <td class="whitespace"><?= ($row['utm_source']) ? $row['utm_source'] : '-' ?></td>
                                                                                     <td class="whitespace"><?= ($row['lead_is_mobile_verified']) ? 'Pancard not verified' : 'OTP not verified' ?></td>
                                                                                    <td class="whitespace"><?= ($row['status']) ? $row['status'] : '-' ?></td>
                                                                                    <td class="whitespace"><?= ($row['created_on']) ?></td>
                                                                                </tr>
                                                                                <?php
																				$i++;
                                                                            endforeach;
                                                                         ?>
                                                                    </tbody>
                                                                </table>
                                                                <?= $links; ?>
                                                            </div>
                                                        </div>
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
</div>
</section>
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Tasks/main_js.php') ?>
</section>
<section>
<script type="text/javascript">

    $(document).ready(function () {
        $('#txt_searchall').keyup(function () {
            var search = $(this).val().toUpperCase();
            $('table tbody tr').hide();
            var len = $('table tbody tr:not(.notfound) td:contains("' + search + '")').length;
            if (len > 0) {
                $('table tbody tr:not(.notfound) td:contains("' + search + '")').each(function () {
                    $(this).closest('tr').show();
                    $('.price-counter').text(len);
                });
            } else {
                $('.notfound').show();
                $('.price-counter').text(len);
            }
        });
    });

</script>
