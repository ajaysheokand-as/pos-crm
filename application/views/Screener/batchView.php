
<?php
$uri = $this->uri->segment(1);
$stage = $this->uri->segment(2);

// echo "<pre>". $totalcount; print_r($leadDetails->result()); exit;
?>
<?php $this->load->view('Layouts/header') ?>
<span id="response" style="width: 100%;float: left;text-align: center;padding-top:-20%;"></span>
<section>
    <div class="width-my">
        <div class="container-fluid">
        <div class="col-md-12">
            <div class="login-formmea">
                <div class="box-widget widget-module">
                    <div class="widget-head clearfix">
                        <span class="h-icon"><i class="fa fa-th"></i></span>
                        <span class="inner-page-tag">Leads </span> 
               
                        <div class="search-desktop">    
                            <button class="btn btn-default inner-page-box" onclick="searchdatalist()"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</button>
                        </div>
                    </div>
                    <div class="widget-container">
                        <div class=" widget-block">
                            <div class="row">
                           
                           
                                <div class="col-md-12" style="padding: 0px !important;">
                               
                                    <div class="page-container list-menu-view">
                                        <div class="page-content">
                                            <div class="main-container">
                                                <div class="container-fluid">
                                                    <div class="col-md-12">
                                                        <div class="login-formmea">
                                                        
                                                        <?php if (!empty($this->session->flashdata('success'))) { ?>
                                                            <div class="alert alert-success" style="background: green; color: #fff;">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                                            <?= $this->session->flashdata('success'); ?>
                                                        </div>
                                                        <?php } else if (!empty($this->session->flashdata('err'))) { ?>
                                                                <div class="alert alert-danger" style="background: red; color: #fff;">
                                                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                                    <?= $this->session->flashdata('err'); ?>
                                                                </div>
                                                           
                                                            <?php } ?>
                                                            <div class="widget-container">
                                                                <div class=" widget-block">
                                                                    <div class="row">
                                                                        <div class="table-responsive">
                                                                            <!-- data-order='[[ 0, "desc" ]]'  dt-table -->
                                                                            <table class="table table-striped table-bordered table-hover"  style="border: 1px solid #dde2eb">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th class="whitespace data-fixed-columns"><b>Lead Id</b></th>
                                                                                        <th class="whitespace"><b>Action </b></th>
                                                                                        <th class="whitespace"><b>First Name</b></th>
                                                                                        <th class="whitespace"><b>Mobile</b></th>
                                                                                        <th class="whitespace"><b>Email</b></th>
                                                                                        <th class="whitespace"><b>Pancard</b></th>
                                                                                        <th class="whitespace"><b>Loan Amount</b></th>
                                                                                        <th class="whitespace"><b>Lead Active</b></th>                                                                            
                                                                                        <th class="whitespace"><b>Reason</b></th>
                                                                                        <th class="whitespace"><b>User Id</b></th>
                                                                                        <th class="whitespace"><b>Lead Rejected DT</b></th>

                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php
                                                                    
                                                                                        foreach ($leadDetails->result() as $row) :
                                                                                            ?>
                                                                                            <tr> 
                                                                                                <td class="whitespace"><?= ($row->lead_id) ? strtoupper($row->lead_id) : "-" ?></td>

                                                                                                <td class="whitespace">
                                                                                            
                                                                                                            <a href="<?= base_url("getRejectDetails/" . $this->encrypt->encode($row->lead_id)) ?>" class="" id="viewLeadsDetails">
                                                                                                                <span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span>
                                                                                                            </a>
                                                                                                    
                                                                                                    </td>  
                                                                                                <td class="whitespace"><?= ($row->first_name) ? strtoupper($row->first_name) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->mobile) ? $row->mobile : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->email) ? strtoupper($row->email) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->pancard) ? strtoupper($row->pancard) : "-" ?></td>
                                                                                                <td class="whitespace"><?= ($row->loan_amount) ? strtoupper($row->loan_amount) : "-" ?></td>
                                                                                                <td class="whitespace"><?= ($row->loan_amount) ? strtoupper($row->lead_active) : "-" ?></td>
                                                                                                <td class="whitespace"><?= ($row->email) ? strtoupper($row->reason) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->email) ? strtoupper($row->lead_screener_assign_user_id) : '-' ?></td>
                                                                                                <td class="whitespace"><?= ($row->pancard) ? strtoupper($row->lead_rejected_datetime) : "-" ?></td>
                                                                                            </tr>
                                                                                            <?php
                                                                                        endforeach; ?>
                                                                                    
                                                                                </tbody>
                                                                            </table>
                                                                        
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
        </div>
        </div>
    </div>
</div>
</section>
<?php $this->load->view('Layouts/footer') ?>
<?php $this->load->view('Tasks/main_js.php') ?>



