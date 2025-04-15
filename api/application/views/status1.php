<?php //$this->load->view("common_front/header") ?>
      <!-- <section class="banner-apply-now"></section> -->
      <section class="welcome section_div">
            <div class="container" style="text-align:center;">
                <h1 style="text-align: -webkit-center;text-decoration: underline;color:green;">Payment Success</h1>
                <div class="row about-tp mt-md-5">
                    <div class="col-lg-6 col-md-6 col-sm-12 welcome-left">
                        <img src="<?= base_url(); ?>thumb.PNG" style="    width: 200px;
    height: 200px;
    border-radius: 100%;
    border: solid 1px #ddd;
    padding: 12px;" alt="Payment Success">
                        <div class="documetation">   
                            <?php 
                                echo "<h3>Thank You. Your order status is ". @$status .".</h3>";
                                echo "<h4>Your Transaction ID for this transaction is ".@$txnid.".</h4>";
                                echo "<h4>We have received a payment of Rs. " . @$amount . ". Your loan account  will updated within 24 hrs.</h4>";
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php //$this->load->view("common_front/footer") ?>