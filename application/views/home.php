
<section class="parent_wrapper">


<?php $this->load->view('Layouts/header');?>

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
          width: 89px;
      }

</style>
<section>
    <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
    <div class="container dashboard-wid  default-page-height">

        <div class="taskPageSizeDashboard ">

            <div class="row default-page-height">
            
                <?php  $i = 0; foreach($menusList->result() as $menu) : ?>
             
                <div class="col-md-2 col-sm-6 col-xs-6 col-md-2-me">
                  
                    <a href="<?= base_url($menu->route_link . "/" . $menu->stage) ?>">

                        <!--<div class="lead-box text-center dashboardBox" style="background:<?= $menu->box_bg_color ?>">-->
                          <div class="lead-box text-center dashboardBox">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="col-md-12"><span class="bookmark-title"><?= $menu->menu_name ?></span></div>
                                    <div class="text-center serviceBox">
	                                    <div class="service-icon orange">
                                            <div class="front-content service-icon">
                                            <i class="<?= $menu->icon ?>"></i>
			                                </div>
						                </div>
                                    </div>

                                </div>

                                <!-- <div class="col-md-6"><strong class="counter"><?php //echo $leadcount[$i] ?></strong></div> -->

                            <!--<div class="col-md-12"><span class="bookmark-title"><?= $menu->menu_name ?></span></div>-->

                            </div>

                        </div>

                    </a>

                </div>

                <?php $i++; endforeach; ?>

                

            </div>

        </div>

    </div>

</section>



<?php $this->load->view('Layouts/footer') ?>
</section>
<style type="text/css">
</section>
