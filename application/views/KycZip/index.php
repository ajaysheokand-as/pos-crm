<?php $this->load->view('Layouts/header') ?>



<section>
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-8">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <div class="col-md-9">
                                        <div class="login-formmea" style="margin-bottom: 20px;">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>KYC LOAN DOCS</h4>
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

                                                        <form id="ifscdata" autocomplete="off" action="<?= base_url('loan-kyc-download-zip'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">

                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="loan_no" id="loan_no"  placeholder="Loan Number" required>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <select class="form-control" name="fy_year" id="fy_year" required>
                                                                        <option value="">Select FY</option>
                                                                        <option value="1">2021-22</option>
                                                                        <option value="2">2022-23</option>
                                                                    </select>    
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <button type="submit" id="searchifsc" class="button btn">Download Zip</button>
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


<?php $this->load->view('Layouts/footer') ?>
