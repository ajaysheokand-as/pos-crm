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
    
    .widget-block {
        padding: 20px;
    }
    
      .parent_wrapper .right-side .logo_container a img {
          margin-right: 0px;
          width: 100px;
          background: #000;
          border-radius: 10px;
      }

</style>
    <div class="logo_container">
           <a href="<?= base_url(); ?>"><img src="<?= LMS_COMPANY_LOGO ?>" alt="logo"> <!---<?= base_url('public/front'); ?>/img/dhanvikas-logo.png---> </a>
    </div> 
<section class="ums">