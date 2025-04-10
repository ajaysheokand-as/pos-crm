<section class="parent_wrapper">
<?php
$this->load->view('Layouts/header');
include('inner_layout.php');
?>

    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <?php if (agent == 'CA') { ?>
                                        <div class="col-md-3 drop-me">
                                            <?php $this->load->view('Layouts/leftsidebar') ?>
                                        </div>
                                    <?php } ?>
                                    <div class="col-sm-12">
                                        <div class="login-formmea" style="margin-bottom: 10px;">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Search lead id? </h4>
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
                                                        else if($this->session->flashdata('error') != '') {
                                                            echo '<div class="alert alert-danger alert-dismissible">
                		                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                		                              <strong>' . $this->session->flashdata('error') . '</strong> 
                		                            </div>';
                                                        }
                                                        ?>

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchBankId'); ?>" method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required="" value="<?php if(isset($_POST['lead_id']) && $_POST['lead_id']!=''){echo $_POST['lead_id'];} ?>" placeholder="Please enter lead id*" onkeypress="if (isNaN(String.fromCharCode(event.keyCode)))
                                                                            return false;">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <button type="submit" id="search_lead_id" class="button btn">Search LEAD ID</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                      
                                        <?php  if(!empty($status)){ 
                                        ?>
                                        <div class="login-formmea">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Docs Lists</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class=" widget-block">                                                        
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped table-bordered table-hover" id="domainTable" style="border:1px solid #dde2eb">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="whitespace data-fixed-columns"><b>S.No.</b></th>
                                                                             <th class="whitespace"><b>Lead Id </b></th>   
                                                                            <th class="whitespace"><b>Beneficiary Name </b></th>                              
                                                                            <th class="whitespace"><b>Bank Account </b></th>
                                                                            <th class="whitespace"><b>IFSC Code</b></th>
                                                                            <th class="whitespace"><b>Bank Name</b></th>
                                                                            <th class="whitespace"><b>Branch</b></th>
                                                                        
                                                                            <th class="whitespace"><b>Action</b></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach($bank_list as $value) { ?>
                                                                        <tr>                                                                            
                                                                            <td class="whitespace data-fixed-columns"><?=$value['id']?></td> 
                                                                            <td class="whitespace"><?=$value['lead_id']?></td>                            
                                                                            <td class="whitespace"><?=$value['beneficiary_name']?></td>
                                                                            <td class="whitespace"><?=$value['account']?></td>
                                                                            <td class="whitespace"><?=$value['ifsc_code']?></td>
                                                                            <td class="whitespace"><?=$value['bank_name']?></td>
                                                                            <td class="whitespace"><?=$value['branch']?></td>
                                                                           
                                                                            <td class="whitespace"><a href="<?= base_url('support/getBankDetailId/'.$this->encrypt->encode($value['lead_id'])); ?>"><span class="glyphicon glyphicon-edit" style="font-size: 20px;"></span></a> <a class="docs_delete_class" title="Delete" href="javascript:void();" onclick="docsDelete('<?=$this->encrypt->encode($value['docs_id']) ?>','id_<?=$i?>');"><i class="fa fa-trash" aria-hidden="true" style="padding : 3px; color : #35b7c4; border : 1px solid #35b7c4;"></i></a></td>                                                                   
                                                                       
                                                                       
                                                                        </tr> 
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                     
                                        <?php }  ?>
                                        
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
<?php $this->load->view('Layouts/footer'); ?>
<?php $this->load->view('Support/support_js'); ?>
</section>
</section>



